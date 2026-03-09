<?php

namespace App\Http\Controllers;

use App\Http\Requests\PpProgrammeOutputRequest;
use App\Models\AuditLog;
use App\Models\Directorate;
use App\Models\PpProgrammeOutput;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PpProgrammeOutputController extends Controller
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
            abort(403, 'You do not have permission to manage PP programme outputs.');
        }
    }

    public function index(Request $request)
    {
        $this->enforcePpAccess($request);

        $outputs = PpProgrammeOutput::with('enteredBy')
            ->orderBy('programme')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('Pp/Index', [
            'activeTab'        => 'programme-outputs',
            'programmeOutputs' => $outputs,
        ]);
    }

    public function store(PpProgrammeOutputRequest $request)
    {
        $this->enforcePpAccess($request);

        $output = PpProgrammeOutput::create([
            ...$request->validated(),
            'entered_by' => auth()->id(),
        ]);

        AuditLog::log('create', 'PP programme output created', $output);

        return redirect()->back()->with('success', 'Programme output created successfully.');
    }

    public function update(PpProgrammeOutputRequest $request, PpProgrammeOutput $ppProgrammeOutput)
    {
        $this->enforcePpAccess($request);

        $old = $ppProgrammeOutput->toArray();
        $ppProgrammeOutput->update($request->validated());

        AuditLog::log('update', 'PP programme output updated', $ppProgrammeOutput, $old, $ppProgrammeOutput->fresh()->toArray());

        return redirect()->back()->with('success', 'Programme output updated successfully.');
    }

    public function destroy(Request $request, PpProgrammeOutput $ppProgrammeOutput)
    {
        $this->enforcePpAccess($request);

        AuditLog::log('delete', 'PP programme output deleted', $ppProgrammeOutput);
        $ppProgrammeOutput->delete();

        return redirect()->back()->with('success', 'Programme output deleted successfully.');
    }
}
