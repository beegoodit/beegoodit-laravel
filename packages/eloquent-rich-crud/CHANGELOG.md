# Changelog

All notable changes to `eloquent-rich-crud` will be documented in this file.

## [Unreleased]

### Added
- `Provisionable` trait guarding `creating` / `deleting`
- `provision()` / `deprovision()` dispatching `{namespace}\Provision{Class}` / `Deprovision{Class}` via `run()`
- `createAllowed()` / `deleteAllowed()` hatches for trusted domain actions and naked factories
