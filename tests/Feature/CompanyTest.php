<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_user_side_company_index()
    {
        $company = Company::factory()->create();

        $response = $this->get(route('company.index'));

        $response->assertStatus(200);
        $response->assertViewIs('company.index');
    }

    public function test_authenticated_user_can_access_user_side_company_index()
    {
        $company = Company::factory()->create();

        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('company.index'));

        $response->assertStatus(200);
        $response->assertViewIs('company.index');
    }

    public function test_authenticated_admin_cannnot_access_user_side_company_index()
    {
        $company = Company::factory()->create();
        
        $admin = Admin::factory()->create();
        $response = $this->actingAs($admin, 'admin')->get(route('company.index'));

        $response->assertRedirect(route('admin.home'));
    }
}

