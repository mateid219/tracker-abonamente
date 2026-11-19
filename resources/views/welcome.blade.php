@extends('layouts.app')

@section('title', 'Acasă')

@section('content')
    <h1>Salut!</h1>

    <div x-data="{ count: 0 }" class="card">
        <p>Ai apăsat de <strong x-text="count"></strong> ori.</p>
        <button class="btn" @click="count++">Apasă</button>
    </div>
@endsection