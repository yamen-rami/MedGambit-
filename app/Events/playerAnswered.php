<?php

namespace App\Events;

use Illuminate\Broadcasting\{Channel, InteractsWithSockets, PresenceChannel, PrivateChannel};
use Illuminate\Contracts\Broadcasting\{ShouldBroadcast, ShouldBroadcastNow};
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use App\Models\Game;

class playerAnswered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $progress = 0 ;
    /**
     * Create a new event instance.
     */
    public function __construct(public int $userId , public  int  $gameId)
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
