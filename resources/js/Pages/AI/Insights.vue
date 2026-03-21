<template>
    <AppLayout :directorates="directorates">
        <template #title>AI Insights</template>

        <Breadcrumb :items="[
            { label: 'Dashboard', href: '/dashboard' },
            { label: 'AI Insights', current: true }
        ]" />

        <!-- AI Status Banner -->
        <div v-if="!aiAvailable" class="mb-6 rounded-lg border border-amber-200 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-800 p-4">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                <div>
                    <p class="text-sm font-medium text-amber-800 dark:text-amber-200">AI Service Unavailable</p>
                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">Ensure Ollama is running locally. Run <code class="bg-amber-100 dark:bg-amber-900/40 px-1.5 py-0.5 rounded">ollama serve</code> to start.</p>
                </div>
            </div>
        </div>

        <div v-else class="mb-6 rounded-lg border border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-800 p-4">
            <div class="flex items-center gap-3">
                <div class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                </div>
                <p class="text-sm text-green-700 dark:text-green-300">AI Connected — <span class="font-medium">{{ aiProvider }}</span></p>
            </div>
        </div>

        <!-- AI Chat / Query -->
        <Card title="Ask AI" class="mb-6">
            <template #actions>
                <button @click="clearChat" class="text-xs text-gray-400 hover:text-gray-600" v-if="chatHistory.length">Clear</button>
            </template>
            <div class="space-y-4">
                <!-- Chat History -->
                <div v-if="chatHistory.length" class="space-y-3 max-h-[400px] overflow-y-auto pr-2">
                    <div v-for="(msg, idx) in chatHistory" :key="idx"
                         :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                        <div :class="[
                            'max-w-[85%] rounded-lg px-4 py-3 text-sm',
                            msg.role === 'user'
                                ? 'bg-black dark:bg-white text-white dark:text-black'
                                : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
                        ]">
                            <p class="whitespace-pre-wrap">{{ msg.content }}</p>
                            <div v-if="msg.dataPoints && msg.dataPoints.length" class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                                <p class="text-xs font-medium mb-1 opacity-70">Key Data Points:</p>
                                <ul class="text-xs space-y-0.5 opacity-80">
                                    <li v-for="dp in msg.dataPoints" :key="dp">• {{ dp }}</li>
                                </ul>
                            </div>
                            <div v-if="msg.suggestions && msg.suggestions.length" class="mt-2 flex flex-wrap gap-1.5">
                                <button v-for="s in msg.suggestions" :key="s" @click="askQuestion(s)"
                                        class="text-xs px-2 py-1 rounded-full bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                                    {{ s }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-if="queryLoading" class="flex justify-start">
                        <div class="bg-gray-100 dark:bg-gray-700 rounded-lg px-4 py-3 text-sm text-gray-500">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Analyzing data<span v-if="elapsedQuery"> ({{ elapsedQuery }}s)</span>...
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Prompts (when no history) -->
                <div v-if="!chatHistory.length && !queryLoading" class="flex flex-wrap gap-2">
                    <button v-for="prompt in quickPrompts" :key="prompt" @click="askQuestion(prompt)"
                            class="text-xs px-3 py-1.5 rounded-full border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        {{ prompt }}
                    </button>
                </div>

                <!-- Input -->
                <form @submit.prevent="submitQuery" class="flex gap-2">
                    <input v-model="queryInput" type="text" placeholder="Ask about KPIs, performance, trends..."
                           class="input-field flex-1 text-sm" :disabled="queryLoading || !aiAvailable" />
                    <Button type="submit" :disabled="queryLoading || !queryInput.trim() || !aiAvailable" :loading="queryLoading" variant="primary" size="md">
                        <span v-if="!queryLoading">Send</span>
                    </Button>
                </form>
            </div>
        </Card>

        <!-- Executive AI Insights -->
        <Card title="Executive AI Summary" class="mb-6">
            <template #actions>
                <div class="flex items-center gap-2">
                    <span v-if="insights?.generated_at" class="text-xs text-gray-400">{{ formatTime(insights.generated_at) }}</span>
                    <button @click="loadInsights(true)" :disabled="insightsLoading"
                            class="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 flex items-center gap-1">
                        <svg :class="['w-3.5 h-3.5', insightsLoading && 'animate-spin']" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
                        </svg>
                        Refresh
                    </button>
                </div>
            </template>

            <div v-if="insightsLoading && !insights" class="flex items-center justify-center py-12">
                <div class="text-center">
                    <svg class="w-8 h-8 animate-spin mx-auto mb-3 text-gray-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-sm text-gray-500">Generating AI insights<span v-if="elapsedInsights"> ({{ elapsedInsights }}s)</span>...</p>
                    <p class="text-xs text-gray-400 mt-1">Large models can take a few minutes</p>
                </div>
            </div>

            <div v-else-if="insights" class="space-y-5">
                <!-- Summary -->
                <div>
                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">{{ insights.summary }}</p>
                </div>

                <!-- Key Concerns -->
                <div v-if="insights.key_concerns?.length">
                    <h4 class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase tracking-wider mb-2">Key Concerns</h4>
                    <ul class="space-y-1">
                        <li v-for="c in insights.key_concerns" :key="c" class="text-sm text-gray-600 dark:text-gray-400 flex items-start gap-2">
                            <span class="text-red-400 mt-0.5">•</span> {{ c }}
                        </li>
                    </ul>
                </div>

                <!-- Positive Highlights -->
                <div v-if="insights.positive_highlights?.length">
                    <h4 class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase tracking-wider mb-2">Positive Highlights</h4>
                    <ul class="space-y-1">
                        <li v-for="h in insights.positive_highlights" :key="h" class="text-sm text-gray-600 dark:text-gray-400 flex items-start gap-2">
                            <span class="text-green-400 mt-0.5">•</span> {{ h }}
                        </li>
                    </ul>
                </div>

                <!-- Recommendations -->
                <div v-if="insights.recommendations?.length">
                    <h4 class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider mb-2">Recommendations</h4>
                    <ul class="space-y-1">
                        <li v-for="r in insights.recommendations" :key="r" class="text-sm text-gray-600 dark:text-gray-400 flex items-start gap-2">
                            <span class="text-blue-400 mt-0.5">→</span> {{ r }}
                        </li>
                    </ul>
                </div>

                <!-- Outlook -->
                <div v-if="insights.outlook" class="pt-3 border-t border-gray-100 dark:border-gray-700">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Outlook</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 italic">{{ insights.outlook }}</p>
                </div>
            </div>

            <div v-else class="text-center py-8">
                <p class="text-sm text-gray-400 mb-3">No insights generated yet.</p>
                <Button @click="loadInsights()" :disabled="!aiAvailable" variant="primary" size="md">Generate Insights</Button>
            </div>
        </Card>

    </AppLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';

const props = defineProps({
    directorates: { type: Array, default: () => [] },
    aiAvailable: { type: Boolean, default: false },
    aiProvider: { type: String, default: null },
    queueDriver: { type: String, default: 'sync' },
});

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content;

// ── Elapsed-time counters ───────────────────────────────
const elapsedQuery = ref(0);
const elapsedInsights = ref(0);
let queryTimer = null;
let insightsTimer = null;

function startTimer(counterRef) {
    counterRef.value = 0;
    return setInterval(() => counterRef.value++, 1000);
}
function stopTimer(timerId, counterRef) {
    clearInterval(timerId);
    counterRef.value = 0;
    return null;
}

// ── Polling helper ──────────────────────────────────────
async function pollTask(taskId, intervalMs = 3000, maxPollMs = 900000) {
    const deadline = Date.now() + maxPollMs;

    while (Date.now() < deadline) {
        await new Promise(r => setTimeout(r, intervalMs));

        const resp = await fetch(`/api/ai/task/${taskId}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
        });

        if (!resp.ok) {
            const text = await resp.text();
            let message = `Polling failed (HTTP ${resp.status})`;
            try { message = JSON.parse(text).message || message; } catch {}
            throw new Error(message);
        }

        const data = await resp.json();

        if (data.status === 'completed') return data.result;
        if (data.status === 'failed') throw new Error(data.error || 'AI processing failed');
        // else: queued / running — keep polling
    }

    throw new Error('AI request timed out');
}

/**
 * Post to an AI endpoint. Handles both sync and async (job) responses.
 * If the response contains { async: true, task_id }, it polls until completion.
 */
async function aiPost(url, body = {}) {
    const resp = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify(body),
    });

    // Guard: read as text first so an HTML error page doesn't blow up JSON.parse
    const text = await resp.text();

    let data;
    try {
        data = JSON.parse(text);
    } catch {
        console.error('Non-JSON response from', url, ':', text.substring(0, 300));
        throw new Error(
            resp.status === 419
                ? 'Session expired — please refresh the page.'
                : `Server returned an unexpected response (HTTP ${resp.status}). Please try again.`
        );
    }

    if (!resp.ok) {
        throw new Error(data.message || `Request failed (HTTP ${resp.status})`);
    }

    // Async job — poll for the result
    if (data.async && data.task_id) {
        return await pollTask(data.task_id);
    }

    // Sync result returned directly
    return data;
}

// ── Chat ────────────────────────────────────────────────
const queryInput = ref('');
const queryLoading = ref(false);
const chatHistory = ref([]);

const quickPrompts = [
    'Summarize this quarter\'s performance',
    'Which directorates are underperforming?',
    'What KPIs are at risk of missing targets?',
    'Compare directorate performance',
    'What are the top 3 risks right now?',
    'Suggest improvement areas',
];

function askQuestion(question) {
    queryInput.value = question;
    submitQuery();
}

async function submitQuery() {
    const question = queryInput.value.trim();
    if (!question) return;

    chatHistory.value.push({ role: 'user', content: question });
    queryInput.value = '';
    queryLoading.value = true;
    queryTimer = startTimer(elapsedQuery);

    try {
        const data = await aiPost('/api/ai/query', { question });

        // Async result comes as the raw AI output; sync result is wrapped
        const result = data.result || data;

        if (result && (result.answer || data.success)) {
            chatHistory.value.push({
                role: 'assistant',
                content: result.answer || 'I couldn\'t generate an answer.',
                dataPoints: result.data_points || [],
                suggestions: result.follow_up_suggestions || [],
            });
        } else {
            chatHistory.value.push({ role: 'assistant', content: data.message || 'Sorry, I couldn\'t process that request.' });
        }
    } catch (e) {
        chatHistory.value.push({ role: 'assistant', content: e.message || 'Failed to connect to AI service.' });
    } finally {
        queryTimer = stopTimer(queryTimer, elapsedQuery);
        queryLoading.value = false;
    }
}

function clearChat() {
    chatHistory.value = [];
}

// ── Executive Insights ──────────────────────────────────
const insights = ref(null);
const insightsLoading = ref(false);

async function loadInsights(fresh = false) {
    insightsLoading.value = true;
    insightsTimer = startTimer(elapsedInsights);

    try {
        const data = await aiPost('/api/ai/executive-insights', { fresh });

        // Async result is the raw insights object; sync is wrapped in { success, insights }
        if (data.insights) {
            insights.value = data.insights;
        } else if (data.summary) {
            // Async returns raw AI object directly
            insights.value = data;
        }
    } catch (e) {
        console.error('Failed to load insights', e);
    } finally {
        insightsTimer = stopTimer(insightsTimer, elapsedInsights);
        insightsLoading.value = false;
    }
}

function formatTime(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    return d.toLocaleString('en-ZM', { dateStyle: 'medium', timeStyle: 'short' });
}

onMounted(() => {
    if (props.aiAvailable) {
        loadInsights();
    }
});

onUnmounted(() => {
    if (queryTimer) clearInterval(queryTimer);
    if (insightsTimer) clearInterval(insightsTimer);
});
</script>
