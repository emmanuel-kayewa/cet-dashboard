<?php

namespace App\Http\Controllers;

use App\Http\Requests\PpFinancialRequest;
use App\Models\AuditLog;
use App\Models\Directorate;
use App\Models\PpFinancial;
use App\Models\PpProject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PpFinancialController extends Controller
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
            abort(403, 'You do not have permission to manage PP financials.');
        }
    }

    public function index(Request $request)
    {
        $this->enforcePpAccess($request);

        $query = PpFinancial::with(['project', 'enteredBy'])
            ->orderByDesc('as_of_date')
            ->orderBy('finance_code');

        if ($request->filled('currency')) {
            $query->where('currency', $request->input('currency'));
        }

        $financials = $query->paginate(30)->withQueryString();
        $projects = PpProject::orderBy('project_code')->get(['id', 'project_code', 'project_name']);

        return Inertia::render('Pp/Index', [
            'activeTab'  => 'financials',
            'financials' => $financials,
            'ppProjects' => $projects,
            'filters'    => $request->only(['currency']),
        ]);
    }

    public function store(PpFinancialRequest $request)
    {
        $this->enforcePpAccess($request);

        $financial = PpFinancial::create([
            ...$request->validated(),
            'entered_by' => auth()->id(),
        ]);

        AuditLog::log('create', 'PP financial created', $financial);

        return redirect()->back()->with('success', 'Financial record created successfully.');
    }

    public function update(PpFinancialRequest $request, PpFinancial $ppFinancial)
    {
        $this->enforcePpAccess($request);

        $old = $ppFinancial->toArray();
        $ppFinancial->update($request->validated());

        AuditLog::log('update', 'PP financial updated', $ppFinancial, $old, $ppFinancial->fresh()->toArray());

        return redirect()->back()->with('success', 'Financial record updated successfully.');
    }

    public function destroy(Request $request, PpFinancial $ppFinancial)
    {
        $this->enforcePpAccess($request);

        AuditLog::log('delete', 'PP financial deleted', $ppFinancial);
        $ppFinancial->delete();

        return redirect()->back()->with('success', 'Financial record deleted successfully.');
    }
}
