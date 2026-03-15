<template>
    <div>
        <!-- Overlay is handled by AppLayout -->
        <aside
            :class="[
                'fixed inset-y-0 right-0 z-50 w-80 sm:w-96 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 transform transition-transform duration-300 no-print',
                open ? 'translate-x-0' : 'translate-x-full',
            ]"
        >
            <div class="h-16 px-4 sm:px-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">AI Assistant</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        {{ scopeLabel }}
                        <span v-if="provider" class="ml-1">· {{ provider }}</span>
                    </p>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    <Link
                        href="/ai"
                        class="text-xs font-medium text-zesco-700 hover:text-zesco-800 dark:text-zesco-400 dark:hover:text-zesco-300"
                        title="Open global AI Insights"
                    >
                        AI Insights
                    </Link>

                    <button
                        type="button"
                        @click="$emit('close')"
                        class="p-2 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                        title="Close"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="h-[calc(100vh-4rem)] flex flex-col">
                <div class="flex-1 overflow-y-auto p-3 sm:p-4 space-y-3">
                    <div v-if="!open" class="hidden"></div>

                    <div v-if="statusError" class="text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-3 py-2 rounded-lg">
                        {{ statusError }}
                    </div>

                    <div v-if="chat.length === 0" class="text-sm text-gray-500 dark:text-gray-400">
                        Ask about the current PP view. For cross-directorate questions, open AI Insights.
                    </div>

                    <div v-for="(m, idx) in chat" :key="idx" class="space-y-1">
                        <p class="text-[11px] uppercase tracking-wide"
                           :class="m.role === 'user' ? 'text-gray-500 dark:text-gray-400' : 'text-zesco-700 dark:text-zesco-400'">
                            {{ m.role === 'user' ? 'You' : 'AI' }}
                        </p>
                        <div
                            class="text-sm leading-relaxed px-3 py-2 rounded-lg border"
                            :class="m.role === 'user'
                                ? 'bg-gray-50 dark:bg-gray-700/30 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white'
                                : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white'"
                        >
                            <p class="whitespace-pre-wrap break-words">{{ m.content }}</p>

                            <div v-if="m.dataPoints && m.dataPoints.length" class="mt-2">
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase tracking-wide">Data points</p>
                                <ul class="text-xs text-gray-600 dark:text-gray-300 list-disc pl-5 mt-1 space-y-0.5">
                                    <li v-for="(dp, i) in m.dataPoints" :key="i">{{ dp }}</li>
                                </ul>
                            </div>

                            <div v-if="m.suggestions && m.suggestions.length" class="mt-2">
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase tracking-wide">Try asking</p>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    <button
                                        v-for="(s, i) in m.suggestions"
                                        :key="i"
                                        type="button"
                                        class="text-xs px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                                        @click="askSuggestion(s)"
                                    >
                                        {{ s }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 p-3 sm:p-4">
                    <form class="relative" @submit.prevent="submit">
                        <textarea
                            v-model="input"
                            rows="2"
                            class="w-full resize-none rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-white px-3 py-2 pr-12 focus:outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white"
                            placeholder="Ask AI about this PP view..."
                            :disabled="loading"
                            @keydown.enter.exact.prevent="submit"
                            @keydown.enter.shift.stop
                        ></textarea>

                        <button
                            v-if="input.trim().length > 0"
                            type="submit"
                            class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full flex items-center justify-center transition disabled:opacity-50 disabled:cursor-not-allowed bg-gray-900 hover:bg-black text-white dark:bg-white dark:hover:bg-gray-100 dark:text-gray-900"
                            :disabled="loading"
                            title="Send"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m0 0l-7 7m7-7l7 7" />
                            </svg>
                        </button>
                    </form>
                    <div class="mt-2 flex items-center justify-between">
                        <button
                            type="button"
                            class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                            @click="clear"
                            :disabled="loading || chat.length === 0"
                        >
                            Clear
                        </button>
                        <p v-if="loading" class="text-xs text-gray-500 dark:text-gray-400">Thinking…</p>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useAiTasks } from '@/Composables/useAiTasks';

const props = defineProps({
    open: { type: Boolean, default: false },
    scope: { type: Object, default: null },
});

defineEmits(['close']);

const { aiPost } = useAiTasks();

const input = ref('');
const loading = ref(false);
const chat = ref([]);
const provider = ref(null);
const statusError = ref('');

const scopeLabel = computed(() => {
    const t = props.scope?.type;
    if (!t) return 'No scope';
    if (t === 'pp_project') return 'PP · Project';
    if (t === 'pp_grid_studies') return 'PP · Grid Studies';
    if (t === 'pp_explorer') return 'PP · Explorer';
    return 'PP · Portfolio';
});

async function loadStatus() {
    try {
        const resp = await fetch('/ai/status', { headers: { 'Accept': 'application/json' } });
        if (!resp.ok) return;
        const data = await resp.json();
        provider.value = data.provider || null;
    } catch {
        // optional
    }
}

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            statusError.value = '';
            loadStatus();
        }
    }
);

function clear() {
    chat.value = [];
    input.value = '';
}

function askSuggestion(s) {
    input.value = s;
    submit();
}

function payloadHistory() {
    return chat.value
        .filter(m => m && (m.role === 'user' || m.role === 'assistant'))
        .map(m => ({ role: m.role, content: m.content }))
        .slice(-12);
}

async function submit() {
    const q = input.value.trim();
    if (!q || loading.value) return;

    if (!props.scope) {
        statusError.value = 'AI scope missing for this page.';
        return;
    }

    chat.value.push({ role: 'user', content: q });
    input.value = '';
    loading.value = true;

    try {
        const data = await aiPost('/api/ai/pp/query', {
            question: q,
            scope: props.scope,
            history: payloadHistory(),
        });

        const result = data?.result || data;

        if (result && result.answer) {
            chat.value.push({
                role: 'assistant',
                content: result.answer,
                dataPoints: result.data_points || [],
                suggestions: result.follow_up_suggestions || [],
            });
        } else {
            chat.value.push({
                role: 'assistant',
                content: data?.message || 'Sorry, I could not generate an answer.',
            });
        }
    } catch (e) {
        chat.value.push({ role: 'assistant', content: e?.message || 'Failed to connect to AI service.' });
    } finally {
        loading.value = false;
    }
}
</script>
