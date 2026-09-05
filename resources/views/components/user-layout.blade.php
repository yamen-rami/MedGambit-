<!doctype html>

<html
    lang="en"
    class="layout-navbar-fixed layout-menu-fixed layout-compact"
    dir="ltr"
    data-skin="default"
    data-bs-theme="light"
    data-assets-path="../../assets/"
    data-template="vertical-menu-template-starter"
>
<head>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />
    <meta name="robots" content="noindex, nofollow" />
    <title>{{ $title ?? "Home Page" }}</title>
    <meta name="description" content="" />
    <link rel="icon" type="image/x-icon" href="../../assets/img/favicon/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    {{-- <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
        rel="stylesheet"
    /> --}}
    @stack('style')

    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/pickr/pickr-themes.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/question.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset("assets/css/demo.css") }}" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.15.12/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.1/build/js/intlTelInput.min.js"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Space+Grotesk:wght@500;600;700&display=swap"
        rel="stylesheet"
    />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet"
    />

    <style>
        * {
            font-family: 'Space Grotesk', sans-serif !important;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="{{ asset("assets/css/all.css") }}" />
    @livewireStyles
</head>

<body class="position-relative">
    <!-- Layout wrapper -->

    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            <aside id="layout-menu" class="layout-menu menu-vertical menu">
                <div class="app-brand demo">
                    <a href="{{ route("home") }}" class="app-brand-link">
                        <span class="app-brand-logo demo">
                            <span class="text-primary">
                                <svg
                                    width="32"
                                    height="22"
                                    viewBox="0 0 32 22"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        clip-rule="evenodd"
                                        d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z"
                                        fill="currentColor"
                                    />
                                    <path
                                        opacity="0.06"
                                        fill-rule="evenodd"
                                        clip-rule="evenodd"
                                        d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z"
                                        fill="#161616"
                                    />
                                    <path
                                        opacity="0.06"
                                        fill-rule="evenodd"
                                        clip-rule="evenodd"
                                        d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z"
                                        fill="#161616"
                                    />
                                    <path
                                        fill-rule="evenodd"
                                        clip-rule="evenodd"
                                        d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z"
                                        fill="currentColor"
                                    />
                                </svg>
                            </span>
                        </span>
                        <span class="app-brand-text demo menu-text fw-bold ms-3">MedGambit</span>
                    </a>

                    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                        <i class="icon-base ti menu-toggle-icon d-none d-xl-block"></i>
                        <i class="icon-base ti tabler-x d-block d-xl-none"></i>
                    </a>
                </div>

                <div class="menu-inner-shadow"></div>

                <ul class="menu-inner py-1">
                    <!-- Home Page -->
                    <li class="menu-item {{ request()->routeIs('home') ? 'active' : '' }}">
                        <a href="{{ route('home') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-smart-home"></i>
                            <div data-i18n="Page 1">Home Page</div>
                        </a>
                    </li>

                    @guest
                        <!-- Register Page -->
                        <li class="menu-item {{ request()->routeIs('register') ? 'active' : '' }}">
                            <a href="{{ route('register') }}" class="menu-link">
                                <i
                                    class="menu-icon icon-base d-flex align-items-center me-2"
                                    style="width: 24px; height: 24px"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                        class="size-6"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"
                                        />
                                    </svg>
                                </i>
                                <div data-i18n="Page 1">Register</div>
                            </a>
                        </li>

                        <!-- Login Page -->
                        <li class="menu-item {{ request()->routeIs('login') ? 'active' : '' }}">
                            <a href="{{ route('login') }}" class="menu-link">
                                <i
                                    class="menu-icon icon-base d-flex align-items-center me-2"
                                    style="width: 24px; height: 24px"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                        class="size-6"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"
                                        />
                                    </svg>
                                </i>
                                <div data-i18n="Login">Login</div>
                            </a>
                        </li>
                    @endguest

                    @auth
                        <!-- Start Quiz -->
                        <li class="menu-item {{ request()->routeIs('start.quiz') ? 'active' : '' }}">
                            <a href="{{ route('start.quiz') }}" class="menu-link">
                                <i
                                    class="menu-icon icon-base d-flex align-items-center me-2"
                                    style="width: 24px; height: 24px"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                        class="size-6"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"
                                        />
                                    </svg>
                                </i>
                                <div data-i18n="Page ">Start A Quiz</div>
                            </a>
                        </li>

                        {{-- <li class="menu-item {{ request()->routeIs('quizGame') ? 'active' : '' }}">
                        <a href="{{ route('quizGame') }}" class="menu-link">
                            <i class="menu-icon icon-base me-2 d-flex align-items-center"
                                style="width: 24px; height: 24px;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </i>
                            <div data-i18n="Page ">Start A Game</div>
                        </a>
                    </li> --}}
                    @endauth
                    @auth
                        <!-- Start Quiz -->
                        <li class="menu-item {{ request()->routeIs('config.game') ? 'active' : '' }}">
                            <a href="{{ route('config.game') }}" class="menu-link">
                                <i
                                    class="menu-icon icon-base d-flex align-items-center me-2"
                                    style="width: 24px; height: 24px"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-swords-icon lucide-swords">
                                        <path d="m13 19 6-6" />
                                        <path d="M14.5 17.5 3.586 6.586A2 2 0 013 5.172V3h2.172a2 2 0 011.414.586L17.5 14.5" />
                                        <path d="m14.828 6.172 2.586-2.586A2 2 0 0118.828 3H21v2.172a2 2 0 01-.586 1.414l-2.586 2.586" />
                                        <path d="m16 16 4 4" />
                                        <path d="m19 21 2-2" />
                                        <path d="m5 14 4 4" />
                                        <path d="m5 21-2-2" />
                                        <path d="M7.5 16.5 4 20" />
                                    </svg>
                                </i>
                                <div data-i18n="Page ">Game With Friend</div>
                            </a>
                        </li>

                    @endauth
                </ul>
            </aside>

            <div class="menu-mobile-toggler d-xl-none rounded-1">
                <a
                    href="javascript:void(0);"
                    class="layout-menu-toggle menu-link text-large text-bg-secondary rounded-1 p-2"
                >
                    <i class="ti tabler-menu icon-base"></i>
                    <i class="ti tabler-chevron-right icon-base"></i>
                </a>
            </div>
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->

                <nav
                    class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
                    id="layout-navbar"
                >
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-xl-0 d-xl-none me-3">
                        <a class="nav-item nav-link me-xl-6 px-0" href="javascript:void(0)">
                            <i class="icon-base ti tabler-menu-2 icon-md"></i>
                        </a>
                    </div>

                    <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
                        <div class="navbar-nav align-items-center">
                            <div class="nav-item dropdown me-xl-0 me-2">
                                <a
                                    class="nav-link dropdown-toggle hide-arrow"
                                    id="nav-theme"
                                    href="javascript:void(0);"
                                    data-bs-toggle="dropdown"
                                >
                                    <i class="icon-base ti tabler-sun icon-md theme-icon-active"></i>
                                    <span class="d-none ms-2" id="nav-theme-text">Toggle theme</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-start" aria-labelledby="nav-theme-text">
                                    <li>
                                        <button
                                            type="button"
                                            class="dropdown-item align-items-center active"
                                            data-bs-theme-value="light"
                                            aria-pressed="false"
                                        >
                                            <span
                                                ><i class="icon-base ti tabler-sun icon-md me-3" data-icon="sun"></i
                                                >Light</span>
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            type="button"
                                            class="dropdown-item align-items-center"
                                            data-bs-theme-value="dark"
                                            aria-pressed="true"
                                        >
                                            <span
                                                ><i
                                                    class="icon-base ti tabler-moon-stars icon-md me-3"
                                                    data-icon="moon-stars"
                                                ></i
                                                >Dark</span>
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            type="button"
                                            class="dropdown-item align-items-center"
                                            data-bs-theme-value="system"
                                            aria-pressed="false"
                                        >
                                            <span
                                                ><i
                                                    class="icon-base ti tabler-device-desktop-analytics icon-md me-3"
                                                    data-icon="device-desktop-analytics"
                                                ></i
                                                >System</span>
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <ul class="navbar-nav align-items-center ms-md-auto flex-row">
                            <!-- User -->
                            @if (auth()->check())
                                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                    <a
                                        class="nav-link dropdown-toggle hide-arrow p-0"
                                        href="javascript:void(0);"
                                        data-bs-toggle="dropdown"
                                    >
                                        <div class="avatar avatar-online">
                                            <img
                                                src="{{
                                                    asset(auth()->user()->image === null ? 'assets/img/avatars/1.png'
                                                    : auth()->user()->image)
                                                }}"
                                                alt
                                                class="rounded-circle"
                                            />
                                        </div>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="#">
                                                <div class="d-flex">
                                                    <div class="me-3 flex-shrink-0">
                                                        <div class="avatar avatar-online">
                                                            <img
                                                                src="{{ asset(auth()->user()->image === null ? "assets/img/avatars/1.png" : auth()->user()->image) }}"
                                                                alt
                                                                class="w-px-40 rounded-circle h-auto"
                                                            />
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-0">{{ auth()->user()->name }}</h6>
                                                        <small class="text-body-secondary">{{ Str::upper(auth()->user()->role) }}</small>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>

                                        <li>
                                            <div class="dropdown-divider mx-n2 my-1"></div>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route("user.profile", auth()->user()) }}">
                                                <i class="icon-base ti tabler-user icon-md me-3"></i
                                                ><span> My Profile </span>
                                            </a>
                                        </li>

                                        <li>
                                            <div class="dropdown-divider mx-n2 my-1"></div>
                                        </li>
                                        <li>
                                            <a
                                                class="dropdown-item d-flex align-items-center"
                                                href="javascript:void(0);"
                                            >
                                                <i class="icon-base ti tabler-power icon-md me-3"></i>
                                                <span>
                                                    <form action="{{ route("logout") }}" method="post">
                                                        @csrf
                                                        <button class="text-danger card">Log Out</button>
                                                    </form>
                                                </span>
                                            </a>
                                        </li>
                                    </ul>

                            @endif
                            </li>
                            <!--/ User -->
                        </ul>
                    </div>
                </nav>

                <!-- / Navbar -->
                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl container-p-y flex-grow-1">{{ $slot ?? "Home Page" }}</div>
                    <!-- / Content -->

                    <!-- Footer -->
                    <x-footer></x-footer>
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>

        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>
    <!-- 1. jQuery must load before Select2 and Bootstrap -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>

    <!-- 2. Select2 JS -->

    <!-- 3. Other Vendor & UI Scripts -->
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/pickr/pickr.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/hammer/hammer.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset("assets/vendor/libs/select2/select2.js") }}"></script>

    <!-- 4. Main & Custom Scripts -->
    {{-- <script src="{{ asset('assets/js/question.js') }}"></script>
    <script src="{{ asset('assets/js/show.js') }}"></script>
    <script src="{{ asset('assets/js/edit.js') }}"></script> --}}
    <script src="{{ asset('assets/js/forms-selects.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@algolia/autocomplete-js.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/template-customizer.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/all.js') }}"></script>
</body>
</html>
