@extends('backend.layouts.master')

@section('title','Disbursement Status')
@section('style')
<style>
    .user_info {
        margin-bottom: 50px;
    }

    .text_aling {
        text-align: center;
        background: #FB3199;
        color:white;
        font-weight: bold;
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
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Member Profile</h5>
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
                        <div class="col-md-8 col-xs-12 col-sm-12 offset-md-2">
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
                            {{-- <form action="{{route('store')}}" method="post" onsubmit="return confirm('Are you
                            sureyou want to submit?');" name="registration"> --}}
                            <form action="{{route('store')}}" method="post">
                                @csrf
                                <div class="box-body">
                                    <div class="user_info">
                                        <div class="form-group">
                                            <input type="text" value="0309309-Sufia Akter"
                                                class="form-control text_aling" id="groupNo" name="groupNo">
                                        </div>
                                        <div class="form-row">
                                            <div class="col-md-6 mb-3">
                                                <input type="text" class="form-control text_aling"
                                                    value="Branch Code: 5594" id="proposedloan" name="proposedloan">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <input type="text" class="form-control text_aling"
                                                    id="tolerancelimit" value="Branch Name: Asulia"
                                                    name="tolerancelimit">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-3 mb-3">
                                            <input type="text" class="form-control" placeholder="date from"
                                                id="proposedloan" name="proposedloan">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <input type="text" class="form-control" placeholder="date to"
                                                id="tolerancelimit" name="tolerancelimit">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                            <input type="text" placeholder="serach" class="form-control" id="groupNo" name="groupNo">
                                        </div>                                        
                                    <div class="row">
                                        <div class="col-md-12">
                                            <table style="text-align: center;font-size:13"
                                                class="table table-bordered" id="data-table">
                                                <thead>
                                                    <tr class="brac-color-pink">
                                                        <th>Date</th>
                                                        <th>Enrollment</th>
                                                        <th>Po</th>
                                                        <th>Client Name</th>
                                                        <th>Member ID</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    
                                                    <tr>
                                                        <td>21-may-2021</td>
                                                        <td>326129</td>
                                                        <td>Nazril Islam(19734)</td>
                                                        <td>Abdul Hakim</td>
                                                        <td>563443</td>
                                                    </tr>
                                                    <tr>
                                                        <td>21-April-2021</td>
                                                        <td>326129</td>
                                                        <td>Nazril Islam(19734)</td>
                                                        <td>Abdul Hakim</td>
                                                        <td>8654</td>
                                                    </tr>
                                                    <tr>
                                                        <td>21-April-2021</td>
                                                        <td>326129</td>
                                                        <td>Nazril Islam(19734)</td>
                                                        <td>Abdul Hakim</td>
                                                        <td>46577</td>
                                                    </tr>
                                                    <tr>
                                                        <td>21-April-2021</td>
                                                        <td>326129</td>
                                                        <td>Nazril Islam(19734)</td>
                                                        <td>Abdul Hakim</td>
                                                        <td>32424</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div><!-- /.box-body -->

                               

                            </form>
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
    

</script>

@endsection
