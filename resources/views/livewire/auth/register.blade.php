@extends('layouts.main')
@section('title')
    Register
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">
@endpush

@section('content')
    <main class="auth-main">
        <section class="auth-card auth-card-wide">
            <div class="auth-header">
                <span class="auth-eyebrow">CREATE PROFILE </span>
                <h1>Create your MedGambit account</h1>
                <p>Join MedGambit and start practicing, learning, and competing.</p>
            </div>

            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data"
                class="auth-form needs-validation" id="registerForm" novalidate>
                @csrf

                <section class="auth-section">
                    <h2>Account information</h2>
                    <div class="form-row">
                        <x-forms.validated-input label="Name" name="name" id="name" placeholder="Your name"
                            autocomplete="name" required />
                        <x-forms.validated-input label="Email" name="email" id="registerEmail" type="email"
                            placeholder="you@example.com" autocomplete="email" required />
                    </div>

                    <div class="form-row">
                        <x-forms.validated-input label="Password" name="password" id="registerPassword" type="password"
                            autocomplete="new-password" :toggle-password="true" required />
                        <x-forms.validated-input label="Confirm password" name="password_confirmation"
                            id="passwordConfirmation" type="password" autocomplete="new-password" :toggle-password="true"
                            required />
                    </div>
                </section>

                <section class="auth-section">
                    <h2>Medical study information</h2>
                    <p class="section-help">Tell us where you are in your clinical journey.</p>

                    <fieldset class="field has-validation border-0 p-0 m-0">
                        <legend>Graduated</legend>
                        <div class="choice-group">
                            <label
                                class="choice-label @error('graduated') border-danger @enderror @if (old('graduated', 'false') === 'false' && !$errors->has('graduated')) is-active @endif">
                                <input type="radio" name="graduated" value="false" @checked(old('graduated', 'false') === 'false')>
                                Student
                            </label>
                            <label
                                class="choice-label @error('graduated') border-danger @enderror @if (old('graduated') === 'true' && !$errors->has('graduated')) is-active @endif">
                                <input type="radio" name="graduated" value="true" @checked(old('graduated') === 'true')>
                                Graduated
                            </label>
                        </div>
                        @error('graduated')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </fieldset>

                    <div class="field has-validation" id="yearField">
                        <label for="year">Year</label>
                        <select
                            class="form-select auth-control @error('year') is-invalid @enderror @if (old('year') && !$errors->has('year')) is-valid @endif"
                            id="year" name="year">
                            <option value="">Select your year...</option>
                            <option value="1" @selected(old('year') == '1')>1st Year</option>
                            <option value="2" @selected(old('year') == '2')>2nd Year</option>
                            <option value="3" @selected(old('year') == '3')>3rd Year</option>
                            <option value="4" @selected(old('year') == '4')>4th Year</option>
                            <option value="5" @selected(old('year') == '5')>5th Year</option>
                            <option value="6" @selected(old('year') == '6')>6th Year</option>
                        </select>
                        @error('year')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @elseif(old('year'))
                            <div class="valid-feedback">Looks good.</div>
                        @enderror
                    </div>
                </section>

                <section class="auth-section">
                    <h2>Personal information</h2>
                    <div class="form-row">
                        <fieldset class="field has-validation border-0 p-0 m-0" data-choice-group="gender">
                            <legend>Gender</legend>
                            <div class="choice-group">
                                <label
                                    class="choice-label @error('gender') border-danger @enderror @if (old('gender', 'male') === 'male' && !$errors->has('gender')) is-active @endif">
                                    <input type="radio" name="gender" value="male" @checked(old('gender', 'male') === 'male')>
                                    Male
                                </label>
                                <label
                                    class="choice-label @error('gender') border-danger @enderror @if (old('gender') === 'female' && !$errors->has('gender')) is-active @endif">
                                    <input type="radio" name="gender" value="female" @checked(old('gender') === 'female')>
                                    Female
                                </label>
                            </div>
                            @error('gender')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </fieldset>

                        <div class="field has-validation">
                            <label for="country">Country</label>
                            <select
                                class="form-select auth-control @error('country') is-invalid @enderror @if (old('country') && !$errors->has('country')) is-valid @endif"
                                id="country" name="country" autocomplete="country" required>
                                <option value="">Select your country...</option>
                                <option value="Egypt" @selected(old('country') === 'Egypt')>Egypt</option>
                                <option value="Saudi Arabia" @selected(old('country') === 'Saudi Arabia')>Saudi Arabia</option>
                                <option value="Jordan" @selected(old('country') === 'Jordan')>Jordan</option>
                                <option value="United Arab Emirates" @selected(old('country') === 'United Arab Emirates')>United Arab Emirates
                                </option>
                                <option value="United Kingdom" @selected(old('country') === 'United Kingdom')>United Kingdom</option>
                                <option value="United States" @selected(old('country') === 'United States')>United States</option>
                                <option value="Canada" @selected(old('country') === 'Canada')>Canada</option>
                                <option value="Germany" @selected(old('country') === 'Germany')>Germany</option>
                                <option value="India" @selected(old('country') === 'India')>India</option>
                                <option value="Australia" @selected(old('country') === 'Australia')>Australia</option>
                            </select>
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @elseif(old('country'))
                                <div class="valid-feedback">Looks good.</div>
                            @enderror
                        </div>
                    </div>

                    <div class="field has-validation">
                        <label for="knowAboutUs">How did you hear about us?</label>
                        <select
                            class="form-select auth-control @error('know_about_us') is-invalid @enderror @if (old('know_about_us') && !$errors->has('know_about_us')) is-valid @endif"
                            id="knowAboutUs" name="know_about_us" required>
                            <option value="">Choose an option...</option>
                            <option value="social" @selected(old('know_about_us') === 'social')>Social media</option>
                            <option value="friend" @selected(old('know_about_us') === 'friend')>Friend</option>
                        </select>
                        @error('know_about_us')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @elseif(old('know_about_us'))
                            <div class="valid-feedback">Looks good.</div>
                        @enderror
                    </div>
                </section>

                <section class="auth-section">
                    <h2>Profile image <span class="text-secondary fw-normal">· optional</span></h2>
                    <div class="profile-picker">
                        <div class="profile-preview" id="profilePreview">Y</div>
                        <div class="profile-picker-copy">
                            <p>Add a profile image to personalize your account.</p>
                            <div class="profile-buttons">
                                <label class="btn btn-sm btn-outline-secondary" for="profileImage">Add image</label>
                                <button class="btn btn-sm btn-outline-secondary" id="removeImage" type="button" hidden>
                                    Remove
                                </button>
                            </div>
                            <span class="file-name" id="fileName">No image selected</span>
                        </div>
                    </div>
                    <input class="form-control @error('image') is-invalid @enderror" id="profileImage" name="image"
                        type="file" accept="image/png,image/jpeg,image/webp" hidden>
                    @error('image')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </section>

                <button class="auth-submit" type="submit">Create account</button>
                <div class="auth-message" id="authMessage" hidden></div>
            </form>

            <div class="auth-footer">
                Already have an account?
                <a class="auth-link" href="{{ route('login') }}">Log in</a>
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/auth.js') }}"></script>
    <script>
        (() => {
            const form = document.getElementById('registerForm');
            const yearField = document.getElementById('yearField');
            const year = document.getElementById('year');
            const graduatedInputs = document.querySelectorAll('input[name="graduated"]');

            const syncGraduationFields = () => {
                const graduated = document.querySelector('input[name="graduated"]:checked')?.value === 'true';

                yearField.hidden = graduated;
                year.disabled = graduated;
                year.required = !graduated;

                graduatedInputs.forEach((input) => {
                    input.closest('.choice-label')?.classList.toggle('is-active', input.checked);
                });
            };

            graduatedInputs.forEach((input) => input.addEventListener('change', syncGraduationFields));
            syncGraduationFields();

            document.querySelectorAll('[data-password-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const input = document.getElementById(button.dataset.passwordToggle);
                    const icon = button.querySelector('i');
                    const showing = input.type === 'text';

                    input.type = showing ? 'password' : 'text';
                    button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
                    button.setAttribute('aria-pressed', String(!showing));
                    icon.classList.toggle('bi-eye', showing);
                    icon.classList.toggle('bi-eye-slash', !showing);
                });
            });

            form.addEventListener('submit', (event) => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                form.classList.add('was-validated');
            });
        })();
    </script>
@endpush
