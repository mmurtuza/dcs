<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Admission;
use App\Models\Loans;
use Illuminate\Support\Facades\Http;
use App\Models\Branch;
use Log;
use App\Http\Controllers\LiveApiController;


ini_set('memory_limit', '3072M');
ini_set('max_execution_time', 1800);

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $db = config('database.db');
        $role_designation = session('role_designation');
        $request->session()->put('status_btn', '1');

        // date make
        $month = date('m');
        $day = date('d');
        $year = date('Y');

        return view('Dashboard');
    }

    public function fetchData(Request $request)
    {
        $today = date('Y-m-d');
        $from_date = date('Y-01-01');
        $status = '';
        $erpstatus = '';
        $status = $request->get('status');
        // $role_designation = session('role_designation');
        $erpstatus = $request->get('ErpStatus');
        $data = DB::table('dcs.loans')
            ->where('reciverrole', '!=', '0')
            ->where('status', $status)
            ->where('ErpStatus', $erpstatus)
            ->where('projectcode', session('projectcode'))
            ->whereDate('loans.time', '>=', $from_date)
            ->whereDate('loans.time', '<=', $today)
            ->get();

        return $data;
    }

    public function search(Request $request)
    {

        $division = $request->input('division');
        $region = $request->input('region');
        $area = $request->input('area');
        $branch = $request->input('branch');
        $po = $request->input('po');
        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');

        if ($division != null) {
            $getbranch = DB::Table('branch')->select('branch_id')->where('division_id', $division)->where('program_id', 1)->distinct('branch_id')->get();
        }
        if ($region != null) {
            $getbranch = DB::Table('branch')->select('branch_id')->where('division_id', $division)->where('region_id', $region)->where('program_id', 1)->distinct('branch_id')->get();
        }

        if ($area != null) {
            $getbranch = DB::Table('branch')->select('branch_id')->where('division_id', $division)->where('division_id', $division)->where('region_id', $region)->where('area_id', $area)->where('program_id', 1)->distinct('branch_id')->get();
        }

        if ($branch != null) {
            $getbranch = DB::Table('branch')->select('branch_id')->where('branch_id', $branch)->where('program_id', 1)->get();
        };

        $all_pending_loan_datas = DB::table('dcs.loans')
            ->join('dcs.branch', 'dcs.loans.branchcode', '=', 'dcs.branch.branch_id')
            ->where('reciverrole', '!=', '0')
            ->where('status', 1)
            ->where('branchcode', $getbranch)
            ->get();

        return response()->json($all_pending_loan_datas);
    }


    public function GetPendingCount(Request $request)
    {
        $all_pending_loan_count = DB::Table('dcs.loans')->where('status', 1)->count();
        echo json_encode($all_pending_loan_count);
    }

    public function GetApproveCount(Request $request)
    {
        $all_approve_loan_count = DB::Table('dcs.loans')->where('status', 2)->count();
        echo json_encode($all_approve_loan_count);
    }
    public function co(Request $request)
    {
        $db = config('database.db');
        $role_designation = session('role_designation');
        //echo $role_designation;
        $request->session()->put('status_btn', '1');

        // Get current date
        $today = date('Y-m-d');
        $from_date = date('Y-01-01');


        $showStartDate = date('d-M-Y', strtotime($from_date));
        $showEndDate = date('d-M-Y', strtotime($today));

        // Role wise data distribution
        $branch = null;
        $branchcodes = [];

        if ($role_designation == 'AM') {
            $value = Branch::where('program_id', session('program_id'))
                ->get();

            $search2 = Branch::where([
                'area_id' => session('asid'),
                'program_id' => session('program_id')
            ])->distinct('branch_id')->get();

            $branch = Branch::where([
                'area_id' => session('asid'),
                'program_id' => session('program_id')
            ])->first();
        } else if ($role_designation == 'RM') {
            $value = Branch::where('program_id', session('program_id'))
                ->get();

            $search2 = Branch::where([
                'region_id' => session('asid'),
                'program_id' => session('program_id')
            ])->distinct('area_id')->get();

            $branch = Branch::where([
                'region_id' => session('asid'),
                'program_id' => session('program_id')
            ])->first();

            foreach ($search2 as $branch) {
                $branchCode = str_pad($branch->branch_id, 4, "0", STR_PAD_LEFT);
                $branchcodes[] = $branchCode;
            }
        } else if ($role_designation == 'DM') {
            $value = Branch::where('program_id', session('program_id'))
                ->get();

            $search2 = Branch::where([
                'division_id' => session('asid'),
                'program_id' => session('program_id')
            ])->distinct('region_id')->get();

            $branch = Branch::where([
                'division_id' => session('asid'),
                'program_id' => session('program_id')
            ])->first();
        } else if ($role_designation == 'HO' || $role_designation == 'PH') {
            $value = Branch::where('program_id', session('program_id'))
                ->get();

            $branch = Branch::where([
                'division_id' => session('asid'),
                'program_id' => session('program_id')
            ])->first();

            $search2 = DB::Table('public.branch')->where('program_id', session('program_id'))->get();
        } else {
            return redirect()->back()->with('error', 'Data does not match.');
        }
        foreach ($search2 as $branch) {
            $branchcode[] = str_pad($branch->branch_id, 4, "0", STR_PAD_LEFT);
        }

        if (!empty($branchcode)) {

            $pending_admission = Admission::where('projectcode', session('projectcode'))
                ->where('Flag', 1)
                ->whereBetween('created_at', [$from_date, $today])
                ->where('reciverrole', '!=', '0')
                ->count();

            $pending_profileadmission = Admission::where('projectcode', session('projectcode'))
                ->where('Flag', 2)
                ->whereBetween('created_at', [$from_date, $today])
                ->where('reciverrole', '!=', '0')
                ->count();

            $pending_loan = Loans::where('projectcode', session('projectcode'))
                ->whereBetween('time', [$from_date, $today])
                ->where('reciverrole', '!=', '0')
                ->count();

            $all_pending_loan = Loans::where('reciverrole', '!=', '0')
                ->where('status', '1')
                ->where('projectcode', session('projectcode'))
                ->whereBetween('time', [$from_date, $today])
                ->count();

            $all_pending_loan_data = DB::table('dcs.loans')
                ->where('reciverrole', '!=', '0')
                ->where('status', 1)
                ->where('projectcode', session('projectcode'))
                ->whereDate('loans.time', '>=', $from_date)
                ->whereDate('loans.time', '<=', $today)
                ->get();

            $all_approve_loan_data = DB::table('dcs.loans')
                ->where('reciverrole', '!=', '0')
                ->where('ErpStatus', 1)
                ->where('projectcode', session('projectcode'))
                ->whereDate('loans.time', '>=', $from_date)
                ->whereDate('loans.time', '<=', $today)
                ->get();

            $all_approve_loan = Loans::where('reciverrole', '!=', '0')
                ->where('ErpStatus', 1)
                ->where('projectcode', session('projectcode'))
                ->whereBetween('time', [$from_date, $today])
                ->count();

            $all_disbursement = Loans::where('reciverrole', '!=', '0')
                ->where('ErpStatus', 2)
                ->where('projectcode', session('projectcode'))
                ->whereBetween('time', [$from_date, $today])
                ->count();

            $all_disburse_loan = Loans::where('reciverrole', '!=', '0')
                ->where('ErpStatus', 4)
                ->where('projectcode', session('projectcode'))
                ->whereBetween('time', [$from_date, $today])
                ->count();

            $all_reject_loan = DB::table('dcs.loans')
                ->where('reciverrole', '!=', '0')
                ->where('status', 3)
                ->where('ErpStatus', 3)
                ->where('projectcode', session('projectcode'))
                ->whereDate('loans.time', '>=', $from_date)
                ->whereDate('loans.time', '<=', $today)
                ->count();


            $disburse_amt = Loans::where('projectcode', session('projectcode'))
                ->where('reciverrole', '!=', '0')
                ->whereBetween('time', [$from_date, $today])
                ->where('ErpStatus', 4)
                ->sum(DB::raw('CAST(propos_amt AS double precision)'));

            //rolewise data count start

            //pending
            $am_pending_loan = Loans::where('ErpStatus', null)
                ->where('reciverrole', '!=', '0')
                ->where('status', 1)
                ->where('projectcode', session('projectcode'))
                ->where('roleid', '2')
                ->whereBetween('time', [$from_date, $today])
                ->count();

            $bm_pending_loan = Loans::where('ErpStatus', null)
                ->where('reciverrole', '!=', '0')
                ->where('status', 1)
                ->where('projectcode', session('projectcode'))
                ->where('roleid', '1')
                ->whereBetween('time', [$from_date, $today])
                ->count();

            $rm_pending_loan = Loans::where('ErpStatus', null)
                ->where('reciverrole', '!=', '0')
                ->where('status', 1)
                ->where('projectcode', session('projectcode'))
                ->where('roleid', '3')
                ->whereBetween('time', [$from_date, $today])
                ->count();

            $dm_pending_loan = Loans::where('ErpStatus', null)
                ->where('reciverrole', '!=', '0')
                ->where('status', 1)
                ->where('projectcode', session('projectcode'))
                ->where('roleid', '4')
                ->whereBetween('time', [$from_date, $today])
                ->count();
            //dd($am_pending_loan,  $bm_pending_loan,  $rm_pending_loan, $dm_pending_loan);

            //approve

            $am_approve_loan = Loans::where('ErpStatus', '1')
                ->where('reciverrole', '!=', '0')
                ->where('status', 2)
                ->where('projectcode', session('projectcode'))
                ->where('roleid', '2')
                ->whereBetween('time', [$from_date, $today])
                ->count();

            $bm_approve_loan = Loans::where('ErpStatus', '1')
                ->where('reciverrole', '!=', '0')
                ->where('status', 2)
                ->where('projectcode', session('projectcode'))
                ->where('roleid', '1')
                ->whereBetween('time', [$from_date, $today])
                ->count();

            $rm_approve_loan = Loans::where('ErpStatus', '1')
                ->where('reciverrole', '!=', '0')
                ->where('status', 2)
                ->where('projectcode', session('projectcode'))
                ->where('roleid', '3')
                ->whereBetween('time', [$from_date, $today])
                ->count();

            $dm_approve_loan = Loans::where('ErpStatus', '1')
                ->where('reciverrole', '!=', '0')
                ->where('status', 2)
                ->where('projectcode', session('projectcode'))
                ->where('roleid', '4')
                ->whereBetween('time', [$from_date, $today])
                ->count();

            // dd($am_approve_loan,  $bm_approve_loan,  $rm_approve_loan, $dm_approve_loan);


            //disbursement

            $am_disbursement_loan = Loans::where('ErpStatus', '2')
                ->where('reciverrole', '!=', '0')
                ->where('status', 2)
                ->where('projectcode', session('projectcode'))
                ->where('roleid', '2')
                ->whereBetween('time', [$from_date, $today])
                ->count();

            $bm_disbursement_loan = Loans::where('ErpStatus', '2')
                ->where('reciverrole', '!=', '0')
                ->where('status', 2)
                ->where('projectcode', session('projectcode'))
                ->where('roleid', '1')
                ->whereBetween('time', [$from_date, $today])
                ->count();

            $rm_disbursement_loan = Loans::where('ErpStatus', '2')
                ->where('reciverrole', '!=', '0')
                ->where('status', 2)
                ->where('projectcode', session('projectcode'))
                ->where('roleid', '3')
                ->whereBetween('time', [$from_date, $today])
                ->count();

            $dm_disbursement_loan = Loans::where('ErpStatus', '2')
                ->where('reciverrole', '!=', '0')
                ->where('status', 2)
                ->where('projectcode', session('projectcode'))
                ->where('roleid', '4')
                ->whereBetween('time', [$from_date, $today])
                ->count();

            // // //disburse

            $am_disburse_loan = Loans::where('ErpStatus', '4')
                ->where('reciverrole', '!=', '0')
                ->where('status', 2)
                ->where('projectcode', session('projectcode'))
                ->where('roleid', '2')
                ->whereBetween('time', [$from_date, $today])
                ->count();

            $bm_disburse_loan = Loans::where('ErpStatus', '4')
                ->where('reciverrole', '!=', '0')
                ->where('status', 2)
                ->where('projectcode', session('projectcode'))
                ->where('roleid', '1')
                ->whereBetween('time', [$from_date, $today])
                ->count();

            $rm_disburse_loan = Loans::where('ErpStatus', '4')
                ->where('reciverrole', '!=', '0')
                ->where('status', 2)
                ->where('projectcode', session('projectcode'))
                ->where('roleid', '3')
                ->whereBetween('time', [$from_date, $today])
                ->count();

            $dm_disburse_loan = Loans::where('ErpStatus', '4')
                ->where('reciverrole', '!=', '0')
                ->where('status', 2)
                ->where('projectcode', session('projectcode'))
                ->where('roleid', '4')
                ->whereBetween('time', [$from_date, $today])
                ->count();

            // //     rejected

            $am_rejected_loan = Loans::where('ErpStatus', '3')
                ->where('reciverrole', '!=', '0')
                ->where('status', '3')
                ->where('projectcode', session('projectcode'))
                ->where('reciverrole', '2')
                ->whereBetween('time', [$from_date, $today])
                ->count();

            $bm_rejected_loan = Loans::where('ErpStatus', '3')
                ->where('reciverrole', '!=', '0')
                ->where('status', '3')
                ->where('projectcode', session('projectcode'))
                ->where('reciverrole', '1')
                ->whereBetween('time', [$from_date, $today])
                ->count();

            $rm_rejected_loan = Loans::where('ErpStatus', '3')
                ->where('reciverrole', '!=', '0')
                ->where('status', '3')
                ->where('projectcode', session('projectcode'))
                ->where('reciverrole', '3')
                ->whereBetween('time', [$from_date, $today])
                ->count();

            $dm_rejected_loan = Loans::where('ErpStatus', '3')
                ->where('reciverrole', '!=', '0')
                ->where('status', '3')
                ->where('projectcode', session('projectcode'))
                ->where('reciverrole', '4')
                ->whereBetween('time', [$from_date, $today])
                ->count();

            // rolewise Data count end


            $jsondata = array(
                "pendingloandata" =>  $all_pending_loan_data,
                "approveloandata" =>  $all_approve_loan_data,
                "pendingadminssioncount" => $pending_admission,
                "pendingprofileadmission" => $pending_profileadmission,
                "pendingloan" =>  $pending_loan,
                "allpendingloan" =>  $all_pending_loan,
                "allapproveloan" => $all_approve_loan,
                "all_disbursement" => $all_disbursement,
                "allrejectloan" => $all_reject_loan,
                "alldisburseloan" => $all_disburse_loan,
                "disburseamt" => $disburse_amt,
                "fromdate" => $from_date,
                "today" => $today

            );
        } else {
            $jsondata = [
                'pendingloandata' =>  0,
                'approveloandata' =>  0,
                'pendingadminssioncount' => 0,
                'pendingprofileadmission' => 0,
                'pendingloan' => 0,
                'allpendingloan' => 0,
                'allapproveloan' => 0,
                'all_disbursement' => 0,
                'allrejectloan' => 0,
                'alldisburseloan' => 0,
                'disburseamt' => 0,
            ];
        }

        return response()->json($jsondata);
    }
    public function getRollWiseData(Request $request)
    {
        $db = config('database.db');
        $role_designation = session('role_designation');
        //echo $role_designation;
        $request->session()->put('status_btn', '1');

        // Get current date
        $today = date('Y-m-d');
        $from_date = date('Y-01-01');
        $status = $request->input('roleStatus');
        $erpstatus = $request->input('erpStatus');
        $roleid = $request->input('roleid');

        if (!empty($roleid)) {
            $pendingData = Loans::where('reciverrole', '!=', '0')
                ->where('ErpStatus', $erpstatus)
                ->where('status', $status)
                ->where('projectcode', session('projectcode'))
                ->where('roleid', $roleid)
                ->whereBetween('time', [$from_date, $today])
                ->get();
        } else {
            $pendingData = Loans::where('ErpStatus', $erpstatus)
                ->where('projectcode', session('projectcode'))
                ->whereBetween('time', [$from_date, $today])
                ->get();
        }

        return $pendingData;
    }

    public function GetDivisionData(Request $request)
    {
        $programId = $request->get('program_id');
        $a = DB::Table('branch')
            ->select('division_id', 'division_name')
            ->where('program_id', $programId)
            ->distinct('division_id')->get();
        return $a;
    }
    public function GetRegionData(Request $request)
    {
        $divisionId = $request->get('division_id');
        $b = DB::Table('branch')
            ->select('region_id', 'region_name')
            ->where('division_id', $divisionId)
            ->distinct('region_id')->get();
        return $b;
    }
    public function GetAreaData(Request $request)
    {
        $regionId = $request->get('region_id');
        $c = DB::Table('branch')
            ->select('area_id', 'area_name')
            ->where('region_id', $regionId)
            ->distinct('area_id')->get();
        return $c;
    }
    public function GetBranchData(Request $request)
    {
        $areaId = $request->get('area_id');
        $d = DB::Table('branch')
            ->select('branch_id', 'branch_name')
            ->where('area_id', $areaId)
            ->distinct('branch_id')->get();
        return $d;
    }
    public function GetProgramOrganizerData(Request $request)
    {
        $BranchCode = $request->get('branchcode');
        $e = DB::Table('dcs.polist')
            ->select('cono', 'coname')
            ->where('branchcode', $BranchCode)->get();

        return $e;
    }
}
