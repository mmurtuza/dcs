<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use view;
use DateTime;
use Illuminate\Support\Facades\Input;
use DB;

date_default_timezone_set('Asia/Dhaka');

ini_set('memory_limit', '6000M');
ini_set('max_execution_time', 1800);

use ZipArchive;
use Log;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;
use File;
use Illuminate\Support\Facades\Session;
//use App\Http\Controllers\TestingController_Version;
header('Content-Type: application/json; charset=utf-8');

class LiveApiController extends Controller
{
  private $dberp = 'erptestingserver'; //erp test db
  private $db = 'dcs';        //dcs db name
  private $Topic = 'sc';
  private $key = '5d0a4a85-df7a-scapi-bits-93eb-145f6a9902ae';

  //private $Topic = 'sc';
  public function DcsDataSync(Request $request)
  {
    $admissionarray = array();
    $loanarray = array();
    $surveyarray = array();
    $DataSetArray = [];
    $DataSetArray = array();
    $apikey = '7f30f4491cb4435984616d1913e88389';
    $key = "5d0a4a85-df7a-scapi-bits-93eb-145f6a9902ae";
    $db = $this->db;
    $dberp = $this->dberp;
    $token = Request::header('apiKey');
    $BranchCode = Request::input('branchCode');
    $ProjectCode = Request::input('projectCode');
    $LastSyncTime1 = Request::input('lastSyncTime');
    $LastSyncTime = Request::input('lastSyncTime');
    if ($LastSyncTime == "2000-01-01 00:00:00") {
      $currentdt = date('Y-m-d H:i:s');
      $LastSyncTime = date('Y-m-d H:i:s', strtotime('-45 days', strtotime($currentdt)));
    } else {
      $LastSyncTime = date('Y-m-d H:i:s', strtotime('-7 days', strtotime($LastSyncTime)));
    }

    $CurrentTime = Request::input('currentTime');
    $Appid = Request::header('appId');
    $Pin = Request::input('pin');
    $AppversionName = Request::header('appVersionName');
    $AppVersionCode = Request::header('appVersionCode');
    $CurrentTimes = date('Y-m-d H:i:s');
    Log::info("Params-" . $token . '/' . $BranchCode . '/' . $Appid . '/' . $Pin . '/' . $LastSyncTime . '/' . $LastSyncTime1 . '/' . $ProjectCode);
    try {
      //$this->DcsDataSyncOff($db, $BranchCode);
      // dd("Huda");
      $config = $this->ConfigurationSync($db, $Pin, $BranchCode, $Appid, $CurrentTimes, $ProjectCode, $token);
      $DataSetArray['Configurationdata'] = $config;
      $bank_info = $this->Bank_List($db, $Pin, $BranchCode, $Appid, $CurrentTimes, $ProjectCode, $token);
      $DataSetArray['BankList'] = $bank_info;
      ///dd("Test");
      $jsonData = "ErpMemberList Qsoft In";
      $type = "ErpMemberList";
      $this->LogCreate($BranchCode, $ProjectCode, $Pin, $type, $jsonData);
      $memberlistdata = $this->MemberLists($db, $dberp, $BranchCode, $ProjectCode, $Pin, $LastSyncTime, $Appid, $key, $LastSyncTime1);
      $DataSetArray['erpmemberlist'] = $memberlistdata;
      $jsonData = "ErpMemberList Qsoft Out";
      $type = "ErpMemberList";
      $this->LogCreate($BranchCode, $ProjectCode, $Pin, $type, $jsonData);
      //dd("Tes");
      $servey = $this->getSurveys($db, $BranchCode, $ProjectCode, $Pin, $Appid);
      $DataSetArray['surveydata'] = $servey;

      $admissiondata =  $this->AdmissionDataSync($db, $dberp, $BranchCode, $ProjectCode, $Pin, $LastSyncTime, $Appid);
      $DataSetArray['admissiondata'] = $admissiondata;
      $jsonData = "Admission Qsoft Out";
      $type = "Admission";
      $this->LogCreate($BranchCode, $ProjectCode, $Pin, $type, $jsonData);
      //dd("huda");
      $loandata = $this->LoanDataSync($db, $dberp, $BranchCode, $ProjectCode, $Pin, $LastSyncTime, $Appid);
      //dd($loandata);
      $DataSetArray['loandata'] = $loandata;
      $jsonData = "Loan Qsoft Out";
      $type = "Loan";
      $this->LogCreate($BranchCode, $ProjectCode, $Pin, $type, $jsonData);
      //dd("hellp");
      $arrayFile = array("status" => "success", "time" => $CurrentTimes, "message" => "", "data" => $DataSetArray);
      $jsonFile = json_encode($arrayFile);
      //echo  $jsonFile;
      //die;
      $DataSetArray = [];

      $memory = memory_get_usage();
      $type = "Memory";
      $this->LogCreate($BranchCode, $ProjectCode, $Pin, $type, $memory);
      $this->ZipFileCreate($db, $Pin, $BranchCode, $Appid, $CurrentTimes, $ProjectCode, $jsonFile);
      $jsonData = "Zip File Create Qsoft";
      $type = "Zip";
      $DataSetArray = [];
      $this->LogCreate($BranchCode, $ProjectCode, $Pin, $type, $jsonData);
    } catch (Exception $e) {
      $this->CUSTMSG($e->getMessage());
    }
  }
  public function AdmissionDataSync($db, $dberp, $BranchCode, $ProjectCode, $Pin, $LastSyncTime, $Appid)
  {
    //dd("huda");
    $type = "Admission";
    $jsonData = "Admission ERP Status IN";
    $this->LogCreate($BranchCode, $ProjectCode, $Pin, $type, $jsonData);
    //$this->Admission_GetDataForErpStatusCheck($db, $dberp, $BranchCode, $ProjectCode, $Pin, $LastSyncTime, $Appid);
    $jsonData = "Admission ERP Status OUT";
    $this->LogCreate($BranchCode, $ProjectCode, $Pin, $type, $jsonData);
    $jsonData = "Admission Qsoft IN";
    $this->LogCreate($BranchCode, $ProjectCode, $Pin, $type, $jsonData);
    $ErpStatusId = null;
    $ErpRejectionReason = null;
    if ($Appid == 'bmsmerp') {
      $admissiondataary = array();
      $admissiondatas = array();
      $polist = $this->PoList($db, $BranchCode, $ProjectCode, $Pin);
      foreach ($polist as $row) {
        $cono = $row->cono;
        $admissiondata = DB::table($db . '.admissions')->where('branchcode', $BranchCode)->where('projectcode', $ProjectCode)->Where('assignedpo', $cono)->where("update_at", ">=", $LastSyncTime)->orderBy('id', 'desc')->get();
        if (!empty($admissiondata)) {
          foreach ($admissiondata as $data) {
            $id = $data->id;
            $MainIdTypeId = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'cardTypeId')->where('data_id', $data->MainIdTypeId)->first();
            $NomineeNidType = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'cardTypeId')->where('data_id', $data->NomineeNidType)->first();
            $OtherIdTypeId = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'cardTypeId')->where('data_id', $data->OtherIdTypeId)->first();
            $SpouseCardType = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'cardTypeId')->where('data_id', $data->SpouseCardType)->first();
            $EducationId = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'educationId')->where('data_id', $data->EducationId)->first();
            $MaritalStatusId = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'maritalStatusId')->where('data_id', $data->MaritalStatusId)->first();
            $SpuseOccupationId = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'occupationId')->where('data_id', $data->SpuseOccupationId)->first();
            $RelationshipId = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'relationshipId')->where('data_id', $data->RelationshipId)->first();
            $Occupation = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'occupationId')->where('data_id', $data->Occupation)->first();
            $genderId = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'genderId')->where('data_id', $data->GenderId)->first();
            $PrimaryEarner = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'primaryEarner')->where('data_id', $data->PrimaryEarner)->first();
            $MemberCateogryId = DB::table($db . '.projectwise_member_category')->select('categoryname')->where('categoryid', $data->MemberCateogryId)->first();
            $WalletOwner = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'primaryEarner')->where('data_id', $data->WalletOwner)->first();
            $role_name = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', $data->projectcode)->where('position', $data->roleid)->first();
            $recieverrole_name = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', $data->projectcode)->where('position', $data->reciverrole)->first();
            $dochistory = DB::table($db . '.document_history')->select('comment')->where('id', $data->dochistory_id)->first();
            $status = DB::table($db . '.status')->select('status_name')->where('status_id', $data->status)->first();
            $presentUpazilaId = DB::table($db . '.office_mapping')->select('thana_name')->where('thana_id', $data->presentUpazilaId)->where('district_id', $data->PresentDistrictId)->first();
            $parmanentUpazilaId = DB::table($db . '.office_mapping')->select('thana_name')->where('thana_id', $data->parmanentUpazilaId)->where('district_id', $data->PermanentDistrictId)->first();
            $PresentDistrictId = DB::table($db . '.office_mapping')->select('district_name')->where('district_id', $data->PresentDistrictId)->first();
            $PermanentDistrictId = DB::table($db . '.office_mapping')->select('district_name')->where('district_id', $data->PermanentDistrictId)->first();
            $created_at = DB::select(DB::raw("select cast(created_at as date) as created_at from $db.admissions where id='$id'"));
            $updated_at = DB::select(DB::raw("select cast(updated_at as date) as updated_at from $db.admissions where id='$id'"));
            //dd($created_at[0]->created_at);
            //print_r($MaritalStatusId);
            $mstatus = $MaritalStatusId->data_name ?? null;
            $MainIdTypeId = $MainIdTypeId->data_name ?? null;
            $EducationId = $EducationId->data_name ?? null;
            $WalletOwner = $WalletOwner->data_name ?? null;
            // $MaritalStatusId = $MaritalStatusId->date_name ?? null;
            $RelationshipId = $RelationshipId->data_name ?? null;
            $Occupation = $Occupation->data_name ?? null;
            $genderId = $genderId->data_name ?? null;
            $PrimaryEarner = $PrimaryEarner->data_name ?? null;
            $MemberCateogryId = $MemberCateogryId->categoryname ?? null;
            $role_name = $role_name->designation ?? null;
            $recieverrole_name = $recieverrole_name->designation ?? null;
            $status = $status->status_name ?? null;
            $NomineeNidType = $NomineeNidType->data_name ?? null;
            $SpuseOccupationId = $SpuseOccupationId->data_name ?? null;
            $SpouseCardType = $SpouseCardType->data_name ?? null;
            $OtherIdTypeId = $OtherIdTypeId->data_name ?? null;
            $presentUpazilaId = $presentUpazilaId->thana_name ?? null;
            $parmanentUpazilaId = $parmanentUpazilaId->thana_name ?? null;
            $PresentDistrictId = $PresentDistrictId->district_name ?? null;
            $PermanentDistrictId = $PermanentDistrictId->district_name ?? null;
            $comment = $dochistory->comment ?? null;
            if ($data->IsBkash == '1') {
              $IsBkash = "Yes";
            } else {
              $IsBkash = "No";
            }
            if ($data->PassbookRequired == '1') {
              $PassbookRequired = "Yes";
            } else {
              $PassbookRequired = "No";
            }
            if ($data->IsSameAddress == '1') {
              $IsSameAddress = "Yes";
            } else {
              $IsSameAddress = "No";
            }
            if ($data->status == '2') {
              if ($data->ErpStatus == '1') {
                $ErpStatus = 'Pending';
              } else if ($data->ErpStatus == '2') {
                $ErpStatus = 'Approved';
              } else if ($data->ErpStatus == '3') {
                $ErpStatus = 'Rejected';
              } else {
                $ErpStatus = 'Pending';
                $ErpStatusId = null;
                $ErpRejectionReason = null;
              }
            } else {
              $ErpStatus = null;
              $ErpStatusId = null;
              $ErpRejectionReason = null;
            }
            $arrayData = array(
              "id" => $data->id,
              "IsRefferal" => $data->IsRefferal,
              "RefferedById" => $data->RefferedById,
              "MemberId" => $data->MemberId,
              "MemberCateogryId" => $data->MemberCateogryId,
              "MemberCateogry" => $MemberCateogryId,
              "ApplicantsName" => $data->ApplicantsName,
              "ApplicantSinglePic" => $data->ApplicantSinglePic,
              "MainIdType" => $MainIdTypeId,
              "MainIdTypeId" => $data->MainIdTypeId,
              "IdNo" => $data->IdNo,
              "OtherIdType" => $OtherIdTypeId,
              "OtherIdTypeId" => $data->OtherIdTypeId,
              "OtherIdNo" => $data->OtherIdNo,
              "ExpiryDate" => $data->ExpiryDate,
              "IssuingCountry" => $data->IssuingCountry,
              "DOB" => $data->DOB,
              "MotherName" => $data->MotherName,
              "FatherName" => $data->FatherName,
              "Education" => $EducationId,
              "EducationId" => $data->EducationId,
              "Phone" => $data->Phone,
              "PresentAddress" => $data->PresentAddress,
              "presentUpazilaId" => $data->presentUpazilaId,
              "presentUpazila" => $presentUpazilaId,
              "PermanentAddress" => $data->PermanentAddress,
              "parmanentUpazilaId" => $data->parmanentUpazilaId,
              "PresentDistrictId" => $data->PresentDistrictId,
              "PresentDistrictName" => $PresentDistrictId,
              // "PresentDistrict" => $PresentDistrictId,
              "PermanentDistrictId" => $data->PermanentDistrictId,
              "PermanentDistrictName" => $PermanentDistrictId,
              // "PermanentDistrict" => $PermanentDistrict,
              "parmanentUpazila" => $parmanentUpazilaId,
              "MaritalStatusId" => $data->MaritalStatusId,
              "MaritalStatus" => $mstatus,
              "SpouseName" => $data->SpouseName,
              "SpouseCardType" => $SpouseCardType,
              "SpouseCardTypeId" => $data->SpouseCardType,
              "SpouseNidOrBid" => $data->SpouseNidOrBid,
              "SposeDOB" => $data->SposeDOB,
              "SpuseOccupationId" => $data->SpuseOccupationId,
              "SpuseOccupation" => $SpuseOccupationId,
              "SpouseNidFront" => $data->SpouseNidFront,
              "SpouseNidBack" => $data->SpouseNidBack,
              "ReffererName" => $data->ReffererName,
              "ReffererPhone" => $data->ReffererPhone,
              "FamilyMemberNo" => $data->FamilyMemberNo,
              "NoOfChildren" => $data->NoOfChildren,
              "NomineeDOB" => $data->NomineeDOB,
              "RelationshipId" => $data->RelationshipId,
              "Relationship" => $RelationshipId,
              "ApplicantCpmbinedImg" => $data->ApplicantCpmbinedImg,
              "ReffererImg" => $data->ReffererImg,
              "ReffererIdImg" => $data->ReffererIdImg,
              "FrontSideOfIdImg" => $data->FrontSideOfIdImg,
              "BackSideOfIdimg" => $data->BackSideOfIdimg,
              "NomineeIdImg" => $data->NomineeIdImg,
              "DynamicFieldValue" => $data->DynamicFieldValue,
              "created_at" => $created_at[0]->created_at,
              "updated_at" => $updated_at[0]->updated_at,
              "branchcode" => $data->branchcode,
              "projectcode" => $data->projectcode,
              "Occupation" => $Occupation,
              "OccupationId" => $data->Occupation,
              "IsBkash" => $IsBkash,
              "WalletNo" => $data->WalletNo,
              "WalletOwnerId" => $data->WalletOwner,
              "WalletOwner" => $WalletOwner,
              "NomineeName" => $data->NomineeName,
              "PrimaryEarner" => $PrimaryEarner,
              "PrimaryEarnerId" => $data->PrimaryEarner,
              "dochistory_id" => $data->dochistory_id,
              "roleid" => $data->roleid,
              "pin" => $data->pin,
              "action" => $data->action,
              "reciverrole" => $data->reciverrole,
              "status" => $status,
              "statusId" => $data->status,
              "orgno" => $data->orgno,
              "assignedpo" => $data->assignedpo,
              "NomineeNidNo" => $data->NomineeNidNo,
              "NomineeNidTypeId" => $data->NomineeNidType,
              "NomineeNidType" => $NomineeNidType,
              "NomineePhoneNumber" => $data->NomineePhoneNumber,
              "NomineeNidFront" => $data->NomineeNidFront,
              "NomineeNidBack" => $data->NomineeNidBack,
              "PassbookRequired" => $PassbookRequired,
              "IsSameAddress" => $IsSameAddress,
              "entollmentid" => $data->entollmentid,
              "GenderId" => $data->GenderId,
              "Gender" => $genderId,
              "SavingsProductId" => $data->SavingsProductId,
              "role_name" => $role_name,
              "reciverrole_name" => $recieverrole_name,
              "SurveyId" => $data->surveyid,
              "Comment" => $comment,
              "ErpStatus" => $ErpStatus,
              "ErpStatusId" => $ErpStatusId,
              "ErpRejectionReason" => $ErpRejectionReason,
              "Flag" => $data->Flag,
              "refByDropdown" => $data->ref_by_dropdown ?? null,
              'otherReferee' => $data->other_referee ?? null
            );
            //Log::info("PIN" . $cono);
            $admissiondataary[] = $arrayData;
          }
        } else {
          //$admissiondataary = $admissiondata;
        }
        $admissiondatas[] = $admissiondataary;
      }
      // Log::info("Addmission" . $cono . "/" . $admissiondatas);
      return $admissiondatas;
    } else {
      $admissiondataary = array();
      $admissiondata = DB::table($db . '.admissions')->where('branchcode', $BranchCode)->where('projectcode', $ProjectCode)->Where('assignedpo', $Pin)->where("update_at", ">=", $LastSyncTime)->orderBy('id', 'desc')->get();
      if (!empty($admissiondata)) {
        foreach ($admissiondata as $data) {
          $MainIdTypeId = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'cardTypeId')->where('data_id', $data->MainIdTypeId)->first();
          $NomineeNidType = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'cardTypeId')->where('data_id', $data->NomineeNidType)->first();
          $OtherIdTypeId = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'cardTypeId')->where('data_id', $data->OtherIdTypeId)->first();
          $SpouseCardType = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'cardTypeId')->where('data_id', $data->SpouseCardType)->first();
          $EducationId = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'educationId')->where('data_id', $data->EducationId)->first();
          $MaritalStatusIds = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'maritalStatusId')->where('data_id', $data->MaritalStatusId)->first();
          $SpuseOccupationId = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'occupationId')->where('data_id', $data->SpuseOccupationId)->first();
          $RelationshipId = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'relationshipId')->where('data_id', $data->RelationshipId)->first();
          $Occupation = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'occupationId')->where('data_id', $data->Occupation)->first();
          $genderId = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'genderId')->where('data_id', $data->GenderId)->first();
          $PrimaryEarner = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'primaryEarner')->where('data_id', $data->PrimaryEarner)->first();
          $MemberCateogryId = DB::table($db . '.projectwise_member_category')->select('categoryname')->where('categoryid', $data->MemberCateogryId)->first();
          $WalletOwner = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'primaryEarner')->where('data_id', $data->WalletOwner)->first();
          $role_name = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', $data->projectcode)->where('position', $data->roleid)->first();
          $recieverrole_name = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', $data->projectcode)->where('position', $data->reciverrole)->first();
          $dochistory = DB::table($db . '.document_history')->select('comment')->where('id', $data->dochistory_id)->first();
          $status = DB::table($db . '.status')->select('status_name')->where('status_id', $data->status)->first();
          $presentUpazilaId = DB::table($db . '.office_mapping')->select('thana_name')->where('thana_id', $data->presentUpazilaId)->where('district_id', $data->PresentDistrictId)->first();
          $parmanentUpazilaId = DB::table($db . '.office_mapping')->select('thana_name')->where('thana_id', $data->parmanentUpazilaId)->where('district_id', $data->PermanentDistrictId)->first();
          $PresentDistrictId = DB::table($db . '.office_mapping')->select('district_name')->where('district_id', $data->PresentDistrictId)->first();
          $PermanentDistrictId = DB::table($db . '.office_mapping')->select('district_name')->where('district_id', $data->PermanentDistrictId)->first();
          $mstatus = $MaritalStatusIds->data_name ?? null;
          //$MaritalStatusIdsName = $MaritalStatusIds->date_name ?? null;
          $MainIdTypeId = $MainIdTypeId->data_name ?? null;
          $EducationId = $EducationId->data_name ?? null;
          $WalletOwner = $WalletOwner->data_name ?? null;
          $RelationshipId = $RelationshipId->data_name ?? null;
          $Occupation = $Occupation->data_name ?? null;
          $genderId = $genderId->data_name ?? null;
          $PrimaryEarner = $PrimaryEarner->data_name ?? null;
          $MemberCateogryId = $MemberCateogryId->categoryname ?? null;
          $role_name = $role_name->designation ?? null;
          $recieverrole_name = $recieverrole_name->designation ?? null;
          $status = $status->status_name ?? null;
          $NomineeNidType = $NomineeNidType->data_name ?? null;
          $SpuseOccupationId = $SpuseOccupationId->data_name ?? null;
          $SpouseCardType = $SpouseCardType->data_name ?? null;
          $OtherIdTypeId = $OtherIdTypeId->data_name ?? null;
          $presentUpazilaId = $presentUpazilaId->thana_name ?? null;
          $parmanentUpazilaId = $parmanentUpazilaId->thana_name ?? null;
          $PresentDistrictId = $PresentDistrictId->district_name ?? null;
          $PermanentDistrictId = $PermanentDistrictId->district_name ?? null;
          $comment = $dochistory->comment ?? null;
          if ($data->IsBkash == '1') {
            $IsBkash = "Yes";
          } else {
            $IsBkash = "No";
          }
          if ($data->PassbookRequired == '1') {
            $PassbookRequired = "Yes";
          } else {
            $PassbookRequired = "No";
          }
          if ($data->IsSameAddress == '1') {
            $IsSameAddress = "Yes";
          } else {
            $IsSameAddress = "No";
          }
          if ($data->status == '2') {
            if ($data->ErpStatus == '1') {
              $ErpStatus = 'Pending';
            } else if ($data->ErpStatus == '2') {
              $ErpStatus = 'Approved';
            } else if ($data->ErpStatus == '3') {
              $ErpStatus = 'Rejected';
            } else {
              $ErpStatus = 'Pending';
              $ErpStatusId = null;
              $ErpRejectionReason = null;
            }
          } else {
            $ErpStatus = null;
            $ErpStatusId = null;
            $ErpRejectionReason = null;
          }
          //dd($mstatus);
          $arrayData = array(
            "id" => $data->id,
            "IsRefferal" => $data->IsRefferal,
            "RefferedById" => $data->RefferedById,
            "MemberId" => $data->MemberId,
            "MemberCateogryId" => $data->MemberCateogryId,
            "MemberCateogry" => $MemberCateogryId,
            "ApplicantsName" => $data->ApplicantsName,
            "ApplicantSinglePic" => $data->ApplicantSinglePic,
            "MainIdType" => $MainIdTypeId,
            "MainIdTypeId" => $data->MainIdTypeId,
            "IdNo" => $data->IdNo,
            "OtherIdType" => $OtherIdTypeId,
            "OtherIdTypeId" => $data->OtherIdTypeId,
            "OtherIdNo" => $data->OtherIdNo,
            "ExpiryDate" => $data->ExpiryDate,
            "IssuingCountry" => $data->IssuingCountry,
            "DOB" => $data->DOB,
            "MotherName" => $data->MotherName,
            "FatherName" => $data->FatherName,
            "Education" => $EducationId,
            "EducationId" => $data->EducationId,
            "Phone" => $data->Phone,
            "PresentAddress" => $data->PresentAddress,
            "presentUpazilaId" => $data->presentUpazilaId,
            "presentUpazila" => $presentUpazilaId,
            "PermanentAddress" => $data->PermanentAddress,
            "parmanentUpazilaId" => $data->parmanentUpazilaId,
            "PresentDistrictId" => $data->PresentDistrictId,
            "PresentDistrictName" => $PresentDistrictId,
            // "PresentDistrict" => $PresentDistrictId,
            "PermanentDistrictId" => $data->PermanentDistrictId,
            "PermanentDistrictName" => $PermanentDistrictId,
            // "PermanentDistrict" => $PermanentDistrict,
            "parmanentUpazila" => $parmanentUpazilaId,
            "MaritalStatusId" => $data->MaritalStatusId,
            "MaritalStatus" => $mstatus,
            "SpouseName" => $data->SpouseName,
            "SpouseCardType" => $SpouseCardType,
            "SpouseCardTypeId" => $data->SpouseCardType,
            "SpouseNidOrBid" => $data->SpouseNidOrBid,
            "SposeDOB" => $data->SposeDOB,
            "SpuseOccupationId" => $data->SpuseOccupationId,
            "SpuseOccupation" => $SpuseOccupationId,
            "SpouseNidFront" => $data->SpouseNidFront,
            "SpouseNidBack" => $data->SpouseNidBack,
            "ReffererName" => $data->ReffererName,
            "ReffererPhone" => $data->ReffererPhone,
            "FamilyMemberNo" => $data->FamilyMemberNo,
            "NoOfChildren" => $data->NoOfChildren,
            "NomineeDOB" => $data->NomineeDOB,
            "RelationshipId" => $data->RelationshipId,
            "Relationship" => $RelationshipId,
            "ApplicantCpmbinedImg" => $data->ApplicantCpmbinedImg,
            "ReffererImg" => $data->ReffererImg,
            "ReffererIdImg" => $data->ReffererIdImg,
            "FrontSideOfIdImg" => $data->FrontSideOfIdImg,
            "BackSideOfIdimg" => $data->BackSideOfIdimg,
            "NomineeIdImg" => $data->NomineeIdImg,
            "DynamicFieldValue" => $data->DynamicFieldValue,
            "created_at" => date('Y-m-d', strtotime($data->created_at)),
            "updated_at" => date('Y-m-d', strtotime($data->updated_at)),
            "branchcode" => $data->branchcode,
            "projectcode" => $data->projectcode,
            "Occupation" => $Occupation,
            "OccupationId" => $data->Occupation,
            "IsBkash" => $IsBkash,
            "WalletNo" => $data->WalletNo,
            "WalletOwnerId" => $data->WalletOwner,
            "WalletOwner" => $WalletOwner,
            "NomineeName" => $data->NomineeName,
            "PrimaryEarner" => $PrimaryEarner,
            "PrimaryEarnerId" => $data->PrimaryEarner,
            "dochistory_id" => $data->dochistory_id,
            "roleid" => $data->roleid,
            "pin" => $data->pin,
            "action" => $data->action,
            "reciverrole" => $data->reciverrole,
            "status" => $status,
            "statusId" => $data->status,
            "orgno" => $data->orgno,
            "assignedpo" => $data->assignedpo,
            "NomineeNidNo" => $data->NomineeNidNo,
            "NomineeNidTypeId" => $data->NomineeNidType,
            "NomineeNidType" => $NomineeNidType,
            "NomineePhoneNumber" => $data->NomineePhoneNumber,
            "NomineeNidFront" => $data->NomineeNidFront,
            "NomineeNidBack" => $data->NomineeNidBack,
            "PassbookRequired" => $PassbookRequired,
            "IsSameAddress" => $IsSameAddress,
            "entollmentid" => $data->entollmentid,
            "GenderId" => $data->GenderId,
            "Gender" => $genderId,
            "SavingsProductId" => $data->SavingsProductId,
            "role_name" => $role_name,
            "reciverrole_name" => $recieverrole_name,
            "SurveyId" => $data->surveyid,
            "Comment" => $comment,
            "ErpStatus" => $ErpStatus,
            "ErpStatusId" => $ErpStatusId,
            "ErpRejectionReason" => $ErpRejectionReason,
            "Flag" => $data->Flag,
            "refByDropdown" => $data->ref_by_dropdown ?? null,
            'otherReferee' => $data->other_referee ?? null
          );
          $admissiondataary[] = $arrayData;
        }
      } else {
        $admissiondataary = $admissiondata;
      }
      return $admissiondataary;
    }
  }
  public function LoanDataSync($db, $dberp, $BranchCode, $ProjectCode, $Pin, $LastSyncTime, $Appid)
  {
    $type = "Loan";
    $jsonData = "Loan ERP Status IN";
    $this->LogCreate($BranchCode, $ProjectCode, $Pin, $type, $jsonData);
    //$this->Loan_GetDataForErpStatusCheck($db, $dberp, $BranchCode, $ProjectCode, $Pin, $LastSyncTime, $Appid);
    $jsonData = "Loan ERP Status OUT";
    $this->LogCreate($BranchCode, $ProjectCode, $Pin, $type, $jsonData);
    $jsonData = "Loan Qsoft In";
    $this->LogCreate($BranchCode, $ProjectCode, $Pin, $type, $jsonData);

    if ($Appid == 'bmsmerp') {
      //dd("huda");
      $dataset = array();
      $loandatas = array();
      $polist = $this->PoList($db, $BranchCode, $ProjectCode, $Pin);
      //dd($polist);
      foreach ($polist as $row) {
        $cono = $row->cono;
        $dataset = array();
        $loandata = DB::table($db . '.loans')->where('branchcode', $BranchCode)->where('projectcode', $ProjectCode)->where('assignedpo', $cono)->where('update_at', '>=', $LastSyncTime)->orderBy('id', 'desc')->get();
        if (!empty($loandata)) {
          foreach ($loandata as $data) {
            $grntorRlationClient = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'relationshipId')->where('data_id', $data->grntor_rlationClient)->first();
            $investSector = DB::table($db . '.schemem_sector_subsector')->select('sectorname')->where('sectorid', $data->invest_sector)->first();
            $subSectorId = DB::table($db . '.schemem_sector_subsector')->select('subsectorname')->where('subsectorid', $data->subSectorId)->first();
            $frequencyId = DB::table($db . '.product_details')->select('frequency')->where('frequencyid', $data->frequencyId)->first();
            $scheme = DB::table($db . '.schemem_sector_subsector')->select('schemename')->where('schemeid', $data->scheme)->first();
            $role_name = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', $ProjectCode)->where('position', $data->roleid)->first();
            $recieverrole_name = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', $ProjectCode)->where('position', $data->reciverrole)->first();
            $memberTypeId = DB::table($db . '.projectwise_member_category')->select('categoryname')->where('categoryid', $data->memberTypeId)->first();
            $loan_product_name = DB::table($db . '.product_project_member_category')->select('productname', 'productcode')->where('productid', $data->loan_product)->first();
            // dd($grntorRlationClient);
            $grntorRlationClients = $grntorRlationClient->data_name ?? null;
            $investSectors = $investSector->sectorname ?? null;
            $subSectorIds = $subSectorId->subsectorname ?? null;
            $frequencyIds = $frequencyId->frequency ?? null;
            $schemes = $scheme->schemename ?? null;
            $role_names = $role_name->designation ?? null;
            $recieverrole_names = $recieverrole_name->designation ?? null;
            $memberTypeIds = $memberTypeId->categoryname ?? null;
            $loan_product_names = $loan_product_name->productname ?? null;
            $loan_product_code = $loan_product_name->productcode ?? null;
            // dd($data->insurn_gender);
            if ($data->insurn_gender != null) {
              $InsurnGender = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'genderId')->where('data_id', $data->insurn_gender)->first();
              $insurnGender = $InsurnGender->data_name ?? null;
            } else {
              $insurnGender = null;
            }
            // dd($insurnGender);
            if ($data->insurn_relation != null) {
              $InsurnRelation = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'relationshipId')->where('data_id', $data->insurn_relation)->first();
              $insurnRelation = $InsurnRelation->data_name;
            } else {
              $insurnRelation = null;
            }
            if ($data->insurn_mainIDType != null) {
              $insurnMainID = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'cardTypeId')->where('data_id', $data->insurn_mainIDType)->first();
              $insurnMainIDType = $insurnMainID->data_name;
            } else {
              $insurnMainIDType = null;
            }
            $status = DB::table($db . '.status')->select('status_name')->where('status_id', $data->status)->first();

            $serverurl = $this->ServerURL($db);
            $urlindex = $serverurl[0];
            $urlindex1 = $serverurl[1];
            if ($urlindex != '' or $urlindex1 != '') {
              $url = $urlindex;
              $url2 = $urlindex1;
            } else {
              $statuss = array("status" => "CUSTMSG", "message" => "Api Url Not Found");
              $json = json_encode($statuss);
              echo $json;
              die;
            }
            $key = '5d0a4a85-df7a-scapi-bits-93eb-145f6a9902ae';
            $UpdatedAt = "2000-01-01 00:00:00";
            $token = $this->TokenCheck();
            if ($token != '') {
              $headers = array(
                "Content-Type: application/json",
                "Authorization: Bearer " . $token
              );
            } else {
              $statuss = array("status" => "CUSTMSG", "message" => "Token Not Found");
              $json = json_encode($statuss);
              echo $json;
              die;
            }
            // Log::info("Token" . $token);
            /* $member = Http::get($url . 'MemberList', [
              'BranchCode' => $data->branchcode,
              'CONo' => $data->assignedpo,
              'ProjectCode' => $data->projectcode,
              'UpdatedAt' => $UpdatedAt,
              'Status' => 1,
              'OrgNo' => $data->orgno,
              'OrgMemNo' => $data->orgmemno,
              'key' => $key
            ]);
            // dd($member);
            $member = $member->object();
            if ($member != null) {
              if ($member->data != null) {
                $member = $member->data[0];
              } else {
                $member = null;
              }
            } else {
              $member = null;
            }*/

            if ($data->status == '2') {
              $checkPostedLoan = DB::table($db . '.posted_loan')->where('loan_id', $data->loan_id)->first();
              if ($checkPostedLoan != null) {
                $ErpStatusId = $checkPostedLoan->loanproposalstatusid;
                if ($ErpStatusId == 1) {
                  $ErpStatus = 'Pending';
                } elseif ($ErpStatusId == 2) {
                  $ErpStatus = 'Approved';
                } elseif ($ErpStatusId == 3) {
                  $ErpStatus = 'Rejected';
                } elseif ($ErpStatusId == 4) {
                  $ErpStatus = 'Disbursed';
                }
                $ErpRejectionReason = $checkPostedLoan->rejectionreason;
              } else {
                $ErpStatus = 'Pending';
                $ErpStatusId = null;
                $ErpRejectionReason = null;
              }
            } else {
              $ErpStatus = null;
              $ErpStatusId = null;
              $ErpRejectionReason = null;
            }
            $dochistory = DB::table($db . '.document_history')->select('comment')->where('id', $data->dochistory_id)->first();


            if ($data->witness_knows == "1") {
              $witnesKnows = "Yes";
            } else {
              $witnesKnows = "No";
            }
            if ($data->insurn_type == "1") {
              $insurnType = "Single";
            } else {
              $insurnType = "Double";
            }
            if ($data->insurn_option == "1") {
              $insurnOption = "Existing";
            } elseif ($data->insurn_option == "2") {
              $insurnOption = "New";
            } else {
              $insurnOption = null;
            }
            if ($data->houseowner_knows == "1") {
              $houseownerKnows = "Yes";
            } else {
              $houseownerKnows = "No";
            }

            $time = date('Y-m-d', strtotime($data->time));
            if ($data->approval_amount != '' or $data->approval_amount != null) {
              if ($data->approval_amount != 0) {
                $approvalAmount = $data->approval_amount;
              } else {
                $approvalAmount = $data->propos_amt;
              }
            } else {
              $approvalAmount = $data->propos_amt;
            }
            $arrayData['loan'] = array(
              "id" => $data->id,
              "orgno" => $data->orgno,
              "branchcode" => $data->branchcode,
              "projectcode" => $data->projectcode,
              "loan_product_id" => $data->loan_product, //previous loan_product
              "loan_product_name" => $loan_product_names,
              "loan_product_code" => $loan_product_code,
              "loan_duration" => $data->loan_duration,
              "invest_sector_id" => $data->invest_sector,
              "invest_sector" => $investSectors,
              "scheme_id" => $data->scheme,
              "scheme" => $schemes,
              "propos_amt" => $data->propos_amt,
              "instal_amt" => $data->instal_amt,
              "bracloan_family" => $data->bracloan_family,
              "vo_leader" => $data->vo_leader,
              "recommender" => $data->recommender,
              "grntor_name" => $data->grntor_name,
              "grntor_phone" => $data->grntor_phone,
              "grntor_rlationClient" => $grntorRlationClients,
              "grntor_rlationClientId" => $data->grntor_rlationClient,
              "grntor_nid" => $data->grntor_nid,
              "witness_knows" => $witnesKnows,
              "residence_type" => $data->residence_type,
              "residence_duration" => $data->residence_duration,
              "houseowner_knows" => $houseownerKnows,
              "reltive_presAddress" => $data->reltive_presAddress,
              "reltive_name" => $data->reltive_name,
              "reltive_phone" => $data->reltive_phone,
              "insurn_type" => $insurnType,
              "insurn_type_id" => $data->insurn_type,
              "insurn_option" => $insurnOption,
              "insurn_option_id" => $data->insurn_option,
              "insurn_spouseName" => $data->insurn_spouseName,
              "insurn_spouseNid" => $data->insurn_spouseNid,
              "insurn_spouseDob" => $data->insurn_spouseDob,
              "insurn_gender" => $insurnGender,
              "insurn_gender_id" => $data->insurn_gender,
              "insurn_relation" => $insurnRelation,
              "insurn_relation_id" => $data->insurn_relation,
              "insurn_name" => $data->insurn_name,
              "insurn_dob" => $data->insurn_dob,
              "insurn_mainID" => $data->insurn_mainID,
              "grantor_nidfront_photo" => $data->grantor_nidfront_photo,
              "grantor_nidback_photo" => $data->grantor_nidback_photo,
              "grantor_photo" => $data->grantor_photo,
              "DynamicFieldValue" => $data->DynamicFieldValue,
              "time" => $time,
              "dochistory_id" => $data->dochistory_id,
              "roleid" => $data->roleid,
              "pin" => $data->pin,
              "reciverrole" => $data->reciverrole,
              "status" => $status->status_name,
              "statusId" => $data->status,
              "action" => $data->action,
              "assignedpo" => $data->assignedpo,

              "bm_repay_loan" => $data->bm_repay_loan,
              "bm_conduct_activity" => $data->bm_conduct_activity,
              "bm_action_required" => $data->bm_action_required,
              "bm_rca_rating" => $data->bm_rca_rating,

              "bm_noofChild" => $data->bm_noofChild,
              "bm_earningMember" => $data->bm_earningMember,
              "bm_duration" => $data->bm_duration,
              "bm_hometown" => $data->bm_hometown,
              "bm_landloard" => $data->bm_landloard,
              "bm_recomand" => $data->bm_recomand,
              "bm_occupation" => $data->bm_occupation,
              "bm_aware" => $data->bm_aware,
              "bm_grantor" => $data->bm_grantor,
              "bm_socialAcecptRating" => $data->bm_socialAcecptRating,
              "bm_grantorRating" => $data->bm_grantorRating,
              "bm_clienthouse" => $data->bm_clienthouse,
              "bm_remarks" => $data->bm_remarks,

              "loan_id" => $data->loan_id,
              "mem_id" => $data->mem_id,
              "erp_mem_id" => $data->erp_mem_id,
              "memberTypeId" => $data->memberTypeId,
              "memberType" => $memberTypeIds,
              "frequencyId" => $data->frequencyId,
              "frequency" => $frequencyIds,
              "subSectorId" => $data->subSectorId,
              "subSector" => $subSectorIds,
              "insurn_mainIDTypeId" => $data->insurn_mainIDType,
              "insurn_mainIDType" => $insurnMainIDType,
              "insurn_id_expire" => $data->insurn_id_expire,
              "insurn_placeofissue" => $data->insurn_placeofissue,
              "ErpHttpStatus" => $data->ErpHttpStatus,
              "ErpErrorMessage" => $data->ErpErrorMessage,
              "ErpErrors" => $data->ErpErrors,
              "erp_loan_id" => $data->erp_loan_id,
              "role_name" => $role_names,
              "reciverrole_name" => $recieverrole_names,
              "SurveyId" => $data->surveyid,
              "amount_inword" => $data->amount_inword,
              "loan_purpose" => $data->loan_purpose,
              "loan_user" => $data->loan_user,
              "loan_type" => $data->loan_type,
              "brac_loancount" => $data->brac_loancount,
              "Comment" => $dochistory->comment ?? null,
              "ErpStatus" => $ErpStatus,
              "ErpStatusId" => $ErpStatusId,
              "ErpRejectionReason" => $ErpRejectionReason,
              "orgmemno" => $data->orgmemno,
              "approval_amount" => $approvalAmount
            );
            // $data['loan']=$loanArrayData;
            $rca = DB::table($db . '.rca')->where('loan_id', $data->id)->first();
            //echo $data->id . "/" . $rca->primary_earner;
            $PrimaryEarner = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'primaryEarner')->where('data_id', $rca->primary_earner)->first();
            $bmPrimaryEarner = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'primaryEarner')->where('data_id', $rca->bm_primary_earner)->first();
            if ($bmPrimaryEarner) {
              $bmPrimaryEarnerIs = $bmPrimaryEarner->data_name ?? null;
            } else {
              $bmPrimaryEarnerIs = null;
            }
            $arrayData['rca'] = array(
              "id" => $rca->id,
              "loan_id" => $rca->loan_id,
              "primary_earner_id" => $rca->primary_earner,
              "primary_earner" => $PrimaryEarner->data_name ?? null,
              "monthlyincome_main" => $rca->monthlyincome_main,
              "monthlyincome_other" => $rca->monthlyincome_other,
              "house_rent" => $rca->house_rent,
              "food" => $rca->food,
              "education" => $rca->education,
              "medical" => $rca->medical,
              "festive" => $rca->festive,
              "utility" => $rca->utility,
              "saving" => $rca->saving,
              "other" => $rca->other,
              "monthly_instal" => $rca->monthly_instal,
              "debt" => $rca->debt,
              "monthly_cash" => $rca->monthly_cash,
              "instal_proposloan" => $rca->instal_proposloan,
              "time" => $rca->time,
              "DynamicFieldValue" => $rca->DynamicFieldValue,
              "bm_primary_earner" => $bmPrimaryEarnerIs,
              "bm_monthlyincome_main" => $rca->bm_monthlyincome_main,
              "bm_monthlyincome_other" => $rca->bm_monthlyincome_other,
              "bm_house_rent" => $rca->bm_house_rent,
              "bm_food" => $rca->bm_food,
              "bm_education" => $rca->bm_education,
              "bm_medical" => $rca->bm_medical,
              "bm_festive" => $rca->bm_festive,
              "bm_utility" => $rca->bm_utility,
              "bm_saving" => $rca->bm_saving,
              "bm_other" => $rca->bm_other,
              "bm_monthly_instal" => $rca->bm_monthly_instal,
              "bm_debt" => $rca->bm_debt,
              "bm_monthly_cash" => $rca->bm_monthly_cash,
              "bm_instal_proposloan" => $rca->bm_instal_proposloan,
              "bm_monthlyincome_spouse_child" => $rca->bm_monthlyincome_spouse_child,
              "monthlyincome_spouse_child" => $rca->monthlyincome_spouse_child,
              "po_seasonal_income"  => $rca->po_seasonal_income,
              "bm_seasonal_income"  => $rca->bm_seasonal_income,
              "po_incomeformfixedassets" => $rca->po_incomeformfixedassets,
              "bm_incomeformfixedassets" => $rca->bm_incomeformfixedassets,
              "po_imcomeformsavings" => $rca->po_imcomeformsavings,
              "bm_imcomeformsavings" => $rca->bm_imcomeformsavings,
              "po_houseconstructioncost" => $rca->po_houseconstructioncost,
              "bm_houseconstructioncost" => $rca->bm_houseconstructioncost,
              "po_expendingonmarriage" => $rca->po_expendingonmarriage,
              "bm_expendingonmarriage" => $rca->bm_expendingonmarriage,
              "po_operation_childBirth" => $rca->po_operation_childBirth,
              "bm_operation_childBirth" => $rca->bm_operation_childBirth,
              "po_foreigntravel" => $rca->po_foreigntravel,
              "bm_foreigntravel" => $rca->bm_foreigntravel
            );
            // $arrayData['clientInfo'] = $member;
            $dataset[] = $arrayData;
          }
        } else {
          //$dataset = [];
        }
        $loandatas[] = $dataset;
      }
      return $loandatas;
    } else {
      $dataset = array();
      $loandata = DB::table($db . '.loans')->where('branchcode', $BranchCode)->where('projectcode', $ProjectCode)->where('assignedpo', $Pin)->where('update_at', '>=', $LastSyncTime)->orderBy('id', 'desc')->get();
      if (!empty($loandata)) {
        foreach ($loandata as $data) {
          $grntorRlationClient = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'relationshipId')->where('data_id', $data->grntor_rlationClient)->first();
          $investSector = DB::table($db . '.schemem_sector_subsector')->select('sectorname')->where('sectorid', $data->invest_sector)->first();
          $subSectorId = DB::table($db . '.schemem_sector_subsector')->select('subsectorname')->where('subsectorid', $data->subSectorId)->first();
          $frequencyId = DB::table($db . '.product_details')->select('frequency')->where('frequencyid', $data->frequencyId)->first();
          $scheme = DB::table($db . '.schemem_sector_subsector')->select('schemename')->where('schemeid', $data->scheme)->first();
          $role_name = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', $ProjectCode)->where('position', $data->roleid)->first();
          $recieverrole_name = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', $ProjectCode)->where('position', $data->reciverrole)->first();
          $memberTypeId = DB::table($db . '.projectwise_member_category')->select('categoryname')->where('categoryid', $data->memberTypeId)->first();
          $loan_product_name = DB::table($db . '.product_project_member_category')->select('productname', 'productcode')->where('productid', $data->loan_product)->first();

          $grntorRlationClients = $grntorRlationClient->data_name ?? null;
          $investSectors = $investSector->sectorname ?? null;
          $subSectorIds = $subSectorId->subsectorname ?? null;
          $frequencyIds = $frequencyId->frequency ?? null;
          $schemes = $scheme->schemename ?? null;
          $role_names = $role_name->designation ?? null;
          $recieverrole_names = $recieverrole_name->designation ?? null;
          $memberTypeIds = $memberTypeId->categoryname ?? null;
          $loan_product_names = $loan_product_name->productname ?? null;
          $loan_product_code = $loan_product_name->productcode ?? null;
          if ($data->insurn_gender != null) {
            $InsurnGender = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'genderId')->where('data_id', $data->insurn_gender)->first();
            $insurnGender = $InsurnGender->data_name;
          } else {
            $insurnGender = null;
          }
          //dd($insurnGender);
          if ($data->insurn_relation != null) {
            $InsurnRelation = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'relationshipId')->where('data_id', $data->insurn_relation)->first();
            $insurnRelation = $InsurnRelation->data_name;
          } else {
            $insurnRelation = null;
          }
          //dd($insurnRelation);
          if ($data->insurn_mainIDType != null) {
            $insurnMainID = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'cardTypeId')->where('data_id', $data->insurn_mainIDType)->first();
            $insurnMainIDType = $insurnMainID->data_name;
          } else {
            $insurnMainIDType = null;
          }
          // dd($insurnMainIDType);
          $status = DB::table($db . '.status')->select('status_name')->where('status_id', $data->status)->first();

          $serverurl = $this->ServerURL($db);
          $urlindex = $serverurl[0];
          $urlindex1 = $serverurl[1];
          if ($urlindex != '' or $urlindex1 != '') {
            $url = $urlindex;
            $url2 = $urlindex1;
          } else {
            $statuss = array("status" => "CUSTMSG", "message" => "Api Url Not Found");
            $json = json_encode($statuss);
            echo $json;
            die;
          }
          $key = '5d0a4a85-df7a-scapi-bits-93eb-145f6a9902ae';
          $UpdatedAt = "2000-01-01 00:00:00";
          $token = $this->TokenCheck();
          if ($token != '') {
            $headers = array(
              "Content-Type: application/json",
              "Authorization: Bearer " . $token
            );
          } else {
            $statuss = array("status" => "CUSTMSG", "message" => "Token Not Found");
            $json = json_encode($statuss);
            echo $json;
            die;
          }
          //Log::info("Token" . $token);
          /* $member = Http::get($url . 'MemberList', [
            'BranchCode' => $data->branchcode,
            'CONo' => $data->assignedpo,
            'ProjectCode' => $data->projectcode,
            'UpdatedAt' => $UpdatedAt,
            'Status' => 1,
            'OrgNo' => $data->orgno,
            'OrgMemNo' => $data->orgmemno,
            'key' => $key
          ]);
          // dd($member);
          $member = $member->object();
          if ($member != null) {
            if ($member->data != null) {
              $member = $member->data[0];
            } else {
              $member = null;
            }
          } else {
            $member = null;
          }*/

          if ($data->status == '2') {
            $checkPostedLoan = DB::table($db . '.posted_loan')->where('loan_id', $data->loan_id)->first();
            if ($checkPostedLoan != null) {
              $ErpStatusId = $checkPostedLoan->loanproposalstatusid;
              if ($ErpStatusId == 1) {
                $ErpStatus = 'Pending';
              } elseif ($ErpStatusId == 2) {
                $ErpStatus = 'Approved';
              } elseif ($ErpStatusId == 3) {
                $ErpStatus = 'Rejected';
              } elseif ($ErpStatusId == 4) {
                $ErpStatus = 'Disbursed';
              }
              $ErpRejectionReason = $checkPostedLoan->rejectionreason;
            } else {
              $ErpStatus = 'Pending';
              $ErpStatusId = null;
              $ErpRejectionReason = null;
            }
          } else {
            $ErpStatus = null;
            $ErpStatusId = null;
            $ErpRejectionReason = null;
          }
          $dochistory = DB::table($db . '.document_history')->select('comment')->where('id', $data->dochistory_id)->first();


          if ($data->witness_knows == "1") {
            $witnesKnows = "Yes";
          } else {
            $witnesKnows = "No";
          }
          if ($data->insurn_type == "1") {
            $insurnType = "Single";
          } else {
            $insurnType = "Double";
          }
          if ($data->insurn_option == "1") {
            $insurnOption = "Existing";
          } elseif ($data->insurn_option == "2") {
            $insurnOption = "New";
          } else {
            $insurnOption = null;
          }
          if ($data->houseowner_knows == "1") {
            $houseownerKnows = "Yes";
          } else {
            $houseownerKnows = "No";
          }

          $time = date('Y-m-d', strtotime($data->time));

          $arrayData['loan'] = array(
            "id" => $data->id,
            "orgno" => $data->orgno,
            "branchcode" => $data->branchcode,
            "projectcode" => $data->projectcode,
            "loan_product_id" => $data->loan_product, //previous loan_product
            "loan_product_name" => $loan_product_names,
            "loan_product_code" => $loan_product_code,
            "loan_duration" => $data->loan_duration,
            "invest_sector_id" => $data->invest_sector,
            "invest_sector" => $investSectors,
            "scheme_id" => $data->scheme,
            "scheme" => $schemes,
            "propos_amt" => $data->propos_amt,
            "instal_amt" => $data->instal_amt,
            "bracloan_family" => $data->bracloan_family,
            "vo_leader" => $data->vo_leader,
            "recommender" => $data->recommender,
            "grntor_name" => $data->grntor_name,
            "grntor_phone" => $data->grntor_phone,
            "grntor_rlationClient" => $grntorRlationClients,
            "grntor_rlationClientId" => $data->grntor_rlationClient,
            "grntor_nid" => $data->grntor_nid,
            "witness_knows" => $witnesKnows,
            "residence_type" => $data->residence_type,
            "residence_duration" => $data->residence_duration,
            "houseowner_knows" => $houseownerKnows,
            "reltive_presAddress" => $data->reltive_presAddress,
            "reltive_name" => $data->reltive_name,
            "reltive_phone" => $data->reltive_phone,
            "insurn_type" => $insurnType,
            "insurn_type_id" => $data->insurn_type,
            "insurn_option" => $insurnOption,
            "insurn_option_id" => $data->insurn_option,
            "insurn_spouseName" => $data->insurn_spouseName,
            "insurn_spouseNid" => $data->insurn_spouseNid,
            "insurn_spouseDob" => $data->insurn_spouseDob,
            "insurn_gender" => $insurnGender,
            "insurn_gender_id" => $data->insurn_gender,
            "insurn_relation" => $insurnRelation,
            "insurn_relation_id" => $data->insurn_relation,
            "insurn_name" => $data->insurn_name,
            "insurn_dob" => $data->insurn_dob,
            "insurn_mainID" => $data->insurn_mainID,
            "grantor_nidfront_photo" => $data->grantor_nidfront_photo,
            "grantor_nidback_photo" => $data->grantor_nidback_photo,
            "grantor_photo" => $data->grantor_photo,
            "DynamicFieldValue" => $data->DynamicFieldValue,
            "time" => $time,
            "dochistory_id" => $data->dochistory_id,
            "roleid" => $data->roleid,
            "pin" => $data->pin,
            "reciverrole" => $data->reciverrole,
            "status" => $status->status_name,
            "statusId" => $data->status,
            "action" => $data->action,
            "assignedpo" => $data->assignedpo,

            "bm_repay_loan" => $data->bm_repay_loan,
            "bm_conduct_activity" => $data->bm_conduct_activity,
            "bm_action_required" => $data->bm_action_required,
            "bm_rca_rating" => $data->bm_rca_rating,

            "bm_noofChild" => $data->bm_noofChild,
            "bm_earningMember" => $data->bm_earningMember,
            "bm_duration" => $data->bm_duration,
            "bm_hometown" => $data->bm_hometown,
            "bm_landloard" => $data->bm_landloard,
            "bm_recomand" => $data->bm_recomand,
            "bm_occupation" => $data->bm_occupation,
            "bm_aware" => $data->bm_aware,
            "bm_grantor" => $data->bm_grantor,
            "bm_socialAcecptRating" => $data->bm_socialAcecptRating,
            "bm_grantorRating" => $data->bm_grantorRating,
            "bm_clienthouse" => $data->bm_clienthouse,
            "bm_remarks" => $data->bm_remarks,

            "loan_id" => $data->loan_id,
            "mem_id" => $data->mem_id,
            "erp_mem_id" => $data->erp_mem_id,
            "memberTypeId" => $data->memberTypeId,
            "memberType" => $memberTypeIds,
            "frequencyId" => $data->frequencyId,
            "frequency" => $frequencyIds,
            "subSectorId" => $data->subSectorId,
            "subSector" => $subSectorIds,
            "insurn_mainIDTypeId" => $data->insurn_mainIDType,
            "insurn_mainIDType" => $insurnMainIDType,
            "insurn_id_expire" => $data->insurn_id_expire,
            "insurn_placeofissue" => $data->insurn_placeofissue,
            "ErpHttpStatus" => $data->ErpHttpStatus,
            "ErpErrorMessage" => $data->ErpErrorMessage,
            "ErpErrors" => $data->ErpErrors,
            "erp_loan_id" => $data->erp_loan_id,
            "role_name" => $role_names,
            "reciverrole_name" => $recieverrole_names,
            "SurveyId" => $data->surveyid,
            "amount_inword" => $data->amount_inword,
            "loan_purpose" => $data->loan_purpose,
            "loan_user" => $data->loan_user,
            "loan_type" => $data->loan_type,
            "brac_loancount" => $data->brac_loancount,
            "Comment" => $dochistory->comment ?? null,
            "ErpStatus" => $ErpStatus,
            "ErpStatusId" => $ErpStatusId,
            "ErpRejectionReason" => $ErpRejectionReason,
            "orgmemno" => $data->orgmemno,
            "approval_amount" => $data->approval_amount
          );
          //dd("te");
          // $data['loan']=$loanArrayData;
          $rca = DB::table($db . '.rca')->where('loan_id', $data->id)->first();
          $PrimaryEarner = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'primaryEarner')->where('data_id', $rca->primary_earner)->first();
          $bmPrimaryEarner = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'primaryEarner')->where('data_id', $rca->bm_primary_earner)->first();
          if ($bmPrimaryEarner) {
            $bmPrimaryEarnerIs = $bmPrimaryEarner->data_name;
          } else {
            $bmPrimaryEarnerIs = null;
          }

          //  dd($bmPrimaryEarnerIs);
          $arrayData['rca'] = array(
            "id" => $rca->id,
            "loan_id" => $rca->loan_id,
            "primary_earner" => $PrimaryEarner->data_name ?? null,
            "monthlyincome_main" => $rca->monthlyincome_main,
            "monthlyincome_other" => $rca->monthlyincome_other,
            "house_rent" => $rca->house_rent,
            "food" => $rca->food,
            "education" => $rca->education,
            "medical" => $rca->medical,
            "festive" => $rca->festive,
            "utility" => $rca->utility,
            "saving" => $rca->saving,
            "other" => $rca->other,
            "monthly_instal" => $rca->monthly_instal,
            "debt" => $rca->debt,
            "monthly_cash" => $rca->monthly_cash,
            "instal_proposloan" => $rca->instal_proposloan,
            "time" => $rca->time,
            "DynamicFieldValue" => $rca->DynamicFieldValue,
            "bm_primary_earner" => $bmPrimaryEarnerIs,
            "bm_monthlyincome_main" => $rca->bm_monthlyincome_main,
            "bm_monthlyincome_other" => $rca->bm_monthlyincome_other,
            "bm_house_rent" => $rca->bm_house_rent,
            "bm_food" => $rca->bm_food,
            "bm_education" => $rca->bm_education,
            "bm_medical" => $rca->bm_medical,
            "bm_festive" => $rca->bm_festive,
            "bm_utility" => $rca->bm_utility,
            "bm_saving" => $rca->bm_saving,
            "bm_other" => $rca->bm_other,
            "bm_monthly_instal" => $rca->bm_monthly_instal,
            "bm_debt" => $rca->bm_debt,
            "bm_monthly_cash" => $rca->bm_monthly_cash,
            "bm_instal_proposloan" => $rca->bm_instal_proposloan,
            "bm_monthlyincome_spouse_child" => $rca->bm_monthlyincome_spouse_child,
            "monthlyincome_spouse_child" => $rca->monthlyincome_spouse_child,
            "po_seasonal_income"  => $rca->po_seasonal_income,
            "bm_seasonal_income"  => $rca->bm_seasonal_income,
            "po_incomeformfixedassets" => $rca->po_incomeformfixedassets,
            "bm_incomeformfixedassets" => $rca->bm_incomeformfixedassets,
            "po_imcomeformsavings" => $rca->po_imcomeformsavings,
            "bm_imcomeformsavings" => $rca->bm_imcomeformsavings,
            "po_houseconstructioncost" => $rca->po_houseconstructioncost,
            "bm_houseconstructioncost" => $rca->bm_houseconstructioncost,
            "po_expendingonmarriage" => $rca->po_expendingonmarriage,
            "bm_expendingonmarriage" => $rca->bm_expendingonmarriage,
            "po_operation_childBirth" => $rca->po_operation_childBirth,
            "bm_operation_childBirth" => $rca->bm_operation_childBirth,
            "po_foreigntravel" => $rca->po_foreigntravel,
            "bm_foreigntravel" => $rca->bm_foreigntravel
          );
          // $arrayData['clientInfo'] = $member;
          $dataset[] = $arrayData;
        }
      } else {
        $dataset = [];
      }
      return $dataset;
    }
  }
  public function TokenCheck()
  {
    //$token = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE2NjI1MjM3MTIsImV4cCI6MTY2MjUyNzMxMiwic3ViIjoiUXNvZnQiLCJpc3MiOiJlcnAuYnJhYy5uZXQiLCJqdGkiOiIxXzQzd2M0MWhlbjdjd2cwc2c0czA0NGMwc2NjOHdjazRvIiwiYXVkIjoiMzcuMTExLjIxNS41In0.Peab_VgG3sBaALINiTMFO9E0He-re_d3hXxz8uP0Bj6JqxiBEqq5v3c9cjQ61YyhjTW2gVowmdqfz0ok31PKfX99_3ThYUe6AZj40LqUdvPLS-_UTimjvqaDs2X7j6tA23xpPSn1cnoS9MsfhZ2Iu_vt2Vwbi3V1RLeVZCh1YEM';
    //$token = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE2NTQ1NzkwNTgsImV4cCI6MTY1NDU4MjY1OCwic3ViIjoiZGNzLWFwaS10ZXN0IiwiaXNzIjoiYnJhY2FwaXRlc3RpbmcuYnJhYy5uZXQiLCJqdGkiOiJJZWcxTjVXMnFoM2hGMHFTOVpoMndxNmVleDJEQjkzNSIsImF1ZCI6IjEwLjE0MC4wLjEwNSJ9.WCJdKEdZnVDH8nj_zm6W_uYFSqdj7f9O36hu1dUVTJ3gpf-xMbEhwZCk-xYlEUx-ltTN0rYW3cEAXlt_iSuUULIs6RnARl_g43QrsQte7zZOmBhRpQcjVMDvMjD1YP1yqDc3jqvv-oTBvvk_mNGw4u9Ghxpe3diWbNSnkpZOw2s';
    //return $token;
    //session_start();
    $token = '';
    if (isset($_SESSION["expirtime"])) {
      $time = $_SESSION["expirtime"];
    } else {
      $time = 0;
    }
    if (isset($_SESSION["expirdate"])) {
      $date = $_SESSION["expirdate"];
    } else {
      $date = '2000-01-01';
    }
    $chour = date('h');
    $cdate = date('Y-m-d');
    $totalh = $chour - $time;
    if ($cdate != $date) {
      $totalh = 1;
    }
    if ($totalh > 0) {
      $tokestart =  date('h');
      $_SESSION["expirtime"] = $tokestart;
      $_SESSION["expirdate"] = date('Y-m-d');
      //$clientid = 'Ieg1N5W2qh3hF0qS9Zh2wq6eex2DB935';
      // $clientsecret = '4H2QJ89kYQBStaCuY73h';
      // $clienturl = 'https://bracapitesting.brac.net/oauth/v2/token?grant_type=client_credentials'; //test
      $clienturl = 'https://erp.brac.net/oauth/v2/token?grant_type=client_credentials'; // live
      $header = array( //live 
        'x-client-id:1_43wc41hen7cwg0sg4s044c0scc8wck4o',
        'x-client-secret:654spemp5qckcg4g448044kco4k0g8wwo0440osgwosggwg4'
      );
      /* $header = array( //test
        'x-client-id:Ieg1N5W2qh3hF0qS9Zh2wq6eex2DB935',
        'x-client-secret:4H2QJ89kYQBStaCuY73h'
      );*/

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $clienturl);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
      curl_setopt($ch, CURLOPT_POSTFIELDS, true);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $auth_output = curl_exec($ch);
      //dd($auth_output);
      Log::info("Access Token" . $auth_output);
      $auth = json_decode($auth_output);
      $accesstoken = $auth->access_token;
      $_SESSION["access_token"] = $accesstoken;
    }
    if (isset($_SESSION["access_token"])) {
      $token = $_SESSION["access_token"];
      //echo $token;
    }
    return $token;
    //Log::info('bksh json Check -' . $PIN . "-" . $walletno . "-" . $qsoftids . "-" . $auth_output . "-" . $token);
  }
  public function ServerURL()
  {
    $db = 'dcs';
    $url = '';
    $url2 = '';
    $serverurl = DB::Table($db . '.server_url')->where('server_status', 1)->where('status', 1)->first();
    //dd($serverurl);
    if (empty($serverurl)) {
      $statuss = array("status" => "CUSTMSG", "message" => "Server Api Not Found");
      $json = json_encode($statuss);
      echo $json;
      die;
    } else {
      //dd($serverurl->url);
      $url = $serverurl->url;
      $url2 = $serverurl->url2;
      $servermessage = $serverurl->maintenance_message;
      $serverstatus = $serverurl->maintenance_status;
      if ($serverstatus == '1') {
        $statuss = array("status" => "CUSTMSG", "message" => $servermessage);
        $json = json_encode($statuss);
        echo $json;
        die;
      }
    }
    $urlaray = array($url, $url2);
    return $urlaray;
  }
  public function ZipFileCreate($db, $Pin, $BranchCode, $Appid, $CurrentTimes, $ProjectCode, $jsonFile)
  {
    $jsonData = "Zip File Create IN";
    $type = "Zip";
    $this->LogCreate($BranchCode, $ProjectCode, $Pin, $type, $jsonData);
    $baseurl = '/var/www/html/json/dcs/';
    // echo $baseurl;
    $mainFile = $baseurl . $Pin . 'dcs.zip';
    //echo $mainFile;
    if (is_file($mainFile)) {
      if (!unlink($mainFile)) {
        //   echo ("Error deleting $mainFile");
      }
    }
    $writeFile = $baseurl . $Pin . 'dcsresults.json';
    if (is_file($writeFile)) {
      if (!unlink($writeFile)) {
        //   echo ("Error deleting $mainFile");
      }
    }
    $fp = fopen($baseurl . $Pin  . 'dcsresults.json', 'w');
    fwrite($fp, $jsonFile);
    fclose($fp);
    $zip = new ZipArchive;
    if ($zip->open($mainFile, ZipArchive::CREATE) === TRUE) {
      // Add files to the zip file
      $zip->addFile($writeFile, $Pin  . 'dcsresults.json');
      //$zip->addFile('/var/www/html/json/'.$PIN.'transtrail.json',$PIN.'transtrail.json');
      //$zip->addFile('test.pdf', 'demo_folder1/test.pdf');

      // All files are added, so close the zip file.
      $zip->close();
    }
    if (is_file($writeFile)) {
      if (!unlink($writeFile)) {
        //   echo ("Error deleting $mainFile");
      }
    }
    $message = array("status" => "DCS", "time" => $CurrentTimes, "message" => "Please Download File!!");
    $json2 = json_encode($message);
    $jsonData = "Zip File Create OUT";
    $type = "Zip";
    $this->LogCreate($BranchCode, $ProjectCode, $Pin, $type, $jsonData);
    Log::info("message-" . $json2);
    echo $json2;
    die;
  }
  public function ConfigurationSync($db, $Pin, $BranchCode, $Appid, $CurrentTimes, $ProjectCode, $token)
  {
    $auth_array = [];
    $branchcode = (int)$BranchCode;
    $projectcode = (int)$ProjectCode;
    $cellingData = '';
    if ($token == '7f30f4491cb4435984616d1913e88389') {
      if ($branchcode != null and $projectcode != null) {
        $Process = DB::Table($db . '.processes')->select('id', 'process')->get();
        $FormConfig = DB::Table($db . '.form_configs')->where('projectcode', $ProjectCode)->get();
        $PayloadData = DB::Table($db . '.payload_data')->where('status', 1)->get();
        $OfficeMapping = DB::Table($db . '.office_mapping')->where('status', 1)->get();
        $ProductDetail = DB::Table($db . '.product_details')->get();
        if ($Appid == 'bmsmerp') {
          $cellingData =  DB::Table($db . '.celing_configs')->where('projectcode', $projectcode)->get();
        }

        //dd($cellingData);
        $ProjectwiseMemberCategory = DB::Table($db . '.projectwise_member_category')->where('projectcode', $projectcode)->get();
        //$ProductProjectMemberCategory = DB::Table($db . '.product_project_member_category')->where('projectcode', $projectcode)->where('branchcode', $BranchCode)->orWhere('branchcode', '*')->get();
        $ProductProjectMemberCategory = DB::Table($db . '.product_project_member_category')->where('projectcode', $projectcode)->where(
          function ($query) use ($BranchCode) {
            return $query
              ->where('branchcode', $BranchCode)->orWhere('branchcode', '*');
          }
        )->get();
        $InsuranceProducts = DB::Table($db . '.insurance_products')->where('project_code', $projectcode)->where(
          function ($query) use ($branchcode) {
            return $query
              ->where('branchcode', $branchcode)->orWhere('branchcode', 'All Office');
          }
        )->get();
        $SchememSectorSubsector = DB::Table($db . '.schemem_sector_subsector')->where(
          function ($query) use ($branchcode) {
            return $query
              ->where('branchcode', $branchcode)->orWhere('branchcode', '*');
          }
        )->where('projectcode', $projectcode)->get();
        // echo $ProjectCode;
        $auth = DB::Table($db . '.auths')->where('projectcode', $ProjectCode)->where('roleId', '0')->where('prerequisiteprocessid', '!=', '0')->get();
        //dd($auth);
        if (!$auth->isEmpty()) {
          foreach ($auth as $row) {
            $processname = DB::Table($db . '.processes')->select('process')->where('id', $row->processId)->first();
            $prerequisiteprocessname = DB::Table($db . '.processes')->select('process')->where('id', $row->prerequisiteprocessid)->first();

            $array['processid'] = $row->processId;
            $array['processname'] = $processname->process;
            $array['prerequisiteprocessid'] = $row->prerequisiteprocessid;
            $array['prerequisiteprocessname'] = $prerequisiteprocessname->process;
            $auth_array[] = $array;
          }
        }

        $result = array(
          "Process" => $Process,
          "FormConfig" => $FormConfig,
          "PayloadData" => $PayloadData,
          "OfficeMapping" => $OfficeMapping,
          "ProductDetail" => $ProductDetail,
          "ProjectwiseMemberCategory" => $ProjectwiseMemberCategory,
          "ProductProjectMemberCategory" => $ProductProjectMemberCategory,
          "SchememSectorSubsector" => $SchememSectorSubsector,
          "AuthConfig" => $auth_array,
          "InsuranceProducts" => $InsuranceProducts,
          "cellingConfig" => $cellingData,
        );
        return $result;
      } else {
        $message = "Branchcode Or ProjectCode Not Found!";
        $this->CUSTMSG($message);
      }
    } else {
      $message = "Api Key Not Found!";
      $this->CUSTMSG($message);
    }
  }
  public function getSurveys($db, $BranchCode, $ProjectCode, $Pin, $Appid)
  {
    if ($Appid == 'bmsmerp') {
      $surveydata = DB::table($db . '.surveys')->where('branchcode', $BranchCode)->where('projectcode', $ProjectCode)->orderBy('id', 'desc')->get();
    } else {
      $surveydata = DB::table($db . '.surveys')->where('branchcode', $BranchCode)->where('projectcode', $ProjectCode)->where('assignedpo', $Pin)->orderBy('id', 'desc')->get();
    }
    return $surveydata;
  }
  public function CUSTMSG($message)
  {
    echo json_encode(array("status" => "CUSTMSG", "message" => $message));
    die;
  }
  public function MemberLists($db, $dberp, $BranchCode, $ProjectCode, $Pin, $LastSyncTime, $Appid, $key, $LastSyncTime1)
  {
    //dd("Huda");
    if ($LastSyncTime1 == "2000-01-01 00:00:00") {
      $LastSyncTime = '2000-01-01%2012:00:00';
    }
    //$LastSyncTime = '2000-01-01%2012:00:00';
    $serverul = $this->ServerURL();
    $url = $serverul[0];
    $url2 = $serverul[1];
    $memberlist = array();
    if ($Appid == 'bmsmerp') {
      //dd("t");
      //$polist = DB::Table($dberp . '.polist')->where('branchcode', $BranchCode)->where('projectcode', $ProjectCode)->where('cono', '!=', $Pin)->where('status', 1)->get();
      //dd($polist);
      $polist = $this->PoList($db, $BranchCode, $ProjectCode, $Pin);
      //dd($polist);
      if (!empty($polist)) {

        foreach ($polist as $row) {
          $cono = $row->cono;
          $url4 = $url . "MemberList?BranchCode=$BranchCode&CONo=$cono&ProjectCode=$ProjectCode&UpdatedAt=$LastSyncTime&key=$key&Status=2";
          //dd($url4);
          $url4 = str_replace(" ", '%20', $url4);
          $headers = array(
            'Accept: application/json',
          );

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url4);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_HEADER, false);
          $output_colsed = curl_exec($ch);
          curl_close($ch);
          $decode = json_decode($output_colsed);
          $data =  $decode->data;
          if (!empty($data)) {
            $memberlist[] = $data;
          }


          //echo $output_colsed;
        }
      }
      // dd($memberlist);
      return $memberlist;
    } else {
      $url4 = $url . "MemberList?BranchCode=$BranchCode&CONo=$Pin&ProjectCode=$ProjectCode&UpdatedAt=$LastSyncTime&key=$key&Status=2";
      //dd($url4);
      $url4 = str_replace(" ", '%20', $url4);
      $headers = array(
        'Accept: application/json',
      );

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url4);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HEADER, false);
      $output_colsed = curl_exec($ch);
      curl_close($ch);
      $decode = json_decode($output_colsed);
      $data = $decode->data;
      return $data;
    }
  }
  public function PoList($db, $BranchCode, $ProjectCode, $Pin)
  {

    $polist = DB::Table($db . '.polist')->where('branchcode', $BranchCode)->where('projectcode', $ProjectCode)->where('status', 1)->get();
    //dd($BranchCode."/".$ProjectCode);
    /* $url4 = "http://scm.brac.net/sc/GetPO_Data?branchcode=$BranchCode&projectcode=$ProjectCode";
    //echo $url4;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url4);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $polistooutput = curl_exec($ch);
    curl_close($ch);
    // dd($polistooutput->data);
    $jsonDateDecode = json_decode($polistooutput);
    //dd($jsonDateDecode->data);
    $polistdta = $jsonDateDecode->data;
    if (!empty(($polistdta))) {
      $polist = $polistdta;
    } else {
      $polist = [];
    }*/
    return $polist;
  }
  public function Admission_GetDataForErpStatusCheck($db, $dberp, $BranchCode, $ProjectCode, $Pin, $LastSyncTime, $Appid)
  {
    if ($Appid == 'bmsmerp') {
      $getapplicationdata = DB::Table($db . '.admissions')->where('branchcode', $BranchCode)->where('ErpStatus', 1)->min('created_at');
      if (!empty($getapplicationdata)) {
        $applicationdate1 = $getapplicationdata;
        $applicationdate = date('Y-m-d', strtotime($applicationdate1));
        $this->GetErpPostedAdmissionData($db, $BranchCode, $ProjectCode, $applicationdate);
      }
      /* $getpo = DB::Table($db . '.admissions')->select('assignedpo')->where('branchcode', $BranchCode)->groupBy('assignedpo')->get();
      if ($getpo->isEmpty()) {
        //$this->CUSTMSG("No Data Found for PO of BM");
      } else {
        foreach ($getpo as $row) {
          $Pin = $row->assignedpo;
          $getapplicationdata = DB::Table($db . '.admissions')->select(DB::raw("cast(created_at as date)"))->where('branchcode', $BranchCode)->where('assignedpo', $Pin)->where('ErpStatus', 1)->groupBy(DB::raw("cast(created_at as date)"))->get();
          if (!$getapplicationdata->isEmpty()) {
            foreach ($getapplicationdata as $row) {
              $applicationdate = $row->created_at;
              $this->GetErpPostedAdmissionData($db, $BranchCode, $ProjectCode, $applicationdate);
            }
          }
        }
      }*/
    } else {
      //$getapplicationdata = DB::select(DB::raw("SELECT MIN(created_at) as create_at FROM $db.admissions where branchcode=$BranchCode and 'ErpStatus'=1 and assignedpo=$Pin"));
      $getapplicationdata = DB::Table($db . '.admissions')->where('branchcode', $BranchCode)->where('assignedpo', $Pin)->where('ErpStatus', 1)->min('created_at');
      // dd($getapplicationdata[0]->created_at);
      if (!empty($getapplicationdata)) {
        // dd($getapplicationdata);
        $applicationdate1 = $getapplicationdata;
        $applicationdate = date('Y-m-d', strtotime($applicationdate1));
        //dd($applicationdate);
        // Log::info("AdmissionBuffer" . $applicationdate);
        $this->GetErpPostedAdmissionData($db, $BranchCode, $ProjectCode, $applicationdate);
      }
      /*$getapplicationdata = DB::Table($db . '.admissions')->select(DB::raw("cast(created_at as date)"))->where('branchcode', $BranchCode)->where('assignedpo', $Pin)->where('ErpStatus', 1)->groupBy(DB::raw("cast(created_at as date)"))->get();
      if (!$getapplicationdata->isEmpty()) {
        foreach ($getapplicationdata as $row) {
          $applicationdate = $row->created_at;
          $this->GetErpPostedAdmissionData($db, $BranchCode, $ProjectCode, $applicationdate);
        }
      }*/
    }
  }
  public function GetErpPostedAdmissionData($db, $BranchCode, $ProjectCode, $applicationdate)
  {
    /*$access_token = $this->tokenVerify();
    $clientid = 'Ieg1N5W2qh3hF0qS9Zh2wq6eex2DB935';
    $clientsecret = '4H2QJ89kYQBStaCuY73h';
    $url = 'https://bracapitesting.brac.net/dcs/v1/branches/' . $branchcode . '/buffer-members';

    $headers = array(
      'Authorization: Bearer ' . $access_token,
      'Accept: application/json',
    );*/
    $db = $this->db;
    $serverurl = $this->ServerURL($db);
    $urlindex = $serverurl[0];
    $urlindex1 = $serverurl[1];
    if ($urlindex != '' or $urlindex1 != '') {
      $url = $urlindex;
      $url2 = $urlindex1;
    } else {
      $statuss = array("status" => "CUSTMSG", "message" => "Api Url Not Found");
      $json = json_encode($statuss);
      echo $json;
      die;
    }
    $servertoken = $this->TokenCheck();
    if ($servertoken != '') {
      $headers = array(
        "Content-Type: application/json",
        "Authorization: Bearer " . $servertoken
      );
    } else {
      $statuss = array("status" => "CUSTMSG", "message" => "Token Not Found");
      $json = json_encode($statuss);
      echo $json;
      die;
    }
    //dd($headers);
    $projectcode = (int)$ProjectCode;
    $urlset = $url2 . "branches/$BranchCode/buffer-members?projectCode=$projectcode&applicationDate=$applicationdate";
    Log::info("AdmissionBuffer" . $urlset);
    //echo $urlset;
    // $urlset = $url2 . "branches/$BranchCode/buffer-members";
    // dd($urlset);
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $urlset,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 60,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => $headers,
    ));

    $response = curl_exec($curl);
    //dd($response);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
      return "cURL Error #:" . $err;
    } else {
      json_decode($response);
      if (json_last_error() == 0) {
        return $this->insertPostedAddmissionList($response);
      } else {
        return "Erp Server Down";
      }
    }
  }
  public function insertPostedAddmissionList($response)
  {
    $db = $this->db;
    $currentDatetime = date("Y-m-d h:i:s");
    $currentDatetimes = date("Y-m-d H:i:s");
    $arrayAddmission = json_decode($response);
    Log::info("ERP Posted AdmissionList" . $response);
    //dd($response);
    if (!empty($arrayAddmission)) {
      // dd("Huda");
      foreach ($arrayAddmission as $data) {
        // echo $data->id . "/";
        if ($data->id == '4d3c14ca-50c0-4546-a5ca-a2b3e286e8a7') {
          continue;
        }
        //echo $data->id . "/";
        // if ($data->guarantor != null) {
        // $guarantordateofbirth = $data->guarantor[0]->dateOfBirth;
        // $guarantorbackimageurl = $data->guarantor[0]->idCard->backImageUrl;
        // $guarantorcardtypeid = $data->guarantor[0]->idCard->cardTypeId;
        // $guarantorissueplace = $data->guarantor[0]->idCard->issuePlace;
        // $guarantorexpirydate = $data->guarantor[0]->idCard->expiryDate;
        // $guarantorfrontimageurl = $data->guarantor[0]->idCard->frontImageUrl;
        // $guarantoridcardno = $data->guarantor[0]->idCard->idCardNo;
        // $guarantorissuedate = $data->guarantor[0]->idCard->issueDate;
        // $guarantornameen = $data->guarantor[0]->nameEn;
        // $guarantorrelationshipid = $data->guarantor[0]->relationshipId;
        // } else {

        // }
        $guarantordateofbirth = null;
        $guarantorbackimageurl = null;
        $guarantorcardtypeid = null;
        $guarantorissueplace = null;
        $guarantorexpirydate = null;
        $guarantorfrontimageurl = null;
        $guarantoridcardno = null;
        $guarantorissuedate = null;
        $guarantornameen = null;
        $guarantorrelationshipid = null;
        if ($data->nominees != null or $data->nominees != '') {
          if (isset($data->nominees[0]->contactNo)) {
            $nomineescontactNo = $data->nominees[0]->contactNo;
          } else {
            $nomineescontactNo = null;
          }

          if (isset($data->nominees[0]->dateOfBirth)) {
            $nomineesdateofbirth = $data->nominees[0]->dateOfBirth;
          } else {
            $nomineesdateofbirth = null;
          }
          $nomineesbackimageurl = $data->nominees[0]->idCard->idCardNo;
          $nomineescardtypeid = $data->nominees[0]->idCard->cardTypeId;
          $nomineesexpirydate = $data->nominees[0]->idCard->expiryDate;
          $nomineesfrontimageurl = $data->nominees[0]->idCard->frontImageUrl;
          $nomineesidcardno = $data->nominees[0]->idCard->idCardNo;
          $nomineesissuedate = $data->nominees[0]->idCard->issueDate;
          $nomineesissueplace = $data->nominees[0]->idCard->issuePlace;
          $nomineesname = $data->nominees[0]->name;

          if (array_key_exists('relationshipId', $data->nominees)) {
            $nomineesrelationshipid = $data->nominees->relationshipId;
          } else {
            $nomineesrelationshipid = null;
          }
        } else {
          $nomineescontactNo = null;
          $nomineesdateofbirth = null;
          $nomineesbackimageurl = null;
          $nomineescardtypeid = null;
          $nomineesexpirydate = null;
          $nomineesfrontimageurl = null;
          $nomineesidcardno = null;
          $nomineesissuedate = null;
          $nomineesissueplace = null;
          $nomineesname = null;
          $nomineesrelationshipid = null;
        }

        $values = array(
          'applicationdate' => $data->applicationDate,
          'assignedpopin' => $data->assignedPoPin,
          'bankaccountnumber' => $data->bankAccountNumber,
          'bankbranchid' => $data->bankBranchId,
          'bankid' => $data->bankId,
          'bkashwalletno' => $data->bkashWalletNo,
          'branchcode' => $data->branchCode,
          'contactno' => $data->contactNo,
          'dateofbirth' => $data->dateOfBirth,
          'educationid' => $data->educationId,
          'fathernameen' => $data->fatherNameEn,
          'flag' => $data->flag,
          'genderid' => $data->genderId,
          //guarantor
          "guarantordateofbirth" => $guarantordateofbirth,
          "guarantorbackimageurl" => $guarantorbackimageurl,
          "guarantorcardtypeid" => $guarantorcardtypeid,
          "guarantorissueplace" => $guarantorissueplace,
          "guarantorexpirydate" => $guarantorexpirydate,
          "guarantorfrontimageurl" => $guarantorfrontimageurl,
          "guarantoridcardno" => $guarantoridcardno,
          "guarantorissuedate" => $guarantorissuedate,
          "guarantornameen" => $guarantornameen,
          "guarantorrelationshipid" => $guarantorrelationshipid,
          'addmission_id' => $data->id,
          //idCard
          "idcardbackimageurl" => $data->idCard->backImageUrl,
          "idcardcardtypeid" => $data->idCard->cardTypeId,
          "idcardexpirydate" => $data->idCard->expiryDate,
          "idcardfrontimageurl" => $data->idCard->frontImageUrl,
          "idcardidcardno" => $data->idCard->idCardNo,
          "idcardissuedate" => $data->idCard->issueDate,
          "idcardissueplace" => $data->idCard->issuePlace,
          'maritalstatusid' => $data->maritalStatusId,
          'memberid' => $data->memberId,
          'memberimageurl' => $data->memberImageUrl,
          'membertypeid' => $data->memberTypeId,
          'mothernameen' => $data->motherNameEn,
          'nameen' => $data->nameEn,
          //nominees
          "nomineescontactno" => $nomineescontactNo,
          "nomineesdateofbirth" => $nomineesdateofbirth,
          // "id" => $data->nominees[0]->id,
          "nomineesbackimageurl" => $nomineesbackimageurl,
          "nomineescardtypeid" => $nomineescardtypeid,
          "nomineesexpirydate" => $nomineesexpirydate,
          "nomineesfrontimageurl" => $nomineesfrontimageurl,
          "nomineesidcardno" => $nomineesidcardno,
          "nomineesissuedate" => $nomineesissuedate,
          "nomineesissueplace" => $nomineesissueplace,
          "nomineesname" => $nomineesname,
          "nomineesrelationshipid" => $nomineesrelationshipid,
          'occupationid' => $data->occupationId,
          'passbooknumber' => $data->passbookNumber,
          'permanentaddress' => $data->permanentAddress,
          'permanentdistrictid' => $data->permanentDistrictId,
          'permanentupazilaid' => $data->permanentUpazilaId,
          'poid' => $data->poId,
          'presentaddress' => $data->presentAddress,
          'presentdistrictid' => $data->presentDistrictId,
          'presentupazilaid' => $data->presentUpazilaId,
          'projectcode' => $data->projectCode,
          'rejectionreason' => $data->rejectionReason,
          'routingnumber' => $data->routingNumber,
          'savingsproductid' => $data->savingsProductId,
          'spousedateofbirth' => $data->spouseDateOfBirth,
          // // spouseIdCard
          "spouseidcardbackimageurl" => $data->spouseIdCard->backImageUrl,
          "spouseidcardcardtypeid" => $data->spouseIdCard->cardTypeId,
          "spouseidcardexpirydate" => $data->spouseIdCard->expiryDate,
          "spouseidcardfrontimageurl" => $data->spouseIdCard->frontImageUrl,
          "spouseidcardidcardno" => $data->spouseIdCard->idCardNo,
          "spouseidcardissuedate" => $data->spouseIdCard->issueDate,
          "spouseidcardissueplace" => $data->spouseIdCard->issuePlace,
          'spousenameen' => $data->spouseNameEn,
          'statusid' => $data->statusId,
          'targetamount' => $data->targetAmount,
          'tinnumber' => $data->tinNumber,
          'updated' => $data->updated,
          'vocode' => $data->voCode,
          'void' => $data->voId,
          'admission_id' => $data->id,
        );

        $checkPostedAdmission = DB::table($db . '.posted_admission')->where('admission_id', $data->id)->first();
        $checkAdmission = DB::table($db . '.admissions')->where('entollmentid', $data->id)->first();
        $checkLoan = DB::table($db . '.loans')->where('mem_id', $data->id)->first();

        if ($data->statusId == 2 or $data->statusId == 3) {  //if erp approve and reject
          if ($checkAdmission != null) {                //if addmission has data
            if ($checkAdmission->MemberId == null and $checkAdmission->ErpStatus == 1) {    //if erp member id empty in dcs admission table
              $this->sendAppNotificationForErpAddmissionAction($data);
            }
          }
        }

        //dd($checkPostedAdmission);
        if ($checkPostedAdmission == null) {
          //dd("null" . $data->statusId . "/" . $data->nameEn);
          DB::table($db . '.posted_admission')->insert($values);
          if ($data->statusId == 2) {
            if ($checkAdmission != null) {
              DB::table($db . '.admissions')->where('entollmentid', $data->id)->update(['MemberId' => $data->memberId, 'ErpStatus' => $data->statusId, 'updated_at' => $currentDatetime, 'update_at' => $currentDatetimes]);
            }
            if ($checkLoan != null) {
              DB::table($db . '.loans')->where('mem_id', $data->id)->update(['erp_mem_id' => $data->memberId, 'updated_at' => $currentDatetime, 'update_at' => $currentDatetimes]);
            }
          } elseif ($data->statusId == 3) {
            if ($checkAdmission != null) {
              DB::table($db . '.admissions')->where('entollmentid', $data->id)->update(['ErpStatus' => $data->statusId, 'updated_at' => $currentDatetime, 'update_at' => $currentDatetimes]);
            }
          }
        } else {
          //dd($data->statusId . "/" . $data->nameEn);
          // if ($data->updated == TRUE) {
          DB::table($db . '.posted_admission')->where('admission_id', $data->id)->update($values);
          // }
          if ($data->statusId == 2) {
            // dd($data->statusId);
            if ($checkAdmission != null) {

              DB::table($db . '.admissions')->where('entollmentid', $data->id)->update(['MemberId' => $data->memberId, 'ErpStatus' => $data->statusId, 'updated_at' => $currentDatetime, 'update_at' => $currentDatetimes]);
              //dd("Update Done");
            }
            if ($checkLoan != null) {

              DB::table($db . '.loans')->where('mem_id', $data->id)->update(['erp_mem_id' => $data->memberId, 'updated_at' => $currentDatetime, 'update_at' => $currentDatetimes]);
            }
          } elseif ($data->statusId == 3) {
            if ($checkAdmission != null) {
              DB::table($db . '.admissions')->where('entollmentid', $data->id)->update(['ErpStatus' => $data->statusId, 'updated_at' => $currentDatetime, 'update_at' => $currentDatetimes]);
            }
          }
        }
      }
    }
    return "Data sync successful";
  }
  public function Loan_GetDataForErpStatusCheck($db, $dberp, $BranchCode, $ProjectCode, $Pin, $LastSyncTime, $Appid)
  {
    if ($Appid == 'bmsmerp') {
      $getapplicationdata = DB::Table($db . '.loans')->where('branchcode', $BranchCode)->where('ErpStatus', 1)->min('time');
      // dd($getapplicationdata[0]->created_at);
      if (!empty($getapplicationdata)) {
        // dd($getapplicationdata);
        $applicationdate1 = $getapplicationdata;
        $applicationdate = date('Y-m-d', strtotime($applicationdate1));
        $this->GetErpPostedLoanData($db, $BranchCode, $ProjectCode, $applicationdate);
      }
      /* $getpo = DB::Table($db . '.loans')->select('assignedpo')->where('branchcode', $BranchCode)->groupBy('assignedpo')->get();
      if ($getpo->isEmpty()) {
        //$this->CUSTMSG("No Data Found for PO of BM");
      } else {
        foreach ($getpo as $row) {
          $Pin = $row->assignedpo;
          $getapplicationdata = DB::Table($db . '.loans')->select(DB::raw("cast(time as date)"))->where('branchcode', $BranchCode)->where('assignedpo', $Pin)->where('ErpStatus', 1)->groupBy(DB::raw("cast(time as date)"))->get();
          if (!$getapplicationdata->isEmpty()) {
            foreach ($getapplicationdata as $row) {
              $applicationdate = $row->time;
              $this->GetErpPostedLoanData($db, $BranchCode, $ProjectCode, $applicationdate);
            }
          }
        }
      }*/
    } else {
      $getapplicationdata = DB::Table($db . '.loans')->where('branchcode', $BranchCode)->where('assignedpo', $Pin)->where('ErpStatus', 1)->min('time');
      // dd($getapplicationdata[0]->created_at);
      if (!empty($getapplicationdata)) {
        // dd($getapplicationdata);
        $applicationdate1 = $getapplicationdata;
        $applicationdate = date('Y-m-d', strtotime($applicationdate1));
        $this->GetErpPostedLoanData($db, $BranchCode, $ProjectCode, $applicationdate);
      }
      /*$getapplicationdata = DB::Table($db . '.loans')->select(DB::raw("cast(time as date)"))->where('branchcode', $BranchCode)->where('assignedpo', $Pin)->where('ErpStatus', 1)->groupBy(DB::raw("cast(time as date)"))->get();
      if (!$getapplicationdata->isEmpty()) {
        foreach ($getapplicationdata as $row) {
          $applicationdate = $row->time;
          $this->GetErpPostedLoanData($db, $BranchCode, $ProjectCode, $applicationdate);
        }
      }*/
    }
  }
  //erp get api loan data
  public function GetErpPostedLoanData($db, $BranchCode, $ProjectCode, $applicationdate)
  {
    /*$access_token = $this->tokenVerify();
    $clientid = 'Ieg1N5W2qh3hF0qS9Zh2wq6eex2DB935';
    $clientsecret = '4H2QJ89kYQBStaCuY73h';
    $url = 'https://bracapitesting.brac.net/dcs/v1/branches/' . $branchcode . '/buffer-loan-proposals';

    $headers = array(
      'Authorization: Bearer ' . $access_token,
      'Accept: application/json',
    );*/
    $db = $this->db;
    $serverurl = $this->ServerURL($db);
    $urlindex = $serverurl[0];
    $urlindex1 = $serverurl[1];
    if ($urlindex != '' or $urlindex1 != '') {
      $url = $urlindex;
      $url2 = $urlindex1;
    } else {
      $statuss = array("status" => "CUSTMSG", "message" => "Api Url Not Found");
      $json = json_encode($statuss);
      echo $json;
      die;
    }
    $servertoken = $this->TokenCheck();
    if ($servertoken != '') {
      $headers = array(
        "Content-Type: application/json",
        "Authorization: Bearer " . $servertoken
      );
    } else {
      $statuss = array("status" => "CUSTMSG", "message" => "Token Not Found");
      $json = json_encode($statuss);
      echo $json;
      die;
    }
    $projectcode = (int)$ProjectCode;
    $urlset = $url2 . "branches/$BranchCode/buffer-loan-proposals?projectCode=$projectcode&applicationDate=$applicationdate";
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $urlset,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => $headers,
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    //dd($response);
    if ($err) {
      return "cURL Error #:" . $err;
    } else {
      //   return $response;
      json_decode($response);
      if (json_last_error() == 0) {
        return $this->insertPostedLoanList($response);
      } else {
        return "Erp Server Down";
      }
    }
  }
  //erp get api loan data's database insertion
  public function insertPostedLoanList($response)
  {
    Log::channel('daily')->info('Posted Loan ' . $response);
    $BufferMemberStatus = $response;
    $db = $this->db;
    $dberp = $this->dberp;
    $currentDatetime = date("Y-m-d h:i:s");
    $arrayLoan = json_decode($response);
    if (!empty($arrayLoan)) {
      foreach ($arrayLoan as $data) {
        //dd($data);

        if ($data->secondInsurer != null) {
          $secondinsurerdateofbirth = $data->secondInsurer->dateOfBirth;
          $secondinsurerbackimageurl = $data->secondInsurer->idCard->idCardNo;
          $secondinsurercardtypeid = $data->secondInsurer->idCard->cardTypeId;
          $secondinsurerexpirydate = $data->secondInsurer->idCard->expiryDate;
          $secondinsurerfrontimageurl = $data->secondInsurer->idCard->frontImageUrl;
          $secondinsureridcardno = $data->secondInsurer->idCard->idCardNo;
          $secondinsurerissuedate = $data->secondInsurer->idCard->issueDate;
          $secondinsurerissueplace = $data->secondInsurer->idCard->issuePlace;
          $secondinsurername = $data->secondInsurer->name;

          /* if (array_key_exists('relationshipId', $data->secondInsurer)) {
            $secondinsurerrelationshipid = $data->secondInsurer->relationshipId;
          } else {
            $secondinsurerrelationshipid = null;
          }*/
          if ('relationshipId' == $data->secondInsurer) {
            $secondinsurerrelationshipid = $data->secondInsurer->relationshipId;
          } else {
            $secondinsurerrelationshipid = null;
          }
          if ('genderId' == $data->secondInsurer) {
            $secondinsurergenderid = $data->secondInsurer->genderId;
          } else {
            $secondinsurergenderid = null;
          }
        } else {
          $secondinsurerdateofbirth = null;
          $secondinsurergenderid = null;
          $secondinsurerbackimageurl = null;
          $secondinsurercardtypeid = null;
          $secondinsurerexpirydate = null;
          $secondinsurerfrontimageurl = null;
          $secondinsureridcardno = null;
          $secondinsurerissuedate = null;
          $secondinsurerissueplace = null;
          $secondinsurername = null;
          $secondinsurerrelationshipid = null;
        }
        $nomineescontactNo = null;
        $nomineesdateofbirth = null;
        $nomineesbackimageurl = null;
        $nomineescardtypeid = null;
        $nomineesexpirydate = null;
        $nomineesfrontimageurl = null;
        $nomineesidcardno = null;
        $nomineesissuedate = null;
        $nomineesissueplace = null;
        $nomineesname = null;
        $nomineesrelationshipid = null;
        /*if ($data->nominees != null) {
          $nomineescontactNo = $data->nominees[0]->contactNo;
          $nomineesdateofbirth = $data->nominees[0]->dateOfBirth;
          $nomineesbackimageurl = $data->nominees[0]->idCard->idCardNo;
          $nomineescardtypeid = $data->nominees[0]->idCard->cardTypeId;
          $nomineesexpirydate = $data->nominees[0]->idCard->expiryDate;
          $nomineesfrontimageurl = $data->nominees[0]->idCard->frontImageUrl;
          $nomineesidcardno = $data->nominees[0]->idCard->idCardNo;
          $nomineesissuedate = $data->nominees[0]->idCard->issueDate;
          $nomineesissueplace = $data->nominees[0]->idCard->issuePlace;
          $nomineesname = $data->nominees[0]->name;

          if (array_key_exists('relationshipId', $data->nominees)) {
            $nomineesrelationshipid = $data->nominees->relationshipId;
          } else {
            $nomineesrelationshipid = null;
          }
        } else {
          $nomineescontactNo = null;
          $nomineesdateofbirth = null;
          $nomineesbackimageurl = null;
          $nomineescardtypeid = null;
          $nomineesexpirydate = null;
          $nomineesfrontimageurl = null;
          $nomineesidcardno = null;
          $nomineesissuedate = null;
          $nomineesissueplace = null;
          $nomineesname = null;
          $nomineesrelationshipid = null;
        }*/

        $values = array(
          "applicationdate" => $data->applicationDate,
          "approveddurationinmonths" => $data->approvedDurationInMonths,
          "approvedloanamount" => $data->approvedLoanAmount,
          "branchcode" => $data->branchCode,
          // coBorrowerDto
          // "coborrowerdtobackimageurl" => $data->coBorrowerDto->idCard->backImageUrl,
          // "coborrowerdtocardtypeid" => $data->coBorrowerDto->idCard->cardTypeId,
          // "coborrowerdtoexpirydate" => $data->coBorrowerDto->idCard->expiryDate,
          // "frontImageUrl" => $data->coBorrowerDto->idCard->backImageUrl,
          // "coborrowerdtoidcardno" => $data->coBorrowerDto->idCard->idCardNo,
          // "coborrowerdtoissuedate" => $data->coBorrowerDto->idCard->issueDate,
          // "coborrowerdtoissueplace" => $data->coBorrowerDto->idCard->issuePlace,            
          // "coborrowerdtoname" => $data->coBorrowerDto->name,
          // "coborrowerdtorelationshipid" => $data->coBorrowerDto->relationshipId,
          "consenturl" => $data->consentUrl,
          "disbursementdate" => $data->disbursementDate,
          // "flag" => $data->flag,
          "frequencyid" => $data->frequencyId,
          "loan_id" => $data->id,
          "insuranceproductid" => $data->insuranceProductId,
          "loanaccountid" => $data->loanAccountId,
          "loanapprover" => $data->loanApprover,
          "loanproductid" => $data->loanProductId,
          "loanproposalstatusid" => $data->loanProposalStatusId,
          "memberid" => $data->memberId,
          "membertypeid" => $data->memberTypeId,
          "microinsurance" => $data->microInsurance,
          "modeofpaymentid" => $data->modeOfPaymentId,
          // nominee
          "nomineescontactno" => $nomineescontactNo,
          "nomineesdateofbirth" => $nomineesdateofbirth,
          // "id" => $data->nominees[0]->id,
          "nomineesbackimageurl" => $nomineesbackimageurl,
          "nomineescardtypeid" => $nomineescardtypeid,
          "nomineesexpirydate" => $nomineesexpirydate,
          "nomineesfrontimageurl" => $nomineesfrontimageurl,
          "nomineesidcardno" => $nomineesidcardno,
          "nomineesissuedate" => $nomineesissuedate,
          "nomineesissueplace" => $nomineesissueplace,
          "nomineesname" => $nomineesname,
          "nomineesrelationshipid" => $nomineesrelationshipid,
          "policytypeid" => $data->policyTypeId,
          "premiumamount" => $data->premiumAmount,
          "projectcode" => $data->projectCode,
          "proposaldurationinmonths" => $data->proposalDurationInMonths,
          "proposedloanamount" => $data->proposedLoanAmount,
          "rejectionreason" => $data->rejectionReason,
          "schemeid" => $data->schemeId,
          "secondinsurerdateofbirth" => $secondinsurerdateofbirth,
          "secondinsurergenderid" => $secondinsurergenderid,
          "secondinsurerbackimageurl" => $secondinsurerbackimageurl,
          "secondinsurercardtypeid" => $secondinsurercardtypeid,
          "secondinsurerexpirydate" => $secondinsurerexpirydate,
          "secondinsurerfrontimageurl" => $secondinsurerfrontimageurl,
          "secondinsureridcardno" => $secondinsureridcardno,
          "secondinsurerissuedate" => $secondinsurerissuedate,
          "secondinsurerissueplace" => $secondinsurerissueplace,
          "secondinsurername" => $secondinsurername,
          "secondinsurerrelationshipid" => $secondinsurerrelationshipid,
          "sectorid" => $data->sectorId,
          "signconsent" => $data->signConsent,
          "subsectorid" => $data->subSectorId,
          "updated" => $data->updated,
          "vocode" => $data->voCode,
          "void" => $data->voId,
        );

        $checkPostedLoan = DB::table($db . '.posted_loan')->where('loan_id', $data->id)->first();
        $checkLoan = DB::table($db . '.loans')->where('loan_id', $data->id)->first();

        if ($data->loanProposalStatusId == 4 or $data->loanProposalStatusId == 3) {  //if erp loan disbursed or reject
          if ($checkLoan != null) {                //if addmission has data
            // $member = DB::table($db . '.posted_admission')->where('memberid', $data->memberId)->first();
            //$serverurl = DB::Table($dberp . '.server_url')->where('server_status', 3)->where('status', 1)->first();
            $serverurl = $this->ServerURL($db);
            $urlindex = $serverurl[0];
            $urlindex1 = $serverurl[1];
            if ($urlindex != '' or $urlindex1 != '') {
              $url = $urlindex;
              $url2 = $urlindex1;
            } else {
              $statuss = array("status" => "CUSTMSG", "message" => "Api Url Not Found");
              $json = json_encode($statuss);
              echo $json;
              die;
            }
            $servertoken = $this->TokenCheck();
            if (
              $servertoken != ''
            ) {
              $headers = array(
                "Content-Type: application/json",
                "Authorization: Bearer " . $servertoken
              );
            } else {
              $statuss = array("status" => "CUSTMSG", "message" => "Token Not Found");
              $json = json_encode($statuss);
              echo $json;
              die;
            }
            $key = '5d0a4a85-df7a-scapi-bits-93eb-145f6a9902ae';
            $UpdatedAt = "2000-01-01 00:00:00";
            $member = Http::get($url . 'MemberList', [
              'BranchCode' => $checkLoan->branchcode,
              'CONo' => $checkLoan->assignedpo,
              'ProjectCode' => $checkLoan->projectcode,
              'UpdatedAt' => $UpdatedAt,
              'Status' => 1,
              'OrgNo' => $checkLoan->orgno,
              'OrgMemNo' => $checkLoan->orgmemno,
              'key' => $key
            ]);
            // dd($member);
            $member = $member->object();
            if ($member != null) {
              if ($member->data != null) {
                $member = $member->data[0];
              } else {
                $member = null;
              }
            } else {
              $member = null;
            }
            if ($checkLoan->erp_loan_id == null and $checkLoan->ErpStatus == 1) {    //if erp member id empty in dcs admission table
              if ($member != null) {
                $this->sendAppNotificationForErpLoanAction($data, $member);
              }
            }
          }
        }
        $UpdatedAts = date('Y-m-d H:i:s');

        if ($checkPostedLoan == null) {
          DB::table($db . '.posted_loan')->insert($values);
          if ($data->loanProposalStatusId == 4) {
            if ($checkLoan != null) {
              DB::table($db . '.loans')->where('loan_id', $data->id)->update(['erp_loan_id' => $data->loanAccountId, 'ErpStatus' => $data->loanProposalStatusId, 'updated_at' => $currentDatetime, 'update_at' => $UpdatedAts]);
            }
          } else {
            if ($checkLoan != null) {
              DB::table($db . '.loans')->where('loan_id', $data->id)->update(['ErpStatus' => $data->loanProposalStatusId, 'updated_at' => $currentDatetime, 'update_at' => $UpdatedAts]);
            }
          }
        } else {
          // if ($data->updated == TRUE) {
          DB::table($db . '.posted_loan')->where('loan_id', $data->id)->update($values);
          if ($data->loanProposalStatusId == 4) {
            if ($checkLoan != null) {
              DB::table($db . '.loans')->where('loan_id', $data->id)->update(['erp_loan_id' => $data->loanAccountId, 'ErpStatus' => $data->loanProposalStatusId, 'updated_at' => $currentDatetime, 'update_at' => $UpdatedAts]);
            }
          } else {
            if ($checkLoan != null) {
              DB::table($db . '.loans')->where('loan_id', $data->id)->update(['ErpStatus' => $data->loanProposalStatusId, 'updated_at' => $currentDatetime, 'update_at' => $UpdatedAts]);
            }
          }
          // }
        }
      }
    }
    return "Data sync successful";
  }
  public function sendAppNotificationForErpAddmissionAction($data)
  {
    $db = $this->db;
    $entollmentid = $data->id;
    $dberp = $this->dberp;
    $doc_type = 'admission';
    $popin = $data->assignedPoPin;
    $projectcode = $data->projectCode;
    $projectcode = str_pad($projectcode, 3, "0", STR_PAD_LEFT);
    $brcode = $data->branchCode;
    if ($data->statusId == 2) {
      $msgcontent = 'Member Addmission Approved In Erp';
      $action = 'ErpApprove';
    } elseif ($data->statusId == 3) {
      $msgcontent = 'Member Addmission Rejected In Erp';
      $action = 'ErpReject';
    }
    $getDeviceid = DB::table($db . '.polist')->where('cono', $popin)->where('status', 1)->where('branchcode', $brcode)->where('projectcode', $projectcode)->get();
    if ($getDeviceid != null) {
      //$deviceid = $getDeviceid->deviceid;
    }
    $checkRoleHierarchie = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', $projectcode)->where('position', 1)->first();

    // for bm role 
    if ($checkRoleHierarchie->designation == 'BM') {
      $polist = $this->PoList($db, $brcode, $projectcode, $popin);
      foreach ($polist as $row) {
        $desig = $row->desig;
        if ($desig == 'Branch Manager') {
          $nextrolepin = $row->cono;
        } else if ($desig == 'Assistant Branch Manager') {
          $nextrolepin = $row->cono;
        }
      }
      /*$findpin = DB::table($dberp . '.polist')->select('cono')->where('status', 1)->where('branchcode', $brcode)->where('projectcode', $projectcode)
        ->Where(function ($query) {
          // $query->where('desig','Branch Manager')->orWhere('desig','Assistant Branch Manager');
          $query->where('desig', 'Branch Manager');
        })->first();
      if ($findpin != null) {
        $nextrolepin = $findpin->cono;
        //$deviceid = $findpin->deviceid;
      } else {
        $findpin = DB::table($dberp . '.polist')->select('cono')->where('status', 1)->where('branchcode', $brcode)->where('projectcode', $projectcode)
          ->Where(function ($query) {
            $query->Where('desig', 'Assistant Branch Manager');
            // $query->where('desig','Branch Manager');
          })->first();
        if ($findpin != null) {
          $nextrolepin = $findpin->cono;
          // $deviceid = $findpin->deviceid;
        }
      }*/
    }

    // for am role
    if ($checkRoleHierarchie->designation == 'AM') {
      $nextrolepin = 'b123';
    }



    $checkPostedAdmission = DB::table($db . '.posted_admission')->where('admission_id', $data->id)->first();
    $checkAdmission = DB::table($db . '.admissions')->where('entollmentid', $data->id)->first();
    $checkLoan = DB::table($db . '.loans')->where('mem_id', $data->id)->first();

    $this->sendAppNotification($entollmentid, $doc_type, $popin, $msgcontent, $action);
    $this->sendAppNotification($entollmentid, $doc_type, $nextrolepin, $msgcontent, $action);
  }

  public function sendAppNotificationForErpLoanAction($data, $member)
  {
    $db = $this->db;
    $entollmentid = $data->id;
    $dberp = $this->dberp;
    $doc_type = 'loan';
    // dd($member);
    $popin = $member->AssignedPoPin;
    $projectcode = $data->projectCode;
    $projectcode = str_pad($projectcode, 3, "0", STR_PAD_LEFT);
    $brcode = $data->branchCode;
    if ($data->loanProposalStatusId == 4) {
      $msgcontent = 'Loan Disbursed In Erp';
      $action = 'ErpApprove';
    } elseif ($data->loanProposalStatusId == 3) {
      $msgcontent = 'Loan Rejected In Erp';
      $action = 'ErpReject';
    } else if ($data->loanProposalStatusId == 2) {
      $msgcontent = 'Loan Approve BAO In Erp';
      $action = 'ErpApproveBAO';
    }
    $getDeviceid = DB::table($db . '.polist')->where('cono', $popin)->where('status', 1)->where('branchcode', $brcode)->where('projectcode', $projectcode)->get();
    if ($getDeviceid != null) {
      //$deviceid = $getDeviceid->deviceid;
    }
    $checkRoleHierarchie = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', $projectcode)->where('position', 1)->first();

    // for bm role 
    if ($checkRoleHierarchie->designation == 'BM') {
      $polist = $this->PoList($db, $brcode, $projectcode, $popin);
      foreach ($polist as $row) {
        $desig = $row->desig;
        if ($desig == 'Branch Manager') {
          $nextrolepin = $row->cono;
        } else if ($desig == 'Assistant Branch Manager') {
          $nextrolepin = $row->cono;
        }
      }
      /*$findpin = DB::table($dberp . '.polist')->select('cono')->where('status', 1)->where('branchcode', $brcode)->where('projectcode', $projectcode)
        ->Where(function ($query) {
          // $query->where('desig','Branch Manager')->orWhere('desig','Assistant Branch Manager');
          $query->where('desig', 'Branch Manager');
        })->first();
      if ($findpin != null) {
        $nextrolepin = $findpin->cono;
        //$deviceid = $findpin->deviceid;
      } else {
        $findpin = DB::table($dberp . '.polist')->select('cono')->where('status', 1)->where('branchcode', $brcode)->where('projectcode', $projectcode)
          ->Where(function ($query) {
            $query->Where('desig', 'Assistant Branch Manager');
            // $query->where('desig','Branch Manager');
          })->first();
        if ($findpin != null) {
          $nextrolepin = $findpin->cono;
          //$deviceid = $findpin->deviceid;
        }
      }*/
    }

    // for am role
    if ($checkRoleHierarchie->designation == 'AM') {
      $nextrolepin = 'b123';
    }

    $this->sendAppNotification($entollmentid, $doc_type, $popin, $msgcontent, $action);
    $this->sendAppNotification($entollmentid, $doc_type, $nextrolepin, $msgcontent, $action);
  }
  //end erp api's functions
  //push notification
  public function sendAppNotification($doc_id, $doc_type, $reciverpin, $msgcontent, $action)
  {
    $res = array();
    $res['doc_id'] = $doc_id;
    $res['doc_type'] = $doc_type;
    $res['pin'] = $reciverpin;
    $res['message'] = $msgcontent;
    $res['command'] = "dataReceived";
    $res['action'] = $action;
    //$res['deviceid'] = $deviceid;
    $res['timestamp'] = date('Y-m-d H:i:s');
    $data['data'] = $res;
    $topic = $this->Topic . "" . $reciverpin;  //$reciverpin;
    $test = $this->sendToTopic($topic, $data);
    // dd($test);
    Log::channel('daily')->info('topic: ' . $topic . ',meg: ' . json_encode($data));
    Log::channel('daily')->info('firease response: ' . $test);
    return $test;
  }
  public function sendToTopic($to, $message)
  {
    $fields = array(
      'to' => '/topics/' . $to,
      'data' => $message,
    );
    return $this->sendPushNotification($fields);
  }

  public function sendPushNotification($fields)
  {
    //define('FIREBASE_API_KEY', 'AAAAAehTCwo:APA91bHE2R70FRVrx_WsEbEnal_AGn8MtyFhfxyyv51bh_9xm85eANaV8OoBPdeA0QUVl9umLY-gfILnAFu6GLSMeB6zTHY2v5aUbo2iXzkX6nnaRD1lqTAPjOCVvZwHZ9MP7wyDUere');
    //var_dump($fields);
    // Set POST variables
    // $FIREBASE_API_KEY = 'AAAAgArpCfk:APA91bEE8TjJgYZvvvh8JycZrmQNhsyVnCP6PTFCeHfeCUZItPnYowcPgScHfTJMO9RRT6RreQyF1OX55UJAGsSzRgMoF9mG_KIQvANzuwlYLuxpCrVFKQ7X-lz2h0h_sClza8w3kk0w';
    $FIREBASE_API_KEY = 'AAAAn7dnUEs:APA91bHWNtWzZrkMOPMvSKPVpgKbIYFRoZlP5k2CbRZzaHlpHXq-B8cfeQUsdi7GqbAg-gDDCN1YK9gbcuuPZmN4IK0IEF6PZVfxu1HHK0vX9IzgTfdY-xQt989E8csMSVNO4lx5Bze-';
    $url = 'https://fcm.googleapis.com/fcm/send';

    $headers = array(
      'Authorization: key=' . $FIREBASE_API_KEY,
      'Content-Type: application/json'
    );
    // Open connection
    $ch = curl_init();

    // Set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Disabling SSL Certificate support temporarly
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

    // Execute post
    $result = curl_exec($ch);
    if ($result === FALSE) {
      die('Curl failed: ' . curl_error($ch));
    }

    // Close connection
    curl_close($ch);
    //echo $result;
    return $result;
  }
  //end push notification
  public function HttpCode_Message($httpcode)
  {
  }
  public function NID_Verification(Request $req)
  {
    $serverul = $this->ServerURL();
    $url = $serverul[0];
    $url2 = $serverul[1];
    $servertoken = $this->TokenCheck();
    //echo $servertoken;
    if ($servertoken != '') {
      $headers = array(
        "Content-Type: application/json",
        "Authorization: Bearer " . $servertoken
      );
    } else {
      $statuss = array("status" => "CUSTMSG", "message" => "Token Not Found");
      $json = json_encode($statuss);
      echo $json;
      die;
    }
    $type = Request::get('type');
    $idno = Request::get('IdNo');
    $url4 = $url2 . "dedupe-check?" . $type . "=" . $idno;
    Log::info("Nid Verification " . $url4);
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $url4,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 60,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => $headers,
    ));
    $output_colsed = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ($http_code != '200') {
      $statuss = array("status" => "CUSTMSG", "message" => $output_colsed . "/" . $http_code);
      $json = json_encode($statuss);
      echo $json;
      die;
    }
    $jsondecodeNID = json_decode($output_colsed);
    if ($jsondecodeNID != null) {
      $datasetNID = array("status" => $http_code, $type => $jsondecodeNID->$type, "suspectedMember" => $jsondecodeNID->suspectedMember);
      echo json_encode($datasetNID);
    } else {
      $data = [];
      $datasetNID = array("status" => $http_code, $type => "", "suspectedMember" => $data);
      echo json_encode($datasetNID);
    }
  }
  public function Bank_List($db, $Pin, $BranchCode, $Appid, $CurrentTimes, $ProjectCode, $token)
  {
    $banklist = DB::table($db . '.bank_name')->get();
    if ($banklist->isEmpty()) {
      $bank = [];
    } else {
      $bank = $banklist;
    }
    return $bank;
  }
  public function Savings_Transaction(Request $request)
  {

    $token = Request::get('token');
    $branchcode = Request::get('branchcode');
    $key = $this->key; //Request::get('key');
    $orgno = Request::get('orgno');
    $orgmemno = Request::get('orgmemno');
    $projectname = Request::get('projectcode');
    $cono = Request::get('cono');
    if ($projectname == 'Progoti') {
      $projectcode = '060';
    } else {
      $projectcode = '015';
    }
    $serverurl = $this->ServerURL($this->db);
    $urlindex = $serverurl[0];
    $urlindex1 = $serverurl[1];
    //dd("Test");
    if ($urlindex != '' or $urlindex1 != '') {
      $url = $urlindex;
      $url2 = $urlindex1;
    } else {
      $statuss = array("status" => "CUSTMSG", "message" => "Api Url Not Found");
      $json = json_encode($statuss);
      echo $json;
      die;
    }
    $servertoken = $this->TokenCheck();
    if ($servertoken != '') {
      $headerss = array(
        "Content-Type: application/json",
        "Authorization: Bearer " . $servertoken
      );
    } else {
      $statuss = array("status" => "CUSTMSG", "message" => "Token Not Found");
      $json = json_encode($statuss);
      echo $json;
      die;
    }
    if ($token == '7f30f4491cb4435984616d1913e88389') {
      $todate = date('Y-m-d');
      $formdate = date('Y-m-d', strtotime('-1 year', strtotime($todate)));
      if ($orgno  != null and $orgmemno  != null and $branchcode != null) {
        $url = $url . "TransactionsSavings?BranchCode=$branchcode&OrgNo=$orgno&OrgMemNo=$orgmemno&key=$key&StartDate=$formdate&EndDate=$todate&ProjectCode=$projectcode&CONo=$cono&caller=$cono";
      } else if ($orgmemno  != null and $branchcode != null) {
        $url = $url . "TransactionsSavings?BranchCode=$branchcode&OrgMemNo=$orgmemno&key=$key&StartDate=$formdate&EndDate=$todate&ProjectCode=$projectcode&CONo=$cono&caller=$cono";
      } else {
        $result = array("status" => "E", "message" => "Please choose MemberId or Orgmemno and OrgNo!");
        return json_encode($result);
      }
      // dd($url);
      $url = str_replace(" ", '%20', $url);
      $headers = array(
        'Accept: application/json',
      );

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HEADER, false);
      $output_colsed = curl_exec($ch);
      curl_close($ch);
      echo $output_colsed;
    } else {
      $result = array("status" => "E", "message" => "Invalid token!");
      return json_encode($result);
    }
  }
  public function LogCreate($branchcode, $projectcode, $pin, $type, $message)
  {
    $db = $this->db;
    $insertquery = DB::Table($db . '.logs')->insert(['branchcode' => $branchcode, "projectcode" => $projectcode, "pin" => $pin, "type" => $type, "message" => $message]);
  }
  public function DcsDataSyncOff($db, $branchcode1)
  {
    if ($branchcode1 == '0550') {
    } else {
      $this->CUSTMSG("কারিগরি মান উন্নয়নের কাজ চলছে। আপনার ইনপুটকৃত ডাটা ড্রাফটে সেভ হয়ে থাকবে। রবিবার (১২ ফেব্রুয়ারি) সকালে চেষ্টা করুন। আপনার সহযোগিতার জন্য অসংখ্য ধন্যবাদ।");
    }
  }
  public function Group_Branch_PO_List_Data($db, $BranchCode, $ProjectCode)
  {
    $polist = DB::Table($db . '.polist')->whereIn('branchcode', $BranchCode)->where('projectcode', $ProjectCode)->where('status', 1)->get();
    return $polist;
  }
}
