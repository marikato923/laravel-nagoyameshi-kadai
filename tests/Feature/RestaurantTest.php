<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\Admin;
use App\Models\User;
use App\Models\RegularHoliday;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RestaurantTest extends TestCase
{
    use RefreshDatabase;

    // indexアクション（店舗一覧ページ）
    public function test_guest_cannot_access_admin_index()
    {
        $response = $this->get('/admin/restaurants');
        $response->assertRedirect('/admin/login'); 
    }

    public function test_user_cannot_access_admin_index()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/admin/restaurants');
        $response->assertRedirect('/admin/login'); 
    }

    public function test_admin_can_access_admin_index()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/restaurants');
        $response->assertStatus(200); 
    }

    // showアクション（店舗詳細ページ）
    public function test_guest_cannot_access_admin_show()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->get("/admin/restaurants/{$restaurant->id}");
        $response->assertRedirect('/admin/login'); 
    }

    public function test_user_cannot_access_admin_show()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($user);

        $response = $this->get("/admin/restaurants/{$restaurant->id}");
        $response->assertRedirect('/admin/login'); 
    }

    public function test_admin_can_access_admin_show()
    {
        $admin = Admin::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($admin, 'admin'); 

        $response = $this->get("/admin/restaurants/{$restaurant->id}");
        $response->assertStatus(200); 
    }

    // createアクション（店舗登録ページ）
    public function test_guest_cannot_access_admin_create()
    {
        $response = $this->get('/admin/restaurants/create');
        $response->assertRedirect('/admin/login'); 
    }

    public function test_user_cannot_access_admin_create()
    {
        $user = User::factory()->create();
        $this->actingAs($user); 

        $response = $this->get('/admin/restaurants/create');
        $response->assertRedirect('/admin/login'); 
    }

    public function test_admin_can_access_admin_create()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin'); 

        $response = $this->get('/admin/restaurants/create');
        $response->assertStatus(200);
    }

    // storeアクション（店舗登録機能）
    public function test_guest_cannot_store_restaurant()
    {
        $categories = Category::factory()->count(3)->create();

        $restaurantData = Restaurant::factory()->make()->toArray();
        $restaurantData['category_ids'] = $categories->pluck('id')->toArray();
        $restaurantData['regular_holiday_ids'] = RegularHoliday::factory()->count(1)->create()->pluck('id')->toArray();

        $response = $this->post('/admin/restaurants', $restaurantData);
        $response->assertRedirect('/admin/login'); 
    }

    public function test_user_cannot_store_restaurant()
    {
        $categories = Category::factory()->count(3)->create();

        $user = User::factory()->create();
        $this->actingAs($user);

        $restaurantData = Restaurant::factory()->make()->toArray();
        $restaurantData['category_ids'] = $categories->pluck('id')->toArray();
        $restaurantData['regular_holiday_ids'] = RegularHoliday::factory()->count(1)->create()->pluck('id')->toArray();

        $response = $this->post('/admin/restaurants', $restaurantData);
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_store_restaurant()
    {
        $categories = Category::factory()->count(3)->create();

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $restaurantData = Restaurant::factory()->make()->toArray();
        $restaurantData['category_ids'] = $categories->pluck('id')->toArray();
        $restaurantData['regular_holiday_ids'] = RegularHoliday::factory()->count(1)->create()->pluck('id')->toArray();

        $response = $this->post('/admin/restaurants', $restaurantData);
        $response->assertRedirect('/admin/restaurants'); 

        $this->assertDatabaseHas('restaurants', [
            'name' => $restaurantData['name'],
            'description' => $restaurantData['description'],
            'lowest_price' => $restaurantData['lowest_price'],
            'highest_price' => $restaurantData['highest_price'],
            'postal_code' => $restaurantData['postal_code'],
            'address' => $restaurantData['address'],
            'opening_time' => $restaurantData['opening_time'],
            'closing_time' => $restaurantData['closing_time'],
            'seating_capacity' => $restaurantData['seating_capacity'],
        ]);

        $restaurant = Restaurant::create($restaurantData);

        $restaurant->categories()->attach($categories->pluck('id')->toArray());

        foreach($categories as $category) {
            $this->assertDatabaseHas('category_restaurant', [
                'restaurant_id' => $restaurant->id,
                'category_id' => $category->id
            ]);
        }
    }

    // editアクション（店舗編集ページ）
    public function test_guest_cannot_access_admin_edit()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->get("/admin/restaurants/{$restaurant->id}/edit");
        $response->assertRedirect('/admin/login');
    }

    public function test_user_cannot_access_admin_edit()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($user);

        $response = $this->get("/admin/restaurants/{$restaurant->id}/edit");
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_access_admin_edit()
    {
        $admin = Admin::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($admin, 'admin');

        $response = $this->get("/admin/restaurants/{$restaurant->id}/edit");
        $response->assertStatus(200);
    }

    // updateアクション（店舗更新機能）
    public function test_guest_cannot_update_restaurant()
    {
        $categories = Category::factory()->count(3)->create();

        $restaurant = Restaurant::factory()->create();
        $restaurantData = Restaurant::factory()->make()->toArray();
        $restaurantData['category_ids'] = $categories->pluck('id')->toArray();
        $restaurantData['regular_holiday_ids'] = RegularHoliday::factory()->count(1)->create()->pluck('id')->toArray();

        $response = $this->put("/admin/restaurants/{$restaurant->id}", $restaurantData);
        $response->assertRedirect('/admin/login');
    }

    public function test_user_cannot_update_restaurant()
    {
        $categories = Category::factory()->count(3)->create();
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user);

        $restaurantData = Restaurant::factory()->make()->toArray();
        $restaurantData['category_ids'] = $categories->pluck('id')->toArray();
        $restaurantData['regular_holiday_ids'] = RegularHoliday::factory()->count(1)->create()->pluck('id')->toArray();

        $response = $this->put("/admin/restaurants/{$restaurant->id}", $restaurantData);
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_update_restaurant()
    {
        $categories = Category::factory()->count(3)->create();
        $restaurant = Restaurant::factory()->create();
        $restaurantData['regular_holiday_ids'] = RegularHoliday::factory()->count(1)->create()->pluck('id')->toArray();

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $restaurantData = Restaurant::factory()->make()->toArray();
        $restaurantData['category_ids'] = $categories->pluck('id')->toArray();

        $response = $this->put("/admin/restaurants/{$restaurant->id}", $restaurantData);
        $response->assertRedirect("/admin/restaurants/{$restaurant->id}");

        unset($restaurantData['category_ids']);

        $this->assertDatabaseHas('restaurants', $restaurantData);

        foreach ($categories as $category) {
            $this->assertDatabaseHas('category_restaurant', [
                'restaurant_id' => $restaurant->id,
                'category_id' => $category->id
            ]);
        }
    }

    // destroyアクション（店舗削除機能）
    public function test_guest_cannot_delete_restaurant()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->delete("/admin/restaurants/{$restaurant->id}");
        $response->assertRedirect('/admin/login');
    }

    public function test_user_cannot_delete_restaurant()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($user);

        $response = $this->delete("/admin/restaurants/{$restaurant->id}");
        $response->assertRedirect('/admin/login'); 
    }

    public function test_admin_can_delete_restaurant()
    {
        $admin = Admin::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($admin, 'admin'); 

        $response = $this->delete("/admin/restaurants/{$restaurant->id}");
        $response->assertRedirect('/admin/restaurants'); 
        $this->assertDatabaseMissing('restaurants', ['id' => $restaurant->id]); 
    }
}
