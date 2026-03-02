<?php

namespace App\Http\Controllers;

use App\Http\Requests\RiskRequest;
use App\Models\AuditLog;
use App\Models\Directorate;
use App\Models\Risk;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RiskController extends Controller
{
    public function index(Request $request)
    {
        $query = Risk::with(['directorate', 'enteredBy']);

        $user = $request->user();
        if ($user && $user->isDirectorateHead() && $user->directorate_id) {
            $query->where('directorate_id', $user->directorate_id);
        } else {
            $query->when($request->directorate_id, fn($q, $id) => $q->where('directorate_id', $id));
        }

        $risks = $query->when($request->category, fn($q, $c) => $q->where('category', $c))
            ->orderByDesc('updated_at')
            ->paginate(25);

        $directorates = Directorate::active()->ordered()->get();
        if ($user && $user->isDirectorateHead() && $user->directorate_id) {
            $directorates = $directorates->where('id', $user->directorate_id)->values();
        }

        return Inertia::render('DataEntry/RiskEntry', [
            'risks' => $risks,
            'directorates' => $directorates,
            'categories' => ['operational', 'financial', 'strategic', 'compliance', 'technical', 'environmental', 'reputational'],
        ]);
    }

    public function store(RiskRequest $request)
    {
        $risk = Risk::create([
            ...$request->validated(),
            'source' => 'manual',
            'entered_by' => auth()->id(),
        ]);

        AuditLog::log('create', 'Risk created', $risk);

        return redirect()->back()->with('success', 'Risk saved successfully.');
    }

    public function update(RiskRequest $request, Risk $risk)
    {
        $user = $request->user();
        if ($user->isDirectorateHead() && (int) $risk->directorate_id !== (int) $user->directorate_id) {
            abort(403, 'You can only edit risks for your own directorate.');
        }

        $old = $risk->toArray();
        $risk->update($request->validated());

        AuditLog::log('update', 'Risk updated', $risk, $old, $risk->fresh()->toArray());

        return redirect()->back()->with('success', 'Risk updated.');
    }

    public function destroy(Risk $risk)
    {
        $user = auth()->user();
        if ($user->isDirectorateHead() && (int) $risk->directorate_id !== (int) $user->directorate_id) {
            abort(403, 'You can only delete risks for your own directorate.');
        }

        AuditLog::log('delete', 'Risk deleted', $risk);
        $risk->delete();

        return redirect()->back()->with('success', 'Risk deleted.');
    }
}
