<?php

use Livewire\Component;
use App\Models\Game;
use Livewire\Attributes\On;
new class extends Component {
    public Game $game;
    public function mount($game)
    {
        $this->game = $game;
    }

    #[On('echo-private:game.{gameId},.game.started')]
    public function toGame($event)
    {   
        return redirect()->route('friend.game.started', [
            'challenge_token' => $this->game->challenge_token,
        ]);
    }
    //
};
?>

<div>
    {{-- The biggest battle is the war against ignorance. - Mustafa Kemal Atatürk --}}
</div>
