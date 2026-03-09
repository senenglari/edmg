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
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse"><i class="fa fa-minus"></i></a>
                    </div>
                    <h4 class="panel-title">{{ $title }}</h4>
                </div> 
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
                <div class="panel-body">{{  $form_act }}
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
                        <div class="form-group preloader-container">
                            <label class="col-md-3 control-label">&nbsp;</label>
                            <div class="col-md-9">
                                <img src="{{ asset('app/img/icon/preloader.gif') }}" alt="" />
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse"><i class="fa fa-minus"></i></a>
                    </div>
                    <h4 class="panel-title">Attached Document(s)</h4>
                </div> 
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="table-document">
                            <thead>
                                <tr>
                                    <th width="18%" style="text-align: center;">Document Number</th>
                                    <th style="text-align: center;">Title</th>
                                    <th width="12%" style="text-align: center;">Issue Status</th>
                                    <th width="12%" style="text-align: center;">Revision Number</th>
                                    <th style="text-align: center;">Remark</th>
                                    <th width="5%" style="text-align: center;"><img src="{{ asset('app/img/icon/eye.png') }}" height="16"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detail AS $row)
                                    <tr>
                                        <td style="text-align: center;">{{ $row->document_no }}</td>
                                        <td>{{ $row->document_title }}</td>
                                        <td style="text-align: center;">{{ $row->issue_status_name }}</td>
                                        <td style="text-align: center;">{{ $row->document_status_name }}</td>
                                        <td style="text-align: left;">{{ $row->remark }}</td>
                                        <td style="text-align: center;">
                                            <a href="{{ asset('uploads') . $row->document_url . $row->document_file }}" target="_blank">
                                                <img src="{{ asset('app/img/icon/eye.png') }}" height="16" style="cursor: pointer">
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(".preloader-container").hide();

    $("#myform").submit(function(){
        var flag = "F";
        var no = 1;

        $(".mandatory-input").each(function() {
            var obj = $(this).attr("name");

            if(obj != 'document_file') {
                if((trim($(this).val()) == "") || (trim($(this).val()) == "0") || (trim($(this).val()) == "__/__/____") || (trim($(this).val()) == "00/00/0000")) {
                    $(this).css("border-color", "red");
                    $(".style_form_input_" + obj).css("border-color", "red");
                    $(".style_form_input_" + obj).css("color", "red");

                    if(no == 1) {
                        $(this).focus();
                        no = 2;
                    }

                    flag = "T";
                } else {
                    $(this).css("border-color", "#DDDDDD");
                    $(".style_form_input_" + obj).css("border-color", "#DDDDDD");
                    $(".style_form_input_" + obj).css("color", "#DDDDDD");
                }
            }
        });

        if(flag == "T") {
            return false;
        }

        $(".button-container").hide();
        $(".preloader-container").show();
    });
</script>
@stop
