<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\News;
use App\Models\User;
use App\Models\UserTwoFactorAuthentication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsImagePickerTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_cover_picker_shows_recent_public_uploads_and_can_serve_their_thumbnails(): void
    {
        Storage::fake('public');
        $user = User::factory()->active('admin')->create();
        UserTwoFactorAuthentication::factory()->create([
            'user_id' => $user->id, 'method' => 'email', 'confirmed_at' => now(),
        ]);
        $this->actingAs($user)->withSession(['auth.two_factor' => [
            'user_id' => $user->id, 'auth_version' => $user->auth_version, 'verified_at' => time(),
        ]]);

        Media::factory()->create(['original_name' => 'old.jpg', 'created_at' => now()->subDay()]);
        $new = Media::factory()->create([
            'original_name' => 'sidang-hery-susanto-.jpg',
            'path' => 'media/sidang-hery-susanto.jpg',
            'created_at' => now(),
        ]);
        Media::factory()->create(['original_name' => 'internal.jpg', 'visibility' => 'private']);
        Storage::disk('public')->put($new->path, 'image bytes');
        $news = News::create(['title' => 'Test news', 'slug' => 'test-news', 'status' => 'draft']);

        $this->get(route('admin.news.edit', $news))->assertOk()
            ->assertSee('Gambar sampul berita')
            ->assertSee('data-image-picker-search', false)
            ->assertSeeInOrder([
                'data-image-name="sidang-hery-susanto-.jpg"',
                'data-image-name="old.jpg"',
            ], false)
            ->assertSee('value="'.$new->id.'"', false)
            ->assertDontSee('internal.jpg');

        $this->get(route('admin.media.file', $new))->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg');
    }
}
