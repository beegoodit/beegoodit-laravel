# Changelog

All notable changes to `laravel-cookie-consent` will be documented in this file.

## [Unreleased]

### Added
- `Consent::allows()`, `Consent::available()`, `Consent::policyUrlUsing()`
- daisyUI Blade view (`cookie-consent::livewire.cookie-consent-daisy`)
- `scripts/sync-banner.sh` for TallCMS filesystem plugins
- `settings_text_no_policy` translations when no policy URL is set

### Changed
- Zinc settings close control lives in the sticky header (no longer covered)
- Banner view is a config string, not `COOKIE_CONSENT_VIEW`
- Optional categories (functional, analytics, marketing) default **off**; essential stays locked on
- Default policy URLs are empty (no “learn more” until configured)
- Default `ignored_paths` skip admin, Livewire, and installer
- Functional consent is granted only when the cookie value is `all`

### Added (earlier)
- Initial release
- Livewire cookie consent component
- Customizable styling and positioning
- Accept/Decline functionality
- Cookie preference storage
- Configurable lifetime
