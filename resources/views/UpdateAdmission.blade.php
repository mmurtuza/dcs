@extends('backend.layouts.master')

@section('title','User')
@section('style')
<style>


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
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">User</h5>
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
                            <?php
                            foreach ($iddata as $row);

                            ?>
                            <form action="updateAdmissionStore" method="post">
                                @csrf
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="email">branchcode</label>
                                        <input type="text" class="form-control" id="email" name="branchcode" value="<?php echo $row->branchcode; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="email">pin</label>
                                        <input type="text" class="form-control" id="email" name="pin" value="<?php echo $row->pin; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="email">orgno</label>
                                        <input type="text" class="form-control" id="email" name="orgno" value="<?php echo $row->orgno; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="email">assignedpo</label>
                                        <input type="text" class="form-control" id="email" name="assignedpo" value="<?php echo $row->assignedpo; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="email">status</label>
                                        <input type="text" class="form-control" id="email" name="status" value="<?php echo $row->status; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="password">transectionId</label>
                                        <input type="text" class="form-control" id="password" name="entollmentid" value="<?php echo $row->entollmentid; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">ErpStatus</label>
                                        <input type="text" class="form-control" id="phone" name="ErpStatus" value="<?php echo $row->ErpStatus; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">IsRefferal</label>
                                        <input type="number" class="form-control" id="phone" name="IsRefferal" value="<?php if ($row->IsRefferal == '0') {
                                                                                                                            echo "0";
                                                                                                                        } else {
                                                                                                                            echo $row->IsRefferal;
                                                                                                                        } ?>">
                                    </div>
                                    <div>
                                        <label for="phone">RefferedById</label>
                                        <input type="text" class="form-control" id="phone" name="RefferedById" value="<?php echo $row->RefferedById; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">MemberId</label>
                                        <input type="text" class="form-control" id="phone" name="MemberId" value="<?php echo $row->MemberId; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">MemberCateogryId</label>
                                        <input type="text" class="form-control" id="phone" name="MemberCateogryId" value="<?php echo $row->MemberCateogryId; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">ApplicantsName</label>
                                        <input type="text" class="form-control" id="phone" name="ApplicantsName" value="<?php echo $row->ApplicantsName; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">MainIdTypeId</label>
                                        <input type="text" class="form-control" id="phone" name="MainIdTypeId" value="<?php echo $row->MainIdTypeId; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">IdNo</label>
                                        <input type="text" class="form-control" id="phone" name="IdNo" value="<?php echo $row->IdNo; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">OtherIdTypeId</label>
                                        <input type="text" class="form-control" id="phone" name="OtherIdTypeId" value="<?php echo $row->OtherIdTypeId; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">OtherIdNo</label>
                                        <input type="text" class="form-control" id="phone" name="OtherIdNo" value="<?php echo $row->OtherIdNo; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">ExpiryDate</label>
                                        <input type="text" class="form-control" id="phone" name="ExpiryDate" value="<?php echo $row->ExpiryDate; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">IssuingCountry</label>
                                        <input type="text" class="form-control" id="phone" name="IssuingCountry" value="<?php echo $row->IssuingCountry; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">DOB</label>
                                        <input type="text" class="form-control" id="phone" name="DOB" value="<?php echo $row->DOB; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">MotherName</label>
                                        <input type="text" class="form-control" id="phone" name="MotherName" value="<?php echo $row->MotherName; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">FatherName</label>
                                        <input type="text" class="form-control" id="phone" name="FatherName" value="<?php echo $row->FatherName; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">EducationId</label>
                                        <input type="text" class="form-control" id="phone" name="EducationId" value="<?php echo $row->EducationId; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">Phone</label>
                                        <input type="text" class="form-control" id="phone" name="Phone" value="<?php echo $row->Phone; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">PresentAddress</label>
                                        <input type="text" class="form-control" id="phone" name="PresentAddress" value="<?php echo $row->PresentAddress; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">presentUpazilaId</label>
                                        <input type="text" class="form-control" id="phone" name="presentUpazilaId" value="<?php echo $row->presentUpazilaId; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">PermanentAddress</label>
                                        <input type="text" class="form-control" id="phone" name="PermanentAddress" value="<?php echo $row->PermanentAddress; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">parmanentUpazilaId</label>
                                        <input type="text" class="form-control" id="phone" name="parmanentUpazilaId" value="<?php echo $row->parmanentUpazilaId; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">MaritalStatusId</label>
                                        <input type="text" class="form-control" id="phone" name="MaritalStatusId" value="<?php echo $row->MaritalStatusId; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">SpouseName</label>
                                        <input type="text" class="form-control" id="phone" name="SpouseName" value="<?php echo $row->SpouseName; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">SpouseNidOrBid</label>
                                        <input type="text" class="form-control" id="phone" name="SpouseNidOrBid" value="<?php echo $row->SpouseNidOrBid; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">SposeDOB</label>
                                        <input type="text" class="form-control" id="phone" name="SposeDOB" value="<?php echo $row->SposeDOB; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">SpuseOccupationId</label>
                                        <input type="text" class="form-control" id="phone" name="SpuseOccupationId" value="<?php echo $row->SpuseOccupationId; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">ReffererName</label>
                                        <input type="text" class="form-control" id="phone" name="ReffererName" value="<?php echo $row->ReffererName; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">ReffererPhone</label>
                                        <input type="text" class="form-control" id="phone" name="ReffererPhone" value="<?php echo $row->ReffererPhone; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">FamilyMemberNo</label>
                                        <input type="text" class="form-control" id="phone" name="FamilyMemberNo" value="<?php echo $row->FamilyMemberNo; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">NoOfChildren</label>
                                        <input type="text" class="form-control" id="phone" name="NoOfChildren" value="<?php echo $row->NoOfChildren; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">NomineeDOB</label>
                                        <input type="text" class="form-control" id="phone" name="NomineeDOB" value="<?php echo $row->NomineeDOB; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">RelationshipId</label>
                                        <input type="text" class="form-control" id="phone" name="RelationshipId" value="<?php echo $row->RelationshipId; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">ApplicantCpmbinedImg</label>
                                        <input type="text" class="form-control" id="phone" name="ApplicantCpmbinedImg" value="<?php echo $row->ApplicantCpmbinedImg; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">ReffererImg</label>
                                        <input type="text" class="form-control" id="phone" name="ReffererImg" value="<?php echo $row->ReffererImg; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">ReffererIdImg</label>
                                        <input type="text" class="form-control" id="phone" name="ReffererIdImg" value="<?php echo $row->ReffererIdImg; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">FrontSideOfIdImg</label>
                                        <input type="text" class="form-control" id="phone" name="FrontSideOfIdImg" value="<?php echo $row->FrontSideOfIdImg; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">BackSideOfIdimg</label>
                                        <input type="text" class="form-control" id="phone" name="BackSideOfIdimg" value="<?php echo $row->BackSideOfIdimg; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">NomineeIdImg</label>
                                        <input type="text" class="form-control" id="phone" name="NomineeIdImg" value="<?php echo $row->NomineeIdImg; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">SpuseIdImg</label>
                                        <input type="text" class="form-control" id="phone" name="SpuseIdImg" value="<?php echo $row->SpuseIdImg; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">DynamicFieldValue</label>
                                        <input type="text" class="form-control" id="phone" name="DynamicFieldValue" value="<?php echo $row->DynamicFieldValue; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">created_at</label>
                                        <input type="text" class="form-control" id="phone" name="created_at" value="<?php echo $row->created_at; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">updated_at</label>
                                        <input type="text" class="form-control" id="phone" name="updated_at" value="<?php echo $row->updated_at; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">projectcode</label>
                                        <input type="text" class="form-control" id="phone" name="projectcode" value="<?php echo $row->projectcode; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">Occupation</label>
                                        <input type="text" class="form-control" id="phone" name="Occupation" value="<?php echo $row->Occupation; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">IsBkash</label>
                                        <input type="text" class="form-control" id="phone" name="IsBkash" value="<?php echo $row->IsBkash; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">WalletNo</label>
                                        <input type="text" class="form-control" id="phone" name="WalletNo" value="<?php echo $row->WalletNo; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">WalletOwner</label>
                                        <input type="text" class="form-control" id="phone" name="WalletOwner" value="<?php echo $row->WalletOwner; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">NomineeName</label>
                                        <input type="text" class="form-control" id="phone" name="NomineeName" value="<?php echo $row->NomineeName; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">PrimaryEarner</label>
                                        <input type="text" class="form-control" id="phone" name="PrimaryEarner" value="<?php echo $row->PrimaryEarner; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">dochistory_id</label>
                                        <input type="text" class="form-control" id="phone" name="dochistory_id" value="<?php echo $row->dochistory_id; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">roleid</label>
                                        <input type="text" class="form-control" id="phone" name="roleid" value="<?php echo $row->roleid; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">action</label>
                                        <input type="text" class="form-control" id="phone" name="action" value="<?php echo $row->action; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">reciverrole</label>
                                        <input type="text" class="form-control" id="phone" name="reciverrole" value="<?php echo $row->reciverrole; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">NomineeNidNo</label>
                                        <input type="text" class="form-control" id="phone" name="NomineeNidNo" value="<?php echo $row->NomineeNidNo; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">NomineeNidFront</label>
                                        <input type="text" class="form-control" id="phone" name="NomineeNidFront" value="<?php echo $row->NomineeNidFront; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">NomineeNidBack</label>
                                        <input type="text" class="form-control" id="phone" name="NomineeNidBack" value="<?php echo $row->NomineeNidBack; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">SpouseNidFront</label>
                                        <input type="text" class="form-control" id="phone" name="SpouseNidFront" value="<?php echo $row->SpouseNidFront; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">SpouseNidBack</label>
                                        <input type="text" class="form-control" id="phone" name="SpouseNidBack" value="<?php echo $row->SpouseNidBack; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">PassbookRequired</label>
                                        <input type="text" class="form-control" id="phone" name="PassbookRequired" value="<?php echo $row->PassbookRequired; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">GenderId</label>
                                        <input type="text" class="form-control" id="phone" name="GenderId" value="<?php echo $row->GenderId; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">SavingsProductId</label>
                                        <input type="text" class="form-control" id="phone" name="SavingsProductId" value="<?php echo $row->SavingsProductId; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">NomineeIdExpiredate</label>
                                        <input type="text" class="form-control" id="phone" name="NomineeIdExpiredate" value="<?php echo $row->NomineeIdExpiredate; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">NomineeIdPlaceOfissue</label>
                                        <input type="text" class="form-control" id="phone" name="NomineeIdPlaceOfissue" value="<?php echo $row->NomineeIdPlaceOfissue; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">NomineePhoneNumber</label>
                                        <input type="text" class="form-control" id="phone" name="NomineePhoneNumber" value="<?php echo $row->NomineePhoneNumber; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">SpouseCardType</label>
                                        <input type="text" class="form-control" id="phone" name="SpouseCardType" value="<?php echo $row->SpouseCardType; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">SpouseIdExpiredate</label>
                                        <input type="text" class="form-control" id="phone" name="SpouseIdExpiredate" value="<?php echo $row->SpouseIdExpiredate; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">SpouseIdPlaceOfissue</label>
                                        <input type="text" class="form-control" id="phone" name="SpouseIdPlaceOfissue" value="<?php echo $row->SpouseIdPlaceOfissue; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">ErpHttpStatus</label>
                                        <input type="text" class="form-control" id="phone" name="ErpHttpStatus" value="<?php echo $row->ErpHttpStatus; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">ErpErrorMessage</label>
                                        <input type="text" class="form-control" id="phone" name="ErpErrorMessage" value="">
                                    </div>
                                    <div>
                                        <label for="phone">ErpErrors</label>
                                        <input type="text" class="form-control" id="phone" name="ErpErrors" value="">
                                    </div>
                                    <div>
                                        <label for="phone">bm_behavior</label>
                                        <input type="text" class="form-control" id="phone" name="bm_behavior" value="<?php echo $row->bm_behavior; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">bm_financial_status</label>
                                        <input type="text" class="form-control" id="phone" name="bm_financial_status" value="<?php echo $row->bm_financial_status; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">bm_client_house_image</label>
                                        <input type="text" class="form-control" id="phone" name="bm_client_house_image" value="<?php echo $row->bm_client_house_image; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">bm_lat</label>
                                        <input type="text" class="form-control" id="phone" name="bm_lat" value="<?php echo $row->bm_lat; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">bm_lng</label>
                                        <input type="text" class="form-control" id="phone" name="bm_lng" value="<?php echo $row->bm_lng; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">Flag</label>
                                        <input type="text" class="form-control" id="phone" name="Flag" value="<?php echo $row->Flag; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">ApplicantSinglePic</label>
                                        <input type="text" class="form-control" id="phone" name="ApplicantSinglePic" value="<?php echo $row->ApplicantSinglePic; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">TargetAmount</label>
                                        <input type="text" class="form-control" id="phone" name="TargetAmount" value="<?php echo $row->TargetAmount; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">PermanentDistrictId</label>
                                        <input type="text" class="form-control" id="phone" name="PermanentDistrictId" value="<?php echo $row->PermanentDistrictId; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">NomineeNidType</label>
                                        <input type="text" class="form-control" id="phone" name="NomineeNidType" value="<?php echo $row->NomineeNidType; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">IsSameAddress</label>
                                        <input type="text" class="form-control" id="phone" name="IsSameAddress" value="<?php echo $row->IsSameAddress; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">PresentDistrictId</label>
                                        <input type="text" class="form-control" id="phone" name="PresentDistrictId" value="<?php echo $row->PresentDistrictId; ?>">
                                    </div>
                                    <div>
                                        <label for="phone">surveyid</label>
                                        <input type="text" class="form-control" id="phone" name="surveyid" value="<?php echo $row->surveyid; ?>">
                                    </div>
                                    <input type="hidden" name='id' value="<?php echo $row->id; ?>">
                                </div><!-- /.box-body -->
                                <br>
                                <div class="box-footer">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <button type="reset" onclick="resetForm()" class="btn btn-warning btn-block">Reset</button>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="submit" class="btn btn-secondary btn-block">Submit</button>
                                        </div>
                                    </div>
                                </div>
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