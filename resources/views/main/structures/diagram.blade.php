@extends('layouts.app-main')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/tree-structure.css') }}">
@endpush

@section('content')
<div class="trees-container border">
    <div class="trees">
        {{ $diagram }}
    </div>
</div>
@endsection