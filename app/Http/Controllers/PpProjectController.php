<?php

namespace App\Http\Controllers;

use App\Http\Requests\PpProjectRequest;
use App\Models\AuditLog;
use App\Models\Directorate;
use App\Models\PpProject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PpProjectController extends Controller
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
            abort(403, 'You do not have permission to manage PP projects.');
        }
    }

    /**
     * PP Landing page — renders the tabbed interface.
     */
    public function landing(Request $request)
    {
        $this->enforcePpAccess($request);

        return Inertia::render('Pp/Index', [
            'activeTab' => $request->query('tab', 'projects'),
        ]);
    }

    public function index(Request $request)
    {
        $this->enforcePpAccess($request);

        $query = PpProject::with('enteredBy')
            ->orderBy('project_code');

        if ($request->filled('sector')) {
            $query->where('sector', $request->input('sector'));
        }
        if ($request->filled('project_stage')) {
            $query->where('project_stage', $request->input('project_stage'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('lifecycle_phase')) {
            $query->where('lifecycle_phase', $request->input('lifecycle_phase'));
        }

        $projects = $query->paginate(30)->withQueryString();

        return Inertia::render('Pp/Index', [
            'activeTab' => 'projects',
            'projects'  => $projects,
            'filters'   => $request->only(['sector', 'project_stage', 'status', 'lifecycle_phase']),
        ]);
    }

    public function store(PpProjectRequest $request)
    {
        $this->enforcePpAccess($request);

        $project = PpProject::create([
            ...$request->validated(),
            'entered_by' => auth()->id(),
        ]);

        AuditLog::log('create', 'PP project created', $project);

        return redirect()->back()->with('success', 'Project created successfully.');
    }

    public function update(PpProjectRequest $request, PpProject $project)
    {
        $this->enforcePpAccess($request);

        $old = $project->toArray();
        $project->update($request->validated());

        AuditLog::log('update', 'PP project updated', $project, $old, $project->fresh()->toArray());

        return redirect()->back()->with('success', 'Project updated successfully.');
    }

    public function destroy(Request $request, PpProject $project)
    {
        $this->enforcePpAccess($request);

        AuditLog::log('delete', 'PP project deleted', $project);
        $project->delete();

        return redirect()->back()->with('success', 'Project deleted successfully.');
    }
}
