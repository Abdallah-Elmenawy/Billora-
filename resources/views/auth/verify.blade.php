@extends('layouts.master2')
@section('content')
<div class="container-fluid">
    <div class="row no-gutter">
        <div class="col-md-6 col-lg-6 col-xl-5 bg-white">
            <div class="login d-flex align-items-center py-2">
                <div class="container p-0">
                    <div class="row">
                        <div class="col-md-10 col-lg-10 col-xl-9 mx-auto">
                            <div class="mb-5 d-flex">
                                <h1 class="main-logo1 ml-1 mr-0 my-auto tx-28">Bill<span>ora</span></h1>
                            </div>
                            <div class="main-signin-header">
                                <h4>أدخل رمز التحقق المرسل إلى بريدك. الرمز صالح لمدة 60 ثانية.</h4>
                                @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
                                <form method="POST" action="{{ route('verify.store') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label for="code">رمز التحقق</label>
                                        <input id="code" class="form-control" placeholder="أدخل رمز التحقق" type="text" name="code" required autofocus>
                                        <x-input-error :messages="$errors->get('code')" class="mt-2" />
                                    </div>
                                    <button type="submit" class="btn btn-main-primary btn-block">تأكيد</button>
                                </form>
                                <form method="POST" action="{{ route('verify.resend') }}" class="mt-3">
                                    @csrf
                                    <button type="submit" id="resendBtn" class="btn btn-outline-primary btn-block" disabled>إعادة الإرسال (<span id="cd">60</span>)</button>
                                </form>
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
<script>
let s = 60, btn = document.getElementById('resendBtn'), el = document.getElementById('cd');
const t = setInterval(() => { s--; if (el) el.textContent = s; if (s <= 0) { clearInterval(t); btn.disabled = false; btn.textContent = 'إعادة الإرسال'; } }, 1000);
</script>
@endsection
