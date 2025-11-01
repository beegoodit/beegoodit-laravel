# Changelog

All notable changes to `filament-oauth` will be documented in this file.

## [Unreleased]

### Added
- Initial release
- OAuth account model with encrypted tokens
- SocialiteUser model for Filament integration
- HasOAuth trait for user models
- TeamAssignmentService for automatic team assignment
- Event listeners for OAuth connection/registration
- Microsoft provider support
- Configuration file for package settings
- Automatic Microsoft Socialite driver registration (Laravel 12 compatible)
- Configurable team assignment (can be disabled via config)
- Microsoft tenant ID extraction from JWT tokens
- Post-install instructions for publishing migrations
- Runtime migration instructions when running vendor:publish
- `suppress_instructions` config option to hide migration warnings

### Changed
- Updated README with clear migration publishing instructions
- Clarified dependency on filament-socialite migrations
- Added UUID migration notes for users with UUID primary keys
- Documented `->registration(true)` requirement in Filament panel configuration
- Added troubleshooting section for common setup issues


