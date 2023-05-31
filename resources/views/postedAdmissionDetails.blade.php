@extends('backend.layouts.master')

@section('title','Posted Admission')
@section('style')
<style>
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
    .white{
        color: white;
    }

    .bgColor{
        background: #F3F6F9;
        color: black;
    }

    .nav_bar{
        background: #FB3199;
    }
    
    .nav_bar .nav .nav-item .active{
        color:#fff;
        background-color:DarkOrange;
    } 
    .nav_bar .nav .nav-item .nav-link{
        color:#fff;
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
                <!--begin::Page Title-->
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Posted Admission Details</h5>
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
                            sureyou want to submit?')" name="registration"> --}}
                            <form action="{{route('store')}}" method="post">
                                @csrf
                                <div class="box-body">
                                    <div class="nav_bar">
                                        <ul class="nav  nav-pills nav-fill">
                                            <li class="nav-item">
                                                <a class="nav-link active" id="client1" data-toggle="tab" href="#clientInfo">Client Information</a>
                                            </li>
                                        </ul>
                                    </div>
                                    @php
                                        $db = config('database.db'); 
                                    @endphp

                                    <div class="tab-content">
                                        <div class="tab-pane active" id="clientInfo">
                                            <table class="table table-bordered">
                                            <tr>
                                                <th colspan="6" class="bgColor">Client Information</th>
                                            </tr>
                                            <tr>
                                                <td rowspan="32"></td>
                                                <td>Applicant's Name</td>
                                                <td rowspan="32"></td>
                                                <td colspan="2">
                                                    {{$data->nameen}}
                                                </td>
                                                <td rowspan="32"></td>
                                            </tr>
                                            <tr>
                                                <td>ID Type </td>
                                                <td colspan="2">
                                                @if($data->idcardcardtypeid!= null)
                                                @php
                                                 $idType=DB::table($db.'.payload_data')->select('data_name')->where('data_type','cardTypeId')->where('data_id',$data->idcardcardtypeid)->first();                                        
                                                @endphp
                                                {{$idType->data_name}}
                                                @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>ID NO.</td>
                                                <td colspan="2">
                                                {{$data->idcardidcardno}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Date of Birth</td>
                                                <td colspan="2" >
                                                {{$data->dateofbirth}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Gender</td>
                                                <td colspan="2" >
                                                @if($data->genderid!= null)
                                                @php
                                                 $gender=DB::table($db.'.payload_data')->select('data_name')->where('data_type','genderId')->where('data_id',$data->genderid)->first();                                        
                                                @endphp
                                                {{$gender->data_name}}
                                                @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Mother's Name</td>
                                                <td colspan="2" >
                                                {{$data->mothernameen}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Father's Name</td>
                                                <td colspan="2" >
                                                {{$data->fathernameen}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Educaiton</td>
                                                <td colspan="2" >
                                                @if($data->educationid!= null)
                                                @php
                                                 $education=DB::table($db.'.payload_data')->select('data_name')->where('data_type','educationId')->where('data_id',$data->educationid)->first();                                        
                                                @endphp
                                                {{$education->data_name}}
                                                @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Occupation</td>
                                                <td colspan="2">
                                                @if($data->occupationid!= null)
                                                @php
                                                 $occupation=DB::table($db.'.payload_data')->select('data_name')->where('data_type','occupationId')->where('data_id',$data->occupationid)->first();                                        
                                                @endphp
                                                {{$occupation->data_name}}
                                                @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Phone Number</td>
                                                <td colspan="2">
                                                    {{$data->contactno}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Present Address </td>
                                                <td colspan="2">
                                                {{$data->presentupazilaid}}, {{$data->presentaddress}}, {{$data->presentdistrictid}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Permanent Address</td>
                                                <td colspan="2" >
                                                {{$data->permanentupazilaid}}, {{$data->permanentaddress }},{{$data->permanentdistrictid }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Marital Status</td>
                                                <td colspan="2" >
                                                @if($data->maritalstatusid!= null)
                                                @php
                                                 $maritStatus=DB::table($db.'.payload_data')->select('data_name')->where('data_type','maritalStatusId')->where('data_id',$data->maritalstatusid)->first();                                        
                                                @endphp
                                                {{$maritStatus->data_name}}
                                                @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Spouse Name</td>
                                                <td colspan="2" >
                                                {{$data->spousenameen}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Spouse NID</td>
                                                <td colspan="2" >
                                                {{$data->spouseidcardidcardno}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Nominee Name</td>
                                                <td colspan="2" >
                                                {{$data->nomineesname}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Nominee Date of Birth</td>
                                                <td colspan="2">
                                                {{$data->nomineesdateofbirth}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Nominee Card Type</td>
                                                <td colspan="2">
                                                @if($data->nomineescardtypeid!= null)
                                                @php
                                                 $nomineeIdType=DB::table($db.'.payload_data')->select('data_name')->where('data_type','cardTypeId')->where('data_id',$data->nomineescardtypeid)->first();                                        
                                                @endphp
                                                {{$nomineeIdType->data_name}}
                                                @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Nominee Relationship</td>
                                                <td colspan="2" >
                                                @if($data->nomineesrelationshipid!= null)
                                                @php
                                                 $nomineeRelation=DB::table($db.'.payload_data')->select('data_name')->where('data_type','relationshipId')->where('data_id',$data->nomineesrelationshipid)->first();                                        
                                                @endphp
                                                {{$nomineeRelation->data_name}}
                                                @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Nominee Id No.</td>
                                                <td colspan="2">
                                                {{$data->nomineesidcardno}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Wallet Number</td>
                                                <td colspan="2">
                                                {{$data->bkashwalletno}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Guarantor Relationship</td>
                                                <td colspan="2" >
                                                @if($data->guarantorrelationshipid!= null)
                                                @php
                                                 $guarantorRelation=DB::table($db.'.payload_data')->select('data_name')->where('data_type','relationshipId')->where('data_id',$data->guarantorrelationshipid)->first();                                        
                                                @endphp
                                                {{$guarantorRelation->data_name}}
                                                @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Applicant's Picture</td>
                                                <td colspan="2"  >
                                                    <img class="guarantor_img " src="{{$data->memberimageurl}}" alt="Combine Picture">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>NID/Others ID Front</td>
                                                <td colspan="2" >
                                                    <img class="nid_img" src="{{$data->idcardfrontimageurl}}" alt="NID/Others ID Front">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>NID/Others ID Back</td>
                                                <td colspan="2" >
                                                    <img class="nid_img" src="{{$data->idcardbackimageurl}}" alt="NID/Others ID Back">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Nominee's font ID Images</td>
                                                <td colspan="2" >
                                                    <img class="nid_img" src="{{$data->nomineesfrontimageurl}}" alt="NID/Others ID Back">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Nominee's back ID Images</td>
                                                <td colspan="2" >
                                                    <img class="nid_img" src="{{$data->nomineesbackimageurl}}" alt="NID/Others ID Back">
                                                </td>
                                            </tr>
                                        </table>
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
$(document).ready(function(){
    $('#more2').on('click',function(){
    $("li .active").removeClass("active");
    $('#more1').addClass('active');
});

$('#social2').on('click',function(){
    $("li .active").removeClass("active");
    $('#social1').addClass('active');
});
});
</script>

@endsection
