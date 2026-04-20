@extends('layouts.app')

@section('title', 'New Menu Item')
@section('page-title', 'New Menu Item')
@section('page-subtitle', 'Build a recipe and set your pricing')

@section('content')

<form method="POST"
      action="{{ route('menu-items.store') }}"
      enctype="multipart/form-data">
    @csrf

    @include('menu-items._form', [
        'item'       => null,
        'ingredients'=> $ingredients,
        'categories' => $categories,
    ])
</form>

@endsection