
@extends('backend.layouts.master')

@section('title','User List')

@section('content')

<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
  <!--begin::Subheader-->
  <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
    <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
      <!--begin::Info-->
      <div class="d-flex align-items-center flex-wrap mr-2">
        <!--begin::Page Title-->
        <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">User List</h5>
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
                        <tr class="brac-color-pink">
                            <th>surveyid</th>
                            <th>enrollmentid</th>
                            <th>name</th>
                            <th>maintypeid</th>
                            <th>idno</th>
                            <th>phone </th>
                            <th>status</th>
                            <th>label</th>
                            <th>{{$label['labelName']}}</th>
                        </tr>
                      </thead>
                      @foreach($surveydata as $survey)
                      <tr>
                      <td>{{$survey->surveyid}}</td>
                      <td>{{$survey->entollmentid}}</td>
                      <td>{{$survey->name}}</td>
                      <td>{{$survey->mainidtypeid}}</td>
                      <td>{{$survey->idno}}</td>
                      <td>{{$survey->phone}}</td>
                      <td>{{$survey->status}}</td>
                      <td>{{$survey->label}}</td>
                      <td>{{$label['value']}}</td>
                      </tr>
                      @endforeach
                      <tbody>
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