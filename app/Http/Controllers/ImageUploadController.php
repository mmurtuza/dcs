<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use DB;
use Carbon\Carbon;
use Log;

class ImageUploadController extends Controller
{
  public function ImageUploadOthers(Request $request)
  {
    // $db = $this->db;
    $appid = $request->input('appid');
    $apikey = $request->input('apikey');
    $image = $request->input('file');
    $branchcode = $request->input('branchcode');
    $dirpath = '/data/uploads/' . $branchcode;
    if (!file_exists($dirpath)) {
      // echo "Path OK";
      mkdir("/data/uploads/" . $branchcode, 0777);
      //echo "The directory $branchcode was successfully created.";
    }
    $uploaddir = '/data/uploads/' . $branchcode . '/';
    $baseurl = 'http://dcs.brac.net/dcsimg/' . $branchcode . '/';
    $time = date('Y-m-d h:i:s');
    $uploadfile = $uploaddir . $time . basename($_FILES['file']['name']);
    $responsefile = $baseurl . $time . basename($_FILES['file']['name']);
    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
      $result = array("status" => "S", "message" => "", "data" => $responsefile);
      echo json_encode($result);
    } else {
      $result = array("status" => "E", "message" => "Failed Upload");
      echo json_encode($result);
    }
  }
}
