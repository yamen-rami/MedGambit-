@extends('layouts.main')
@section('title', $user->name)
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/profile.css') }}">
@endpush
@section('content')
    <livewire:user.profile :user="$user" :attempts="$attempts" :stats="$stats" :answer-stats="$answerStats" :rank-position="$rankPosition" />
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/profile.js') }}"></script>
@endpush
