<?php

namespace App\Console\Commands;

use App\Services\SimulationService;
use Illuminate\Console\Command;

class SimulateDataCommand extends Command
{
    protected $signature = 'dashboard:simulate
                            {--seed : Generate initial historical data}
                            {--months=12 : Number of months for historical data}';

    protected $description = 'Run simulation cycle or generate historical data';

    public function handle(SimulationService $simulation): int
    {
        if ($this->option('seed')) {
            $months = (int) $this->option('months');
            $this->info("Generating {$months} months of historical data...");

            $count = $simulation->generateHistoricalData($months);
            $this->info("Generated {$count} KPI entries.");

            return self::SUCCESS;
        }

        if (!$simulation->isEnabled()) {
            $this->warn('Simulation mode is currently disabled.');
            return self::FAILURE;
        }

        $this->info('Running simulation cycle...');
        $result = $simulation->runSimulationCycle();

        $this->info("Generated {$result['entries_generated']} entries at {$result['timestamp']}");

        return self::SUCCESS;
    }
}
