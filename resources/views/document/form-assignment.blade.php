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
                    <form class="form-horizontal" id="myformUpdate" name="myformUpdate" action="{{ URL::to('/').$form_act }}" method="post" enctype="multipart/form-data">
                        @csrf
                        @foreach ($fields as $row)
                        {!! $row !!}
                        @endforeach

                        <input type="hidden" class="form-control" id="statusAssignment" name="statusAssignment" value="{{ $statusAssignment }}">
                        <input type="hidden" class="form-control" id="idDoc" name="idDoc" value="{{ $idDoc }}">
                        <div class="table-responsive">
						
						@php
						  $showDeadline = false;
						  if (!empty($comment)) {
							foreach ($comment as $r) {
							  if (!empty($r->start_date)) { // atau !empty($r->end_date)
								$showDeadline = true;
								break;
							  }
							}
						  }
						@endphp
						
                            <table class="table table-bordered" id="table-document">
                                <thead>
                                    <tr>
                                        <th width="20%" style="text-align: center;">Name</th>
                                        <th width="20%" style="text-align: center;">Department</th>
                                        <th width="15%" style="text-align: center;">Discipline</th>
                                        <th width="15%" style="text-align: center;">Position</th>
                                        <th width="15%" style="text-align: center;">Role</th>
										@if($showDeadline)
										  <th>Start Date</th>
										  <th>End Date</th>
										@endif
                                        
                                        <!--th width="10%" style="text-align: center;">Order</th-->
                                        <th width="5%" style="text-align: center;"><img src="{{ asset('app/img/icon/delete.png') }}" height="16"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($comment) == 0)
                                    <tr>
                                        <td colspan="7" style="text-align: center;">No data found (0)</td>
                                    </tr>
                                    @else
                                    @foreach ($comment as $row)
                                    @if ($row->status == 2)
                                    @php
                                    $background = 'background: #d2d7db';
                                    @endphp
                                    @else
                                    @php
                                    $background = '';
                                    @endphp
                                    @endif
                                    <tr>
                                        <td style="text-align: left; {{ $background }}">{{ $row->full_name }}</td>
                                        <td style="text-align: left; {{ $background }}">{{ $row->department_name }}</td>
                                        <td style="text-align: left; {{ $background }}">{{ $row->discipline_name }}</td>
                                        <td style="text-align: left; {{ $background }}">{{ $row->position_name }}</td>
                                        <td style="text-align: center; {{ $background }}">
                                            @if ($row->status != 2)
                                            <input type="hidden" class="form-control" id="comment_temp_id" name="comment_temp_id[]" value="{{ $row->comment_temp_id }}">
                                            <select id="role" name="role[]" class="form-control form-control-sm text-center">
                                                <option selected value="0">-Select-</option>
                                                @foreach ($selectRole as $indexR => $rowRole)
                                                <option class="center" value="{{ $rowRole['id'] }}" {{($rowRole['id']==$row->role ? 'selected' : '')}}>{{ $rowRole['name'] }}</option>
                                                @endforeach
                                            </select>
                                            @else
                                            {{ $row->role }}
                                            @endif
                                        </td>
										@if($showDeadline)
										  <td>{{ !empty($row->start_date) ? displayDMY($row->start_date) : '' }}</td>
										  <td>{{ !empty($row->end_date) ? displayDMY($row->end_date) : '' }}</td>
										@endif
                                        <!--td style="text-align: center; {{ $background }}">
                                            @if ($row->status != 2)
                                            <select id="orderNo" name="orderNo[]" class="form-control form-control-sm text-center">
                                                <option selected value="0">-Select-</option>
                                                @foreach ($selectOrder as $index => $rowOrder)
                                                <option class="center" value="{{ $rowOrder['id'] }}" {{($rowOrder['id']==$row->order_no ? 'selected' : '')}}>{{ $rowOrder['name'] }}</option>
                                                @endforeach
                                            </select>
                                            @else
                                            {{ $row->order_no }}
                                            @endif
                                        </td-->
                                        <input type=hidden name="orderNo[]" value="{{  $row->order_no  }}">
                                        <td style="text-align: center; {{ $background }};" title="">
                                            @if ($row->status != 2)
                                                <a onClick="deleteThis({{ $row->comment_temp_id }})"><img src="{{ asset('app/img/icon/delete.png') }}" height="16" class="delete-item" title="Delete" style="cursor: pointer"></a>
                                            @elseif ($status_document == 2)
                                                <a href="{{ url('/') . '/document/reset_assignment/' . base64_encode($document_id) . '/'. base64_encode($assignment_id) . '/' . base64_encode($row->user_id) }})"><img src="{{ asset('app/img/icon/reset.png') }}" height="16" class="reset-item" title="Reset" style="cursor: pointer"></a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                    @endif

                                </tbody>
                            </table>
                            <div class="form-group button-container">
                                <div class="col-md-12">
                                    <a href="{{ url('document/index') }}" type="button" class="btn btn-sm btn-default">
                                        <i class="fa fa-angle-double-left m-r-2"></i>Back
                                    </a>
                                    <button type="submit" class="btn btn-sm btn-primary text-right" style="float: right;">
                                        <i class="fa fa-save m-r-2"></i>Update
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning text-right m-r-5" style="float: right;" data-toggle="modal" data-target="#modal-document">
                                        <i class="fa fa-plus m-r-2"></i> Add Users
                                    </button>
                                    @if($status_nonaktif != 1)
                                    <button type="button" class="btn btn-sm btn-success text-right m-r-5" onclick="setActive('{{ $id_of_assignment }}')" style="float: right;">
                                        <i class="fa fa-plus m-r-2"></i> Deactivate
                                    </button>
                                    @else
                                    <button type="button" class="btn btn-sm btn-success text-right m-r-5"onclick="setActive('{{ $id_of_assignment }}')" style="float: right;">
                                        <i class="fa fa-plus m-r-2"></i> Activate
                                    </button>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group preloader-container">
                                <div class="col-md-12" style="text-align: right;">
                                    <img src="{{ asset('app/img/icon/preloader.gif') }}" alt="" />
                                </div>
                            </div>
                        </div>
                    </form>
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
                        <form class="form-horizontal" id="myformModal" name="myformModal" action="" method="post" enctype="multipart/form-data">
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

    $("#myformModal").submit(function() {
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

    $("#myformUpdate").submit(function() {
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

    function setActive(params) {
        var data = params.split("@");

        $.ajax({
            url: "<?= URL::to('/') . $activate_url . '/' ?>" + params,
            type: "GET",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                window.location.href = "{{ URL::to('/') }}" + '/document/assignment/' + data[2];
            },
        });
    }

    function deleteThis(id) {
        $.ajax({
            url: "<?= URL::to('/') . $delete_clone_comment_temp_url . '/' ?>" + id,
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
                            <td colspan="7" style="text-align: center;">` + 'No data found (0)' + `</td>
                         </tr>`
                    );
                } else {
                    response.data.forEach((element) => {
                        if (element.status != 2) {
                            var btnDelete = '';
                            var background = '';

                            var rev = (element.role == 'REVIEWER') ? 'selected' : '';
                            var app = (element.role == 'APPROVER') ? 'selected' : '';


                            var no1 = (element.order_no == 1) ? 'selected' : '';
                            var no2 = (element.order_no == 2) ? 'selected' : '';
                            var no3 = (element.order_no == 3) ? 'selected' : '';
                            var no4 = (element.order_no == 4) ? 'selected' : '';
                            var no5 = (element.order_no == 5) ? 'selected' : '';
                            var no6 = (element.order_no == 6) ? 'selected' : '';
                            var no7 = (element.order_no == 7) ? 'selected' : '';
                            var no8 = (element.order_no == 8) ? 'selected' : '';
                            var no9 = (element.order_no == 9) ? 'selected' : '';
                            var no10 = (element.order_no == 10) ? 'selected' : '';
                            var no11 = (element.order_no == 11) ? 'selected' : '';
                            var no12 = (element.order_no == 12) ? 'selected' : '';
                            var no13 = (element.order_no == 13) ? 'selected' : '';
                            var no14 = (element.order_no == 14) ? 'selected' : '';
                            var no15 = (element.order_no == 15) ? 'selected' : '';

                            var selectRole = '<input type="hidden" class="form-control" id="comment_temp_id" name="comment_temp_id[]" value="' + element.comment_temp_id + '"><select id="role" name="role[]" class="form-control form-control-sm text-center">' +
                                '<option  value="REVIEWER"' + rev + '>REVIEWER</option>' +
                                '<option  value="APPROVER"' + app + '>APPROVER</option>' +
                                '</select>';

                            var selectOrder = '<select id="orderNo" name="orderNo[]" class="form-control form-control-sm text-center">' +
                                '<option  value="1"' + no1 + '>1</option>' +
                                '<option  value="2"' + no2 + '>2</option>' +
                                '<option  value="3"' + no3 + '>3</option>' +
                                '<option  value="4"' + no4 + '>4</option>' +
                                '<option  value="5"' + no5 + '>5</option>' +
                                '<option  value="6"' + no6 + '>6</option>' +
                                '<option  value="7"' + no7 + '>7</option>' +
                                '<option  value="8"' + no8 + '>8</option>' +
                                '<option  value="9"' + no9 + '>9</option>' +
                                '<option  value="10"' + no10 + '>10</option>' +
                                '<option  value="11"' + no11 + '>11</option>' +
                                '<option  value="12"' + no12 + '>12</option>' +
                                '<option  value="13"' + no13 + '>13</option>' +
                                '<option  value="14"' + no14 + '>14</option>' +
                                '<option  value="15"' + no15 + '>15</option>' +
                                '</select>';
                        } else {
                            console.log(Math.max(element.order_no));
                            var btnDelete = 'display:none';
                            var background = 'background:#d2d7db';
                            var selectRole = element.role;
                            var selectOrder = element.order_no;

                        }
                        $("#table-document > tbody").append(
                            `<tr>
                            <td style="text-align: left; ` + background + `">` + element.full_name + `</td>
                            <td style="text-align: left; ` + background + `">` + element.department_name + `</td>
                            <td style="text-align: left; ` + background + `">` + element.discipline_name + `</td>
                            <td style="text-align: left; ` + background + `">` + element.position_name + `</td>
                            <td style="text-align: center; ` + background + `">` + selectRole + `</td>
                            <td style="text-align: center; ` + background + `">` + selectOrder + `</td>
                            <td style="text-align: center; ` + background + `" title="` + element.comment_temp_id + `">
                            <a onClick="deleteThis(` + element.comment_temp_id + `)"><img src="{{ asset('app/img/icon/delete.png') }}" height="16" class="delete-item" style="cursor: pointer; ` + btnDelete + `"></a>
                            </td>
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
        var roleId = $("#role_id").val();
        var document_id = $("#document_id").val();
        var assignment_id = $("#assignment_id").val();

        let formData = new FormData();

        formData.append('user_id', user_id);
        formData.append('role_id', roleId);
        formData.append('document_id', document_id);
        formData.append('assignment_id', assignment_id);


        for (var pair of formData.entries()) {
            console.log(pair[0] + ', ' + pair[1]);
        }

        // return false;


        // $(".button-attach-container").hide();
        // $(".preloader-attach-container").show();

        $.ajax({
            url: "<?= URL::to('/') . $save_clone_comment_temp_url ?>",
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
                // console.log(response);
                // return false;
                $("#modal-document .close").click();

                $("#document_file").val("");
                $("#remark").val("");
                $("#table-document > tbody").html("");

                var cloneTemp = response.data.length;
                console.log(cloneTemp);
                if (cloneTemp == 0) {
                    $("#table-document > tbody").append(
                        `<tr>
                            <td colspan="7" style="text-align: center;">` + 'No data found (0)' + `</td>
                         </tr>`
                    );
                } else {
                    response.data.forEach((element) => {
                        if (element.status != 2) {
                            var btnDelete = '';
                            var background = '';

                            var rev = (element.role == 'REVIEWER') ? 'selected' : '';
                            var app = (element.role == 'APPROVER') ? 'selected' : '';
                            var obs = (element.role == 'RESPONSIBILITY') ? 'selected' : '';


                            var no1 = (element.order_no == 1) ? 'selected' : '';
                            var no2 = (element.order_no == 2) ? 'selected' : '';
                            var no3 = (element.order_no == 3) ? 'selected' : '';
                            var no4 = (element.order_no == 4) ? 'selected' : '';
                            var no5 = (element.order_no == 5) ? 'selected' : '';
                            var no6 = (element.order_no == 6) ? 'selected' : '';
                            var no7 = (element.order_no == 7) ? 'selected' : '';
                            var no8 = (element.order_no == 8) ? 'selected' : '';
                            var no9 = (element.order_no == 9) ? 'selected' : '';
                            var no10 = (element.order_no == 10) ? 'selected' : '';
                            var no11 = (element.order_no == 11) ? 'selected' : '';
                            var no12 = (element.order_no == 12) ? 'selected' : '';
                            var no13 = (element.order_no == 13) ? 'selected' : '';
                            var no14 = (element.order_no == 14) ? 'selected' : '';
                            var no15 = (element.order_no == 15) ? 'selected' : '';

                            var selectRole = '<input type="hidden" class="form-control" id="comment_temp_id" name="comment_temp_id[]" value="' + element.comment_temp_id + '"><select id="role" name="role[]" class="form-control form-control-sm text-center">' +
                                '<option  value="REVIEWER"' + rev + '>REVIEWER</option>' +
                                '<option  value="APPROVER"' + app + '>APPROVER</option>' +
                                '<option  value="RESPONSIBILITY"' + obs + '>RESPONSIBILITY</option>' +
                                '</select>';

                            var selectOrder = '<select id="orderNo" name="orderNo[]" class="form-control form-control-sm text-center">' +
                                '<option  value="1"' + no1 + '>1</option>' +
                                '<option  value="2"' + no2 + '>2</option>' +
                                '<option  value="3"' + no3 + '>3</option>' +
                                '<option  value="4"' + no4 + '>4</option>' +
                                '<option  value="5"' + no5 + '>5</option>' +
                                '<option  value="6"' + no6 + '>6</option>' +
                                '<option  value="7"' + no7 + '>7</option>' +
                                '<option  value="8"' + no8 + '>8</option>' +
                                '<option  value="9"' + no9 + '>9</option>' +
                                '<option  value="10"' + no10 + '>10</option>' +
                                '<option  value="11"' + no11 + '>11</option>' +
                                '<option  value="12"' + no12 + '>12</option>' +
                                '<option  value="13"' + no13 + '>13</option>' +
                                '<option  value="14"' + no14 + '>14</option>' +
                                '<option  value="15"' + no15 + '>15</option>' +
                                '</select>';
                        } else {
                            console.log(Math.max(element.order_no));
                            var btnDelete = 'display:none';
                            var background = 'background:#d2d7db';
                            var selectRole = element.role;
                            var selectOrder = element.order_no;

                        }
                        $("#table-document > tbody").append(
                            `<tr>
                            <td style="text-align: left; ` + background + `">` + element.full_name + `</td>
                            <td style="text-align: left; ` + background + `">` + element.department_name + `</td>
                            <td style="text-align: left; ` + background + `">` + element.discipline_name + `</td>
                            <td style="text-align: left; ` + background + `">` + element.position_name + `</td>
                            <td style="text-align: center; ` + background + `">` + selectRole + `</td>
                            <td style="text-align: center; ` + background + `">` + selectOrder + `</td>
                            <td style="text-align: center; ` + background + `" title="` + element.comment_temp_id + `">
                            <a onClick="deleteThis(` + element.comment_temp_id + `)"><img src="{{ asset('app/img/icon/delete.png') }}" height="16" class="delete-item" style="cursor: pointer; ` + btnDelete + `"></a>
                            </td>
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