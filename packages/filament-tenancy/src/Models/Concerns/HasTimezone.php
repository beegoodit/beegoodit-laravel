<?php

namespace BeegoodIT\FilamentTenancy\Models\Concerns;

trait HasTimezone
{
    /**
     * Get the team's preferred timezone for unattended / tenant-ops clocks.
     */
    public function getTimezone(): string
    {
        return $this->timezone ?? config('app.timezone', 'UTC');
    }
}
