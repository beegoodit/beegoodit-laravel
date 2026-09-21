# Changelog

All notable changes to `filament-tenancy-plugins` will be documented in this file.

## [Unreleased]

### Added
- `Tenant::allows(?Model $tenant, string $id)` — false if the tenant is null or not assigned
- `Tenant::find(string $id)` / `Tenant::ensure(Model|string $tenant, string $id)` — `ensure` throws `NotAssignedException`
- Initial release
- Code catalog via `Tenant::register()`
- Morph assignment table and `HasPlugins` trait
- `PluginAssigned` / `PluginRevoked` events
- Artisan `tenancy-plugins:list`, `tenancy-plugins:assign`, `tenancy-plugins:revoke`

### Fixed
- `Tenant::find()` returns null for an unknown slug instead of querying a UUID/integer primary key with that string

### Changed
- Kernel class is `Tenant` (was `TenantPlugins`); `for()` returns `Access` (was `TenantPluginAccess`)
- Artisan classes are `ListCommand`, `AssignCommand`, `RevokeCommand`
