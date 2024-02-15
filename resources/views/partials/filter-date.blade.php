@php
    $endDate = formatFullDate(date('Y-m-d'));
    $containerStartClass = isset($containerStartClass) ? $containerStartClass : 'col-sm-6 col-md-3';
    $containerEndClass = isset($containerEndClass) ? $containerEndClass : 'col-sm-6 col-md-3';
@endphp
<div class="{{ $containerStartClass }}">
    <div>Dari Tanggal</div>
    <div class="input-group">
        <input type="text" id="start-date" class="form-control bg-white bs-date" value="@formatFullDate($dateRange['start'])" data-date-format="d MM yyyy" data-date-end-date="{{ $endDate }}" placeholder="Tanggal" autocomplete="off" readonly="">
        <label for="start-date" class="input-group-text cursor-pointer">
            <i class="fa-solid fa-calendar-days"></i>
        </label>
    </div>
</div>
<div class="{{ $containerEndClass }}">
    <div>Sampai Tanggal</div>
    <div class="input-group">
        <input type="text" id="end-date" class="form-control bg-white bs-date" value="@formatFullDate($dateRange['end'])" data-date-format="d MM yyyy" data-date-end-date="{{ $endDate }}" placeholder="Tanggal" autocomplete="off" readonly="">
        <label for="end-date" class="input-group-text cursor-pointer">
            <i class="fa-solid fa-calendar-days"></i>
        </label>
    </div>
</div>