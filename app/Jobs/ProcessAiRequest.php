<?php

namespace App\Jobs;

use App\Services\AI\AiProviderManager;
use App\Services\AiAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Processes an AI request in the background so that PHP's
 * max_execution_time doesn't kill long-running LLM calls.
 *
 * Flow:
 *   Controller sets task status to 'processing' → dispatches this job
 *   Job runs the AI call → writes result to cache under task:<id>
 *   Frontend polls GET /api/ai/task/{id} to retrieve the result.
 */
class ProcessAiRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     * Large models can easily take 5-10 minutes.
     */
    public int $timeout = 900; // 15 minutes

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 1;

    public function __construct(
        public string $taskId,
        public string $method,
        public array $params = [],
    ) {}

    public function handle(AiAnalysisService $aiService): void
    {
        $cacheKey = "ai_task:{$this->taskId}";

        try {
            // Mark as running
            Cache::put($cacheKey, [
                'status' => 'running',
                'started_at' => now()->toISOString(),
            ], now()->addMinutes(30));

            $result = match ($this->method) {
                'executiveInsights' => $aiService->generateExecutiveInsights(
                    $this->params['fresh'] ?? false
                ),
                'query' => $aiService->answerQuery(
                    $this->params['question'] ?? ''
                ),
                'explainAnomaly' => $aiService->explainAnomaly(
                    $this->params['kpi_id'],
                    $this->params['directorate_id']
                ),
                'recommendations' => $aiService->suggestActions(
                    $this->params['directorate_id']
                ),
                'predictBreach' => $aiService->predictDeadlineBreach(
                    $this->params['kpi_id'],
                    $this->params['directorate_id']
                ),
                default => ['error' => "Unknown AI method: {$this->method}"],
            };

            Cache::put($cacheKey, [
                'status' => 'completed',
                'result' => $result,
                'completed_at' => now()->toISOString(),
            ], now()->addMinutes(30));

        } catch (\Throwable $e) {
            Log::error('AI job failed', [
                'task' => $this->taskId,
                'method' => $this->method,
                'error' => $e->getMessage(),
            ]);

            Cache::put($cacheKey, [
                'status' => 'failed',
                'error' => 'AI processing failed: ' . $e->getMessage(),
                'failed_at' => now()->toISOString(),
            ], now()->addMinutes(30));
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        Cache::put("ai_task:{$this->taskId}", [
            'status' => 'failed',
            'error' => $exception?->getMessage() ?? 'Unknown error',
            'failed_at' => now()->toISOString(),
        ], now()->addMinutes(30));
    }
}
