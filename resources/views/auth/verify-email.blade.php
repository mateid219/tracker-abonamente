@extends('layouts.app')

@section('title', 'Verifică emailul')

@section('content')
<div class="card auth-card">
    <h1>Verifică-ți adresa de email</h1>
    <p class="muted">
        Ți-am trimis un link de confirmare pe <strong>{{ auth()->user()->email }}</strong>.
        Deschide-l pentru a-ți activa contul.
    </p>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-block">Retrimite emailul</button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="auth-links">
        @csrf
        <button type="submit" class="btn-link">Ieșire</button>
    </form>
</div>
@endsection
