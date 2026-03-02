<?php

namespace App\Console\Commands;

use App\Services\AlertService;
use Illuminate\Console\Command;

class CheckAlertsCommand extends Command
{
    protected $signature = 'dashboard:check-alerts {--type=all : Check type (all, thresholds, deadlines, stale, milestones)}';
    protected $description = 'Check KPI thresholds, deadlines, stale data and generate alerts';

    public function handle(AlertService $alertService): int
    {
        $type = $this->option('type');

        if ($type === 'all') {
            $this->info('Running all alert checks...');
            $results = $alertService->runAllChecks();

            $this->table(
                ['Check Type', 'Alerts Generated'],
                collect($results)->map(fn($count, $type) => [$type, $count])->toArray()
            );

            $total = array_sum($results);
            $this->info("Total: {$total} alerts generated.");
        } else {
            $count = match ($type) {
                'thresholds' => $alertService->checkThresholds(),
                'deadlines' => $alertService->checkDeadlines(),
                'stale' => $alertService->checkStaleData(),
                'milestones' => $alertService->checkMilestones(),
                default => 0,
            };
            $this->info("Generated {$count} {$type} alerts.");
        }

        return self::SUCCESS;
    }
}
