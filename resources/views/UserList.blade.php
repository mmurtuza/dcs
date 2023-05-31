
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
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>User Name</th>
                            <th>Password</th>
                            <th>User Pin</th>
                            <th>Action</th>
                        </tr>
                      </thead>
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

    $(function () {
   
       var table = $('#data-table').DataTable({
        dom: 'fBrtip',
    dom:"<'row'<'col-sm-6 text-left'f><'col-sm-6 text-right'B>>\n\t\t\t<'row'<'col-sm-12'tr>>\n\t\t\t<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 dataTables_pager'lp>>",
        buttons: [
          {
                extend: 'print',
                exportOptions: {
                    columns: [ 0, 1, 2, 3,4,5,6 ]
                }
            },
            {
                extend: 'excelHtml5',
                exportOptions: {
                    columns: [ 0, 1, 2, 3,4,5,6 ]
                }
            },
            {
                extend: 'pdfHtml5',
                exportOptions: {
                    columns: [ 0, 1, 2, 3,4,5,6 ]
                }
            }
        ],
       processing: true,
       serverSide: true,
        responsive: true,
       ajax: "{{ url('UserLoad') }}",
       columns: [
           {data: 'name', name: 'name'},
           {data: 'email', name: 'email'},
           {data: 'phone', name: 'phone'},
           {data: 'username', name: 'username'},
           {data: 'password', name: 'password'},
           {data: 'user_pin', name: 'user_pin'},
           {data: 'action', name: 'action', orderable: false, searchable: false},
           ]
       });
   });
   
   </script>
@endsection