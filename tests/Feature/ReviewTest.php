<?php

namespace Tests\Feature;

use App\Models\User;
use App\models\Admin;
use App\Models\Restaurant;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use SebastianBergmann\GlobalState\Restorer;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_user_review_index_page()
    {
        $restaurant = Restaurant::factory()->create();
          
        $response = $this->get(route('restaurants.reviews.index', $restaurant));
        $response->assertRedirect('/login');
    }

    public function test_authenticated_free_user_can_access_user_review_index_page()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
          
        $response = $this->actingAs($user)
            ->get(route('restaurants.reviews.index', $restaurant));
        $response->assertStatus(200);
        $response->assertViewIs('reviews.index');
    }

    public function test_authenticated_paid_user_can_access_user_review_index_page()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QUlQtEEOG402gkC2UDm94kG')->create('pm_card_visa');

        $restaurant = Restaurant::factory()->create();
      
        $response = $this->actingAs($user)
            ->get(route('restaurants.reviews.index', $restaurant));

        $response->assertStatus(200);
        $response->assertViewIs('reviews.index');
    }

    public function test_authenticated_admin_cannot_access_user_review_index_page()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $restaurant = Restaurant::factory()->create();

        $response=$this->get(route('restaurants.reviews.index', $restaurant));
        $response->assertRedirect(route('admin.home'));
    }

    public function test_guest_cannot_post_review()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->post(route('restaurants.reviews.store', $restaurant), [
            'content' => '素晴らしいレストランです！',
        ]);
        $response->assertRedirect('/login');
    }

    public function test_free_user_cannot_post_review()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($user);
        $response = $this->post(route('restaurants.reviews.store', $restaurant), [
            'content' => '素晴らしいレストランです！',
        ]);
        $response->assertRedirect();
    }

    public function test_paid_user_can_post_review()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QUlQtEEOG402gkC2UDm94kG')->create('pm_card_visa');
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($user);
        $response = $this->post(route('restaurants.reviews.store', $restaurant), [
            'score' => '1',
            'content' => '素晴らしいレストランです！',
        ]);
        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'score' => '1',
            'content' => '素晴らしいレストランです！',
        ]);
    }

    public function test_admin_cannot_post_review()
    {
        $admin = Admin::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($admin, 'admin');
        $response = $this->post(route('restaurants.reviews.store', $restaurant), [
            'content' => '管理者として投稿できません。',
        ]);
        $response->assertRedirect(route('admin.home'));
    }

    public function test_unauthenticated_user_cannot_access_review_create_page()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->get(route('restaurants.reviews.create', $restaurant));

        $response->assertRedirect(route('login'));
    }

    public function test_free_user_cannot_access_review_create_page()
    {
        $user = User::factory()->create(); 
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('restaurants.reviews.create', $restaurant));

        $response->assertRedirect();
    }

    public function test_paid_user_can_access_review_create_page()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QUlQtEEOG402gkC2UDm94kG')->create('pm_card_visa');

        $restaurant = Restaurant::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('restaurants.reviews.create', $restaurant));

        $response->assertStatus(200);
        $response->assertViewIs('reviews.create');
    }

    public function test_admin_cannot_access_review_create_page()
    {
        $admin = Admin::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('restaurants.reviews.create', $restaurant));

        $response->assertRedirect(route('admin.home'));
    }

    public function test_unauthenticated_user_cannot_access_review_edit_page()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
        ]);

        $response = $this->get(route('restaurants.reviews.edit', [$restaurant, $review]));
        $response->assertRedirect(route('login'));
    }

    public function test_free_user_cannot_access_review_edit_page()
    {
        $user = User::factory()->create(); 
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);
        $response = $this->get(route('restaurants.reviews.edit', [$restaurant, $review]));

        $response->assertRedirect();
    }

    public function test_paid_user_can_access_own_review_edit_page()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QUlQtEEOG402gkC2UDm94kG')->create('pm_card_visa');
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);
        $response = $this->get(route('restaurants.reviews.edit', [$restaurant, $review]));

        $response->assertStatus(200);
        $response->assertViewIs('reviews.edit');
    }

    public function test_paid_user_cannot_access_others_review_edit_page()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QUlQtEEOG402gkC2UDm94kG')->create('pm_card_visa');
        $restaurant = Restaurant::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $otherUser->id,
        ]);

        $this->actingAs($user);
        $response = $this->get(route('restaurants.reviews.edit', [$restaurant, $review]));

        $response->assertRedirect();
    }

    public function test_admin_cannot_access_review_edit_page()
    {
        $admin = Admin::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,  
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('restaurants.reviews.edit', [$restaurant, $review]));
        $response->assertRedirect(route('admin.home'));
    }

    public function test_unauthenticated_user_cannot_update_review()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
        ]);

        $response = $this->patch(route('restaurants.reviews.update', [$restaurant, $review]));
        $response->assertRedirect(route('login'));
    }

    public function test_free_user_cannot_update_review()
    {
        $freeUser = User::factory()->create();
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
            ]);

        $this->actingAs($freeUser);

        $response = $this->patch(route('restaurants.reviews.update', [$restaurant, $review]), [
            'score' => 4,
            'content' => 'Updated review content',
        ]);
        $response->assertRedirect(); 
    }

    public function test_paid_user_cannot_update_others_review()
    {
        $user = User::factory()->create(); 
        $user->newSubscription('premium_plan', 'price_1QUlQtEEOG402gkC2UDm94kG')->create('pm_card_visa');
        $anotherUser = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $anotherUser->id,
        ]);

        $this->actingAs($anotherUser);

        $response = $this->patch(route('restaurants.reviews.update', [$restaurant, $review]), [
            'score' => 4,
            'content' => 'Updated review content',
        ]);
        $response->assertRedirect();
    }

    public function test_paid_user_can_update_own_review()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QUlQtEEOG402gkC2UDm94kG')->create('pm_card_visa');
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        $response = $this->patch(route('restaurants.reviews.update', [$restaurant, $review]), [
            'score' => 4,
            'content' => 'Updated review content',
        ]);
        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    public function test_admin_cannot_update_review()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->patch(route('restaurants.reviews.update', [$restaurant, $review]), [
            'score' => 4,
            'content' => 'Updated review content',
        ]);
        $response->assertRedirect(route('admin.home'));
    }

    public function test_unauthenticated_user_cannot_delete_review()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $review = Review::factory()->create([
        'restaurant_id' => $restaurant->id,
        'user_id' => $user->id,
        ]);

        $response = $this->delete(route('restaurants.reviews.destroy', [$restaurant, $review]));
        $response->assertRedirect(route('login'));
    }

    public function test_free_user_cannot_delete_review()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('restaurants.reviews.destroy', [$restaurant, $review]));
        $response->assertRedirect(); 
    }

    public function test_paid_user_cannot_delete_others_review()
    {
        $user = User::factory()->create(); 
        $user->newSubscription('premium_plan', 'price_1QUlQtEEOG402gkC2UDm94kG')->create('pm_card_visa');
        $anotherUser = User::factory()->create(); 
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $anotherUser->id,
        ]);

        $this->actingAs($anotherUser);

        $response = $this->delete(route('restaurants.reviews.destroy', [$restaurant, $review]));
        $response->assertRedirect();
    }

    public function test_paid_user_can_delete_own_review()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QUlQtEEOG402gkC2UDm94kG')->create('pm_card_visa');
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('restaurants.reviews.destroy', [$restaurant, $review]));
        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    public function test_admin_cannot_delete_review()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->delete(route('restaurants.reviews.destroy', [$restaurant, $review]));
        $response->assertRedirect(route('admin.home')); 
    }
}