export function useAiTasks() {
    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content;

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

    return {
        aiPost,
        pollTask,
    };
}
