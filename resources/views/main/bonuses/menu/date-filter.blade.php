<div class="d-block border w-100 rounded-3 bg-gray-100 p-3" id="bonus-filter-date">
    <div class="row g-2">
        @php
            $endDate = formatFullDate(date('Y-m-d'));
        @endphp
        <div class="col-sm-6">
            <div>Dari Tanggal</div>
            <div class="input-group">
                <input type="text" id="start-date" class="form-control bg-white bs-date" value="@formatFullDate($dateRange['start'])" data-date-format="d MM yyyy" data-date-end-date="{{ $endDate }}" placeholder="Tanggal" autocomplete="off" readonly="">
                <label for="start-date" class="input-group-text cursor-pointer">
                    <i class="fa-solid fa-calendar-days"></i>
                </label>
            </div>
        </div>
        <div class="col-sm-6">
            <div>Sampai Tanggal</div>
            <div class="input-group">
                <input type="text" id="end-date" class="form-control bg-white bs-date" value="@formatFullDate($dateRange['end'])" data-date-format="d MM yyyy" data-date-end-date="{{ $endDate }}" placeholder="Tanggal" autocomplete="off" readonly="">
                <label for="end-date" class="input-group-text cursor-pointer">
                    <i class="fa-solid fa-calendar-days"></i>
                </label>
            </div>
        </div>
        @if (isset($otherContent) && !empty($otherContent))
            {!! $otherContent !!}
        @endif
    </div>
</div>