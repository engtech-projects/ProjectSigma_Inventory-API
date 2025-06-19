<?php

namespace App\Jobs;

use App\Http\Services\ApiServices\HrmsSecretKeyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiHrmsSyncJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected string $method;

    /**
     * Create a new job instance.
     */
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
            $service = app(HrmsSecretKeyService::class);
            if (!method_exists($service, $this->method)) {
                Log::warning("ApiHrmsSyncJob: Method {$this->method} does not exist.");
                return;
            }
            DB::transaction(function () use ($service) {
                $service->{$this->method}();
            });
            Log::info("ApiHrmsSyncJob successfully synced with [{$this->method}]");
        } catch (\Throwable $e) {
            Log::error("ApiHrmsSyncJob failed [{$this->method}]: " . $e->getMessage());
        }
    }
}
