<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ env('APPS_NAME') }} | Login</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
    <meta content="" name="description" />
    <meta content="" name="author" />
    <link rel="icon" type="image/ico" href="{{ asset('app/img/icon/favicon.ico') }}" />
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <link href="{{ asset('vendor/color_admin/plugins/jquery-ui/themes/base/minified/jquery-ui.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/color_admin/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/color_admin/plugins/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/color_admin/css/animate.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/color_admin/css/style.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/color_admin/css/style-responsive.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/color_admin/css/theme/default.css') }}" rel="stylesheet" id="theme" />
    <script src="{{ asset('vendor/color_admin/plugins/pace/pace.min.js') }}"></script>
</head>
<body class="pace-top bg-white">
<div id="page-loader" class="fade in"><span class="spinner"></span></div>
<div id="page-container" class="fade">
    <div class="login login-with-news-feed">
        <div class="news-feed">
            <div class="news-image">
            <!-- <img src="{{ asset('vendor/color_admin/img/login-bg/bg-7.jpg') }}" data-id="login-cover-image" alt="" /> -->
                @php
                    $number   = rand(1, 9);
                @endphp
                <img src="{{ asset('app/img/background/'.$number.'.png') }}" data-id="login-cover-image" alt="" />
            </div>
            <div class="news-caption">
                <h4 class="caption-title"><img src="{{ asset(env('APPS_DEVELOPER_ICON')) }}" /></h4>
                <!-- <p>
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                </p> -->
            </div>
        </div>
        <div class="right-content">
            <div class="login-header">
                <div class="brand">
                    <img src="{{ asset(env('APPS_COMPANY_LOGO')) }}" style="width: 150px;" />
                </div>
            </div>
            <div class="login-content">
                <form method="POST" action="{{ URL::to('/reset_password') }}" class="margin-bottom-0" id="myform" name="myform" autocomplete="off">
                    @csrf
                    <div class="form-group m-b-15">
                        <input type="text" class="form-control input-lg mandatory-input style_form_input_email" placeholder="Email Address" id="email" name="email" value="{{ $email }}" readonly />
                    </div>
                    <div class="form-group m-b-15">
                        <input type="password" class="form-control input-lg mandatory-input style_form_input_password first-selected" placeholder="Password Saat Ini" id="current_password" name="current_password" />
                    </div>
                    <div class="form-group m-b-15">
                        <input type="password" class="form-control input-lg mandatory-input style_form_input_password" placeholder="Password Baru" id="new_password" name="new_password" />
                    </div>
                    <div class="form-group m-b-15">
                        <input type="password" class="form-control input-lg mandatory-input style_form_input_password" placeholder="Password Konfirmasi" id="password_confirm" name="password_confirm" />
                    </div>
                    @if(session()->get('RESET_SES_MESSAGE') != "")
                    <div class="alert alert-danger fade in m-b-15">
                        <strong>Alert!</strong>
                        {{ session()->get('RESET_SES_MESSAGE') }}
                        <span class="close" data-dismiss="alert">&times;</span>
                    </div>
                    @endif
                    <div class="login-buttons">
                        <button type="submit" class="btn btn-success btn-block btn-lg">Update my password</button>
                    </div>
                    <div class="note note-info" style="margin-top: 15px;">
                        <h4>Aturan Password</h4>
                        <ul>
                            <li>1. Minimum length 6 Character.</li>
                            <li>2. Minimum 1 Upper Case : A, B, C, D, E ..</li>
                            <li>3. Minimum 1 Lower Case : a, b, c, d, e, ...</li>
                            <li>4. Minimum 1 Numeric : 0, 1, 2, 3, 4, 5, 6, 7, 8, 9</li>
                            <li>5. Minimum 1 Special Karakter : @, $, #, &</li>
                        </ul>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('vendor/color_admin/plugins/jquery/jquery-1.9.1.min.js') }}"></script>
<script src="{{ asset('vendor/color_admin/plugins/jquery/jquery-migrate-1.1.0.min.js') }}"></script>
<script src="{{ asset('vendor/color_admin/plugins/jquery-ui/ui/minified/jquery-ui.min.js') }}"></script>
<script src="{{ asset('vendor/color_admin/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
<!--[if lt IE 9]>
<script src="{{ asset('vendor/color_admin/crossbrowserjs/html5shiv.js') }}"></script>
<script src="{{ asset('vendor/color_admin/crossbrowserjs/respond.min.js') }}"></script>
<script src="{{ asset('vendor/color_admin/crossbrowserjs/excanvas.min.js') }}"></script>
<![endif]-->
<script src="{{ asset('vendor/color_admin/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>
<script src="{{ asset('vendor/color_admin/plugins/jquery-cookie/jquery.cookie.js') }}"></script>
<script src="{{ asset('vendor/color_admin/js/apps.min.js') }}"></script>
<script src="{{ asset('app/js/general.js') }}"></script>
<script src="{{ asset('app/js/auth_validation.js') }}"></script>
<script>
    $(document).ready(function() {
        App.init();
    });
</script>
</body>
</html>