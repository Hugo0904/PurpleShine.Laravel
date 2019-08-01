<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>

        </title>
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <meta name="robots" content="noindex, nofollow">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="stylesheet" href="{{ mix('css/app.css') }}">
        @yield('style')
    </head>
    <body class="hold-transition skin-blue sidebar-mini">
        <div class="wrapper">
            @include('includes.header')
            @include('includes.sidebar')

            <div class="content-wrapper">
                @yield('content')
            </div>

            @include('includes.footer')

            <div class="control-sidebar-bg"></div>
        </div>
        <div id="js-lang-another_login" class="hidden">{{trans('auth.another_login')}}</div>


            <script src="{{ mix('js/app.js') }}"></script>
            {{--<script src="{{ asset("/js/index.js") }}?v={{time()}}"></script>--}}

            @yield('script')
    </body>
</html>
