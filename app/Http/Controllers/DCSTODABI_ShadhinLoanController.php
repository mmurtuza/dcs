<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use view;
use DateTime;
use Illuminate\Support\Facades\Input;
use DB;

date_default_timezone_set('Asia/Dhaka');

ini_set('memory_limit', '3072M');
ini_set('max_execution_time', 1800);

use ZipArchive;
use Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use File;
use Illuminate\Support\Facades\Session;
//use App\Http\Controllers\TestingController_Version;
header('Content-Type: application/json; charset=utf-8');

class DCSTODABI_ShadhinLoanController extends Controller
{
  private $db = 'dcs';
  public function Get_ShadhinLoan_Data(Request $request)
  {
    $db = $this->db;
    $BranchCode = Request::input('branchcode');
    $ProjectCode = (int)Request::input('projectcode');
    $getData = DB::table($db . '.product_project_member_category')->where('branchcode', $BranchCode)->where('projectcode', $ProjectCode)->get();
    $json = array("code" => 200, "message" => "", "data" => $getData);
    return json_encode($json);
  }
}
