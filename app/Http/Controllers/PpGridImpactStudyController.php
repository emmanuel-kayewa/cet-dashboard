<?php

namespace App\Http\Controllers;

use App\Http\Requests\PpGridImpactStudyRequest;
use App\Models\AuditLog;
use App\Models\Directorate;
use App\Models\PpGridImpactStudy;
use App\Models\PpProject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PpGridImpactStudyController extends Controller
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
            abort(403, 'You do not have permission to manage PP grid impact studies.');
        }
    }

    public function index(Request $request)
    {
        $this->enforcePpAccess($request);

        $studies = PpGridImpactStudy::with(['project', 'enteredBy'])
            ->orderByDesc('progress_pct')
            ->paginate(30)
            ->withQueryString();

        $projects = PpProject::orderBy('project_code')->get(['id', 'project_code', 'project_name']);

        return Inertia::render('Pp/Index', [
            'activeTab'        => 'grid-impact-studies',
            'gridImpactStudies' => $studies,
            'ppProjects'       => $projects,
        ]);
    }

    public function store(PpGridImpactStudyRequest $request)
    {
        $this->enforcePpAccess($request);

        $data = $request->validated();
        $data['entered_by'] = auth()->id();

        $study = PpGridImpactStudy::create($data);

        AuditLog::log('create', 'PP grid impact study created', $study);

        return redirect()->back()->with('success', 'Grid impact study created successfully.');
    }

    public function update(PpGridImpactStudyRequest $request, PpGridImpactStudy $gridImpactStudy)
    {
        $this->enforcePpAccess($request);

        $old = $gridImpactStudy->toArray();
        $gridImpactStudy->update($request->validated());

        AuditLog::log('update', 'PP grid impact study updated', $gridImpactStudy, $old, $gridImpactStudy->fresh()->toArray());

        return redirect()->back()->with('success', 'Grid impact study updated successfully.');
    }

    public function destroy(Request $request, PpGridImpactStudy $gridImpactStudy)
    {
        $this->enforcePpAccess($request);

        AuditLog::log('delete', 'PP grid impact study deleted', $gridImpactStudy);
        $gridImpactStudy->delete();

        return redirect()->back()->with('success', 'Grid impact study deleted successfully.');
    }
}
