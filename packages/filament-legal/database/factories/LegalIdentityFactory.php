<?php

namespace BeegoodIT\FilamentLegal\Database\Factories;

use BeegoodIT\FilamentLegal\Models\LegalIdentity;
use Illuminate\Database\Eloquent\Factories\Factory;

class LegalIdentityFactory extends Factory
{
    protected $model = LegalIdentity::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'form' => 'GmbH',
            'representative' => $this->faker->name(),
            'email' => $this->faker->companyEmail(),
            'founded_at' => $this->faker->date(),
        ];
    }
}
