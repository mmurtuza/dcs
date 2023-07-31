<?php

namespace App\Http\Controllers;

use App\Models\Loans;
use App\Models\Branch;
use App\Models\Admission;
use App\Models\Polist;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;

ini_set('memory_limit', '3072M');
ini_set('max_execution_time', 1800);

class DashboardController extends Controller
{
    /**
     * index
     *
     * @param  Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request): View | RedirectResponse
    {
        $role_designation = session('role_designation');
        $request->session()->put('status_btn', '1');
        if(empty(Branch::getBranchForRole()))
        {
            return redirect('https://trendx.brac.net/');
        }

        return view('Dashboard')->with('role_designation', $role_designation);
    }

    /**
     * fetchData
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function fetchData(Request $request): JsonResponse
    {
        $role_designation = session('role_designation');
        $status = $request->get('status') ?? null;
        $erpstatus = $request->get('ErpStatus') ?? null;

        $getbranch = Branch::getBranch($request->input('division') , $request->input('region') , $request->input('area'), $request->input('branch'))->pluck('branch_id')->toArray() ?? [];
        $branch = Branch::getBranchForRole();
        $polist = Loans::select('assignedpo')->whereIn("branchcode", $getbranch)->distinct("assignedpo")->get()->pluck("assignedpo")->toArray();
        // $polist = Polist::select('cono')->whereIn('branchcode', $getbranch)->get()->pluck('cono')->toArray();

        $data = Loans::fetchLoans($request, $polist);

        $counts = $this->allCount($request, $polist, $status, $erpstatus, $request->po);

        return response()->json(["data"=> $data, 'counts'=> $counts, 'branch' => $branch]);
    }

    /**
     * search
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $status = $request->input('status') ?? null;
        $erpstatus = $request->input('ErpStatus') ?? null;
        $division = $request->input('division') ?? null;
        $region = $request->input('region') ?? null;
        $area = $request->input('area') ?? null;
        $branch = $request->input('branch') ?? null;
        $po = $request->input('po') ?? null;
        $getbranch = Branch::getBranch($division, $region, $area, $branch)->pluck('branch_id')->toArray();

        $polist = Loans::select('assignedpo')->whereIn("branchcode", $getbranch)->distinct("assignedpo")->get()->pluck("assignedpo")->toArray();


        $searchResult = Loans::fetchLoans($request, $polist);
        $counts = $this->allCount($request, $polist, $status, $erpstatus, $po);
        return response()->json([ 'searchResult'=>$searchResult, 'counts'=>$counts ]);
        // return datatables()->eloquent(Loans::fetchLoans($request, $polist))->toJson();
    }

    /**
     * allCount
     *
     * @param  Request $request
     * @param  array $polist
     * @param  mixed $status
     * @param  mixed $erpstatus
     * @param  mixed $po
     * @return array
     */
    public function allCount(Request $request, array $polist = [], $status=null, $erpstatus =null, $po =null): array
    {

        $role_designation = session('role_designation');
        $request->session()->put('status_btn', '1');
        // Get current date
        $to_date = $request->input('dataTo') ?: date('Y-m-d');
        $from_date =$request->input('dateFrom') ?: date('Y-m-01');
        $roleData = ($role_designation === 'AM' || $role_designation === 'RM' || $role_designation === 'DM') ?: false;

        // No of Admission
        $pending_admission = Admission::where('projectcode', session('projectcode'))
            ->where('Flag', 1)
            ->where('reciverrole', '!=', '0')
            ->whereBetween('created_at', [$from_date, $to_date])
            ->when( $roleData && !empty($polist), function ($q) use ($polist) {
                return $q->whereIn('assignedpo', $polist);
            })
            ->count();

        // No of Profile Update
        $pending_profileadmission = Admission::where('projectcode', session('projectcode'))
            ->where('Flag', 2)
            ->whereBetween('created_at', [$from_date, $to_date])
            ->where('reciverrole', '!=', '0')
            ->when( $roleData && !empty($polist), function ($q) use ($polist) {
                return $q->whereIn('assignedpo', $polist);
            })
            ->count();

        // No of Loan Application
        $pending_loan = Loans::where('projectcode', session('projectcode'))
            ->where('reciverrole',  '!=', '0')
            ->whereBetween('time', [$from_date, $to_date])
            ->when( $roleData && !empty($polist), function ($q) use ($polist) {
                return $q->whereIn('assignedpo', $polist);
            })
            //brandcode
            ->count();

        // Total Disbursed Amount
        $disbursment_amnt = Loans::where('projectcode', session('projectcode'))
            ->where('reciverrole', '!=', '0')
            ->where('ErpStatus', 4)
            ->whereBetween('time', [$from_date, $to_date])
            ->when($roleData && !empty($polist), function ($query) use ($polist){
                return $query->whereIn('loans.assignedpo', $polist);
            })
            ->sum(DB::raw('CAST(propos_amt AS double precision)'));

        // Pending for Approval
        $all_pending_loan = Loans::where('reciverrole', '!=', '0')
            ->where('status', '1')
            ->where('projectcode', session('projectcode'))
            ->whereBetween('time', [$from_date, $to_date])
            ->when((empty($po) && (!empty($request->input('division')) || $roleData) && !empty($polist)) && !empty($polist), function ($query) use ($polist){
                return $query->whereIn('loans.assignedpo', $polist);
            })
            ->count();

        $all_approve_loan = Loans::where('reciverrole', '!=', '0')
                ->where('projectcode', session('projectcode'))
                ->where('ErpStatus', 1)
                ->whereBetween('time', [$from_date, $to_date])
                ->when((empty($po) && (!empty($request->input('division')) || $roleData) && !empty($polist)), function ($query) use ($polist){
                    return $query->whereIn('loans.assignedpo', $polist);
                })
                ->count();

        $all_disbursement_loan = Loans::where('reciverrole', '!=', '0')
                ->where('ErpStatus', 2)
                ->where('projectcode', session('projectcode'))
                ->whereBetween('time', [$from_date, $to_date])
                ->when(!empty($po), function ($query) use ($po) {
                    return $query->where('assignedpo', $po);
                })
                ->when((empty($po) && (!empty($request->input('division')) || $roleData) && !empty($polist)), function ($query) use ($polist){
                    return $query->whereIn('loans.assignedpo', $polist);
                })
                ->count();

        $all_disburse_loan = Loans::where('reciverrole', '!=', '0')
                ->where('ErpStatus', 4)
                ->where('projectcode', session('projectcode'))
                ->whereBetween('time', [$from_date, $to_date])
                ->when(!empty($po), function ($query) use ($po) {
                    return $query->where('assignedpo', $po);
                })
                ->when((empty($po) && (!empty($request->input('division')) || $roleData) && !empty($polist)), function ($query) use ($polist){
                    return $query->whereIn('loans.assignedpo', $polist);
                })
                ->count();

        $all_reject_loan = Loans::where('reciverrole', '!=', '0')
                ->where('projectcode', session('projectcode'))
                ->whereDate('loans.time', '>=', $from_date)
                ->whereDate('loans.time', '<=', $to_date)
                ->where(function ($query) {
                    $query->where('status', 3)
                        ->orWhere('ErpStatus', 3);
                })
                ->when(!empty($po), function ($query) use ($po) {
                    return $query->where('assignedpo', $po);
                })
                ->when((empty($po) && (!empty($request->input('division')) || $roleData) && !empty($polist)), function ($query) use ($polist){
                    return $query->whereIn('loans.assignedpo', $polist);
                })
                ->count();

        $total_disbursed_amount = Loans::where('projectcode', session('projectcode'))
                ->where('reciverrole', '!=', '0')
                ->whereBetween('time', [$from_date, $to_date])
                ->where('ErpStatus', 4)
                ->when(!empty($po), function ($query) use ($po) {
                    return $query->where('assignedpo', $po);
                })
                ->when(!empty($status), function($query) use($status){
                    return $query->where('status', $status);
                })
                ->when((empty($po) && (!empty($request->input('division')) || $roleData) && !empty($polist)), function ($query) use ($polist){
                    return $query->whereIn('loans.assignedpo', $polist);
                })
                ->count();

        //all_pending_loan_data  all_approve_loan_data
        return  [
            'role_designation' => $role_designation,
            'pendingadminssioncount' => $pending_admission,
            'pendingprofileadmission' => $pending_profileadmission,
            'pendingloan' => $pending_loan,
            'disbursment_amnt' => $disbursment_amnt,
            'allpendingloan' => $all_pending_loan,
            'allapproveloan' => $all_approve_loan,
            'all_disbursement' => $all_disbursement_loan,
            'allrejectloan' => $all_reject_loan,
            'alldisburseloan' => $all_disburse_loan,
            'disburseamt' => $total_disbursed_amount,
            'fromdate' => $from_date,
            'today' => $to_date
        ];
    }
    /**
     * getRollWiseCounts
     *
     * @param  Request $request
     * @return array
     */
    public function getRollWiseCounts(Request $request):array
    {
        $to_date = $request->input('dataTo') ?: date('Y-m-d');
        $from_date =$request->input('dateFrom') ?: date('Y-m-01');
        $projectcode = session('projectcode');
        $counts = [];
        $po = $request->input('po') ?? null;
        $getbranch = Branch::getBranch($request->input('division') ?? null, $request->input('region') ?? null, $request->input('area'), $request->input('branch') ?? null);
        $polist = Loans::select('assignedpo')->whereIn("branchcode", $getbranch)->distinct("assignedpo")->get()->pluck("assignedpo")->toArray();

        // Pending Loans
        $counts['am_pending_loan'] = Loans::where('reciverrole', '2')
        ->where('projectcode', $projectcode)
        ->whereBetween('time', [$from_date, $to_date])
        ->when(!empty($po), function ($query) use ($po) {
            return $query->where('assignedpo', $po);
        })
        ->where(function ($q) use ($request, $polist) {
            return self::searchFilter($request, $q, $polist);
        })
        ->count();

        $counts['bm_pending_loan'] = Loans::where('reciverrole', '1')
            ->where('projectcode', $projectcode)
            ->when(!empty($po), function ($query) use ($po) {
                return $query->where('assignedpo', $po);
            })
            ->where(function ($q) use ($request, $polist) {
                return self::searchFilter($request, $q, $polist);
            })
            ->count();

        $counts['rm_pending_loan'] = Loans::where('reciverrole', '3')
            ->where('projectcode', $projectcode)
            ->whereBetween('time', [$from_date, $to_date])
            ->when(!empty($po), function ($query) use ($po) {
                return $query->where('assignedpo', $po);
            })
            ->where(function ($q) use ($request, $polist) {
                return self::searchFilter($request, $q, $polist);
            })
            ->count();

        $counts['dm_pending_loan'] = Loans::where('reciverrole', '4')
            ->where('projectcode', $projectcode)
            ->whereBetween('time', [$from_date, $to_date])
            ->when(!empty($po), function ($query) use ($po) {
                return $query->where('assignedpo', $po);
            })
            ->where(function ($q) use ($request, $polist) {
                return self::searchFilter($request, $q, $polist);
            })
            ->count();

        return $counts;
    }
    /**
     * getRollWiseData
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function getRollWiseData(Request $request): JsonResponse
    {

        $reciverrole = $request->input('reciverrole');
        $getbranch = Branch::getBranch($request->input('division') ?? null, $request->input('region') ?? null, $request->input('area'), $request->input('branch') ?? null);
        $polist = Loans::select('assignedpo')->whereIn("branchcode", $getbranch)->distinct("assignedpo")->get()->pluck("assignedpo")->toArray();

        return response()->json(Loans::fetchLoans($request, $polist));
    }

    /**
     * GetDivisionData
     *
     * @param  Request $request
     * @return Collection
     */
    public function GetDivisionData(Request $request): Collection
    {
        $programId = $request->get('program_id');
        $division_list = Branch::select('division_id', 'division_name')
            ->where('program_id', $programId)
            ->distinct('division_id')->get();
        return $division_list;
    }
    /**
     * GetRegionData
     *
     * @param  Request $request
     * @return Collection
     */
    public function GetRegionData(Request $request): Collection
    {
        $divisionId = $request->get('division_id');
        $region_list = Branch::select('region_id', 'region_name')
            ->where('division_id', $divisionId)
            ->distinct('region_id')->get();
        return $region_list;
    }
    /**
     * GetAreaData
     *
     * @param  Request $request
     * @return Collection
     */
    public function GetAreaData(Request $request): Collection
    {
        $regionId = $request->get('region_id');
        $area_list = Branch::select('area_id', 'area_name')
            ->where('region_id', $regionId)
            ->distinct('area_id')->get();
        return $area_list;
    }
    /**
     * GetBranchData
     *
     * @param  Request $request
     * @return Collection
     */
    public function GetBranchData(Request $request): Collection
    {
        $areaId = $request->get('area_id');
        $branch_list = Branch::select('branch_id', 'branch_name')
            ->where('area_id', $areaId)
            ->distinct('branch_id')->get();
        return $branch_list;
    }
    /**
     * GetProgramOrganizerData
     *
     * @param  Request $request
     * @return Collection
     */
    public function GetProgramOrganizerData(Request $request): Collection
    {
        $BranchCode = $request->get('branchcode');
        $pos_list = Polist::select('cono', 'coname')
            ->where('branchcode', $BranchCode)->get();

        return $pos_list;
    }

    /**
     * searchFilter
     *
     * @param  Request $request
     * @param  mixed $query
     * @param  array $polist
     * @return mixed returtns a DB query
     */
    public static function searchFilter(Request $request, $query,  array $polist): mixed
    {
        $to_date = $request->input('dataTo') ?: date('Y-m-d');
        $from_date =$request->input('dateFrom') ?: date('Y-m-01');
        $role_designation = session('role_designation');
        $roleData = ($role_designation === 'AM' || $role_designation === 'RM' || $role_designation === 'DM') ?: false;

        return $query->whereDate('loans.time', '>=', $from_date)
            ->whereDate('loans.time', '<=', $to_date)
            ->when($request->input('erpStatus') != null && $request->input('roleStatus') == null, function ($query) use ($request) {
                return $query->where('ErpStatus', $request->input('erpStatus'));
            })
            ->when($request->input('roleStatus') != null && $request->input('erpStatus') != null, function ($query) {
                return $query->where(function ($query) {
                    $query->where('loans.status', 3)
                        ->orWhere('loans.ErpStatus', 3);
                });
            })
            ->when($request->input('roleStatus') != null && $request->input('erpStatus') == null, function ($query) use ($request) {
                return $query->where('loans.status', $request->input('roleStatus'));
            })
            ->when(!empty($request->po), function ($query) use ($request) {
                $query->where('assignedpo', $request->input("po"));
            })
            ->when((empty($po) &&  (!empty($request->input('division')) || $roleData) && !empty($polist)), function ($query) use ($polist) {
                return $query->whereIn('loans.assignedpo', $polist);
            });
    }

}
