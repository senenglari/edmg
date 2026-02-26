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
                    <h4 class="panel-title">Assignment Document(s)</h4>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="table-document">
                            <thead>
                                <tr>
                                    <th width="20%" style="text-align: center;">Name</th>
                                    <th width="20%" style="text-align: center;">Department</th>
                                    <th width="20%" style="text-align: center;">Discipline</th>
                                    <th width="13%" style="text-align: center;">Position</th>
                                    <th width="10%" style="text-align: center;">Role</th>
                                    <th width="5%" style="text-align: center;"><img src="{{ asset('app/img/icon/delete.png') }}" height="16"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" style="text-align: center;">No data found (0)</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="form-group button-container">
                            <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modal-document">
                                Add Assignment Users
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
                        <h4 class="modal-title" id="myModalLabel2">Add Assignment User</h4>
                    </div>
                    <div class="modal-body">
                        <div class="panel-body">
                            @csrf
                            @foreach ($fields_modal as $row)
                            {!! $row !!}
                            @endforeach
                        </div>
                        <div class="form-group button-attach-container">
                            <label class="col-md-3 control-label">&nbsp;</label>
                            <div class="col-md-9">
                                <button type="button" class="btn btn-sm btn-primary" name="button_save_modal" id="button_save_modal" style="margin-left: 8px;">&nbsp;&nbsp;Save&nbsp;&nbsp;</button>
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

    $("#myform").submit(function() {
        var flag = "F";
        var no = 1;

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

        $(".button-container").hide();
        $(".preloader-container").show();
    });


    function deleteThis(id) {
        $.ajax({
            url: "<?= URL::to('/') . $delete_modal_url . '/' ?>" + id,
            type: "GET",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $("#table-document > tbody").html("");

                var totalData = response.data.length;
                console.log(totalData);
                if (totalData == 0) {
                    $("#table-document > tbody").append(
                        `<tr>
                            <td colspan="6" style="text-align: center;">` + 'No data found (0)' + `</td>
                         </tr>`
                    );
                } else {
                    response.data.forEach((element) => {
                        $("#table-document > tbody").append(
                            `<tr>
                            <td>` + element.full_name + `</td>
                            <td>` + element.department_name + `</td>
                            <td>` + element.discipline_name + `</td>
                            <td>` + element.position_name + `</td>
                            <td style="text-align: center;">` + element.role + `</td>
                            <td style="text-align: center;" title="` + element.comment_temp_id + `"><a onClick="deleteThis(` + element.comment_temp_id + `)"><img src="{{ asset('app/img/icon/delete.png') }}" height="16" class="delete-item" style="cursor: pointer"></a></td>
                         </tr>`
                        );
                    });
                }

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
                console.log(textStatus, errorThrown);
            }
        });
    }

    $("#button_save_modal").click(function() {
        var user_id = $("#user_id").val();
        var role = $("#role").val();

        let formData = new FormData();

        formData.append('user_id', user_id);
        formData.append('role', role);


        // $(".button-attach-container").hide();
        // $(".preloader-attach-container").show();

        $.ajax({
            url: "<?= URL::to('/') . $save_modal_url ?>",
            type: "POST",
            data: formData,
            "Content-Type": "multipart/form-data",
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $(".button-attach-container").hide();
                $(".preloader-attach-container").show();
            },
            success: function(response) {
                $("#modal-document .close").click();

                $("#document_file").val("");
                $("#remark").val("");
                $("#table-document > tbody").html("");

                var totalData = response.data.length;
                console.log(totalData);
                if (totalData == 0) {
                    $("#table-document > tbody").append(
                        `<tr>
                            <td colspan="6" style="text-align: center;">` + 'No data found (0)' + `</td>
                         </tr>`
                    );
                } else {
                    response.data.forEach((element) => {
                        $("#table-document > tbody").append(
                            `<tr>
                            <td>` + element.full_name + `</td>
                            <td>` + element.department_name + `</td>
                            <td>` + element.discipline_name + `</td>
                            <td>` + element.position_name + `</td>
                            <td style="text-align: center;">` + element.role + `</td>
                            <td style="text-align: center;" title="` + element.comment_temp_id + `"><a onClick="deleteThis(` + element.comment_temp_id + `)"><img src="{{ asset('app/img/icon/delete.png') }}" height="16" class="delete-item" style="cursor: pointer"></a></td>
                         </tr>`
                        );
                    });
                }


                $(".button-attach-container").show();
                $(".preloader-attach-container").hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $(".button-attach-container").show();
                $(".preloader-attach-container").hide();
            }
        });

        return false;
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
</style>
@stop