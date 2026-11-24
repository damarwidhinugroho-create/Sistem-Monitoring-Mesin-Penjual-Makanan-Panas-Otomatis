@php
    $today = now('Asia/Jakarta')->format('Y-m-d');
    [$todayYear, $todayMonth, $todayDay] = array_map('intval', explode('-', $today));
    $availableYears = range($todayYear - 2, $todayYear);
    $months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];
@endphp

<div class="date-range-picker" data-date-range-picker data-start-date="{{ $today }}" data-end-date="{{ $today }}">
    <button
        class="period-picker__trigger report__custom-button"
        type="button"
        aria-expanded="false"
        aria-controls="report-date-range-panel"
        data-date-range-trigger
    >
        <span>Custom Tanggal</span>
        <svg class="period-picker__chevron" viewBox="0 0 12 12" aria-hidden="true"><path d="m2.5 4.5 3.5 3 3.5-3" /></svg>
    </button>
    <div class="date-range-picker__panel" id="report-date-range-panel" role="group" aria-label="Pilih rentang tanggal" hidden>
        <div class="date-range-picker__group">
            <span>Dari</span>
            <label>
                <span class="visually-hidden">Tanggal mulai</span>
                <select data-range-start-day aria-label="Tanggal mulai">
                    @foreach (range(1, 31) as $day)
                        <option value="{{ $day }}" @selected($day === $todayDay)>{{ $day }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="visually-hidden">Bulan mulai</span>
                <select data-range-start-month aria-label="Bulan mulai">
                    @foreach ($months as $index => $month)
                        <option value="{{ $index + 1 }}" @selected($index + 1 === $todayMonth)>{{ $month }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="visually-hidden">Tahun mulai</span>
                <select data-range-start-year aria-label="Tahun mulai">
                    @foreach ($availableYears as $year)
                        <option value="{{ $year }}" @selected($year === $todayYear)>{{ $year }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        <div class="date-range-picker__group">
            <span>Sampai</span>
            <label>
                <span class="visually-hidden">Tanggal akhir</span>
                <select data-range-end-day aria-label="Tanggal akhir">
                    @foreach (range(1, 31) as $day)
                        <option value="{{ $day }}" @selected($day === $todayDay)>{{ $day }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="visually-hidden">Bulan akhir</span>
                <select data-range-end-month aria-label="Bulan akhir">
                    @foreach ($months as $index => $month)
                        <option value="{{ $index + 1 }}" @selected($index + 1 === $todayMonth)>{{ $month }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="visually-hidden">Tahun akhir</span>
                <select data-range-end-year aria-label="Tahun akhir">
                    @foreach ($availableYears as $year)
                        <option value="{{ $year }}" @selected($year === $todayYear)>{{ $year }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        <p class="date-range-picker__error" role="alert" data-range-error hidden></p>
        <button type="button" data-clear-period>Hapus filter periode</button>
    </div>
</div>
