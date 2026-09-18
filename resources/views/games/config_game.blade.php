{{-- <x-user-layout>
    <livewire:config_game />
</x-user-layout> --}}
@extends('layouts.main')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/create-quiz.css') }}">
@endpush
@section('title')
    Config Game
@endsection
@section('content')
    <livewire:config_game />
@endsection
