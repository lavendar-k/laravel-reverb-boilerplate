<?php

namespace App\Events;

use App\Models\Comment;

use Illuminate\Broadcasting\InteractsWithSockets;

use Illuminate\Broadcasting\PrivateChannel;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

use Illuminate\Foundation\Events\Dispatchable;

use Illuminate\Queue\SerializesModels;

class CommentPosted  implements ShouldBroadcastNow

{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comment;

    public function __construct(Comment $comment)

    {

        $this->comment = $comment;
    }

    public function broadcastOn(): array

    {

        return [

            new PrivateChannel("posts.{$this->comment->post_id}"),

        ];
    }

    public function broadcastAs()

    {

        return 'CommentPosted';
    }

    /**

     * The data to broadcast with the event.

     *

     * @return array

     */

    public function broadcastWith()

    {

        return [

            'id' => $this->comment->id,

            'user_id' => $this->comment->user_id,

            'post_id' => $this->comment->post_id,

            'comment' => $this->comment->comment,

            'created_at' => $this->comment->created_at,

            'user' => $this->comment->user

        ];
    }
}
