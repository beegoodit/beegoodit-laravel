<?php

namespace BeegoodIT\FilamentSocialLinks\Tests;

use BeegoodIT\FilamentSocialLinks\Database\Seeders\SocialPlatformSeeder;
use BeegoodIT\FilamentSocialLinks\Models\SocialPlatform;

class SocialPlatformTest extends TestCase
{
    public function test_it_can_create_a_social_platform()
    {
        $platform = SocialPlatform::create([
            'name' => 'Custom Platform',
            'slug' => 'custom-platform',
            'base_url' => 'https://custom.com/',
        ]);

        $this->assertDatabaseHas('social_platforms', [
            'slug' => 'custom-platform',
        ]);
    }

    public function test_seeder_populates_default_platforms()
    {
        $seeder = new SocialPlatformSeeder;
        $seeder->run();

        $this->assertDatabaseHas('social_platforms', ['slug' => 'facebook']);
        $this->assertDatabaseHas('social_platforms', ['slug' => 'instagram']);
        $this->assertDatabaseHas('social_platforms', ['slug' => 'tiktok']);
        $this->assertDatabaseHas('social_platforms', ['slug' => 'telegram']);
    }

    public function test_slug_is_unique()
    {
        SocialPlatform::create([
            'name' => 'Platform 1',
            'slug' => 'unique-slug',
            'base_url' => 'https://p1.com/',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        SocialPlatform::create([
            'name' => 'Platform 2',
            'slug' => 'unique-slug',
            'base_url' => 'https://p2.com/',
        ]);
    }
}
