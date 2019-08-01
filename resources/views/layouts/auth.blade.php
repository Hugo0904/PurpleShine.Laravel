<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>
            321321
        </title>
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <meta name="robots" content="noindex, nofollow">

        <link rel="stylesheet" href="{{ mix('css/app.css') }}">
        @yield('style')
    </head>
    <body class="hold-transition login-page">
        <div id="app" class="login-box">

            @yield('content')

        </div>

        <script src="{{ mix('js/app.js') }}"></script>
        @yield('script')
    </body>
</html>
