<?php

namespace App\Jobs;

use App\Services\FacebookCapiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendFacebookCapiEvent implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $uniqueFor = 3600;

    public function __construct(
        public string $eventName,
        public array $payload,
        public string $eventId,
    ) {}

    public function uniqueId(): string
    {
        return $this->eventId;
    }

    public function handle(FacebookCapiService $capi): void
    {
        $capi->send($this->eventName, $this->payload, $this->eventId);
    }
}
