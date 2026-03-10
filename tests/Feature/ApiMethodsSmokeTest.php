<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiMethodsSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        Sanctum::actingAs($user, ['create', 'update', 'delete']);
    }

    public function test_customers_index_works(): void
    {
        Customer::factory()->count(2)->create();
        $this->getJson('/api/v1/customers')->assertOk();
    }

    public function test_customers_store_works(): void
    {
        $createPayload = [
            'name' => 'Acme Corp',
            'type' => 'B',
            'email' => 'acme@example.com',
            'address' => 'Main St 1',
            'city' => 'Madrid',
            'state' => 'Madrid',
            'postalCode' => '28001',
        ];

        $create = $this->postJson('/api/v1/customers', $createPayload);
        $create->assertSuccessful();
        $this->assertDatabaseHas('customers', ['email' => 'acme@example.com']);
    }

    public function test_customers_show_works(): void
    {
        $customer = Customer::factory()->create();
        $this->getJson("/api/v1/customers/{$customer->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $customer->id);
    }

    public function test_customers_update_works(): void
    {
        $customer = Customer::factory()->create();

        $update = $this->patchJson("/api/v1/customers/{$customer->id}", [
            'city' => 'Barcelona',
        ]);
        $update->assertOk();
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'city' => 'Barcelona']);
    }

    public function test_customers_destroy_works(): void
    {
        $customer = Customer::factory()->create();
        $delete = $this->deleteJson("/api/v1/customers/{$customer->id}");
        $delete->assertSuccessful();
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_invoices_index_works(): void
    {
        $index = $this->getJson('/api/v1/invoices');
        $index->assertOk();
    }

    public function test_invoices_store_works(): void
    {
        $customer = Customer::factory()->create();

        $store = $this->postJson('/api/v1/invoices', [[
            'customerId' => $customer->id,
            'amount' => 150,
            'status' => 'B',
            'billedDate' => '2026-03-07 12:00:00',
            'paidDate' => null,
        ]]);
        $store->assertSuccessful();
        $this->assertDatabaseHas('invoices', ['customer_id' => $customer->id, 'amount' => 150]);
    }

    public function test_invoices_show_works(): void
    {
        $invoice = Invoice::factory()->create();

        $show = $this->getJson("/api/v1/invoices/{$invoice->id}");
        $show->assertOk()->assertJsonPath('data.id', $invoice->id);
    }

    public function test_invoices_update_works(): void
    {
        $invoice = Invoice::factory()->create();

        $update = $this->patchJson("/api/v1/invoices/{$invoice->id}", [
            'amount' => 300,
        ]);
        $update->assertOk();
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'amount' => 300]);
    }

    public function test_invoices_destroy_works(): void
    {
        $invoice = Invoice::factory()->create();
        $delete = $this->deleteJson("/api/v1/invoices/{$invoice->id}");
        $delete->assertSuccessful();
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    public function test_invoice_bulk_insert_route_works(): void
    {
        $customer = Customer::factory()->create();

        $payload = [[
            'customerId' => $customer->id,
            'amount' => 500,
            'status' => 'B',
            'billedDate' => '2026-03-07 12:00:00',
            'paidDate' => null,
        ]];

        $response = $this->postJson('/api/v1/invoice/bulk', $payload);
        $response->assertSuccessful();

        $this->assertDatabaseHas('invoices', [
            'customer_id' => $customer->id,
            'amount' => 500,
            'status' => 'B',
        ]);
    }
}
