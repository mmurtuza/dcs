<?php

namespace App\Http\Controllers;

use view;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use DB;
use App\Http\Requests;

ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 500);

use ZipArchive;
use Log;

header('Content-Type: text/html; charset=utf-8');
class POListController extends Controller
{
  public function Get_PO_From_SyncCloud(Request $request)
  {
    $db = config('database.db');
    $url = "http://scm.brac.net/sc/GetDabiActivePO";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $documentoutput = curl_exec($ch);
    curl_close($ch);

    $polistdecode = json_decode($documentoutput);

    if (!empty($polistdecode)) {
      foreach ($polistdecode as $row) {
        $cono = $row->cono;
        $status = $row->status;
        $branchcode = $row->branchcode;
        $checkpo = DB::Table($db . '.polist')->where('cono', $cono)->where('branchcode', $branchcode)->get();
        if ($checkpo->isEmpty()) {
          $insert = DB::Table($db . '.polist')->insert(['cono' => $row->cono, 'coname' => $row->coname, 'sessionno' => $row->sessionno, 'opendate' => $row->opendate, 'openingbal' => $row->openingbal, 'password' => $row->password, 'emethod' => $row->emethod, 'cashinhand' => $row->cashinhand, 'enteredby' => $row->enteredby, 'deviceid' => $row->deviceid, 'status' => $row->status, 'branchcode' => $row->branchcode, 'branchname' => $row->branchname, 'projectcode' => $row->projectcode, 'desig' => $row->desig, 'lastposynctime' => $row->lastposynctime, 'sl_no' => $row->sl_no, 'clearstatus' => $row->clearstatus, 'abm' => $row->abm, 'mobileno' => $row->mobileno, 'sls' => $row->sls, 'checklogin' => $row->checklogin, 'imei' => $row->imei, 'qsoftid' => $row->qsoftid, 'trxsl' => $row->trxsl, 'admindeviceid' => $row->admindeviceid, 'upgdeviceid' => $row->upgdeviceid]);
        } else {
          $checkstatus = $checkpo[0]->status;
          $id = $checkpo[0]->id;
          if ($status != $checkstatus) {
            $insert = DB::Table($db . '.polist')->where('id', $id)->update(['cono' => $row->cono, 'coname' => $row->coname, 'sessionno' => $row->sessionno, 'opendate' => $row->opendate, 'openingbal' => $row->openingbal, 'password' => $row->password, 'emethod' => $row->emethod, 'cashinhand' => $row->cashinhand, 'enteredby' => $row->enteredby, 'deviceid' => $row->deviceid, 'status' => $row->status, 'branchcode' => $row->branchcode, 'branchname' => $row->branchname, 'projectcode' => $row->projectcode, 'desig' => $row->desig, 'lastposynctime' => $row->lastposynctime, 'sl_no' => $row->sl_no, 'clearstatus' => $row->clearstatus, 'abm' => $row->abm, 'mobileno' => $row->mobileno, 'sls' => $row->sls, 'checklogin' => $row->checklogin, 'imei' => $row->imei, 'qsoftid' => $row->qsoftid, 'trxsl' => $row->trxsl, 'admindeviceid' => $row->admindeviceid, 'upgdeviceid' => $row->upgdeviceid]);
          }
        }
      }
    }
  }
}
