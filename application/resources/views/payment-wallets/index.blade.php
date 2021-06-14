@extends('layouts.master')

@push('styles')
    <link href="/js/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
@endpush

@push('breadcrumbs')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('payment-wallets.index') }}">Payment Wallets</a></li>
            <li class="breadcrumb-item active" aria-current="page">Wallet List</li>
        </ol>
    </nav>
@endpush

@section('content')
    <!-- Page Info -->
    <p class="mb-4">
        These wallets are where expected payments/dust transactions should arrive.
        You should show these wallet addresses to your users & ask them to send payments/dust.
    </p>

    <!-- Wallet List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <a href="{{ route('payment-wallets.create-form') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-plus-circle fa-sm text-white-50"></i>
                Create
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" style="width: 100%; border-spacing: 0;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Network</th>
                            <th>Created By</th>
                            <th>Created On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($walletList as $wallet)
                            <tr>
                                <td>{{ $wallet->id }}</td>
                                <td>{{ $wallet->name }}</td>
                                <td>
                                    <a title="{{ $wallet->address }}" href="{{ route('payment-wallets.show', $wallet->id) }}">
                                        {{ \Illuminate\Support\Str::limit($wallet->address, 20) }}
                                    </a>
                                </td>
                                <td>{!! $wallet->network_badge !!}</td>
                                <td>{{ $wallet->createdByUser->name }}</td>
                                <td>{{ $wallet->created_at->diffForHumans() }}</td>
                                <td>
                                    <a href="{{ route('payment-wallets.show', $wallet->id) }}" class="badge bg-primary text-white">
                                        <i class="fas fa-eye fa-sm text-white-50"></i>
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="/js/datatables/jquery.dataTables.min.js"></script>
    <script src="/js/datatables/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable();
        });
    </script>
@endpush
