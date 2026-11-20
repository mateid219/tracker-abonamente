@extends('layouts.app')

@section('title', 'Administrare conturi')

@section('content')
<div x-data="{ newAccounts: 0 }"
     @realtime:admin-user-created.window="newAccounts++">

<div class="page-header">
    <h1>Conturi</h1>
    <a href="{{ route('admin.users.create') }}" class="btn">Cont nou</a>
</div>

<nav class="tabs" aria-label="Filtru conturi">
    <a href="{{ route('admin.users.index', array_filter(['search' => $search])) }}"
       @class(['tab', 'is-active' => ! $showDeleted]) @if (! $showDeleted) aria-current="page" @endif>Active</a>
    <a href="{{ route('admin.users.index', array_filter(['status' => 'deleted', 'search' => $search])) }}"
       @class(['tab', 'is-active' => $showDeleted]) @if ($showDeleted) aria-current="page" @endif>
        Șterse @if ($deletedCount > 0)<span class="tab-count">{{ $deletedCount }}</span>@endif
    </a>
</nav>

<form method="GET" action="{{ route('admin.users.index') }}" class="search-form" role="search">
    @if ($showDeleted)
        <input type="hidden" name="status" value="deleted">
    @endif
    <label for="search" class="visually-hidden">Caută</label>
    <input id="search" type="search" name="search" value="{{ $search }}" placeholder="Caută după nume, prenume sau email">
    <button type="submit" class="btn btn-secondary">Caută</button>
    @if ($search !== '')
        <a href="{{ route('admin.users.index', $showDeleted ? ['status' => 'deleted'] : []) }}" class="btn-link">Resetează</a>
    @endif
</form>

<p class="alert alert-info" x-show="newAccounts > 0" x-cloak role="status">
    <span><span x-text="newAccounts"></span> cont(uri) nou(i) sau restaurat(e) între timp.</span>
    <a href="{{ request()->fullUrl() }}" class="btn btn-secondary btn-small">Reîncarcă lista</a>
</p>

<div class="card table-card">
    @if ($users->isEmpty())
        <p class="muted empty-state">
            @if ($search !== '')
                Niciun cont nu corespunde căutării.
            @else
                {{ $showDeleted ? 'Nu există conturi șterse.' : 'Nu există conturi.' }}
            @endif
        </p>
    @else
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Nume</th>
                        <th scope="col">Prenume</th>
                        <th scope="col" class="hide-sm">Email</th>
                        <th scope="col">Rol</th>
                        @if ($showDeleted)
                            <th scope="col" class="hide-sm">Șters</th>
                        @else
                            <th scope="col" class="hide-sm">Email verificat</th>
                            <th scope="col" class="hide-sm">Creat</th>
                        @endif
                        <th scope="col"><span class="visually-hidden">Acțiuni</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        {{-- Each row updates itself when the account changes in another window (WebSockets). --}}
                        <tr x-data="{ user: @js(\App\Events\AccountUpdated::payload($user)), gone: false }"
                            x-show="! gone"
                            @realtime:admin-user-updated.window="if ($event.detail.id === user.id) user = $event.detail"
                            @realtime:admin-user-deleted.window="if ($event.detail.id === user.id && {{ $showDeleted ? 'false' : 'true' }}) gone = true">
                            <td>
                                <span x-text="user.last_name">{{ $user->last_name }}</span>
                                @if ($user->is(auth()->user()))
                                    <span class="muted small">(tu)</span>
                                @endif
                                <span class="show-sm muted small" x-text="user.email">{{ $user->email }}</span>
                            </td>
                            <td x-text="user.first_name">{{ $user->first_name }}</td>
                            <td class="hide-sm" x-text="user.email">{{ $user->email }}</td>
                            <td>
                                <span class="badge" :class="user.is_admin ? 'badge-primary' : 'badge-neutral'"
                                      x-text="user.is_admin ? 'Admin' : 'Utilizator'">{{ $user->is_admin ? 'Admin' : 'Utilizator' }}</span>
                            </td>
                            @if ($showDeleted)
                                <td class="muted small hide-sm">{{ $user->deleted_at?->format('d.m.Y H:i') }}</td>
                            @else
                                <td class="hide-sm">
                                    <span class="badge" :class="user.verified ? 'badge-success' : 'badge-warning'"
                                          x-text="user.verified ? 'Da' : 'Nu'">{{ $user->hasVerifiedEmail() ? 'Da' : 'Nu' }}</span>
                                </td>
                                <td class="muted small hide-sm" title="{{ $user->created_at?->format('d.m.Y H:i') }}">
                                    {{ $user->created_at?->format('d.m.Y') }}
                                </td>
                            @endif
                            <td class="actions-cell">
                                <div class="table-actions">
                                    @if ($showDeleted)
                                        <form method="POST" action="{{ route('admin.users.restore', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-secondary btn-small">Restaurează</button>
                                        </form>
                                    @else
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-secondary btn-small">Editează</a>

                                        @can('delete', $user)
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                  x-data
                                                  @submit="if (! confirm(@js('Ștergi contul '.$user->email.'? Contul nu se mai poate autentifica, dar poate fi restaurat din lista „Șterse”.'))) $event.preventDefault()">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-small">Șterge</button>
                                            </form>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{ $users->links('partials.pagination') }}
</div>
@endsection
