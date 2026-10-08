<?php

namespace App\Repositories;

use App\Contracts\SalesRepositoryInterface;
use App\Enums\SalesPeriod;

class DemoSalesRepository implements SalesRepositoryInterface
{
    public function reportFor(SalesPeriod $period): array
    {
        return match ($period) {
            SalesPeriod::Jam => [
                'transactions_label' => 'Jumlah Transaksi Per Jam',
                'transactions' => '4',
                'revenue_label' => 'Total Omzet Per Jam',
                'revenue' => 'Rp. 120.000',
                'waste_label' => 'Persentase Makanan Terbuang Per Jam',
                'waste' => '0%',
                'product_performance' => [
                    ['product' => 'Mie Tarempa', 'sold' => 1, 'wasted' => 0, 'waste_rate' => '0%'],
                    ['product' => 'Luti Gendang', 'sold' => 2, 'wasted' => 0, 'waste_rate' => '0%'],
                    ['product' => 'Mie Sagu', 'sold' => 1, 'wasted' => 0, 'waste_rate' => '0%'],
                ],
                'bars' => [7, 2, 5, 6, 3],
            ],
            SalesPeriod::Hari => [
                'transactions_label' => 'Jumlah Transaksi Per Hari',
                'transactions' => '32',
                'revenue_label' => 'Total Omzet Per Hari',
                'revenue' => 'Rp. 700.000',
                'waste_label' => 'Persentase Makanan Terbuang Per Hari',
                'waste' => '17%',
                'product_performance' => [
                    ['product' => 'Mie Tarempa', 'sold' => 13, 'wasted' => 2, 'waste_rate' => '5%'],
                    ['product' => 'Luti Gendang', 'sold' => 10, 'wasted' => 4, 'waste_rate' => '2%'],
                    ['product' => 'Mie Sagu', 'sold' => 4, 'wasted' => 7, 'waste_rate' => '6%'],
                ],
                'bars' => [7, 9, 5, 11, 8],
            ],
            SalesPeriod::Minggu => [
                'transactions_label' => 'Jumlah Transaksi Per Hari',
                'transactions' => '32',
                'revenue_label' => 'Total Omzet Per Hari',
                'revenue' => 'Rp. 700.000',
                'waste_label' => 'Persentase Makanan Terbuang Per Hari',
                'waste' => '17%',
                'product_performance' => [
                    ['product' => 'Mie Tarempa', 'sold' => 13, 'wasted' => 2, 'waste_rate' => '5%'],
                    ['product' => 'Luti Gendang', 'sold' => 10, 'wasted' => 4, 'waste_rate' => '2%'],
                    ['product' => 'Mie Sagu', 'sold' => 4, 'wasted' => 7, 'waste_rate' => '6%'],
                ],
                'bars' => [7, 5, 8, 11, 6],
            ],
        };
    }

    public function transactions(): array
    {
        return [
            ['time' => '10:30', 'product' => 'Mie Tarempa', 'amount' => 'Rp. 25.000'],
            ['time' => '12:00', 'product' => 'Mie Tarempa', 'amount' => 'Rp. 25.000'],
            ['time' => '14:00', 'product' => 'Luti Gendang', 'amount' => 'Rp. 10.000'],
        ];
    }
}
