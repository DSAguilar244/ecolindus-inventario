<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMenuVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_admin_menu_link()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $res = $this->actingAs($admin)->get(route('dashboard'));
        $res->assertStatus(200);
        $res->assertSee('Usuarios');
    }

    public function test_non_admin_does_not_see_admin_menu_link()
    {
        $user = User::factory()->create();
        $res = $this->actingAs($user)->get(route('dashboard'));
        $res->assertStatus(200);
        $res->assertDontSee('Usuarios');
    }
}
