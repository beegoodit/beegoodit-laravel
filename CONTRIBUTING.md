# Contributing to BeeGoodIT Laravel Packages

Thank you for your interest in contributing to the BeeGoodIT Laravel Packages monorepo!

## Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer
- Git

### Development Setup

1. Clone the repository:
```bash
git clone https://github.com/beegoodit/beegoodit-laravel.git
cd beegoodit-laravel
```

2. Install dependencies:
```bash
composer install
```

3. Run tests to ensure everything works:
```bash
composer test
```

## Development Workflow

### Making Changes

1. Create a new branch for your changes:
```bash
git checkout -b feature/your-feature-name
```

2. Make your changes in the appropriate package directory:
   - Package source code: `packages/package-name/src/`
   - Tests: `packages/package-name/tests/`
   - Documentation: `packages/package-name/README.md`

3. Run tests to ensure your changes work:
```bash
composer test
```

4. Format your code:
```bash
composer format
```

5. Commit your changes with a clear message:
```bash
git commit -m "feat(package-name): description of your change"
```

### Code Style

We use [Laravel Pint](https://laravel.com/docs/pint) to maintain consistent code style. All code must follow PSR-12 standards.

Before committing, always run:
```bash
composer format
```

### Testing

- All packages use PHPUnit with Orchestra Testbench
- Tests should be written for new features and bug fixes
- Aim for high test coverage
- Run tests before submitting:
```bash
composer test
```

### Documentation

- Update package README.md if you add new features
- Update CHANGELOG.md with your changes under [Unreleased]
- Keep documentation clear and concise

## Submitting Changes

### Pull Request Process

1. Push your branch to GitHub:
```bash
git push origin feature/your-feature-name
```

2. Create a Pull Request on GitHub
   - Provide a clear title and description
   - Reference any related issues
   - Ensure all tests pass

3. Wait for review
   - We'll review your PR and provide feedback
   - Make any requested changes
   - Once approved, your PR will be merged

### Commit Message Format

We follow [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

Examples:
```
feat(eloquent-userstamps): add soft delete support
fix(filament-oauth): resolve token expiration issue
docs(laravel-file-storage): update S3 configuration examples
```

## Reporting Issues

### Before Reporting

- Check if the issue already exists
- Ensure you're using the latest version
- Verify it's not a configuration issue

### Creating an Issue

When creating an issue, please include:

1. **Package name** and version
2. **Laravel version** you're using
3. **PHP version**
4. **Description** of the issue
5. **Steps to reproduce**
6. **Expected behavior**
7. **Actual behavior**
8. **Error messages** (if any)
9. **Code examples** (if relevant)

## Package Structure

Each package should have:

- `composer.json` - Package configuration
- `README.md` - Documentation
- `CHANGELOG.md` - Change log
- `LICENSE` - MIT license
- `src/` - Source code
- `tests/` - Test files

## Questions?

If you have questions about contributing, please:
- Open a discussion on GitHub
- Check existing issues and PRs
- Review package documentation

Thank you for contributing! 🎉

