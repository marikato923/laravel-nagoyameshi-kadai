<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
   use RefreshDatabase;

   public function test_guest_cannot_access_admin_users_index(): void
    {
        $response = $this->get('/admin/users');

        $response->assertRedirect('/admin/login');
    }

    public function test_logged_in_user_cannot_access_users_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_access_users_index(): void
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get('/admin/users/');

        $response->assertStatus(200);
    }

    public function test_guest_cannot_access_user_detail_page(): void
    {
        $response = $this->get('/admin/users/1');

        $response->assertRedirect('/admin/login');
    }

    public function test_logged_in_user_cannot_access_user_detail_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get("/admin/users/{$user->id}");

        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_access_admin_user_show(): void
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $user = User::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get("/admin/users/{$user->id}");

        $response->assertStatus(200);
    }
}
