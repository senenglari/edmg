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
                        <div id="ex_1">
                            @foreach ($fields_1 as $row)
                                {!! $row !!}
                            @endforeach
                        </div>
                        @foreach ($fields_2 as $row)
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
                    </form>
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
                    <h4 class="panel-title">Comments</h4>
                </div> 
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="table-document">
                            <thead>
                                <tr>
                                    <th width="20%" style="text-align: center;">User</th>
                                    <th width="10%" style="text-align: center;">Time</th>
                                    <th width="50%" style="text-align: center;">Remark</th>
                                    <th width="10%" style="text-align: center;">Return Code</th>
                                    <th width="10%" style="text-align: center;">File</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detail AS $row)
                                    <tr>
                                        <td style="text-align: center;">{{ $row->user_name }}</td>
                                        <td style="text-align: center;">{{ $row->tanggal_log }}</td>
                                        <td style="text-align: left;">{!! nl2br($row->remark) !!}</td>
                                        <td style="text-align: center;">{{ $row->return_code }}</td>
                                        <td style="text-align: center;">
                                            <table style="margin-left: auto; margin-right: auto;">
                                                <tr>
                                                    <td style="text-align: center;" width="50%">&nbsp;
                                                        @if(!empty($row->document_file))
                                                            <a href="{{ url('').'/uploads'.$row->document_url.$row->document_file }}" target="_blank"><img src="{{ url('') . '/app/img/icon/attachment.png'}}" alt="" width="30px"></a>
                                                        @endif
                                                    </td>
                                                    <td style="text-align: center;" width="50%">&nbsp;
                                                        @if(!empty($row->document_file_2))
                                                            <a href="{{ url('').'/uploads'.$row->document_url.$row->document_file_2 }}" target="_blank"><img src="{{ url('') . '/app/img/icon/attachment.png'}}" alt="" width="30px"></a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
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
<script type="text/javascript" src="{{ asset('app/js/form_validation.js') }}"></script>
<script>
$(".preloader-container").hide();
var statusApproval = $('#status_approval').val();
var issueStatusId = $('#issue_status_id').val();

if (statusApproval == "APPROVAL") {
    if(issueStatusId == 12) {
        $("#ex_1").show();
    } else {
        $("#ex_1").hide();
    }
} else {
    $("#ex_1").hide();
}

$(document).ready(function () {
    $("select#issue_status_id").change(function( event, ui ) {
        var val = $(this).val();
        if (val == 12) {
            $("#ex_1").show();
        } else {
            $("#ex_1").hide();
        }
    });
})
</script>
@stop
