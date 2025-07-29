<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PusherBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $channelName, $scannedBeacon;

    /**
     * Create a new event instance (esta é a info que esta vindo do canal).
     */
    public function __construct($channelName, $scannedBeacon)
    {
        $this->channelName = $channelName;
        $this->scannedBeacon = $scannedBeacon;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('public')];
    }

    // nome do evento que vai ser ouvido pelo cliente
    public function broadcastAs(): string 
    {
        return 'beaconScanning';
    }
}
