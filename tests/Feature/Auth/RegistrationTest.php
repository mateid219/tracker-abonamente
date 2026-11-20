<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('registration screen can be rendered', function () {
    $this->get(route('register'))->assertOk()->assertSee('Cont nou');
});

test('new users can register and must verify their email', function () {
    Notification::fake();

    $this->post(route('register'), [
        'last_name' => 'Popescu',
        'first_name' => 'Ana',
        'email' => 'test@example.com',
        'password' => 'Parola-Sigura-123',
        'password_confirmation' => 'Parola-Sigura-123',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();

    $user = User::where('email', 'test@example.com')->firstOrFail();

    expect($user)->last_name->toBe('Popescu')->first_name->toBe('Ana');

    expect(Hash::check('Parola-Sigura-123', $user->password))->toBeTrue()
        ->and($user->password)->not->toBe('Parola-Sigura-123')
        ->and($user->hasVerifiedEmail())->toBeFalse();

    Notification::assertSentTo($user, VerifyEmail::class);

    $this->get(route('dashboard'))->assertRedirect(route('verification.notice'));
});

test('weak passwords are rejected', function (string $password) {
    $this->post(route('register'), [
        'last_name' => 'Popescu',
        'first_name' => 'Ana',
        'email' => 'test@example.com',
        'password' => $password,
        'password_confirmation' => $password,
    ])->assertSessionHasErrors('password');

    $this->assertGuest();
})->with([
    'too short' => 'Abc12',
    'no numbers' => 'ParolaFaraCifre',
    'no uppercase' => 'parola-mica-123',
]);

test('email addresses must be unique', function () {
    User::factory()->create(['email' => 'test@example.com']);

    $this->post(route('register'), [
        'last_name' => 'Popescu',
        'first_name' => 'Ana',
        'email' => 'test@example.com',
        'password' => 'Parola-Sigura-123',
        'password_confirmation' => 'Parola-Sigura-123',
    ])->assertSessionHasErrors('email');
});

test('last name and first name are required', function () {
    $this->post(route('register'), [
        'last_name' => '',
        'first_name' => '',
        'email' => 'test@example.com',
        'password' => 'Parola-Sigura-123',
        'password_confirmation' => 'Parola-Sigura-123',
    ])->assertSessionHasErrors(['last_name', 'first_name']);
});
