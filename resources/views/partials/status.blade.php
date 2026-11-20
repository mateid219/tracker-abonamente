@php
    $messages = [
        'profile-information-updated' => 'Profilul a fost actualizat.',
        'password-updated' => 'Parola a fost schimbată.',
        'verification-link-sent' => 'Am trimis un nou link de verificare pe email.',
        'other-sessions-logged-out' => 'Celelalte dispozitive au fost deconectate.',
        'user-created' => 'Contul a fost creat.',
        'user-updated' => 'Contul a fost actualizat.',
        'user-deleted' => 'Contul a fost șters. Îl poți restaura din lista „Șterse”.',
        'user-restored' => 'Contul a fost restaurat.',
        'subscription-created' => 'Abonamentul a fost adăugat.',
        'subscription-updated' => 'Abonamentul a fost actualizat.',
        'subscription-deleted' => 'Abonamentul a fost șters.',
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
