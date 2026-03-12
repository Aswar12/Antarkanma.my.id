<?php

namespace App\Console\Commands;

use App\Services\TableManagementService;
use Illuminate\Console\Command;

class AutoReleaseTablesCommand extends Command
{
    protected $signature = 'tables:auto-release';
    protected $description = 'Auto-release tables that have passed their scheduled release time';

    public function handle(): int
    {
        $service = new TableManagementService();
        $count = $service->checkAndAutoReleaseTables();

        if ($count > 0) {
            $this->info("Auto-released {$count} table(s).");
        } else {
            $this->info('No tables to auto-release.');
        }

        return Command::SUCCESS;
    }
}
