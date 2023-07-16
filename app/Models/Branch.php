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
}
