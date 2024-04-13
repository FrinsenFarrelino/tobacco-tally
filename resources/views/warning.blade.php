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
                            <h4> {{ __('warning')['title'] }}</h4>
                            <p>{{ __('warning')['content'] }}.</p>
                            <a class="btn btn-primary" href="/">{{ __('warning')['back'] }}</a>

                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12">
                        <img class="w-100 move-2" src="images/error.png" alt="">
                    </div>
                </div>
            </div>
        </div>
        <?php echo renderBaseModel(); ?>
    </main>

    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('js/dlabnav-init.js') }}"></script>
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            <?php echo renderOpenModal() ?>
            var modalHeader = '';
            var modalBody = '';
            var modalFooter = '';
            $.ajax({
                url: "{{ route('call-helper-function') }}",
                method: 'GET',
                dataType: 'json',
                data: {
                    type: 'alert',
                    class_icon: 'fa-solid fa-triangle-exclamation fa-beat-fade',
                    color: "#FF0000",
                    title: "Warning",
                    text: "You do not have permission to view this resource.",
                    text_button_cancel: "Close",
                    text_button_ok: "Back to index"
                },
                success: function(response) {
                    // Handle the response from the server
                    console.log(response);
                    modalBody = response.body_content;
                    modalFooter = response.footer;
    
                    openModal(modalHeader, modalBody, modalFooter);
                },
                error: function(xhr, status, error, response) {
                    console.error(error);
                    var errorMessage = xhr.status + ': ' + xhr.statusText + ". Error: " + error;
                    alert('Error - ' + errorMessage);
                }
            });
        });
    </script>

</body>

</html>