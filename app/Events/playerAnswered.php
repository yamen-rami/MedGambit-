<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class playerAnswered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $progress = 0;

    /**
     * Create a new event instance.
     */
    public function __construct(public int $userId, public int $gameId)
    {
        // Getting Game adn getting the user count
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("playerAnswerd.{$this->gameId}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
        ];
    }

    public function broadcastAs(): string
    {
        return 'game.progress';
    }
}
