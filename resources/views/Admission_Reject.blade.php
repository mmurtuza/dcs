@extends('backend.layouts.master')

@section('title','Admission Reject')

@section('content')

<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
  <!--begin::Subheader-->
  <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
    <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
      <!--begin::Info-->
      <div class="d-flex align-items-center flex-wrap mr-2">
        <!--begin::Page Title-->
        <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Admission Reject Page</h5>
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
        <div class="col-md-12">
          <div class="card card-custom gutter-b">
            <!--begin::Form-->
            <br /><br />
            <form action="" method="GET">
              <div class="row mx-5">
                <div class="col-sm-3">
                  <input type="text" name="branchcode" placeholder="branchcode" autocomplete="on">
                </div>
                <div class="col-sm-3">
                  <input type="text" name="orgno" placeholder="Vo Code" autocomplete="off">
                </div>
                <div class="col-sm-3">
                  <input type="text" name="assignedpo" placeholder="Assigned PO" autocomplete="off">
                </div>
                <div class="col-sm-3">
                  <div class="d-grid gap-2">
                    <input class="btn btn-primary" type="submit" value="Submit">
                  </div>
                </div>
                <br />
              </div>
            </form>
            <div class="card-body">
              <form action="AdmissionReject" method="POST">
                <div class="row">
                  <div class="col-md-12">
                    <table style="text-align: center;font-size:13" class="table table-bordered" id="data-table">
                      <thead>
                        <tr class="brac-color-pink">
                          <th>Check</th>
                          <th>Name</th>
                          <th>Vo Code</th>
                          <th>transectionId</th>
                          <th>Status</th>
                          <th>ERP Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $branchcode = '';
                        $orgno = '';
                        $assignedpo = '';
                        if (isset($_GET['branchcode'])) {
                          $branchcode = $_GET['branchcode'];
                        }

                        if (isset($_GET['orgno'])) {
                          $orgno = $_GET['orgno'];
                        }
                        if (isset($_GET['assignedpo'])) {
                          $assignedpo = $_GET['assignedpo'];
                        }
                        if ($branchcode != '' and $orgno != '' and $assignedpo != '') {
                          $getdata = DB::Table('dcs.admissions')->where('branchcode', $branchcode)->where('orgno', $orgno)->where('assignedpo', $assignedpo)->get();
                        } else if ($branchcode != '' and $orgno != '') {
                          $getdata = DB::Table('dcs.admissions')->where('branchcode', $branchcode)->where('orgno', $orgno)->get();
                        } else if ($orgno != '') {
                          $getdata = DB::Table('dcs.admissions')->where('orgno', $orgno)->get();
                        } else if ($branchcode != '') {
                          $getdata = DB::Table('dcs.admissions')->where('branchcode', $branchcode)->get();
                        } else {
                          $getdata = [];
                        }
                        if (empty($getdata)) {
                        ?>
                          <tr>
                            <td colspan="12" align="center"><?php echo "Data Not Found" ?></td>
                          </tr>
                          <?php
                        } else {
                          foreach ($getdata as $row) {
                          ?>
                            <tr>
                              <td><input type='checkbox' name='update[]' value='<?= $row->id ?>'></td>
                              <td><?php echo $row->ApplicantsName; ?></td>
                              <td><?php echo $row->orgno; ?></td>
                              <td><?php echo $row->entollmentid; ?></td>
                              <td><?php echo $row->status; ?></td>
                              <td><?php echo $row->ErpStatus; ?></td>
                            </tr>
                        <?php
                          }
                        }
                        ?>
                      </tbody>
                    </table>
                  </div>
                </div>
                <input type="submit" value="Save">
              </form>

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

@endsection