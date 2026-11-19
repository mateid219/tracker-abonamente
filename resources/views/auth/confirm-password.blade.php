@extends('layouts.app')

@section('title', 'Confirmă parola')

@section('content')
<div class="card auth-card">
    <h1>Confirmă parola</h1>
    <p class="muted">Aceasta este o zonă protejată. Introdu parola pentru a continua.</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="field">
            <label for="password">Parolă</label>
            <x-password-input name="password" autofocus />
            @error('password') <p class="error">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn btn-block">Confirmă</button>
    </form>
</div>
@endsection
