@props(['context'])

@php
    $monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $currentYear = (int) now()->format('Y');
@endphp

<div
    class="period-picker"
    data-period-picker
    data-context="{{ $context }}"
    data-picker-month=""
    data-picker-year=""
>
    <button
        class="period-picker__trigger history-alert__period-button"
        type="button"
        aria-expanded="false"
        aria-controls="period-picker-panel-{{ $context }}"
        data-period-picker-trigger
    >
        <svg viewBox="0 0 16 16" aria-hidden="true"><rect x="2" y="3.5" width="12" height="10.5" rx="1" /><path d="M5 2v3m6-3v3M2 6.5h12M5 9h2m2 0h2m-6 2h2" /></svg>
        <span>Filter Periode</span>
    </button>
    <div class="period-picker__panel" id="period-picker-panel-{{ $context }}" role="dialog" aria-label="Pilih bulan dan tahun" hidden>
        <div class="period-picker__group">
            <h2>Bulan</h2>
            <div class="period-picker__grid period-picker__grid--months">
                @foreach ($monthNames as $monthIndex => $monthName)
                    <button
                        type="button"
                        data-picker-month-option="{{ $monthIndex + 1 }}"
                        aria-pressed="false"
                    >{{ $monthName }}</button>
                @endforeach
            </div>
        </div>
        <div class="period-picker__group">
            <h2>Tahun</h2>
            <div class="period-picker__grid period-picker__grid--years">
                @foreach (range($currentYear - 2, $currentYear + 3) as $year)
                    <button
                        type="button"
                        data-picker-year-option="{{ $year }}"
                        aria-pressed="false"
                    >{{ $year }}</button>
                @endforeach
            </div>
        </div>
        <button class="period-picker__clear" type="button" data-clear-period>Hapus filter periode</button>
    </div>
</div>
