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
                
                

                
<div class="form-group" style="text-align: right; margin-bottom: 20px;">
    <a href="{{ url('/document/export-detail/' . request()->segment(3)) }}" 
       class="btn btn-sm btn-success">
        <i class="fa fa-file-excel-o m-r-2"></i> Export All to Excel
    </a>
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
                    <!-- <div class="row" style="display: block;">
                        <div class="col-lg-12 col-md-12">
                            <object data="your_url_to_pdf" type="application/pdf">
                                <iframe src="{{ url('uploads/') . $document->document_url.$document->document_file }}" width="100%" height="900px"></iframe>
                            </object>
                        </div>
                    </div> -->
                    <div class="row">
                        <div class="col-lg-12 col-md-12">
                            <div class="profile-section">
                                <!-- begin profile-left -->
                                <div class="profile-left">
                                    <!-- begin profile-image -->
                                    <div class="profile-image" style="border: none; margin-top: 40px;">
                                        @if ($document->status == 99)
                                            <a href="{{ url('uploads/') . $document->url_file . $document->file_migration}}" target="_blank">
                                                <img src="{{ url('') . '/app/img/icon/attachment.png'}}" alt="">
                                            </a>
                                        @else
                                            <a href="{{ url('uploads/') . $document->document_url }}{{ ($document->document_file_revision != '') ? $document->document_file_revision : $document->document_file }}" target="_blank">
                                                <img src="{{ url('') . '/app/img/icon/attachment.png'}}" alt="">
                                            </a>
                                        @endif
                                    </div>

									<!--cari di incoming transmital detail -->
									<!--http://dzaries.my.id/edmg-github-clone/edmg/pdf-stamp/index.php?pdf_id=5&stamp=1&fileName=support.pdf-->
								{{-- Buka PDF dengan PDF Stamp - FIX Undefined offset: 2 --}}
@php
    // Amankan dulu document_url (sesuai request kamu)
    $document_url = $document->document_url ?? '';

    if (!$document_url || trim($document_url) === '' || trim($document_url) === '/') {
        $document_url = "///";   // trik kamu biar explode selalu punya index 2
    }

    $parts = explode('/', $document_url);

    // Ambil pdf_id dengan aman (tidak akan error lagi)
    $pdf_id = $parts[2] ?? '';

    $laspath = explode('/', request()->path());  // ini kalau masih dipakai
@endphp

<div class="mt-4">
    @if ($pdf_id !== '')
        <a href="http://dzaries.my.id/edms/pdf-stamp/index.php?pdf_id={{ $parts[2] }}&rname={{ auth()->user()->name }}&lpath={{ $laspath[2] }}&idrole={{ auth()->user()->position_id }}&iduser={{ auth()->user()->id }}&fileName={{ $document->document_file }}" 
										   target="_blank" 
										   class="btn btn-primary btn-lg">
            <i class="fas fa-file-pdf me-2"></i> Buka PDF
        </a>
    @else
        <button class="btn btn-secondary btn-lg" disabled>
            <i class="fas fa-file-pdf me-2"></i> File PDF Tidak Tersedia
        </button>
    @endif
</div>
									
                                </div>
								
								
								
								
								
                                <!-- end profile-left -->
                                <!-- begin profile-right -->
                                <div class="profile-right">
                                    <!-- begin profile-info -->
                                    <div class="profile-info">
                                        <!-- begin table -->
                                        <div class="table-responsive">
                                            <table class="table">
                                                <tr>
                                                    <th width="20%" class="p-10">Project Name</th>
                                                    <td width="1%" class="p-10">:</td>
                                                    <td width="29%" class="p-10">{{ ucwordString($document->project_name) }}</td>
                                                    <th width="20%" class="p-10">Vendor</th>
                                                    <td width="1%" class="p-10">:</td>
                                                    <td width="29%" class="p-10">{{ ucwordString($document->vendor_name) }}</td>
                                                </tr>
                                                <tr>
                                                    <th class="p-10">Document Number</th>
                                                    <td class="p-10">:</td>
                                                    <td class="p-10">{{ $document->document_no }}</td>
                                                    <th class="p-10">Document Title</th>
                                                    <td class="p-10">:</td>
                                                    <td class="p-10">{{ ucwordString($document->document_title) }}</td>
                                                </tr>
                                                <tr>
                                                    <th class="p-10">Department</th>
                                                    <td class="p-10">:</td>
                                                    <td class="p-10">{{ ucwordString($document->department_name) }}</td>
                                                    <th class="p-10">Document Type</th>
                                                    <td class="p-10">:</td>
                                                    <td class="p-10">{{ ucwordString($document->document_type_name) }}</td>
                                                </tr>
                                                <tr>
                                                    <th class="p-10">Area</th>
                                                    <td class="p-10">:</td>
                                                    <td class="p-10">{{ ucwordString($document->area_name) }}</td>
                                                    <th class="p-10">PIC</th>
                                                    <td class="p-10">:</td>
                                                    <td class="p-10">{{ ucwordString($document->pic_name) }}</td>
                                                </tr>
                                                <tr>
                                                    <th class="p-10">Incoming Number</th>
                                                    <td class="p-10">:</td>
                                                    <td class="p-10">{{ $document->incoming_no }}</td>
                                                    <th class="p-10">Receive Date</th>
                                                    <td class="p-10">:</td>
                                                    <td class="p-10">{{ displayDMY($document->receive_date) }}</td>
                                                </tr>
                                                <tr>
                                                    <th class="p-10">Document Status</th>
                                                    <td class="p-10">:</td>
                                                    <td class="p-10">{{ $document->document_status }}</td>
                                                    <th class="p-10">Next Issue Status</th>
                                                    <td class="p-10">:</td>
                                                    <td class="p-10">{{ $document->issue_status }}</td>
                                                </tr>
                                                <tr>
                                                    <th class="p-10">Status</th>
                                                    <td class="p-10">:</td>
                                                    <td class="p-10">{{ $document->status_code }}</td>
                                                    <th class="p-10">Approval By</th>
                                                    <td class="p-10">:</td>
                                                    <td class="p-10">{{ ucwordString($document->approval_name) }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <!-- end table -->
                                    </div>
                                    <!-- end profile-info -->
                                </div>
                                <!-- end profile-right -->
                            </div>
                        </div>
                        <div class="col-lg-12 col-md-12">
                            <div class="table-responsive">
                                <table class="table">
                                    <tr>
                                        <th width="20%" class="p-10">Description</th>
                                        <td width="1%" class="p-10">:</td>
                                        <td width="79%" class="p-10">
                                            <p style="text-align: justify;">{{ $document->document_description }}</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    @if ($document->status != 99)
                    <div class="row" style="margin-top: 40px;">
                        <div class="col-lg-12 col-md-12">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 m-b-5">
                                    <div class="title" style="background-color: #04abac;width: 20%;padding: 7px 7px 7px 15px; border-radius:3px;">
                                        <h4 style="margin:0px; color:white;">Document History</h4>
                                    </div>
                                    <hr style="width:100%; border: 1px solid #04abac; float:left; margin-top:5px; margin-bottom:5px;">
                                </div>
                                <div class="col-lg-12 col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="table-document">
                                            <thead>
                                                <tr>
                                                    <th width="20%" style="text-align: center;">Transmittal Number</th>
                                                    <th width="10%" style="text-align: center;">Sender Date</th>
                                                    <th width="10%" style="text-align: center;">Receive Date</th>
                                                    <th width="38%" style="text-align: center;">File Name</th>
                                                    <th width="12%" style="text-align: center;">Document File</th>
                                                    <th width="10%" style="text-align: center;">Issue Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if (count($historyDoc) > 0)
                                                @foreach ($historyDoc as $rowHisDoc)
                                                <tr>
                                                    <td style="{{ ($rowHisDoc->status_incoming == 3) ? 'color:  red' : ''  }}">{{ $rowHisDoc->incoming_no }}</td>
                                                    <td style="{{ ($rowHisDoc->status_incoming == 3) ? 'color:  red' : ''  }}" class="text-center">{{ displayDMY($rowHisDoc->sender_date) }}</td>
                                                    <td style="{{ ($rowHisDoc->status_incoming == 3) ? 'color:  red' : ''  }}" class="text-center">{{ displayDMY($rowHisDoc->receive_date) }}</td>
                                                    <td style="{{ ($rowHisDoc->status_incoming == 3) ? 'color:  red' : ''  }}">{{ $rowHisDoc->document_file }}</td>
                                                    <td style="{{ ($rowHisDoc->status_incoming == 3) ? 'color:  red' : ''  }}" class="text-center">
                                                        @if($rowHisDoc->document_file_revision != '')
                                                        <a href="{{ url('uploads/') . $rowHisDoc->document_url.$rowHisDoc->document_file_revision }}" target="_blank" class="email-user" style="border-radius:0px; color:none; background:none;">
                                                            <img src="{{ url('') . '/app/img/icon/attachment.png'}}" alt="" width="30px">
                                                        </a>
                                                        @else
                                                        <a href="{{ url('uploads/') . $rowHisDoc->document_url.$rowHisDoc->document_file }}" target="_blank" class="email-user" style="border-radius:0px; color:none; background:none;">
                                                            <img src="{{ url('') . '/app/img/icon/attachment.png'}}" alt="" width="30px">
                                                        </a>
                                                        @endif
                                                    </td>
                                                    <td style="{{ ($rowHisDoc->status_incoming == 3) ? 'color:  red' : ''  }}">{{ $rowHisDoc->issue_status }}</td>
                                                </tr>
                                                @endforeach
                                                @else
                                                <tr>
                                                    <td colspan="6" class="text-center">Not Found</td>
                                                </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12 col-md-12 m-b-20">
                                    <div class="title" style="background-color: #04abac;width: 20%;padding: 7px 7px 7px 15px; border-radius:3px;">
                                        <h4 style="margin:0px; color:white;">Comment History</h4>
                                    </div>
                                    <hr style="width:100%; border: 1px solid #04abac; float:left; margin-top:5px; margin-bottom:5px;">
                                </div>
                                @if (count($transmittal) > 0)
                                @foreach ($transmittal as $rowTrans)
                                <div class="col-md-12">
                                    <div style="display: flex; margin-bottom:10px; font-weight: 600;">
                                        <div class="m-r-10 f-s-14">
                                            <i class="fa fa-tags m-r-2"></i>
                                            {{ $rowTrans->incoming_no }}
                                        </div>
                                        <div class="m-r-10 f-s-14">
                                            <i class="fa fa-calendar m-r-2"></i>
                                            {{ displayDMY($rowTrans->receive_date) }}
                                        </div>
                                        <div class="m-r-10 f-s-14">
                                            <a href="{{ url('uploads/') . $rowTrans->document_url.$rowTrans->document_file }}" target="_blank" style="text-decoration: none;">
                                                <img src="{{ url('') . '/app/img/icon/attachment.png'}}" alt="" width="20px">
                                                Document File
                                            </a>
                                        </div>
                                        <div class="m-r-10 f-s-14">
                                            <span class="label label-inverse">{{ $rowTrans->issue_status_name }}</span>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <ul class="list-group list-group-lg no-radius list-email">
                                            @foreach ($comment as $rowComment)
                                            @if ($rowComment->role == 'APPROVER')
                                            @php
                                            $lineColor = 'success';
                                            @endphp
                                            @else
                                            @php
                                            $lineColor = 'warning';
                                            @endphp
                                            @endif
                                            @if ($rowTrans->assignment_id == $rowComment->assignment_id)
                                            <li class="list-group-item {{ $lineColor }}">
                                                @if ($rowComment->document_file != null)
                                                <a href="{{ url('uploads/') . $rowComment->document_url.$rowComment->document_file }}" target="_blank" class="email-user" style="border-radius:0px; color:none; background:none;">
                                                    <!-- <i class="fa fa-file-pdf-o fa-2x text-danger"></i> -->
                                                    <img src="{{ url('') . '/app/img/icon/attachment.png'}}" alt="" width="50px">
                                                </a>
                                                @else
                                                <a href="#" class="email-user" style="border-radius:0px; color:none; background:none;">
                                                    <!-- <i class="fa fa-file-pdf-o fa-2x text-danger"></i> -->
                                                    <img src="{{ url('') . '/app/img/icon/attachment.png'}}" alt="" width="50px">
                                                </a>
                                                @endif
                                                @if ($rowComment->document_file_2 != null)
                                                <a href="{{ url('uploads/') . $rowComment->document_url.$rowComment->document_file_2 }}" target="_blank" class="email-user" style="border-radius:0px; color:none; background:none;">
                                                    <!-- <i class="fa fa-file-pdf-o fa-2x text-danger"></i> -->
                                                    <img src="{{ url('') . '/app/img/icon/attachment.png'}}" alt="" width="50px">
                                                </a>
                                                @else
                                                <a href="#" class="email-user" style="border-radius:0px; color:none; background:none;">
                                                    <!-- <i class="fa fa-file-pdf-o fa-2x text-danger"></i> -->
                                                    <img src="{{ url('') . '/app/img/icon/attachment.png'}}" alt="" width="50px">
                                                </a>
                                                @endif
                                                <div class="email-info">
                                                    @if ($rowComment->updated_at != null)
                                                    <span class="email-time">
                                                        @php
                                                        $tgl1 = new DateTime($rowComment->created_at);
                                                        $tgl2 = new DateTime($rowComment->updated_at);
                                                        $hasil = $tgl1->diff($tgl2);
                                                        @endphp
                                                        <p class="card-sub mb-1 font-weight-medium">
                                                            @if ($hasil->d != 0)
                                                            @if ($hasil->h == 0 AND $hasil->i == 0)
                                                            {{ $hasil->d . ' days'}}
                                                            @elseif ($hasil->i == 0)
                                                            {{ $hasil->d . ' days ' . $hasil->h . ' hours'}}
                                                            @else
                                                            {{ $hasil->d . ' days ' . $hasil->h . ' hours' . ' ' . $hasil->i . ' minutes'}}
                                                            @endif

                                                            @elseif ($hasil->h != 0)
                                                            @if ($hasil->i == 0)
                                                            {{ $hasil->h . ' hours'}}
                                                            @else
                                                            {{ $hasil->h . ' hours' . ' ' . $hasil->i . ' minutes'}}
                                                            @endif
                                                            @elseif ($hasil->i != 0)
                                                            {{ $hasil->i . ' minutes'}}
                                                            @else
                                                            {{ '1 minutes'}}
                                                            @endif
                                                        </p>
                                                    </span>
                                                    @endif
                                                    <h5 class="email-title">
                                                        <span>{{ $rowComment->comment_user }}</span>
                                                        <span class="label label-info">{{ $rowComment->return_code }}</span>
                                                        <span class="label label-{{$lineColor}} f-s-10">{{ ucwordString($rowComment->role) }}</span> 
                                                    </h5>
                                                    @if ($rowComment->updated_at != null)
                                                    <div style="display: flex; margin-bottom:10px;">
                                                        <div class="m-r-10 f-s-10">
                                                            <i class="fa fa-calendar m-r-2"></i>
                                                            {{ date('d/m/Y H:i:s' , strtotime($rowComment->updated_at)) }}
                                                            <!-- {{ $rowComment->updated_at }} -->
                                                        </div>
                                                    </div>
                                                    @endif
                                                    <p class="email-desc">
                                                        {{ ucwordString($rowComment->remark) }}
                                                    </p>
                                                </div>
                                            </li>
                                            @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                    <hr style="border: 1px solid #939699;">
                                </div>
                                @endforeach
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-lg-12 col-md-12 m-b-5">
                                    <div class="title" style="background-color: #04abac;width: 20%;padding: 7px 7px 7px 15px; border-radius:3px;">
                                        <h4 style="margin:0px; color:white;">Migration History</h4>
                                    </div>
                                    <hr style="width:100%; border: 1px solid #04abac; float:left; margin-top:5px; margin-bottom:5px;">
                                </div>
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="table-document">
                                            <thead>
                                                <tr>
                                                    <th width="5%" style="text-align: center;">No</th>
                                                    <th width="20%" style="text-align: center;">Document Number</th>
                                                    <th width="40%" style="text-align: center;">Document Title</th>
                                                    <th width="15%" style="text-align: center;">Issue Name</th>
                                                    <th width="20%" style="text-align: center;">Revision Code</th>
                                                    <th width="20%" style="text-align: center;">File Name</th>
                                                    <th width="20%" style="text-align: center;">Document File</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if (count($migration) > 0)
                                                @foreach ($migration as $index => $rowLog)
                                                <tr>
                                                    <td class="text-center">{{ ++$index }}</td>
                                                    <td>{{ $rowLog->document_no }}</td>
                                                    <td>{{ $rowLog->document_title }}</td>
                                                    <td>{{ $rowLog->issue_name }}</td>
                                                    <td>{{ $rowLog->revision_code }}</td>
                                                    <td>{{ $rowLog->file_migration }}</td>
                                                    <td class="text-center">
                                                        @if($rowLog->file_migration != '')
                                                        <a href="{{ url('uploads/') . $rowLog->url_file.$rowLog->file_migration }}" target="_blank" class="email-user" style="border-radius:0px; color:none; background:none;">
                                                            <img src="{{ url('') . '/app/img/icon/attachment.png'}}" alt="" width="30px">
                                                        </a>
                                                        @else
                                                        <a href="#" target="_blank" class="email-user" style="border-radius:0px; color:none; background:none;">
                                                            <img src="{{ url('') . '/app/img/icon/attachment.png'}}" alt="" width="30px">
                                                        </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                                @else
                                                <tr>
                                                    <td colspan="5" class="text-center">Not Found</td>
                                                </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12 col-md-12 m-b-5">
                                    <div class="title" style="background-color: #04abac;width: 20%;padding: 7px 7px 7px 15px; border-radius:3px;">
                                        <h4 style="margin:0px; color:white;">Document Change Log</h4>
                                    </div>
                                    <hr style="width:100%; border: 1px solid #04abac; float:left; margin-top:5px; margin-bottom:5px;">
                                </div>
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="table-document">
                                            <thead>
                                                <tr>
                                                    <th width="5%" style="text-align: center;">No</th>
                                                    <th width="20%" style="text-align: center;">Document Number</th>
                                                    <th width="40%" style="text-align: center;">Document Title</th>
                                                    <th width="15%" style="text-align: center;">Date</th>
                                                    <th width="20%" style="text-align: center;">Changed By</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if (count($docLog) > 0)
                                                @foreach ($docLog as $index => $rowLog)
                                                <tr>
                                                    <td class="text-center">{{ ++$index }}</td>
                                                    <td>{{ $rowLog->document_no }}</td>
                                                    <td>{{ $rowLog->document_title }}</td>
                                                    <td class="text-center">{{ date('d/m/Y H:i:s' , strtotime($rowLog->changed_at))  }}</td>
                                                    <td>{{ $rowLog->full_name }}</td>
                                                </tr>
                                                @endforeach
                                                @else
                                                <tr>
                                                    <td colspan="5" class="text-center">Not Found</td>
                                                </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
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
</script>
@stop