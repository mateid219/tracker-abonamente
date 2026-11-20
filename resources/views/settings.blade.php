@extends('layouts.app')

@section('title', 'Setări cont')

@section('content')
<h1>Setări cont</h1>

{{-- Profile --}}
<section class="card settings-section">
    <h2>Profil</h2>

    <form method="POST" action="{{ route('user-profile-information.update') }}">
        @csrf
        @method('PUT')

        <div class="field-pair">
            <div class="field">
                <label for="last_name">Nume</label>
                <input id="last_name" type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" maxlength="100" required autocomplete="family-name">
                @error('last_name', 'updateProfileInformation') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label for="first_name">Prenume</label>
                <input id="first_name" type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" maxlength="100" required autocomplete="given-name">
                @error('first_name', 'updateProfileInformation') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
            <p class="hint">Dacă schimbi emailul, va trebui să confirmi noua adresă.</p>
            @error('email', 'updateProfileInformation') <p class="error">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn">Salvează</button>
    </form>
</section>

{{-- Password --}}
<section class="card settings-section">
    <h2>Schimbă parola</h2>

    <form method="POST" action="{{ route('user-password.update') }}">
        @csrf
        @method('PUT')

        <div class="field">
            <label for="current_password">Parola actuală</label>
            <x-password-input name="current_password" />
            @error('current_password', 'updatePassword') <p class="error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label for="new_password">Parola nouă</label>
            <x-password-input name="password" id="new_password" autocomplete="new-password" />
            @error('password', 'updatePassword') <p class="error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label for="new_password_confirmation">Confirmă parola nouă</label>
            <x-password-input name="password_confirmation" id="new_password_confirmation" autocomplete="new-password" />
        </div>

        <button type="submit" class="btn">Schimbă parola</button>
    </form>
</section>

{{-- Active sessions (stored in the PostgreSQL `sessions` table) --}}
<section class="card settings-section">
    <h2>Dispozitive conectate</h2>

    @if ($sessions->isNotEmpty())
        <ul class="session-list">
            @foreach ($sessions as $session)
                <li>
                    <strong>{{ $session->agent }}</strong>
                    <span class="muted">
                        {{ $session->ip_address }} ·
                        @if ($session->is_current_device)
                            <span class="badge badge-success">Acest dispozitiv</span>
                        @else
                            activ {{ $session->last_active }}
                        @endif
                    </span>
                </li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('settings.sessions.destroy') }}"
          x-data="{ open: {{ $errors->logoutOtherSessions->isNotEmpty() ? 'true' : 'false' }} }">
        @csrf
        @method('DELETE')

        <button type="button" class="btn btn-secondary" x-show="!open" @click="open = true">
            Deconectează celelalte dispozitive
        </button>

        <div x-show="open" x-cloak>
            <div class="field">
                <label for="logout_password">Confirmă cu parola</label>
                <x-password-input name="password" id="logout_password" x-bind:disabled="!open" />
                @error('password', 'logoutOtherSessions') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="button-row">
                <button type="submit" class="btn btn-danger">Deconectează</button>
                <button type="button" class="btn-link" @click="open = false">Renunță</button>
            </div>
        </div>
    </form>
</section>
@endsection
