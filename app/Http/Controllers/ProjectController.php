<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Models\AuditLog;
use App\Models\Directorate;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with(['directorate', 'enteredBy']);

        $user = $request->user();
        if ($user && $user->isDirectorateHead() && $user->directorate_id) {
            $query->where('directorate_id', $user->directorate_id);
        } else {
            $query->when($request->directorate_id, fn($q, $id) => $q->where('directorate_id', $id));
        }

        $projects = $query->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderByDesc('updated_at')
            ->paginate(25);

        $directorates = Directorate::active()->ordered()->get();
        if ($user && $user->isDirectorateHead() && $user->directorate_id) {
            $directorates = $directorates->where('id', $user->directorate_id)->values();
        }

        return Inertia::render('DataEntry/ProjectEntry', [
            'projects' => $projects,
            'directorates' => $directorates,
        ]);
    }

    public function store(ProjectRequest $request)
    {
        $project = Project::create([
            ...$request->validated(),
            'source' => 'manual',
            'entered_by' => auth()->id(),
        ]);

        AuditLog::log('create', 'Project created', $project);

        return redirect()->back()->with('success', 'Project saved successfully.');
    }

    public function update(ProjectRequest $request, Project $project)
    {
        $user = $request->user();
        if ($user->isDirectorateHead() && (int) $project->directorate_id !== (int) $user->directorate_id) {
            abort(403, 'You can only edit projects for your own directorate.');
        }

        $old = $project->toArray();
        $project->update($request->validated());

        AuditLog::log('update', 'Project updated', $project, $old, $project->fresh()->toArray());

        return redirect()->back()->with('success', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        $user = auth()->user();
        if ($user->isDirectorateHead() && (int) $project->directorate_id !== (int) $user->directorate_id) {
            abort(403, 'You can only delete projects for your own directorate.');
        }

        AuditLog::log('delete', 'Project deleted', $project);
        $project->delete();

        return redirect()->back()->with('success', 'Project deleted.');
    }
}
