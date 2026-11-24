@extends('layouts.layout_dashboard')

@section('title', 'Manajemen Stok')
@section('breadcrumb', 'STOK')
@section('page-heading', 'Manajemen Stok')
@section('page-subtitle', 'Manajemen stok produk VM')

@section('content')

    {{-- kartu ringkas --}}
    <section class="stat-grid inventory__stats" aria-label="Ringkasan stok">
        @foreach ($inventory['summary'] as $item)
            <x-kartu-ringkasan :label="$item['label']" :value="(string) $item['value']" :detail="$item['detail']" :icon="false" />
        @endforeach
    </section>

    {{-- masa simpan makanan --}}
    <section class="surface inventory__life" aria-label="Masa simpan makanan">
        <header class="inventory__life-title">
            <h2 class="panel__title">Masa Simpan Makanan</h2>
        </header>

        <div data-paginated-list data-item-label="produk" data-page-size="6">
            <x-tabel-data class="inventory__life-table" :headers="['PRODUK', 'SUHU', 'STOK', 'DURASI DI MESIN', 'SISA WAKTU', 'STATUS']">
                @forelse ($inventory['shelfLifeRows'] as $row)
                    <tr data-pagination-row>
                        <td>{{ $row['product'] }}</td>
                        <td>{{ $row['temperature'] ?? '61°C' }}</td>
                        <td>
                            <x-progress-bar :percentage="$row['stock_percent']" :status="$row['stock_status']->value"
                                :label="$row['stock'] . '/' . $row['capacity']" />
                        </td>
                        <td>{{ $row['duration'] ?? '—' }}</td>
                        <td>{{ $row['remaining'] ?? '—' }}</td>
                        <td>
                            @if ($row['status'] !== null)
                                <x-lencana-status :label="$row['status']->label()" :status="$row['status']->value" />
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center; color:var(--text-secondary); padding:24px 12px;">
                            Tidak ada data produk.
                        </td>
                    </tr>
                @endforelse
            </x-tabel-data>

            <footer class="inventory__list-footer" data-pagination-controls hidden>
                <p class="inventory__list-count" data-pagination-count aria-live="polite"></p>
                <div class="inventory__list-actions">
                    <button class="button" type="button" data-pagination-view-all>Lihat semua</button>
                    <nav class="pagination inventory__list-pagination" aria-label="Halaman masa simpan" data-pagination-nav>
                    </nav>
                </div>
            </footer>
        </div>
    </section>

    {{-- manajemen stok --}}
    @php
        $worstSlotStatus = 'aman';
        foreach ($inventory['slots'] as $s) {
            if ($s['status']->value === 'habis') {
                $worstSlotStatus = 'habis';
                break;
            }
            if ($s['status']->value === 'rendah') {
                $worstSlotStatus = 'rendah';
            }
        }
        $slotsWrapperClass = match ($worstSlotStatus) {
            'habis' => 'inventory__slots--habis',
            'rendah' => 'inventory__slots--rendah',
            default => '',
        };
    @endphp

    <section class="inventory__slots {{ $slotsWrapperClass }}" aria-label="Manajemen stok slot">
        <header class="inventory__slots-header">
            <h2 class="panel__title">Manajemen Stok</h2>
            <div class="inventory__legend" aria-hidden="true">
                <x-lencana-status label="Aman" status="aman" />
                <x-lencana-status label="Rendah" status="rendah" />
                <x-lencana-status label="Habis" status="habis" />
            </div>
        </header>

        <div class="inventory__slot-grid" data-slot-grid-wrapper>
            @forelse ($inventory['slots'] as $slot)
                <article class="slot-card" data-slot-card aria-label="Slot {{ $slot['slot'] }}: {{ $slot['product'] }}">
                    <div class="slot-card__top">
                        <span class="slot-card__slot">
                            {{ $slot['slot'] }}
                            <small>Slot {{ $slot['slot'] }}</small>
                        </span>
                        <span class="slot-card__label">
                            <x-lencana-status :label="$slot['status']->label()" :status="$slot['status']->value" />
                        </span>
                    </div>

                    <p class="slot-card__product">{{ $slot['product'] }}</p>

                    <x-progress-bar :percentage="$slot['stock_percent']" :status="$slot['status']->value"
                        :label="$slot['stock'] . '/' . $slot['capacity']" />

                    <div class="slot-card__meta">
                        <span>Terakhir refill</span>
                        <strong>{{ $slot['filled_at'] ?? '—' }}</strong>
                        <span>Harga</span>
                        <strong>{{ $slot['price'] ?? '—' }}</strong>
                    </div>

                    <button class="slot-card__edit" type="button" data-open-slot="{{ $slot['slot'] }}"
                        data-slot-product="{{ $slot['product'] }}" data-slot-stock="{{ $slot['stock'] }}"
                        data-slot-price="{{ $slot['price'] }}"
                        data-slot-capacity="{{ $slot['capacity'] ?: 10 }}"
                        data-filled-at="{{ $slot['filled_at'] !== '—' ? $slot['filled_at'] : '' }}"
                        data-update-url="{{ route('inventory.slots.update', ['slot' => $slot['slot']]) }}" aria-haspopup="dialog">
                        Edit
                    </button>
                </article>
            @empty
                <p style="grid-column:1/-1; text-align:center; color:var(--text-secondary); padding:24px 0;">
                    Tidak ada slot terdaftar.
                </p>
            @endforelse
        </div>

        <footer class="inventory__slots-footer" data-slot-controls hidden>
            <p class="inventory__slot-count" data-slot-count aria-live="polite"></p>
            <div class="inventory__slot-actions">
                <button class="button" type="button" data-slot-view-all>Lihat semua</button>
                <nav class="pagination inventory__slot-pagination" aria-label="Halaman slot" data-slot-pagination>
                </nav>
            </div>
        </footer>
    </section>

    {{-- riwayat restok --}}
    <section class="surface restock" aria-label="Riwayat restok">
        <header class="restock__heading">
            <h2 class="panel__title">Riwayat Restok</h2>
        </header>

        <div data-paginated-list data-item-label="entri" data-page-size="6">
            <x-tabel-data class="restock__table" :headers="['TANGGAL & WAKTU', 'SLOT', 'PRODUK', 'JUMLAH TAMBAH']">
                @forelse ($inventory['restockHistory'] as $log)
                    <tr data-pagination-row>
                        <td>{{ $log['date'] }}</td>
                        <td>{{ $log['slot'] }}</td>
                        <td>{{ $log['product'] }}</td>
                        <td style="color:var(--safe); font-weight:600;">
                            {{ str_starts_with((string) $log['quantity'], '+') ? $log['quantity'] : '+' . $log['quantity'] }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align:center; color:var(--text-secondary); padding:24px 12px;">
                            Belum ada riwayat restok.
                        </td>
                    </tr>
                @endforelse
            </x-tabel-data>

            <footer class="inventory__list-footer" data-pagination-controls hidden>
                <p class="inventory__list-count" data-pagination-count aria-live="polite"></p>
                <div class="inventory__list-actions">
                    <button class="button" type="button" data-pagination-view-all>Lihat semua</button>
                    <nav class="pagination inventory__list-pagination" aria-label="Halaman riwayat restok"
                        data-pagination-nav>
                    </nav>
                </div>
            </footer>
        </div>
    </section>

    {{-- popup edit --}}
    <dialog class="dialog" data-slot-dialog aria-labelledby="slot-dialog-title">
        <div class="dialog__content">
            <header class="dialog__header">
                <h3 class="dialog__title" id="slot-dialog-title" data-slot-dialog-title>Edit Stok Slot</h3>
            </header>

            <form method="POST" action="#" data-slot-form>
                @csrf
                @method('PATCH')

                <div class="dialog__info" aria-label="Informasi slot (tidak dapat diedit)">
                    <div class="dialog__row dialog__info-row">
                        <span class="dialog__label">ID Slot</span>
                        <strong class="dialog__value" data-slot-id></strong>
                    </div>
                    <div class="dialog__row dialog__info-row">
                        <span class="dialog__label">Nama Produk</span>
                        <strong class="dialog__value" data-slot-product></strong>
                    </div>
                    <div class="dialog__row dialog__info-row">
                        <span class="dialog__label">Harga</span>
                        <strong class="dialog__value" data-slot-price></strong>
                    </div>
                </div>

                <div class="dialog__row">
                    <label class="dialog__label" for="slot-stock">Jumlah Stok</label>
                    <input class="dialog__input" id="slot-stock" name="stock" type="number" min="0" required
                        aria-required="true">
                </div>

                <div class="dialog__row">
                    <label class="dialog__label" for="slot-filledat">Tanggal Refill</label>
                    <input class="dialog__input" id="slot-filledat" name="filled_at" type="text"
                        placeholder="DD/MM/YYYY HH:mm" pattern="[0-9]{2}/[0-9]{2}/[0-9]{4} [0-9]{2}:[0-9]{2}"
                        maxlength="16" required aria-required="true">
                </div>

                <div class="dialog__row dialog__actions">
                    <button class="button dialog__cancel" type="button" data-close-slot>Batal</button>
                    <button class="dialog__edit button button--primary" type="submit">Simpan</button>
                </div>
            </form>
        </div>
    </dialog>

@endsection

@push('styles')
    @vite('resources/css/inventaris_stok.css')
@endpush