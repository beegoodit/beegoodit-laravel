<?php

namespace BeegoodIT\FilamentLegalEntity\Database\Factories;

use BeegoodIT\FilamentLegalEntity\Enums\LegalForm;
use BeegoodIT\FilamentLegalEntity\Models\LegalEntity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LegalEntity>
 */
class LegalEntityFactory extends Factory
{
    protected $model = LegalEntity::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'address_line' => $this->faker->streetAddress(),
            'postal_code' => $this->faker->postcode(),
            'city' => $this->faker->city(),
            'email' => $this->faker->companyEmail(),
            'country' => 'DE',
            'profile' => [
                'legal_form' => LegalForm::Gmbh->value,
                'representative' => $this->faker->name(),
                'register_court' => 'Amtsgericht Berlin',
                'register_number' => 'HRB 123456',
            ],
        ];
    }

    public function platform(): static
    {
        return $this->state(fn (): array => [
            'owner_type' => null,
            'owner_id' => null,
        ]);
    }

    public function generic(?string $country = 'US'): static
    {
        return $this->state(fn (): array => [
            'country' => $country,
            'profile' => [],
            'region' => 'California',
        ]);
    }

    public function forOwner(object $owner): static
    {
        return $this->state(fn (): array => [
            'owner_type' => $owner->getMorphClass(),
            'owner_id' => (string) $owner->getKey(),
        ]);
    }
}
