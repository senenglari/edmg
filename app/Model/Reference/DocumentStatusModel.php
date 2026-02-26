<?php

namespace App\Model\Reference;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use Carbon\Carbon;
use App\Model\Sys\LogModel;

class DocumentStatusModel extends Model
{
    protected $table        = "ref_document_status";
    protected $primaryKey   = "document_status_id";

    public function __construct() {
        $this->logModel     = new LogModel;   
    }

    public function getCollections() {
        try {
            $query  = DB::table($this->table)
                                ->select("$this->table.document_status_id", "$this->table.name AS name", "ref_issue_status.name AS issue_name"
                                        , DB::RAW("IF($this->table.status = 1, 'ACTIVE', 'INACTIVE') AS status_code"))
                                ->join("ref_issue_status", "$this->table.issue_status_id", "ref_issue_status.issue_status_id")
                                // ->where("$this->table.status", 1)
                                ->orderBy("$this->table.document_status_id", "DESC");

            if(session()->has("SES_SEARCH_DOCUMENT_STATUS_NAME") != "") {
                $query->where("$this->table.name", "LIKE", "%" . session()->get("SES_SEARCH_DOCUMENT_STATUS_NAME") . "%");
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
                                "issue_status_id"=>$request->issue_status_id,
                                "status"=>1,
                            ]);
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("CREATE DOCUMENT STATUS", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id   = $this->logModel->createError($e->getMessage(), "CREATE DOCUMENT STATUS FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }

    public function updateData($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table($this->table)
                    ->where("document_status_id", $request->id)
                    ->update([
                        "issue_status_id"=>$request->issue_status_id,
                        "status"=>$request->status,
                    ]);
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("UPDATE DOCUMENT STATUS", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "UPDATE DOCUMENT STATUS FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }
}
