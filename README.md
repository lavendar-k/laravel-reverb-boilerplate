<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Laravel Reverb

### Run this commands
```cmd
php artisan install:broadcasting
```

Above command will update .env file

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=151212

REVERB_APP_KEY=1rcztnsuiavyzgsomsms

REVERB_APP_SECRET=uezrgv668uyajdrlq69x

REVERB_HOST="localhost"

REVERB_PORT=8080

REVERB_SCHEME=http


VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"

VITE_REVERB_HOST="${REVERB_HOST}"

VITE_REVERB_PORT="${REVERB_PORT}"

VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

```cmd
php artisan make:controller CommentController

php artisan make:model Post
php artisan make:model Comment
```

- Run Reverb Server
```cmd
php artisan reverb:start
```

Please refer the Post.php and Comment.php

### CommentController.php
```php
<?php

namespace App\Http\Controllers;

use App\Events\CommentPosted;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:255',
        ]);
        $validated['post_id'] = $post->id;
        $validated['user_id'] = auth()->user()->id;

        $comment = $post->comments()->create($validated);

        broadcast(new CommentPosted($comment))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Comment posted successfully.',
            'comment' => $comment->with("user"),
        ], 200);
    }

    public function index(Post $post): JsonResponse
    {
        $comments = $post->comments()->latest()->with("user")->get();

        return response()->json($comments);
    }
}
```
### app/Events/CommmentPosted.php
```php
<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentPosted implements ShouldBroadcastNow
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
```
### CommentSection.vue
```vue
<template>
    <div>
        <div
            v-for="comment in sortedComments"
            :key="comment.id"
            class="comment space-y-2"
        >
            <div class="mb-2 rounded bg-gray-100">
                <div class="flex items-center justify-between px-3 py-2">
                    <p>
                        {{ comment.user.name }}
                        <span class="text-sm text-gray-500">{{
                            comment.user.email
                        }}</span>
                    </p>
                    <span class="text-sm text-gray-500">{{
                        comment.created_at
                    }}</span>
                </div>
                <p class="border-t px-3 py-2">{{ comment.comment }}</p>
            </div>
        </div>
        <input
            v-model="newComment"
            @keyup.enter="postComment"
            placeholder="Write a comment..."
        />
    </div>
</template>

<script>
import dayjs from 'dayjs/esm/index.js';

export default {
    data() {
        return {
            comments: [],
            newComment: '',
        };
    },
    computed: {
        sortedComments() {
            return this.comments?.map((t) => ({
                ...t,
                created_at: dayjs(t.created_at).format('MMM d YYYY, HH:mm'),
            }));
        },
    },
    mounted() {
        console.log('Component mounted.');

        // Listen for new comments
        window.Echo.private('posts.1').listen('.CommentPosted', (event) => {
            this.comments.push(event);
        });

        // Fetch existing comments
        axios
            .get('/posts/1/comments')
            .then((response) => {
                this.comments = response.data;
            })
            .catch((error) => {
                console.log(error);
            });
    },
    methods: {
        postComment() {
            if (this.newComment.trim()) {
                axios
                    .post('/posts/1/comments', {
                        comment: this.newComment,
                    })
                    .then(() => {
                        this.newComment = ''; // Clear input after posting
                    })
                    .catch((error) => {
                        console.log(error);
                    });
            }
        },
    },
};
</script>
```

### routes/channels.php
```php
Broadcast::channel('posts.{id}', function ($user) {
    return true;
});
```
