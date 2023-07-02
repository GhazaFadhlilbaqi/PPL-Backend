@extends('mails.auth.layouts.email')

@section('title', 'Email Verification')

@section('content')
    <p class="font-weight-bold">Halo, {{ $recepient }}</p>
    <p>This is testing email. Yeay! It worked!</p>
@endsection
