<?php

namespace Tests\Unit;

use App\Services\DataSources\SimulationDataSource;
use App\Services\DataSources\DataSourceManager;
use PHPUnit\Framework\TestCase;

class DataSourceTest extends TestCase
{
    public function test_simulation_data_source_is_available(): void
    {
        $source = new SimulationDataSource();
        $this->assertTrue($source->isAvailable());
    }

    public function test_simulation_returns_kpi_data(): void
    {
        $source = new SimulationDataSource();
        $data = $source->getKpiData('gen');

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('name', $data[0]);
        $this->assertArrayHasKey('actual', $data[0]);
        $this->assertArrayHasKey('target', $data[0]);
    }

    public function test_simulation_returns_financial_data(): void
    {
        $source = new SimulationDataSource();
        $data = $source->getFinancialData('f-s');

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('category', $data[0]);
        $this->assertArrayHasKey('budget', $data[0]);
        $this->assertArrayHasKey('actual', $data[0]);
    }

    public function test_simulation_returns_project_data(): void
    {
        $source = new SimulationDataSource();
        $data = $source->getProjectData('p-e');

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('name', $data[0]);
        $this->assertArrayHasKey('status', $data[0]);
        $this->assertArrayHasKey('completion', $data[0]);
    }

    public function test_simulation_returns_risk_data(): void
    {
        $source = new SimulationDataSource();
        $data = $source->getRiskData('gen');

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('title', $data[0]);
        $this->assertArrayHasKey('impact', $data[0]);
        $this->assertArrayHasKey('likelihood', $data[0]);
    }

    public function test_simulation_returns_executive_summary(): void
    {
        $source = new SimulationDataSource();
        $summary = $source->getExecutiveSummary();

        $this->assertIsArray($summary);
        $this->assertArrayHasKey('total_revenue', $summary);
        $this->assertArrayHasKey('avg_completion', $summary);
        $this->assertArrayHasKey('high_risks', $summary);
        $this->assertArrayHasKey('directorates', $summary);
        $this->assertNotEmpty($summary['directorates']);
    }

    public function test_simulation_returns_kpi_trend(): void
    {
        $source = new SimulationDataSource();
        $trend = $source->getKpiTrend('GEN-001', 12);

        $this->assertIsArray($trend);
        $this->assertNotEmpty($trend);
        $this->assertArrayHasKey('date', $trend[0]);
        $this->assertArrayHasKey('value', $trend[0]);
    }

    public function test_simulation_is_deterministic_with_same_seed(): void
    {
        $source = new SimulationDataSource();
        $summary1 = $source->getExecutiveSummary();
        $summary2 = $source->getExecutiveSummary();

        $this->assertEquals($summary1['total_revenue'], $summary2['total_revenue']);
    }
}
