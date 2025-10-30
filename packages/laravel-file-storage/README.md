# BeeGoodIT Laravel File Storage

Unified file storage with automatic S3/local disk selection and URL generation. Handles the common pattern of generating signed URLs for S3 and public URLs for local storage.

## Installation

```bash
composer require beegoodit/laravel-file-storage
```

## The Problem

Every model with file uploads duplicates this logic:

```php
// ❌ Duplicated in every model
private function getDisk(): string {
    return config('filesystems.default') === 's3' ? 's3' : 'public';
}

public function getAvatarUrl(): ?string {
    $disk = $this->getDisk();
    if ($disk === 's3') {
        return Storage::disk('s3')->temporaryUrl($this->avatar, now()->addHour());
    }
    return Storage::disk($disk)->url($this->avatar);
}
```

## The Solution

Use the `HasStoredFiles` trait:

```php
use BeeGoodIT\LaravelFileStorage\Models\Concerns\HasStoredFiles;

class User extends Model
{
    use HasStoredFiles;
    
    protected $storedFiles = ['avatar', 'document'];
}
```

Now you get automatic URL methods:

```php
$user->getAvatarUrl();     // Auto-generates signed URL for S3 or public URL
$user->getDocumentUrl();   // Works for all fields in $storedFiles
```

## Usage

### With Models

```php
use BeeGoodIT\LaravelFileStorage\Models\Concerns\HasStoredFiles;

class Team extends Model
{
    use HasStoredFiles;
    
    protected $storedFiles = ['logo'];
}

// Get logo URL (automatic S3 signed URL or public URL)
$team->getLogoUrl();

// Custom expiry (default is 60 minutes)
$team->getLogoUrl(120); // 2 hours
```

### With Service

```php
use BeeGoodIT\LaravelFileStorage\Services\FileStorageService;

$service = app(FileStorageService::class);

// Store file
$path = $service->store($fileContents, 'uploads/documents', 'doc.pdf');

// Get URL
$url = $service->url($path);

// Delete file
$service->delete($path);

// Check existence
if ($service->exists($path)) {
    // ...
}
```

## Configuration

Works with your existing `config/filesystems.php`:

```php
'default' => env('FILESYSTEM_DISK', 'local'),

'disks' => [
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
        // ...
    ],
    
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        // ...
    ],
],
```

## How It Works

- **Local/Public disk**: Returns public URLs (`/storage/file.jpg`)
- **S3 disk**: Returns temporary signed URLs (secure, time-limited)
- **Automatic**: Detects disk from `config('filesystems.default')`

## Requirements

- PHP 8.2+
- Laravel 11.0+ or 12.0+

## Testing

```bash
composer test
```

## License

MIT License. See [LICENSE](LICENSE) for details.

