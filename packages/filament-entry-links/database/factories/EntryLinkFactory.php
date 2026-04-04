<?php

namespace BeegoodIT\FilamentEntryLinks\Database\Factories;

use BeegoodIT\FilamentEntryLinks\Enums\EntryLinkRedirectCode;
use BeegoodIT\FilamentEntryLinks\Models\EntryLink;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends Factory<EntryLink>
 */
class EntryLinkFactory extends Factory
{
    protected $model = EntryLink::class;

    public function definition(): array
    {
        return [
            'token' => Str::lower(Str::random(8)),
            'slug' => fake()->optional()->slug(2),
            'target_url' => 'https://example.com/target',
            'redirect_code' => EntryLinkRedirectCode::Temporary,
            'is_enabled' => true,
            'active_from' => EntryLink::ACTIVE_FROM_OPEN,
            'active_to' => EntryLink::ACTIVE_TO_OPEN,
            'notes' => null,
        ];
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_enabled' => false,
        ]);
    }

    public function scheduled(?Carbon $from = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'active_from' => $from ?? now()->addDay(),
            'active_to' => EntryLink::ACTIVE_TO_OPEN,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'active_from' => now()->subMonths(2),
            'active_to' => now()->subDay(),
        ]);
    }
}
