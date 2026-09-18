@extends('layouts.main')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/arena-waiting.css') }}">
    @vite(['resources/js/app.js'])
@endpush

@section('title', 'Challenge ready')

@section('content')
    <section class="arena-wait-page" x-data="{
        copied: false,
        async copyChallenge() {
            const url = window.location.href;

            try {
                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(url);
                } else {
                    const input = document.createElement('textarea');
                    input.value = url;
                    input.style.position = 'fixed';
                    input.style.opacity = '0';
                    document.body.appendChild(input);
                    input.select();
                    document.execCommand('copy');
                    input.remove();
                }

                this.copied = true;
                setTimeout(() => this.copied = false, 3500);
            } catch (error) {
                window.prompt('Copy this challenge link:', url);
            }
        }
    }">
        <div class="arena-wait-card card">
            <div class="card-body p-4 p-md-5 text-center">
                <div class="arena-wait-icon mx-auto mb-4" aria-hidden="true"><i class="bi bi-crosshair2"></i></div>
                <span class="badge text-bg-primary mb-3"><span class="arena-live-dot"></span> PRIVATE 1V1 DUEL</span>
                <h1 class="h2 mb-3">Your challenge is ready</h1>
                <p class="text-secondary mx-auto mb-4 arena-wait-copy">Share this link with one opponent. Opening it while signed in joins them to this match automatically.</p>

                <label for="challenge-link" class="form-label text-start w-100 mb-2">Challenge link</label>
                <div class="input-group arena-challenge-input">
                    <input id="challenge-link" class="form-control font-monospace" type="text" readonly x-bind:value="window.location.href" aria-label="Challenge link">
                    <button class="btn btn-primary" type="button" x-on:click="copyChallenge()">
                        <i class="bi bi-copy me-1"></i> Copy link
                    </button>
                </div>

                <div class="alert alert-primary d-flex align-items-start gap-2 mt-4 mb-0 text-start" role="status">
                    <i class="bi bi-info-circle-fill mt-1"></i>
                    <div><strong>Waiting for an opponent</strong><br><span class="small">The game starts immediately when they use your link.</span></div>
                </div>

                <div class="arena-wait-status mt-4"><span class="spinner-grow spinner-grow-sm" aria-hidden="true"></span> Match lobby active</div>
                <livewire:waiting :game="$game" />
            </div>
        </div>

        <div class="toast-container position-fixed bottom-0 end-0 p-3" aria-live="polite" aria-atomic="true">
            <div class="toast show border-success" x-cloak x-show="copied" x-transition role="alert">
                <div class="toast-header">
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    <strong class="me-auto">Link copied</strong>
                    <button class="btn-close" type="button" x-on:click="copied = false" aria-label="Close"></button>
                </div>
                <div class="toast-body">Challenge link copied successfully. Send it to your opponent.</div>
            </div>
        </div>
    </section>
@endsection
