<?php

namespace Tests\Feature;

use App\Models\User;
use App\models\Admin;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_favorites_index()
    {
        $response = $this->get(route('favorites.index'));
        $response->assertRedirect('/login');
    }

    public function test_free_user_cannot_access_favorites_index()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('favorites.index'));
        $response->assertRedirect(route('subscription.create'));
    }

    public function test_paid_user_can_access_favorites_index()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QUlQtEEOG402gkC2UDm94kG')->create('pm_card_visa');

        $response = $this->actingAs($user)->get(route('favorites.index'));
        $response->assertStatus(200);
    }

    public function test_admin_cannot_access_favorites_index()
    {   
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get(route('favorites.index'));
        $response->assertRedirect(route('admin.home')); 
    }

    public function test_guest_cannot_add_to_favorites()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->post(route('favorites.store', $restaurant));
        $response->assertRedirect('/login');
    }

    public function test_free_user_cannot_add_to_favorites()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($user)->post(route('favorites.store', $restaurant));
        $response->assertRedirect(route('subscription.create'));
    }

    public function test_paid_user_can_add_to_favorites()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QUlQtEEOG402gkC2UDm94kG')->create('pm_card_visa');
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($user)->post(route('favorites.store', $restaurant));
        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('restaurant_user', [
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
        ]);
    }

    public function test_admin_cannot_add_to_favorites()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');
        $restaurant = Restaurant::factory()->create();

        $response = $this->post(route('favorites.store', $restaurant));
        $response->assertRedirect(route('admin.home'));
    }

    public function test_guest_cannot_remove_favorites()
{
    $restaurant = Restaurant::factory()->create();

    $response = $this->delete(route('favorites.destroy', $restaurant));
    $response->assertRedirect('/login');
}

public function test_free_user_cannot_remove_favorites()
{
    $user = User::factory()->create();
    $restaurant = Restaurant::factory()->create();

    $response = $this->actingAs($user)->delete(route('favorites.destroy', $restaurant));
    $response->assertRedirect(route('subscription.create'));
}

    public function test_paid_user_can_remove_favorites()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QUlQtEEOG402gkC2UDm94kG')->create('pm_card_visa');
        $restaurant = Restaurant::factory()->create();

        $user->favorite_restaurants()->attach($restaurant);

        $response = $this->actingAs($user)->delete(route('favorites.destroy', $restaurant));
        $response->assertStatus(302);

        $this->assertDatabaseMissing('restaurant_user', [
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
        ]);
    }

    public function test_admin_cannot_remove_favorites()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin, 'admin'); 
        $restaurant = Restaurant::factory()->create();

        $response = $this->delete(route('favorites.destroy', $restaurant));
        $response->assertRedirect(route('admin.home')); 
    }
}
