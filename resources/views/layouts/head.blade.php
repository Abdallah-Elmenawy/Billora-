<!-- Title -->
<title>{{ optional(company())->name ?? 'Billora' }} — نظام محاسبي</title>
<!-- Favicon -->
<link rel="icon" href="{{ URL::asset('assets/img/brand/favicon.png') }}" type="image/x-icon" />
<!-- Cairo Font from Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:slnt,wght@-11..11,200..1000&display=swap" rel="stylesheet">
<!-- Icons css -->
<link href="{{ URL::asset('assets/css/icons.css') }}" rel="stylesheet">
<!--  Custom Scroll bar-->
<link href="{{ URL::asset('assets/plugins/mscrollbar/jquery.mCustomScrollbar.css') }}" rel="stylesheet" />
<!--  Sidebar css -->
<link href="{{ URL::asset('assets/plugins/sidebar/sidebar.css') }}" rel="stylesheet">
<!-- Sidemenu css -->
<link rel="stylesheet" href="{{ URL::asset('assets/css-rtl/sidemenu.css') }}">
@yield('css')
<!--- Style css -->
<link href="{{ URL::asset('assets/css-rtl/style.css') }}" rel="stylesheet">
<!---Skinmodes css-->
<link href="{{ URL::asset('assets/css-rtl/skin-modes.css') }}" rel="stylesheet">
<link href="{{ URL::asset('assets/css/cairo.css') }}" rel="stylesheet">
<!--- Billora theme -->
<link href="{{ URL::asset('assets/css/billora-theme.css') }}" rel="stylesheet">
