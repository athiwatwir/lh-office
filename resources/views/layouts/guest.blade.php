@extends('layouts.auth')

@section('auth-title', $title ?? config('app.name'))
@section('auth-content')
    {{ $slot }}
@endsection
