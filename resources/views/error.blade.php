<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="DexignLab">
    <meta name="robots" content="">
    <meta name="keywords" content="bootstrap admin, card, clean, credit card, dashboard template, elegant, invoice, modern, money, transaction, Transfer money, user interface, wallet">
    <meta name="description" content="Dompet is a clean-coded, responsive HTML template that can be easily customised to fit the needs of various credit card and invoice, modern, creative, Transfer money, and other businesses.">
    <meta property="og:title" content="Dompet - Payment Admin Dashboard Bootstrap Template">
    <meta property="og:description" content="Dompet is a clean-coded, responsive HTML template that can be easily customised to fit the needs of various credit card and invoice, modern, creative, Transfer money, and other businesses.">
    <meta property="og:image" content="social-image.png">
    <meta name="format-detection" content="telephone=no">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="{{ asset('assets/modules/bootstrap/css/bootstrap.min.css') }}">
    <link rel="shortcut icon" type="image/png" href="{{ url('images/favicon.png') }}" />
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet" />
</head>

<body class="vh-100">
    <main>
        <div class="authincation h-100">
            <div class="container h-100">
                <div class="row h-100 align-items-center">
                    <div class="col-lg-6 col-sm-12">
                        <div class="form-input-content  error-page">
                            <h1 class="error-text text-primary">500</h1>
                            <h4> {{ __('error')['title'] }}</h4>
                            <p>{{ __('error')['content'] }}.</p>
                            <a class="btn btn-primary" href="{{ route('logout') }}">{{ __('error')['back'] }}</a>

                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12">
                        <img class="w-100 move-2" src="images/error.png" alt="">
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('js/dlabnav-init.js') }}"></script>
    <script src="{{ asset('js/jquery.min.js') }}"></script>
</body>

</html>