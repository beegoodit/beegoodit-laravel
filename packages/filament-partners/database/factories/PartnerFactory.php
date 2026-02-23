<?php

namespace BeegoodIT\FilamentPartners\Database\Factories;

use BeegoodIT\FilamentPartners\Enums\PartnerType;
use BeegoodIT\FilamentPartners\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerFactory extends Factory
{
    protected $model = Partner::class;

    public function definition(): array
    {
        $activeFrom = $this->faker->dateTimeBetween('-1 year', 'now');
        $activeTo = $this->faker->dateTimeBetween($activeFrom, '+2 years');

        return [
            'partnerable_type' => null,
            'partnerable_id' => null,
            'type' => PartnerType::Partner,
            'name' => $this->faker->company(),
            'description' => $this->faker->optional(0.3)->paragraph(),
            'url' => $this->faker->optional(0.5)->url(),
            'logo' => null,
            'position' => 0,
            'active_from' => $activeFrom,
            'active_to' => $activeTo,
        ];
    }

    public function platform(): static
    {
        return $this->state(fn (array $attributes): array => [
            'partnerable_type' => null,
            'partnerable_id' => null,
        ]);
    }

    public function sponsor(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => PartnerType::Sponsor,
        ]);
    }
}
