<?php

namespace BeeGoodIT\FilamentTenancy\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SeedDemoTeamCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'team:seed:demo 
                            {--name= : Team name (auto-generated if not provided)}
                            {--no-user : Do not create a demo user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a demo team with sample data';

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

        // Generate slug from team name
        $slug = Str::slug($teamName);

        // Ensure slug uniqueness
        $slug = $this->ensureUniqueSlug($teamModel, $slug);

        // Create the team
        $team = $teamModel::create([
            'name' => $teamName,
            'slug' => $slug,
        ]);

        $this->info("✓ Created team: {$team->name} (slug: {$team->slug})");

        // Handle user creation (default: create user, unless --no-user flag is set)
        $withUser = ! $this->option('no-user');
        $user = null;

        if ($withUser) {
            $user = $this->createDemoUser($userModel, $team);
        }

        // Try to call app-specific seeder if it exists
        $this->callAppSeeder($team, $withUser);

        return Command::SUCCESS;
    }

    /**
     * Create a demo user with automatic email generation.
     */
    protected function createDemoUser(string $userModel, object $team): ?object
    {
        $baseEmail = 'user@domain.local';
        $name = 'Demo User';

        // Use firstOrCreate like cargonauten does
        $user = $userModel::firstOrCreate(
            ['email' => $baseEmail],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Always update password directly in DB to bypass the 'hashed' cast
        // This prevents double-hashing when the cast is 'hashed'
        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

        // Refresh the model to get updated attributes
        $user->refresh();

        $this->info("✓ Demo user: {$user->email} (password: password)");

        // Ensure user is attached to team
        $this->attachUserToTeam($user, $team);

        return $user;
    }

    /**
     * Generate a unique email address for demo user.
     * Starts with user@domain.local, then uses Heroku-style names if taken.
     */
    protected function generateUniqueEmail(string $userModel): string
    {
        $baseEmail = 'user@domain.local';

        // Check if base email is available
        if (! $userModel::where('email', $baseEmail)->exists()) {
            return $baseEmail;
        }

        // Generate Heroku-style emails until we find an available one
        $maxAttempts = 1000;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $email = $this->generateFantasyEmail();

            if (! $userModel::where('email', $email)->exists()) {
                return $email;
            }

            $attempt++;
        }

        throw new \RuntimeException('Unable to generate unique email after 1000 attempts');
    }

    /**
     * Generate a Heroku-style email address (adjective-noun@domain.local).
     */
    protected function generateFantasyEmail(): string
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

        return "{$adjective}-{$noun}@domain.local";
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
     * Try to call app-specific seeder if it exists.
     */
    protected function callAppSeeder(object $team, bool $withUser): void
    {
        $seederClass = 'Database\Seeders\Team\DemoSeeder';

        if (! class_exists($seederClass)) {
            $this->warn("Seeder class {$seederClass} not found. Skipping domain data seeding.");
            $this->info("💡 Tip: Create {$seederClass} to seed domain-specific demo data.");

            return;
        }

        try {
            $this->info("Calling app seeder: {$seederClass}");

            $seeder = new $seederClass();
            
            // Check if seeder has a run method that accepts team and withUser
            if (method_exists($seeder, 'run')) {
                // Try to call with team and withUser parameters
                $reflection = new \ReflectionMethod($seeder, 'run');
                $params = $reflection->getParameters();

                if (count($params) >= 2) {
                    $seeder->run($team->name, $withUser);
                } elseif (count($params) >= 1) {
                    $seeder->run($team->name);
                } else {
                    $seeder->run();
                }

                $this->info("✓ App seeder executed successfully");
            } else {
                $this->warn("Seeder class {$seederClass} exists but doesn't have a run() method.");
            }
        } catch (\Exception $e) {
            $this->error("Failed to execute app seeder: {$e->getMessage()}");
            $this->warn("Team was created but domain data seeding failed.");
        }
    }

    /**
     * Get the team model class.
     */
    protected function getTeamModel(): string
    {
        return config('filament-tenancy.team_model', \App\Models\Team::class);
    }

    /**
     * Get the user model class.
     */
    protected function getUserModel(): string
    {
        return config('auth.providers.users.model', \App\Models\User::class);
    }

    /**
     * Generate a fantasy team name (Heroku-style: adjective-noun-randomNumber).
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
        $randomNumber = random_int(10000, 99999);

        return "{$adjective}-{$noun}-{$randomNumber}";
    }

    /**
     * Ensure the slug is unique by appending a number if needed.
     */
    protected function ensureUniqueSlug(string $teamModel, string $slug): string
    {
        $originalSlug = $slug;
        $counter = 1;

        while ($teamModel::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;

            // Safety limit to prevent infinite loops
            if ($counter > 1000) {
                throw new \RuntimeException('Unable to generate unique slug after 1000 attempts');
            }
        }

        return $slug;
    }
}

