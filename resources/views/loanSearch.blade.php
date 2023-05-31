@extends('backend.layouts.master')

@section('title','Loan')
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

    /* .form_value{
        display:flex;
    } */

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
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Loan</h5>
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
                            <div class="form_value">
                            <form action="{{url('/operation/loan-search')}}" method="get">
                            @if(session('roll')==2)
                                    <div class="form-row">
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="branch">Area</label>
                                            <h6 class="mt-2">{{$branch->area_name}}</h6>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="branch">Branch</label>
                                            <select id="branch" name="branch" class="form-control">
                                                <option value="">Select</option>
                                                @foreach($search2 as $row)
                                                <option value="{{$row->branch_id}}">{{$row->branch_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="po">Branch Manager</label>
                                            <select id="po" name="po" class="form-control">
                                                <option value="">Select</option>
                                                <option value="">Selim Hossain</option>
                                                <option value="">Abdullah Akash</option>
                                            </select>
                                        </div>

                                        <div class="col-md-2 mb-3">
                                            <label for="Project" class="form-label">Date Range</label>
                                            <input type="date" value="" class="form-control" id="Project"
                                                name="dateFrom">
                                        </div>
                                        <div class="col-md-2 mb-3 mt-2">
                                            <label class="ml-2" for="level"></label>
                                            <input type="date" value="" class="form-control" id="Project"
                                                name="dateTo">
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="status">Status</label>
                                            <select id="status" name="status" class="form-control">
                                                <option value="">Select</option>
                                                <option value="Pending">Pending</option>
                                                <option value="Approved">Approved</option>
                                                <option value="Rejected">Rejected</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn-secondary">Search</button>
                                    </div>
                                    @endif
                                    @if(session('roll')==3)
                                    <div class="form-row">
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="region">Region</label>
                                            <h6 class="mt-2">{{$branch->region_name}}</h6>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="area">Area</label>
                                            <select id="area" name="area" class="form-control">
                                                <option value="">Select</option>
                                                @foreach($search2 as $row)
                                                <option value="{{$row->area_id}}">{{$row->area_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="po">Area Manager</label>
                                            <select id="po" name="po" class="form-control">
                                                <option value="">Select</option>
                                                <option value="">Selim Hossain</option>
                                                <option value="">Abdullah Akash</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="branch">Branch</label>
                                            <select id="branch" name="branch" class="form-control">
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label for="Project" class="form-label">Date Range</label>
                                            <input type="date" value="" class="form-control" id="Project"
                                                name="dateFrom">
                                        </div>
                                        <div class="col-md-2 mb-3 mt-2">
                                            <label class="ml-2" for="level"></label>
                                            <input type="date" value="" class="form-control" id="Project"
                                                name="dateTo">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="status">Status</label>
                                            <select id="status" name="status" class="form-control">
                                                <option value="">Select</option>
                                                <option value="Pending">Pending</option>
                                                <option value="Approved">Approved</option>
                                                <option value="Rejected">Rejected</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3 mt-8">
                                            <button type="submit" class="btn btn-secondary">Search</button>
                                        </div>
                                    </div>
                                    @endif
                                    @if(session('roll')==4)
                                    <div class="form-row">
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="division">Division</label>
                                            <h6 class="mt-2">{{$branch->division_name}}</h6>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="region">Region</label>
                                            <select id="region" name="region" class="form-control">
                                                <option value="">Select</option>
                                                @foreach($search2 as $row)
                                                <option value="{{$row->region_id}}">{{$row->region_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="po">Region Manager</label>
                                            <select id="po" name="po" class="form-control">
                                                <option value="">Select</option>
                                                <option value="">Selim Hossain</option>
                                                <option value="">Abdullah Akash</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="area">Area</label>
                                            <select id="area" name="area" class="form-control">
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="branch">Branch</label>
                                            <select id="branch" name="branch" class="form-control">
                                            </select>
                                        </div>
                                        
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-2 mb-3">
                                            <label for="Project" class="form-label">Date Range</label>
                                            <input type="date" value="" class="form-control" id="Project"
                                                name="dateFrom">
                                        </div>
                                        <div class="col-md-2 mb-3 mt-2">
                                            <label class="ml-2" for="level"></label>
                                            <input type="date" value="" class="form-control" id="Project"
                                                name="dateTo">
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="status">Status</label>
                                            <select id="status" name="status" class="form-control">
                                                <option value="">Select</option>
                                                <option value="Pending">Pending</option>
                                                <option value="Approved">Approved</option>
                                                <option value="Rejected">Rejected</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3 mt-8">
                                            <button type="submit" class="btn btn-secondary">Search</button>
                                        </div>
                                    </div>
                                    @endif
                                    @if(session('roll')==7)
                                    <div class="form-row">
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="division">Division</label>
                                            <select id="division" name="division" class="form-control">
                                                <option value="">Select</option>
                                                @foreach($search2 as $row)
                                                <option value="{{$row->division_id}}">{{$row->division_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="po">Division Manager</label>
                                            <select id="po" name="po" class="form-control">
                                                <option value="">Select</option>
                                                <option value="">Selim Hossain</option>
                                                <option value="">Abdullah Akash</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="region">Region</label>
                                            <select id="region" name="region" class="form-control">
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="area">Area</label>
                                            <select id="area" name="area" class="form-control">
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="branch">Branch</label>
                                            <select id="branch" name="branch" class="form-control">
                                            </select>
                                        </div>
                                        
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-2 mb-3">
                                            <label for="Project" class="form-label">Date Range</label>
                                            <input type="date" value="" class="form-control" id="Project"
                                                name="dateFrom">
                                        </div>
                                        <div class="col-md-2 mb-3 mt-2">
                                            <label class="ml-2" for="level"></label>
                                            <input type="date" value="" class="form-control" id="Project"
                                                name="dateTo">
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="ml-2" for="status">Status</label>
                                            <select id="status" name="status" class="form-control">
                                                <option value="">Select</option>
                                                <option value="Pending">Pending</option>
                                                <option value="Approved">Approved</option>
                                                <option value="Rejected">Rejected</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3 mt-8">
                                            <button type="submit" class="btn btn-secondary">Search</button>
                                        </div>
                                    </div>
                                    @endif
                                </form>
                            </div>
                            <br>
                            <table style="text-align: center;" class="table table-bordered" id="myTable">
                                <thead>
                                    <tr class="brac-color-pink">
                                        <th>Branch</th>
                                        <th>Date</th>
                                        <th>Member</th>
                                        <th>PO</th>
                                        <th>Client Name</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @if ($search->count() > 0 )
                                    @foreach($search as $data)
                                    <tr>
                                        <td>{{$data->branchcode}}</td>
                                        <td>{{date('d-m-Y',strtotime($data->time))}}</td>
                                        <td>{{$data->mem_id}}</td>
                                        @php
                                        $coname = \App\Polist::select('coname')->where(['cono' => $data->assignedpo])->first()
                                        @endphp
                                        <td>{{$coname->coname}}</td>
                                        <td>{{$data->ApplicantsName}}</td>
                                        <td></td>
                                        @if(session('roll')=='2')
                                        @if($data->reciverrole=='2' && $data->status=='Pending')
                                        <td>Pending</td>
                                        @endif
                                        @if($data->reciverrole=='2' and $data->status=='Approved')
                                        <td>Approved</td>
                                        @endif
                                        @if($data->reciverrole=='2' and $data->status=='Rejected')
                                        <td>Rejected</td>
                                        @endif
                                        @if($data->reciverrole=='3' and $data->status=='Pending')
                                        <td>Pending at RM</td>
                                        @endif
                                        @if($data->reciverrole=='3' and $data->status=='Approved')
                                        <td>Approved at RM</td>
                                        @endif
                                        @if($data->reciverrole=='3' and $data->status=='Rejected')
                                        <td>Rejected at RM</td>
                                        @endif
                                        @if($data->reciverrole=='4' and $data->status=='Pending')
                                        <td>Pending at DM</td>
                                        @endif
                                        @if($data->reciverrole=='4' and $data->status=='Approved')
                                        <td>Approved at DM</td>
                                        @endif
                                        @if($data->reciverrole=='4' and $data->status=='Rejected')
                                        <td>Rejected at DM</td>
                                        @endif
                                        @if($data->reciverrole=='1' and $data->status=='Pending')
                                        <td>Pending at BM</td>
                                            @endif
                                            @if($data->reciverrole=='1' and $data->status=='Approved')
                                        <td>Approved at BM</td>
                                        @endif
                                        @if($data->reciverrole=='1' and $data->status=='Rejected')
                                        <td>Rejected at BM</td>
                                        @endif
                                        @if($data->reciverrole=='7' and $data->status=='Pending')
                                        <td>Pending at HO</td>
                                        @endif
                                        @if($data->reciverrole=='7' and $data->status=='Approved')
                                        <td>Approved at HO</td>
                                        @endif
                                        @if($data->reciverrole=='7' and $data->status=='Rejected')
                                        <td>Rejected at HO</td>
                                        @endif
                                        @if($data->reciverrole=='0' and $data->status=='Rejected')
                                        <td>Rejected</td>
                                        @endif
                                        @if($data->reciverrole=='0')
                                        <td>Sendback to PO</td>
                                        @endif
                                        @elseif (session('roll')=='3')
                                        @if($data->reciverrole=='3' and $data->status=='Pending')
                                        <td>Pending</td>
                                        @endif
                                        @if($data->reciverrole=='3' and $data->status=='Approved')
                                        <td>Approved</td>
                                        @endif
                                        @if($data->reciverrole=='3' and $data->status=='Rejected')
                                        <td>Rejected</td>
                                        @endif
                                        @if($data->reciverrole=='2' and $data->status=='Pending')
                                        <td>Pending at AM</td>
                                        @endif
                                        @if($data->reciverrole=='2' and $data->status=='Approved')
                                        <td>Approved at AM</td>
                                        @endif
                                        @if($data->reciverrole=='2' and $data->status=='Rejected')
                                        <td>Rejected at AM</td>
                                        @endif
                                        @if($data->reciverrole=='4' and $data->status=='Pending')
                                        <td>Pending at DM</td>
                                        @endif
                                        @if($data->reciverrole=='4' and $data->status=='Approved')
                                        <td>Approved at DM</td>
                                        @endif
                                        @if($data->reciverrole=='4' and $data->status=='Rejected')
                                        <td>Rejected at DM</td>
                                        @endif
                                        @if($data->reciverrole=='1' and $data->status=='Pending')
                                        <td>Pending at BM</td>
                                        @endif
                                        @if($data->reciverrole=='1' and $data->status=='Approved')
                                        <td>Approved at BM</td>
                                        @endif
                                        @if($data->reciverrole=='1' and $data->status=='Rejected')
                                        <td>Rejected at BM</td>
                                        @endif
                                        @if($data->reciverrole=='7' and $data->status=='Pending')
                                        <td>Pending at HO</td>
                                        @endif
                                        @if($data->reciverrole=='7' and $data->status=='Approved')
                                        <td>Approved at HO</td>
                                        @endif
                                        @if($data->reciverrole=='7' and $data->status=='Rejected')
                                        <td>Rejected at HO</td>
                                        @endif
                                        @if($data->reciverrole=='0' and $data->status=='Rejected')
                                        <td>Rejected</td>
                                        @endif
                                        @if($data->reciverrole=='0')
                                        <td>Sendback to PO</td>
                                        @endif
                                        @elseif (session('roll')=='4')
                                        @if($data->reciverrole=='4' and $data->status=='Pending')
                                        <td>Pending</td>
                                        @endif
                                        @if($data->reciverrole=='4' and $data->status=='Approved')
                                        <td>Approved</td>
                                        @endif
                                        @if($data->reciverrole=='4' and $data->status=='Rejected')
                                        <td>Rejected</td>
                                        @endif
                                        @if($data->reciverrole=='3' and $data->status=='Pending')
                                        <td>Pending at RM</td>
                                        @endif
                                        @if($data->reciverrole=='3' and $data->status=='Approved')
                                        <td>Approved at RM</td>
                                        @endif
                                        @if($data->reciverrole=='3' and $data->status=='Rejected')
                                        <td>Rejected at RM</td>
                                        @endif
                                        @if($data->reciverrole=='2' and $data->status=='Pending')
                                        <td>Pending at AM</td>
                                        @endif
                                        @if($data->reciverrole=='2' and $data->status=='Approved')
                                        <td>Approved at AM</td>
                                        @endif
                                        @if($data->reciverrole=='2' and $data->status=='Rejected')
                                        <td>Rejected at AM</td>
                                        @endif
                                        @if($data->reciverrole=='1' and $data->status=='Pending')
                                        <td>Pending at BM</td>
                                        @endif
                                        @if($data->reciverrole=='1' and $data->status=='Approved')
                                        <td>Approved at BM</td>
                                        @endif
                                        @if($data->reciverrole=='1' and $data->status=='Rejected')
                                        <td>Rejected at BM</td>
                                        @endif
                                        @if($data->reciverrole=='7' and $data->status=='Pending')
                                        <td>Pending at HO</td>
                                        @endif
                                        @if($data->reciverrole=='7' and $data->status=='Approved')
                                        <td>Approved at HO</td>
                                        @endif
                                        @if($data->reciverrole=='7' and $data->status=='Rejected')
                                        <td>Rejected at HO</td>
                                        @endif
                                        @if($data->reciverrole=='0' and $data->status=='Rejected')
                                        <td>Rejected</td>
                                        @endif
                                        @if($data->reciverrole=='0')
                                        <td>Sendback to PO</td>
                                        @endif
                                        @elseif (session('roll')=='1')
                                        @if($data->reciverrole=='1' and $data->status=='Pending')
                                        <td>Pending</td>
                                        @endif
                                        @if($data->reciverrole=='1' and $data->status=='Approved')
                                        <td>Approved</td>
                                        @endif
                                        @if($data->reciverrole=='1' and $data->status=='Rejected')
                                        <td>Rejected</td>
                                        @endif
                                        @if($data->reciverrole=='3' and $data->status=='Pending')
                                        <td>Pending at RM</td>
                                        @endif
                                        @if($data->reciverrole=='3' and $data->status=='Approved')
                                        <td>Approved at RM</td>
                                        @endif
                                        @if($data->reciverrole=='3' and $data->status=='Rejected')
                                        <td>Rejected at RM</td>
                                        @endif
                                        @if($data->reciverrole=='4' and $data->status=='Pending')
                                        <td>Pending at DM</td>
                                        @endif
                                        @if($data->reciverrole=='4' and $data->status=='Approved')
                                        <td>Approved at DM</td>
                                        @endif
                                        @if($data->reciverrole=='4' and $data->status=='Rejected')
                                        <td>Rejected at DM</td>
                                        @endif
                                        @if($data->reciverrole=='2' and $data->status=='Pending')
                                        <td>Pending at AM</td>
                                        @endif
                                        @if($data->reciverrole=='2' and $data->status=='Approved')
                                        <td>Approved at AM</td>
                                        @endif
                                        @if($data->reciverrole=='2' and $data->status=='Rejected')
                                        <td>Rejected at AM</td>
                                        @endif
                                        @if($data->reciverrole=='7' and $data->status=='Pending')
                                        <td>Pending at HO</td>
                                        @endif
                                        @if($data->reciverrole=='7' and $data->status=='Approved')
                                        <td>Approved at HO</td>
                                        @endif
                                        @if($data->reciverrole=='7' and $data->status=='Rejected')
                                        <td>Rejected at HO</td>
                                        @endif
                                        @if($data->reciverrole=='0' and $data->status=='Rejected')
                                        <td>Rejected</td>
                                        @endif
                                        @if($data->reciverrole=='0')
                                        <td>Sendback to PO</td>
                                        @endif
                                        @elseif(session('roll')=='7')
                                        @if($data->reciverrole=='7' and $data->status=='Pending')
                                        <td>Pending</td>
                                        @endif
                                        @if($data->reciverrole=='7' and $data->status=='Approved')
                                        <td>Approved</td>
                                        @endif
                                        @if($data->reciverrole=='7' and $data->status=='Rejected')
                                        <td>Rejected</td>
                                        @endif
                                        @if($data->reciverrole=='3' and $data->status=='Pending')
                                        <td>Pending at RM</td>
                                        @endif
                                        @if($data->reciverrole=='3' and $data->status=='Approved')
                                        <td>Approved at RM</td>
                                        @endif
                                        @if($data->reciverrole=='3' and $data->status=='Rejected')
                                        <td>Rejected at RM</td>
                                        @endif
                                        @if($data->reciverrole=='4' and $data->status=='Pending')
                                        <td>Pending at DM</td>
                                        @endif
                                        @if($data->reciverrole=='4' and $data->status=='Approved')
                                        <td>Approved at DM</td>
                                        @endif
                                        @if($data->reciverrole=='4' and $data->status=='Rejected')
                                        <td>Rejected at DM</td>
                                        @endif
                                        @if($data->reciverrole=='1' and $data->status=='Pending')
                                        <td>Pending at BM</td>
                                        @endif
                                        @if($data->reciverrole=='1' and $data->status=='Approved')
                                        <td>Approved at BM</td>
                                        @endif
                                        @if($data->reciverrole=='1' and $data->status=='Rejected')
                                        <td>Rejected at BM</td>
                                        @endif
                                        @if($data->reciverrole=='2' and $data->status=='Pending')
                                        <td>Pending at AM</td>
                                        @endif
                                        @if($data->reciverrole=='2' and $data->status=='Approved')
                                        <td>Approved at AM</td>
                                        @endif
                                        @if($data->reciverrole=='2' and $data->status=='Rejected')
                                        <td>Rejected at AM</td>
                                        @endif
                                        @if($data->reciverrole=='0' and $data->status=='Rejected')
                                        <td>Rejected</td>
                                        @endif
                                        @if($data->reciverrole=='0')
                                        <td>Sendback to PO</td>
                                        @endif
                                        @else
                                        <td></td>
                                        @endif
                                        <td><a class="btn btn-warning"
                                                href="{{URL::to('/operation/loan-approval',$data->id)}}">Details</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @else
                                    <tr>
                                    <td colspan="7"> <p style="text-align:center;">data not found</p></td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                            <div style="float:right;">
                            {{$search->links()}}
                            </div>
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
        $(document).ready( function () {
        $('#myTable').DataTable();
        });

        $('#division').on('change', function () {
            if ($(this).val() != '') {
                var division = $(this).val();
                $.ajax({
                    url: "{{url('operation/admission-division')}}",
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        division: division,
                    },
                    success: function (data) {
                        $("#region").empty();
                        $( `<option value="">Select</option>` ).appendTo( "#region" );
                        $.each(data, function (key, value) {
                            $("#region").append(` <option value="` + value
                                .region_id + `">` + value.region_name +
                                `</option>`)

                        });
                    }
                });
            }
        });

        $('#region').on('change', function () {
            if ($(this).val() != '') {
                var region = $(this).val();
                $.ajax({
                    url: "{{url('operation/admission-region')}}",
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        region: region,
                    },
                    success: function (data) {
                        console.log(data);
                        $("#area").empty();
                        $( `<option value="">Select</option>` ).appendTo( "#area" );
                        $.each(data, function (key, value) {
                            $("#area").append(`<option value="` + value
                                .area_id + `">` + value.area_name +
                                `</option>`)
                        });
                    }
                });
            }
        });

        $('#area').on('change', function () {
            if ($(this).val() != '') {
                var area = $(this).val();

                $.ajax({
                    url: "{{url('operation/admission-area')}}",
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        area: area,
                    },
                    success: function (data) {
                        $("#branch").empty();
                        $( `<option value="">Select</option>` ).appendTo( "#branch" );
                        $.each(data, function (key, value) {
                            $("#branch").append(` <option value="` + value
                                .branch_id + `">` + value.branch_name +
                                `</option>`)

                        });
                    }
                });
            }
        });

        $('#branch').on('change', function () {
        if ($(this).val() != '') {
            var branch = $(this).val();

            $.ajax({
                url: "{{url('operation/admission-branch')}}",
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    branch: branch,
                },
                success: function (data) {
                    $("#po").empty();
                    $( `<option value="">Select</option>` ).appendTo( "#po" );
                    $.each(data, function (key, value) {
                        $("#po").append(` <option value="` + value
                            .cono + `">` + value.coname+
                            `</option>`)

                    });
                }
            });
        }
    });
    });

</script>

@endsection
