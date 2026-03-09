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

$responsibility = DB::table('comment as c')
->join('assignment as a','a.assignment_id','=','c.assignment_id')
->where('a.document_id',$doc->document_id)
->where('c.role','RESPONSIBILITY')
->where('c.status_nonaktif',0)
->count();

$owner = DB::table('comment as c')
->join('assignment as a','a.assignment_id','=','c.assignment_id')
->where('a.document_id',$doc->document_id)
->where('c.role','OWNER')
->where('c.status_nonaktif',0)
->count();

$approver = DB::table('comment as c')
->join('assignment as a','a.assignment_id','=','c.assignment_id')
->where('a.document_id',$doc->document_id)
->where('c.role','APPROVER')
->where('c.status_nonaktif',0)
->count();

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

@if($responsibility>0)

@for($i=0;$i<$responsibility;$i++)
<span class="btn btn-default btn-icon btn-xs"
style="width:12px;height:12px;margin-right:3px;padding:0;border-radius:2px;"></span>
@endfor

@else
<span class="text-muted">-</span>
@endif

</td>

{{-- OWNER --}}
<td style="text-align:center">

@if($owner>0)

@for($i=0;$i<$owner;$i++)
<span class="btn btn-warning btn-icon btn-xs"
style="width:12px;height:12px;margin-right:3px;padding:0;border-radius:2px;"></span>
@endfor

@else
<span class="text-muted">-</span>
@endif

</td>

{{-- APPROVER --}}
<td style="text-align:center">

@if($approver>0)

@for($i=0;$i<$approver;$i++)
<span class="btn btn-info btn-icon btn-xs"
style="width:12px;height:12px;margin-right:3px;padding:0;border-radius:2px;"></span>
@endfor

@else
<span class="text-muted">-</span>
@endif

</td>

<td style="text-align:center">
{{ $doc->issue_status_name ?: '-' }}
</td>

<td style="text-align:center">
<span class="label label-success">DONE</span>
</td>

</tr>

@empty

<tr>
<td colspan="9" class="text-center">
Tidak ada dokumen DONE.
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