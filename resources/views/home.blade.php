@extends('layouts.main')
@push('styles')
    <link href="{{ asset('assets/css/story.css') }}" rel="stylesheet" />
@endpush
@section('content')
    <section class="story-hero">
        <div>
            <div class="story-kicker"><i></i> EDITORIAL PRODUCT STORY · PROTOCOL 2.4</div>
            <h1>Medicine is hard.<br><span>Practice should be worth the struggle.</span></h1>
            <p>Train your clinical thinking, challenge others, learn from mistakes, and improve one question at a time.
            </p>
            <div class="story-actions"><a class="btn btn-primary btn-lg" href="#practice"><span
                        class="material-symbols-outlined align-middle me-1">play_circle</span>Start practicing</a><a
                    class="btn btn-outline-secondary btn-lg" href="#arena"><span
                        class="material-symbols-outlined align-middle me-1">swords</span>Enter the arena</a></div>
        </div>
    </section>
    <div class="story-shell">
        <div class="story-spine" id="storySpine">
            <article class="story-section is-active" id="the-idea">
                <div class="story-node"></div>
                <div class="story-label">01 — The idea · Cognitive resonance</div>
                <h2>What if medical practice felt more like improving at chess?</h2>
                <p>In chess, losing is part of improving. You make a mistake, find the blunder, understand why it
                    happened, and play again. Medicine requires that same habit of reflection, with far more important
                    consequences.</p>
                <div class="story-card story-quote">“Lose the question. Learn the lesson. Come back stronger.”<div
                        class="story-label mt-3 mb-0">— The MedGambit principle</div>
                </div>
            </article>
            <article class="story-section" id="about-us">
                <div class="story-node"></div>
                <div class="story-label">02 — Our story · Genesis</div>
                <h2>Built from medicine, chess, and a love of solving problems.</h2>
                <p>MedGambit brings competition, rating, reviewing mistakes, and continuous improvement into medical
                    education. Questions are organized by both specialty and the skill required to solve them.</p>
                <div class="story-grid">
                    <div class="story-metric"><strong>Medicine</strong><span>Clinical dilemmas with meaningful
                            context.</span></div>
                    <div class="story-metric"><strong>Competition</strong><span>Challenge yourself and another
                            student.</span></div>
                    <div class="story-metric"><strong>Reflection</strong><span>Review the reasoning behind every
                            result.</span></div>
                </div>
            </article>
            <article class="story-section" id="gambits">
                <div class="story-node"></div>
                <div class="story-label">03 — Gambits · Targeted slicing</div>
                <h2>Shape your practice. Find your gambit.</h2>
                <p>Gambits narrow thousands of questions into exactly the practice you need: difficulty, length, branch,
                    specialty, skill, and reference.</p>
                <div class="story-card d-flex flex-wrap align-items-center gap-2"><span
                        class="badge text-bg-primary">Hard</span><span
                        class="badge text-bg-secondary">Cardiology</span><span
                        class="badge text-bg-secondary">Diagnosis</span><span class="badge text-bg-primary">ECG
                        interpretation</span><span class="ms-auto text-secondary small mono">10 questions · ready</span>
                </div>
            </article>
            <article class="story-section" id="arena">
                <div class="story-node"></div>
                <div class="story-label">04 — Arena · Live diagnostic duel</div>
                <h2>Competition turns attention into commitment.</h2>
                <p>Enter a focused 1v1 clinical scenario. Read quickly, reason carefully, and see how your decisions
                    compare in real time.</p>
                <div class="story-card">
                    <div class="d-flex justify-content-between border-bottom pb-3 mb-3"><span
                            class="mono text-secondary">RANKED DUEL / LIVE</span><span class="badge text-bg-success">●
                            Online</span></div>
                    <div class="row g-3 text-center">
                        <div class="col"><small class="text-secondary d-block">YOU</small><strong
                                class="text-primary">14.2s</strong></div>
                        <div class="col d-flex align-items-center justify-content-center text-secondary">VS</div>
                        <div class="col"><small class="text-secondary d-block">OPPONENT</small><strong>18.6s</strong>
                        </div>
                    </div>
                </div>
            </article>
            <article class="story-section" id="review">
                <div class="story-node"></div>
                <div class="story-label">05 — Review · Post-mortem analysis</div>
                <h2>A wrong answer should tell you what to do next.</h2>
                <p>The score tells you how you performed. The review tells you how to improve: correct answers, weak
                    skills, weak specialties, and the next practice target.</p>
                <div class="progress mt-4" role="progressbar" aria-label="Review progress">
                    <div class="progress-bar" style="width: 72%"></div>
                </div>
            </article>
            <article class="story-section" id="practice">
                <div class="story-node"></div>
                <div class="story-label">06 — Begin · The diagnostic frontier</div>
                <h2>Your next question might expose your next weakness.</h2>
                <p>Find it. Solve it. Learn from it. Then go again.</p><a class="btn btn-primary mt-3"
                    href="index.html"><span class="material-symbols-outlined align-middle me-1">bolt</span>Start
                    practicing</a>
            </article>
            <article class="story-section" id="precision">
                <div class="story-node"></div>
                <div class="story-label">07 — Precision · Cognitive vectors</div>
                <h2>A question can test more than one thing.</h2>
                <p>MedGambit considers the skill required to solve each question, making practice more precise than
                    subject-only question banks.</p>
                <div class="story-card">
                    <div class="d-flex flex-wrap gap-2"><span class="badge text-bg-primary">Diagnosis</span><span
                            class="badge text-bg-primary">Clinical reasoning</span><span class="badge text-bg-primary">ECG
                            interpretation</span></div>
                </div>
            </article>
            <article class="story-section" id="community">
                <div class="story-node"></div>
                <div class="story-label">08 — Community · Collaborative pedagogy</div>
                <h2>Built with people who understand the exam room.</h2>
                <p>MedGambit grows with medical students and colleagues who understand education. The goal is useful
                    questions, clear reasoning, meaningful explanations, and real learning value.</p>
                <div class="story-grid">
                    <div class="story-metric"><strong>Clear reasoning</strong><span>Questions framed around clinical
                            dilemmas.</span></div>
                    <div class="story-metric"><strong>Good classification</strong><span>Indexed by specialty, skill, and
                            reference.</span></div>
                    <div class="story-metric"><strong>Real learning value</strong><span>Designed for clinical
                            intuition.</span></div>
                </div>
            </article>
            <article class="story-section" id="why">
                <div class="story-node"></div>
                <div class="story-label">09 — Why MedGambit · Synthesis</div>
                <h2>Practice precisely. Learn deeply. Improve continuously.</h2>
                <div class="story-card p-0 overflow-hidden">
                    <div class="p-3 border-bottom"><strong>Practice precisely.</strong><span
                            class="d-block text-secondary small mt-1">Find the questions you actually need.</span></div>
                    <div class="p-3 border-bottom"><strong>Learn from mistakes.</strong><span
                            class="d-block text-secondary small mt-1">Understand why your reasoning failed.</span></div>
                    <div class="p-3"><strong>Compete and improve.</strong><span
                            class="d-block text-secondary small mt-1">Return stronger than before.</span></div>
                </div>
            </article>
            <article class="story-section" id="final-cta">
                <div class="story-node"></div>
                <div class="story-card text-center py-5">
                    <div class="story-label">10 — The diagnostic frontier</div>
                    <h2 class="mx-auto">Your next question might expose your next weakness.</h2>
                    <p class="mx-auto">Find it. Solve it. Learn from it. Then go again.</p><a
                        class="btn btn-primary btn-lg mt-3" href="index.html"><span
                            class="material-symbols-outlined align-middle me-1">bolt</span>Start practicing</a>
                </div>
            </article>
        </div>
    </div>
    <footer class="story-footer">
        <div class="container d-flex flex-wrap justify-content-between gap-3"><span>MEDGAMBIT · CLINICAL DIAGNOSTIC
                ARENA</span><span>4,800+ VIGNETTES ONLINE · DX-NODE-04</span></div>
    </footer>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/story.js') }}"></script>
@endpush
