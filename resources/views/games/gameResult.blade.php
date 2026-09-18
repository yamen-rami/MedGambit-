@extends('layouts.main')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/battle-results.css') }}">
@endpush
@section('title')
    Game Result
@endsection
@section('content')
    <livewire:game-results :game="$game" :questions="$questions" :attempts="$attempts" />
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/battle-results.js') }}"></script>
@endpush
