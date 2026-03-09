<?php

namespace App\Http\Controllers;

use App\Http\Requests\PpMilestoneRequest;
use App\Models\AuditLog;
use App\Models\Directorate;
use App\Models\PpMilestone;
use App\Models\PpProject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PpMilestoneController extends Controller
{
    private function enforcePpAccess(Request $request): void
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }
        if ($user->isAdmin()) {
            return;
        }
        $pp = Directorate::where('code', 'PP')->firstOrFail();
        if (!$user->isDirectorateHead() || (int) $user->directorate_id !== (int) $pp->id) {
            abort(403, 'You do not have permission to manage PP milestones.');
        }
    }

    public function index(Request $request)
    {
        $this->enforcePpAccess($request);

        $milestones = PpMilestone::with(['project', 'enteredBy'])
            ->orderByDesc('actual_date')
            ->paginate(30)
            ->withQueryString();

        $projects = PpProject::orderBy('project_code')->get(['id', 'project_code', 'project_name']);

        return Inertia::render('Pp/Index', [
            'activeTab'  => 'milestones',
            'milestones' => $milestones,
            'ppProjects' => $projects,
        ]);
    }

    public function store(PpMilestoneRequest $request)
    {
        $this->enforcePpAccess($request);

        $milestone = PpMilestone::create([
            ...$request->validated(),
            'entered_by' => auth()->id(),
        ]);

        AuditLog::log('create', 'PP milestone created', $milestone);

        return redirect()->back()->with('success', 'Milestone created successfully.');
    }

    public function update(PpMilestoneRequest $request, PpMilestone $ppMilestone)
    {
        $this->enforcePpAccess($request);

        $old = $ppMilestone->toArray();
        $ppMilestone->update($request->validated());

        AuditLog::log('update', 'PP milestone updated', $ppMilestone, $old, $ppMilestone->fresh()->toArray());

        return redirect()->back()->with('success', 'Milestone updated successfully.');
    }

    public function destroy(Request $request, PpMilestone $ppMilestone)
    {
        $this->enforcePpAccess($request);

        AuditLog::log('delete', 'PP milestone deleted', $ppMilestone);
        $ppMilestone->delete();

        return redirect()->back()->with('success', 'Milestone deleted successfully.');
    }
}
