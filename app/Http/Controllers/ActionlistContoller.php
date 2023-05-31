<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActionList;
use App\Models\Process;
use App\Models\Project;

class ActionlistContoller extends Controller
{
    public function index(){
        $Projects= Project::all();
        $processes= Process::all();
        return view('ActionList')->with('processes', $processes)
        ->with('Projects', $Projects);
    }

    public function store(Request $request)
    {
        $data= new ActionList();
        $data->projectcode = $request->get('projectcode');
        $data->actionname = $request->get('actionname');
        $data->process = $request->get('process');
        $data->save();
        return redirect()->back()->with('success', 'Record has been updated.');
    }
}
