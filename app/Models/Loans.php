<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Loans extends Model
{

    protected $table = 'dcs.loans';
    protected $guarded = ['updated_at',];

    protected $casts = [
        'DynamicFieldValue' => 'array'
    ];

    public static function getPendingData(Request $request)
    {
        $today = date('Y-m-d');
        $from_date = date('Y-01-01');

        $all_pending_loan =
            DB::table('dcs.loans')
            ->where('reciverrole', '!=', '0')
            ->where('status', 1)
            ->where('projectcode', session('projectcode'))
            ->whereDate('loans.time', '>=', $from_date)
            ->whereDate('loans.time', '<=', $today);

        // if ($request->input('res')
        $data = $all_pending_loan->get();

        return $data;
    }
}
