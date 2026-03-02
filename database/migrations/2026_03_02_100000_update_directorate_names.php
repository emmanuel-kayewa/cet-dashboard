<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Map old codes to new codes and names
        $updates = [
            'MD'   => ['code' => 'MD',   'name' => 'Managing Director',                                'sort_order' => 1],
            'GEN'  => ['code' => 'GEN',  'name' => 'Power Generation Directorate',                     'sort_order' => 2],
            'T&S'  => ['code' => 'TOT',  'name' => 'Transmission, Operations & Trade Directorate',     'sort_order' => 3],
            'DS'   => ['code' => 'DCS',  'name' => 'Distribution and Customer Services Directorate',   'sort_order' => 4],
            'F&S'  => ['code' => 'IF',   'name' => 'Investment & Finance Directorate',                 'sort_order' => 5],
            'HR'   => ['code' => 'HCD',  'name' => 'Human Capital & Development Directorate',          'sort_order' => 6],
            'ICT'  => ['code' => 'ICT',  'name' => 'Information & Communication Technology',           'sort_order' => 7],
            'L&CS' => ['code' => 'CSE',  'name' => 'Company Secretariat Directorate',                  'sort_order' => 8],
            'IA'   => ['code' => 'ARM',  'name' => 'Auditing and Risk Management Directorate',         'sort_order' => 9],
            'P&E'  => ['code' => 'PP',   'name' => 'Planning and Projects Directorate',                'sort_order' => 10],
            'CTO'  => ['code' => 'CTO',  'name' => 'Chief Technical Officer',                          'sort_order' => 11],
        ];

        // Deactivate directorates that are no longer in the list
        $removedCodes = ['CS', 'SHE', 'SBD'];
        DB::table('directorates')
            ->whereIn('code', $removedCodes)
            ->update(['is_active' => false]);

        // Update existing directorates with new codes, names, and slugs
        foreach ($updates as $oldCode => $data) {
            DB::table('directorates')
                ->where('code', $oldCode)
                ->update([
                    'code'       => $data['code'],
                    'name'       => $data['name'],
                    'slug'       => Str::slug($data['code']),
                    'sort_order' => $data['sort_order'],
                    'is_active'  => true,
                    'updated_at' => now(),
                ]);
        }

        // Create the new Kalungwishi Projects directorate
        $exists = DB::table('directorates')->where('code', 'KWP')->exists();
        if (!$exists) {
            DB::table('directorates')->insert([
                'code'       => 'KWP',
                'name'       => 'Kalungwishi Projects',
                'slug'       => Str::slug('KWP'),
                'color'      => '#65a30d',
                'icon'       => 'building',
                'sort_order' => 12,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Reverse: restore old codes and names
        $reversals = [
            'MD'   => ['code' => 'MD',   'name' => "Managing Director's Office",          'sort_order' => 1],
            'GEN'  => ['code' => 'GEN',  'name' => 'Generation',                          'sort_order' => 2],
            'TOT'  => ['code' => 'T&S',  'name' => 'Transmission & Systems',              'sort_order' => 3],
            'DCS'  => ['code' => 'DS',   'name' => 'Distribution & Supply',               'sort_order' => 4],
            'IF'   => ['code' => 'F&S',  'name' => 'Finance & Strategy',                  'sort_order' => 6],
            'HCD'  => ['code' => 'HR',   'name' => 'Human Resources & Administration',    'sort_order' => 7],
            'ICT'  => ['code' => 'ICT',  'name' => 'Information & Communication Technology','sort_order' => 8],
            'CSE'  => ['code' => 'L&CS', 'name' => 'Legal & Company Secretariat',         'sort_order' => 9],
            'ARM'  => ['code' => 'IA',   'name' => 'Internal Audit',                      'sort_order' => 10],
            'PP'   => ['code' => 'P&E',  'name' => 'Projects & Engineering',              'sort_order' => 11],
            'CTO'  => ['code' => 'CTO',  'name' => 'Chief Technical Officer',             'sort_order' => 14],
        ];

        foreach ($reversals as $currentCode => $data) {
            DB::table('directorates')
                ->where('code', $currentCode)
                ->update([
                    'code'       => $data['code'],
                    'name'       => $data['name'],
                    'slug'       => Str::slug($data['code']),
                    'sort_order' => $data['sort_order'],
                    'updated_at' => now(),
                ]);
        }

        // Re-activate removed directorates
        DB::table('directorates')
            ->whereIn('code', ['CS', 'SHE', 'SBD'])
            ->update(['is_active' => true]);

        // Remove the Kalungwishi Projects directorate
        DB::table('directorates')->where('code', 'KWP')->delete();
    }
};
