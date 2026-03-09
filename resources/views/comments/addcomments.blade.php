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
<h4 class="panel-title">{{ $title }}</h4>
</div>

<div class="panel-body">

<form class="form-horizontal" id="myform" name="myform" action="{{ URL::to('/').$form_act }}" method="post" enctype="multipart/form-data">

@csrf

@foreach ($fields as $row)
{!! $row !!}
@endforeach

@foreach ($fields_1 as $row)
{!! $row !!}
@endforeach

@foreach ($fields_2 as $row)
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

</form>

</div>

</div>

</div>
</div>

<div class="row">

<div class="col-md-12">

<div class="panel panel-inverse">

<div class="panel-heading">
<h4 class="panel-title">Comments History</h4>
</div>

<div class="panel-body">

<table class="table table-bordered">

<thead>

<tr>
<th>User</th>
<th>Time</th>
<th>Remark</th>
<th>Return Code</th>
<th>File</th>
</tr>

</thead>

<tbody>

@foreach($detail AS $row)

<tr>

<td>{{ $row->user_name }}</td>

<td>{{ $row->tanggal_log }}</td>

<td>{!! nl2br($row->remark) !!}</td>

<td>{{ $row->return_code }}</td>

<td>

@if(!empty($row->document_file))
<a href="{{ url('').'/uploads'.$row->document_url.$row->document_file }}" target="_blank">Attachment</a>
@endif

@if(!empty($row->document_file_2))
<a href="{{ url('').'/uploads'.$row->document_url.$row->document_file_2 }}" target="_blank">CRS</a>
@endif

</td>

</tr>

@endforeach

</tbody>

</table>

</div>

</div>

</div>

</div>

</div>


<script type="text/javascript" src="{{ asset('app/js/form_validation.js') }}"></script>
<script>
$(".preloader-container").hide();

</script>
@stop
