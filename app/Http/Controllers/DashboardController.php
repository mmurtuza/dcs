<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Admission;
use App\Models\Loans;
use App\Models\Branch;

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
        $po = null;
        $status = $request->input('status');
        $erpstatus = $request->input('ErpStatus');
        $division = $request->input('division');
        $region = $request->input('region');
        $area = $request->input('area');
        $branch = $request->input('branch');
        $po = $request->input('po');
        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');

        if ($division != null and $region == null and $area == null and $branch == null and $po == null) {
            $getbranch = DB::Table('branch')
                ->select('branch_id')
                ->where('division_id', $division)
                ->where('program_id', 1)
                ->distinct('branch_id')
                ->get();
        }
        if ($division != null and $region != null and $area == null and $branch == null and $po == null) {
            $getbranch = DB::Table('branch')
                ->select('branch_id')
                ->where('division_id', $division)
                ->where('region_id', $region)
                ->where('program_id', 1)
                ->distinct('branch_id')
                ->get();
        }
        if ($division != null and $region != null and $area != null and $branch == null and $po == null) {
            $getbranch = DB::Table('branch')
                ->select('branch_id')
                ->where('division_id', $division)
                ->where('region_id', $region)
                ->where('area_id', $area)
                ->where('program_id', 1)
                ->distinct('branch_id')
                ->get();
        }
        if ($division != null and $region != null and $area != null and $branch != null and $po == null) {
            $getbranch = DB::Table('branch')
                ->select('branch_id')
                ->where('division_id', $division)
                ->where('region_id', $region)
                ->where('area_id', $area)
                ->where('branch_id', $branch)
                ->where('program_id', 1)
                ->distinct('branch_id')
                ->get();
        }
        if ($division != null and $region != null and $area != null and $branch != null and $po != null) {
            $getbranch = DB::Table('branch')
                ->select('branch_id')
                ->where('division_id', $division)
                ->where('region_id', $region)
                ->where('area_id', $area)
                ->where('branch_id', $branch)
                ->where('program_id', 1)
                ->distinct('branch_id')
                ->get();
        }


        $searchDataResult = $this->searchData($getbranch, $status, $erpstatus, $po);
        return response()->json($searchDataResult);
    }

    public function searchData($getbranch, $status, $erpstatus, $po)
    {
        $today = date('Y-m-d');
        $from_date = date('Y-01-01');
        $getbranchIds = $getbranch->pluck('branch_id')->toArray();

        if ($po != null) {
            $data = DB::table('dcs.loans')
                ->where('reciverrole', '!=', '0')
                ->whereIn('branchcode', $getbranchIds)
                ->where('status', $status)
                ->where('assignedpo', $po)
                ->where('ErpStatus', $erpstatus)
                ->where('projectcode', session('projectcode'))
                ->whereDate('loans.time', '>=', $from_date)
                ->whereDate('loans.time', '<=', $today)
                ->get();

            // dd($data);
            return $data;
        } else {
            $data = DB::table('dcs.loans')
                ->where('reciverrole', '!=', '0')
                ->whereIn('branchcode', $getbranchIds)
                ->where('status', $status)
                ->where('ErpStatus', $erpstatus)
                ->where('projectcode', session('projectcode'))
                ->whereDate('loans.time', '>=', $from_date)
                ->whereDate('loans.time', '<=', $today)
                ->get();
        }

        return $data;
    }




public function allCount(Request $request)
{
    $db = config('database.db');
    $role_designation = session('role_designation');
    $request->session()->put('status_btn', '1');
    // Get current date
    $today = date('Y-m-d');
    $from_date = date('Y-01-01');
    $showStartDate = date('d-M-Y', strtotime($from_date));
    $showEndDate = date('d-M-Y', strtotime($today));

    // Role wise data distribution
    $branch = null;
    $branchcodes = [];
    if ($role_designation == 'AM') 
    {
        $search = Branch::where(['area_id' => session('asid'),'program_id' => session('program_id')])->distinct('branch_id')->get();
    } 
    else if ($role_designation == 'RM') 
    {
        $search = Branch::where(['region_id' => session('asid'),'program_id' => session('program_id')])->distinct('area_id')->get();
    } 
    else if ($role_designation == 'DM') 
    {
        $search = Branch::where(['division_id' => session('asid'),'program_id' => session('program_id')])->distinct('region_id')->get();
    } 
    else if ($role_designation == 'HO' || $role_designation == 'PH') 
    {
        $search = DB::table('public.branch')->where('program_id', session('program_id'))->get();
    } 
    else 
    {
        return redirect()->back()->with('error', 'Data does not match.');
    }

    $branchcodes = $search->pluck('branch_id')->map(function ($branchId) 
    {
        return str_pad($branchId, 4, "0", STR_PAD_LEFT);
    })->toArray();

    if (!empty($branchcodes)) {
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

        $all_pending_loan_data = DB::table('dcs.loans')
            ->where('reciverrole', '!=', '0')
            ->where('status', 1)
            ->where('projectcode', session('projectcode'))
            ->whereDate('loans.time', '>=', $from_date)
            ->whereDate('loans.time', '<=', $today)
            ->get();

        $all_approve_loan_data = DB::table('dcs.loans')
            ->where('reciverrole', '!=', '0')
            ->where('status', 2)
            ->where('projectcode', session('projectcode'))
            ->whereDate('loans.time', '>=', $from_date)
            ->whereDate('loans.time', '<=', $today)
            ->get();

        $all_disbursed_loan_data = DB::table('dcs.loans')
            ->where('reciverrole', '!=', '0')
            ->where('status', 3)
            ->where('projectcode', session('projectcode'))
            ->whereDate('loans.time', '>=', $from_date)
            ->whereDate('loans.time', '<=', $today)
            ->get();

        $all_reject_loan_data = DB::table('dcs.loans')
            ->where('reciverrole', '!=', '0')
            ->where('status', 0)
            ->where('projectcode', session('projectcode'))
            ->whereDate('loans.time', '>=', $from_date)
            ->whereDate('loans.time', '<=', $today)
            ->get();

        $total_disbursed_amount = Loans::where('projectcode', session('projectcode'))
                ->where('reciverrole', '!=', '0')
                ->whereBetween('time', [$from_date, $today])
                ->where('ErpStatus', 4)
                ->sum(DB::raw('CAST(propos_amt AS double precision)'));

        // Role wise data counts
        $roleWiseCounts = [];
        $roles = ['1', '2', '3', '4'];
        foreach ($roles as $role) {
            $roleCounts = DB::table('dcs.loans')
                ->select('status', DB::raw('count(*) as count'))
                ->where('reciverrole', $role)
                ->where('projectcode', session('projectcode'))
                ->whereDate('loans.time', '>=', $from_date)
                ->whereDate('loans.time', '<=', $today)
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $roleCounts = Loans::where('ErpStatus', null)
                ->where('reciverrole', '!=', '0')
                ->where('status', 1)
                ->where('projectcode', session('projectcode'))
                ->where('roleid', $role)
                ->whereBetween('time', [$from_date, $today])
                ->count();

            $roleWiseCounts[$role] = [
                'pending' => $roleCounts[1] ?? 0,
                'approved' => $roleCounts[2] ?? 0,
                'disbursed' => $roleCounts[3] ?? 0,
                'rejected' => $roleCounts[0] ?? 0,
            ];
        }
//all_pending_loan_data  all_approve_loan_data
        $jsondata = [
            'pendingadminssioncount' => $pending_admission,
            'pendingprofileadmission' => $pending_profileadmission,
            'pendingloan' => $pending_loan,
            'allpendingloan' => $all_pending_loan,
            'allapproveloan' => $all_approve_loan,
            'all_disbursement' => $all_disbursement,
            'allrejectloan' => $all_reject_loan,
            'alldisburseloan' => $all_disburse_loan,
            'pendingloandata' => $all_pending_loan_data,
            'approveloandata' => $all_approve_loan_data,
            'alldisburseloandata' => $all_disbursed_loan_data,
            'allrejectloandata' => $all_reject_loan_data,
            'disburseamt' => $total_disbursed_amount,
            'fromdate' => $from_date,
            'today' => $today
            //'roleWiseCounts' => $roleWiseCounts,
        ];

        return response()->json($jsondata);
    }

    return response()->json([]);
}

    public function getRollWiseData(Request $request)
    {

        $role_designation = session('role_designation');
        $request->session()->put('status_btn', '1');

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
