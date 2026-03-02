import { ref, onMounted, onUnmounted } from 'vue';

export function useEcho() {
    const connected = ref(false);
    let echoInstance = null;

    const connect = async () => {
        try {
            const { default: Echo } = await import('laravel-echo');
            const { default: Pusher } = await import('pusher-js');

            window.Pusher = Pusher;

            echoInstance = new Echo({
                broadcaster: 'reverb',
                key: import.meta.env.VITE_REVERB_APP_KEY,
                wsHost: import.meta.env.VITE_REVERB_HOST,
                wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
                wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
                forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
                enabledTransports: ['ws', 'wss'],
            });

            connected.value = true;
        } catch (err) {
            console.warn('WebSocket connection failed:', err);
            connected.value = false;
        }
    };

    const listenForUpdates = (callback) => {
        if (echoInstance) {
            echoInstance.channel('dashboard')
                .listen('.simulation.updated', (event) => {
                    callback(event);
                });
        }
    };

    const disconnect = () => {
        if (echoInstance) {
            echoInstance.disconnect();
            connected.value = false;
        }
    };

    return { connected, connect, listenForUpdates, disconnect };
}
