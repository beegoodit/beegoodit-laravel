<?php

namespace BeegoodIT\FilamentStreaks\Data;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

readonly class StreakEventData
{
    public function __construct(
        public CarbonInterface $happenedAt,
        public string $eventType,
        public string $eventId,
    ) {}

    public static function fromModel(Model $model): self
    {
        $happenedAt = $model->getAttribute('happened_at')
            ?? $model->getAttribute('occurred_at')
            ?? $model->getAttribute('start_at')
            ?? now();

        return new self(
            happenedAt: $happenedAt->copy()->utc(),
            eventType: $model->getMorphClass(),
            eventId: (string) $model->getKey(),
        );
    }
}
