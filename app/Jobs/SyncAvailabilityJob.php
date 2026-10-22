<?php

namespace App\Jobs;

use App\Models\SyncLog;
use App\Services\Ota\OtaRegistry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SyncAvailabilityJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 6;

    public function __construct(
        public int $syncLogId,
        public int $roomTypeId,
        public array $nights,
    ) {}

    public function backoff(): array
    {
        return [5, 15, 30, 60, 90];
    }

    public function handle(): void
    {
        $log = SyncLog::find($this->syncLogId);
        if (! $log) {
            return;
        }
        $log->increment('attempts');

        try {
            $code = OtaRegistry::for($log->channel)->push($this->roomTypeId, $this->nights);
            $log->update(['status' => 'success', 'response_code' => $code, 'message' => null]);
        } catch (Throwable $e) {
            $log->update([
                'status' => 'retrying',
                'response_code' => $e->getCode() ?: null,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        SyncLog::where('id', $this->syncLogId)->update([
            'status' => 'failed',
            'message' => $e->getMessage(),
        ]);
    }
}
