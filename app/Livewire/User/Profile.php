<?php

namespace App\Livewire\User;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Profile extends Component
{
    public User $user;
    public array $attemptRows = [];
    public object $stats;
    public object $answerStats;
    public int $rankPosition;

    public string $quizSearch = '';

    public string $quizFilter = 'all';

    public string $gameFilter = 'all';

    public string $name = '';

    public string $email = '';

    public bool $editing = false;

    public array $games = [];

    public function mount(User $user, $attempts, object $stats, object $answerStats, int $rankPosition): void
    {
        $this->user = $user;
        $this->attemptRows = $this->formatAttempts($attempts->getCollection())->all();
        $this->stats = $stats;
        $this->answerStats = $answerStats;
        $this->rankPosition = $rankPosition;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->games = $user->gameAttempts()
            ->with(['game.attempts.user'])
            ->latest()
            ->get()
            ->map(function ($gameAttempt) use ($user): array {
                $opponent = $gameAttempt->game?->attempts
                    ->first(fn ($opponentAttempt) => $opponentAttempt->user_id !== $user->id)?->user;

                return [
                    'game_id' => $gameAttempt->game_id,
                    'difficulty' => $gameAttempt->game?->difficulty ?? 'standard',
                    'opponent_name' => $opponent?->name ?? 'Opponent unavailable',
                    'opponent_email' => $opponent?->email ?? 'No opponent data',
                    'is_winner' => (bool) $gameAttempt->is_winner,
                    'score' => $gameAttempt->score ?? 0,
                    'status' => $gameAttempt->status,
                    'created_at' => $gameAttempt->created_at?->format('M d, Y'),
                ];
            })
            ->values()
            ->all();
    }

    public function filteredAttempts(): Collection
    {
        $search = trim($this->quizSearch);

        if ($search === '') {
            return collect($this->attemptRows);
        }

        return $this->user->attempts()
            ->with('quiz')
            ->withCount([
                'answers',
                'answers as correct_answers_count' => fn ($query) => $query->where('is_correct', true),
            ])
            ->whereHas('quiz', function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($this->quizFilter === 'completed', fn ($query) => $query->where('status', 'finished'))
            ->when($this->quizFilter === 'incomplete', fn ($query) => $query->where('status', 'pending'))
            ->latest()
            ->get()
            ->pipe(fn (Collection $attempts): Collection => $this->formatAttempts($attempts));
    }

    private function formatAttempts(Collection $attempts): Collection
    {
        return $attempts->map(function ($attempt): array {
            $name = trim($attempt->quiz?->name ?? 'Quiz');
            $topic = trim($attempt->quiz?->topic ?? 'General');

            return [
                'name' => match ($name) {
                    'Detected Topic' => 'System Exam Mode',
                    'Detected Learning Quiz' => 'System Learning Quiz',
                    default => $name,
                },
                'quiz_id' => $attempt->quiz_id,
                'quiz_type' => $attempt->quiz?->type,
                'topic' => match ($topic) {
                    'Detected Topic' => 'System Exam Mode',
                    'Detected Learning Quiz' => 'System Learning Quiz',
                    default => $topic,
                },
                'status' => $attempt->status,
                'score' => $attempt->score ?? 0,
                'correct_answers' => $attempt->correct_answers_count ?? 0,
                'answers' => $attempt->answers_count ?? 0,
                'created_at' => $attempt->created_at?->format('M d, Y'),
            ];
        });
    }

    public function filteredGames(): Collection
    {
        return collect($this->games)->when($this->gameFilter !== 'all', function (Collection $games): Collection {
            return $games->filter(fn (array $game) => $this->gameFilter === 'winning'
                ? $game['is_winner']
                : ! $game['is_winner']);
        });
    }

    public function startEditing(): void
    {
        abort_unless(auth()->id() === $this->user->id, 403);

        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->resetValidation();
        $this->editing = true;
    }

    public function cancelEditing(): void
    {
        $this->resetValidation();
        $this->editing = false;
        $this->name = $this->user->name;
        $this->email = $this->user->email;
    }

    public function updateProfile(): void
    {
        abort_unless(auth()->id() === $this->user->id, 403);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user->id)],
        ]);

        $this->user->update($validated);
        $this->user->refresh();
        $this->editing = false;
        $this->dispatch('profile-updated');
    }

    public function render(): View
    {
        return view('livewire.user.profile', [
            'accuracy' => $this->answerStats->answered > 0
                ? round(($this->answerStats->correct / $this->answerStats->answered) * 100, 1)
                : 0,
        ]);
    }
}
