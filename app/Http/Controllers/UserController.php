<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use DB;

class UserController extends Controller
{
    private $dberp = 'erptestingserver'; //erp test db
    private $db = 'dcs';                //dcs db name

    public function index()
    {
        $db = config('database.db');
        $admins = DB::table($db . '.admin_config')->get();
        return view('Admin', compact('admins'));
    }

    public function store(Request $request)
    {
        $db = config('database.db');
        $this->validate($request, [
            'phone' => 'digits:11|numeric'
        ]);
        $adminInfo = array(
            'name' => $request->get('Name'),
            'username' => $request->get('username'),
            'email' => $request->get('email'),
            'phone' => $request->get('phone'),
            'password' => $request->get('password'),
            'userpin' => $request->get('userpin'),
            'role' => $request->get('role'),
            'status' => $request->get('status')
        );
        if ($adminInfo) {
            DB::table($db . '.admin_config')->insert($adminInfo);
            return redirect()->back()->with('success', 'Data Save Successfully');
        } else {
            return redirect()->back()->with('error', 'Data Save failed');
        }
    }

    public function edit($id)
    {
        $db = config('database.db');
        $adminData = DB::table($db . '.admin_config')->where('id', $id)->first();
        return view('admin-edit')->with('adminData', $adminData);
    }

    public function update(Request $request, $id)
    {
        $db = config('database.db');
        $this->validate($request, [
            'phone' => 'digits:11|numeric'
        ]);
        $adminInfo = array(
            'name' => $request->get('Name'),
            'username' => $request->get('username'),
            'email' => $request->get('email'),
            'phone' => $request->get('phone'),
            'password' => $request->get('password'),
            'userpin' => $request->get('userpin'),
            'role' => $request->get('role'),
            'status' => $request->get('status')
        );
        if ($adminInfo) {
            DB::table($db . '.admin_config')->where('id', $id)->update($adminInfo);
            return redirect()->to('config/admin-config')->with('success', 'Data updated Successfully');
        } else {
            return redirect()->to('config/admin-config')->with('error', 'Data update failed');
        }
    }

    public function delete($id)
    {
        $db = config('database.db');
        $adminData = DB::table($db . '.admin_config')->where('id', $id)->delete();
        if ($adminData) {
            return redirect()->back()->with('success', 'Data deleted successfuly');
        } else {
            return redirect()->back()->with('error', 'Data deleted failed');
        }
    }

    public function testLoanSet($id)
    {
        $db = $this->db;
        $dberp = $this->dberp;
        $serverurl = DB::Table($dberp . '.server_url')->where('server_status', 3)->where('status', 1)->first();
        $data = DB::table($db . '.loans')->where('id', $id)->first();
        $loanapprover = DB::table($db . '.role_hierarchies')->where('projectcode', $data->projectcode)->where('position', $data->reciverrole)->first();
        $key = '5d0a4a85-df7a-scapi-bits-93eb-145f6a9902ae';
        $UpdatedAt = "2000-01-01 00:00:00";
        // dd($data);

        $member = Http::get($serverurl->url . 'MemberList', [
            'BranchCode' => $data->branchcode,
            'CONo' => $data->assignedpo,
            'ProjectCode' => $data->projectcode,
            'UpdatedAt' => $UpdatedAt,
            'Status' => 2,
            'OrgNo' => $data->orgno,
            'OrgMemNo' => $data->orgmemno,
            'key' => $key
        ]);
        // dd($member);
        $member = $member->object();
        return json_encode($member->data[0]);
        // dd($member->data[0]);

        $memberInfo = DB::table($db . '.posted_admission')->where('memberid', $data->erp_mem_id)->first();

        $projectcode = (int)$data->projectcode;
        $insuranceProduct = DB::table($db . '.insurance_products')->where('project_code', $projectcode)->first();
        // dd($memberInfo->membertypeid);
        $arrayData = array();
        $nominees = array();
        $nominees[] = array(
            "contactNo" => $memberInfo->nomineescontactno,
            "dateOfBirth" => $memberInfo->nomineesdateofbirth,
            "id" => null,
            "idCard" => array(),
            "idCard" => array(
                "backImageUrl" => $memberInfo->nomineesbackimageurl,
                "cardTypeId" => $memberInfo->nomineescardtypeid,
                "expiryDate" => $memberInfo->nomineesexpirydate,
                "frontImageUrl" => $memberInfo->nomineesfrontimageurl,
                "idCardNo" => $memberInfo->nomineesidcardno,
                "issueDate" => $memberInfo->nomineesissuedate,
                "issuePlace" => $memberInfo->nomineesissueplace,
            ),
            "name" => $memberInfo->nomineesname,
            "relationshipId" => $memberInfo->nomineesrelationshipid,
        );

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
                $secondInsurer = array(
                    "dateOfBirth" => $memberInfo->spousedateofbirth,
                    "genderId" => null,
                    "idCard" => array(),
                    "idCard" => array(
                        "backImageUrl" => $memberInfo->spouseidcardbackimageurl,
                        "cardTypeId" => $memberInfo->spouseidcardcardtypeid,
                        "expiryDate" => $memberInfo->spouseidcardexpirydate,
                        "frontImageUrl" => $memberInfo->spouseidcardfrontimageurl,
                        "idCardNo" => $memberInfo->spouseidcardidcardno,
                        "issueDate" => $memberInfo->spouseidcardissuedate,
                        "issuePlace" => $memberInfo->spouseidcardissuedate,
                    ),
                    "name" => $memberInfo->spousenameen,
                    "relationshipId" => null,
                );
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
                        "idCardNo" => $data->insurn_mainID,
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
            "memberTypeId" => $memberInfo->membertypeid,
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
            "secondInsurer" => $secondInsurer,    //array
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
}
