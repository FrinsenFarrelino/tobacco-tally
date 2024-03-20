<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests" />
    <title>Dashboard | </title>
    @include('template.style')
</head>

<body>
    <div id="preloader">
        <div class="waviy">
            <span style="--i:1">L</span>
            <span style="--i:2">o</span>
            <span style="--i:3">a</span>
            <span style="--i:4">d</span>
            <span style="--i:5">i</span>
            <span style="--i:6">n</span>
            <span style="--i:7">g</span>
            <span style="--i:8">.</span>
            <span style="--i:9">.</span>
            <span style="--i:10">.</span>
        </div>
    </div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            @include('template.header')
            @include('template.sidebar')
            <!-- Main Content -->
            <div class="main-content">
                @yield('content')
            </div>
            <footer class="main-footer">
                <div class="footer-left">
                    Copyright &copy; 2024 <div class="bullet"></div> TA <a href="https://www.linkedin.com/in/agustinus-frinsen-farrelino-yoses/">Agustinus Frinsen Farrelino Yoses</a>
                </div>
                <div class="footer-right">

                </div>
            </footer>
        </div>
    </div>
    @include('template.script')
</body>

</html>