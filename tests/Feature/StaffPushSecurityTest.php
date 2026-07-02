<?php

namespace Tests\Feature;

use App\Jobs\WebPushStaffNotificationJob;
use App\Models\Setting;
use App\Models\User;
use App\Support\StaffNotificationUrl;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use NotificationChannels\WebPush\PushSubscription;
use Tests\TestCase;

class StaffPushSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_guest_cannot_store_push_subscription(): void
    {
        $response = $this->postJson(route('admin.push-subscriptions.store'), $this->validSubscription());

        $response->assertUnauthorized();
    }

    public function test_non_staff_user_cannot_store_push_subscription(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->withoutMiddleware([
            \Spatie\Permission\Middleware\RoleMiddleware::class,
            \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ])
            ->actingAs($user)
            ->postJson(route('admin.push-subscriptions.store'), $this->validSubscription());

        $response->assertForbidden()
            ->assertJson(['success' => false]);
    }

    public function test_unverified_staff_with_role_can_store_push_subscription(): void
    {
        $admin = User::factory()->unverified()->create();
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)
            ->postJson(route('admin.push-subscriptions.store'), $this->validSubscription());

        $response->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_push_subscription_status_reports_subscribed_state(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        $this->actingAs($admin)
            ->getJson(route('admin.push-subscriptions.status'))
            ->assertOk()
            ->assertJsonPath('data.subscribed', false);

        $admin->updatePushSubscription(
            'https://fcm.googleapis.com/fcm/send/test-endpoint',
            'test-p256dh-key-value-here-abc',
            'test-auth-key-value-here-abc'
        );

        $this->actingAs($admin)
            ->getJson(route('admin.push-subscriptions.status'))
            ->assertOk()
            ->assertJsonPath('data.subscribed', true);
    }

    public function test_admin_can_store_push_subscription_when_logged_in(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)
            ->postJson(route('admin.push-subscriptions.store'), $this->validSubscription());

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('push_subscriptions', [
            'subscribable_id' => $admin->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint',
        ]);
    }

    public function test_store_push_subscription_is_idempotent(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');
        $payload = $this->validSubscription();

        $this->actingAs($admin)
            ->postJson(route('admin.push-subscriptions.store'), $payload)
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->actingAs($admin)
            ->postJson(route('admin.push-subscriptions.store'), $payload)
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame(1, PushSubscription::query()->where('endpoint', $payload['endpoint'])->count());
    }

    public function test_store_push_subscription_reassigns_endpoint_to_current_user(): void
    {
        $adminA = User::factory()->create(['email_verified_at' => now()]);
        $adminA->assignRole('Admin');
        $adminB = User::factory()->create(['email_verified_at' => now()]);
        $adminB->assignRole('Admin');
        $payload = $this->validSubscription();

        $this->actingAs($adminA)
            ->postJson(route('admin.push-subscriptions.store'), $payload)
            ->assertOk();

        $this->actingAs($adminB)
            ->postJson(route('admin.push-subscriptions.store'), $payload)
            ->assertOk();

        $this->assertSame(1, PushSubscription::query()->where('endpoint', $payload['endpoint'])->count());
        $this->assertDatabaseHas('push_subscriptions', [
            'endpoint' => $payload['endpoint'],
            'subscribable_id' => $adminB->id,
            'subscribable_type' => User::class,
        ]);
        $this->assertDatabaseMissing('push_subscriptions', [
            'endpoint' => $payload['endpoint'],
            'subscribable_id' => $adminA->id,
        ]);
    }

    public function test_logout_removes_push_subscriptions(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');
        $admin->updatePushSubscription(
            'https://fcm.googleapis.com/fcm/send/test-endpoint',
            'test-p256dh-key-value-here-abc',
            'test-auth-key-value-here-abc'
        );

        $this->actingAs($admin)->post(route('logout'));

        $this->assertDatabaseMissing('push_subscriptions', [
            'subscribable_id' => $admin->id,
        ]);
    }

    public function test_web_push_job_skips_user_without_staff_role(): void
    {
        Setting::set('notifications_web_push_enabled', '1');
        Setting::setEncrypted('webpush_public_key', 'BHtestpublickey');
        Setting::setEncrypted('webpush_private_key', 'testprivatekey');
        Setting::flushCache();

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->updatePushSubscription(
            'https://fcm.googleapis.com/fcm/send/test-endpoint',
            'test-p256dh-key-value-here-abc',
            'test-auth-key-value-here-abc'
        );

        $job = new WebPushStaffNotificationJob($user->id, [
            'title' => 'Test',
            'body' => 'Body',
            'url' => 'https://evil.example/phish',
        ]);

        $job->handle(app(\App\Services\StaffPushSubscriptionService::class));

        $this->assertDatabaseMissing('push_subscriptions', [
            'subscribable_id' => $user->id,
        ]);
    }

    public function test_staff_notification_url_sanitizer_blocks_external_urls(): void
    {
        $safe = StaffNotificationUrl::sanitize('https://evil.example/admin/orders/1');

        $this->assertStringContainsString('/admin/dashboard', $safe);
        $this->assertStringNotContainsString('evil.example', $safe);
    }

    public function test_staff_notification_url_to_app_path_uses_relative_admin_path(): void
    {
        $path = StaffNotificationUrl::toAppPath(route('admin.orders.show', ['order' => 42]));

        $this->assertSame('/admin/orders/42', $path);
        $this->assertStringNotContainsString('://', $path);
    }

    public function test_staff_notification_url_to_app_path_falls_back_to_dashboard(): void
    {
        $path = StaffNotificationUrl::toAppPath('https://evil.example/admin/orders/1');

        $this->assertSame('/admin/dashboard', $path);
    }

    /**
     * @return array<string, mixed>
     */
    private function validSubscription(): array
    {
        return [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint',
            'keys' => [
                'p256dh' => 'test-p256dh-key-value-here-abc',
                'auth' => 'test-auth-key-value-here-abc',
            ],
        ];
    }
}
