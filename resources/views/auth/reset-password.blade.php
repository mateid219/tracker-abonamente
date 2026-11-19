@extends('layouts.app')

@section('title', 'Parolă nouă')

@section('content')
<div class="card auth-card">
    <h1>Setează o parolă nouă</h1>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autocomplete="username">
            @error('email') <p class="error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label for="password">Parolă nouă</label>
            <x-password-input name="password" autocomplete="new-password" autofocus />
            @error('password') <p class="error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label for="password_confirmation">Confirmă parola</label>
            <x-password-input name="password_confirmation" autocomplete="new-password" />
        </div>

        <button type="submit" class="btn btn-block">Salvează parola</button>
    </form>
</div>
@endsection
