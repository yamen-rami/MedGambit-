<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'Display Quiz ' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link rel="stylesheet" href="{{ asset('assets/css/quiztheme.css') }}" />
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
    <script>
        (() => {
            const theme = localStorage.getItem('quiz-theme') || 'light';
            
            document.documentElement.setAttribute('data-theme', theme);
        })();
        </script>
        <link rel="stylesheet" href="{{ asset("assets/css/all.css") }}">
        {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" /> --}}
</head>

<body>
    <div class="mg-root">
        <main class="main">
            <header class="topbar">
                <div class="topbar-left">
                    <i class="fa-solid fa-shield-halved topbar-icon"></i>
                    <div>
                        <div class="topbar-sub">{{ $topic ?? 'Quiz Battle' }}</div>
                    </div>
                </div>

                <div class="topbar-right">
                    <button type="button" onclick="toggleDarkMode()" class="theme-toggle" id="theme-toggle">
                        <i class="fa-solid fa-moon"></i>
                    </button>

                    <button type="button" class="surrender-btn" data-bs-toggle="modal"
                        data-bs-target="#staticBackdrop">
                        <i class="fa-solid fa-flag"></i>
                        Exit Quiz
                    </button>
                    <!-- Button trigger modal -->
                    <!-- Large Modal -->
                    <!--
                        Large Modal
                      <div class="modal-dialog modal-xl">...</div>
                        Meduim Modal
                      <div class="modal-dialog modal-lg">...</div>
                        Small Modal
                      <div class="modal-dialog modal-sm">...</div>
                    -->

                    <!-- Modal -->
                    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false"
                        tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content question-card">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5 gauge-label" id="staticBackdropLabel">
                                        Are You Sure To Quit Quiz
                                    </h1>
                                    <button type="button" class="btn-close gauge-label text-white"
                                        data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <h1 class="gauge-label fs-6">
                                        Your Answers Will Saved And Your <span class="text-danger">Elo</span> Will
                                        Change
                                    </h1>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        Close
                                    </button>
                                    <button type="button" id="quitQuiz" class="btn btn-danger">
                                        Quit QUiz
                                        {{-- <a href="{{ route("home") }}"  style="text-decoration: none" class="text-white">Quit Game</a> --}}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            {{ $slot }}
        </main>
    </div>
    @stack('scripts')

    <script>
        let quitQuiz = document.querySelector('#quitQuiz');
        quitQuiz.addEventListener('click', (event) => {
            window.dispatchEvent(new CustomEvent('quit-quiz'));
        });
    </script>
    <script src="{{ asset("assets/js/all.js") }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"></script>
</body>

</html>
