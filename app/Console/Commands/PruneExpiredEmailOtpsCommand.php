<?php

namespace App\Console\Commands;

use App\Models\EmailLoginOtp;
use Illuminate\Console\Command;

class PruneExpiredEmailOtpsCommand extends Command
{
    protected $signature = 'email-otps:prune';

    protected $description = 'Remove expired and consumed email login OTP records';

    public function handle(): int
    {
        $deleted = EmailLoginOtp::query()
            ->where(function ($q) {
                $q->where('expires_at', '<', now()->subDay())
                    ->orWhereNotNull('consumed_at');
            })
            ->delete();

        $this->info("Pruned {$deleted} OTP record(s).");

        return self::SUCCESS;
    }
}
