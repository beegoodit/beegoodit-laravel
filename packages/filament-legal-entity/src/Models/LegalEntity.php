<?php

namespace BeegoodIT\FilamentLegalEntity\Models;

use BeegoodIT\FilamentLegalEntity\Database\Factories\LegalEntityFactory;
use BeegoodIT\FilamentLegalEntity\Profiles\CountryProfileRegistry;
use BeegoodIT\FilamentLegalEntity\Support\Iso3166Countries;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LegalEntity extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'legal_entities';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'owner_type',
        'owner_id',
        'name',
        'address_line',
        'address_line_2',
        'postal_code',
        'city',
        'region',
        'email',
        'phone',
        'country',
        'profile',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'profile' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (LegalEntity $entity): void {
            $entity->normalizeOwner();
            $entity->assertCoreAttributes();
            $entity->assertUniqueOwnership();
            $entity->profile = CountryProfileRegistry::for($entity->country)->normalize($entity->profile ?? []);

            if (CountryProfileRegistry::isDe($entity->country)) {
                $entity->region = null;
            }
        });
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return list<string>
     */
    public function addressLines(): array
    {
        $locality = trim(implode(' ', array_filter([
            $this->postal_code,
            $this->city,
        ], fn (?string $part): bool => filled($part))));

        $lines = [
            $this->address_line,
            $this->address_line_2,
            $locality !== '' ? $locality : null,
        ];

        if (! CountryProfileRegistry::isDe($this->country) && filled($this->region)) {
            $lines[] = $this->region;
        }

        return array_values(array_filter($lines, fn (?string $line): bool => filled($line)));
    }

    public function profileValue(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->profile ?? [], $key, $default);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    protected function scopePlatform(Builder $query): Builder
    {
        return $query->whereNull('owner_type')->whereNull('owner_id');
    }

    protected static function newFactory(): LegalEntityFactory
    {
        return LegalEntityFactory::new();
    }

    private function normalizeOwner(): void
    {
        if ($this->owner_id === null || $this->owner_id === '') {
            $this->owner_id = null;
            $this->owner_type = $this->owner_type !== null && $this->owner_type !== '' ? $this->owner_type : null;

            return;
        }

        $this->owner_id = (string) $this->owner_id;
    }

    private function assertCoreAttributes(): void
    {
        Validator::make(
            [
                'name' => $this->name,
                'address_line' => $this->address_line,
                'address_line_2' => $this->address_line_2,
                'postal_code' => $this->postal_code,
                'city' => $this->city,
                'region' => $this->region,
                'email' => $this->email,
                'phone' => $this->phone,
                'country' => $this->country,
                'owner_type' => $this->owner_type,
                'owner_id' => $this->owner_id,
            ],
            [
                'name' => ['required', 'string', 'max:255'],
                'address_line' => ['required', 'string', 'max:255'],
                'address_line_2' => ['nullable', 'string', 'max:255'],
                'postal_code' => ['required', 'string', 'max:32'],
                'city' => ['required', 'string', 'max:255'],
                'region' => ['nullable', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:64'],
                'country' => ['required', 'string', 'size:2', function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! Iso3166Countries::isValid((string) $value)) {
                        $fail(__('filament-legal-entity::entity.invalid_country'));
                    }
                }],
                'owner_type' => ['nullable', 'string', 'max:255', 'required_with:owner_id'],
                'owner_id' => ['nullable', 'string', 'max:64', 'required_with:owner_type'],
            ]
        )->validate();
    }

    private function assertUniqueOwnership(): void
    {
        $query = static::query();

        if ($this->exists) {
            $query->whereKeyNot($this->getKey());
        }

        if ($this->owner_type === null && $this->owner_id === null) {
            if ($query->clone()->platform()->exists()) {
                throw ValidationException::withMessages([
                    'owner' => __('filament-legal-entity::entity.unique_platform'),
                ]);
            }

            return;
        }

        if ($query->where('owner_type', $this->owner_type)->where('owner_id', $this->owner_id)->exists()) {
            throw ValidationException::withMessages([
                'owner' => __('filament-legal-entity::entity.unique_owner'),
            ]);
        }
    }
}
