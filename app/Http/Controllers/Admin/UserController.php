<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Support\UserSessions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Admin area: create, list, edit and delete user accounts.
 * Access: `can:admin` middleware on the route group + UserPolicy.
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $search = trim((string) $request->query('search', ''));

        $users = User::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->whereLike('name', "%{$search}%")
                ->orWhereLike('email', "%{$search}%")))
            ->orderByDesc('is_admin')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('admin.users.create', ['user' => new User]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = new User($request->safe()->only(['name', 'email', 'password']));
        $user->forceFill([
            'is_admin' => $request->boolean('is_admin'),
            'email_verified_at' => $request->boolean('email_verified') ? now() : null,
        ])->save();

        if (! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

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

        $user->fill($request->safe()->only(['name', 'email']));

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

        // A new password signs the account out everywhere (except the admin's own session).
        if ($passwordChanged) {
            UserSessions::revoke($user, exceptSessionId: $isSelf ? $request->session()->getId() : null);
        }

        return to_route('admin.users.index')->with('status', 'user-updated');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        UserSessions::revoke($user);
        $user->delete();

        return to_route('admin.users.index')->with('status', 'user-deleted');
    }
}
