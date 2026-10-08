@extends('layouts.tata-letak-panel')

@section('title', 'Manajemen Stok')

@section('content')
    <div class="inventory__heading">
        <h1 class="page-title">Manajemen Stok</h1>
        <p class="page-subtitle">Manajemen stok produk VM</p>
    </div>

    <section class="stat-grid inventory__stats" aria-label="Ringkasan stok">
        @foreach ($inventory->summary as $stat)
            <x-kartu-ringkasan :label="$stat['label']" :value="$stat['value']" :icon="false" />
        @endforeach
    </section>

    <section class="surface inventory__life">
        <div class="inventory__life-title"><h2 class="section-heading">Masa Simpan Makanan</h2></div>
        <x-tabel-data :headers="['PRODUK', 'SUHU', 'STOK', 'DURASI DI MESIN', 'SISA WAKTU', 'STATUS']">
            @foreach ($inventory->shelfLifeRows as $row)
                <tr>
                    <td>{{ $row['product'] }}</td>
                    <td>{{ $row['temperature'] }}</td>
                    <td><x-batang-progres :percentage="$row['stock_percent']" :status="$row['stock_status']->value" :label="$row['stock'].'/'.$row['capacity']" /></td>
                    <td>{{ $row['duration'] }}</td>
                    <td>{{ $row['remaining'] }}</td>
                    <td><x-lencana-status :label="$row['status']->label()" :status="$row['status']->value" /></td>
                </tr>
            @endforeach
        </x-data-table>
    </section>

    <section class="surface inventory__slots">
        <div class="inventory__slots-header">
            <h2 class="section-heading">Manajemen Stok</h2>
            <div class="inventory__legend">
                <x-lencana-status label="Aman" status="aman" />
                <x-lencana-status label="Rendah" status="rendah" />
                <x-lencana-status label="Habis" status="habis" />
            </div>
        </div>
        <div class="inventory__slot-grid">
            @foreach ($inventory->slots as $slot)
                <article class="slot-card">
                    <div class="slot-card__top">
                        <span class="slot-card__slot">{{ $slot['slot'] }}<small>{{ $slot['slot_number'] }}</small></span>
                        <span class="slot-card__label">Status <x-lencana-status :label="$slot['status']->label()" :status="$slot['status']->value" /></span>
                    </div>
                    <h3 class="slot-card__product">{{ $slot['product'] }}</h3>
                    <span class="slot-card__duration">{{ $slot['duration'] }}</span>
                    <div class="slot-card__meta">Stok <x-batang-progres :percentage="$slot['stock_percent']" :status="$slot['status']->value" :label="$slot['stock'].'/'.$slot['capacity']" /></div>
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
            @foreach ($inventory->restockHistory as $entry)
                <tr>
                    <td>{{ $entry['date'] }}</td>
                    <td>{{ $entry['slot'] }}</td>
                    <td>{{ $entry['product'] }}</td>
                    <td>{{ $entry['quantity'] }}</td>
                </tr>
            @endforeach
        </x-tabel-data>
        <nav class="pagination" aria-label="Halaman riwayat restok">
            <button type="button" class="is-current">1</button><button type="button">2</button><button type="button">3</button><span>...</span><button type="button">10</button><button type="button" aria-label="Halaman berikutnya">&gt;</button>
        </nav>
    </section>

    @foreach ($inventory->slots as $slot)
        <dialog class="dialog" id="slot-dialog-{{ $slot['slot'] }}" data-slot-dialog>
            <form class="dialog__content" method="POST" action="{{ route('inventory.slots.update', ['slot' => $slot['slot']]) }}">
                @csrf
                @method('PATCH')
                <div class="dialog__row"><span class="dialog__label">NAMA PRODUK</span><span class="dialog__value" data-slot-view>{{ $slot['product'] }}</span><input class="dialog__input" name="product" value="{{ $slot['product'] }}" required hidden></div>
                <div class="dialog__row"><span class="dialog__label">SLOT</span><span class="dialog__value">{{ $slot['slot'] }}</span></div>
                <div class="dialog__row"><span class="dialog__label">STOK</span><span class="dialog__value" data-slot-view><x-batang-progres :percentage="$slot['stock_percent']" :status="$slot['status']->value" :label="$slot['stock'].'/'.$slot['capacity']" /></span><input class="dialog__input" type="number" name="stock" value="{{ $slot['stock'] }}" min="0" max="10" required hidden></div>
                <div class="dialog__row"><span class="dialog__label">TERAKHIR FILL</span><span class="dialog__value" data-slot-view>{{ $slot['filled_at'] }}</span><input class="dialog__input" name="filled_at" value="{{ $slot['filled_at'] }}" required hidden></div>
                <div class="dialog__row"><span class="dialog__label">HARGA</span><span class="dialog__value" data-slot-view>{{ $slot['price'] }}</span><input class="dialog__input" type="number" name="price" value="{{ $slot['price_value'] }}" min="0" required hidden></div>
                <div class="dialog__row"><span class="dialog__label">AKSI</span><span class="dialog__value"><button class="dialog__edit" type="button" data-edit-slot>Edit</button><button class="dialog__edit" type="submit" data-save-slot hidden>Simpan</button></span></div>
            </form>
        </dialog>
    @endforeach
@endsection

@push('styles')
    @vite('resources/css/inventaris-stok.css')
@endpush
