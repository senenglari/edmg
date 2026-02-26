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
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
<script type="text/javascript" src="{{ asset('app/js/form_validation.js') }}"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
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

        $('#search-input').on('change', function() {
            table
                .column(0)
                .search(this.value)
                .draw();

        });

    });
</script>

<script type="text/javascript">
    $("#button_save").click(function(event) {

        event.preventDefault();

        $.confirm({
            title: '',
            content: 'Are you sure want to delete this folder? ',
            buttons: {
                confirm: {
                    text: 'Yes',
                    btnClass: 'btn-blue',
                    keys: ['enter', 'shift'],
                    action: function() {
                        $("#myform").submit();
                        // Clear local storage
                        localStorage.clear();
                    }
                },
                cancel: {
                    text: 'Cancel',
                    btnClass: 'btn-red',
                    keys: ['enter', 'shift']
                }
            }
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