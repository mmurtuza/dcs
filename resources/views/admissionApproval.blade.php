@extends('backend.layouts.master')

@section('title','admission approval')
@section('style')
<style>
    .header-section {
        background-color: #f3eded;
    }

    .img-circle {
        border-radius: 50%;
        height: 150px;
        width: auto;
    }

    .text_align {
        text-align: center;
    }

    /* input {
        text-align: center;
    } */

    .guarantor_img {
        height: 150px;
        width: 150px;
        border-radius: 50%;
    }

    .nid_img {
        width: 230px;
        height: 140px;
    }

    .font_red {
        color: red;
    }

    .member_info table {
        background: #FB3199;
        color: #FFFFFF;
    }

    .white {
        color: white;
    }

    .bgColor {
        background: #F3F6F9;
        color: black;
    }

    .nav_bar {
        background: #FB3199;
    }

    .nav_bar .nav .nav-item .active {
        color: #fff;
        background-color: DarkOrange;
    }

    .nav_bar .nav .nav-item .nav-link {
        color: #fff;
        /*background-color:*/
    }

    .modal {
        display: none;
        /* Hidden by default */
        position: fixed;
        /* Stay in place */
        z-index: 1;
        /* Sit on top */
        padding-top: 150px;
        margin-left: 120px;
        /* Location of the box */
        left: 0;
        top: 0;
        width: 100%;
        /* Full width */
        height: 100%;
        /* Full height */
        overflow: auto;
        /* Enable scroll if needed */
        background-color: rgb(0, 0, 0);
        /* Fallback color */
        background-color: rgba(0, 0, 0, 0.4);
        /* Black w/ opacity */
    }

    /* Modal Content */
    .modal-content {
        background-color: #fefefe;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 50%;
        display: flex;
    }

    /* The Close Button */
    .close {
        color: #aaaaaa;
        font-size: 28px;
        float: right;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
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
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">@lang('admission.approval_header')</h5>
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
                    @php
                    $db = config('database.db');
                    $poName=DB::table($db.'.polist')->where('cono',$data->assignedpo)->first();
                    @endphp

                    <div class="member_info">
                        <div class="title text_align">
                            @if(session('project'))
                            <?php
                            if (session('locale') == 'bn') {
                                $getproject = DB::table('dcs.projects')->where('projectTitle', session('project'))->get();
                                if ($getproject->isEmpty()) {
                                    $projectsName = '';
                                } else {
                                    $projectsName = $getproject[0]->bangla;
                                }
                            } else {
                                $projectsName = session('project');
                            }
                            ?>
                            @endif
                            <h3>@lang('loanApproval.header17')-{{$projectsName}}</h3>
                            <h6>@lang('admissionApproval.header8')</h6>
                        </div>


                        <div class="row justify-content-around">
                            <div class="col-md-8">
                                <div class="card card-custom header-section">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <p>@lang('loanApproval.header1') :</p>
                                                @if($data->ApplicantSinglePic==null)
                                                <img src="{{ asset('images/Sample_User_Icon.png') }}" class="img-circle" alt="Applicant image">
                                                @else
                                                <img src="{{ $data->ApplicantSinglePic }}" class="img-circle" alt="Applicant --image">
                                                @endif
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p>@lang('loanApproval.header8') : <a target="_blank" href="{{ $data->FrontSideOfIdImg }}">image</a>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p>@lang('loanApproval.header9') : <a target="_blank" href="{{ $data->SpouseNidFront }}">image</a></p>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p>@lang('loanApproval.header10') : <a target="_blank" href="{{ $data->BackSideOfIdimg }}">image</a>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p>@lang('loanApproval.header11') : <a target="_blank" href="{{ $data->SpouseNidBack }}">image</a></p>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <p>
                                                        <h4>@lang('admissionApproval.header1')</h4>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">

                                                        @if(session('projectcode')=='060' and
                                                        session('role_designation') == 'AM')
                                                        <select class="form-control" name="behavior" id="behavior">
                                                            <option value="0">High</option>
                                                            <option value="1">Medium</option>
                                                            <option value="2">Low</option>
                                                        </select>
                                                        @else
                                                        <p>@lang('admissionApproval.header2'):
                                                            @if($data->bm_behavior == '0')
                                                            {{"High"}}
                                                            @elseif($data->bm_behavior == '1')
                                                            {{"Medium"}}
                                                            @elseif($data->bm_behavior == '2')
                                                            {{"Low"}}
                                                            @endif
                                                        </p>
                                                        @endif



                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        @if(session('projectcode')=='060' and
                                                        session('role_designation') == 'AM')
                                                        <div class="star_rating">
                                                            <input type="hidden" value="{{$data->bm_financial_status}}" id="bm_financial_status">
                                                            <i class="fa fa-star" aria-hidden="true" id="st1"></i>
                                                            <i class="fa fa-star" aria-hidden="true" id="st2"></i>
                                                            <i class="fa fa-star" aria-hidden="true" id="st3"></i>
                                                            <i class="fa fa-star" aria-hidden="true" id="st4"></i>
                                                            <i class="fa fa-star" aria-hidden="true" id="st5"></i>
                                                            <input type="hidden" value="0" name="all_financial_status" class="all_financial_status">
                                                        </div>
                                                        @else
                                                        <div class="star_rating">
                                                            @lang('admissionApproval.header3') :
                                                            <input type="hidden" value="{{$data->bm_financial_status}}" id="bm_financial_status">
                                                            <i class="fa fa-star" aria-hidden="true" id="st1"></i>
                                                            <i class="fa fa-star" aria-hidden="true" id="st2"></i>
                                                            <i class="fa fa-star" aria-hidden="true" id="st3"></i>
                                                            <i class="fa fa-star" aria-hidden="true" id="st4"></i>
                                                            <i class="fa fa-star" aria-hidden="true" id="st5"></i>
                                                        </div>
                                                        @endif

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card card-custom header-section">
                                    <div class="card-body">
                                        <p>@lang('loanApproval.header12') : {{$data->branchcode}}</p>
                                        <p>@lang('loanApproval.header13') : {{$data->orgno}}</p>
                                        <p>@lang('loanApproval.header14') : {{$poName->coname}}</p>
                                        <p>@lang('loanApproval.header15') : {{$data->assignedpo}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card card-custom header-section">
                                    <div class="card-body">
                                        <p>
                                        <h4>@lang('admissionApproval.header4')</h4>
                                        </p>
                                        <p>@lang('admissionApproval.header5') : {{ $data->IsRefferal }}</p>
                                        <p>@lang('admissionApproval.header6') : {{ $data->RefferedById }}
                                            {{ $data->ReffererName }}
                                        </p>
                                        <p>@lang('admissionApproval.header7') : {{ $data->ReffererPhone }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                    </div>
                    <form action="{{route('admission_approve')}}" method="post">
                        @csrf
                        <div class="box-body">
                            <div class="nav_bar">
                                <ul class="nav  nav-pills nav-fill">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="client1" href="#clientInfo">@lang('admission.approval_tab1')</a>
                                    </li>
                                    @if($data2!=null)
                                    <li class="nav-item">
                                        <a class="nav-link" id="more1" href="#moreInfo">@lang('admission.approval_tab2')</a>
                                    </li>
                                    @endif

                                </ul>
                            </div>
                            @php
                            $db = config('database.db');
                            @endphp
                            <div class="tab-content">
                                <div class="tab-pane active" id="clientInfo">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th colspan="6" class="bgColor">@lang('admissionApproval.title1')</th>
                                        </tr>
                                        <tr>
                                            <td rowspan="32"></td>
                                            <td>@lang('admissionApproval.label1')</td>
                                            <td rowspan="32"></td>
                                            <td colspan="2">
                                                {{$data->ApplicantsName}}
                                            </td>
                                            <td rowspan="32"></td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label32')</td>
                                            <td colspan="2">
                                                @if($data->MemberCateogryId!= null)
                                                @php
                                                $membercategory=DB::table($db.'.product_project_member_category')->select('membercategory')->where('membercategoryid',$data->MemberCateogryId)->first();
                                                @endphp
                                                {{$membercategory->membercategory}}
                                                @endif
                                                <!-- {{$data->MainIdTypeId}} -->
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label2')</td>
                                            <td colspan="2">
                                                @if($data->MainIdTypeId!= null)
                                                @php
                                                $idType=DB::table($db.'.payload_data')->select('data_name')->where('data_type','cardTypeId')->where('data_id',$data->MainIdTypeId)->first();
                                                @endphp
                                                {{$idType->data_name}}
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label33')</td>
                                            <td colspan="2">
                                                {{$data->IdNo}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label3')</td>
                                            <td colspan="2">
                                                {{$data->DOB}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label4')</td>
                                            <td colspan="2">
                                                {{$data->MotherName}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label5')</td>
                                            <td colspan="2">
                                                {{$data->FatherName}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label6')</td>
                                            <td colspan="2">
                                                @if($data->EducationId!= null)
                                                @php
                                                $education=DB::table($db.'.payload_data')->select('data_name')->where('data_type','educationId')->where('data_id',$data->EducationId)->first();
                                                @endphp
                                                {{$education->data_name}}
                                                @endif
                                                <!-- {{$data->EducationId}} -->
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label7')</td>
                                            <td colspan="2">
                                                @if($data->Occupation!= null)
                                                @php
                                                $occupation=DB::table($db.'.payload_data')->select('data_name')->where('data_type','occupationId')->where('data_id',$data->Occupation)->first();
                                                @endphp
                                                {{$occupation->data_name}}
                                                @endif
                                                <!-- {{$data->Occupation}} -->
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label8')</td>
                                            <td colspan="2">
                                                {{$data->Phone}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label9') </td>
                                            <td colspan="2">
                                                {{$data->PresentAddress}}
                                                <!-- {{$data->presentUpazilaId}}, {{$data->PresentAddress}} -->
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label10')</td>
                                            <td colspan="2">
                                                {{$data->PermanentAddress }}
                                                <!-- {{$data->parmanentUpazilaId}}, {{$data->PermanentAddress }} -->
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label11')</td>
                                            <td colspan="2">
                                                @if($data->MaritalStatusId!= null)
                                                @php
                                                $maritStatus=DB::table($db.'.payload_data')->select('data_name')->where('data_type','maritalStatusId')->where('data_id',$data->MaritalStatusId)->first();
                                                @endphp
                                                {{$maritStatus->data_name}}
                                                @endif
                                                <!-- {{$data->MaritalStatusId}} -->
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label12')</td>
                                            <td colspan="2">
                                                {{$data->SpouseName}}
                                            </td>
                                        </tr>
                                        <!-- <tr>
                                                <td>Spouse NID</td>
                                                <td colspan="2">
                                                    {{$data->SpouseNidOrBid}}
                                                </td>
                                            </tr> -->
                                        <tr>
                                            <td>@lang('admissionApproval.label13')</td>
                                            <td colspan="2">
                                                @if($data->SpuseOccupationId!= null)
                                                @php
                                                $spuseOccupation=DB::table($db.'.payload_data')->select('data_name')->where('data_type','occupationId')->where('data_id',$data->SpuseOccupationId)->first();
                                                @endphp
                                                {{$spuseOccupation->data_name}}
                                                @endif
                                                <!-- {{$data->SpuseOccupationId}} -->
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>@lang('admissionApproval.label14')</td>
                                            <td colspan="2">
                                                {{$data->FamilyMemberNo}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label15')</td>
                                            <td colspan="2">
                                                {{$data->NoOfChildren}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label16')</td>
                                            <td colspan="2">
                                                {{$data->NomineeName}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label34')</td>
                                            <td colspan="2">
                                                {{$data->NomineePhoneNumber}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label35')</td>
                                            <td colspan="2">
                                                @if($data->NomineeNidType!= null)
                                                @php
                                                $NomineeNidType=DB::table($db.'.payload_data')->select('data_name')->where('data_type','cardTypeId')->where('data_id',$data->NomineeNidType)->first();
                                                @endphp
                                                {{$NomineeNidType->data_name}}
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label36')</td>
                                            <td colspan="2">
                                                {{$data->NomineeNidNo}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label17')</td>
                                            <td colspan="2">
                                                {{$data->NomineeDOB}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label18')</td>
                                            <td colspan="2">
                                                @if($data->RelationshipId!= null)
                                                @php
                                                $relation=DB::table($db.'.payload_data')->select('data_name')->where('data_type','relationshipId')->where('data_id',$data->RelationshipId)->first();
                                                @endphp
                                                {{$relation->data_name}}
                                                @endif
                                                <!-- {{$data->RelationshipId}} -->
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label19')</td>
                                            <td colspan="2">
                                                @if($data->IsBkash== '1')
                                                {{"yes"}}
                                                @else
                                                {{"no"}}
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label20')</td>
                                            <td colspan="2">
                                                {{$data->WalletNo}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label21')</td>
                                            <td colspan="2">
                                                @if($data->WalletOwner!= null)
                                                @php
                                                $WalletOwner=DB::table($db.'.payload_data')->select('data_name')->where('data_type','primaryEarner')->where('data_id',$data->WalletOwner)->first();
                                                @endphp
                                                {{$WalletOwner->data_name}}
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label22')</td>
                                            <td colspan="2">
                                                @if($data->ApplicantCpmbinedImg)
                                                <img class="guarantor_img " src="{{$data->ApplicantCpmbinedImg}}" alt="Combine Picture">
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label23')</td>
                                            <td colspan="2">
                                                @if($data->NomineeNidFront)
                                                <img class="nid_img  " src="{{$data->NomineeNidFront}}" alt="Nominee's Images">
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>@lang('admissionApproval.label24')</td>
                                            <td colspan="2">
                                                @if($data->NomineeNidBack)
                                                <img class="nid_img  " src="{{$data->NomineeNidBack}}" alt="Nominee's Images">
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                    <div class="box-footer">
                                        <div class="row">
                                            @if($data2!=null)
                                            <div class="col-md-4">

                                            </div>
                                            <div class="col-md-4">

                                            </div>
                                            <div class="col-md-4">
                                                <a href="#moreInfo" id="more2" data-toggle="tab" class="btn btn-secondary float-right">@lang('actionBtn.next')</a>
                                            </div>
                                            @else

                                            <input type="hidden" value="{{$data->id}}" name="id">
                                            <input type="hidden" value="Approve" name="action">
                                            @php
                                            $authData =
                                            DB::table($db.'.auths')->where('roleId',session('roll'))->where('processId','3')->where('projectcode',session('projectcode'))->first();
                                            $authorization = $authData->isAuthorized;
                                            @endphp
                                            @if($authorization == true and $data->status=='1' and
                                            $data->reciverrole==session('roll'))
                                            <div class="col-md-4">
                                                <a class="btn btn-danger btn-block" id="reject" href="#">@lang('actionBtn.rejectbtn')</a>
                                            </div>
                                            <div class="col-md-4">
                                                <a class="btn btn-primary btn-block" id="sendback" href="#">@lang('actionBtn.sendBack')</a>
                                            </div>
                                            <div class="col-md-4">
                                                <button type="submit" id="approve" class="btn btn-success btn-block">@lang('actionBtn.approve')</button>
                                            </div>
                                            @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if($data2!=null)
                                <div class="tab-pane" id="moreInfo">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th colspan="6" class="bgColor">@lang('admissionApproval.title2')</th>
                                        </tr>
                                        <tr class="brac-color-pink">
                                            <th>Field Name</th>
                                            <th>Field Value</th>
                                        </tr>
                                        @foreach($data2 as $row)
                                        <tr>
                                            <td>{{$row['fieldName']}}</td>
                                            <td>{{$row['fieldValue']}}</td>
                                        </tr>
                                        @endforeach

                                    </table>
                                    <input type="hidden" value="{{$data->id}}" name="id">
                                    <input type="hidden" value="Approve" name="action">
                                    @php
                                    $authData =
                                    DB::table($db.'.auths')->where('roleId',session('roll'))->where('processId','3')->where('projectcode',session('projectcode'))->first();
                                    $authorization = $authData->isAuthorized;
                                    @endphp
                                    @if($authorization == true and $data->status=='1' and
                                    $data->reciverrole==session('roll'))
                                    <div class="box-footer">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <a class="btn btn-danger btn-block" id="reject" href="#">Reject</a>
                                            </div>
                                            <div class="col-md-4">
                                                <a class="btn btn-primary btn-block" id="sendback" href="#">Send
                                                    Back</a>
                                            </div>
                                            <div class="col-md-4">
                                                <button type="submit" id="approve" class="btn btn-success btn-block">Approve</button>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endif

                                <div class="tab-pane" id="acceptiblity">
                                    <table class="table table-bordered">
                                        <tr class="font_red">
                                            <th colspan="6">Social Acceptibility (Only For Admission)</th>
                                        </tr>
                                        <tr>
                                            <td rowspan="3"></td>
                                            <td>Behaviour/ Social Acceptance</td>
                                            <td rowspan="3"></td>
                                            @if(session('projectcode')!='060')
                                            <td class="" colspan="2">
                                                @if($data->bm_behavior == '0')
                                                {{"High"}}
                                                @elseif($data->bm_behavior == '1')
                                                {{"Medium"}}
                                                @elseif($data->bm_behavior == '2')
                                                {{"Low"}}
                                                @endif
                                            </td>
                                            @endif
                                            @if(session('projectcode')=='060' and
                                            session('role_designation') == 'AM')
                                            <td class="" colspan="2">
                                                <select class="form-control" name="behavior" id="behavior">
                                                    <option value="0">High</option>
                                                    <option value="1">Medium</option>
                                                    <option value="2">Low</option>
                                                </select>
                                            </td>
                                            @endif

                                            <td rowspan="3"></td>
                                        </tr>

                                        <tr>
                                            <td>Financial Status</td>
                                            @if(session('projectcode')=='060' and
                                            session('role_designation') == 'AM')
                                            <td class="" colspan="2">
                                                <div class="star_rating">
                                                    <input type="hidden" value="{{$data->bm_financial_status}}" id="bm_financial_status">
                                                    <i class="fa fa-star" aria-hidden="true" id="st1"></i>
                                                    <i class="fa fa-star" aria-hidden="true" id="st2"></i>
                                                    <i class="fa fa-star" aria-hidden="true" id="st3"></i>
                                                    <i class="fa fa-star" aria-hidden="true" id="st4"></i>
                                                    <i class="fa fa-star" aria-hidden="true" id="st5"></i>
                                                    <input type="hidden" value="0" name="all_financial_status" class="all_financial_status">
                                                </div>
                                            </td>
                                            @endif
                                            @if(session('projectcode')!='060')
                                            <td colspan="2" class="">
                                                {{$data->bm_financial_status}}
                                                <!-- <i class="fas fa-star" style="color:hotpink;"></i> -->
                                            </td>
                                            @endif
                                        </tr>
                                        @if(session('projectcode')!='060' and session('role_designation') ==
                                        'AM')
                                        <tr>
                                            <td>Picture of Client House</td>
                                            <td colspan="2" class="">
                                                @if($data->bm_client_house_image)
                                                <img class="nid_img" src="{{$data->bm_client_house_image}}" alt="client house">
                                                @endif
                                            </td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div><!-- /.box-body -->
                    </form>
                    <!--end: Datatable-->
                </div>
            </div>
            <!--end::Card-->
        </div>
        <!--end::Container-->
    </div>
</div>

<!-- The Modal -->
<div id="reject_modal" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
        <div>
            <span class="close">&times;</span>
        </div>
        <div>
            <form id="action_btn" action="{{ route('admission_action') }}" method="post">
                @csrf
                <input type="hidden" value="{{$data->id}}" name="id">
                <div id="action"></div>
                <div class="box-body">
                    <h5>Comment:</h5>
                    <div class="form-group">
                        <textarea name="comment" id="comment" class="form-control" cols="30" rows="5"></textarea>
                    </div>
                    <div>
                        <input type="hidden" value="" name="all_behavior" id="all_behavior">
                        <input type="hidden" value="0" name="all_financial_status" class="all_financial_status">
                    </div>
                </div><!-- /.box-body -->
                <div class="form-footer">
                    <div class="row">
                        <div class="col-md-4">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-secondary btn-block">Submit</button>
                        </div>
                        <div class="col-md-4">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')

<script>
    $(document).ready(function() {
        $('#more2').on('click', function() {
            $("li .active").removeClass("active");
            $('#more1').addClass('active');
        });

        $('#social2').on('click', function() {
            $("li .active").removeClass("active");
            $('#social1').addClass('active');
        });

        // star_rating js
        // $("#st1").click(function () {
        //     $(".fa-star").css("color", "black");
        //     $("#st1").css("color", "#FB3199");
        //     $(".all_financial_status").val("1");

        // });
        // $("#st2").click(function () {
        //     $(".fa-star").css("color", "black");
        //     $("#st1, #st2").css("color", " #FB3199");
        //     $(".all_financial_status").val('2');

        // });
        // $("#st3").click(function () {
        //     $(".fa-star").css("color", "black")
        //     $("#st1, #st2, #st3").css("color", "#FB3199");
        //     $(".all_financial_status").val('3');

        // });
        // $("#st4").click(function () {
        //     $(".fa-star").css("color", "black");
        //     $("#st1, #st2, #st3, #st4").css("color", "#FB3199");
        //     $(".all_financial_status").val('4');

        // });
        // $("#st5").click(function () {
        //     $(".fa-star").css("color", "black");
        //     $("#st1, #st2, #st3, #st4, #st5").css("color", "#FB3199");
        //     $(".all_financial_status").val('5');

        // });
        var rating_value = $("#bm_financial_status").val();
        if (rating_value == '1') {
            $(".fa-star").css("color", "black");
            $("#st1").css("color", "#FB3199");
            $(".all_financial_status").val("1");
        } else if (rating_value == '2') {
            $(".fa-star").css("color", "black");
            $("#st1, #st2").css("color", " #FB3199");
            $(".all_financial_status").val('2');
        } else if (rating_value == '3') {
            $(".fa-star").css("color", "black")
            $("#st1, #st2, #st3").css("color", "#FB3199");
            $(".all_financial_status").val('3');
        } else if (rating_value == '4') {
            $(".fa-star").css("color", "black");
            $("#st1, #st2, #st3, #st4").css("color", "#FB3199");
            $(".all_financial_status").val('4');
        } else if (rating_value == '5') {
            $(".fa-star").css("color", "black");
            $("#st1, #st2, #st3, #st4, #st5").css("color", "#FB3199");
            $(".all_financial_status").val('5');
        }
        //   else{
        //     $(".all_financial_status").val("0");
        //   }

        $('#reject').on('click', function() {
            document.querySelector('#reject_modal').style.display = 'block';
            $("#action").append(`<input type="hidden" value="Reject" name="action">`);
            $("#action").append(`<input type="hidden" value="Sendback" name="action">`);
            let behavior = $('#behavior').val();
            $("#all_behavior").val(behavior);
        })

        $('#sendback').on('click', function() {
            document.querySelector('#reject_modal').style.display = 'block';
            $("#action").append(`<input type="hidden" value="Sendback" name="action">`);
            let behavior = $('#behavior').val();
            $("#all_behavior").val(behavior);
        })

        $('.close').on('click', function() {
            document.querySelector('#reject_modal').style.display = 'none';
        })

    });
</script>

@endsection