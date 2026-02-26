<?php

namespace App\Model\Project;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use Storage;
use File;
use Carbon\Carbon;
use App\Model\Sys\LogModel;

class ProjectModel extends Model
{
    protected $table        = "project";
    protected $primaryKey   = "project_id";

    public function __construct()
    {
        $this->logModel     = new LogModel;
    }

    public function getCollections()
    {
        try {
            $query  = DB::table($this->table)
                ->select(
                    "$this->table.*",
                    "ref_company.name as company_name",
                    DB::RAW("DATE_FORMAT($this->table.start_date, '%d/%m/%Y') AS st_date"),
                    DB::RAW("DATE_FORMAT($this->table.end_date, '%d/%m/%Y') AS ed_date"),
                    db::Raw("(CASE $this->table.status WHEN 1 THEN 'Active' WHEN 2 THEN 'Inactive' END) AS status_code")
                )
                ->join("ref_company", "$this->table.company_id", "ref_company.company_id")
                ->where("$this->table.status", "!=",  0)
                ->orderBy("$this->table.project_id", "DESC");

            if (session()->has("SES_SEARCH_PROJECT_COMPANY")) {
                if (session()->get("SES_SEARCH_PROJECT_COMPANY") != "0") {
                    $query->where("$this->table.company_id", session()->get("SES_SEARCH_PROJECT_COMPANY"));
                }
            }

            if (session()->has("SES_SEARCH_PROJECT_CODE")) {
                $query->where("$this->table.project_code", session()->get("SES_SEARCH_PROJECT_CODE"));
            }

            if (session()->has("SES_SEARCH_PROJECT_NAME")) {
                $query->where("$this->table.project_name", "LIKE", "%" . session()->get("SES_SEARCH_PROJECT_NAME") . "%");
            }

            if (session()->has("SES_SEARCH_PROJECT_CONTRACT_NO")) {
                $query->where("$this->table.project_code", session()->get("SES_SEARCH_PROJECT_CONTRACT_NO"));
            }

            if (session()->has("SES_SEARCH_PROJECT_END_DATE") != "") {
                $query->where("$this->table.end_date", setYMD(session()->get("SES_SEARCH_PROJECT_END_DATE"), "/"));
            }

            $result = $query->paginate(PAGINATION);

            return array("status" => true, "data" => $result);
        } catch (\Exception $e) {
            throw $e;
            return array("status" => false, "data" => []);
        }
    }

    public function getDataById($id)
    {
        $query      = DB::table($this->table)
            ->select(
                "$this->table.*",
                "ref_company.name as company_name",
                db::Raw("(CASE $this->table.status WHEN 1 THEN 'Active' WHEN 2 THEN 'Inactive' END) AS status_code")
            )
            ->join("ref_company", "$this->table.company_id", "ref_company.company_id")
            ->where("$this->table.$this->primaryKey", $id)
            ->first();

        return $query;
    }

    public function saveProject($request)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            # ------------------------
            $id = DB::table($this->table)
                ->insertGetId([
                    "project_code" => $request->project_code,
                    "project_name" => $request->project_name,
                    "project_description" => $request->description,
                    "contract_no" => $request->contract_no,
                    "company_id" => $request->company_id,
                    "vendor_id" => $request->vendor_id,
                    "start_date" => (!empty($request->start_date)) ? setYMD($request->start_date, "/") : null,
                    "end_date" => (!empty($request->end_date)) ? setYMD($request->end_date, "/") : null,
                    "status" => 1, // Active
                    "created_by" => Auth::user()->id,
                    "created_at" => Carbon::now()->toDateTimeString(),
                ]);
            /* ----------
             Logs
            ----------------------- */
            $this->logModel->createLog("ADD PROJECT (" . $id . ")", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "ADD PROJECT FAILED", "");
            # ---------------
            return array("status" => false, "id" => 0);
        }
    }

    public function updateProject($request)
    {
        DB::beginTransaction();
        # ------------------------
        try {

            DB::table($this->table)
                ->where("project_id", $request->project_id)
                ->update([
                    "project_code" => $request->project_code,
                    "project_name" => $request->project_name,
                    "project_description" => $request->description,
                    "contract_no" => $request->contract_no,
                    "company_id" => $request->company_id,
                    "vendor_id" => $request->vendor_id,
                    "start_date" => (!empty($request->start_date)) ? setYMD($request->start_date, "/") : null,
                    "end_date" => (!empty($request->end_date)) ? setYMD($request->end_date, "/") : null,
                    "status" => $request->status,
                    "updated_by" => Auth::user()->id,
                    "updated_at" => Carbon::now()->toDateTimeString(),
                ]);
            /* ----------
             Logs
            ----------------------- */
            $this->logModel->createLog("UPDATE PROJECT (" . $request->project_id . ")", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "UPDATE PROJECT FAILED", "");
            # ---------------
            return array("status" => false, "id" => 0);
        }
    }

    public function removeProject($request)
    {
        DB::beginTransaction();
        # ------------------------
        try {

            DB::table($this->table)
                ->where("project_id", $request->project_id)
                ->update([
                    "status" => 0, // Remove
                    "updated_by" => Auth::user()->id,
                    "updated_at" => Carbon::now()->toDateTimeString(),
                ]);
            /* ----------
             Logs
            ----------------------- */
            $this->logModel->createLog("DELETE PROJECT (" . $request->project_id . ")", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "DELETE PROJECT FAILED", "");
            # ---------------
            return array("status" => false, "id" => 0);
        }
    }

    public function get_all() {
        try {
            $result     = DB::table("project")->select("*")->where("status", 1)->orderBy("vendor_id", "DESC")->get();

            return array("status"=>true, "data"=>$result);
        } catch (\Exception $e) {
            return array("status"=>false, "data"=>[]);
        }
    }
}
