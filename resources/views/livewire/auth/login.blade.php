<x-user-layout>
    <div class="container-xxl col-lg-6">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-6">
                <!-- Login -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center mb-6">
                            <a href="index.html" class="app-brand-link">
                                <span class="app-brand-logo demo">
                                    <span class="text-primary">
                                        <svg width="32" height="22" viewBox="0 0 32 22" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z"
                                                fill="currentColor" />
                                            <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd"
                                                d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z"
                                                fill="#161616" />
                                            <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd"
                                                d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z"
                                                fill="#161616" />
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z"
                                                fill="currentColor" />
                                        </svg>
                                    </span>
                                </span>
                                <span class="app-brand-text demo text-heading fw-bold">MedGambit</span>
                            </a>
                        </div>
                        <!-- /Logo -->
                        <h4 class="mb-1">Welcom To Med Gambit</h4>
                        <p class="mb-6">Sign In</p>

                        <form id="formAuthentication" class="mb-4" action="{{ route('login.store') }}" method="Post">
                            @csrf
                            <div class="form-control-validation mb-6">
                                <label for="email" class="form-label">Email </label>
                                <input class="form-control" name="email" type="email" value="{{ old('email') }}"
                                    autofocus autocomplete="email" placeholder="Enter your email " />
                                @error('email')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-password-toggle form-control-validation mb-6">
                                <label class="form-label" for="password">Password</label>
                                <div class="input-group input-group-merge">
                                    <input name="password" class="form-control" type="password"
                                        autocomplete="current-password" placeholder="Password" viewable
                                        aria-describedby="password" />

                                    <span class="input-group-text cursor-pointer"><i
                                            class="icon-base ti tabler-eye-off"></i></span>

                                </div>
                                @error('password')
                                    <p class="text-danger ">{{ $message }}</p>
                                @enderror

                            </div>
                            <div class="my-8">
                                <div class="d-flex justify-content-between">
                                    <div class="form-check ms-2 mb-0">
                                        <input class="form-check-input" type="checkbox" @checked(old('remember'))
                                            name="remember" id="remember-me" />
                                        <label class="form-check-label" for="remember-me"> Remember Me </label>
                                    </div>
                                    {{-- TODO Forgot Password --}}
                                    {{-- <a href="auth-forgot-password-basic.html">
                                        <p class="mb-0">Forgot Password?</p>
                                    </a> --}}
                                </div>
                            </div>
                            <div class="mb-6">
                                <button class="btn btn-primary d-grid w-100" type="submit">Login</button>
                            </div>
                        </form>

                    </div>
                </div>
                <!-- /Login -->
            </div>
        </div>
    </div>
</x-user-layout>
