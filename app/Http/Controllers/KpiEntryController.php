<?php

namespace App\Http\Controllers;

use App\Http\Requests\KpiEntryRequest;
use App\Models\AuditLog;
use App\Models\Directorate;
use App\Models\Kpi;
use App\Models\KpiEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class KpiEntryController extends Controller
{
    public function index(Request $request)
    {
        $sources = ['manual'];
        if (config('dashboard.data_source') === 'simulation') {
            $sources[] = 'simulation';
        }

        $query = KpiEntry::with(['kpi', 'directorate', 'enteredBy'])
            ->whereIn('source', $sources);

        // Directorate heads only see their own directorate's data
        $user = $request->user();
        if ($user && $user->isDirectorateHead() && $user->directorate_id) {
            $query->where('directorate_id', $user->directorate_id);
        } elseif ($request->directorate_id) {
            $query->where('directorate_id', $request->directorate_id);
        }

        $entries = $query->orderByDesc('period_date')->paginate(25);

        // Filter available directorates for directorate heads
        $directorates = Directorate::active()->ordered()->get();
        if ($user && $user->isDirectorateHead() && $user->directorate_id) {
            $directorates = $directorates->where('id', $user->directorate_id)->values();
        }

        return Inertia::render('DataEntry/KpiEntry', [
            'entries' => $entries,
            'kpis' => Kpi::active()->orderBy('name')->get(),
            'directorates' => $directorates,
        ]);
    }

    public function store(KpiEntryRequest $request)
    {
        // Ensure this KPI is linked to the directorate so it appears on the Directorate Detail page.
        $directorate = Directorate::find($request->directorate_id);
        if ($directorate) {
            $directorate->kpis()->syncWithoutDetaching([(int) $request->kpi_id]);
        }

        $previous = KpiEntry::where('kpi_id', $request->kpi_id)
            ->where('directorate_id', $request->directorate_id)
            ->where('source', 'manual')
            ->orderByDesc('period_date')
            ->first();

        $entry = KpiEntry::create([
            ...$request->validated(),
            'previous_value' => $previous?->value,
            'source' => 'manual',
            'entered_by' => Auth::id(),
        ]);

        AuditLog::log('create', 'KPI entry created', $entry);

        return redirect()->back()->with('success', 'KPI entry saved successfully.');
    }

    public function update(KpiEntryRequest $request, KpiEntry $kpiEntry)
    {
        if ($kpiEntry->source !== 'manual') {
            abort(403, 'Only manually entered KPI entries can be edited.');
        }

        // Ownership check: directorate heads can only edit entries in their directorate
        $user = $request->user();
        if ($user->isDirectorateHead() && (int) $kpiEntry->directorate_id !== (int) $user->directorate_id) {
            abort(403, 'You can only edit entries for your own directorate.');
        }

        $old = $kpiEntry->toArray();
        $kpiEntry->update($request->validated());

        AuditLog::log('update', 'KPI entry updated', $kpiEntry, $old, $kpiEntry->fresh()->toArray());

        return redirect()->back()->with('success', 'KPI entry updated.');
    }

    public function destroy(KpiEntry $kpiEntry)
    {
        if ($kpiEntry->source !== 'manual') {
            abort(403, 'Only manually entered KPI entries can be deleted.');
        }

        // Ownership check
        $user = Auth::user();
        if ($user->isDirectorateHead() && (int) $kpiEntry->directorate_id !== (int) $user->directorate_id) {
            abort(403, 'You can only delete entries for your own directorate.');
        }

        AuditLog::log('delete', 'KPI entry deleted', $kpiEntry);
        $kpiEntry->delete();

        return redirect()->back()->with('success', 'KPI entry deleted.');
    }
}
