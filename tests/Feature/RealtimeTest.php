<?php

use App\Events\AccountDeleted;
use App\Events\AccountUpdated;
use App\Events\SessionsRevoked;
use App\Events\SubscriptionCreated;
use App\Models\Subscription;
use App\Models\User;
use App\Support\Realtime;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\Facades\Event;

/**
 * Uses the real Reverb (Pusher protocol) broadcaster for channel authorization.
 * No network is needed: /broadcasting/auth only checks the channel rules and signs the reply.
 */
function useReverbBroadcaster(): void
{
    config([
        'broadcasting.default' => 'reverb',
        'broadcasting.connections.reverb.key' => 'test-key',
        'broadcasting.connections.reverb.secret' => 'test-secret',
        'broadcasting.connections.reverb.app_id' => '1',
    ]);
    app(BroadcastManager::class)->forgetDrivers();
    require base_path('routes/channels.php');
}

function authorizeChannel(string $channel)
{
    return test()->post('/broadcasting/auth', ['socket_id' => '1234.5678', 'channel_name' => $channel]);
}

// --- Channel authorization -------------------------------------------------------

test('users may listen only on their own private channel', function () {
    useReverbBroadcaster();
    $user = User::factory()->create();
    $other = User::factory()->create();

    $this->actingAs($user);
    authorizeChannel("private-App.Models.User.{$user->id}")->assertOk()->assertJsonStructure(['auth']);
    authorizeChannel("private-App.Models.User.{$other->id}")->assertForbidden();
});

test('only admins may listen on the admin channel', function () {
    useReverbBroadcaster();

    $this->actingAs(User::factory()->create());
    authorizeChannel('private-admin.users')->assertForbidden();

    $this->actingAs(User::factory()->admin()->create());
    authorizeChannel('private-admin.users')->assertOk();
});

test('guests cannot authorize any channel', function () {
    useReverbBroadcaster();
    $user = User::factory()->create();

    $this->post('/broadcasting/auth', [
        'socket_id' => '1234.5678', 'channel_name' => "private-App.Models.User.{$user->id}",
    ])->assertForbidden();
});

// --- Events go to the right channels ------------------------------------------------

test('subscription events are sent on the owner\'s private channel', function () {
    $subscription = Subscription::factory()->create();
    $event = new SubscriptionCreated($subscription);

    expect($event->broadcastOn()[0]->name)->toBe("private-App.Models.User.{$subscription->user_id}")
        ->and($event->broadcastAs())->toBe('subscription.created')
        ->and($event->broadcastWith()['subscription'])->toMatchArray([
            'id' => $subscription->id,
            'name' => $subscription->name,
            'status' => 'active',
        ]);
});

test('account events reach the user and the admins', function () {
    $user = User::factory()->create();

    $channels = collect((new AccountUpdated($user))->broadcastOn())->pluck('name');

    expect($channels->all())->toBe(["private-App.Models.User.{$user->id}", 'private-admin.users']);
});

test('the change is sent "to others": the window that made it is excluded', function () {
    Event::fake();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withHeader('X-Socket-ID', '1111.2222')
        ->postJson(route('subscriptions.store'), ['name' => 'Netflix', 'monthly_cost' => '15.99', 'status' => 'active'])
        ->assertCreated();

    Event::assertDispatched(SubscriptionCreated::class, fn ($e) => $e->socket === '1111.2222');
});

test('a WebSocket server outage never breaks saving', function () {
    // Real Reverb broadcaster pointing to a port where nothing listens.
    config([
        'broadcasting.default' => 'reverb',
        'broadcasting.connections.reverb.key' => 'k',
        'broadcasting.connections.reverb.secret' => 's',
        'broadcasting.connections.reverb.app_id' => '1',
        'broadcasting.connections.reverb.options.host' => '127.0.0.1',
        'broadcasting.connections.reverb.options.port' => 1,
        'broadcasting.connections.reverb.options.scheme' => 'http',
    ]);
    app(BroadcastManager::class)->forgetDrivers();
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('subscriptions.store'), [
        'name' => 'Netflix', 'monthly_cost' => '15.99', 'status' => 'active',
    ])->assertCreated();

    expect($user->subscriptions()->count())->toBe(1);
});

// --- Account changes from the admin area -------------------------------------------------

test('admin changes to an account are broadcast', function () {
    Event::fake();
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)->put(route('admin.users.update', $user), [
        'last_name' => 'Nou', 'first_name' => 'Nume', 'email' => $user->email, 'email_verified' => '1',
    ]);
    Event::assertDispatched(AccountUpdated::class, fn ($e) => $e->user['first_name'] === 'Nume');

    $this->actingAs($admin)->delete(route('admin.users.destroy', $user));
    Event::assertDispatched(AccountDeleted::class, fn ($e) => $e->userId === $user->id);
    Event::assertDispatched(SessionsRevoked::class, fn ($e) => $e->userId === $user->id);
});

test('the session hash identifies a tab without revealing the session id', function () {
    $hash = Realtime::sessionHash('abc');

    expect($hash)->not->toBe('abc')->toHaveLength(64)
        ->and(Realtime::sessionHash('abc'))->toBe($hash)
        ->and(Realtime::sessionHash(null))->toBeNull();
});
