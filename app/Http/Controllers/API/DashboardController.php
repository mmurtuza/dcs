<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Admission;
use App\Models\Loans;
use App\Models\Branch;
use App\Http\Controllers\LiveApiController;
use Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $db = config('database.db');
        $role_designation = session('role_designation');
       // $request->session()->put('status_btn', '1');

        // Get current date
        $today = date('Y-m-d');
        $from_date = date('Y-m-01');

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
        } elseif ($role_designation == 'RM') {
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
        } elseif ($role_designation == 'DM') {
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

            foreach ($search2 as $branch) {
                $branchCode = str_pad($branch->branch_id, 4, "0", STR_PAD_LEFT);
                $branchcodes[] = $branchCode;
            }
        } elseif ($role_designation == 'HO' || $role_designation == 'PH') {
            $value = Branch::where('program_id', session('program_id'))
                ->get();

            $branch = Branch::where([
                'division_id' => session('asid'),
                'program_id' => session('program_id')
            ])->first();

            $search2 = Branch::where('program_id', session('program_id'))
                ->distinct('division_id')->get();
        } else {
            return redirect()->back()->with('error', 'Data does not match.');
        }

        if (!empty($branchcodes)) {
            $pending_admission = Admission::where('projectcode', session('projectcode'))
                ->where('Flag', 1)
                ->whereIn('branchcode', $branchcodes)
                ->where('admission_date', '>=', $from_date)
                ->where('admission_date', '<=', $today)
                ->count();

            $approved_admission = Admission::where('projectcode', session('projectcode'))
                ->where('Flag', 2)
                ->whereIn('branchcode', $branchcodes)
                ->where('admission_date', '>=', $from_date)
                ->where('admission_date', '<=', $today)
                ->count();

            $pending_loans = Loans::where('projectcode', session('projectcode'))
                ->where('status', '!=', 0)
                ->whereIn('branchcode', $branchcodes)
                ->where('loan_release_date', '>=', $from_date)
                ->where('loan_release_date', '<=', $today)
                ->count();

            $approved_loans = Loans::where('projectcode', session('projectcode'))
                ->where('status', '=', 0)
                ->whereIn('branchcode', $branchcodes)
                ->where('loan_release_date', '>=', $from_date)
                ->where('loan_release_date', '<=', $today)
                ->count();

            $jsondata = [
                'pending_admission' => $pending_admission,
                'approved_admission' => $approved_admission,
                'pending_loans' => $pending_loans,
                'approved_loans' => $approved_loans,
            ];
        } else {
            $jsondata = [
                'pending_admission' => 0,
                'approved_admission' => 0,
                'pending_loans' => 0,
                'approved_loans' => 0,
            ];
        }

        return response()->json($jsondata);
    }
}
