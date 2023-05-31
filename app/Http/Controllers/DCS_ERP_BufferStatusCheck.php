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

ini_set('memory_limit', '3072M');
ini_set('max_execution_time', 1800);

use ZipArchive;
use Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use File;
use Illuminate\Support\Facades\Session;
//use App\Http\Controllers\TestingController_Version;
use App\Http\Controllers\ApiController;

header('Content-Type: application/json; charset=utf-8');

class DCS_ERP_BufferStatusCheck extends Controller
{
  private $dberp = 'erptestingserver'; //erp test db
  private $db = 'dcs';        //dcs db name
  private $Topic = 'sc';
  public function ERP_Server_Status_Check(Request $request)
  {
    $db = $this->db;
    $admissionBufferPendings = DB::Table($db . '.admissions')->select('branchcode', 'projectcode')->where('ErpStatus', 1)->groupBy('branchcode', 'projectcode')->get();
    if (!empty($admissionBufferPendings)) {
      foreach ($admissionBufferPendings as $row) {
        $branchcode = $row->branchcode;
        $projectcode = $row->projectcode;
        $getMinDate = DB::Table($db . '.admissions')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->min('created_at');
        if (!empty($getMinDate)) {
          $applicationdate = date('Y-m-d', strtotime($getMinDate));
          $this->GetErpPendingAdmissionDataStatus($branchcode, $projectcode, $applicationdate);
        }
      }
    }
    $loanBufferPendings = DB::Table($db . '.loans')->select('branchcode', 'projectcode')->where('ErpStatus', 2)->groupBy('branchcode', 'projectcode')->get();
    //dd($loanBufferPendings);
    if (!empty($loanBufferPendings)) {
      foreach ($loanBufferPendings as $row) {
        $branchcode = $row->branchcode;
        $projectcode = $row->projectcode;
        $getMinDate = DB::Table($db . '.loans')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->min('time');
        if (!empty($getMinDate)) {
          $applicationdate = date('Y-m-d', strtotime($getMinDate));
          $this->GetErpPendingLoanDataStatus($branchcode, $projectcode, $applicationdate);
        }
      }
    }
  }
  public function GetErpPendingLoanDataStatus($branchcode, $projectcode, $applicationdate)
  {
    //$this->LaravelLog();
    $ApiController = new ApiController();
    $ApiController->LaravelLog();
    /*$access_token = $this->tokenVerify();
    $clientid = 'Ieg1N5W2qh3hF0qS9Zh2wq6eex2DB935';
    $clientsecret = '4H2QJ89kYQBStaCuY73h';
    $url = 'https://bracapitesting.brac.net/dcs/v1/branches/' . $branchcode . '/buffer-loan-proposals?projectcode=' . $projectcode . '&applicationDate=' . $applicationdate;

    $headers = array(
      'Authorization: Bearer ' . $access_token,
      'Accept: application/json',
    );*/
    $db = $this->db;
    //$serverurl = $this->ServerURL($db);
    $serverurl = $ApiController->ServerURL($db);
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
    //$servertoken = $this->TokenCheck();
    $servertoken = $ApiController->TokenCheck();
    //echo $servertoken;

    if ($servertoken != '') {
      $headers = array(
        "Content-Type: application/json",
        "Authorization: Bearer " . $servertoken
      );
    } else {
      $statuss = array(
        "status" => "CUSTMSG", "message" => "Token Not Found"
      );
      $json = json_encode($statuss);
      echo $json;
      die;
    }
    $curl = curl_init();
    $projectcode = (int)$projectcode;
    $urlset = $url2 . "branches/$branchcode/buffer-loan-proposals?projectCode=$projectcode&applicationDate=$applicationdate";
    echo $urlset;
    curl_setopt_array($curl, array(
      CURLOPT_URL => $urlset, //$url2 . 'branches/' . $branchcode . '/buffer-loan-proposals?projectcode=' . $projectcode . '&applicationDate=' . $applicationdate,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => $headers,
    ));

    $response = curl_exec($curl);
    //dd($response);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($httpcode != 200) {
      Log::info("Loan Buffer Status Check" . $response);
      die;
    }
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
      return "cURL Error #:" . $err;
    } else {
      //   return $response;
      json_decode($response);
      if (json_last_error() == 0) {
        return $this->checkPostedLoanStatus($response);
      } else {
        return "Erp Server Down";
      }
    }
  }

  //erp get api loan data's database insertion
  public function checkPostedLoanStatus($response)
  {
    $ApiController = new ApiController();
    $ApiController->LaravelLog();
    //$this->LaravelLog();
    $BufferMemberStatus = $response;
    $db = $this->db;
    $dberp = $this->dberp;
    $currentDatetime = date("Y-m-d h:i:s");
    $arrayLoan = json_decode($response);
    //dd($arrayLoan);
    foreach ($arrayLoan as $data) {
      // dd($data->secondInsurer);

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

      if ($data->nominees != null) {
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

        if ('relationshipId' == $data->nominees) {
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
      //dd($checkLoan);

      if ($data->loanProposalStatusId == 4 or $data->loanProposalStatusId == 3) {  //if erp loan disbursed or reject
        // echo $data->loanProposalStatusId . '/';
        if ($checkLoan != null) {                //if addmission has data
          // $member = DB::table($db . '.posted_admission')->where('memberid', $data->memberId)->first();
          // $serverurl = DB::Table($dberp . '.server_url')->where('server_status', 3)->where('status', 1)->first();
          //$serverurl = $this->ServerURL($db);
          $serverurl = $ApiController->ServerURL($db);
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
          $servertoken = $ApiController->TokenCheck();
          // $servertoken = $this->TokenCheck();
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
          // dd($member);

          if ($checkLoan->erp_loan_id == null and $checkLoan->ErpStatus == 1) {    //if erp member id empty in dcs admission table
            if ($member != null) {
              //$this->sendAppNotificationForErpLoanAction($data, $member);
            }
          }
        }
      }
      $updatedAt = date('Y-m-d H:i:s');
      if ($checkPostedLoan == null) {
        DB::table($db . '.posted_loan')->insert($values);
        if ($data->loanProposalStatusId == 4) {
          //dd("Test");
          if ($checkLoan != null) {
            DB::table($db . '.loans')->where('loan_id', $data->id)->update(['erp_loan_id' => $data->loanAccountId, 'ErpStatus' => $data->loanProposalStatusId, 'updated_at' => $currentDatetime, 'update_at' => $updatedAt]);
          }
        } else {
          if ($checkLoan != null) {
            DB::table($db . '.loans')->where('loan_id', $data->id)->update(['ErpStatus' => $data->loanProposalStatusId, 'updated_at' => $currentDatetime, 'update_at' => $updatedAt]);
          }
        }
      } else {
        // if ($data->updated == TRUE) {
        DB::table($db . '.posted_loan')->where('loan_id', $data->id)->update($values);
        if ($data->loanProposalStatusId == 4) {
          if ($checkLoan != null) {
            DB::table($db . '.loans')->where('loan_id', $data->id)->update(['erp_loan_id' => $data->loanAccountId, 'ErpStatus' => $data->loanProposalStatusId, 'updated_at' => $currentDatetime, 'update_at' => $updatedAt]);
          }
        } else {
          if ($checkLoan != null) {
            DB::table($db . '.loans')->where('loan_id', $data->id)->update(['ErpStatus' => $data->loanProposalStatusId, 'updated_at' => $currentDatetime, 'update_at' => $updatedAt]);
          }
        }
        // }
      }
    }
    return "Data sync successful";
  }
}
