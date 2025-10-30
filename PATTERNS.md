# Documented Patterns

Some features are too simple for packages - use these patterns in your apps.

## Force HTTPS in Production

Add to `app/Providers/AppServiceProvider.php`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
```

**When to use**: All production apps  
**Why not a package**: Only 2 lines, already app-specific

---

## Use UUIDs Instead of Integer IDs

Laravel provides a built-in trait for UUIDs.

### 1. Add Trait to Models

```php
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Authenticatable
{
    use HasUuids;
}

class Team extends Model
{
    use HasUuids;
}
```

### 2. Update Migrations

**For new tables:**
```php
Schema::create('users', function (Blueprint $table) {
    $table->uuid('id')->primary();  // ← Instead of $table->id()
    $table->uuid('team_id');         // ← Instead of foreignId
    $table->string('name');
    $table->timestamps();
});
```

**For foreign keys:**
```php
$table->uuid('user_id');
$table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

// Or use the index method:
$table->uuid('team_id')->index();
```

### 3. Update Pivot Tables

```php
Schema::create('team_user', function (Blueprint $table) {
    $table->uuid('team_id');
    $table->uuid('user_id');
    
    $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
    
    $table->primary(['team_id', 'user_id']);
});
```

### 4. Converting Existing Tables (Advanced)

**⚠️ WARNING**: Backup your database before running!

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add new UUID column
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });
        
        // 2. Generate UUIDs for existing records
        DB::table('users')->get()->each(function ($user) {
            DB::table('users')
                ->where('id', $user->id)
                ->update(['uuid' => (string) Str::uuid()]);
        });
        
        // 3. Drop old ID and rename UUID to ID
        // This is complex - consider starting fresh or keeping integer IDs
    }
};
```

**Recommendation**: Use UUIDs for **new apps**. For existing apps, consider the migration complexity.

---

## S3 Configuration

Already provided by Laravel's `config/filesystems.php`:

```php
'default' => env('FILESYSTEM_DISK', 'local'),

'disks' => [
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
        'url' => env('AWS_URL'),
        'endpoint' => env('AWS_ENDPOINT'),
        'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
    ],
],
```

**`.env` configuration:**
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket
AWS_ENDPOINT=https://s3.amazonaws.com
```

**Why not a package**: Laravel already provides this. Use `beegoodit/laravel-file-storage` for automatic URL generation.

---

## When to Create a Package vs Use a Pattern

### Create a Package When:
- ✅ Code is duplicated across 3+ apps
- ✅ Logic is non-trivial (50+ lines)
- ✅ Provides reusable functionality
- ✅ Can be tested independently
- ✅ Has clear boundaries

### Use a Pattern When:
- ✅ Very simple (2-10 lines)
- ✅ Already provided by framework
- ✅ App-specific configuration
- ✅ One-time setup
- ✅ Rarely changes


