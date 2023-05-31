<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\FormConfig;
use App\Models\FieldDetails;
use App\Models\Addetails;
use App\Models\PopupModel;
use App\Models\LoanProduct;
use DB;

class FormConfigController extends Controller
{
    public function index(){
		$db = config('database.db'); 
		$datas=Project::all();
		$product_details=DB::table($db.'.product_details')->select('productname','productcode')->distinct('productcode')->get();
		// dd($product_details);
		return view('Formconfig')
            ->with('datas', $datas)
			->with('product_details',$product_details);
			// ->with('app_form', $app_form);
    }

	// function hex_chars($data){ 
	// 	$mb_hex = ''; 
	// 	for($i = 0 ; $i<mb_strlen($data,'UTF-8') ; $i++){ 
	// 		$c = mb_substr($data,$i,1,'UTF-8'); 
	// 		$o = unpack('N',mb_convert_encoding($c,'UCS-4BE','UTF-8')); 
	// 		$mb_hex .= sprintf('%04X',$o[1]); 
	// 	} 
	// 	return $mb_hex; 
	
	// }
	public function app_form(Request $request)
	{

		$appForm=$request->app_form;
		$loan_product=$request->loan_product;
		$value = FormConfig::where([
			'formID' => $appForm,
			'loanProduct' => $loan_product
			 ])->first();
		if($value!=null)
		{
			$details = FormConfig::where([
				'formID' => $request->app_form,
				'loanProduct' => $loan_product
				 ])->orderBy('id')->get();
		}
		else{
			$details = FieldDetails::where([
				'formName' => $request->app_form
				 ])->orderBy('id')->get();
		}
		return response()->json($details);
		
	}
    public function store(Request $request)
	{
		$appForm=$request->formID;
		$loan_product=$request->loan_product;
		$value = FormConfig::where([
			'formID' => $appForm,
			'loanProduct' => $loan_product
			 ])->orderBy('id')->get();
		$value_count=count($value);
		for($k=0; $k<$value_count; $k++)
		{
			$valueID = FormConfig::find($value[$k]['id']);
			$valueID->delete();
		}
		
		// brings popup values
		$popupDetails=PopupModel::all();
		$popup_count=count($popupDetails);
		// dd($popupDetails[1]['datatype']);
		// die;
		// $show = $request->get('groupLabel');
		// $groupLabel= json_encode($show);
		
		$groupLabel=array(         
			   'english' => $request->groupLabelEn,
			   'bangla' => $request->groupLabelBn,          
			   );

	//for label	 	
	$data1= $request->labelEn;
	$count1=count($data1);
		for ($i=0; $i < $count1; $i++){
			 $data=array(         
				'english' => $request->labelEn[$i],
				'bangla' => $request->labelBn[$i],          
				);
		$label[]=$data;
		}
			
		for ($i = 0; $i < $count1; $i++) {
			$data= new FormConfig();
			$data->projectcode= session('projectcode');
			$data->formID= $request->get('formID');
			$data->groupLabel= $request->groupLabelEn[$i];
			$data->lebel= $label[$i];
			$data->dataType= $request->dataType[$i];
			$data->loanProduct= $request->get('loan_product');
			for ($j = 0; $j < $popup_count; $j++){
				if($data->lebel['english']==$popupDetails[$j]['label'] && $data->dataType==$popupDetails[$j]['datatype'])
				{
					$captions=$popupDetails[$j]['captions'];
					$data->captions= $captions;
					$values=$popupDetails[$j]['values'];
					$data->values= $values;
					$delete = PopupModel::find($popupDetails[$j]['id']);
					$delete->delete();
				}				
				}	
			$data->columnType= $request->columnType[$i];
			$data->displayOrder= $request->displayOrder[$i];		
			$data->status= $request->status[$i];
			$data->groupNo= $request->get('groupNo');
			$data->save();			
		}
		// dd($data);
		return redirect()->back()->with('success', 'Record has been updated.');
	}
}
