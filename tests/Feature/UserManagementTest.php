<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the admin "Users" screen (App\Http\Controllers\Admin\UserController)
 * — every registered user is an admin (see User::isAdmin()), so the
 * interesting behaviour is UserPolicy::delete()'s two safety rails: an
 * admin can't delete their own account or the last remaining one.
 */
class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_user(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Jane Staff',
                'email' => 'jane@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['name' => 'Jane Staff', 'email' => 'jane@example.com']);
    }

    public function test_creating_a_user_requires_a_unique_email(): void
    {
        $admin = User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Duplicate',
                'email' => 'taken@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_admin_can_update_a_user_without_changing_the_password(): void
    {
        $admin = User::factory()->create();
        $target = User::factory()->create(['name' => 'Old Name']);
        $originalPassword = $target->password;

        $this->actingAs($admin)
            ->put(route('admin.users.update', $target), [
                'name' => 'New Name',
                'email' => $target->email,
            ])
            ->assertRedirect(route('admin.users.index'));

        $target->refresh();
        $this->assertSame('New Name', $target->name);
        $this->assertSame($originalPassword, $target->password);
    }

    public function test_admin_can_change_a_users_password(): void
    {
        $admin = User::factory()->create();
        $target = User::factory()->create();
        $originalPassword = $target->password;

        $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $this->assertNotSame($originalPassword, $target->fresh()->password);
    }

    public function test_an_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->create();
        User::factory()->create(); // a second account, so "last admin" isn't the blocker here

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_the_last_remaining_user_cannot_be_deleted(): void
    {
        $onlyUser = User::factory()->create();

        $this->actingAs($onlyUser)
            ->delete(route('admin.users.destroy', $onlyUser))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $onlyUser->id]);
    }

    public function test_admin_can_delete_another_user_when_more_than_one_exists(): void
    {
        $admin = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $target))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }
}
