<?php

namespace BeegoodIT\FilamentSocialGraph\Database\Factories;

use BeegoodIT\FilamentSocialGraph\Enums\AttachmentType;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use BeegoodIT\FilamentSocialGraph\Models\FeedItemAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeedItemAttachmentFactory extends Factory
{
    protected $model = FeedItemAttachment::class;

    public function definition(): array
    {
        $filename = $this->faker->word().'.'.$this->faker->fileExtension();

        return [
            'feed_item_id' => FeedItem::factory(),
            'type' => AttachmentType::File,
            'path' => 'attachments/'.$this->faker->uuid().'/'.$filename,
            'filename' => $filename,
            'mime_type' => $this->faker->mimeType(),
            'size' => $this->faker->numberBetween(1024, 1024 * 1024),
            'order' => 0,
        ];
    }

    public function image(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => AttachmentType::Image,
            'mime_type' => $this->faker->randomElement(['image/jpeg', 'image/png', 'image/webp']),
        ]);
    }
}
