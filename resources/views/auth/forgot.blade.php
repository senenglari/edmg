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
                    <img src="{{ asset('app/img/background/bg_doc.jpg') }}" data-id="login-cover-image" alt="" />
                </div>
                <div class="news-caption">
                    <!-- <p>
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                    </p> -->
                </div>
            </div>
            <div class="right-content">
                <div class="login-header">
                    <div class="brand">
                        <img src="{{ asset(env('APPS_COMPANY_LOGO')) }}" style="width: 100px;" />
                    </div>
                </div>
                <div class="login-content">
                    <form method="POST" action="{{ route('password.forgot-send') }}" class="margin-bottom-0" id="myform" name="myform" autocomplete="off">
                        @csrf
                        <div class="form-group m-b-15">
                            <input type="email" class="form-control input-lg mandatory-input first-selected style_form_input_email" placeholder="Email Address" id="email" name="email" />
                        </div>
                        @if(Session::has("error_message"))
                        <div class="login-buttons">
                            <div class="alert alert-danger fade in m-b-15">
                                <strong>Error!</strong>
                                {{ Session::get("error_message") }}
                                <span class="close" data-dismiss="alert">×</span>
                            </div>
                        </div>
                        @endif
                        <div class="login-buttons">
                            <button type="submit" id="button_submit" class="btn btn-success btn-block btn-lg">Send new password</button>
                        </div>
                        <div style="margin-top: 10px;">
                            <a href="{{ route('login') }}">Back to login page</a>
                        </div>
                        <p class="text-center" style="padding-top: 50px; color: #999999;">
                            &copy; {{ env('APPS_COPYRIGHT') }}, All Right Reserved {{ env('APPS_PRODUCTION_YEAR') }}
                        </p>
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
<!-- <script src="{{ asset('app/js/auth_validation.js') }}"></script> -->
<script>
    $(document).ready(function() {
        App.init();

        $(".first-selected").focus();
        $(".first-selected").select();

        $("#myform").submit(function(){
            var flag = "F";
            var no = 1;

            $("#button_submit").text("Please wait ...");

            $(".mandatory-input").each(function() {
                var obj = $(this).attr("name");

                if((trim($(this).val()) == "") || (trim($(this).val()) == "0") || (trim($(this).val()) == "__/__/____") || (trim($(this).val()) == "00/00/0000")) {
                    $(this).css("border-color", "red");
                    $(".style_form_input_" + obj).css("border-color", "red");
                    //$(".style_form_input_" + obj).css("color", "red");

                    if(no == 1) {
                        $(this).focus();
                        no = 2;
                    }

                    flag = "T";
                } else {
                    $(this).css("border-color", "#CCD0D4");
                    $(".style_form_input_" + obj).css("border-color", "#CCD0D4");
                    $(".style_form_input_" + obj).css("color", "#999999");
                }
            });
            
            if(flag == "T") {
                $("#button_submit").text("Send new password");
                return false;
            }
        });
    });
</script>
</body>
</html>
