@extends('layouts.master')

@section('content')
    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">TODO Dashboard</h1>

    <!-- Version Info -->
    <h5>Cardano CLI Version</h5>
    <div class="card">
        <div class="card-body pb-0">
            <pre>{{ $cardanoCliVersion }}</pre>
        </div>
    </div>
@endsection
