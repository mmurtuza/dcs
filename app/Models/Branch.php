<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'public.branch';
    protected $guarded = [];

    public static function getBranchForRole(string $role_designation)
    {
        $branch = [];
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

        // if($role_designation === 'AM' || $role_designation === 'RM' || $role_designation === 'DM' && $branch->isEmpty())
        // {
        //     return redirect()->back()->with('error', 'Data does not match.');
        // }

        return $branch;
    }

    public static function getBranch($division = null, $region = null, $area = null, $branch = null){
        $role_designation =session('role_designation');
        $asid = session('asid');
        return Branch::select('branch_id')
                ->where('program_id', session('program_id'))
                ->distinct('branch_id')
                ->when( $role_designation ==='AM', function($query) use($asid){
                    return $query->where('area_id', $asid);
                })
                ->when( $role_designation ==='RM', function($query) use($asid){
                    return $query->where('region_id', $asid);
                })
                ->when( $role_designation ==='DM', function($query) use($asid){
                    return $query->where('division_id', $asid);
                })
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
