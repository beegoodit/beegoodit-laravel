<?php

namespace BeeGoodIT\FilamentOAuth\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TeamAssignmentService
{
    public function __construct(
        protected MicrosoftGraphService $graphService
    ) {}

    /**
     * Assign a user to a team based on their OAuth tenant ID.
     */
    public function assignUserToTeam(Model $user, ?string $provider = null, ?string $tenantId = null, ?string $accessToken = null): void
    {
        if (! $tenantId) {
            Log::info('No tenant ID provided for team assignment', ['user_id' => $user->id]);

            return;
        }

        $teamModel = config('filament-oauth.team_model', \App\Models\Team::class);

        // Try to find existing team with this OAuth tenant
        $team = $teamModel::query()
            ->where('oauth_provider', $provider)
            ->where('oauth_tenant_id', $tenantId)
            ->first();

        // If no team exists, create one
        if (! $team) {
            $team = $teamModel::create([
                'name' => $this->generateTeamName($provider, $tenantId, $accessToken),
                'slug' => Str::slug($this->generateTeamName($provider, $tenantId, $accessToken)),
                'oauth_provider' => $provider,
                'oauth_tenant_id' => $tenantId,
            ]);

            Log::info('Created new team from OAuth tenant', [
                'team_id' => $team->id,
                'provider' => $provider,
                'tenant_id' => $tenantId,
            ]);
        }

        // Attach user to team if not already attached
        if (! $user->teams()->where('team_id', $team->id)->exists()) {
            $user->teams()->attach($team->id);

            Log::info('Assigned user to team', [
                'user_id' => $user->id,
                'team_id' => $team->id,
            ]);
        }
    }

    /**
     * Generate a team name from OAuth tenant information.
     */
    protected function generateTeamName(string $provider, string $tenantId, ?string $accessToken = null): string
    {
        // For Microsoft providers, try to fetch organization name from Graph API
        if ($provider === 'microsoft' && $accessToken) {
            return $this->graphService->getOrganizationName($accessToken, $tenantId);
        }

        // Fallback to generic name generation
        return ucfirst($provider).' Organization '.substr($tenantId, 0, 8);
    }
}
