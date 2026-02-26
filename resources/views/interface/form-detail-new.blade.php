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
                    <h4 class="panel-title">List Subfolder</h4>
                </div>
                <div class="panel-body">
                    <table id="sub-table" class="table table-bordered" id="table-document" width="100%">
                        <thead>
                            <tr>
                                <th width="20%" style="text-align: center;">SubFolder Name</th>
                                <th width="15%" style="text-align: center;">Document Number</th>
                                <th style="text-align: center;">Document Title</th>
                                <th width="10%" style="text-align: center;">Issue Status</th>
                                <th width="10%" style="text-align: center;">Revision Number</th>
                                <th width="20%" style="text-align: center;">Remark</th>
                                <th width="8%" style="text-align: center;"><img src="{{ asset('app/img/icon/eye.png') }}" height="16"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($subfolder) != 0)
                            @foreach($items AS $index => $row)
                            <tr>
                                <td style="text-align: center;">{{ $row->subfolder_name }}</td>
                                <td style="text-align: center;">{{ $row->document_no }}</td>
                                <td>{{ $row->document_title }}</td>
                                <td style="text-align: center;">{{ $row->issue_status_name }}</td>
                                <td style="text-align: center;">{{ $row->document_status_name }}</td>
                                <td style="text-align: center;">{{ $row->remark }}</td>
                                <td style="text-align: center;">
                                    @php
                                    $path = public_path('uploads' . $row->document_url . $row->document_file);
                                    $isExists_file = file_exists($path);
                                    @endphp
                                    <a href="{{ url('/uploads') . $row->document_url . $row->document_file }}" target="_blank">
                                        @if($isExists_file)
                                        <img src="{{ asset('app/img/icon/eye.png') }}" height="16" class="view-item" style="cursor: pointer">
                                        @else
                                        <img src="{{ asset('app/img/icon/remove.png') }}" height="16" class="view-item" style="cursor: pointer">
                                        @endif
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="container demo">
            <div class="modal right fade" id="modal-document" tabindex="-1" role="dialog" aria-labelledby="modal-document">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel2">Document</h4>
                        </div>
                        <div class="modal-body">
                            <div class="panel-body">
                                <div class="alert alert-danger fade in m-b-30" id="notif_attach" style="display: none;">
                                    <strong>Alert!</strong>
                                    <span id="message_notif_attach">Please wait</span>
                                </div>
                                @csrf

                            </div>
                            <div class="form-group button-attach-container">
                                <label class="col-md-3 control-label">&nbsp;</label>
                                <div class="col-md-9">
                                    <button type="button" class="btn btn-sm btn-danger" name="button_attach" id="button_attach" style="margin-left: 8px;">&nbsp;&nbsp;Attach&nbsp;&nbsp;</button>
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
    <script src="{{ asset("vendor/color_admin/plugins/DataTables/media/js/jquery.dataTables.js") }}"></script>
    <script src="{{ asset("vendor/color_admin/plugins/DataTables/media/js/dataTables.bootstrap.min.js") }}"></script>
    <script>
        $(".preloader-container").hide();
        $(".button-attach-container").show();
        $(".preloader-attach-container").hide();

        $(".first-selected").focus();
        $(".first-selected").select();
        $(document).ready(function() {
            $('#sub-table').dataTable({
                "searching": true,
                "paging": false,
                "info": false,
            });

            $('#sub-table').on('change', function() {
                console.log('masuk');
                table
                    .column(0)
                    .search(this.value)
                    .draw();

            });

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

        .modal.left.fade .modal-dialog {
            left: -320px;
            -webkit-transition: opacity 0.3s linear, left 0.3s ease-out;
            -moz-transition: opacity 0.3s linear, left 0.3s ease-out;
            -o-transition: opacity 0.3s linear, left 0.3s ease-out;
            transition: opacity 0.3s linear, left 0.3s ease-out;
        }

        .modal.left.fade.in .modal-dialog {
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

        .dataTables_filter {
            width: 50%;
            float: right;
            text-align: right;
        }
    </style>
    @stop