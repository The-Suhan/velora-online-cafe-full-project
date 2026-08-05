<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Canlı sipariş takibi uç noktaları.
 *
 * Not: bu suite sqlite :memory: üzerinde koşuyor ve sqlite datetime'ları
 * saniye hassasiyetinde saklıyor (Postgres mikrosaniye saklar). Bu yüzden
 * cursor'dan sonraki değişiklikler travel() ile ayrı bir saniyeye taşınıyor.
 */
class OrderUpdatesTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role = 'customer'): User
    {
        return User::create([
            'name' => 'Test '.$role,
            'email' => $role.'-'.uniqid().'@velora.test',
            'password' => 'secret-hash',
            'role' => $role,
            'is_verified' => true,
        ]);
    }

    private function makeOrder(User $user, string $status = 'pending'): Order
    {
        return Order::create([
            'user_id' => $user->id,
            'status' => $status,
            'delivery_type' => 'pickup',
            'phone' => '+993 61 000000',
            'total_price' => 25.00,
        ]);
    }

    public function test_customer_updates_returns_empty_when_nothing_changed(): void
    {
        $user = $this->makeUser();
        $this->makeOrder($user);
        Sanctum::actingAs($user);

        $cursor = $this->getJson('/api/orders/updates')->json('server_time');

        $this->travel(2)->seconds();

        $this->getJson('/api/orders/updates?since='.urlencode($cursor))
            ->assertOk()
            ->assertJsonPath('orders', [])
            ->assertJsonPath('active_count', 1);
    }

    public function test_customer_updates_returns_order_after_status_change(): void
    {
        $user = $this->makeUser();
        $order = $this->makeOrder($user);
        Sanctum::actingAs($user);

        $cursor = $this->getJson('/api/orders/updates')->json('server_time');

        $this->travel(2)->seconds();
        $order->update(['status' => 'preparing']);

        $this->getJson('/api/orders/updates?since='.urlencode($cursor))
            ->assertOk()
            ->assertJsonCount(1, 'orders')
            ->assertJsonPath('orders.0.id', $order->id)
            ->assertJsonPath('orders.0.status', 'preparing');
    }

    public function test_customer_updates_only_returns_own_orders(): void
    {
        $alice = $this->makeUser();
        $bob = $this->makeUser();
        $bobOrder = $this->makeOrder($bob);

        Sanctum::actingAs($alice);
        $cursor = $this->getJson('/api/orders/updates')->json('server_time');

        $this->travel(2)->seconds();
        $bobOrder->update(['status' => 'preparing']);

        $this->getJson('/api/orders/updates?since='.urlencode($cursor))
            ->assertOk()
            ->assertJsonPath('orders', [])
            ->assertJsonPath('active_count', 0);
    }

    public function test_customer_updates_baseline_returns_only_active_orders(): void
    {
        $user = $this->makeUser();
        $active = $this->makeOrder($user, 'preparing');
        $this->makeOrder($user, 'delivered');
        $this->makeOrder($user, 'cancelled');

        Sanctum::actingAs($user);

        $this->getJson('/api/orders/updates')
            ->assertOk()
            ->assertJsonCount(1, 'orders')
            ->assertJsonPath('orders.0.id', $active->id)
            ->assertJsonPath('active_count', 1);
    }

    public function test_admin_updates_counts_new_orders(): void
    {
        $admin = $this->makeUser('admin');
        $customer = $this->makeUser();
        Sanctum::actingAs($admin);

        $cursor = $this->getJson('/api/admin/orders/updates')->json('server_time');

        $this->travel(2)->seconds();
        $this->makeOrder($customer);

        $this->getJson('/api/admin/orders/updates?since='.urlencode($cursor))
            ->assertOk()
            ->assertJsonPath('new_orders', 1)
            ->assertJsonCount(1, 'changed')
            ->assertJsonPath('changed.0.is_new', true)
            ->assertJsonPath('counts.pending', 1);
    }

    public function test_admin_updates_requires_admin_role(): void
    {
        Sanctum::actingAs($this->makeUser());

        $this->getJson('/api/admin/orders/updates')->assertForbidden();
    }

    public function test_status_history_is_recorded_on_transition(): void
    {
        $admin = $this->makeUser('admin');
        $customer = $this->makeUser();
        $order = $this->makeOrder($customer);

        // Sipariş oluşturulurken başlangıç durumu kaydedilir.
        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $order->id,
            'from_status' => null,
            'to_status' => 'pending',
        ]);

        Sanctum::actingAs($admin);
        $this->patchJson("/api/admin/orders/{$order->id}/status", ['status' => 'preparing'])
            ->assertOk();

        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $order->id,
            'from_status' => 'pending',
            'to_status' => 'preparing',
            'changed_by' => $admin->id,
        ]);

        // Sadece not güncellemesi geçmişe hiçbir şey yazmamalı — canlı takipteki
        // yanlış pozitif korumasının backend tarafı.
        $before = OrderStatusHistory::where('order_id', $order->id)->count();
        $this->patchJson("/api/admin/orders/{$order->id}/note", ['note' => 'extra napkins'])
            ->assertOk();

        $this->assertSame($before, OrderStatusHistory::where('order_id', $order->id)->count());
    }

    public function test_invalid_since_is_rejected(): void
    {
        Sanctum::actingAs($this->makeUser());

        $this->getJson('/api/orders/updates?since=not-a-date')
            ->assertStatus(422);
    }
}
