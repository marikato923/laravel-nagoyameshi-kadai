<?php

namespace Tests\Feature\tests\Feature\Admin;

use App\Models\Company;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    // indexアクション
    public function test_guest_user_cannot_access_company_index()
    {
        $response = $this->get(route('admin.company.index'));
        $response->assertRedirect('/admin/login');
    }

    public function test_authenticated_user_cannot_access_company_index()
    {
        $user = User::factory()->create(); 
        $response = $this->actingAs($user)->get(route('admin.company.index'));
        $response->assertRedirect('/admin/login');
    }

    public function test_authenticated_admin_can_access_company_index()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin');
        $company = Company::factory()->create();
        $response = $this->get(route('admin.company.index'));
        $response->assertStatus(200); 
    }

    // editアクション
    public function test_guest_cannot_access_company_edit()
    {
        $company = Company::factory()->create();
        $response = $this->get(route('admin.company.edit', $company));
        $response->assertRedirect('/admin/login'); 
    }

    public function test_authenticated_user_cannot_access_company_edit()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $response = $this->actingAs($user)->get(route('admin.company.edit', $company));
        $response->assertRedirect('/admin/login'); 
    }

    public function test_authenticated_admin_can_access_company_edit()
    {
        $admin = Admin::factory()->create();
        $company = Company::factory()->create();
        $response = $this->actingAs($admin, 'admin')->get(route('admin.company.edit', $company));
        $response->assertStatus(200); 
    }

    // updateアクション
    public function test_guest_in_user_cannot_update_company()
    {
        $company = Company::factory()->create();
        $response = $this->patch(route('admin.company.update', $company), [
            'name' => '新しい会社名',
            'postal_code' => '1111111',
            'address' => '新しい住所',
            'representative' => '新しい代表者',
            'establishment_date' => '2020-01-01',
            'capital' => '1000000',
            'business' => '新しい事業内容',
            'number_of_employees' => 100,
        ]);
        $response->assertRedirect('/admin/login');
    }

    public function test_authenticated_user_cannot_update_company()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $response = $this->actingAs($user)->patch(route('admin.company.update', $company), [
            'name' => '新しい会社名',
            'postal_code' => '1111111',
            'address' => '新しい住所',
            'representative' => '新しい代表者',
            'establishment_date' => '2020-01-01',
            'capital' => '1000000',
            'business' => '新しい事業内容',
            'number_of_employees' => 100,
        ]);
        $response->assertRedirect('/admin/login'); 
    }

    public function test_authenticated_admin_can_update_company()
    {
        $admin = Admin::factory()->create();
        $company = Company::factory()->create();
        $response = $this->actingAs($admin, 'admin')->patch(route('admin.company.update', $company), [
            'name' => '新しい会社名',
            'postal_code' => '1111111',
            'address' => '新しい住所',
            'representative' => '新しい代表者',
            'establishment_date' => '2020-01-01',
            'capital' => '1000000',
            'business' => '新しい事業内容',
            'number_of_employees' => 100,
        ]);
        $response->assertRedirect(route('admin.company.index')); 
        $this->assertDatabaseHas('companies', ['name' => '新しい会社名']);
    }
}
