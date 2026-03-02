<?php

namespace App\Exports;

use App\Models\Directorate;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DirectorateDetailExport implements WithMultipleSheets
{
    public function __construct(
        private Directorate $directorate,
        private array $detail
    ) {}

    public function sheets(): array
    {
        return [
            new DirectorateKpisSheet($this->directorate, $this->detail['kpis'] ?? []),
            new DirectorateFinancialsSheet($this->directorate, $this->detail['financials'] ?? []),
            new DirectorateProjectsSheet($this->directorate, $this->detail['projects'] ?? []),
            new DirectorateRisksSheet($this->directorate, $this->detail['risks'] ?? []),
        ];
    }
}

class DirectorateKpisSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    public function __construct(private Directorate $directorate, private array $kpis) {}

    public function title(): string { return 'KPIs'; }

    public function headings(): array
    {
        return ['KPI', 'Value', 'Target', 'Achievement (%)', 'Trend', 'Change (%)'];
    }

    public function array(): array
    {
        return array_map(fn($k) => [
            $k['kpi_name'] ?? '',
            $k['value'] ?? 0,
            $k['target'] ?? 0,
            isset($k['target']) && $k['target'] > 0 ? round(($k['value'] ?? 0) / $k['target'] * 100, 1) . '%' : 'N/A',
            $k['trend'] ?? '',
            ($k['change_percentage'] ?? 0) . '%',
        ], $this->kpis);
    }

    public function styles(Worksheet $sheet): array { return [1 => ['font' => ['bold' => true]]]; }
}

class DirectorateFinancialsSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    public function __construct(private Directorate $directorate, private array $financials) {}

    public function title(): string { return 'Financials'; }

    public function headings(): array
    {
        return ['Category', 'Amount (ZMW)', 'Budget (ZMW)', 'Variance (ZMW)'];
    }

    public function array(): array
    {
        return array_map(fn($f) => [
            ucfirst($f['category'] ?? ''),
            number_format($f['amount'] ?? 0, 2),
            number_format($f['budget'] ?? 0, 2),
            number_format(($f['amount'] ?? 0) - ($f['budget'] ?? 0), 2),
        ], $this->financials);
    }

    public function styles(Worksheet $sheet): array { return [1 => ['font' => ['bold' => true]]]; }
}

class DirectorateProjectsSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    public function __construct(private Directorate $directorate, private array $projects) {}

    public function title(): string { return 'Projects'; }

    public function headings(): array
    {
        return ['Project', 'Status', 'Completion (%)', 'Budget (ZMW)', 'Start Date', 'End Date'];
    }

    public function array(): array
    {
        return array_map(fn($p) => [
            $p['name'] ?? $p['title'] ?? '',
            ucfirst($p['status'] ?? ''),
            round($p['completion_percentage'] ?? 0, 1) . '%',
            number_format($p['budget'] ?? 0, 2),
            $p['start_date'] ?? '',
            $p['end_date'] ?? '',
        ], $this->projects);
    }

    public function styles(Worksheet $sheet): array { return [1 => ['font' => ['bold' => true]]]; }
}

class DirectorateRisksSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    public function __construct(private Directorate $directorate, private array $risks) {}

    public function title(): string { return 'Risks'; }

    public function headings(): array
    {
        return ['Risk', 'Category', 'Likelihood', 'Impact', 'Score', 'Status', 'Owner'];
    }

    public function array(): array
    {
        return array_map(fn($r) => [
            $r['title'] ?? '',
            ucfirst($r['category'] ?? ''),
            $r['likelihood'] ?? 0,
            $r['impact'] ?? 0,
            ($r['likelihood'] ?? 0) * ($r['impact'] ?? 0),
            ucfirst($r['status'] ?? ''),
            $r['owner'] ?? '',
        ], $this->risks);
    }

    public function styles(Worksheet $sheet): array { return [1 => ['font' => ['bold' => true]]]; }
}
