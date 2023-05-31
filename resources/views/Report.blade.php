@extends('backend.layouts.master')

@section('title','Report')
<link href="cdn.datatables.net/1.11.1/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
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
    /* font-weight: bold; */
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
        <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">@lang('report.header')</h5>
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
                <form action="{{route('report.search')}}" method="post">
                  <div class="form-row">
                    <div class="col-md-2 mb-3">
                      <label class="ml-2" for="report_type">@lang('report.report_type')</label>
                      <select id="report_type" name="report_type" class="form-control">
                        <option value="summary">Summary Report</option>
                        <option value="detailed">Detailed Report</option>
                      </select>
                    </div>
                    <div class="col-md-2 mb-3">
                      <label class="ml-2" for="division">@lang('report.division')</label>
                      <select id="division" name="division" class="form-control">
                        <option value="">Select</option>
                        @foreach($search2 as $row)
                        <option value="{{$row->division_id}}">{{$row->division_id}}-{{$row->division_name}}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-2 mb-3">
                      <label class="ml-2" for="region">@lang('report.region')</label>
                      <select id="region" name="region" class="form-control">
                        <option value="">Select</option>
                      </select>
                    </div>
                    <div class="col-md-2 mb-3">
                      <label class="ml-2" for="area">@lang('report.area')</label>
                      <select id="area" name="area" class="form-control">
                        <option value="">Select</option>
                      </select>
                    </div>
                    <div class="col-md-2 mb-3">
                      <label class="ml-2" for="branch">@lang('report.branch')</label>
                      <select id="branch" name="branch" class="form-control">
                        <option value="">Select</option>
                      </select>
                    </div>
                    <div class="col-md-2 mb-3">
                      <label class="ml-2" for="po">@lang('report.po')</label>
                      <select id="po" name="po" class="form-control">
                        <option value="">Select</option>
                      </select>
                    </div>
                  </div>
                  @php
                  $month = date('m');
                  $day = date('d');
                  $year = date('Y');
                  $today = $year . '-' . $month . '-' . $day;
                  $firstdayofthemonth=date('Y-m-01');
                  @endphp
                  <div class="form-row">
                    <div class="col-md-2 mb-3">
                      <label for="dateFrom" class="form-label">@lang('report.dateRange')</label>
                      <input type="date" value="<?php echo $firstdayofthemonth; ?>" id="dateFrom" class="form-control" id="Project" name="dateFrom">
                    </div>

                    <div class="col-md-2 mb-3 mt-6">
                      <label class="ml-2" for="dateTo"></label>
                      <input type="date" value="<?php echo $today; ?>" min="" max="" id="dateTo" class="form-control" id="Project" name="dateTo">
                    </div>
                    <div class="col-md-2 mb-3 mt-8">
                      <button type="submit" id="filter" class="btn btn-secondary">@lang('actionBtn.search')</button>
                    </div>
                  </div>
                  <!-- </form> -->
              </div>
              <br>
              <h4>@lang('report.titile1')</h4>
              <br>
              <table style="text-align: center;" class="table table-bordered" id="summary_table">
                <thead>
                  <tr class="brac-color">
                    <th>@lang('report.table_header1')</th>
                    <th>@lang('report.table_header2')</th>
                    <th>@lang('report.table_header3')</th>
                    <th>@lang('report.table_header4')</th>
                    <th>@lang('report.table_header5')</th>
                    <th>@lang('report.table_header6')</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>All</td>
                    <td>{{ $noOfSurvey }}</td>
                    <td>{{ $noOfAdmission }}</td>
                    <td>{{ $noOfLoan }}</td>
                    <td>{{ $totalDisAmount }}</td>
                    <td>{{ $averageLoanSize }}</td>
                  </tr>
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
    $('#summary_table').DataTable({
      dom: 't',
      ordering: false,
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
            console.log(data);
            $("#region").empty();
            $(`<option value="">Select</option>`).appendTo("#region");
            $.each(data, function(key, value) {
              $("#region").append(`<option value="` + value
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
              $("#area").append(`<option value="` + value.area_id +
                `">` + value.area_id + `-` + value.area_name + `</option>`)
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

    $('#branch').on('change', function() {
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

    // $('#po').on('change', function () {
    //     alert($(this).val() )

    // });


  });
</script>
@endsection
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
<script src="cdn.datatables.net/1.11.1/js/jquery.dataTables.min.js"></script>