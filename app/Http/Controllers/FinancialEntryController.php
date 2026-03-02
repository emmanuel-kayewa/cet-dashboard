<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinancialEntryRequest;
use App\Models\AuditLog;
use App\Models\Directorate;
use App\Models\FinancialEntry;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FinancialEntryController extends Controller
{
    public function index(Request $request)
    {
        $query = FinancialEntry::with(['directorate', 'enteredBy'])
            ->where('source', 'manual');

        $user = $request->user();
        if ($user && $user->isDirectorateHead() && $user->directorate_id) {
            $query->where('directorate_id', $user->directorate_id);
        } elseif ($request->directorate_id) {
            $query->where('directorate_id', $request->directorate_id);
        }

        $entries = $query->orderByDesc('period_date')->paginate(25);

        $directorates = Directorate::active()->ordered()->get();
        if ($user && $user->isDirectorateHead() && $user->directorate_id) {
            $directorates = $directorates->where('id', $user->directorate_id)->values();
        }

        return Inertia::render('DataEntry/FinancialEntry', [
            'entries' => $entries,
            'directorates' => $directorates,
            'categories' => ['revenue', 'expense', 'budget', 'capex', 'opex'],
        ]);
    }

    public function store(FinancialEntryRequest $request)
    {
        $entry = FinancialEntry::create([
            ...$request->validated(),
            'source' => 'manual',
            'entered_by' => auth()->id(),
        ]);

        AuditLog::log('create', 'Financial entry created', $entry);

        return redirect()->back()->with('success', 'Financial entry saved successfully.');
    }

    public function update(FinancialEntryRequest $request, FinancialEntry $financialEntry)
    {
        $user = $request->user();
        if ($user->isDirectorateHead() && (int) $financialEntry->directorate_id !== (int) $user->directorate_id) {
            abort(403, 'You can only edit entries for your own directorate.');
        }

        $old = $financialEntry->toArray();
        $financialEntry->update($request->validated());

        AuditLog::log('update', 'Financial entry updated', $financialEntry, $old, $financialEntry->fresh()->toArray());

        return redirect()->back()->with('success', 'Financial entry updated.');
    }

    public function destroy(FinancialEntry $financialEntry)
    {
        $user = auth()->user();
        if ($user->isDirectorateHead() && (int) $financialEntry->directorate_id !== (int) $user->directorate_id) {
            abort(403, 'You can only delete entries for your own directorate.');
        }

        AuditLog::log('delete', 'Financial entry deleted', $financialEntry);
        $financialEntry->delete();

        return redirect()->back()->with('success', 'Financial entry deleted.');
    }
}
