<?php

use App\Models\User;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use PragmaRX\Google2FA\Google2FA;

function userWithConfirmedTwoFactor(): User
{
    $user = User::factory()->create();
    app(EnableTwoFactorAuthentication::class)($user);
    $user->forceFill(['two_factor_confirmed_at' => now()])->save();

    return $user->fresh();
}

test('enabling two factor authentication requires password confirmation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('two-factor.enable'))
        ->assertRedirect(route('password.confirm'));

    expect($user->fresh()->two_factor_secret)->toBeNull();
});

test('starting two factor setup asks for the password, then resumes on the settings page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('settings.two-factor.start'))
        ->assertRedirect(route('password.confirm'));

    $this->post(route('password.confirm'), ['password' => 'password'])
        ->assertRedirect(route('settings.two-factor.start'));

    $this->get(route('settings.two-factor.start'))
        ->assertRedirect(route('settings').'#two-factor')
        ->assertSessionHas('enable-two-factor', true);

    $this->get(route('settings'))->assertSee('Continuă activarea');
});

test('two factor authentication can be enabled and confirmed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post(route('two-factor.enable'))
        ->assertSessionHas('status', 'two-factor-authentication-enabled');

    $user->refresh();
    expect($user->two_factor_secret)->not->toBeNull()
        ->and($user->hasEnabledTwoFactorAuthentication())->toBeFalse();

    $this->get(route('settings'))->assertOk()->assertSee('<svg', false);

    $code = app(Google2FA::class)->getCurrentOtp(decrypt($user->two_factor_secret));

    $this->post(route('two-factor.confirm'), ['code' => $code])
        ->assertSessionHas('status', 'two-factor-authentication-confirmed');

    expect($user->fresh()->hasEnabledTwoFactorAuthentication())->toBeTrue();
});

test('users with two factor enabled must pass the challenge to log in', function () {
    $user = userWithConfirmedTwoFactor();

    $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('two-factor.login'));

    $this->assertGuest();

    $this->get(route('two-factor.login'))->assertOk();

    $this->post(route('two-factor.login'), ['code' => '000000'])
        ->assertRedirect(route('two-factor.login'));
    $this->assertGuest();

    $code = app(Google2FA::class)->getCurrentOtp(decrypt($user->two_factor_secret));

    $this->post(route('two-factor.login'), ['code' => $code])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('a recovery code can be used once', function () {
    $user = userWithConfirmedTwoFactor();
    $recoveryCode = $user->recoveryCodes()[0];

    $this->post(route('login'), ['email' => $user->email, 'password' => 'password']);
    $this->post(route('two-factor.login'), ['recovery_code' => $recoveryCode])
        ->assertRedirect(route('dashboard'));

    expect($user->fresh()->recoveryCodes())->not->toContain($recoveryCode);
});
