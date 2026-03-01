<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Enums\Visibility;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;

class FeedItemViewAttachmentsTest extends TestCase
{
    public function test_is_image_path_identifies_image_extensions(): void
    {
        $this->assertTrue(FeedItem::isImagePath('photo.jpg'));
        $this->assertTrue(FeedItem::isImagePath('photo.jpeg'));
        $this->assertTrue(FeedItem::isImagePath('photo.png'));
        $this->assertTrue(FeedItem::isImagePath('photo.gif'));
        $this->assertTrue(FeedItem::isImagePath('photo.webp'));
        $this->assertTrue(FeedItem::isImagePath('path/to/image.PNG'));
    }

    public function test_is_image_path_returns_false_for_non_images(): void
    {
        $this->assertFalse(FeedItem::isImagePath('document.pdf'));
        $this->assertFalse(FeedItem::isImagePath('file.doc'));
        $this->assertFalse(FeedItem::isImagePath('data.json'));
    }

    public function test_attachments_can_be_split_into_images_and_files(): void
    {
        $user = TestUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $paths = [
            'feed-item-attachments/photo.jpg',
            'feed-item-attachments/document.pdf',
            'feed-item-attachments/chart.png',
        ];

        $feedItem = FeedItem::create([
            'actor_type' => TestUser::class,
            'actor_id' => $user->id,
            'body' => 'With mixed attachments',
            'visibility' => Visibility::Public,
            'attachments' => $paths,
        ]);

        $imagePaths = array_values(array_filter(
            $feedItem->attachments ?? [],
            fn (string $path): bool => FeedItem::isImagePath($path)
        ));
        $filePaths = array_values(array_filter(
            $feedItem->attachments ?? [],
            fn (string $path): bool => ! FeedItem::isImagePath($path)
        ));

        $this->assertCount(2, $imagePaths);
        $this->assertContains('feed-item-attachments/photo.jpg', $imagePaths);
        $this->assertContains('feed-item-attachments/chart.png', $imagePaths);
        $this->assertCount(1, $filePaths);
        $this->assertContains('feed-item-attachments/document.pdf', $filePaths);
    }
}
