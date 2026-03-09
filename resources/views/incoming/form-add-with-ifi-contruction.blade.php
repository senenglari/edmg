@extends('main')
@section('content')

<script>
    var baseUrl = "{{ url('/') }}"; // ini akan jadi https://dzaries.my.id/edms/public
</script>


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
                                                @php
                                                $path               = public_path('uploads' . $row->document_url . $row->document_file);
                                                $isExists_file      = file_exists($path);
                                                @endphp
                                                <a href="{{ url('/uploads/') . $row->document_url . $row->document_file }}" target="_blank" data-toggle="tooltip" title="{{$row->document_file}}">
                                                    @if($isExists_file)
                                                    <img src="{{ asset('app/img/icon/eye.png') }}" height="16" class="view-item" style="cursor: pointer">
                                                    @else
                                                    <img src="{{ asset('app/img/icon/remove.png') }}" height="16" class="view-item" style="cursor: pointer">
                                                    @endif
                                                </a>
                                                @if($row->issue_status_id != 13 && $row->issue_status_id != 18)
                                                    @php
                                                    $path               = public_path('uploads' . $row->document_url . $row->document_crs);
                                                    $isExists_crs       = file_exists($path);
                                                    @endphp
                                                <a href="{{ url('/uploads/') . $row->document_url . $row->document_crs }}" target="_blank" data-toggle="tooltip" title="{{$row->document_file}}">
                                                    @if($isExists_file)
                                                    <img src="{{ asset('app/img/icon/eye.png') }}" height="16" class="view-item" style="cursor: pointer">
                                                    @else
                                                    <img src="{{ asset('app/img/icon/remove.png') }}" height="16" class="view-item" style="cursor: pointer">
                                                    @endif
                                                </a>
                                                @endif
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
                            <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modal-document-ifi">
                                Add New IFI
                            </button>
                            <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modal-document-ifi-contruction">
                                Add New IFC (Construction)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DOCUMENT --}}
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

    {{-- MODAL IFI --}}
    <div class="container demo">
        <div class="modal right fade" id="modal-document-ifi" tabindex="-1" role="dialog" aria-labelledby="modal-document-ifi">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel2">Attach Document IFI</h4>
                    </div>
                    <div class="modal-body">
                        <div class="panel-body">
                            <div class="alert alert-danger fade in m-b-30" id="notif_attach_ifi" style="display: none;">
                                <strong>Alert!</strong>
                                <span id="message_notif_attach_ifi">Please wait</span>
                            </div>
                            @csrf
                            @foreach ($fields_modal_ifi as $row)
                                {!! $row !!}
                            @endforeach
                        </div>
                        <div class="form-group button-attach-container">
                            <label class="col-md-3 control-label">&nbsp;</label>
                            <div class="col-md-9">
                                <button type="button" class="btn btn-sm btn-danger" name="button_attach_ifi" id="button_attach_ifi" style="margin-left: 8px;">&nbsp;&nbsp;Attach&nbsp;&nbsp;</button>
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

    {{-- MODAL IFC CONSTRUCTION --}}
    <div class="container demo">
        <div class="modal right fade" id="modal-document-ifi-contruction" tabindex="-1" role="dialog" aria-labelledby="modal-document-ifi-contruction">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel2">Attach Document IFC (Contruction)</h4>
                    </div>
                    <div class="modal-body">
                        <div class="panel-body">
                            <div class="alert alert-danger fade in m-b-30" id="notif_attach_ifi_contruction" style="display: none;">
                                <strong>Alert!</strong>
                                <span id="message_notif_attach_ifi_contruction">Please wait</span>
                            </div>
                            @csrf
                            @foreach ($fields_modal_ifi_contruction as $row)
                                {!! $row !!}
                            @endforeach
                        </div>
                        <div class="form-group button-attach-container">
                            <label class="col-md-3 control-label">&nbsp;</label>
                            <div class="col-md-9">
                                <button type="button" class="btn btn-sm btn-danger" name="button_attach_ifi_contruction" id="button_attach_ifi_contruction" style="margin-left: 8px;">&nbsp;&nbsp;Attach&nbsp;&nbsp;</button>
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

{{-- LOAD PDF-LIB (needed for normalize) --}}
<script src="https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>

<script>
    $(".preloader-container").hide();
    $(".button-attach-container").show();
    $(".preloader-attach-container").hide();

    $(".first-selected").focus();
    $(".first-selected").select();

    // ==== PDF NORMALIZE HELPER ====
    async function normalizePdfFile(file) {
        const bytes = await file.arrayBuffer();
        const { PDFDocument } = PDFLib;
        const pdfDoc = await PDFDocument.load(bytes, { ignoreEncryption: false });

        const normalizedBytes = await pdfDoc.save({
            useObjectStreams: false,
            updateFieldAppearances: true,
            addDefaultPage: false
        });

        return new File(
            [normalizedBytes],
            file.name.replace(/\.pdf$/i, "") + "_normalized.pdf",
            { type: "application/pdf" }
        );
    }

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
                    if(element.issue_status_id == 13) {
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
                                    </td>
                                    <td style="text-align: center;" title="` + element.incoming_transmittal_detail_temp_id + `"><a onClick="deleteThis(` + element.incoming_transmittal_detail_temp_id + `)"><img src="{{ asset('app/img/icon/delete.png') }}" height="16" class="delete-item" style="cursor: pointer"></a></td>
                                 </tr>`
                            );
                        } else if(element.issue_status_id == 18) {
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
                                    </td>
                                    <td style="text-align: center;" title="` + element.incoming_transmittal_detail_temp_id + `"><a onClick="deleteThis(` + element.incoming_transmittal_detail_temp_id + `)"><img src="{{ asset('app/img/icon/delete.png') }}" height="16" class="delete-item" style="cursor: pointer"></a></td>
                                 </tr>`
                            );
                        } else {
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
                        }

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

    // ✅ UPDATED: async + normalize before upload
    $("#button_attach").click(async function() {
        $("#notif_attach").hide();

        if (typeof PDFLib === "undefined") {
            $("#message_notif_attach").text("pdf-lib gagal load (unpkg blocked).");
            $("#notif_attach").show();
            return false;
        }

        const document_file_raw     = $('#document_file')[0].files[0];
        const document_crs_raw      = $('#document_crs')[0].files[0];
        var document_id             = $("#document_id").val();
        var issue_status_id         = $("#issue_status_id").val();
        var return_status_id        = $("#return_status_id").val();
        var document_status_id      = $("#document_status_id").val();
        var remark                  = $("#remark").val();
        var project_id              = $("#project_id").val();
        
        if(issue_status_id == 0) {
            $("#message_notif_attach").text("Issue status is required issue_status_id");
            $("#notif_attach").show();
            return false;
        }
        if(document_status_id == 0) {
            $("#message_notif_attach").text("Document status is required document_status_id");
            $("#notif_attach").show();
            return false;   
        }
        if(document_id == null) {
            $("#message_notif_attach").text("Document is required document_id");
            $("#notif_attach").show();
            return false;   
        }
        if(!document_file_raw) {
            $("#message_notif_attach").text("Document file is required");
            $("#notif_attach").show();
            return false;   
        }

        let document_file = document_file_raw;
        let document_crs  = document_crs_raw;

        try {
            if (document_file_raw && document_file_raw.type === "application/pdf") {
                document_file = await normalizePdfFile(document_file_raw);
                console.log("DOC normalized:", document_file_raw.name, "=>", document_file.name);
            }
            if (document_crs_raw && document_crs_raw.type === "application/pdf") {
                document_crs = await normalizePdfFile(document_crs_raw);
                console.log("CRS normalized:", document_crs_raw.name, "=>", document_crs.name);
            }
        } catch (e) {
            console.error(e);
            $("#message_notif_attach").text("Gagal normalize PDF (mungkin encrypted/kompleks).");
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
        formData.append('project_id', project_id);
        formData.append('remark', remark);

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
                        if(element.issue_status_id == 13) {
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
                                    </td>
                                    <td style="text-align: center;" title="` + element.incoming_transmittal_detail_temp_id + `"><a onClick="deleteThis(` + element.incoming_transmittal_detail_temp_id + `)"><img src="{{ asset('app/img/icon/delete.png') }}" height="16" class="delete-item" style="cursor: pointer"></a></td>
                                 </tr>`
                            );
                        } else if(element.issue_status_id == 18) {
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
                                    </td>
                                    <td style="text-align: center;" title="` + element.incoming_transmittal_detail_temp_id + `"><a onClick="deleteThis(` + element.incoming_transmittal_detail_temp_id + `)"><img src="{{ asset('app/img/icon/delete.png') }}" height="16" class="delete-item" style="cursor: pointer"></a></td>
                                 </tr>`
                            );
                        } else {
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
                        }

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
                console.log(jqXHR, textStatus, errorThrown);
                $("#message_notif_attach").text(errorThrown);
                $("#notif_attach").show();
                $(".button-attach-container").show();
                $(".preloader-attach-container").hide();
            }
        });

        return false;
    });

    // NOTE: handlers lain (#button_attach_ifi, #button_attach_ifi_contruction) tetap seperti file kamu
    // Kalau IFI/IFC juga perlu normalize, bilang ya — aku tambahin juga dengan pola yang sama.
	
	$(document).ready(function () {
function reloadRevisionOptions(issueId) {
    if (!issueId || issueId == 0) {
        $('#document_status_id').html('<option value="0">-Pilih-</option>');
        $('.selectpicker').selectpicker('refresh');
        return;
    }

    $.get(baseUrl +`/incoming/issue-status/document-status/${issueId}`, function (res) {
        let opt = '<option value="0">-Pilih-</option>';
        if (res.data && res.data.length > 0) {
            res.data.forEach(v => {
                //opt += `<option value="${v.id}">${v.name}</option>`;
                opt += `<option value="${v.id}">${v.new_revision || v.name}</option>`;
            });
        } else {
            opt += '<option value="0">Tidak ada revision untuk status ini</option>';
        }
        $('#document_status_id').html(opt);
        $('.selectpicker').selectpicker('refresh');
    }).fail(function(jqXHR) {
        $('#document_status_id').html('<option value="0">Gagal memuat (Error ' + jqXHR.status + ')</option>');
        $('.selectpicker').selectpicker('refresh');
    });
}
  
  function loadAutoRevision(documentId, issueId) {
    if (!documentId || !issueId) return;
    $.get('/incoming/auto-revision/' + documentId + '/' + issueId, function(res) {
        $('#document_status_id').val(res.document_status_id);
        $('.selectpicker').selectpicker('refresh');
    });
}



function autoSetRevision() {
    let docId = $('#document_id').val();
    let issueId = $('#issue_status_id').val();

    if (!docId || !issueId || issueId == 0) return;

    $.get('/incoming/auto-revision/' + docId + '/' + issueId, function(res) {
        console.log("Auto revision response:", res);
        if (res.document_status_id && res.document_status_id != 0) {
            $('#document_status_id').val(res.document_status_id);
            $('.selectpicker').selectpicker('refresh');
        }
    }).fail(function(err) {
        console.error("Auto revision gagal:", err);
    });
}

// Panggil saat document_id atau issue_status_id berubah
$('#document_id, #issue_status_id').on('change', autoSetRevision);

// Panggil sekali saat modal dibuka (kalau edit)
$('#modal-document').on('shown.bs.modal', autoSetRevision);



$('select#issue_status_id').on('change', function() {
    let issueId = $(this).val();
    let docId = $('#document_id').val(); // kalau document_id sudah dipilih
    reloadRevisionOptions(issueId);
    loadAutoRevision(docId, issueId); // auto set revision
});

// Kalau document_id berubah juga trigger
$('#document_id').on('change', function() {
    let issueId = $('#issue_status_id').val();
    if (issueId && issueId != 0) {
        loadAutoRevision($(this).val(), issueId);
    }
});

  // on change
  $('select#issue_status_id').on('change', function () {
    reloadRevisionOptions($(this).val());
  });

  // trigger sekali supaya langsung keisi kalau issue_status_id sudah ada value
  reloadRevisionOptions($('select#issue_status_id').val());
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