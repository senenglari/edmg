@extends('main')
@section('content')
<div id="content" class="content">
    <ol class="breadcrumb pull-right">
        <li style="margin-left: -10px;">
            @if((Auth::user()->vendor_id == 0) || (Auth::user()->vendor_id == 7))
            <form id="myform" name="myform" action="{{ URL::to('/').$form_act }}" method="post" autocomplete="off">
                @csrf
                {!! $vendor !!}
                <input type="submit" id="submit" name="submit" value="" style="position: absolute; top: 0">
            </form>
            @endif
        </li>
    </ol>
    <h1 class="page-header">{{ $title }}<small></small></h1>
    <div class="row" style="margin-top: 30px;">
        <!--div class="col-md-3 col-sm-6">
            <div class="widget widget-stats" style="background-color: #FFCC00; color: black;">
                <div class="stats-icon stats-icon-lg"><i class="fa fa-tags fa-fw"></i></div>
                <div class="stats-title" style="color: black; font-weight: bold; font-size: 14px;">IFR</div>
                <div class="stats-number">{{ number_format($summary["data"]->ifc_status, 0) }} document(s)</div>
                <div class="stats-progress progress">
                    <div class="progress-bar" style="width: 100%;"></div>
                </div>
                <div class="stats-desc" style="color: black;">Issue for Review</div>
            </div>
        </div-->
        <div class="col-md-3 col-sm-6">
            <div class="widget widget-stats" style="background-color: #FF9900; color: black;">
                <div class="stats-icon stats-icon-lg"><i class="fa fa-check-square fa-fw"></i></div>
                <div class="stats-title" style="color: black; font-weight: bold; font-size: 14px;">IFA</div>
                <div class="stats-number">
                    {{ number_format($summary["data"]->ifa_status, 0) }} document(s)  <br>
                0 overdue
                </div>
                <div class="stats-progress progress">
                    <div class="progress-bar" style="width: 100%;"></div>
                </div>
                <div class="stats-desc" style="color: black;">Issue for Approval</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="widget widget-stats bg-blue" style="color: black;">
                <div class="stats-icon stats-icon-lg"><i class="fa fa-legal fa-fw"></i></div>
                <div class="stats-title" style="color: black; font-weight: bold; font-size: 14px;">IFR</div>
                <div class="stats-number">{{ number_format($summary["data"]->afd_status, 0) }} document(s)<br>
                0 overdue
                </div>
                <div class="stats-progress progress">
                    <div class="progress-bar" style="width: 100%;"></div>
                </div>
                <div class="stats-desc" style="color: black;">Issue for Review</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="widget widget-stats bg-green" style="color: black;">
                <div class="stats-icon stats-icon-lg"><i class="fa fa-gear (alias) fa-fw"></i></div>
                <div class="stats-title" style="color: black; font-weight: bold; font-size: 14px;">IFI</div>
                <div class="stats-number">{{ number_format($summary["data"]->afc_status, 0) }} document(s)<br>
                0 overdue
                </div>
                <div class="stats-progress progress">
                    <div class="progress-bar" style="width: 100%;"></div>
                </div>
                <div class="stats-desc" style="color: black;">Issue for Info</div>
            </div>
        </div>
   </div>
   <div class="row">
        <!-- begin col-6 -->
        <div class="col-md-12">
            <!-- begin panel -->
            <div class="panel panel-inverse" data-sortable-id="table-basic-1">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse"><i class="fa fa-minus"></i></a>
                    </div>
                    <h4 class="panel-title">IFR ~ Issue for Review</h4>
                </div>
                <div class="panel-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="5%" style="text-align: center;">No</th>
                                <th width="20%" style="text-align: center;">Document Number</th>
                                <th width="" style="text-align: center;">Document Title</th>
                                <th width="20%" style="text-align: center;">Progress</th>
                                <th width="10%" style="text-align: center;">Deadline</th>
                                <th width="20%" style="text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($ifc_list["data"]) > 0)
                                @php
                                $no     = 1;
                                @endphp
                                @foreach($ifc_list["data"] as $row)
                                <tr>
                                    <td style="text-align: center;">{{ $no }}</td>
                                    <td style="text-align: center;">{{ $row->document_no }}</td>
                                    <td style="text-align: left;">{{ $row->document_title }}</td>
                                    <td style="text-align: center;">
                                        @for ($i=1; $i<=$row->unit; $i++)
                                            @php
                                            $user = explode(",", $row->list_name);
                                            $sts  = explode(",", $row->list_status);
                                            $roles= explode(",", $row->list_role);

                                            $usr  = (!empty($user[$i-1])) ? $user[$i-1] : "";
                                            @endphp

                                            @if($row->status == 1)
                                                <span class="btn btn-default btn-icon btn-xs" data-toggle="tooltip" data-placement="top" title="{{ $usr }}" style="width: 12px; height: 12px;">a</span>
                                            @else
                                                @if($sts[$i-1] == 1)
                                                    @if($roles[$i-1] == "RESPONSIBILITY")
                                                    <span class="btn btn-default btn-icon btn-xs" data-toggle="tooltip" data-placement="top" title="{{ $usr }}" style="width: 12px; height: 12px;"></span>
                                                    @else
                                                    <span class="btn btn-warning btn-icon btn-xs" data-toggle="tooltip" data-placement="top" title="{{ $usr }}" style="width: 12px; height: 12px;"></span>
                                                    @endif
                                                @else
                                                    @if($roles[$i-1] == "RESPONSIBILITY")
                                                    <span class="btn btn-default btn-icon btn-xs" data-toggle="tooltip" data-placement="top" title="{{ $usr }}" style="width: 12px; height: 12px;"></span>
                                                    @else
                                                    <span class="btn btn-success btn-icon btn-xs" data-toggle="tooltip" data-placement="top" title="{{ $usr }}" style="width: 12px; height: 12px;"></span>
                                                    @endif
                                                @endif
                                            @endif
                                        @endfor
                                    </td>
                                    <td style="text-align: center;">{{ $row->deadline_date }}</td>
                                    <td style="text-align: center;">{{ $row->status_code }}</td>
                                </tr>
                                    @php
                                    $no++;
                                    @endphp
                                @endforeach
                            @else
                                <tr>
                                    <td style="text-align: center; border-bottom: 1px solid #eee;" colspan="5">No data found (0)</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" style="text-align: center;"><a href="{{$url_ifc}}" target="_blank" id="link">See all...</a></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <!-- end panel -->
            <!-- begin panel -->
            <div class="panel panel-inverse" data-sortable-id="table-basic-2">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse"><i class="fa fa-minus"></i></a>
                    </div>
                    <h4 class="panel-title">IFA ~ Issue for Approval</h4>
                </div>
                <div class="panel-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%" style="text-align: center;">No</th>
                                <th width="20%" style="text-align: center;">Document Number</th>
                                <th width="" style="text-align: center;">Document Title</th>
                                <th width="20%" style="text-align: center;">Progress</th>
                                <th width="10%" style="text-align: center;">Deadline</th>
                                <th width="20%" style="text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($ifa_list["data"]) > 0)
                                @php
                                $no     = 1;
                                @endphp
                                @foreach($ifa_list["data"] as $row)
                                <tr>
                                    <td style="text-align: center;">{{ $no }}</td>
                                    <td style="text-align: center;">{{ $row->document_no }}</td>
                                    <td style="text-align: left;">{{ $row->document_title }}</td>
                                    <td style="text-align: center;">
                                        @for ($i=1; $i<=$row->unit; $i++)
                                            @php
                                            $user = explode(",", $row->list_name);
                                            $sts  = explode(",", $row->list_status);
                                            $roles= explode(",", $row->list_role);

                                            $usr  = (!empty($user[$i-1])) ? $user[$i-1] : "";
                                            @endphp

                                            @if($row->status == 1)
                                                <span class="btn btn-default btn-icon btn-xs" data-toggle="tooltip" data-placement="top" title="{{ $usr }}" style="width: 12px; height: 12px;"></span>
                                            @else
                                                @if($sts[$i-1] == 1) 
                                                    @if($roles[$i-1] == "RESPONSIBILITY")
                                                    <span class="btn btn-default btn-icon btn-xs" data-toggle="tooltip" data-placement="top" title="{{ $usr }}" style="width: 12px; height: 12px;"></span>
                                                    @else
                                                    <span class="btn btn-warning btn-icon btn-xs" data-toggle="tooltip" data-placement="top" title="{{ $usr }}" style="width: 12px; height: 12px;"></span>
                                                    @endif
                                                @else
                                                    @if($roles[$i-1] == "RESPONSIBILITY")
                                                    <span class="btn btn-default btn-icon btn-xs" data-toggle="tooltip" data-placement="top" title="{{ $usr }}" style="width: 12px; height: 12px;"></span>
                                                    @else
                                                    <span class="btn btn-success btn-icon btn-xs" data-toggle="tooltip" data-placement="top" title="{{ $usr }}" style="width: 12px; height: 12px;"></span>
                                                    @endif
                                                @endif
                                            @endif
                                        @endfor
                                    </td>
                                    <td style="text-align: center;">{{ $row->deadline_date }}</td>
                                    <td style="text-align: center;">{{ $row->status_code }}</td>
                                </tr>
                                    @php
                                    $no++;
                                    @endphp
                                @endforeach
                            @else
                                <tr>
                                    <td style="text-align: center; border-bottom: 1px solid #eee;" colspan="5">No data found (0)</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" style="text-align: center;"><a href="{{$url_ifa}}" target="_blank" id="link">See all...</a></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <!-- begin panel -->
            <div class="panel panel-inverse" data-sortable-id="table-basic-3">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse"><i class="fa fa-minus"></i></a>
                    </div>
                    <h4 class="panel-title">IFU ~ Issued for Use</h4>
                </div>
                <div class="panel-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="5%" style="text-align: center;">No</th>
                                <th width="20%" style="text-align: center;">Document Number</th>
                                <th width="" style="text-align: center;">Document Title</th>
                                <th width="20%" style="text-align: center;">Progress</th>
                                <th width="10%" style="text-align: center;">Deadline</th>
                                <th width="20%" style="text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($afd_list["data"]) > 0)
                                @php
                                $no     = 1;
                                @endphp
                                @foreach($afd_list["data"] as $row)
                                <tr>
                                    <td style="text-align: center;">{{ $no }}</td>
                                    <td style="text-align: center;">{{ $row->document_no }}</td>
                                    <td style="text-align: left;">{{ $row->document_title }}</td>
                                    <td style="text-align: center;">
                                        @for ($i=1; $i<=$row->unit; $i++)
                                            @php
                                            $user = explode(",", $row->list_name);
                                            $sts  = explode(",", $row->list_status);
                                            $roles= explode(",", $row->list_role);

                                            $usr  = (!empty($user[$i-1])) ? $user[$i-1] : "";
                                            @endphp

                                            @if($row->status == 1)
                                                <span class="btn btn-default btn-icon btn-xs" data-toggle="tooltip" data-placement="top" title="{{ $usr }}" style="width: 12px; height: 12px;"></span>
                                            @else
                                                @if($sts[$i-1] == 1) 
                                                    @if($roles[$i-1] == "RESPONSIBILITY")
                                                    <span class="btn btn-default btn-icon btn-xs" data-toggle="tooltip" data-placement="top" title="{{ $usr }}" style="width: 12px; height: 12px;"></span>
                                                    @else
                                                    <span class="btn btn-warning btn-icon btn-xs" data-toggle="tooltip" data-placement="top" title="{{ $usr }}" style="width: 12px; height: 12px;"></span>
                                                    @endif
                                                @else
                                                    @if($roles[$i-1] == "RESPONSIBILITY")
                                                    <span class="btn btn-default btn-icon btn-xs" data-toggle="tooltip" data-placement="top" title="{{ $usr }}" style="width: 12px; height: 12px;"></span>
                                                    @else
                                                    <span class="btn btn-success btn-icon btn-xs" data-toggle="tooltip" data-placement="top" title="{{ $usr }}" style="width: 12px; height: 12px;"></span>
                                                    @endif
                                                @endif
                                            @endif
                                        @endfor
                                    </td>
                                    <td style="text-align: center;">{{ $row->deadline_date }}</td>
                                    <td style="text-align: center;">{{ $row->status_code }}</td>
                                </tr>
                                    @php
                                    $no++;
                                    @endphp
                                @endforeach
                            @else
                                <tr>
                                    <td style="text-align: center; border-bottom: 1px solid #eee;" colspan="5">No data found (0)</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" style="text-align: center;"><a href="{{$url_ifu}}" target="_blank" id="link">See all...</a></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <!-- begin panel -->
            <div class="panel panel-inverse" data-sortable-id="table-basic-4">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse"><i class="fa fa-minus"></i></a>
                    </div>
                    <h4 class="panel-title">AFC ~ Approved for Construction</h4>
                </div>
                <div class="panel-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="5%" style="text-align: center;">No</th>
                                <th width="20%" style="text-align: center;">Document Number</th>
                                <th width="" style="text-align: center;">Document Title</th>
                                <th width="20%" style="text-align: center;">Progress</th>
                                <th width="10%" style="text-align: center;">Deadline</th>
                                <th width="20%" style="text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($afc_list["data"]) > 0)
                                @php
                                $no     = 1;
                                @endphp
                                @foreach($afc_list["data"] as $row)
                                <tr>
                                    <td style="text-align: center;">{{ $no }}</td>
                                    <td style="text-align: center;">{{ $row->document_no }}</td>
                                    <td style="text-align: left;">{{ $row->document_title }}</td>
                                    <td style="text-align: center;">
                                        @for ($i=1; $i<=$row->unit; $i++)
                                            @php
                                            $user = explode(",", $row->list_name);
                                            $sts  = explode(",", $row->list_status);
                                            $roles= explode(",", $row->list_role);

                                            $usr  = (!empty($user[$i-1])) ? $user[$i-1] : "";
                                            @endphp

                                            
                                            @if($row->status == 1)
                                                <span class="btn btn-default btn-icon btn-xs" data-toggle="tooltip" data-placement="top" title="{{ $usr }}" style="width: 12px; height: 12px;"></span>
                                            @else
                                                @if($sts[$i-1] == 1) 
                                                    @if($roles[$i-1] == "RESPONSIBILITY")
                                                    <span class="btn btn-default btn-icon btn-xs" data-toggle="tooltip" data-placement="top" title="{{ $usr }}" style="width: 12px; height: 12px;"></span>
                                                    @else
                                                    <span class="btn btn-warning btn-icon btn-xs" data-toggle="tooltip" data-placement="top" title="{{ $usr }}" style="width: 12px; height: 12px;"></span>
                                                    @endif
                                                @else
                                                    @if($roles[$i-1] == "RESPONSIBILITY")
                                                    <span class="btn btn-default btn-icon btn-xs" data-toggle="tooltip" data-placement="top" title="{{ $usr }}" style="width: 12px; height: 12px;"></span>
                                                    @else
                                                    <span class="btn btn-success btn-icon btn-xs" data-toggle="tooltip" data-placement="top" title="{{ $usr }}" style="width: 12px; height: 12px;"></span>
                                                    @endif
                                                @endif
                                            @endif
                                        @endfor
                                    </td>
                                    <td style="text-align: center;">{{ $row->deadline_date }}</td>
                                    <td style="text-align: center;">{{ $row->status_code }}</td>
                                </tr>
                                    @php
                                    $no++;
                                    @endphp
                                @endforeach
                            @else
                                <tr>
                                    <td style="text-align: center; border-bottom: 1px solid #eee;" colspan="5">No data found (0)</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" style="text-align: center;"><a href="{{$url_afc}}" target="_blank" id="link">See all...</a></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <!-- end col-6 -->
    </div>
</div>
<script type="text/javascript">
    // $("#vendor_id").change(function() {
    //     document.forms["myform"].submit();
    // });

    $('#vendor_id').on('change', function() {
        // $("#myform").submit();
         $('#submit').click();
    });

    // $("#myform").submit(function(){
        
    // });
</script>
@stop
