<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_user_index()
    {
        $response = $this->get(route('user.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_user_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('user.index'));
        $response->assertStatus(200);
        $response->assertViewIs('user.index');
    }

    public function test_admin_cannot_access_user_index()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin)->get(route('user.index'));
        $response->assertRedirect(route('admin.home'));
    }

    public function test_guest_cannot_access_user_edit()
    {
        $user = User::factory()->create();

        $response = $this->get(route('user.edit', ['user' => $user->id]));  // ユーザーIDを明示的に渡す
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_cannot_access_other_user_edit()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user)->get(route('user.edit', ['user' => $otherUser->id]));  // 他のユーザーIDを渡す
        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('error_message', '不正なアクセスです。');
    }

    public function test_authenticated_user_can_access_own_user_edit()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('user.edit', ['user' => $user->id]));  // 自分のユーザーIDを渡す
        $response->assertStatus(200);
        $response->assertViewIs('user.edit');  // ビュー名を正しく指定
    }

    public function test_admin_cannot_access_user_edit()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('user.edit', ['user' => $user->id]));  // ユーザーIDを渡す
        $response->assertRedirect(route('admin.home')); 
    }

    public function test_guest_cannot_update_user_information()
    {
        $user = User::factory()->create();

        $response = $this->patch(route('user.update', ['user' => $user->id]), [
            'name' => 'New Name',
            'kana' => 'カナカナ',      
            'email' => 'newemail@example.com', 
            'postal_code' => '1234567', 
            'address' => '新しい住所',    
            'phone_number' => '08012345678', 
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_cannot_update_other_user_information()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user)->patch(route('user.update', ['user' => $otherUser->id]), [
            'name' => 'New Name',
            'kana' => 'カナカナ',      
            'email' => 'newemail@example.com', 
            'postal_code' => '1234567', 
            'address' => '新しい住所',    
            'phone_number' => '08012345678', 
        ]);

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('error_message', '不正なアクセスです。');
    }

    public function test_authenticated_user_can_update_own_user_edit()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch(route('user.update', ['user' => $user->id]), [
            'name' => 'New Name',
            'kana' => 'カナカナ',      
            'email' => 'newemail@example.com', 
            'postal_code' => '1234567', 
            'address' => '新しい住所',    
            'phone_number' => '08012345678', 
        ]);
        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('flash_message', '会員情報を編集しました。');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name']);
    }

    public function test_admin_cannot_update_user_information()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->patch(route('user.update', ['user' => $user->id]), [
            'name' => 'New Name',
            'kana' => 'カナカナ',      
            'email' => 'newemail@example.com', 
            'postal_code' => '1234567', 
            'address' => '新しい住所',    
            'phone_number' => '08012345678', 
        ]);

        $response->assertRedirect('/admin/home'); 
    }
}