<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_users_and_update_role()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['role' => 'viewer']);

        $res = $this->actingAs($admin)->get(route('admin.users.index'));
        $res->assertStatus(200);
        $res->assertSee($user->email);

        $res = $this->actingAs($admin)->patch(route('admin.users.update', $user), ['role' => 'editor']);
        $res->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'editor']);
    }

    public function test_non_admin_cannot_manage_users()
    {
        $user = User::factory()->create();
        $res = $this->actingAs($user)->get(route('admin.users.index'));
        $res->assertStatus(403);
    }
}
