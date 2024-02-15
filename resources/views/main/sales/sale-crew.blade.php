<option value="">-- Pilih Sales --</option>
@foreach ($salesman as $salesTeam)
    <option value="{{ $salesTeam->id }}" @optionSelected($salesTeam->id, $currentSalesmanId)>{{ $salesTeam->name }}</option>
@endforeach