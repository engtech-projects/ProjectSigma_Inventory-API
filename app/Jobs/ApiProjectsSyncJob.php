<?php

namespace App\Jobs;

use App\Http\Services\ApiServices\ProjectMonitoringSecretKeyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiProjectsSyncJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    protected string $method;

    public function __construct(string $method)
    {
        $this->method = $method;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $service = app(ProjectMonitoringSecretKeyService::class);
            if (!method_exists($service, $this->method)) {
                Log::warning("ApiProjectsSyncJob: Method {$this->method} does not exist.");
                return;
            }
            DB::transaction(function () use ($service) {
                $service->{$this->method}();
            });
            Log::info("ApiProjectsSyncJob successfully synced with [{$this->method}]");
        } catch (\Throwable $e) {
            Log::error("ApiProjectsSyncJob failed [{$this->method}]: " . $e->getMessage());
        }
    }
}
