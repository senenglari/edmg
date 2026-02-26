
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/ico" href="{{ asset('app/img/icon/favicon.ico') }}" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script>
</head>
<body>
    <div class="card card-1" style="padding: 20px;">
        <h5>{{ $title }}</h5>
        <span>Periode : <strong>{{ $periode }}</strong></span><br /><br />
        <table id="tabel-data" class="display compact nowrap display" style="width:100%;"></table>
    </div>
    <form>
        <input type="hidden" id="content_center" value="{{ $content_center }}" />
        <input type="hidden" id="content_right" value="{{ $content_right }}" />
        <br>
        <span id="message"></span>
        <input type="hidden" id="text_jumlah_baris" name="text_jumlah_baris" value="{{ (empty($jumlah_baris)) ? 20 : $jumlah_baris  }}" />
    </form>
</body>
</html>
<script>
    var columns = [];

    $(document).ready(function() {
        $.ajax({
            url: "{{ $url_data }}",
            headers: {
                "Authorization": "Bearer " + "{{ $token }}"
            },
            success: function (data) {
                var columnNames     = Object.keys(data.data[0]);
                var col_center      = $("#content_center").val();
                var content_center  = col_center.split(',').map(function(item) {
                    return parseInt(item, 10);
                });

                var col_right   = $("#content_right").val();

                var col_array   = col_right.split(',').map(function(item) {
                    return parseInt(item, 10);
                });

                for (var i in columnNames) {
                    var h = columnNames[i].replace(/_/g, " ");
                    columns.push({
                        data: columnNames[i],
                        title: h.toUpperCase(),
                        width: '200px',
                    });
                }

                var table = $('#tabel-data').DataTable({
                    pageLength: 12,
                    processing: true,
                    serverSide: false,
                    data: data.data,
                    columns: columns,
                    dom: 'Bfrtip',
                    scrollX: true,
                    autoWidth: true,
                    buttons: [
                        {
                            extend: 'copyHtml5',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'excelHtml5',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            
                            extend: 'csvHtml5',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                    ],
                    columnDefs: [
                        { "render": $.fn.dataTable.render.number(",", ".", 0, ''), "targets": col_array },
                        { "className": "text-center", "targets": content_center },
                        { "className": "text-right", "targets": col_array },
                    ],
                });

            }
        })
    });

    function formatNumber (num) {
        return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,")
    }
</script>
<style type="text/css" media="screen">
    * {
        margin: 0px;
        padding: 0;
        outline: 0;
    }
    body {
        font-family: Tahoma, Verdana, Arial, Helvetica, Sans-Serif;
        color: #000000;
        font-size: 11px;
        background: #e2e1e0;
    }
    ::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }
    ::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    ::-webkit-scrollbar-thumb {
        background: #888;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    /*.dataTables_filter {
        float: left !important;
    }*/
    tr { height: 35px; }
    td { margin-left: 10px; margin-right: 10px; }

    .card {
  background: #fff;
  border-radius: 2px;
  display: inline-block;
  margin: 1rem;
  position: relative;
  width: 97.9%;
}

.card-1 {
  box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
  transition: all 0.3s cubic-bezier(.25,.8,.25,1);
}
</style>