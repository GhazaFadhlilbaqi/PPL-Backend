@extends('mails.auth.layouts.email')

@section('title', 'Email Verification')

@section('content')
    <p class="font-weight-bold">Halo, {{ $user->first_name . ' ' . $user->last_name }}</p>
    <p class="font-weight-bold">Anda telah mendaftarkan {{ $user->email }} sebagai email untuk akun Rencanakan kamu.</p>
    <p class="font-weight-bold">Untuk menyelesaikan proses pendaftaran, kami perlu memverifikasi bahwa email ini benar milik anda. Silahkan <i>click</i> tombol di bawah untuk mulai membuat RAB Anda !</p>
    <a class="font-weight-bold custom-btn" href="{{ route('register.confirm_email', ['token' => $token]) }}" target="_blank" style="margin: 35px 0; display: block; text-align: center; color: white; padding: 20px 0; height: 25px; background-color: rgba(246, 144, 34, 1); border-radius: 10px; border: 0;text-decoration: none;">Verify Email</a>
    <p class="font-weight-bold">P.S. Jika Anda mengalami masalah dengan tombol diatas, silahkan salin dan tempelkan tautan dibawah dalam browser Anda:</p>
    <a class="font-weight-bold" target="_blank" href="{{ route('register.confirm_email', ['token' => $token]) }}">{{ route('register.confirm_email', ['token' => $token]) }}</a>
@endsection
