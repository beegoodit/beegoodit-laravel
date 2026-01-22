<?php

namespace BeegoodIT\FilamentOAuth\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MicrosoftGraphService
{
    /**
     * Get the organization name from Microsoft Graph API.
     */
    public function getOrganizationName(string $accessToken, string $tenantId): string
    {
        try {
            $response = Http::withToken($accessToken)
                ->get('https://graph.microsoft.com/v1.0/organization');

            if ($response->successful()) {
                $data = $response->json();

                // The organization endpoint returns an array with organization details
                if (isset($data['value'][0]['displayName'])) {
                    return $data['value'][0]['displayName'];
                }
            }

            Log::warning('Failed to fetch organization name from Microsoft Graph API', [
                'status' => $response->status(),
                'tenant_id' => $tenantId,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching organization name from Microsoft Graph API', [
                'error' => $e->getMessage(),
                'tenant_id' => $tenantId,
            ]);
        }

        // Return fallback name using first 8 characters of tenant ID
        return 'Team '.substr($tenantId, 0, 8);
    }
}
