<?php

namespace App\Models;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductProjectMemberCategory;
use App\Models\Polist;

class Loans extends Model
{
    protected $table = 'dcs.loans';
    protected $guarded = ['updated_at',];

    protected $casts = [
        'DynamicFieldValue' => 'array'
    ];

    public static function fetchLoans(Request $request, array $polist= [])
    {
        $to_date = $request->input('dataTo') ?: date('Y-m-d');
        $from_date =$request->input('dateFrom') ?: date('Y-m-01');
        $role_designation = session('role_designation');
        $status = $request->get('status') ?? null;
        $erpstatus = $request->get('ErpStatus') ?? null;
        $roleData = ($role_designation === 'AM' || $role_designation === 'RM' || $role_designation === 'DM') ?: false;

        // $polist = Loans::select('assignedpo')->whereIn("branchcode", $getbranch)->distinct("assignedpo")->get()->pluck("assignedpo")->toArray();

        return self::select(
            'loans.id',
            'loans.orgno',
            'loans.orgmemno',
            'loans.propos_amt',
            'loans.loan_type',
            'loans.time',
            'loans.branchcode',
            'product_project_member_category.productname',
            'polist.coname'
        )
            ->distinct('loans.id')
            // ->when(!empty($getbranch), function ($query) use ($getbranch){
            //     return $query->whereIn('loans.assignedpo', $getbranch);
            // })
            ->when(!empty($request->input('reciverrole')), function ($q) use ($request) {
                return $q->where('loans.reciverrole', $request->input('reciverrole'));
            })
            ->when(empty($request->input('reciverrole')), function ($q) {
                return $q->where('loans.reciverrole', '!=', '0');
            })
            ->where('loans.projectcode', session('projectcode'))
            ->whereDate('loans.time', '>=', $from_date)
            ->whereDate('loans.time', '<=', $to_date)
            ->when(!empty($request->input('po')), function ($query) use ($request) {
                $query->where('assignedpo', $request->input('po'));
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
                $query->where('loans.status', (string) $status);
            })
            ->when((empty($request->input('po')) && (!empty($request->input('division')) || $roleData)), function ($query) use ($polist) {
                return $query->whereIn('loans.assignedpo', $polist);
            })
            ->leftJoin('dcs.product_project_member_category', function ($join) {
                $join->on(DB::raw('CAST(loans.loan_product AS INT)'), '=', 'product_project_member_category.productid');
            })
            ->leftJoin('dcs.polist', function ($join) {
                $join->on('loans.assignedpo', '=', 'polist.cono');
            })
            ->groupBy('loans.id',
                'loans.orgno',
                'loans.orgmemno',
                'loans.propos_amt',
                'loans.loan_type',
                'loans.time',
                'loans.branchcode',
                'product_project_member_category.productname',
                'polist.coname'
            )
            ->paginate(10);

            }
        }
