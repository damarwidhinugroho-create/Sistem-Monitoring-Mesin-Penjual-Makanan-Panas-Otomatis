<?php

namespace Tests\Feature;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OperatorPanelTest extends TestCase
{
    public function test_operator_login_authenticates_the_demo_operator(): void
    {
        $this->fakeInventoryApi();

        $response = $this->post('/login', [
            'username' => 'Budi',
            'password' => 'budi123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertOk()->assertSee('DASHBOARD OVERVIEW');
    }

    public function test_forgot_password_page_is_available_to_guests(): void
    {
        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('LUPA PASSWORD')
            ->assertSee('Kembali ke Login')
            ->assertSee('images/logo.svg');
    }

    public function test_operator_pages_require_a_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/inventory')->assertRedirect('/login');
    }

    public function test_all_operator_screens_render_their_prototype_content(): void
    {
        $this->fakeInventoryApi();

        $this->post('/login', [
            'username' => 'Budi',
            'password' => 'budi123',
        ]);

        $this->get('/dashboard')->assertOk()->assertSee('Rp 1.250.000');
        $this->get('/inventory')->assertOk()->assertSee('30/10/2026 13:00');
        $this->get('/sales?period=jam')->assertOk()->assertSee('Rp. 120.000');
        $this->get('/sales?period=hari')->assertOk()->assertSee('Senin');
        $this->get('/sales?period=minggu')->assertOk()->assertSee('Minggu 1');
        $this->get('/temperature')->assertOk()->assertSee('62°C');
        $this->get('/machine')->assertOk()->assertSee('VM - 001');
        $this->get('/alerts')->assertOk()->assertSee('History Alert');
        $this->get('/reports')->assertOk()->assertSee('Laporan');
    }

    public function test_inventory_data_is_shared_with_the_dashboard(): void
    {
        $this->fakeInventoryApi();

        $this->post('/login', [
            'username' => 'Budi',
            'password' => 'budi123',
        ]);

        $this->get('/inventory')
            ->assertOk()
            ->assertSee('Mie Tarempa Original')
            ->assertSee('Luti Gendang Abon')
            ->assertSee('40')
            ->assertDontSee('Produk Mesin Lain')
            ->assertDontSee('Data masih belum ada');

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('40 Porsi')
            ->assertSee('dari 4 slot aktif')
            ->assertSee('Mie Tarempa Original')
            ->assertSee('Luti Gendang Abon')
            ->assertDontSee('Produk Mesin Lain')
            ->assertDontSee('Data masih belum ada');
    }

    public function test_inventory_and_dashboard_show_a_message_when_api_data_is_empty(): void
    {
        config(['app.api_vending_url' => 'https://vending.example.test/api/inventory']);
        Http::fake(['*' => Http::response(['data' => []], 200)]);

        $this->post('/login', [
            'username' => 'Budi',
            'password' => 'budi123',
        ]);

        $this->get('/inventory')
            ->assertOk()
            ->assertSee('colspan="6" role="status">Data masih belum ada.', false)
            ->assertSee('colspan="4" role="status">Data masih belum ada.', false)
            ->assertDontSee('<p class="surface" role="status">Data masih belum ada.</p>', false);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('colspan="6" role="status">Data masih belum ada.', false)
            ->assertDontSee('<p class="surface" role="status">Data masih belum ada.</p>', false);
    }

    public function test_api_controller_uses_the_configured_vending_url(): void
    {
        $url = 'https://vending.example.test/api/inventory';
        config(['app.api_vending_url' => $url]);
        Http::fake(['*' => Http::response(['data' => []], 200)]);

        app(ApiController::class)->getInventory();

        Http::assertSent(fn (Request $request): bool => $request->url() === $url);
    }

    public function test_sales_period_must_be_one_of_the_supported_values(): void
    {
        $this->post('/login', [
            'username' => 'Budi',
            'password' => 'budi123',
        ]);

        $this->getJson('/sales?period=year')->assertUnprocessable();
    }

    public function test_editing_a_slot_updates_the_session_override(): void
    {
        $this->fakeInventoryApi();

        $this->post('/login', [
            'username' => 'Budi',
            'password' => 'budi123',
        ]);

        $this->patch('/inventory/slots/A1', [
            'product' => 'Mie Tarempa',
            'stock' => 7,
            'filled_at' => '10 Sep 2026 09.00',
            'price' => 30000,
        ])->assertRedirect('/inventory');

        $this->get('/inventory')->assertOk()->assertSee('7/10');
    }

    private function fakeInventoryApi(): void
    {
        config(['app.api_vending_url' => 'https://vending.example.test/api/inventory']);

        Http::fake([
            '*' => Http::response([
                'data' => [
                    [
                        'machine_id' => ApiController::MACHINE_ID,
                        'quantity' => 8,
                        'minimum_stock' => 2,
                        'maximum_stock' => 10,
                        'last_restocked_at' => '2026-10-30T06:00:00.000000Z',
                        'product' => ['name' => 'Mie Tarempa Original', 'price' => '25000.00'],
                    ],
                    [
                        'machine_id' => ApiController::MACHINE_ID,
                        'quantity' => 2,
                        'minimum_stock' => 2,
                        'maximum_stock' => 10,
                        'last_restocked_at' => '2026-10-29T06:00:00.000000Z',
                        'product' => ['name' => 'Luti Gendang Abon', 'price' => '12000.00'],
                    ],
                    [
                        'machine_id' => ApiController::MACHINE_ID,
                        'quantity' => 20,
                        'minimum_stock' => 3,
                        'maximum_stock' => 20,
                        'last_restocked_at' => '2026-10-28T06:00:00.000000Z',
                        'product' => ['name' => 'Mie Sagu', 'price' => '13000.00'],
                    ],
                    [
                        'machine_id' => ApiController::MACHINE_ID,
                        'quantity' => 10,
                        'minimum_stock' => 2,
                        'maximum_stock' => 10,
                        'last_restocked_at' => '2026-10-27T06:00:00.000000Z',
                        'product' => ['name' => 'Luti Gendang Cokelat', 'price' => '11000.00'],
                    ],
                    [
                        'machine_id' => ApiController::MACHINE_ID + 1,
                        'quantity' => 100,
                        'minimum_stock' => 1,
                        'maximum_stock' => 100,
                        'last_restocked_at' => '2026-10-27T06:00:00.000000Z',
                        'product' => ['name' => 'Produk Mesin Lain', 'price' => '1000.00'],
                    ],
                ],
            ], 200),
        ]);
    }
}
