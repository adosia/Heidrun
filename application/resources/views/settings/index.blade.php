@extends('layouts.master')

@push('breadcrumbs')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('settings.index') }}">Settings</a></li>
            <li class="breadcrumb-item active" aria-current="page">Summary</li>
        </ol>
    </nav>
@endpush

@section('content')
    <!-- Heidrun Settings -->
    <form action="{{ route('settings.update') }}" method="post">
        @csrf
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    Configure Heidrun
                </h6>
            </div>
            <div class="card-body">

                <div class="mb-3">
                    <label for="api_access_token" class="form-label">API Access Token</label>
                    <div class="input-group">
                        <input id="api_access_token" name="api_access_token" value="{{ old('api_access_token', $allSettings->where('key', 'api_access_token')->first()->value ?? '') }}" aria-describedby="apiAccessTokenHelp" type="text" class="form-control" placeholder="Not Set" required autofocus>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick='document.getElementById("api_access_token").value = Password.generate(16)'>
                                <i class="fas fa-sync"></i>
                                Generate Random
                            </button>
                        </div>
                    </div>
                    <div id="apiAccessTokenHelp" class="form-text">Must be at least 16 characters; containers letters, numbers, and at least one symbol <code>._-+,@!~#=*()^&$!</code></div>
                    @error('api_access_token')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    Update
                </button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        /**
         * Based on https://stackoverflow.com/a/26528271/2332336
         */
        var Password = {
            _pattern : /[a-zA-Z0-9]/,
            _getRandomByte : function()
            {
                if (window.crypto && window.crypto.getRandomValues) {
                    var result = new Uint8Array(1);
                    window.crypto.getRandomValues(result);
                    return result[0];
                } else if(window.msCrypto && window.msCrypto.getRandomValues) {
                    var result = new Uint8Array(1);
                    window.msCrypto.getRandomValues(result);
                    return result[0];
                } else {
                    return Math.floor(Math.random() * 256);
                }
            },
            generate : function(length)
            {
                var randomPass = Array.apply(null, {'length': length - 2})
                    .map(function()
                    {
                        var result;
                        while (true) {
                            result = String.fromCharCode(this._getRandomByte());
                            if (this._pattern.test(result)) {
                                return result;
                            }
                        }
                    }, this)
                    .join('');
                var symbolList = '._-+,@!~#=*()^&$!';
                randomPass = (
                    randomPass +
                    symbolList.charAt(Math.floor(Math.random() * symbolList.length)) +
                    symbolList.charAt(Math.floor(Math.random() * symbolList.length))
                );
                return randomPass.split('').sort(function() {
                    return 0.5 - Math.random()
                }).join('')
            }
        };
    </script>
@endpush
