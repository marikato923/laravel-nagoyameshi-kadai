<?php

namespace Tests\Feature;

use App\Models\User;
use App\models\Admin;
use App\Models\Restaurant;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_reservation_index()
    {
        $response = $this->get(route('reservations.index'));
        $response->assertRedirect('login');
    }

    public function test_authenticated_free_user_cannot_access_reservation_index()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get(route('reservations.index'));
        $response->assertRedirect(route('subscription.create'));
    }

    public function test_authenticated_paid_user_can_access_reservation_index()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QUlQtEEOG402gkC2UDm94kG')->create('pm_card_visa');

        $response = $this->actingAs($user)->get(route('reservations.index'));
        $response->assertStatus(200);
        $response->assertViewIs('reservations.index');
    }

    public function test_authenticated_admin_cannot_access_reservation_index()
    {   $admin = Admin::factory()->create();
        
        $response = $this->actingAs($admin, 'admin')->get(route('reservations.index'));
        $response->assertRedirect(route('admin.home'));
    }

    public function test_guest_cannot_access_reservation_create()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->get(route('restaurants.reservations.create', $restaurant));
        $response->assertRedirect('login');
    }

    public function test_authenticated_free_user_cannot_access_reservation_create()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        
        $response = $this->actingAs($user)->get(route('restaurants.reservations.create', $restaurant));
        $response->assertRedirect(route('subscription.create'));
    }

    public function test_authenticated_paid_user_can_access_reservation_create()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QUlQtEEOG402gkC2UDm94kG')->create('pm_card_visa');
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($user)->get(route('restaurants.reservations.create', $restaurant));
        $response->assertStatus(200);
        $response->assertViewIs('reservations.create');
    }

    public function test_authenticated_admin_cannot_access_reservation_create()
    {   
        $admin = Admin::factory()->create();
        $restaurant = Restaurant::factory()->create();
        
        $response = $this->actingAs($admin, 'admin')->get(route('restaurants.reservations.create', $restaurant));
        $response->assertRedirect(route('admin.home'));
    }

    public function test_guest_cannot_reserve_a_restaurant()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $reservation = Reservation::factory()->create([
            'reserved_datetime' => now()->addHours(2),
            'number_of_people' => 4,
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
        ]);
        $response = $this->post(route('restaurants.reservations.store', $restaurant), [
            'reserved_datetime' => $reservation->reserved_datetime,
            'number_of_people' => $reservation->number_of_people,
        ]);
        $response->assertRedirect('login');
    }

    public function test_authenticated_free_user_cannot_reserve_a_restaurant()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $reservation = Reservation::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
        ]);
        $response = $this->actingAs($user)->post(route('restaurants.reservations.store', $restaurant), [
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
        ]);
        $response->assertRedirect(route('subscription.create'));
    }

    public function test_authenticated_paid_user_can_reserve_a_restaurant()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QUlQtEEOG402gkC2UDm94kG')->create('pm_card_visa');
    
        $restaurant = Restaurant::factory()->create();
        $reservation = Reservation::factory()->make([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
        ]);
        $response = $this->actingAs($user)->post(route('restaurants.reservations.store', $restaurant), [
            'reservation_date' => $reservation->reserved_datetime->format('Y-m-d'),
            'reservation_time' => $reservation->reserved_datetime->format('H:i'),
            'number_of_people' => $reservation->number_of_people,
            'restaurant_id' => $restaurant->id,
        ]);
        $response->assertRedirect(route('reservations.index'));
        $response->assertSessionHas('flash_message', '予約を完了しました。');
    }

    public function test_authenticated_admin_cannot_reserve_a_restaurant()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $reservation = Reservation::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('restaurants.reservations.store', $restaurant));
        $response->assertRedirect(route('admin.home'));
    }

    public function test_guest_cannot_cancel_a_reservation()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $reservation = Reservation::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);
    
        $response = $this->delete(route('reservations.destroy', $reservation));
        $response->assertRedirect('login');
    }

    public function test_free_user_cannot_cancel_a_reservation()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        $reservation = Reservation::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->delete(route('reservations.destroy', $reservation));
        $response->assertRedirect(route('subscription.create'));
    }

    public function test_paid_user_can_cancel_their_reservation()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QUlQtEEOG402gkC2UDm94kG')->create('pm_card_visa');

        $restaurant = Restaurant::factory()->create();
        $reservation = Reservation::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(route('reservations.destroy', $reservation->id));

        $response->assertRedirect(route('reservations.index'));

        $this->assertDatabaseMissing('reservations', [
            'id' => $reservation->id,
        ]);
    }

    public function test_paid_user_cannot_cancel_other_users_reservation()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QUlQtEEOG402gkC2UDm94kG')->create('pm_card_visa');
        $restaurant = Restaurant::factory()->create();

        $anotherUser = User::factory()->create();
        $reservation = Reservation::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $anotherUser->id,
        ]);

        $response = $this->actingAs($user)->delete(route('reservations.destroy', $reservation));
        $response->assertRedirect(route('reservations.index'));
    }
}
