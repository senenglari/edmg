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
<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand">
<i class="fa fa-expand"></i>
</a>
<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse">
<i class="fa fa-minus"></i>
</a>
</div>

<h4 class="panel-title">{{ $title }}</h4>
</div>


<div class="panel-body">

<p class="m-b-10">

<button id="btn-assign" class="btn btn-sm btn-info m-r-5">
<i class="fa fa-user-plus m-r-2"></i> Assignment
</button>

<button id="btn-detail-doc" class="btn btn-sm btn-default m-r-5">
<i class="fa fa-file-text-o m-r-2"></i> Detail
</button>

<button id="btn-detail-trans" class="btn btn-sm btn-primary">
<i class="fa fa-external-link m-r-2"></i> Detail Transmittal
</button>

</p>


<div class="table-responsive">

<table class="table table-striped table-bordered">

<thead>

<tr>

<th style="width:40px;text-align:center">
<input type="checkbox" id="check-all">
</th>

<th style="width:15%;text-align:center">Document Number</th>

<th>Title</th>

<th style="width:12%;text-align:center">Vendor</th>

<th style="width:12%;text-align:center">Responsibility</th>

<th style="width:8%;text-align:center">Owner</th>

<th style="width:8%;text-align:center">Approver</th>

<th style="width:12%;text-align:center">Issue Status</th>

<th style="width:10%;text-align:center">Status</th>

</tr>

</thead>


<tbody>

@forelse($documents as $doc)

@php

// load detailed users per role so we can show tooltips and status coloring
$users = DB::table('comment as c')
    ->join('assignment as a','a.assignment_id','=','c.assignment_id')
    ->join('sys_users as u','c.user_id','=','u.id')
    ->where('a.document_id',$doc->document_id)
    ->whereIn('c.role',['RESPONSIBLE','OWNER','APPROVER_COMPANY'])
    ->where('c.status_nonaktif',0)
    ->select('c.role','u.full_name','c.status','c.user_id')
    ->orderBy('c.order_no')
    ->get();

$responsible = $users->where('role','RESPONSIBLE');
$owner        = $users->where('role','OWNER');
$approver     = $users->where('role','APPROVER_COMPANY');

@endphp

<tr>

<td style="text-align:center">
<input type="checkbox" class="doc-check" value="{{ $doc->document_id }}">
</td>

<td style="text-align:center">
{{ $doc->document_no ?: '-' }}
</td>

<td>
{{ $doc->document_title ?: '-' }}
</td>

<td style="text-align:center">
{{ $doc->vendor_name ?: '-' }}
</td>

{{-- RESPONSIBILITY --}}
<td style="text-align:center">

@if(count($responsible)>0)
    @foreach($responsible as $u)
        @php
            $extra = '';
            // highlight green if current user commented (status 2 or greater)
            if(Auth::check() && $u->user_id == Auth::user()->id && $u->status >= 2) {
                $extra = 'background-color:#00ff00; border-color:#00ff00;';
            }
        @endphp
        <span class="btn btn-info btn-icon btn-xs" title="{{ $u->full_name }}"
              style="width:12px;height:12px;margin-right:3px;padding:0;border-radius:2px; background-color:#add8e6; border-color:#add8e6; {{ $extra }}"></span>
    @endforeach
@else
    <span class="text-muted">-</span>
@endif

</td>

{{-- OWNER --}}
<td style="text-align:center">

@if(count($owner)>0)
    @foreach($owner as $u)
        @php
            $extra = '';
            if(Auth::check() && $u->user_id == Auth::user()->id && $u->status >= 2) {
                $extra = 'background-color:#00ff00; border-color:#00ff00;';
            }
        @endphp
        <span class="btn btn-warning btn-icon btn-xs" title="{{ $u->full_name }}"
              style="width:12px;height:12px;margin-right:3px;padding:0;border-radius:2px; {{ $extra }}"></span>
    @endforeach
@else
    <span class="text-muted">-</span>
@endif

</td>

{{-- APPROVER --}}
<td style="text-align:center">

@if(count($approver)>0)
    @foreach($approver as $u)
        @php
            $extra = '';
            if(Auth::check() && $u->user_id == Auth::user()->id && $u->status >= 2) {
                $extra = 'background-color:#00ff00; border-color:#00ff00;';
            }
        @endphp
        <span class="btn btn-info btn-icon btn-xs" title="{{ $u->full_name }}"
              style="width:12px;height:12px;margin-right:3px;padding:0;border-radius:2px; {{ $extra }}"></span>
    @endforeach
@else
    <span class="text-muted">-</span>
@endif

</td>

<td style="text-align:center">
{{ $doc->issue_status_name ?: '-' }}
</td>

<td style="text-align:center">
    @php
        $statusText = $doc->backdoor_status ?? '';
        switch(strtolower($statusText)) {
            case 'pending':
                $labelClass = 'label-default';
                break;
            case 'on review':
                $labelClass = 'label-info';
                break;
            case 'owner':
                $labelClass = 'label-warning';
                break;
            case 'approver':
                $labelClass = 'label-primary';
                break;
            case 'done':
                $labelClass = 'label-success';
                break;
            default:
                $labelClass = 'label-default';
        }
    @endphp
    <span class="label {{ $labelClass }}">
        {{ strtoupper($statusText ?: 'PENDING') }}
    </span>
</td>

</tr>

@empty

<tr>
<td colspan="9" class="text-center">
Tidak ada dokumen.
</td>
</tr>

@endforelse

</tbody>

</table>

</div>


<div class="row">
<div class="col-md-12 text-center">
{{ $documents->links() }}
</div>
</div>


</div>
</div>
</div>
</div>
</div>



<script>

(function(){

function getCheckedIds(){

return Array.from(document.querySelectorAll('.doc-check:checked')).map(ch=>ch.value);

}


const checkAll=document.getElementById('check-all');

if(checkAll){

checkAll.addEventListener('change',function(e){

document.querySelectorAll('.doc-check').forEach(ch=>ch.checked=e.target.checked);

});

}


// Assignment
document.getElementById('btn-assign').addEventListener('click',function(){

const ids=getCheckedIds();

if(ids.length===0){alert('Silakan pilih satu dokumen.');return;}

if(ids.length>1){alert('Hanya bisa satu dokumen untuk Assignment.');return;}

window.location.href="{{ url('incoming_company/assignment') }}/"+ids[0];

});


// Detail Document
document.getElementById('btn-detail-doc').addEventListener('click',function(){

const ids=getCheckedIds();

if(ids.length===0){alert('Silakan pilih satu dokumen.');return;}

if(ids.length>1){alert('Pilih hanya satu dokumen.');return;}

window.location.href="{{ url('document/detail') }}/"+ids[0];

});


// Detail Transmittal
document.getElementById('btn-detail-trans').addEventListener('click',function(){

const ids=getCheckedIds();

if(ids.length===0){alert('Silakan pilih satu dokumen.');return;}

if(ids.length>1){alert('Pilih hanya satu dokumen.');return;}

window.location.href="{{ url('incoming_company/transmittal_detail') }}/"+ids[0];

});

})();

</script>

@stop