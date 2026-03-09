<?php

namespace App\Http\Controllers;

use App\Http\Requests\PpSafeguardRequest;
use App\Models\AuditLog;
use App\Models\Directorate;
use App\Models\PpSafeguard;
use App\Models\PpProject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PpSafeguardController extends Controller
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
            abort(403, 'You do not have permission to manage PP safeguards.');
        }
    }

    public function index(Request $request)
    {
        $this->enforcePpAccess($request);

        $safeguards = PpSafeguard::with(['project', 'enteredBy'])
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        $projects = PpProject::orderBy('project_code')->get(['id', 'project_code', 'project_name']);

        return Inertia::render('Pp/Index', [
            'activeTab'  => 'safeguards',
            'safeguards' => $safeguards,
            'ppProjects' => $projects,
        ]);
    }

    public function store(PpSafeguardRequest $request)
    {
        $this->enforcePpAccess($request);

        $safeguard = PpSafeguard::create([
            ...$request->validated(),
            'entered_by' => auth()->id(),
        ]);

        AuditLog::log('create', 'PP safeguard created', $safeguard);

        return redirect()->back()->with('success', 'Safeguard record created successfully.');
    }

    public function update(PpSafeguardRequest $request, PpSafeguard $ppSafeguard)
    {
        $this->enforcePpAccess($request);

        $old = $ppSafeguard->toArray();
        $ppSafeguard->update($request->validated());

        AuditLog::log('update', 'PP safeguard updated', $ppSafeguard, $old, $ppSafeguard->fresh()->toArray());

        return redirect()->back()->with('success', 'Safeguard record updated successfully.');
    }

    public function destroy(Request $request, PpSafeguard $ppSafeguard)
    {
        $this->enforcePpAccess($request);

        AuditLog::log('delete', 'PP safeguard deleted', $ppSafeguard);
        $ppSafeguard->delete();

        return redirect()->back()->with('success', 'Safeguard record deleted successfully.');
    }
}
