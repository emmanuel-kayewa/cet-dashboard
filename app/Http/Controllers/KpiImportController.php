<?php

namespace App\Http\Controllers;

use App\Imports\KpiImport;
use App\Models\AuditLog;
use App\Models\Directorate;
use App\Models\Kpi;
use App\Services\AiAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class KpiImportController extends Controller
{
    /**
     * Show the KPI upload form.
     */
    public function showUploadForm()
    {
        return Inertia::render('Admin/KpiImport', [
            'directorates' => Directorate::active()->ordered()->get(),
            'categories' => config('dashboard.kpi_categories', []),
            'maxFileSize' => config('dashboard.import.max_file_size', 10240),
        ]);
    }

    /**
     * Parse an uploaded file and return a preview.
     */
    public function parseFile(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:' . config('dashboard.import.max_file_size', 10240),
                'mimes:xlsx,csv,xls',
            ],
        ]);

        $import = new KpiImport();
        Excel::import($import, $request->file('file'));

        $rows = $import->getRows();
        $headers = $import->getHeaders();
        $autoMap = $import->autoMapHeaders();

        // Map rows using auto-detected columns
        $mappedData = [];
        if (!empty($autoMap)) {
            $mappedData = $import->mapToKpiData($autoMap);
        }

        // Store the parsed data in session for the confirm step
        session(['kpi_import_data' => $rows]);
        session(['kpi_import_headers' => $headers]);
        session(['kpi_import_auto_map' => $autoMap]);

        return response()->json([
            'success' => true,
            'headers' => $headers,
            'auto_mapping' => $autoMap,
            'preview' => array_slice($mappedData ?: $rows, 0, 50), // Preview first 50 rows
            'total_rows' => count($rows),
            'mapped_fields' => array_values($autoMap),
            'available_fields' => [
                'name', 'code', 'description', 'category', 'unit',
                'target_value', 'warning_threshold', 'critical_threshold',
                'target_deadline', 'trend_direction', 'directorate',
                'current_value', 'weight',
            ],
        ]);
    }

    /**
     * Use AI to auto-categorize and enrich parsed KPI data.
     */
    public function aiEnrich(Request $request, AiAnalysisService $aiService)
    {
        $request->validate([
            'kpis' => 'required|array',
            'kpis.*.name' => 'required|string',
        ]);

        if (!$aiService->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'AI service is not available. Please check Ollama is running.',
            ], 503);
        }

        $rawKpis = collect($request->kpis)->map(fn($k) => [
            'name' => $k['name'] ?? '',
            'description' => $k['description'] ?? '',
            'category' => $k['category'] ?? '',
            'unit' => $k['unit'] ?? '',
        ])->toArray();

        $enriched = $aiService->categorizeKpis($rawKpis);

        return response()->json([
            'success' => true,
            'enriched_kpis' => $enriched,
        ]);
    }

    /**
     * Confirm and import KPIs into the database.
     */
    public function confirmImport(Request $request)
    {
        $request->validate([
            'kpis' => 'required|array|min:1',
            'kpis.*.name' => 'required|string|max:255',
            'kpis.*.category' => 'required|string',
            'kpis.*.unit' => 'nullable|string',
            'kpis.*.target_value' => 'nullable|numeric',
            'kpis.*.warning_threshold' => 'nullable|numeric',
            'kpis.*.critical_threshold' => 'nullable|numeric',
            'kpis.*.trend_direction' => 'nullable|string|in:up_is_good,down_is_good,neutral',
            'kpis.*.target_deadline' => 'nullable|date',
            'kpis.*.directorate_ids' => 'nullable|array',
            'kpis.*.directorate_ids.*' => 'exists:directorates,id',
            'kpis.*.current_value' => 'nullable|numeric',
        ]);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($request->kpis as $index => $kpiData) {
            try {
                $slug = Str::slug($kpiData['name']);

                // Skip duplicates by slug
                if (Kpi::where('slug', $slug)->exists()) {
                    $skipped++;
                    $errors[] = "Row {$index}: '{$kpiData['name']}' already exists — skipped.";
                    continue;
                }

                $kpi = Kpi::create([
                    'name' => $kpiData['name'],
                    'slug' => $slug,
                    'code' => $kpiData['code'] ?? strtoupper(Str::limit(Str::slug($kpiData['name'], '_'), 20, '')),
                    'description' => $kpiData['description'] ?? null,
                    'category' => $kpiData['category'] ?? 'operational',
                    'unit' => $kpiData['unit'] ?? 'number',
                    'currency_code' => $kpiData['currency_code'] ?? null,
                    'target_value' => $kpiData['target_value'] ?? null,
                    'warning_threshold' => $kpiData['warning_threshold'] ?? null,
                    'critical_threshold' => $kpiData['critical_threshold'] ?? null,
                    'target_deadline' => $kpiData['target_deadline'] ?? null,
                    'trend_direction' => $kpiData['trend_direction'] ?? 'up_is_good',
                    'is_global' => !empty($kpiData['is_global']),
                    'is_active' => true,
                    'sort_order' => $imported,
                ]);

                // Attach to directorates if specified
                if (!empty($kpiData['directorate_ids'])) {
                    $pivotData = [];
                    foreach ($kpiData['directorate_ids'] as $dirId) {
                        $pivotData[$dirId] = ['custom_target' => $kpiData['target_value'] ?? null];
                    }
                    $kpi->directorates()->attach($pivotData);
                }

                // If current value is provided, create an initial entry for each directorate
                if (isset($kpiData['current_value']) && !empty($kpiData['directorate_ids'])) {
                    foreach ($kpiData['directorate_ids'] as $dirId) {
                        $kpi->entries()->create([
                            'directorate_id' => $dirId,
                            'value' => $kpiData['current_value'],
                            'period_date' => now()->startOfMonth(),
                            'period_type' => 'monthly',
                            'source' => 'manual',
                            'entered_by' => auth()->id(),
                            'notes' => 'Imported via KPI upload',
                        ]);
                    }
                }

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$index}: Error importing '{$kpiData['name']}' — {$e->getMessage()}";
            }
        }

        AuditLog::log('create', "{$imported} KPIs imported via file upload" . ($skipped ? ", {$skipped} skipped" : ''));

        // Clear session data
        session()->forget(['kpi_import_data', 'kpi_import_headers', 'kpi_import_auto_map']);

        return response()->json([
            'success' => true,
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
            'message' => "{$imported} KPIs imported successfully." . ($skipped ? " {$skipped} duplicates skipped." : ''),
        ]);
    }

    /**
     * Download a sample KPI import template.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Name', 'Code', 'Description', 'Category', 'Unit',
            'Target Value', 'Warning Threshold', 'Critical Threshold',
            'Trend Direction', 'Target Deadline', 'Directorate', 'Current Value',
        ];

        $sample = [
            ['Revenue Collection Rate', 'RCR-001', 'Percentage of billed revenue collected',
             'financial', 'percentage', 95, 85, 75, 'up_is_good', '2026-12-31', 'Finance', 88.5],
            ['System Average Interruption Duration', 'SAIDI-001', 'Average outage duration per customer',
             'technical', 'number', 50, 70, 100, 'down_is_good', '2026-12-31', 'Transmission', 65],
            ['Customer Satisfaction Index', 'CSI-001', 'Customer satisfaction survey score',
             'customer', 'percentage', 90, 75, 60, 'up_is_good', '2026-12-31', '', 78],
        ];

        $callback = function () use ($headers, $sample) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($sample as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="kpi-import-template.csv"',
        ]);
    }
}
