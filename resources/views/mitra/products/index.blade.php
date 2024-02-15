@extends('layouts.app-mitra')

@section('content')
<div class="d-block fs-auto">
    @include('partials.alert')
    @php
        $countCategory = $categories->count();
        $categoryNo = 1;
    @endphp
    @foreach ($categories as $category)
        @php
            $mb = ($categoryNo >= $countCategory) ? '' : 'mb-3';
        @endphp
        <div class="d-block mb-2 border-bottom fw-bold">{{ $category->name }}</div>
        <div class="row g-3 {{ $mb }}" id="products-container">
            @include('mitra.products.list', ['products' => $category->products, 'zone' => $zone])
        </div>
        @php
            $categoryNo++;
        @endphp
    @endforeach
</div>
@endsection
