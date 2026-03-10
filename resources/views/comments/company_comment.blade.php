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

                @if (session('success_message'))
                <div class="alert alert-success">
                    {{ session('success_message') }}
                </div>
                @endif

                <div class="panel-body">
                    <!-- FORM ADD COMMENT -->
                    @if(!(Auth::user()->role == 'OWNER' && ($doc->note_backdoor ?? '') == '5'))
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">Add Comment</h4>
                        </div>
                        <div class="panel-body">
                            <form class="form-horizontal" action="{{ route('comment_company.save') }}" method="post">
                                @csrf
                                <input type="hidden" name="document_id" value="{{ $doc->document_id }}">
                                <input type="hidden" name="note_backdoor" value="{{ $doc->note_backdoor }}">
                                <input type="hidden" name="comment_id" id="comment_id" value="{{ $editComment->comment_id ?? '' }}">

                                <div class="form-group">
                                    <label class="col-md-3 control-label">Comment</label>
                                    <div class="col-md-6">
                                        <textarea name="remark" class="form-control" rows="4" placeholder="Enter your comment..." required>{{ old('remark', $editComment->remark ?? '') }}</textarea>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">Status</label>
                                    <div class="col-md-6">
                                        <select name="status" class="form-control" required>
                                            <option value="">-- Select Status --</option>
                                            <option value="10" {{ (old('status',$editComment->status ?? '')==10) ? 'selected' : '' }}>Assigned</option>
                                            <option value="20" {{ (old('status',$editComment->status ?? '')==20) ? 'selected' : '' }}>In Progress</option>
                                            <option value="30" {{ (old('status',$editComment->status ?? '')==30) ? 'selected' : '' }}>Done</option>
                                        </select>
                                    </div>
                                </div>
                                @if(in_array($docRole ?? '', ['APPROVER_COMPANY','ADMINISTRATOR']) && !empty($nextStageOptions))
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Next Stage</label>
                                    <div class="col-md-6">
                                        <select name="next_stage" class="form-control">
                                            @foreach($nextStageOptions as $key=>$label)
                                                <option value="{{ $key }}" {{ old('next_stage')==$key ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @endif

                                <div class="form-group">
                                    <label class="col-md-3 control-label">Revision Status</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" readonly
                                               value="{{ $doc->issue_status_name ?? '' }}">
                                        <!-- value is derived from document's issue status -->
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Return Status</label>
                                    <div class="col-md-6">
                                        <select name="return_status_id" class="form-control">
                                            <option value="">-- Select Return Status --</option>
                                            @foreach($returnStatusOptions as $id => $name)
                                                <option value="{{ $id }}" {{ (old('return_status_id',$editComment->return_status_id ?? '') == $id) ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-6 col-md-offset-3">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-save"></i> Save Comment
                                        </button>
                                        <a href="{{ url()->previous() }}" class="btn btn-default">
                                            <i class="fa fa-arrow-left"></i> Back
                                        </a>
                                        @if($doc)
                                            <a href="{{ route('incoming_company.document.view', encodedData($doc->document_id)) }}" 
                                               target="_blank" 
                                               class="btn btn-info" 
                                               title="View Document Attachment">
                                                <i class="fa fa-eye"></i> View Attachment
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @else
                        <div class="alert alert-info">
                            You cannot add more comments as the document has moved past the OWNER stage.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- COMMENTS HISTORY -->
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse"><i class="fa fa-minus"></i></a>
                    </div>
                    <h4 class="panel-title">Comments History</h4>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="20%">User</th>
                                    <th width="15%">Role</th>
                                    <th width="15%">Status</th>
                                    <th width="15%">Revision</th>
                                    <th width="">Comment</th>
                                    <th width="15%">Attachment</th>
                                    <th width="10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($comments as $c)
                                    <tr>
                                        <td>{{ $c->full_name }}</td>
                                        <td>
                                            <span class="label label-{{ $c->role == 'RESPONSIBLE' ? 'primary' : ($c->role == 'OWNER' ? 'warning' : 'success') }}">
                                                {{ $c->role }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="label label-{{ $c->status == 10 ? 'default' : ($c->status == 20 ? 'info' : 'success') }}">
                                                {{ $c->status == 10 ? 'Assigned' : ($c->status == 20 ? 'In Progress' : 'Done') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if(isset($c->revision_status))
                                                <span class="label label-info">{{ $c->revision_status }}</span>
                                            @else
                                                <span class="label label-default">-</span>
                                            @endif
                                        </td>
                                        <td>{!! nl2br($c->remark ?? '-') !!}</td>
                                        <td>
                                            <table style="margin-left: auto; margin-right: auto;">
                                                <tr>
                                                    <td style="text-align: center;" width="50%">
                                                        <a href="{{ route('incoming_company.comment.download', encodedData($c->comment_id)) }}" 
                                                           target="_blank" 
                                                           class="btn btn-xs btn-info" 
                                                           title="View Attachment">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td>
                                            <a href="{{ url('comment_company/'.$doc->document_id.'?edit_id='.$c->comment_id) }}{{ ($docRole ?? '') ? '&role='.$docRole : '' }}" class="btn btn-xs btn-warning" title="Edit Comment">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No comments yet</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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