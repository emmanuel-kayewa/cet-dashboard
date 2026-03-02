<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Models\Directorate;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExecutiveSummaryExport;
use App\Exports\DirectorateDetailExport;

class ExportController extends Controller
{
    public function __construct(
        private DashboardService $dashboard
    ) {}

    /**
     * Export executive summary as PDF.
     */
    public function executivePdf(Request $request)
    {
        $summary = $this->dashboard->getExecutiveSummary();
        $directorates = Directorate::active()->ordered()->get();

        // Directorate-level scoping
        $user = $request->user();
        if ($user->isDirectorateHead() && $user->directorate_id) {
            $summary['directorates'] = collect($summary['directorates'] ?? [])
                ->filter(fn($d) => ($d['id'] ?? null) == $user->directorate_id)
                ->values()
                ->toArray();
            $directorates = $directorates->where('id', $user->directorate_id)->values();
        }

        $pdf = Pdf::loadView('exports.executive-summary', [
            'summary' => $summary,
            'directorates' => $directorates,
            'generatedAt' => now()->format('d M Y H:i'),
            'generatedBy' => $user->name,
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('executive-summary-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export executive summary as Excel.
     */
    public function executiveExcel(Request $request)
    {
        $summary = $this->dashboard->getExecutiveSummary();

        $user = $request->user();
        if ($user->isDirectorateHead() && $user->directorate_id) {
            $summary['directorates'] = collect($summary['directorates'] ?? [])
                ->filter(fn($d) => ($d['id'] ?? null) == $user->directorate_id)
                ->values()
                ->toArray();
        }

        return Excel::download(
            new ExecutiveSummaryExport($summary),
            'executive-summary-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export directorate detail as PDF.
     */
    public function directoratePdf(Request $request, Directorate $directorate)
    {
        $user = $request->user();
        if (!$user->canViewDirectorate($directorate->id)) {
            abort(403);
        }

        $detail = $this->dashboard->getDirectorateDetail($directorate->id);

        $pdf = Pdf::loadView('exports.directorate-detail', [
            'directorate' => $directorate,
            'detail' => $detail,
            'generatedAt' => now()->format('d M Y H:i'),
            'generatedBy' => $user->name,
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("directorate-{$directorate->slug}-" . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export directorate detail as Excel.
     */
    public function directorateExcel(Request $request, Directorate $directorate)
    {
        $user = $request->user();
        if (!$user->canViewDirectorate($directorate->id)) {
            abort(403);
        }

        $detail = $this->dashboard->getDirectorateDetail($directorate->id);

        return Excel::download(
            new DirectorateDetailExport($directorate, $detail),
            "directorate-{$directorate->slug}-" . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
