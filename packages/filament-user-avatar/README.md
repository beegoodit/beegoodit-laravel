# BeeGoodIT Filament User Avatar

User avatar upload and display with automatic S3/local storage support for Filament applications.

## Installation

```bash
composer require beegoodit/filament-user-avatar
```

**Note**: This package depends on `beegoodit/laravel-file-storage`.

## Setup

### 1. Publish Migration

```bash
php artisan vendor:publish --tag=user-avatar-migrations
php artisan migrate
```

### 2. Add Trait to User Model

```php
use BeeGoodIT\FilamentUserAvatar\Models\Concerns\HasAvatar;
use Filament\Models\Contracts\HasAvatar as FilamentHasAvatar;

class User extends Authenticatable implements FilamentHasAvatar
{
    use HasAvatar;
    
    protected $fillable = [
        // ...
        'avatar',
    ];
}
```

## Usage

### Get Avatar URL

```php
$user->getAvatarUrl();  // Automatic S3 signed URL or public URL

// For Filament (navbar, etc.)
$user->getFilamentAvatarUrl();

// Get initials as fallback
$user->initials();  // "JD" for "John Doe"
```

### Upload Avatar

```php
use BeeGoodIT\FilamentUserAvatar\Services\AvatarService;

$avatarService = app(AvatarService::class);

// From uploaded file
$path = $avatarService->storeAvatar($user, $imageData, 'jpg');

// From base64 (OAuth, etc.)
$path = $avatarService->storeAvatarFromBase64($user, 'data:image/png;base64,...');

// Update and delete old
$avatarService->updateUserAvatar($user, $path);

// Delete avatar
$avatarService->deleteAvatar($user);
```

### In Filament Forms

```php
use Filament\Forms\Components\FileUpload;

FileUpload::make('avatar')
    ->label('Avatar')
    ->image()
    ->disk(config('filesystems.default'))
    ->directory('users/avatar')
    ->maxSize(2048)
    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
    ->avatar()
    ->imageEditor();
```

## Features

- ✅ Automatic S3/local storage selection
- ✅ Signed URLs for S3 (secure, time-limited)
- ✅ Base64 image support (for OAuth avatars)
- ✅ Old avatar cleanup on upload
- ✅ User initials generation
- ✅ Filament avatar interface support

## Requirements

- PHP 8.2+
- Laravel 11.0+ or 12.0+
- Filament 4.0+
- beegoodit/laravel-file-storage

## License

MIT License. See [LICENSE](LICENSE) for details.

