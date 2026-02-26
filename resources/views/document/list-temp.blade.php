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
                    <div class="row m-b-10">
                        <div class="col-lg-12 col-md-12">
                            <h4 style="margin-bottom: 0px;"><b>Project : {{ $project }}</b></h4>
                        </div>
                        <div class="col-lg-12 col-md-12">
                            <h4><b>Vendor : {{ $vendor }}</b></h4>
                        </div>
                    </div>
                    <form class="form-horizontal" id="myform" name="myform" action="{{ URL::to('/').$form_act }}" method="post" enctype="multipart/form-data">
                        @csrf
                        @foreach ($fields as $row)
                        {!! $row !!}
                        @endforeach
                        <div class="table-responsive" style="overflow-x: hidden; margin-bottom: 10px">
                            <table id="data-table" class="table display-data-table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th width="5%" style="text-align: center;">No</th>
                                        <th width="17%" style="text-align: center;">Document Number</th>
                                        <th width="18%" style="text-align: center;">Document Title</th>
                                        <th width="23%" style="text-align: center;">Description</th>
                                        <th width="17%" style="text-align: center;">Ref Number</th>
                                        <th width="20%" style="text-align: center;">Note</th>
                                        <th width="10%" style="text-align: center;">Type</th>
                                        <th width="10%" style="text-align: center;">Area</th>
                                        <th width="10%" style="text-align: center;">Pic</th>
                                        <th width="10%" style="text-align: center;">Departemen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($temp) == 0)
                                    <tr>
                                        <td colspan="6" class="text-center">No data found (0)</td>
                                    </tr>
                                    @else
                                    @foreach ($temp as $index => $row)
                                    <tr>
                                        @php
                                            if ($row->status == 3) {
                                                $color = 'red';
                                            } else {
                                                $color = '';
                                            }
                                        @endphp
                                        <td class="text-center" style="color: {{ $color }}">{{ ++$index }}</td>
                                        <td style="color: {{ $color }}">{{ $row->document_no }}</td>
                                        <td style="color: {{ $color }}">{{ $row->document_title }}</td>
                                        <td style="color: {{ $color }}">{{ $row->document_description }}</td>
                                        <td style="color: {{ $color }}">{{ $row->ref_no }}</td>

                                        <td style="color: {{ $color }}">{{ $row->note }}</td>
                                        <td style="color: {{ $color }}">{{ $row->doc_type_name ?? '-' }}</td>
                                        <td style="color: {{ $color }}">{{ $row->area_name ?? '-' }}</td>
                                        <td style="color: {{ $color }}">{{ $row->pic_name ?? '-' }}</td>
                                        <td style="color: {{ $color }}">{{ $row->department_name ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="form-group">
                            <div class="col-md-9">
                                @foreach ($buttons as $row)
                                {!! $row !!}
                                @endforeach
                            </div>
                            <div class="col-md-3">
                                <span class="pull-right">
                                    {{ "Found (" . count($temp) .") rows" }}
                                </span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset("vendor/color_admin/plugins/DataTables/media/js/jquery.dataTables.js") }}"></script>
<script src="{{ asset("vendor/color_admin/plugins/DataTables/media/js/dataTables.bootstrap.min.js") }}"></script>
<script>
    $(".preloader-container").hide();

    $(document).ready(function() {
        $('table.display-data-table').DataTable({
            "scrollX": true,
            "scrollY": 300,
            "bInfo": false,
            "paging": false,
            "searching": true,
            "language": {
                "emptyTable": "Empty Table",
                "search": "",
                "searchPlaceholder": "Search"
            },
        });

        $('.dataTables_length').addClass('bs-select');
        $('.dataTables_filter').addClass('pull-right');

    });
</script>
@stop