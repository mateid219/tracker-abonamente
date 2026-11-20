<?php

namespace App\Http\Controllers;

use App\Enums\SubscriptionStatus;
use App\Events\SubscriptionCreated;
use App\Events\SubscriptionDeleted;
use App\Events\SubscriptionUpdated;
use App\Http\Requests\SubscriptionRequest;
use App\Models\Subscription;
use App\Support\Realtime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Subscription CRUD. The dashboard (Alpine.js) calls these with fetch() and gets JSON;
 * without JavaScript the same routes work as normal forms with redirects.
 * Every change is also sent over WebSockets to the user's other open windows.
 */
class SubscriptionController extends Controller
{
    /**
     * Dashboard: all of the user's subscriptions; the total is calculated in the browser.
     */
    public function index(Request $request): View
    {
        $subscriptions = $request->user()->subscriptions()->latest('id')->get();

        return view('dashboard', [
            'subscriptions' => $subscriptions->map->toClientArray()->values(),
        ]);
    }

    public function store(SubscriptionRequest $request): JsonResponse|RedirectResponse
    {
        $subscription = $request->user()->subscriptions()->create($request->validated());

        Realtime::broadcast(new SubscriptionCreated($subscription));

        return $request->expectsJson()
            ? response()->json(['subscription' => $subscription->toClientArray()], 201)
            : to_route('dashboard')->with('status', 'subscription-created');
    }

    public function edit(Subscription $subscription): View
    {
        Gate::authorize('update', $subscription);

        return view('subscriptions.edit', ['subscription' => $subscription]);
    }

    public function update(SubscriptionRequest $request, Subscription $subscription): JsonResponse|RedirectResponse
    {
        $subscription->update($request->validated());

        Realtime::broadcast(new SubscriptionUpdated($subscription));

        return $request->expectsJson()
            ? response()->json(['subscription' => $subscription->toClientArray()])
            : to_route('dashboard')->with('status', 'subscription-updated');
    }

    /**
     * The Activ / Pauză toggle on the dashboard.
     */
    public function updateStatus(Request $request, Subscription $subscription): JsonResponse|RedirectResponse
    {
        Gate::authorize('update', $subscription);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(SubscriptionStatus::class)],
        ]);

        $subscription->update($validated);

        Realtime::broadcast(new SubscriptionUpdated($subscription));

        return $request->expectsJson()
            ? response()->json(['subscription' => $subscription->toClientArray()])
            : to_route('dashboard');
    }

    public function destroy(Request $request, Subscription $subscription): JsonResponse|RedirectResponse
    {
        Gate::authorize('delete', $subscription);

        $id = $subscription->id;
        $userId = $subscription->user_id;
        $subscription->delete();

        Realtime::broadcast(new SubscriptionDeleted($id, $userId));

        return $request->expectsJson()
            ? response()->json(['id' => $id])
            : to_route('dashboard')->with('status', 'subscription-deleted');
    }
}
