    @extends('backend.layouts.master')

    @section('title','Loan')
    <link href="https://cdn.datatables.net/1.11.1/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    @section('style')
    <style>
        .user_info {
            margin-bottom: 50px;
        }

        .text_aling {
            text-align: center;
            background: #FB3199;
            color: white;
            font-weight: bold;
        }

        .view_btn {
            text-decoration: none;
            color: #fff;
            padding: 7px;
            background-color: #EE9D01;
            border-radius: 15%;
        }

        label {
            margin-left: 2px;
        }

        .select2-selection__rendered {
            line-height: 31px !important;
            padding-top: 0px !important;
            padding-left: 16px !important;
        }

        .select2-container .select2-selection--single {
            height: 38px !important;
        }

        .select2-selection__arrow {
            height: 34px !important;
        }
    </style>
    @endsection

    @section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Subheader-->
        <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
            <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
                <!--begin::Info-->
                <div class="d-flex align-items-center flex-wrap mr-2">
                    <!--begin::Page Title-->
                    <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">@lang('loan.header')</h5>
                </div>
                <!--end::Info-->
            </div>
        </div>
        <div class="d-flex flex-column-fluid">
            <!--begin::Container-->
            <div class="container">
                <!--begin::Card-->
                <div class="card card-custom">
                    {{-- <div class="card-header flex-wrap py-5">
                <div class="card-title">
                <h3 class="card-label">Form </h3>
                </div>
            </div> --}}
                    <div class="card-body">
                        <!--begin: Datatable-->
                        <div class="row">
                            <div class="col-md-12">
                                @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul style="margin-bottom: 0rem;">
                                        @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                                @if (Session::has('success'))
                                <div class="alert alert-success" role="success">
                                    {{ Session::get('success') }}
                                </div>
                                @endif
                                @if (Session::has('error'))
                                <div class="alert alert-danger" role="success">
                                    {{ Session::get('error') }}
                                </div>
                                @endif
                                <div class="form_value">
                                    <!-- <form action="" method="get"> -->
                                    @if(session('role_designation')=='AM')
                                    <div class="form-row">
                                        <div class="col-md-2 mb-3">
                                            <label class="" for="division">@lang('loan.division')</label>
                                            <h6 id="division" class="mt-2">{{$branch->division_id}}-{{$branch->division_name}}</h6>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="" for="region">@lang('loan.region')</label>
                                            <h6 id="region" class="mt-2">{{$branch->region_id}}-{{$branch->region_name}}</h6>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="" for="area">@lang('loan.area')</label>
                                            <h6 id="area" class="mt-2">{{$branch->area_id}}-{{$branch->area_name}}</h6>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="branch">@lang('loan.branch')</label>
                                            <select id="branch" name="branch" class="form-control">
                                                <option value="">Select</option>
                                                @foreach($search2 as $row)
                                                <option value="{{$row->branch_id}}">{{$row->branch_id}}-{{$row->branch_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="po">@lang('loan.po')</label>
                                            <select id="po" name="po" class="form-control">
                                            </select>
                                        </div>

                                    </div>
                                    <div class="form-row">
                                        @php
                                        $month = date('m');
                                        $day = date('d');
                                        $year = date('Y');
                                        $today = $year . '-' . $month . '-' . $day;
                                        $fromday =date('Y-m')."-01";
                                        @endphp
                                        <div class="col-md-2 mb-3">
                                            <label for="dateFrom" class="form-label">@lang('loan.dateRange')</label>
                                            <input type="date" value="<?php echo $fromday; ?>" class="form-control" id="dateFrom" name="dateFrom">
                                        </div>
                                        <div class="col-md-2 mb-3 mt-6">
                                            <label class="ml-2" for="level"></label>
                                            <input type="date" value="<?php echo $today; ?>" class="form-control" id="dateTo" name="dateTo">
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="status">@lang('loan.status')</label>
                                            <select id="status" name="status" class="form-control">
                                                <option value="">Select</option>
                                                @foreach($status as $row)
                                                <option value="{{$row->status_id}}">{{$row->status_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-2 mb-3 mt-8">
                                            <button type="submit" id="filter" class="btn btn-secondary">@lang('actionBtn.search')</button>
                                        </div>
                                    </div>
                                    @endif
                                    @if(session('role_designation')=='RM')
                                    <div class="form-row">
                                        <div class="col-md-2 mb-3">
                                            <label class="" for="division">@lang('loan.division')</label>
                                            <h6 id="division" class="mt-2">{{$branch->division_id}}-{{$branch->division_name}}</h6>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="" for="region">@lang('loan.region')</label>
                                            <h6 id="region" class="mt-2">{{$branch->region_id}}-{{$branch->region_name}}</h6>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="area">@lang('loan.area')</label>
                                            <select id="area" name="area" class="form-control">
                                                <option value="">Select</option>
                                                @foreach($search2 as $row)
                                                <option value="{{$row->area_id}}">{{$row->area_id}}-{{$row->area_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="branch">@lang('loan.branch')</label>
                                            <select id="branch" name="branch" class="form-control">
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="po">@lang('loan.po')</label>
                                            <select id="po" name="po" class="form-control">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        @php
                                        $month = date('m');
                                        $day = date('d');
                                        $year = date('Y');
                                        $today = $year . '-' . $month . '-' . $day;
                                        $fromday =date('Y-m')."-01";
                                        @endphp
                                        <div class="col-md-2 mb-3">
                                            <label for="Project" class="form-label">@lang('loan.dateRange')</label>
                                            <input type="date" value="<?php echo $fromday; ?>" class="form-control" id="dateFrom" name="dateFrom">
                                        </div>
                                        <div class="col-md-2 mb-3 mt-6">
                                            <label class="ml-2" for="level"></label>
                                            <input type="date" value="<?php echo $today; ?>" class="form-control" id="dateTo" name="dateTo">
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="status">@lang('loan.status')</label>
                                            <select id="status" name="status" class="form-control">
                                                <option value="">Select</option>
                                                @foreach($status as $row)
                                                <option value="{{$row->status_id}}">{{$row->status_name}}</option>
                                                @endforeach>
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3 mt-8">
                                            <button type="submit" id="filter" class="btn btn-secondary">@lang('actionBtn.search')</button>
                                        </div>
                                    </div>
                                    @endif
                                    @if(session('role_designation')=='DM')
                                    <div class="form-row">
                                        <div class="col-md-2 mb-3">
                                            <label class="" for="division">@lang('loan.division')</label>
                                            <h6 id="division" class="mt-2">{{$branch->division_id}}-{{$branch->division_name}}</h6>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="region">@lang('loan.region')</label>
                                            <select id="region" name="region" class="form-control">
                                                <option value="">Select</option>
                                                @foreach($search2 as $row)
                                                <option value="{{$row->region_id}}">{{$row->region_id}}-{{$row->region_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="area">@lang('loan.area')</label>
                                            <select id="area" name="area" class="form-control">
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="branch">@lang('loan.branch')</label>
                                            <select id="branch" name="branch" class="form-control">
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="po">@lang('loan.po')</label>
                                            <select id="po" name="po" class="form-control">
                                            </select>
                                        </div>

                                    </div>
                                    <div class="form-row">
                                        @php
                                        $month = date('m');
                                        $day = date('d');
                                        $year = date('Y');
                                        $today = $year . '-' . $month . '-' . $day;
                                        $fromday =date('Y-m')."-01";
                                        @endphp
                                        <div class="col-md-2 mb-3">
                                            <label for="dateFrom" class="form-label">@lang('loan.dateRange')</label>
                                            <input type="date" value="<?php echo $fromday; ?>" class="form-control" id="dateFrom" name="dateFrom">
                                        </div>
                                        <div class="col-md-2 mb-3 mt-6">
                                            <label class="ml-2" for="level"></label>
                                            <input type="date" value="<?php echo $today; ?>" class="form-control" id="dateTo" name="dateTo">
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="status">@lang('loan.status')</label>
                                            <select id="status" name="status" class="form-control">
                                                <option value="">Select</option>
                                                @foreach($status as $row)
                                                <option value="{{$row->status_id}}">{{$row->status_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3 mt-8">
                                            <button type="submit" id="filter" class="btn btn-secondary">@lang('actionBtn.search')</button>
                                        </div>
                                    </div>
                                    @endif
                                    @if((session('role_designation')=='HO') or (session('role_designation')=='PH'))
                                    <div class="form-row">
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="division">@lang('loan.division')</label>
                                            <select id="division" name="division" class="form-control">
                                                <option value="">Select</option>
                                                @foreach($search2 as $row)
                                                <option value="{{$row->division_id}}">{{$row->division_id}}-{{$row->division_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="region">@lang('loan.region')</label>
                                            <select id="region" name="region" class="form-control">
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="area">@lang('loan.area')</label>
                                            <select id="area" name="area" class="form-control">
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="branch">@lang('loan.branch')</label>
                                            <select id="branch" name="branch" class="form-control">
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="po">@lang('loan.po')</label>
                                            <select id="po" name="po" class="form-control">
                                            </select>
                                        </div>

                                    </div>
                                    @php
                                    $month = date('m');
                                    $day = date('d');
                                    $year = date('Y');
                                    $today = $year . '-' . $month . '-' . $day;
                                    $fromday =date('Y-m-d');
                                    @endphp
                                    <div class="form-row">
                                        <div class="col-md-3 mb-3">
                                            <label class="ml-2" for="branch_wise_search">@lang('loan.branchSearch')</label>
                                            <select style="" id="branch_wise_search" name="branch_wise_search" class="form-control pb-2">
                                                <option value="">Select</option>
                                                @foreach($value as $row)
                                                <option value="{{$row->branch_id}}">{{$row->branch_id}}-{{$row->branch_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label for="dateFrom" class="form-label">@lang('loan.dateRange')</label>
                                            <input type="date" value="<?php echo $fromday; ?>" id="dateFrom" class="form-control" id="Project" name="dateFrom">
                                        </div>

                                        <div class="col-md-2 mb-3 mt-6">
                                            <label class="ml-2" for="level"></label>
                                            <input type="date" value="<?php echo $today; ?>" min="" max="" id="dateTo" class="form-control" id="Project" name="dateTo">
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="status">@lang('loan.status')</label>
                                            <select id="status" name="status" class="form-control">
                                                <option value="">Select</option>
                                                @foreach($status as $row)
                                                <option value="{{$row->status_id}}">{{$row->status_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3 mt-8">
                                            <button type="button" id="filter" class="btn btn-secondary">@lang('actionBtn.search')</button>
                                        </div>
                                    </div>
                                    @endif
                                    <!-- </form> -->
                                </div>
                                <br>
                                <table style="text-align: center;" class="table table-bordered" id="myTable">
                                    <thead>
                                        <tr class="brac-color">
                                            <th>@lang('loan.table_header1')</th>
                                            <th>@lang('loan.table_header2')</th>
                                            <th>@lang('loan.table_header4')</th>
                                            <th>@lang('loan.table_header5')</th>
                                            <th>@lang('loan.table_header6')</th>
                                            <th>@lang('loan.table_header7')</th>
                                            <th>@lang('loan.table_header9')</th>
                                            <th>@lang('loan.table_header8')</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!--end: Datatable-->
                    </div>
                </div>
                <!--end::Card-->
            </div>
            <!--end::Container-->
        </div>
    </div>
    @endsection

    @section('script')

    <script>
        $(document).ready(function() {
            // branch-wish-select
            $("#branch_wise_search").select2();
            $('#branch_wise_search').on('change', function() {
                if ($(this).val()) {
                    var branch_filter = $(this).val();
                    $.ajax({
                        url: "{{url('operation/branch_filter')}}",
                        type: 'get',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            branch_filter: branch_filter,
                        },
                        success: function(data) {
                            console.log(data);
                            $("#division").empty();
                            $("#region").empty();
                            $("#area").empty();
                            $("#branch").empty();
                            $.each(data, function(key, value) {
                                $("#division").append(`<option value="` + value
                                    .division_id + `">` + value.division_id + `-` + value.division_name +
                                    `</option>`)
                                $("#region").append(`<option value="` + value
                                    .region_id + `">` + value.region_id + `-` + value.region_name +
                                    `</option>`)
                                $("#area").append(`<option value="` + value.area_id +
                                    `">` + value.area_id + `-` + value.area_name + `</option>`)

                                $("#branch").append(` <option value="` + value
                                    .branch_id + `">` + value.branch_id + `-` + value.branch_name +
                                    `</option>`)
                            });
                        }
                    });
                }
            });

            //max 30 days
            $("#dateTo").click(function() {
                var mydate = $('input[name^=dateFrom]').map(function(idx, elem) {
                    return $(elem).val();
                }).get()
                caldate = new Date(mydate);

                caldate.setDate(caldate.getDate() + 30);
                // var day = caldate.getDate();
                var day = ("0" + (caldate.getDate() + 1)).slice(-2)
                var month = ("0" + (caldate.getMonth() + 1)).slice(-2)
                var year = caldate.getFullYear();
                var fullDate = year + '-' + month + '-' + day;
                var myDatePicker = document.getElementById('dateTo');
                myDatePicker.setAttribute('max', fullDate);
                myDatePicker.setAttribute('min', mydate);
            });
            //max 30 days end
            fill_datatable();

            function fill_datatable(division = '', region = '', area = '', status = '', branch = '', dateFrom = '', dateTo = '', po = '') {
                var table = $('#myTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: "{{url('operation/loanTable')}}",
                        data: {
                            area: area,
                            division: division,
                            region: region,
                            status: status,
                            branch: branch,
                            dateFrom: dateFrom,
                            dateTo: dateTo,
                            po: po
                        }
                    },
                    columns: [{
                            data: 'branchcode',
                            name: 'branchcode'
                        },
                        {
                            data: 'time',
                            name: 'time',
                            format: 'dd-mm-yyyy'
                        },
                        {
                            data: 'assignedpo',
                            name: 'assignedpo'
                        },
                        {
                            data: 'ApplicantsName',
                            name: 'ApplicantsName'
                        },
                        {
                            data: 'propos_amt',
                            name: 'propos_amt'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'ERPstatus',
                            name: 'ERPstatus'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                    ]
                });
            };
            $('#filter').click(function() {
                var division = $('#division').val();
                var region = $('#region').val();
                var area = $('#area').val();
                var status = $('#status').val();
                var branch = $('#branch').val();
                var dateFrom = $('#dateFrom').val();
                var dateTo = $('#dateTo').val();
                var po = $('#po').val();
                if (division) {
                    $('#myTable').DataTable().destroy();
                    fill_datatable(division, region, area, status, branch, dateFrom, dateTo, po);
                } else if (region) {
                    $('#myTable').DataTable().destroy();
                    fill_datatable(division, region, area, status, branch, dateFrom, dateTo, po);
                } else if (area) {
                    $('#myTable').DataTable().destroy();
                    fill_datatable(division, region, area, status, branch, dateFrom, dateTo, po);
                } else if (branch) {
                    $('#myTable').DataTable().destroy();
                    fill_datatable(division, region, area, status, branch, dateFrom, dateTo, po);
                } else if (po) {
                    $('#myTable').DataTable().destroy();
                    fill_datatable(division, region, area, status, branch, dateFrom, dateTo, po);
                } else if (status != '') {
                    $('#myTable').DataTable().destroy();
                    fill_datatable(division, region, area, status, branch, dateFrom, dateTo, po);
                } else if (dateFrom && dateTo) {
                    $('#myTable').DataTable().destroy();
                    fill_datatable(division, region, area, status, branch, dateFrom, dateTo, po);
                } else if (branch && status) {
                    $('#myTable').DataTable().destroy();
                    fill_datatable(division, region, area, status, branch, dateFrom, dateTo, po);
                } else if (dateFrom && dateTo && branch) {
                    $('#myTable').DataTable().destroy();
                    fill_datatable(division, region, area, status, branch, dateFrom, dateTo, po);
                } else if (dateFrom && dateTo && status) {
                    $('#myTable').DataTable().destroy();
                    fill_datatable(division, region, area, status, branch, dateFrom, dateTo, po);
                } else if (dateFrom && dateTo && status && branch) {
                    $('#myTable').DataTable().destroy();
                    fill_datatable(division, region, area, status, branch, dateFrom, dateTo, po);
                } else {
                    $('#myTable').DataTable().destroy();
                    fill_datatable();
                }
            });


            $('#division').on('change', function() {
                if ($(this).val() != '') {
                    var division = $(this).val();
                    $.ajax({
                        url: "{{url('operation/admission-division')}}",
                        type: 'POST',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            division: division,
                        },
                        success: function(data) {
                            $("#region").empty();
                            $(`<option value="">Select</option>`).appendTo("#region");
                            $.each(data, function(key, value) {
                                $("#region").append(` <option value="` + value
                                    .region_id + `">` + value.region_id + `-` + value.region_name +
                                    `</option>`)

                            });
                        }
                    });
                }
            });

            $('#region').on('change', function() {
                if ($(this).val() != '') {
                    var region = $(this).val();
                    $.ajax({
                        url: "{{url('operation/admission-region')}}",
                        type: 'POST',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            region: region,
                        },
                        success: function(data) {
                            console.log(data);
                            $("#area").empty();
                            $(`<option value="">Select</option>`).appendTo("#area");
                            $.each(data, function(key, value) {
                                $("#area").append(`<option value="` + value
                                    .area_id + `">` + value.area_id + `-` + value.area_name +
                                    `</option>`)
                            });
                        }
                    });
                }
            });

            $('#area').on('change', function() {
                if ($(this).val() != '') {
                    var area = $(this).val();

                    $.ajax({
                        url: "{{url('operation/admission-area')}}",
                        type: 'POST',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            area: area,
                        },
                        success: function(data) {
                            $("#branch").empty();
                            $(`<option value="">Select</option>`).appendTo("#branch");
                            $.each(data, function(key, value) {
                                $("#branch").append(` <option value="` + value
                                    .branch_id + `">` + value.branch_id + `-` + value.branch_name +
                                    `</option>`)

                            });
                        }
                    });
                }
            });

            $('#branch').on('focus', function() {
                if ($(this).val() != '') {
                    var branch = $(this).val();

                    $.ajax({
                        url: "{{url('operation/admission-branch')}}",
                        type: 'POST',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            branch: branch,
                        },
                        success: function(data) {
                            $("#po").empty();
                            $(`<option value="">Select</option>`).appendTo("#po");
                            $.each(data, function(key, value) {
                                $("#po").append(` <option value="` + value
                                    .cono + `">` + value.cono + `-` + value.coname +
                                    `</option>`)

                            });
                        }
                    });
                }
            });
        });
    </script>
    @endsection
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    <script src="cdn.datatables.net/1.11.1/js/jquery.dataTables.min.js"></script>
