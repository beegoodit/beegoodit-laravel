<?php

namespace BeegoodIT\FilamentSocialGraph\Actions;

use BeegoodIT\FilamentSocialGraph\Enums\Visibility;
use BeegoodIT\FilamentSocialGraph\Models\Concerns\HasSocialFeed;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

class CreateFeedItemForEntity
{
    public function __invoke(Model $entity, array $data): FeedItem
    {
        if (! in_array(HasSocialFeed::class, class_uses_recursive($entity), true)) {
            throw new \InvalidArgumentException(
                'Entity must use '.HasSocialFeed::class.' to create feed items.'
            );
        }

        $visibility = $data['visibility'] ?? Visibility::Public;
        if (! $visibility instanceof Visibility) {
            $visibility = Visibility::from($visibility);
        }

        $feedItem = $entity->createFeedItem([
            'subject' => $data['subject'] ?? null,
            'body' => $data['body'] ?? null,
            'visibility' => $visibility,
        ]);

        if (config('filament-social-graph.tenancy.enabled')) {
            $teamId = $this->resolveCurrentTeamId();
            if ($teamId !== null) {
                $feedItem->update(['team_id' => $teamId]);
            }
        }

        return $feedItem;
    }

    protected function resolveCurrentTeamId(): mixed
    {
        $resolver = config('filament-social-graph.tenancy.team_resolver');
        $team = null;
        if ($resolver !== null && is_callable($resolver)) {
            $team = $resolver();
        }
        if ($team === null && class_exists(Filament::class)) {
            $team = Filament::getTenant();
        }

        return $team instanceof Model ? $team->getKey() : (is_scalar($team) ? $team : null);
    }
}
