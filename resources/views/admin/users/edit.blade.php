@extends('layouts.app')

@section('title', 'Editează contul')

@section('content')
<p class="breadcrumb"><a href="{{ route('admin.users.index') }}">&larr; Conturi</a></p>

<div class="card form-card">
    <h1>Editează contul</h1>
    <p class="muted">Creat pe {{ $user->created_at?->format('d.m.Y H:i') }}</p>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" x-data="{ loading: false }" @submit="loading = true">
        @csrf
        @method('PUT')

        @include('admin.users._form', ['isEdit' => true])

        <div class="button-row">
            <button type="submit" class="btn" :disabled="loading">Salvează</button>
            <a href="{{ route('admin.users.index') }}" class="btn-link">Renunță</a>
        </div>
    </form>
</div>

@can('delete', $user)
    <div class="card form-card danger-zone">
        <h2>Șterge contul</h2>
        <p class="muted">Contul este dezactivat (soft delete): nu se mai poate autentifica și este deconectat imediat de pe toate dispozitivele. Îl poți restaura oricând din lista „Șterse”.</p>

        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
              x-data
              @submit="if (! confirm(@js('Ștergi contul '.$user->email.'? Contul nu se mai poate autentifica, dar poate fi restaurat din lista „Șterse”.'))) $event.preventDefault()">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Șterge contul</button>
        </form>
    </div>
@endcan
@endsection
