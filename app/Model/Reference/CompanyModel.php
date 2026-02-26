<?php

namespace App\Model\Reference;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use Carbon\Carbon;
use App\Model\Sys\LogModel;

class CompanyModel extends Model
{
    protected $table        = "ref_company";
    protected $primaryKey   = "company_id";

    public function __construct() {
        $this->logModel     = new LogModel;   
    }

    public function getCollections() {
        try {
            $query  = DB::table($this->table)
                                ->select("$this->table.company_id", "$this->table.name", "address", "phone_number", "fax_number"
                                        , "email_address", "pic", DB::RAW("IF($this->table.status = 1, 'ACTIVE', 'INACTIVE') AS status_code"))
                                // ->where("$this->table.status", 1)
                                ->orderBy("$this->table.company_id", "DESC");

            if(session()->has("SES_SEARCH_COMPANY_NAME") != "") {
                $query->where("$this->table.name", "LIKE", "%" . session()->get("SES_SEARCH_COMPANY_NAME") . "%");
            }

            $result = $query->paginate(PAGINATION);

            return array("status"=>true, "data"=>$result);
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
                                "status"=>1,
                            ]);
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("CREATE COMPANY", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id   = $this->logModel->createError($e->getMessage(), "CREATE COMPANY FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }

    public function updateData($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table($this->table)
                    ->where("company_id", $request->id)
                    ->update([
                        "address"=>$request->address,
                        "phone_number"=>$request->phone_number,
                        "fax_number"=>$request->fax_number,
                        "email_address"=>$request->email_address,
                        "pic"=>$request->pic,
                        "status"=>$request->status,
                    ]);
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("UPDATE COMPANY", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "UPDATE COMPANY FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }
}
