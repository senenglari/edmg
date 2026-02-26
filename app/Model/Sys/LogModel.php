<?php

namespace App\Model\Sys;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use Carbon\Carbon;

class LogModel extends Model
{
    public function createLog($desc=null, $user=null, $request=null) {
        DB::table("sys_logs")
                ->insertGetId([
                    "keterangan"=>setString($desc),
                    "user_id"=>Auth::user()->id,
        ]);
    }

    public function createError($error, $trans, $nomor=null) {
        $id     = DB::table("sys_errors")
                            ->insertGetId([
                                "error"=>$error,
                                "transaction"=>$trans,
                                "nomor_transaksi"=>$nomor,
                                "created_by"=>Auth::user()->id,
                                "created_at"=>Carbon::now()->toDateTimeString()
                    ]);

        return $id;
    }
}
