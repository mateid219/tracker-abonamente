<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Account settings: profile, password and active sessions.
     */
    public function show(Request $request): View
    {
        return view('settings', [
            'user' => $request->user(),
            'sessions' => $this->activeSessions($request),
        ]);
    }

    /**
     * Sign out every other device by deleting its rows from the `sessions` table
     * and rotating the remember-me token so "remember me" cookies stop working.
     */
    public function destroyOtherSessions(Request $request): RedirectResponse
    {
        $request->validateWithBag('logoutOtherSessions', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))
                ->table(config('session.table', 'sessions'))
                ->where('user_id', $user->getAuthIdentifier())
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        $user->setRememberToken(Str::random(60));
        $user->save();

        return back()->with('status', 'other-sessions-logged-out');
    }

    /**
     * @return Collection<int, object{agent: string, ip_address: ?string, is_current_device: bool, last_active: string}>
     */
    protected function activeSessions(Request $request): Collection
    {
        if (config('session.driver') !== 'database') {
            return collect();
        }

        return DB::connection(config('session.connection'))
            ->table(config('session.table', 'sessions'))
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->orderByDesc('last_activity')
            ->get()
            ->map(fn ($session) => (object) [
                'agent' => $this->describeAgent((string) $session->user_agent),
                'ip_address' => $session->ip_address,
                'is_current_device' => $session->id === $request->session()->getId(),
                'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
            ]);
    }

    protected function describeAgent(string $agent): string
    {
        $browser = match (true) {
            str_contains($agent, 'Edg/') => 'Edge',
            str_contains($agent, 'Firefox/') => 'Firefox',
            str_contains($agent, 'Chrome/') => 'Chrome',
            str_contains($agent, 'Safari/') => 'Safari',
            default => 'Browser necunoscut',
        };

        $platform = match (true) {
            str_contains($agent, 'iPhone'), str_contains($agent, 'iPad') => 'iOS',
            str_contains($agent, 'Android') => 'Android',
            str_contains($agent, 'Mac OS') => 'macOS',
            str_contains($agent, 'Windows') => 'Windows',
            str_contains($agent, 'Linux') => 'Linux',
            default => 'sistem necunoscut',
        };

        return "{$browser} pe {$platform}";
    }
}
