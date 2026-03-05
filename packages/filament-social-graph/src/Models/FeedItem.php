<?php

namespace BeegoodIT\FilamentSocialGraph\Models;

use BeegoodIT\EloquentUserstamps\HasUserStamps;
use BeegoodIT\FilamentSocialGraph\Database\Factories\FeedItemFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FeedItem extends Model
{
    use HasFactory;
    use HasUserStamps;
    use HasUuids;

    /**
     * @var array<int, string>
     */
    public const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public static function isImagePath(string $path): bool
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($ext, self::IMAGE_EXTENSIONS, true);
    }

    protected static function newFactory(): FeedItemFactory
    {
        return FeedItemFactory::new();
    }

    protected $fillable = [
        'actor_type',
        'actor_id',
        'team_id',
        'subject',
        'body',
        'attachments',
        'created_by_id',
        'updated_by_id',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
        ];
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo($this->getTeamModel());
    }

    public static function getStorageDisk(): string
    {
        return config('filesystems.default') === 's3' ? 's3' : 'public';
    }

    /**
     * Storage directory for attachments: feed-item-attachments or feed-item-attachments/{team_id} when tenancy enabled.
     */
    public static function getAttachmentDirectory(?string $teamId = null): string
    {
        $base = 'feed-item-attachments';
        if (! config('filament-social-graph.tenancy.enabled') || $teamId === null) {
            return $base;
        }

        return $base.'/'.$teamId;
    }

    protected function getTeamModel(): string
    {
        return config('filament-social-graph.tenancy.team_model', \App\Models\Team::class);
    }
}
