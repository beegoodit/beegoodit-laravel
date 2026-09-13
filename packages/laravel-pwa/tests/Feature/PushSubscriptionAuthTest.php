<?php

namespace BeegoodIT\LaravelPwa\Tests\Feature;

use BeegoodIT\LaravelPwa\Tests\TestCase;

class PushSubscriptionAuthTest extends TestCase
{
    public function test_guest_cannot_store_a_push_subscription(): void
    {
        $this->post('/api/push-subscriptions', [
            'endpoint' => 'https://push.example/sub',
            'keys' => [
                'p256dh' => 'key',
                'auth' => 'token',
            ],
        ])->assertRedirect();
    }

    public function test_guest_cannot_delete_a_push_subscription(): void
    {
        $this->delete('/api/push-subscriptions', [
            'endpoint' => 'https://push.example/sub',
        ])->assertRedirect();
    }
}
