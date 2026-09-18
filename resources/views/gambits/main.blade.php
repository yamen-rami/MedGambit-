
@extends('layouts.main')
@section('title')
    Gambits
@endsection
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/gambits.css') }}">
@endpush
@section('content')
    <livewire:gambits /> 
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/gambits.js') }}"></script>
@endpush
