<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class CellingController extends Controller
{
    public function index(){
        $db = config('database.db');   
        $cellingData = DB::table($db.'.celing_configs')->orderBy('id')->get();
        // dd($cellingData);
        return view('CelingConfig')->with('cellingData', $cellingData);
    }
}
