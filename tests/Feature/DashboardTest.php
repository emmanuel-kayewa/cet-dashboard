<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Directorate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function createAuthenticatedUser(string $roleName = 'executive'): User
    {
        $role = Role::where('name', $roleName)->first();
        return User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_view_directorate_detail(): void
    {
        $user = $this->createAuthenticatedUser();
        $directorate = Directorate::first();

        $response = $this->actingAs($user)->get("/dashboard/directorate/{$directorate->slug}");
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_view_comparison(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user)->get('/dashboard/comparison');
        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_admin(): void
    {
        $user = $this->createAuthenticatedUser('executive');

        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_panel(): void
    {
        $user = $this->createAuthenticatedUser('admin');

        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(200);
    }

    public function test_directorate_head_can_access_data_entry(): void
    {
        $user = $this->createAuthenticatedUser('directorate_head');

        $response = $this->actingAs($user)->get('/data-entry/kpi-entries');
        $response->assertStatus(200);
    }

    public function test_executive_cannot_access_data_entry(): void
    {
        $user = $this->createAuthenticatedUser('executive');

        $response = $this->actingAs($user)->get('/data-entry/kpi-entries');
        $response->assertStatus(403);
    }
}
