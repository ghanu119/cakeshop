<?php

namespace App\Jobs;

use App\Mail\CriticalErrorNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendCriticalErrorMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, int>  $backoff
     */
    public function __construct(
        public array $payload,
        public array $backoff = [60, 300],
    ) {}

    public function handle(): void
    {
        $recipient = config('error-reporting.recipient');

        if (! is_string($recipient) || filter_var($recipient, FILTER_VALIDATE_EMAIL) === false) {
            return;
        }

        Mail::to($recipient)->send(new CriticalErrorNotification($this->payload));
    }

    public function failed(?Throwable $exception): void
    {
        if ($exception === null) {
            return;
        }

        Log::error('Failed to send critical error notification email.', [
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
            'original_error' => $this->payload['exception_class'] ?? null,
        ]);
    }
}
