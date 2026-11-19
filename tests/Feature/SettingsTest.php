<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

test('settings page can be rendered', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('settings'))
        ->assertOk()
        ->assertSee('Profil')
        ->assertSee('Dispozitive conectate')
        ->assertDontSee('Autentificare în doi pași');
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('settings'))
        ->put(route('user-profile-information.update'), ['name' => 'Nume Nou', 'email' => $user->email])
        ->assertRedirect(route('settings'))
        ->assertSessionHas('status', 'profile-information-updated');

    expect($user->fresh()->name)->toBe('Nume Nou');
});

test('changing the email requires verifying it again', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('user-profile-information.update'), ['name' => $user->name, 'email' => 'nou@example.com']);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('password can be updated with the current password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('user-password.update'), [
        'current_password' => 'password',
        'password' => 'Parola-Noua-456',
        'password_confirmation' => 'Parola-Noua-456',
    ])->assertSessionHasNoErrors();

    expect(Hash::check('Parola-Noua-456', $user->fresh()->password))->toBeTrue();
});

test('password is not updated with a wrong current password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('user-password.update'), [
        'current_password' => 'wrong-password',
        'password' => 'Parola-Noua-456',
        'password_confirmation' => 'Parola-Noua-456',
    ])->assertSessionHasErrorsIn('updatePassword', 'current_password');

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

test('other sessions can be logged out with the current password', function () {
    config(['session.driver' => 'database']);

    $user = User::factory()->create();
    $other = User::factory()->create();
    $rememberToken = $user->remember_token;

    DB::table('sessions')->insert([
        ['id' => 'other-device', 'user_id' => $user->id, 'ip_address' => '10.0.0.1', 'user_agent' => 'Firefox/120', 'payload' => '', 'last_activity' => time()],
        ['id' => 'someone-else', 'user_id' => $other->id, 'ip_address' => '10.0.0.2', 'user_agent' => 'Chrome/120', 'payload' => '', 'last_activity' => time()],
    ]);

    $this->actingAs($user)
        ->delete(route('settings.sessions.destroy'), ['password' => 'wrong-password'])
        ->assertSessionHasErrorsIn('logoutOtherSessions', 'password');

    expect(DB::table('sessions')->where('id', 'other-device')->exists())->toBeTrue();

    $this->actingAs($user)
        ->delete(route('settings.sessions.destroy'), ['password' => 'password'])
        ->assertSessionHas('status', 'other-sessions-logged-out');

    expect(DB::table('sessions')->where('id', 'other-device')->exists())->toBeFalse()
        ->and(DB::table('sessions')->where('id', 'someone-else')->exists())->toBeTrue()
        ->and($user->fresh()->remember_token)->not->toBe($rememberToken);
});
