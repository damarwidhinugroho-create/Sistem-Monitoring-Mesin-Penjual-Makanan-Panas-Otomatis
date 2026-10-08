<?php

namespace Tests\Feature;

use Tests\TestCase;

class OperatorPanelTest extends TestCase
{
    public function test_operator_login_authenticates_the_demo_operator(): void
    {
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

    public function test_sales_period_must_be_one_of_the_supported_values(): void
    {
        $this->post('/login', [
            'username' => 'Budi',
            'password' => 'budi123',
        ]);

        $this->getJson('/sales?period=year')->assertUnprocessable();
    }

    public function test_editing_a_slot_updates_the_session_backed_demo_repository(): void
    {
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
}
