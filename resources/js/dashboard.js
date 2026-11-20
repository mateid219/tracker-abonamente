import { api } from './http';

/**
 * Dashboard: list of subscriptions, "Cost Lunar Total" and the Activ / Pauză toggle.
 *
 * Usage: x-data="subscriptionsDashboard(@js($subscriptions), @js($urls))"
 *  - The total sums the Active subscriptions and recalculates instantly on every change.
 *  - Add / toggle / delete are saved with fetch() (JSON) without reloading the page.
 *  - Changes made in another window arrive over WebSockets (realtime.js) and update this list.
 */
const money = new Intl.NumberFormat('ro-RO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const toCents = (value) => Math.round(Number(value) * 100);

export default (initial = [], urls = {}) => ({
    items: initial,
    form: { name: '', monthly_cost: '', status: 'active' },
    errors: {},
    error: null,
    saving: false,
    busy: {},
    showForm: initial.length === 0,
    connection: 'connecting',

    init() {
        this.connection = window.Echo?.connector?.pusher?.connection?.state ?? 'unavailable';
    },

    // ---- Total ---------------------------------------------------------------

    get total() {
        const cents = this.items
            .filter((item) => item.status === 'active')
            .reduce((sum, item) => sum + toCents(item.monthly_cost), 0);
        return cents / 100;
    },

    get activeCount() {
        return this.items.filter((item) => item.status === 'active').length;
    },

    get pausedCount() {
        return this.items.length - this.activeCount;
    },

    format(value) {
        return money.format(Number(value));
    },

    // ---- List helpers (also used for WebSocket events) -----------------------

    upsert(subscription) {
        const index = this.items.findIndex((item) => item.id === subscription.id);
        if (index === -1) {
            this.items.unshift(subscription);
        } else {
            this.items.splice(index, 1, subscription);
        }
    },

    remove(id) {
        this.items = this.items.filter((item) => item.id !== id);
    },

    url(template, id) {
        return template.replace('__ID__', id);
    },

    // ---- Actions ---------------------------------------------------------------

    async add() {
        this.saving = true;
        this.errors = {};
        this.error = null;

        try {
            const data = await api(urls.store, { method: 'POST', body: this.form });
            this.upsert(data.subscription);
            this.form = { name: '', monthly_cost: '', status: 'active' };
            this.$nextTick(() => this.$refs.name?.focus());
        } catch (e) {
            this.errors = e.fieldErrors ?? {};
            if (Object.keys(this.errors).length === 0) this.error = e.message;
        } finally {
            this.saving = false;
        }
    },

    async toggle(item) {
        const previous = item.status;
        item.status = previous === 'active' ? 'paused' : 'active'; // total updates immediately
        this.busy[item.id] = true;
        this.error = null;

        try {
            const data = await api(this.url(urls.status, item.id), { method: 'PATCH', body: { status: item.status } });
            this.upsert(data.subscription);
        } catch (e) {
            item.status = previous; // undo on failure
            this.error = e.message;
            if (e.status === 404) this.remove(item.id);
        } finally {
            delete this.busy[item.id];
        }
    },

    async destroy(item) {
        if (!window.confirm(`Ștergi abonamentul „${item.name}”?`)) return;

        this.busy[item.id] = true;
        this.error = null;

        try {
            await api(this.url(urls.destroy, item.id), { method: 'DELETE' });
            this.remove(item.id);
        } catch (e) {
            if (e.status === 404) {
                this.remove(item.id);
            } else {
                this.error = e.message;
            }
        } finally {
            delete this.busy[item.id];
        }
    },
});
