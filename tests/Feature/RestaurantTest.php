<?php

namespace Tests\Feature;

use App\Models\User;
use App\models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use app\http\Controllers\RestaurantController;

class RestaurantTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_user_restaurant_index()
    {
        $response = $this->get(route('restaurants.index'));
        $response->assertStatus(200);
        $response->assertViewIs('restaurants.index');
    }

    public function test_authenticated_user_can_access_user_index()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get(route('restaurants.index'));
        $response->assertStatus(200);
        $response->assertViewIs('restaurants.index');
    }

    public function test_authenticates_admin_cannot_access_user_restaurant_index()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('restaurants.index'));
        $response->assertRedirect(route('admin.home'));
    }
}
