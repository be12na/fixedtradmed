@extends('layouts.app-main')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/tree-structure.css') }}">
@endpush

@section('content')
<div class="trees-container border">
    <div class="trees">
        {{ $treeStructure }}
    </div>
</div>
@endsection