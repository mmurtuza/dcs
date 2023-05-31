<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Process;

class ProcessController extends Controller
{
    public function index(){
        return view('Process');
    }

    public function store(Request $request){
        $data=new Process();
        $data->process=$request->get('process');
        $data->save();

        return redirect()->back()->with('success','Successfully saved');

    }
}