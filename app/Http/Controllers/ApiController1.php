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
header('Content-Type: application/json; charset=utf-8');

class ApiController extends Controller
{
	private $dberp = 'erptestingserver'; //erp test db
	private $db = 'dcs';				//dcs db name

	public function PoSync(Request $req)
	{
		//echo "Huda";
		//dd("H");
		$db = $this->db;
		$token = Request::input('token');
		$branchcode = Request::input('branchcode');
		$ProjectCode = Request::input('projectcode');
		$project_code = Request::input('projectcode');
		$auth_array = [];
		$branchcode = (int)$branchcode;
		$projectcode = (int)$ProjectCode;
		if ($token == '7f30f4491cb4435984616d1913e88389') {
			if ($branchcode != null and $projectcode != null) {
				$Process = DB::Table($db . '.processes')->select('id', 'process')->get();
				$FormConfig = DB::Table($db . '.form_configs')->where('projectcode', $project_code)->get();
				$PayloadData = DB::Table($db . '.payload_data')->where('status', 1)->get();
				$OfficeMapping = DB::Table($db . '.office_mapping')->where('status', 1)->get();
				$ProductDetail = DB::Table($db . '.product_details')->get();
				$ProjectwiseMemberCategory = DB::Table($db . '.projectwise_member_category')->where('projectcode', $projectcode)->get();
				$ProductProjectMemberCategory = DB::Table($db . '.product_project_member_category')->where('projectcode', $projectcode)->where(
					function ($query) use ($branchcode) {
						return $query
							->where('branchcode', $branchcode)->orWhere('branchcode', '*');
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
				$auth = DB::Table($db . '.auths')->where('projectcode', $ProjectCode)->where('roleId', '0')->whereNotNull('prerequisiteprocessid')->get();

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
					"status" => "S",
					"message" => "",
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
				);
				return json_encode($result);
			} else {
				$result = array("status" => "E", "message" => "Invalid perameter!");
				return json_encode($result);
			}
		} else {
			$result = array("status" => "E", "message" => "Invalid token!");
			return json_encode($result);
		}
	}

	public function OperationsDataSync(Request $req)
	{
		$token = Request::input('token');
		$branch_code = Request::input('branchcode');
		$branchcode = str_pad($branch_code, 4, "0", STR_PAD_LEFT);
		$pin = Request::input('pin');
		$project_code = Request::input('projectcode');
		$projectcode = str_pad($project_code, 3, "0", STR_PAD_LEFT);

		$this->GetErpPostedAdmissionData($branchcode); //erp dcs admission data sync 
		$this->GetErpPostedLoanData($branchcode); //erp dcs Loan data sync


		if ($token == '7f30f4491cb4435984616d1913e88389') {
			if ($branchcode != null and $projectcode != null) {
				$SurveyDatas = $this->getSurveys($branchcode, $projectcode, $pin);
				$AdmissionDatas = $this->getAdmissions($branchcode, $projectcode, $pin);
				$LoanRcaDatas = $this->getLoanRcas($branchcode, $projectcode, $pin);

				$result = array(
					"status" => "S",
					"message" => "",
					"SurveyDatas" => $SurveyDatas,
					"AdmissionDatas" => $AdmissionDatas,
					"LoanRcaDatas" => $LoanRcaDatas,
				);
				return json_encode($result);
			} else {
				$result = array("status" => "E", "message" => "Invalid perameter!");
				return json_encode($result);
			}
		} else {
			$result = array("status" => "E", "message" => "Invalid token!");
			return json_encode($result);
		}
	}

	public function getSurveys($branchcode, $projectcode, $pin)
	{
		$db = $this->db;
		if ($branchcode != null and $pin == null) {
			$surveydata = DB::table($db . '.surveys')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->orderBy('id', 'desc')->get();
		} elseif ($branchcode != null and $pin != null) {
			$surveydata = DB::table($db . '.surveys')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->where('assignedpo', $pin)->orderBy('id', 'desc')->get();
		}
		return $surveydata;
	}

	public function getAdmissions($branchcode, $projectcode, $pin)
	{
		$db = $this->db;

		if ($pin == null) {
			// $admissiondata = DB::table($db . '.admissions')->where('branchcode', $branchcode)->orderBy('id', 'desc')->get();
			$admissionsDataWithoutPending = DB::table($db . '.admissions')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->where('status', '!=', '1')->where('updated_at', '<=', Carbon::now()->format('Y-m-d H:i:s'))->where('updated_at', '>=', Carbon::now()->subMonth(6)->format('Y-m-d H:i:s'))->orderBy('id', 'desc');
			$admissiondata = DB::table($db . '.admissions')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->Where('status', '1')->orderBy('id', 'desc')->unionAll($admissionsDataWithoutPending)->orderBy('id', 'desc')->get();
		} elseif ($pin != null) {
			$admissionsDataWithoutPending = DB::table($db . '.admissions')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->where('assignedpo', $pin)->where('status', '!=', '1')->where('updated_at', '<=', Carbon::now()->format('Y-m-d H:i:s'))->where('updated_at', '>=', Carbon::now()->subMonth(6)->format('Y-m-d H:i:s'))->orderBy('id', 'desc');
			$admissiondata = DB::table($db . '.admissions')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->where('assignedpo', $pin)->Where('status', '1')->orderBy('id', 'desc')->unionAll($admissionsDataWithoutPending)->orderBy('id', 'desc')->get();
			// dd($admissiondata, $branchcode, $projectcode, $pin);
			// $admissiondata = DB::table($db . '.admissions')->where('branchcode', $branchcode)->where('assignedpo', $pin)->orderBy('id', 'desc')->get();
			// $admissiondata = DB::table($db . '.admissions')->where('branchcode', $branchcode)->where('assignedpo', $pin)->orWhere('status', 1)->orWhere(function ($query) {
			// 	$query->where('updated_at', '<=', Carbon::now()->subMonth(2)->format('Y-m-d H:i:s'))->where('updated_at', '>=', Carbon::now());
			// })->orderBy('id', 'desc')->get();

		}
		if ($admissiondata->isEmpty()) {
			return $admissiondata;
		} else {
			foreach ($admissiondata as $data) {
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
				$role_name = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', $projectcode)->where('position', $data->roleid)->first();
				$recieverrole_name = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', $projectcode)->where('position', $data->reciverrole)->first();
				$dochistory = DB::table($db . '.document_history')->select('comment')->where('id', $data->dochistory_id)->first();
				$status = DB::table($db . '.status')->select('status_name')->where('status_id', $data->status)->first();
				$presentUpazilaId = DB::table($db . '.office_mapping')->select('thana_name')->where('thana_id', $data->presentUpazilaId)->first();
				$parmanentUpazilaId = DB::table($db . '.office_mapping')->select('thana_name')->where('thana_id', $data->parmanentUpazilaId)->first();
				// $PresentDistrict = DB::table($db . '.office_mapping')->select('district_name')->where('district_id', $data->PresentDistrictId)->first();


				$WalletOwner = $WalletOwner->data_name ?? null;
				$NomineeNidType = $NomineeNidType->data_name ?? null;
				$SpuseOccupationId = $SpuseOccupationId->data_name ?? null;
				$SpouseCardType = $SpouseCardType->data_name ?? null;
				$OtherIdTypeId = $OtherIdTypeId->data_name ?? null;
				$presentUpazila = $presentUpazilaId->thana_name ?? null;
				$parmanentUpazilaId = $parmanentUpazilaId->thana_name ?? null;

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
					$checkPostedAdmission = DB::table($db . '.posted_admission')->where('admission_id', $data->entollmentid)->first();
					if ($checkPostedAdmission != null) {
						$ErpStatusId = $checkPostedAdmission->statusid;
						if ($ErpStatusId == 1) {
							$ErpStatus = 'Pending';
						} elseif ($ErpStatusId == 2) {
							$ErpStatus = 'Approved';
						} elseif ($ErpStatusId == 3) {
							$ErpStatus = 'Rejected';
						}
						$ErpRejectionReason = $checkPostedAdmission->rejectionreason;
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
				$created_at = date('Y-m-d', strtotime($data->created_at));
				$updated_at = date('Y-m-d', strtotime($data->updated_at));

				$arrayData = array(
					"id" => $data->id,
					"IsRefferal" => $data->IsRefferal,
					"RefferedById" => $data->RefferedById,
					"MemberId" => $data->MemberId,
					"MemberCateogryId" => $data->MemberCateogryId,
					"MemberCateogry" => $MemberCateogryId->categoryname,
					"ApplicantsName" => $data->ApplicantsName,
					"ApplicantSinglePic" => $data->ApplicantSinglePic,
					"MainIdType" => $MainIdTypeId->data_name,
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
					"Education" => $EducationId->data_name,
					"EducationId" => $data->EducationId,
					"Phone" => $data->Phone,
					"PresentAddress" => $data->PresentAddress,
					"presentUpazilaId" => $data->presentUpazilaId,
					"presentUpazila" => $presentUpazila,
					"PermanentAddress" => $data->PermanentAddress,
					"parmanentUpazilaId" => $data->parmanentUpazilaId,
					"PresentDistrictId" => $data->PresentDistrictId,
					// "PresentDistrict" => $PresentDistrictId,
					"PermanentDistrictId" => $data->PermanentDistrictId,
					// "PermanentDistrict" => $PermanentDistrict,
					"parmanentUpazila" => $parmanentUpazilaId,
					"MaritalStatusId" => $data->MaritalStatusId,
					"MaritalStatus" => $MaritalStatusId->data_name,
					"SpouseName" => $data->SpouseName,
					"SpouseCardType" => $SpouseCardType,
					"SpouseCardTypeId" => $data->SpouseCardType,
					"SpouseNidOrBid" => $data->SpouseNidOrBid,
					"SposeDOB" => $data->SposeDOB,
					"SpuseOccupationId" => $data->SpuseOccupationId,
					"SpuseOccupation" => $SpuseOccupationId,
					"SpouseNidFront" => $data->SpouseNidFront,
					"SpouseNidFront" => $data->SpouseNidFront,
					"ReffererName" => $data->ReffererName,
					"ReffererPhone" => $data->ReffererPhone,
					"FamilyMemberNo" => $data->FamilyMemberNo,
					"NoOfChildren" => $data->NoOfChildren,
					"NomineeDOB" => $data->NomineeDOB,
					"RelationshipId" => $data->RelationshipId,
					"Relationship" => $RelationshipId->data_name,
					"ApplicantCpmbinedImg" => $data->ApplicantCpmbinedImg,
					"ReffererImg" => $data->ReffererImg,
					"ReffererIdImg" => $data->ReffererIdImg,
					"FrontSideOfIdImg" => $data->FrontSideOfIdImg,
					"BackSideOfIdimg" => $data->BackSideOfIdimg,
					"NomineeIdImg" => $data->NomineeIdImg,
					"DynamicFieldValue" => $data->DynamicFieldValue,
					"created_at" => $created_at,
					"updated_at" => $updated_at,
					"branchcode" => $data->branchcode,
					"projectcode" => $data->projectcode,
					"Occupation" => $Occupation->data_name,
					"OccupationId" => $data->Occupation,
					"IsBkash" => $IsBkash,
					"WalletNo" => $data->WalletNo,
					"WalletOwnerId" => $data->WalletOwner,
					"WalletOwner" => $WalletOwner,
					"NomineeName" => $data->NomineeName,
					"PrimaryEarner" => $PrimaryEarner->data_name,
					"PrimaryEarnerId" => $data->PrimaryEarner,
					"dochistory_id" => $data->dochistory_id,
					"roleid" => $data->roleid,
					"pin" => $data->pin,
					"action" => $data->action,
					"reciverrole" => $data->reciverrole,
					"status" => $status->status_name,
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
					"Gender" => $genderId->data_name,
					"SavingsProductId" => $data->SavingsProductId,
					"role_name" => $role_name->designation,
					"reciverrole_name" => $recieverrole_name->designation,
					"SurveyId" => $data->surveyid,
					"Comment" => $dochistory->comment,
					"ErpStatus" => $ErpStatus,
					"ErpStatusId" => $ErpStatusId,
					"ErpRejectionReason" => $ErpRejectionReason,
					"Flag" => $data->Flag
				);
				$admissiondataary[] = $arrayData;
			}
		}
		return $admissiondataary;
	}

	public function getLoanRcas($branchcode, $projectcode, $pin)
	{
		$db = $this->db;
		$dberp = $this->dberp;
		if ($pin == null) {
			// $loandata = DB::table($db . '.loans')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->orderBy('id', 'desc')->get();
			$loansDataWithoutPending = DB::table($db . '.loans')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->where('status', '!=', '1')->where('updated_at', '<=', Carbon::now()->format('Y-m-d H:i:s'))->where('updated_at', '>=', Carbon::now()->subMonth(6)->format('Y-m-d H:i:s'))->orderBy('id', 'desc');
			$loandata = DB::table($db . '.loans')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->Where('status', '1')->orderBy('id', 'desc')->unionAll($loansDataWithoutPending)->orderBy('id', 'desc')->get();
		} elseif ($pin != null) {
			// $loandata = DB::table($db . '.loans')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->where('assignedpo', $pin)->orderBy('id', 'desc')->get();
			$loansDataWithoutPending = DB::table($db . '.loans')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->where('assignedpo', $pin)->where('status', '!=', '1')->where('updated_at', '<=', Carbon::now()->format('Y-m-d H:i:s'))->where('updated_at', '>=', Carbon::now()->subMonth(6)->format('Y-m-d H:i:s'))->orderBy('id', 'desc');
			$loandata = DB::table($db . '.loans')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->where('assignedpo', $pin)->Where('status', '1')->orderBy('id', 'desc')->unionAll($loansDataWithoutPending)->orderBy('id', 'desc')->get();
		}

		if ($loandata->isEmpty()) {
			return $loandata;
		} else {
			foreach ($loandata as $data) {
				$grntorRlationClient = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'relationshipId')->where('data_id', $data->grntor_rlationClient)->first();
				$investSector = DB::table($db . '.schemem_sector_subsector')->select('sectorname')->where('sectorid', $data->invest_sector)->first();
				$subSectorId = DB::table($db . '.schemem_sector_subsector')->select('subsectorname')->where('subsectorid', $data->subSectorId)->first();
				$frequencyId = DB::table($db . '.product_details')->select('frequency')->where('frequencyid', $data->frequencyId)->first();
				$scheme = DB::table($db . '.schemem_sector_subsector')->select('schemename')->where('schemeid', $data->scheme)->first();
				$role_name = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', $projectcode)->where('position', $data->roleid)->first();
				$recieverrole_name = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', $projectcode)->where('position', $data->reciverrole)->first();
				$memberTypeId = DB::table($db . '.projectwise_member_category')->select('categoryname')->where('categoryid', $data->memberTypeId)->first();
				$loan_product_name = DB::table($db . '.product_project_member_category')->select('productname')->where('productid', $data->loan_product)->first();
				if ($data->insurn_gender != null) {
					$InsurnGender = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'genderId')->where('data_id', $data->insurn_gender)->first();
					$insurnGender = $InsurnGender->data_name;
				} else {
					$insurnGender = null;
				}

				if ($data->insurn_gender != null) {
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
				// if ($data->status == '2') {
				// 	$checkPostedLoan = DB::table($db . '.posted_loan')->where('loan_id', $data->loan_id)->first();
				// 	if ($checkPostedLoan != null) {
				// 		$ErpStatusId = $checkPostedLoan->loanproposalstatusid;
				// 		if ($ErpStatusId == 1) {
				// 			$ErpStatus = 'Pending';
				// 		} elseif ($ErpStatusId == 2) {
				// 			$ErpStatus = 'Approved';
				// 		} elseif ($ErpStatusId == 3) {
				// 			$ErpStatus = 'Rejected';
				// 		}
				// 		$ErpRejectionReason = $checkPostedLoan->rejectionreason;
				// 	}
				// } else {
				// 	$ErpStatus = null;
				// 	$ErpStatusId = null;
				// 	$ErpRejectionReason = null;
				// }

				$serverurl = DB::Table($dberp . '.server_url')->where('server_status', 3)->where('status', 1)->first();
				$key = '5d0a4a85-df7a-scapi-bits-93eb-145f6a9902ae';
				$UpdatedAt = "2000-01-01 00:00:00";
				$member = Http::get($serverurl->url . 'MemberList', [
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
				}

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
					"loan_product" => $data->loan_product,
					"loan_product_name" => $loan_product_name->productname,
					"loan_duration" => $data->loan_duration,
					"invest_sector_id" => $data->invest_sector,
					"invest_sector" => $investSector->sectorname,
					"scheme_id" => $data->scheme,
					"scheme" => $scheme->schemename,
					"propos_amt" => $data->propos_amt,
					"instal_amt" => $data->instal_amt,
					"bracloan_family" => $data->bracloan_family,
					"vo_leader" => $data->vo_leader,
					"recommender" => $data->recommender,
					"grntor_name" => $data->grntor_name,
					"grntor_phone" => $data->grntor_phone,
					"grntor_rlationClient" => $grntorRlationClient->data_name,
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
					"memberType" => $memberTypeId->categoryname,
					"frequencyId" => $data->frequencyId,
					"frequency" => $frequencyId->frequency,
					"subSectorId" => $data->subSectorId,
					"subSector" => $subSectorId->subsectorname,
					"insurn_mainIDTypeId" => $data->insurn_mainIDType,
					"insurn_mainIDType" => $insurnMainIDType,
					"insurn_id_expire" => $data->insurn_id_expire,
					"insurn_placeofissue" => $data->insurn_placeofissue,
					"ErpHttpStatus" => $data->ErpHttpStatus,
					"ErpErrorMessage" => $data->ErpErrorMessage,
					"ErpErrors" => $data->ErpErrors,
					"erp_loan_id" => $data->erp_loan_id,
					"role_name" => $role_name->designation,
					"reciverrole_name" => $recieverrole_name->designation,
					"SurveyId" => $data->surveyid,
					"amount_inword" => $data->amount_inword,
					"loan_purpose" => $data->loan_purpose,
					"loan_user" => $data->loan_user,
					"loan_type" => $data->loan_type,
					"brac_loancount" => $data->brac_loancount,
					"Comment" => $dochistory->comment,
					"ErpStatus" => $ErpStatus,
					"ErpStatusId" => $ErpStatusId,
					"ErpRejectionReason" => $ErpRejectionReason
				);
				// $data['loan']=$loanArrayData;
				$rca = DB::table($db . '.rca')->where('loan_id', $data->id)->first();
				$PrimaryEarner = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'primaryEarner')->where('data_id', $rca->primary_earner)->first();
				$bmPrimaryEarner = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'primaryEarner')->where('data_id', $rca->bm_primary_earner)->first();
				if ($bmPrimaryEarner) {
					$bmPrimaryEarnerIs = $bmPrimaryEarner->data_name;
				} else {
					$bmPrimaryEarnerIs = null;
				}
				$arrayData['rca'] = array(
					"id" => $rca->id,
					"loan_id" => $rca->loan_id,
					"primary_earner" => $PrimaryEarner->data_name,
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
					"monthlyincome_spouse_child" => $rca->monthlyincome_spouse_child
				);
				$arrayData['clientInfo'] = $member;
				$dataset[] = $arrayData;
			}
		}
		return $dataset;
	}

	public function Index(Request $req)
	{
		$baseUrl = url('');
		$projectCode = json_decode($projectCode);
		$projectCode = date('Y-m-d', strtotime($projectCode->asd)) ?? null;
		// $projectCode = $projectCode->asd;
		//echo "Huda";
		//dd("H");
		$db = $this->db;
		$projectCode = Request::input('projectcode');
		$Approver = Request::input('approver');
		$GrowthRate = Request::input('growthrate');
		$apikey = Request::input('apikey');
		$json = DB::Table($db . '.celing_configs')->where('projectcode', $projectCode)->where('approver', $Approver)->where('growth_rate', $GrowthRate)->get();
		if ($json->isEmpty()) {
			$result = array("status" => "E", "message" => "Data Not Found!");
			echo json_encode($result);
		} else {
			$result = array("status" => "S", "message" => "", "data" => $json);
			echo json_encode($result);
		}
	}

	public function Delete_All(Request $req)
	{
		$dbs = $this->db;
		//dd($eventid);
		DB::select(DB::raw("Delete from $dbs.admissions")); //DB::table('mnw_progoti.respondents')->where('eventid',$eventid)->delete();//"Delete from mnw_progoti.respondents";
		DB::select(DB::raw("Delete from $dbs.loans")); //DB::table('mnw_progoti.survey_data')->where('event_id',$eventid)->delete();//"Delete from mnw_progoti.survay_data";
		DB::select(DB::raw("Delete from $dbs.rca"));
		DB::select(DB::raw("Delete from $dbs.message_ques"));
		DB::select(DB::raw("Delete from $dbs.document_history"));
		echo "Delete Successfully";
	}

	public function erpVOList(Request $req)
	{
		$dberp = $this->dberp;
		$token = Request::input('token');
		$serverurl = DB::Table($dberp . '.server_url')->where('server_status', 3)->where('status', 1)->first();
		$BranchCode = Request::get('BranchCode');
		$PIN = Request::get('PIN');
		$ProjectCode = Request::get('ProjectCode');
		$UpdatedAt = Request::get('UpdatedAt');
		$key = Request::get('key');
		if ($token == '7f30f4491cb4435984616d1913e88389') {
			if ($serverurl != null) {
				$server_url = $serverurl->url;
				// $url = $server_url . "VOList?BranchCode=$BranchCode&PIN=$PIN&ProjectCode=$ProjectCode&UpdatedAt=$UpdatedAt&key=$key";
				$url = $server_url . "VOList?BranchCode=$BranchCode&PIN=$PIN&ProjectCode=$ProjectCode&UpdatedAt=$UpdatedAt&key=$key";
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

				return $output_colsed;
			} else {
				$result = array("status" => "E", "message" => "No Server Found!");
				return json_encode($result);
			}
		} else {
			$result = array("status" => "E", "message" => "Invalid token!");
			return json_encode($result);
		}
	}

	public function LastOneCloseLoanBehavior()
	{
		$dberp = $this->dberp;
		$token = Request::input('token');
		$serverurl = DB::Table($dberp . '.server_url')->where('server_status', 3)->where('status', 1)->first();
		$BranchCode = Request::get('BranchCode');
		$MemberId = Request::get('MemberId');
		$OrgNo = Request::get('OrgNo');
		$OrgMemNo = Request::get('OrgMemNo');
		$key = Request::get('key');
		// dd($MemberId, $OrgMemNo, $OrgNo);
		if ($token == '7f30f4491cb4435984616d1913e88389') {
			if ($serverurl != null) {
				$server_url = $serverurl->url;
				// $url = $server_url . "VOList?BranchCode=$BranchCode&PIN=$PIN&ProjectCode=$ProjectCode&UpdatedAt=$UpdatedAt&key=$key";
				if ($OrgNo == null and $OrgMemNo == null and $MemberId != null) {
					$url = $server_url . "LastOneCloseLoanBehavior?BranchCode=$BranchCode&MemberId=$MemberId&key=$key";
				} elseif ($OrgNo != null and $OrgMemNo != null and $MemberId == null) {
					$url = $server_url . "LastOneCloseLoanBehavior?BranchCode=$BranchCode&OrgNo=$OrgNo&OrgMemNo=$OrgMemNo&key=$key";
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

				return $output_colsed;
			} else {
				$result = array("status" => "E", "message" => "No Server Found!");
				return json_encode($result);
			}
		} else {
			$result = array("status" => "E", "message" => "Invalid token!");
			return json_encode($result);
		}
	}

	public function erpMemberList(Request $req)
	{
		$dberp = $this->dberp;
		$serverurl = DB::Table($dberp . '.server_url')->where('server_status', 3)->where('status', 1)->first();
		$BranchCode = Request::get('BranchCode');
		$PIN = Request::get('PIN');
		$ProjectCode = Request::get('ProjectCode');
		$CONo = Request::get('CONo');
		$UpdatedAt = Request::get('UpdatedAt');
		$key = Request::get('key');
		$Status = Request::get('Status');
		$OrgNo = Request::get('OrgNo');
		$OrgMemNo = Request::get('OrgMemNo');
		$token = Request::input('token');
		if ($token == '7f30f4491cb4435984616d1913e88389') {
			if ($serverurl != null) {
				$server_url = $serverurl->url;
				$url = $server_url . "MemberList?BranchCode=$BranchCode&CONo=$CONo&ProjectCode=$ProjectCode&UpdatedAt=$UpdatedAt&key=$key&Status=$Status&OrgNo=$OrgNo&OrgMemNo=$OrgMemNo";
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

				return $output_colsed;
			} else {
				$result = array("status" => "E", "message" => "No Server Found!");
				return json_encode($result);
			}
		} else {
			$result = array("status" => "E", "message" => "Invalid token!");
			return json_encode($result);
		}
	}

	public function erpSavingsInfo(Request $req)
	{
		$dberp = $this->dberp;
		$db = $this->db;
		$serverurl = DB::Table($dberp . '.server_url')->where('server_status', 3)->where('status', 1)->first();
		$BranchCode = Request::get('BranchCode');
		$PIN = Request::get('PIN');
		$ProjectCode = Request::get('ProjectCode');
		$CONo = Request::get('CONo');
		$UpdatedAt = Request::get('UpdatedAt');
		$key = Request::get('key');
		$Status = Request::get('Status');
		$OrgNo = Request::get('OrgNo');
		$token = Request::input('token');
		$dataset = [];
		if ($token == '7f30f4491cb4435984616d1913e88389') {
			if ($serverurl != null) {
				$server_url = $serverurl->url;
				$url = $server_url . "SavingsInfo?BranchCode=$BranchCode&CONo=$CONo&ProjectCode=$ProjectCode&UpdatedAt=$UpdatedAt&key=$key&Status=$Status";
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

				$savingsInfo = json_decode($output_colsed);
				$data = $savingsInfo->data;

				if (!empty($data)) {
					foreach ($data as $row) {
						$posted_admission = DB::Table($db . '.admissions')->select('ApplicantCpmbinedImg')->where('MemberId', $row->OrgMemNo)->first();
						$array['OrgNo'] = $row->OrgNo;
						$array['OrgMemNo'] = $row->OrgMemNo;
						$array['ProjectCode'] = $row->ProjectCode;
						$array['BranchCode'] = $row->BranchCode;
						$array['MemberName'] = $row->MemberName;
						$array['MemberImage'] = $posted_admission->ApplicantCpmbinedImg ?? null;
						$array['SavBalan'] = $row->SavBalan;
						$array['SavPayable'] = $row->SavPayable;
						$array['CalcIntrAmt'] = $row->CalcIntrAmt;
						$array['TargetAmtSav'] = $row->TargetAmtSav;
						$array['ApplicationDate'] = $row->ApplicationDate;
						$array['NationalId'] = $row->NationalId;
						$array['FatherName'] = $row->FatherName;
						$array['MotherName'] = $row->MotherName;
						$array['SpouseName'] = $row->SpouseName;
						$array['ContactNo'] = $row->ContactNo;
						$array['BkashWalletNo'] = $row->BkashWalletNo;
						$array['AssignedPO'] = $row->AssignedPO;
						$array['UpdatedAt'] = $row->UpdatedAt;
						$dataset[] = $array;
					}
					$response = array("code" => 200, "data" => $dataset);
					return json_encode($response);
				} else {
					return $output_colsed;
				}
			} else {
				$result = array("status" => "E", "message" => "No Server Found!");
				return json_encode($result);
			}
		} else {
			$result = array("status" => "E", "message" => "Invalid token!");
			return json_encode($result);
		}
	}

	//admission member erp posting
	public function dcsInstallmentCalculator()
	{
		$json = json_encode(Request::all());
		// dd($json);

		$db = $this->db;
		$currentDatetime = date("Y-m-d h:i:s");
		$access_token = $this->tokenVerify();
		$clientid = 'Ieg1N5W2qh3hF0qS9Zh2wq6eex2DB935';
		$clientsecret = '4H2QJ89kYQBStaCuY73h';
		$url = 'https://bracapitesting.brac.net/dcs/v1/loan/installment-calculator';

		$headers = array(
			'Authorization: Bearer ' . $access_token,
			'Content-Type: application/json'
		);

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $json,
			CURLOPT_HTTPHEADER => $headers,
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);
		// dd($response);
		if ($err) {
			return "cURL Error #:" . $err;
		} else {
			return $response;
		}
	}

	public function dcsInsurancePremiumCalculation()
	{
		$json = json_encode(Request::all());
		// dd(Request::toJson());

		$db = $this->db;
		$currentDatetime = date("Y-m-d h:i:s");
		$access_token = $this->tokenVerify();
		$clientid = 'Ieg1N5W2qh3hF0qS9Zh2wq6eex2DB935';
		$clientsecret = '4H2QJ89kYQBStaCuY73h';
		$url = 'https://bracapitesting.brac.net/dcs/v1/loan/insurance-premium-calculator';

		$headers = array(
			'Authorization: Bearer ' . $access_token,
			'Content-Type: application/json'
		);

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $json,
			CURLOPT_HTTPHEADER => $headers,
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);
		// dd($response);
		if ($err) {
			return "cURL Error #:" . $err;
		} else {
			return $response;
		}
	}

	public function CelingConfig(Request $request)
	{
		//dd("H");
		$db = $this->db;
		$projectCode = Request::input('projectcode');
		$branchcode = Request::input('branchcode');
		$Approver = Request::input('approver');
		$apikey = Request::input('apikey');
		$token = Request::input('token');
		if ($token == '7f30f4491cb4435984616d1913e88389') {
			$BranchGrowthType = DB::Table($db . '.project_wise_branch_growth_types')->where('project_code', (int)$projectCode)->where('office_code', (int)$branchcode)->first();
			if ($BranchGrowthType != null) {
				$GrowthRate = $BranchGrowthType->branch_growth_type;
				$json = DB::Table($db . '.celing_configs')->where('projectcode', (int)$projectCode)->where('approver', $Approver)->where('growth_rate', $GrowthRate)->get();

				if ($json->isEmpty()) {
					$result = array("status" => "E", "message" => "Data Not Found!");
					echo json_encode($result);
				} else {
					$result = array("status" => "S", "message" => "", "data" => $json);
					echo json_encode($result);
				}
			} else {
				$result = array("status" => "E", "message" => "Branch Growth type Not Found!");
				echo json_encode($result);
			}
		} else {
			$result = array("status" => "E", "message" => "Invalid token!");
			return json_encode($result);
		}
	}

	public function GetConfig(Request $request)
	{
		$db = $this->db;
		$projectCode = Request::input('projectcode');
		$appid = Request::input('appid');
		$updatedat = Request::input('LastSynctime');
		//$new_timestamp=strtotime("-12 hour 30 minute", $source_timestamp);
		$apikey = Request::input('apikey');
		$formconfig = Request::input('formconfig');
		//$json = DB::Table($db.'.form_configs')->where('projectcode',$projectCode)->where('created_at','>=',$updatedat)->get();
		$json = DB::Table($db . '.form_configs')->where('projectcode', $projectCode)->get();
		$token = Request::input('token');
		if ($token == '7f30f4491cb4435984616d1913e88389') {
			if ($json->isEmpty()) {
				$result = array("status" => "E", "message" => "Data Not Found!");
				echo json_encode($result);
			} else {
				foreach ($json as $row) {
					$id = $row->id;
					$projectCode = $row->projectcode;
					$formid = $row->formID;
					$grouplabel = $row->groupLabel;
					$lebel =  $row->lebel;
					if (!empty($lebel)) {
						$lbl = json_decode($lebel);
						$enlbl = $lbl->english;
						$enlbn = $lbl->bangla;
					}

					$datatype = $row->dataType;
					$columntype = $row->columnType;
					$displayorder = $row->displayOrder;
					$status = $row->status;
					$groupno = $row->groupNo;
					$createdby = $row->createdby;
					$created_at = $row->created_at;
					$updated_at = $row->updated_at;
					$loanProduct = $row->loanProduct;
					// $getdatatye = DB::select(DB::raw("select * from $db.popup_models where label='$enlbl' and datatype='$datatype'"));
					// if (empty($getdatatye)) {
					// 	$values = '';
					// } else {
					// 	$values = $getdatatye[0]->values;
					// }
					$jsnarray[] = array(
						"id" => $id, "projectcode" => $projectCode, "formID" => $formid, "loanProduct" => $loanProduct, "groupLabel" => $grouplabel, "lebel" => $lebel, "values" => $row->values, "dataType" => $datatype, "columnType" => $columntype,
						"displayOrder" => $displayorder, "status" => $status, "groupNo" => $groupno, "createdby" => $createdby, "created_at" => $created_at, "updated_at" => $updated_at, 'captions' => $row->captions
					);
				}
				//echo json_encode($jsnarray);
				// $result = array("status" => "S", "message" => "", "data" => $jsnarray);
				$result = array("status" => "S", "message" => "", "data" => $json);
				echo json_encode($result);
			}
		} else {
			$result = array("status" => "E", "message" => "Invalid token!");
			return json_encode($result);
		}
	}
	public function Auth(Request $request)
	{
		$db = $this->db;
		$projectCode = Request::input('projectcode');
		$appid = Request::input('appid');
		$processId = Request::input('processId');
		$apikey = Request::input('apikey');
		$json = DB::Table($db . '.auths')->where('projectcode', $projectCode)->where('processId', $processId)->get();
		$token = Request::input('token');
		if ($token == '7f30f4491cb4435984616d1913e88389') {
			if ($json->isEmpty()) {
				$result = array("status" => "E", "message" => "Data Not Found!");
				echo json_encode($result);
			} else {
				$result = array("status" => "S", "message" => "", "data" => $json);
				echo json_encode($result);
			}
		} else {
			$result = array("status" => "E", "message" => "Invalid token!");
			return json_encode($result);
		}
	}

	public function NIDVerification(Request $req)
	{
		$db = $this->db;
		$appid = Request::input('appid');
		$apikey = Request::input('apikey');
		$nid =  Request::input('nid');
		$nidverificationcheck = DB::Table($db . '.nids')->where('nidno', $nid)->get();
		$token = Request::input('token');
		if ($token == '7f30f4491cb4435984616d1913e88389') {
			if ($nidverificationcheck->isEmpty()) {
				$result = array("status" => "E", "message" => "Data Not Found!");
				echo json_encode($result);
			} else {
				$result = array("status" => "S", "message" => "", "data" => $nidverificationcheck);
				echo json_encode($result);
			}
		} else {
			$result = array("status" => "E", "message" => "Invalid token!");
			return json_encode($result);
		}
	}

	public function ImageUpload(Request $req)
	{
		$db = $this->db;
		$appid = Request::input('appid');
		$apikey = Request::input('apikey');
		$image = Request::input('file');
		$uploaddir = '/var/www/html/brac/mnw/uploads/';
		$baseurl = 'http://scm.brac.net/brac/mnw/uploads/';
		$time = date('Y-m-d h:i:s');
		$uploadfile = $uploaddir . $time . basename($_FILES['file']['name']);
		$responsefile = $baseurl . $time . basename($_FILES['file']['name']);
		if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
			$result = array("status" => "S", "message" => "", "data" => $responsefile);
			echo json_encode($result);
		} else {
			$result = array("status" => "E", "message" => "Failed Upload");
			echo json_encode($result);
		}
	}

	public function SurveyStore(Request $request)
	{
		$db = $this->db;
		// $json = '{"token":"xxxxxxxxx","appid":"bmsm","data":[{"entollmentid":"12321","projectcode":"015","voCode":"123","branch_code":"321",
		// 	"client_name":"Rz Tutul","mainid_type":"Smart ID","mainid_number":"123456789","phone":"01726553589","status":"Potential","label":"High","fdate":"June 28, 2021","reffered_by":"tutul"}],"extra":[{"fieldName":"Location","fieldType":"input","fieldValue":"Mirpur-2"},{"fieldName":"City","fieldType":"input","fieldValue":"Dhaka"}]
		// 	}';
		$json = Request::input('json');
		Log::channel('daily')->info('Survey Data: ' . $json);
		$dataset = json_decode($json);
		// $token = $dataset->token;
		// $appid = $dataset->appid;
		$data = $dataset->data[0];
		$dynamicfieldvalue = $dataset->extra;
		$projectcode = $data->projectcode;
		$projectcode = str_pad($projectcode, 3, "0", STR_PAD_LEFT);
		$token = Request::input('token');
		if ($token == '7f30f4491cb4435984616d1913e88389') {
			$entollmentid = $data->entollmentid;
			$branchcode = $data->branch_code;
			$branchcode = str_pad($branchcode, 4, "0", STR_PAD_LEFT); //
			$name = $data->client_name;
			$mainidtypeid = $data->mainid_type;
			$idno = $data->mainid_number;
			$phone = $data->phone;
			$status = $data->status;
			$label = $data->label;
			$targetdate = $data->fdate;
			$targetdate = date_create($targetdate);
			$targetdate = date_format($targetdate, "Y-m-d");
			$assignedpo = $data->pin;
			$refferdbyid = $data->reffered_by;
			$orgno = $data->vo_code;
			if ($dynamicfieldvalue == '') {
				$dynamicfieldvalue = null;
			}

			DB::Table($db . '.surveys')->insert(['entollmentid' => $entollmentid, 'name' => $name, 'mainidtypeid' => $mainidtypeid, 'idno' => $idno, 'phone' => $phone, 'status' => $status, 'label' => $label, 'targetdate' => $targetdate, 'refferdbyid' => $refferdbyid, 'dynamicfieldvalue' => $dynamicfieldvalue, 'projectcode' => $projectcode, 'branchcode' => $branchcode, 'assignedpo' => $assignedpo, 'orgno' => $orgno]);

			$result = array("status" => "S", "message" => "Data send to server");
			echo json_encode($result);
		} else {
			$result = array("status" => "E", "message" => "Invalid token!");
			return json_encode($result);
		}
		// if($token=='xxxxxxxxx'){
		// if($projectcode=='015'){

		// }else{
		// 	$result = array("status"=>"E","message"=>"","Please check project");
		// 	echo json_encode($result);
		// }
		// }else{
		// 	$result = array("status"=>"E","message"=>"","Unauthorized Request");
		// 	echo json_encode($result);
		// }

	}

	public function AdmissionStore(Request $request)
	{
		$db = $this->db;
		$baseUrl = url('');
		$json = Request::input('json');
		$currentTime = date('Y-m-d h:i:s');
		Log::channel('daily')->info('Admission Data: ' . $json);
		$dataset = json_decode($json);
		// $token = $dataset->token;
		$Flag = $dataset->flag;
		// $appid=$dataset->appid;
		$data = $dataset->data[0];
		$dynamicfieldvalue = $dataset->extra;
		$projectcode = $data->project_code;
		$projectcode = str_pad($projectcode, 3, "0", STR_PAD_LEFT);
		$token = Request::input('token');
		if ($token == '7f30f4491cb4435984616d1913e88389') {
			$roleid = 0;
			$reciverrole = 1;
			$status = 1;
			$orgno = $data->vo_code; //
			$entollmentid = $data->enroll_id;
			$MemberId = $data->erp_mem_id;
			$pin = $data->pin;
			$assignedpo = $data->pin;
			$branchcode = $data->branch_code;
			$branchcode = str_pad($branchcode, 4, "0", STR_PAD_LEFT); //
			$IsRefferal = $data->is_ref;
			$RefferedById = $data->refby;
			$ReffererName = $data->refname;
			$ReffererPhone = $data->refphone;
			$MemberCateogryId = $data->mem_category;
			$ApplicantsName = $data->applicant_name;
			$MainIdTypeId = $data->mainid_type;
			$IdNo = $data->mainid_number;
			$OtherIdTypeId = $data->other_idtype;
			$OtherIdNo = $data->other_idnumber;
			$ExpiryDate = $data->expiredate;
			$IssuingCountry = $data->place_ofissue;
			$DOB = $data->dob;
			$MotherName = $data->mother_name;
			$FatherName = $data->father_name;
			$EducationId = $data->education;
			$Occupation = $data->occupation;
			$Phone = $data->phone;
			$IsBkash = $data->isbkash;
			$WalletNo = $data->wallet_no;
			$WalletOwner = $data->wallet_owner;
			$PresentAddress = $data->present_adds;
			$presentUpazilaId = $data->present_upazila;
			$PresentDistrictId = $data->presentDistrictId;
			$PermanentAddress = $data->permanent_adds;
			$parmanentUpazilaId = $data->permanent_upazila;
			$MaritalStatusId = $data->matrial;
			$SpouseName = $data->spouse_name;
			$SpouseNidOrBid = $data->spouse_nid;
			$SposeDOB = $data->spouse_dob;
			$SpuseOccupationId = $data->spouse_occ;
			$FamilyMemberNo = $data->total_family_mem;
			$NoOfChildren = $data->total_child;
			$NomineeName = $data->nominee_name;
			$NomineeDOB = $data->nominee_dob;
			$RelationshipId = $data->relationship;
			$PrimaryEarner = $data->primary_earner;
			$ApplicantCpmbinedImg = $data->applicant_photo;
			$ReffererImg = $data->ref_photo;
			$ReffererIdImg = $data->refid_photo;
			$FrontSideOfIdImg = $data->nidfront_photo;
			$BackSideOfIdimg = $data->nidback_photo;
			$NomineeIdImg = $data->nominee_nid_photo;
			$SpuseIdImg = $data->spouse_nid_photo;
			$NomineeNidNo = $data->nominee_nid_no;
			$NomineeNidType = $data->nominee_nid_type;
			$NomineeNidFront = $data->nominee_nid_front;
			$NomineeNidBack = $data->nominee_nid_back;
			$SpouseNidFront = $data->spouse_nid_front;
			$SpouseNidBack = $data->spouse_nid_back;
			$PassbookRequired = $data->passbook_required;
			$GenderId = $data->genderid;
			$SavingsProductId = $data->savingsProductId;
			$NomineeIdExpiredate = $data->nominee_id_expiredate;
			$NomineeIdPlaceOfissue	= $data->nominee_id_place_ofissue;
			$NomineePhoneNumber = $data->nominee_phone_number;
			$SpouseCardType = $data->spouse_card_type;
			$SpouseIdExpiredate = $data->spouse_id_expiredate;
			$SpouseIdPlaceOfissue = $data->spouse_id_place_ofissue;
			$ApplicantSinglePic = $data->applicant_single_pic;
			$TargetAmount = $data->targetAmount;
			$PermanentDistrictId = $data->permanentDistrictId;
			$IsSameAddress = $data->is_same_addss;
			$surveyid = $data->surveyid;
			// $dynamicfieldvalue=json_encode($extra);
			if ($dynamicfieldvalue == '') {
				$dynamicfieldvalue = null;
			}

			$checkData = DB::table($db . '.admissions')->where('entollmentid', $entollmentid)->first();

			if ($checkData == null) {
				$doc_id = DB::Table($db . '.admissions')->insertGetId(['IsRefferal' => $IsRefferal, 'RefferedById' => $RefferedById, 'ReffererName' => $ReffererName, 'ReffererPhone' => $ReffererPhone, 'MemberCateogryId' => $MemberCateogryId, 'ApplicantsName' => $ApplicantsName, 'MainIdTypeId' => $MainIdTypeId, 'IdNo' => $IdNo, 'OtherIdTypeId' => $OtherIdTypeId, 'OtherIdNo' => $OtherIdNo, 'ExpiryDate' => $ExpiryDate, 'IssuingCountry' => $IssuingCountry, 'DOB' => $DOB, 'MotherName' => $MotherName, 'FatherName' => $FatherName, 'EducationId' => $EducationId, 'Occupation' => $Occupation, 'Phone' => $Phone, 'IsBkash' => $IsBkash, 'WalletNo' => $WalletNo, 'WalletOwner' => $WalletOwner, 'PresentAddress' => $PresentAddress, 'presentUpazilaId' => $presentUpazilaId, 'PermanentAddress' => $PermanentAddress, 'parmanentUpazilaId' => $parmanentUpazilaId, 'MaritalStatusId' => $MaritalStatusId, 'SpouseName' => $SpouseName, 'SpouseNidOrBid' => $SpouseNidOrBid, 'SposeDOB' => $SposeDOB, 'SpuseOccupationId' => $SpuseOccupationId, 'FamilyMemberNo' => $FamilyMemberNo, 'NoOfChildren' => $NoOfChildren, 'NomineeName' => $NomineeName, 'NomineeDOB' => $NomineeDOB, 'RelationshipId' => $RelationshipId, 'PrimaryEarner' => $PrimaryEarner, 'ApplicantCpmbinedImg' => $ApplicantCpmbinedImg, 'ReffererImg' => $ReffererImg, 'ReffererIdImg' => $ReffererIdImg, 'FrontSideOfIdImg' => $FrontSideOfIdImg, 'BackSideOfIdimg' => $BackSideOfIdimg, 'NomineeIdImg' => $NomineeIdImg, 'SpuseIdImg' => $SpuseIdImg, 'DynamicFieldValue' => $dynamicfieldvalue, 'projectcode' => $projectcode, 'branchcode' => $branchcode, 'pin' => $pin, 'roleid' => $roleid, 'reciverrole' => $reciverrole, 'status' => $status, 'orgno' => $orgno, 'assignedpo' => $assignedpo, 'NomineeNidNo' => $NomineeNidNo, 'NomineeNidFront' => $NomineeNidFront, 'NomineeNidBack' => $NomineeNidBack, 'SpouseNidFront' => $SpouseNidFront, 'SpouseNidBack' => $SpouseNidBack, 'PassbookRequired' => $PassbookRequired, 'entollmentid' => $entollmentid, 'GenderId' => $GenderId, 'SavingsProductId' => $SavingsProductId, 'NomineeIdExpiredate' => $NomineeIdExpiredate, 'NomineeIdPlaceOfissue' => $NomineeIdPlaceOfissue, 'NomineePhoneNumber' => $NomineePhoneNumber, 'SpouseCardType' => $SpouseCardType, 'SpouseIdExpiredate' => $SpouseIdExpiredate, 'SpouseIdPlaceOfissue' => $SpouseIdPlaceOfissue, 'Flag' => $Flag, 'ApplicantSinglePic' => $ApplicantSinglePic, 'TargetAmount' => $TargetAmount, 'PermanentDistrictId' => $PermanentDistrictId, 'NomineeNidType' => $NomineeNidType, 'MemberId' => $MemberId, 'IsSameAddress' => $IsSameAddress, 'PresentDistrictId' => $PresentDistrictId, 'surveyid' => $surveyid]);
			} else {
				$doc_id = $checkData->id;
				DB::Table($db . '.admissions')->where('entollmentid', $entollmentid)->update(['IsRefferal' => $IsRefferal, 'RefferedById' => $RefferedById, 'ReffererName' => $ReffererName, 'ReffererPhone' => $ReffererPhone, 'MemberCateogryId' => $MemberCateogryId, 'ApplicantsName' => $ApplicantsName, 'MainIdTypeId' => $MainIdTypeId, 'IdNo' => $IdNo, 'OtherIdTypeId' => $OtherIdTypeId, 'OtherIdNo' => $OtherIdNo, 'ExpiryDate' => $ExpiryDate, 'IssuingCountry' => $IssuingCountry, 'DOB' => $DOB, 'MotherName' => $MotherName, 'FatherName' => $FatherName, 'EducationId' => $EducationId, 'Occupation' => $Occupation, 'Phone' => $Phone, 'IsBkash' => $IsBkash, 'WalletNo' => $WalletNo, 'WalletOwner' => $WalletOwner, 'PresentAddress' => $PresentAddress, 'presentUpazilaId' => $presentUpazilaId, 'PermanentAddress' => $PermanentAddress, 'parmanentUpazilaId' => $parmanentUpazilaId, 'MaritalStatusId' => $MaritalStatusId, 'SpouseName' => $SpouseName, 'SpouseNidOrBid' => $SpouseNidOrBid, 'SposeDOB' => $SposeDOB, 'SpuseOccupationId' => $SpuseOccupationId, 'FamilyMemberNo' => $FamilyMemberNo, 'NoOfChildren' => $NoOfChildren, 'NomineeName' => $NomineeName, 'NomineeDOB' => $NomineeDOB, 'RelationshipId' => $RelationshipId, 'PrimaryEarner' => $PrimaryEarner, 'ApplicantCpmbinedImg' => $ApplicantCpmbinedImg, 'ReffererImg' => $ReffererImg, 'ReffererIdImg' => $ReffererIdImg, 'FrontSideOfIdImg' => $FrontSideOfIdImg, 'BackSideOfIdimg' => $BackSideOfIdimg, 'NomineeIdImg' => $NomineeIdImg, 'SpuseIdImg' => $SpuseIdImg, 'DynamicFieldValue' => $dynamicfieldvalue, 'projectcode' => $projectcode, 'branchcode' => $branchcode, 'pin' => $pin, 'roleid' => $roleid, 'reciverrole' => $reciverrole, 'status' => $status, 'orgno' => $orgno, 'assignedpo' => $assignedpo, 'NomineeNidNo' => $NomineeNidNo, 'NomineeNidFront' => $NomineeNidFront, 'NomineeNidBack' => $NomineeNidBack, 'SpouseNidFront' => $SpouseNidFront, 'SpouseNidBack' => $SpouseNidBack, 'PassbookRequired' => $PassbookRequired, 'entollmentid' => $entollmentid, 'GenderId' => $GenderId, 'SavingsProductId' => $SavingsProductId, 'NomineeIdExpiredate' => $NomineeIdExpiredate, 'NomineeIdPlaceOfissue' => $NomineeIdPlaceOfissue, 'NomineePhoneNumber' => $NomineePhoneNumber, 'SpouseCardType' => $SpouseCardType, 'SpouseIdExpiredate' => $SpouseIdExpiredate, 'SpouseIdPlaceOfissue' => $SpouseIdPlaceOfissue, 'Flag' => $Flag, 'ApplicantSinglePic' => $ApplicantSinglePic, 'TargetAmount' => $TargetAmount, 'PermanentDistrictId' => $PermanentDistrictId, 'NomineeNidType' => $NomineeNidType, 'MemberId' => $MemberId, 'IsSameAddress' => $IsSameAddress, 'PresentDistrictId' => $PresentDistrictId, 'updated_at' => $currentTime, 'surveyid' => $surveyid]);
			}


			if ($Flag == 1) {
				$document_url = $baseUrl . "/DocumentManager?doc_id=$doc_id&projectcode=$projectcode&doc_type=admission&pin=$pin&role=0&branchcode=$branchcode&action=Request";
			} elseif ($Flag == 2) {
				$document_url = $baseUrl . "/DocumentManager?doc_id=$doc_id&projectcode=$projectcode&doc_type=admission&pin=$pin&role=0&branchcode=$branchcode&action=Modify";
			}

			Log::channel('daily')->info('Document_url : ' . $document_url);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $document_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, false);
			$documentoutput = curl_exec($ch);
			curl_close($ch);

			$collectionfordocument = json_decode($documentoutput);

			Log::channel('daily')->info('document_url : ' . $document_url);
			Log::channel('daily')->info('document_response : ' . $documentoutput);

			$notification_url = $baseUrl . "/NotificatioManager?projectcode=$projectcode&doc_type=admission&pin=$pin&role=0&branchcode=$branchcode&entollmentid=$entollmentid&action=Request";
			// echo $notification_url;
			Log::channel('daily')->info('notification_url : ' . $notification_url);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $notification_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, false);
			$notificationoutput = curl_exec($ch);
			curl_close($ch);

			$collectionfornotification = json_decode($notificationoutput);

			Log::channel('daily')->info('notification_response : ' . $notificationoutput);
			// dd($collection);
			if ($collectionfornotification->status == 'S' and $collectionfordocument->status == 'S') {
				$result = array("status" => "S", "message" => "Data send to server");
				echo json_encode($result);
			}
		} else {
			$result = array("status" => "E", "message" => "Invalid token!");
			return json_encode($result);
		}
		// if($token=='xxxxxxxxx'){
		// 	if($projectcode=='015'){



		// 	}else{
		// 		$result = array("status"=>"E","message"=>"","Please check project");
		// 		echo json_encode($result);
		// 	}
		// }else{
		// 	$result = array("status"=>"E","message"=>"","Unauthorized Request");
		// 	echo json_encode($result);
		// }

	}

	public function BmAdmissionAssessment(Request $request)
	{
		$db = $this->db;
		$json = Request::input('json');
		Log::channel('daily')->info('Bm Assessment Admission Data: ' . $json);
		$dataset = json_decode($json);
		$token = $dataset->token;
		// $appid=$dataset->appid;
		$data = $dataset->admission[0];
		$projectcode = $data->project_code;
		$projectcode = str_pad($projectcode, 3, "0", STR_PAD_LEFT);
		$token = Request::input('token');
		if ($token == '7f30f4491cb4435984616d1913e88389') {
			$orgno = $data->vo_code;
			$pin = $data->pin;
			$branchcode = $data->branch_code;
			$branchcode = str_pad($branchcode, 4, "0", STR_PAD_LEFT); //
			$entollmentid = $data->mem_id;
			$MemberId = $data->erp_mem_id;
			$bm_behavior = $data->behavior;
			$bm_financial_status = $data->financial_status;
			$bm_client_house_image = $data->client_house_image;
			$bm_lat = $data->lat;
			$bm_lng = $data->lng;

			// dd($dynamicfieldvalue);

			if ($entollmentid != null) {
				DB::Table($db . '.admissions')->where('entollmentid', $entollmentid)->update(['bm_behavior' => $bm_behavior, 'bm_financial_status' => $bm_financial_status, 'bm_client_house_image' => $bm_client_house_image, 'bm_lat' => $bm_lat, 'bm_lng' => $bm_lng]);
			} else {
				DB::Table($db . '.admissions')->where('MemberId', $MemberId)->update(['bm_behavior' => $bm_behavior, 'bm_financial_status' => $bm_financial_status, 'bm_client_house_image' => $bm_client_house_image, 'bm_lat' => $bm_lat, 'bm_lng' => $bm_lng]);
			}

			Log::channel('daily')->info('Bm Admission Assessment Successful.');
			$result = array("status" => "S", "message" => "Data saved");
			return json_encode($result);
		} else {
			$result = array("status" => "E", "message" => "Invalid token!");
			return json_encode($result);
		}
		// dd($data);
		// if($token=='xxxxxxxxx'){
		// 	if($projectcode=='015'){



		// 	}else{
		// 		$result = array("status"=>"E","message"=>"","Please check project");
		// 		echo json_encode($result);
		// 	}
		// }else{
		// 	$result = array("status"=>"E","message"=>"","Unauthorized Request");
		// 	echo json_encode($result);
		// }

	}

	public function BmLoanAssessment(Request $request)
	{
		$db = $this->db;
		// { "token": "xxxxxxxxx", "appid": "xxxxxxxxx", "loan_checklist": [{ "vo_code": "2050", "loan_id": "d920660e-f09e-4fc9-8f5c-34c9a43ebf4d", "erp_loan_id": null, "branch_code": "0607", "pin": "00196144", "project_code": "015", "mem_id": "886dc81c-528d-4b02-8031-c9513ebc8186", "erp_mem_id": null, "bm_repay_loan": 1, "bm_conduct_activity": 3, "bm_action_required": 1, "bm_rca_rating": 0 }], "rca": [{ "bm_monthlyincome_main": "2000", "bm_monthlyincome_spouse_child": "2000", "bm_monthlyincome_other": "555", "bm_house_rent": "40", "bm_food": "0", "bm_education": "0", "bm_medical": "0", "bm_festive": "0", "bm_utility": "0", "bm_saving": "0", "bm_other": "0", "bm_monthly_instal": "", "bm_debt": "855", "bm_monthly_cash": "2515", "bm_instal_proposloan": "466.67" }] }
		$json = Request::input('json');
		Log::channel('daily')->info('Bm Loan Assessment Data: ' . $json);
		$dataset = json_decode($json);
		$token = $dataset->token;
		// $appid=$dataset->appid;
		$dataLoan = $dataset->loan_checklist[0];
		$dataRca = $dataset->rca[0];
		$projectcode = $dataLoan->project_code;
		$projectcode = str_pad($projectcode, 3, "0", STR_PAD_LEFT);
		$token = Request::input('token');
		if ($token == '7f30f4491cb4435984616d1913e88389') {
			$orgno = $dataLoan->vo_code;
			$pin = $dataLoan->pin;
			$branchcode = $dataLoan->branch_code;
			$branchcode = str_pad($branchcode, 4, "0", STR_PAD_LEFT); //
			$mem_id = $dataLoan->mem_id;
			$MemberId = $dataLoan->erp_mem_id;
			$loan_id = $dataLoan->loan_id;
			//loan
			$bm_repay_loan = $dataLoan->bm_repay_loan;
			$bm_conduct_activity = $dataLoan->bm_conduct_activity;
			$bm_action_required = $dataLoan->bm_action_required;
			$bm_rca_rating = $dataLoan->bm_rca_rating;
			//loan new
			$bm_noofChild = $dataLoan->bm_noofChild;
			$bm_earningMember = $dataLoan->bm_earningMember;
			$bm_duration = $dataLoan->bm_duration;
			$bm_hometown = $dataLoan->bm_hometown;
			$bm_landloard = $dataLoan->bm_landloard;
			$bm_recomand = $dataLoan->bm_recomand;
			$bm_occupation = $dataLoan->bm_occupation;
			$bm_aware = $dataLoan->bm_aware;
			$bm_grantor = $dataLoan->bm_grantor;
			$bm_socialAcecptRating = $dataLoan->bm_socialAcecptRating;
			$bm_grantorRating = $dataLoan->bm_grantorRating;
			$bm_clienthouse = $dataLoan->bm_clienthouse;
			$bm_remarks = $dataLoan->bm_remarks;

			//rca
			$bm_monthlyincome_main = $dataRca->bm_monthlyincome_main;
			$bm_monthlyincome_spouse_child = $dataRca->bm_monthlyincome_spouse_child;
			$bm_monthlyincome_other = $dataRca->bm_monthlyincome_other;
			$bm_house_rent = $dataRca->bm_house_rent;
			$bm_food = $dataRca->bm_food;
			$bm_education = $dataRca->bm_education;
			$bm_medical = $dataRca->bm_medical;
			$bm_festive = $dataRca->bm_festive;
			$bm_utility = $dataRca->bm_utility;
			$bm_saving = $dataRca->bm_saving;
			$bm_other = $dataRca->bm_other;
			$bm_monthly_instal = $dataRca->bm_monthly_instal;
			$bm_debt = $dataRca->bm_debt;
			$bm_monthly_cash = $dataRca->bm_monthly_cash;
			$bm_instal_proposloan = $dataRca->bm_instal_proposloan;

			// dd($dynamicfieldvalue);

			if ($loan_id != null) {
				$loan_sl = Db::table($db . '.loans')->select('id')->where('loan_id', $loan_id)->first();
				$loan = $loan_sl->id;

				DB::Table($db . '.loans')->where('loan_id', $loan_id)->update(['bm_repay_loan' => $bm_repay_loan, 'bm_conduct_activity' => $bm_conduct_activity, 'bm_action_required' => $bm_action_required, 'bm_rca_rating' => $bm_rca_rating, 'bm_noofChild' => $bm_noofChild, 'bm_earningMember' => $bm_earningMember, 'bm_duration' => $bm_duration, 'bm_hometown' => $bm_hometown, 'bm_landloard' => $bm_landloard, 'bm_recomand' => $bm_recomand, 'bm_occupation' => $bm_occupation, 'bm_aware' => $bm_aware, 'bm_grantor' => $bm_grantor, 'bm_socialAcecptRating' => $bm_socialAcecptRating, 'bm_grantorRating' => $bm_grantorRating, 'bm_grantorRating' => $bm_grantorRating, 'bm_clienthouse' => $bm_clienthouse, 'bm_remarks' => $bm_remarks]);

				DB::Table($db . '.rca')->where('loan_id', $loan)->update([
					'bm_monthlyincome_main' => $bm_monthlyincome_main, 'bm_monthlyincome_spouse_child' => $bm_monthlyincome_spouse_child, 'bm_monthlyincome_other' => $bm_monthlyincome_other,
					'bm_house_rent' => $bm_house_rent, 'bm_food' => $bm_food, 'bm_education' => $bm_education, 'bm_medical' => $bm_medical, 'bm_festive' => $bm_festive, 'bm_utility' => $bm_utility, 'bm_saving' => $bm_saving, 'bm_other' => $bm_other, 'bm_monthly_instal' => $bm_monthly_instal, 'bm_debt' => $bm_debt, 'bm_monthly_cash' => $bm_monthly_cash, 'bm_instal_proposloan' => $bm_instal_proposloan
				]);
			} else {
				$result = array("status" => "E", "message" => "loan id can not be empty");
				return json_encode($result);
			}

			Log::channel('daily')->info('Bm Loan Assessment Successful.');
			$result = array("status" => "S", "message" => "Data saved");
			return json_encode($result);
		} else {
			$result = array("status" => "E", "message" => "Invalid token!");
			return json_encode($result);
		}
		// dd($dataRca);
		// if($token=='xxxxxxxxx'){
		// 	if($projectcode=='015'){



		// 	}else{
		// 		$result = array("status"=>"E","message"=>"","Please check project");
		// 		echo json_encode($result);
		// 	}
		// }else{
		// 	$result = array("status"=>"E","message"=>"","Unauthorized Request");
		// 	echo json_encode($result);
		// }

	}

	public function AllSurveyData(Request $request)
	{
		$db = $this->db;
		// $projectCode = Request::input('projectcode');
		// $appid = Request::input('appid');
		// $processId = Request::input('processId');
		// $apikey = Request::input('apikey');
		$json = DB::Table($db . '.surveys')->get();
		// dd($json);
		$token = Request::input('token');
		if ($token == '7f30f4491cb4435984616d1913e88389') {
			if ($json->isEmpty()) {
				$result = array("status" => "E", "message" => "Data Not Found!");
				echo json_encode($result);
			} else {
				$result = array("status" => "S", "message" => "", "data" => $json);
				echo json_encode($result);
			}
		} else {
			$result = array("status" => "E", "message" => "Invalid token!");
			return json_encode($result);
		}
	}

	public function AllAdmissionData(Request $request)
	{
		$db = $this->db;
		// $projectCode = Request::input('projectcode');
		// $appid = Request::input('appid');
		// $processId = Request::input('processId');
		// $apikey = Request::input('apikey');
		$json = DB::Table($db . '.admissions')->get();
		$token = Request::input('token');
		if ($token == '7f30f4491cb4435984616d1913e88389') {
			if ($json->isEmpty()) {
				$result = array("status" => "E", "message" => "Data Not Found!");
				echo json_encode($result);
			} else {
				$result = array("status" => "S", "message" => "", "data" => $json);
				echo json_encode($result);
			}
		} else {
			$result = array("status" => "E", "message" => "Invalid token!");
			return json_encode($result);
		}
	}

	public function LoanRcaDataStore(Request $request)
	{
		$db = $this->db;
		$baseUrl = url('');
		$json = Request::input('json');
		Log::channel('daily')->info('Loan Rca Data: ' . $json);
		$dataset = json_decode($json);
		// $token = $dataset->token;
		$data = $dataset->loan[0];
		$dataRca = $dataset->rca[0];
		$projectcode = $data->project_code;
		$projectcode = str_pad($projectcode, 3, "0", STR_PAD_LEFT);
		$token = Request::input('token');
		if ($token == '7f30f4491cb4435984616d1913e88389') {
			$roleid = 0;
			$reciverrole = 1;
			$status = 1;
			$orgno = $data->vo_code; //
			$branchcode = $data->branch_code;
			$loanid = $data->loan_id;
			$pin = $data->pin;
			$assignedpo = $data->pin;
			$branchcode = str_pad($branchcode, 4, "0", STR_PAD_LEFT); //
			$mem_id = $data->mem_id;
			$loan_product = $data->loan_product;
			$loan_duration = $data->loan_duration;
			$invest_sector = $data->invest_sector;
			$scheme = $data->scheme;
			$propos_amt = $data->propos_amt;
			$instal_amt = $data->instal_amt;
			$bracloan_family = $data->bracloan_family;
			$vo_leader = $data->vo_leader;
			$recommender = $data->recommender;
			$grntor_name = $data->grntor_name;
			$grntor_phone = $data->grntor_phone;
			$grntor_rlationClient = $data->grntor_rlationClient;
			$grntor_nid = $data->grntor_nid;
			$witness_knows = $data->witness_knows;
			$residence_type = $data->residence_type;
			$residence_duration = $data->residence_duration;
			$houseowner_knows = $data->houseowner_knows;
			$reltive_presAddress = $data->reltive_presAddress;
			$reltive_name = $data->reltive_name;
			$reltive_phone = $data->reltive_phone;
			$insurn_type = $data->insurn_type;
			$insurn_option = $data->insurn_option;
			$insurn_spouseName = $data->insurn_spouseName;
			$insurn_spouseNid = $data->insurn_spouseNid;
			$insurn_spouseDob = $data->insurn_spouseDob;
			$insurn_gender = $data->insurn_gender;
			$insurn_relation = $data->insurn_relation;
			$insurn_name = $data->insurn_name;
			$insurn_dob = $data->insurn_dob;
			$insurn_mainID = $data->insurn_mainID;
			$grantor_nidfront_photo = $data->grantor_nidfront_photo;
			$grantor_nidback_photo = $data->grantor_nidback_photo;
			$grantor_photo = $data->grantor_photo;
			$erp_mem_id = $data->erp_mem_id;
			$memberTypeId = $data->memberTypeId;
			$subSectorId = $data->subSectorId;
			$frequencyId = $data->frequencyId;
			$insurn_mainIDType = $data->insurn_mainIDType;
			$insurn_id_expire = $data->insurn_id_expire;
			$insurn_placeofissue = $data->insurn_placeofissue;
			$dynamicfieldvalueLoan = $data->extra;
			$surveyid = $data->surveyid;
			$orgmemno = $data->orgmemno;
			$amount_inword = $data->amount_inword;
			$loan_purpose = $data->loan_purpose;
			$loan_user = $data->loan_user;
			$loan_type = $data->loan_type;
			$brac_loancount = $data->brac_loancount;

			if ($dynamicfieldvalueLoan == '') {
				$dynamicfieldvalueLoan = null;
			}
			// $dynamicfieldvalueLoan=json_encode($loanjson);
			$checkData = DB::table($db . '.loans')->where('loan_id', $loanid)->first();
			// DB::beginTransaction();
			// try {

			if ($checkData == null) {
				$doc_id = DB::Table($db . '.loans')->insertGetId(['mem_id' => $mem_id, 'loan_product' => $loan_product, 'loan_duration' => $loan_duration, 'invest_sector' => $invest_sector, 'propos_amt' => $propos_amt, 'instal_amt' => $instal_amt, 'bracloan_family' => $bracloan_family, 'vo_leader' => $vo_leader, 'recommender' => $recommender, 'grntor_name' => $grntor_name, 'grntor_phone' => $grntor_phone, 'grntor_rlationClient' => $grntor_rlationClient, 'grntor_nid' => $grntor_nid, 'witness_knows' => $witness_knows, 'residence_type' => $residence_type, 'residence_duration' => $residence_duration, 'houseowner_knows' => $houseowner_knows, 'reltive_presAddress' => $reltive_presAddress, 'reltive_name' => $reltive_name, 'reltive_phone' => $reltive_phone, 'insurn_type' => $insurn_type, 'insurn_option' => $insurn_option, 'insurn_spouseName' => $insurn_spouseName, 'insurn_spouseNid' => $insurn_spouseNid, 'insurn_spouseDob' => $insurn_spouseDob, 'insurn_gender' => $insurn_gender, 'insurn_relation' => $insurn_relation, 'insurn_name' => $insurn_name, 'insurn_dob' => $insurn_dob, 'insurn_mainID' => $insurn_mainID, 'grantor_nidfront_photo' => $grantor_nidfront_photo, 'grantor_nidback_photo' => $grantor_nidback_photo, 'grantor_photo' => $grantor_photo, 'DynamicFieldValue' => $dynamicfieldvalueLoan, 'projectcode' => $projectcode, 'branchcode' => $branchcode, 'pin' => $pin, 'roleid' => $roleid, 'reciverrole' => $reciverrole, 'loan_id' => $loanid, 'assignedpo' => $assignedpo, 'orgno' => $orgno, 'erp_mem_id' => $erp_mem_id, 'scheme' => $scheme, "memberTypeId" => $memberTypeId, "subSectorId" => $subSectorId, "frequencyId" => $frequencyId, "insurn_mainIDType" => $insurn_mainIDType, "insurn_id_expire" => $insurn_id_expire, "insurn_placeofissue" => $insurn_placeofissue, 'surveyid' => $surveyid, 'amount_inword' => $amount_inword, 'loan_purpose' => $loan_purpose, 'loan_user' => $loan_user, 'loan_type' => $loan_type, 'brac_loancount' => $brac_loancount, 'orgmemno' => $orgmemno]);
			} else {
				$doc_id = $checkData->id;
				$loan_id = $checkData->loan_id;
				DB::Table($db . '.loans')->where('loan_id', $loan_id)->update(['mem_id' => $mem_id, 'loan_product' => $loan_product, 'loan_duration' => $loan_duration, 'invest_sector' => $invest_sector, 'propos_amt' => $propos_amt, 'instal_amt' => $instal_amt, 'bracloan_family' => $bracloan_family, 'vo_leader' => $vo_leader, 'recommender' => $recommender, 'grntor_name' => $grntor_name, 'grntor_phone' => $grntor_phone, 'grntor_rlationClient' => $grntor_rlationClient, 'grntor_nid' => $grntor_nid, 'witness_knows' => $witness_knows, 'residence_type' => $residence_type, 'residence_duration' => $residence_duration, 'houseowner_knows' => $houseowner_knows, 'reltive_presAddress' => $reltive_presAddress, 'reltive_name' => $reltive_name, 'reltive_phone' => $reltive_phone, 'insurn_type' => $insurn_type, 'insurn_option' => $insurn_option, 'insurn_spouseName' => $insurn_spouseName, 'insurn_spouseNid' => $insurn_spouseNid, 'insurn_spouseDob' => $insurn_spouseDob, 'insurn_gender' => $insurn_gender, 'insurn_relation' => $insurn_relation, 'insurn_name' => $insurn_name, 'insurn_dob' => $insurn_dob, 'insurn_mainID' => $insurn_mainID, 'grantor_nidfront_photo' => $grantor_nidfront_photo, 'grantor_nidback_photo' => $grantor_nidback_photo, 'grantor_photo' => $grantor_photo, 'DynamicFieldValue' => $dynamicfieldvalueLoan, 'projectcode' => $projectcode, 'branchcode' => $branchcode, 'pin' => $pin, 'roleid' => $roleid, 'reciverrole' => $reciverrole, 'loan_id' => $loanid, 'assignedpo' => $assignedpo, 'orgno' => $orgno, 'erp_mem_id' => $erp_mem_id, 'scheme' => $scheme, "memberTypeId" => $memberTypeId, "subSectorId" => $subSectorId, "frequencyId" => $frequencyId, "insurn_mainIDType" => $insurn_mainIDType, "insurn_id_expire" => $insurn_id_expire, "insurn_placeofissue" => $insurn_placeofissue, 'surveyid' => $surveyid, 'amount_inword' => $amount_inword, 'loan_purpose' => $loan_purpose, 'loan_user' => $loan_user, 'loan_type' => $loan_type, 'brac_loancount' => $brac_loancount, 'orgmemno' => $orgmemno]);
			}

			Log::channel('daily')->info('Loan Rca Data check: ' . $checkData);

			$primary_earner = $dataRca->primary_earner;
			$monthlyincome_main = $dataRca->monthlyincome_main;
			$monthlyincome_other = $dataRca->monthlyincome_other;
			$house_rent = $dataRca->house_rent;
			$food = $dataRca->food;
			$education = $dataRca->education;
			$medical = $dataRca->medical;
			$festive = $dataRca->festive;
			$utility = $dataRca->utility;
			$saving = $dataRca->saving;
			$other = $dataRca->other;
			$monthly_instal = $dataRca->monthly_instal;
			$debt = $dataRca->debt;
			$monthly_cash = $dataRca->monthly_cash;
			$monthlyincome_spouse_child = $dataRca->monthlyincome_spouse_child;
			$instal_proposloan = $dataRca->instal_proposloan;
			$dynamicfieldvalueRca = $dataRca->extra;
			if ($dynamicfieldvalueRca == '') {
				$dynamicfieldvalueRca = null;
			}
			// $dynamicfieldvalueRca=json_encode($rcajson);
			// dd($checkData);
			if ($checkData == null) {
				DB::Table($db . '.rca')->insert(['loan_id' => $doc_id, 'primary_earner' => $primary_earner, 'monthlyincome_main' => $monthlyincome_main, 'monthlyincome_other' => $monthlyincome_other, 'house_rent' => $house_rent, 'food' => $food, 'education' => $education, 'medical' => $medical, 'festive' => $festive, 'utility' => $utility, 'saving' => $saving, 'other' => $other, 'monthly_instal' => $monthly_instal, 'debt' => $debt, 'monthly_cash' => $monthly_cash, 'instal_proposloan' => $instal_proposloan, 'DynamicFieldValue' => $dynamicfieldvalueRca, 'monthlyincome_spouse_child' => $monthlyincome_spouse_child]);
			} else {
				DB::Table($db . '.rca')->where('loan_id', $doc_id)->update(['loan_id' => $doc_id, 'primary_earner' => $primary_earner, 'monthlyincome_main' => $monthlyincome_main, 'monthlyincome_other' => $monthlyincome_other, 'house_rent' => $house_rent, 'food' => $food, 'education' => $education, 'medical' => $medical, 'festive' => $festive, 'utility' => $utility, 'saving' => $saving, 'other' => $other, 'monthly_instal' => $monthly_instal, 'debt' => $debt, 'monthly_cash' => $monthly_cash, 'instal_proposloan' => $instal_proposloan, 'DynamicFieldValue' => $dynamicfieldvalueRca, 'monthlyincome_spouse_child' => $monthlyincome_spouse_child]);
			}
			// } catch (\Throwable $e) {
			// 	DB::rollback();
			// 	Log::channel('daily')->info('loan data store error: ' . $e);

			// 	$result = array("status" => "E", "message" => $e);
			// 	return json_encode($result);
			// }


			$document_url = $baseUrl . "/DocumentManager?doc_id=$doc_id&projectcode=$projectcode&doc_type=loan&pin=$pin&role=0&branchcode=$branchcode&action=Request";
			// dd($document_url);
			// $document_url = "http://scm.brac.net/dcs/DocumentManager?doc_id=1&projectcode=$projectcode&doc_type=admission&pin=$pin&role=0&branchcode=$branchcode";
			// echo $document_url;
			Log::channel('daily')->info('Document_url : ' . $document_url);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $document_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, false);
			$documentoutput = curl_exec($ch);
			curl_close($ch);

			$collectionfordocument = json_decode($documentoutput);

			Log::channel('daily')->info('Document_response : ' . $documentoutput);
			// dd($collection);
			// if($collectionfordocument->status=='S'){
			// 	$result = array("status"=>"S","message"=>"Data send to server");
			// 	echo json_encode($result);
			// }

			$notification_url = $baseUrl . "/NotificatioManager?projectcode=$projectcode&doc_type=loan&pin=$pin&role=0&branchcode=$branchcode&entollmentid=$loanid&action=Request";
			// echo $notification_url;
			Log::channel('daily')->info('notification_url : ' . $notification_url);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $notification_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, false);
			$notificationoutput = curl_exec($ch);
			curl_close($ch);

			$collectionfornotification = json_decode($notificationoutput);

			Log::channel('daily')->info('notification_response : ' . $notificationoutput);

			// dd($collection);
			if ($collectionfornotification->status == 'S' and $collectionfordocument->status == 'S') {
				$result = array("status" => "S", "message" => "Data send to server");
				echo json_encode($result);
			}
		} else {
			$result = array("status" => "E", "message" => "Invalid token!");
			return json_encode($result);
		}
		// if($token=='xxxxxxxxx'){
		// 	if($projectcode=='015'){

		// 	}else{
		// 		$result = array("status"=>"E","message"=>"","Please check project");
		// 		echo json_encode($result);
		// 	}
		// }else{
		// 	$result = array("status"=>"E","message"=>"","Unauthorized Request");
		// 	echo json_encode($result);
		// }

	}

	public function AllLoanRcaData(Request $request)
	{
		$db = $this->db;
		// $projectCode = Request::input('projectcode');
		// $appid = Request::input('appid');
		// $processId = Request::input('processId');
		// $apikey = Request::input('apikey');
		$loans = DB::Table($db . '.loans')->get();
		$rca = DB::Table($db . '.rca')->get();

		if ($loans->isEmpty()) {
			$result = array("status" => "E", "message" => "Data Not Found!");
			echo json_encode($result);
		} else {
			$result = array("status" => "S", "message" => "", "loans" => $loans, "rca" => $rca);
			echo json_encode($result);
		}
	}

	public function NotificationManager(Request $request)  //dummy
	{
		$db = $this->db;
		// $projectCode = Request::input('projectcode');
		// $appid = Request::input('appid');
		// $processId = Request::input('processId');
		// $apikey = Request::input('apikey');
		$result = array("status" => "E", "message" => "Data Not Found!");
		echo json_encode($result);
	}

	public function AdmissionDataSync(Request $request)
	{
		$db = $this->db;
		$branchcode = Request::input('branchcode');
		$projectcode = Request::input('projectcode');
		$pin = Request::input('pin');
		$branch__code = str_pad($branchcode, 4, "0", STR_PAD_LEFT);
		//after schuler have to remove this function call
		$this->GetErpPostedAdmissionData($branch__code); //erp dcs admission data sync 
		// dd(date('Y-m-d H:i:s'));

		$token = Request::input('token');
		if ($token == '7f30f4491cb4435984616d1913e88389') {
			if ($branchcode != null and $pin == null) {
				// $admissiondata = DB::table($db . '.admissions')->where('branchcode', $branchcode)->orderBy('id', 'desc')->get();
				$admissionsDataWithoutPending = DB::table($db . '.admissions')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->where('status', '!=', '1')->where('updated_at', '<=', Carbon::now()->format('Y-m-d H:i:s'))->where('updated_at', '>=', Carbon::now()->subMonth(6)->format('Y-m-d H:i:s'));
				$admissiondata = DB::table($db . '.admissions')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->Where('status', '1')->unionAll($admissionsDataWithoutPending)->orderBy('id', 'desc')->get();
			} elseif ($branchcode != null and $pin != null) {
				$admissionsDataWithoutPending = DB::table($db . '.admissions')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->where('assignedpo', $pin)->where('status', '!=', '1')->where('updated_at', '<=', Carbon::now()->format('Y-m-d H:i:s'))->where('updated_at', '>=', Carbon::now()->subMonth(6)->format('Y-m-d H:i:s'));
				$admissiondata = DB::table($db . '.admissions')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->where('assignedpo', $pin)->Where('status', '1')->unionAll($admissionsDataWithoutPending)->orderBy('id', 'desc')->get();
				// dd($admissiondata);
				// $admissiondata = DB::table($db . '.admissions')->where('branchcode', $branchcode)->where('assignedpo', $pin)->orderBy('id', 'desc')->get();
				// $admissiondata = DB::table($db . '.admissions')->where('branchcode', $branchcode)->where('assignedpo', $pin)->orWhere('status', 1)->orWhere(function ($query) {
				// 	$query->where('updated_at', '<=', Carbon::now()->subMonth(2)->format('Y-m-d H:i:s'))->where('updated_at', '>=', Carbon::now());
				// })->orderBy('id', 'desc')->get();

			} else {
				$result = array("status" => "E", "message" => "parameter missing!");
				echo json_encode($result);
			}

			if ($admissiondata->isEmpty()) {
				$result = array("status" => "E", "message" => "Data Not Found!");
				echo json_encode($result);
			} else {
				foreach ($admissiondata as $data) {
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

					$WalletOwner = $WalletOwner->data_name ?? null;
					$NomineeNidType = $NomineeNidType->data_name ?? null;
					$SpuseOccupationId = $SpuseOccupationId->data_name ?? null;
					$SpouseCardType = $SpouseCardType->data_name ?? null;
					$OtherIdTypeId = $OtherIdTypeId->data_name ?? null;
					$presentUpazilaId = $presentUpazilaId->thana_name ?? null;
					$parmanentUpazilaId = $parmanentUpazilaId->thana_name ?? null;
					$PresentDistrictId = $PresentDistrictId->district_name ?? null;
					$PermanentDistrictId = $PermanentDistrictId->district_name ?? null;
					if ($dochistory == null) {
						Log::channel('daily')->info('document problem admission : ' . $data->id);
					}
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
						$checkPostedAdmission = DB::table($db . '.posted_admission')->where('admission_id', $data->entollmentid)->first();
						if ($checkPostedAdmission != null) {
							$ErpStatusId = $checkPostedAdmission->statusid;
							if ($ErpStatusId == 1) {
								$ErpStatus = 'Pending';
							} elseif ($ErpStatusId == 2) {
								$ErpStatus = 'Approved';
							} elseif ($ErpStatusId == 3) {
								$ErpStatus = 'Rejected';
							}
							$ErpRejectionReason = $checkPostedAdmission->rejectionreason;
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
					$created_at = date('Y-m-d', strtotime($data->created_at));
					$updated_at = date('Y-m-d', strtotime($data->updated_at));

					$arrayData = array(
						"id" => $data->id,
						"IsRefferal" => $data->IsRefferal,
						"RefferedById" => $data->RefferedById,
						"MemberId" => $data->MemberId,
						"MemberCateogryId" => $data->MemberCateogryId,
						"MemberCateogry" => $MemberCateogryId->categoryname,
						"ApplicantsName" => $data->ApplicantsName,
						"ApplicantSinglePic" => $data->ApplicantSinglePic,
						"MainIdType" => $MainIdTypeId->data_name,
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
						"Education" => $EducationId->data_name,
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
						"MaritalStatus" => $MaritalStatusId->data_name,
						"SpouseName" => $data->SpouseName,
						"SpouseCardType" => $SpouseCardType,
						"SpouseCardTypeId" => $data->SpouseCardType,
						"SpouseNidOrBid" => $data->SpouseNidOrBid,
						"SposeDOB" => $data->SposeDOB,
						"SpuseOccupationId" => $data->SpuseOccupationId,
						"SpuseOccupation" => $SpuseOccupationId,
						"SpouseNidFront" => $data->SpouseNidFront,
						"SpouseNidFront" => $data->SpouseNidFront,
						"ReffererName" => $data->ReffererName,
						"ReffererPhone" => $data->ReffererPhone,
						"FamilyMemberNo" => $data->FamilyMemberNo,
						"NoOfChildren" => $data->NoOfChildren,
						"NomineeDOB" => $data->NomineeDOB,
						"RelationshipId" => $data->RelationshipId,
						"Relationship" => $RelationshipId->data_name,
						"ApplicantCpmbinedImg" => $data->ApplicantCpmbinedImg,
						"ReffererImg" => $data->ReffererImg,
						"ReffererIdImg" => $data->ReffererIdImg,
						"FrontSideOfIdImg" => $data->FrontSideOfIdImg,
						"BackSideOfIdimg" => $data->BackSideOfIdimg,
						"NomineeIdImg" => $data->NomineeIdImg,
						"DynamicFieldValue" => $data->DynamicFieldValue,
						"created_at" => $created_at,
						"updated_at" => $updated_at,
						"branchcode" => $data->branchcode,
						"projectcode" => $data->projectcode,
						"Occupation" => $Occupation->data_name,
						"OccupationId" => $data->Occupation,
						"IsBkash" => $IsBkash,
						"WalletNo" => $data->WalletNo,
						"WalletOwnerId" => $data->WalletOwner,
						"WalletOwner" => $WalletOwner,
						"NomineeName" => $data->NomineeName,
						"PrimaryEarner" => $PrimaryEarner->data_name,
						"PrimaryEarnerId" => $data->PrimaryEarner,
						"dochistory_id" => $data->dochistory_id,
						"roleid" => $data->roleid,
						"pin" => $data->pin,
						"action" => $data->action,
						"reciverrole" => $data->reciverrole,
						"status" => $status->status_name,
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
						"Gender" => $genderId->data_name,
						"SavingsProductId" => $data->SavingsProductId,
						"role_name" => $role_name->designation,
						"reciverrole_name" => $recieverrole_name->designation,
						"SurveyId" => $data->surveyid,
						"Comment" => $comment,
						"ErpStatus" => $ErpStatus,
						"ErpStatusId" => $ErpStatusId,
						"ErpRejectionReason" => $ErpRejectionReason,
						"Flag" => $data->Flag
					);
					$admissiondataary[] = $arrayData;
				}
				$result = array("status" => "S", "message" => "", "data" => $admissiondataary);
				return json_encode($result);
			}
		} else {
			$result = array("status" => "E", "message" => "Invalid token!");
			return json_encode($result);
		}
	}

	public function LoanDataSync(Request $request)
	{
		$db = $this->db;
		$dberp = $this->dberp;
		$branchcode = Request::input('branchcode');
		$projectcode = Request::input('projectcode');
		$pin = Request::input('pin');
		$appid = Request::input('appid');
		$appversion = Request::input('appversion');
		$branch__code = str_pad($branchcode, 4, "0", STR_PAD_LEFT);
		//after schuler have to remove this function call
		$this->GetErpPostedLoanData($branch__code); //erp dcs Loan data sync 


		$dataset = [];
		$token = Request::input('token');
		if ($token == '7f30f4491cb4435984616d1913e88389') {
			if ($branchcode != null and $pin == null) {
				// $loandata = DB::table($db . '.loans')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->orderBy('id', 'desc')->get();
				$loansDataWithoutPending = DB::table($db . '.loans')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->where('status', '!=', '1')->where('updated_at', '<=', Carbon::now()->format('Y-m-d H:i:s'))->where('updated_at', '>=', Carbon::now()->subMonth(6)->format('Y-m-d H:i:s'));
				$loandata = DB::table($db . '.loans')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->Where('status', '1')->unionAll($loansDataWithoutPending)->orderBy('id', 'desc')->get();
			} elseif ($branchcode != null and $pin != null) {
				// $loandata = DB::table($db . '.loans')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->where('assignedpo', $pin)->orderBy('id', 'desc')->get();
				$loansDataWithoutPending = DB::table($db . '.loans')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->where('assignedpo', $pin)->where('status', '!=', '1')->where('updated_at', '<=', Carbon::now()->format('Y-m-d H:i:s'))->where('updated_at', '>=', Carbon::now()->subMonth(6)->format('Y-m-d H:i:s'));
				$loandata = DB::table($db . '.loans')->where('branchcode', $branchcode)->where('projectcode', $projectcode)->where('assignedpo', $pin)->Where('status', '1')->unionAll($loansDataWithoutPending)->orderBy('id', 'desc')->get();
			} else {
				$result = array("status" => "E", "message" => "parameter missing!");
				return json_encode($result);
			}

			if ($loandata->isEmpty()) {
				$result = array("status" => "E", "message" => "Data Not Found!");
				return json_encode($result);
			} else {
				foreach ($loandata as $data) {
					$grntorRlationClient = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'relationshipId')->where('data_id', $data->grntor_rlationClient)->first();
					$investSector = DB::table($db . '.schemem_sector_subsector')->select('sectorname')->where('sectorid', $data->invest_sector)->first();
					$subSectorId = DB::table($db . '.schemem_sector_subsector')->select('subsectorname')->where('subsectorid', $data->subSectorId)->first();
					$frequencyId = DB::table($db . '.product_details')->select('frequency')->where('frequencyid', $data->frequencyId)->first();
					$scheme = DB::table($db . '.schemem_sector_subsector')->select('schemename')->where('schemeid', $data->scheme)->first();
					$role_name = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', $data->projectcode)->where('position', $data->roleid)->first();
					$recieverrole_name = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', $data->projectcode)->where('position', $data->reciverrole)->first();
					$memberTypeId = DB::table($db . '.projectwise_member_category')->select('categoryname')->where('categoryid', $data->memberTypeId)->first();
					$loan_product = DB::table($db . '.product_project_member_category')->select('productcode')->where('productid', $data->loan_product)->first();
					$loan_product_name = DB::table($db . '.product_project_member_category')->select('productname')->where('productid', $data->loan_product)->first();
					if ($data->insurn_gender != null) {
						$InsurnGender = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'genderId')->where('data_id', $data->insurn_gender)->first();
						$insurnGender = $InsurnGender->data_name;
					} else {
						$insurnGender = null;
					}

					if ($data->insurn_gender != null) {
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

					// if ($data->status == '2') {
					// 	$checkPostedLoan = DB::table($db . '.posted_loan')->where('loan_id', $data->loan_id)->first();
					// 	if ($checkPostedLoan != null) {
					// 		$ErpStatusId = $checkPostedLoan->loanproposalstatusid;
					// 		if ($ErpStatusId == 1) {
					// 			$ErpStatus = 'Pending';
					// 		} elseif ($ErpStatusId == 2) {
					// 			$ErpStatus = 'Approved';
					// 		} elseif ($ErpStatusId == 3) {
					// 			$ErpStatus = 'Rejected';
					// 		}
					// 		$ErpRejectionReason = $checkPostedLoan->rejectionreason;
					// 	}
					// } else {
					// 	$ErpStatus = null;
					// 	$ErpStatusId = null;
					// 	$ErpRejectionReason = null;
					// }
					$serverurl = DB::Table($dberp . '.server_url')->where('server_status', 3)->where('status', 1)->first();
					$key = '5d0a4a85-df7a-scapi-bits-93eb-145f6a9902ae';
					$UpdatedAt = "2000-01-01 00:00:00";
					$member = Http::get($serverurl->url . 'MemberList', [
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
					}

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
					// dd($time);
					$arrayData['loan'] = array(
						"id" => $data->id,
						"orgno" => $data->orgno,
						"branchcode" => $data->branchcode,
						"projectcode" => $data->projectcode,
						"loan_product" => $data->loan_product,
						"loan_product_code" => $loan_product->productcode,
						"loan_product_name" => $loan_product_name->productname,
						"loan_duration" => $data->loan_duration,
						"invest_sector_id" => $data->invest_sector,
						"invest_sector" => $investSector->sectorname,
						"scheme_id" => $data->scheme,
						"scheme" => $scheme->schemename,
						"propos_amt" => $data->propos_amt,
						"instal_amt" => $data->instal_amt,
						"bracloan_family" => $data->bracloan_family,
						"vo_leader" => $data->vo_leader,
						"recommender" => $data->recommender,
						"grntor_name" => $data->grntor_name,
						"grntor_phone" => $data->grntor_phone,
						"grntor_rlationClient" => $grntorRlationClient->data_name,
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
						"memberType" => $memberTypeId->categoryname,
						"frequencyId" => $data->frequencyId,
						"frequency" => $frequencyId->frequency,
						"subSectorId" => $data->subSectorId,
						"subSector" => $subSectorId->subsectorname,
						"insurn_mainIDTypeId" => $data->insurn_mainIDType,
						"insurn_mainIDType" => $insurnMainIDType,
						"insurn_id_expire" => $data->insurn_id_expire,
						"insurn_placeofissue" => $data->insurn_placeofissue,
						"ErpHttpStatus" => $data->ErpHttpStatus,
						"ErpErrorMessage" => $data->ErpErrorMessage,
						"ErpErrors" => $data->ErpErrors,
						"erp_loan_id" => $data->erp_loan_id,
						"role_name" => $role_name->designation,
						"reciverrole_name" => $recieverrole_name->designation,
						"SurveyId" => $data->surveyid,
						"amount_inword" => $data->amount_inword,
						"loan_purpose" => $data->loan_purpose,
						"loan_user" => $data->loan_user,
						"loan_type" => $data->loan_type,
						"brac_loancount" => $data->brac_loancount,
						"Comment" => $dochistory->comment,
						"ErpStatus" => $ErpStatus,
						"ErpStatusId" => $ErpStatusId,
						"ErpRejectionReason" => $ErpRejectionReason
					);
					// $data['loan']=$loanArrayData;
					$rca = DB::table($db . '.rca')->where('loan_id', $data->id)->first();
					$PrimaryEarner = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'primaryEarner')->where('data_id', $rca->primary_earner)->first();
					// dd($PrimaryEarner);
					$bmPrimaryEarner = DB::table($db . '.payload_data')->select('data_name')->where('data_type', 'primaryEarner')->where('data_id', $rca->bm_primary_earner)->first();
					if ($bmPrimaryEarner) {
						$bmPrimaryEarnerIs = $bmPrimaryEarner->data_name;
					} else {
						$bmPrimaryEarnerIs = null;
					}
					$arrayData['rca'] = array(
						"id" => $rca->id,
						"loan_id" => $rca->loan_id,
						"primary_earner" => $PrimaryEarner->data_name,
						"primary_earner_id" => $rca->primary_earner,
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
						"monthlyincome_spouse_child" => $rca->monthlyincome_spouse_child
					);
					$arrayData['clientInfo'] = $member;
					$dataset[] = $arrayData;
				}
				// foreach($loandata as $row){
				// 	$data['loan']=$row;
				// 	$rca=DB::table($db.'.rca')->where('loan_id',$row->id)->first();
				// 	$data['rca']=$rca;
				// 	$dataset[]=$data;
				// }
				$result = array("status" => "S", "message" => "", "data" => $dataset);
				echo json_encode($result);
			}
		} else {
			$result = array("status" => "E", "message" => "Invalid token!");
			return json_encode($result);
		}
	}


	public function BmDataModify(Request $request)
	{
		$db = $this->db;
		$json = Request::input('json');
		// Log::channel('daily')->info('Bm Assesment Data: '.$json);
		$dataset = json_decode($json);
		// dd($dataset);
		$token = $dataset->token;
		$data = $dataset->loan_bm[0];
		$dataRca = $dataset->rca_bm[0];
		$loanid = $data->loan_id;

		$loan = DB::table($db . '.loans')->where('loan_id', $loanid)->first();
		$token = Request::input('token');
		if ($token == '7f30f4491cb4435984616d1913e88389') {
			if ($loan != null) {
				$id = $loan->id;
				$bm_repay_loan = $data->repay_loan;
				$bm_conduct_activity = $data->conduct_activity;
				$bm_action_required = $data->action_required;
				$bm_rca_rating = $data->rca_rating;
				//loan new
				$bm_noofChild = $data->bm_noofChild;
				$bm_earningMember = $data->bm_earningMember;
				$bm_duration = $data->bm_duration;
				$bm_hometown = $data->bm_hometown;
				$bm_landloard = $data->bm_landloard;
				$bm_recomand = $data->bm_recomand;
				$bm_occupation = $data->bm_occupation;
				$bm_aware = $data->bm_aware;
				$bm_grantor = $data->bm_grantor;
				$bm_socialAcecptRating = $data->bm_socialAcecptRating;
				$bm_grantorRating = $data->bm_grantorRating;
				$bm_clienthouse = $data->bm_clienthouse;
				$bm_remarks = $data->bm_remarks;

				DB::table($db . '.loans')->where('id', $id)->update(['bm_repay_loan' => $bm_repay_loan, 'bm_conduct_activity' => $bm_conduct_activity, 'bm_action_required' => $bm_action_required, 'bm_rca_rating' => $bm_rca_rating, 'bm_noofChild' => $bm_noofChild, 'bm_earningMember' => $bm_earningMember, 'bm_duration' => $bm_duration, 'bm_hometown' => $bm_hometown, 'bm_landloard' => $bm_landloard, 'bm_recomand' => $bm_recomand, 'bm_occupation' => $bm_occupation, 'bm_aware' => $bm_aware, 'bm_grantor' => $bm_grantor, 'bm_socialAcecptRating' => $bm_socialAcecptRating, 'bm_grantorRating' => $bm_grantorRating, 'bm_clienthouse' => $bm_clienthouse, 'bm_remarks' => $bm_remarks]);

				$rca = DB::table($db . '.rca')->where('loan_id', $id)->first();
				if ($rca != null) {
					$bm_primary_earner = $dataRca->primary_earner;
					$bm_monthlyincome_main = $dataRca->monthlyincome_main;
					$bm_monthlyincome_other = $dataRca->monthlyincome_other;
					$bm_house_rent = $dataRca->house_rent;
					$bm_food = $dataRca->food;
					$bm_education = $dataRca->education;
					$bm_medical = $dataRca->medical;
					$bm_festive = $dataRca->festive;
					$bm_utility = $dataRca->utility;
					$bm_saving = $dataRca->saving;
					$bm_other = $dataRca->other;
					$bm_monthly_instal = $dataRca->monthly_instal;
					$bm_debt = $dataRca->debt;
					$bm_monthly_cash = $dataRca->monthly_cash;
					$bm_instal_proposloan = $dataRca->instal_proposloan;

					DB::table($db . '.rca')->where('loan_id', $id)->update(['bm_primary_earner' => $bm_primary_earner, 'bm_monthlyincome_main' => $bm_monthlyincome_main, 'bm_monthlyincome_other' => $bm_monthlyincome_other, 'bm_house_rent' => $bm_house_rent, 'bm_food' => $bm_food, 'bm_education' => $bm_education, 'bm_medical' => $bm_medical, 'bm_festive' => $bm_festive, 'bm_utility' => $bm_utility, 'bm_saving' => $bm_saving, 'bm_other' => $bm_other, 'bm_monthly_instal' => $bm_monthly_instal, 'bm_debt' => $bm_debt, 'bm_monthly_cash' => $bm_monthly_cash, 'bm_instal_proposloan' => $bm_instal_proposloan]);

					$result = array("status" => "S", "message" => "", "loan_id" => $loanid);
					echo json_encode($result);
				}
			} else {
				$result = array("status" => "E", "message" => "Data not found!");
				echo json_encode($result);
			}
		} else {
			$result = array("status" => "E", "message" => "Invalid token!");
			return json_encode($result);
		}
	}

	public function DocumentManager(Request $request)
	{
		$db = $this->db;
		$baseUrl = url('');
		$projectcode = Request::input('projectcode');
		$doc_type = Request::input('doc_type');
		$doc_id = Request::input('doc_id');
		$entollmentid = Request::input('entollmentid');
		$pin = Request::input('pin');
		$roleid = Request::input('role');
		$branchcode = Request::input('branchcode');
		$action = Request::input('action');
		$comment = Request::input('comment');

		//get proccessid by doc type request
		if ($doc_type == 'admission') {
			$processid = DB::table($db . '.processes')->select('id')->where('process', 'member admission')->first();
			$processid = $processid->id;
		} elseif ($doc_type == 'loan') {
			$processid = DB::table($db . '.processes')->select('id')->where('process', 'loan application')->first();
			$processid = $processid->id;
		}

		//get doc_id by enrollment id
		if ($doc_id == '' and $entollmentid != '') {
			if ($doc_type == 'admission') {
				$doc = DB::table($db . '.admissions')->select('id')->where('entollmentid', $entollmentid)->first();
				$doc_id = $doc->id;
			} elseif ($doc_type == 'loan') {
				$doc = DB::table($db . '.loans')->select('id')->where('loan_id', $entollmentid)->first();
				$doc_id = $doc->id;
			}
		}

		//get enrollment id by doc id
		if ($doc_id != '' and $entollmentid == '') {
			if ($doc_type == 'admission') {
				$doc = DB::table($db . '.admissions')->select('entollmentid')->where('id', $doc_id)->first();
				$entollmentid = $doc->entollmentid;
			} elseif ($doc_type == 'loan') {
				$doc = DB::table($db . '.loans')->select('loan_id')->where('id', $doc_id)->first();
				$entollmentid = $doc->loan_id;
			}
		}

		//find action id for the action
		$actionAry = DB::table($db . '.action_lists')->select('id')->where('actionname', $action)->where('process_id', $processid)->where('projectcode', $projectcode)->first();
		$actionid = $actionAry->id;

		//check for parameter
		if ($projectcode != '' and $doc_type != '' and $doc_id != '' and $pin != '' and $roleid != '' and $branchcode != '') {
			$check_doc_history = DB::table($db . '.document_history')->where('projectcode', $projectcode)->where('doc_type', $doc_type)->where('doc_id', $doc_id)->get();
			$status = 1;
			if ($action == 'Request' or $action == 'Modify') {
				$dochistory_id = DB::Table($db . '.document_history')->insertGetId(['doc_id' => $doc_id, 'doc_type' => $doc_type, 'pin' => $pin, 'action' => $actionid, 'projectcode' => $projectcode, 'roleid' => $roleid, 'reciverrole' => 1]);
				if ($doc_type == 'admission') {
					DB::table($db . '.admissions')->where('id', $doc_id)->update(['dochistory_id' => $dochistory_id, 'roleid' => $roleid, 'pin' => $pin, 'reciverrole' => 1, 'status' => $status]);
				} elseif ($doc_type == 'loan') {
					DB::table($db . '.loans')->where('id', $doc_id)->update(['dochistory_id' => $dochistory_id, 'roleid' => $roleid, 'pin' => $pin, 'reciverrole' => 1, 'status' => $status]);
				}
				Log::channel('daily')->info('Po :' . $pin . ' send member admission to bm for approval');

				$result = array("status" => "S", "message" => "Document history saved");
				echo json_encode($result);
			} else {
				if ($doc_type == 'admission') {
					$document = DB::table($db . '.admissions')->where('id', $doc_id)->first();
				} elseif ($doc_type == 'loan') {
					$document = DB::table($db . '.loans')->where('id', $doc_id)->first();
				}
				if ($roleid != $document->reciverrole) {
					$result = array("status" => "E", "message" => "Domument already been proccesed.");
					return json_encode($result);
				}
				$reciverrole = $document->reciverrole;
				$branchcode = $document->branchcode;
				$docpin = $document->pin;

				//authrizetion check
				$checkAuth = $this->roleAuthrizatioCheck($reciverrole, $processid, $projectcode);
				if ($checkAuth) {

					$findHierarchyRole = $this->findHierarchyRole($reciverrole, $projectcode);
					$nextrole = $findHierarchyRole[0];
					$nextroledesig = $findHierarchyRole[1];

					$findPreviousRole = $this->findPreviousRole($reciverrole, $projectcode);

					$Previousrole = $findPreviousRole[0];
					$Previousroledesig = $findPreviousRole[1];

					if ($action != '') {
						if ($action == 'Recommend') {
							$checkApprove = $this->actionForRecommend($nextrole, $nextroledesig, $action, $reciverrole, $pin, $processid, $doc_type, $doc_id, $projectcode, $comment);

							if ($checkApprove) {
								Log::channel('daily')->info($reciverrole . $doc_type . '  to ' . $nextroledesig . '(' . $nextrole . ') for approval');
							}
						}
						if ($action == 'Sendback') {
							$checkApprove = $this->actionForSendback($Previousrole, $Previousroledesig, $action, $reciverrole, $pin, $processid, $doc_type, $doc_id, $projectcode, $comment);

							if ($checkApprove) {
								Log::channel('daily')->info($reciverrole . $doc_type . ' to ' . $nextroledesig . '(' . $nextrole . ') for sendback');
							}
						}
						if ($action == 'Reject') {
							$checkApprove = $this->actionForReject($Previousrole, $Previousroledesig, $action, $reciverrole, $pin, $processid, $doc_type, $doc_id, $projectcode, $comment);

							if ($checkApprove) {
								Log::channel('daily')->info($reciverrole . $doc_type . ' to ' . $nextroledesig . '(' . $nextrole . ') for Reject');
							}
						}
						if ($action == 'Approve') {
							$checkErpResponse = $this->documentErpPosting($doc_id, $doc_type);

							Log::channel('daily')->info('Erp Response : ' . json_encode($checkErpResponse));

							//return erp errors
							if ($checkErpResponse != "OK") {
								$result = array("status" => "E", "errors" => $checkErpResponse);
								return json_encode($result);
							}

							$checkApprove = $this->actionForApprove($nextrole, $nextroledesig, $action, $reciverrole, $pin, $processid, $doc_type, $doc_id, $projectcode);

							if ($checkApprove) {
								Log::channel('daily')->info($reciverrole . ' Approve ' . $doc_type);
							}
						}

						//send notification
						$notification_url = $baseUrl . "/NotificatioManager?projectcode=$projectcode&doc_type=$doc_type&pin=$docpin&role=$roleid&branchcode=$branchcode&entollmentid=$entollmentid&action=$action";

						Log::channel('daily')->info('notification_url : ' . $notification_url);

						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, $notification_url);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_HEADER, false);
						$notificationoutput = curl_exec($ch);
						curl_close($ch);
						Log::channel('daily')->info('notification_response : ' . $notificationoutput);
						//end notification

						$result = array("status" => "S", "entollmentid" => "$entollmentid");

						Log::channel('daily')->info('tab_response : ' . json_encode($result));

						return json_encode($result);
					} else {
						$result = array("status" => "E", "message" => "Action Required");
						return json_encode($result);
					}
				} else {
					$result = array("status" => "E", "message" => "User Not Authrize!");
					return json_encode($result);
				}
			}
		} else {
			$result = array("status" => "E", "message" => "parameter missing!");
			return json_encode($result);
		}
	}

	//start Document manager functions
	public function roleAuthrizatioCheck($roleId, $processId, $projectcode)
	{
		$db = $this->db;
		$isAuthurize = DB::table($db . '.auths')->select('isAuthorized')->where('roleId', $roleId)->where('processId', $processId)->where('projectcode', $projectcode)->first();

		return $isAuthurize->isAuthorized;
	}

	public function findHierarchyRole($role, $projectcode)
	{
		$db = $this->db;
		// $position=DB::table($db.'.role_hierarchies')->select('position')->where('role', $role)->where('projectcode', $projectcode)->first();
		// $position=$position->position;
		// $position=$position+1;
		// $nextrole=DB::table($db.'.role_hierarchies')->where('position', $position)->where('projectcode', $projectcode)->first();
		$role = $role + 1;
		$nextrole = DB::table($db . '.role_hierarchies')->where('position', $role)->where('projectcode', $projectcode)->first();
		return array($nextrole->position, $nextrole->designation);
	}

	public function findPreviousRole($role, $projectcode)
	{
		$db = $this->db;
		// $position=DB::table($db.'.role_hierarchies')->select('position')->where('role', $role)->where('projectcode', $projectcode)->first();
		// $position=$position->position;
		// $position=$position-1;
		// $nextrole=DB::table($db.'.role_hierarchies')->where('position', $position)->where('projectcode', $projectcode)->first();
		$role = $role - 1;
		$nextrole = DB::table($db . '.role_hierarchies')->where('position', $role)->where('projectcode', $projectcode)->first();
		return array($nextrole->position, $nextrole->designation);
	}

	public function actionForRecommend($nextrole, $nextroledesig, $action, $role, $pin, $processid, $doc_type, $doc_id, $projectcode, $comment)
	{
		$db = $this->db;
		$status = 1;
		$currentDatetime = date("Y-m-d h:i:s");
		$action = DB::table($db . '.action_lists')->select('id')->where('actionname', $action)->where('process_id', $processid)->where('projectcode', $projectcode)->first();
		$actionid = $action->id;
		$actioncounter = DB::table('document_history')->where('doc_id', $doc_id)->max('action_counter');
		$actioncounter = $actioncounter + 1;
		$dochistory_id = DB::Table($db . '.document_history')->insertGetId(['doc_id' => $doc_id, 'doc_type' => $doc_type, 'pin' => $pin, 'projectcode' => $projectcode, 'action' => $actionid, 'roleid' => $role, 'reciverrole' => $nextrole, 'action_counter' => $actioncounter, 'comment' => $comment]);
		DB::table($db . '.loans')->where('id', $doc_id)->update(['dochistory_id' => $dochistory_id, 'roleid' => $role, 'pin' => $pin, 'action' => $actionid, 'reciverrole' => $nextrole, 'status' => $status, 'updated_at' => $currentDatetime]);
		// if($doc_type=='admission'){
		// 	DB::table($db.'.admissions')->where('id', $doc_id)->update(['dochistory_id' => $dochistory_id,'roleid'=>$role,'pin'=>$pin,'action'=>$actionid,'reciverrole'=>$nextrole,'status'=>$status]);
		// }elseif($doc_type=='loan'){
		// 	DB::table($db.'.loans')->where('id', $doc_id)->update(['dochistory_id' => $dochistory_id,'roleid'=>$role,'pin'=>$pin,'action'=>$actionid,'reciverrole'=>$nextrole,'status'=>$status]);
		// }

		return true;
	}

	public function actionForSendback($nextrole, $nextroledesig, $action, $role, $pin, $processid, $doc_type, $doc_id, $projectcode, $comment)
	{
		$db = $this->db;
		$status = 1;
		$currentDatetime = date("Y-m-d h:i:s");
		$action = DB::table($db . '.action_lists')->select('id')->where('actionname', $action)->where('process_id', $processid)->where('projectcode', $projectcode)->first();
		$actionid = $action->id;
		$actioncounter = DB::table('document_history')->where('doc_id', $doc_id)->max('action_counter');
		$actioncounter = $actioncounter + 1;
		$dochistory_id = DB::Table($db . '.document_history')->insertGetId(['doc_id' => $doc_id, 'doc_type' => $doc_type, 'pin' => $pin, 'projectcode' => $projectcode, 'action' => $actionid, 'roleid' => $role, 'reciverrole' => $nextrole, 'action_counter' => $actioncounter, 'comment' => $comment]);
		if ($doc_type == 'admission') {
			DB::table($db . '.admissions')->where('id', $doc_id)->update(['dochistory_id' => $dochistory_id, 'roleid' => $role, 'pin' => $pin, 'action' => $actionid, 'reciverrole' => $nextrole, 'status' => $status, 'updated_at' => $currentDatetime]);
		} elseif ($doc_type == 'loan') {
			DB::table($db . '.loans')->where('id', $doc_id)->update(['dochistory_id' => $dochistory_id, 'roleid' => $role, 'pin' => $pin, 'action' => $actionid, 'reciverrole' => $nextrole, 'status' => $status, 'updated_at' => $currentDatetime]);
		}

		return true;
	}

	public function actionForReject($nextrole, $nextroledesig, $action, $role, $pin, $processid, $doc_type, $doc_id, $projectcode, $comment)
	{
		$db = $this->db;
		$status = '3';
		$currentDatetime = date("Y-m-d h:i:s");
		$action = DB::table($db . '.action_lists')->select('id')->where('actionname', $action)->where('process_id', $processid)->where('projectcode', $projectcode)->first();
		$actionid = $action->id;
		$actioncounter = DB::table('document_history')->where('doc_id', $doc_id)->max('action_counter');
		$actioncounter = $actioncounter + 1;
		$dochistory_id = DB::Table($db . '.document_history')->insertGetId(['doc_id' => $doc_id, 'doc_type' => $doc_type, 'pin' => $pin, 'projectcode' => $projectcode, 'action' => $actionid, 'roleid' => $role, 'reciverrole' => $nextrole, 'action_counter' => $actioncounter, 'comment' => $comment]);
		if ($doc_type == 'admission') {
			DB::table($db . '.admissions')->where('id', $doc_id)->update(['dochistory_id' => $dochistory_id, 'roleid' => $role, 'pin' => $pin, 'action' => $actionid, 'reciverrole' => $nextrole, 'status' => $status, 'updated_at' => $currentDatetime]);
		} elseif ($doc_type == 'loan') {
			DB::table($db . '.loans')->where('id', $doc_id)->update(['dochistory_id' => $dochistory_id, 'roleid' => $role, 'pin' => $pin, 'action' => $actionid, 'status' => $status, 'updated_at' => $currentDatetime]);
		}

		return true;
	}

	public function actionForApprove($nextrole, $nextroledesig, $action, $role, $pin, $processid, $doc_type, $doc_id, $projectcode)
	{
		$db = $this->db;
		$status = '2';
		$erpstatus = 1;
		$currentDatetime = date("Y-m-d h:i:s");
		$action = DB::table($db . '.action_lists')->select('id')->where('actionname', $action)->where('process_id', $processid)->where('projectcode', $projectcode)->first();
		$actionid = $action->id;
		$actioncounter = DB::table('document_history')->where('doc_id', $doc_id)->max('action_counter');
		$actioncounter = $actioncounter + 1;
		$dochistory_id = DB::Table($db . '.document_history')->insertGetId(['doc_id' => $doc_id, 'doc_type' => $doc_type, 'pin' => $pin, 'projectcode' => $projectcode, 'action' => $actionid, 'roleid' => $role, 'action_counter' => $actioncounter]);
		if ($doc_type == 'admission') {
			DB::table($db . '.admissions')->where('id', $doc_id)->update(['dochistory_id' => $dochistory_id, 'roleid' => $role, 'pin' => $pin, 'action' => $actionid, 'status' => $status, 'ErpStatus' => $erpstatus, 'updated_at' => $currentDatetime]);
		} elseif ($doc_type == 'loan') {
			DB::table($db . '.loans')->where('id', $doc_id)->update(['dochistory_id' => $dochistory_id, 'roleid' => $role, 'pin' => $pin, 'action' => $actionid, 'status' => $status, 'ErpStatus' => $erpstatus, 'updated_at' => $currentDatetime]);
		}
		return true;
	}
	//end document manager functions

	//start notification Manager
	public function NotificatioManager(Request $request)
	{
		$db = $this->db;
		$projectcode = Request::input('projectcode');
		$doc_type = Request::input('doc_type');
		$doc_id = Request::input('doc_id');
		$entollmentid = Request::input('entollmentid');
		$pin = Request::input('pin');
		$roleid = Request::input('role');
		$branchcode = Request::input('branchcode');
		$action = Request::input('action');
		// $comment = Request::input('comment');

		//get doc_id by enrollment id
		if ($doc_id == '' and $entollmentid != '') {
			if ($doc_type == 'admission') {
				$doc = DB::table($db . '.admissions')->select('id')->where('entollmentid', $entollmentid)->first();
				$doc_id = $doc->id;
			} elseif ($doc_type == 'loan') {
				$doc = DB::table($db . '.loans')->select('id')->where('loan_id', $entollmentid)->first();
				$doc_id = $doc->id;
			}
		}

		if ($doc_type == 'admission') {
			$processid = DB::table($db . '.processes')->select('id')->where('process', 'member admission')->first();
			$processid = $processid->id;
		} elseif ($doc_type == 'loan') {
			$processid = DB::table($db . '.processes')->select('id')->where('process', 'loan application')->first();
			$processid = $processid->id;
		}

		//find designation
		$roleDesignationQuery = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', $projectcode)->where('position', $roleid)->first();
		$roleDesignation = $roleDesignationQuery->designation;

		$actionary = DB::table($db . '.action_lists')->select('id')->where('actionname', $action)->where('process_id', $processid)->where('projectcode', $projectcode)->first();
		$actionid = $actionary->id;

		$notification = DB::table($db . '.notifications')->where('actionid', $actionid)->where('projectid', $projectcode)->where('roleid', $roleid)->where('status', 1)->first();
		if ($notification->inApp) {
			$reciverrole = $notification->recieverlist;
			$msgcontent = $notification->msgcontent;

			$reciverroleary = explode(',', $reciverrole);

			if (count($reciverroleary) == 1) {
				//find designation
				$reciverRoleDesignationQuery = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', $projectcode)->where('position', $reciverrole)->first();
				$reciverroleDesignation = $reciverRoleDesignationQuery->designation;

				$inAppReturn = $this->inAppAction($roleid, $roleDesignation, $reciverrole, $reciverroleDesignation, $msgcontent, $projectcode, $pin, $processid, $doc_type, $doc_id, $entollmentid, $actionid, $action, $branchcode);
				// return $inAppReturn;
			} else {
				foreach ($reciverroleary as $reciverrole) {
					//find designation
					$reciverRoleDesignationQuery = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', $projectcode)->where('position', $reciverrole)->first();
					$reciverroleDesignation = $reciverRoleDesignationQuery->designation;

					$inAppReturn = $this->inAppAction($roleid, $roleDesignation, $reciverrole, $reciverroleDesignation, $msgcontent, $projectcode, $pin, $processid, $doc_type, $doc_id, $entollmentid, $actionid, $action, $branchcode);
				}
			}

			if ($inAppReturn) {
				Log::channel('daily')->info('In App notification suucessful');
			}
		} else if ($notification->sms) {
		} else if ($notification->email) {
		}
		$result = array("status" => "S", "message" => "Notification created successfully");
		echo json_encode($result);
	}

	public function inAppAction($role, $roleDesignation, $reciverrole, $reciverroleDesignation, $msgcontent, $projectcode, $pin, $processid, $doc_type, $doc_id, $entollmentid, $actionid, $action, $branchcode)
	{
		$db = $this->db;
		$dberp = $this->dberp;
		$baseUrl = url('');
		$trendxurl = 'http://trendxstage.brac.net/api/';
		$reciverpin = '';
		$associateid = 0;
		$brcode = $branchcode;
		$branchcode = (int)$branchcode; //for remover inital zero 
		$tendxbmpin = 'b' . $branchcode;

		if ($projectcode == '015') {
			$program_id = 1;
		} elseif ($projectcode == '060') {
			$program_id = 5;
		}

		// dd($reciverroleDesignation);

		if ($reciverroleDesignation == 'BM') {
			$findpin = DB::table($dberp . '.polist')->select('cono')->where('status', 1)->where('branchcode', $brcode)->where('projectcode', $projectcode)
				->Where(function ($query) {
					// $query->where('desig','Branch Manager')->orWhere('desig','Assistant Branch Manager');
					$query->where('desig', 'Branch Manager');
				})->first();
			if ($findpin != null) {
				$reciverpin = $findpin->cono;
			} else {
				$findpin = DB::table($dberp . '.polist')->select('cono')->where('status', 1)->where('branchcode', $brcode)->where('projectcode', $projectcode)
					->Where(function ($query) {
						$query->Where('desig', 'Assistant Branch Manager');
						// $query->where('desig','Branch Manager');
					})->first();
				if ($findpin != null) {
					$reciverpin = $findpin->cono;
				}
			}
		} else if ($reciverroleDesignation == 'PO') {
			$findpin = DB::table('document_history')->select('pin')->where('doc_type', $doc_type)->where('doc_id', $doc_id)->where('projectcode', $projectcode)->where('action_counter', 1)->first();

			if ($findpin != null) {
				$reciverpin = $findpin->pin;
			}
			// $reciverpin='186251';
		}

		$associate = DB::table('public.branch')->select('area_id', 'region_id', 'division_id')->where('branch_id', $branchcode)->where('program_id', $program_id)->first();
		// dd($associate);
		if ($reciverroleDesignation == 'AM') {
			$associateid = $associate->area_id;
		} else if ($reciverroleDesignation == 'RM') {
			$associateid = $associate->region_id;
		} else if ($reciverroleDesignation == 'DM') {
			$associateid = $associate->division_id;
		}

		if ($doc_type == 'admission') {
			$docreff = $baseUrl . '/operation/admission-approval/' . $doc_id;
		} elseif ($doc_type == 'loan') {
			$docreff = $baseUrl . '/operation/loan-approval/' . $doc_id;
		}


		if ($reciverroleDesignation == 'PO' or $reciverroleDesignation == 'BM') {
			DB::Table($db . '.message_ques')->insert(['pin' => $reciverpin, 'message' => $msgcontent, 'docreff' => $docreff, 'doctype' => $doc_type]);

			$test = $this->sendAppNotification($entollmentid, $doc_type, $reciverpin, $msgcontent, $action);
			// dd($test);
		} else {
			DB::Table($db . '.message_ques')->insert(['message' => $msgcontent, 'docreff' => $docreff, 'doctype' => $doc_type, 'roleid' => $reciverrole, 'associateid' => $associateid, 'programid' => $program_id]);
		}

		return true;

		// else if ($reciverroleDesignation == 'AM') {
		// 	//find associate id
		// 	$findassciateid = DB::table('public.branch')->select('area_id')->where('branch_id', $branchcode)->where('program_id', $program_id)->groupBy('area_id')->first();
		// 	$associated_id = $findassciateid->area_id;

		// 	$findpin = DB::table($db . '.user')->select('user_pin')->where('status_id', 1)->where('associated_id', $associated_id)->where('role_id', $reciverrole)->where('program_id', $program_id)->first();
		// 	if ($findpin != null) {
		// 		$reciverpin = $findpin->user_pin;
		// 	}
		// 	if ($projectcode == '015') {
		// 		$reciverpin = 'a123';
		// 	} elseif ($projectcode == '060') {
		// 		$reciverpin = 'b123';
		// 	}
		// } else if ($reciverroleDesignation == 'RM') {

		// 	//find associate id
		// 	$findassciateid = DB::table('public.branch')->select('region_id')->where('branch_id', $branchcode)->where('program_id', $program_id)->groupBy('region_id')->first();
		// 	$associated_id = $findassciateid->region_id;

		// 	$findpin = DB::table($db . '.user')->select('user_pin')->where('status_id', 1)->where('associated_id', $associated_id)->where('role_id', $reciverrole)->where('program_id', $program_id)->first();
		// 	if ($findpin != null) {
		// 		$reciverpin = $findpin->user_pin;
		// 	}
		// 	if ($projectcode == '015') {
		// 		$reciverpin = '50515';
		// 	} elseif ($projectcode == '060') {
		// 		$reciverpin = '40414';
		// 	}
		// } else if ($reciverroleDesignation == 'DM') {

		// 	//find associate id
		// 	$findassciateid = DB::table('public.branch')->select('division_id')->where('branch_id', $branchcode)->where('program_id', $program_id)->groupBy('division_id')->first();
		// 	$associated_id = $findassciateid->division_id;

		// 	// $findpin=DB::table($db.'.user')->select('user_pin')->where('status_id',1)->where('branchcode',$branchcode)->where('designation','Divisional Manager')->first();
		// 	$findpin = DB::table($db . '.user')->select('user_pin')->where('status_id', 1)->where('associated_id', $associated_id)->where('role_id', $reciverrole)->where('program_id', $program_id)->first();
		// 	if ($findpin != null) {
		// 		$reciverpin = $findpin->user_pin;
		// 	}
		// 	if ($projectcode == '015') {
		// 		$reciverpin = '112233';
		// 	} elseif ($projectcode == '060') {
		// 		$reciverpin = '445566';
		// 	}
		// } 

		//trendx api integration for am,rm,dm
		// $trendx = Http::get($trendxurl . 'branch', [
		// 	'user_pin' => $tendxbmpin,
		// 	'role_id' => 1,
		// 	'module_id' => 10
		// ]);

		// $trendxAry = $trendx->object();

		// if (!empty($trendxAry)) {
		// 	$bm_id = $trendxAry[0]->bm_id;
		// 	$am_id = $trendxAry[0]->am_id;
		// 	$rm_id = $trendxAry[0]->rm_id;
		// 	$div_id = $trendxAry[0]->div_id;

		// 	if ($reciverroleDesignation == 'AM') {
		// 		$reciverpin = $am_id;
		// 	} else if ($reciverroleDesignation == 'RM') {
		// 		$reciverpin = $rm_id;
		// 	} else if ($reciverroleDesignation == 'DM') {
		// 		$reciverpin = $div_id;
		// 	}
		// } else {
		// 	return false;
		// }
		//end trendx api integration for am,rm,dm

	}
	//end notification manager

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
		$res['timestamp'] = date('Y-m-d H:i:s');
		$data['data'] = $res;
		$topic = $reciverpin;
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

	//start erp api's functions
	public function tokenVerify()
	{
		$db = $this->db;
		$currentDatetime = date("Y-m-d h:i:s");
		$tokenCheckDb = Db::table($db . '.oauth2')->get();

		if ($tokenCheckDb->isEmpty()) {
			$jsonresponse = $this->getToken();
			$response_ary = json_decode($jsonresponse);
			if (json_last_error() === JSON_ERROR_NONE) {
				// JSON is valid
				$expires_time = $response_ary->expires_in;
				$access_token = $response_ary->access_token;
				$expires_in = date("Y-m-d h:i:s", time() + $expires_time);

				DB::table($db . '.oauth2')->insert(['expires_time' => $expires_time, 'expires_in' => $expires_in, 'access_token' => $access_token]);

				return $access_token;
			} else {
				//invalid json
				Log::channel('daily')->info('ERP access token error');

				return json_last_error();
			}
		} else {
			$id = $tokenCheckDb[0]->id;
			$expires_in = $tokenCheckDb[0]->expires_in;
			if ($expires_in > $currentDatetime) {

				//get token from DB 
				$access_token = $tokenCheckDb[0]->access_token;
				return $access_token;
			} else {
				//token expired
				$jsonresponse = $this->getToken();
				$response_ary = json_decode($jsonresponse);
				if (json_last_error() === JSON_ERROR_NONE) {
					// JSON is valid
					$expires_time = $response_ary->expires_in;
					$access_token = $response_ary->access_token;
					$expires_in = date("Y-m-d h:i:s", time() + $expires_time);

					DB::table($db . '.oauth2')->where('id', $id)->update(['expires_time' => $expires_time, 'expires_in' => $expires_in, 'access_token' => $access_token]);

					return $access_token;
				} else {
					//invalid json
					Log::channel('daily')->info('ERP access token error');

					return json_last_error();
				}
			}
		}
	}

	public function getToken()
	{
		$clientid = 'Ieg1N5W2qh3hF0qS9Zh2wq6eex2DB935';
		$clientsecret = '4H2QJ89kYQBStaCuY73h';
		$url = 'https://bracapitesting.brac.net/oauth/v2/token?grant_type=client_credentials';

		$headers = array(
			// 'Authorization: key=' . $FIREBASE_API_KEY,
			'Accept: application/json',
			'X-CLIENT-ID: ' . $clientid,
			'X-CLIENT-SECRET: ' . $clientsecret
		);


		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_HTTPHEADER => $headers,
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return "cURL Error #:" . $err;
		} else {
			return $response;
		}
	}

	public function documentErpPosting($doc_id, $doc_type)
	{
		if ($doc_type == 'admission') {
			$response = $this->admissionDataProccessForErp($doc_id);
			return $response;
		} elseif ($doc_type == 'loan') {
			$response = $this->loanDataProccessForErp($doc_id);
			return $response;
		}
	}

	//admission member erp posting
	public function admissionErpPosting($admission)
	{
		// dd('asd');
		$db = $this->db;
		$currentDatetime = date("Y-m-d h:i:s");
		$access_token = $this->tokenVerify();
		$clientid = 'Ieg1N5W2qh3hF0qS9Zh2wq6eex2DB935';
		$clientsecret = '4H2QJ89kYQBStaCuY73h';
		$url = 'https://bracapitesting.brac.net/dcs/v1/buffer-members';

		$headers = array(
			'Authorization: Bearer ' . $access_token,
			'Content-Type: application/json'
		);


		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $admission,
			CURLOPT_HTTPHEADER => $headers,
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return "cURL Error #:" . $err;
		} else {
			$documentAry = json_decode($admission);
			$id = $documentAry[0]->id;
			Log::channel('daily')->info('Admission Erp Posting response: ' . $response);
			$response = json_decode($response);
			// dd($response);

			if (array_key_exists("message", $response)) {
				if ($response->message == "OK") {
					DB::table($db . '.admissions')->where('entollmentid', $id)->update(['ErpHttpStatus' => 200, 'ErpErrorMessage' => null, 'ErpErrors' => null, 'updated_at' => $currentDatetime]);
					return $response->message;
				} else {
					Log::channel('daily')->info('Admission Erp Posting errors: ' . json_encode($response));
					// dd($response);
					$ErpHttpStatus = $response->httpStatus;
					$ErpErrorMessage = $response->errorMessage;
					$ErpErrors = $response->errors;
					// dd($ErpErrors);

					DB::table($db . '.admissions')->where('entollmentid', $id)->update(['ErpHttpStatus' => $ErpHttpStatus, 'ErpErrorMessage' => $ErpErrorMessage, 'ErpErrors' => $ErpErrors, 'updated_at' => $currentDatetime]);
					//   dd($responseAry);
					return $ErpErrors;
				}
			}
		}
	}

	//loan member erp posting
	public function loanErpPosting($loan)
	{
		Log::channel('daily')->info('Loan Request File: ' . $loan);
		$db = $this->db;
		$currentDatetime = date("Y-m-d h:i:s");
		$access_token = $this->tokenVerify();
		$clientid = 'Ieg1N5W2qh3hF0qS9Zh2wq6eex2DB935';
		$clientsecret = '4H2QJ89kYQBStaCuY73h';
		$url = 'https://bracapitesting.brac.net/dcs/v1/buffer-loan-proposals';

		$headers = array(
			'Authorization: Bearer ' . $access_token,
			'Content-Type: application/json'
		);


		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $loan,
			CURLOPT_HTTPHEADER => $headers,
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);
		// dd($loan);
		if ($err) {
			return "cURL Error #:" . $err;
		} else {
			//   return $response;
			$documentAry = json_decode($loan);
			$id = $documentAry[0]->id;
			Log::channel('daily')->info('Loan Erp Posting response: ' . $response);
			$response = json_decode($response);

			if (array_key_exists("message", $response)) {
				if ($response->message == "OK") {
					DB::table($db . '.loans')->where('loan_id', $id)->update(['ErpHttpStatus' => 200, 'ErpErrorMessage' => null, 'ErpErrors' => null, 'updated_at' => $currentDatetime]);
					return $response->message;
				} else {
					Log::channel('daily')->info('Loan Erp Posting errors: ' . json_encode($response));
					$ErpHttpStatus = $response->httpStatus;
					$ErpErrorMessage = $response->errorMessage;
					$ErpErrors = $response->errors;
					//   dd($id);

					DB::table($db . '.loans')->where('loan_id', $id)->update(['ErpHttpStatus' => $ErpHttpStatus, 'ErpErrorMessage' => $ErpErrorMessage, 'ErpErrors' => $ErpErrors, 'updated_at' => $currentDatetime]);
					//   dd($responseAry);
					return $ErpErrors;
				}
			}
		}
	}

	//admission data proccessing for erp posting
	public function admissionDataProccessForErp($id)
	{
		$db = $this->db;
		$data = DB::table($db . '.admissions')->where('id', $id)->first();
		if ($data->SposeDOB == null) {
			$SposeDOB = null;
		} else {
			$SposeDOB = date('Y-m-d', strtotime($data->SposeDOB));
		}
		if ($data->SpouseIdExpiredate == null) {
			$SpouseIdExpiredate = null;
		} else {
			$SpouseIdExpiredate = date('Y-m-d', strtotime($data->SpouseIdExpiredate));
		}
		if ($data->NomineeDOB == null) {
			$NomineeDOB = null;
		} else {
			$NomineeDOB = date('Y-m-d', strtotime($data->NomineeDOB));
		}
		if ($data->NomineeIdExpiredate == null) {
			$NomineeIdExpiredate = null;
		} else {
			$NomineeIdExpiredate = date('Y-m-d', strtotime($data->NomineeIdExpiredate));
		}
		if ($data->DOB == null) {
			$DOB = null;
		} else {
			$DOB = date('Y-m-d', strtotime($data->DOB));
		}
		// dd($data);
		$arrayData = array();
		$guarantor = array();
		$guarantor[] = array(
			"dateOfBirth" => null,
			"idCard" => array(),
			"idCard" => array(
				"backImageUrl" => null,
				"cardTypeId" => null,
				"expiryDate" => null,
				"frontImageUrl" => null,
				"idCardNo" => null,
				"issueDate" => null,
				"issuePlace" => null,
			),
			"nameEn" => null,
			"relationshipId" => null,
		);
		$nominees = array();
		$nominees[] = array(
			"contactNo" => $data->NomineePhoneNumber,
			"dateOfBirth" => $NomineeDOB,
			"id" => null,
			"idCard" => array(),
			"idCard" => array(
				"backImageUrl" => $data->NomineeNidBack,
				"cardTypeId" => $data->NomineeNidType,
				"expiryDate" => $NomineeIdExpiredate,
				"frontImageUrl" => $data->NomineeNidFront,
				"idCardNo" => $data->NomineeNidNo,
				"issueDate" => null,
				"issuePlace" => $data->NomineeIdPlaceOfissue,
			),
			"name" => $data->NomineeName,
			"relationshipId" => "$data->RelationshipId",
		);
		$projectcode = (int)$data->projectcode;
		$arrayData[] = array(
			"applicationDate" => date('Y-m-d', strtotime($data->created_at)),
			"assignedPoPin" => $data->assignedpo,
			"bankAccountNumber" => null,
			"bankBranchId" => null,
			"bankId" => null,
			"bkashWalletNo" => $data->WalletNo,
			"branchCode" => $data->branchcode,
			"contactNo" => $data->Phone,
			"dateOfBirth" => $DOB,
			"educationId" => $data->EducationId,
			"fatherNameEn" => $data->FatherName,
			"flag" => $data->Flag,
			"genderId" => $data->GenderId,
			"guarantor" => null,
			"id" => $data->entollmentid,
			"idCard" => array(),
			"idCard" => array(
				"backImageUrl" => $data->BackSideOfIdimg,
				"cardTypeId" => $data->MainIdTypeId,
				"expiryDate" => $data->ExpiryDate,
				"frontImageUrl" => $data->FrontSideOfIdImg,
				"idCardNo" => $data->IdNo,
				"issueDate" => null,
				"issuePlace" => $data->IssuingCountry,
			),
			"maritalStatusId" => $data->MaritalStatusId,
			"memberId" => $data->MemberId,
			"memberImageUrl" => $data->ApplicantSinglePic,
			"memberTypeId" => $data->MemberCateogryId,
			"motherNameEn" => $data->MotherName,
			"nameEn" => $data->ApplicantsName,
			"nominees" => $nominees,
			"occupationId" => $data->Occupation,
			"passbookNumber" => null,
			"permanentAddress" => $data->PermanentAddress,
			"permanentDistrictId" => $data->PermanentDistrictId,
			"permanentUpazilaId" => $data->parmanentUpazilaId,
			"poId" => $data->assignedpo,
			"presentAddress" => $data->PresentAddress,
			"presentDistrictId" => $data->PresentDistrictId,
			"presentUpazilaId" => $data->presentUpazilaId,
			"projectCode" => $projectcode,
			"rejectionReason" => null,
			"routingNumber" => null,
			"savingsProductId" => $data->SavingsProductId,
			"spouseDateOfBirth" => $SposeDOB,
			"spouseIdCard" => array(),
			"spouseIdCard" => array(
				"backImageUrl" => $data->SpouseNidBack,
				"cardTypeId" => $data->SpouseCardType,
				"expiryDate" => $SpouseIdExpiredate,
				"frontImageUrl" => $data->SpouseNidFront,
				"idCardNo" => $data->SpouseNidOrBid,
				"issueDate" => null,
				"issuePlace" => $data->SpouseIdPlaceOfissue,
			),
			"spouseNameEn" => $data->SpouseName,
			"statusId" => null,
			"targetAmount" => $data->TargetAmount,
			"tinNumber" => null,
			"updated" => true,
			"voCode" => $data->orgno,
			"voId" => null,
			"orgId" => 2
		);
		$jsonData = json_encode($arrayData);
		Log::channel('daily')->info('Dcs_erp_admission_erp_dataset : ' . $jsonData);
		//erp curl posting
		$response = $this->admissionErpPosting($jsonData);

		return $response;
	}

	//loan data proccessing for erp posting
	public function loanDataProccessForErp($id)
	{
		$db = $this->db;
		$dberp = $this->dberp;
		$data = DB::table($db . '.loans')->where('id', $id)->first();
		$loanapprover = DB::table($db . '.role_hierarchies')->where('projectcode', $data->projectcode)->where('position', $data->reciverrole)->first();
		// $memberInfo = DB::table($db . '.posted_admission')->where('memberid', $data->erp_mem_id)->first();

		$serverurl = DB::Table($dberp . '.server_url')->where('server_status', 3)->where('status', 1)->first();
		$key = '5d0a4a85-df7a-scapi-bits-93eb-145f6a9902ae';
		$UpdatedAt = "2000-01-01 00:00:00";
		$member = Http::get($serverurl->url . 'MemberList', [
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
				$memberInfo = $member->data[0];
			} else {
				$memberInfo = null;
			}
		} else {
			$memberInfo = null;
		}

		$projectcode = (int)$data->projectcode;
		$insuranceProduct = DB::table($db . '.insurance_products')->where('project_code', $projectcode)->first();
		// dd($memberInfo->membertypeid);
		$arrayData = array();
		$nominees = array();
		if ($memberInfo->Nominees != null) {
			$nominees[] = array(
				"contactNo" => null,
				"dateOfBirth" => $memberInfo->Nominees[0]->DateOfBirth,
				"id" => null,
				"idCard" => array(),
				"idCard" => array(
					"backImageUrl" => null,
					"cardTypeId" => $memberInfo->Nominees[0]->CardTypeId,
					"expiryDate" => null,
					"frontImageUrl" => null,
					"idCardNo" => '7654891255', //$memberInfo->Nominees[0]->CardTypeId,
					"issueDate" => null,
					"issuePlace" => null,
				),
				"name" => $memberInfo->Nominees[0]->DateOfBirth,
				"relationshipId" => $memberInfo->Nominees[0]->RelationshipId,
			);
		} else {
			$nominees = null;
		}


		// $coBorrowerDto=array();
		// $coBorrowerDto=array(
		//         "idCard"=>array(),
		//         "idCard"=>array(
		//             "backImageUrl" => null,
		//             "cardTypeId" => $data->insurn_mainIDType,
		//             "expiryDate" => null,
		//             "frontImageUrl" => null,
		//             "idCardNo" => $data->insurn_mainID,
		//             "issueDate" => null,
		//             "issuePlace" => null,
		//         ),
		//         "name" => $data->grntor_name,
		//         "relationshipId" => $data->grntor_rlationClient,
		// );

		$coBorrowerDto = null;

		if ($data->insurn_type == 1) {
			$secondInsurer = null;
		} elseif ($data->insurn_type == 2) {
			if ($data->insurn_option == 1) {
				$secondInsurer = array();
				if ($memberInfo->Nominees != null) {
					$secondInsurer = array(
						"dateOfBirth" => null,
						"genderId" => null,
						"idCard" => array(),
						"idCard" => array(
							"backImageUrl" => null,
							"cardTypeId" => $memberInfo->SpouseIDCard[0]->CardTypeId,
							"expiryDate" => $memberInfo->SpouseIDCard[0]->CardTypeId,
							"frontImageUrl" => null,
							"idCardNo" => '7654891255', //$memberInfo->SpouseIDCard[0]->CardTypeId,
							"issueDate" => null,
							"issuePlace" => $memberInfo->SpouseIDCard[0]->CardTypeId,
						),
						"name" => null,
						"relationshipId" => null,
					);
				} else {
					$secondInsurer = null;
				}
			} elseif ($data->insurn_option == 2) {
				$secondInsurer = array();
				$secondInsurer = array(
					"dateOfBirth" => $data->insurn_dob,
					"genderId" => $data->insurn_gender,
					"idCard" => array(),
					"idCard" => array(
						"backImageUrl" => null,
						"cardTypeId" => $data->insurn_mainIDType,
						"expiryDate" => $data->insurn_id_expire,
						"frontImageUrl" => null,
						"idCardNo" => '7654891255', //$data->insurn_mainID,
						"issueDate" => null,
						"issuePlace" => $data->insurn_placeofissue,
					),
					"name" => $data->insurn_name,
					"relationshipId" => $data->insurn_relation,
				);
			}
		}

		$projectcode = (int)$data->projectcode;
		$arrayData[] = array(
			"applicationDate" => date('Y-m-d', strtotime($data->time)),
			"approvedDurationInMonths" => null,
			"approvedLoanAmount" => null,
			"branchCode" => $data->branchcode,
			"coBorrowerDto" => $coBorrowerDto,
			"consentUrl" => null,
			"disbursementDate" => null,
			"flag" => 1,
			"frequencyId" => $data->frequencyId,
			"id" => $data->loan_id,
			"insuranceProductId" => $insuranceProduct->product_id,
			"loanAccountId" => null,
			"loanApprover" => $loanapprover->role,
			"loanProductId" => $data->loan_product,
			"loanProposalStatusId" => null,                         //test
			"memberId" => $data->erp_mem_id,
			"memberTypeId" => $memberInfo->MemberClassificationId,
			"microInsurance" => true,
			"modeOfPaymentId" => 1,
			"nominees" => $nominees,           //array
			"policyTypeId" => $data->insurn_type,  //insurenc type single or double
			"premiumAmount" => null,
			"projectCode" => $projectcode,
			"proposalDurationInMonths" => $data->loan_duration,
			"proposedLoanAmount" => $data->propos_amt,
			"rejectionReason" => $data->comment,
			"schemeId" => $data->scheme,
			// "spouseIdCard"=>$spouseIdCard,       //array
			"secondInsurer" => $secondInsurer,	//array
			"sectorId" => $data->invest_sector,
			"signConsent" => null,
			"subSectorId" => $data->subSectorId,
			"updated" => null,
			"voCode" => $data->orgno,
			"voId" => null,
			"orgId" => 2

		);
		$jsonData = json_encode($arrayData);
		// return $jsonData;

		//loan curl request
		$response = $this->loanErpPosting($jsonData);
		// dd($response);
		return $response;
	}

	//erp get api admission data
	public function GetErpPostedAdmissionData($branchcode)
	{
		$access_token = $this->tokenVerify();
		$clientid = 'Ieg1N5W2qh3hF0qS9Zh2wq6eex2DB935';
		$clientsecret = '4H2QJ89kYQBStaCuY73h';
		$url = 'https://bracapitesting.brac.net/dcs/v1/branches/' . $branchcode . '/buffer-members';

		$headers = array(
			'Authorization: Bearer ' . $access_token,
			'Accept: application/json',
		);

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
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

	//erp get api admission data's database insertion
	public function insertPostedAddmissionList($response)
	{
		$db = $this->db;
		$currentDatetime = date("Y-m-d h:i:s");
		$arrayAddmission = json_decode($response);
		// dd($arrayAddmission);
		if (!empty($arrayAddmission)) {
			foreach ($arrayAddmission as $data) {
				// dd($data->id);
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

				if ($data->statusId == 2 or $data->statusId == 3) {	//if erp approve and reject
					if ($checkAdmission != null) {                //if addmission has data
						if ($checkAdmission->MemberId == null and $checkAdmission->ErpStatus == 1) {		//if erp member id empty in dcs admission table
							$this->sendAppNotificationForErpAddmissionAction($data);
						}
					}
				}


				if ($checkPostedAdmission == null) {
					DB::table($db . '.posted_admission')->insert($values);
					if ($data->statusId == 2) {
						if ($checkAdmission != null) {
							DB::table($db . '.admissions')->where('entollmentid', $data->id)->update(['MemberId' => $data->memberId, 'ErpStatus' => $data->statusId, 'updated_at' => $currentDatetime]);
						}
						if ($checkLoan != null) {
							DB::table($db . '.loans')->where('mem_id', $data->id)->update(['erp_mem_id' => $data->memberId, 'updated_at' => $currentDatetime]);
						}
					} elseif ($data->statusId == 3) {
						if ($checkAdmission != null) {
							DB::table($db . '.admissions')->where('entollmentid', $data->id)->update(['ErpStatus' => $data->statusId, 'updated_at' => $currentDatetime]);
						}
					}
				} else {
					// if ($data->updated == TRUE) {
					DB::table($db . '.posted_admission')->where('admission_id', $data->id)->update($values);
					// }
					if ($data->statusId == 2) {
						if ($checkAdmission != null) {
							DB::table($db . '.admissions')->where('entollmentid', $data->id)->update(['MemberId' => $data->memberId, 'ErpStatus' => $data->statusId, 'updated_at' => $currentDatetime]);
						}
						if ($checkLoan != null) {
							DB::table($db . '.loans')->where('mem_id', $data->id)->update(['erp_mem_id' => $data->memberId, 'updated_at' => $currentDatetime]);
						}
					} elseif ($data->statusId == 3) {
						if ($checkAdmission != null) {
							DB::table($db . '.admissions')->where('entollmentid', $data->id)->update(['ErpStatus' => $data->statusId, 'updated_at' => $currentDatetime]);
						}
					}
				}
			}
		}
		return "Data sync successful";
	}

	//erp get api loan data
	public function GetErpPostedLoanData($branchcode)
	{
		$access_token = $this->tokenVerify();
		$clientid = 'Ieg1N5W2qh3hF0qS9Zh2wq6eex2DB935';
		$clientsecret = '4H2QJ89kYQBStaCuY73h';
		$url = 'https://bracapitesting.brac.net/dcs/v1/branches/' . $branchcode . '/buffer-loan-proposals';

		$headers = array(
			'Authorization: Bearer ' . $access_token,
			'Accept: application/json',
		);

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
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
				// dd($data);

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

					if (array_key_exists('relationshipId', $data->secondInsurer)) {
						$secondinsurerrelationshipid = $data->secondInsurer->relationshipId;
					} else {
						$secondinsurerrelationshipid = null;
					}
					if (array_key_exists('genderId', $data->secondInsurer)) {
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

				if ($data->loanProposalStatusId == 4 or $data->loanProposalStatusId == 3) {	//if erp loan disbursed or reject
					if ($checkLoan != null) {                //if addmission has data
						// $member = DB::table($db . '.posted_admission')->where('memberid', $data->memberId)->first();
						$serverurl = DB::Table($dberp . '.server_url')->where('server_status', 3)->where('status', 1)->first();
						$key = '5d0a4a85-df7a-scapi-bits-93eb-145f6a9902ae';
						$UpdatedAt = "2000-01-01 00:00:00";
						$member = Http::get($serverurl->url . 'MemberList', [
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
						if ($checkLoan->erp_loan_id == null and $checkLoan->ErpStatus == 1) {		//if erp member id empty in dcs admission table
							if ($member != null) {
								$this->sendAppNotificationForErpLoanAction($data, $member);
							}
						}
					}
				}

				if ($checkPostedLoan == null) {
					DB::table($db . '.posted_loan')->insert($values);
					if ($data->loanProposalStatusId == 4) {
						if ($checkLoan != null) {
							DB::table($db . '.loans')->where('loan_id', $data->id)->update(['erp_loan_id' => $data->loanAccountId, 'ErpStatus' => $data->loanProposalStatusId, 'updated_at' => $currentDatetime]);
						}
					} else {
						if ($checkLoan != null) {
							DB::table($db . '.loans')->where('loan_id', $data->id)->update(['ErpStatus' => $data->loanProposalStatusId, 'updated_at' => $currentDatetime]);
						}
					}
				} else {
					// if ($data->updated == TRUE) {
					DB::table($db . '.posted_loan')->where('loan_id', $data->id)->update($values);
					if ($data->loanProposalStatusId == 4) {
						if ($checkLoan != null) {
							DB::table($db . '.loans')->where('loan_id', $data->id)->update(['erp_loan_id' => $data->loanAccountId, 'ErpStatus' => $data->loanProposalStatusId, 'updated_at' => $currentDatetime]);
						}
					} else {
						if ($checkLoan != null) {
							DB::table($db . '.loans')->where('loan_id', $data->id)->update(['ErpStatus' => $data->loanProposalStatusId, 'updated_at' => $currentDatetime]);
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

		$checkRoleHierarchie = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', $projectcode)->where('position', 1)->first();

		// for bm role 
		if ($checkRoleHierarchie->designation == 'BM') {
			$findpin = DB::table($dberp . '.polist')->select('cono')->where('status', 1)->where('branchcode', $brcode)->where('projectcode', $projectcode)
				->Where(function ($query) {
					// $query->where('desig','Branch Manager')->orWhere('desig','Assistant Branch Manager');
					$query->where('desig', 'Branch Manager');
				})->first();
			if ($findpin != null) {
				$nextrolepin = $findpin->cono;
			} else {
				$findpin = DB::table($dberp . '.polist')->select('cono')->where('status', 1)->where('branchcode', $brcode)->where('projectcode', $projectcode)
					->Where(function ($query) {
						$query->Where('desig', 'Assistant Branch Manager');
						// $query->where('desig','Branch Manager');
					})->first();
				if ($findpin != null) {
					$nextrolepin = $findpin->cono;
				}
			}
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
		}

		$checkRoleHierarchie = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', $projectcode)->where('position', 1)->first();

		// for bm role 
		if ($checkRoleHierarchie->designation == 'BM') {
			$findpin = DB::table($dberp . '.polist')->select('cono')->where('status', 1)->where('branchcode', $brcode)->where('projectcode', $projectcode)
				->Where(function ($query) {
					// $query->where('desig','Branch Manager')->orWhere('desig','Assistant Branch Manager');
					$query->where('desig', 'Branch Manager');
				})->first();
			if ($findpin != null) {
				$nextrolepin = $findpin->cono;
			} else {
				$findpin = DB::table($dberp . '.polist')->select('cono')->where('status', 1)->where('branchcode', $brcode)->where('projectcode', $projectcode)
					->Where(function ($query) {
						$query->Where('desig', 'Assistant Branch Manager');
						// $query->where('desig','Branch Manager');
					})->first();
				if ($findpin != null) {
					$nextrolepin = $findpin->cono;
				}
			}
		}

		// for am role
		if ($checkRoleHierarchie->designation == 'AM') {
			$nextrolepin = 'b123';
		}

		$this->sendAppNotification($entollmentid, $doc_type, $popin, $msgcontent, $action);
		$this->sendAppNotification($entollmentid, $doc_type, $nextrolepin, $msgcontent, $action);
	}
	//end erp api's functions

	//tab po bm dashboard reports api
	public function ReportSync(Request $req)
	{
		//echo "Huda";
		//dd("H");
		$db = $this->db;
		$token = Request::input('token');
		$branchcode = Request::input('branchcode');
		$ProjectCode = Request::input('projectcode');
		$project_code = Request::input('projectcode');
		$branchcode = (int)$branchcode;
		$projectcode = (int)$ProjectCode;
		if ($token == '7f30f4491cb4435984616d1913e88389') {
			if ($branchcode != null and $projectcode != null) {
				$FormConfig = DB::Table($db . '.form_configs')->where('projectcode', $project_code)->get();


				$result = array(
					"status" => "S",
					"message" => "",
					"FormConfig" => $FormConfig,
					"PayloadData" => $PayloadData,
					"OfficeMapping" => $OfficeMapping,
					"ProductDetail" => $ProductDetail,
					"ProjectwiseMemberCategory" => $ProjectwiseMemberCategory,
					"ProductProjectMemberCategory" => $ProductProjectMemberCategory,
					"SchememSectorSubsector" => $SchememSectorSubsector,
					"AuthConfig" => $auth_array,
				);
				return json_encode($result);
			} else {
				$result = array("status" => "E", "message" => "Invalid perameter!");
				return json_encode($result);
			}
		} else {
			$result = array("status" => "E", "message" => "Invalid token!");
			return json_encode($result);
		}
	}

	public function DcsBufferStatusCheck()
	{
		$db = $this->db;
		$currentDatetime = date("Y-m-d h:i:s");
		$admissionBufferPendings = DB::Table($db . '.admissions')->select('branchcode', 'projectcode', 'created_at')->where('ErpStatus', 1)->get();
		$loanBufferPendings = DB::Table($db . '.loans')->select('branchcode', 'projectcode', 'time')->where('ErpStatus', 1)->get();
		Log::channel('daily')->info('Dcs Buffer Status Check at ' . $currentDatetime);

		foreach ($loanBufferPendings as $row) {
			$applicationdate = date('Y-m-d', strtotime($row->time));
			$branchcode = $row->branchcode;
			$projectcode = $row->projectcode;
			$this->GetErpPendingLoanDataStatus($branchcode, $projectcode, $applicationdate);
		}

		foreach ($admissionBufferPendings as $row) {
			$applicationdate = date('Y-m-d', strtotime($row->created_at));
			$branchcode = $row->branchcode;
			$projectcode = $row->projectcode;
			$this->GetErpPendingAdmissionDataStatus($branchcode, $projectcode, $applicationdate);
		}
	}

	//erp get api admission data
	public function GetErpPendingAdmissionDataStatus($branchcode, $projectcode, $applicationdate)
	{
		$access_token = $this->tokenVerify();
		$clientid = 'Ieg1N5W2qh3hF0qS9Zh2wq6eex2DB935';
		$clientsecret = '4H2QJ89kYQBStaCuY73h';
		$url = 'https://bracapitesting.brac.net/dcs/v1/branches/' . $branchcode . '/buffer-members?projectcode=' . $projectcode . '&applicationDate=' . $applicationdate;
		// $url = 'https://bracapitesting.brac.net/dcs/v1/branches/1344/buffer-members?projectcode=015';

		$headers = array(
			'Authorization: Bearer ' . $access_token,
			'Accept: application/json',
		);

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
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

		if ($err) {
			return "cURL Error #:" . $err;
		} else {

			// dd(json_decode($response));
			if (json_last_error() == 0) {
				return $this->checkPostedAdmissionStatus($response);
			} else {
				return "Erp Server Down";
			}
		}
	}

	//erp get api admission data's database insertion
	public function checkPostedAdmissionStatus($response)
	{
		// dd($response);
		$db = $this->db;
		$currentDatetime = date("Y-m-d h:i:s");
		$arrayAddmission = json_decode($response);
		foreach ($arrayAddmission as $data) {
			// dd($data->id);
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

			if ($data->statusId == 2 or $data->statusId == 3) {	//if erp approve and reject
				if ($checkAdmission != null) {                //if addmission has data
					if ($checkAdmission->MemberId == null and $checkAdmission->ErpStatus == 1) {		//if erp member id empty in dcs admission table
						$this->sendAppNotificationForErpAddmissionAction($data);
					}
				}
			}


			if ($checkPostedAdmission == null) {
				DB::table($db . '.posted_admission')->insert($values);
				if ($data->statusId == 2) {
					if ($checkAdmission != null) {
						DB::table($db . '.admissions')->where('entollmentid', $data->id)->update(['MemberId' => $data->memberId, 'ErpStatus' => $data->statusId, 'updated_at' => $currentDatetime]);
					}
					if ($checkLoan != null) {
						DB::table($db . '.loans')->where('mem_id', $data->id)->update(['erp_mem_id' => $data->memberId, 'updated_at' => $currentDatetime]);
					}
				} elseif ($data->statusId == 3) {
					if ($checkAdmission != null) {
						DB::table($db . '.admissions')->where('entollmentid', $data->id)->update(['ErpStatus' => $data->statusId, 'updated_at' => $currentDatetime]);
					}
				}
			} else {
				// if ($data->updated == TRUE) {
				DB::table($db . '.posted_admission')->where('admission_id', $data->id)->update($values);
				// }
				if ($data->statusId == 2) {
					if ($checkAdmission != null) {
						DB::table($db . '.admissions')->where('entollmentid', $data->id)->update(['MemberId' => $data->memberId, 'ErpStatus' => $data->statusId, 'updated_at' => $currentDatetime]);
					}
					if ($checkLoan != null) {
						DB::table($db . '.loans')->where('mem_id', $data->id)->update(['erp_mem_id' => $data->memberId, 'updated_at' => $currentDatetime]);
					}
				} elseif ($data->statusId == 3) {
					if ($checkAdmission != null) {
						DB::table($db . '.admissions')->where('entollmentid', $data->id)->update(['ErpStatus' => $data->statusId, 'updated_at' => $currentDatetime]);
					}
				}
			}
		}
		return "Data sync successful";
	}

	//erp get api loan data
	public function GetErpPendingLoanDataStatus($branchcode, $projectcode, $applicationdate)
	{
		$access_token = $this->tokenVerify();
		$clientid = 'Ieg1N5W2qh3hF0qS9Zh2wq6eex2DB935';
		$clientsecret = '4H2QJ89kYQBStaCuY73h';
		$url = 'https://bracapitesting.brac.net/dcs/v1/branches/' . $branchcode . '/buffer-loan-proposals?projectcode=' . $projectcode . '&applicationDate=' . $applicationdate;

		$headers = array(
			'Authorization: Bearer ' . $access_token,
			'Accept: application/json',
		);

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => $headers,
		));

		$response = curl_exec($curl);
		// dd($curl);

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
		$BufferMemberStatus = $response;
		$db = $this->db;
		$dberp = $this->dberp;
		$currentDatetime = date("Y-m-d h:i:s");
		$arrayLoan = json_decode($response);
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

				if (array_key_exists('relationshipId', $data->secondInsurer)) {
					$secondinsurerrelationshipid = $data->secondInsurer->relationshipId;
				} else {
					$secondinsurerrelationshipid = null;
				}
				if (array_key_exists('genderId', $data->secondInsurer)) {
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
			// dd($checkLoan);

			if ($data->loanProposalStatusId == 4 or $data->loanProposalStatusId == 3) {	//if erp loan disbursed or reject
				if ($checkLoan != null) {                //if addmission has data
					// $member = DB::table($db . '.posted_admission')->where('memberid', $data->memberId)->first();
					$serverurl = DB::Table($dberp . '.server_url')->where('server_status', 3)->where('status', 1)->first();
					$key = '5d0a4a85-df7a-scapi-bits-93eb-145f6a9902ae';
					$UpdatedAt = "2000-01-01 00:00:00";
					$member = Http::get($serverurl->url . 'MemberList', [
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

					if ($checkLoan->erp_loan_id == null and $checkLoan->ErpStatus == 1) {		//if erp member id empty in dcs admission table
						if ($member != null) {
							$this->sendAppNotificationForErpLoanAction($data, $member);
						}
					}
				}
			}

			if ($checkPostedLoan == null) {
				DB::table($db . '.posted_loan')->insert($values);
				if ($data->loanProposalStatusId == 4) {
					if ($checkLoan != null) {
						DB::table($db . '.loans')->where('loan_id', $data->id)->update(['erp_loan_id' => $data->loanAccountId, 'ErpStatus' => $data->loanProposalStatusId, 'updated_at' => $currentDatetime]);
					}
				} else {
					if ($checkLoan != null) {
						DB::table($db . '.loans')->where('loan_id', $data->id)->update(['ErpStatus' => $data->loanProposalStatusId, 'updated_at' => $currentDatetime]);
					}
				}
			} else {
				// if ($data->updated == TRUE) {
				DB::table($db . '.posted_loan')->where('loan_id', $data->id)->update($values);
				if ($data->loanProposalStatusId == 4) {
					if ($checkLoan != null) {
						DB::table($db . '.loans')->where('loan_id', $data->id)->update(['erp_loan_id' => $data->loanAccountId, 'ErpStatus' => $data->loanProposalStatusId, 'updated_at' => $currentDatetime]);
					}
				} else {
					if ($checkLoan != null) {
						DB::table($db . '.loans')->where('loan_id', $data->id)->update(['ErpStatus' => $data->loanProposalStatusId, 'updated_at' => $currentDatetime]);
					}
				}
				// }
			}
		}
		return "Data sync successful";
	}

	public function DcsDataPulling()
	{
		$db = $this->db;
		$currentDatetime = date("Y-m-d h:i:s");
		$access_token = $this->tokenVerify();
		$url = 'https://bracapitesting.brac.net/dcs/v1/';

		DB::beginTransaction();
		try {
			// table deletion
			DB::table($db . '.project_wise_branch_growth_types')->truncate();
			DB::table($db . '.celing_configs')->truncate();
			DB::table($db . '.projectwise_member_category')->truncate();
			DB::table($db . '.product_project_member_category')->truncate();
			DB::table($db . '.product_details')->truncate();
			DB::table($db . '.schemem_sector_subsector')->truncate();
			DB::table($db . '.insurance_products')->truncate();
			DB::table($db . '.office_mapping')->truncate();
			// end table delete

			//branch-project-growth-type-mappings (5 api dociment)
			$page = 1;
			$branchprojectgrowthtypemappings = Http::withToken($access_token)->get($url . 'branch-project-growth-type-mappings?page=' . $page);
			$branchprojectgrowthtypemappings = $branchprojectgrowthtypemappings->object();
			if ($branchprojectgrowthtypemappings) {
				foreach ($branchprojectgrowthtypemappings as $row) {
					DB::Table($db . '.project_wise_branch_growth_types')->insert(['office_id' => null, 'office_code' => $row->officeCode, 'office_name' => $row->officeName, 'project_code' => $row->projectCode, 'project_name' => $row->projectName, 'branch_growth_type' => $row->branchGrowthType]);
				}
				$page++;
				while ($branchprojectgrowthtypemappings) {
					$branchprojectgrowthtypemappings = Http::withToken($access_token)->get($url . 'branch-project-growth-type-mappings?page=' . $page);
					$branchprojectgrowthtypemappings = $branchprojectgrowthtypemappings->object();

					if ($branchprojectgrowthtypemappings) {
						foreach ($branchprojectgrowthtypemappings as $row) {
							DB::Table($db . '.project_wise_branch_growth_types')->insert(['office_id' => null, 'office_code' => $row->officeCode, 'office_name' => $row->officeName, 'project_code' => $row->projectCode, 'project_name' => $row->projectName, 'branch_growth_type' => $row->branchGrowthType]);
						}
					}
					$page++;
				}
			}
			//end branch-project-growth-type-mappings (5 api dociment)

			//Approver wise loan limit mapping API  (6 api dociment)
			$approverwiseloanlimitmappings = Http::withToken($access_token)->get($url . 'approver-wise-loan-limit-mappings');
			$approverwiseloanlimitmappings = $approverwiseloanlimitmappings->object();
			if ($approverwiseloanlimitmappings) {
				foreach ($approverwiseloanlimitmappings as $row) {
					DB::Table($db . '.celing_configs')->insert(['projectcode' => $row->projectCode, 'approver' => $row->approverName, 'growth_rate' => $row->branchGrowthType, 'limit_form' => $row->limitFrom, 'limit_to' => $row->limitTo, 'repeat_limit_form' => $row->repeatLimitFrom, 'repeat_limit_to' => $row->repeatLimitTo]);
				}
			}
			//end Approver wise loan limit mapping API  (6 api dociment)

			//Project wise Member type mappings API  (7 api dociment)
			$projectwisemembertypes = Http::withToken($access_token)->get($url . 'project-wise-member-types');
			$projectwisemembertypes = $projectwisemembertypes->object();
			if ($projectwisemembertypes) {
				foreach ($projectwisemembertypes as $row) {
					DB::Table($db . '.projectwise_member_category')->insert(['categoryid' => $row->categoryId, 'categoryname' => $row->categoryName, 'projectcode' => $row->projectCode]);
				}
			}
			//end Project wise Member type mappings API  (7 api dociment)

			//specific branch wise loan products (8 api dociment)
			$allbranchwiseloanproducts = Http::withToken($access_token)->get($url . 'all-branch-wise-loan-products');
			$allbranchwiseloanproducts = $allbranchwiseloanproducts->object();
			foreach ($allbranchwiseloanproducts as $row) {
				DB::Table($db . '.product_project_member_category')->insert(['productcode' => $row->productCode, 'productname' => $row->productName, 'projectcode' => $row->projectCode, 'membercategory' => $row->memberCategoryName, 'membercategoryid' => $row->memberCategoryId, 'productid' => $row->productId, 'branchcode' => "*"]);
			}
			//end specific branch wise loan products (8 api dociment)

			//specific branch wise loan products (8.1 api dociment)
			$page = 1;
			$specificbranchwiseloanproducts = Http::withToken($access_token)->get($url . 'specific-branch-wise-loan-products?page=' . $page);
			$specificbranchwiseloanproducts = $specificbranchwiseloanproducts->object();
			if ($specificbranchwiseloanproducts) {
				foreach ($specificbranchwiseloanproducts as $row) {
					DB::Table($db . '.product_project_member_category')->insert(['productcode' => $row->productCode, 'productname' => $row->productName, 'projectcode' => $row->projectCode, 'membercategory' => $row->memberCategoryName, 'membercategoryid' => $row->memberCategoryId, 'productid' => $row->productId, 'branchcode' => $row->branchCode]);
				}
				$page++;
				while ($specificbranchwiseloanproducts) {
					$specificbranchwiseloanproducts = Http::withToken($access_token)->get($url . 'specific-branch-wise-loan-products?page=' . $page);
					$specificbranchwiseloanproducts = $specificbranchwiseloanproducts->object();

					if ($specificbranchwiseloanproducts) {
						foreach ($specificbranchwiseloanproducts as $row) {
							DB::Table($db . '.product_project_member_category')->insert(['productcode' => $row->productCode, 'productname' => $row->productName, 'projectcode' => $row->projectCode, 'membercategory' => $row->memberCategoryName, 'membercategoryid' => $row->memberCategoryId, 'productid' => $row->productId, 'branchcode' => $row->branchCode]);
						}
					}
					$page++;
				}
			}
			//end specific branch wise loan products (8.1 api dociment)

			//Loan product-wise frequency mapping (9 api dociment)
			$loanproductwisefrequencymappings = Http::withToken($access_token)->get($url . 'loan-product-wise-frequency-mappings');
			$loanproductwisefrequencymappings = $loanproductwisefrequencymappings->object();
			foreach ($loanproductwisefrequencymappings as $row) {
				DB::Table($db . '.product_details')->insert(['productcode' => $row->loanProductCode, 'productname' => $row->loanProductName, 'frequency' => $row->frequency, 'frequencyid' => $row->frequencyId, 'noofinstallment' => $row->noOfInstallment, 'loanduration' => $row->loanDuration]);
			}
			//end Loan product-wise frequency mapping (9 api dociment)

			//Branch-wise Project and insurance mapping  (10 api dociment)
			$branchwiseprojectinsuranceproductsmappings = Http::withToken($access_token)->get($url . 'branch-wise-projects-insurance-products-mappings');
			$branchwiseprojectinsuranceproductsmappings = $branchwiseprojectinsuranceproductsmappings->object();
			foreach ($branchwiseprojectinsuranceproductsmappings as $row) {
				DB::Table($db . '.insurance_products')->insert(['product_id' => $row->insuranceProductId, 'product_code' => $row->insuranceProductCode, 'product_name' => $row->insuranceProductName, 'project_code' => $row->projectCode, 'branchcode' => $row->branchCode]);
			}
			//end Branch-wise Project and insurance mapping (10 api dociment)

			//Geo division district thana Upazila mapping API (11 api dociment)
			$page = 1;
			$districtdivisionupazilaofficemappings = Http::withToken($access_token)->get($url . 'district-division-upazila-office-mappings?page=' . $page);
			$districtdivisionupazilaofficemappings = $districtdivisionupazilaofficemappings->object();
			if ($districtdivisionupazilaofficemappings) {
				foreach ($districtdivisionupazilaofficemappings as $row) {
					DB::Table($db . '.office_mapping')->insert(['division_id' => $row->divisionId, 'division_name' => $row->divisionName, 'district_id' => $row->districtId, 'district_name' => $row->districtName, 'thana_id' => $row->thanaId, 'thana_name' => $row->thanaName, 'branchcode' => $row->officeCode, 'branch_name' => $row->officeName, 'status' => 1]);
				}
				$page++;
				while ($districtdivisionupazilaofficemappings) {
					$districtdivisionupazilaofficemappings = Http::withToken($access_token)->get($url . 'district-division-upazila-office-mappings?page=' . $page);
					$districtdivisionupazilaofficemappings = $districtdivisionupazilaofficemappings->object();

					if ($districtdivisionupazilaofficemappings) {
						foreach ($districtdivisionupazilaofficemappings as $row) {
							DB::Table($db . '.office_mapping')->insert(['division_id' => $row->divisionId, 'division_name' => $row->divisionName, 'district_id' => $row->districtId, 'district_name' => $row->districtName, 'thana_id' => $row->thanaId, 'thana_name' => $row->thanaName, 'branchcode' => $row->officeCode, 'branch_name' => $row->officeName, 'status' => 1]);
						}
					}
					$page++;
				}
			}
			//end Geo division district thana Upazila mapping API (11 api dociment)

			//All Branch-wise Project Product Scheme and Sector mapping API (12 api dociment)
			$page = 1;
			$allbranchwiseprojectproductschemesectormappings = Http::withToken($access_token)->get($url . 'all-branch-wise-project-product-scheme-sector-mappings?page=' . $page);
			$allbranchwiseprojectproductschemesectormappings = $allbranchwiseprojectproductschemesectormappings->object();
			if ($allbranchwiseprojectproductschemesectormappings) {
				foreach ($allbranchwiseprojectproductschemesectormappings as $row) {
					DB::Table($db . '.schemem_sector_subsector')->insert(['sectorid' => $row->sectorId, 'sectorcode' => $row->sectorCode, 'sectorname' => $row->sectorName, 'subsectorid' => $row->subSectorId, 'subsectorcode' => $row->subSectorCode, 'subsectorname' => $row->subSectorName, 'schemeid' => $row->schemeId, 'schemecode' => $row->schemeCode, 'schemename' => $row->schemeName, 'branchcode' => "*", 'loanproductid' => $row->loanProductId, 'productcode' => $row->loanProductCode, 'productname' => $row->loanProductName, 'productid' => null, 'projectcode' => $row->projectCode]);
				}
				$page++;
				while ($allbranchwiseprojectproductschemesectormappings) {
					$allbranchwiseprojectproductschemesectormappings = Http::withToken($access_token)->get($url . 'all-branch-wise-project-product-scheme-sector-mappings?page=' . $page);
					$allbranchwiseprojectproductschemesectormappings = $allbranchwiseprojectproductschemesectormappings->object();

					if ($allbranchwiseprojectproductschemesectormappings) {
						foreach ($allbranchwiseprojectproductschemesectormappings as $row) {
							DB::Table($db . '.schemem_sector_subsector')->insert(['sectorid' => $row->sectorId, 'sectorcode' => $row->sectorCode, 'sectorname' => $row->sectorName, 'subsectorid' => $row->subSectorId, 'subsectorcode' => $row->subSectorCode, 'subsectorname' => $row->subSectorName, 'schemeid' => $row->schemeId, 'schemecode' => $row->schemeCode, 'schemename' => $row->schemeName, 'branchcode' => "*", 'loanproductid' => $row->loanProductId, 'productcode' => $row->loanProductCode, 'productname' => $row->loanProductName, 'productid' => null, 'projectcode' => $row->projectCode]);
						}
					}
					$page++;
				}
			}
			//end All Branch-wise Project Product Scheme and Sector mapping API (12 api dociment)

			// Specific Branch-wise Project Product Scheme and Sector mapping API  (12.1 api dociment)
			$page = 1;
			$specificbranchwiseprojectproductschemesectormappings = Http::withToken($access_token)->get($url . 'specific-branch-wise-project-product-scheme-sector-mappings?page=' . $page);

			$specificbranchwiseprojectproductschemesectormappings = $specificbranchwiseprojectproductschemesectormappings->object();
			if ($specificbranchwiseprojectproductschemesectormappings) {
				foreach ($specificbranchwiseprojectproductschemesectormappings as $row) {
					DB::Table($db . '.schemem_sector_subsector')->insert(['sectorid' => $row->sectorId, 'sectorcode' => $row->sectorCode, 'sectorname' => $row->sectorName, 'subsectorid' => $row->subSectorId, 'subsectorcode' => $row->subSectorCode, 'subsectorname' => $row->subSectorName, 'schemeid' => $row->schemeId, 'schemecode' => $row->schemeCode, 'schemename' => $row->schemeName, 'branchcode' => $row->branchCode, 'loanproductid' => $row->loanProductId, 'productcode' => $row->loanProductCode, 'productname' => $row->loanProductName, 'productid' => null, 'projectcode' => $row->projectCode]);
				}
				$page++;
				while ($specificbranchwiseprojectproductschemesectormappings) {
					$specificbranchwiseprojectproductschemesectormappings = Http::withToken($access_token)->get($url . 'specific-branch-wise-project-product-scheme-sector-mappings?page=' . $page);
					$specificbranchwiseprojectproductschemesectormappings = $specificbranchwiseprojectproductschemesectormappings->object();

					if ($specificbranchwiseprojectproductschemesectormappings) {
						foreach ($specificbranchwiseprojectproductschemesectormappings as $row) {
							DB::Table($db . '.schemem_sector_subsector')->insert(['sectorid' => $row->sectorId, 'sectorcode' => $row->sectorCode, 'sectorname' => $row->sectorName, 'subsectorid' => $row->subSectorId, 'subsectorcode' => $row->subSectorCode, 'subsectorname' => $row->subSectorName, 'schemeid' => $row->schemeId, 'schemecode' => $row->schemeCode, 'schemename' => $row->schemeName, 'branchcode' => $row->branchCode, 'loanproductid' => $row->loanProductId, 'productcode' => $row->loanProductCode, 'productname' => $row->loanProductName, 'productid' => null, 'projectcode' => $row->projectCode]);
						}
					}
					$page++;
				}
			}
			//end Specific Branch-wise Project Product Scheme and Sector mapping API  (12.1 api dociment)
			Log::channel('daily')->info('Data mapping Sucessfull');
			DB::commit();

			return "Data Pulled Sucessfully";
		} catch (\Throwable $e) {
			DB::rollback();
			throw $e;
			Log::channel('daily')->info('Data mapping pull error: ' . $e);
		}
	}
}
