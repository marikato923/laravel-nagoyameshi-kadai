<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HomeTest extends TestCase
{
   use RefreshDatabase;

   public function test_guests_can_access_home_page()
   {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertViewIs('home');
   }

   public function test_logged_in_user_can_access_home_page()
   {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/');

    $response->assertStatus(200);
    $response->assertViewIs('home');
   }

   public function test_logged_in_admin_cannot_accesss_home_page()
   {
    $admin = Admin::factory()->create();

    $response = $this->actingAs($admin, 'admin')->get('/');

    $response->assertRedirect('/admin/home');
   }
}
