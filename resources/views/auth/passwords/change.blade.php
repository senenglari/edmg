@extends('main')
@section('content')
    <div id="content" class="content">
        <ol class="breadcrumb pull-right">
            <li>Home</li>
            <li class="active">{{ $title }}</li>
        </ol>
        <h1 class="page-header">{{ $title }}</h1>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-inverse">
                    @if (isset($tabs))
                        @if ($tabs > 0)
                            <div class="tab-overflow" style="background-color: #242A30">
                                <ul class="nav nav-tabs nav-tabs-inverse">
                                    <li class="prev-button"><a href="javascript:;" data-click="prev-tab" class="text-success"><i class="fa fa-arrow-left"></i></a></li>
                                    @foreach ($tabs as $row)
                                        <li class="{{ $row["active"] }}"><a href="{{ url('/') . $row['url'] }}">{{ $row["label"] }}</a></li>
                                    @endforeach
                                    <li class="next-button"><a href="javascript:;" data-click="next-tab" class="text-success"><i class="fa fa-arrow-right"></i></a></li>
                                </ul>
                            </div>
                        @endif
                    @else
                        <div class="panel-heading">
                            <div class="panel-heading-btn">
                                <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                                <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse"><i class="fa fa-minus"></i></a>
                            </div>
                            <h4 class="panel-title">{{ $title }}</h4>
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger" id="alert-box">
                            @foreach ($errors->all() as $error)
                                <i class="fa fa-times-circle fa-fw"></i>
                                <span id="alert-message"> {{ $error }}</span><br>
                            @endforeach
                        </div>
                    @endif
                    <div class="alert alert-danger" id="alert-box" style="{{ (Session::has("error_message")) ? "" : "display:none;" }}">
                        <i class="fa fa-times-circle fa-fw"></i> <span id="alert-message">{{ (Session::has("error_message")) ? Session::get("error_message") : "" }}</span>
                    </div>
                    <div class="panel-body">
                        <form class="form-horizontal" id="myform" name="myform" action="{{ URL::to('/').$form_act }}" method="post" enctype="multipart/form-data">
                            @csrf
                            @foreach ($fields as $row)
                                {!! $row !!}
                            @endforeach
                            <div class="form-group button-container">
                                <label class="col-md-3 control-label">&nbsp;</label>
                                <div class="col-md-9">
                                    @foreach ($buttons as $row)
                                        {!! $row !!}
                                    @endforeach
                                </div>
                            </div>
                            <div class="note note-info" style="margin-top: 15px;">
                                <h4>Password Conditions</h4>
                                <ul>
                                    <li>Minimum length 6 Character.</li>
                                    <li>Minimum 1 Upper Case : A, B, C, D, E ..</li>
                                    <li>Minimum 1 Lower Case : a, b, c, d, e, ...</li>
                                    <li>Minimum 1 Numeric : 0, 1, 2, 3, 4, 5, 6, 7, 8, 9</li>
                                    <li>Minimum 1 Special Karakter : @, $, #, &</li>
                                </ul>
                            </div>
                            <div class="form-group preloader-container">
                                <label class="col-md-3 control-label">&nbsp;</label>
                                <div class="col-md-9">
                                    <img src="{{ asset('app/img/icon/preloader.gif') }}" alt="" />
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="{{ asset('app/js/form_validation.js') }}"></script>
    <script>
        $(".preloader-container").hide();
    </script>
@stop
