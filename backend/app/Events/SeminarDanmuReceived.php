<?php

namespace App\Events;

use App\Models\SeminarMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SeminarDanmuReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly SeminarMessage $message) {}

    /**
     * Channel name: seminar.{id}.danmu — public channel, no auth required.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('seminar.' . $this->message->seminar_id . '.danmu'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'danmu.received';
    }

    public function broadcastWith(): array
    {
        return [
            'id'        => $this->message->id,
            'user_name' => $this->message->user_name,
            'content'   => $this->message->content,
            'color'     => $this->message->color,
            'position'  => $this->message->position,
            'send_at'   => $this->message->send_at?->toISOString(),
        ];
    }
}
