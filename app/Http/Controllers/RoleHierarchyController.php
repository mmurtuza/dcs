<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoleHierarchy;
use App\Models\Role;
use App\Models\Project;
use DB;

class RoleHierarchyController extends Controller
{

    public function index()
    {
        $projectcode = session('projectcode');
        $details = RoleHierarchy::where('projectcode', $projectcode)->orderBy('position', 'desc')->get();
        return view('RoleHierarchy')
            ->with('details', $details);
    }
    // public function store(Request $request){

    //     $data=new RoleHierarchy();
    //     $data-> projectcode=$request->get('projectcode');
    //     $data-> role=$request->get('role');
    //     $data-> position=$request->get('position');
    //     $data-> designation=$request->get('designation');
    //     $data->save();
    //     return redirect()->back()->with('success', 'Record has been updated.');
    // }

    public function update(Request $request)
    {
        $projectcode = session('projectcode');        
        $array_reverse = array_reverse($request->designation); 
        $finalArray=array();      
        foreach ($array_reverse as $key => $value) {
            $data = RoleHierarchy::select('status')->where(['designation'=>$value,'projectcode'=>$projectcode])->first();
            if($data->status == "0") 
            {
               RoleHierarchy::where(['designation'=>$value,'projectcode'=>$projectcode])->update(['position' => null]);
                // $mainArray[]= $value;
                unset($array_reverse[$key]); 
                $finalArray = array_values($array_reverse);               
            } 
                       
        } 
        if($finalArray == null)
        {
            $finalArray=$array_reverse;
        } 
        foreach($finalArray as $key => $value)
        {
            RoleHierarchy::where(['designation'=>$value,'projectcode'=>$projectcode])->update(['position' => $key]);
        }
        return redirect()->back()->with('success', 'Record has been updated.');
    }

    public function username(Request $request)
    {
        $details = Role::where([
            'role' => $request->role
        ])->get();

        return response()->json($details);
    }

    // foreach ($array_reverse as $key => $value) {
    //     $checkData= RoleHierarchy::select('status')->where([
    //         'designation' => $value, 'projectcode' => session('projectcode')
    //     ]);
    //     if($checkData->status == 0)
    //     {
    //         i
    //     }
    //     // ->update(['position' => $key])
    // }

    public function updateStatus(Request $request)
    {
        $role = RoleHierarchy::find($request->role_id);
        $role->status = $request->status;  
        $role->save();      
        $projectcode = session('projectcode');        
        $array_reverse = array_reverse($request->designation);   
        $finalArray=array();     
        foreach ($array_reverse as $key => $value) {
          // $data = DB::select(DB::raw("select status from dcs.role_hierarchies where designation = '$value' and projectcode='$projectcode'"));
            $data = RoleHierarchy::select('status')->where(['designation'=>$value,'projectcode'=>$projectcode])->first();
            if($data->status == "0") 
            {
               RoleHierarchy::where(['designation'=>$value,'projectcode'=>$projectcode])->update(['position' => null]);
                // $mainArray[]= $value;
                unset($array_reverse[$key]); 
                $finalArray = array_values($array_reverse);               
            }           
        }
        if($finalArray == null)
        {
            $finalArray=$array_reverse;
        }         
        foreach($finalArray as $key => $value)
        {
            RoleHierarchy::where(['designation'=>$value,'projectcode'=>$projectcode])->update(['position' => $key]);
        }
        
        echo json_encode("successfull");
        
    }
}
