<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_admin_category_index_page()
    {
        $response = $this->get('/admin/categories');
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_users_can_access_admin_category_index_page()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/categories');
        $response->assertStatus(200);
        $response->assertViewIs('admin.categories.index');
    }

    public function test_guests_cannot_store_category()
    {
        $response = $this->post('/admin/categories', ['name' => 'Test Category']);
        $response->assertRedirect('/admin/login');
    }

    public function test_regular_users_cannot_store_category()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/admin/categories', ['name' => 'Test Category']);
        $response->assertStatus(302);
    }

    public function test_admin_users_can_store_category()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->post('/admin/categories', ['name' => 'Test Category']);
        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('categories', ['name' => 'Test Category']);
    }

    public function test_guests_cannot_update_category()
    {
        $category = Category::factory()->create(['name' => 'Old Name']);

        $response = $this->put("/admin/categories/{$category->id}", ['name' => 'New Name']);
        $response->assertRedirect('/admin/login');
    }

    public function test_regular_users_cannot_update_category()
    {
        $category = Category::factory()->create(['name' => 'Old Name']);
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->put("/admin/categories/{$category->id}", ['name' => 'New Name']);
        $response->assertStatus(302);
    }

    public function test_admin_users_can_update_category()
    {
        $category = Category::factory()->create(['name' => 'Old Name']);
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin'); 

        $response = $this->put("/admin/categories/{$category->id}", ['name' => 'New Name']);
        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('categories', ['name' => 'New Name']);
    }

    public function test_guests_cannot_destroy_category()
    {
        $category = Category::factory()->create();

        $response = $this->delete("/admin/categories/{$category->id}");
        $response->assertRedirect('/admin/login');
    }

    public function test_regular_users_cannot_destroy_category()
    {
        $category = Category::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->delete("/admin/categories/{$category->id}");
        $response->assertStatus(302);
    }

    public function test_admin_users_can_destroy_category()
    {
        $category = Category::factory()->create();
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin'); 

        $response = $this->delete("/admin/categories/{$category->id}");
        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

}
