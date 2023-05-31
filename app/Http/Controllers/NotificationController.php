<?php

namespace App\Http\Controllers;
// use Illuminate\Support\Facades\Request;
use Illuminate\Http\Request;
use Input;
use Symfony\Component\HttpFoundation\StreamedResponse;
use view;
use DateTime;
// use Illuminate\Support\Facades\Input;
use DB;
use App\Models\Role;
use App\Models\ActionList;
use App\Models\Notification;
use App\Models\RoleHierarchy;
use App\Models\Process;
use App\Models\Project;

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
/*header("Access-Control-Allow-Origin: *");
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');*/

class NotificationController extends Controller
{
	public function Notification(Request $req)
	{
		$db = config('database.db');
		session_start();
		$program_id = Session::get('program_id');
		$roll = Session::get('roll');
		$asid = Session::get('asid');
		// $pin='47817';

		$count = 0;
		$getnotification = DB::Table($db . '.message_ques')->where('roleid', $roll)->where('programid', $program_id)->where('associateid', $asid)->get();
		if (!$getnotification->isEmpty()) {
			$count = DB::Table($db . '.message_ques')->where('roleid', $roll)->where('programid', $program_id)->where('associateid', $asid)->where('readstatus', 0)->count();
		}
		// dd($count);
		$_SESSION['WEB'] = $count;
		$_SESSION['Cnt'] = $_SESSION['WEB'];
		return view('dataload');
	}


	public function getNotification(Request $req)
	{
		$db = config('database.db');
		$program_id = Session::get('program_id');
		$roll = Session::get('roll');
		$asid = Session::get('asid');
		// $pin='47817';
		$currentdate = date('m/d/Y');
		$msg = [];
		$data = DB::Table($db . '.message_ques')->where('roleid', $roll)->where('programid', $program_id)->where('associateid', $asid)->latest()->get();
		if (!$data->isEmpty()) {
			foreach ($data as $row) {
				$dataset = [];
				$dataset['id'] = $row->id;
				$dataset['message'] = $row->message;
				$dataset['readstatus'] = $row->readstatus;
				$dataset['docreff'] = $row->docreff;
				$dataset['created_at'] = Carbon::parse($row->created_at)->addMinutes(360)->diffForHumans(); //added 6 hour for server time
				$msg[] = $dataset;
			}
			return response()->json($msg);
		}
	}

	public function readNotification(Request $request)
	{
		$db = config('database.db');
		$id = $request->input('id');
		DB::table($db . '.message_ques')
			->where('id', $id)
			->update(['readstatus' => 1]);
	}

	public function Notification2(Request $req)
	{

		$response = new StreamedResponse(function () {
			echo 'data: ' . 'Hello' . "\n\n";
			ob_flush();
			flush();
			sleep(3);
			while (true) {
			}
		});

		$response->headers->set('Content-Type', 'text/event-stream');
		$response->headers->set('X-Accel-Buffering', 'no');
		$response->headers->set('Cach-Control', 'no-cache');
		return $response;
		/*$response = new StreamedResponse(function() {
			while(true) {
				$roll =1;
				$check = DB::Table($db.'.notifications')->where('status',1)->get();
				if($check->isEmpty())
				{
					
				}
				else
				{
					$roleid = $check[0]->roleid;
					$sms = $check[0]->sms;
					$web = $check[0]->web;
					$email = $check[0]->email;
					$inapp = $check[0]->inapp;
					if(!empty($roleid))
					{
						$roleexplde = explode(",",$roleid);
						$count = count($roleexplde);
						for($i=0;$i<$count;$i++)
						{
							$rolid = $roleexplde[$i];
							if($rolid==$roll)
							{
								if($sms==1)
								{
									
								}
								else if($web==1)
								{
									$notificationmsg = DB::table($db.'.notifications')->where('status',1)->where('web',1)->get();
									$notificationcnt = DB::table($db.'.notifications')->where('status',1)->where('web',1)->count();
									if($notificationmsg->isEmpty())
									{
										
									}
									else
									{
										$msg = $notificationmsg[0]->msgcontent;
										$cnt = $notificationcnt;
										//echo $cnt.",".$msg;
									}
								}
								else if($email==1)
								{
									
								}
								else if($inapp==1)
								{
									
								}
							}
						}
					}
					echo 'data: ' . $roleid . "\n\n";
				}
			 
			  ob_flush();
			  flush();
			  sleep(3);
			}
		});

		$response->headers->set('Content-Type', 'text/event-stream');
		$response->headers->set('X-Accel-Buffering', 'no');
		$response->headers->set('Cach-Control', 'no-cache');
		return $response;*/
	}
	public function Notification1(Request $req)
	{
		$roll = 1;
		$db = config('database.db');
		$response = new StreamedResponse(function () {
			while (true) {
				$getnotification = DB::Table($db . '.notifications')->where('status', 1)->get();
				if ($getnotification->isEmpty()) {
				} else {
					$roleid = $getnotification[0]->roleid;
					$sms = $getnotification[0]->sms;
					$web = $getnotification[0]->web;
					$email = $getnotification[0]->email;
					$inapp = $getnotification[0]->inapp;
					if (!empty($roleid)) {
						$roleexplde = explode(",", $roleid);
						$count = count($roleexplde);
						for ($i = 0; $i < $count; $i++) {
							$rolid = $roleexplde[$i];
							if ($rolid == $roll) {
								if ($sms == 1) {
								} else if ($web == 1) {
									$notificationmsg = DB::table($db . '.notifications')->where('status', 1)->where('web', 1)->get();
									$notificationcnt = DB::table($db . '.notifications')->where('status', 1)->where('web', 1)->count();
									if ($notificationmsg->isEmpty()) {
									} else {
										$msg = $notificationmsg[0]->msgcontent;
										$cnt = $notificationcnt;
										echo $cnt . "," . $msg;
									}
								} else if ($email == 1) {
								} else if ($inapp == 1) {
								}
							}
						}
					}
				}
				ob_flush();
				flush();
				sleep(3);
			}
		});
		$response->headers->set('Content-Type', 'text/event-stream');
		$response->headers->set('X-Accel-Buffering', 'no');
		$response->headers->set('Cach-Control', 'no-cache');
		return $response;
	}

	public function index()
	{
		$projectcode = session('projectcode');
		$actionlist = ActionList::where('projectcode', $projectcode)->get();
		$process = Process::all();
		$notifications = Notification::where('projectid', $projectcode)->get();
		$roleHierarchy = RoleHierarchy::where('projectcode', $projectcode)->whereNotNull('position')->orderBy('position')->get();
		// $notifications=Notification::distinct('roleid')->get();
		return view('Notification')->with('roleHierarchy', $roleHierarchy)
			->with('process', $process)
			->with('actionlist', $actionlist)
			->with('notifications', $notifications);
	}

	public function store(Request $request)
	{
		// dd($project_code[0]['projectCode']);
		// die;

		$notification = Notification::where([
			'roleid' => $request->get('roleid'),
			'actionid' => $request->get('actionid')
		])->first();
		$recieverlist = implode(",", $request->get('recieverlist'));
		if ($notification != null) {
			return redirect()->back()->with('error', 'Record already exists.');
		} else {
			$data = new Notification();
			$data->roleid = $request->get('roleid');
			$data->projectid = session('projectcode');
			$data->sms = $request->get('sms');
			$data->email = $request->get('email');
			$data->inApp = $request->get('inapp');
			$data->actionid = $request->get('actionid');
			$data->recieverlist = $recieverlist;
			$data->msgcontent = $request->get('msgcontent');
			$data->createdby = session('user_pin');
			$data->save();
			return redirect()->back()->with('success', 'successfuly saved');
		}
	}

	public function view(Request $request)
	{
		$projectcode = session('projectcode');
		$db = config('database.db');
		$datas = DB::table($db . '.notifications')
			->select($db . '.notifications.*', $db . '.action_lists.actionname',  $db . '.action_lists.process_id')
			->join($db . '.action_lists', $db . '.notifications.actionid', '=', $db . '.action_lists.id')
			->where($db . '.action_lists.process_id', '=', $request->process)
			->where($db . '.notifications.roleid', '=', $request->roleid)
			->where('projectid', $projectcode)
			->get();

		// 	if (!$datas->isEmpty())
		//    {

		//    }
		return response()->json($datas);
	}
	public function process(Request $request)
	{
		$projectcode = session('projectcode');
		$datas = ActionList::where([
			'process_id' => $request->process
		])->where('projectcode', $projectcode)->orderBy('process_id')->get();

		return response()->json($datas);
	}

	public function edit($id)
	{
		$projectcode = session('projectcode');
		$roleHierarchy = RoleHierarchy::where('projectcode', $projectcode)->whereNotNull('position')->orderBy('position')->get();
		$actionlist = ActionList::where('projectcode', $projectcode)->orderBy('process_id')->get();
		$process = Process::all();
		$data = Notification::find($id);
		return view('Notification-edit')->with('data', $data)
			->with('roleHierarchy', $roleHierarchy)
			->with('actionlist', $actionlist)
			->with('process', $process);
	}

	public function update(Request $request, $id)
	{
		$recieverlist = implode(",", $request->get('recieverlist'));

		$data = Notification::findorFail($id);
		$data->roleid = $request->get('roleid');
		$data->projectid = session('projectcode');
		$data->sms = $request->get('sms');
		$data->email = $request->get('email');
		$data->inApp = $request->get('inapp');
		$data->actionid = $request->get('actionid');
		$data->recieverlist = $recieverlist;
		$data->msgcontent = $request->get('msgcontent');
		$data->createdby = session('user_pin');
		$data->save();
		return redirect()->back()->with('success', 'successfuly saved');
	}

	public function delete($id)
	{
		$data = Notification::find($id);
		$data->delete();
		return redirect()->back()->with('success', 'Data deleted successfuly');
	}
}
