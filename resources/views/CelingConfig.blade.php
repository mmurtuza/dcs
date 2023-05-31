
@extends('backend.layouts.master')

@section('title','Celling Config')
@section('style')
<style>
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
        <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">@lang('cellingConfig.header')</h5>
      </div>
      <!--end::Info-->
    </div>
  </div>
  <!--end::Subheader-->
  <!--begin::Entry-->
  <div class="d-flex flex-column-fluid">
    <!--begin::Container-->
    <div class="container">
      <!--begin::Dashboard-->
      <!--begin::Row-->
      <div class="row">
        <div class="col-md-12">
          <div class="card card-custom gutter-b">
            <!--begin::Form-->
            <div class="card-body">
                    
              <div class="row">
                <div class="col-md-12">
                  <table style="text-align: center;font-size:13" class="table table-bordered" id="data-table">
                    <thead>
                        <tr class="brac-color">
                            <th>@lang('cellingConfig.table_header1')</th>
                            <th>@lang('cellingConfig.table_header2')</th>
                            <th>@lang('cellingConfig.table_header3')</th>
                            <th>@lang('cellingConfig.table_header4')</th>
                            <th>@lang('cellingConfig.table_header5')</th>
                            <th>@lang('cellingConfig.table_header6')</th>
                            <th>@lang('cellingConfig.table_header7')</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($cellingData as $row)
                        <tr >
                            <td>{{$row->approver}}</td>
                            <td>{{$row->growth_rate}}</td>
                            <td>{{$row->limit_form}}</td>
                            <td>{{$row->limit_to}}</td>
                            <td>{{$row->repeat_limit_form}}</td>
                            <td>{{$row->repeat_limit_to}}</td>
                            <td><a class="btn btn-warning" href="#">Edit</a></td>
                        </tr>
                        @endforeach
                      </tbody>
                  </table>  
                </div>
            </div>
            </div>
            <!--end::Form-->
          </div>
          <!--end::Advance Table Widget 4-->
        </div>
        <br>
        
      </div>
      <!--end::Row-->
      <!--begin::Row-->
      
      <!--end::Row-->
      <!--end::Dashboard-->
    </div>
    <!--end::Container-->
  </div>
  <!--end::Entry-->
</div>
    
@endsection

@section('script')
<script>
$(document).ready( function () {
    $('#data-table').DataTable();
} );
   
</script>
@endsection