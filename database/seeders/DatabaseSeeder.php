<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Directorate;
use App\Models\Kpi;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRoles();
        $this->seedDirectorates();
        $this->seedKpis();
        $this->seedSettings();
        $this->seedDefaultAdmin();
    }

    private function seedRoles(): void
    {
        $roles = [
            ['name' => 'executive', 'display_name' => 'Executive', 'permissions' => json_encode(['view_all', 'export', 'print'])],
            ['name' => 'directorate_head', 'display_name' => 'Directorate Head', 'permissions' => json_encode(['view_own', 'input_data', 'export', 'print'])],
            ['name' => 'admin', 'display_name' => 'Administrator', 'permissions' => json_encode(['view_all', 'input_data', 'manage_users', 'manage_settings', 'manage_simulation', 'export', 'print'])],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }

    private function seedDirectorates(): void
    {
        $directorates = [
            ['code' => 'MD',   'name' => 'Managing Director',                                'color' => '#1e40af', 'sort_order' => 1],
            ['code' => 'GEN',  'name' => 'Power Generation Directorate',                     'color' => '#dc2626', 'sort_order' => 2],
            ['code' => 'TOT',  'name' => 'Transmission, Operations & Trade Directorate',     'color' => '#059669', 'sort_order' => 3],
            ['code' => 'DCS',  'name' => 'Distribution and Customer Services Directorate',   'color' => '#d97706', 'sort_order' => 4],
            ['code' => 'IF',   'name' => 'Investment & Finance Directorate',                 'color' => '#0891b2', 'sort_order' => 5],
            ['code' => 'HCD',  'name' => 'Human Capital & Development Directorate',          'color' => '#be185d', 'sort_order' => 6],
            ['code' => 'ICT',  'name' => 'Information & Communication Technology',           'color' => '#4f46e5', 'sort_order' => 7],
            ['code' => 'CSE',  'name' => 'Company Secretariat Directorate',                  'color' => '#78716c', 'sort_order' => 8],
            ['code' => 'ARM',  'name' => 'Auditing and Risk Management Directorate',         'color' => '#b91c1c', 'sort_order' => 9],
            ['code' => 'PP',   'name' => 'Planning and Projects Directorate',                'color' => '#0d9488', 'sort_order' => 10],
            ['code' => 'CTO',  'name' => 'Chief Technical Officer',                          'color' => '#6366f1', 'sort_order' => 11],
            ['code' => 'KWP',  'name' => 'Kalungwishi Projects',                             'color' => '#65a30d', 'sort_order' => 12],
        ];

        foreach ($directorates as $d) {
            Directorate::updateOrCreate(
                ['code' => $d['code']],
                array_merge($d, ['slug' => Str::slug($d['code']), 'is_active' => true])
            );
        }
    }

    private function seedKpis(): void
    {
        $kpis = [
            // Generation
            ['name' => 'Total Generation Output (MW)',           'code' => 'GEN-001', 'unit' => 'number',     'category' => 'generation',  'target_value' => 3200, 'warning_threshold' => 2800, 'critical_threshold' => 2400],
            ['name' => 'Plant Availability Factor (%)',          'code' => 'GEN-002', 'unit' => 'percentage', 'category' => 'generation',  'target_value' => 95,   'warning_threshold' => 85,   'critical_threshold' => 75],
            ['name' => 'Forced Outage Rate (%)',                 'code' => 'GEN-003', 'unit' => 'percentage', 'category' => 'generation',  'target_value' => 5,    'warning_threshold' => 10,   'critical_threshold' => 15],
            ['name' => 'Energy Sent Out (GWh)',                  'code' => 'GEN-004', 'unit' => 'number',     'category' => 'generation',  'target_value' => 15000,'warning_threshold' => 13000,'critical_threshold' => 11000],

            // Transmission
            ['name' => 'System Average Interruption (min)',      'code' => 'T&S-001', 'unit' => 'number',     'category' => 'transmission','target_value' => 120,  'warning_threshold' => 180,  'critical_threshold' => 240],
            ['name' => 'Transmission Losses (%)',                'code' => 'T&S-002', 'unit' => 'percentage', 'category' => 'transmission','target_value' => 4,    'warning_threshold' => 6,    'critical_threshold' => 8],
            ['name' => 'Network Availability (%)',               'code' => 'T&S-003', 'unit' => 'percentage', 'category' => 'transmission','target_value' => 99.5, 'warning_threshold' => 98,   'critical_threshold' => 96],

            // Distribution
            ['name' => 'Distribution Losses (%)',                'code' => 'DS-001',  'unit' => 'percentage', 'category' => 'distribution','target_value' => 10,   'warning_threshold' => 14,   'critical_threshold' => 18],
            ['name' => 'New Connections (count)',                 'code' => 'DS-002',  'unit' => 'number',     'category' => 'distribution','target_value' => 50000,'warning_threshold' => 40000,'critical_threshold' => 30000],
            ['name' => 'Electrification Rate (%)',               'code' => 'DS-003',  'unit' => 'percentage', 'category' => 'distribution','target_value' => 45,   'warning_threshold' => 38,   'critical_threshold' => 30],

            // Finance
            ['name' => 'Revenue Collection Rate (%)',            'code' => 'F&S-001', 'unit' => 'percentage', 'category' => 'financial',   'target_value' => 95,   'warning_threshold' => 85,   'critical_threshold' => 75],
            ['name' => 'Operating Cost Ratio',                   'code' => 'F&S-002', 'unit' => 'number',     'category' => 'financial',   'target_value' => 0.75, 'warning_threshold' => 0.85, 'critical_threshold' => 0.95],
            ['name' => 'Debt Service Coverage Ratio',            'code' => 'F&S-003', 'unit' => 'number',     'category' => 'financial',   'target_value' => 1.5,  'warning_threshold' => 1.2,  'critical_threshold' => 1.0],

            // Customer Service
            ['name' => 'Customer Satisfaction Index',            'code' => 'CS-001',  'unit' => 'percentage', 'category' => 'customer',    'target_value' => 85,   'warning_threshold' => 70,   'critical_threshold' => 55],
            ['name' => 'Average Response Time (hrs)',            'code' => 'CS-002',  'unit' => 'number',     'category' => 'customer',    'target_value' => 4,    'warning_threshold' => 8,    'critical_threshold' => 24],
            ['name' => 'Complaint Resolution Rate (%)',          'code' => 'CS-003',  'unit' => 'percentage', 'category' => 'customer',    'target_value' => 90,   'warning_threshold' => 75,   'critical_threshold' => 60],

            // HR
            ['name' => 'Staff Turnover Rate (%)',                'code' => 'HR-001',  'unit' => 'percentage', 'category' => 'hr',          'target_value' => 5,    'warning_threshold' => 10,   'critical_threshold' => 15],
            ['name' => 'Training Completion Rate (%)',           'code' => 'HR-002',  'unit' => 'percentage', 'category' => 'hr',          'target_value' => 90,   'warning_threshold' => 75,   'critical_threshold' => 60],

            // Safety
            ['name' => 'Lost Time Injury Frequency Rate',       'code' => 'SHE-001', 'unit' => 'number',     'category' => 'safety',      'target_value' => 0.5,  'warning_threshold' => 1.0,  'critical_threshold' => 2.0],
            ['name' => 'Safety Audit Compliance (%)',            'code' => 'SHE-002', 'unit' => 'percentage', 'category' => 'safety',      'target_value' => 95,   'warning_threshold' => 85,   'critical_threshold' => 70],

            // ICT
            ['name' => 'System Uptime (%)',                      'code' => 'ICT-001', 'unit' => 'percentage', 'category' => 'ict',         'target_value' => 99.9, 'warning_threshold' => 99,   'critical_threshold' => 97],
            ['name' => 'Cybersecurity Incidents',                'code' => 'ICT-002', 'unit' => 'number',     'category' => 'ict',         'target_value' => 0,    'warning_threshold' => 2,    'critical_threshold' => 5],

            // Projects
            ['name' => 'Project Delivery On Time (%)',           'code' => 'P&E-001', 'unit' => 'percentage', 'category' => 'projects',    'target_value' => 85,   'warning_threshold' => 70,   'critical_threshold' => 55],
            ['name' => 'CAPEX Utilization (%)',                  'code' => 'P&E-002', 'unit' => 'percentage', 'category' => 'projects',    'target_value' => 90,   'warning_threshold' => 75,   'critical_threshold' => 60],
        ];

        // Map KPIs to directorates
        $directorateMap = [
            'generation'   => 'GEN',
            'transmission' => 'TOT',
            'distribution' => 'DCS',
            'financial'    => 'IF',
            'customer'     => 'DCS',
            'hr'           => 'HCD',
            'safety'       => 'ARM',
            'ict'          => 'ICT',
            'projects'     => 'PP',
        ];

        foreach ($kpis as $kpiData) {
            $kpiData['slug'] = Str::slug($kpiData['code']);

            $kpi = Kpi::updateOrCreate(
                ['code' => $kpiData['code']],
                $kpiData
            );

            // Attach to matching directorate
            $dirCode = $directorateMap[$kpiData['category']] ?? null;
            if ($dirCode) {
                $directorate = Directorate::where('code', $dirCode)->first();
                if ($directorate && !$kpi->directorates()->where('directorate_id', $directorate->id)->exists()) {
                    $kpi->directorates()->attach($directorate->id);
                }
            }
        }
    }

    private function seedSettings(): void
    {
        $settings = [
            ['key' => 'data_source', 'value' => 'simulation', 'type' => 'string', 'group' => 'general', 'description' => 'Dashboard data source', 'is_public' => false],
            ['key' => 'simulation_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'simulation', 'description' => 'Simulation mode enabled', 'is_public' => false],
            ['key' => 'simulation_interval', 'value' => '30', 'type' => 'integer', 'group' => 'simulation', 'description' => 'Simulation interval (seconds)', 'is_public' => false],
            ['key' => 'cache_ttl', 'value' => '300', 'type' => 'integer', 'group' => 'general', 'description' => 'Cache TTL (seconds)', 'is_public' => false],
            ['key' => 'session_timeout', 'value' => '900', 'type' => 'integer', 'group' => 'general', 'description' => 'Session timeout (seconds)', 'is_public' => false],
            ['key' => 'allowed_email_domain', 'value' => 'zesco.co.zm', 'type' => 'string', 'group' => 'general', 'description' => 'Allowed email domain', 'is_public' => false],
            ['key' => 'alert_threshold_warning', 'value' => '15', 'type' => 'integer', 'group' => 'alerts', 'description' => 'Warning alert threshold (%)', 'is_public' => false],
            ['key' => 'alert_threshold_critical', 'value' => '25', 'type' => 'integer', 'group' => 'alerts', 'description' => 'Critical alert threshold (%)', 'is_public' => false],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }

    private function seedDefaultAdmin(): void
    {
        $adminRole = Role::where('name', 'admin')->first();

        User::updateOrCreate(
            ['email' => 'admin@zesco.co.zm'],
            [
                'name' => 'System Administrator',
                'role_id' => $adminRole?->id,
                'azure_id' => null,
                'is_active' => true,
            ]
        );
    }
}
