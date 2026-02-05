<?php

namespace BeegoodIT\LaravelFeedback\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackItem extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subject',
        'description',
        'created_by',
        'user_agent',
        'ip_address',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user who created this feedback item.
     */
    public function creator(): BelongsTo
    {
        $userModel = config('feedback.user_model', \App\Models\User::class);

        return $this->belongsTo($userModel, 'created_by');
    }

    /**
     * Get formatted IP address.
     */
    public function getFormattedIpAddress(): ?string
    {
        return $this->ip_address;
    }

    /**
     * Get formatted user agent string.
     */
    public function getFormattedUserAgent(): ?string
    {
        return $this->user_agent;
    }

    /**
     * Extract browser name from user agent.
     */
    public function getBrowserName(): ?string
    {
        if (!$this->user_agent) {
            return null;
        }

        $userAgent = $this->user_agent;

        // Simple browser detection
        if (preg_match('/Chrome\/([0-9.]+)/', $userAgent, $matches) && !preg_match('/Edg/', $userAgent)) {
            return 'Chrome';
        }
        if (preg_match('/Firefox\/([0-9.]+)/', $userAgent, $matches)) {
            return 'Firefox';
        }
        if (preg_match('/Safari\/([0-9.]+)/', $userAgent, $matches) && !preg_match('/Chrome/', $userAgent)) {
            return 'Safari';
        }
        if (preg_match('/Edg\/([0-9.]+)/', $userAgent, $matches)) {
            return 'Edge';
        }
        if (preg_match('/Opera\/([0-9.]+)/', $userAgent, $matches)) {
            return 'Opera';
        }

        return null;
    }

    /**
     * Extract operating system from user agent.
     */
    public function getOperatingSystem(): ?string
    {
        if (!$this->user_agent) {
            return null;
        }

        $userAgent = $this->user_agent;

        // Simple OS detection
        if (preg_match('/Windows NT ([0-9.]+)/', $userAgent, $matches)) {
            $version = $matches[1] ?? '';
            if ($version === '10.0') {
                return 'Windows 10/11';
            }
            if ($version === '6.3') {
                return 'Windows 8.1';
            }
            if ($version === '6.2') {
                return 'Windows 8';
            }
            if ($version === '6.1') {
                return 'Windows 7';
            }

            return 'Windows';
        }
        if (preg_match('/Mac OS X ([0-9_]+)/', $userAgent, $matches)) {
            return 'macOS';
        }
        if (preg_match('/Linux/', $userAgent)) {
            return 'Linux';
        }
        if (preg_match('/Android ([0-9.]+)/', $userAgent, $matches)) {
            return 'Android';
        }
        if (preg_match('/iPhone OS ([0-9_]+)/', $userAgent, $matches) || preg_match('/iPad.*OS ([0-9_]+)/', $userAgent, $matches)) {
            return 'iOS';
        }

        return null;
    }
}
