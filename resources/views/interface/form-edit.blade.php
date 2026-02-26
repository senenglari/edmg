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
                        <input type="hidden" id="text_row_attachment" name="text_row_attachment" value="{{ count($items) }}">
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
                    <div class="row">
                        <table id="sub-table" class="table table-bordered" id="table-document" width="100%">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">Subfolder</th>
                                    <th style="text-align: center;">Created By</th>
                                    <th style="text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($items) != 0)
                                @foreach($items AS $index => $row)
                                <tr id="tr_item_{{$row->interface_data_subfolder_id}}">
                                    @if ($row->status == 1)
                                    @php
                                    $color = '';
                                    @endphp
                                    @else
                                    @php
                                    $color = 'color:red';
                                    @endphp
                                    @endif
                                    <td style="text-align: center; {{ $color }}">{{ $row->subfolder_name }}</td>
                                    <td style="text-align: center; {{ $color }}">{{ $row->name }}</td>
                                    <td style="text-align: center;">
                                        @php
                                        $encrypt = base64_encode($idfolder . '|' . $row->interface_data_subfolder_id);
                                        @endphp
                                        @if ($row->status == 1)
                                        <a href="{{ $detail . $encrypt }}" class="btn btn-xs btn-warning" title="Detail" id="btn_detail_{{$row->interface_data_subfolder_id}}"><i class="fa fa-eye"></i></a>
                                        <a href="{{ $upload_subfolder . $encrypt }}" class="btn btn-xs btn-primary" title="Upload" id="btn_upload_{{$row->interface_data_subfolder_id}}"><i class="fa fa-upload"></i></a>
                                        <a onClick="deleteThis('{{ $row->interface_data_subfolder_id }}' , '{{$row->interface_data_id}}')" class="btn btn-xs btn-danger" title="Delete" id="btn_delete_{{$row->interface_data_subfolder_id}}"><i class="fa fa-trash"></i></a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="form-group button-container">
                            <a href="{{ $callback }}">
                                <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modal-document">
                                    Add Subfolder
                                </button>
                            </a>
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

    $("#myform").submit(function() {
        var flag = "F";
        var no = 1;
        var row_attach = $("#text_row_attachment").val();

        $(".mandatory-input").each(function() {
            var obj = $(this).attr("name");

            if (obj != 'document_file') {
                if ((trim($(this).val()) == "") || (trim($(this).val()) == "0") || (trim($(this).val()) == "__/__/____") || (trim($(this).val()) == "00/00/0000")) {
                    $(this).css("border-color", "red");
                    $(".style_form_input_" + obj).css("border-color", "red");
                    $(".style_form_input_" + obj).css("color", "red");

                    if (no == 1) {
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

        if (flag == "T") {
            return false;
        }

        if (row_attach == 0) {
            alert('No attachment found (0)');
            return false;
        }

        $(".button-container").hide();
        $(".preloader-container").show();
    });
</script>

<script type="text/javascript">
    function deleteThis(detail, id) {

        $.ajax({
            url: "<?= URL::to('/') . $delete . '/' ?>" + detail + '/' + id,
            type: "GET",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {

                if (response['status'] == "000") {
                    console.log(response);
                    $.alert({
                        title: 'Warning !',
                        content: 'Subfolders cannot be deleted',
                        type: 'orange',
                        buttons: {
                            ok: {
                                text: 'Ok',
                                btnClass: 'btn-orange',
                            }
                        }
                    });
                } else {
                    $("#tr_item_" + detail).css("color", "red");
                    $("#btn_delete_" + detail).remove();
                    $("#btn_detail_" + detail).remove();
                    $("#btn_upload_" + detail).remove();

                    $(".button-attach-container").show();
                    $(".preloader-attach-container").hide();
                }
            },
            beforeSend: function() {
                $(".button-attach-container").hide();
                $(".preloader-attach-container").show();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $(".button-attach-container").show();
                $(".preloader-attach-container").hide();
            }
        });
    }

    function addZero(i) {
        if (i < 10) {
            i = "0" + i
        }
        return i;
    }
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