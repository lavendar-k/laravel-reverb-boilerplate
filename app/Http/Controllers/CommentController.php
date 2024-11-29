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
