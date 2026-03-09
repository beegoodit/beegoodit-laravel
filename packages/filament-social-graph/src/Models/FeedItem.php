<?php

namespace BeegoodIT\FilamentSocialGraph\Models;

use BeegoodIT\EloquentUserstamps\HasUserStamps;
use BeegoodIT\FilamentSocialGraph\Database\Factories\FeedItemFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Conventional thumbnail path for an attachment path (dir/filename.ext -> dir/thumbs/filename.ext).
     */
    public static function getThumbnailPath(string $path): string
    {
        $dir = pathinfo($path, PATHINFO_DIRNAME);
        $basename = pathinfo($path, PATHINFO_BASENAME);
        if ($dir === '.') {
            return 'thumbs/'.$basename;
        }

        return rtrim($dir, '/').'/thumbs/'.$basename;
    }

    /**
     * URL for the thumbnail path. No fallback to full URL; use regenerate command if thumbnail file is missing.
     */
    public static function getThumbnailUrl(string $path, ?\DateTimeInterface $expiresAt = null): string
    {
        return self::getAttachmentUrl(self::getThumbnailPath($path), $expiresAt);
    }

    /**
     * First image attachment's thumbnail URL for use as feed item preview, or null if none.
     */
    public function getFirstImageThumbnailUrl(): ?string
    {
        $attachments = $this->attachments ?? [];
        foreach ($attachments as $path) {
            if (self::isImagePath($path)) {
                return self::getThumbnailUrl($path);
            }
        }

        return null;
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
     * URL for an attachment path. Uses temporary (signed) URLs for S3 so private buckets work without public read.
     * Delegates to FileStorageService when laravel-file-storage is present (same path as tenancy/avatar).
     */
    public static function getAttachmentUrl(string $path, ?\DateTimeInterface $expiresAt = null): string
    {
        $fileStorageServiceClass = 'BeegoodIT\LaravelFileStorage\Services\FileStorageService';
        if (class_exists($fileStorageServiceClass) && app()->bound($fileStorageServiceClass)) {
            $ttlMinutes = (int) config('filament-social-graph.attachments.signed_url_ttl_minutes', 60);
            $url = app($fileStorageServiceClass)->url($path, $ttlMinutes, self::getStorageDisk());
            if ($url !== null) {
                return $url;
            }
        }

        $disk = self::getStorageDisk();
        if ($disk === 's3') {
            $expiresAt ??= now()->addMinutes(
                (int) config('filament-social-graph.attachments.signed_url_ttl_minutes', 60)
            );

            return Storage::disk($disk)->temporaryUrl($path, $expiresAt);
        }

        return Storage::disk($disk)->url($path);
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
