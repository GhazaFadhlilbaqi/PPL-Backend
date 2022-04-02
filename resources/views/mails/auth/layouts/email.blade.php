<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <!-- CSS only -->
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

        .footer {
            position: fixed;
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
        }

        .custom-btn:hover {
            background-color: rgb(182, 106, 24) !important;
        }
    </style>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto&display=swap');

        body {
            margin: 0;
        }

        * {
            font-family: 'Roboto'
        }

        .font-weight-bold {
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="custom-container" style="position: relative; padding-bottom: 200px; width: 100%; max-width: 700px; margin: auto;">
        <div class="heading1 text-center py-5" style="background-size: cover; position: relative; padding-top: 60px; text-align: center; height: 138px; background-image: url('{{ asset('assets/images/header-bg.jpg') }}')">
            <img src="{{ asset('assets/images/logo_white.png') }}" class="logo-img" alt="" style="margin: auto;">
            <div class="blue-overlay" style="position: absolute; top: 0; right: 0; left: 0; bottom: 0; background-color: rgba(21, 51, 70, 0.8);"></div>
        </div>
        <div class="content-container" style="height: 49vh; overflow: auto; padding: 50px;">
            @yield('content')
        </div>
        <div class="footers" style="position: absolute; bottom: 0; height: 40px; bottom: 0; left: 0; width: 100%; max-width: 700px; margin: auto; right: 0; background-color: rgba(21, 51, 70, 1); color: white; text-align: center; vertical-align: middle; padding: 87px 0;">
            <p style="height: 100%;">&copy; Rencanakan {{ date('Y') }}</p>
        </div>
    </div>
</body>
</html>
