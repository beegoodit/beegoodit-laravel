<?php

namespace BeegoodIT\FilamentTenancyDomains;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Domain extends Model
{
    use HasUuids;

    protected $fillable = [
        'domain',
        'type',
        'model_id',
        'model_type',
        'is_primary',
        'is_active',
        'verification_token',
        'verified_at',
        'last_verification_error',
        'last_verification_attempt_at',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function verify(): bool
    {
        if ($this->verified_at || $this->type === 'platform') {
            return true;
        }

        $verificationHostname = '_foosbeaver-verification.' . $this->domain;
        $expectedToken = $this->verification_token;

        $this->update([
            'last_verification_attempt_at' => now(),
        ]);

        try {
            $records = dns_get_record($verificationHostname, DNS_TXT);
            
            $verified = false;
            foreach ($records as $record) {
                if (isset($record['txt']) && $record['txt'] === $expectedToken) {
                    $verified = true;
                    break;
                }
            }

            if ($verified) {
                $this->update([
                    'verified_at' => now(),
                    'last_verification_error' => null,
                ]);
                
                \Illuminate\Support\Facades\Log::info("Domain verification successful for: {$this->domain}");
                
                return true;
            }

            $this->update([
                'last_verification_error' => 'Verification TXT record not found or mismatch.',
            ]);
            
            \Illuminate\Support\Facades\Log::warning("Domain verification failed for: {$this->domain}. TXT record not found or mismatch.");
            
            return false;

        } catch (\Exception $e) {
            $this->update([
                'last_verification_error' => $e->getMessage(),
            ]);
            
            \Illuminate\Support\Facades\Log::error("Error during domain verification for {$this->domain}: " . $e->getMessage());
            
            return false;
        }
    }

    protected function isVerified(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->verified_at !== null || $this->type === 'platform',
        );
    }

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
            'verified_at' => 'datetime',
            'last_verification_attempt_at' => 'datetime',
        ];
    }
}
