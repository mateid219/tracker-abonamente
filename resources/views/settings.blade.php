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

        <div class="field">
            <label for="name">Nume</label>
            <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name">
            @error('name', 'updateProfileInformation') <p class="error">{{ $message }}</p> @enderror
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

{{-- Two-factor authentication --}}
<section class="card settings-section" id="two-factor">
    <h2>Autentificare în doi pași</h2>

    @if (! $user->two_factor_secret)
        <p class="muted">
            Pe lângă parolă, la autentificare vei introduce un cod din aplicația de pe telefon
            (Google Authenticator, Microsoft Authenticator, 1Password etc.).
        </p>

        @if (session('enable-two-factor'))
            {{-- Password was just confirmed: continue enabling automatically. --}}
            <form method="POST" action="{{ route('two-factor.enable') }}" x-data x-init="$el.submit()">
                @csrf
                <button type="submit" class="btn">Continuă activarea</button>
            </form>
        @else
            <a href="{{ route('settings.two-factor.start') }}" class="btn">Activează</a>
        @endif

    @elseif (! $user->two_factor_confirmed_at)
        <p><span class="badge badge-warning">În curs de activare</span></p>
        <p class="muted">Scanează codul QR cu aplicația de autentificare, apoi introdu codul de 6 cifre afișat.</p>

        <div class="qr-code">{!! $user->twoFactorQrCodeSvg() !!}</div>

        <details class="secret-key">
            <summary>Nu poți scana? Introdu cheia manual</summary>
            <code>{{ decrypt($user->two_factor_secret) }}</code>
        </details>

        <form method="POST" action="{{ route('two-factor.confirm') }}" class="inline-form">
            @csrf
            <div class="field">
                <label for="code">Cod de confirmare</label>
                <input id="code" type="text" name="code" inputmode="numeric" pattern="[0-9]*"
                       autocomplete="one-time-code" maxlength="6" required autofocus>
                @error('code', 'confirmTwoFactorAuthentication') <p class="error">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn">Confirmă</button>
        </form>

        <form method="POST" action="{{ route('two-factor.disable') }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-link">Anulează</button>
        </form>

    @else
        <p><span class="badge badge-success">Activă</span></p>

        @if (in_array(session('status'), ['two-factor-authentication-confirmed', 'recovery-codes-generated']))
            <div class="recovery-codes" x-data="{ copied: false }">
                <p>
                    <strong>Salvează aceste coduri de recuperare într-un loc sigur.</strong>
                    Fiecare poate fi folosit o singură dată dacă pierzi telefonul. Nu vor mai fi afișate.
                </p>
                <ul x-ref="codes">
                    @foreach ($user->recoveryCodes() as $code)
                        <li><code>{{ $code }}</code></li>
                    @endforeach
                </ul>
                <button type="button" class="btn btn-secondary btn-small"
                        @click="navigator.clipboard.writeText($refs.codes.innerText).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                        x-text="copied ? 'Copiat!' : 'Copiază codurile'">Copiază codurile</button>
            </div>
        @endif

        <div class="button-row">
            <form method="POST" action="{{ route('two-factor.recovery-codes') }}">
                @csrf
                <button type="submit" class="btn btn-secondary">Generează coduri de recuperare noi</button>
            </form>

            <form method="POST" action="{{ route('two-factor.disable') }}"
                  x-data @submit="if (! confirm('Sigur dezactivezi autentificarea în doi pași?')) $event.preventDefault()">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Dezactivează</button>
            </form>
        </div>
    @endif
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
