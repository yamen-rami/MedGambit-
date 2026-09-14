<x-user-layout>
    @push('style')
        <link rel="stylesheet" href="{{ asset("assets/css/home.css") }}">
    @endpush

    <main>

        <!-- Hero Introduction Section -->
        <section class="hero-box" id="home">
            <div class="hero-glow"></div>
            <div class="hero-content">
                <div class="hero-badge">
                    <span class="dot"></span>
                    Medical Learning Platform
                </div>
                <h1 class="hero-title">MedGambit — turning medical study into head-to-head challenges.</h1>
                <p class="hero-sub">MedGambit is a quiz-battle platform built for medical students who want to learn by
                    competing. Face an opponent in real time, answer clinical questions under pressure, and track your
                    progress as you climb through topics and specialties.</p>
                <div class="hero-actions">
                    <a class="btn-primary" href="#services">Explore the Platform</a>
                    <a class="btn-secondary" href="#about">Meet the Team</a>
                </div>
            </div>
        </section>

        <!-- What We Do Section -->
        <section class="section" id="services">
            <div class="section-head">
                <div>
                    <span class="section-eyebrow">Capabilities</span>
                    <h2 class="section-title">Medical Learning Challenges</h2>
                </div>
                <p class="section-desc">A quiz platform built around competition, speed, and real clinical knowledge —
                    designed to make studying feel like a match, not a chore.</p>
            </div>

            <div class="services-grid">
                <!-- Card 1 -->
                <div class="service-card">
                    <div class="service-icon indigo">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h3 class="service-title">Think Beyond the Textbook</h3>
                    <p class="service-text">
                        Practice internationally relevant medical questions inspired by leading Q-banks, covering basic sciences, clinical medicine, and real-world diagnostic reasoning for students.
                    </p>
                    <div class="tag-row">
                        <span class="tag">International</span>
                        <span class="tag">Question Bank</span>
                        <span class="tag">Difficulty Levels</span>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="service-card">
                    <div class="service-icon purple">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h3 class="service-title">Master the Core Before the Details</h3>
                    <p class="service-text">Master the core concepts first, then filter by specialty, topic, skill, difficulty, and length to target exactly what you need.</p>
                    <div class="tag-row">
                        <span class="tag">Filter</span>
                        <span class="tag">Speciality</span>
                        <span class="tag">Branch of medicine</span>
                        <span class="tag">General Medical Concept </span>
                        <span class="tag">Memorization</span>


                    </div>
                </div>

                <!-- Card 3 -->
                <div class="service-card">
                    <div class="service-icon cyan">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm6 0V9a2 2 0 00-2-2h-2a2 2 0 00-2 2v10m10 0v-4a2 2 0 00-2-2h-2a2 2 0 00-2 2v4"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h3 class="service-title">Put Your Rank on the Line</h3>
                    <p class="service-text">Build your ELO, challenge students worldwide under the same questions and timer, climb the ranks, and prove your medical knowledge.</p>
                    <div class="tag-row">
                        <span class="tag">Elo Rating</span>
                        <span class="tag">Player Vs Player</span>
                        <span class="tag">Challenge</span>
                        <span class="tag">Show Excellence</span>

                    </div>
                </div>
            </div>
        </section>

        <!-- About Us Section -->
        <section class="section" id="about" style="padding-bottom:48px;">
            <div class="section-head">
                <div>
                    <span class="section-eyebrow"> Team</span>
                    <h2 class="section-title">Built by Two Brothers, Made for Every Med Student</h2>
                </div>
                <p class="section-desc">MedGambit is built and maintained by two brothers who wanted a better way to study
                    for exams — together.</p>
            </div>

            <div class="about-card">
                <div class="about-grid">
                    <!-- Founder 1: Yamen -->
                    <div class="about-portrait">
                        <img alt="Yamen - Co-Founder"
                            src="https://lh3.googleusercontent.com/aida/AEtjO1VkO7ZAd9VX-wAaZNvbQ2E-t5Sn_ygl245shrYF3x8VKsA956iJqSrp_2z09R1tx8DK3kWo1DURl959t4ln47K32oKiUjvvZeut_AwVVVAlUWQOwO5aszjWBZy9CU9-4vHoN_gFuLORz_pGeC6_JsIlw-qLweLA7B-QdLLMEhjLV_g7U8QRE3HcsN8IbwX3SFTAV267qupU7XkM5JXwx91kfsjzq9y_taKcyDmWXCKoPYco0lFRfYR3Zyg">
                        <div class="portrait-tag">
                            <p class="name">Yamen</p>
                            <p class="role">Co-Founder</p>
                        </div>
                    </div>

                    <!-- Founder 2: Mohammed -->
                    <div class="about-portrait">
                        <div class="avatar-initials">M</div>
                        <div class="portrait-tag">
                            <p class="name">Mohammed</p>
                            <p class="role">Co-Founder</p>
                        </div>
                    </div>

                    <!-- Story & Stats -->
                    <div class="about-body">
                        <div>
                            <h3 class="about-heading">Two builders, one platform for medical learning.</h3>
                            <p class="lead">MedGambit is built by Yamen and Mohammed Abuwarda, who set out to make medical
                                exam prep feel less like a grind and more like a match — head-to-head quiz battles built
                                around real clinical questions.</p>
                            <p class="sub">Every feature is shaped around one goal: helping medical students learn
                                faster by testing themselves against each other, not just against a question bank.</p>
                        </div>

                        <div class="stats-row">
                            <div>
                                <p class="stat-value">2</p>
                                <div class="stat-label">Founders</div>
                            </div>
                            <div>
                                <p class="stat-value accent">1v1</p>
                                <div class="stat-label">Battle Mode</div>
                            </div>
                            <div>
                                <p class="stat-value">100%</p>
                                <div class="stat-label">Built for Med Students</div>
                            </div>
                        </div>

                        <div class="about-cta-row">
                            <a href="{{ route("config.game") }}" class="btn-gradient text-white">Try a Challenge</a>
                            <span class="about-note">Built by Yamen &amp; Mohammed</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
  

  

</x-user-layout>
