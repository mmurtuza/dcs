<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MessageQue;

class MessageQueController extends Controller
{
    public function index()
    {
        return view('MessageQue');
    }

    public function store(Request $request)
    {
        $data= new MessageQue();
        $data->pin =  $request->get('pin');
        $data-> message=  $request->get('message');
        $data-> readstatus=  $request->get('readstatus');
        $data-> docreff=  $request->get('docreff');
        $data-> doctype=  $request->get('doctype');
        $data->save();
        return redirect()->back()->with('success', 'Record has been updated.');
    }
}
