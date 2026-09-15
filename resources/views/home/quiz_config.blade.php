@extends('layouts.main')
@section('title')
    Config Quiz
@endsection
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/create-quiz.css') }}">
@endpush


@section('content')
    <livewire:quiz-config />    
@endsection
