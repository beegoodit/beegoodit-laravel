# BeeGoodIT Eloquent Userstamps

Automatically track which user created and last updated your Eloquent models.

## Installation

```bash
composer require beegoodit/eloquent-userstamps
```

## Usage

### 1. Add Migration

You can either publish the migration stub or create your own migration:

#### Option 1: Publish Migration Stub (Recommended)

```bash
php artisan vendor:publish --tag=eloquent-userstamps-migrations
```

This will publish a migration stub to `database/migrations/`. Edit the stub to replace `your_table_name` with your actual table name.

#### Option 2: Create Migration Manually

Create a migration to add the columns:

```php
Schema::table('posts', function (Blueprint $table) {
    $table->uuid('created_by_id')->nullable();
    $table->uuid('updated_by_id')->nullable();
    
    // Or with foreign keys:
    // $table->foreignId('created_by_id')->nullable()->constrained('users');
    // $table->foreignId('updated_by_id')->nullable()->constrained('users');
});
```

### 2. Add Trait to Model

```php
use BeeGoodIT\EloquentUserstamps\HasUserStamps;

class Post extends Model
{
    use HasUserStamps;
}
```

### 3. Use Relationships

```php
$post = Post::find(1);

// Get who created the post
$creator = $post->createdBy;

// Get who last updated the post
$updater = $post->updatedBy;
```

## How It Works

When a user creates or updates a model with this trait:
- On **create**: Sets both `created_by_id` and `updated_by_id` to current user
- On **update**: Sets `updated_by_id` to current user

## Requirements

- PHP 8.2+
- Laravel 11.0+ or 12.0+

## Testing

```bash
composer test
```

## License

MIT License. See [LICENSE](LICENSE) for details.

