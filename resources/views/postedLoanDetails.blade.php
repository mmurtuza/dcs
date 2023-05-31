@extends('backend.layouts.master')

@section('title','loan approval')
@section('style')
<style>
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

    .member_info {
        text-align: center;
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

        color: red;
        background-color: DarkOrange;
    }

    .nav_bar .nav .nav-item .nav-link {
        color: #fff;
        background-color:
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
                <h5>
                    <!--begin::Page Title-->@lang('loan.approval_header')</h5>
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
                            @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <ul style="margin-bottom: 0rem;">
                                    @foreach ($errors as $error)
                                    <li>{{$error['message'] }}</li>
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
                            <!-- {{-- <form action="{{route('store')}}" method="post" onsubmit="return confirm('Are you
                            sureyou want to submit?')" name="registration"> --}}
                            <form action="{{route('loan_approve')}}" method="post">
                                @csrf -->
                            <div class="box-body">
                                <div class="nav_bar">
                                    <ul class="nav  nav-pills nav-fill">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="details1" data-toggle="tab"
                                                href="#loanDetails">@lang('loan.approval_tab1')</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="client1" data-toggle="tab"
                                                href="#clientInfo">@lang('loan.approval_tab6')</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="tab-content">
                                    <div class="tab-pane active" id="loanDetails">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th colspan="6" class="bgColor">Loan Details</th>
                                            </tr>
                                            <tr class="text_align">
                                                <td rowspan="5"></td>
                                                <td>Approved Loan Amount</td>
                                                <td rowspan="5"></td>
                                                <td colspan="2">{{$data->approvedloanamount}}</td>
                                                <td rowspan="5"></td>
                                            </tr>
                                            <tr class="text_align">
                                                <td>Loan Duration (Months)</td>
                                                <td colspan="2">{{$data->approveddurationinmonths}}</td>
                                            </tr>
                                            <tr class="text_align">
                                                <td>Investment Sector</td>
                                                <td colspan="2">{{$data->sectorid}}</td>
                                            </tr>
                                            <tr class="text_align">
                                                <td>Loan Product</td>
                                                <td colspan="2">{{$data->loanproductid}}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    @php
                                        $admissionData = DB::table('dcs.posted_admission')->where('memberid', $data->memberid)->first()
                                    @endphp
                                    <div class="tab-pane" id="clientInfo">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th colspan="6" class="bgColor">Client Information</th>
                                            </tr>
                                            <tr>
                                                <td rowspan="32"></td>
                                                <td>Applicant's Name</td>
                                                <td rowspan="32"></td>
                                                <td colspan="2">
                                                    {{$admissionData->nameen}}
                                                </td>
                                                <td rowspan="32"></td>
                                            </tr>
                                            <tr>
                                                <td>ID Type </td>
                                                <td colspan="2">
                                                    @if($admissionData->idcardcardtypeid!= null)
                                                    @php
                                                    $idType=DB::table('dcs.payload_data')->select('data_name')->where('data_type','cardTypeId')->where('data_id',$admissionData->idcardcardtypeid)->first();
                                                    @endphp
                                                    {{$idType->data_name}}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>ID NO.</td>
                                                <td colspan="2">
                                                    {{$admissionData->idcardidcardno}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Date of Birth</td>
                                                <td colspan="2">
                                                    {{$admissionData->dateofbirth}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Gender</td>
                                                <td colspan="2">
                                                    @if($admissionData->genderid!= null)
                                                    @php
                                                    $gender=DB::table('dcs.payload_data')->select('data_name')->where('data_type','genderId')->where('data_id',$admissionData->genderid)->first();
                                                    @endphp
                                                    {{$gender->data_name}}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Mother's Name</td>
                                                <td colspan="2">
                                                    {{$admissionData->mothernameen}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Father's Name</td>
                                                <td colspan="2">
                                                    {{$admissionData->fathernameen}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Educaiton</td>
                                                <td colspan="2">
                                                    @if($admissionData->educationid!= null)
                                                    @php
                                                    $education=DB::table('dcs.payload_data')->select('data_name')->where('data_type','educationId')->where('data_id',$admissionData->educationid)->first();
                                                    @endphp
                                                    {{$education->data_name}}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Occupation</td>
                                                <td colspan="2">
                                                    @if($admissionData->occupationid!= null)
                                                    @php
                                                    $occupation=DB::table('dcs.payload_data')->select('data_name')->where('data_type','occupationId')->where('data_id',$admissionData->occupationid)->first();
                                                    @endphp
                                                    {{$occupation->data_name}}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Phone Number</td>
                                                <td colspan="2">
                                                    {{$admissionData->contactno}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Present Address </td>
                                                <td colspan="2">
                                                    {{$admissionData->presentupazilaid}}, {{$admissionData->presentaddress}},
                                                    {{$admissionData->presentdistrictid}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Permanent Address</td>
                                                <td colspan="2">
                                                    {{$admissionData->permanentupazilaid}},
                                                    {{$admissionData->permanentaddress }},{{$admissionData->permanentdistrictid }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Marital Status</td>
                                                <td colspan="2">
                                                    @if($admissionData->maritalstatusid!= null)
                                                    @php
                                                    $maritStatus=DB::table('dcs.payload_data')->select('data_name')->where('data_type','maritalStatusId')->where('data_id',$admissionData->maritalstatusid)->first();
                                                    @endphp
                                                    {{$maritStatus->data_name}}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Spouse Name</td>
                                                <td colspan="2">
                                                    {{$admissionData->spousenameen}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Spouse NID</td>
                                                <td colspan="2">
                                                    {{$admissionData->spouseidcardidcardno}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Nominee Name</td>
                                                <td colspan="2">
                                                    {{$admissionData->nomineesname}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Nominee Date of Birth</td>
                                                <td colspan="2">
                                                    {{$admissionData->nomineesdateofbirth}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Nominee Card Type</td>
                                                <td colspan="2">
                                                    @if($admissionData->nomineescardtypeid!= null)
                                                    @php
                                                    $nomineeIdType=DB::table('dcs.payload_data')->select('data_name')->where('data_type','cardTypeId')->where('data_id',$admissionData->nomineescardtypeid)->first();
                                                    @endphp
                                                    {{$nomineeIdType->data_name}}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Nominee Relationship</td>
                                                <td colspan="2">
                                                    @if($admissionData->nomineesrelationshipid!= null)
                                                    @php
                                                    $nomineeRelation=DB::table('dcs.payload_data')->select('data_name')->where('data_type','relationshipId')->where('data_id',$admissionData->nomineesrelationshipid)->first();
                                                    @endphp
                                                    {{$nomineeRelation->data_name}}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Nominee Id No.</td>
                                                <td colspan="2">
                                                    {{$admissionData->nomineesidcardno}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Wallet Number</td>
                                                <td colspan="2">
                                                    {{$admissionData->bkashwalletno}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Guarantor Relationship</td>
                                                <td colspan="2">
                                                    @if($admissionData->guarantorrelationshipid!= null)
                                                    @php
                                                    $guarantorRelation=DB::table('dcs.payload_data')->select('data_name')->where('data_type','relationshipId')->where('data_id',$admissionData->guarantorrelationshipid)->first();
                                                    @endphp
                                                    {{$guarantorRelation->data_name}}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Applicant's Picture</td>
                                                <td colspan="2">
                                                    <img class="guarantor_img " src="{{$admissionData->memberimageurl}}"
                                                        alt="Combine Picture">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>NID/Others ID Front</td>
                                                <td colspan="2">
                                                    <img class="nid_img" src="{{$admissionData->idcardfrontimageurl}}"
                                                        alt="NID/Others ID Front">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>NID/Others ID Back</td>
                                                <td colspan="2">
                                                    <img class="nid_img" src="{{$admissionData->idcardbackimageurl}}"
                                                        alt="NID/Others ID Back">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Nominee's font ID Images</td>
                                                <td colspan="2">
                                                    <img class="nid_img" src="{{$admissionData->nomineesfrontimageurl}}"
                                                        alt="NID/Others ID Back">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Nominee's back ID Images</td>
                                                <td colspan="2">
                                                    <img class="nid_img" src="{{$admissionData->nomineesbackimageurl}}"
                                                        alt="NID/Others ID Back">
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div><!-- /.box-body -->
                            <!-- </form> -->
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
