@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1>Salut, {{ auth()->user()->name }}!</h1>

    <div class="card">
        <p>Ești autentificat. Aici vor apărea abonamentele tale.</p>
    </div>
@endsection
