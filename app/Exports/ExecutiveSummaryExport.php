<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExecutiveSummaryExport implements WithMultipleSheets
{
    public function __construct(
        private array $summary
    ) {}

    public function sheets(): array
    {
        return [
            new ExecutiveOverviewSheet($this->summary),
            new DirectoratesSheet($this->summary['directorates'] ?? []),
        ];
    }
}

class ExecutiveOverviewSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    public function __construct(private array $summary) {}

    public function title(): string
    {
        return 'Overview';
    }

    public function headings(): array
    {
        return ['Metric', 'Value'];
    }

    public function array(): array
    {
        return [
            ['Total Revenue (ZMW)', number_format($this->summary['total_revenue'] ?? 0, 2)],
            ['Total Budget (ZMW)', number_format($this->summary['total_budget'] ?? 0, 2)],
            ['Budget Utilization (%)', isset($this->summary['total_budget']) && $this->summary['total_budget'] > 0
                ? round(($this->summary['total_revenue'] ?? 0) / $this->summary['total_budget'] * 100, 1) . '%'
                : 'N/A'],
            ['Total Active Projects', $this->summary['total_projects'] ?? 0],
            ['Avg. Project Completion (%)', round($this->summary['avg_completion'] ?? 0, 1) . '%'],
            ['Total Risks', $this->summary['total_risks'] ?? 0],
            ['High Risks', $this->summary['high_risks'] ?? 0],
            ['Avg. Uptime (%)', round($this->summary['avg_uptime'] ?? 0, 2) . '%'],
            ['Data Source', $this->summary['data_source'] ?? 'unknown'],
            ['Generated At', $this->summary['generated_at'] ?? now()->toISOString()],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

class DirectoratesSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    public function __construct(private array $directorates) {}

    public function title(): string
    {
        return 'Directorates';
    }

    public function headings(): array
    {
        return ['Directorate', 'Code', 'Revenue (ZMW)', 'Budget (ZMW)', 'Completion (%)', 'Open Risks', 'Score'];
    }

    public function array(): array
    {
        return array_map(fn($d) => [
            $d['name'] ?? '',
            $d['code'] ?? '',
            number_format($d['revenue'] ?? 0, 2),
            number_format($d['budget'] ?? 0, 2),
            round($d['completion_percentage'] ?? 0, 1) . '%',
            $d['open_risks'] ?? 0,
            round($d['score'] ?? 0, 1),
        ], $this->directorates);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
