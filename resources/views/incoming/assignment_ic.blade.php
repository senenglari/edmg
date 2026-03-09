@extends('main')

@push('styles')
    <!-- CSS DataTables (CDN + fallback) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
@endpush

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
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success_message'))
                    <div class="alert alert-success">
                        {{ session('success_message') }}
                    </div>
                @endif

                <div class="panel-body">
                    <!-- FORM UTAMA -->
                    <form class="form-horizontal" action="{{ $form_act }}" method="post">
                        @csrf
                        <input type="hidden" name="idDoc" value="{{ $idDoc }}">

                        <!-- TABEL DENGAN DATATABLES -->
                        <div class="table-responsive">
                            <table id="assignmentTable" class="table table-bordered table-hover display nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Discipline</th>
                                        <th>Position</th>
                                        <th>Role</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($comment as $row)
                                        <tr>
                                            <td>{{ $row->full_name ?? '-' }}</td>
                                            <td>{{ $row->department_name ?? '-' }}</td>
                                            <td>{{ $row->discipline_name ?? '-' }}</td>
                                            <td>{{ $row->position_name ?? '-' }}</td>
                                            <td>{{ $row->role ?? '-' }}</td>
                                            <td>
                                                <a href="{{ url('incoming_company/assignment/delete-user/' . $row->comment_id) }}" 
                                                   onclick="return confirm('Yakin hapus?')" 
                                                   class="btn btn-xs btn-danger">
                                                    Hapus
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">Belum ada assignment</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- FORM TAMBAH USER -->
                        <div class="panel panel-default m-t-20">
                            <div class="panel-heading">
                                <h3 class="panel-title">Tambah User Baru</h3>
                            </div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">User</label>
                                    <div class="col-md-6">
                                        <select name="user_id" class="form-control" required>
                                            <option value="">-- Pilih User --</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}">
                                                    {{ $user->full_name }} - {{ $user->department_name ?? '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">Role</label>
                                    <div class="col-md-6">
                                        <select name="role" class="form-control" required>
                                            <option value="">-- Pilih Role --</option>
                                            <option value="RESPONSIBILITY">RESPONSIBILITY</option>
                                            <option value="OWNER">OWNER</option>
                                            <option value="APPROVER">APPROVER</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-6 col-md-offset-3">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-plus"></i> Tambah User
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- JS jQuery (pastikan hanya sekali, kalau sudah ada di main.blade.php, hapus ini) -->


<!-- JS DataTables -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<!-- Buttons Export -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    console.log('jQuery version:', $.fn.jquery); // debug: harus muncul 3.7.1
    console.log('DataTables loaded:', typeof $.fn.DataTable); // harus 'function'

    var table = $('#assignmentTable').DataTable({
        "paging": true,
        "pagingType": "full_numbers",
        "lengthChange": true,
        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Semua"]],
        "pageLength": 5,  // kecil dulu biar paging kelihatan meski data sedikit
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "scrollX": true,
        "deferRender": true,
        "responsive": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
        },
        "dom": 'Bfrtip',
        "buttons": ['copy', 'csv', 'excel', 'pdf', 'print']
    });

    console.log('DataTables initialized successfully');
});
</script>
@endpush

@stop