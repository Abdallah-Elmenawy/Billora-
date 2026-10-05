@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'الملف الشخصي'])
@endsection
@section('css')
<style>
    .profile-page .profile-side-card,
    .profile-page .profile-main-card {
        border-radius: 18px !important;
        border: none !important;
        box-shadow: 0 4px 20px rgba(45, 62, 80, 0.06) !important;
    }
    .profile-page .profile-label {
        display: block;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 0.45rem;
        font-size: 15px;
    }
    .profile-page .profile-input {
        background: #F0F1F3 !important;
        border: 1px solid transparent !important;
        border-radius: 12px !important;
        height: 48px;
        padding: 0.65rem 1rem;
        color: #1F2937;
        font-size: 14px;
        font-weight: 400;
        box-shadow: none !important;
    }
    .profile-page .profile-nav-btn {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 10px;
        width: 100%;
        padding: 0.9rem 1.1rem;
        margin-bottom: 0.75rem;
        border-radius: 12px;
        border: 1px solid #E5E7EB;
        background: #fff;
        color: #1F2937;
        font-weight: 600;
        font-size: 14px;
        text-align: right;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .profile-page .profile-nav-btn i {
        font-size: 18px;
        color: #6B7280;
    }
    .profile-page .profile-nav-btn:hover {
        border-color: transparent;
        background: rgba(107, 92, 231, 0.08);
        color: #6B5CE7;
    }
    .profile-page .profile-nav-btn:hover i {
        color: #6B5CE7;
    }
    .profile-page .profile-nav-btn.active {
        background: linear-gradient(135deg, #4A90E2 0%, #9B51E0 100%);
        border-color: transparent;
        color: #fff;
        box-shadow: 0 6px 16px rgba(107, 92, 231, 0.28);
    }
    .profile-page .profile-nav-btn.active i {
        color: #fff;
    }
    .profile-page .profile-avatar-wrap {
        text-align: center;
        margin-bottom: 2rem;
    }
    .profile-page .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        background: #EEF1F6;
        border: 4px solid #fff;
        box-shadow: 0 6px 18px rgba(45, 62, 80, 0.1);
        margin: 0 auto 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9B51E0;
        font-size: 42px;
        font-weight: 700;
        background-image: linear-gradient(135deg, rgba(74, 144, 226, 0.12), rgba(155, 81, 224, 0.18));
    }
    .profile-page .profile-avatar-label {
        color: #6B7280;
        font-weight: 600;
        margin: 0;
        font-size: 14px;
    }
    .profile-page .profile-input:focus {
        background: #fff !important;
        border-color: #4A90E2 !important;
        box-shadow: 0 0 0 0.2rem rgba(107, 92, 231, 0.18) !important;
    }
    .profile-page .profile-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-top: 1.5rem;
        width: 100%;
    }
    .profile-page .btn-profile-save {
        min-width: 150px;
        height: 46px;
        border-radius: 12px !important;
        font-weight: 700;
        border: none !important;
        color: #fff !important;
        background-image: linear-gradient(135deg, #4A90E2 0%, #9B51E0 100%) !important;
        box-shadow: 0 6px 16px rgba(107, 92, 231, 0.28);
    }
    .profile-page .btn-profile-save:hover {
        filter: brightness(1.05);
        color: #fff !important;
    }
    .profile-page .btn-profile-back {
        min-width: 110px;
        height: 46px;
        border-radius: 12px !important;
        font-weight: 700;
        background: #fff !important;
        border: 1.5px solid #C4B5FD !important;
        color: #6B5CE7 !important;
    }
    .profile-page .btn-profile-back:hover {
        background: rgba(107, 92, 231, 0.08) !important;
        color: #5A4BD6 !important;
    }
    .profile-page .profile-panel { display: none; }
    .profile-page .profile-panel.active { display: block; }
</style>
@endsection
@section('content')
@php
    $activeTab = old('_profile_tab', request('tab', 'basic'));
    $initial = mb_substr($user->name ?? 'U', 0, 1);
@endphp
<div class="profile-page">
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-3">
            <div class="card profile-side-card">
                <div class="card-body p-3">
                    <button type="button" class="profile-nav-btn {{ $activeTab === 'basic' ? 'active' : '' }}" data-profile-tab="basic">
                        <i class="fe fe-settings"></i>
                        <span>البيانات الأساسية</span>
                    </button>
                    <button type="button" class="profile-nav-btn {{ $activeTab === 'password' ? 'active' : '' }}" data-profile-tab="password">
                        <i class="fe fe-edit-2"></i>
                        <span>تعديل كلمة السر</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-9 col-md-8 mb-3">
            <div class="card profile-main-card">
                <div class="card-body p-4 p-md-5">
                    <div class="profile-avatar-wrap">
                        <div class="profile-avatar">{{ $initial }}</div>
                        <p class="profile-avatar-label">الصورة الشخصية</p>
                    </div>

                    <div class="profile-panel {{ $activeTab === 'basic' ? 'active' : '' }}" id="profile-panel-basic">
                        <form method="post" action="{{ route('profile.update') }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_profile_tab" value="basic">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="profile-label" for="name">الاسم</label>
                                    <input id="name" name="name" class="form-control profile-input @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="profile-label" for="phone">رقم الهاتف</label>
                                    <input id="phone" name="phone" class="form-control profile-input @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" placeholder="رقم الهاتف">
                                    @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="profile-label" for="email">البريد الالكتروني</label>
                                    <input id="email" name="email" type="email" class="form-control profile-input @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                    @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="profile-actions">
                                <button type="submit" class="btn btn-profile-save">حفظ التغييرات</button>
                                <a href="{{ route('dashboard') }}" class="btn btn-profile-back">رجوع</a>
                            </div>
                        </form>
                    </div>

                    <div class="profile-panel {{ $activeTab === 'password' ? 'active' : '' }}" id="profile-panel-password">
                        <form method="post" action="{{ route('profile.password') }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_profile_tab" value="password">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="profile-label" for="current_password">كلمة المرور الحالية</label>
                                    <input id="current_password" name="current_password" type="password" class="form-control profile-input @error('current_password') is-invalid @enderror" required>
                                    @error('current_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="profile-label" for="password">كلمة المرور الجديدة</label>
                                    <input id="password" name="password" type="password" class="form-control profile-input @error('password') is-invalid @enderror" required>
                                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="profile-label" for="password_confirmation">تأكيد كلمة المرور</label>
                                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control profile-input" required>
                                </div>
                            </div>
                            <div class="profile-actions">
                                <button type="submit" class="btn btn-profile-save">تغيير كلمة المرور</button>
                                <a href="{{ route('dashboard') }}" class="btn btn-profile-back">رجوع</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
<script>
(function ($) {
    function showProfileTab(tab) {
        $('.profile-nav-btn').removeClass('active');
        $('.profile-nav-btn[data-profile-tab="' + tab + '"]').addClass('active');
        $('.profile-panel').removeClass('active');
        $('#profile-panel-' + tab).addClass('active');
    }

    $(document).on('click', '.profile-nav-btn', function () {
        showProfileTab($(this).data('profile-tab'));
    });
})(window.jQuery);
</script>
@endsection
