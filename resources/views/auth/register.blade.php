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
                    <div class="card">
                        <div class="card-header">{{ __('Register') }}</div>

                        <div class="card-body">
                            <form method="POST" action="{{ route('register') }}" aria-label="{{ __('Register') }}">
                                @csrf

                                <div class="form-group row">
                                    <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Name') }}</label>

                                    <div class="col-md-6">
                                        <input id="name" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ old('name') }}" required autofocus>

                                        @if ($errors->has('name'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('name') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                                    <div class="col-md-6">
                                        <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required>

                                        @if ($errors->has('email'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('email') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                                    <div class="col-md-6">
                                        <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                                        @if ($errors->has('password'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('password') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>

                                    <div class="col-md-6">
                                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                                    </div>
                                </div>

                                <div class="form-group row mb-0">
                                    <div class="col-md-6 offset-md-4">
                                        <button type="submit" class="btn btn-primary">
                                            {{ __('Register') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
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
                $("#button_submit").text("Sign me in");
                return false;
            }
        });
	});
</script>
</body>
</html>
