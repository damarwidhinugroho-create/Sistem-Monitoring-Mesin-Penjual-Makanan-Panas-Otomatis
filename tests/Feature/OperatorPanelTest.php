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

    public function test_login_page_displays_the_vending_machine_logo_without_forgot_password(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('LOGIN operator/UMKM')
            ->assertSee('icon login-card__avatar-logo')
            ->assertDontSee('Lupa Password');

        $this->get('/forgot-password')->assertNotFound();
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

        $this->get('/dashboard')->assertOk()->assertSee('Rp 1.250.000')->assertSee('Status VM')->assertSee('Online');
        $this->get('/inventory')->assertOk()->assertSee('30/10/2026 13:00')->assertSee('Online');
        $this->get('/sales?period=jam')->assertOk()->assertSee('Rp. 120.000')->assertSee('Online');
        $this->get('/sales?period=hari')->assertOk()->assertSee('Senin')->assertSee('Online');
        $this->get('/sales?period=minggu')->assertOk()->assertSee('Minggu 1')->assertSee('Online');
        $this->get('/temperature')->assertOk()->assertSee('62°C')->assertSee('Online');
        $this->get('/machine')->assertOk()->assertSee('VM - 001')->assertSee('Online');
        $this->get('/alerts')->assertOk()->assertSee('History Alert')->assertSee('Online');
        $this->get('/reports')->assertOk()->assertSee('Laporan')->assertSee('Online');
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

    public function test_stock_of_three_is_critical_on_dashboard_and_inventory(): void
    {
        config(['app.api_vending_url' => 'https://vending.example.test/api/inventory']);
        Http::fake([
            '*' => Http::response([
                'data' => [
                    [
                        'machine_id' => ApiController::MACHINE_ID,
                        'slot_code' => 'A1',
                        'quantity' => 3,
                        'minimum_stock' => 0,
                        'maximum_stock' => 10,
                        'product' => ['name' => 'Produk Kritis'],
                    ],
                    [
                        'machine_id' => ApiController::MACHINE_ID,
                        'slot_code' => 'A2',
                        'quantity' => 0,
                        'minimum_stock' => 0,
                        'maximum_stock' => 10,
                        'product' => ['name' => 'Produk Habis'],
                    ],
                    [
                        'machine_id' => ApiController::MACHINE_ID,
                        'slot_code' => 'A3',
                        'quantity' => 4,
                        'minimum_stock' => 0,
                        'maximum_stock' => 10,
                        'product' => ['name' => 'Produk Aman'],
                    ],
                ],
            ], 200),
        ]);

        $this->post('/login', [
            'username' => 'Budi',
            'password' => 'budi123',
        ]);

        foreach (['/dashboard', '/inventory'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSeeInOrder(['Produk Kritis', 'progress--warning', '3/10'])
                ->assertSeeInOrder(['Produk Habis', 'progress--empty', '0/10'])
                ->assertSeeInOrder(['Produk Aman', 'progress--safe', '4/10']);
        }
    }

    public function test_nested_inventory_api_data_is_displayed_for_each_slot(): void
    {
        config(['app.api_vending_url' => 'https://vending.example.test/api/inventory']);
        Http::fake([
            '*' => Http::response([
                'success' => true,
                'message' => 'Inventory retrieved successfully',
                'data' => [
                    [
                        'machine' => ['id' => ApiController::MACHINE_ID, 'name' => 'Vending Machine Ptr', 'status' => 'ONLINE'],
                        'slot' => ['id' => 3, 'slot_code' => 'A03', 'capacity' => 10],
                        'product' => ['id' => 1, 'name' => 'Mie Lendir', 'price' => '15000.00'],
                        'current_qty' => 7,
                        'hold_qty' => 2,
                        'available_qty' => 5,
                    ],
                    [
                        'machine' => ['id' => ApiController::MACHINE_ID, 'name' => 'Vending Machine Ptr', 'status' => 'ONLINE'],
                        'slot' => ['id' => 5, 'slot_code' => 'A04', 'capacity' => 1],
                        'product' => ['id' => 12, 'name' => 'Ayam Bakar Taliwang', 'price' => '17100.00'],
                        'current_qty' => 1,
                        'hold_qty' => 0,
                        'available_qty' => 1,
                    ],
                    [
                        'machine' => ['id' => ApiController::MACHINE_ID, 'name' => 'Vending Machine Ptr', 'status' => 'ONLINE'],
                        'slot' => ['id' => 7, 'slot_code' => 'A05', 'capacity' => 7],
                        'product' => ['id' => 11, 'name' => 'Ayam Geprek', 'price' => '22500.00'],
                        'current_qty' => 6,
                        'hold_qty' => 0,
                        'available_qty' => 6,
                    ],
                    [
                        'machine' => ['id' => ApiController::MACHINE_ID, 'name' => 'Vending Machine Ptr', 'status' => 'ONLINE'],
                        'slot' => ['id' => 8, 'slot_code' => 'A06', 'capacity' => 5],
                        'product' => ['id' => 1, 'name' => 'Mie Lendir', 'price' => '15000.00'],
                        'current_qty' => 5,
                        'hold_qty' => 0,
                        'available_qty' => 5,
                    ],
                    [
                        'machine' => ['id' => ApiController::MACHINE_ID, 'name' => 'Vending Machine Ptr', 'status' => 'ONLINE'],
                        'slot' => ['id' => 9, 'slot_code' => 'A07', 'capacity' => 2],
                        'product' => ['id' => 6, 'name' => 'Lemper Ayam', 'price' => '9000.00'],
                        'current_qty' => 2,
                        'hold_qty' => 0,
                        'available_qty' => 2,
                    ],
                ],
            ], 200),
        ]);

        $this->post('/login', [
            'username' => 'Budi',
            'password' => 'budi123',
        ]);

        $this->get('/inventory')
            ->assertOk()
            ->assertSee('A03')
            ->assertSee('A07')
            ->assertSee('Mie Lendir')
            ->assertSee('Ayam Bakar Taliwang')
            ->assertSee('5/10')
            ->assertSee('1/1')
            ->assertSee('6/7')
            ->assertSee('5/5')
            ->assertSee('2/2');

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('19 Porsi')
            ->assertSee('dari 5 slot aktif')
            ->assertSee('Mie Lendir')
            ->assertSee('Ayam Bakar Taliwang');
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

    public function test_offline_machine_inventory_is_not_displayed(): void
    {
        config(['app.api_vending_url' => 'https://vending.example.test/api/inventory']);
        Http::fake([
            '*' => Http::response([
                'data' => [
                    [
                        'machine' => [
                            'id' => ApiController::MACHINE_ID,
                            'status' => 'OFFLINE',
                        ],
                        'slot' => ['slot_code' => 'A1', 'capacity' => 10],
                        'product' => ['name' => 'Produk Offline'],
                        'available_qty' => 8,
                    ],
                    [
                        'machine' => [
                            'id' => ApiController::MACHINE_ID,
                            'status' => 'UNKNOWN',
                        ],
                        'slot' => ['slot_code' => 'A2', 'capacity' => 10],
                        'product' => ['name' => 'Produk Status Tidak Diketahui'],
                        'available_qty' => 5,
                    ],
                ],
            ], 200),
        ]);

        $this->post('/login', [
            'username' => 'Budi',
            'password' => 'budi123',
        ]);

        $this->get('/inventory')
            ->assertOk()
            ->assertDontSee('Produk Offline')
            ->assertDontSee('Produk Status Tidak Diketahui')
            ->assertSee('Data masih belum ada.');

        $this->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Produk Offline')
            ->assertDontSee('Produk Status Tidak Diketahui')
            ->assertSee('dari 0 slot aktif');

        $this->get('/machine')
            ->assertOk()
            ->assertSee('sidebar__vm-card--offline', false)
            ->assertSee('Offline');
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
                        'status' => 'ONLINE',
                        'quantity' => 8,
                        'minimum_stock' => 2,
                        'maximum_stock' => 10,
                        'last_restocked_at' => '2026-10-30T06:00:00.000000Z',
                        'product' => ['name' => 'Mie Tarempa Original', 'price' => '25000.00'],
                    ],
                    [
                        'machine_id' => ApiController::MACHINE_ID,
                        'status' => 'ONLINE',
                        'quantity' => 2,
                        'minimum_stock' => 2,
                        'maximum_stock' => 10,
                        'last_restocked_at' => '2026-10-29T06:00:00.000000Z',
                        'product' => ['name' => 'Luti Gendang Abon', 'price' => '12000.00'],
                    ],
                    [
                        'machine_id' => ApiController::MACHINE_ID,
                        'status' => 'ONLINE',
                        'quantity' => 20,
                        'minimum_stock' => 3,
                        'maximum_stock' => 20,
                        'last_restocked_at' => '2026-10-28T06:00:00.000000Z',
                        'product' => ['name' => 'Mie Sagu', 'price' => '13000.00'],
                    ],
                    [
                        'machine_id' => ApiController::MACHINE_ID,
                        'status' => 'ONLINE',
                        'quantity' => 10,
                        'minimum_stock' => 2,
                        'maximum_stock' => 10,
                        'last_restocked_at' => '2026-10-27T06:00:00.000000Z',
                        'product' => ['name' => 'Luti Gendang Cokelat', 'price' => '11000.00'],
                    ],
                    [
                        'machine_id' => ApiController::MACHINE_ID + 1,
                        'status' => 'ONLINE',
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
