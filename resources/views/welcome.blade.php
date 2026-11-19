@extends('layouts.app')

@section('title', 'Acasă')

@section('content')
    <section class="hero">
        <h1>Ține evidența abonamentelor tale</h1>
        <p class="muted">Toate abonamentele, costurile și datele de plată într-un singur loc.</p>

        <div class="hero-actions">
            @auth
                <a href="{{ route('dashboard') }}" class="btn">Mergi la dashboard</a>
            @else
                <a href="{{ route('register') }}" class="btn">Creează cont</a>
                <a href="{{ route('login') }}" class="btn btn-secondary">Autentificare</a>
            @endauth
        </div>
    </section>
@endsection
