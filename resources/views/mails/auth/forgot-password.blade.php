@extends('mails.auth.layouts.email')

@section('title', 'Email Verification')

@section('content')
    <p>Halo, jane</p>
    <p>Kami telah menerima permintaan Anda untuk reset password akun Rencanakan.id</p>
    <p>Silakan konfirmasi lewat tombol di bawah ini:</p>
    <a href="#" target="_blank" class="btn btn-primary w-100 custom-btn mt-4 mb-5" style="height: 50px; background-color: rgba(246, 144, 34, 1); border-radius: 10px; border: 0; padding-top: 13px;">Verify Email</a>
    <p>P.S. Jika Anda mengalami masalah dengan tombol diatas, silahkan salin dan tempelkan tautan dibawah dalam browser Anda:</p>
    <a href="#">https://exampleurl.com</a>
@endsection
