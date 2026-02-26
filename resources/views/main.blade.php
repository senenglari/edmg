<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<head>
    <meta charset="utf-8"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EDMS</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport"/>
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <link rel="icon" type="image/ico" href="{{ asset('app/img/icon/faviconn.ico') }}"/>
    <link href="{{ asset('app/css/fonts.googleapis.css') }}" rel="stylesheet">
    <!-- ================== BEGIN BASE CSS STYLE ================== -->
    <link href="{{ asset('vendor/color_admin/plugins/jquery-ui/themes/base/minified/jquery-ui.min.css') }}"
          rel="stylesheet"/>
    <link href="{{ asset('vendor/color_admin/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('vendor/color_admin/plugins/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('vendor/color_admin/css/animate.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('vendor/color_admin/css/style.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('vendor/color_admin/css/style-responsive.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('vendor/color_admin/css/theme/default.css') }}" rel="stylesheet" id="theme"/>
    <!-- ================== END BASE CSS STYLE ================== -->

    <!-- ================== BEGIN PAGE LEVEL STYLE ================== -->
    <link href="{{ asset('vendor/color_admin/plugins/bootstrap-datepicker/css/datepicker.css') }}" rel="stylesheet"/>
    <link href="{{ asset('vendor/color_admin/plugins/bootstrap-datepicker/css/datepicker3.css') }}" rel="stylesheet"/>
    <!-- <link href="{{ asset('vendor/color_admin/plugins/ionRangeSlider/css/ion.rangeSlider.css') }}" rel="stylesheet" />
  	<link href="{{ asset('vendor/color_admin/plugins/ionRangeSlider/css/ion.rangeSlider.skinNice.css') }}" rel="stylesheet" /> -->
    <!-- <link href="{{ asset('vendor/color_admin/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css') }}" rel="stylesheet" />
  	<link href="{{ asset('vendor/color_admin/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css') }}" rel="stylesheet" /> -->
    <!-- <link href="{{ asset('vendor/color_admin/plugins/password-indicator/css/password-indicator.css') }}" rel="stylesheet" /> -->
    <link href="{{ asset('vendor/color_admin/plugins/bootstrap-combobox/css/bootstrap-combobox.css') }}"
          rel="stylesheet"/>
    <link href="{{ asset('vendor/color_admin/plugins/bootstrap-select/bootstrap-select.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('vendor/color_admin/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css') }}"
          rel="stylesheet"/>
    <link href="{{ asset('vendor/color_admin/plugins/jquery-tag-it/css/jquery.tagit.css') }}" rel="stylesheet"/>
    <link href="{{ asset('vendor/color_admin/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css') }}"
          rel="stylesheet"/>
    <link href="{{ asset('vendor/color_admin/plugins/select2/dist/css/select2.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('vendor/color_admin/plugins/bootstrap-eonasdan-datetimepicker/build/css/bootstrap-datetimepicker.min.css') }}"
          rel="stylesheet"/>
    <!-- ================== END PAGE LEVEL STYLE ================== -->

    <!-- ================== BEGIN BASE JS ================== -->
    <script src="{{ asset('vendor/color_admin/plugins/pace/pace.min.js') }}"></script>
    <!-- ================== END BASE JS ================== -->
    <script src="{{ asset('vendor/color_admin/plugins/jquery/jquery-1.9.1.min.js') }}"></script>
    <!-- <script src="{{ asset('vendor/color_admin/plugins/jquery/jQuery-2.1.4.min.js') }}"></script> -->
</head>
<body>
<div id="page-loader" class="fade in"><span class="spinner"></span></div>
<div id="page-container" class="page-container fade page-sidebar-fixed page-header-fixed">
    @include("header")
    @include("sidebar")
    @yield("content")
    @include('chat.chatting')
</div>
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
<!-- ================== END BASE JS ================== -->

<!-- ================== BEGIN PAGE LEVEL JS ================== -->
<script src="{{ asset('vendor/color_admin/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js') }}"></script>
<script src="{{ asset('vendor/color_admin/plugins/ionRangeSlider/js/ion-rangeSlider/ion.rangeSlider.min.js') }}"></script>
<script src="{{ asset('vendor/color_admin/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js') }}"></script>
<script src="{{ asset('vendor/color_admin/plugins/masked-input/masked-input.min.js') }}"></script>
<script src="{{ asset('vendor/color_admin/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js') }}"></script>
<script src="{{ asset('vendor/color_admin/plugins/password-indicator/js/password-indicator.js') }}"></script>
<script src="{{ asset('vendor/color_admin/plugins/bootstrap-combobox/js/bootstrap-combobox.js') }}"></script>
<script src="{{ asset('vendor/color_admin/plugins/bootstrap-select/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('vendor/color_admin/plugins/bootstrap-tagsinput/bootstrap-tagsinput.min.js') }}"></script>
<script src="{{ asset('vendor/color_admin/plugins/bootstrap-tagsinput/bootstrap-tagsinput-typeahead.js') }}"></script>
<script src="{{ asset('vendor/color_admin/plugins/jquery-tag-it/js/tag-it.min.js') }}"></script>
<script src="{{ asset('vendor/color_admin/plugins/bootstrap-daterangepicker/moment.js') }}"></script>
<script src="{{ asset('vendor/color_admin/plugins/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('vendor/color_admin/plugins/select2/dist/js/select2.min.js') }}"></script>
<script src="{{ asset('vendor/color_admin/plugins/bootstrap-eonasdan-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
<script src="{{ asset('vendor/color_admin/js/form-plugins.demo.js') }}"></script>
<script src="{{ asset('vendor/color_admin/js/apps.min.js') }}"></script>
<script src="{{ asset('app/js/general.js') }}"></script>
<script src="{{ asset('app/js/action.js') }}"></script>
<script>
    $(document).ready(function () {
        App.init();
        FormPlugins.init();
    });
</script>
</body>
</html>
