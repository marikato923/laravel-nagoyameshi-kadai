<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_side_home()
    {
        $response = $this->get(route('admin.home'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_users_cannot_access_admin_side_home()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.home'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_authenticated_admin_can_acccess_admin_side_home()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.home'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.home');
    }
}
