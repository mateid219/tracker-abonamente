@extends('layouts.app')

@section('title', 'Autentificare')

@section('content')
<div class="card auth-card">
    <h1>Autentificare</h1>

    <form method="POST" action="{{ route('login') }}" x-data="{ loading: false }" @submit="loading = true">
        @csrf

        <div class="field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email') <p class="error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <div class="field-row">
                <label for="password">Parolă</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="small">Ai uitat parola?</a>
                @endif
            </div>
            <x-password-input name="password" />
            @error('password') <p class="error">{{ $message }}</p> @enderror
        </div>

        <label class="checkbox">
            <input type="checkbox" name="remember" @checked(old('remember'))> Ține-mă minte
        </label>

        <button type="submit" class="btn btn-block" :disabled="loading">
            <span x-show="!loading">Intră în cont</span>
            <span x-show="loading" x-cloak>Se verifică...</span>
        </button>
    </form>

    @if (Route::has('register'))
        <p class="auth-links">Nu ai cont? <a href="{{ route('register') }}">Creează unul</a></p>
    @endif
</div>
@endsection
