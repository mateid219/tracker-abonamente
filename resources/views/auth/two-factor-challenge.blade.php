@extends('layouts.app')

@section('title', 'Verificare în doi pași')

@section('content')
<div class="card auth-card" x-data="{ recovery: {{ $errors->has('recovery_code') ? 'true' : 'false' }} }">
    <h1>Verificare în doi pași</h1>

    <form method="POST" action="{{ route('two-factor.login') }}">
        @csrf

        <template x-if="!recovery">
            <div class="field">
                <p class="muted">Introdu codul de 6 cifre din aplicația de autentificare.</p>
                <label for="code">Cod</label>
                <input id="code" type="text" name="code" inputmode="numeric" pattern="[0-9]*"
                       autocomplete="one-time-code" maxlength="6" required x-init="$el.focus()">
            </div>
        </template>

        <template x-if="recovery">
            <div class="field">
                <p class="muted">Introdu unul dintre codurile de recuperare salvate.</p>
                <label for="recovery_code">Cod de recuperare</label>
                <input id="recovery_code" type="text" name="recovery_code" autocomplete="one-time-code" required x-init="$el.focus()">
            </div>
        </template>

        @error('code') <p class="error">{{ $message }}</p> @enderror
        @error('recovery_code') <p class="error">{{ $message }}</p> @enderror

        <button type="submit" class="btn btn-block">Verifică</button>
    </form>

    <p class="auth-links">
        <button type="button" class="btn-link" @click="recovery = !recovery"
                x-text="recovery ? 'Folosește codul din aplicație' : 'Folosește un cod de recuperare'"></button>
    </p>
</div>
@endsection
