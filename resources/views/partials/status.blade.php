@php
    $messages = [
        'profile-information-updated' => 'Profilul a fost actualizat.',
        'password-updated' => 'Parola a fost schimbată.',
        'verification-link-sent' => 'Am trimis un nou link de verificare pe email.',
        'two-factor-authentication-enabled' => 'Scanează codul QR și confirmă cu un cod din aplicație.',
        'two-factor-authentication-confirmed' => 'Autentificarea în doi pași este activă.',
        'two-factor-authentication-disabled' => 'Autentificarea în doi pași a fost dezactivată.',
        'recovery-codes-generated' => 'Au fost generate coduri de recuperare noi.',
        'other-sessions-logged-out' => 'Celelalte dispozitive au fost deconectate.',
    ];

    $status = session('status');
    $message = $status ? ($messages[$status] ?? $status) : null;
@endphp

@if ($message)
    <div class="alert alert-success" role="status"
         x-data="{ show: true }" x-show="show" x-transition.opacity>
        <span>{{ $message }}</span>
        <button type="button" class="alert-close" @click="show = false" aria-label="Închide">&times;</button>
    </div>
@endif
