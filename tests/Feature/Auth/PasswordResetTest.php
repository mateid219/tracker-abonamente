<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('forgot password screen can be rendered', function () {
    $this->get(route('password.request'))->assertOk();
});

test('reset password link can be requested and used', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
        $this->get(route('password.reset', ['token' => $notification->token, 'email' => $user->email]))
            ->assertOk()
            ->assertSee($notification->token);

        $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'Parola-Noua-456',
            'password_confirmation' => 'Parola-Noua-456',
        ])->assertSessionHasNoErrors()->assertRedirect(route('login'));

        return true;
    });

    expect(Hash::check('Parola-Noua-456', $user->fresh()->password))->toBeTrue();
});

test('password cannot be reset with an invalid token', function () {
    $user = User::factory()->create();

    $this->post(route('password.update'), [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'Parola-Noua-456',
        'password_confirmation' => 'Parola-Noua-456',
    ])->assertSessionHasErrors('email');

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});
