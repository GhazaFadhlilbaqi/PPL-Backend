@extends('mails.auth.layouts.email')

@section('title', 'Email Verification')

@section('content')
    <p>Halo, jane</p>
    <p>Anda telah mendaftarkan jane21@fakemail.com sebafgai email untuk akun Rencanakan kamu.</p>
    <p>Untuk menyelesaikan proses pendaftaran, kami perlu memverifikasi bahwa email ini benar milik anda. Silahkan <i>click</i> tombol di bawah untuk mulai membuat RAB Anda !</p>
    <a href="{{ route('register.confirm_email', ['token' => $token]) }}" class="btn btn-primary w-100 custom-btn mt-4 mb-5">Verify Email</a>
    <p>P.S. Jika Anda mengalami masalah dengan tombol diatas, silahkan salin dan tempelkan tautan dibawah dalam browser Anda:</p>
    <a target="_blank" href="{{ route('register.confirm_email', ['token' => $token]) }}">{{ route('register.confirm_email', ['token' => $token]) }}</a>
@endsection
