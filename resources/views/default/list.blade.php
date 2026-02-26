@extends('main')
@section('content')
<div id="content" class="content">
    <form id="myform" name="myform" action="{{ URL::to('/').$form_act }}" method="post" autocomplete="off">
        @csrf
        <ol class="breadcrumb pull-right">
            <li><a href="javascript:;">Home</a></li>
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
                        <h4 class="panel-title">{{ $title }}&nbsp;&nbsp;&nbsp;
                            @if (!empty($filtered_info))
                            @foreach ($filtered_info as $row)
                            <span class="label label-danger">{{ $row }}</span>
                            @endforeach
                            @endif
                        </h4>
                    </div>
                    <div class="alert alert-danger" id="alert-box" style="{{ (Session::has("error_message")) ? "" : "display:none;" }}">
                        <i class="fa fa-times-circle fa-fw"></i> <span id="alert-message">{{ (Session::has("error_message")) ? Session::get("error_message") : "" }}</span>
                    </div>
                    <div class="alert alert-success" id="success-box" style="{{ (Session::has("success_message")) ? "" : "display:none;" }}; border-radius: 0 !important;">
                        <i class="fa fa-times-circle fa-fw"></i> <span id="success-message">{{ (Session::has("success_message")) ? Session::get("success_message") : "" }}</span>
                    </div>
                    <div class="alert alert-success" id="info-box" style="{{ (Session::has("info_message")) ? "" : "display:none;" }}; border-radius: 0 !important;">
                        <i class="fa fa-times-circle fa-fw"></i> <span id="info-message">{{ (Session::has("info_message")) ? Session::get("info_message") : "" }}</span>
                    </div>
                    <div class="alert alert-danger" id="error-box" style="{{ (Session::has("error_log")) ? "" : "display:none;" }} border-radius: 0 !important;">
                        @if(Session::get("id_log") == 0)
                        <i class="fa fa-times-circle fa-fw"></i> <span id="error-message">{{ Session::get("error_log") }}.</span>
                        @else
                        <i class="fa fa-times-circle fa-fw"></i> <span id="error-message">{{ Session::get("error_log") }}. <a href="javascript:void(0)" class="error_link" id="button_error" title="{{ url('/error/' . Session::get("id_log")) }}">Lihat pesan error</a></span>
                        @endif
                    </div>
                    <div class="panel-body">
                        @if (empty($hide_simple_search))
                        <div class="col-md-3 pull-right" style="position: absolute; right: 0; margin-right: 10px;">
                            <input type="text" class="form-control" placeholder="Search ..." name="text_search" id="text_search" value="{{ $text_search }}">
                        </div>
                        @endif
                        <p>
                            @php
                            $isSingle = "FALSE";
                            @endphp

                            @foreach ($action as $action_menu)
                            {!! getActionButton($action_menu->name, $action_menu->url, $action_menu->icon) !!}

                            @php
                            $var = explode("|", $action_menu->icon);
                            @endphp

                            @if ($var[0] == "single-modal")
                            @php
                            $isSingle = "TRUE";
                            @endphp
                            @endif

                            @endforeach

                            @if (!empty($adv_search))
                            <a href="javascript:;" class="btn btn-sm btn-success m-b-5" id="search-panel-botton"><i class="fa fa-search"></i></a>
                            @endif
                        </p>
                        <div style="margin-bottom: 15px; display: none;" id="search-panel-body">
                            <form class="form-horizontal" id="myform" name="myform" action="{{ URL::to('/').$form_act }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <h4>Search</h4>
                                @if (!empty($adv_search))
                                @foreach ($fields as $row)
                                {!! $row !!}
                                @endforeach

                                @foreach ($buttons as $row)
                                {!! $row !!}
                                @endforeach
                                @endif
                        </div>
                        <div class="table-responsive">
                            <table id="data-table" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        @foreach ($table_header as $header)
                                        <th width="{{ $header["width"] }}" style="text-align:{{ $header["align"] }}; border: 0px; font-weight: bold;">{{ $header["label"] }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($select) == 0)
                                    <tr>
                                        <td colspan="{{ count($table_header) }}" style="text-align: center; background-color: #fff">No data found (0)</td>
                                    </tr>
                                    @else
                                        @php 
                                            $No = 0;
                                        @endphp

                                        @foreach ($select as $rs)
                                        <tr>
                                            @foreach ($table_header as $row)
                                            @php ($field = $row["name"]) @endphp

                                            <td style="width:{{ $row["width"] }}; text-align:{{ $row["item-align"] }};">
                                                @if ($row["item-format"] == "number")
                                                {{ number_format($rs->$field, 0) }}
                                                @elseif ($row["item-format"] == "flag")
                                                {!! getLabelFlag($rs->$field) !!}
                                                @elseif ($row["item-format"] == "checkbox")
                                                <input type="checkbox" id="checkbox_id_<?= $No ?>" name="checkbox_id[]" class="checkbox_id" value="{{ encodedData($rs->$field) }}">
                                                @elseif ($row["item-format"] == "checkbox")
                                                @else
                                                {{ $rs->$field }}
                                                @endif
                                            </td>
                                            @endforeach
                                        </tr>
                                            @php
                                            $No = $No + 1
                                            @endphp
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-sm-15">
                                {!! $pagging !!}
                            </div>
                            <div class="col-sm-5">
                                <div class="dataTables_info">
                                    @if (empty($select))
                                        No data found | (0) data
                                    @else
                                        Found ({{$select->total()}}) rows
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="text_control" name="text_control" value="hide">
        <input type="hidden" id="text_checked_control" name="text_checked_control" value="ERROR">
        <input type="hidden" id="text_checked_single" name="text_checked_single" value="{{ $isSingle }}">
        <input type="hidden" id="text_url_modal" name="text_url_modal" value="{{ (!empty($form_act_modal)) ? URL::to('/').$form_act_modal : "" }}">
        <form>
</div>
<script>
    $("#success-box").delay(6000).slideUp(500);
    $("#alert-box").delay(6000).slideUp(500);

    $("#search-panel-botton").click(function() {
        var control = $("#text_control").val();

        if (control == "hide") {
            $("#search-panel-body").show();
            $("#text_control").val("show");
        } else {
            $("#search-panel-body").hide();
            $("#text_control").val("hide");
        }
    });

    $("#button_modal").click(function() {
        var url = $("#text_url_modal").val();
        var param = $("#text_param").val();
        var param_id = $("#text_checked_control").val();
        var single = $("#text_checked_single").val();

        if (single == "TRUE") {
            if (param_id == "ERROR") {
                alert("No item selected (0)");
            } else {
                if (param_id == "ERROR2") {
                    alert("Too many selected");
                    return false;
                } else {
                    window.location = url + "/" + param + "/" + param_id;
                }
            }
        } else {
            window.location = url + "/" + param;
        }

        return false;
    });

    $("#button_wiki").click(function() {
        var w = 500;
        var h = 600;
        var left = (screen.width / 2) - (w / 2);
        var top = (screen.height / 2) - (h / 2);
        var url = $(this).attr("title");

        return window.open(url, "Wiki", 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
    });

    $("#button_error").click(function() {
        var w = 550;
        var h = 500;
        var left = (screen.width / 2) - (w / 2);
        var top = (screen.height / 2) - (h / 2);
        var url = $(this).attr("title");

        return window.open(url, "Error", 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

        return false;
    });

    $(".checkbox_id").click(function() {
        var searchIDs = [];
        var atLeastOneIsChecked = $(".checkbox_id:checked").length;

        if (atLeastOneIsChecked == 0) {
            $("#text_checked_control").val("ERROR");
            $("#favoritesModalLabel").text("Pengajuan");
        } else if (atLeastOneIsChecked == 1) {
            $("#myform input:checkbox:checked").map(function() {
                searchIDs.push($(this).val());
            });
            $("#favoritesModalLabel").text("Pengajuan : " + searchIDs);
            $("#text_checked_control").val(searchIDs);
        } else {
            $("#text_checked_control").val("ERROR2");
            $("#favoritesModalLabel").text("Pengajuan");
        }
    });

    function rightclick(url_href) {
        var rightclick;
        var e = window.event;

        if (e.which == 3) {
            var searchIDs = [];
            var atLeastOneIsChecked = $(".checkbox_id:checked").length;

            if (atLeastOneIsChecked == 0) {
                alert("Silahkan pilih salah satu item");
            } else if (atLeastOneIsChecked == 1) {
                $("#myform input:checkbox:checked").map(function() {
                    searchIDs.push($(this).val());
                });

                var url = url_href + "/" + searchIDs;
                window.open(url);

                return false;
            } else {
                alert("Silahkan pilih salah satu item");
            }

            return false;
        }


    }
</script>
<style>
    .error_link {
        text-decoration: underline;
        color: #A94442;
    }
</style>
@stop