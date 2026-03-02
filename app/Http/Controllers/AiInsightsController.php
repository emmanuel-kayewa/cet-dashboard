<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessAiRequest;
use App\Services\AI\AiProviderManager;
use App\Services\AiAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AiInsightsController extends Controller
{
    /**
     * AI Insights overview page.
     */
    public function index(AiProviderManager $ai)
    {
        return Inertia::render('AI/Insights', [
            'aiAvailable' => $ai->isAvailable(),
            'aiProvider' => $ai->isAvailable() ? $ai->getIdentifier() : null,
            'queueDriver' => config('queue.default'),
        ]);
    }

    // ─────────────────────────────────────────────────────────
    //  Async dispatch + poll approach
    // ─────────────────────────────────────────────────────────

    /**
     * Dispatch any AI method as a background job.
     * Returns a task ID that the frontend polls.
     */
    private function dispatchAiTask(string $method, array $params = []): JsonResponse
    {
        $taskId = Str::uuid()->toString();

        // Seed the cache entry so the poll endpoint finds it immediately
        Cache::put("ai_task:{$taskId}", [
            'status' => 'queued',
            'queued_at' => now()->toISOString(),
        ], now()->addMinutes(30));

        ProcessAiRequest::dispatch($taskId, $method, $params);

        return response()->json([
            'async' => true,
            'task_id' => $taskId,
        ]);
    }

    /**
     * Poll for a background AI task result.
     * GET /api/ai/task/{taskId}
     */
    public function pollTask(string $taskId): JsonResponse
    {
        $data = Cache::get("ai_task:{$taskId}");

        if (!$data) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Task not found or expired.',
            ], 404);
        }

        return response()->json($data);
    }

    // ─────────────────────────────────────────────────────────
    //  Sync-or-async wrappers for each AI feature
    // ─────────────────────────────────────────────────────────

    /**
     * Should we dispatch to queue? Yes when queue driver is not 'sync'.
     * When running `sync` driver, we still bump set_time_limit.
     */
    private function shouldAsync(): bool
    {
        return config('queue.default') !== 'sync';
    }

    /**
     * Generate executive AI insights.
     */
    public function executiveInsights(Request $request, AiAnalysisService $aiService): JsonResponse
    {
        if (!$aiService->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'AI service unavailable. Ensure Ollama is running.',
            ], 503);
        }

        $fresh = $request->boolean('fresh', false);

        if ($this->shouldAsync()) {
            return $this->dispatchAiTask('executiveInsights', ['fresh' => $fresh]);
        }

        // Synchronous fallback — extend PHP time limit
        set_time_limit(0);
        $insights = $aiService->generateExecutiveInsights($fresh);

        return response()->json([
            'success' => !empty($insights),
            'insights' => $insights,
        ]);
    }

    /**
     * Explain a KPI anomaly.
     */
    public function explainAnomaly(Request $request, AiAnalysisService $aiService): JsonResponse
    {
        $request->validate([
            'kpi_id' => 'required|integer|exists:kpis,id',
            'directorate_id' => 'required|integer|exists:directorates,id',
        ]);

        if (!$aiService->isAvailable()) {
            return response()->json(['success' => false, 'message' => 'AI service unavailable.'], 503);
        }

        if ($this->shouldAsync()) {
            return $this->dispatchAiTask('explainAnomaly', $request->only('kpi_id', 'directorate_id'));
        }

        set_time_limit(0);
        $explanation = $aiService->explainAnomaly($request->kpi_id, $request->directorate_id);

        return response()->json([
            'success' => !empty($explanation),
            'explanation' => $explanation,
        ]);
    }

    /**
     * Get AI recommendations for a directorate.
     */
    public function recommendations(Request $request, AiAnalysisService $aiService): JsonResponse
    {
        $request->validate([
            'directorate_id' => 'required|integer|exists:directorates,id',
        ]);

        if (!$aiService->isAvailable()) {
            return response()->json(['success' => false, 'message' => 'AI service unavailable.'], 503);
        }

        if ($this->shouldAsync()) {
            return $this->dispatchAiTask('recommendations', $request->only('directorate_id'));
        }

        set_time_limit(0);
        $recommendations = $aiService->suggestActions($request->directorate_id);

        return response()->json([
            'success' => !empty($recommendations),
            'recommendations' => $recommendations,
        ]);
    }

    /**
     * Natural language query.
     */
    public function query(Request $request, AiAnalysisService $aiService): JsonResponse
    {
        $request->validate([
            'question' => 'required|string|max:1000',
        ]);

        if (!$aiService->isAvailable()) {
            return response()->json(['success' => false, 'message' => 'AI service unavailable.'], 503);
        }

        if ($this->shouldAsync()) {
            return $this->dispatchAiTask('query', ['question' => $request->question]);
        }

        set_time_limit(0);
        $answer = $aiService->answerQuery($request->question);

        return response()->json([
            'success' => !empty($answer),
            'result' => $answer,
        ]);
    }

    /**
     * Predict deadline breach for a KPI.
     */
    public function predictBreach(Request $request, AiAnalysisService $aiService): JsonResponse
    {
        $request->validate([
            'kpi_id' => 'required|integer|exists:kpis,id',
            'directorate_id' => 'required|integer|exists:directorates,id',
        ]);

        if (!$aiService->isAvailable()) {
            return response()->json(['success' => false, 'message' => 'AI service unavailable.'], 503);
        }

        if ($this->shouldAsync()) {
            return $this->dispatchAiTask('predictBreach', $request->only('kpi_id', 'directorate_id'));
        }

        set_time_limit(0);
        $prediction = $aiService->predictDeadlineBreach($request->kpi_id, $request->directorate_id);

        return response()->json([
            'success' => !empty($prediction),
            'prediction' => $prediction,
        ]);
    }

    /**
     * Check AI provider status.
     */
    public function status(AiProviderManager $ai): JsonResponse
    {
        return response()->json([
            'available' => $ai->isAvailable(),
            'provider' => $ai->isAvailable() ? $ai->getIdentifier() : null,
            'configured_provider' => config('dashboard.ai.provider'),
            'enabled' => config('dashboard.ai.enabled', true),
            'async' => $this->shouldAsync(),
        ]);
    }

    /**
     * Clear AI caches.
     */
    public function clearCache(AiAnalysisService $aiService): JsonResponse
    {
        $aiService->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'AI analysis cache cleared.',
        ]);
    }
}
