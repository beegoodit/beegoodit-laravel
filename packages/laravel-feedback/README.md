# Laravel Feedback

A comprehensive feedback system for Laravel applications with Filament integration. Allows users to submit feedback through public pages (using Flux UI) and Filament admin panels, with automatic metadata collection and full CRUDL management.

## Features

- **Dual Interface Support**:
  - Public pages: Flux UI modal for feedback submission
  - Filament panels: Icon button in top navbar with Filament modal
- **Automatic Integration**: Feedback button automatically appears on all Filament panels via render hooks
- **Full CRUDL Resource**: Admin panel resource for managing feedback items
- **Metadata Collection**: Automatically stores user agent and IP address
- **Success/Error Feedback**: Visual feedback messages for successful and failed submissions
- **Multi-language Support**: Translations included for English, German, and Spanish
- **Authentication Required**: Users must be logged in to submit feedback
- **UUID Support**: Uses UUIDs for primary keys (matching Laravel conventions)

## Installation

```bash
composer require beegoodit/laravel-feedback
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=feedback-config
```

This will create `config/feedback.php` with the following options:

- `user_model`: The user model class (default: `App\Models\User`)

## Database Migration

Publish and run the migration:

```bash
php artisan vendor:publish --tag=feedback-migrations
php artisan migrate
```

The migration creates a `feedback_items` table with:
- `id` (UUID, primary key)
- `subject` (string, required)
- `description` (text, required)
- `created_by` (UUID, foreign key to users, required)
- `user_agent` (text, nullable)
- `ip_address` (string, nullable)
- `created_at`, `updated_at` (timestamps)

## Usage

### Automatic Filament Panel Integration

The feedback button is **automatically added** to all Filament panels via render hooks. No manual configuration is required! The button appears in the top navbar, left of the user profile menu, on all panels including:

- Admin panel (`/admin`)
- Portal panel (`/portal`)
- Player panel (`/player`)
- User profile panel (`/me`)
- Any other Filament panels

The button opens a Filament modal with a feedback form. On successful submission, a success notification appears and the modal closes. On error, an error notification appears and the modal stays open.

### Manual Filament Panel Integration (Optional)

If you need to customize the integration, you can manually add the feedback resource to your Admin panel:

```php
use BeegoodIT\LaravelFeedback\Filament\Resources\FeedbackItemResource;

public function panel(Panel $panel): Panel
{
    return $panel
        ->id('admin')
        ->resources([
            FeedbackItemResource::class,
            // ... other resources
        ]);
}
```

The resource will appear in the "Feedback" navigation group with a count badge showing the number of feedback items.

### Public Pages Integration

Add the feedback modal to your public layout header:

```blade
<div>
    @livewire('laravel-feedback::feedback-modal')
</div>
```

Or use the component directly:

```blade
<livewire:laravel-feedback::feedback-modal />
```

**Important**: The feedback button is visible to all users (authenticated and unauthenticated). When an unauthenticated user clicks it, they will be redirected to the login page. After successful login, they can submit feedback.

#### Authentication Flow

1. Unauthenticated user clicks feedback button
2. User is redirected to login page (`/me/login`)
3. After successful login, user can submit feedback
4. Modal shows success message and closes after 1.5 seconds

### Components

#### FeedbackModal (Public Pages)

Livewire component for public-facing feedback submission using Flux UI.

**Properties:**
- `subject` (string): Feedback subject
- `description` (string): Feedback description
- `showSuccess` (bool): Controls success message visibility
- `showError` (bool): Controls error message visibility
- `errorMessage` (string|null): Error message text

**Methods:**
- `openModal()`: Checks authentication and redirects to login if needed
- `submit()`: Validates and saves feedback, shows success/error messages

#### FeedbackButton (Filament Panels)

Livewire component for Filament panel feedback submission using Filament Actions.

**Methods:**
- `feedbackAction()`: Returns a Filament Action that opens a modal with the feedback form

## Translations

The package includes translations for English, German, and Spanish. All translation keys are namespaced under `feedback::feedback.*`.

### Available Translation Keys

- `feedback::feedback.modal.title` - Modal title
- `feedback::feedback.modal.description` - Modal description
- `feedback::feedback.form.subject` - Subject label
- `feedback::feedback.form.description` - Description label
- `feedback::feedback.form.submit` - Submit button label
- `feedback::feedback.form.cancel` - Cancel button label
- `feedback::feedback.submit.success` - Success message
- `feedback::feedback.submit.error` - Error message title
- `feedback::feedback.submit.error_body` - Error message body

To customize translations, publish them:

```bash
php artisan vendor:publish --tag=feedback-lang
```

This will copy translation files to `lang/vendor/laravel-feedback/`.

## Testing

The package includes comprehensive Pest tests. To run the tests:

### Running Tests from Package Directory

```bash
cd ../../composer/beegoodit-laravel/packages/laravel-feedback
composer install
composer test
```

### Running Tests from Main Application

Since the package uses a path repository, tests can be run from the main application:

```bash
php artisan test --filter=FeedbackItem
php artisan test --filter=FeedbackModal
php artisan test --filter=FeedbackButton
```

### Test Coverage

**Unit Tests** (`tests/Unit/FeedbackItemTest.php`):
- Model relationships (`creator()` relationship)
- Required field validation (`subject`, `description`)
- Metadata storage (`user_agent`, `ip_address`)

**Feature Tests** (`tests/Feature/FeedbackModalTest.php`):
- Authenticated user can submit feedback
- Form validation (required fields)
- Success message display after submission
- Error message display on failure
- Metadata collection (user agent, IP address)
- Form reset after successful submission

**Feature Tests** (`tests/Feature/FeedbackButtonTest.php`):
- FeedbackButton component can be instantiated
- Action method exists and is callable

## Models

### FeedbackItem

The main model for feedback items.

**Relationships:**
- `creator()`: BelongsTo relationship to the User model

**Fillable Fields:**
- `subject`
- `description`
- `created_by`
- `user_agent`
- `ip_address`

## Filament Resources

### FeedbackItemResource

Full CRUDL resource for managing feedback items in the Admin panel.

**Features:**
- List view with searchable and sortable columns
- View, Edit, Delete actions
- Date range filters
- Navigation badge showing total count
- Translated navigation group and labels

**Columns:**
- Subject (searchable, sortable)
- Description (toggleable, hidden by default)
- Creator (searchable, sortable)
- Created At (sortable, toggleable)

## Architecture

### Service Provider

The `FeedbackServiceProvider` automatically:
- Registers Livewire components
- Loads migrations
- Loads translations
- Registers policies
- Adds feedback button to all Filament panels via render hooks

### Render Hooks

The feedback button is added to all panels using Filament's global render hook system:

```php
FilamentView::registerRenderHook(
    PanelsRenderHook::USER_MENU_BEFORE,
    fn (): string => Blade::render('@livewire("laravel-feedback::feedback-button")')
);
```

This ensures the button appears on all panels, including those registered by vendor packages.

## Customization

### Custom User Model

If your application uses a custom user model, update the configuration:

```php
// config/feedback.php
return [
    'user_model' => App\Models\CustomUser::class,
];
```

### Customizing the Feedback Form

To add additional fields or customize validation, you can extend the components:

1. Create your own Livewire component extending `FeedbackModal` or `FeedbackButton`
2. Override the `submit()` method or `feedbackAction()` method
3. Register your custom component in your service provider

## Requirements

- PHP 8.2+
- Laravel 11.0+ or 12.0+
- Filament 4.0+
- Livewire 3.0+
- Flux UI 2.0+ (for public pages)

## License

MIT
