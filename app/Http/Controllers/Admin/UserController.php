<?php

namespace App\Http\Controllers\Admin;

use App\Events\AccountCreated;
use App\Events\AccountDeleted;
use App\Events\AccountUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Support\Realtime;
use App\Support\UserSessions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Admin area: user accounts CRUD (Nume, Prenume, Email) with soft delete.
 * Access: `can:admin` middleware on the route group + UserPolicy.
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $search = trim((string) $request->query('search', ''));
        $showDeleted = $request->query('status') === 'deleted';

        $users = User::query()
            ->when($showDeleted, fn ($query) => $query->onlyTrashed())
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->whereLike('last_name', "%{$search}%")
                ->orWhereLike('first_name', "%{$search}%")
                ->orWhereLike('email', "%{$search}%")))
            ->orderByDesc('is_admin')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'showDeleted' => $showDeleted,
            'deletedCount' => User::onlyTrashed()->count(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('admin.users.create', ['user' => new User]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = new User($request->safe()->only(['last_name', 'first_name', 'email', 'password']));
        $user->forceFill([
            'is_admin' => $request->boolean('is_admin'),
            'email_verified_at' => $request->boolean('email_verified') ? now() : null,
        ])->save();

        if (! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        Realtime::broadcast(new AccountCreated($user));

        return to_route('admin.users.index')->with('status', 'user-created');
    }

    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        return view('admin.users.edit', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $isSelf = $request->user()->is($user);

        $user->fill($request->safe()->only(['last_name', 'first_name', 'email']));

        if ($request->filled('password')) {
            $user->password = $request->validated('password');
        }

        $verified = $request->boolean('email_verified');
        if ($verified && ! $user->hasVerifiedEmail()) {
            $user->email_verified_at = now();
        } elseif (! $verified) {
            $user->email_verified_at = null;
        }

        // An admin cannot remove their own admin rights (avoids locking everyone out).
        if (! $isSelf) {
            $user->is_admin = $request->boolean('is_admin');
        }

        $passwordChanged = $user->isDirty('password');
        $user->save();

        Realtime::broadcast(new AccountUpdated($user));

        // A new password signs the account out everywhere (except the admin's own session).
        if ($passwordChanged) {
            UserSessions::revoke($user, exceptSessionId: $isSelf ? $request->session()->getId() : null);
        }

        return to_route('admin.users.index')->with('status', 'user-updated');
    }

    /**
     * Soft delete: the account is hidden and cannot sign in, but can be restored.
     */
    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        UserSessions::revoke($user);
        $user->delete();

        Realtime::broadcast(new AccountDeleted($user->id));

        return to_route('admin.users.index')->with('status', 'user-deleted');
    }

    public function restore(User $user): RedirectResponse
    {
        Gate::authorize('restore', $user);

        $user->restore();

        Realtime::broadcast(new AccountCreated($user));

        return to_route('admin.users.index', ['status' => 'deleted'])->with('status', 'user-restored');
    }
}
