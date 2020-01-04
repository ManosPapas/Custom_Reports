
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    @include('includes/css')
</head>

<body>
    {{-- Use it for JS purposes --}}
    <input type="hidden" id="current_locale" value="{{ app()->getLocale() }}">

    <div id="app" class="wrapper">        
        <div id="main-content-wrapper" class="content-wrapper flex-column mh-100">
            @yield('content')
        </div>        
    </div>

    <!-- Scripts -->
    @include('includes/scripts')
    @include('includes/js')
    @yield('extra_scripts')
</body>
</html>
