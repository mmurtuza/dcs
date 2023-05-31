<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Survey;

class SurveyController extends Controller
{
    public function index(){
        return view('Survey');
    }

    public function store(Request $request)
	{
			
			$data= new Survey();
			$data->entollmentid= $request->get('enrolmentid');
			$data->name= $request->get('name');
			$data->mainidtypeid= $request->get('mainidtype');
			$data->idno= $request->get('mainidnumber');		
			$data->phone= $request->get('phonenumber');
            $data->status= $request->get('status');
			$data->label= $request->get('level');		
			$data->targetdate= $request->get('follow-upDate');
            $data->refferdbyid= $request->get('referreredname');
			$data->save();
			return redirect()->back();
			
	}
}
