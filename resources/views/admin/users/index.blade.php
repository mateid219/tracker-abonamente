@extends('layouts.app')

@section('title', 'Administrare conturi')

@section('content')
<div class="page-header">
    <h1>Conturi</h1>
    <a href="{{ route('admin.users.create') }}" class="btn">Cont nou</a>
</div>

<form method="GET" action="{{ route('admin.users.index') }}" class="search-form" role="search">
    <label for="search" class="visually-hidden">Caută</label>
    <input id="search" type="search" name="search" value="{{ $search }}" placeholder="Caută după nume sau email">
    <button type="submit" class="btn btn-secondary">Caută</button>
    @if ($search !== '')
        <a href="{{ route('admin.users.index') }}" class="btn-link">Resetează</a>
    @endif
</form>

<div class="card table-card">
    @if ($users->isEmpty())
        <p class="muted empty-state">
            {{ $search !== '' ? 'Niciun cont nu corespunde căutării.' : 'Nu există conturi.' }}
        </p>
    @else
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Nume</th>
                        <th scope="col" class="hide-sm">Email</th>
                        <th scope="col">Rol</th>
                        <th scope="col" class="hide-sm">Email verificat</th>
                        <th scope="col" class="hide-sm">Creat</th>
                        <th scope="col"><span class="visually-hidden">Acțiuni</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>
                                {{ $user->name }}
                                @if ($user->is(auth()->user()))
                                    <span class="muted small">(tu)</span>
                                @endif
                                <span class="show-sm muted small">{{ $user->email }}</span>
                            </td>
                            <td class="hide-sm">{{ $user->email }}</td>
                            <td>
                                @if ($user->is_admin)
                                    <span class="badge badge-primary">Admin</span>
                                @else
                                    <span class="badge badge-neutral">Utilizator</span>
                                @endif
                            </td>
                            <td class="hide-sm">
                                @if ($user->hasVerifiedEmail())
                                    <span class="badge badge-success">Da</span>
                                @else
                                    <span class="badge badge-warning">Nu</span>
                                @endif
                            </td>
                            <td class="muted small hide-sm" title="{{ $user->created_at?->format('d.m.Y H:i') }}">
                                {{ $user->created_at?->format('d.m.Y') }}
                            </td>
                            <td class="actions-cell">
                                <div class="table-actions">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-secondary btn-small">Editează</a>

                                @can('delete', $user)
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                          x-data
                                          @submit="if (! confirm(@js('Ștergi definitiv contul '.$user->email.'? Acțiunea nu poate fi anulată.'))) $event.preventDefault()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-small">Șterge</button>
                                    </form>
                                @endcan
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
@endsection
