<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Session;
use Illuminate\Support\Facades\DB;
// use DB;
use Carbon\Carbon;
use App\Models\Admission;
use App\Models\Loans;
use Illuminate\Support\Facades\Http;
use App\Models\Branch;
use Log;
use App\Http\Controllers\LiveApiController;

ini_set('memory_limit', '3072M');
ini_set('max_execution_time', 1800);
// header('Content-Type: application/json; charset=utf-8');
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
        $today = $year . '-' . $month . '-' . $day;
        $from_date = $year . '-' . $month . '-' . '01';

        $showStartDate = date('d-M-Y', strtotime($from_date));
        $showEndDate = date('d-M-Y', strtotime($today));

        // role wise data distribution
        if (session('role_designation') == 'AM') {
            $value = Branch::where([
                'program_id' => session('program_id')
            ])->get();
            $search2 = Branch::where([
                'area_id' => session('asid'),
                'program_id' => session('program_id')
            ])->distinct('branch_id')->get();
            $branch = Branch::where([
                'area_id' => session('asid'),
                'program_id' => session('program_id')
            ])->first();
            if ($search2->isEmpty()) {
?>
                <script>
                    alert("BranchCode Not found for this Area");
                    window.location = 'https://trendx.brac.net/';
                </script>
<?php
            }
            foreach ($search2 as $branch) {
                $branchCode = str_pad($branch->branch_id, 4, "0", STR_PAD_LEFT);
                $branchcode[] = $branchCode;
            }
            //dd(session('program_id'));
            //dd($search2);
            $pending_admission = Admission::select(DB::raw("COUNT(*) as count"))->where('projectcode', session('projectcode'))->where('Flag', 1)->whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $today)
                ->whereIn('branchcode', $branchcode)->where('reciverrole', '!=', '0')->first();
            $pending_profileadmission = Admission::select(DB::raw("COUNT(*) as count"))->where('projectcode', session('projectcode'))->where('Flag', 2)->whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $today)
                ->whereIn('branchcode', $branchcode)->where('reciverrole', '!=', '0')->first();
            $pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('projectcode', session('projectcode'))->whereDate($db . '.loans.time', '>=', $from_date)->where('reciverrole', '!=', '0')
                ->whereDate($db . '.loans.time', '<=', $today)->whereIn('branchcode', $branchcode)->first();
            // dd($pending_loan);
            // status wise count**********************
            $all_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('status', '1')->where('projectcode', session('projectcode'))->whereDate($db . '.loans.time', '>=', $from_date)->where('reciverrole', '!=', '0')->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $all_approve_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('ErpStatus', 1)->where('projectcode', session('projectcode'))->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $all_disbursement = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('ErpStatus', 2)->where('projectcode', session('projectcode'))->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $all_disburse_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('ErpStatus', 4)->where('projectcode', session('projectcode'))->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $all_reject_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('projectcode', session('projectcode'))->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->Where(function ($query) {
                    $query->where('status', 3)
                        ->orWhere('ErpStatus', 3);
                })->first();

            // roll wise count**********************
            $am_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('status', '1')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '2')->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $rm_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('status', '1')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '3')->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $dm_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('status', '1')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '4')->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $bm_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('status', '1')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '1')->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            // disbursement_amt
            $disburse_amt = Loans::select(DB::raw('sum(cast(propos_amt as double precision))'))->where('projectcode', session('projectcode'))->whereDate($db . '.loans.time', '>=', $from_date)->whereDate($db . '.loans.time', '<=', $today)
                ->whereIn('branchcode', $branchcode)->where('reciverrole', '!=', '0')->where('ErpStatus', 4)->first();
        } else if (session('role_designation') == 'RM') {
            //dd("Test");
            $value = Branch::where([
                'program_id' => session('program_id')
            ])->get();
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
                $branchcode[] = $branchCode;
            }
            //dd($branchcode);
            //$pending_admission = Admission::select(DB::raw("COUNT(*) as count"))->where('projectcode', session('projectcode'))->whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $today)->whereIn('branchcode', $branchcode)->where('reciverrole', '!=', '0')->first();
            $pending_admission = Admission::select(DB::raw("COUNT(*) as count"))->where('projectcode', session('projectcode'))->where('Flag', 1)->whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $today)
                ->whereIn('branchcode', $branchcode)->where('reciverrole', '!=', '0')->first();
            $pending_profileadmission = Admission::select(DB::raw("COUNT(*) as count"))->where('projectcode', session('projectcode'))->where('Flag', 2)->whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $today)
                ->whereIn('branchcode', $branchcode)->where('reciverrole', '!=', '0')->first();
            $pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('projectcode', session('projectcode'))->whereDate($db . '.loans.time', '>=', $from_date)->whereDate('time', '<=', $today)->whereIn('branchcode', $branchcode)->where('reciverrole', '!=', '0')->first();

            // status wise count**********************
            $all_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('status', '1')->where('projectcode', session('projectcode'))->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $all_approve_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('ErpStatus', 1)->where('projectcode', session('projectcode'))->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $all_disbursement = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('ErpStatus', 2)->where('projectcode', session('projectcode'))->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $all_disburse_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('ErpStatus', 4)->where('projectcode', session('projectcode'))->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $all_reject_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('projectcode', session('projectcode'))->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->Where(function ($query) {
                    $query->where('status', 3)
                        ->orWhere('ErpStatus', 3);
                })->first();

            // roll wise count**********************
            $am_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('status', '1')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '2')->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $rm_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('status', '1')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '3')->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $dm_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('status', '1')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '4')->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $bm_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('status', '1')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '1')->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            // disbursement_amt
            //dd($dm_pending_loan->count);
            $disburse_amt = Loans::select(DB::raw('sum(cast(propos_amt as double precision))'))->where('projectcode', session('projectcode'))->whereDate($db . '.loans.time', '>=', $from_date)->whereDate($db . '.loans.time', '<=', $today)
                ->whereIn('branchcode', $branchcode)->where('reciverrole', '!=', '0')->where('ErpStatus', 4)->first();
        } else if (session('role_designation') == 'DM') {
            $value = Branch::where([
                'program_id' => session('program_id')
            ])->get();
            $search2 = Branch::where([
                'division_id' => session('asid'),
                'program_id' => session('program_id')
            ])->distinct('region_id')->get();
            $branch = Branch::where([
                'division_id' => session('asid'),
                'program_id' => session('program_id')
            ])->first();
            foreach ($search2 as $branch) {
                $branchCode = str_pad($branch->branch_id, 4, "0", STR_PAD_LEFT);
                $branchcode[] = $branchCode;
            }
            //$pending_admission = Admission::select(DB::raw("COUNT(*) as count"))->where('projectcode', session('projectcode'))->whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $today)
            // ->whereIn('branchcode', $branchcode)->where('reciverrole', '!=', '0')->first();
            $pending_admission = Admission::select(DB::raw("COUNT(*) as count"))->where('projectcode', session('projectcode'))->where('Flag', 1)->whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $today)
                ->whereIn('branchcode', $branchcode)->where('reciverrole', '!=', '0')->first();
            $pending_profileadmission = Admission::select(DB::raw("COUNT(*) as count"))->where('projectcode', session('projectcode'))->where('Flag', 2)->whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $today)
                ->whereIn('branchcode', $branchcode)->where('reciverrole', '!=', '0')->first();
            $pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('projectcode', session('projectcode'))->whereDate($db . '.loans.time', '>=', $from_date)
                ->whereDate($db . '.loans.time', '<=', $today)->where($db . '.loans.reciverrole', '!=', '0')->whereIn('branchcode', $branchcode)->first();
            // dd($pending_loan);
            // status wise count**********************
            $all_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('status', '1')->where('projectcode', session('projectcode'))->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $all_approve_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('ErpStatus', 1)->where('projectcode', session('projectcode'))->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $all_disbursement = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('ErpStatus', 2)->where('projectcode', session('projectcode'))->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $all_disburse_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('ErpStatus', 4)->where('projectcode', session('projectcode'))->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $all_reject_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('projectcode', session('projectcode'))->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->Where(function ($query) {
                    $query->where('status', 3)
                        ->orWhere('ErpStatus', 3);
                })->first();

            // roll wise count**********************
            $am_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('status', '1')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '2')->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $rm_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('status', '1')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '3')->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $dm_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('status', '1')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '4')->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $bm_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('status', '1')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '1')->whereDate($db . '.loans.time', '>=', $from_date)->whereIn('branchcode', $branchcode)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            // disbursement_amt
            $disburse_amt = Loans::select(DB::raw('sum(cast(propos_amt as double precision))'))->where('projectcode', session('projectcode'))->whereDate($db . '.loans.time', '>=', $from_date)->whereDate($db . '.loans.time', '<=', $today)
                ->whereIn('branchcode', $branchcode)->where('reciverrole', '!=', '0')->where('ErpStatus', 4)->first();
        } else if ((session('role_designation') == 'HO') or (session('role_designation') == 'PH')) {
            $value = Branch::where([
                'program_id' => session('program_id')
            ])->get();
            $branch = Branch::where([
                'division_id' => session('asid'),
                'program_id' => session('program_id')
            ])->first();
            $search2 = Branch::where([
                'program_id' => session('program_id')
            ])->distinct('division_id')->get();
            //$pending_admission = Admission::select(DB::raw("COUNT(*) as count"))->where('projectcode', session('projectcode'))->whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $today)->where('reciverrole', '!=', '0')->first();
            $pending_admission = Admission::select(DB::raw("COUNT(*) as count"))->where('projectcode', session('projectcode'))->where('Flag', 1)->whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $today)->where('reciverrole', '!=', '0')->first();
            $pending_profileadmission = Admission::select(DB::raw("COUNT(*) as count"))->where('projectcode', session('projectcode'))->where('Flag', 2)->whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $today)->where('reciverrole', '!=', '0')->first();;
            $pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('projectcode', session('projectcode'))->whereDate($db . '.loans.time', '>=', $from_date)->whereDate($db . '.loans.time', '<=', $today)->where('reciverrole', '!=', '0')->first();
            // status wise count**********************
            $all_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('status', '1')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '!=', '0')->whereDate($db . '.loans.time', '>=', $from_date)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $all_approve_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('ErpStatus', 1)->where('projectcode', session('projectcode'))
                ->where('reciverrole', '!=', '0')->whereDate($db . '.loans.time', '>=', $from_date)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $all_disbursement = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('ErpStatus', 2)->where('projectcode', session('projectcode'))
                ->where('reciverrole', '!=', '0')->whereDate($db . '.loans.time', '>=', $from_date)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $all_disburse_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('ErpStatus', 4)->where('projectcode', session('projectcode'))
                ->where('reciverrole', '!=', '0')->whereDate($db . '.loans.time', '>=', $from_date)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $all_reject_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '!=', '0')->whereDate($db . '.loans.time', '>=', $from_date)
                ->whereDate($db . '.loans.time', '<=', $today)->Where(function ($query) {
                    $query->where('status', 3)
                        ->orWhere('ErpStatus', 3);
                })->first();

            // roll wise count**********************
            $am_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('status', '1')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '2')->whereDate($db . '.loans.time', '>=', $from_date)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $rm_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('status', '1')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '3')->whereDate($db . '.loans.time', '>=', $from_date)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $dm_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('status', '1')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '4')->whereDate($db . '.loans.time', '>=', $from_date)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $bm_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('status', '1')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '1')->whereDate($db . '.loans.time', '>=', $from_date)
                ->whereDate($db . '.loans.time', '<=', $today)->first();

            // disbursement_amt
            $disburse_amt = Loans::select(DB::raw('sum(cast(propos_amt as double precision))'))->where('projectcode', session('projectcode'))->where('reciverrole', '!=', '0')->whereDate($db . '.loans.time', '>=', $from_date)->whereDate($db . '.loans.time', '<=', $today)->where('ErpStatus', 4)->first();
        } else {
            return redirect()->back()->with('error', 'data does not match');
        }
        //dd($dm_pending_loan);
        return view('Dashboard')->with('branch', $branch)->with('value', $value)->with('search2', $search2)
            ->with('pending_admission', $pending_admission)->with('disburse_amt', $disburse_amt)
            ->with('pending_loan', $pending_loan)->with('am_pending_loan', $am_pending_loan)
            ->with('rm_pending_loan', $rm_pending_loan)->with('dm_pending_loan', $dm_pending_loan)
            ->with('bm_pending_loan', $bm_pending_loan)->with('all_pending_loan', $all_pending_loan)
            ->with('all_approve_loan', $all_approve_loan)->with('all_disbursement', $all_disbursement)
            ->with('all_disburse_loan', $all_disburse_loan)->with('all_reject_loan', $all_reject_loan)
            ->with('showStartDate', $showStartDate)->with('showEndDate', $showEndDate)
            ->with('pending_profileadmission', $pending_profileadmission);
    }

    public function project(Request $request, $project)
    {
        // dd('asd');
        $db = config('database.db');
        $request->session()->put('project', $project);
        if (session('project') == "Dabi") {
            $request->session()->put('projectcode', '015');
            $request->session()->put('program_id', '1');
        }
        if (session('project') == "Progoti") {
            $request->session()->put('projectcode', '060');
            $request->session()->put('program_id', '5');
        }
        $getRole = Db::table($db . '.role_hierarchies')->select('designation', 'position')->where('projectcode', session('projectcode'))->where('role', session('erp_user_role'))->first();
        $request->session()->put('role_designation', $getRole->designation);
        $request->session()->put('roll', $getRole->position);
        // dd($getRole);
        return redirect('dashboard');
    }

    public function login()
    {
        return redirect('https://trendx.brac.net/home');
    }

    public function tokenVerify()
    {
        $db = config('database.db');
        $currentDatetime = date("Y-m-d h:i:s");
        $tokenCheckDb = DB::table($db . '.oauth2')->get();

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

    public function documentErpPosting($document)
    {
        $db = config('database.db');
        $access_token = $this->tokenVerify();
        $clientid = 'Ieg1N5W2qh3hF0qS9Zh2wq6eex2DB935';
        $clientsecret = '4H2QJ89kYQBStaCuY73h';
        $url = 'https://bracapitesting.brac.net/v1/buffer-members';

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
            CURLOPT_POSTFIELDS => $document,
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            //   return $response;
            if ($response == "ok") {
                DB::table($db . '.admissions')->where('entollmentid', $id)->update(['ErpHttpStatus' => $ErpHttpStatus, 'ErpErrorMessage' => null, 'ErpErrors' => null]);
            } else {
                $responseAry = json_decode($response);
                $ErpHttpStatus = $responseAry->httpStatus;
                $ErpErrorMessage = $responseAry->errorMessage;
                $ErpErrors = $responseAry->errors;
                $documentAry = json_decode($document);
                //   dd($documentAry);
                $id = $documentAry[0]->id;
                //   dd($id);

                DB::table($db . '.admissions')->where('entollmentid', $id)->update(['ErpHttpStatus' => $ErpHttpStatus, 'ErpErrorMessage' => $ErpErrorMessage, 'ErpErrors' => $ErpErrors]);
                //   dd($responseAry);
            }
        }
    }

    public function getErpDcsAddmissionList()
    {
        $access_token = $this->tokenVerify();
        $clientid = 'Ieg1N5W2qh3hF0qS9Zh2wq6eex2DB935';
        $clientsecret = '4H2QJ89kYQBStaCuY73h';
        $url = 'https://bracapitesting.brac.net/v1/branches/0607/buffer-members';

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

            return $response;
            return $this->insertPostedAddmissionList($response);
        }
    }

    public function insertPostedAddmissionList($response)
    {
        $db = config('database.db');
        $BufferMemberStatus = $response;
        $arrayAddmission = json_decode($response);
        foreach ($arrayAddmission as $datas) {
            $values = array(
                'applicationdate' => $datas->applicationDate,
                'assignedpopin' => $datas->assignedPoPin,
                'bankaccountnumber' => $datas->bankAccountNumber,
                'bankbranchid' => $datas->bankBranchId,
                'bankid' => $datas->bankId,
                'bkashwalletno' => $datas->bkashWalletNo,
                'branchcode' => $datas->branchCode,
                'contactno' => $datas->contactNo,
                'dateofbirth' => $datas->dateOfBirth,
                'educationid' => $datas->educationId,
                'fathernameen' => $datas->fatherNameEn,
                'flag' => $datas->flag,
                'genderid' => $datas->genderId,
                //guarantor
                "guarantordateofbirth" => $datas->guarantor->dateOfBirth,
                "guarantorbackimageurl" => $datas->guarantor->idCard->backImageUrl,
                "guarantorcardtypeid" => $datas->guarantor->idCard->cardTypeId,
                "guarantorissueplace" => $datas->guarantor->idCard->issuePlace,
                "guarantorexpirydate" => $datas->guarantor->idCard->expiryDate,
                "guarantorfrontimageurl" => $datas->guarantor->idCard->frontImageUrl,
                "guarantoridcardno" => $datas->guarantor->idCard->idCardNo,
                "guarantorissuedate" => $datas->guarantor->idCard->issueDate,
                "guarantornameen" => $datas->guarantor->nameEn,
                "guarantorrelationshipid" => $datas->guarantor->relationshipId,
                'addmission_id' => $datas->id,
                //idCard
                "idcardbackimageurl" => $datas->idCard->backImageUrl,
                "idcardcardtypeid" => $datas->idCard->cardTypeId,
                "idcardexpirydate" => $datas->idCard->expiryDate,
                "idcardfrontimageurl" => $datas->idCard->frontImageUrl,
                "idcardidcardno" => $datas->idCard->idCardNo,
                "idcardissuedate" => $datas->idCard->issueDate,
                "idcardissueplace" => $datas->idCard->issuePlace,
                'maritalstatusid' => $datas->maritalStatusId,
                'memberid' => $datas->memberId,
                'memberimageurl' => $datas->memberImageUrl,
                'memberTypeId' => $datas->memberTypeId,
                'motherNameEn' => $datas->motherNameEn,
                'nameEn' => $datas->nameEn,
                //nominees
                "nomineescontactno" => $datas->nominees->contactNo,
                "nomineesdateofbirth" => $datas->nominees->contactNo,
                "nomineesid" => $datas->nominees->contactNo,
                "nomineesbackimageurl" => $datas->nominees->idCard->backImageUrl,
                "nomineescardtypeid" => $datas->nominees->idCard->cardTypeId,
                "nomineesexpirydate" => $datas->nominees->idCard->expiryDate,
                "nomineesfrontimageurl" => $datas->nominees->idCard->frontImageUrl,
                "nomineesidcardno" => $datas->nominees->idCard->idCardNo,
                "nomineesissuedate" => $datas->nominees->idCard->issueDate,
                "nomineesissueplace" => $datas->nominees->idCard->issuePlace,
                "nomineesname" => $datas->nominees->name,
                "nomineesrelationshipid" => $datas->nominees->relationshipId,
                'occupationid' => $datas->occupationId,
                'passbooknumber' => $datas->passbookNumber,
                'permanentaddress' => $datas->permanentAddress,
                'permanentdistrictid' => $datas->permanentDistrictId,
                'permanentupazilaid' => $datas->permanentUpazilaId,
                'poid' => $datas->poId,
                'presentaddress' => $datas->presentAddress,
                'presentdistrictid' => $datas->presentDistrictId,
                'presentupazilaid' => $datas->presentUpazilaId,
                'projectcode' => $datas->projectCode,
                'rejectionreason' => $datas->rejectionReason,
                'routingnumber' => $datas->routingNumber,
                'savingsproductid' => $datas->savingsProductId,
                'spousedateofbirth' => $datas->spouseDateOfBirth,
                // spouseIdCard
                "spouseidcardbackimageurl" => $datas->spouseIdCard->backImageUrl,
                "spouseidcardcardtypeid" => $datas->spouseIdCard->cardTypeId,
                "spouseidcardexpirydate" => $datas->spouseIdCard->expiryDate,
                "spouseidcardfrontimageurl" => $datas->spouseIdCard->frontImageUrl,
                "spouseidcardidcardno" => $datas->spouseIdCard->idCardNo,
                "spouseidcardissuedate" => $datas->spouseIdCard->issueDate,
                "spouseidcardissueplace" => $datas->spouseIdCard->issuePlace,
                'spousenameen' => $datas->spouseNameEn,
                'statusid' => $datas->statusId,
                'targetamount' => $datas->targetAmount,
                'tinnumber' => $datas->tinNumber,
                'updated' => $datas->updated,
                'vocode' => $datas->voCode,
                'void' => $datas->voId
            );

            $checkPostedAdmission = DB::table($db . '.posted_admission')->where($datas->id)->get();
            return $checkPostedAdmission;
            // DB::table('dcs.posted_admission')->insert($values);
        }
    }

    public function test($id)
    {
        $db = config('database.db');
        $data = DB::table($db . '.admissions')->where('id', $id)->first();
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
            "nameEn" => $data->ReffererName,
            "relationshipId" => null,
        );
        $nominees = array();
        $nominees[] = array(
            "contactNo" => $data->NomineePhoneNumber,
            "dateOfBirth" => $data->NomineeDOB,
            "id" => null,
            "idCard" => array(),
            "idCard" => array(
                "backImageUrl" => $data->NomineeIdImg,
                "cardTypeId" => 5,
                "expiryDate" => $data->NomineeIdExpiredate,
                "frontImageUrl" => $data->NomineeIdImg,
                "idCardNo" => $data->NomineeNidNo,
                "issueDate" => null,
                "issuePlace" => $data->NomineeIdPlaceOfissue,
            ),
            "name" => $data->NomineeName,
            "relationshipId" => "$data->RelationshipId",
        );
        $arrayData[] = array(
            "applicationDate" => date('Y-m-d', strtotime($data->created_at)),
            "assignedPoPin" => $data->assignedpo,
            "bankAccountNumber" => null,
            "bankBranchId" => null,
            "bankId" => null,
            "bkashWalletNo" => $data->WalletNo,
            "branchCode" => $data->branchcode,
            "contactNo" => $data->Phone,
            "dateOfBirth" => $data->DOB,
            "educationId" => $data->EducationId,
            "fatherNameEn" => $data->FatherName,
            "flag" => 1,
            "genderId" => $data->GenderId,
            "guarantor" => $guarantor,
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
            "memberImageUrl" => $data->ApplicantCpmbinedImg,
            "memberTypeId" => $data->MemberCateogryId,
            "motherNameEn" => $data->MotherName,
            "nameEn" => $data->ApplicantsName,
            "nominees" => $nominees,
            "occupationId" => $data->Occupation,
            "passbookNumber" => null,
            "permanentAddress" => $data->PermanentAddress,
            "permanentDistrictId" => null,
            "permanentUpazilaId" => $data->parmanentUpazilaId,
            "poId" => $data->assignedpo,
            "presentAddress" => $data->PresentAddress,
            "presentDistrictId" => null,
            "presentUpazilaId" => $data->presentUpazilaId,
            "projectCode" => $data->projectcode,
            "rejectionReason" => null,
            "routingNumber" => null,
            "savingsProductId" => $data->SavingsProductId,
            "spouseDateOfBirth" => $data->SposeDOB,
            "spouseIdCard" => array(),
            "spouseIdCard" => array(
                "backImageUrl" => $data->SpuseIdImg,
                "cardTypeId" => $data->SpouseCardType,
                "expiryDate" => $data->SpouseIdExpiredate,
                "frontImageUrl" => $data->SpuseIdImg,
                "idCardNo" => $data->SpouseNidOrBid,
                "issueDate" => null,
                "issuePlace" => $data->SpouseIdPlaceOfissue,
            ),
            "spouseNameEn" => $data->SpouseName,
            "statusId" => 1,
            "targetAmount" => null,
            "tinNumber" => null,
            "updated" => true,
            "voCode" => $data->orgno,
            "voId" => null
        );
        $jsonData = json_encode($arrayData);
        // dd($jsonData);
        // return $jsonData;

        $response = $this->documentErpPosting($jsonData);
        // dd($response);
        return $response;
    }


    public function loantest($id)
    {
        $db = config('database.db');
        $data = DB::table($db . '.loans')->where('id', $id)->first();
        // $memberInfo = DB::table('dcs.admissions')->where('entollmentid', $data->mem_id)->first();
        // dd($memberInfo->NomineeDOB);
        $arrayData = array();
        $nominees = array();
        $nominees[] = array(
            "contactNo" => $data->grntor_phone,
            "dateOfBirth" => $data->insurn_dob,
            "id" => $data->id,
            "idCard" => array(),
            "idCard" => array(
                "backImageUrl" => $data->grantor_nidback_photo,
                "cardTypeId" => null,
                "expiryDate" => null,
                "frontImageUrl" => $data->grantor_nidfront_photo,
                "idCardNo" => $data->insurn_spouseNid,
                "issueDate" => null,
                "issuePlace" => null,
            ),
            "name" => $data->insurn_name,
            "relationshipId" => $data->insurn_relation,
        );

        $arrayData[] = array(
            "applicationDate" => date('Y-m-d', strtotime($data->time)),
            "approvedDurationInMonths" => $data->loan_duration,
            "approvedLoanAmount" => $data->propos_amt,
            "branchCode" => $data->branchcode,
            "coBorrowerDto" => array(),
            "coBorrowerDto" => array(
                "idCard" => array(),
                "idCard" => array(
                    "backImageUrl" => $data->grantor_nidback_photo,
                    "cardTypeId" => $data->insurn_mainID,
                    "expiryDate" => null,
                    "frontImageUrl" => $data->grantor_nidfront_photo,
                    "idCardNo" => $data->insurn_mainID,
                    "issueDate" => null,
                    "issuePlace" => null,
                ),
                "name" => $data->grntor_name,
                "relationshipId" => $data->grntor_rlationClient,
            ),
            "consentUrl" => null,
            "disbursementDate" => null,
            "flag" => null,
            "frequencyId" => null,
            "id" => $data->loan_id,
            "insuranceProductId" => $data->loan_product,
            "loanAccountId" => null,
            "loanApprover" => $data->reciverrole,
            "loanProductId" => $data->loan_product,
            "loanProposalStatusId" => 1,                         //test
            "memberId" => $data->erp_mem_id,
            "memberTypeId" => null,
            "microInsurance" => "false",
            "modeOfPaymentId" => null,
            "nominees" => $nominees,
            "policyTypeId" => null,
            "premiumAmount" => $data->instal_amt,
            "projectCode" => $data->projectcode,
            "proposalDurationInMonths" => $data->loan_duration,
            "proposedLoanAmount" => $data->propos_amt,
            "rejectionReason" => $data->comment,
            "schemeId" => $data->scheme,
            "spouseIdCard" => array(),
            "spouseIdCard" => array(
                "dateOfBirth" => $data->insurn_dob,
                "genderId" => $data->insurn_gender,
                "idCard" => array(),
                "idCard" => array(
                    "backImageUrl" => $data->grantor_nidback_photo,
                    "cardTypeId" => null,
                    "expiryDate" => null,
                    "frontImageUrl" => $data->grantor_nidfront_photo,
                    "idCardNo" => $data->insurn_spouseNid,
                    "issueDate" => null,
                    "issuePlace" => null,
                ),
                "name" => $data->insurn_name,
                "relationshipId" => $data->insurn_relation,
            ),
            "sectorId" => null,
            "signConsent" => null,
            "subSectorId" => null,
            "updated" => $data->time,
            "voCode" => $data->orgno,
            "voId" => null,

        );
        $jsonData = json_encode($arrayData);
        // dd($jsonData);
        return $jsonData;

        $response = $this->documentErpPosting($jsonData);
        // dd($response);
        return $response;
    }

    public function statusSession(Request $req)
    {
        $status = $req->status;
        if ($status) {
            if ($status == 'pending') {
                session()->forget('erpstatus_btn');
                $req->session()->put('status_btn', 1);
            } else if ($status == 'approve') {
                session()->forget('status_btn');
                $req->session()->put('erpstatus_btn', 1);
            } else if ($status == 'disbursement') {
                session()->forget('status_btn');
                $req->session()->put('erpstatus_btn', 2);
            } else if ($status == 'disburse') {
                session()->forget('status_btn');
                $req->session()->put('erpstatus_btn', 4);
            } else if ($status == 'reject') {
                $req->session()->put('status_btn', 3);
                $req->session()->put('erpstatus_btn', 3);
            }
        }

        // dd(session()->all());
        $db = config('database.db');
        $db = config('database.db');
        if (session('role_designation') == 'AM') {
            $value = Branch::where([
                'area_id' => session('asid'),
                'program_id' => session('program_id')
            ])->get();
        } else if (session('role_designation') == 'RM') {
            $value = Branch::where([
                'region_id' => session('asid'),
                'program_id' => session('program_id')
            ])->get();
        } else if (session('role_designation') == 'DM') {
            $value = Branch::where([
                'division_id' => session('asid'),
                'program_id' => session('program_id')
            ])->get();
        } else if ((session('role_designation') == 'HO') or (session('role_designation') == 'PH')) {
            $value = Branch::where([
                'program_id' => session('program_id')
            ])->get();
        } else if (session('role_designation') == 'BM') {
            $value = Branch::where([
                'branch_id' => session('asid'),
                'program_id' => session('program_id')
            ])->get();
        } else {
            return redirect()->back()->with('error', 'data does not match');
        }
        $all_branchcode = array();
        $all_assignedpo = array();
        $all_branchcodeAdmission = array();
        $all_assignedpoAdmission = array();
        foreach ($value as $row) {
            $branchCode1 = $row->branch_id;
            $branchCode = str_pad($branchCode1, 4, "0", STR_PAD_LEFT);

            $value1 = DB::table($db . '.loans')->select('branchcode', 'assignedpo')
                ->where('branchcode', $branchCode)->groupBy('branchcode', 'assignedpo')->get();
            $value1Admission = DB::table($db . '.admissions')->select('branchcode', 'assignedpo')
                ->where('branchcode', $branchCode)->groupBy('branchcode', 'assignedpo')->get();

            if (!$value1Admission->isEmpty()) {
                foreach ($value1Admission as $assignedpoAdmission) {
                    $all_branchcodeAdmission[] = $assignedpoAdmission->branchcode;
                    $all_assignedpoAdmission[] = str_pad($assignedpoAdmission->assignedpo, 8, "0", STR_PAD_LEFT);
                }
            }
            if (!$value1->isEmpty()) {
                foreach ($value1 as $assignedpo) {
                    $all_branchcode[] = $assignedpo->branchcode;
                    $all_assignedpo[] = str_pad($assignedpo->assignedpo, 8, "0", STR_PAD_LEFT);
                }
            }
        }

        $responseAdmission = Http::post('http://scm.brac.net/sc/GetPO', [
            'branchcode' => $all_branchcodeAdmission,
            'projectcode' => session('projectcode'),
        ]);
        $polistAdmission = $responseAdmission->object()->data;

        if ($polistAdmission) {
            $poAdmission = array();
            foreach ($polistAdmission as $conoAdmission) {
                foreach ($all_assignedpoAdmission as $key => $value) {
                    if ($conoAdmission->cono == $value) {
                        $poAdmission[] = $value;
                    }
                }
            }
        }

        $responseLoan = Http::post('http://scm.brac.net/sc/GetPO', [
            'branchcode' => $all_branchcode,
            'projectcode' => session('projectcode'),
        ]);
        $polist = $responseLoan->object()->data;

        if ($polist) {
            $po = array();
            foreach ($polist as $cono) {
                foreach ($all_assignedpo as $key => $value) {
                    if ($cono->cono == $value) {
                        $po[] = $value;
                    }
                }
            }
        }

        // division search
        $division = $req->division;
        if ($division != null) {
            $d_branch = DB::table('public.branch')
                ->where('program_id', session('program_id'))
                ->where('division_id', $req->division)
                ->distinct('branch_id')
                ->get();
            foreach ($d_branch as $key => $value) {
                $division_search1 = str_pad($value->branch_id, 4, "0", STR_PAD_LEFT);
                $division_search[] = $division_search1;
            }
        }

        //find branch for region search
        $region = $req->region;
        if ($region != null) {
            $r_branch = DB::table('public.branch')
                ->where('program_id', session('program_id'))
                ->where('region_id', $region)
                ->distinct('branch_id')
                ->get();
            foreach ($r_branch as $key => $value) {
                $region_search1 = str_pad($value->branch_id, 4, "0", STR_PAD_LEFT);
                $region_search[] = $region_search1;
            }
        }
        //find branch for area search
        $area = $req->area;
        if ($area != null) {
            $area_branch = DB::table('public.branch')
                ->where('program_id', session('program_id'))
                ->where('area_id', $area)
                ->distinct('branch_id')
                ->get();

            foreach ($area_branch as $key => $value) {
                $area_search1 = str_pad($value->branch_id, 4, "0", STR_PAD_LEFT);
                $area_search[] = $area_search1;
            }
        }
        // request search value
        $branch_search = $req->branch;
        $branchcode_search = str_pad($branch_search, 4, "0", STR_PAD_LEFT);
        $dateForm = $req->dateFrom;
        $dateTo = $req->dateTo;
        $po_search = $req->po;
        // division & date search
        if ($division != null && $region == null && $area == null && $branch_search == null && $po_search == null) {
            $counts = DB::table($db . '.loans')
                ->whereIn($db . '.loans.assignedpo', $po)
                ->whereDate($db . '.loans.time', '>=', $dateForm)
                ->whereDate($db . '.loans.time', '<=', $dateTo)
                ->whereIn($db . '.loans.branchcode', $division_search)
                ->where($db . '.loans.reciverrole', '!=', '0')
                ->where($db . '.loans.projectcode', session('projectcode'))
                ->get();
            $admission = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $poAdmission)
                ->whereIn('branchcode', $division_search)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('reciverrole', '!=', '0')
                ->where('Flag', 1)
                ->where('projectcode', session('projectcode'))
                ->get();
            $profileadmission = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $poAdmission)
                ->whereIn('branchcode', $division_search)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('reciverrole', '!=', '0')
                ->where('Flag', 2)
                ->where('projectcode', session('projectcode'))
                ->get();
        }
        // region & date search
        else if ($region != null && $area == null && $branch_search == null && $po_search == null) {
            $counts = DB::table($db . '.loans')
                ->whereIn($db . '.loans.assignedpo', $po)
                ->whereDate($db . '.loans.time', '>=', $dateForm)
                ->whereDate($db . '.loans.time', '<=', $dateTo)
                ->whereIn($db . '.loans.branchcode', $region_search)
                ->where($db . '.loans.reciverrole', '!=', '0')
                ->where($db . '.loans.projectcode', session('projectcode'))
                ->get();
            $admission = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $poAdmission)
                ->whereIn('branchcode', $region_search)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('reciverrole', '!=', '0')
                ->where('Flag', 1)
                ->where('projectcode', session('projectcode'))
                ->get();
            $profileadmission = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $poAdmission)
                ->whereIn('branchcode', $region_search)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('reciverrole', '!=', '0')
                ->where('Flag', 2)
                ->where('projectcode', session('projectcode'))
                ->get();
        }
        // area & date search
        else if ($area != null && $branch_search == null && $po_search == null) {
            $counts = DB::table($db . '.loans')
                ->whereIn($db . '.loans.assignedpo', $po)
                ->whereDate($db . '.loans.time', '>=', $dateForm)
                ->whereDate($db . '.loans.time', '<=', $dateTo)
                ->whereIn($db . '.loans.branchcode', $area_search)
                ->where($db . '.loans.reciverrole', '!=', '0')
                ->where($db . '.loans.projectcode', session('projectcode'))
                ->get();
            $admission = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $poAdmission)
                ->whereIn('branchcode', $area_search)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('reciverrole', '!=', '0')
                ->where('Flag', 1)
                ->where('projectcode', session('projectcode'))
                ->get();
            $profileadmission = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $poAdmission)
                ->whereIn('branchcode', $area_search)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('reciverrole', '!=', '0')
                ->where('Flag', 2)
                ->where('projectcode', session('projectcode'))
                ->get();
            // dd( $counts);
        }
        // branch & date search
        else if ($branch_search != null && $dateForm != null && $dateTo != null && $po_search == null) {
            $counts = DB::table($db . '.loans')
                ->whereIn($db . '.loans.assignedpo', $po)
                ->whereDate($db . '.loans.time', '>=', $dateForm)
                ->whereDate($db . '.loans.time', '<=', $dateTo)
                ->where($db . '.loans.branchcode', $branchcode_search)
                ->where($db . '.loans.reciverrole', '!=', '0')
                ->where($db . '.loans.projectcode', session('projectcode'))
                ->get();
            $admission = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $poAdmission)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('reciverrole', '!=', '0')
                ->where('branchcode', $branchcode_search)
                ->where('Flag', 1)
                ->where('projectcode', session('projectcode'))
                ->get();
            $profileadmission = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $poAdmission)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('reciverrole', '!=', '0')
                ->where('branchcode', $branchcode_search)
                ->where('Flag', 2)
                ->where('projectcode', session('projectcode'))
                ->get();
        }
        // po & date
        else if ($dateForm != null && $dateTo != null && $po_search != null) {
            $counts = DB::table($db . '.loans')
                ->whereIn('assignedpo', $po)
                ->whereDate('time', '>=', $dateForm)
                ->whereDate('time', '<=', $dateTo)
                ->where('assignedpo', $po_search)
                ->where('reciverrole', '!=', '0')
                ->where('projectcode', session('projectcode'))
                ->get();
            $admission = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $poAdmission)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('branchcode', $branchcode_search)
                ->where('reciverrole', '!=', '0')
                ->where('assignedpo', $po_search)
                ->where('Flag', 1)
                ->where('projectcode', session('projectcode'))
                ->get();
            $profileadmission = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $poAdmission)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('branchcode', $branchcode_search)
                ->where('reciverrole', '!=', '0')
                ->where('assignedpo', $po_search)
                ->where('Flag', 2)
                ->where('projectcode', session('projectcode'))
                ->get();
        }
        // date search
        else if ($dateForm != null && $dateTo != null && $branch_search == null && $po_search == null) {
            $counts = DB::table($db . '.loans')
                ->whereIn($db . '.loans.assignedpo', $po)
                // ->whereBetween($db.'.loans.time', [$dateForm, $dateTo])
                ->whereDate($db . '.loans.time', '>=', $dateForm)
                ->whereDate($db . '.loans.time', '<=', $dateTo)
                ->where($db . '.loans.reciverrole', '!=', '0')
                ->where($db . '.loans.projectcode', session('projectcode'))
                ->get();
            $admission = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $poAdmission)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('reciverrole', '!=', '0')
                ->where('Flag', 1)
                ->where('projectcode', session('projectcode'))
                ->get();
            $profileadmission = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $poAdmission)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('reciverrole', '!=', '0')
                ->where('Flag', 2)
                ->where('projectcode', session('projectcode'))
                ->get();
        }
        $pending = 0;
        $approved = 0;
        $disbursement = 0;
        $disburse = 0;
        $rejected = 0;
        $btn_bm = 0;
        $btn_am = 0;
        $btn_rm = 0;
        $btn_dm  = 0;
        $disburse_amt = 0;

        // total admission
        $totalAdmission = count($admission);
        $totalProfileAdmission = count($profileadmission);
        // total loan count
        $totalLoan = count($counts);

        foreach ($counts as $count) {
            if ($count->ErpStatus == 4) {
                $disburse_amt += $count->propos_amt;
            }
            if ($count->status == 1) {
                $pending++;
            } else if ($count->ErpStatus == 1) {
                $approved++;
            }

            if ($count->ErpStatus == 2) {
                $disbursement++;
            } else if ($count->ErpStatus == 4) {
                $disburse++;
            } else if ($count->status == 3 or $count->ErpStatus == 3) {
                $rejected++;
            }
            // dd(session('status_btn'));

            if (session('status_btn')) {
                if ($count->status == session('status_btn')) {
                    if ($count->reciverrole == 1) {
                        $btn_bm++;
                    } else if ($count->reciverrole == 2) {
                        $btn_am++;
                    } else if ($count->reciverrole == 3) {
                        $btn_rm++;
                    } else if ($count->reciverrole == 4) {
                        $btn_dm++;
                    }
                }
            }
            if (session('erpstatus_btn')) {
                if ($count->ErpStatus == session('erpstatus_btn')) {
                    if ($count->reciverrole == 1) {
                        $btn_bm++;
                    } else if ($count->reciverrole == 2) {
                        $btn_am++;
                    } else if ($count->reciverrole == 3) {
                        $btn_rm++;
                    } else if ($count->reciverrole == 4) {
                        $btn_dm++;
                    }
                }
            }
        }

        $showStartDate = date('d-M-Y', strtotime($dateForm));
        $showEndDate = date('d-M-Y', strtotime($dateTo));

        // dd($pending, $approved, $disbursement, $disburse, $rejected, $btn_bm, $btn_am, $btn_rm, $btn_dm, $counts);
        return response()->json([$pending, $approved, $disbursement, $disburse, $rejected, $btn_bm, $btn_am, $btn_rm, $btn_dm, $totalLoan, $totalAdmission, $disburse_amt, $showStartDate, $showEndDate, $totalProfileAdmission]);
    }

    public function dashboardTable(Request $req)
    {
        $db = config('database.db');
        $db = config('database.db');
        if (session('role_designation') == 'AM') {
            $value = Branch::where([
                'area_id' => session('asid'),
                'program_id' => session('program_id')
            ])->get();
        } else if (session('role_designation') == 'RM') {
            $value = Branch::where([
                'region_id' => session('asid'),
                'program_id' => session('program_id')
            ])->get();
        } else if (session('role_designation') == 'DM') {
            $value = Branch::where([
                'division_id' => session('asid'),
                'program_id' => session('program_id')
            ])->get();
        } else if ((session('role_designation') == 'HO') or (session('role_designation') == 'PH')) {
            $value = Branch::where([
                'program_id' => session('program_id')
            ])->get();
        } else if (session('role_designation') == 'BM') {
            $value = Branch::where([
                'branch_id' => session('asid'),
                'program_id' => session('program_id')
            ])->get();
        } else {
            return redirect()->back()->with('error', 'data does not match');
        }
        $all_branchcode = array();
        $all_assignedpo = array();
        foreach ($value as $row) {
            $branchCode1 = $row->branch_id;
            $branchCode = str_pad($branchCode1, 4, "0", STR_PAD_LEFT);

            $value1 = DB::table($db . '.loans')->select('branchcode', 'assignedpo')
                ->where('branchcode', $branchCode)->groupBy('branchcode', 'assignedpo')->get();
            if (!$value1->isEmpty()) {
                foreach ($value1 as $assignedpo) {
                    $all_branchcode[] = $assignedpo->branchcode;
                    $all_assignedpo[] = str_pad($assignedpo->assignedpo, 8, "0", STR_PAD_LEFT);
                }
            }
        }

        // $responseLoan = Http::post('http://scm.brac.net/sc/GetPO', [
        //     'branchcode' => $all_branchcode,
        //     'projectcode' => session('projectcode'),
        // ]);
        // $polist = $responseLoan->object()->data;
        $ProjectCode = session('projectcode');
        $livepolist = new LiveApiController();
        $polist = $livepolist->Group_Branch_PO_List_Data($db, $all_branchcode, $ProjectCode);
        if ($polist) {
            $po = array();
            foreach ($polist as $cono) {
                foreach ($all_assignedpo as $key => $value) {
                    if ($cono->cono == $value) {
                        $po[] = $value;
                    }
                }
            }
        }

        // division search
        $division = $req->division;
        if ($division != null) {
            $d_branch = DB::table('public.branch')
                ->where('program_id', session('program_id'))
                ->where('division_id', $req->division)
                ->distinct('branch_id')
                ->get();
            foreach ($d_branch as $key => $value) {
                $division_search1 = str_pad($value->branch_id, 4, "0", STR_PAD_LEFT);
                $division_search[] = $division_search1;
            }
        }

        //find branch for region search
        $region = $req->region;
        if ($region != null) {
            $r_branch = DB::table('public.branch')
                ->where('program_id', session('program_id'))
                ->where('region_id', $region)
                ->distinct('branch_id')
                ->get();
            foreach ($r_branch as $key => $value) {
                $region_search1 = str_pad($value->branch_id, 4, "0", STR_PAD_LEFT);
                $region_search[] = $region_search1;
            }
        }
        //find branch for area search
        $area = $req->area;
        if ($area != null) {
            $area_branch = DB::table('public.branch')
                ->where('program_id', session('program_id'))
                ->where('area_id', $area)
                ->distinct('branch_id')
                ->get();

            foreach ($area_branch as $key => $value) {
                $area_search1 = str_pad($value->branch_id, 4, "0", STR_PAD_LEFT);
                $area_search[] = $area_search1;
            }
        }
        $branch_search = $req->branch;
        $branchcode_search = str_pad($branch_search, 4, "0", STR_PAD_LEFT);
        $dateForm = $req->dateFrom;
        $dateTo = $req->dateTo;
        $po_search = $req->po;
        $roll = $req->roll;

        // date make
        $month = date('m');
        $day = date('d');
        $year = date('Y');
        $today = $year . '-' . $month . '-' . $day;
        $from_date = $year . '-' . $month . '-' . '01';
        // dd($from_date,$today,$roll);
        // division & date search
        if ($division != null && $region == null && $area == null && $branch_search == null && $po_search == null && $roll == null) {
            if (session('status_btn')) {
                if (session('status_btn') == 3) {
                    $datas = DB::table($db . '.loans')
                        ->whereIn($db . '.loans.assignedpo', $po)
                        ->whereDate($db . '.loans.time', '>=', $dateForm)
                        ->whereDate($db . '.loans.time', '<=', $dateTo)
                        ->whereIn($db . '.loans.branchcode', $division_search)
                        ->where($db . '.loans.reciverrole', '!=', '0')
                        ->where($db . '.loans.projectcode', session('projectcode'))
                        ->Where(function ($query) {
                            $query->where('status', 3)
                                ->orWhere('ErpStatus', 3);
                        })->get();
                } else {
                    $datas = DB::table($db . '.loans')
                        ->whereIn($db . '.loans.assignedpo', $po)
                        ->whereDate($db . '.loans.time', '>=', $dateForm)
                        ->whereDate($db . '.loans.time', '<=', $dateTo)
                        ->whereIn($db . '.loans.branchcode', $division_search)
                        ->where($db . '.loans.status', session('status_btn'))
                        ->where($db . '.loans.reciverrole', '!=', '0')
                        ->where($db . '.loans.projectcode', session('projectcode'))
                        ->get();
                }
            }
            if (session('erpstatus_btn')) {
                $datas = DB::table($db . '.loans')
                    ->whereIn($db . '.loans.assignedpo', $po)
                    ->whereDate($db . '.loans.time', '>=', $dateForm)
                    ->whereDate($db . '.loans.time', '<=', $dateTo)
                    ->whereIn($db . '.loans.branchcode', $division_search)
                    ->where($db . '.loans.ErpStatus', session('erpstatus_btn'))
                    ->where($db . '.loans.reciverrole', '!=', '0')
                    ->where($db . '.loans.projectcode', session('projectcode'))
                    ->get();
            }

            return datatables($datas)->addColumn('branchcode', function ($datas) {
                $branch_name = '';
                $branchcode = $datas->branchcode;
                $branch_qry = DB::table('public.branch')->where('branch_id', $branchcode)->first();
                if ($branch_qry) {
                    $branch_name = $branch_qry->branch_name;
                    return $branch_name;
                }
                return $branch_name;
            })->addColumn('assignedpo', function ($datas) {
                $db = config('database.db');
                $coname = '';
                $assignedpo = $datas->assignedpo;
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                // $polist = $this->Individual_GetPO($datas->branchcode, session('projectcode'), $assignedpo);
                // $coname = $polist[0]->coname;
                return $coname;
            })->addColumn('loan_product', function ($datas) {
                $db = config('database.db');
                $productName = '';
                $loan_product = $datas->loan_product;
                if (session('projectcode') == '015') {
                    $projectcode = "15";
                } elseif (session('projectcode') == '015') {
                    $projectcode = "60";
                }
                $query = DB::table($db . '.product_project_member_category')->select('productname')->where('productid', $loan_product)->where('projectcode', $projectcode)->first();
                if ($query) {
                    $productName = $query->productname;
                    return $productName;
                }
                return $productName;
            })->addColumn('time', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->time));
                return $time;
            })->addColumn('action', function ($datas) {
                return '<a href="operation/loan-approval/' . $datas->id . '" class="btn btn-warning">View</a>';
            })->toJson();
        }
        // region & date search
        if ($region != null && $area == null && $branch_search == null && $po_search == null && $roll == null) {
            if (session('status_btn')) {
                if (session('status_btn') == 3) {
                    $datas = DB::table($db . '.loans')
                        ->whereIn($db . '.loans.assignedpo', $po)
                        ->whereDate($db . '.loans.time', '>=', $dateForm)
                        ->whereDate($db . '.loans.time', '<=', $dateTo)
                        ->whereIn($db . '.loans.branchcode', $region_search)
                        ->where($db . '.loans.reciverrole', '!=', '0')
                        ->where($db . '.loans.projectcode', session('projectcode'))
                        ->Where(function ($query) {
                            $query->where('status', 3)
                                ->orWhere('ErpStatus', 3);
                        })->get();
                } else {
                    $datas = DB::table($db . '.loans')
                        ->whereIn($db . '.loans.assignedpo', $po)
                        ->whereDate($db . '.loans.time', '>=', $dateForm)
                        ->whereDate($db . '.loans.time', '<=', $dateTo)
                        ->whereIn($db . '.loans.branchcode', $region_search)
                        ->where($db . '.loans.status', session('status_btn'))
                        ->where($db . '.loans.reciverrole', '!=', '0')
                        ->where($db . '.loans.projectcode', session('projectcode'))
                        ->get();
                }
            }
            if (session('erpstatus_btn')) {
                $datas = DB::table($db . '.loans')
                    ->whereIn($db . '.loans.assignedpo', $po)
                    ->whereDate($db . '.loans.time', '>=', $dateForm)
                    ->whereDate($db . '.loans.time', '<=', $dateTo)
                    ->whereIn($db . '.loans.branchcode', $region_search)
                    ->where($db . '.loans.ErpStatus', session('erpstatus_btn'))
                    ->where($db . '.loans.reciverrole', '!=', '0')
                    ->where($db . '.loans.projectcode', session('projectcode'))
                    ->get();
            }
            return datatables($datas)->addColumn('branchcode', function ($datas) {
                $branch_name = '';
                $branchcode = $datas->branchcode;
                $branch_qry = DB::table('public.branch')->where('branch_id', $branchcode)->first();
                if ($branch_qry) {
                    $branch_name = $branch_qry->branch_name;
                    return $branch_name;
                }
                return $branch_name;
            })->addColumn('assignedpo', function ($datas) {
                $db = config('database.db');
                $coname = '';
                $assignedpo = $datas->assignedpo;
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                /*$polist = $this->Individual_GetPO($datas->branchcode, session('projectcode'), $assignedpo);
                $coname = $polist[0]->coname;*/
                return $coname;
            })->addColumn('loan_product', function ($datas) {
                $db = config('database.db');
                $productName = '';
                $loan_product = $datas->loan_product;
                if (session('projectcode') == '015') {
                    $projectcode = "15";
                } elseif (session('projectcode') == '015') {
                    $projectcode = "60";
                }
                $query = DB::table($db . '.product_project_member_category')->select('productname')->where('productid', $loan_product)->where('projectcode', $projectcode)->first();
                if ($query) {
                    $productName = $query->productname;
                    return $productName;
                }
                return $productName;
            })->addColumn('time', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->time));
                return $time;
            })->addColumn('action', function ($datas) {
                return '<a href="operation/loan-approval/' . $datas->id . '" class="btn btn-warning">View</a>';
            })->toJson();
        }
        // area & date search
        if ($area != null && $branch_search == null && $po_search == null && $roll == null) {
            if (session('status_btn')) {
                if (session('status_btn') == 3) {
                    $datas = DB::table($db . '.loans')
                        ->whereIn($db . '.loans.assignedpo', $po)
                        ->whereDate($db . '.loans.time', '>=', $dateForm)
                        ->whereDate($db . '.loans.time', '<=', $dateTo)
                        ->whereIn($db . '.loans.branchcode', $area_search)
                        ->where($db . '.loans.reciverrole', '!=', '0')
                        ->where($db . '.loans.projectcode', session('projectcode'))
                        ->Where(function ($query) {
                            $query->where('status', 3)
                                ->orWhere('ErpStatus', 3);
                        })->get();
                } else {
                    $datas = DB::table($db . '.loans')
                        ->whereIn($db . '.loans.assignedpo', $po)
                        ->whereDate($db . '.loans.time', '>=', $dateForm)
                        ->whereDate($db . '.loans.time', '<=', $dateTo)
                        ->whereIn($db . '.loans.branchcode', $area_search)
                        ->where($db . '.loans.status', session('status_btn'))
                        ->where($db . '.loans.reciverrole', '!=', '0')
                        ->where($db . '.loans.projectcode', session('projectcode'))
                        ->get();
                }
            }
            if (session('erpstatus_btn')) {
                $datas = DB::table($db . '.loans')
                    ->whereIn($db . '.loans.assignedpo', $po)
                    ->whereDate($db . '.loans.time', '>=', $dateForm)
                    ->whereDate($db . '.loans.time', '<=', $dateTo)
                    ->whereIn($db . '.loans.branchcode', $area_search)
                    ->where($db . '.loans.ErpStatus', session('erpstatus_btn'))
                    ->where($db . '.loans.reciverrole', '!=', '0')
                    ->where($db . '.loans.projectcode', session('projectcode'))
                    ->get();
            }
            // dd($datas);
            return datatables($datas)->addColumn('branchcode', function ($datas) {
                $branch_name = '';
                $branchcode = $datas->branchcode;
                $branch_qry = DB::table('public.branch')->where('branch_id', $branchcode)->first();
                if ($branch_qry) {
                    $branch_name = $branch_qry->branch_name;
                    return $branch_name;
                }
                return $branch_name;
            })->addColumn('assignedpo', function ($datas) {
                $db = config('database.db');
                $coname = '';
                $assignedpo = $datas->assignedpo;
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                /*$polist = $this->Individual_GetPO($datas->branchcode, session('projectcode'), $assignedpo);
                $coname = $polist[0]->coname;*/
                return $coname;
            })->addColumn('loan_product', function ($datas) {
                $db = config('database.db');
                $productName = '';
                $loan_product = $datas->loan_product;
                if (session('projectcode') == '015') {
                    $projectcode = "15";
                } elseif (session('projectcode') == '015') {
                    $projectcode = "60";
                }
                $query = DB::table($db . '.product_project_member_category')->select('productname')->where('productid', $loan_product)->where('projectcode', $projectcode)->first();
                if ($query) {
                    $productName = $query->productname;
                    return $productName;
                }
                return $productName;
            })->addColumn('time', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->time));
                return $time;
            })->addColumn('action', function ($datas) {
                return '<a href="operation/loan-approval/' . $datas->id . '" class="btn btn-warning">View</a>';
            })->toJson();
        }
        // branch & date search
        if ($branch_search != null && $dateForm != null && $dateTo != null && $roll == null && $po_search == null) {
            if (session('status_btn')) {
                if (session('status_btn') == 3) {
                    $datas = DB::table($db . '.loans')
                        ->whereIn('assignedpo', $po)
                        ->whereDate('time', '>=', $dateForm)
                        ->whereDate('time', '<=', $dateTo)
                        ->where('branchcode', $branchcode_search)
                        ->where('reciverrole', '!=', '0')
                        ->where('projectcode', session('projectcode'))
                        ->Where(function ($query) {
                            $query->where('status', 3)
                                ->orWhere('ErpStatus', 3);
                        })->get();
                } else {
                    $datas = DB::table($db . '.loans')
                        ->whereIn($db . '.loans.assignedpo', $po)
                        ->whereDate($db . '.loans.time', '>=', $dateForm)
                        ->whereDate($db . '.loans.time', '<=', $dateTo)
                        ->where($db . '.loans.branchcode', $branchcode_search)
                        ->where($db . '.loans.status', session('status_btn'))
                        ->where($db . '.loans.reciverrole', '!=', '0')
                        ->where($db . '.loans.projectcode', session('projectcode'))
                        ->get();
                }
            } else if (session('erpstatus_btn')) {
                $datas = DB::table($db . '.loans')
                    ->whereIn($db . '.loans.assignedpo', $po)
                    ->whereDate($db . '.loans.time', '>=', $dateForm)
                    ->whereDate($db . '.loans.time', '<=', $dateTo)
                    ->where($db . '.loans.branchcode', $branchcode_search)
                    ->where($db . '.loans.ErpStatus', session('erpstatus_btn'))
                    ->where($db . '.loans.reciverrole', '!=', '0')
                    ->where($db . '.loans.projectcode', session('projectcode'))
                    ->get();
            }

            return datatables($datas)->addColumn('branchcode', function ($datas) {
                $branch_name = '';
                $branchcode = $datas->branchcode;
                $branch_qry = DB::table('public.branch')->where('branch_id', $branchcode)->first();
                if ($branch_qry) {
                    $branch_name = $branch_qry->branch_name . '(' . $datas->branchcode . ')';
                    return $branch_name;
                }
                return $branch_name;
            })->addColumn('assignedpo', function ($datas) {
                $db = config('database.db');
                $coname = '';
                $assignedpo = $datas->assignedpo;
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                /* $polist = $this->Individual_GetPO($datas->branchcode, session('projectcode'), $assignedpo);
                $coname = $polist[0]->coname;*/
                return $coname;
            })->addColumn('loan_product', function ($datas) {
                $db = config('database.db');
                $productName = '';
                $loan_product = $datas->loan_product;
                if (session('projectcode') == '015') {
                    $projectcode = "15";
                } elseif (session('projectcode') == '015') {
                    $projectcode = "60";
                }
                $query = DB::table($db . '.product_project_member_category')->select('productname')->where('productid', $loan_product)->where('projectcode', $projectcode)->first();
                if ($query) {
                    $productName = $query->productname;
                    return $productName;
                }
                return $productName;
            })->addColumn('time', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->time));
                return $time;
            })->addColumn('action', function ($datas) {
                return '<a href="operation/loan-approval/' . $datas->id . '" class="btn btn-warning">View</a>';
            })->toJson();
        }
        // po & date
        if ($roll == null && $dateForm != null && $dateTo != null && $po_search != null) {
            if (session('status_btn')) {
                if (session('status_btn') == 3) {
                    $datas = DB::table($db . '.loans')
                        ->whereIn($db . '.loans.assignedpo', $po)
                        ->whereDate($db . '.loans.time', '>=', $dateForm)
                        ->whereDate($db . '.loans.time', '<=', $dateTo)
                        ->where($db . '.loans.assignedpo', $po_search)
                        ->where($db . '.loans.reciverrole', '!=', '0')
                        ->where($db . '.loans.projectcode', session('projectcode'))
                        ->Where(function ($query) {
                            $query->where('status', 3)
                                ->orWhere('ErpStatus', 3);
                        })->get();
                } else {
                    $datas = DB::table($db . '.loans')
                        ->whereIn($db . '.loans.assignedpo', $po)
                        ->whereDate($db . '.loans.time', '>=', $dateForm)
                        ->whereDate($db . '.loans.time', '<=', $dateTo)
                        ->where($db . '.loans.assignedpo', $po_search)
                        ->where($db . '.loans.reciverrole', '!=', '0')
                        ->where($db . '.loans.status', session('status_btn'))
                        ->where($db . '.loans.projectcode', session('projectcode'))
                        ->get();
                }
            }
            if (session('erpstatus_btn')) {
                $datas = DB::table($db . '.loans')
                    ->whereIn($db . '.loans.assignedpo', $po)
                    ->whereDate($db . '.loans.time', '>=', $dateForm)
                    ->whereDate($db . '.loans.time', '<=', $dateTo)
                    ->where($db . '.loans.assignedpo', $po_search)
                    ->where($db . '.loans.reciverrole', '!=', '0')
                    ->where($db . '.loans.ErpStatus', session('erpstatus_btn'))
                    ->where($db . '.loans.projectcode', session('projectcode'))
                    ->get();
            }

            return datatables($datas)->addColumn('branchcode', function ($datas) {
                $branch_name = '';
                $branchcode = $datas->branchcode;
                $branch_qry = DB::table('public.branch')->where('branch_id', $branchcode)->first();
                if ($branch_qry) {
                    $branch_name = $branch_qry->branch_name;
                    return $branch_name;
                }
                return $branch_name;
            })->editColumn('time', function ($datas) {
                return date('d-m-Y', strtotime($datas->time));
            })->addColumn('assignedpo', function ($datas) {
                $db = config('database.db');
                $coname = '';
                $assignedpo = $datas->assignedpo;
                /* $polist = $this->Individual_GetPO($datas->branchcode, session('projectcode'), $assignedpo);
                $coname = $polist[0]->coname;*/
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                return $coname;
            })->addColumn('loan_product', function ($datas) {
                $db = config('database.db');
                $productName = '';
                $loan_product = $datas->loan_product;
                if (session('projectcode') == '015') {
                    $projectcode = "15";
                } elseif (session('projectcode') == '015') {
                    $projectcode = "60";
                }
                $query = DB::table($db . '.product_project_member_category')->select('productname')->where('productid', $loan_product)->where('projectcode', $projectcode)->first();
                if ($query) {
                    $productName = $query->productname;
                    return $productName;
                }
                return $productName;
            })->addColumn('time', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->time));
                return $time;
            })->addColumn('action', function ($datas) {
                return '<a href="operation/loan-approval/' . $datas->id . '" class="btn btn-warning">View</a>';
            })->make(true);
        }
        // date search
        if ($dateForm != null && $dateTo != null && $roll == null && $branch_search == null && $po_search == null) {

            if (session('status_btn')) {
                if (session('status_btn') == 3) {
                    $datas = DB::table($db . '.loans')
                        ->whereIn($db . '.loans.assignedpo', $po)
                        ->whereDate($db . '.loans.time', '>=', $dateForm)
                        ->whereDate($db . '.loans.time', '<=', $dateTo)
                        ->where($db . '.loans.projectcode', session('projectcode'))
                        ->where($db . '.loans.reciverrole', '!=', '0')
                        ->Where(function ($query) {
                            $query->where('status', 3)
                                ->orWhere('ErpStatus', 3);
                        })->get();
                } else {
                    $datas = DB::table($db . '.loans')
                        ->whereIn($db . '.loans.assignedpo', $po)
                        ->whereDate($db . '.loans.time', '>=', $dateForm)
                        ->whereDate($db . '.loans.time', '<=', $dateTo)
                        ->where($db . '.loans.projectcode', session('projectcode'))
                        ->where($db . '.loans.reciverrole', '!=', '0')
                        ->where($db . '.loans.status', session('status_btn'))
                        ->get();
                }
            } else if (session('erpstatus_btn')) {
                $datas = DB::table($db . '.loans')
                    ->whereIn($db . '.loans.assignedpo', $po)
                    ->whereDate($db . '.loans.time', '>=', $dateForm)
                    ->whereDate($db . '.loans.time', '<=', $dateTo)
                    ->where($db . '.loans.projectcode', session('projectcode'))
                    ->where($db . '.loans.reciverrole', '!=', '0')
                    ->where($db . '.loans.ErpStatus', session('erpstatus_btn'))
                    ->get();
            }

            return datatables($datas)->addColumn('branchcode', function ($datas) {
                $branch_name = '';
                $branchcode = $datas->branchcode;
                $branch_qry = DB::table('public.branch')->where('branch_id', $branchcode)->first();
                if ($branch_qry) {
                    $branch_name = $branch_qry->branch_name;
                    return $branch_name;
                }
                return $branch_name;
            })->addColumn('assignedpo', function ($datas) {
                $db = config('database.db');
                $coname = '';
                $assignedpo = $datas->assignedpo;
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                /* $polist = $this->Individual_GetPO($datas->branchcode, session('projectcode'), $assignedpo);
                $coname = $polist[0]->coname;*/
                return $coname;
            })->addColumn('loan_product', function ($datas) {
                $db = config('database.db');
                $productName = '';
                $loan_product = $datas->loan_product;
                if (session('projectcode') == '015') {
                    $projectcode = "15";
                } elseif (session('projectcode') == '015') {
                    $projectcode = "60";
                }
                $query = DB::table($db . '.product_project_member_category')->select('productname')->where('productid', $loan_product)->where('projectcode', $projectcode)->first();
                if ($query) {
                    $productName = $query->productname;
                    return $productName;
                }
                return $productName;
            })->addColumn('time', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->time));
                return $time;
            })->addColumn('action', function ($datas) {
                return '<a href="operation/loan-approval/' . $datas->id . '" class="btn btn-warning">View</a>';
            })->toJson();
        }
        // area & date & status & roll search
        if ($area != null && $branch_search == null && $po_search == null && $roll != null) {
            if (session('status_btn')) {
                if (session('status_btn') == 3) {
                    $datas = DB::table($db . '.loans')
                        ->whereIn($db . '.loans.assignedpo', $po)
                        ->whereDate($db . '.loans.time', '>=', $dateForm)
                        ->whereDate($db . '.loans.time', '<=', $dateTo)
                        ->whereIn($db . '.loans.branchcode', $area_search)
                        ->where($db . '.loans.reciverrole', $roll)
                        ->where($db . '.loans.reciverrole', '!=', '0')
                        ->where($db . '.loans.projectcode', session('projectcode'))
                        ->Where(function ($query) {
                            $query->where('status', 3)
                                ->orWhere('ErpStatus', 3);
                        })->get();
                } else {
                    $datas = DB::table($db . '.loans')
                        ->whereIn($db . '.loans.assignedpo', $po)
                        ->whereDate($db . '.loans.time', '>=', $dateForm)
                        ->whereDate($db . '.loans.time', '<=', $dateTo)
                        ->whereIn($db . '.loans.branchcode', $area_search)
                        ->where($db . '.loans.reciverrole', $roll)
                        ->where($db . '.loans.reciverrole', '!=', '0')
                        ->where($db . '.loans.status', session('status_btn'))
                        ->where($db . '.loans.projectcode', session('projectcode'))
                        ->get();
                }
            }
            if (session('erpstatus_btn')) {
                $datas = DB::table($db . '.loans')
                    ->whereIn($db . '.loans.assignedpo', $po)
                    ->whereDate($db . '.loans.time', '>=', $dateForm)
                    ->whereDate($db . '.loans.time', '<=', $dateTo)
                    ->whereIn($db . '.loans.branchcode', $area_search)
                    ->where($db . '.loans.reciverrole', $roll)
                    ->where($db . '.loans.reciverrole', '!=', '0')
                    ->where($db . '.loans.ErpStatus', session('erpstatus_btn'))
                    ->where($db . '.loans.projectcode', session('projectcode'))
                    ->get();
            }

            return datatables($datas)->addColumn('branchcode', function ($datas) {
                $branch_name = '';
                $branchcode = $datas->branchcode;
                $branch_qry = DB::table('public.branch')->where('branch_id', $branchcode)->first();
                if ($branch_qry) {
                    $branch_name = $branch_qry->branch_name;
                    return $branch_name;
                }
                return $branch_name;
            })->addColumn('assignedpo', function ($datas) {
                $db = config('database.db');
                $coname = '';
                $assignedpo = $datas->assignedpo;
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                /* $polist = $this->Individual_GetPO($datas->branchcode, session('projectcode'), $assignedpo);
                $coname = $polist[0]->coname;*/
                return $coname;
            })->addColumn('loan_product', function ($datas) {
                $db = config('database.db');
                $productName = '';
                $loan_product = $datas->loan_product;
                if (session('projectcode') == '015') {
                    $projectcode = "15";
                } elseif (session('projectcode') == '015') {
                    $projectcode = "60";
                }
                $query = DB::table($db . '.product_project_member_category')->select('productname')->where('productid', $loan_product)->where('projectcode', $projectcode)->first();
                if ($query) {
                    $productName = $query->productname;
                    return $productName;
                }
                return $productName;
            })->addColumn('time', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->time));
                return $time;
            })->addColumn('action', function ($datas) {
                return '<a href="operation/loan-approval/' . $datas->id . '" class="btn btn-warning">View</a>';
            })->toJson();
        }
        // region & date & status & roll search
        if ($region != null && $area == null && $branch_search == null && $po_search == null && $roll != null) {
            if (session('status_btn')) {
                if (session('status_btn') == 3) {
                    $datas = DB::table($db . '.loans')
                        ->whereIn($db . '.loans.assignedpo', $po)
                        ->whereDate($db . '.loans.time', '>=', $dateForm)
                        ->whereDate($db . '.loans.time', '<=', $dateTo)
                        ->whereIn($db . '.loans.branchcode', $region_search)
                        ->where($db . '.loans.reciverrole', $roll)
                        ->where($db . '.loans.reciverrole', '!=', '0')
                        ->where($db . '.loans.projectcode', session('projectcode'))
                        ->Where(function ($query) {
                            $query->where('status', 3)
                                ->orWhere('ErpStatus', 3);
                        })->get();
                } else {
                    $datas = DB::table($db . '.loans')
                        ->whereIn($db . '.loans.assignedpo', $po)
                        ->whereDate($db . '.loans.time', '>=', $dateForm)
                        ->whereDate($db . '.loans.time', '<=', $dateTo)
                        ->whereIn($db . '.loans.branchcode', $region_search)
                        ->where($db . '.loans.reciverrole', $roll)
                        ->where($db . '.loans.reciverrole', '!=', '0')
                        ->where($db . '.loans.status', session('status_btn'))
                        ->where($db . '.loans.projectcode', session('projectcode'))
                        ->get();
                }
            } else if (session('erpstatus_btn')) {
                $datas = DB::table($db . '.loans')
                    ->whereIn($db . '.loans.assignedpo', $po)
                    ->whereDate($db . '.loans.time', '>=', $dateForm)
                    ->whereDate($db . '.loans.time', '<=', $dateTo)
                    ->whereIn($db . '.loans.branchcode', $region_search)
                    ->where($db . '.loans.reciverrole', $roll)
                    ->where($db . '.loans.reciverrole', '!=', '0')
                    ->where($db . '.loans.ErpStatus', session('erpstatus_btn'))
                    ->where($db . '.loans.projectcode', session('projectcode'))
                    ->get();
            }
            return datatables($datas)->addColumn('branchcode', function ($datas) {
                $branch_name = '';
                $branchcode = $datas->branchcode;
                $branch_qry = DB::table('public.branch')->where('branch_id', $branchcode)->first();
                if ($branch_qry) {
                    $branch_name = $branch_qry->branch_name;
                    return $branch_name;
                }
                return $branch_name;
            })->addColumn('assignedpo', function ($datas) {
                $db = config('database.db');
                $coname = '';
                $assignedpo = $datas->assignedpo;
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                /* $polist = $this->Individual_GetPO($datas->branchcode, session('projectcode'), $assignedpo);
                $coname = $polist[0]->coname;*/
                return $coname;
            })->addColumn('loan_product', function ($datas) {
                $db = config('database.db');
                $productName = '';
                $loan_product = $datas->loan_product;
                if (session('projectcode') == '015') {
                    $projectcode = "15";
                } elseif (session('projectcode') == '015') {
                    $projectcode = "60";
                }
                $query = DB::table($db . '.product_project_member_category')->select('productname')->where('productid', $loan_product)->where('projectcode', $projectcode)->first();
                if ($query) {
                    $productName = $query->productname;
                    return $productName;
                }
                return $productName;
            })->addColumn('time', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->time));
                return $time;
            })->addColumn('action', function ($datas) {
                return '<a href="operation/loan-approval/' . $datas->id . '" class="btn btn-warning">View</a>';
            })->toJson();
        }
        // division & date & status & roll search
        if ($division != null && $region == null && $area == null && $branch_search == null && $po_search == null && $roll != null) {
            if (session('status_btn')) {
                if (session('status_btn') == 3) {
                    $datas = DB::table($db . '.loans')
                        ->whereIn($db . '.loans.assignedpo', $po)
                        ->whereDate($db . '.loans.time', '>=', $dateForm)
                        ->whereDate($db . '.loans.time', '<=', $dateTo)
                        ->whereIn($db . '.loans.branchcode', $division_search)
                        ->where($db . '.loans.reciverrole', $roll)
                        ->where($db . '.loans.reciverrole', '!=', '0')
                        ->where($db . '.loans.projectcode', session('projectcode'))
                        ->Where(function ($query) {
                            $query->where('status', 3)
                                ->orWhere('ErpStatus', 3);
                        })->get();
                } else {
                    $datas = DB::table($db . '.loans')
                        ->whereIn($db . '.loans.assignedpo', $po)
                        ->whereDate($db . '.loans.time', '>=', $dateForm)
                        ->whereDate($db . '.loans.time', '<=', $dateTo)
                        ->whereIn($db . '.loans.branchcode', $division_search)
                        ->where($db . '.loans.reciverrole', $roll)
                        ->where($db . '.loans.reciverrole', '!=', '0')
                        ->where($db . '.loans.status', session('status_btn'))
                        ->where($db . '.loans.projectcode', session('projectcode'))
                        ->get();
                }
            } else if (session('erpstatus_btn')) {
                $datas = DB::table($db . '.loans')
                    ->whereIn($db . '.loans.assignedpo', $po)
                    ->whereDate($db . '.loans.time', '>=', $dateForm)
                    ->whereDate($db . '.loans.time', '<=', $dateTo)
                    ->whereIn($db . '.loans.branchcode', $division_search)
                    ->where($db . '.loans.reciverrole', $roll)
                    ->where($db . '.loans.reciverrole', '!=', '0')
                    ->where($db . '.loans.ErpStatus', session('erpstatus_btn'))
                    ->where($db . '.loans.projectcode', session('projectcode'))
                    ->get();
            }

            return datatables($datas)->addColumn('branchcode', function ($datas) {
                $branch_name = '';
                $branchcode = $datas->branchcode;
                $branch_qry = DB::table('public.branch')->where('branch_id', $branchcode)->first();
                if ($branch_qry) {
                    $branch_name = $branch_qry->branch_name;
                    return $branch_name;
                }
                return $branch_name;
            })->addColumn('assignedpo', function ($datas) {
                $db = config('database.db');
                $coname = '';
                $assignedpo = $datas->assignedpo;
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                /* $polist = $this->Individual_GetPO($datas->branchcode, session('projectcode'), $assignedpo);
                $coname = $polist[0]->coname;*/
                return $coname;
            })->addColumn('loan_product', function ($datas) {
                $db = config('database.db');
                $productName = '';
                $loan_product = $datas->loan_product;
                if (session('projectcode') == '015') {
                    $projectcode = "15";
                } elseif (session('projectcode') == '015') {
                    $projectcode = "60";
                }
                $query = DB::table($db . '.product_project_member_category')->select('productname')->where('productid', $loan_product)->where('projectcode', $projectcode)->first();
                if ($query) {
                    $productName = $query->productname;
                    return $productName;
                }
                return $productName;
            })->addColumn('time', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->time));
                return $time;
            })->addColumn('action', function ($datas) {
                return '<a href="operation/loan-approval/' . $datas->id . '" class="btn btn-warning">View</a>';
            })->toJson();
        }
        // branch & date & status & roll search
        if ($branch_search != null  && $roll != null && $dateForm != null && $dateTo != null && $po_search == null) {
            if (session('status_btn')) {
                if (session('status_btn') == 3) {
                    $datas = DB::table($db . '.loans')
                        ->whereIn($db . '.loans.assignedpo', $po)
                        ->whereDate($db . '.loans.time', '>=', $dateForm)
                        ->whereDate($db . '.loans.time', '<=', $dateTo)
                        ->where($db . '.loans.branchcode', $branchcode_search)
                        ->where($db . '.loans.reciverrole', $roll)
                        ->where($db . '.loans.reciverrole', '!=', '0')
                        ->where($db . '.loans.projectcode', session('projectcode'))
                        ->Where(function ($query) {
                            $query->where('status', 3)
                                ->orWhere('ErpStatus', 3);
                        })->get();
                } else {
                    $datas = DB::table($db . '.loans')
                        ->whereIn($db . '.loans.assignedpo', $po)
                        ->whereDate($db . '.loans.time', '>=', $dateForm)
                        ->whereDate($db . '.loans.time', '<=', $dateTo)
                        ->where($db . '.loans.branchcode', $branchcode_search)
                        ->where($db . '.loans.reciverrole', $roll)
                        ->where($db . '.loans.status', session('status_btn'))
                        ->where($db . '.loans.reciverrole', '!=', '0')
                        ->where($db . '.loans.projectcode', session('projectcode'))
                        ->get();
                }
            } else if (session('erpstatus_btn')) {
                $datas = DB::table($db . '.loans')
                    ->whereIn($db . '.loans.assignedpo', $po)
                    ->whereDate($db . '.loans.time', '>=', $dateForm)
                    ->whereDate($db . '.loans.time', '<=', $dateTo)
                    ->where($db . '.loans.branchcode', $branchcode_search)
                    ->where($db . '.loans.reciverrole', $roll)
                    ->where($db . '.loans.reciverrole', '!=', '0')
                    ->where($db . '.loans.ErpStatus', session('erpstatus_btn'))
                    ->where($db . '.loans.projectcode', session('projectcode'))
                    ->get();
            }
            return datatables($datas)->addColumn('branchcode', function ($datas) {
                $branch_name = '';
                $branchcode = $datas->branchcode;
                $branch_qry = DB::table('public.branch')->where('branch_id', $branchcode)->first();
                if ($branch_qry) {
                    $branch_name = $branch_qry->branch_name;
                    return $branch_name;
                }
                return $branch_name;
            })->editColumn('time', function ($datas) {
                return date('d-m-Y', strtotime($datas->time));
            })->addColumn('assignedpo', function ($datas) {
                $db = config('database.db');
                $coname = '';
                $assignedpo = $datas->assignedpo;

                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                /*$polist = $this->Individual_GetPO($datas->branchcode, session('projectcode'), $assignedpo);
                $coname = $polist[0]->coname;*/
                return $coname;
            })->addColumn('loan_product', function ($datas) {
                $db = config('database.db');
                $productName = '';
                $loan_product = $datas->loan_product;
                if (session('projectcode') == '015') {
                    $projectcode = "15";
                } elseif (session('projectcode') == '015') {
                    $projectcode = "60";
                }
                $query = DB::table($db . '.product_project_member_category')->select('productname')->where('productid', $loan_product)->where('projectcode', $projectcode)->first();
                if ($query) {
                    $productName = $query->productname;
                    return $productName;
                }
                return $productName;
            })->addColumn('time', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->time));
                return $time;
            })->addColumn('action', function ($datas) {
                return '<a href="operation/loan-approval/' . $datas->id . '" class="btn btn-warning">View</a>';
            })->make(true);
        }
        // date & status search
        if ($dateForm != null && $dateTo != null && $roll != null && $po_search == null && $branch_search == null) {
            if (session('status_btn')) {
                if (session('status_btn') == 3) {
                    $datas = DB::table($db . '.loans')
                        ->whereIn($db . '.loans.assignedpo', $po)
                        ->whereDate($db . '.loans.time', '>=', $dateForm)
                        ->whereDate($db . '.loans.time', '<=', $dateTo)
                        ->where($db . '.loans.reciverrole', $roll)
                        ->where($db . '.loans.status', 3)
                        ->orWhere($db . '.loans.ErpStatus', 3)
                        ->where($db . '.loans.reciverrole', '!=', '0')
                        ->where($db . '.loans.projectcode', session('projectcode'))
                        ->Where(function ($query) {
                            $query->where('status', 3)
                                ->orWhere('ErpStatus', 3);
                        })->get();
                } else {
                    $datas = DB::table($db . '.loans')
                        ->whereIn($db . '.loans.assignedpo', $po)
                        ->whereDate($db . '.loans.time', '>=', $dateForm)
                        ->whereDate($db . '.loans.time', '<=', $dateTo)
                        ->where($db . '.loans.reciverrole', $roll)
                        ->where($db . '.loans.status', session('status_btn'))
                        ->where($db . '.loans.reciverrole', '!=', '0')
                        ->where($db . '.loans.projectcode', session('projectcode'))
                        ->get();
                }
            } else if (session('erpstatus_btn')) {
                $datas = DB::table($db . '.loans')
                    ->whereIn($db . '.loans.assignedpo', $po)
                    ->whereDate($db . '.loans.time', '>=', $dateForm)
                    ->whereDate($db . '.loans.time', '<=', $dateTo)
                    ->where($db . '.loans.reciverrole', $roll)
                    ->where($db . '.loans.reciverrole', '!=', '0')
                    ->where($db . '.loans.ErpStatus', session('erpstatus_btn'))
                    ->where($db . '.loans.projectcode', session('projectcode'))
                    ->get();
            }

            return datatables($datas)->addColumn('branchcode', function ($datas) {
                $branch_name = '';
                $branchcode = $datas->branchcode;
                $branch_qry = DB::table('public.branch')->where('branch_id', $branchcode)->first();
                if ($branch_qry) {
                    $branch_name = $branch_qry->branch_name;
                    return $branch_name;
                }
                return $branch_name;
            })->addColumn('assignedpo', function ($datas) {
                $db = config('database.db');
                $coname = '';
                $assignedpo = $datas->assignedpo;
                /*$polist = $this->Individual_GetPO($datas->branchcode, session('projectcode'), $assignedpo);
                $coname = $polist[0]->coname;*/
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                return $coname;
            })->addColumn('loan_product', function ($datas) {
                $db = config('database.db');
                $productName = '';
                $loan_product = $datas->loan_product;
                if (session('projectcode') == '015') {
                    $projectcode = "15";
                } elseif (session('projectcode') == '015') {
                    $projectcode = "60";
                }
                $query = DB::table($db . '.product_project_member_category')->select('productname')->where('productid', $loan_product)->where('projectcode', $projectcode)->first();
                if ($query) {
                    $productName = $query->productname;
                    return $productName;
                }
                return $productName;
            })->addColumn('time', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->time));
                return $time;
            })->addColumn('action', function ($datas) {
                return '<a href="operation/loan-approval/' . $datas->id . '" class="btn btn-warning">View</a>';
            })->toJson();
        }

        // po & date & status
        if ($branch_search != null  && $roll != null && $dateForm != null && $dateTo != null && $po_search != null) {
            if (session('status_btn')) {
                if (session('status_btn') == 3) {
                    $datas = DB::table($db . '.loans')
                        ->whereIn($db . '.loans.assignedpo', $po)
                        ->whereDate($db . '.loans.time', '>=', $dateForm)
                        ->whereDate($db . '.loans.time', '<=', $dateTo)
                        ->where($db . '.loans.reciverrole', $roll)
                        ->where($db . '.loans.assignedpo', $po_search)
                        ->where($db . '.loans.status', 3)
                        ->orWhere($db . '.loans.ErpStatus', 3)
                        ->where($db . '.loans.reciverrole', '!=', '0')
                        ->where($db . '.loans.projectcode', session('projectcode'))
                        ->Where(function ($query) {
                            $query->where('status', 3)
                                ->orWhere('ErpStatus', 3);
                        })->get();
                } else {
                    $datas = DB::table($db . '.loans')
                        ->whereIn($db . '.loans.assignedpo', $po)
                        ->whereDate($db . '.loans.time', '>=', $dateForm)
                        ->whereDate($db . '.loans.time', '<=', $dateTo)
                        ->where($db . '.loans.reciverrole', $roll)
                        ->where($db . '.loans.assignedpo', $po_search)
                        ->where($db . '.loans.status', session('status_btn'))
                        ->where($db . '.loans.reciverrole', '!=', '0')
                        ->where($db . '.loans.projectcode', session('projectcode'))
                        ->get();
                }
            } else if (session('erpstatus_btn')) {
                $datas = DB::table($db . '.loans')
                    ->whereIn($db . '.loans.assignedpo', $po)
                    ->whereDate($db . '.loans.time', '>=', $dateForm)
                    ->whereDate($db . '.loans.time', '<=', $dateTo)
                    ->where($db . '.loans.reciverrole', $roll)
                    ->where($db . '.loans.assignedpo', $po_search)
                    ->where($db . '.loans.reciverrole', '!=', '0')
                    ->where($db . '.loans.ErpStatus', session('erpstatus_btn'))
                    ->where($db . '.loans.projectcode', session('projectcode'))
                    ->get();
            }
            return datatables($datas)->addColumn('branchcode', function ($datas) {
                $branch_name = '';
                $branchcode = $datas->branchcode;
                $branch_qry = DB::table('public.branch')->where('branch_id', $branchcode)->first();
                if ($branch_qry) {
                    $branch_name = $branch_qry->branch_name;
                    return $branch_name;
                }
                return $branch_name;
            })->editColumn('time', function ($datas) {
                return date('d-m-Y', strtotime($datas->time));
            })->addColumn('assignedpo', function ($datas) {
                $db = config('database.db');
                $coname = '';
                $assignedpo = $datas->assignedpo;
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                /*$polist = $this->Individual_GetPO($datas->branchcode, session('projectcode'), $assignedpo);
                $coname = $polist[0]->coname;*/
                return $coname;
            })->addColumn('loan_product', function ($datas) {
                $db = config('database.db');
                $productName = '';
                $loan_product = $datas->loan_product;
                if (session('projectcode') == '015') {
                    $projectcode = "15";
                } elseif (session('projectcode') == '015') {
                    $projectcode = "60";
                }
                $query = DB::table($db . '.product_project_member_category')->select('productname')->where('productid', $loan_product)->where('projectcode', $projectcode)->first();
                if ($query) {
                    $productName = $query->productname;
                    return $productName;
                }
                return $productName;
            })->addColumn('time', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->time));
                return $time;
            })->addColumn('action', function ($datas) {
                return '<a href="operation/loan-approval/' . $datas->id . '" class="btn btn-warning">View</a>';
            })->make(true);
        }
        // for superadmin
        if (session('role_designation') == 'HO') {
            $datas = DB::table($db . '.loans')
                ->whereIn($db . '.loans.assignedpo', $po)
                ->whereDate($db . '.loans.time', '>=', $from_date)
                ->whereDate($db . '.loans.time', '<=', $today)
                ->where($db . '.loans.projectcode', session('projectcode'))
                ->where($db . '.loans.status', '1')
                ->where($db . '.loans.reciverrole', '!=', '0')
                // ->whereDate($db.'.loans.time', Carbon::today())
                ->get();
            return datatables($datas)->addColumn('branchcode', function ($datas) {
                $branch_name = '';
                $branchcode = $datas->branchcode;
                $branch_qry = DB::table('public.branch')->where('branch_id', $branchcode)->first();
                if ($branch_qry) {
                    $branch_name = $branch_qry->branch_name . '(' . $datas->branchcode . ')';
                    return $branch_name;
                }
                return $branch_name;
            })->addColumn('assignedpo', function ($datas) {
                $db = config('database.db');
                $coname = '';
                $assignedpo = $datas->assignedpo;
                /* $polist = $this->Individual_GetPO($datas->branchcode, session('projectcode'), $assignedpo);
                $coname = $polist[0]->coname;*/
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                return $coname;
            })->addColumn('loan_product', function ($datas) {
                $db = config('database.db');
                $productName = '';
                $loan_product = $datas->loan_product;
                if (session('projectcode') == '015') {
                    $projectcode = "15";
                } elseif (session('projectcode') == '015') {
                    $projectcode = "60";
                }
                $query = DB::table($db . '.product_project_member_category')->select('productname')->where('productid', $loan_product)->where('projectcode', $projectcode)->first();
                if ($query) {
                    $productName = $query->productname;
                    return $productName;
                }
                return $productName;
            })->addColumn('time', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->time));
                return $time;
            })->addColumn('action', function ($datas) {
                return '<a href="operation/loan-approval/' . $datas->id . '" class="btn btn-warning">View</a>';
            })->toJson();
        }
        // for other role
        if ((session('role_designation') != 'HO') or (session('role_designation') != 'PH')) {

            $datas = DB::table($db . '.loans')
                ->whereIn($db . '.loans.assignedpo', $po)
                ->whereDate($db . '.loans.time', '>=', $from_date)
                ->whereDate($db . '.loans.time', '<=', $today)
                ->where($db . '.loans.projectcode', session('projectcode'))
                ->where($db . '.loans.status', '1')
                ->where($db . '.loans.reciverrole', '!=', '0')
                // ->where($db . '.loans.reciverrole', session('roll'))
                // ->whereDate($db.'.loans.time', Carbon::today())
                ->get();
            return datatables($datas)->addColumn('branchcode', function ($datas) {
                $branch_name = '';
                $branchcode = $datas->branchcode;
                $branch_qry = DB::table('public.branch')->where('branch_id', $branchcode)->first();
                if ($branch_qry) {
                    $branch_name = $branch_qry->branch_name;
                    return $branch_name;
                }
                return $branch_name;
            })->addColumn('assignedpo', function ($datas) {
                $db = config('database.db');
                $coname = '';
                $assignedpo = $datas->assignedpo;
                /* $polist = $this->Individual_GetPO($datas->branchcode, session('projectcode'), $assignedpo);
                $coname = $polist[0]->coname;*/
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                return $coname;
            })->addColumn('loan_product', function ($datas) {
                $db = config('database.db');
                $productName = '';
                $loan_product = $datas->loan_product;
                if (session('projectcode') == '015') {
                    $projectcode = "15";
                } elseif (session('projectcode') == '015') {
                    $projectcode = "60";
                }
                $query = DB::table($db . '.product_project_member_category')->select('productname')->where('productid', $loan_product)->where('projectcode', $projectcode)->first();
                if ($query) {
                    $productName = $query->productname;
                    return $productName;
                }
                return $productName;
            })->addColumn('time', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->time));
                return $time;
            })->addColumn('action', function ($datas) {
                return '<a href="operation/loan-approval/' . $datas->id . '" class="btn btn-warning">View</a>';
            })->toJson();
        }
    }
    public function Individual_GetPO($branchcode, $projectcode, $cono)
    {
        $url = "http://scm.brac.net/sc/Individual_PO?branchcode=$branchcode&projectcode=$projectcode&cono=$cono";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $documentoutput = curl_exec($ch);
        curl_close($ch);

        $polistdecode = json_decode($documentoutput);
        $dataset = $polistdecode->data;
        if (!empty($dataset)) {
            return $dataset;
        } else {
            return $dataset;
        }
    }

    // Sagor Start
    public function GetPendingData(Request $request)
    {
        $today = date('Y-m-d');
        $from_date = date('Y-01-01');

        $all_pending_loan =
        DB::table('dcs.loans')
        ->where('reciverrole', '!=', '0')
        ->where('status', 1)
        ->where('projectcode', session('projectcode'))
        ->whereDate('loans.time', '>=', $from_date)
        ->whereDate('loans.time', '<=', $today)
        ->get();

        echo json_encode($all_pending_loan);
    }

    public function GetApproveLoanData(Request $request)
    {
        $today = date('Y-m-d');
        $from_date = date('Y-01-01');

        $all_approve_loan =
        DB::table('dcs.loans')
        ->where('reciverrole', '!=', '0')
            ->where('ErpStatus', 1)
            ->where('projectcode', session('projectcode'))
            ->whereDate('loans.time', '>=', $from_date)
            ->whereDate('loans.time', '<=', $today)
            ->get();
        echo json_encode($all_approve_loan);
    }
    public function GetReadyForDisburseLoanData(Request $request)
    {
        $today = date('Y-m-d');
        $from_date = date('Y-01-01');
        $all_readyfordisbursement_loan =
         DB::table('dcs.loans')
        ->where('reciverrole', '!=', '0')
        ->where('ErpStatus', 2)
        ->where('projectcode', session('projectcode'))
        ->whereDate('loans.time', '>=', $from_date)
        ->whereDate('loans.time', '<=', $today)
        ->get();

        echo json_encode($all_readyfordisbursement_loan);
    }
    public function GetDisburseLoanData(Request $request)
    {
        $today = date('Y-m-d');
        $from_date = date('Y-01-01');
        $all_disburse_loan =
            DB::table('dcs.loans')
            ->where('reciverrole', '!=', '0')
            ->where('ErpStatus', 4)
            ->where('projectcode', session('projectcode'))
            ->whereDate('loans.time', '>=', $from_date)
            ->whereDate('loans.time', '<=', $today)
            ->get();

        echo json_encode($all_disburse_loan);
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
        // dd($from_date);

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
            //dd(session('program_id'));
            $search2 = DB::Table('public.branch')->where('program_id', session('program_id'))->get();
            // $search2= Branch::where(['program_id' => session('program_id')])->get();
            //dd($search2);
        } else {
            return redirect()->back()->with('error', 'Data does not match.');
        }
        //dd($search2);
        foreach ($search2 as $branch) {
            $branchCode = str_pad($branch->branch_id, 4, "0", STR_PAD_LEFT);
            $branchcode[] = $branchCode;
        }
        //dd($branchcode);

        if (!empty($branchcode)) {

            $pending_admission = Admission::select(DB::raw("COUNT(*) as count"))->where('projectcode', session('projectcode'))->where('Flag', 1)->whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $today)->where('reciverrole', '!=', '0')->first();
            $pending_profileadmission = Admission::select(DB::raw("COUNT(*) as count"))->where('projectcode', session('projectcode'))->where('Flag', 2)->whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $today)->where('reciverrole', '!=', '0')->first();;
            $pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('projectcode', session('projectcode'))->whereDate($db . '.loans.time', '>=', $from_date)->whereDate($db . '.loans.time', '<=', $today)->where('reciverrole', '!=', '0')->first();
            // status wise count**********************
            $all_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('status', '1')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '!=', '0')->whereDate($db . '.loans.time', '>=', $from_date)
                ->whereDate($db . '.loans.time', '<=', $today)->first();

            $all_pending_loan_data = DB::table('dcs.loans')
            ->where('reciverrole', '!=', '0')
            ->where('status', 1)
            ->where('projectcode', session('projectcode'))
            ->whereDate('loans.time', '>=', $from_date)
            ->whereDate('loans.time', '<=', $today)
            ->get();
           // dd($all_pending_loan_data);


            $all_approve_loan_data = DB::table('dcs.loans')
                ->where('reciverrole', '!=', '0')
                ->where('ErpStatus', 1)
                ->where('projectcode', session('projectcode'))
                ->whereDate('loans.time', '>=', $from_date)
                ->whereDate('loans.time', '<=', $today)
                ->get();

            //  dd($all_approve_loan_data);





            $all_approve_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('ErpStatus', 1)->where('projectcode', session('projectcode'))
                ->where('reciverrole', '!=', '0')->whereDate($db . '.loans.time', '>=', $from_date)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $all_disbursement = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('ErpStatus', 2)->where('projectcode', session('projectcode'))
                ->where('reciverrole', '!=', '0')->whereDate($db . '.loans.time', '>=', $from_date)
                ->whereDate($db . '.loans.time', '<=', $today)->first();

            $all_disburse_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('ErpStatus', 4)->where('projectcode', session('projectcode'))
                ->where('reciverrole', '!=', '0')->whereDate($db . '.loans.time', '>=', $from_date)
                ->whereDate($db . '.loans.time', '<=', $today)->first();

            $all_reject_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('reciverrole', '!=', '0')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '!=', '0')->whereDate($db . '.loans.time', '>=', $from_date)
                ->whereDate($db . '.loans.time', '<=', $today)->Where(function ($query) {
                    $query->where('status', 3)
                        ->orWhere('ErpStatus', 3);
                })->first();

            //kjhgfds


            // roll wise count**********************
            $am_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('status', '1')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '2')->whereDate($db . '.loans.time', '>=', $from_date)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $rm_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('status', '1')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '3')->whereDate($db . '.loans.time', '>=', $from_date)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $dm_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('status', '1')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '4')->whereDate($db . '.loans.time', '>=', $from_date)
                ->whereDate($db . '.loans.time', '<=', $today)->first();
            $bm_pending_loan = Loans::select(DB::raw("COUNT(*) as count"))->where('status', '1')->where('projectcode', session('projectcode'))
                ->where('reciverrole', '1')->whereDate($db . '.loans.time', '>=', $from_date)
                ->whereDate($db . '.loans.time', '<=', $today)->first();


            $disburse_amt = Loans::select(DB::raw('sum(cast(propos_amt as double precision))'))->where('projectcode', session('projectcode'))->where('reciverrole', '!=', '0')->whereDate($db . '.loans.time', '>=', $from_date)->whereDate($db . '.loans.time', '<=', $today)->where('ErpStatus', 4)->first();





            $jsondata = array(
                "pendingloandata" =>  $all_pending_loan_data,
                "approveloandata" =>  $all_approve_loan_data,
                "pendingadminssioncount" => $pending_admission->count,
                "pendingprofileadmission" => $pending_profileadmission->count,
                "pendingloan" =>  $pending_loan->count,
                "allpendingloan" =>  $all_pending_loan->count,
                "allapproveloan" => $all_approve_loan->count,
                "all_disbursement" => $all_disbursement->count,
                "allrejectloan" => $all_reject_loan->count,
                "ampendingloan" =>  $am_pending_loan->count,
                "rmpendingloan" => $rm_pending_loan->count,
                "dmpendingloan" =>  $dm_pending_loan->count,
                "bmpendingloan" => $bm_pending_loan->count,
                "alldisburseloan" => $all_disburse_loan->count,
                "disburseamt" => $disburse_amt->sum,
                "fromdate" => $from_date,
                "today" => $today

            );
            //dd($jsondata);
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
                'ampendingloan' => 0,
                'rmpendingloan' => 0,
                'dmpendingloan' => 0,
                'bmpendingloan' => 0,
                'alldisburseloan' => 0,
                'disburseamt' => 0,
            ];
        }

        return response()->json($jsondata);
    }
    public function GetDivisionData(Request $request)
    {
        $programId = $request->get('program_id');
        $a = DB::Table('branch')->select('division_id', 'division_name')->where('program_id', $programId)->distinct('division_id')->get();
        return $a;
    }
    public function GetRegionData(Request $request)
    {
        $divisionId = $request->get('division_id');
        $b = DB::Table('branch')->select('region_id', 'region_name')->where('division_id', $divisionId)->distinct('region_id')->get();
        return $b;
    }
    public function GetAreaData(Request $request)
    {
        $regionId = $request->get('region_id');
        $c = DB::Table('branch')->select('area_id', 'area_name')->where('region_id', $regionId)->distinct('area_id')->get();
        return $c;
    }
    public function GetBranchData(Request $request)
    {
        $areaId = $request->get('area_id');
        $d = DB::Table('branch')->select('branch_id', 'branch_name')->where('area_id', $areaId)->distinct('branch_id')->get();
        return $d;
    }
    public function GetProgramOrganizerData(Request $request)
    {
        //dd($request);
        $BranchCode = $request->get('branchcode');
        $e = DB::Table('dcs.polist')->select('cono', 'coname')->where('branchcode', $BranchCode)->get();

        return $e;
    }
    //GetBranchData

    //Sagor end
}
