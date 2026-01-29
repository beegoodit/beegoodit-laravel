<?php

namespace BeegoodIT\FilamentSocialLinks\Models\Concerns;

use BeegoodIT\FilamentSocialLinks\Models\SocialLink;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasSocialLinks
{
    /**
     * Get the social links for the model.
     */
    public function socialLinks(): MorphMany
    {
        return $this->morphMany(SocialLink::class, 'linkable')
            ->orderBy('sort_order');
    }
}
