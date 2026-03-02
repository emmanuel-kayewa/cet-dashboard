<?php

namespace App\Console\Commands;

use App\Mail\WeeklyDigestMail;
use App\Models\User;
use App\Services\AiAnalysisService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WeeklyDigestCommand extends Command
{
    protected $signature = 'dashboard:weekly-digest {--test : Send only to the first admin user}';
    protected $description = 'Generate and send AI-powered weekly performance digest';

    public function handle(AiAnalysisService $aiService): int
    {
        if (!$aiService->isAvailable()) {
            $this->error('AI service is not available. Is Ollama running?');
            return self::FAILURE;
        }

        $this->info('Generating weekly digest with AI analysis...');

        $digest = $aiService->generateWeeklyDigest();

        if (empty($digest)) {
            $this->error('Failed to generate digest — AI returned empty response.');
            return self::FAILURE;
        }

        $this->info('Digest generated. Sending emails...');

        // Determine recipients
        if ($this->option('test')) {
            $recipients = User::where('is_active', true)
                ->whereHas('role', fn($q) => $q->where('slug', 'admin'))
                ->limit(1)
                ->get();
        } else {
            $recipients = User::where('is_active', true)
                ->whereHas('role', fn($q) => $q->whereIn('slug', ['admin', 'executive', 'directorate_head']))
                ->get();
        }

        $sent = 0;
        foreach ($recipients as $user) {
            try {
                Mail::to($user->email)->queue(new WeeklyDigestMail($digest, $user));
                $sent++;
            } catch (\Exception $e) {
                Log::error("Failed to send digest to {$user->email}", ['error' => $e->getMessage()]);
                $this->warn("Failed to send to {$user->email}: {$e->getMessage()}");
            }
        }

        $this->info("Weekly digest sent to {$sent} recipient(s).");

        return self::SUCCESS;
    }
}
