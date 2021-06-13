@extends('layouts.master')

@section('content')
    <!-- Create New Payment Wallet -->
    <form action="{{ route('payment-wallets.create-wallet') }}" method="post">
        @csrf
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    Create New Payment Wallet
                </h6>
            </div>
            <div class="card-body">

                <div class="mb-3">
                    <label for="name" class="form-label">Wallet Name</label>
                    <input id="name" name="name" value="{{ old('name') }}" aria-describedby="nameHelp" type="text" class="form-control" placeholder="Enter wallet name..." required autofocus>
                    <div id="nameHelp" class="form-text">Only alpha numeric characters (a-z, A-Z and 0-9) are allowed</div>
                    @error('name')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="type" class="form-label">Wallet Type</label>
                    <input id="type" value="{{ WALLET_TYPE_PAYMENT }}" type="text" class="form-control" readonly>
                </div>

                <div class="mb-3">
                    <label for="created_by" class="form-label">Created By</label>
                    <input id="created_by" value="{{ auth()->user()->name }}" type="text" class="form-control" readonly>
                </div>

            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    Create
                </button>
            </div>
        </div>
    </form>
@endsection
