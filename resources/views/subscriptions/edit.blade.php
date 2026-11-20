@extends('layouts.app')

@section('title', 'Editează abonamentul')

@section('content')
<p class="breadcrumb"><a href="{{ route('dashboard') }}">&larr; Dashboard</a></p>

<div class="card form-card">
    <h1>Editează abonamentul</h1>

    <form method="POST" action="{{ route('subscriptions.update', $subscription) }}" x-data="{ loading: false }" @submit="loading = true">
        @csrf
        @method('PUT')

        <div class="field">
            <label for="name">Nume</label>
            <input id="name" type="text" name="name" value="{{ old('name', $subscription->name) }}" maxlength="100" required autofocus>
            @error('name') <p class="error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label for="monthly_cost">Cost Lunar</label>
            <input id="monthly_cost" type="text" inputmode="decimal" name="monthly_cost"
                   value="{{ old('monthly_cost', $subscription->monthly_cost) }}" required>
            @error('monthly_cost') <p class="error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status">
                @foreach (\App\Enums\SubscriptionStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $subscription->status->value) === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
            @error('status') <p class="error">{{ $message }}</p> @enderror
        </div>

        <div class="button-row">
            <button type="submit" class="btn" :disabled="loading">Salvează</button>
            <a href="{{ route('dashboard') }}" class="btn-link">Renunță</a>
        </div>
    </form>
</div>
@endsection
