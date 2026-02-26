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
                                    <th width="10%" style="text-align: center;"><img src="{{ asset('app/img/icon/eye.png') }}" height="16"></th>
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
                                            @php
                                            $path               = public_path('uploads' . $row->document_url . $row->document_file);
                                            $isExists_file      = file_exists($path);
                                            @endphp
                                            <a href="{{ asset('uploads') . $row->document_url . $row->document_file }}" target="_blank" data-toggle="tooltip" title="{{$row->document_file}}">
                                                @if($isExists_file)
                                                <img src="{{ asset('app/img/icon/eye.png') }}" height="16" style="cursor: pointer">
                                                @else
                                                <img src="{{ asset('app/img/icon/remove.png') }}" height="16" class="view-item" style="cursor: pointer">
                                                @endif
                                            </a>
                                            @if($row->document_crs != "")
                                                @php
                                                $path               = public_path('uploads' . $row->document_url . $row->document_crs);
                                                $isExists_file      = file_exists($path);
                                                @endphp
                                            <a href="{{ asset('uploads') . $row->document_url . $row->document_crs }}" target="_blank" data-toggle="tooltip" title="{{$row->document_file}}">
                                                @if($isExists_file)
                                                <img src="{{ asset('app/img/icon/eye.png') }}" height="16" style="cursor: pointer">
                                                @else
                                                <img src="{{ asset('app/img/icon/remove.png') }}" height="16" class="view-item" style="cursor: pointer">
                                                @endif
                                            </a>
                                            @endif
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
@stop
