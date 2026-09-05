<?php

use Livewire\Component;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Reactive;
new class extends Component {
    //
    // #[Reactive()]
    public $questions;
    public $attempts;
    public $selectedQuestion = null;
    public function mount(?Collection $questions, ?Collection $attempts)
    {
        $this->questions = $questions;
        $this->attempts = $attempts;
    }
    public function showQuestion(int $questionId)
    {
        $this->selectedQuestion = $this->questions->findOrFail($questionId);
        if (!$this->selectedQuestion) {
            return;
        }
        $this->selectedQuestion->loadMissing('options', 'correctAnswer');
        $this->attempts->loadMissing('answers', 'user');
    }
};
?>

<div class="table-scroll">
    @push('style')
        <style>
            /* =========================================================
       QUIZ DETAILS MODAL
       Uses the same theme variables as the rest of the UI
       ========================================================= */

            .qd-modal .qd-content {
                background-color: var(--surface) !important;
                border: 1px solid var(--outline-variant) !important;
                border-radius: 10px !important;
                color: var(--on-surface) !important;
                box-shadow: 0 0 40px rgba(0, 0, 0, 0.25) !important;
            }

            /* Header */
            .qd-modal .qd-header {
                background-color: var(--surface) !important;
                border-bottom: 1px solid var(--outline-variant) !important;
                padding: 1rem 1.25rem !important;
            }

            .qd-modal .qd-title {
                color: var(--primary) !important;
                font-family: var(--font-code) !important;
                font-weight: 700 !important;
                letter-spacing: 1px !important;
                margin: 0 !important;
                font-size: 0.95rem !important;
            }

            /* X close button */
            .qd-modal .qd-close {
                width: 1em !important;
                height: 1em !important;
                padding: 0.5em !important;
                background-color: transparent !important;
                border: 0 !important;
                opacity: 0.75 !important;
                cursor: pointer !important;

                /* Bootstrap close icon */
                background-image: none !important;

                color: var(--on-surface) !important;
                position: relative !important;
            }

            .qd-modal .qd-close::before,
            .qd-modal .qd-close::after {
                content: "" !important;
                position: absolute !important;
                width: 16px !important;
                height: 2px !important;
                background-color: var(--on-surface) !important;
                left: 50% !important;
                top: 50% !important;
                border-radius: 2px !important;
            }

            .qd-modal .qd-close::before {
                transform: translate(-50%, -50%) rotate(45deg) !important;
            }

            .qd-modal .qd-close::after {
                transform: translate(-50%, -50%) rotate(-45deg) !important;
            }

            .qd-modal .qd-close:hover {
                opacity: 1 !important;
            }

            /* Body */
            .qd-modal .qd-body {
                background-color: var(--surface) !important;
                padding: 1.25rem !important;
            }

            /* Prompt label */
            .qd-modal .qd-prompt-label {
                color: var(--primary) !important;
                font-family: var(--font-code) !important;
                font-size: 0.7rem !important;
                letter-spacing: 1px !important;
                margin-bottom: 0.5rem !important;
            }

            /* Question / prompt */
            .qd-modal .qd-prompt {
                background-color: var(--surface-container-low) !important;
                border: 1px solid var(--outline-variant) !important;
                border-radius: 8px !important;
                padding: 1rem 1.25rem !important;
                color: var(--on-surface) !important;
                font-size: 0.9rem !important;
                line-height: 1.6 !important;
                margin-bottom: 1.25rem !important;
            }

            /* Options container */
            .qd-modal .qd-options {
                display: flex !important;
                flex-direction: column !important;
                gap: 0.6rem !important;
            }

            /* Option */
            .qd-modal .qd-option {
                background-color: var(--surface-container-low) !important;
                border: 1px solid var(--outline-variant) !important;
                border-radius: 8px !important;
                padding: 0.85rem 1rem !important;
                cursor: pointer !important;
                transition:
                    border-color 0.15s ease,
                    background-color 0.15s ease !important;
            }

            .qd-modal .qd-option:hover {
                border-color: var(--primary) !important;
                background-color: var(--surface-container) !important;
            }

            /* Correct option */
            .qd-modal .qd-option.is-correct {
                border-color: var(--success) !important;
                background-color: color-mix(in srgb,
                        var(--success) 8%,
                        var(--surface)) !important;
            }

            /* Option row */
            .qd-modal .qd-option-row {
                display: flex !important;
                align-items: center !important;
                gap: 0.75rem !important;
                flex-wrap: wrap !important;
            }

            /* A / B / C / D key */
            .qd-modal .qd-option-key {
                background-color: var(--surface-container-high) !important;
                color: var(--primary) !important;
                font-family: var(--font-code) !important;
                font-weight: 700 !important;
                font-size: 0.75rem !important;

                width: 24px !important;
                height: 24px !important;
                min-width: 24px !important;

                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;

                border-radius: 5px !important;
            }

            /* Correct option key */
            .qd-modal .qd-option.is-correct .qd-option-key {
                background-color: var(--success) !important;
                color: var(--background) !important;
            }

            /* Option text */
            .qd-modal .qd-option-label {
                color: var(--on-surface) !important;
                font-size: 0.85rem !important;
                flex: 1 1 auto !important;
            }

            /* Correct badge */
            .qd-modal .qd-correct-badge {
                color: var(--success) !important;
                font-family: var(--font-code) !important;
                font-size: 0.7rem !important;
                letter-spacing: 0.5px !important;
                font-weight: 700 !important;
                white-space: nowrap !important;
            }

            /* Explanation */
            .qd-modal .qd-explanation {
                margin-top: 0.75rem !important;
                padding-top: 0.75rem !important;
                border-top: 1px dashed var(--outline-variant) !important;
                color: var(--on-surface-variant) !important;
                font-size: 0.8rem !important;
                line-height: 1.5 !important;
            }

            /* Footer */
            .qd-modal .qd-footer {
                background-color: var(--surface) !important;
                border-top: 1px solid var(--outline-variant) !important;
                padding: 0.85rem 1.25rem !important;
            }

            /* Footer close button */
            .qd-modal .qd-close-btn {
                background-color: var(--surface-container-high) !important;
                color: var(--on-surface) !important;
                border: 1px solid var(--outline-variant) !important;

                font-family: var(--font-code) !important;
                font-size: 0.75rem !important;
                letter-spacing: 1px !important;

                padding: 0.5rem 1.25rem !important;
                border-radius: 6px !important;

                cursor: pointer !important;
                transition:
                    background-color 0.15s ease,
                    color 0.15s ease,
                    border-color 0.15s ease !important;
            }

            .qd-modal .qd-close-btn:hover {
                background-color: var(--surface-container-highest) !important;
                color: var(--on-surface) !important;
                border-color: var(--primary) !important;
            }
        </style>
    @endpush
    <div class="modal fade modal-xl qd-modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false"
        tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content qd-content">

                <div class="modal-header qd-header">
                    <h1 class="modal-title fs-5 qd-title" id="staticBackdropLabel">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round"
                            class="lucide lucide-shield-question-mark-icon lucide-shield-question-mark">
                            <path
                                d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" />
                            <path d="M9.1 9a3 3 0 0 1 5.82 1c0 2-3 3-3 3" />
                            <path d="M12 17h.01" />
                        </svg>
                        QUESTION DETAIL
                    </h1>
                    <button type="button" class="btn-close qd-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body qd-body">

                    <div class="qd-prompt-label">Question Details </div>
                    <div class="qd-prompt">
                        {{ $this->selectedQuestion?->content }}
                    </div>

                    @if (isset($selectedQuestion))
                        <div class="qd-options">
                            @foreach ($selectedQuestion?->options as $option)
                                @php
                                    $correct = $selectedQuestion?->correctAnswer?->id;
                                    $isCorrect = $option->id == $correct;
                                @endphp

                                <div x-data="{ showEx: false }" class="qd-option {{ $isCorrect ? 'is-correct' : '' }}"
                                    x-cloak @click="showEx = !showEx">

                                    <div class="qd-option-row">
                                        <span class="qd-option-key">{{ $option->name }}</span>
                                        <span class="qd-option-label">{{ $option->content }}</span>

                                        @if ($isCorrect)
                                            <span class="qd-correct-badge">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="lucide lucide-check-icon lucide-check">
                                                    <path d="M20 6 9 17l-5-5" />
                                                </svg>

                                            </span>
                                        @endif
                                    </div>

                                    <div x-show="showEx" x-collapse class="qd-explanation">
                                        <p class="mb-0">{{ $option->explanation }}</p>
                                    </div>
                                </div>
                            @endforeach
                            <div>
                                <p>High Yield </p>
                                <p class="ps-5">
                                    {{ $selectedQuestion?->high_yield }}
                                </p>
                                <hr>
                                <p>Question Explanation</p>
                                <p class="ps-5">
                                    {{ $selectedQuestion?->main_explanation }}
                                </p>
                            </div>
                        </div>

                    @endif

                </div>

                <div class="modal-footer qd-footer">
                    <button type="button" class="btn qd-close-btn col-lg-3" data-bs-dismiss="modal">
                        Back To Results
                    </button>
                </div>

            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th class="col-question">QUESTION DATA</th>
                @foreach ($attempts as $attempt)
                    <th class="col-player">{{ $attempt->user->name }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($questions as $question)
                <tr>
                    <td class="num-cell">
                        {{ $loop->iteration < 9 ? '0' . $loop->iteration : $loop->iteration }}
                    </td>
                    <td class="question-cell" wire:click='showQuestion({{ $question->id }})' data-bs-toggle="modal"
                        data-bs-target="#staticBackdrop">
                        {{ Str::limit($question->content, 30, '') }}
                    </td>

                    @foreach ($attempts as $attempt)
                        @php
                            $answer = $attempt->answers->where('question_id', $question->id)->first();
                        @endphp
                        <td class="result-cell">
                            @if ($answer && $answer->is_correct)
                                <svg class="text-success" xmlns="http://www.w3.org/2000/svg" width="16"
                                    height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-check-icon lucide-check">
                                    <path d="M20 6 9 17l-5-5" />
                                </svg>
                            @else
                                <svg class = "text-danger " xmlns="http://www.w3.org/2000/svg" width="16"
                                    height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-x-icon lucide-x">
                                    <path d="M18 6 6 18" />
                                    <path d="m6 6 12 12" />
                                </svg>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach

        </tbody>
    </table>
</div>
