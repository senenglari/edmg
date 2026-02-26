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
                    <h4 class="panel-title">Attach Document(s)</h4>
                </div> 
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="table-document">
                            <thead>
                                <tr>
                                    <th width="20%" style="text-align: center;">Document Number</th>
                                    <th style="text-align: center;">Title</th>
                                    <th width="10%" style="text-align: center;">Issue Status</th>
                                    <th width="10%" style="text-align: center;">Return Code</th>
                                    <th width="10%" style="text-align: center;">Doc File</th>
                                    <th width="10%" style="text-align: center;">CRS File</th>

                                </tr>
                            </thead>
                            <tbody>
                                @if(count($detail) < 1)
                                    <tr>
                                        <td colspan="5" style="text-align: center;">No data found (0)</td>
                                    </tr>
                                @else
                                    @foreach($detail AS $row)
                                        <tr>
                                            <td style="text-align: center;">{{ $row->document_no }}</td>
                                            <td style="text-align: center;">{{ $row->document_title }}</td>
                                            <td style="text-align: center;">{{ $row->issue_status_name }}</td>
                                            <td style="text-align: center;">{{ $row->return_status_name }}</td>
                                            <td style="text-align: center;">
                                                    <a href="{{ url('').'/uploads'.$row->outgoing_document_url.$row->outgoing_document_file }}" target="_blank"><img src="{{ url('') . '/app/img/icon/eye.png'}}" height="16" alt=""></a>
                                            </td>
                                            <td style="text-align: center;">
                                                @if(!empty($row->document_crs))
                                                    <a href="{{ url('').'/uploads'.$row->outgoing_document_url.$row->outgoing_document_crs }}" target="_blank"><img src="{{ url('') . '/app/img/icon/eye.png'}}" height="16" alt=""></a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
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
</script>

@stop
