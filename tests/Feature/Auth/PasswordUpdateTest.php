<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_still_update_password_via_breeze_profile_component(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('Str0n9@123'),
        ]);

        $this->actingAs($user);

        $component = Volt::test('profile.update-password-form')
            ->set('current_password', 'Str0n9@123')
            ->set('password', 'NewStr0n9@456')
            ->set('password_confirmation', 'NewStr0n9@456');

        $component->call('updatePassword');

        $component->assertHasNoErrors();
    }
}
