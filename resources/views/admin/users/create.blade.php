@extends('layouts.app')

@section('title', 'Cont nou')

@section('content')
<p class="breadcrumb"><a href="{{ route('admin.users.index') }}">&larr; Conturi</a></p>

<div class="card form-card">
    <h1>Cont nou</h1>

    <form method="POST" action="{{ route('admin.users.store') }}" x-data="{ loading: false }" @submit="loading = true">
        @csrf

        @include('admin.users._form', ['isEdit' => false])

        <div class="button-row">
            <button type="submit" class="btn" :disabled="loading">Creează contul</button>
            <a href="{{ route('admin.users.index') }}" class="btn-link">Renunță</a>
        </div>
    </form>
</div>
@endsection
