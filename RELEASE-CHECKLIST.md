# Release Checklist

Use this checklist when preparing a new release of packages.

## Pre-Release

### Code Quality
- [ ] All tests passing (`composer test`)
- [ ] Code formatted with Pint (`composer format`)
- [ ] No syntax errors
- [ ] No linter warnings

### Documentation
- [ ] All package README.md files are up to date
- [ ] CHANGELOG.md updated with new changes
- [ ] Main README.md reflects current state
- [ ] Code examples in docs are correct

### Package Validation
- [ ] All packages have valid `composer.json`
- [ ] Service providers are properly configured
- [ ] Autoload paths are correct
- [ ] Dependencies are correctly specified
- [ ] Package names follow convention: `beegoodit/package-name`

### Testing
- [ ] All PHPUnit tests pass (32/32)
- [ ] Test coverage is acceptable
- [ ] Integration tests pass (if applicable)
- [ ] No breaking changes (or documented if intentional)

## Release Process

### Versioning
- [ ] Decide on version number (following semantic versioning)
- [ ] Update CHANGELOG.md with version and date
- [ ] Update version in package if needed (currently using [Unreleased])

### Git
- [ ] All changes committed
- [ ] Create release branch (optional): `git checkout -b release/v1.0.0`
- [ ] Create git tag: `git tag -a v1.0.0 -m "Release version 1.0.0"`
- [ ] Push tag: `git push origin v1.0.0`

### Packagist
- [ ] Verify package exists on Packagist
- [ ] Check auto-update is enabled (or manually trigger update)
- [ ] Verify new version appears on Packagist
- [ ] Test installation: `composer require beegoodit/package-name:^1.0`

## Post-Release

### Verification
- [ ] Package installs correctly via Packagist
- [ ] All dependencies resolve correctly
- [ ] Service providers auto-discover correctly
- [ ] No errors in fresh Laravel installation

### Communication
- [ ] Update any relevant documentation
- [ ] Announce release (if applicable)
- [ ] Update dependent projects if needed

## Example Release Workflow

```bash
# 1. Ensure everything is ready
composer test
composer format

# 2. Update CHANGELOG.md
# Edit packages/package-name/CHANGELOG.md

# 3. Commit changes
git add .
git commit -m "chore: prepare v1.0.0 release"

# 4. Create and push tag
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin v1.0.0

# 5. Verify on Packagist
# Check https://packagist.org/packages/beegoodit/package-name

# 6. Test installation
composer require beegoodit/package-name:^1.0
```

## Version History Template

When updating CHANGELOG.md, use this format:

```markdown
## [1.0.0] - 2024-01-15

### Added
- Feature description
- Another feature

### Changed
- Improvement description

### Fixed
- Bug fix description

### Removed
- Deprecated feature (if any)
```

## Notes

- Currently using `[Unreleased]` in CHANGELOG.md - update when ready to publish
- All packages are ready for Packagist publishing
- Internal dependencies use `*` version constraint (acceptable for monorepo)
- Test thoroughly before releasing to production

