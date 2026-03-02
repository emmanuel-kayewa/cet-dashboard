<?php

namespace App\Services\DataSources;

use Carbon\Carbon;

interface DataSourceInterface
{
    /**
     * Get the identifier for this data source.
     */
    public function getIdentifier(): string;

    /**
     * Get KPI data for a directorate.
     */
    public function getKpiData(int $directorateId, ?Carbon $fromDate = null, ?Carbon $toDate = null): array;

    /**
     * Get financial data for a directorate.
     */
    public function getFinancialData(int $directorateId, ?Carbon $fromDate = null, ?Carbon $toDate = null): array;

    /**
     * Get project data for a directorate.
     */
    public function getProjectData(int $directorateId): array;

    /**
     * Get risk data for a directorate.
     */
    public function getRiskData(int $directorateId): array;

    /**
     * Get organization-wide executive summary.
     */
    public function getExecutiveSummary(?Carbon $fromDate = null, ?Carbon $toDate = null): array;

    /**
     * Get trending data for a specific KPI.
     */
    public function getKpiTrend(int $kpiId, int $directorateId, int $periods = 12): array;

    /**
     * Get comparison data across directorates.
     */
    public function getDirectorateComparison(array $directorateIds, array $kpiIds): array;

    /**
     * Check if the data source is available and operational.
     */
    public function isAvailable(): bool;
}
