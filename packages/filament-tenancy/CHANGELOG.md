# Changelog

All notable changes to `filament-tenancy` will be documented in this file.

## [Unreleased]

### Changed
- Package renamed from `filament-team-branding` to `filament-tenancy` to better reflect its scope
- Namespace changed from `BeeGoodIT\FilamentTeamBranding` to `BeeGoodIT\FilamentTenancy`
- Migration tag changed from `team-branding-migrations` to `tenancy-migrations`

### Added
- Initial release
- `HasBranding` trait for team models
- Logo upload with S3 support
- Primary/secondary color customization
- OAuth provider and tenant ID fields
- whereOAuthTenant scope for team lookup
- Team registration and profile management pages (coming soon)


