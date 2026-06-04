<?php

namespace App\Console\Commands;

use App\Services\ProductImageTempService;
use Illuminate\Console\Command;

class PruneProductImageTempCommand extends Command
{
    protected $signature = 'product-images:prune-temp';

    protected $description = 'Delete orphaned temporary product image uploads older than 24 hours';

    public function handle(ProductImageTempService $tempService): int
    {
        $deleted = $tempService->pruneExpiredFiles();
        $this->info("Pruned {$deleted} temporary product image file(s).");

        return self::SUCCESS;
    }
}
