@extends('mails.auth.layouts.email')

@section('title', 'Email Verification')

@section('content')
    <p>Halo, jane</p>
    <p>Kami telah menerima permintaan Anda untuk reset password akun Rencanakan.id</p>
    <p>Silakan konfirmasi lewat tombol di bawah ini:</p>
    <a href="{{ config('app.password_reset_form.callback_domain') . config('app.password_reset_form.form_route') . '?token=' . $passwordReset->token }}" target="_blank" class="btn btn-primary w-100 custom-btn mt-4 mb-5">Verify Email</a>
    <p>P.S. Jika Anda mengalami masalah dengan tombol diatas, silahkan salin dan tempelkan tautan dibawah dalam browser Anda:</p>
    <a href="{{ route('register.confirm_email', ['token' => $passwordReset->token]) }}">{{ route('register.confirm_email', ['token' => $passwordReset->token]) }}</a>
@endsection
