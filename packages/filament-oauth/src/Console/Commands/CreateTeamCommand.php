<?php

namespace BeeGoodIT\FilamentOAuth\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateTeamCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'team:create
                            {--name= : The name of the team}
                            {--slug= : The slug for the team (auto-generated if not provided)}
                            {--user-email= : Email of user to attach (creates new user if not exists)}
                            {--user-name= : Name of user (only used when creating new user)}
                            {--user-password= : Password for new user (auto-generated if not provided)}
                            {--oauth-provider= : OAuth provider (e.g., microsoft)}
                            {--oauth-tenant-id= : OAuth tenant ID}
                            {--primary-color= : Primary branding color (hex)}
                            {--secondary-color= : Secondary branding color (hex)}
                            {--no-user : Create team without attaching a user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new team and optionally attach a user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $teamModel = $this->getTeamModel();
        $userModel = $this->getUserModel();

        // Get or generate team name
        $teamName = $this->option('name');
        
        if (! $teamName && ! $this->option('no-interaction')) {
            $teamName = $this->ask('Team name (leave empty to generate a fantasy name)');
        }
        
        // Generate fantasy name if still no name provided
        if (! $teamName) {
            $teamName = $this->generateFantasyTeamName();
            $this->info("Generated team name: {$teamName}");
        }

        // Generate slug from team name (no random suffix)
        $slug = $this->option('slug') ?: Str::slug($teamName);
        
        // Ensure slug uniqueness
        $slug = $this->ensureUniqueSlug($teamModel, $slug);

        // Prepare team data
        $teamData = [
            'name' => $teamName,
            'slug' => $slug,
        ];

        // Add OAuth configuration if provided
        if ($this->option('oauth-provider')) {
            $teamData['oauth_provider'] = $this->option('oauth-provider');
            $teamData['oauth_tenant_id'] = $this->option('oauth-tenant-id');
            
            // Only prompt if not in non-interactive mode and no tenant ID provided
            if (! $teamData['oauth_tenant_id'] && ! $this->option('no-interaction')) {
                $teamData['oauth_tenant_id'] = $this->ask('OAuth tenant ID');
            }
        }

        // Add branding colors if provided
        if ($this->option('primary-color')) {
            $teamData['primary_color'] = $this->option('primary-color');
        }

        if ($this->option('secondary-color')) {
            $teamData['secondary_color'] = $this->option('secondary-color');
        }

        // Create the team
        $team = $teamModel::create($teamData);

        $this->info("✓ Created team: {$team->name} (slug: {$team->slug})");

        // Handle user attachment
        if (! $this->option('no-user')) {
            $userEmail = $this->option('user-email');

            // Only prompt if not in non-interactive mode and no email provided
            if (! $userEmail && ! $this->option('no-interaction')) {
                $userEmail = $this->ask('User email (leave empty to skip)');
            }

            if ($userEmail) {
                $user = $this->getOrCreateUser($userModel, $userEmail);
                $this->attachUserToTeam($user, $team);
            }
        }

        return 0;
    }

    /**
     * Get or create a user.
     */
    protected function getOrCreateUser(string $userModel, string $email): object
    {
        $user = $userModel::where('email', $email)->first();

        if ($user) {
            $this->info("✓ Using existing user: {$user->email}");

            return $user;
        }

        // Create new user
        $userName = $this->option('user-name');
        if (! $userName && ! $this->option('no-interaction')) {
            $userName = $this->ask('User name');
        }

        if (! $userName) {
            $this->error('User name is required when creating a new user.');

            throw new \RuntimeException('User name is required');
        }

        $password = $this->option('user-password');
        if (! $password && ! $this->option('no-interaction')) {
            $password = $this->secret('User password (leave empty to auto-generate)');
        }

        if (! $password) {
            $password = Str::random(16);
            $this->warn("Generated password: {$password}");
        }

        $user = $userModel::create([
            'name' => $userName,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        $this->info("✓ Created user: {$user->email}");

        return $user;
    }

    /**
     * Attach a user to a team.
     */
    protected function attachUserToTeam(object $user, object $team): void
    {
        // Check if relationship already exists
        if ($user->teams()->where('team_id', $team->id)->exists()) {
            $this->warn("User {$user->email} is already attached to team {$team->name}.");

            return;
        }

        $user->teams()->attach($team->id);
        $this->info("✓ Attached user {$user->email} to team {$team->name}");
    }

    /**
     * Get the team model class.
     */
    protected function getTeamModel(): string
    {
        return config('filament-oauth.team_model', \App\Models\Team::class);
    }

    /**
     * Get the user model class.
     */
    protected function getUserModel(): string
    {
        return config('auth.providers.users.model', \App\Models\User::class);
    }

    /**
     * Generate a fantasy team name (Heroku-style: adjective-noun).
     */
    protected function generateFantasyTeamName(): string
    {
        $adjectives = [
            'gentle', 'mystic', 'silent', 'bright', 'swift', 'noble', 'ancient', 'crystal',
            'golden', 'silver', 'crimson', 'azure', 'emerald', 'sapphire', 'amber', 'violet',
            'cosmic', 'stellar', 'lunar', 'solar', 'oceanic', 'mountain', 'forest', 'desert',
            'tropical', 'arctic', 'volcanic', 'peaceful', 'dynamic', 'radiant', 'eternal',
        ];
        
        $nouns = [
            'dragon', 'phoenix', 'eagle', 'wolf', 'tiger', 'lion', 'bear', 'falcon',
            'storm', 'thunder', 'lightning', 'wind', 'flame', 'ice', 'shadow', 'light',
            'star', 'moon', 'sun', 'planet', 'comet', 'nebula', 'galaxy', 'cosmos',
            'ocean', 'mountain', 'river', 'forest', 'valley', 'peak', 'canyon', 'island',
            'crystal', 'gem', 'pearl', 'diamond', 'ruby', 'emerald', 'sapphire', 'jade',
        ];
        
        $adjective = $adjectives[array_rand($adjectives)];
        $noun = $nouns[array_rand($nouns)];
        
        return ucfirst($adjective) . ' ' . ucfirst($noun);
    }

    /**
     * Ensure the slug is unique by appending a number if needed.
     */
    protected function ensureUniqueSlug(string $teamModel, string $slug): string
    {
        $originalSlug = $slug;
        $counter = 1;
        
        while ($teamModel::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
            
            // Safety limit to prevent infinite loops
            if ($counter > 1000) {
                throw new \RuntimeException('Unable to generate unique slug after 1000 attempts');
            }
        }
        
        return $slug;
    }
}

