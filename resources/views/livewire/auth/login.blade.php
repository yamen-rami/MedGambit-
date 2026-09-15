@extends('layouts.main')
@section('title')
    Login
@endsection
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">
@endpush
@section('content')
    <main class="auth-main">
        <section class="auth-card">
            <div class="auth-header"><span class="auth-eyebrow"></span>
                <h1>Welcome back</h1>
                <p>Continue your MedGambit journey.</p>
            </div>
            <form method="POST" action="{{ route('login') }}" class="auth-form" id="loginForm">
                @csrf
                <div class="field has-validation"><label for="email">Email</label><input
                        class="form-control auth-control @error('email') is-invalid @enderror" id="email" name="email"
                        type="email" placeholder="you@example.com" autocomplete="email" value="{{ old('email') }}"
                        required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="field"><label for="password">Password</label>
                    <div class="password-wrap has-validation"><input
                            class="form-control auth-control @error('password') is-invalid @enderror" id="password"
                            name="password" type="password" autocomplete="current-password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <button class="password-toggle" type="button" data-password-toggle="password"
                            aria-label="Show password"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <div class="check-row"><label><input type="checkbox" name="remember"> Remember me</label><a
                        class="auth-link" href="#">Forgot password?</a></div><button class="auth-submit"
                    type="submit">Log In</button>
                <div class="auth-message" id="authMessage" hidden></div>
            </form>
            <div class="auth-footer">Don't have an account? <a class="auth-link" href="register.html">Create
                    account</a></div>
        </section>
    </main>

    <script src="profile-dropdown.js"></script>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/auth.js') }}"></script>
@endpush
