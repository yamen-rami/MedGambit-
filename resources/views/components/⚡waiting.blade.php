<?php

use App\Models\Game;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public Game $game;
    public int $gameId;

    public function mount(Game $game): void
    {
        $this->game = $game;
        $this->gameId = $game->id;
    }

    #[On('echo-private:game.{gameId},.game.started')]
    public function toGame(): void
    {
        $this->redirectRoute('gameRedirect', [
            'challenge_token' => $this->game->challenge_token,
        ]);
    }
    //
};
?>

<div>
    {{-- The biggest battle is the war against ignorance. - Mustafa Kemal Atatürk --}}
</div>
