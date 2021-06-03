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
                    <div class="card-body p-0">

                        <!-- Alerts -->
                        @include('partials.alerts', ['noMargin' => true])

                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Back</h1>
                                    </div>
                                    <form action="{{ route('login-handler') }}" method="post" class="user">
                                        @csrf
                                        <div class="form-group">
                                            <input type="email" class="form-control form-control-user"
                                                   name="email" aria-describedby="emailHelp"
                                                   placeholder="Enter Admin Account Email" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user"
                                                   name="password" aria-describedby="passwordHelp"
                                                   placeholder="Enter Admin Account Password" required>
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" name="remember" id="remember" value="yes">
                                                <label class="custom-control-label" for="remember">
                                                    Remember Me
                                                </label>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Login
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-xl-10 col-lg-12 col-md-9 text-center text-white">
                Powered by
                <a href="https://github.com/adosia/Heidrun" target="_blank" class="text-white">Heidrun</a>
                &copy; {{ date('Y') }}
                <br>
                <small>
                    Developed by
                    <a href="https://github.com/latheesan-k" target="_blank" class="text-white">Latheesan Kanesamoorthy</a>
                    &amp;
                    <a href="https://github.com/adosia/Heidrun/graphs/contributors" target="_blank" class="text-white">community contributors</a>
                </small>
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
