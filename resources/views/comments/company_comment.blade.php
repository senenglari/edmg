@extends('main')

@section('content')

<div class="content">

<h1 class="page-header">{{ $title }}</h1>

<table class="table table-bordered">

<thead>

<tr>
<th>User</th>
<th>Role</th>
<th>Comment</th>
<th>Status</th>
<th>Action</th>
</tr>

</thead>

<tbody>

@foreach($comments as $c)

<tr>

<td>{{ $c->full_name }}</td>

<td>{{ $c->role }}</td>

<td>

<form method="POST" action="{{ route('comment_company.save') }}">

@csrf

<input type="hidden" name="comment_id" value="{{ $c->comment_id }}">

<textarea name="remark" class="form-control">
{{ $c->remark }}
</textarea>

</td>

<td>

<select name="status" class="form-control">

<option value="10" {{ $c->status==10?'selected':'' }}>
Assigned
</option>

<option value="20" {{ $c->status==20?'selected':'' }}>
In Progress
</option>

<option value="30" {{ $c->status==30?'selected':'' }}>
Done
</option>

</select>

</td>

<td>

<button class="btn btn-success btn-sm">
Save
</button>

</form>

</td>

</tr>

@endforeach

</tbody>

</table>

</div>

@endsection