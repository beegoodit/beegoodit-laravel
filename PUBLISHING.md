# Publishing to Packagist

This document describes the process for publishing packages from this monorepo to Packagist.

## Prerequisites

1. Packagist account at [packagist.org](https://packagist.org)
2. GitHub repository access
3. Packagist API token

## Initial Setup

### 1. Create Packagist Account

1. Go to [packagist.org](https://packagist.org)
2. Sign up with your GitHub account
3. Get your API token from the profile page

### 2. Submit Packages to Packagist

For each package in the monorepo:

1. Go to [packagist.org/packages/submit](https://packagist.org/packages/submit)
2. Enter the repository URL: `https://github.com/beegoodit/beegoodit-laravel`
3. Packagist will detect all packages in the monorepo
4. Select the package you want to publish (e.g., `beegoodit/eloquent-userstamps`)
5. Click "Submit"

**Note**: For a monorepo, you'll need to submit each package individually, or use Packagist's monorepo support if available.

### 3. Configure Auto-Update (Recommended)

1. Go to your package page on Packagist
2. Click "Settings"
3. Enable "Auto-Update" with GitHub webhook
4. Add the webhook URL to your GitHub repository:
   - Go to repository Settings → Webhooks
   - Add webhook: `https://packagist.org/api/github?username=YOUR_USERNAME&apiToken=YOUR_TOKEN`
   - Select "Just the push event"

## Publishing a New Version

### Current Status: [Unreleased]

Currently, all packages use `[Unreleased]` in their CHANGELOG.md files. When ready to publish:

### Versioning Strategy

We use [Semantic Versioning](https://semver.org/):
- **MAJOR** (1.0.0): Breaking changes
- **MINOR** (1.1.0): New features, backward compatible
- **PATCH** (1.0.1): Bug fixes, backward compatible

### Release Process

1. **Update CHANGELOG.md**
   ```markdown
   ## [1.0.0] - 2024-01-15
   
   ### Added
   - Initial release
   - Feature descriptions
   ```

2. **Create Git Tag**
   ```bash
   git tag -a v1.0.0 -m "Release version 1.0.0"
   git push origin v1.0.0
   ```

3. **Packagist Auto-Update**
   - If auto-update is enabled, Packagist will detect the new tag
   - Otherwise, manually trigger update on Packagist

4. **Verify on Packagist**
   - Check package page shows new version
   - Verify installation works: `composer require beegoodit/package-name:^1.0`

## Monorepo Considerations

### Package Dependencies

Packages in this monorepo may depend on each other (e.g., `filament-oauth` depends on `filament-user-avatar`). When publishing:

1. **Internal Dependencies**: Use `*` or `dev-main` for internal packages
2. **External Dependencies**: Use proper version constraints (e.g., `^4.0`)

### Publishing Order

If packages have dependencies, publish in dependency order:

1. Base packages first (e.g., `laravel-file-storage`)
2. Dependent packages second (e.g., `filament-user-avatar`)
3. Packages with multiple dependencies last (e.g., `filament-oauth`)

## Troubleshooting

### Package Not Found on Packagist

- Verify the package name matches exactly: `beegoodit/package-name`
- Check that the repository URL is correct
- Ensure the package has a valid `composer.json`

### Auto-Update Not Working

- Verify webhook is configured correctly
- Check webhook delivery logs in GitHub
- Manually trigger update on Packagist if needed

### Version Not Appearing

- Ensure git tag is pushed: `git push origin v1.0.0`
- Check tag format matches version in composer.json
- Wait a few minutes for Packagist to process

## Best Practices

1. **Always test locally** before publishing
2. **Update CHANGELOG.md** for every release
3. **Use semantic versioning** consistently
4. **Tag releases** in git
5. **Verify installation** after publishing
6. **Keep dependencies up to date**

## Resources

- [Packagist Documentation](https://packagist.org/about)
- [Semantic Versioning](https://semver.org/)
- [Composer Versioning](https://getcomposer.org/doc/articles/versions.md)

