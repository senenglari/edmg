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
                <div class="panel-body">
                    <form class="form-horizontal" id="myform" name="myform" action="{{ URL::to('/').$form_act }}" method="post" enctype="multipart/form-data" target="_blank">
                        @csrf
                        @foreach ($fields as $row)
  			              	{!! $row !!}
  			          	@endforeach
                        @if(!empty($buttons))
                        <div class="form-group button-container">
                            <label class="col-md-3 control-label">&nbsp;</label>
                            <div class="col-md-9">
                                @foreach ($buttons as $row)
                                    {!! $row !!}
                                @endforeach
                            </div>
                        </div>
                        @endif
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

<script>
$(".preloader-container").hide();

$("#button_submit").click(function(){
    var type        = $("#report_type").val();
    var url         = $("#text_url").val();
    var pdf         = $("#pdf").val();
    var xls         = $("#xls").val();

    if(type == "XLS") {
        if(xls == "true") {
            myform.setAttribute("target", "");
            
            window.setTimeout("document.myform.submit();", 500);
        } else {
            alert("XLS report is not available");
            return false;
        }
    } else {
        var a = window.setTimeout("document.myform.submit();", 10);

        return false;
    }
});
</script>
@stop
