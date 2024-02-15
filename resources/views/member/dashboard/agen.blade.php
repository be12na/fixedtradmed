@extends('layouts.app-member')

@section('content')
@include('partials.alert')
{{-- @referralLinkHtml() --}}
@endsection

@push('scripts')
{{-- @referralLinkScript() --}}
@endpush