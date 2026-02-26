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
                        <input type="hidden" id="text_row_attachment" name="text_row_attachment" value="0">
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
                                    <th width="18%" style="text-align: center;">Document Number</th>
                                    <th style="text-align: center;">Title</th>
                                    <th width="12%" style="text-align: center;">Issue Status</th>
                                    <th width="12%" style="text-align: center;">Revision Number</th>
                                    <th width="12%" style="text-align: center;"><img src="{{ asset('app/img/icon/eye.png') }}" height="16"></th>
                                    <th width="5%" style="text-align: center;"><img src="{{ asset('app/img/icon/delete.png') }}" height="16"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($items) > 0)
                                    @foreach($items AS $index => $row)
                                        <tr>
                                            <td style="text-align: center;">{{ $row->document_no }}</td>
                                            <td>{{ $row->document_title }}</td>
                                            <td style="text-align: center;">{{ $row->issue_status_name }}</td>
                                            <td style="text-align: center;">{{ $row->document_status_name }}</td>
                                            <td style="text-align: center;">
                                                <a href="{{ url('/uploads/') . $row->document_url . $row->document_file }}" target="_blank">
                                                    <img src="{{ asset('app/img/icon/eye.png') }}" height="16" class="view-item" style="cursor: pointer">
                                                </a>
                                                <a href="{{ url('/uploads/') . $row->document_url . $row->document_crs }}" target="_blank">
                                                    <img src="{{ asset('app/img/icon/eye.png') }}" height="16" class="view-item" style="cursor: pointer">
                                                </a>
                                            </td>
                                            <td style="text-align: center;">
                                                <a onClick="deleteThis('{{ $row->incoming_transmittal_detail_temp_id }}')" title="Hapus">
                                                    <img src="{{ asset('app/img/icon/delete.png') }}" height="16" class="delete-item" style="cursor: pointer">
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" style="text-align: center;">No data found (0)</td>
                                    </tr>
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
<script>
    $(".preloader-container").hide();
    $(".button-attach-container").show();
    $(".preloader-attach-container").hide();

    $(".first-selected").focus();
    $(".first-selected").select();

    $("#myform").submit(function(){
        var flag = "F";
        var no = 1;
        var row_attach = $("#text_row_attachment").val();

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

        if(row_attach == 0) {
            alert('No attachment found (0)');
            return false;   
        }

        $(".button-container").hide();
        $(".preloader-container").show();
    });


    function deleteThis(id) {
        $.ajax({
            url: "<?=URL::to('/').$delete_url.'/'?>" + id,
            type: "GET",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                $("#table-document > tbody").html("");
                
                var num = 0;
                response.data.forEach((element) => {
                    $("#table-document > tbody").append(
                        `<tr>
                            <td style="text-align: center;">` + element.document_no + `</td>
                            <td>` + element.document_title + `</td>
                            <td style="text-align: center;">` + element.issue_status_name + `</td>
                            <td style="text-align: center;">` + element.document_status_name + `</td>
                            <td style="text-align: center;" title="` + element.incoming_transmittal_detail_temp_id + `">
                                <a href="{{ asset('uploads') }}` + element.document_url + element.document_file + `" target="_blank">
                                    <img src="{{ asset('app/img/icon/eye.png') }}" height="16" class="view-item" style="cursor: pointer">
                                </a>
                                <a href="{{ asset('uploads') }}` + element.document_url + element.document_crs + `" target="_blank">
                                    <img src="{{ asset('app/img/icon/eye.png') }}" height="16" class="view-item" style="cursor: pointer">
                                </a>
                            </td>
                            <td style="text-align: center;" title="` + element.incoming_transmittal_detail_temp_id + `"><a onClick="deleteThis(` + element.incoming_transmittal_detail_temp_id + `)"><img src="{{ asset('app/img/icon/delete.png') }}" height="16" class="delete-item" style="cursor: pointer"></a></td>
                         </tr>`
                    );

                    num = num + 1;
                });

                $("#text_row_attachment").val(num);
                $(".button-attach-container").show();
                $(".preloader-attach-container").hide();
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

    $("#button_attach").click(function() {
        $("#notif_attach").hide();

        const document_file     = $('#document_file')[0].files[0];
        const document_crs      = $('#document_crs')[0].files[0];
        var document_id         = $("#document_id").val();
        var issue_status_id     = $("#issue_status_id").val();
        var return_status_id    = $("#return_status_id").val();
        var document_status_id  = $("#document_status_id").val();
        var remark              = $("#remark").val();
        
        if(issue_status_id == 0) {
            $("#message_notif_attach").text("Issue status is required");
            $("#notif_attach").show();
            return false;
        }
        if(document_status_id == 0) {
            $("#message_notif_attach").text("Document status is required");
            $("#notif_attach").show();
            return false;   
        }
        if(document_id == null) {
            $("#message_notif_attach").text("Document is required");
            $("#notif_attach").show();
            return false;   
        }
        if(document_crs == null) {
            $("#message_notif_attach").text("CRS is required");
            $("#notif_attach").show();
            return false;   
        }

        let formData = new FormData();

        formData.append('document_file', document_file);
        formData.append('document_crs', document_crs);
        formData.append('document_id', document_id);
        formData.append('issue_status_id', issue_status_id);
        formData.append('return_status_id', return_status_id);
        formData.append('return_status_id', return_status_id);
        formData.append('document_status_id', document_status_id);
        formData.append('remark', remark);

        // $(".button-attach-container").hide();
        // $(".preloader-attach-container").show();
        
        $.ajax({
            url: "<?=URL::to('/').$attach_url?>",
            type: "POST",
            data: formData,
            "Content-Type": "multipart/form-data",
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                // $("#notif_attach").show();
                $(".button-attach-container").hide();
                $(".preloader-attach-container").show();
            },
            success: function (response) {
                if(response.status == "000") {
                    $("#message_notif_attach").text(response.message);
                    $("#notif_attach").show();

                    $(".button-attach-container").show();
                    $(".preloader-attach-container").hide();
                } else {
                    $("#notif_attach").hide();
                    $("#modal-document .close").click();

                    $("#document_file").val("");
                    $("#document_crs").val("");
                    $("#remark").val("");
                    $("#table-document > tbody").html("");

                    var num = 0;
                    response.data.forEach((element) => {
                        $("#table-document > tbody").append(
                            `<tr>
                                <td style="text-align: center;">` + element.document_no + `</td>
                                <td>` + element.document_title + `</td>
                                <td style="text-align: center;">` + element.issue_status_name + `</td>
                                <td style="text-align: center;">` + element.document_status_name + `</td>
                                <td style="text-align: center;" title="` + element.incoming_transmittal_detail_temp_id + `">
                                    <a href="{{ asset('uploads') }}` + element.document_url + element.document_file + `" target="_blank">
                                        <img src="{{ asset('app/img/icon/eye.png') }}" height="16" class="view-item" style="cursor: pointer">
                                    </a>
                                    <a href="{{ asset('uploads') }}` + element.document_url + element.document_crs + `" target="_blank">
                                        <img src="{{ asset('app/img/icon/eye.png') }}" height="16" class="view-item" style="cursor: pointer">
                                    </a>
                                </td>
                                <td style="text-align: center;" title="` + element.incoming_transmittal_detail_temp_id + `"><a onClick="deleteThis(` + element.incoming_transmittal_detail_temp_id + `)"><img src="{{ asset('app/img/icon/delete.png') }}" height="16" class="delete-item" style="cursor: pointer"></a></td>
                             </tr>`
                        );

                        num = num + 1;
                    });

                    $("#text_row_attachment").val(num);

                    $("#issue_status_id").val(0);
                    $("#document_status_id").val(0);
                    $('#issue_status_id').selectpicker('refresh');
                    $('#document_status_id').selectpicker('refresh');
                    $("#issue_status_id").change();

                    $(".button-attach-container").show();
                    $(".preloader-attach-container").hide();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $("#message_notif_attach").text(errorThrown);
                $("#notif_attach").show();
                $(".button-attach-container").show();
                $(".preloader-attach-container").hide();
            }
        });

        return false;
    });

    $(document).ready(function () {
        $("select#issue_status_id").change(function(event, ui) {
            var val = $(this).val();

            $.get('{{ route('issue_status.document_status') }}/'+val, function (res) {
                // var opt = '<option selected="selected" value="pilih">-Pilih-</option>';
                var opt = '';
                
                $.each(res.data, function (index, value) {
                    opt += '<option value="'+value.id.valueOf()+'">'+value.name+'</option>';
                });

                if(res.data.length == 0) {
                    opt = '<option selected="selected" value="pilih">-Pilih-</option>';
                }

                $('#document_status_id').html(opt);
                $('.selectpicker').selectpicker('refresh');

            }, 'json')
        });
    })
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
