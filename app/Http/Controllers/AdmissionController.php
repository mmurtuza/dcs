<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admission;
use Illuminate\Support\Facades\Http;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Log;
use App\Http\Controllers\LiveApiController;

class AdmissionController extends Controller
{

    public function index()
    {
        $db = config('database.db');
        // $admission= DB::table($db.'.admissions')
        // ->select('dcs.admissions.*','public.branch.branch_name')
        // ->join('public.branch','dcs.admissions.branchcode','=','public.branch.branch_id')
        // ->get();
        $role_designation = session('role_designation');
        // dd($role_designation);


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
            // .
            // $data1=Branch::select('branch_id','branch_name')
            //                 ->where([
            //                 'area_id' => session('asid'),
            //                 'program_id' => session('program_id')])->get();
        } else if (session('role_designation') == 'RM') {
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
        } else if (session('role_designation') == 'BM') {
            $value = Branch::where([
                'branch_id' => session('asid'),
                'program_id' => session('program_id')
            ])->gorupBy('area_id')->get();
            $branch = Branch::where([
                'branch_id' => session('asid'),
                'program_id' => session('program_id')
            ])->first();
            $search2 = Branch::where([
                'program_id' => session('program_id')
            ])->distinct('division_id')->get();
        } else {
            return redirect()->back()->with('error', 'data does not match');
        }

        $status = DB::table($db . '.status')->where('process', '*')->orderBy('status_id', 'asc')->get();
        return view('admission-request')->with('branch', $branch)->with('value', $value)->with('search2', $search2)->with('status', $status);
    }

    public function admissionTable(Request $req)
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
            $branchCode = $row->branch_id;
            // $length=strlen($branchCode);
            // if($length==3)
            // {

            $branchCode = str_pad($branchCode, 4, "0", STR_PAD_LEFT);
            // }
            $value1 = DB::table($db . '.admissions')->select('branchcode', 'assignedpo')
                ->where('branchcode', $branchCode)->groupBy('branchcode', 'assignedpo')->get();
            if (!$value1->isEmpty()) {
                foreach ($value1 as $assignedpo) {
                    $all_branchcode[] = $assignedpo->branchcode;
                    $all_assignedpo[] = str_pad($assignedpo->assignedpo, 8, "0", STR_PAD_LEFT);
                }
            }
        }
        /*$responseLoan = Http::post('http://scm.brac.net/sc/GetPO', [
            'branchcode' => $all_branchcode,
            'projectcode' => session('projectcode'),
        ]);
        $polist = $responseLoan->object()->data;*/
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
        $status_search = $req->status;
        $branch_search = $req->branch;
        $branchcode_search = str_pad($branch_search, 4, "0", STR_PAD_LEFT);
        $dateForm = $req->dateFrom;
        $dateTo = $req->dateTo;
        $po_search = $req->po;

        // division search
        if ($division != null && $region == null && $area == null && $branch_search == null && $po_search == null && $status_search == null && $po_search == null) {
            $datas = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $po)
                ->whereIn('branchcode', $division_search)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('projectcode', session('projectcode'))
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
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                return $coname;
            })->addColumn('created_at', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->created_at));
                return $time;
            })->addColumn('status', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                $recieverRole = $datas->reciverrole;
                $getRecieverRole = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', session('projectcode'))->where('position', $recieverRole)->first();
                $recieverRole_destination = $getRecieverRole->designation;
                if (session('role_designation') == 'AM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'RM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'DM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if ((session('role_designation') == 'HO') or (session('role_designation') == 'PH')) {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                return $Mainstatus;
            })->addColumn('ERPstatus', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                if ($datas->status == 1 and $datas->ErpStatus == '') {
                    $Mainstatus = 'N/A';
                } else if ($datas->status == 2 and $datas->ErpStatus == 1) {
                    $Mainstatus = 'ERP Pending';
                } else if ($datas->status == 2 and $datas->ErpStatus == 2) {
                    $Mainstatus = 'ERP Approved';
                } else if ($datas->status == 3) {
                    if ($datas->ErpStatus == 3 and $datas->status == 2) {
                        $Mainstatus = 'Rejected';
                    } else {
                        $Mainstatus = 'N/A';
                    }
                }
                return $Mainstatus;
            })->addColumn('flag', function ($datas) {
                $db = config('database.db');
                $types = '';
                $flag = $datas->Flag;
                $Flag = DB::table($db . '.admissions')->where('Flag', $flag)->first();
                if ($Flag) {
                    $type = $Flag->Flag;
                    if ($type == '1') {
                        $types = 'New';
                    } else if ($type == '2') {
                        $types = 'Profile Update';
                    }
                }
                return $types;
            })->addColumn('action', function ($datas) {
                return '<a href="admission-approval/' . $datas->id . '" class="btn btn-warning">Details</a>';
            })->toJson();
        }
        // division & status search
        if ($division != null && $status_search != null && $region == null && $area == null && $branch_search == null && $po_search == null) {
            $datas = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $po)
                ->whereIn('branchcode', $division_search)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('status', $status_search)
                ->where('projectcode', session('projectcode'))
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
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                return $coname;
            })->addColumn('created_at', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->created_at));
                return $time;
            })->addColumn('status', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                $recieverRole = $datas->reciverrole;
                $getRecieverRole = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', session('projectcode'))->where('position', $recieverRole)->first();
                $recieverRole_destination = $getRecieverRole->designation;
                if (session('role_designation') == 'AM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'RM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'DM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if ((session('role_designation') == 'HO') or (session('role_designation') == 'PH')) {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                return $Mainstatus;
            })->addColumn('ERPstatus', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                if ($datas->status == 1 and $datas->ErpStatus == '') {
                    $Mainstatus = 'N/A';
                } else if ($datas->status == 2 and $datas->ErpStatus == 1) {
                    $Mainstatus = 'ERP Pending';
                } else if ($datas->status == 2 and $datas->ErpStatus == 2) {
                    $Mainstatus = 'ERP Approved';
                } else if ($datas->status == 3) {
                    if ($datas->ErpStatus == 3 and $datas->status == 2) {
                        $Mainstatus = 'Rejected';
                    } else {
                        $Mainstatus = 'N/A';
                    }
                }
                return $Mainstatus;
            })->addColumn('flag', function ($datas) {
                $db = config('database.db');
                $types = '';
                $flag = $datas->Flag;
                $Flag = DB::table($db . '.admissions')->where('Flag', $flag)->first();
                if ($Flag) {
                    $type = $Flag->Flag;
                    if ($type == '1') {
                        $types = 'New';
                    } else if ($type == '2') {
                        $types = 'Profile Update';
                    }
                }
                return $types;
            })->addColumn('action', function ($datas) {
                return '<a href="admission-approval/' . $datas->id . '" class="btn btn-warning">Details</a>';
            })->toJson();
        }
        //  region search
        if ($region != null && $area == null && $branch_search == null && $po_search == null && $status_search == null) {
            $datas = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $po)
                ->whereIn('branchcode', $region_search)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('projectcode', session('projectcode'))
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
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                return $coname;
            })->addColumn('created_at', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->created_at));
                return $time;
            })->addColumn('status', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                $recieverRole = $datas->reciverrole;
                $getRecieverRole = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', session('projectcode'))->where('position', $recieverRole)->first();
                $recieverRole_destination = $getRecieverRole->designation;
                if (session('role_designation') == 'AM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'RM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'DM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if ((session('role_designation') == 'HO') or (session('role_designation') == 'PH')) {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                return $Mainstatus;
            })->addColumn('ERPstatus', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                if ($datas->status == 1 and $datas->ErpStatus == '') {
                    $Mainstatus = 'N/A';
                } else if ($datas->status == 2 and $datas->ErpStatus == 1) {
                    $Mainstatus = 'ERP Pending';
                } else if ($datas->status == 2 and $datas->ErpStatus == 2) {
                    $Mainstatus = 'ERP Approved';
                } else if ($datas->status == 3) {
                    if ($datas->ErpStatus == 3 and $datas->status == 2) {
                        $Mainstatus = 'Rejected';
                    } else {
                        $Mainstatus = 'N/A';
                    }
                }
                return $Mainstatus;
            })->addColumn('flag', function ($datas) {
                $db = config('database.db');
                $types = '';
                $flag = $datas->Flag;
                $Flag = DB::table($db . '.admissions')->where('Flag', $flag)->first();
                if ($Flag) {
                    $type = $Flag->Flag;
                    if ($type == '1') {
                        $types = 'New';
                    } else if ($type == '2') {
                        $types = 'Profile Update';
                    }
                }
                return $types;
            })->addColumn('action', function ($datas) {
                return '<a href="admission-approval/' . $datas->id . '" class="btn btn-warning">Details</a>';
            })->toJson();
        }
        // region & status search
        if ($region != null  && $status_search != null && $area == null && $branch_search == null && $po_search == null) {
            $datas = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $po)
                ->whereIn('branchcode', $region_search)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('status', $status_search)
                ->where('projectcode', session('projectcode'))
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
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                return $coname;
            })->addColumn('created_at', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->created_at));
                return $time;
            })->addColumn('status', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                $recieverRole = $datas->reciverrole;
                $getRecieverRole = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', session('projectcode'))->where('position', $recieverRole)->first();

                $recieverRole_destination = $getRecieverRole->designation;
                if (session('role_designation') == 'AM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'RM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'DM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if ((session('role_designation') == 'HO') or (session('role_designation') == 'PH')) {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                return $Mainstatus;
            })->addColumn('ERPstatus', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                if ($datas->status == 1 and $datas->ErpStatus == '') {
                    $Mainstatus = 'N/A';
                } else if ($datas->status == 2 and $datas->ErpStatus == 1) {
                    $Mainstatus = 'ERP Pending';
                } else if ($datas->status == 2 and $datas->ErpStatus == 2) {
                    $Mainstatus = 'ERP Approved';
                } else if ($datas->status == 3) {
                    if ($datas->ErpStatus == 3 and $datas->status == 2) {
                        $Mainstatus = 'Rejected';
                    } else {
                        $Mainstatus = 'N/A';
                    }
                }
                return $Mainstatus;
            })->addColumn('flag', function ($datas) {
                $db = config('database.db');
                $types = '';
                $flag = $datas->Flag;
                $Flag = DB::table($db . '.admissions')->where('Flag', $flag)->first();
                if ($Flag) {
                    $type = $Flag->Flag;
                    if ($type == '1') {
                        $types = 'New';
                    } else if ($type == '2') {
                        $types = 'Profile Update';
                    }
                }
                return $types;
            })->addColumn('action', function ($datas) {
                return '<a href="admission-approval/' . $datas->id . '" class="btn btn-warning">Details</a>';
            })->toJson();
        }
        // area search & date
        if ($area != null && $branch_search == null && $po_search == null && $status_search == null) {
            $datas = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $po)
                ->whereIn('branchcode', $area_search)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('projectcode', session('projectcode'))
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
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                return $coname;
            })->addColumn('created_at', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->created_at));
                return $time;
            })->addColumn('status', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                $recieverRole = $datas->reciverrole;
                $getRecieverRole = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', session('projectcode'))->where('position', $recieverRole)->first();
                $recieverRole_destination = $getRecieverRole->designation;
                if (session('role_designation') == 'AM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'RM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'DM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if ((session('role_designation') == 'HO') or (session('role_designation') == 'PH')) {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                return $Mainstatus;
            })->addColumn('ERPstatus', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                if ($datas->status == 1 and $datas->ErpStatus == '') {
                    $Mainstatus = 'N/A';
                } else if ($datas->status == 2 and $datas->ErpStatus == 1) {
                    $Mainstatus = 'ERP Pending';
                } else if ($datas->status == 2 and $datas->ErpStatus == 2) {
                    $Mainstatus = 'ERP Approved';
                } else if ($datas->status == 3) {
                    if ($datas->ErpStatus == 3 and $datas->status == 2) {
                        $Mainstatus = 'Rejected';
                    } else {
                        $Mainstatus = 'N/A';
                    }
                }
                return $Mainstatus;
            })->addColumn('flag', function ($datas) {
                $db = config('database.db');
                $types = '';
                $flag = $datas->Flag;
                $Flag = DB::table($db . '.admissions')->where('Flag', $flag)->first();
                if ($Flag) {
                    $type = $Flag->Flag;
                    if ($type == '1') {
                        $types = 'New';
                    } else if ($type == '2') {
                        $types = 'Profile Update';
                    }
                }
                return $types;
            })->addColumn('action', function ($datas) {
                return '<a href="admission-approval/' . $datas->id . '" class="btn btn-warning">Details</a>';
            })->toJson();
        }
        // area & status search
        if ($area != null && $status_search != null && $branch_search == null && $po_search == null) {
            $datas = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $po)
                ->whereIn('branchcode', $area_search)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('status', $status_search)
                ->where('projectcode', session('projectcode'))
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
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                return $coname;
            })->addColumn('created_at', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->created_at));
                return $time;
            })->addColumn('status', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                $recieverRole = $datas->reciverrole;
                $getRecieverRole = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', session('projectcode'))->where('position', $recieverRole)->first();
                $recieverRole_destination = $getRecieverRole->designation;
                if (session('role_designation') == 'AM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'RM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'DM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if ((session('role_designation') == 'HO') or (session('role_designation') == 'PH')) {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                return $Mainstatus;
            })->addColumn('ERPstatus', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                if ($datas->status == 1 and $datas->ErpStatus == '') {
                    $Mainstatus = 'N/A';
                } else if ($datas->status == 2 and $datas->ErpStatus == 1) {
                    $Mainstatus = 'ERP Pending';
                } else if ($datas->status == 2 and $datas->ErpStatus == 2) {
                    $Mainstatus = 'ERP Approved';
                } else if ($datas->status == 3) {
                    if ($datas->ErpStatus == 3 and $datas->status == 2) {
                        $Mainstatus = 'Rejected';
                    } else {
                        $Mainstatus = 'N/A';
                    }
                }
                return $Mainstatus;
            })->addColumn('flag', function ($datas) {
                $db = config('database.db');
                $types = '';
                $flag = $datas->Flag;
                $Flag = DB::table($db . '.admissions')->where('Flag', $flag)->first();
                if ($Flag) {
                    $type = $Flag->Flag;
                    if ($type == '1') {
                        $types = 'New';
                    } else if ($type == '2') {
                        $types = 'Profile Update';
                    }
                }
                return $types;
            })->addColumn('action', function ($datas) {
                return '<a href="admission-approval/' . $datas->id . '" class="btn btn-warning">Details</a>';
            })->toJson();
        }
        // branch & date & status  search
        if ($branch_search != null  && $status_search != null && $dateForm != null && $dateTo != null && $po_search == null) {
            $datas = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $po)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('branchcode', $branchcode_search)
                ->where('status', $status_search)
                ->where('projectcode', session('projectcode'))
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
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                return $coname;
            })->addColumn('created_at', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->created_at));
                return $time;
            })->addColumn('status', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                $recieverRole = $datas->reciverrole;
                $getRecieverRole = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', session('projectcode'))->where('position', $recieverRole)->first();
                $recieverRole_destination = $getRecieverRole->designation;
                if (session('role_designation') == 'AM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'RM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'DM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if ((session('role_designation') == 'HO') or (session('role_designation') == 'PH')) {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                return $Mainstatus;
            })->addColumn('ERPstatus', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                if ($datas->status == 1 and $datas->ErpStatus == '') {
                    $Mainstatus = 'N/A';
                } else if ($datas->status == 2 and $datas->ErpStatus == 1) {
                    $Mainstatus = 'ERP Pending';
                } else if ($datas->status == 2 and $datas->ErpStatus == 2) {
                    $Mainstatus = 'ERP Approved';
                } else if ($datas->status == 3) {
                    if ($datas->ErpStatus == 3 and $datas->status == 2) {
                        $Mainstatus = 'Rejected';
                    } else {
                        $Mainstatus = 'N/A';
                    }
                }
                return $Mainstatus;
            })->addColumn('flag', function ($datas) {
                $db = config('database.db');
                $types = '';
                $flag = $datas->Flag;
                $Flag = DB::table($db . '.admissions')->where('Flag', $flag)->first();
                if ($Flag) {
                    $type = $Flag->Flag;
                    if ($type == '1') {
                        $types = 'New';
                    } else if ($type == '2') {
                        $types = 'Profile Update';
                    }
                }
                return $types;
            })->addColumn('action', function ($datas) {
                return '<a href="admission-approval/' . $datas->id . '" class="btn btn-warning">Details</a>';
            })->toJson();
        }
        // branch & date search
        if ($branch_search != null && $dateForm != null && $dateTo != null && $status_search == null && $po_search == null) {
            $datas = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $po)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('branchcode', $branchcode_search)
                ->where('projectcode', session('projectcode'))
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
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                return $coname;
            })->addColumn('created_at', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->created_at));
                return $time;
            })->addColumn('status', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                $recieverRole = $datas->reciverrole;
                $getRecieverRole = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', session('projectcode'))->where('position', $recieverRole)->first();
                $recieverRole_destination = $getRecieverRole->designation;
                if (session('role_designation') == 'AM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'RM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'DM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if ((session('role_designation') == 'HO') or (session('role_designation') == 'PH')) {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                return $Mainstatus;
            })->addColumn('ERPstatus', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                if ($datas->status == 1 and $datas->ErpStatus == '') {
                    $Mainstatus = 'N/A';
                } else if ($datas->status == 2 and $datas->ErpStatus == 1) {
                    $Mainstatus = 'ERP Pending';
                } else if ($datas->status == 2 and $datas->ErpStatus == 2) {
                    $Mainstatus = 'ERP Approved';
                } else if ($datas->status == 3) {
                    if ($datas->ErpStatus == 3 and $datas->status == 2) {
                        $Mainstatus = 'Rejected';
                    } else {
                        $Mainstatus = 'N/A';
                    }
                }
                return $Mainstatus;
            })->addColumn('flag', function ($datas) {
                $db = config('database.db');
                $types = '';
                $flag = $datas->Flag;
                $Flag = DB::table($db . '.admissions')->where('Flag', $flag)->first();
                if ($Flag) {
                    $type = $Flag->Flag;
                    if ($type == '1') {
                        $types = 'New';
                    } else if ($type == '2') {
                        $types = 'Profile Update';
                    }
                }
                return $types;
            })->addColumn('action', function ($datas) {
                return '<a href="admission-approval/' . $datas->id . '" class="btn btn-warning">Details</a>';
            })->toJson();
        }

        // po & date search
        if ($po_search != null && $dateForm != null && $dateTo != null && $status_search == null) {
            $datas = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $po)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('branchcode', $branchcode_search)
                ->where('assignedpo', $po_search)
                ->where('projectcode', session('projectcode'))
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
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                return $coname;
            })->addColumn('created_at', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->created_at));
                return $time;
            })->addColumn('status', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                $recieverRole = $datas->reciverrole;
                $getRecieverRole = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', session('projectcode'))->where('position', $recieverRole)->first();
                $recieverRole_destination = $getRecieverRole->designation;
                if (session('role_designation') == 'AM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'RM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'DM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if ((session('role_designation') == 'HO') or (session('role_designation') == 'PH')) {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                return $Mainstatus;
            })->addColumn('ERPstatus', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                if ($datas->status == 1 and $datas->ErpStatus == '') {
                    $Mainstatus = 'N/A';
                } else if ($datas->status == 2 and $datas->ErpStatus == 1) {
                    $Mainstatus = 'ERP Pending';
                } else if ($datas->status == 2 and $datas->ErpStatus == 2) {
                    $Mainstatus = 'ERP Approved';
                } else if ($datas->status == 3) {
                    if ($datas->ErpStatus == 3 and $datas->status == 2) {
                        $Mainstatus = 'Rejected';
                    } else {
                        $Mainstatus = 'N/A';
                    }
                }
                return $Mainstatus;
            })->addColumn('flag', function ($datas) {
                $db = config('database.db');
                $types = '';
                $flag = $datas->Flag;
                $Flag = DB::table($db . '.admissions')->where('Flag', $flag)->first();
                if ($Flag) {
                    $type = $Flag->Flag;
                    if ($type == '1') {
                        $types = 'New';
                    } else if ($type == '2') {
                        $types = 'Profile Update';
                    }
                }
                return $types;
            })->addColumn('action', function ($datas) {
                return '<a href="admission-approval/' . $datas->id . '" class="btn btn-warning">Details</a>';
            })->toJson();
        }

        // po & date & status
        if ($po_search != null && $dateForm != null && $dateTo != null && $status_search != null) {
            $datas = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $po)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('assignedpo', $po_search)
                ->where('status', $status_search)
                ->where('projectcode', session('projectcode'))
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
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                return $coname;
            })->addColumn('created_at', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->created_at));
                return $time;
            })->addColumn('status', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                $recieverRole = $datas->reciverrole;
                $getRecieverRole = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', session('projectcode'))->where('position', $recieverRole)->first();
                $recieverRole_destination = $getRecieverRole->designation;
                if (session('role_designation') == 'AM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'RM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'DM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if ((session('role_designation') == 'HO') or (session('role_designation') == 'PH')) {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                return $Mainstatus;
            })->addColumn('ERPstatus', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                if ($datas->status == 1 and $datas->ErpStatus == '') {
                    $Mainstatus = 'N/A';
                } else if ($datas->status == 2 and $datas->ErpStatus == 1) {
                    $Mainstatus = 'ERP Pending';
                } else if ($datas->status == 2 and $datas->ErpStatus == 2) {
                    $Mainstatus = 'ERP Approved';
                } else if ($datas->status == 3) {
                    if ($datas->ErpStatus == 3 and $datas->status == 2) {
                        $Mainstatus = 'Rejected';
                    } else {
                        $Mainstatus = 'N/A';
                    }
                }
                return $Mainstatus;
            })->addColumn('flag', function ($datas) {
                $db = config('database.db');
                $types = '';
                $flag = $datas->Flag;
                $Flag = DB::table($db . '.admissions')->where('Flag', $flag)->first();
                if ($Flag) {
                    $type = $Flag->Flag;
                    if ($type == '1') {
                        $types = 'New';
                    } else if ($type == '2') {
                        $types = 'Profile Update';
                    }
                }
                return $types;
            })->addColumn('action', function ($datas) {
                return '<a href="admission-approval/' . $datas->id . '" class="btn btn-warning">Details</a>';
            })->toJson();
        }
        // date & status search
        if ($division == null && $region == null && $area == null && $dateForm != null && $dateTo != null && $status_search != null && $po_search == null && $branch_search == null) {
            $datas = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $po)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('status', $status_search)
                ->where('projectcode', session('projectcode'))
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
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                return $coname;
            })->addColumn('created_at', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->created_at));
                return $time;
            })->addColumn('status', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                $recieverRole = $datas->reciverrole;
                $getRecieverRole = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', session('projectcode'))->where('position', $recieverRole)->first();
                $recieverRole_destination = $getRecieverRole->designation;
                if (session('role_designation') == 'AM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'RM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'DM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if ((session('role_designation') == 'HO') or (session('role_designation') == 'PH')) {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                return $Mainstatus;
            })->addColumn('ERPstatus', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                if ($datas->status == 1 and $datas->ErpStatus == '') {
                    $Mainstatus = 'N/A';
                } else if ($datas->status == 2 and $datas->ErpStatus == 1) {
                    $Mainstatus = 'ERP Pending';
                } else if ($datas->status == 2 and $datas->ErpStatus == 2) {
                    $Mainstatus = 'ERP Approved';
                } else if ($datas->status == 3) {
                    if ($datas->ErpStatus == 3 and $datas->status == 2) {
                        $Mainstatus = 'Rejected';
                    } else {
                        $Mainstatus = 'N/A';
                    }
                }
                return $Mainstatus;
            })->addColumn('flag', function ($datas) {
                $db = config('database.db');
                $types = '';
                $flag = $datas->Flag;
                $Flag = DB::table($db . '.admissions')->where('Flag', $flag)->first();
                if ($Flag) {
                    $type = $Flag->Flag;
                    if ($type == '1') {
                        $types = 'New';
                    } else if ($type == '2') {
                        $types = 'Profile Update';
                    }
                }
                return $types;
            })->addColumn('action', function ($datas) {
                return '<a href="admission-approval/' . $datas->id . '" class="btn btn-warning">Details</a>';
            })->toJson();
        }


        // date search
        if ($division == null && $region == null && $area == null && $dateForm != null && $dateTo != null && $status_search == null && $branch_search == null && $po_search == null) {
            //dd("Huda");
            $datas = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $po)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('projectcode', session('projectcode'))
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
                $co_qry = DB::table($db . '.polist')->where('cono', $assignedpo)->first();
                if ($co_qry) {
                    $coname = $co_qry->coname;
                    return $coname;
                }
                return $coname;
            })->addColumn('created_at', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->created_at));
                return $time;
            })->addColumn('status', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                $recieverRole = $datas->reciverrole;
                $getRecieverRole = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', session('projectcode'))->where('position', $recieverRole)->first();
                $recieverRole_destination = $getRecieverRole->designation;
                if (session('role_designation') == 'AM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'RM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at HO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if (session('role_designation') == 'DM') {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                if ((session('role_designation') == 'HO') or (session('role_designation') == 'PH')) {
                    if ($recieverRole_destination == 'AM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at AM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'RM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at RM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'DM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at DM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'BM') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at BM";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'PO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name . " at PO";
                        return $Mainstatus;
                    }
                    if ($recieverRole_destination == 'HO') {
                        $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                        $Mainstatus = $statusQuery->status_name;
                        return $Mainstatus;
                    }
                    return $Mainstatus;
                }
                return $Mainstatus;
            })->addColumn('ERPstatus', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                if ($datas->status == 1 and $datas->ErpStatus == '') {
                    $Mainstatus = 'N/A';
                } else if ($datas->status == 2 and $datas->ErpStatus == 1) {
                    $Mainstatus = 'ERP Pending';
                } else if ($datas->status == 2 and $datas->ErpStatus == 2) {
                    $Mainstatus = 'ERP Approved';
                } else if ($datas->status == 3) {
                    if ($datas->ErpStatus == 3 and $datas->status == 2) {
                        $Mainstatus = 'Rejected';
                    } else {
                        $Mainstatus = 'N/A';
                    }
                }
                return $Mainstatus;
            })->addColumn('flag', function ($datas) {
                $db = config('database.db');
                $types = '';
                $flag = $datas->Flag;
                $Flag = DB::table($db . '.admissions')->where('Flag', $flag)->first();
                if ($Flag) {
                    $type = $Flag->Flag;
                    if ($type == '1') {
                        $types = 'New';
                    } else if ($type == '2') {
                        $types = 'Profile Update';
                    }
                }
                return $types;
            })->addColumn('action', function ($datas) {
                return '<a href="admission-approval/' . $datas->id . '" class="btn btn-warning">Details</a>';
            })->toJson();
        }

        if ((session('role_designation') == 'HO') or (session('role_designation') == 'PH')) {
            $dateForm = date('Y-m-d');
            $dateTo = date('Y-m-d');
            $datas = DB::table($db . '.admissions')
                ->whereIn('assignedpo', $po)
                ->whereDate('created_at', '>=', $dateForm)
                ->whereDate('created_at', '<=', $dateTo)
                ->where('projectcode', session('projectcode'))
                // ->whereDate('created_at', Carbon::today())
                ->get();

            // ->paginate(10);
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
                return $coname;
            })->addColumn('created_at', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->created_at));
                return $time;
            })->addColumn('status', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                $recieverRole = $datas->reciverrole;
                $getRecieverRole = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', session('projectcode'))->where('position', $recieverRole)->first();
                $recieverRole_destination = $getRecieverRole->designation;
                if ($recieverRole_destination == 'AM') {
                    $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                    $Mainstatus = $statusQuery->status_name . " at AM";
                    return $Mainstatus;
                }
                if ($recieverRole_destination == 'RM') {
                    $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                    $Mainstatus = $statusQuery->status_name . " at RM";
                    return $Mainstatus;
                }
                if ($recieverRole_destination == 'DM') {
                    $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                    $Mainstatus = $statusQuery->status_name . " at DM";
                    return $Mainstatus;
                }
                if ($recieverRole_destination == 'BM') {
                    $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                    $Mainstatus = $statusQuery->status_name . " at BM";
                    return $Mainstatus;
                }
                if ($recieverRole_destination == 'HO') {
                    $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                    $Mainstatus = $statusQuery->status_name;
                    return $Mainstatus;
                }
                if ($recieverRole_destination == 'PO') {
                    $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                    $Mainstatus = $statusQuery->status_name . " at PO";
                    return $Mainstatus;
                }
                return $Mainstatus;
            })->addColumn('ERPstatus', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                if ($datas->status == 1 and $datas->ErpStatus == '') {
                    $Mainstatus = 'N/A';
                } else if ($datas->status == 2 and $datas->ErpStatus == 1) {
                    $Mainstatus = 'ERP Pending';
                } else if ($datas->status == 2 and $datas->ErpStatus == 2) {
                    $Mainstatus = 'ERP Approved';
                } else if ($datas->status == 3) {
                    if ($datas->ErpStatus == 3 and $datas->status == 2) {
                        $Mainstatus = 'Rejected';
                    } else {
                        $Mainstatus = 'N/A';
                    }
                }
                return $Mainstatus;
            })->addColumn('flag', function ($datas) {
                $db = config('database.db');
                $types = '';
                $flag = $datas->Flag;
                $Flag = DB::table($db . '.admissions')->where('Flag', $flag)->first();
                if ($Flag) {
                    $type = $Flag->Flag;
                    if ($type == '1') {
                        $types = 'New';
                    } else if ($type == '2') {
                        $types = 'Profile Update';
                    }
                }
                return $types;
            })->addColumn('action', function ($datas) {
                return '<a href="admission-approval/' . $datas->id . '" class="btn btn-warning">Details</a>';
            })->toJson();
        }

        if ((session('role_designation') != 'HO') or (session('role_designation') != 'PH')) {
            $dateForm = date('Y-m') . "-01";
            $dateTo = date('Y-m-d');
            if (session('role_designation') == 'AM' and session('projectcode') == '060') {
                $datas = DB::table($db . '.admissions')
                    ->whereIn('assignedpo', $po)
                    ->whereDate('created_at', '>=', $dateForm)
                    ->whereDate('created_at', '<=', $dateTo)
                    ->where('projectcode', session('projectcode'))
                    ->where('status', '1')
                    // ->whereDate('created_at', Carbon::today())
                    // ->where('created_at', $today)
                    // ->where('reciverrole', session('roll'))
                    ->get();
            } else {
                $datas = DB::table($db . '.admissions')
                    ->whereIn('assignedpo', $po)
                    ->where('projectcode', session('projectcode'))
                    // ->whereDate('created_at', Carbon::today())
                    // ->where('created_at', $today)
                    // ->where('reciverrole', session('roll'))
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
                return $coname;
            })->addColumn('created_at', function ($datas) {
                $time = date('d/m/Y', strtotime($datas->created_at));
                return $time;
            })->addColumn('status', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                $recieverRole = $datas->reciverrole;
                $getRecieverRole = DB::table($db . '.role_hierarchies')->select('designation')->where('projectcode', session('projectcode'))->where('position', $recieverRole)->first();
                $recieverRole_destination = $getRecieverRole->designation;
                if ($recieverRole_destination == 'AM') {
                    $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                    if (session('projectcode') == '060') {
                        $Mainstatus = $statusQuery->status_name;
                    } else {
                        $Mainstatus = $statusQuery->status_name . " at AM";
                    }

                    return $Mainstatus;
                }
                if ($recieverRole_destination == 'RM') {
                    $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                    $Mainstatus = $statusQuery->status_name . " at RM";
                    return $Mainstatus;
                }
                if ($recieverRole_destination == 'DM') {
                    $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                    $Mainstatus = $statusQuery->status_name . " at DM";
                    return $Mainstatus;
                }
                if ($recieverRole_destination == 'BM') {
                    $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                    $Mainstatus = $statusQuery->status_name . " at BM";
                    return $Mainstatus;
                }
                if ($recieverRole_destination == 'HO') {
                    $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                    $Mainstatus = $statusQuery->status_name . " at HO";
                    return $Mainstatus;
                }
                if ($recieverRole_destination == 'PO') {
                    $statusQuery = DB::table($db . '.status')->select('status_name')->where('status_id', $datas->status)->first();
                    $Mainstatus = $statusQuery->status_name . " at PO";
                    return $Mainstatus;
                }
                return $Mainstatus;
            })->addColumn('ERPstatus', function ($datas) {
                $db = config('database.db');
                $Mainstatus = '';
                if ($datas->status == 1 and $datas->ErpStatus == '') {
                    $Mainstatus = 'N/A';
                } else if ($datas->status == 2 and $datas->ErpStatus == 1) {
                    $Mainstatus = 'ERP Pending';
                } else if ($datas->status == 2 and $datas->ErpStatus == 2) {
                    $Mainstatus = 'ERP Approved';
                } else if ($datas->status == 3) {
                    if ($datas->ErpStatus == 3 and $datas->status == 2) {
                        $Mainstatus = 'Rejected';
                    } else {
                        $Mainstatus = 'N/A';
                    }
                }
                return $Mainstatus;
            })->addColumn('flag', function ($datas) {
                $db = config('database.db');
                $types = '';
                $flag = $datas->Flag;
                $Flag = DB::table($db . '.admissions')->where('Flag', $flag)->first();
                if ($Flag) {
                    $type = $Flag->Flag;
                    if ($type == '1') {
                        $types = 'New';
                    } else if ($type == '2') {
                        $types = 'Profile Update';
                    }
                }
                return $types;
            })->addColumn('action', function ($datas) {
                return '<a href="admission-approval/' . $datas->id . '" class="btn btn-warning">Details</a>';
            })->toJson();
        }
    }

    public function division(Request $request)
    {
        $data = Branch::where([
            'division_id' => $request->division,
            'program_id' => session('program_id')
        ])->distinct('region_name')->get();
        return response()->json($data);
    }

    public function region(Request $request)
    {

        $data = Branch::where([
            'region_id' => $request->region,
            'program_id' => session('program_id')
        ])->distinct('area_name')->get();
        return response()->json($data);
    }

    public function area(Request $request)
    {
        $data = Branch::where([
            'area_id' => $request->area,
            'program_id' => session('program_id')
        ])->distinct('branch_name')->get();
        return response()->json($data);
    }

    public function branch(Request $request)
    {
        $db = config('database.db');
        $branchCode = str_pad($request->branch, 4, "0", STR_PAD_LEFT);
        $data = DB::table($db . '.polist')->where('branchcode', $branchCode)
            ->where('projectcode', session('projectcode'))->where('status', '1')->distinct('cono')->get();
        return response()->json($data);
    }

    public function admission_approve($id)
    {
        $data = Admission::where([
            'id' => $id
        ])->first();

        // $data2 = json_decode($data['DynamicFieldValue']);
        $data2 = $data['DynamicFieldValue'];
        return view('admissionApproval')->with('data', $data)->with('data2', $data2);
    }

    public function branchFilter(Request $req)
    {

        $branch_filter = $req->branch_filter;
        if ($branch_filter != null) {
            $data = DB::table('public.branch')
                ->where('program_id', session('program_id'))
                ->where('branch_id', $branch_filter)
                ->distinct('branch_id')
                ->get();
        }
        return response()->json($data);
    }

    public function approve_admission(Request $request)
    {
        $baseurl = $this->Server_Url();
        $db = config('database.db');
        $updateData = DB::table($db . '.admissions')
            ->where('id', $request->id)
            ->update(['bm_behavior' => $request->behavior, 'bm_financial_status' => $request->all_financial_status]);


        $doc_type = "admission";
        $role = session('roll');
        $db1 = DB::table($db . '.admissions')
            ->where($db . '.admissions.id', '=', $request->id)
            ->where($db . '.admissions.projectcode', session('projectcode'))
            ->first();
        $branchcode = $db1->branchcode;
        $loan_id = $db1->entollmentid;
        $projectcode = $db1->projectcode;
        $action = "Approve";
        $doc_id = $request->id;
        $pin = session('user_pin');

        $document_url = $baseurl . "DocumentManager?doc_id=$doc_id&projectcode=$projectcode&doc_type=admission&pin=$pin&role=$role&branchcode=$branchcode&action=$action";
        // dd($document_url);
        Log::channel('daily')->info('Document_url : ' . $document_url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $document_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $documentoutput = curl_exec($ch);
        curl_close($ch);

        $collectionfordocument = json_decode($documentoutput);
        // dd($collectionfordocument);


        if ($collectionfordocument != NULL) {
            if ($collectionfordocument->status == "E") {
                if (isset($collectionfordocument->errors)) {
                    if (isset($collectionfordocument->errors[0]->message)) {
                        $errors = $collectionfordocument->errors[0]->message;
                        return redirect()->back()->with('error', $errors);
                    } else if (isset($collectionfordocument->errors[0]->fieldErrors)) {
                        foreach ($collectionfordocument->errors[0]->fieldErrors as $row) {
                            $errors[] = [
                                "field" => $row->field,
                                "message" => $row->message
                            ];
                        }
                        return redirect()->back()->with('errors', $errors);
                    } else {
                        $errors = $collectionfordocument->errors;
                        return redirect()->back()->with('error', $errors);
                    }
                } else {
                    $errors = $collectionfordocument->message;
                    return redirect()->back()->with('error', $errors);
                }
            } else {
                return redirect('/operation/admission')->with('success', 'Action suucessful.');
            }
        } else {
            return redirect()->back()->with('error', 'Data does not send!');
        }
    }


    public function action_btn(Request $request)
    {
        $baseurl = $this->Server_Url();
        $db = config('database.db');
        $updateData = DB::table($db . '.admissions')
            ->where('id', $request->id)
            ->update(['bm_behavior' => $request->all_behavior, 'bm_financial_status' => $request->all_financial_status]);

        // $this->assessmentInsertion($request);
        $role = session('roll');
        $db1 = DB::table($db . '.admissions')
            ->where($db . '.admissions.id', '=', $request->id)
            ->where($db . '.admissions.projectcode', session('projectcode'))
            ->first();
        $branchcode = $db1->branchcode;
        $loan_id = $db1->entollmentid;
        $projectcode = $db1->projectcode;
        $action = $request->action;
        $doc_id = $request->id;
        $pin = session('user_pin');
        $comment = urlencode($request->comment);


        $document_url = $baseurl . "DocumentManager?doc_id=$doc_id&projectcode=$projectcode&doc_type=admission&pin=$pin&role=$role&branchcode=$branchcode&action=$action&comment=$comment";
        // dd($document_url);
        Log::channel('daily')->info('Document_url : ' . $document_url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $document_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $documentoutput = curl_exec($ch);
        curl_close($ch);

        $collectionfordocument = json_decode($documentoutput);
        // dd($collectionfordocument);

        if ($collectionfordocument != NULL) {
            return redirect('/operation/admission')->with('success', 'Action suucessful.');
        } else {
            return redirect()->back()->with('error', 'Data does not send!');
        }
    }
    public function Server_Url()
    {
        $db = config('database.db');
        $getServerUrl = DB::Table($db . '.server_url')->where('status', 1)->get();
        if ($getServerUrl->isEmpty()) {
            $ownbaseurl = '';
        } else {
            $ownbaseurl = $getServerUrl[0]->server_url;
        }
        return $ownbaseurl;
    }
}
