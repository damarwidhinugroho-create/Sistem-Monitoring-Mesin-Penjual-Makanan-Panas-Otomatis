<?php

namespace Tests\Feature;

use App\Http\Controllers\ApiController;
use App\Enums\StockStatus;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
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
        $this->get('/dashboard')->assertOk()->assertSee('Dashboard');
    }

    public function test_login_page_uses_the_saji_vending_machine_without_prefilled_credentials(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Welcome back!')
            ->assertSee('MASUK KE DASHBOARD')
            ->assertSee('pageMotion-', false)
            ->assertSee('data-vending-machine', false)
            ->assertSee('SAJI!')
            ->assertDontSee('SMART WARM FOOD')
            ->assertDontSee('OPEN 24/7')
            ->assertDontSee('OPERATOR')
            ->assertDontSee('Akses khusus untuk operator mesin.')
            ->assertDontSee('PUSH')
            ->assertSee('name="username"', false)
            ->assertSee('name="password"', false)
            ->assertDontSee('value="Budi"', false)
            ->assertDontSee('value="budi123"', false)
            ->assertDontSee('Lupa password?');

        $this->get('/forgot-password')->assertNotFound();
    }

    public function test_operator_pages_require_a_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/inventory')->assertRedirect('/login');
        $this->get('/reports')->assertRedirect('/login');
    }

    public function test_dashboard_and_inventory_keep_the_shared_shell_while_reports_render_export_controls(): void
    {
        $this->fakeInventoryApi();

        $this->post('/login', [
            'username' => 'Budi',
            'password' => 'budi123',
        ]);

        foreach (['/dashboard', '/inventory'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee('id="operator-sidebar"', false)
                ->assertSee(route('dashboard'), false)
                ->assertSee(route('inventory'), false)
                ->assertSee(route('reports'), false)
                ->assertDontSee('pageMotion-', false)
                ->assertDontSee('VENDING MANAGEMENT')
                ->assertDontSee('Administrator')
                ->assertDontSee('TAMPILAN')
                ->assertDontSee('WORKSPACE')
                ->assertDontSee('sidebar__bottom-status', false)
                ->assertDontSee('data-dashboard-stage', false)
                ->assertDontSee('data-slot-card', false)
                ->assertDontSee('data-open-export', false)
                ->assertDontSee('Mie Tarempa');
        }

        $this->get('/reports')
            ->assertOk()
            ->assertSee('Laporan Penjualan')
            ->assertSee('data-open-export', false)
            ->assertSee('data-report-history-table', false)
            ->assertSee('TANGGAL EKSPOR')
            ->assertDontSee('data-report-tab', false)
            ->assertSee('History Alert')
            ->assertSee('data-export-empty-note', false);

        $this->get('/sales?period=jam')->assertOk()->assertSee('Rp. 120.000')->assertSee('Online')->assertSee('pageMotion-', false);
        $this->get('/sales?period=hari')->assertOk()->assertSee('Senin')->assertSee('Online');
        $this->get('/sales?period=minggu')->assertOk()->assertSee('Minggu 1')->assertSee('Online');
        $this->get('/temperature')->assertNotFound();
        $this->get('/machine')->assertOk()->assertSee('VM - 001')->assertSee('Online')->assertSee('pageMotion-', false);
        $this->get('/alerts')->assertOk()->assertSee('History Alert')->assertSee('Online')->assertSee('pageMotion-', false);
    }

    public function test_inventory_data_is_shared_with_the_dashboard(): void
    {
        $this->fakeInventoryApi();

        $this->post('/login', [
            'username' => 'Budi',
            'password' => 'budi123',
        ]);

        $this->get('/inventory')->assertOk();
        $this->get('/dashboard')->assertOk();

        $inventory = app(ApiController::class)->getInventory();
        $products = array_column(array_column($inventory, 'product'), 'name');
        $this->assertContains('Mie Tarempa Original', $products);
        $this->assertContains('Luti Gendang Abon', $products);
        $this->assertNotContains('Produk Mesin Lain', $products);
    }

    public function test_vending_api_response_is_cached_between_page_requests(): void
    {
        $this->fakeInventoryApi();

        $this->post('/login', [
            'username' => 'Budi',
            'password' => 'budi123',
        ]);

        $this->get('/inventory')->assertOk();
        $this->get('/dashboard')->assertOk();
        $this->get('/sales')->assertOk();

        Http::assertSentCount(1);
    }

    public function test_last_known_inventory_is_used_when_vending_api_is_unavailable(): void
    {
        $apiUrl = 'https://vending.example.test/api/inventory';
        config(['app.api_vending_url' => $apiUrl]);
        Cache::put('vending.inventory.'.hash('sha256', $apiUrl).'.last-known-good', [
            [
                'machine_id' => ApiController::MACHINE_ID,
                'status' => 'ONLINE',
                'quantity' => 4,
                'product' => ['name' => 'Produk Cache'],
            ],
        ], now()->addMinutes(15));
        Http::fake(['*' => Http::failedConnection()]);

        $inventory = app(ApiController::class)->getInventory();

        $this->assertSame('Produk Cache', $inventory[0]['product']['name']);
    }

    public function test_stock_status_logic_still_classifies_inventory_levels(): void
    {
        $this->assertSame(StockStatus::Rendah, StockStatus::fromStock(3, 0));
        $this->assertSame(StockStatus::Habis, StockStatus::fromStock(0, 0));
        $this->assertSame(StockStatus::Aman, StockStatus::fromStock(4, 0));
    }

    public function test_nested_inventory_api_data_is_normalized_for_each_slot(): void
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

        $this->get('/inventory')->assertOk();
        $inventory = app(ApiController::class)->getInventory();

        $this->assertSame(['A03', 'A04', 'A05', 'A06', 'A07'], array_column($inventory, 'slot_code'));
        $this->assertSame(['5', '1', '6', '5', '2'], array_map(
            fn (array $row): string => (string) $row['quantity'],
            $inventory,
        ));
        $this->assertSame('Mie Lendir', $inventory[0]['product']['name']);
        $this->assertSame('Ayam Bakar Taliwang', $inventory[1]['product']['name']);
    }

    public function test_empty_page_views_do_not_render_page_specific_inventory_content(): void
    {
        config(['app.api_vending_url' => 'https://vending.example.test/api/inventory']);
        Http::fake(['*' => Http::response(['data' => []], 200)]);

        $this->post('/login', [
            'username' => 'Budi',
            'password' => 'budi123',
        ]);

        $this->get('/inventory')
            ->assertOk()
            ->assertSee('id="operator-sidebar"', false)
            ->assertDontSee('data-slot-card', false)
            ->assertDontSee('Data masih belum ada');

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('id="operator-sidebar"', false)
            ->assertDontSee('data-dashboard-stage', false)
            ->assertDontSee('Data masih belum ada');

        $this->assertSame([], app(ApiController::class)->getInventory());
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

        $this->get('/inventory')->assertOk()->assertDontSee('Produk Offline');
        $this->get('/dashboard')->assertOk()->assertDontSee('Produk Offline');

        $this->get('/machine')
            ->assertOk()
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
        ])->assertRedirect('/inventory')
            ->assertSessionHas('operator.slot_updates.A1.stock', 7);

        $this->get('/inventory')->assertOk()->assertDontSee('7/10');
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
