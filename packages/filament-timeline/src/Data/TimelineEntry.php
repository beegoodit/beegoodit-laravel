<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTimeline\Data;

use Illuminate\Support\Carbon;

final class TimelineEntry
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $title,
        public ?string $description = null,
        public ?string $icon = null,
        public ?string $color = null,
        public ?Carbon $occurredAt = null,
        public ?string $url = null,
        public array $metadata = [],
    ) {
        // We allow null occurredAt for milestones without a specific date
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public static function make(
        string $title,
        ?string $description = null,
        ?string $icon = null,
        ?string $color = null,
        ?Carbon $occurredAt = null,
        ?string $url = null,
        array $metadata = [],
    ): self {
        return new self($title, $description, $icon, $color, $occurredAt, $url, $metadata);
    }
}
