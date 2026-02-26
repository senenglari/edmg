<?php

namespace App\Model\Reference;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use Carbon\Carbon;
use App\Model\Sys\LogModel;

class DisciplineModel extends Model
{
    protected $table        = "ref_discipline";
    protected $primaryKey   = "discipline_id";

    public function __construct() {
        $this->logModel     = new LogModel;   
    }

    public function getCollections() {
        try {
            $query  = DB::table($this->table)
                                ->select("$this->table.discipline_id", "code", "$this->table.name AS discipline_name", "ref_department.name AS department_name"
                                        , DB::RAW("IF($this->table.status = 1, 'ACTIVE', 'INACTIVE') AS status_code"))
                                ->join("ref_department", "$this->table.department_id", "ref_department.department_id")
                                // ->where("$this->table.status", 1)
                                ->orderBy("$this->table.discipline_id", "DESC");

            if(session()->has("SES_SEARCH_DISCIPLINE_NAME") != "") {
                $query->where("$this->table.name", "LIKE", "%" . session()->get("SES_SEARCH_DISCIPLINE_NAME") . "%");
            }

            if(session()->has("SES_SEARCH_DEPARTMENT_ID") != "") {
                if(session()->get("SES_SEARCH_DEPARTMENT_ID") != "0") {
                    $query->where("$this->table.department_id", session()->get("SES_SEARCH_DEPARTMENT_ID"));
                }
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
                                "department_id"=>$request->department_id,
                                "status"=>1,
                            ]);
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("CREATE DISCIPLINE", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id   = $this->logModel->createError($e->getMessage(), "CREATE DISCIPLINE FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }

    public function updateData($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table($this->table)
                    ->where("discipline_id", $request->id)
                    ->update([
                        "department_id"=>$request->department_id,
                        "status"=>$request->status,
                    ]);
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("UPDATE DISCIPLINE", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "UPDATE DISCIPLINE FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }
}
