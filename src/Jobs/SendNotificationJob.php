<?php

declare(strict_types=1);

namespace G80st\OrthancClient\Jobs;

use G80st\OrthancClient\Facades\Orthanc;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        public array $payload
    ) {
        $this->onQueue(config('orthanc-client.queue.queue', 'orthanc-client'));
        $this->onConnection(config('orthanc-client.queue.connection', 'redis'));
    }

    public function handle(): void
    {
        Orthanc::sendNow($this->payload);
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('Orthanc client: Failed to send notification after all retries', [
            'payload' => $this->payload,
            'exception' => $exception->getMessage(),
        ]);
    }
}
