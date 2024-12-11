<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Fscades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\Stripe;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_subscription_create_page()
    {
        $response = $this->get('/subscription/create');
        $response->assertRedirect('/login');
    }

    public function test_free_user_can_access_subscription_create_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/subscription/create');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_subscription_create_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/subscription/create');
        $response->assertStatus(200);
    }

    public function test_authenticated_admin_cannot_access_subscription_create_page()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/subscription/create');
        $response->assertRedirect(route('admin.home'));
    }

    public function test_guests_cannot_subscribe_to_the_remium_plan()
    {
        $response = $this->post('/subscription/store', [
            'paymentMethodId' => 'pm_card_visa'
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_subscribe_to_the_premium_plan()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
    
        $paymentMethodId = 'pm_card_visa';
    
        $response = $this->post('/subscription/store', [
            'paymentMethodId' => $paymentMethodId,
        ]);
        
        $response->assertRedirect(route('home'));
    
        $this->assertTrue($user->fresh()->subscribed('premium_plan'));
    }

    public function test_paid_user_cannot_subscribe_to_the_premium_plan()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QUlQtEEOG402gkC2UDm94kG')->create('pm_card_visa');
        $this->actingAs($user);

        $response = $this->post('/subscription/store', [
            'paymentMethodId' => 'pm_card_visa'
        ]);

        $response->assertRedirect(route('subscription.edit'));
        $this->assertTrue($user->fresh()->subscribed('premium_plan'));
    }

    public function an_admin_cannot_subscribe_to_the_premium_plan()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->post('/subscription/store', [
            'paymentMethodId' => 'pm_card_visa'
        ]);

        $response->assertRedirect(route('admin.home')); 
        $this->assertFalse($admin->fresh()->subscribed('premium_plan'));
    }

    public function test_guest_cannot_access_subscription_edit_page()
    {
        $response = $this->get('/subscription/edit');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_free_user_cannot_access_subscription_edit_page()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/subscription/edit');
        $response->assertRedirect('/subscription/create');
    }

    public function test_authenticated_paid_user_can_access_subscription_edit_page()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QUlQtEEOG402gkC2UDm94kG')->create('pm_card_visa');
        $response = $this->actingAs($user)->get('/subscription/edit');

        $response->assertStatus(200);
        $response->assertViewIs('subscription.edit');
    }

    public function test_authenticated_admin_cannot_access_subscription_edit_page()
    {
        $admin =  $admin = Admin::factory()->create();
        $response = $this->actingAs($admin, 'admin')->get('/subscription/edit');
        
        $response->assertRedirect(route('admin.home'));
    }

    public function test_guest_cannot_update_payment_method()
    {
        $response = $this->patch('/subscription/update', [
            'paymentMethodId' => 'pm_card_mastercard',
        ]);

        $response->assertRedirect('/login');
    }
    public function test_free_user_cannot_update_payment_method()
    {
        $user = User::factory()->create();  
        $this->actingAs($user);

        $response = $this->patch('/subscription/update', [
            'paymentMethodId' => 'pm_card_mastercard',
        ]);

        $response->assertRedirect('/subscription/create');
    }

    public function test_paid_user_can_update_payment_method()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QUlQtEEOG402gkC2UDm94kG')->create('pm_card_visa');
        $this->actingAs($user);

        $original_payment_method_id = $user->defaultPaymentMethod()->id;

        $response = $this->patch('/subscription/update', [
            'paymentMethodId' => 'pm_card_mastercard',
        ]);

        $response->assertRedirect(route('home'));
        $user->refresh();

        $this->assertNotEquals($original_payment_method_id, $user->defaultPaymentMethod()->id);
    }

    public function test_admin_cannot_update_payment_method()
    {
        $admin = admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->patch('/subscription/update', [
            'paymentMethodId' => 'pm_card_mastercard',
        ]);

        $response->assertRedirect(route('admin.home'));
    }

    public function test_guest_cannot_access_cancel_page()
    {
        $response = $this->get('/subscription/cancel');
        $response->assertRedirect('/login');
    }

    public function test_free_user_cannot_access_cancel_page()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/subscription/cancel');
        $response->assertRedirect('/subscription/create');
    }

    public function test_paid_user_can_access_cancel_page()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QUlQtEEOG402gkC2UDm94kG')->create('pm_card_visa');
        $response = $this->actingAs($user)->get('/subscription/cancel');

        $response->assertStatus(200);
        $response->assertViewIs('subscription.cancel');
    }

    public function test_admin_cannot_access_cancel_page()
    {
        $admin = User::factory()->create();
        $response = $this->actingAs($admin, 'admin')->get('/subscription/cancel');

        $response->assertRedirect(route('admin.home'));
    }

    public function test_guest_cannot_cancel_subscription()
    {
        $response = $this->delete('/subscription');
        $response->assertRedirect('/login');
    }

    public function test_free_user_cannot_cancel_subscription()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->delete('/subscription');
        $response->assertRedirect('/subscription/create');
    }

    public function test_paid_user_can_cancel_subscription()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QUlQtEEOG402gkC2UDm94kG')->create('pm_card_visa');

        $response = $this->actingAs($user)->delete('/subscription');
        $response->assertRedirect(route('home'));
        $this->assertFalse($user->fresh()->subscribed('premium_plan'));
    }

    public function test_admin_cannot_cancel_subscription()
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin, 'admin')->delete('/subscription');
        $response->assertRedirect(route('admin.home'));
    }
}

