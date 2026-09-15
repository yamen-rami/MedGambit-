{{-- <x-quizlayout>
    <livewire:start-quiz :quiz="$quiz" />
</x-quizlayout> --}}

@extends('layouts.main')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/quiz-question.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/quiz-sidebar-layout.css') }}">
@endpush
@section('title')
    Quiz
@endsection
@section('content')
    <livewire:start-quiz :quiz="$quiz" />
@endsection
