@extends('main')

@section('content')

<div class="content">

<h1 class="page-header">{{ $title }}</h1>

<table class="table table-bordered">

<thead>

<tr>
<th>Document No</th>
<th>Title</th>
<th>Vendor</th>
<th>Action</th>
</tr>

</thead>

<tbody>

@foreach($documents as $doc)

<tr>

<td>{{ $doc->document_no }}</td>

<td>{{ $doc->document_title }}</td>

<td>{{ $doc->vendor_name }}</td>

<td>

<a class="btn btn-warning btn-sm"
href="{{ url('comment_company/'.$doc->document_id.'?role='.($doc->user_role ?? '')) }}">

Comment

</a>

</td>

</tr>

@endforeach

</tbody>

</table>

{{ $documents->links() }}

</div>

@endsection