<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use View;
use DataTables;
class MainController extends Controller
{
    public function UserList(Request $req)
    {
        return view('Reports.UserList');
    }
    public function userListLoad()
	{
        //return datatables()->of(DB::table('testusers'))->toJson();
        //return datatables(DB::table('testusers'))->toJson();
        //return datatables()->query(DB::table('testusers')->get())->toJson();
       $data = DB::table('testusers')->get();
       return datatables($data)->addColumn('action', function ($data) {
        return '<a href="#edit-'.$data->id.'" class="btn btn-light">Edit</a>';
        })->toJson();
    //return datatables($users)->toJson();
    
      /* $db=config('database.database');
       $users=DB::table($this->db.'.user')->latest()->get();
		return datatables($users)->addColumn('cluster_name', function ($users) {
            $cluster_name='';
            $cluster_id=$users->cluster_id;
            $cluster_ary=DB::table('mnw_progoti.cluster')->where('cluster_id',$cluster_id)->first();
            if($cluster_ary){
                $cluster_name=$cluster_ary->cluster_name;
                return $cluster_name;
            }
            return $cluster_name;
        })->addColumn('action', function ($users) {
            return '<a href="UserEdit?id='.$users->id.'" class="btn btn-light btn-sm">Edit</a> <a href="UserDelete?id='.$users->id.'" class="btn btn-danger btn-sm" onclick="return confirm('."'".'Are you sure you want to delete the user?'."'".')">Delete</a>';
        })->toJson();*/
	}
}
