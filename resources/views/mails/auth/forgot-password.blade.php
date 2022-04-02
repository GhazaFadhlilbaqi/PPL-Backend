@extends('mails.auth.layouts.email')

@section('title', 'Email Verification')

@section('content')
    <p class="font-weight-bold">Halo, {{ $user->first_name . ' ' . $user->last_name }}</p>
    <p class="font-weight-bold">Kami telah menerima permintaan Anda untuk reset password akun Rencanakan.id</p>
    <p class="font-weight-bold">Silakan konfirmasi lewat tombol di bawah ini:</p>
    {{-- <a href="{{ config('app.password_reset_form.callback_domain') }}" target="_blank" class="btn btn-primary w-100 custom-btn mt-4 mb-5" style="margin: 35px 0; display: block; text-align: center; color: white; padding: 20px 0; height: 25px; background-color: rgba(246, 144, 34, 1); border-radius: 10px; border: 0;text-decoration: none;">Verify Email</a> --}}
    <p class="font-weight-bold">P.S. Jika Anda mengalami masalah dengan tombol diatas, silahkan salin dan tempelkan tautan dibawah dalam browser Anda:</p>
    <a href="#">https://exampleurl.com</a>
@endsection
