<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PopupModel;

class PopupController extends Controller
{
    
    public function store(Request $request) {
        
        $popup_length=array("max","min");
        $popup_date=array("form","to");        
       
        $popupModel = new PopupModel();
        
        $popupModel->label = $request->label;
        
        $popupModel->datatype = $request->datatype;
        if($popupModel->datatype=="number" || $popupModel->datatype=="text")
		{
            $popupModel->captions = $popup_length;			
		}
        elseif ($popupModel->datatype=="date") {
            $popupModel->captions = $popup_date;
        } 
        else {
            $popupModel->captions = $request->popup_caption;
        }
        $popupModel->values = $request->popup_value;
        $popupModel->save();
        return response()->json(
          [
              'success' => true,
              'message' => 'Data inserted successfully'
          ]
      );
  
      }
}
