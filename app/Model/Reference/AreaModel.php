<?php

namespace App\model\Reference;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use Carbon\Carbon;
use App\Model\Sys\LogModel;

class AreaModel extends Model
{
    protected $table        = "ref_area";
    protected $primaryKey   = "area_id";

    public function __construct() {
        $this->logModel     = new LogModel;   
    }

    public function getCollections() {
        try {
            $query  = DB::table($this->table)
                                ->select(
                                    "$this->table.area_id",
                                    "code",
//                                    "$this->table.name AS name",
                                    DB::raw("CONCAT(area_id, ' - ', $this->table.name) AS name"),
                                    DB::RAW("IF($this->table.status = 1, 'ACTIVE', 'INACTIVE') AS status_code")
                                )
                                // ->where("$this->table.status", 1)
                                ->orderBy("$this->table.area_id", "DESC");

            if(session()->has("SES_SEARCH_AREA_NAME") != "") {
                $query->where("$this->table.name", "LIKE", "%" . session()->get("SES_SEARCH_AREA_NAME") . "%");
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
                                "code"=>$request->code,
                                "name"=>$request->name,
                                "status"=>1,
                            ]);
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("CREATE AREA", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id   = $this->logModel->createError($e->getMessage(), "CREATE AREA FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }

    public function updateData($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table($this->table)
                    ->where("area_id", $request->id)
                    ->update([
                        "status"=>$request->status,
                    ]);
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("UPDATE AREA", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "UPDATE AREA FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }
}
