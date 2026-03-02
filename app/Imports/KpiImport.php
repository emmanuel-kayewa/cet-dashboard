<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class KpiImport implements ToArray, WithHeadingRow, WithCalculatedFormulas
{
    private array $rows = [];
    private array $headers = [];

    public function array(array $rows): void
    {
        $this->rows = $rows;

        // Extract headers from the first row keys
        if (!empty($rows)) {
            $this->headers = array_keys($rows[0]);
        }
    }

    /**
     * Get the parsed rows.
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * Get detected headers from the file.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Attempt to auto-map file headers to KPI model fields.
     * Returns an array of ['file_column' => 'model_field'] mappings.
     */
    public function autoMapHeaders(): array
    {
        $mappings = [];
        $headerMap = [
            // name
            'name' => 'name',
            'kpi_name' => 'name',
            'kpi name' => 'name',
            'indicator' => 'name',
            'indicator_name' => 'name',
            'kpi' => 'name',
            'title' => 'name',
            'metric' => 'name',

            // code
            'code' => 'code',
            'kpi_code' => 'code',
            'kpi code' => 'code',
            'id' => 'code',
            'kpi_id' => 'code',
            'ref' => 'code',
            'reference' => 'code',

            // description
            'description' => 'description',
            'desc' => 'description',
            'details' => 'description',
            'definition' => 'description',
            'kpi_description' => 'description',

            // category
            'category' => 'category',
            'type' => 'category',
            'kpi_category' => 'category',
            'group' => 'category',
            'pillar' => 'category',
            'perspective' => 'category',

            // unit
            'unit' => 'unit',
            'uom' => 'unit',
            'unit_of_measure' => 'unit',
            'measurement' => 'unit',

            // target
            'target' => 'target_value',
            'target_value' => 'target_value',
            'kpi_target' => 'target_value',
            'goal' => 'target_value',
            'benchmark' => 'target_value',
            'annual_target' => 'target_value',
            'yearly_target' => 'target_value',
            'annual target' => 'target_value',

            // warning_threshold
            'warning' => 'warning_threshold',
            'warning_threshold' => 'warning_threshold',
            'amber' => 'warning_threshold',
            'amber_threshold' => 'warning_threshold',
            'yellow_threshold' => 'warning_threshold',

            // critical_threshold
            'critical' => 'critical_threshold',
            'critical_threshold' => 'critical_threshold',
            'red' => 'critical_threshold',
            'red_threshold' => 'critical_threshold',
            'minimum' => 'critical_threshold',

            // deadline
            'deadline' => 'target_deadline',
            'target_deadline' => 'target_deadline',
            'due_date' => 'target_deadline',
            'target_date' => 'target_deadline',
            'completion_date' => 'target_deadline',

            // trend direction
            'trend' => 'trend_direction',
            'trend_direction' => 'trend_direction',
            'direction' => 'trend_direction',
            'polarity' => 'trend_direction',

            // directorate
            'directorate' => 'directorate',
            'department' => 'directorate',
            'division' => 'directorate',
            'business_unit' => 'directorate',
            'owner' => 'directorate',
            'responsible' => 'directorate',

            // current value
            'current_value' => 'current_value',
            'actual' => 'current_value',
            'actual_value' => 'current_value',
            'value' => 'current_value',
            'current' => 'current_value',
            'ytd' => 'current_value',
            'ytd_actual' => 'current_value',

            // weight
            'weight' => 'weight',
            'weighting' => 'weight',
        ];

        foreach ($this->headers as $header) {
            $normalised = strtolower(trim(str_replace(['-', '/', '\\'], '_', $header)));
            if (isset($headerMap[$normalised])) {
                $mappings[$header] = $headerMap[$normalised];
            }
        }

        return $mappings;
    }

    /**
     * Transform parsed rows into a standardised format using the given column mapping.
     *
     * @param  array  $columnMap  ['file_header' => 'model_field']
     * @return array  Standardised KPI rows
     */
    public function mapToKpiData(array $columnMap): array
    {
        $reverseMap = array_flip($columnMap); // model_field => file_header
        $result = [];

        foreach ($this->rows as $row) {
            $mapped = [];

            foreach ($reverseMap as $modelField => $fileHeader) {
                $mapped[$modelField] = $row[$fileHeader] ?? null;
            }

            // Clean up: if the row has no name, skip it
            if (empty($mapped['name'])) {
                continue;
            }

            // Normalise unit values
            if (isset($mapped['unit'])) {
                $mapped['unit'] = $this->normaliseUnit($mapped['unit']);
            }

            // Normalise trend direction
            if (isset($mapped['trend_direction'])) {
                $mapped['trend_direction'] = $this->normaliseTrendDirection($mapped['trend_direction']);
            }

            $result[] = $mapped;
        }

        return $result;
    }

    private function normaliseUnit(?string $unit): string
    {
        if (!$unit) return 'number';

        $unit = strtolower(trim($unit));
        return match (true) {
            str_contains($unit, '%') || str_contains($unit, 'percent') => 'percentage',
            str_contains($unit, 'zmw') || str_contains($unit, 'usd') || str_contains($unit, 'currency') || str_contains($unit, 'kwacha') => 'currency',
            str_contains($unit, 'ratio') => 'ratio',
            default => 'number',
        };
    }

    private function normaliseTrendDirection(?string $direction): string
    {
        if (!$direction) return 'up_is_good';

        $direction = strtolower(trim($direction));
        return match (true) {
            str_contains($direction, 'up') || str_contains($direction, 'higher') || str_contains($direction, 'increase') || str_contains($direction, 'maximize') => 'up_is_good',
            str_contains($direction, 'down') || str_contains($direction, 'lower') || str_contains($direction, 'decrease') || str_contains($direction, 'minimize') || str_contains($direction, 'reduce') => 'down_is_good',
            default => 'neutral',
        };
    }
}
