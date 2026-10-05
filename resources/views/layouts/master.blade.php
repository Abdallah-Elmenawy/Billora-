<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layouts.head')
</head>
<body class="main-body app sidebar-mini cairo">
    <div id="global-loader">
        <img src="{{ URL::asset('assets/img/loader.svg') }}" class="loader-img" alt="Loader">
    </div>
    @include('layouts.sidebar')
    <div class="main-content app-content">
        @include('layouts.main-header')
        <div class="container-fluid">
            @yield('page-header')
            @include('admin.partials.alerts')
            @yield('content')
        </div>
    </div>
    @include('layouts.models')
    @include('layouts.confirm-delete-modal')
    @include('layouts.footer')
    @include('layouts.footer-scripts')
</body>
</html>
