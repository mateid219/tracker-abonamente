@extends('layouts.app')

@section('title', 'Resetare parolă')

@section('content')
<div class="card auth-card">
    <h1>Ai uitat parola?</h1>
    <p class="muted">Introdu adresa de email și îți trimitem un link pentru a seta o parolă nouă.</p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email') <p class="error">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn btn-block">Trimite link de resetare</button>
    </form>

    <p class="auth-links"><a href="{{ route('login') }}">Înapoi la autentificare</a></p>
</div>
@endsection
