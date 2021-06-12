<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Latheesan Kanesamoorthy">
    <title>Heidrun</title>
    <!-- Fonts -->
    <link href="/css/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Styles -->
    <link href="/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="/css/custom.css?_={{ filemtime(public_path('css/custom.css')) }}" rel="stylesheet">
</head>
<body class="bg-gradient-primary">

<div class="container">

    <!-- Outer Row -->
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12 col-md-9">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body text-center">
                    <h1>Heidrun Environment Error</h1>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $errorMessage }}
                    </div>
                    <a class="btn btn-primary" href="{{ route('home') }}">
                        Re-check Environment
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-10 col-lg-12 col-md-9 text-center text-white">
            @include('layouts.partials.powered-by', ['white' => true])
        </div>
    </div>

</div>

<!-- JavaScripts-->
<script src="/js/jquery/jquery.min.js"></script>
<script src="/js/bootstrap/bootstrap.bundle.min.js"></script>
<script src="/js/jquery-easing/jquery.easing.min.js"></script>
<script src="/js/sb-admin-2.min.js"></script>

</body>
</html>
