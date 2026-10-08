@php
    $today = now()->format('Y-m-d');
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
        <label class="date-range-picker__field">
            <span>Tanggal Mulai</span>
            <input type="date" value="{{ $today }}" data-range-start>
        </label>
        <label class="date-range-picker__field">
            <span>Tanggal Akhir</span>
            <input type="date" value="{{ $today }}" data-range-end>
        </label>
        <p class="date-range-picker__error" role="alert" data-range-error hidden></p>
    </div>
</div>
