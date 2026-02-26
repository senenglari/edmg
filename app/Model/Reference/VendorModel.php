<?php

namespace App\Model\Reference;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use Carbon\Carbon;
use App\Model\Sys\LogModel;

class VendorModel extends Model
{
    protected $table        = "ref_vendor";
    protected $primaryKey   = "vendor_id";

    public function __construct() {
        $this->logModel     = new LogModel;   
    }

    public function getCollections() {
        try {
            $query  = DB::table($this->table)
                                ->select("$this->table.vendor_id", "$this->table.name", "address", "phone_number", "fax_number"
                                        , "email_address", "pic", DB::RAW("IF($this->table.status = 1, 'ACTIVE', 'INACTIVE') AS status_code"))
                                // ->where("$this->table.status", 1)
                                ->orderBy("$this->table.vendor_id", "DESC");

            if(session()->has("SES_SEARCH_VENDOR_NAME") != "") {
                $query->where("$this->table.name", "LIKE", "%" . session()->get("SES_SEARCH_VENDOR_NAME") . "%");
            }

            $result = $query->paginate(PAGINATION);

            return array("status"=>true, "data"=>$result);
        } catch (\Exception $e) {
            return array("status"=>false, "data"=>[]);
        }
    }

    public function getVendor($id) {
        try {
            $query  = DB::table($this->table)
                                ->select("*")
                                ->where("$this->table.vendor_id", $id)
                                ->first();

            return array("status"=>true, "data"=>$query);
        } catch (\Exception $e) {
            return array("status"=>false, "data"=>[]);
        }
    }

    public function createData($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table($this->table)
                            ->insert([
                                "name"=>$request->name,
                                "address"=>$request->address,
                                "phone_number"=>$request->phone_number,
                                "fax_number"=>$request->fax_number,
                                "email_address"=>$request->email_address,
                                "pic"=>$request->pic,
                                "country_id"=>$request->country_id,
                                "status"=>1,
                            ]);
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("CREATE VENDOR", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id   = $this->logModel->createError($e->getMessage(), "CREATE VENDOR FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }

    public function updateData($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table($this->table)
                    ->where("vendor_id", $request->id)
                    ->update([
                        "address"=>$request->address,
                        "phone_number"=>$request->phone_number,
                        "fax_number"=>$request->fax_number,
                        "email_address"=>$request->email_address,
                        "pic"=>$request->pic,
                        "status"=>$request->status,
                        "country_id"=>$request->country_id,
                    ]);
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("UPDATE VENDOR", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "UPDATE VENDOR FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }
}
