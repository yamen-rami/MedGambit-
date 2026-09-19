<?php

namespace App\Livewire\User;

use App\Models\GameAttempt;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Profile extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public User $user;

    public object $stats;

    public object $answerStats;

    public int $rankPosition;

    public string $quizSearch = '';

    public string $quizFilter = 'all';

    public string $gameFilter = 'all';

    public string $name = '';

    public string $email = '';

    public bool $editing = false;

    public function mount(User $user, object $stats, object $answerStats, int $rankPosition): void
    {
        $this->user = $user;
        $this->stats = $stats;
        $this->answerStats = $answerStats;
        $this->rankPosition = $rankPosition;
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function updatingQuizSearch(): void
    {
        $this->resetPage('quizPage');
    }

    public function updatingQuizFilter(): void
    {
        $this->resetPage('quizPage');
    }

    public function updatingGameFilter(): void
    {
        $this->resetPage('gamePage');
    }

    public function filteredAttempts(): LengthAwarePaginator
    {
        $attempts = $this->user->attempts()
            ->with('quiz:id,name,topic,type,questions_number')
            ->when(trim($this->quizSearch) !== '', function ($query): void {
                $query->whereHas('quiz', function ($query): void {
                    $query->where('name', 'like', '%'.trim($this->quizSearch).'%');
                });
            })
            ->when($this->quizFilter === 'completed', fn ($query) => $query->where('status', 'finished'))
            ->when($this->quizFilter === 'incomplete', fn ($query) => $query->where('status', 'pending'))
            ->latest()
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'quizPage');

        return $attempts->setCollection($this->formatAttempts($attempts->getCollection()));
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
                'correct_answers' => (int) ($attempt->score ?? 0),
                'answers' => (int) ($attempt->quiz?->questions_number ?? 0),
                'created_at' => $attempt->created_at?->format('M d, Y'),
            ];
        });
    }

    public function filteredGames(): LengthAwarePaginator
    {
        $gameAttempts = $this->user->gameAttempts()
            ->with('game:id,difficulty')
            ->when($this->gameFilter === 'winning', fn ($query) => $query->where('is_winner', true))
            ->when($this->gameFilter === 'losing', fn ($query) => $query->where(function ($query): void {
                $query->where('is_winner', false)->orWhereNull('is_winner');
            }))
            ->latest()
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'gamePage');

        $opponents = GameAttempt::query()
            ->whereIn('game_id', $gameAttempts->getCollection()->pluck('game_id')->unique())
            ->where('user_id', '!=', $this->user->id)
            ->with('user:id,name,email')
            ->get()
            ->groupBy('game_id');

        return $gameAttempts->setCollection($gameAttempts->getCollection()->map(function ($gameAttempt) use ($opponents): array {
            $opponent = $opponents->get($gameAttempt->game_id)?->first()?->user;

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
        }));
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
            'attempts' => $this->filteredAttempts(),
            'games' => $this->filteredGames(),
            'accuracy' => $this->answerStats->answered > 0
                ? round(($this->answerStats->correct / $this->answerStats->answered) * 100, 1)
                : 0,
        ]);
    }
}
