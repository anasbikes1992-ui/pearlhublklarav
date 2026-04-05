<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SocialComment;
use App\Models\SocialFollow;
use App\Models\SocialLike;
use App\Models\SocialPost;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialController extends BaseApiController
{
    /** GET /api/v1/social/feed — public chronological feed, filterable by vertical_tag */
    public function feed(Request $request): JsonResponse
    {
        $query = SocialPost::query()
            ->with(['author:id,full_name,profile_photo'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at');

        if ($request->filled('vertical')) {
            $query->where('vertical_tag', $request->string('vertical'));
        }

        $posts = $query->paginate(20)->through(fn (SocialPost $p) => [
            'id'              => $p->id,
            'author'          => $p->author ? [
                'id'    => $p->author->id,
                'name'  => $p->author->full_name,
                'photo' => $p->author->profile_photo,
            ] : null,
            'content'         => $p->content,
            'media_urls'      => $p->media_urls ?? [],
            'vertical_tag'    => $p->vertical_tag,
            'listing_id'      => $p->listing_id,
            'likes_count'     => $p->likes_count,
            'comments_count'  => $p->comments_count,
            'is_pinned'       => $p->is_pinned,
            'created_at'      => $p->created_at,
        ]);

        return $this->success($posts);
    }

    /** POST /api/v1/social/posts */
    public function createPost(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content'      => ['required', 'string', 'max:2000'],
            'media_urls'   => ['nullable', 'array', 'max:6'],
            'media_urls.*' => ['url'],
            'vertical_tag' => ['nullable', 'string', 'in:property,stays,vehicles,events,sme,taxi,experience,social'],
            'listing_id'   => ['nullable', 'uuid'],
        ]);

        $post = SocialPost::create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return $this->success($post, 'Post created', 201);
    }

    /** POST /api/v1/social/posts/{post}/like — toggle */
    public function toggleLike(Request $request, string $postId): JsonResponse
    {
        $post = SocialPost::findOrFail($postId);
        $userId = $request->user()->id;

        $existing = SocialLike::where(['user_id' => $userId, 'post_id' => $postId])->first();

        if ($existing) {
            $existing->delete();
            $post->decrement('likes_count');
            $liked = false;
        } else {
            SocialLike::create(['user_id' => $userId, 'post_id' => $postId]);
            $post->increment('likes_count');
            $liked = true;
        }

        return $this->success(['liked' => $liked, 'likes_count' => $post->fresh()->likes_count]);
    }

    /** GET /api/v1/social/posts/{post}/comments */
    public function comments(string $postId): JsonResponse
    {
        $comments = SocialComment::query()
            ->where('post_id', $postId)
            ->whereNull('parent_id')
            ->with(['author:id,full_name,profile_photo'])
            ->orderBy('created_at')
            ->paginate(30)
            ->through(fn (SocialComment $c) => [
                'id'         => $c->id,
                'body'       => $c->body,
                'parent_id'  => $c->parent_id,
                'author'     => $c->author ? [
                    'id'    => $c->author->id,
                    'name'  => $c->author->full_name,
                    'photo' => $c->author->profile_photo,
                ] : null,
                'created_at' => $c->created_at,
            ]);

        return $this->success($comments);
    }

    /** POST /api/v1/social/posts/{post}/comments */
    public function addComment(Request $request, string $postId): JsonResponse
    {
        SocialPost::findOrFail($postId);

        $validated = $request->validate([
            'body'      => ['required', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'uuid', 'exists:social_comments,id'],
        ]);

        $comment = SocialComment::create([
            ...$validated,
            'post_id' => $postId,
            'user_id' => $request->user()->id,
        ]);

        SocialPost::where('id', $postId)->increment('comments_count');

        return $this->success($comment, 'Comment added', 201);
    }

    /** POST /api/v1/social/users/{user}/follow */
    public function follow(Request $request, string $userId): JsonResponse
    {
        $target = User::findOrFail($userId);

        if ((string) $request->user()->id === (string) $target->id) {
            return $this->error('Cannot follow yourself', [], 422);
        }

        SocialFollow::firstOrCreate([
            'follower_id'  => $request->user()->id,
            'following_id' => $target->id,
        ]);

        return $this->success(null, 'Followed');
    }

    /** DELETE /api/v1/social/users/{user}/follow */
    public function unfollow(Request $request, string $userId): JsonResponse
    {
        SocialFollow::where([
            'follower_id'  => $request->user()->id,
            'following_id' => $userId,
        ])->delete();

        return $this->success(null, 'Unfollowed');
    }

    /** GET /api/v1/social/users/{user}/profile */
    public function profile(string $userId): JsonResponse
    {
        $user = User::findOrFail($userId);

        $posts = SocialPost::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate(12)
            ->through(fn (SocialPost $p) => [
                'id'             => $p->id,
                'content'        => $p->content,
                'media_urls'     => $p->media_urls ?? [],
                'vertical_tag'   => $p->vertical_tag,
                'likes_count'    => $p->likes_count,
                'comments_count' => $p->comments_count,
                'created_at'     => $p->created_at,
            ]);

        return $this->success([
            'id'              => $user->id,
            'name'            => $user->full_name,
            'photo'           => $user->profile_photo ?? null,
            'followers_count' => SocialFollow::where('following_id', $userId)->count(),
            'following_count' => SocialFollow::where('follower_id', $userId)->count(),
            'posts_count'     => SocialPost::where('user_id', $userId)->count(),
            'posts'           => $posts,
        ]);
    }
}
