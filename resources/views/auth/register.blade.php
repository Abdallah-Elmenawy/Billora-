@extends('layouts.master2')

@section('css')
    <!-- Sidemenu-responsive-tabs css -->
    <link href="{{ URL::asset('assets/plugins/sidemenu-responsive-tabs/css/sidemenu-responsive-tabs.css') }}"
        rel="stylesheet">
@endsection

@section('content')
    <div class="container-fluid">

        <div class="row no-gutter">

            <!-- Image Half -->
            <div class="col-md-6 col-lg-6 col-xl-7 d-none d-md-flex bg-primary-transparent">

                <div class="row wd-100p mx-auto text-center">

                    <div class="col-md-12 col-lg-12 col-xl-12 my-auto mx-auto wd-100p">

                        <img src="{{ URL::asset('assets/img/media/login.png') }}"
                            class="my-auto ht-xl-80p wd-md-100p wd-xl-80p mx-auto" alt="logo">

                    </div>

                </div>

            </div>
            <!-- End Image Half -->


            <!-- Content Half -->
            <div class="col-md-6 col-lg-6 col-xl-5 bg-white">

                <div class="login d-flex align-items-center py-2">

                    <!-- Content -->
                    <div class="container p-0">

                        <div class="row">

                            <div class="col-md-10 col-lg-10 col-xl-9 mx-auto">

                                <div class="card-sigin">

                                    <!-- Logo -->
                                    <div class="mb-5 d-flex">

                                        <a href="{{ url('/' . ($page = 'index')) }}">
                                            <img src="{{ URL::asset('assets/img/brand/favicon.png') }}"
                                                class="sign-favicon ht-40" alt="logo">
                                        </a>

                                        <h1 class="main-logo1 ml-1 mr-0 my-auto tx-28">
                                            Va<span>le</span>x
                                        </h1>

                                    </div>
                                    <!-- End Logo -->


                                    <div class="main-signup-header">

                                        <h2 class="text-primary">
                                            إنشاء حساب
                                        </h2>

                                        <h5 class="font-weight-normal mb-4">
                                            أنشئ حسابك مجانًا، ولن يستغرق الأمر سوى دقيقة.
                                        </h5>


                                        <!-- Register Form -->
                                        <form method="POST" action="{{ route('register') }}">

                                            @csrf


                                            <!-- Name -->
                                            <div class="form-group">

                                                <label>
                                                    الاسم
                                                </label>

                                                <input class="form-control @error('name') is-invalid @enderror"
                                                    type="text" name="name" value="{{ old('name') }}"
                                                    placeholder="أدخل الاسم" required autofocus autocomplete="name">

                                                @error('name')
                                                    <span class="text-danger">
                                                        {{ $message }}
                                                    </span>
                                                @enderror

                                            </div>


                                            <!-- Email -->
                                            <div class="form-group">

                                                <label>
                                                    البريد الإلكتروني
                                                </label>

                                                <input class="form-control @error('email') is-invalid @enderror"
                                                    type="email" name="email" value="{{ old('email') }}"
                                                    placeholder="أدخل البريد الإلكتروني" required autocomplete="username">

                                                @error('email')
                                                    <span class="text-danger">
                                                        {{ $message }}
                                                    </span>
                                                @enderror

                                            </div>


                                            <!-- Password -->
                                            <div class="form-group">

                                                <label>
                                                    كلمة المرور
                                                </label>

                                                <input class="form-control @error('password') is-invalid @enderror"
                                                    type="password" name="password" placeholder="أدخل كلمة المرور" required
                                                    autocomplete="new-password">

                                                @error('password')
                                                    <span class="text-danger">
                                                        {{ $message }}
                                                    </span>
                                                @enderror

                                            </div>


                                            <!-- Confirm Password -->
                                            <div class="form-group">

                                                <label>
                                                    تأكيد كلمة المرور
                                                </label>

                                                <input
                                                    class="form-control @error('password_confirmation') is-invalid @enderror"
                                                    type="password" name="password_confirmation"
                                                    placeholder="أعد إدخال كلمة المرور" required
                                                    autocomplete="new-password">

                                                @error('password_confirmation')
                                                    <span class="text-danger">
                                                        {{ $message }}
                                                    </span>
                                                @enderror

                                            </div>


                                            <!-- Register Button -->
                                            <button type="submit" class="btn btn-main-primary btn-block">
                                                إنشاء الحساب
                                            </button>

                                        </form>
                                        <!-- End Register Form -->


                                        <!-- Login Link -->
                                        <div class="main-signup-footer mt-5">

                                            <p>
                                                لديك حساب بالفعل؟

                                                <a href="{{ route('login') }}">
                                                    تسجيل الدخول
                                                </a>
                                            </p>

                                        </div>
                                        <!-- End Login Link -->

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>
                    <!-- End Content -->

                </div>

            </div>
            <!-- End Content Half -->

        </div>

    </div>
@endsection


@section('js')
@endsection
