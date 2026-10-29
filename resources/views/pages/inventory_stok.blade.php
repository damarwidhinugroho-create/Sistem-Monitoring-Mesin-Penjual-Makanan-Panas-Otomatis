@extends('layouts.layout_dashboard')

@section('title', 'Manajemen Stok')

@section('content')
    <div class="inventory__heading">
        <h1 class="page-title">Manajemen Stok</h1>
        <p class="page-subtitle">Manajemen stok produk VM</p>
    </div>

    <section class="stat-grid inventory__stats" aria-label="Ringkasan stok">
        @foreach ($inventory['summary'] as $stat)
            @php
                $iconName = match ($stat['label']) {
                    'Total Stok' => 'box',
                    'Stok Rendah' => 'alert-triangle',
                    'Produk Habis' => 'box-empty',
                    'Perlu Perhatian (Masa Simpan)' => 'clock',
                };
            @endphp
            <x-kartu-ringkasan :label="$stat['label']" :value="$stat['value']" :icon-name="$iconName" />
        @endforeach
    </section>

    <section class="surface inventory__life">
        <div class="inventory__life-title"><h2 class="section-heading">Masa Simpan Makanan</h2></div>
        <x-tabel-data :headers="['PRODUK', 'SUHU', 'STOK', 'DURASI DI MESIN', 'SISA WAKTU', 'STATUS']">
            @forelse ($inventory['shelfLifeRows'] as $row)
                <tr>
                    <td>{{ $row['product'] }}</td>
                    <td>{{ $row['temperature'] }}</td>
                    <td><x-progress-bar :percentage="$row['stock'] === 0 ? 4 : $row['stock_percent']" :status="$row['stock_status']->value" :label="$row['stock'].'/'.$row['capacity']" /></td>
                    <td>{{ $row['duration'] ?? '—' }}</td>
                    <td>{{ $row['remaining'] ?? '—' }}</td>
                    <td>
                        @if ($row['status'])
                            <x-lencana-status :label="$row['status']->label()" :status="$row['status']->value" />
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" role="status">Data masih belum ada.</td>
                </tr>
            @endforelse
        </x-data-table>
    </section>

    @php
        $inventorySlotTone = 'aman';
        foreach ($inventory['slots'] as $slot) {
            if ($slot['status']->value === 'habis') {
                $inventorySlotTone = 'habis';
                break;
            }

            if ($slot['status']->value === 'rendah') {
                $inventorySlotTone = 'rendah';
            }
        }
    @endphp

    <section class="inventory__slots inventory__slots--{{ $inventorySlotTone }}">
        <div class="inventory__slots-header">
            <h2 class="section-heading">Manajemen Stok</h2>
            {{-- <div class="inventory__legend">
                <x-lencana-status label="Aman" status="aman" />
                <x-lencana-status label="Rendah" status="rendah" />
                <x-lencana-status label="Habis" status="habis" />
            </div> --}}
        </div>
        <div class="inventory__slot-grid">
            @foreach ($inventory['slots'] as $slot)
                <article class="slot-card">
                    <div class="slot-card__top">
                        <span class="slot-card__slot">{{ $slot['slot'] }}<small>{{ $slot['slot_number'] }}</small></span>
                        {{-- <span class="slot-card__label">Status <x-lencana-status :label="$slot['status']->label()" :status="$slot['status']->value" /></span> --}}
                    </div>
                    <h3 class="slot-card__product">{{ $slot['product'] }}</h3>
                    <span class="slot-card__duration">{{ $slot['duration'] }}</span>
                    <div class="slot-card__meta">Stok <x-progress-bar :percentage="$slot['stock'] === 0 ? 4 : $slot['stock_percent']" :status="$slot['status']->value" :label="$slot['stock'].'/'.$slot['capacity']" /></div>
                    <div class="slot-card__meta">Terakhir refill<strong>{{ $slot['filled_at'] }}</strong></div>
                    <div class="slot-card__meta">Harga<strong>{{ $slot['price'] }}</strong></div>
                    <button class="slot-card__edit" type="button" data-open-slot="{{ $slot['slot'] }}">Edit</button>
                </article>
            @endforeach
        </div>
    </section>

    <section class="surface restock">
        <h2 class="section-heading restock__heading">Riwayat Restok</h2>
        <x-tabel-data :headers="['TANGGAL & WAKTU', 'SLOT', 'PRODUK', 'JUMLAH TAMBAH']">
            @forelse ($inventory['restockHistory'] as $entry)
                <tr>
                    <td>{{ $entry['date'] }}</td>
                    <td>{{ $entry['slot'] }}</td>
                    <td>{{ $entry['product'] }}</td>
                    <td>{{ $entry['quantity'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" role="status">Data masih belum ada.</td>
                </tr>
            @endforelse
        </x-tabel-data>
        <nav class="pagination" aria-label="Halaman riwayat restok">
            <button type="button" class="is-current">1</button><button type="button">2</button><button type="button">3</button><span>...</span><button type="button">10</button>
            <button type="button" aria-label="Halaman berikutnya"><x-ikon name="chevron-right" :size="12" /></button>
        </nav>
    </section>

    @foreach ($inventory['slots'] as $slot)
        <dialog class="dialog" id="slot-dialog-{{ $slot['slot'] }}" aria-labelledby="slot-dialog-title-{{ $slot['slot'] }}" data-slot-dialog>
            <form class="dialog__content" method="POST" action="{{ route('inventory.slots.update', ['slot' => $slot['slot']]) }}">
                @csrf
                @method('PATCH')
                <header class="dialog__header">
                    <h2 class="dialog__title" id="slot-dialog-title-{{ $slot['slot'] }}">Edit Slot {{ $slot['slot'] }}</h2>
                    <button class="dialog__close" type="button" data-close-slot>Tutup</button>
                </header>
                <div class="dialog__row"><span class="dialog__label">NAMA PRODUK</span><input class="dialog__input" name="product" value="{{ $slot['product'] }}" required></div>
                <div class="dialog__row"><span class="dialog__label">SLOT</span><span class="dialog__value">{{ $slot['slot'] }}</span></div>
                <div class="dialog__row"><span class="dialog__label">STOK</span><input class="dialog__input" type="number" name="stock" value="{{ $slot['stock'] }}" min="0" max="10" required></div>
                <div class="dialog__row"><span class="dialog__label">TERAKHIR FILL</span><input class="dialog__input" name="filled_at" value="{{ $slot['filled_at'] }}" required></div>
                <div class="dialog__row"><span class="dialog__label">HARGA</span><input class="dialog__input" type="number" name="price" value="{{ $slot['price_value'] }}" min="0" required></div>
                <div class="dialog__row"><span class="dialog__label">AKSI</span><span class="dialog__value"><button class="dialog__edit" type="submit">Simpan</button></span></div>
            </form>
        </dialog>
    @endforeach
@endsection

@push('styles')
    @vite('resources/css/inventaris_stok.css')
@endpush
