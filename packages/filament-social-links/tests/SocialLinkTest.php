<?php

namespace BeegoodIT\FilamentSocialLinks\Tests;

use BeegoodIT\FilamentSocialLinks\Models\SocialLink;
use BeegoodIT\FilamentSocialLinks\Models\SocialPlatform;
use Illuminate\Support\Facades\Auth;

class SocialLinkTest extends TestCase
{
    public function test_it_can_create_a_social_link()
    {
        $platform = SocialPlatform::create([
            'name' => 'Instagram',
            'slug' => 'instagram',
            'base_url' => 'https://instagram.com/',
            'icon' => 'fab-instagram',
        ]);

        $model = TestModel::create(['name' => 'Test Team']);

        $link = SocialLink::create([
            'linkable_id' => $model->id,
            'linkable_type' => TestModel::class,
            'social_platform_id' => $platform->id,
            'handle' => 'foosbeaver',
        ]);

        $this->assertDatabaseHas('social_links', [
            'id' => $link->id,
            'handle' => 'foosbeaver',
        ]);
    }

    public function test_it_generates_correct_url_from_handle()
    {
        $platform = SocialPlatform::create([
            'name' => 'Instagram',
            'slug' => 'instagram',
            'base_url' => 'https://instagram.com/',
            'icon' => 'fab-instagram',
        ]);

        $link = new SocialLink([
            'social_platform_id' => $platform->id,
            'handle' => 'foosbeaver',
        ]);
        $link->setRelation('platform', $platform);

        $this->assertEquals('https://instagram.com/foosbeaver', $link->url);
    }

    public function test_it_handles_handles_starting_with_at_symbol()
    {
        $platform = SocialPlatform::create([
            'name' => 'TikTok',
            'slug' => 'tiktok',
            'base_url' => 'https://tiktok.com/@',
            'icon' => 'fab-tiktok',
        ]);

        $link = new SocialLink([
            'social_platform_id' => $platform->id,
            'handle' => '@foosbeaver',
        ]);
        $link->setRelation('platform', $platform);

        // Should NOT result in @@foosbeaver
        $this->assertEquals('https://tiktok.com/@foosbeaver', $link->url);
    }

    public function test_it_tracks_userstamps()
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => 'secret',
        ]);

        Auth::login($user);

        $platform = SocialPlatform::create([
            'name' => 'Facebook',
            'slug' => 'facebook',
            'base_url' => 'https://facebook.com/',
        ]);

        $this->assertEquals($user->id, $platform->created_by_id);
    }
}
