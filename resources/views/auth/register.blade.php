@extends('layouts.app')

@section('title', 'Cont nou')

@section('content')
<div class="card auth-card">
    <h1>Cont nou</h1>

    <form method="POST" action="{{ route('register') }}"
          x-data="{ password: '', loading: false }" @submit="loading = true">
        @csrf

        <div class="field">
            <label for="name">Nume</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
            @error('name') <p class="error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
            @error('email') <p class="error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label for="password">Parolă</label>
            <x-password-input name="password" autocomplete="new-password" x-model="password" />
            <ul class="password-hints">
                <li :class="{ 'is-ok': password.length >= 10 }">Minimum 10 caractere</li>
                <li :class="{ 'is-ok': /[a-z]/.test(password) && /[A-Z]/.test(password) }">Litere mari și mici</li>
                <li :class="{ 'is-ok': /[0-9]/.test(password) }">Cel puțin o cifră</li>
            </ul>
            @error('password') <p class="error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label for="password_confirmation">Confirmă parola</label>
            <x-password-input name="password_confirmation" autocomplete="new-password" />
        </div>

        <button type="submit" class="btn btn-block" :disabled="loading">Creează cont</button>
    </form>

    <p class="auth-links">Ai deja cont? <a href="{{ route('login') }}">Autentifică-te</a></p>
</div>
@endsection
