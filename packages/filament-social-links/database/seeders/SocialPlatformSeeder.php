<?php

namespace BeegoodIT\FilamentSocialLinks\Database\Seeders;

use BeegoodIT\FilamentSocialLinks\Models\SocialPlatform;
use Illuminate\Database\Seeder;

class SocialPlatformSeeder extends Seeder
{
    public function run(): void
    {
        $platforms = [
            [
                'name' => 'Facebook',
                'slug' => 'facebook',
                'base_url' => 'https://facebook.com/',
                'icon' => 'fab-facebook',
                'sort_order' => 10,
            ],
            [
                'name' => 'Instagram',
                'slug' => 'instagram',
                'base_url' => 'https://instagram.com/',
                'icon' => 'fab-instagram',
                'sort_order' => 20,
            ],
            [
                'name' => 'TikTok',
                'slug' => 'tiktok',
                'base_url' => 'https://tiktok.com/@',
                'icon' => 'fab-tiktok',
                'sort_order' => 30,
            ],
            [
                'name' => 'YouTube',
                'slug' => 'youtube',
                'base_url' => 'https://youtube.com/@',
                'icon' => 'fab-youtube',
                'sort_order' => 40,
            ],
            [
                'name' => 'Twitch',
                'slug' => 'twitch',
                'base_url' => 'https://twitch.tv/',
                'icon' => 'fab-twitch',
                'sort_order' => 50,
            ],
            [
                'name' => 'Telegram',
                'slug' => 'telegram',
                'base_url' => 'https://t.me/',
                'icon' => 'fab-telegram',
                'sort_order' => 60,
            ],
            [
                'name' => 'X (Twitter)',
                'slug' => 'x-twitter',
                'base_url' => 'https://x.com/',
                'icon' => 'fab-x-twitter',
                'sort_order' => 70,
            ],
            [
                'name' => 'Discord',
                'slug' => 'discord',
                'base_url' => 'https://discord.gg/',
                'icon' => 'fab-discord',
                'sort_order' => 80,
            ],
            [
                'name' => 'LinkedIn',
                'slug' => 'linkedin',
                'base_url' => 'https://linkedin.com/in/',
                'icon' => 'fab-linkedin',
                'sort_order' => 90,
            ],
        ];

        foreach ($platforms as $platform) {
            SocialPlatform::updateOrCreate(
                ['slug' => $platform['slug']],
                $platform
            );
        }
    }
}
