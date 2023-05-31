<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\Admission;
use Illuminate\Support\Facades\Http;
use App\Models\Branch;

class postedLoanController extends Controller
{
    public function index()
    {

        return view('postedLoan');
    }

    public function postedLoanData(Request $req)
    {
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
        } else if (session('role_designation') == 'HO') {
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
            $branchCode = $row->branch_id;
            $branchCode = str_pad($branchCode, 4, "0", STR_PAD_LEFT);
            $value1 = DB::table($db . '.posted_admission')->select('branchcode', 'assignedpopin')
                ->where('branchcode', $branchCode)->first();
            if ($value1 != null) {
                $all_branchcode[] = $value1->branchcode;
                $all_assignedpo[] = str_pad($value1->assignedpopin, 8, "0", STR_PAD_LEFT);
            }
        }
        $polist = DB::table($db . '.polist')
            ->where('projectcode', session('projectcode'))
            ->where('status', '1')
            ->whereIn('branchcode', $all_branchcode)
            ->get();
        $po = array();
        foreach ($polist as $cono) {
            foreach ($all_assignedpo as $key => $value) {
                if ($cono->cono == $value) {
                    $po[] = $value;
                }
            }
        }
        $datas = DB::table($db . '.posted_admission')
            ->whereIn('dcs.posted_admission.assignedpopin', $po)
            ->select('dcs.posted_loan.*', 'dcs.posted_admission.assignedpopin', 'dcs.posted_admission.nameen')
            ->join('dcs.posted_loan', 'dcs.posted_admission.memberid', '=', 'dcs.posted_loan.memberid')
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
        })->addColumn('assignedpopin', function ($datas) {
            $coname = '';
            $assignedpo = $datas->assignedpopin;
            $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
            if ($co_qry) {
                $coname = $co_qry->coname;
                return $coname;
            }
            return $coname;
        })->addColumn('applicationdate', function ($datas) {
            $time = date('m/d/Y', strtotime($datas->applicationdate));
            return $time;
        })->addColumn('action', function ($datas) {
            return '<a href="posted-loan-details/' . $datas->id . '" class="btn btn-warning">Details</a>';
        })->toJson();
    }
    public function postedLoanDetails($id)
    {
        $db = config('database.db');
        $data = DB::table($db . '.posted_loan')->where('id', $id)->first();
        return view('postedLoanDetails')->with('data', $data);
    }
}
