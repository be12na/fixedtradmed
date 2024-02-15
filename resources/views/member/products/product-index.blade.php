@extends('layouts.app-member')

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
            @include('member.products.product-list', ['products' => $category->products])
        </div>
        @php
            $categoryNo++;
        @endphp
    @endforeach
</div>
@endsection
