<?php

namespace BeegoodIT\FilamentSocialGraph\Models;

use BeegoodIT\EloquentUserstamps\HasUserStamps;
use BeegoodIT\FilamentSocialGraph\Database\Factories\FeedItemFactory;
use BeegoodIT\FilamentSocialGraph\Enums\Visibility;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeedItem extends Model
{
    use HasFactory;
    use HasUserStamps;
    use HasUuids;
    use SoftDeletes;

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
        'visibility',
        'created_by_id',
        'updated_by_id',
    ];

    protected function casts(): array
    {
        return [
            'visibility' => Visibility::class,
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

    public function attachments(): HasMany
    {
        return $this->hasMany(FeedItemAttachment::class)->orderBy('order');
    }

    protected function getTeamModel(): string
    {
        return config('filament-social-graph.tenancy.team_model', \App\Models\Team::class);
    }
}
