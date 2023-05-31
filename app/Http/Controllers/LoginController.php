<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class LoginController extends Controller
{
	public function weblogin(Request $req)
	{
		$db = config('database.db');
		$req->session()->flush();
		$roll = 0;
		$pin = $req->input('user_pin');
		$roll = $req->input('roll_id');
		$name = $req->input('name');
		$user_id = $req->input('user_id');
		$as_id = $req->input('as_id');
		$program_id = $req->input('program_id');

		$roll_array = array(2, 3, 4, 7, 10);
		if (!in_array($roll, $roll_array)) {
			return redirect('https://trendx.brac.net/home');
		}



		// if (($roll != 10) or ($roll != 7)) {
		// }

		if (($roll == 10) or ($roll == 7)) {
			$getRole = DB::table($db . '.role_hierarchies')->select('designation', 'position')->where('projectcode', '015')->where('trendxrole', $roll)->first();
		} else {

			if ($program_id == 1) {
				$req->session()->put('project', 'Dabi');
				$req->session()->put('projectcode', '015');
				$projectcode = '015';
			} elseif ($program_id == 5) {
				$req->session()->put('project', 'Progoti');
				$req->session()->put('projectcode', '060');
				$projectcode = '060';
			}
			$getRole = DB::table($db . '.role_hierarchies')->select('designation', 'position')->where('projectcode', $projectcode)->where('trendxrole', $roll)->first();
		}

		$req->session()->put('role_designation', $getRole->designation);
		$token = uniqid();
		$req->session()->put('token', $token);
		$req->session()->put('username', $name);
		$req->session()->put('erp_user_role', $roll);
		$req->session()->put('roll', $getRole->position);
		$req->session()->put('asid', $as_id);
		$req->session()->put('user_pin', $pin);
		$req->session()->put('program_id', $program_id);
		$req->session()->put('user_id', $user_id);

		if ($roll == 7) {
			$req->session()->put('project', "Dabi");
			$req->session()->put('projectcode', '015');
			$req->session()->put('program_id', '1');
			$getRole = DB::table($db . '.role_hierarchies')->select('designation', 'position')->where('projectcode', session('projectcode'))->where('role', session('erp_user_role'))->first();
			$req->session()->put('role_designation', $getRole->designation);
			$req->session()->put('roll', $getRole->position);
			$checkAdmin = DB::table('dcs.admin_config')->where('userpin', $pin)->where('status', 1)->first();
			if ($checkAdmin == null) {
				return redirect('https://trendx.brac.net/');
			} else {
				if ($checkAdmin->role == "1") {
					$req->session()->put('adminRole', "1");
				} else {
					$req->session()->put('adminRole', "0");
				}
			}
		}

		if ($roll == 10) {
			$req->session()->put('project', "Dabi");
			$req->session()->put('projectcode', '015');
			$req->session()->put('program_id', '1');
			$getRole = DB::table($db . '.role_hierarchies')->select('designation', 'position')->where('projectcode', session('projectcode'))->where('role', session('erp_user_role'))->first();
			$req->session()->put('role_designation', $getRole->designation);
			$req->session()->put('roll', $getRole->position);
		}
		if ($program_id == '1') {
			return redirect('/dashboard');
		} else if ($program_id == '5') {
			//return redirect('http://dcsprogoti.brac.net/dcs/weblogin&user_pin=dcstestprogotiam2&roll_id=2&name=&user_id=10040&as_id=713&email=&phone=&program_id=5');
			//dd("http://dcsprogoti.brac.net/dcs/weblogin?user_pin=$pin&roll_id=$roll&name=$name&user_id=$user_id&as_id=$as_id&program_id=5");
			return redirect("http://dcsprogoti.brac.net/dcs/weblogin?user_pin=$pin&roll_id=$roll&name=$name&user_id=$user_id&as_id=$as_id&program_id=5");
		} else {
			return redirect('/dashboard');
		}
	}
}
