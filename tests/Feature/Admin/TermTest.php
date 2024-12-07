<?php

namespace Tests\Feature\tests\Feature\Admin;

use App\Models\Term;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TermTest extends TestCase
{
    use RefreshDatabase;

    // indexアクション
    public function test_non_logged_in_user_cannot_access_term_index()
    {
        $response = $this->get(route('admin.terms.index'));
        $response->assertRedirect('/admin/login');
    }

    public function test_logged_in_user_cannot_access_term_index()
    {
        $user = User::factory()->create(); 
        $response = $this->actingAs($user)->get(route('admin.terms.index'));
        $response->assertRedirect('/admin/login');
    }

    public function test_logged_in_admin_can_access_term_index()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin');
        $term = Term::factory()->create();
        $response = $this->get(route('admin.terms.index'));
        $response->assertStatus(200); 
    }

    // editアクション
    public function test_non_logged_in_user_cannot_access_term_edit()
    {
        $term = Term::factory()->create();
        $response = $this->get(route('admin.terms.edit', $term));
        $response->assertRedirect('/admin/login'); 
    }

    public function test_logged_in_user_cannot_access_term_edit()
    {
        $user = User::factory()->create();
        $term = Term::factory()->create();
        $response = $this->actingAs($user)->get(route('admin.terms.edit', $term));
        $response->assertRedirect('/admin/login'); 
    }

    public function test_logged_in_admin_can_access_term_edit()
    {
        $admin = Admin::factory()->create();
        $term = Term::factory()->create();
        $response = $this->actingAs($admin, 'admin')->get(route('admin.terms.edit', $term));
        $response->assertStatus(200); 
    }

    // updateアクション
    public function test_non_logged_in_user_cannot_update_term()
    {
        $term = Term::factory()->create();
        $response = $this->put(route('admin.terms.update', $term), [
            'content' => '新しい概要'
        ]);
        $response->assertRedirect('/admin/login');
    }

    public function test_logged_in_user_cannot_update_term()
    {
        $user = User::factory()->create();
        $term = Term::factory()->create();
        $response = $this->actingAs($user)->put(route('admin.terms.update', $term), [
            'content' => '新しい概要'
        ]);
        $response->assertRedirect('/admin/login'); 
    }

    public function test_logged_in_admin_can_update_term()
    {
        $admin = Admin::factory()->create();
        $term = Term::factory()->create();
        $response = $this->actingAs($admin, 'admin')->put(route('admin.terms.update', $term), [
            'content' => '新しい概要'
        ]);
        $response->assertRedirect(route('admin.terms.index')); 
        $this->assertDatabaseHas('terms', ['content' => '新しい概要']);
    }
}
