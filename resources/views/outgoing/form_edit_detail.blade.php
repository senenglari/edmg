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
                                    <th width="5%" style="text-align: center;"><img src="{{ asset('app/img/icon/delete.png') }}" height="16"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($detail) < 1)
                                    <tr>
                                        <td colspan="6" style="text-align: center;">No data found (0)</td>
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
                                            <td style="text-align: center;">
                                            <a href="{{ url('/outgoing/deletedocument/'.$row->outgoing_transmittal_detail_id.'/'.$row->outgoing_transmittal_id) }}"><img src="{{ asset('app/img/icon/delete.png') }}" height="16" class="delete-item" style="cursor: pointer"></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="form-group button-container">
                            <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modal-document">
                                Add New Document
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container demo">
        <div class="modal right fade" id="modal-document" tabindex="-1" role="dialog" aria-labelledby="modal-document">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel2">Attach Document</h4>
                    </div>
                    <div class="modal-body">
                    <form class="form-horizontal" id="myform2" name="myform2" action="{{ URL::to('/').$form_act_modal }}" method="post" enctype="multipart/form-data">
                        <div class="panel-body">
                            <div class="alert alert-danger fade in m-b-30" id="notif_attach" style="display: none;">
                                <strong>Alert!</strong>
                                <span id="message_notif_attach">Please wait</span>
                            </div>
                            @csrf
                            @foreach ($fields_modal as $row)
                                {!! $row !!}
                            @endforeach
                        </div>
                        <div class="form-group button-attach-container">
                            <label class="col-md-3 control-label">&nbsp;</label>
                            <div class="col-md-9">
                                <button type="submit" class="btn btn-sm btn-danger" name="button_attach" id="button_attach" style="margin-left: 8px;">&nbsp;&nbsp;Attach&nbsp;&nbsp;</button>
                            </div>
                        </div>
                        <div class="form-group preloader-attach-container">
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
</div>
<script type="text/javascript" src="{{ asset('app/js/form_validation.js') }}"></script>
<script>
    $(".preloader-container").hide();
    $(".button-attach-container").show();
    $(".preloader-attach-container").hide();

    $("#button_save").click(function() {
        $(".button-container").hide();
        $(".preloader-container").show();
    });

    $("#button_attach").click(function() {
        $(".button-attach-container").hide();
        $(".preloader-attach-container").show();
    });
    
</script>
<style type="text/css">
    .modal.left .modal-dialog,
    .modal.right .modal-dialog {
        position: fixed;
        margin: auto;
        width: 800px;
        height: 100%;
        -webkit-transform: translate3d(0%, 0, 0);
            -ms-transform: translate3d(0%, 0, 0);
             -o-transform: translate3d(0%, 0, 0);
                transform: translate3d(0%, 0, 0);
    }

    .modal.left .modal-content,
    .modal.right .modal-content {
        height: 100%;
        overflow-y: auto;
    }
    
    .modal.left .modal-body,
    .modal.right .modal-body {
        padding: 15px 15px 80px;
    }

    .modal.left.fade .modal-dialog{
        left: -320px;
        -webkit-transition: opacity 0.3s linear, left 0.3s ease-out;
           -moz-transition: opacity 0.3s linear, left 0.3s ease-out;
             -o-transition: opacity 0.3s linear, left 0.3s ease-out;
                transition: opacity 0.3s linear, left 0.3s ease-out;
    }
    
    .modal.left.fade.in .modal-dialog{
        left: 0;
    }
        
/*Right*/
    .modal.right.fade .modal-dialog {
        right: -320px;
        -webkit-transition: opacity 0.3s linear, right 0.3s ease-out;
           -moz-transition: opacity 0.3s linear, right 0.3s ease-out;
             -o-transition: opacity 0.3s linear, right 0.3s ease-out;
                transition: opacity 0.3s linear, right 0.3s ease-out;
    }
    
    .modal.right.fade.in .modal-dialog {
        right: 0;
    }

/* ----- MODAL STYLE ----- */
    .modal-content {
        border-radius: 0;
        border: none;
    }

    .modal-header {
        border-bottom-color: #EEEEEE;
        background-color: #FAFAFA;
    }

</style>
@stop
