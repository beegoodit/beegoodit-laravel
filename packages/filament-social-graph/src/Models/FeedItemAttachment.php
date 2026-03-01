<?php

namespace BeegoodIT\FilamentSocialGraph\Models;

use BeegoodIT\FilamentSocialGraph\Enums\AttachmentType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class FeedItemAttachment extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'feed_item_id',
        'type',
        'path',
        'filename',
        'mime_type',
        'size',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'type' => AttachmentType::class,
            'size' => 'integer',
            'order' => 'integer',
        ];
    }

    public function feedItem(): BelongsTo
    {
        return $this->belongsTo(FeedItem::class);
    }

    public function getUrlAttribute(): ?string
    {
        if (empty($this->path)) {
            return null;
        }

        return Storage::url($this->path);
    }

    public function isImage(): bool
    {
        return $this->type === AttachmentType::Image
            || str_starts_with($this->mime_type ?? '', 'image/');
    }
}
