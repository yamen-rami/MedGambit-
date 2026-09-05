<x-quizlayout>
    <div>
        
        @push('style')
            <link rel="stylesheet" href="{{ asset('assets/css/shimmer.css') }}">
        @endpush
        <div style="
        z-index: 1000;
            position: absolute;
            top: 10%; 
            right: 50%;
            color:var(--text);
            
        "
            class="py-2 my-2 px-5 rounded-2 " id="textHasCopied">

        <livewire:waiting :game="$game"/>
        </div>
        <div class="skeleton-wrap">

            <div class="grid">

                <div class="card main">
                    <div class="players-grid my-5">
                        <div class="player-card-skeleton">
                            <div class="player-avatar skeleton"></div>
                            <div class="player-name skeleton">
                                <div class="text-white">
                                </div>
                            </div>
                            <div class="player-score skeleton"></div>
                        </div>
                        <div class="vs-text skeleton text-center ">
                            <span class="text-center text-white  rounded-circle   p-3 skeleton">Vs</span>
                        </div>
                        <div class="player-card-skeleton">
                            <div class="player-avatar skeleton"></div>
                            <div class="player-name skeleton"></div>
                            <div class="player-score skeleton"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="skeleton w-90 h-14"></div>
                        <div class="skeleton w-50 h-22 pill"></div>
                    </div>
                    <div class="skeleton w-70p h-20 mt-20 mb-20"></div>
                    <div class="options">
                        <div class="skeleton h-44 r8"></div>
                        <div class="skeleton h-44 r8"></div>
                        <div class="skeleton h-44 r8"></div>
                        <div class="skeleton h-44 r8"></div>
                    </div>
                    <div class="row">
                        <div class="skeleton w-100 h-14"></div>
                        <div class="skeleton w-60 h-14"></div>
                    </div>
                    <div class="row mt-20">
                        <div class="skeleton w-40p h-38 r8 mr-1"></div>
                        <div class="skeleton w-40p h-38 r8"></div>
                    </div>
                </div>

                <div class="side">
                    <div class="card side-card">
                        <div class="skeleton w-100 h-14 mb-14"></div>
                        <div class="skeleton w-80p h-12 mb-8"></div>
                        <div class="skeleton w-60p h-16 mb-14"></div>
                        <div class="skeleton w-80p h-12 mb-8"></div>
                        <div class="skeleton w-40p h-16"></div>
                    </div>

                    <div class="card side-card">
                        <p style="color:var(--text)">Challenge Token : </p>
                        <div id="challenge_token" class="skeleton py-2 px-3 my-2" style="color:var(--text)">
                            <span class="text-end d-block" id="copyBtn"
                                style="color: var(--text) ; background-color: transparent;"> <svg
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-copy-icon lucide-copy">
                                    <rect width="14" height="14" x="8" y="8" rx="2" ry="2" />
                                    <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2" />
                                </svg></span>
                            <span>
                            medgambit.test/friend/challenge/
                            </span>
                            <span>
                                {{ $game->challenge_token }}
                            </span>
                        </div>
                    </div>

                    <div class="card side-card center">
                        <div class="skeleton w-90 h-14 center-x mb-16"></div>
                        <div class="skeleton circle mb-10 center-x"></div>
                        <div class="skeleton w-60 h-12 center-x"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            const themeToggle = document.getElementById('theme-toggle');
            const root = document.documentElement;

            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    const isDark = root.getAttribute('data-theme') === 'dark';

                    root.setAttribute('data-theme', isDark ? 'light' : 'dark');

                    themeToggle.innerHTML = isDark ? '<i class="fa-solid fa-moon"></i>' :
                        '<i class="fa-solid fa-sun"></i>';
                });
            }
            document.getElementById('copyBtn').addEventListener('click', async () => {
                const text = document.getElementById('challenge_token').textContent.trim();

                if (navigator.clipboard) {
                    await navigator.clipboard.writeText(text);
                } else {
                    const textarea = document.createElement('textarea');

                    textarea.value = text;
                    textarea.style.position = 'fixed';
                    textarea.style.opacity = '0';

                    document.body.appendChild(textarea);
                    textarea.select();

                    document.execCommand('copy');

                    textarea.remove();
                    setTimeout(() => {
                        const textCopied = document.getElementById("textHasCopied");

                        textCopied.innerHTML = `
    <span>✓</span>
    Copied Successfully
`;

                        textCopied.style.background = "green";
                    }, 1000);
                }

            });
        </script>
    @endpush

</x-quizlayout>
