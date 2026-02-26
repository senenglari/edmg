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
            <div>
                @foreach($menu AS $row)
                <div class="panel panel-inverse overflow-hidden" style="margin-bottom: 1px;">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapse_{{ $row['menu_id'] }}" aria-expanded="false">
                                <i class="fa fa-plus-circle pull-right"></i> 
                                {{ $row["menu_name"] }}
                            </a>
                        </h3>
                    </div>
                    <div id="collapse_{{ $row['menu_id'] }}" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
                        <div class="panel-body">
                            <input type="checkbox" name="check_menu[]" value="{{ $row['menu_id'] }}" {{ $row['menu_checklist'] == '1' ? 'checked' : '' }} />&nbsp;&nbsp;&nbsp;&nbsp;{{ $row["menu_name"] }}</br>
                            @foreach($row["children"] AS $row_children)
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="check_menu[]" value="{{ $row_children['menu_id'] }}" {{ $row_children['menu_checklist'] == '1' ? 'checked' : '' }} />&nbsp;&nbsp;&nbsp;&nbsp;{{ $row_children["menu_name"] }}</br>

                                @if($row["submenu_count"] > 0)
                                    @foreach($row_children["children"] AS $sub_children)
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="check_menu[]" value="{{ $sub_children['menu_id'] }}" {{ $sub_children['menu_checklist'] == '1' ? 'checked' : '' }} />&nbsp;&nbsp;&nbsp;&nbsp;{{ $sub_children["menu_name"] }}</br>
                                    @endforeach
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ asset('app/js/form_validation.js') }}"></script>
<script>
$(".preloader-container").hide();
</script>
@stop
