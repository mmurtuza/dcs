<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Auth;
use App\Models\Role;
use App\Models\Project;
use App\Models\Process;
use App\Models\RoleHierarchy;
use DB;

class AuthConfigController extends Controller
{
    
    public function index(){
        $db = config('database.db');        
        $roleHierarchy=RoleHierarchy::where('projectcode',session('projectcode'))->whereNotNull('position')->orderBy('position')->get();
        $processes=Process::all();
        $auths=DB::table($db.'.auths')
                    ->join($db.'.processes',$db.'.auths.processId','=',$db.'.processes.id')
                    ->where('projectcode',session('projectcode'))
                    ->distinct($db.'.auths.roleId')
                    ->get();
        
        // $roleH=DB::table($db.'.role_hierarchies')->where('position',1)->where('projectcode',session('projectcode'))->first();
        return view('AuthConfig')
        ->with('roleHierarchy', $roleHierarchy)
                ->with('processes', $processes)
                ->with('auths', $auths);
    }

    public function store(Request $request)
    {
        
        $auth = Auth::where([
			'roleId' => $request->get('roleid'),
            'processId' => $request->get('processId'),
            'projectcode' =>session('projectcode')
			 ])->first();
        if ($auth != null) {
            return redirect()->back()->with('error', 'Record already exists.');
        }
        else{
            
            $data= new Auth();
            $data->roleId =$request->get('roleid');
            $data->projectcode =session('projectcode');
            $data->processId =$request->get('processId');
            $data->isAuthorized =$request->get('isAuthorized');
            $data->createdBy = session('user_pin');
            $data->save();
            return redirect()->back()->with('success', 'Record has been save.');
        }
       
    }

    public function edit(Request $request)
    {
        $db = config('database.db'); 
       $datas = DB::table($db.'.auths')
                ->join($db.'.processes',$db.'.auths.processId','=',$db.'.processes.id')
                ->where('roleId', $request->role)
                ->where('projectcode',session('projectcode'))
                ->orderBy($db.'.auths.processId')
                ->get();
        return response()->json($datas);
        
    }

    public function update(Request $request){
        $auth1 = Auth::where([
            'roleId' => $request->get('roleid'),
            'projectcode' =>session('projectcode')
             ])->update(['isAuthorized'=> '0']);
        $process=$request->isAuthorized;
        if($process != null)
        {
        $count=count($process);
        for($i=0; $i<$count; $i++)
        {
            $auth = Auth::where([
                'roleId' => $request->get('roleid'),
                'processId' => $process[$i],
                'projectcode' =>session('projectcode')
                 ])->update(['isAuthorized'=> 'on']);
            
        }
        }
        return redirect()->back()->with('success', 'Record has been updated.');
    }
}
