@extends('layouts.app')
 
@section('title', 'Point of Sale')
@section('page-title', 'Point of Sale')
@section('page-subtitle', 'Select items and process orders')
 
@push('styles')
<style>
    /* Override default padding for POS — it needs full height */
    main.page-content { padding: 0 !important; overflow: hidden; }
</style>
@endpush
 
@section('content')
    @livewire('pos-cart')
@endsection