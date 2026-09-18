{{-- <x-quizlayout>
    <x-slot:title>Quiz Learning Mode</x-slot:title>
    <div class="main-quiz">
        <livewire:learning-quiz :quiz="$quiz" />
    </div>
</x-quizlayout> --}}
@extends('layouts.main')
@section('title')
    Learning Quiz
@endsection
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/brand-question.css') }}">
@endpush

@section('content')
    <livewire:learning-quiz :quiz="$quiz"/> 
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/brand-question.js') }}"></script>
@endpush
