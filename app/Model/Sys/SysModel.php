<?php

namespace App\Model\Sys;
use DB;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Model\Sys\LogModel;

class SysModel extends Model
{
    public function __construct() {
        $this->logModel     = new LogModel;
    }
    
    public function getConfig() {
        $query  = DB::table("sys_config")->first();

        return $query;
    }

    public function updateData($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table("sys_config")
                        ->update([
                            "maintenance_sys_mode"=>$request->maintenance_sys_mode,
                            "password_expired"=>$request->password_expired,
                            "max_due_days"=>$request->max_due_days,
                            "return_max_due_days"=>$request->return_max_due_days,
                            "email_status"=>$request->email_status,
                            "default_approval_id"=>$request->default_approval_id,
                            "attachment_extention"=>transposeMultiSelect($request->attachment_extention),
                            "document_controll_email_address_notification"=>transposeMultiSelect($request->document_controll_email_address_notification),
                            "attachment_max_size"=>setNoComma($request->attachment_max_size),
                        ]);
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("UPDATE CONFIG", Auth::user()->id, "");
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "UPDATE CONFIG FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }
}
