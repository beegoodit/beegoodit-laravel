<?php

namespace BeegoodIT\FilamentEntryLinks\Models;

use BeegoodIT\FilamentEntryLinks\Database\Factories\EntryLinkFactory;
use BeegoodIT\FilamentEntryLinks\Enums\EntryLinkRedirectCode;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Sentinel window: always-on scheduling without nulls. Resolver treats these as “no restriction”.
 *
 * @property string $id
 * @property string $token
 * @property string|null $slug
 * @property string $target_url
 * @property \BeegoodIT\FilamentEntryLinks\Enums\EntryLinkRedirectCode $redirect_code
 * @property bool $is_enabled
 * @property \Illuminate\Support\Carbon $active_from
 * @property \Illuminate\Support\Carbon $active_to
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class EntryLink extends Model
{
    /**
     * Minimum “active from” = no future start restriction (Unix epoch, UTC).
     */
    public const ACTIVE_FROM_OPEN = '1970-01-01 00:00:00';

    /**
     * Maximum “active until” = no end restriction.
     */
    public const ACTIVE_TO_OPEN = '9999-12-31 23:59:59';

    /** @use HasFactory<EntryLinkFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    protected $table = 'entry_links';

    protected $fillable = [
        'token',
        'slug',
        'target_url',
        'redirect_code',
        'is_enabled',
        'active_from',
        'active_to',
        'notes',
    ];

    protected static function newFactory(): EntryLinkFactory
    {
        return EntryLinkFactory::new();
    }

    public static function defaultOpenActiveFrom(): Carbon
    {
        return Carbon::parse(self::ACTIVE_FROM_OPEN, 'UTC');
    }

    public static function defaultOpenActiveTo(): Carbon
    {
        return Carbon::parse(self::ACTIVE_TO_OPEN, 'UTC');
    }

    public function isOpenEndedActiveFrom(): bool
    {
        return $this->active_from->year === 1970
            && $this->active_from->month === 1
            && $this->active_from->day === 1
            && $this->active_from->hour === 0
            && $this->active_from->minute === 0
            && (int) $this->active_from->second === 0;
    }

    public function isOpenEndedActiveTo(): bool
    {
        return $this->active_to->year >= 9999;
    }

    /**
     * Absolute URL for the public redirect route ({prefix}/{token} or {prefix}/{token}-{slug}).
     */
    public function publicUrl(): string
    {
        $prefix = trim((string) config('filament-entry-links.route_prefix', 'link'), '/');

        if ($prefix === '') {
            $prefix = 'link';
        }

        $segment = filled($this->slug)
            ? $this->token.'-'.$this->slug
            : $this->token;

        return url($prefix.'/'.$segment);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'redirect_code' => EntryLinkRedirectCode::class,
            'is_enabled' => 'boolean',
            'active_from' => 'datetime',
            'active_to' => 'datetime',
        ];
    }
}
