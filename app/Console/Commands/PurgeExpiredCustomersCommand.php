<?php

namespace App\Console\Commands;

use App\Services\CustomerDeletionService;
use Illuminate\Console\Command;

class PurgeExpiredCustomersCommand extends Command
{
    protected $signature = 'customers:purge-expired';

    protected $description = 'Permanently delete soft-deleted customers past the retention period';

    public function handle(CustomerDeletionService $service): int
    {
        $purged = $service->purgeExpiredCustomers();

        $this->info("Purged {$purged} customer(s).");

        return self::SUCCESS;
    }
}
