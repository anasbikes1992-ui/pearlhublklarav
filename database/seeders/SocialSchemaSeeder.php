<?php

namespace Database\Seeders;

use App\Models\AiConciergeLog;
use App\Models\AuditLog;
use App\Models\Listing;
use App\Models\SocialComment;
use App\Models\SocialFollow;
use App\Models\SocialLike;
use App\Models\SocialPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SocialSchemaSeeder extends Seeder
{
    public function run(): void
    {
        if ($this->hasUserKeyTypeMismatch()) {
            $this->command?->warn('Skipped social seeders because social_* user_id uses numeric foreign keys while users.id is UUID.');
        } else {
            $this->seedSocialGraph();
        }

        $users = User::query()->get();
        foreach ($users->take(12) as $user) {
            AiConciergeLog::query()->create([
                'user_id' => $user->id,
                'query' => fake()->sentence(10),
                'response' => fake()->paragraph(4),
                'model_used' => fake()->randomElement(['gpt-4.1', 'gpt-4o-mini', 'assistant-v2']),
            ]);
        }

        foreach ($users->take(12) as $user) {
            AuditLog::query()->create([
                'actor_id' => fake()->boolean(85) ? $user->id : null,
                'action' => fake()->randomElement(['listing.updated', 'booking.created', 'wallet.credited', 'login.success']),
                'entity_type' => fake()->randomElement(['listing', 'booking', 'wallet', 'auth']),
                'entity_id' => (string) Str::uuid(),
                'meta' => ['seed' => true, 'source' => 'social-schema-seeder'],
            ]);
        }
    }

    private function seedSocialGraph(): void
    {
        $users = User::query()->inRandomOrder()->take(20)->get();
        $listings = Listing::query()->inRandomOrder()->take(20)->get();
        $posts = collect();

        foreach ($users as $user) {
            $postCount = fake()->numberBetween(1, 2);
            foreach (range(1, $postCount) as $_) {
                $posts->push(SocialPost::query()->create([
                    'user_id' => $user->id,
                    'content' => fake()->paragraph(),
                    'media_urls' => fake()->boolean(40) ? ['/media/seed/' . Str::lower(Str::random(10)) . '.jpg'] : null,
                    'vertical_tag' => fake()->randomElement(['property', 'stays', 'vehicles', 'events', 'sme', 'taxi', 'experience', 'social']),
                    'listing_id' => fake()->boolean(40) && $listings->isNotEmpty() ? $listings->random()->id : null,
                    'likes_count' => 0,
                    'comments_count' => 0,
                    'is_pinned' => false,
                    'is_flagged' => false,
                ]));
            }
        }

        foreach ($posts as $post) {
            $commenters = $users->shuffle()->take(fake()->numberBetween(1, 4));
            $parent = null;

            foreach ($commenters as $commenter) {
                $parent = SocialComment::query()->create([
                    'post_id' => $post->id,
                    'user_id' => $commenter->id,
                    'body' => fake()->sentence(12),
                    'parent_id' => fake()->boolean(35) ? $parent?->id : null,
                    'is_flagged' => false,
                ]);
            }

            $likers = $users->shuffle()->take(fake()->numberBetween(2, 8));
            foreach ($likers as $liker) {
                SocialLike::query()->firstOrCreate([
                    'user_id' => $liker->id,
                    'post_id' => $post->id,
                ]);
            }

            $post->forceFill([
                'likes_count' => SocialLike::query()->where('post_id', $post->id)->count(),
                'comments_count' => SocialComment::query()->where('post_id', $post->id)->count(),
            ])->save();
        }

        foreach ($users as $follower) {
            $following = $users->where('id', '!=', $follower->id)->shuffle()->take(fake()->numberBetween(1, 4));
            foreach ($following as $followedUser) {
                SocialFollow::query()->firstOrCreate([
                    'follower_id' => $follower->id,
                    'following_id' => $followedUser->id,
                ]);
            }
        }
    }

    private function hasUserKeyTypeMismatch(): bool
    {
        if (! Schema::hasTable('social_posts')) {
            return true;
        }

        $socialUserType = strtolower((string) Schema::getColumnType('social_posts', 'user_id'));
        $userId = (string) User::query()->value('id');

        return in_array($socialUserType, ['integer', 'bigint', 'int'], true)
            && Str::isUuid($userId);
    }
}
