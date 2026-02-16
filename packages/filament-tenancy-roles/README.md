# BeeGoodIT Filament Tenancy Roles

Role-based authorization and membership management for Filament multi-tenant applications.

## Installation

```bash
composer require beegoodit/filament-tenancy-roles
```

## Features

- **Role-based Access Control**: Manage team memberships with specific roles (`owner`, `admin`, `member`).
- **Superadmin Support**: Built-in "God Mode" for platform admins (users with `is_admin = true`).
- **Pivot Model**: Uses a custom `Membership` model for the `team_user` table.
- **Traits**: Easy integration with `User` and `Team` models.
- **Tenant Scoping**: Automatically scopes authorization checks to the current tenant.

## Setup

### 1. Database Migration

Add a `role` column to your `team_user` table:

```php
Schema::table('team_user', function (Blueprint $table) {
    $table->string('role')->default('member')->after('user_id');
});
```

### 2. Add Traits to Models

#### User Model
Implement `InteractsWithTenantRoles` to handle role checks and relationships.

```php
use BeegoodIT\FilamentTenancyRoles\Models\Concerns\InteractsWithTenantRoles;

class User extends Authenticatable
{
    use InteractsWithTenantRoles;
}
```

#### Team Model
Implement `HasTenantRoles` to handle members and roles.

```php
use BeegoodIT\FilamentTenancyRoles\Models\Concerns\HasTenantRoles;

class Team extends Model
{
    use HasTenantRoles;
}
```

## Usage

### Authorization Checks

```php
$user->isTeamOwner($team); // Returns true if user has 'owner' role
$user->isTeamAdmin($team); // Returns true if user has 'admin' or 'owner' role
$user->hasTeamRole($team, ['admin', 'owner']);
```

### Accessing Roles

```php
$role = $user->teamRole($team); // Returns TeamRole Enum
echo $role->label(); // "Owner", "Admin", or "Member"
```

### Superadmin (God Mode)

If your `User` model has an `is_admin` property set to `true`, all `isTeam*` checks will return `true` automatically.

## License

MIT License. See [LICENSE](LICENSE) for details.
