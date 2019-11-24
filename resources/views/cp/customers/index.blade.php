@extends('statamic::layout')
@section('title', 'Customers')

@section('content')
    <div class="flex mb-3">
        <h1 class="flex-1">Customers</h1>

        <a href="{{ cp_route('customers.create') }}" class="btn btn-primary">Create Customer</a>
    </div>

    <commerce-listing
            model="customers"
            cols='{{ json_encode([
            [
                'label' => 'Name',
                'field' => 'name',
            ],
            [
                'label' => 'Email Address',
                'field' => 'email'
            ]
        ]) }}'
            items='@json($customers)'
    />
@endsection