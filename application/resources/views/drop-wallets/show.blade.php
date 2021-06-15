@extends('layouts.master')

@push('breadcrumbs')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('drop-wallets.index') }}">Drop Wallets</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $wallet->name }}</li>
        </ol>
    </nav>
@endpush

@section('content')
    <!-- Wallet Info -->
    <div class="card shadow mb-4">
        <a href="#wallet-info" aria-controls="wallet-info" class="d-block card-header py-3" data-toggle="collapse" role="button" aria-expanded="true">
            <h6 class="m-0 font-weight-bold text-primary">
                Wallet Info
            </h6>
        </a>
        <div id="wallet-info" class="collapse show">
            <div class="card-body">
                <div class="mb-3">
                    <label for="wallet_address" class="form-label">Wallet Address</label>
                    <input id="wallet_address" value="{{ $wallet->address }}" type="text" class="form-control" readonly>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="type" class="form-label">Wallet Type</label>
                            <input id="type" value="{{ $wallet->type }}" type="text" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="created_by" class="form-label">Created By</label>
                            <input id="created_by" value="{{ $wallet->createdByUser->name }} ({{ $wallet->createdByUser->email }})" type="text" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="cardano_network" class="form-label">Cardano Network</label>
                            <input id="cardano_network" value="{{ ucfirst($wallet->network) }}" type="text" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="created_at" class="form-label">Created At</label>
                            <input id="created_at" value="{{ $wallet->created_at->format('d/m/Y H:i:s') }} ({{ $wallet->created_at->diffForHumans() }})" type="text" class="form-control" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Wallet UTXOs -->
    @if (!count($addressUTXOs))
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            There are no UTXOs found on this wallet address or failed to load them.
        </div>
    @else
        <h6 class="mb-3">
            Found
            <strong>{{ count($addressUTXOs) }} {{ \Illuminate\Support\Str::plural('UTXO', count($addressUTXOs)) }}</strong>
            on this wallet address
        </h6>
        @foreach ($addressUTXOs as $rowIndex => $addressUTXO)
            <div class="card shadow mb-4">
                <a href="#row-{{ $rowIndex }}" aria-controls="row-{{ $rowIndex }}" class="d-block card-header py-3" data-toggle="collapse" role="button" aria-expanded="true">
                    <h6 class="m-0 font-weight-bold text-primary">
                        {{ $addressUTXO['tx_hash'] }}#{{ $addressUTXO['tx_index'] }}
                    </h6>
                </a>
                <div id="row-{{ $rowIndex }}" class="collapse show">
                    <div class="card-body">
                        @foreach ($addressUTXO['amount'] as $amount)
                            @if ($amount['unit'] == 'lovelace')
                                {!! parseADAInfo($amount) !!}
                            @else
                                {!! parseAssetInfo($amount) !!}
                            @endif
                        @endforeach
                        <a href="{{ txExplorerUrl($addressUTXO['tx_hash']) }}" target="_blank" class="btn btn-outline-primary mr-2">
                            <i class="fas fa-external-link-square-alt"></i>
                            View Transaction
                        </a>
                        <a href="{{ blockExplorerUrl($addressUTXO['block']) }}" target="_blank" class="btn btn-outline-primary">
                            <i class="fas fa-external-link-square-alt"></i>
                            View Block
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
@endsection
