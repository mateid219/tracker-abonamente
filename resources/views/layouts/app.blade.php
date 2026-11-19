<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header">
        <nav class="container nav" x-data="{ open: false }">
            <a href="{{ route('home') }}" class="nav-brand">{{ config('app.name') }}</a>

            <button type="button" class="nav-toggle" @click="open = !open" :aria-expanded="open" aria-controls="nav-links">
                Meniu
            </button>

            <div id="nav-links" class="nav-links" :class="{ 'is-open': open }">
                @auth
                    <a href="{{ route('dashboard') }}" @class(['is-active' => request()->routeIs('dashboard')])>Dashboard</a>
                    <a href="{{ route('settings') }}" @class(['is-active' => request()->routeIs('settings')])>Setări</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-link">Ieșire</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" @class(['is-active' => request()->routeIs('login')])>Autentificare</a>
                    <a href="{{ route('register') }}" class="btn btn-small">Cont nou</a>
                @endauth
            </div>
        </nav>
    </header>

    <main class="container">
        @include('partials.status')
        @yield('content')
    </main>
</body>
</html>
