<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<head>
    <meta charset="utf-8"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EDMS</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport"/>
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <link rel="icon" type="image/ico" href="{{ asset('app/img/icon/faviconn.ico') }}"/>
    <!-- ================== BEGIN BASE CSS STYLE ================== -->
    <link href="{{ asset('vendor/color_admin/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('vendor/color_admin/plugins/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('vendor/color_admin/css/style.min.css') }}" rel="stylesheet"/>
    <!-- ================== END BASE CSS STYLE ================== -->
    <style>
        /* ===============================
        Vendor Section
        =============================== */
        .vendor-title {
            font-size: 22px;
            font-weight: 600;
            margin: 40px 0 20px;
            padding-bottom: 8px;
            border-bottom: 1px solid #dcdcdc;
            color: #1f2937;
            letter-spacing: 0.3px;
        }

        /* ===============================
        Widget Container
        =============================== */
        .widget {
            padding: 24px 28px;
            border-radius: 8px;
        }

        .widget-stats {
            min-height: 220px;
            position: relative;
            overflow: hidden;
        }

        /* ===============================
        Title (IFR, IFA, IFU, AFC)
        =============================== */
        .widget-stats > .stats-title {
            color: #000;
            font-weight: 700;
            font-size: 38px;
            margin-bottom: 6px;
            letter-spacing: 1px;
        }

        /* ===============================
        Number (270, 24, etc)
        =============================== */
        .widget-stats > .stats-number {
            font-size: 42px;
            font-weight: 600;
            line-height: 1.25;
            margin-bottom: 12px;
            color: #000;
            white-space: normal;
            word-break: break-word;
        }

        /* ===============================
        Description
        =============================== */
        .widget-stats > .stats-desc {
            font-size: 16px;
            color: #111;
            margin-top: 8px;
            letter-spacing: 0.2px;
        }

        /* ===============================
        Divider Line
        =============================== */
        .widget-stats .stats-progress {
            height: 2px;
            background: rgba(255, 255, 255, 0.7);
            margin: 10px 0;
        }

        /* ===============================
        Icon (Watermark Style)
        =============================== */
        .widget-stats > .stats-icon.stats-icon-lg {
            font-size: 62px;
            position: absolute;
            top: 22px;
            right: 26px;
            opacity: 0.15;
            color: #000;
        }

        /* ===============================
        Grid Spacing
        =============================== */
        .row > [class*="col-"] {
            margin-bottom: 24px;
        }

        /* ===============================
        Screenshot / Report Optimization
        =============================== */
        @media print {
            body {
                zoom: 0.9;
            }
        }
    </style>

</head>
<body>
<div id="content" class="content" style="margin-left: 0px; padding: 55px 55px;">
    @foreach ($summary['data'] as $row)

        {{-- Nama Vendor --}}
        <div class="vendor-title">
            {{ $row->name }}
        </div>

        <div class="row" style="margin-top: 30px;">

            <div class="col-sm-6">
                <div class="widget widget-stats" style="background-color: #FFCC00; color: black;">
                    <div class="stats-icon stats-icon-lg"><i class="fa fa-tags fa-fw"></i></div>
                    <div class="stats-title">IFR</div>
                    <div class="stats-number">
                        {{ number_format($row->ifc_status, 0) }} document(s)
                    </div>
                    <div class="stats-progress progress">
                        <div class="progress-bar" style="width: 100%;"></div>
                    </div>
                    <div class="stats-desc">Issue for Review</div>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="widget widget-stats" style="background-color: #FF9900; color: black;">
                    <div class="stats-icon stats-icon-lg"><i class="fa fa-check-square fa-fw"></i></div>
                    <div class="stats-title">IFA</div>
                    <div class="stats-number">
                        {{ number_format($row->ifa_status, 0) }} document(s)
                    </div>
                    <div class="stats-progress progress">
                        <div class="progress-bar" style="width: 100%;"></div>
                    </div>
                    <div class="stats-desc">Issue for Approval</div>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="widget widget-stats bg-blue" style="color: black;">
                    <div class="stats-icon stats-icon-lg"><i class="fa fa-legal fa-fw"></i></div>
                    <div class="stats-title">IFU</div>
                    <div class="stats-number">
                        {{ number_format($row->afd_status, 0) }} document(s)
                    </div>
                    <div class="stats-progress progress">
                        <div class="progress-bar" style="width: 100%;"></div>
                    </div>
                    <div class="stats-desc">Issued for Use</div>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="widget widget-stats bg-green" style="color: black;">
                    <div class="stats-icon stats-icon-lg"><i class="fa fa-gear fa-fw"></i></div>
                    <div class="stats-title">AFC</div>
                    <div class="stats-number">
                        {{ number_format($row->afc_status, 0) }} document(s)
                    </div>
                    <div class="stats-progress progress">
                        <div class="progress-bar" style="width: 100%;"></div>
                    </div>
                    <div class="stats-desc">Approved for Construction</div>
                </div>
            </div>

        </div>

    @endforeach

</div>
</body>
</html>