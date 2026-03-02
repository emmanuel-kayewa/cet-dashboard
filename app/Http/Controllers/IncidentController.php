<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncidentRequest;
use App\Models\AuditLog;
use App\Models\Directorate;
use App\Models\Incident;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IncidentController extends Controller
{
    public function index(Request $request)
    {
        $query = Incident::with(['directorate', 'enteredBy']);

        $user = $request->user();
        if ($user && $user->isDirectorateHead() && $user->directorate_id) {
            $query->where('directorate_id', $user->directorate_id);
        } else {
            $query->when($request->directorate_id, fn($q, $id) => $q->where('directorate_id', $id));
        }

        $incidents = $query
            ->when($request->severity, fn($q, $s) => $q->where('severity', $s))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->orderByDesc('occurred_at')
            ->paginate(25);

        $directorates = Directorate::active()->ordered()->get();
        if ($user && $user->isDirectorateHead() && $user->directorate_id) {
            $directorates = $directorates->where('id', $user->directorate_id)->values();
        }

        return Inertia::render('DataEntry/IncidentEntry', [
            'incidents' => $incidents,
            'directorates' => $directorates,
            'types' => ['outage', 'safety', 'security', 'environmental', 'equipment_failure', 'operational', 'other'],
            'severities' => ['critical', 'high', 'medium', 'low'],
        ]);
    }

    public function store(IncidentRequest $request)
    {
        $incident = Incident::create([
            ...$request->validated(),
            'source' => 'manual',
            'entered_by' => auth()->id(),
        ]);

        AuditLog::log('create', 'Incident created', $incident);

        return redirect()->back()->with('success', 'Incident recorded successfully.');
    }

    public function update(IncidentRequest $request, Incident $incident)
    {
        $user = $request->user();
        if ($user->isDirectorateHead() && (int) $incident->directorate_id !== (int) $user->directorate_id) {
            abort(403, 'You can only edit incidents for your own directorate.');
        }

        $old = $incident->toArray();
        $incident->update($request->validated());

        AuditLog::log('update', 'Incident updated', $incident, $old, $incident->fresh()->toArray());

        return redirect()->back()->with('success', 'Incident updated.');
    }

    public function destroy(Incident $incident)
    {
        $user = auth()->user();
        if ($user->isDirectorateHead() && (int) $incident->directorate_id !== (int) $user->directorate_id) {
            abort(403, 'You can only delete incidents for your own directorate.');
        }

        AuditLog::log('delete', 'Incident deleted', $incident);
        $incident->delete();

        return redirect()->back()->with('success', 'Incident deleted.');
    }
}
