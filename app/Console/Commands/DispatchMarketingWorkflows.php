<?php

namespace App\Console\Commands;

use App\Services\MarketingWorkflowService;
use Illuminate\Console\Command;

class DispatchMarketingWorkflows extends Command
{
    protected $signature = 'marketing:dispatch {--retry-configuration : Retry deliveries stopped by missing provider configuration}';
    protected $description = 'Create and dispatch consented lifecycle marketing messages';

    public function handle(MarketingWorkflowService $workflows): int
    {
        if ($this->option('retry-configuration')) {
            $retried = $workflows->retryConfigurationFailures();
            $this->info("Requeued {$retried} configuration-blocked deliveries.");
        }

        $count = $workflows->dispatchDue();
        $this->info("Queued {$count} due marketing deliveries.");

        return self::SUCCESS;
    }
}
