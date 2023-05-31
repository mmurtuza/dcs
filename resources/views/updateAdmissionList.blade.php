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
                                                <th>transectionId</th>
                                                <th>Status</th>
                                                <th>ERP Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($getdata as $row) {
                                            ?>
                                                <tr>
                                                    <td><?php echo $row->ApplicantsName; ?></td>
                                                    <td><?php echo $row->entollmentid; ?></td>
                                                    <td><?php echo $row->status; ?></td>
                                                    <td><?php echo $row->ErpStatus; ?></td>
                                                    <td><a href="EditView?id=<?php echo $row->id; ?>">Edit</a></td>

                                                </tr>
                                            <?php
                                            }
                                            ?>
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

@endsection