@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1>Salut, {{ auth()->user()->name }}!</h1>

    <div class="card">
        <p>Ești autentificat. Aici vor apărea abonamentele tale.</p>

        @unless (auth()->user()->hasEnabledTwoFactorAuthentication())
            <p class="muted">
                Recomandare: activează <a href="{{ route('settings') }}#two-factor">autentificarea în doi pași</a>
                pentru un cont mai sigur.
            </p>
        @endunless
    </div>
@endsection
