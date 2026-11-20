import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

/**
 * Laravel Echo + Reverb (WebSockets).
 *
 * The connection settings come from <meta> tags rendered by the server (layouts/app.blade.php),
 * not from build-time variables, so the same build works on http://localhost:8080,
 * through the Cloudflare tunnel (https) and in local development.
 * When no host/port is configured, the browser connects to the page's own address:
 * in Docker, Nginx forwards /app/* to the Reverb container.
 */
const meta = (name) => document.querySelector(`meta[name="${name}"]`)?.content || null;

const key = meta('reverb-key');

if (key) {
    const secure = (meta('reverb-scheme') ?? window.location.protocol.replace(':', '')) === 'https';
    const port = Number(meta('reverb-port') ?? (window.location.port || (secure ? 443 : 80)));

    window.Pusher = Pusher;

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key,
        wsHost: meta('reverb-host') ?? window.location.hostname,
        wsPort: port,
        wssPort: port,
        forceTLS: secure,
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: { 'X-CSRF-TOKEN': meta('csrf-token') ?? '' },
        },
    });
}
