# Implementation Plan - User Registration and Social Login (Phased)

Enable user registration on the `/me/login` page and activate social login via `filament-oauth` with Discord support. The plan is split into two main phases with a planned break in between.

## Phase 1: User Registration (Core)

This phase focuses on enabling the basic email/password registration flow in a toggleable way.

### [packages/filament-user-profile]

#### [NEW] [config/filament-user-profile.php](file:///wsl.localhost/Ubuntu/home/robo/projects/composer/beegoodit-laravel/packages/filament-user-profile/config/filament-user-profile.php)
- Define a `registration` flag, defaulting to `false`.

#### [MODIFY] [FilamentUserProfileServiceProvider.php](file:///wsl.localhost/Ubuntu/home/robo/projects/composer/beegoodit-laravel/packages/filament-user-profile/src/FilamentUserProfileServiceProvider.php)
- Register and publish the new configuration file.

#### [MODIFY] [UserProfilePanelProvider.php](file:///wsl.localhost/Ubuntu/home/robo/projects/composer/beegoodit-laravel/packages/filament-user-profile/src/Filament/UserProfilePanelProvider.php)
- Use `config('filament-user-profile.registration')` to conditionally call `->registration()`.

#### [MODIFY] [README.md](file:///wsl.localhost/Ubuntu/home/robo/projects/composer/beegoodit-laravel/packages/filament-user-profile/README.md)
- Document the new configuration option for enabling registration.

### [foosbeaver]

#### [MODIFY] [config/filament-user-profile.php] [NEW]
- Publish the config from the package and set `'registration' => true`.

#### [NEW] [Knowledge Base Entry](file:///wsl.localhost/Ubuntu/home/robo/projects/beegoodit/foosbeaver/docs/knowledge-base/user-registration.md)
- Create a new help page explaining how to register for a player account.

### [Verification (Phase 1)]
- **Automated**: Verify that the registration route presence depends on the config value in `filament-user-profile`.
- **Manual**: Navigate to `/me/register` in `foosbeaver` and complete registration. Verify no team creation prompt appears.

---

> [!NOTE]
> **Planned Break**: We will stop here before proceeding to Phase 2.

---

## Phase 2: Social Login (Discord)

This phase adds Discord support to the existing OAuth infrastructure.

### [packages/filament-oauth]

#### [MODIFY] [composer.json](file:///wsl.localhost/Ubuntu/home/robo/projects/composer/beegoodit-laravel/packages/filament-oauth/composer.json)
- Add `socialiteproviders/discord` as a dependency.

#### [MODIFY] [config/filament-oauth.php](file:///wsl.localhost/Ubuntu/home/robo/projects/composer/beegoodit-laravel/packages/filament-oauth/config/filament-oauth.php)
- Add Discord to the `providers` configuration.
- Map environment variables: `DISCORD_CLIENT_ID`, `DISCORD_CLIENT_SECRET`.

#### [MODIFY] [README.md](file:///wsl.localhost/Ubuntu/home/robo/projects/composer/beegoodit-laravel/packages/filament-oauth/README.md)
- Add instructions for configuring Discord support.

### [foosbeaver]

#### [MODIFY] [.env](file:///wsl.localhost/Ubuntu/home/robo/projects/beegoodit/foosbeaver/.env)
- Add Discord credentials:
  ```env
  OAUTH_DISCORD_ENABLED=true
  DISCORD_CLIENT_ID=your_client_id
  DISCORD_CLIENT_SECRET=your_client_secret
  ```

#### [MODIFY] [config/services.php](file:///wsl.localhost/Ubuntu/home/robo/projects/beegoodit/foosbeaver/config/services.php)
- Add Discord configuration:
  ```php
  'discord' => [
      'client_id' => env('DISCORD_CLIENT_ID'),
      'client_secret' => env('DISCORD_CLIENT_SECRET'),
      'redirect' => env('APP_URL') . '/me/oauth/callback/discord',
  ],
  ```

### [Verification (Phase 2)]
- **Setup**:
  1. Publish `filament-socialite` migrations: `php artisan vendor:publish --tag=filament-socialite-migrations`
  2. Publish `filament-oauth` migrations: `php artisan vendor:publish --tag=filament-oauth-migrations`
  3. Run migrations: `php artisan migrate`
- **Automated**: Verify the Discord provider configuration in `filament-oauth`.
- **Manual**: Verify the Discord button initiates the OAuth flow on `/me/login`.

## Phase 3: Avatar Sync and Team Assignment Refactoring

### goal
Sync profile pictures from Discord/M365 and make team assignment configurable and provider-agnostic.

### Proposed Changes

#### [MODIFY] [config/filament-oauth.php](file:///wsl.localhost/Ubuntu/home/robo/projects/composer/beegoodit-laravel/packages/filament-oauth/config/filament-oauth.php)
- Add `sync_avatars` global config.
- Add `team_assignment` flag per provider.

#### [MODIFY] [FilamentSocialitePluginHelper.php](file:///wsl.localhost/Ubuntu/home/robo/projects/composer/beegoodit-laravel/packages/filament-oauth/src/FilamentSocialitePluginHelper.php)
- Implement avatar sync logic using `AvatarService` (downloading Socialite avatar URL).
- Refactor `createUserUsing` to call `TeamAssignmentService` for any provider if enabled.

#### [MODIFY] [TeamAssignmentService.php](file:///wsl.localhost/Ubuntu/home/robo/projects/composer/beegoodit-laravel/packages/filament-oauth/src/Services/TeamAssignmentService.php)
- Make `assignUserToTeam` more generic.
- Add support for extracting tenant/organization info from other providers if needed.

#### [MODIFY] [.env](file:///wsl.localhost/Ubuntu/home/robo/projects/beegoodit/foosbeaver/.env)
- Set `OAUTH_AUTO_ASSIGN_TEAMS=false` for `foosbeaver`.

### Verification (Phase 3)
- **Automated**: Add tests for avatar sync in `filament-oauth`.
- **Manual**: Verify Discord profile picture is synced after login.
- **Manual**: Verify no teams are created for Discord users in `foosbeaver`.
