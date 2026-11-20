{{-- Shared fields for create and edit. Expects: $user (User), $isEdit (bool) --}}
@php($isSelf = $user->exists && $user->is(auth()->user()))

<div class="field">
    <label for="name">Nume</label>
    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required autofocus autocomplete="off">
    @error('name') <p class="error">{{ $message }}</p> @enderror
</div>

<div class="field">
    <label for="email">Email</label>
    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="off">
    @error('email') <p class="error">{{ $message }}</p> @enderror
</div>

<div class="field">
    <label for="password">{{ $isEdit ? 'Parolă nouă' : 'Parolă' }}</label>
    <x-password-input name="password" autocomplete="new-password" :required="! $isEdit" />
    @if ($isEdit)
        <p class="hint">Lasă gol pentru a păstra parola actuală. O parolă nouă deconectează contul de pe toate dispozitivele.</p>
    @else
        <p class="hint">Minimum 10 caractere, litere mari și mici și cel puțin o cifră.</p>
    @endif
    @error('password') <p class="error">{{ $message }}</p> @enderror
</div>

<div class="field">
    <label for="password_confirmation">Confirmă parola</label>
    <x-password-input name="password_confirmation" autocomplete="new-password" :required="! $isEdit" />
</div>

<fieldset class="field checkbox-group">
    <legend class="visually-hidden">Opțiuni</legend>

    {{-- The hidden "0" is sent when the checkbox is unchecked. --}}
    <input type="hidden" name="email_verified" value="0">
    <div class="checkbox">
        <input id="email_verified" type="checkbox" name="email_verified" value="1" aria-describedby="email_verified_hint"
               @checked(old('email_verified', $user->exists ? $user->hasVerifiedEmail() : true))>
        <label for="email_verified">Email verificat</label>
    </div>
    <p id="email_verified_hint" class="hint">{{ $isEdit ? 'Dacă debifezi, utilizatorul trebuie să-și confirme din nou adresa.' : 'Dacă nu bifezi, utilizatorul primește un email de verificare.' }}</p>

    <input type="hidden" name="is_admin" value="0">
    <div class="checkbox">
        <input id="is_admin" type="checkbox" name="is_admin" value="1" @if ($isSelf) aria-describedby="is_admin_hint" @endif
               @checked(old('is_admin', $user->is_admin)) @disabled($isSelf)>
        <label for="is_admin">Administrator</label>
    </div>
    @if ($isSelf)
        <p id="is_admin_hint" class="hint">Nu îți poți retrage propriile drepturi de administrator.</p>
    @endif
</fieldset>
