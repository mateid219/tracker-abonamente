/**
 * Listens on the user's private WebSocket channels and turns server events into
 * browser events (`realtime:*` on window). Alpine components react to them with
 * @realtime:subscription-created.window="..." etc., so they stay independent of Echo.
 */
const meta = (name) => document.querySelector(`meta[name="${name}"]`)?.content || null;

const emit = (name, detail) => window.dispatchEvent(new CustomEvent(`realtime:${name}`, { detail }));

function updateNameOnPage(user) {
    document.querySelectorAll('[data-user-first-name]').forEach((el) => (el.textContent = user.first_name));
    document.querySelectorAll('[data-user-full-name]').forEach((el) => {
        el.textContent = `${user.first_name} ${user.last_name}`.trim();
    });
}

export function startRealtime() {
    const userId = meta('user-id');
    if (!userId || !window.Echo) return;

    window.Echo.private(`App.Models.User.${userId}`)
        // Subscriptions: dashboard list and total
        .listen('.subscription.created', (e) => emit('subscription-created', e.subscription))
        .listen('.subscription.updated', (e) => emit('subscription-updated', e.subscription))
        .listen('.subscription.deleted', (e) => emit('subscription-deleted', e))

        // Account: name changed (e.g. by an admin), role or verification changed, account deleted
        .listen('.account.updated', (e) => {
            const roleChanged = String(e.user.is_admin) !== String(meta('user-is-admin') === '1');
            if (roleChanged || !e.user.verified) {
                window.location.reload();
                return;
            }
            updateNameOnPage(e.user);
            emit('account-updated', e.user);
        })
        .listen('.account.deleted', () => window.location.reload())

        // Signed out remotely (new password, "log out other devices"): reload -> login page.
        // The window whose session was kept recognises itself by the session hash and stays.
        .listen('.sessions.revoked', (e) => {
            if (!e.except_session || e.except_session !== meta('session-hash')) {
                window.location.reload();
            }
        });

    if (meta('user-is-admin') === '1') {
        window.Echo.private('admin.users')
            .listen('.account.updated', (e) => emit('admin-user-updated', e.user))
            .listen('.account.deleted', (e) => emit('admin-user-deleted', e))
            .listen('.account.created', (e) => emit('admin-user-created', e.user));
    }

    // Connection state, shown as a small indicator on the dashboard.
    const connection = window.Echo.connector?.pusher?.connection;
    if (connection) {
        const report = () => emit('connection', connection.state);
        connection.bind('state_change', report);
        report();
    }
}
