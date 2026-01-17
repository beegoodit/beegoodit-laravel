# Changelog

All notable changes to `beegoodit/filament-connect` will be documented in this file.

## [Unreleased]

### Added
- Initial release (Phase 1)
- `ApiAccount` model for storing service credentials
- Filament resource for managing `ApiAccount` records (tenant-aware)
- `Connect` facade with `getCredentials()` helper
- Multi-tenant support via Filament `getTenant()`
