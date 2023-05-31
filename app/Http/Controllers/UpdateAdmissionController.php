<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use DB;

class UpdateAdmissionController extends Controller
{
    private $db = 'dcs';
    public function index(Request $request)
    {
        return view('UpdateAdmissionSearch');
    }
    public function GetData(Request $request)
    {
        $branchcode = $request->get('branchcode');
        $orgno = $request->get('orgno');
        $pin = $request->get('pin');
        $getdata = DB::Table('dcs.admissions')->where('branchcode', $branchcode)->where('orgno', $orgno)->get();
        return view('updateAdmissionList', compact('getdata'));
    }
    public function AdmissionRejectView(Request $request)
    {
        return view('Admission_Reject');
    }
    public function Editpage(Request $request)
    {
        $id = $request->get('id');
        $iddata = DB::Table('dcs.admissions')->where('id', $id)->get();
        //dd($iddata);
        return view('UpdateAdmission', compact('iddata'));
    }
    public function UpdateAdmissionStore(Request $request)
    {
        //dd("Huda");
        $IsRefferal = $request->get('IsRefferal');
        $RefferedById = $request->get('RefferedById');
        $MemberId = $request->get('MemberId');
        $MemberCateogryId = $request->get('MemberCateogryId');
        $ApplicantsName = $request->get('ApplicantsName');
        $MainIdTypeId = $request->get('MainIdTypeId');
        $IdNo = $request->get('IdNo');
        $OtherIdTypeId = $request->get('OtherIdTypeId');
        $OtherIdNo = $request->get('OtherIdNo');
        $ExpiryDate = $request->get('ExpiryDate');
        $IssuingCountry = $request->get('IssuingCountry');
        $DOB = $request->get('DOB');
        $MotherName = $request->get('MotherName');
        $FatherName = $request->get('FatherName');
        $EducationId = $request->get('EducationId');
        $Phone = $request->get('Phone');
        $PresentAddress = $request->get('PresentAddress');
        $presentUpazilaId = $request->get('presentUpazilaId');
        $PermanentAddress = $request->get('PermanentAddress');
        $parmanentUpazilaId = $request->get('parmanentUpazilaId');
        $MaritalStatusId = $request->get('MaritalStatusId');
        $SpouseName = $request->get('SpouseName');
        $SpouseNidOrBid = $request->get('SpouseNidOrBid');
        $SposeDOB = $request->get('SposeDOB');
        $SpuseOccupationId = $request->get('SpuseOccupationId');
        $ReffererName = $request->get('ReffererName');
        $ReffererPhone = $request->get('ReffererPhone');
        $FamilyMemberNo = $request->get('FamilyMemberNo');
        $NoOfChildren = $request->get('NoOfChildren');
        $NomineeDOB = $request->get('NomineeDOB');
        $RelationshipId = $request->get('RelationshipId');
        $ApplicantCpmbinedImg = $request->get('ApplicantCpmbinedImg');
        $ReffererImg = $request->get('ReffererImg');
        $ReffererIdImg = $request->get('ReffererIdImg');
        $FrontSideOfIdImg = $request->get('FrontSideOfIdImg');
        $BackSideOfIdimg = $request->get('BackSideOfIdimg');
        $NomineeIdImg = $request->get('NomineeIdImg');
        $SpuseIdImg = $request->get('SpuseIdImg');
        $DynamicFieldValue = $request->get('DynamicFieldValue');
        $created_at = $request->get('created_at');
        $updated_at = $request->get('updated_at');
        $updated_at = $request->get('updated_at');
        $projectcode = $request->get('projectcode');
        $Occupation = $request->get('Occupation');
        $IsBkash = $request->get('IsBkash');
        $WalletNo = $request->get('WalletNo');
        $WalletOwner = $request->get('WalletOwner');
        $NomineeName = $request->get('NomineeName');
        $PrimaryEarner = $request->get('PrimaryEarner');
        $dochistory_id = $request->get('dochistory_id');
        $roleid = $request->get('roleid');
        $pin = $request->get('pin');
        $action = $request->get('action');
        $reciverrole = $request->get('reciverrole');
        $status = $request->get('status');
        $orgno = $request->get('orgno');
        $assignedpo = $request->get('assignedpo');
        $NomineeNidNo = $request->get('NomineeNidNo');
        $NomineeNidFront = $request->get('NomineeNidFront');
        $NomineeNidBack = $request->get('NomineeNidBack');
        $SpouseNidFront = $request->get('SpouseNidFront');
        $SpouseNidBack = $request->get('SpouseNidBack');
        $PassbookRequired = $request->get('PassbookRequired');
        $entollmentid = $request->get('entollmentid');
        $GenderId = $request->get('GenderId');
        $SavingsProductId = $request->get('SavingsProductId');
        $NomineeIdExpiredate = $request->get('NomineeIdExpiredate');
        $NomineeIdPlaceOfissue = $request->get('NomineeIdPlaceOfissue');
        $NomineePhoneNumber = $request->get('NomineePhoneNumber');
        $SpouseCardType = $request->get('SpouseCardType');
        $SpouseIdExpiredate = $request->get('SpouseIdExpiredate');
        $SpouseIdPlaceOfissue = $request->get('SpouseIdPlaceOfissue');
        $ErpHttpStatus = $request->get('ErpHttpStatus');
        $ErpErrorMessage = $request->get('ErpErrorMessage');
        $ErpErrors = $request->get('ErpErrors');
        $bm_behavior = $request->get('bm_behavior');
        $bm_financial_status = $request->get('bm_financial_status');
        $bm_client_house_image = $request->get('bm_client_house_image');
        $bm_lat = $request->get('bm_lat');
        $bm_lng = $request->get('bm_lng');
        $Flag = $request->get('Flag');
        $ApplicantSinglePic = $request->get('ApplicantSinglePic');
        $TargetAmount = $request->get('TargetAmount');
        $PermanentDistrictId = $request->get('PermanentDistrictId');
        $NomineeNidType = $request->get('NomineeNidType');
        $IsSameAddress = $request->get('IsSameAddress');
        $PresentDistrictId = $request->get('PresentDistrictId');
        $ErpStatus = $request->get('ErpStatus');
        $surveyid = $request->get('surveyid');
        $branchcode =  $request->get('branchcode');
        $id = $request->get('id');
        $updateAt = date('Y-m-d H:i:s');
        if ($id != '') {
            $insertquery = DB::Table('dcs.admissions')->where('id', $id)->update([
                'IsRefferal' => $IsRefferal, 'RefferedById' => $RefferedById, 'MemberId' => $MemberId, 'MemberCateogryId' => $MemberCateogryId, 'ApplicantsName' => $ApplicantsName, 'MainIdTypeId' => $MainIdTypeId, 'IdNo' => $IdNo,
                'OtherIdTypeId' => $OtherIdTypeId, 'OtherIdNo' => $OtherIdNo, 'ExpiryDate' => $ExpiryDate, 'IssuingCountry' => $IssuingCountry, 'DOB' => $DOB, 'MotherName' => $MotherName, 'FatherName' => $FatherName, 'EducationId' => $EducationId, 'Phone' => $Phone, 'PresentAddress' => $PresentAddress,
                'presentUpazilaId' => $presentUpazilaId, 'PermanentAddress' => $PermanentAddress, 'parmanentUpazilaId' => $parmanentUpazilaId, 'MaritalStatusId' => $MaritalStatusId, 'SpouseName' => $SpouseName,
                'SpouseNidOrBid' => $SpouseNidOrBid, 'SposeDOB' => $SposeDOB, 'SpuseOccupationId' => $SpuseOccupationId, 'ReffererName' => $ReffererName, 'ReffererPhone' => $ReffererPhone, 'FamilyMemberNo' => $FamilyMemberNo, 'NoOfChildren' => $NoOfChildren,
                'NomineeDOB' => $NomineeDOB, 'RelationshipId' => $RelationshipId, 'ApplicantCpmbinedImg' => $ApplicantCpmbinedImg, 'ReffererImg' => $ReffererImg, 'ReffererIdImg' => $ReffererIdImg,
                'FrontSideOfIdImg' => $FrontSideOfIdImg, 'BackSideOfIdimg' => $BackSideOfIdimg, 'NomineeIdImg' => $NomineeIdImg, 'SpuseIdImg' => $SpuseIdImg, 'DynamicFieldValue' => $DynamicFieldValue,
                'created_at' => $created_at, 'updated_at' => $updated_at, 'branchcode' => $branchcode, 'projectcode' => $projectcode, 'Occupation' => $Occupation, 'IsBkash' => $IsBkash, 'WalletNo' => $WalletNo,
                'WalletOwner' => $WalletOwner, 'NomineeName' => $NomineeName, 'PrimaryEarner' => $PrimaryEarner, 'dochistory_id' => $dochistory_id, 'roleid' => $roleid, 'pin' => $pin, 'action' => $action,
                'reciverrole' => $reciverrole, 'status' => $status, 'orgno' => $orgno, 'assignedpo' => $assignedpo, 'NomineeNidNo' => $NomineeNidNo, 'NomineeNidFront' => $NomineeNidFront, 'NomineeNidBack' => $NomineeNidBack,
                'SpouseNidFront' => $SpouseNidFront, 'SpouseNidBack' => $SpouseNidBack, 'PassbookRequired' => $PassbookRequired, 'entollmentid' => $entollmentid, 'GenderId' => $GenderId, 'SavingsProductId' => $SavingsProductId, 'NomineeIdExpiredate' => $NomineeIdExpiredate, 'NomineeIdPlaceOfissue' => $NomineeIdPlaceOfissue,
                'NomineePhoneNumber' => $NomineePhoneNumber, 'SpouseCardType' => $SpouseCardType, 'SpouseIdExpiredate' => $SpouseIdExpiredate, 'SpouseIdPlaceOfissue' => $SpouseIdPlaceOfissue, 'ErpHttpStatus' => $ErpHttpStatus, 'ErpErrorMessage' => $ErpErrorMessage,
                'ErpErrors' => $ErpErrors, 'bm_behavior' => $bm_behavior, 'bm_financial_status' => $bm_financial_status, 'bm_client_house_image' => $bm_client_house_image, 'bm_lat' => $bm_lat, 'bm_lng' => $bm_lng, 'Flag' => $Flag,
                'ApplicantSinglePic' => $ApplicantSinglePic, 'TargetAmount' => $TargetAmount, 'PermanentDistrictId' => $PermanentDistrictId, 'NomineeNidType' => $NomineeNidType, 'IsSameAddress' => $IsSameAddress, 'PresentDistrictId' => $PresentDistrictId, 'ErpStatus' => $ErpStatus, 'surveyid' => $surveyid, 'update_at' => $updateAt
            ]);
            if ($insertquery) {
                return redirect("admissionview?_token=HUF1WzTgRLWMdixa0Ndh9gH70rf30iJoa5iKtmzA&branchcode=$branchcode&orgno=$orgno&pin=$pin");
            }
        }
    }
    public function AdmissionReject(Request $request)
    {
        $checkboxdata = $request->get('update');
        $cont = count($checkboxdata);
        if (empty($checkboxdata)) {
            echo "Data Not Selected";
        } else {
            $updateAt = date('Y-m-d H:i:s');
            for ($i = 0; $i < $cont; $i++) {
                $id = $checkboxdata[$i];
                $insertquery = DB::Table('dcs.admissions')->where('id', $id)->update(["status" => 3, "update_at" => $updateAt]);
            }
            if ($insertquery) {
                return redirect()->back()->with('success', "Data Update Successfully!");
            } else {
                return redirect()->back()->with('error', "Data Update Failed!");
            }
        }
    }
    public function CheckLoan(Request $request)
    {
        dd("huda");
        $a = DB::Table('dcs.loans')->where('branchcode', '2867')->get();
        return json_encode($a);
    }
}
