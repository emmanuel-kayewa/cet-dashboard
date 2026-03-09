<?php

namespace App\Http\Controllers;

use App\Models\Directorate;
use App\Models\PpProject;
use App\Services\PpDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PpDashboardController extends Controller
{
    public function __construct(
        private PpDashboardService $service
    ) {}

    /**
     * Enforce PP directorate access. Returns the PP directorate model.
     */
    private function enforcePpAccess(Request $request): Directorate
    {
        $user  = $request->user();
        $ppDir = Directorate::where('code', 'PP')->firstOrFail();

        if ($user && !$user->isAdmin() && (!$user->isDirectorateHead() || $user->directorate_id !== $ppDir->id)) {
            abort(403, 'You do not have permission to view this page.');
        }

        return $ppDir;
    }

    /**
     * Level 1 — Portfolio Overview.
     */
    public function overview(Request $request)
    {
        $ppDir = $this->enforcePpAccess($request);

        $data = $this->service->getOverview();

        return Inertia::render('Pp/Dashboard/Overview', [
            'ppData'       => $data,
            'directorate'  => $ppDir,
            'directorates' => Directorate::active()->ordered()->get(),
        ]);
    }

    /**
     * Level 2 — Explorer (adaptive drill-down).
     */
    public function explore(Request $request)
    {
        $ppDir = $this->enforcePpAccess($request);

        // Collect filter params
        $filters = [];
        foreach (PpDashboardService::DIMENSIONS as $dim) {
            $val = $request->get($dim);
            if ($val) {
                $filters[$dim] = $val;
            }
        }

        $data = $this->service->getExplorerData($filters);

        return Inertia::render('Pp/Dashboard/Explorer', [
            'explorerData' => $data,
            'directorate'  => $ppDir,
            'directorates' => Directorate::active()->ordered()->get(),
            'dimensionLabels' => PpDashboardService::DIMENSION_LABELS,
        ]);
    }

    /**
     * Level 3 — Single Project Detail.
     */
    public function projectDetail(Request $request, PpProject $ppProject)
    {
        $ppDir = $this->enforcePpAccess($request);

        $data = $this->service->getProjectDetail($ppProject);

        // Preserve the explorer filters for back-navigation
        $backFilters = [];
        foreach (PpDashboardService::DIMENSIONS as $dim) {
            $val = $request->get($dim);
            if ($val) {
                $backFilters[$dim] = $val;
            }
        }

        return Inertia::render('Pp/Dashboard/ProjectDetail', [
            'projectData'     => $data,
            'directorate'     => $ppDir,
            'directorates'    => Directorate::active()->ordered()->get(),
            'backFilters'     => $backFilters,
        ]);
    }
}
