@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div x-data="subscriptionsDashboard(@js($subscriptions), @js([
        'store' => route('subscriptions.store'),
        'status' => route('subscriptions.status', '__ID__'),
        'destroy' => route('subscriptions.destroy', '__ID__'),
        'edit' => route('subscriptions.edit', '__ID__'),
     ]))"
     @realtime:subscription-created.window="upsert($event.detail)"
     @realtime:subscription-updated.window="upsert($event.detail)"
     @realtime:subscription-deleted.window="remove($event.detail.id)"
     @realtime:connection.window="connection = $event.detail">

    <div class="page-header">
        <h1>Salut, <span data-user-first-name>{{ auth()->user()->first_name }}</span>!</h1>

        <span class="live-indicator" :class="'is-' + connection" x-cloak
              :title="connection === 'connected' ? 'Lista se actualizează automat din alte ferestre' : 'Actualizarea automată nu este disponibilă'">
            <span class="live-dot" aria-hidden="true"></span>
            <span x-text="{ connected: 'Live', connecting: 'Se conectează…', initialized: 'Se conectează…' }[connection] ?? 'Offline'"></span>
        </span>
    </div>

    {{-- Cost Lunar Total: sum of Active subscriptions, above the list --}}
    <section class="card total-card" aria-labelledby="total-label">
        <p id="total-label" class="total-label">Cost Lunar Total</p>
        <p class="total-value" aria-live="polite">
            <span x-text="format(total)">{{ number_format($subscriptions->where('status', 'active')->sum(fn ($s) => (float) $s['monthly_cost']), 2, ',', '.') }}</span>
            <span class="total-currency">lei</span>
        </p>
        <p class="muted small">
            <span x-text="activeCount"></span> active
            <template x-if="pausedCount > 0">
                <span>· <span x-text="pausedCount"></span> în pauză (nu sunt incluse)</span>
            </template>
        </p>
    </section>

    {{-- Add a subscription --}}
    <section class="card">
        <div class="section-header">
            <h2>Adaugă abonament</h2>
            <button type="button" class="btn-link" @click="showForm = !showForm; if (showForm) $nextTick(() => $refs.name.focus())"
                    :aria-expanded="showForm" aria-controls="add-form"
                    x-text="showForm ? 'Ascunde' : 'Arată formularul'"></button>
        </div>

        <form id="add-form" x-show="showForm" x-cloak @submit.prevent="add()" class="add-form" novalidate>
            <div class="field">
                <label for="sub-name">Nume</label>
                <input id="sub-name" x-ref="name" type="text" x-model="form.name" placeholder="ex. Netflix" maxlength="100" required>
                <p class="error" x-show="errors.name" x-text="errors.name"></p>
            </div>

            <div class="field">
                <label for="sub-cost">Cost Lunar</label>
                <input id="sub-cost" type="text" inputmode="decimal" x-model="form.monthly_cost" placeholder="ex. 15.99" required>
                <p class="error" x-show="errors.monthly_cost" x-text="errors.monthly_cost"></p>
            </div>

            <div class="field">
                <label for="sub-status">Status</label>
                <select id="sub-status" x-model="form.status">
                    <option value="active">Activ</option>
                    <option value="paused">Pauză</option>
                </select>
                <p class="error" x-show="errors.status" x-text="errors.status"></p>
            </div>

            <div class="add-form-submit">
                <button type="submit" class="btn" :disabled="saving" x-text="saving ? 'Se salvează...' : 'Adaugă'">Adaugă</button>
            </div>
        </form>
    </section>

    <p class="alert alert-error" role="alert" x-show="error" x-cloak>
        <span x-text="error"></span>
        <button type="button" class="alert-close" @click="error = null" aria-label="Închide">&times;</button>
    </p>

    {{-- List of subscriptions --}}
    <section class="card list-card" aria-labelledby="list-title">
        <h2 id="list-title" class="list-title">Abonamentele mele <span class="muted" x-text="'(' + items.length + ')'"></span></h2>

        <p class="muted empty-state" x-show="items.length === 0" x-cloak>
            Nu ai încă niciun abonament. Adaugă primul din formularul de mai sus.
        </p>

        <ul class="subscription-list" x-show="items.length > 0">
            <template x-for="item in items" :key="item.id">
                <li class="subscription" :class="{ 'is-paused': item.status === 'paused' }" x-transition.opacity>
                    <div class="subscription-main">
                        <span class="subscription-name" x-text="item.name"></span>
                        <span class="subscription-cost"><span x-text="format(item.monthly_cost)"></span> lei / lună</span>
                    </div>

                    <label class="switch" :title="item.status === 'active' ? 'Pune pe pauză' : 'Activează'">
                        <input type="checkbox" role="switch"
                               :checked="item.status === 'active'"
                               :disabled="!!busy[item.id]"
                               :aria-label="'Status ' + item.name"
                               @change="toggle(item)">
                        <span class="switch-track" aria-hidden="true"></span>
                        <span class="switch-label" x-text="item.status === 'active' ? 'Activ' : 'Pauză'"></span>
                    </label>

                    <div class="subscription-actions">
                        <a :href="url(@js(route('subscriptions.edit', '__ID__')), item.id)" class="btn btn-secondary btn-small">Editează</a>
                        <button type="button" class="btn btn-danger btn-small" :disabled="!!busy[item.id]" @click="destroy(item)">Șterge</button>
                    </div>
                </li>
            </template>
        </ul>
    </section>
</div>
@endsection
