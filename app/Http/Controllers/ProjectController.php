<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use DB;

class ProjectController extends Controller
{
    public function index()
    {
        return view('Project');
    }

    public function store(Request $request)
    {
        $db = config('database.db');
        $projectcode = $request->get('projectCode');
        $projectcode = str_pad($projectcode, 3, "0", STR_PAD_LEFT);
        $created_by = session('user_pin');
        $created_at = date('Y-m-d h:i:s');
        $formconfig = DB::table($db . '.form_configs')->where('projectcode', '015')->get();
        $actionlists = DB::table($db . '.action_lists')->where('projectcode', '015')->get();
        $rolehierarchies = DB::table($db . '.role_hierarchies')->where('projectcode', '015')->get();
        $notifications = DB::table($db . '.notifications')->where('projectid', '015')->get();
        $auths = DB::table($db . '.auths')->where('projectcode', '015')->get();


        $project = Project::where([
            'projectCode' => $request->get('projectCode')
        ])->first();
        if ($project != null) {
            return redirect()->back()->with('error', 'Record already exists.');
        } else {
            DB::beginTransaction();

            try {
                foreach ($auths as $row) {
                    $singledata = array(
                        'roleId' => $row->roleId,
                        'projectcode' => $projectcode,
                        'isAuthorized' => $row->isAuthorized,
                        'createdBy' => $created_by,
                        'created_at' =>  $created_at,
                        'updated_at' =>  $created_at,
                        'processId' => $row->processId,
                    );
                    // DB::table($db . '.auths')->insert($singledata);
                }

                foreach ($formconfig as $row) {
                    $singledata = array(
                        'projectcode' => $projectcode,
                        'formID' => $row->formID,
                        'groupLabel' => $row->groupLabel,
                        'lebel' => $row->lebel,
                        'dataType' => $row->dataType,
                        'captions' => $row->captions,
                        'values' => $row->values,
                        'columnType' => $row->columnType,
                        'displayOrder' => $row->displayOrder,
                        'status' => $row->status,
                        'groupNo' => $row->groupNo,
                        'createdby' => $created_by,
                        'created_at' =>  $created_at,
                        'updated_at' =>  null,
                        'loanProduct' => $row->loanProduct
                    );
                    // DB::table($db . '.form_configs')->insert($singledata);
                }
                foreach ($actionlists as $row) {
                    $singledata = array(
                        'projectcode' => $projectcode,
                        'actionname' => $row->actionname,
                        'process_id' => $row->process_id,
                        'created_at' => $created_at,
                        'updated_at' => null
                    );
                    // DB::table($db . '.action_lists')->insert($singledata);
                }
                foreach ($rolehierarchies as $row) {
                    $singledata = array(
                        'projectcode' => $projectcode,
                        'role' => $row->role,
                        'position' => $row->position,
                        'designation' => $row->designation,
                        'created_at' => $created_at,
                        'updated_at' => null,
                        'status' => $row->status
                    );
                    // DB::table($db . '.role_hierarchies')->insert($singledata);
                }

                foreach ($notifications as $row) {
                    $actionAry = DB::table($db . '.action_lists')->where('id', $row->actionid)->first();
                    $actionid = DB::table($db . '.action_lists')->where('projectcode', $projectcode)->where('process_id', $actionAry->process_id)->where('actionname', $actionAry->actionname)->first();
                    $singledata = array(
                        'roleid' => $row->roleid,
                        'projectid' => $projectcode,
                        'sms' => $row->sms,
                        'email' => $row->email,
                        'web' => $row->web,
                        'inApp' => $row->inApp,
                        'actionid' => $actionid->id,
                        'recieverlist' => $row->recieverlist,
                        'msgcontent' => $row->msgcontent,
                        'createdby' => $created_by,
                        'created_at' =>  $created_at,
                        'updated_at' =>  null,
                        'status' => $row->status
                    );
                    // DB::table($db . '.notifications')->insert($singledata);
                }
                // dd($notifications);

                $data = new Project();
                $data->projectCode = $request->get('projectCode');
                $data->projectTitle = $request->get('projectTitle');
                $data->isActive = $request->get('isActive');
                // $data->save();
                DB::commit();
                return redirect()->back()->with('success', 'Record has been saved.');
            } catch (\Throwable $e) {
                DB::rollback();
                throw $e;
            }
        }
    }
}
