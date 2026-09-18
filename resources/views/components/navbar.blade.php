<header class="topbar">
    <div class="top-left">
        <div class="brand">
            <span class="brand-mark">MG</span> MEDGAMBIT
            <span class="version">V1</span>
            <button class="nav-toggle" id="navToggle" type="button" aria-label="Open navigation" aria-expanded="false">
                <svg class="open-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">
                    <rect width="18" height="18" x="3" y="3" rx="2" />
                    <path d="M15 3v18" />
                    <path d="m10 15-3-3 3-3" />
                </svg>
                <svg class="close-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">
                    <rect width="18" height="18" x="3" y="3" rx="2" />
                    <path d="M15 3v18" />
                    <path d="m8 9 3 3-3 3" />
                </svg>
            </button>
        </div>
        <nav class="mg-nav nav" aria-label="Main navigation">
            <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
            @auth
                <a class="nav-link {{ request()->routeIs('gambits') ? 'active' : '' }}"
                    href="{{ route('gambits') }}">Gambits</a>
                <a class="nav-link {{ request()->routeIs('config.game') ? 'active' : '' }}"
                    href="{{ route('config.game') }}">Arena</a>
                <a class="nav-link {{ request()->routeIs('start.quiz') ? 'active' : '' }}"
                    href="{{ route('start.quiz') }}">Quizzes</a>
            @endauth
            @guest
                <a class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">Login</a>
                <a class="nav-link {{ request()->routeIs('register') ? 'active' : '' }}"
                    href="{{ route('register') }}">Register</a>
            @endguest
        </nav>
    </div>
    <div class="top-right">
        

        <button class="theme-toggle icon-btn" id="themeToggle" type="button" aria-label="Switch theme">
            <span class="moon material-symbols-outlined">dark_mode</span>
            <span class="sun material-symbols-outlined">light_mode</span>
        </button>
        @auth

            <div class="profile" aria-label="User profile">
                <div class="{{ auth()->user()->image ? '' : 'avatar' }}">
                    @if (auth()->user()->image)
                        <img class="avatar" src="{{ asset(auth()->user()->image) }}" alt="">
                    @else
                        {{ ucfirst(Str::limit(auth()->user()->name, 1, '')) }}
                    @endif
                </div>
                <div class="profile-info">
                    <span class="profile-name">Dr. {{ auth()->user()->name }}</span><span class="profile-role">Elo
                        {{ auth()->user()->rank }}</span>
                </div>
                <button class="profile-menu" type="button" aria-label="Open profile menu">
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="profile-dropdown" id="login-profile-dropdown">
                    <a href="{{ route('user.profile', auth()->user()) }}"><i class="bi bi-person-circle"></i>Profile
                        settings</a>

                    <hr class="dropdown-divider">
                    <form action="{{ route('logout') }}" method="post">
                        @csrf
                        <button class="btn bg-body text-danger">
                            Logout
                            <i class="bi bi-box-arrow-right"></i>
                        </button>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</header>
