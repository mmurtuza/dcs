<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Survey;
use Illuminate\Support\Facades\Http;

class SurveyTableController extends Controller
{
    public function index(){
        
    
        return view('survey_table');
    }

    public function survey_info(){
        $data = Survey::all();
        return datatables($data)->addColumn('action', function ($data) {
        })->toJson();
    }

    public function survey_api(){
        $datas= Http::get('http://scm.brac.net/dcs/AllSurveyData')->json();
        $count=Count($datas['data']);
        // $show=$datas['data'][3]['dynamicfieldvalue']["fieldName"];
        // dd($show);
        // $json = json_decode(file_get_contents('http://scm.brac.net/dcs/AllSurveyData'));
        // dd(json_decode($json->data[1]->dynamicfieldvalue));
        return view('SurveyApi',['datas'=>$datas])->with('count',$count);
    }

    public function dynamic_value(Request $request){
        $id=$request->id;
        $json = json_decode(file_get_contents('http://scm.brac.net/dcs/AllSurveyData'));
        $data=json_decode($json->data[$id-1]->dynamicfieldvalue);
        return response()->json($data);
    }

}
