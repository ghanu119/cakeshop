<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class UserEmailSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_soft_deleted_user_releases_email_on_delete(): void
    {
        $user = User::factory()->create(['email' => 'reuse@example.com']);

        $user->delete();

        $user->refresh();
        $this->assertSoftDeleted($user);
        $this->assertSame('reuse@example.com-deleted-'.$user->id, $user->email);
    }

    public function test_admin_can_create_user_with_same_email_after_soft_delete(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        $deleted = User::factory()->create(['email' => 'reuse@example.com']);
        $deleted->delete();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Reused Email User',
            'email' => 'reuse@example.com',
            'password' => 'Str0n9@123',
            'password_confirmation' => 'Str0n9@123',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'reuse@example.com',
            'deleted_at' => null,
        ]);
    }

    public function test_registration_allows_same_email_after_soft_delete(): void
    {
        $deleted = User::factory()->create(['email' => 'reuse@example.com']);
        $deleted->delete();

        $component = Volt::test('pages.auth.register')
            ->set('name', 'New Register User')
            ->set('email', 'reuse@example.com')
            ->set('password', 'Str0n9@123')
            ->set('password_confirmation', 'Str0n9@123');

        $component->call('register');

        $component->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'reuse@example.com',
            'deleted_at' => null,
        ]);
    }
}
