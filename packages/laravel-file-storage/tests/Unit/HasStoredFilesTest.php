<?php

use BeeGoodIT\LaravelFileStorage\Models\Concerns\HasStoredFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('s3');
});

it('generates URLs for stored files', function () {
    $model = new class extends Model {
        use HasStoredFiles;
        
        protected $storedFiles = ['avatar'];
        public $avatar = 'users/1/avatar.jpg';
    };
    
    Storage::disk('public')->put('users/1/avatar.jpg', 'test content');
    
    $url = $model->getAvatarUrl();
    
    expect($url)->toContain('users/1/avatar.jpg');
});

it('returns null for empty file paths', function () {
    $model = new class extends Model {
        use HasStoredFiles;
        
        protected $storedFiles = ['avatar'];
        public $avatar = null;
    };
    
    expect($model->getAvatarUrl())->toBeNull();
});

it('uses S3 disk when configured', function () {
    config(['filesystems.default' => 's3']);
    
    $model = new class extends Model {
        use HasStoredFiles;
        
        protected $storedFiles = ['document'];
        public $document = 'docs/file.pdf';
    };
    
    Storage::disk('s3')->put('docs/file.pdf', 'test content');
    
    $url = $model->getDocumentUrl();
    
    // S3 URLs should be temporary signed URLs
    expect($url)->not->toBeNull();
});

it('detects disk automatically', function () {
    $model = new class extends Model {
        use HasStoredFiles;
    };
    
    config(['filesystems.default' => 'public']);
    expect($model->getFileDisk())->toBe('public');
    
    config(['filesystems.default' => 's3']);
    expect($model->getFileDisk())->toBe('s3');
});

