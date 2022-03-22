<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <!-- CSS only -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom-email.css') }}">
    @include('mails.auth.layouts.style')
    <style>
        .custom-container {
            position: relative;
            padding-bottom: 200px;
        }

        .heading1 {
            background-size: cover;
            position: relative;
            height: 180px;
        }

        .blue-overlay {
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            bottom: 0;
            background-color: rgba(21, 51, 70, 0.8);
        }

        .logo-img {
            /* background-color: red; */
            /* width: 50%; */
            margin: auto;
            z-index: 99;
            right: 0;
            top: 0;
            bottom: 0;
            left: 0;
            position: absolute;
        }

        .custom-container {
            width: 100%;
            max-width: 700px;
            margin: auto;
        }

        .footers {
            height: 200px;
            bottom: 0;
            left: 0;
            width: 100%;
            max-width: 700px;
            margin: auto;
            right: 0;
            background-color: rgba(21, 51, 70, 1);
            color: white;
            text-align: center;
            vertical-align: middle;
            padding: 87px 0;
        }

        .footer p {
            height: 100%;
        }

        .content-container {
            padding: 50px;
        }

        .custom-btn {
            height: 50px;
            background-color: rgba(246, 144, 34, 1);
            border-radius: 10px;
            border: 0;
            padding-top: 13px;
        }

        .custom-btn:hover {
            background-color: rgb(182, 106, 24) !important;
        }

    </style>
</head>
<body>
    <div class="custom-container">
        <div class="heading1 text-center py-5">
            <img src="{{ asset('assets/images/logo_white.png') }}" class="logo-img" alt="">
            <div class="blue-overlay"></div>
        </div>
        <div class="content-container" style="height: 85vh; overflow: auto;">
            @yield('content')
        </div>
        <div class="footers" style="position: absolute; bottom: 0;">
            <p>&copy; Rencanakan {{ date('Y') }}</p>
        </div>
    </div>
</body>
</html>
