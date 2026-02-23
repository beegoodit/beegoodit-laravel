<?php

namespace BeegoodIT\FilamentPartners\Models;

use BeegoodIT\FilamentPartners\Enums\PartnerType;
use BeegoodIT\FilamentPartners\Models\Concerns\HasActivePeriod;
use BeegoodIT\FilamentPartners\Models\Concerns\HasUserStamps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Partner extends Model implements Sortable
{
    use HasActivePeriod;
    use HasUserStamps;
    use HasUuids;
    use HasFactory;
    use SortableTrait;

    public array $sortable = [
        'order_column_name' => 'position',
        'sort_when_creating' => true,
    ];

    protected $fillable = [
        'partnerable_type',
        'partnerable_id',
        'type',
        'name',
        'description',
        'url',
        'logo',
        'position',
        'active_from',
        'active_to',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => PartnerType::class,
            'active_from' => 'datetime',
            'active_to' => 'datetime',
        ];
    }

    public function partnerable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopePlatform(Builder $query): Builder
    {
        return $query->whereNull('partnerable_id');
    }

    /**
     * Scope sort order to the same partnerable (platform or team), so each owner has its own position sequence.
     */
    public function buildSortQuery(): Builder
    {
        return static::query()
            ->where('partnerable_type', $this->partnerable_type)
            ->where('partnerable_id', $this->partnerable_id);
    }

    public function getLogoUrl(): ?string
    {
        if (empty($this->logo)) {
            return null;
        }

        $disk = config('filament-partners.logo_disk') ?? (config('filesystems.default') === 's3' ? 's3' : 'public');

        if ($disk === 's3') {
            return Storage::disk('s3')->temporaryUrl($this->logo, now()->addHour());
        }

        return Storage::disk($disk)->url($this->logo);
    }
}
