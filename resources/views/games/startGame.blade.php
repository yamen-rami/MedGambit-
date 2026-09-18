{{-- <x-quizlayout>
    <x-slot:title>Start Game</x-slot:title>
    <livewire:game :gameId="$gameId" />
</x-quizlayout> --}}

@extends('layouts.main')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/arena-questions.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/ecg-loader.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/flatline.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/draw.css') }}">
    @vite(['resources/js/app.js'])
@endpush
@section('title')
    Game
@endsection
@section('body_class', 'game-page')
@section('content')
    <livewire:game :gameId="$gameId" />
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/ecg-loader.js') }}"></script>
    <script src="{{ asset('assets/js/flatline.js') }}"></script>
    <script src="{{ asset('assets/js/draw.js') }}"></script>
@endpush
