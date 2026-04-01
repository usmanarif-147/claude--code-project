<?php

namespace App\Console\Commands;

use App\Services\RecurringTaskService;
use Illuminate\Console\Command;

class GenerateRecurringTasks extends Command
{
    protected $signature = 'tasks:generate-recurring';

    protected $description = 'Generate tasks from active recurring task templates';

    public function handle(RecurringTaskService $service): int
    {
        $count = $service->generateDueRecurringTasks();

        $this->info("Generated {$count} task(s) from recurring templates.");

        return self::SUCCESS;
    }
}
