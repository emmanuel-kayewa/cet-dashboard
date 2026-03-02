<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Directorate;
use App\Models\Kpi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataEntryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function createDataEntryUser(): User
    {
        $role = Role::where('name', 'directorate_head')->first();
        $directorate = Directorate::first();
        return User::factory()->create([
            'role_id' => $role->id,
            'directorate_id' => $directorate->id,
            'is_active' => true,
        ]);
    }

    public function test_can_create_kpi_entry(): void
    {
        $user = $this->createDataEntryUser();
        $kpi = Kpi::first();
        $directorate = Directorate::first();

        $response = $this->actingAs($user)->post('/data-entry/kpi-entries', [
            'kpi_id' => $kpi->id,
            'directorate_id' => $directorate->id,
            'actual_value' => 85.5,
            'target_value' => 100,
            'reporting_period' => '2025-06',
            'notes' => 'Test KPI entry',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kpi_entries', [
            'kpi_id' => $kpi->id,
            'actual_value' => 85.5,
        ]);
    }

    public function test_can_create_financial_entry(): void
    {
        $user = $this->createDataEntryUser();
        $directorate = Directorate::first();

        $response = $this->actingAs($user)->post('/data-entry/financial-entries', [
            'directorate_id' => $directorate->id,
            'category' => 'revenue',
            'budget_amount' => 1000000,
            'actual_amount' => 950000,
            'period' => '2025-06',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('financial_entries', [
            'directorate_id' => $directorate->id,
            'category' => 'revenue',
        ]);
    }

    public function test_can_create_project(): void
    {
        $user = $this->createDataEntryUser();
        $directorate = Directorate::first();

        $response = $this->actingAs($user)->post('/data-entry/projects', [
            'directorate_id' => $directorate->id,
            'name' => 'Kariba Dam Rehabilitation',
            'description' => 'Phase 2 of dam rehabilitation project',
            'status' => 'in_progress',
            'completion_percentage' => 45,
            'budget' => 5000000,
            'actual_cost' => 2200000,
            'start_date' => '2025-01-01',
            'end_date' => '2026-12-31',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', [
            'name' => 'Kariba Dam Rehabilitation',
        ]);
    }

    public function test_can_create_risk(): void
    {
        $user = $this->createDataEntryUser();
        $directorate = Directorate::first();

        $response = $this->actingAs($user)->post('/data-entry/risks', [
            'directorate_id' => $directorate->id,
            'title' => 'Load shedding escalation',
            'description' => 'Increasing frequency of load shedding due to low water levels',
            'category' => 'operational',
            'impact' => 5,
            'likelihood' => 4,
            'mitigation' => 'Diversify energy sources, negotiate power imports',
            'status' => 'mitigating',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('risks', [
            'title' => 'Load shedding escalation',
            'impact' => 5,
        ]);
    }

    public function test_kpi_entry_requires_valid_data(): void
    {
        $user = $this->createDataEntryUser();

        $response = $this->actingAs($user)->post('/data-entry/kpi-entries', [
            'kpi_id' => '',
            'actual_value' => '',
        ]);

        $response->assertSessionHasErrors(['kpi_id', 'actual_value']);
    }
}
