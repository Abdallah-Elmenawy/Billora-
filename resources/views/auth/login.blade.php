@extends('layouts.master2')

@section('css')
    <!-- Sidemenu-responsive-tabs css -->
    <link href="{{ URL::asset('assets/plugins/sidemenu-responsive-tabs/css/sidemenu-responsive-tabs.css') }}"
        rel="stylesheet">
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">

            <!-- Image Half -->
            {{-- <div class="col-md-6 col-lg-6 col-xl-7 d-none d-md-flex bg-primary-transparent">

                <div class="row wd-100p mx-auto text-center">

                    <div class="col-md-12 col-lg-12 col-xl-12 my-auto mx-auto wd-100p">

                        <img src="{{ URL::asset('assets/img/media/login.png') }}"
                            class="my-auto ht-xl-80p wd-md-100p wd-xl-80p mx-auto" alt="logo">

                    </div>

                </div>

            </div> --}}

            <!-- Content Half -->
            <div class="col-md-6 col-lg-6 col-xl-5 bg-white ">

                <div class="login d-flex align-items-center py-2">

                    <div class="container p-0">

                        <div class="row">

                            <div class="col-md-10 col-lg-10 col-xl-9 mx-auto">

                                <div class="card-sigin">

                                    <!-- Logo -->
                                    <div class="mb-5 d-flex">

                                        {{-- <a href="{{ url('/') }}">
                                            <img src="{{ URL::asset('assets/img/brand/favicon.png') }}"
                                                class="sign-favicon ht-40" alt="logo">
                                        </a> --}}

                                        <h1 class="main-logo1 ml-1 mr-0 my-auto tx-28">
                                            Bill<span>ora</span>
                                        </h1>

                                    </div>

                                    <div class="card-sigin">

                                        <div class="main-signup-header">

                                            <!-- Session Status -->
                                            <x-auth-session-status class="mb-4" :status="session('status')" />

                                            <h2>مرحبا لعوتك لبرنامج حساباتك !</h2>

                                            <h5 class="font-weight-semibold mb-4">
                                                من فضلك سجل الدخول للمتابعة.
                                            </h5>


                                            <!-- Login Form -->
                                            <form method="POST" action="{{ route('login') }}">

                                                @csrf


                                                <!-- Email -->
                                                <div class="form-group">

                                                    <label for="email">
                                                        البريد أو اسم المستخدم
                                                    </label>

                                                    <input id="email" class="form-control"
                                                        placeholder="admin أو admin@billora.test" type="text" name="email"
                                                        value="{{ old('email') }}" required autofocus
                                                        autocomplete="username">

                                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />

                                                </div>


                                                <!-- Password -->
                                                <div class="form-group">

                                                    <label for="password">
                                                        كلمة المرور
                                                    </label>

                                                    <input id="password" class="form-control" placeholder="كلمة المرور"
                                                        type="password" name="password" required
                                                        autocomplete="current-password">

                                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />

                                                </div>


                                                <!-- Remember Me -->
                                                <div class="form-group">

                                                    <label class="d-flex align-items-center">

                                                        <input type="checkbox" name="remember" class="ml-2"
                                                            style="margin-right: 8px;" id="remember_me">

                                                        <span>
                                                            تذكرني
                                                        </span>

                                                    </label>

                                                </div>


                                                <!-- Sign In -->
                                                <button type="submit" class="btn btn-main-primary btn-block">
                                                    سجل الدخول
                                                </button>


                                            </form>


                                            <!-- Footer -->
                                            <div class="main-signin-footer mt-5">

                                                @if (Route::has('password.request'))
                                                    <p>
                                                        <a href="{{ route('password.request') }}">
                                                            نسيت كلمة المرور؟
                                                        </a>
                                                    </p>
                                                @endif

                                                <p>
                                                    ليس لديك حساب؟

                                                    <a href="{{ route('register') }}">
                                                        انشئ حساباً
                                                    </a>
                                                </p>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>
@endsection

@section('js')
@endsection
