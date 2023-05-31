@extends('backend.layouts.master')

@section('title','Loan')
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
                            </div>
                            <br>
                            <table style="text-align: center;" class="table table-bordered" id="myTable">
                                <thead>
                                    <tr class="brac-color-pink">
                                        <th>@lang('loan.table_header1')</th>
                                        <th>@lang('loan.table_header2')</th>
                                        <th>@lang('loan.table_header3')</th>
                                        <th>@lang('loan.table_header4')</th>
                                        <th>@lang('loan.table_header5')</th>
                                        <th>@lang('loan.table_header6')</th>
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
    $(document).ready(function () {
        var table = $('#myTable').DataTable({
        processing: true,
        serverSide: true,
        ajax:{
            url:"{{url('report/postedLoanData')}}"
        },
        columns: [
            {data: 'branchcode', name: 'branchcode'},
            {data: 'applicationdate', name: 'applicationdate'},
            {data: 'memberid', name: 'memberid'},
            {data: 'assignedpopin', name: 'assignedpopin'},
            {data: 'nameen', name: 'nameen'},
            {data: 'approvedloanamount', name: 'approvedloanamount'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    });
</script>
@endsection
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
<script src="cdn.datatables.net/1.11.1/js/jquery.dataTables.min.js"></script>
