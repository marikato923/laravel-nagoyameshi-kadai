<?php

namespace Tests\Feature;

use App\Models\Term;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TermTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_user_side_terms_index()
    {
        $company = Term::factory()->create();

        $response = $this->get(route('terms.index'));

        $response->assertStatus(200);
        $response->assertViewIs('terms.index');
    }

    public function test_authenticated_user_can_access_user_side_terms_index()
    {
        $company = Term::factory()->create();

        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('terms.index'));

        $response->assertStatus(200);
        $response->assertViewIs('terms.index');
    }

    public function test_authenticated_admin_cannnot_access_user_side_terms_index()
    {
        $company = Term::factory()->create();
        
        $admin = Admin::factory()->create();
        $response = $this->actingAs($admin, 'admin')->get(route('terms.index'));

        $response->assertRedirect(route('admin.home'));
    }
}


