<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

function insertSession(User $user, string $id): void
{
    DB::table('sessions')->insert([
        'id' => $id, 'user_id' => $user->id, 'ip_address' => '10.0.0.1',
        'user_agent' => 'Firefox/120', 'payload' => '', 'last_activity' => time(),
    ]);
}

// --- Access ------------------------------------------------------------------

test('guests are redirected to login', function () {
    $this->get(route('admin.users.index'))->assertRedirect(route('login'));
});

test('regular users cannot access any admin page', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.users.create'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.users.edit', $other))->assertForbidden();
    $this->actingAs($user)->post(route('admin.users.store'), [])->assertForbidden();
    $this->actingAs($user)->put(route('admin.users.update', $other), [])->assertForbidden();
    $this->actingAs($user)->delete(route('admin.users.destroy', $other))->assertForbidden();

    expect(User::find($other->id))->not->toBeNull();
});

test('the admin link is only shown to admins', function () {
    $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertDontSee('Administrare');
    $this->actingAs(User::factory()->admin()->create())->get(route('dashboard'))->assertSee('Administrare');
});

// --- Read ----------------------------------------------------------------------

test('admins see the list of users', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['name' => 'Ana Popescu', 'email' => 'ana@example.test']);

    $this->actingAs($admin)->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Ana Popescu')
        ->assertSee('ana@example.test');
});

test('admins can search users by name or email', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'Ana Popescu', 'email' => 'ana@example.test']);
    User::factory()->create(['name' => 'Ion Ionescu', 'email' => 'ion@example.test']);

    $this->actingAs($admin)->get(route('admin.users.index', ['search' => 'popescu']))
        ->assertSee('Ana Popescu')
        ->assertDontSee('Ion Ionescu');

    $this->actingAs($admin)->get(route('admin.users.index', ['search' => 'ion@']))
        ->assertSee('Ion Ionescu')
        ->assertDontSee('Ana Popescu');
});

// --- Create --------------------------------------------------------------------

test('admins can create a verified user', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Cont Nou',
        'email' => 'Nou@Example.TEST',
        'password' => 'Parola-Sigura-123',
        'password_confirmation' => 'Parola-Sigura-123',
        'email_verified' => '1',
        'is_admin' => '0',
    ])->assertRedirect(route('admin.users.index'))->assertSessionHas('status', 'user-created');

    $user = User::where('email', 'nou@example.test')->firstOrFail();

    expect($user->hasVerifiedEmail())->toBeTrue()
        ->and($user->is_admin)->toBeFalse()
        ->and(Hash::check('Parola-Sigura-123', $user->password))->toBeTrue();

    Notification::assertNothingSent();
});

test('an unverified user created by an admin receives a verification email', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Cont Nou',
        'email' => 'nou@example.test',
        'password' => 'Parola-Sigura-123',
        'password_confirmation' => 'Parola-Sigura-123',
        'email_verified' => '0',
        'is_admin' => '1',
    ]);

    $user = User::where('email', 'nou@example.test')->firstOrFail();

    expect($user->hasVerifiedEmail())->toBeFalse()->and($user->is_admin)->toBeTrue();
    Notification::assertSentTo($user, VerifyEmail::class);
});

test('creating a user validates the input', function (array $data, string $field) {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['email' => 'existent@example.test']);

    $this->actingAs($admin)->post(route('admin.users.store'), array_merge([
        'name' => 'Cont Nou',
        'email' => 'nou@example.test',
        'password' => 'Parola-Sigura-123',
        'password_confirmation' => 'Parola-Sigura-123',
    ], $data))->assertSessionHasErrors($field);
})->with([
    'missing name' => [['name' => ''], 'name'],
    'invalid email' => [['email' => 'nu-e-email'], 'email'],
    'duplicate email' => [['email' => 'Existent@example.test'], 'email'],
    'weak password' => [['password' => 'slaba', 'password_confirmation' => 'slaba'], 'password'],
    'unconfirmed password' => [['password_confirmation' => 'Alta-Parola-999'], 'password'],
]);

// --- Update --------------------------------------------------------------------

test('admins can update a user without changing the password', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $oldHash = $user->password;

    $this->actingAs($admin)->put(route('admin.users.update', $user), [
        'name' => 'Nume Schimbat',
        'email' => 'schimbat@example.test',
        'password' => '',
        'password_confirmation' => '',
        'email_verified' => '1',
        'is_admin' => '1',
    ])->assertRedirect(route('admin.users.index'))->assertSessionHas('status', 'user-updated');

    $user->refresh();
    expect($user->name)->toBe('Nume Schimbat')
        ->and($user->email)->toBe('schimbat@example.test')
        ->and($user->is_admin)->toBeTrue()
        ->and($user->password)->toBe($oldHash);
});

test('setting a new password signs the user out of all devices', function () {
    config(['session.driver' => 'database']);
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    insertSession($user, 'user-device');
    $rememberToken = $user->remember_token;

    $this->actingAs($admin)->put(route('admin.users.update', $user), [
        'name' => $user->name,
        'email' => $user->email,
        'password' => 'Parola-Noua-456',
        'password_confirmation' => 'Parola-Noua-456',
        'email_verified' => '1',
    ])->assertSessionHasNoErrors();

    $user->refresh();
    expect(Hash::check('Parola-Noua-456', $user->password))->toBeTrue()
        ->and(DB::table('sessions')->where('id', 'user-device')->exists())->toBeFalse()
        ->and($user->remember_token)->not->toBe($rememberToken);
});

test('unchecking email verified requires the user to verify again', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)->put(route('admin.users.update', $user), [
        'name' => $user->name,
        'email' => $user->email,
        'email_verified' => '0',
    ]);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('admins cannot remove their own admin rights', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->put(route('admin.users.update', $admin), [
        'name' => $admin->name,
        'email' => $admin->email,
        'email_verified' => '1',
        'is_admin' => '0',
    ])->assertSessionHasNoErrors();

    expect($admin->fresh()->is_admin)->toBeTrue();
});

test('updating checks that the email stays unique', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    User::factory()->create(['email' => 'ocupat@example.test']);

    $this->actingAs($admin)->put(route('admin.users.update', $user), [
        'name' => $user->name,
        'email' => 'ocupat@example.test',
    ])->assertSessionHasErrors('email');
});

// --- Delete --------------------------------------------------------------------

test('admins can delete a user and their sessions', function () {
    config(['session.driver' => 'database']);
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    insertSession($user, 'user-device');

    $this->actingAs($admin)->delete(route('admin.users.destroy', $user))
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('status', 'user-deleted');

    expect(User::find($user->id))->toBeNull()
        ->and(DB::table('sessions')->where('id', 'user-device')->exists())->toBeFalse();
});

test('admins cannot delete their own account', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertForbidden();

    expect(User::find($admin->id))->not->toBeNull();
});

// --- Command -------------------------------------------------------------------

test('the make-admin command grants and revokes admin rights', function () {
    $user = User::factory()->create(['email' => 'ana@example.test']);

    $this->artisan('app:make-admin', ['email' => 'ANA@example.test'])->assertSuccessful();
    expect($user->fresh()->is_admin)->toBeTrue();

    $this->artisan('app:make-admin', ['email' => 'ana@example.test', '--revoke' => true])->assertSuccessful();
    expect($user->fresh()->is_admin)->toBeFalse();

    $this->artisan('app:make-admin', ['email' => 'lipsa@example.test'])->assertFailed();
});
