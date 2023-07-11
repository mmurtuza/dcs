<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Admission;
use App\Models\Loans;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

ini_set('memory_limit', '3072M');
ini_set('max_execution_time', 1800);

class DashboardController extends Controller
{
    public $db = 'dcs';
    public function index(Request $request)
    {
        $db = config('database.db');
        $role_designation = session('role_designation');
        $request->session()->put('status_btn', '1');

        return view('Dashboard')->with('role_designation', $role_designation);
    }

    public function fetchData(Request $request): JsonResponse
    {
        $today = date('Y-m-d');
        $role_designation = session('role_designation');
        $from_date = date('Y-01-01');
        $status = $request->get('status') ?? null;
        $erpstatus = $request->get('ErpStatus') ?? null;
        $getbranch =$request->input('division') ? ($this->getBranch($request->input('division') ?? null, $request->input('region') ?? null, $request->input('area'), $request->input('branch') ?? null)->pluck('branch_id')->toArray() ?? null) : null;
        $branch = null;
        
        if($role_designation === "AM"){
            $branch = Branch::where('area_id', session('asid'))
            ->where('program_id', session('program_id'))
            ->first();
        } else if($role_designation === "RM"){
            $branch = Branch::where('region_id', session('asid'))
            ->where('program_id', session('program_id'))
            ->first();
        } else if($role_designation === "DM"){
            $branch = Branch::where('division_id', session('asid'))
            ->where('program_id', session('program_id'))
            ->first();
        }

        $data = Loans::select('loans.id', 'loans.*', 'product_project_member_category.productname', 'polist.coname')
            ->distinct('loans.id')
            ->leftJoin('dcs.product_project_member_category', function ($join) {
                $join->on(DB::raw('CAST(loans.loan_product AS INT)'), '=', 'product_project_member_category.productid');
            })
            ->leftJoin('dcs.polist', function ($join) {
                $join->on('loans.assignedpo', '=', 'polist.cono');
            })
            ->where('loans.reciverrole', '!=', '0')
            ->where('loans.projectcode', session('projectcode'))
            ->whereDate('loans.time', '>=', $from_date)
            ->whereDate('loans.time', '<=', $today)
            ->when(!empty($request->po), function ($query) use ($request) {
                $query->where('assignedpo', $request->po);
            })
            ->when(empty($request->po) && !empty($request->input('division')), function ($query) use ($getbranch) {
                $query->whereIn('loans.branchcode', $getbranch);
            })
            ->when($erpstatus != null && $status == null, function ($query) use ($erpstatus) {
                $query->where('ErpStatus', $erpstatus);
            })
            ->when($status != null && $erpstatus != null, function ($query) {
                $query->where(function ($query) {
                    $query->where('loans.status', 3)
                        ->orWhere('loans.ErpStatus', 3);
                });
            })
            ->when($status != null && $erpstatus == null, function ($query) use ($status) {
                $query->where('loans.status', $status);
            })
            ->groupBy('loans.id', 'product_project_member_category.productname', 'polist.id')
            ->get();

        // $data = $query->get();

        $counts = $this->allCount($request, $getbranch, $status, $erpstatus, $request->po);

        return response()->json(["data"=> $data, 'counts'=> $counts, 'branch' => $branch]);
    }

    public function search(Request $request): JsonResponse
    {
        $status = $request->input('status') ?? null;
        $erpstatus = $request->input('ErpStatus') ?? null;
        $division = $request->input('division') ?? null;
        $region = $request->input('region') ?? null;
        $area = $request->input('area') ?? null;
        $branch = $request->input('branch') ?? null;
        $po = $request->input('po') ?? null;
        $getbranch = null;
        if(!empty($division))
            $getbranch = $this->getBranch($division, $region, $area, $branch)->pluck('branch_id')->toArray();

        $searchDataResult = $this->searchData($getbranch, $status, $erpstatus, $po);
        $counts = $this->allCount($request, $getbranch, $status, $erpstatus, $po);
        return response()->json([
            'searchDataResult'=>$searchDataResult,
            'counts'=>$counts
        ]);
    }

    public function searchData($getbranch, $status, $erpstatus, $po): JsonResponse
    {

        $today = date('Y-m-d');
        $from_date = date('Y-01-01');

        if ($po != null) {
            $data = Loans::select('loans.*', 'product_project_member_category.productname', 'polist.coname')
                ->where('loans.reciverrole', '!=', '0')
                ->where('loans.status', $status)
                ->where('loans.assignedpo', $po)
                ->where('loans.ErpStatus', $erpstatus)
                ->where('loans.projectcode', session('projectcode'))
                ->whereDate('loans.time', '>=', $from_date)
                ->whereDate('loans.time', '<=', $today)
                ->leftJoin('dcs.product_project_member_category', function ($join) {
                    $join->on(DB::raw('CAST(loans.loan_product AS INT)'), '=', 'product_project_member_category.productid');
                })
                ->leftJoin('dcs.polist', function ($join) {
                    $join->on('loans.assignedpo', '=', 'polist.cono');
                })
                ->groupBy('loans.id','product_project_member_category.productname', 'polist.id')
                ->get();

            return response()->json($data);
        } else {
            $data = Loans::select('loans.*', 'product_project_member_category.productname', 'polist.coname')
                ->where('loans.reciverrole', '!=', '0')
                ->when(!empty($getbranch), function ($query) use ($getbranch) {
                    return $query->whereIn('loans.branchcode', $getbranch);
                })
                ->where('loans.status', $status)
                ->where('loans.ErpStatus', $erpstatus)
                ->where('loans.projectcode', session('projectcode'))
                ->whereDate('loans.time', '>=', $from_date)
                ->whereDate('loans.time', '<=', $today)
                ->leftJoin('dcs.product_project_member_category', function ($join) {
                    $join->on(DB::raw('CAST(loans.loan_product AS INT)'), '=', 'product_project_member_category.productid');
                })
                ->leftJoin('dcs.polist', function ($join) {
                    $join->on('loans.assignedpo', '=', 'polist.cono');
                })
                ->groupBy('loans.id','product_project_member_category.productname', 'polist.id')
                ->get();
        }

        return response()->json($data);
    }

public function allCount(Request $request, $getbranch=null, $status=null, $erpstatus =null, $po =null)
{
    $db = config('database.db');
    $role_designation = session('role_designation');
    $request->session()->put('status_btn', '1');
    // Get current date
    $today = date('Y-m-d');
    $from_date = date('Y-01-01');

    // Role wise data distribution
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
        $search = Branch::where('program_id', session('program_id'))->get();
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
            ->whereIn('branchcode', $branchcodes)
            ->where('Flag', 1)
            ->whereBetween('created_at', [$from_date, $today])
            ->where('reciverrole', '!=', '0')
            ->count();

        $pending_profileadmission = Admission::where('projectcode', session('projectcode'))
            ->where('Flag', 2)
            ->whereBetween('created_at', [$from_date, $today])
            ->where('reciverrole', '!=', '0')
            ->count();

        $pending_loan_query = Loans::where('projectcode', session('projectcode'))
            ->whereBetween('time', [$from_date, $today])
            ->where('reciverrole', '!=', '0');
        if(empty($po) && !empty($getbranch)){
            $pending_loan_query
                ->whereIn('branchcode', $getbranch)
                ->where('status', $status)
                ->where('ErpStatus', $erpstatus);
        }

        $pending_loan = $pending_loan_query->count();

        $all_pending_loan = Loans::where('reciverrole', '!=', '0')
            ->where('status', '1')
            ->where('projectcode', session('projectcode'))
            ->whereBetween('time', [$from_date, $today])
            ->when(!empty($po), function ($query) use ($po) {
                return $query->where('assignedpo', $po);
            })
            ->when(empty($po) && !empty($getbranch), function ($query) use ($getbranch){
                return $query->whereIn('branchcode', $getbranch);
            })
            ->count();

        $all_approve_loan = Loans::where('reciverrole', '!=', '0')
                ->where('projectcode', session('projectcode'))
                ->where('status','2')
                ->whereBetween('time', [$from_date, $today])
                ->when(!empty($po), function ($query) use ($po) {
                    return $query->where('assignedpo', $po);
                })
                ->when(empty($po) && !empty($getbranch), function ($query) use ($getbranch){
                    return $query->whereIn('branchcode', $getbranch);
                })
                ->count();

        $all_disbursement_loan = Loans::where('reciverrole', '!=', '0')
                ->where('ErpStatus', 1)
                ->where('projectcode', session('projectcode'))
                ->whereBetween('time', [$from_date, $today])
                ->when(!empty($po), function ($query) use ($po) {
                    return $query->where('assignedpo', $po);
                })
                ->when(empty($po) && !empty($getbranch), function ($query) use ($getbranch){
                    return $query->whereIn('branchcode', $getbranch);
                })
                ->count();

            $all_disburse_loan = Loans::where('reciverrole', '!=', '0')
                ->where('ErpStatus', 4)
                ->where('projectcode', session('projectcode'))
                ->whereBetween('time', [$from_date, $today])
                ->when(!empty($po), function ($query) use ($po) {
                    return $query->where('assignedpo', $po);
                })
                ->when(empty($po) && !empty($getbranch), function ($query) use ($getbranch) {
                    return $query->whereIn('branchcode', $getbranch);
                })
                ->count();

        $all_reject_loan = Loans::where('reciverrole', '!=', '0')
                ->where('projectcode', session('projectcode'))
                ->whereDate('loans.time', '>=', $from_date)
                ->whereDate('loans.time', '<=', $today)
                ->where(function ($query) {
                    $query->where('status', 3)
                        ->orWhere('ErpStatus', 3);
                })
                ->when(!empty($po), function ($query) use ($po) {
                    return $query->where('assignedpo', $po);
                })
                ->when(empty($po) && !empty($getbranch), function ($query) use ($getbranch){
                    return $query->whereIn('branchcode', $getbranch);
                })
                ->count();

        $total_disbursed_amount = Loans::where('projectcode', session('projectcode'))
                ->where('reciverrole', '!=', '0')
                ->whereBetween('time', [$from_date, $today])
                ->where('ErpStatus', 4)
                ->when(!empty($po), function ($query) use ($po) {
                    return $query->where('assignedpo', $po);
                })
                ->when(empty($po) && !empty($getbranch), function ($query) use ($getbranch){
                    return $query->whereIn('branchcode', $getbranch);
                })
                ->when(!empty($status), function($query) use($status){
                    return $query->where('status', $status);
                })
                ->count();


        //all_pending_loan_data  all_approve_loan_data
        $jsondata = [
            'role_designation' => $role_designation,
            'pendingadminssioncount' => $pending_admission,
            'pendingprofileadmission' => $pending_profileadmission,
            'pendingloan' => $pending_loan,
            'allpendingloan' => $all_pending_loan,
            'allapproveloan' => $all_approve_loan,
            'all_disbursement' => $all_disbursement_loan,
            'allrejectloan' => $all_reject_loan,
            'alldisburseloan' => $all_disburse_loan,
            'disburseamt' => $total_disbursed_amount,
            'fromdate' => $from_date,
            'today' => $today
        ];

        return $jsondata;
    }

    // return response()->json([]);
    return [];
}
    public function getRollWiseCounts(Request $request)
    {
        $today = date('Y-m-d');
        $from_date = date('Y-01-01');
        $projectcode = session('projectcode');
        $counts = [];
        $erpstatus = $request->input('erpStatus');
        $status = $request->input('roleStatus');
        $po = $request->input('po') ?? null;
        $getbranch = $this->getBranch($request->input('division') ?? null, $request->input('region') ?? null, $request->input('area'), $request->input('branch') ?? null);
        $branchId = $getbranch->pluck('branch_id')->toArray() ?? null;

        // Pending Loans
        $am_query = Loans::where('reciverrole', '2')
        ->where('projectcode', $projectcode)
        ->whereBetween('time', [$from_date, $today])
        ->when(!empty($po), function ($query) use ($po) {
            return $query->where('assignedpo', $po);
        });

        if(!empty($request->input('division'))){
            if(!empty($branchId)){
                $am_query->whereIn('branchcode', $branchId);
            }
        }

        if($erpstatus !=null && $status == null) $am_query->where('ErpStatus', $erpstatus);

        if($status !=null && $erpstatus !=null)
        {
            $am_query->where(function($am_query){
                $am_query->where('status', 3)
                    ->orWhere('ErpStatus', 3);
            });
        }elseif($status !=null && $erpstatus ==null){
            $am_query->where('status', $status);
        }

        $counts['am_pending_loan'] = $am_query->count();
        unset($am_query);

        $bm_query = Loans::where('reciverrole', '1')
            ->where('projectcode', $projectcode)
            ->whereBetween('time', [$from_date, $today])
            ->when(!empty($po), function ($query) use ($po) {
                return $query->where('assignedpo', $po);
            });

        if(!empty($request->input('division'))){
            if(!empty($branchId)){
                $bm_query->whereIn('branchcode', $branchId);
            }
        }

        if($erpstatus !=null && $status == null) $bm_query->where('ErpStatus', $erpstatus);

        if($status !=null && $erpstatus !=null)
        {
            $bm_query->where(function($bm_query){
                $bm_query->where('status', 3)
                    ->orWhere('ErpStatus', 3);
            });
        }elseif($status !=null && $erpstatus ==null){
            $bm_query->where('status', $status);
        }

        $counts['bm_pending_loan'] = $bm_query->count();
        unset($bm_query);

        $rm_query = Loans::where('reciverrole', '3')
            ->where('projectcode', $projectcode)
            ->whereBetween('time', [$from_date, $today])
            ->when(!empty($po), function ($query) use ($po) {
                return $query->where('assignedpo', $po);
            });

        if(!empty($request->input('division'))){
            if(!empty($branchId)){
                $rm_query->whereIn('branchcode', $branchId);
            }
        }

        if($erpstatus !=null && $status == null) $rm_query->where('ErpStatus', $erpstatus);

        if($status !=null && $erpstatus !=null)
        {
            $rm_query->where(function($rm_query){
                $rm_query->where('status', 3)
                    ->orWhere('ErpStatus', 3);
            });

        }elseif($status !=null && $erpstatus ==null){
            $rm_query->where('status', $status);
        }
        $counts['rm_pending_loan'] = $rm_query->count();
        unset($rm_query);

        $dm_query = Loans::where('reciverrole', '4')
            ->where('projectcode', $projectcode)
            ->whereBetween('time', [$from_date, $today])
            ->when(!empty($po), function ($query) use ($po) {
                return $query->where('assignedpo', $po);
            });

        if(!empty($request->input('division'))){
            if(!empty($branchId)){
                $dm_query->whereIn('branchcode', $branchId);
            }
        }

        if($erpstatus !=null && $status == null) $dm_query->where('ErpStatus', $erpstatus);

        if($status !=null && $erpstatus !=null)
        {
            $dm_query->where(function($dm_query){
                $dm_query->where('status', 3)
                    ->orWhere('ErpStatus', 3);
            });
        }elseif($status !=null && $erpstatus ==null){
            $dm_query->where('status', $status);
        }
        $counts['dm_pending_loan'] = $dm_query->count();
        unset($dm_query);

        return $counts;
    }
    public function getRollWiseData(Request $request)
    {
        $today = date('Y-m-d');
        $from_date = date('Y-01-01');
        $status = $request->input('roleStatus') ?? null;
        $po = $request->get('po') ?? null;
        $erpstatus = $request->input('erpStatus')?? null;
        $reciverrole = $request->input('reciverrole');
        $getbranch = null;

        $query =Loans::select('loans.id','loans.*', 'product_project_member_category.productname','polist.coname')
            ->distinct('loans.id')
            ->where('loans.reciverrole', $reciverrole)
            ->where('loans.projectcode', session('projectcode'))
            ->whereDate('loans.time', '>=', $from_date)
            ->whereDate('loans.time', '<=', $today)
            ->when(!empty($po), function ($query) use ($po) {
                return $query->where('assignedpo', $po);
            });

        if(!empty($request->input('division'))){
            $getbranch = $this->getBranch($request->input('division') ?? null, $request->input('region') ?? null, $request->input('area'), $request->input('branch') ?? null);
            $branchId = $getbranch->pluck('branch_id')->toArray() ?? null;
            if(!empty($branchId)){
                $query->whereIn('loans.branchcode', $branchId);
            }
        }

        if($erpstatus !=null && $status == null) $query->where('ErpStatus', $erpstatus);

        if($status !=null && $erpstatus !=null)
        {
            $query->where(function($query){
                $query->where('loans.status', 3)
                    ->orWhere('loans.ErpStatus', 3);
            });
        }elseif($status !=null && $erpstatus ==null){
            $query->where('loans.status', $status);
        }

        $data = $query->leftJoin('dcs.product_project_member_category', function ($join) {
            $join->on(DB::raw('CAST(loans.loan_product AS INT)'), '=', 'product_project_member_category.productid');
        })
        ->leftJoin('dcs.polist', function ($join) {
            $join->on('loans.assignedpo', '=', 'polist.cono');
        })
        ->groupBy('loans.id','product_project_member_category.productname', 'polist.id')
        ->get();


        return response()->json($data);
    }

    public function GetDivisionData(Request $request)
    {
        $programId = $request->get('program_id');
        $division_list = DB::Table('branch')
            ->select('division_id', 'division_name')
            ->where('program_id', $programId)
            ->distinct('division_id')->get();
        return $division_list;
    }
    public function GetRegionData(Request $request)
    {
        $divisionId = $request->get('division_id');
        $region_list = DB::Table('branch')
            ->select('region_id', 'region_name')
            ->where('division_id', $divisionId)
            ->distinct('region_id')->get();
        return $region_list;
    }
    public function GetAreaData(Request $request)
    {
        $regionId = $request->get('region_id');
        $area_list = DB::Table('branch')
            ->select('area_id', 'area_name')
            ->where('region_id', $regionId)
            ->distinct('area_id')->get();
        return $area_list;
    }
    public function GetBranchData(Request $request)
    {
        $areaId = $request->get('area_id');
        $branch_list = DB::Table('branch')
            ->select('branch_id', 'branch_name')
            ->where('area_id', $areaId)
            ->distinct('branch_id')->get();
        return $branch_list;
    }
    public function GetProgramOrganizerData(Request $request)
    {
        $BranchCode = $request->get('branchcode');
        $pos_list = DB::Table('dcs.polist')
            ->select('cono', 'coname')
            ->where('branchcode', $BranchCode)->get();

        return $pos_list;
    }

    public static function getBranch($division = null, $region = null, $area = null, $branch = null){
        return Branch::select('branch_id')
                ->where('program_id', 1)
                ->distinct('branch_id')
                ->when(!empty($division), function($query) use ($division){
                    return $query->where('division_id', $division);
                })
                ->when(!empty($region), function($query) use ($region){
                    return $query->where('region_id', $region);
                })
                ->when(!empty($area), function($query) use ($area){
                    return $query->where('area_id', $area);
                })
                ->when(!empty($branch), function($query) use ($branch){
                    return $query->where('branch_id', $branch);
                })
                ->get();

    }
}
