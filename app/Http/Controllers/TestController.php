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
use Exception;
use Illuminate\Support\Facades\Storage;
use File;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\LiveApiProgotiController;

header('Content-Type: application/json; charset=utf-8');

class TestController extends Controller
{
  private $db = 'dcs';        //dcs db name
  private $Topic = 'testsc';
  private $key = '5d0a4a85-df7a-scapi-bits-93eb-145f6a9902ae';
  public function Sync(Request $request)
  {
    $admissionarray = array();
    $loanarray = array();
    $surveyarray = array();
    $DataSetArray = array();
    $apikey = '7f30f4491cb4435984616d1913e88389';
    $key = "5d0a4a85-df7a-scapi-bits-93eb-145f6a9902ae";
    $db = $this->db;
    $token = Request::header('apiKey');
    $BranchCode = Request::input('branchCode');
    $ProjectCode = Request::input('projectCode');
    $LastSyncTime = Request::input('lastSyncTime');
    $LastSyncTime = date('Y-m-d H:i:s', strtotime('-60 seconds', strtotime($LastSyncTime)));
    $CurrentTime = Request::input('currentTime');
    $Appid = Request::header('appId');
    $Pin = Request::input('pin');
    $AppversionName = Request::header('appVersionName');
    $AppVersionCode = Request::header('appVersionCode');
    $CurrentTimes = date('Y-m-d H:i:s');
    Log::info("TestController Params-" . $token . '/' . $BranchCode . '/' . $Appid . '/' . $Pin . '/' . $LastSyncTime . '/' . $ProjectCode);
    try {
      $admissiondata =  $this->AdmissionDataSync($db, $BranchCode, $ProjectCode, $Pin, $LastSyncTime, $Appid);
      $DataSetArray['admissiondata'] = $admissiondata;
      $arrayFile = array("status" => "success", "time" => $CurrentTimes, "message" => "", "data" => $DataSetArray);
      $jsonFile = json_encode($arrayFile);
      $this->ZipFileCreate($db, $Pin, $BranchCode, $Appid, $CurrentTimes, $ProjectCode, $jsonFile);
      $mem_usage = memory_get_usage();
      echo "now Memory" . $mem_usage . "/";
      $mem_peak = memory_get_peak_usage();
      echo "MemoryPic" . $mem_peak;
    } catch (Exception $e) {
      $this->CUSTMSG($e->getMessage());
    }
  }
  public function AdmissionDataSync($db, $BranchCode, $ProjectCode, $Pin, $LastSyncTime, $Appid)
  {
    $admissiondatas = array();
    $polist = $this->PoList($db, $BranchCode, $ProjectCode, $Pin);
    dd($polist);
    foreach ($polist as $row) {
      $cono = $row->cono;
      $GetAdmission = DB::Table($db . ".admissions")->where('branchcode', $BranchCode)->where('assignedpo', $cono)->where('update_at', '>=', $LastSyncTime)->get();

      $admissiondatas[] = $GetAdmission;
    }
    //$GetAdmission = DB::Table($db . ".admissions")->where('branchcode', $BranchCode)->where('update_at', $LastSyncTime)->get();
    return $admissiondatas;
  }
  public function PoList($db, $BranchCode, $ProjectCode, $Pin)
  {

    $polist = DB::Table($db . '.polist')->where('branchcode', $BranchCode)->where('projectcode', $ProjectCode)->where('status', 1)->get();
    return $polist;
  }
  public function ZipFileCreate($db, $Pin, $BranchCode, $Appid, $CurrentTimes, $ProjectCode, $jsonFile)
  {
    $jsonData = "Zip File Create IN";
    $type = "Zip";
    //$this->LogCreate($BranchCode, $ProjectCode, $Pin, $type, $jsonData);
    $baseurl = '/var/www/html/json/';
    // echo $baseurl;
    $mainFile = $baseurl . $Pin . 'dcs.zip';
    //echo $mainFile;
    if (is_file($mainFile)) {
      if (!unlink($mainFile)) {
        //   echo ("Error deleting $mainFile");
      }
    }
    $writeFile = $baseurl . $Pin . 'dcsresults.json';
    if (is_file($writeFile)) {
      if (!unlink($writeFile)) {
        //   echo ("Error deleting $mainFile");
      }
    }
    $fp = fopen($baseurl . $Pin  . 'dcsresults.json', 'w');
    fwrite($fp, $jsonFile);
    fclose($fp);
    $zip = new ZipArchive;
    if ($zip->open($mainFile, ZipArchive::CREATE) === TRUE) {
      // Add files to the zip file
      $zip->addFile($writeFile, $Pin  . 'dcsresults.json');
      //$zip->addFile('/var/www/html/json/'.$PIN.'transtrail.json',$PIN.'transtrail.json');
      //$zip->addFile('test.pdf', 'demo_folder1/test.pdf');

      // All files are added, so close the zip file.
      $zip->close();
    }
    if (is_file($writeFile)) {
      if (!unlink($writeFile)) {
        //   echo ("Error deleting $mainFile");
      }
    }
    $message = array("status" => "DCS", "time" => $CurrentTimes, "message" => "Please Download File!!");
    $json2 = json_encode($message);
    $jsonData = "Zip File Create OUT";
    $type = "Zip";
    //$this->LogCreate($BranchCode, $ProjectCode, $Pin, $type, $jsonData);
    Log::info("message-" . $json2);
    echo $json2;
    die;
  }
}
