<?php

use App\Enums\SubscriptionStatus;
use App\Events\SubscriptionCreated;
use App\Events\SubscriptionDeleted;
use App\Events\SubscriptionUpdated;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Event;

// --- Dashboard (list + Cost Lunar Total) ----------------------------------------

test('guests cannot see the dashboard or manage subscriptions', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->post(route('subscriptions.store'), [])->assertRedirect(route('login'));
});

test('the dashboard lists only the user\'s own subscriptions', function () {
    $user = User::factory()->create();
    Subscription::factory()->for($user)->create(['name' => 'Netflix']);
    Subscription::factory()->for($user)->paused()->create(['name' => 'Sală']);
    Subscription::factory()->create(['name' => 'Abonament Străin']);

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Cost Lunar Total')
        ->assertSee('Netflix')
        ->assertSee('Sală')
        ->assertDontSee('Abonament Străin');
});

test('the total sums only active subscriptions', function () {
    $user = User::factory()->create();
    Subscription::factory()->for($user)->create(['monthly_cost' => 15.99]);
    Subscription::factory()->for($user)->create(['monthly_cost' => 9.99]);
    Subscription::factory()->for($user)->paused()->create(['monthly_cost' => 120]);

    // Initial value rendered by the server (Alpine recalculates it live in the browser).
    $this->actingAs($user)->get(route('dashboard'))->assertSee('25,98');
});

// --- Create --------------------------------------------------------------------

test('users can add a subscription (JSON, used by the dashboard)', function () {
    Event::fake();
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('subscriptions.store'), [
        'name' => 'Netflix',
        'monthly_cost' => '15.99',
        'status' => 'active',
    ])->assertCreated()->assertJsonPath('subscription.name', 'Netflix')
        ->assertJsonPath('subscription.monthly_cost', '15.99')
        ->assertJsonPath('subscription.status', 'active');

    $subscription = $user->subscriptions()->sole();
    expect($subscription->status)->toBe(SubscriptionStatus::Active);

    Event::assertDispatched(SubscriptionCreated::class, fn ($e) => $e->userId === $user->id
        && $e->subscription['id'] === $subscription->id);
});

test('a decimal comma is accepted for the monthly cost', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('subscriptions.store'), [
        'name' => 'Spotify', 'monthly_cost' => '9,99', 'status' => 'paused',
    ])->assertCreated();

    expect($user->subscriptions()->sole())
        ->monthly_cost->toBe('9.99')
        ->status->toBe(SubscriptionStatus::Paused);
});

test('the add form also works without JavaScript', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('subscriptions.store'), [
        'name' => 'Sală', 'monthly_cost' => '120', 'status' => 'active',
    ])->assertRedirect(route('dashboard'));

    expect($user->subscriptions()->count())->toBe(1);
});

test('subscriptions are validated', function (array $data, string $field) {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('subscriptions.store'), array_merge([
        'name' => 'Netflix', 'monthly_cost' => '15.99', 'status' => 'active',
    ], $data))->assertUnprocessable()->assertJsonValidationErrors($field);

    expect($user->subscriptions()->count())->toBe(0);
})->with([
    'missing name' => [['name' => ''], 'name'],
    'name too long' => [['name' => str_repeat('a', 101)], 'name'],
    'missing cost' => [['monthly_cost' => ''], 'monthly_cost'],
    'cost not a number' => [['monthly_cost' => 'gratis'], 'monthly_cost'],
    'negative cost' => [['monthly_cost' => '-5'], 'monthly_cost'],
    'too many decimals' => [['monthly_cost' => '15.999'], 'monthly_cost'],
    'invalid status' => [['status' => 'anulat'], 'status'],
]);

// --- Update / toggle Activ-Pauză ---------------------------------------------------

test('users can toggle a subscription between Activ and Pauză', function () {
    Event::fake();
    $user = User::factory()->create();
    $subscription = Subscription::factory()->for($user)->create();

    $this->actingAs($user)->patchJson(route('subscriptions.status', $subscription), ['status' => 'paused'])
        ->assertOk()->assertJsonPath('subscription.status', 'paused');
    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::Paused);

    $this->actingAs($user)->patchJson(route('subscriptions.status', $subscription), ['status' => 'active'])
        ->assertOk();
    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::Active);

    Event::assertDispatchedTimes(SubscriptionUpdated::class, 2);
});

test('users can edit a subscription', function () {
    Event::fake();
    $user = User::factory()->create();
    $subscription = Subscription::factory()->for($user)->create(['name' => 'Netflix', 'monthly_cost' => 15.99]);

    $this->actingAs($user)->get(route('subscriptions.edit', $subscription))->assertOk()->assertSee('Netflix');

    $this->actingAs($user)->put(route('subscriptions.update', $subscription), [
        'name' => 'Netflix Premium', 'monthly_cost' => '19.99', 'status' => 'paused',
    ])->assertRedirect(route('dashboard'));

    expect($subscription->fresh())
        ->name->toBe('Netflix Premium')
        ->monthly_cost->toBe('19.99')
        ->status->toBe(SubscriptionStatus::Paused);

    Event::assertDispatched(SubscriptionUpdated::class);
});

// --- Delete --------------------------------------------------------------------

test('users can delete a subscription', function () {
    Event::fake();
    $user = User::factory()->create();
    $subscription = Subscription::factory()->for($user)->create();

    $this->actingAs($user)->deleteJson(route('subscriptions.destroy', $subscription))
        ->assertOk()->assertJsonPath('id', $subscription->id);

    expect(Subscription::find($subscription->id))->toBeNull();
    Event::assertDispatched(SubscriptionDeleted::class, fn ($e) => $e->subscriptionId === $subscription->id
        && $e->userId === $user->id);
});

// --- Ownership -------------------------------------------------------------------

test('users cannot edit, toggle or delete someone else\'s subscription', function () {
    $user = User::factory()->create();
    $other = Subscription::factory()->create(['name' => 'Străin']);

    $this->actingAs($user)->get(route('subscriptions.edit', $other))->assertForbidden();
    $this->actingAs($user)->putJson(route('subscriptions.update', $other), [
        'name' => 'Furat', 'monthly_cost' => '1', 'status' => 'active',
    ])->assertForbidden();
    $this->actingAs($user)->patchJson(route('subscriptions.status', $other), ['status' => 'paused'])->assertForbidden();
    $this->actingAs($user)->deleteJson(route('subscriptions.destroy', $other))->assertForbidden();

    expect($other->fresh())->name->toBe('Străin')->status->toBe(SubscriptionStatus::Active);
});
