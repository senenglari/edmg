<?php

namespace App\Model\Document;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use Storage;
use File;
use DataTables;
use Mail;
use Excel;
use Carbon\Carbon;
use App\Model\Sys\LogModel;
use App\Model\Sys\SysModel;

class DocumentModel extends Model
{
    protected $table        = "document";
    protected $primaryKey   = "document_id";

    public function __construct()
    {
        $this->logModel     = new LogModel;
        $this->sysModel     = new SysModel;
    }

    public function getCollections()
    {
        try {
            $query  = DB::table($this->table)
                ->select(
                    "$this->table.document_id",
                    "$this->table.document_no",
                    "$this->table.document_title",
                    "$this->table.deadline",
                    DB::Raw("DATE_FORMAT($this->table.deadline, '%d-%m-%Y') AS deadline"),
                    "a.name as document_type_name",
                    "b.name as vendor_name",
                    "c.name as area_name",
                    "d.project_name",
                    "$this->table.status",
                    DB::Raw("concat(e.name,' - ', f.name) as issue_status"),
                    "e.name as document_status",
                    db::Raw("(CASE $this->table.status WHEN 1 THEN 'Unissued' WHEN 2 THEN 'Waiting for reviewer' WHEN 3 THEN 'Waiting for compiler' WHEN 4 THEN 'Waiting for return' WHEN 5 THEN 'Waiting for approval' WHEN 7 THEN 'Waiting for view' WHEN 99 THEN 'Stored' WHEN 88 THEN 'Reject' WHEN 6 THEN 'Done' END) AS status_code"),
                    db::Raw("COUNT(h.comment_id) AS unit"),
                    db::Raw("COUNT(IF((h.status = '2'),1,NULL)) AS done"),
                    db::Raw("COUNT(IF((h.status = '1'),1,NULL)) AS progress"),
                    db::Raw("GROUP_CONCAT(i.full_name ORDER BY h.order_no) AS list_name"),
                    db::Raw("GROUP_CONCAT(h.status ORDER BY h.order_no) AS list_status"),
                    db::Raw("GROUP_CONCAT(h.role ORDER BY h.order_no) AS list_role")
                )
                ->leftjoin("ref_document_type as a", "$this->table.document_type_id", "a.document_type_id")
                ->leftjoin("ref_vendor as b", "$this->table.vendor_id", "b.vendor_id")
                ->leftjoin("ref_area as c", "$this->table.area_id", "c.area_id")
                ->leftjoin("project as d", "$this->table.project_id", "d.project_id")
                ->leftjoin("ref_document_status as e", "$this->table.document_status_id", "e.document_status_id")
                ->leftjoin("ref_issue_status as f", "$this->table.issue_status_id", "f.issue_status_id")
                ->leftJoin('assignment as g', function ($join) {
                    $join->on("$this->table.document_id", "g.document_id");
                    $join->on("$this->table.incoming_transmittal_detail_id", "g.incoming_transmittal_detail_id");
                })
                ->leftjoin("comment as h", "g.assignment_id", "h.assignment_id")
                ->leftjoin("sys_users as i", "h.user_id", "i.id")
                ->whereNotIn("$this->table.status", [0,88])
                ->groupBy(
                    "$this->table.document_id",
                    "$this->table.document_no",
                    "$this->table.document_title",
                    "deadline",
                    "a.name",
                    "b.name",
                    "c.name",
                    "d.project_name",
                    "e.name",
                    "f.name",
                    "$this->table.status"
                )
                ->orderBy("$this->table.document_id", "DESC");

            if (session()->has("SES_SEARCH_DOCUMENT_PROJECT_NAME") != "") {
                $query->where("d.project_name", "LIKE", "%" . session()->get("SES_SEARCH_DOCUMENT_PROJECT_NAME") . "%");
            }

            if (session()->has("SES_SEARCH_DOCUMENT_NO") != "") {
                $query->where("$this->table.document_no", "LIKE", "%" . session()->get("SES_SEARCH_DOCUMENT_NO") . "%");
            }

            if (session()->has("SES_SEARCH_DOCUMENT_TITLE") != "") {
                $query->where("$this->table.document_title", "LIKE", "%" . session()->get("SES_SEARCH_DOCUMENT_TITLE") . "%");
            }

            if (session()->has("SES_SEARCH_DOCUMENT_TYPE")) {
                if (session()->get("SES_SEARCH_DOCUMENT_TYPE") != "0") {
                    $query->where("$this->table.document_type_id", session()->get("SES_SEARCH_DOCUMENT_TYPE"));
                }
            }

            if (session()->has("SES_SEARCH_DOCUMENT_VENDOR")) {
                if (session()->get("SES_SEARCH_DOCUMENT_VENDOR") != "0") {
                    $query->where("$this->table.vendor_id", session()->get("SES_SEARCH_DOCUMENT_VENDOR"));
                }
            }

            if (session()->has("SES_SEARCH_DOCUMENT_AREA")) {
                if (session()->get("SES_SEARCH_DOCUMENT_AREA") != "0") {
                    $query->where("$this->table.area_id", session()->get("SES_SEARCH_DOCUMENT_AREA"));
                }
            }
            if (session()->has("SES_SEARCH_DOCUMENT_STATUS")) {
                if (session()->get("SES_SEARCH_DOCUMENT_STATUS") != "0") {
                    $query->where("$this->table.status", session()->get("SES_SEARCH_DOCUMENT_STATUS"));
                }
            }

            if (session()->has("SES_SEARCH_DOCUMENT_DOC_STATUS")) {
                if (session()->get("SES_SEARCH_DOCUMENT_DOC_STATUS") != "0") {
                    $query->where("$this->table.document_status_id", session()->get("SES_SEARCH_DOCUMENT_DOC_STATUS"));
                }
            }

            if (session()->has("SES_SEARCH_DOCUMENT_ISSUE_STATUS")) {
                if (session()->get("SES_SEARCH_DOCUMENT_ISSUE_STATUS") != "0") {
                    $query->where("f.issue_status_id", session()->get("SES_SEARCH_DOCUMENT_ISSUE_STATUS"));
                }
            }

            if (session()->has("SES_SEARCH_DOCUMENT_DEADLINE_AWAL") != "") {
                $query->where("$this->table.deadline", ">=", setYMD(session()->get("SES_SEARCH_DOCUMENT_DEADLINE_AWAL"), "/"));
            }
    
            if (session()->has("SES_SEARCH_DOCUMENT_DEADLINE_AKHIR") != "") {
                $query->where("$this->table.deadline", "<=", setYMD(session()->get("SES_SEARCH_DOCUMENT_DEADLINE_AKHIR"), "/"));
            }

            $result = $query->paginate(PAGINATION);

            return array("status" => true, "data" => $result);
        } catch (\Exception $e) {
            return array("status" => false, "data" => []);
        }
    }

    public function getIFICollections()
    {
        try {
            $query  = DB::table($this->table)
                ->select(
                    "$this->table.document_id",
                    "$this->table.document_no",
                    "$this->table.document_title",
                    "$this->table.deadline",
                    DB::Raw("DATE_FORMAT($this->table.deadline, '%d-%m-%Y') AS deadline"),
                    "a.name as document_type_name",
                    "b.name as vendor_name",
                    "c.name as area_name",
                    "d.project_name",
                    "$this->table.status",
                    DB::Raw("concat(e.name,' - ', f.name) as issue_status"),
                    "e.name as document_status",
                    db::Raw("(CASE $this->table.status WHEN 1 THEN 'Unissued' WHEN 2 THEN 'Waiting for reviewer' WHEN 3 THEN 'Waiting for compiler' WHEN 4 THEN 'Waiting for return' WHEN 5 THEN 'Waiting for approval' WHEN 7 THEN 'Waiting for view' WHEN 99 THEN 'Stored' WHEN 88 THEN 'Reject' WHEN 6 THEN 'Done' END) AS status_code"),
                    db::Raw("COUNT(h.comment_id) AS unit"),
                    db::Raw("COUNT(IF((h.status = '2'),1,NULL)) AS done"),
                    db::Raw("COUNT(IF((h.status = '1'),1,NULL)) AS progress"),
                    db::Raw("GROUP_CONCAT(i.full_name ORDER BY h.order_no) AS list_name"),
                    db::Raw("GROUP_CONCAT(h.status ORDER BY h.order_no) AS list_status")
                )
                ->leftjoin("ref_document_type as a", "$this->table.document_type_id", "a.document_type_id")
                ->leftjoin("ref_vendor as b", "$this->table.vendor_id", "b.vendor_id")
                ->leftjoin("ref_area as c", "$this->table.area_id", "c.area_id")
                ->leftjoin("project as d", "$this->table.project_id", "d.project_id")
                ->leftjoin("ref_document_status as e", "$this->table.document_status_id", "e.document_status_id")
                ->leftjoin("ref_issue_status as f", "$this->table.issue_status_id", "f.issue_status_id")
                ->leftJoin('assignment as g', function ($join) {
                    $join->on("$this->table.document_id", "g.document_id");
                    $join->on("$this->table.incoming_transmittal_detail_id", "g.incoming_transmittal_detail_id");
                })
                ->leftjoin("comment as h", "g.assignment_id", "h.assignment_id")
                ->leftjoin("sys_users as i", "h.user_id", "i.id")
                ->where("$this->table.status", "!=", 0)
                ->where("$this->table.issue_status_id", STATUS_ONLY_IFI)
                ->groupBy(
                    "$this->table.document_id",
                    "$this->table.document_no",
                    "$this->table.document_title",
                    "deadline",
                    "a.name",
                    "b.name",
                    "c.name",
                    "d.project_name",
                    "e.name",
                    "f.name",
                    "$this->table.status"
                )
                ->orderBy("$this->table.document_id", "DESC");

            if (session()->has("SES_SEARCH_IFI_DOCUMENT_NO") != "") {
                $query->where("$this->table.document_no", "LIKE", "%" . session()->get("SES_SEARCH_IFI_DOCUMENT_NO") . "%");
            }

            if (session()->has("SES_SEARCH_IFI_DOCUMENT_TITLE") != "") {
                $query->where("$this->table.document_title", "LIKE", "%" . session()->get("SES_SEARCH_IFI_DOCUMENT_TITLE") . "%");
            }

            if (session()->has("SES_SEARCH_IFI_DOCUMENT_STATUS")) {
                if (session()->get("SES_SEARCH_IFI_DOCUMENT_STATUS") != "0") {
                    $query->where("$this->table.status", session()->get("SES_SEARCH_IFI_DOCUMENT_STATUS"));
                }
            }

            if (session()->has("SES_SEARCH_IFI_DOCUMENT_VENDOR")) {
                if (session()->get("SES_SEARCH_IFI_DOCUMENT_VENDOR") != "0") {
                    $query->where("$this->table.vendor_id", session()->get("SES_SEARCH_IFI_DOCUMENT_VENDOR"));
                }
            }

            $result = $query->paginate(PAGINATION);

            return array("status" => true, "data" => $result);
        } catch (\Exception $e) {
            return array("status" => false, "data" => []);
        }
    }

    public function emptyTemp()
    {
        DB::table("comment_temp")->where("comment_temp.created_by", Auth::user()->id)->delete();
    }

    public function emptyTempByClone()
    {
        DB::table("comment_temp")->where("comment_temp.clone_by", Auth::user()->id)->delete();
    }

    public function addUserTemp($request)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            if ($request->user_id != 0) {
                # ------------------------
                $id     = DB::table("comment_temp")
                    ->insertGetId([
                        "assignment_id" => null,
                        "user_id" => $request->user_id,
                        "role" => $request->role,
                        "remark" => null,
                        "issue_status_id" => null,
                        "order_no" => null,
                        "created_by" => Auth::user()->id
                    ]);
            }

            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            # ------------------------
            $this->logModel->createError($e->getMessage(), "ADD USER IN TEMP TABLE", "");
            # ------------------------
            return array("status" => false, "id" => 0);
        }
    }

    public function getUserTemp()
    {
        $query      = DB::table("comment_temp as a")->select(
            "a.*",
            "b.full_name",
            "c.name as department_name",
            "d.name as discipline_name",
            "e.name as position_name"
        )
            ->join("sys_users as b", "a.user_id", "b.id")
            ->leftJoin("ref_department as c", "b.department_id", "c.department_id")
            ->leftJoin("ref_discipline as d", "b.discipline_id", "d.discipline_id")
            ->leftJoin("ref_position as e", "b.position_id", "e.position_id")
            ->where("a.created_by", Auth::user()->id)
            ->orderBy("a.comment_temp_id")
            ->get();

        return $query;
    }

    public function deleteUserTemp($id)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            $id     = DB::table("comment_temp")->where("comment_temp_id", $id)->where("created_by", Auth::user()->id)->delete();
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            # ------------------------
            $this->logModel->createError($e->getMessage(), "DELETE ITEM IN TEMP", "");
            # ------------------------
            return array("status" => false, "id" => 0);
        }
    }

    public function saveDocument($request)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            $user_id    = Auth::user()->id;
            # ------------------------

            $docId         = DB::table("document")
                ->insertGetId([
                    "document_no" => $request->document_no,
                    "document_title" => $request->document_title,
                    "document_description" => $request->document_description,
                    "document_type_id" => $request->document_type,
                    "project_id" => $request->project_id,
                    "vendor_id" => $request->vendor_id,
                    "area_id" => $request->area_id,
                    "status" => 1, // NEW
                    "ref_no" => $request->ref_no,
                    "pic_id" => $request->pic,
                    "department_id" => $request->departemen_id,
                    "incoming_transmittal_detail_id" => 0,
                    "created_by" => Auth::user()->id,
                    "created_at" => Carbon::now()->toDateTimeString(),
                    "approved_by" => $request->approval_by
                ]);

            $assignmentId         = DB::table("assignment")
                ->insertGetId([
                    "document_id" =>  $docId,
                    "incoming_transmittal_detail_id" => 0,
                    "created_by" => Auth::user()->id,
                    "created_at" => Carbon::now()->toDateTimeString(),
                ]);

            // CREATE DOCUMENT LOG
            DB::table("document_change_log")
                ->insert([
                    "document_id" => $docId,
                    "document_no" => $request->document_no,
                    "document_title" => $request->document_title,
                    "changed_by" => Auth::user()->id,
                    "changed_at" => Carbon::now()->toDateTimeString()
                ]);


            $qTemp      = $this->getUserTemp();
            foreach ($qTemp as $index => $row) {
                $no = ++$index;
                # ------------------------
                DB::table("comment")
                    ->insert([
                        "assignment_id" => $assignmentId,
                        "user_id" => $row->user_id,
                        "role" => $row->role,
                        "status" => 0, // NEW
                        "order_no" => $no
                    ]);
            }
            # ------------------------
            DB::table("comment_temp")->where("created_by", $user_id)->delete();
            /* ----------
             Logs
            ----------------------- */
            $this->logModel->createLog("ADD DOCUMENT (" . $docId . ")", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "ADD DOCUMENT FAILED", "");
            # ---------------
            return array("status" => false, "id" => 0);
        }
    }

    public function getDataById($id)
    {
        $query  = DB::table($this->table)
            ->select(
                "$this->table.*",
                "a.name as document_type_name",
                "b.name as vendor_name",
                "c.name as area_name",
                "d.project_name",
                "e.name as document_status",
                "f.name as issue_status",
                "h.assignment_id",
                "h.status_nonaktif",
                db::Raw("(CASE $this->table.status WHEN 1 THEN 'New' END) AS status_code")
            )
            ->leftjoin("ref_document_type as a", "$this->table.document_type_id", "a.document_type_id")
            ->leftjoin("ref_vendor as b", "$this->table.vendor_id", "b.vendor_id")
            ->leftjoin("ref_area as c", "$this->table.area_id", "c.area_id")
            ->leftjoin("project as d", "$this->table.project_id", "d.project_id")
            ->leftjoin("ref_document_status as e", "$this->table.document_status_id", "e.document_status_id")
            ->leftjoin("ref_issue_status as f", "$this->table.issue_status_id", "f.issue_status_id")
            ->leftJoin('assignment as h', function ($join) {
                $join->on("$this->table.document_id", "h.document_id");
                $join->on("$this->table.incoming_transmittal_detail_id", "h.incoming_transmittal_detail_id");
            })
            ->where("$this->table.document_id", $id)
            ->first();

        return $query;
    }

    public function updateDocument($request)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            if ($request->document_no_old != $request->document_no || $request->document_title_old != $request->document_title) {
                DB::table("document_change_log")
                    ->insert([
                        "document_id" => $request->document_id,
                        "document_no" => $request->document_no,
                        "document_title" => $request->document_title,
                        "changed_by" => Auth::user()->id,
                        "changed_at" => Carbon::now()->toDateTimeString()
                    ]);
            }

            DB::table($this->table)
                ->where("document_id", $request->document_id)
                ->update([
                    "document_no" => $request->document_no,
                    "document_title" => $request->document_title,
                    "document_description" => $request->document_description,
                    "document_type_id" => $request->document_type,
                    "project_id" => $request->project_id,
                    "vendor_id" => $request->vendor_id,
                    "area_id" => $request->area_id,
                    "ref_no" => $request->ref_no,
                    "pic_id" => $request->pic,
                    "department_id" => $request->departemen_id,
                    "updated_by" => Auth::user()->id,
                    "updated_at" => Carbon::now()->toDateTimeString(),
                    "approved_by" => $request->approval_by
                ]);

            /* ----------
             Logs
            ----------------------- */
            $this->logModel->createLog("UPDATE DOCUMENT (" .  $request->document_id . ")", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "UPDATE DOCUMENT FAILED", "");
            # ---------------
            return array("status" => false, "id" => 0);
        }
    }

    public function removeDocument($request)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            $datetime = date("YmdHis");
            DB::table($this->table)
                ->where("document_id", $request->document_id)
                ->update([
                    "document_no" => 'DELETE-' . $request->document_no . '-' . $datetime, // Remove
                    "status" => 0, // Remove
                    "updated_by" => Auth::user()->id,
                    "updated_at" => Carbon::now()->toDateTimeString(),
                ]);
            /* ----------
             Logs
            ----------------------- */
            $this->logModel->createLog("DELETE DOCUMENT (" . $request->document_id . ")", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "DELETE DOCUMENT FAILED", "");
            # ---------------
            return array("status" => false, "id" => 0);
        }
    }

    public function getDataComment($id)
    {
        $query  = DB::table($this->table)
            ->select(
                "$this->table.document_id",
                "a.assignment_id",
                "b.*"
            )
            ->leftJoin('assignment as a', function ($join) {
                $join->on("$this->table.document_id", "a.document_id");
                $join->on("$this->table.incoming_transmittal_detail_id", "a.incoming_transmittal_detail_id");
            })
            ->leftjoin("comment as b", "a.assignment_id", "b.assignment_id")
            ->where("$this->table.document_id", $id)
            ->orderBy("b.order_no", "ASC")
            ->get();

        return $query;
    }

    public function cloneCommentTemp($id, $assignment)
    {
        DB::beginTransaction();
        # ------------------------
        try {

            if ($assignment != null) {
                $qComment   = $this->getDataComment($id);
                foreach ($qComment as $row) {
                    # ------------------------
                    DB::table("comment_temp")
                        ->insert([
                            "assignment_id" => $row->assignment_id,
                            "user_id" => $row->user_id,
                            "start_date" => $row->start_date,
                            "end_date" => $row->end_date,
                            "remark_before" => $row->remark_before,
                            "remark" => $row->remark,
                            "role" => $row->role,
                            "issue_status_id" => $row->issue_status_id,
                            "status" => $row->status,
                            "order_no" => $row->order_no,
                            "created_by" => $row->created_by,
                            "created_at" => $row->created_at,
                            "updated_by" => $row->updated_by,
                            "updated_at" => $row->updated_at,
                            "clone_by" => Auth::user()->id
                        ]);
                }
            }

            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "CLONE COMMENT FAILED", "");
            # ---------------
            return array("status" => false, "id" => 0);
        }
    }

    public function getCommentTempByClone()
    {
        $query      = DB::table("comment_temp as a")
            ->select(
                "a.*",
                "b.full_name",
                "c.name as department_name",
                "d.name as discipline_name",
                "e.name as position_name"
            )
            ->join("sys_users as b", "a.user_id", "b.id")
            ->leftJoin("ref_department as c", "b.department_id", "c.department_id")
            ->leftJoin("ref_discipline as d", "b.discipline_id", "d.discipline_id")
            ->leftJoin("ref_position as e", "b.position_id", "e.position_id")
            ->where("a.clone_by", Auth::user()->id)
            ->orderBy("a.order_no", "ASC")
            ->get();

        return $query;
    }

    public function addUserCloneTemp($request)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            if ($request->user_id != 0) {
                $orderNo = DB::table("comment_temp")->max("order_no");

                # ------------------------
                $id     = DB::table("comment_temp")
                    ->insertGetId([
                        "assignment_id" => $request->assignment_id,
                        "user_id" => $request->user_id,
                        "role" => $request->role_id,
                        "remark" => null,
                        "issue_status_id" => 0,
                        "order_no" => $orderNo + 1,
                        "status" => 0,
                        "flag_status" => 1,
                        "created_by" => Auth::user()->id,
                        "created_at" => Carbon::now()->toDateTimeString(),
                        "clone_by" => Auth::user()->id
                    ]);
            }

            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            # ------------------------
            $this->logModel->createError($e->getMessage(), "ADD USER CLONE IN TEMP TABLE", "");
            # ------------------------
            return array("status" => false, "id" => 0);
        }
    }

    public function getUserCloneTemp()
    {
        $query      = DB::table("comment_temp as a")->select(
            "a.*",
            "b.full_name",
            "c.name as department_name",
            "d.name as discipline_name",
            "e.name as position_name"
        )
            ->join("sys_users as b", "a.user_id", "b.id")
            ->leftJoin("ref_department as c", "b.department_id", "c.department_id")
            ->leftJoin("ref_discipline as d", "b.discipline_id", "d.discipline_id")
            ->leftJoin("ref_position as e", "b.position_id", "e.position_id")
            ->where("a.clone_by", Auth::user()->id)
            ->orderBy("a.order_no", "ASC")
            ->get();

        return $query;
    }

    public function deleteUserCloneTemp($id)
    {
        DB::beginTransaction();
        # ------------------------
        try {

            $id     = DB::table("comment_temp")->where("comment_temp_id", $id)->where("clone_by", Auth::user()->id)->delete();
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            # ------------------------
            $this->logModel->createError($e->getMessage(), "DELETE ITEM IN TEMP", "");
            # ------------------------
            return array("status" => false, "id" => 0);
        }
    }

    public function updateTempCommentToComment($request)
    {

        DB::beginTransaction();
        # ------------------------
        try {
            // get interval assignment date
            $maxDueDays = DB::table("sys_config")
                ->select("max_due_days")
                ->first()->max_due_days;

            if (!empty($request->start_date)) {
                $startDate = (!empty($request->start_date)) ? setYMD($request->start_date, "/") : null;
                $endDate = date("Y-m-d", strtotime('+' . $maxDueDays . 'days', strtotime($startDate)));
            } else {
                $startDate = null;
                $endDate = null;
            }


            foreach ($request->comment_temp_id as $index => $row) {
                // update comment temp
                DB::table("comment_temp")
                    ->where("comment_temp_id", $row)
                    ->update([
                        "start_date" => $startDate,
                        "end_date" => $endDate,
                        "role" => $request->role[$index],
                        "order_no" => $request->orderNo[$index]
                    ]);
            }


            // Get data update from clone temp table
            $qCloneTemp = DB::table("comment_temp as a")
                ->select("a.*")
                ->where("a.status", "!=", 2)
                ->where("a.clone_by", Auth::user()->id)
                ->orderBy("a.order_no", "ASC")
                ->get();

            // Get value incoming transmittal detail id
            $idIncomingTrasn   = DB::table("document as a")
                ->select("a.incoming_transmittal_detail_id")
                ->where("a.document_id", $request->idDoc)
                ->first()->incoming_transmittal_detail_id;

            if ($request->statusAssignment == null) {
                // create assignment
                $assignmentId         = DB::table("assignment")
                    ->insertGetId([
                        "document_id" =>  $request->idDoc,
                        "incoming_transmittal_detail_id" => $idIncomingTrasn,
                        "created_by" => Auth::user()->id,
                        "created_at" => Carbon::now()->toDateTimeString(),
                    ]);
            } else {
                $assignmentId = $request->statusAssignment;
                // delete comment where != status 2
                DB::table('comment')
                    ->where('assignment_id', $assignmentId)
                    ->where('status', "!=", 2)
                    ->delete();
            }

            // create comment
            foreach ($qCloneTemp as $indxClone => $rowClone) {
                if ($idIncomingTrasn == 0) {
                    $status = 0;
                    $assignmentStartDate = null;
                    $assignmentEndDate = null;
                } else {
                    $no = ++$indxClone;
                    if ($no == 1) {
                        $status = 1;
                        /* ----------
                        Send Email
                        ----------------------- */
                        if ($this->sysModel->getConfig()->email_status == 1) {
                            // $title              = "Document Review Notification";
                            // $qTransmittal       = DB::table("incoming_transmittal as a")
                            //     ->select("a.incoming_transmittal_id", "a.incoming_no")
                            //     ->join("incoming_transmittal_detail as b", "a.incoming_transmittal_id", "b.incoming_transmittal_id")
                            //     ->where("b.incoming_transmittal_detail_id", $idIncomingTrasn)
                            //     ->first();

                            // $emails             = DB::table("sys_users")
                            //     ->select("sys_users.id", "sys_users.email", "sys_users.full_name")
                            //     ->where("sys_users.id", $rowClone->user_id)
                            //     ->first();

                            // $data["title"]      = $title;
                            // $data["inc_no"]     = $qTransmittal->incoming_no;
                            // $data["content"]    = DB::table("incoming_transmittal as a")
                            //     ->select(
                            //         "document.document_no",
                            //         "document.document_title",
                            //         "ref_document_status.name AS document_status_name",
                            //         "ref_issue_status.name AS issue_status_name"
                            //     )
                            //     ->join("incoming_transmittal_detail as b", "a.incoming_transmittal_id", "b.incoming_transmittal_id")
                            //     ->join("document", "b.document_id", "document.document_id")
                            //     ->leftJoin("ref_document_status", "b.document_status_id", "ref_document_status.document_status_id")
                            //     ->leftJoin("ref_issue_status", "b.issue_status_id", "ref_issue_status.issue_status_id")
                            //     ->where("b.incoming_transmittal_detail_id", $idIncomingTrasn)
                            //     ->get();
                            // # ---------------
                            // Mail::send('email.review-notification', $data, function ($message) use ($title, $emails) {
                            //     $message->to($emails->email, $emails->full_name)->subject($title);
                            //     # ---------------
                            //     $message->from(env("MAIL_USERNAME"), 'Automatic Mail System');
                            // });
                        }
                    } else {
                        $status = 1;
                    }

                    $assignmentStartDate = $rowClone->start_date;
                    $assignmentEndDate = $rowClone->end_date;
                }

                if ($rowClone->created_at == null) {
                    $createdId = Auth::user()->id;
                    $createdDate = Carbon::now()->toDateTimeString();
                } else {
                    $createdId = $rowClone->created_by;
                    $createdDate = $rowClone->created_at;
                }

                DB::table("comment")
                    ->insert([
                        "assignment_id" =>   $assignmentId,
                        "user_id" =>  $rowClone->user_id,
                        "start_date" =>  $assignmentStartDate,
                        "end_date" =>  $assignmentEndDate,
                        "role" =>  $rowClone->role,
                        "issue_status_id" =>  $rowClone->issue_status_id,
                        "status" =>  $status,
                        "order_no" =>  $rowClone->order_no,
                        "created_by" =>  $createdId,
                        "created_at" => $createdDate
                    ]);
                /* -----------
                Kirim email ke user yg baru di assign saja
                --------------------- */
                if($rowClone->flag_status == 1) {
                    if($this->sysModel->getConfig()->email_status == 1) {
                        $data["content"]    = DB::table("incoming_transmittal_detail")->select("document.document_no", "document.document_title", "sys_users.email", "incoming_transmittal.incoming_no"
                                                                                                , "ref_document_status.name AS document_status_name", "ref_issue_status.name AS issue_status_name")
                                                                                      ->join("document", "incoming_transmittal_detail.document_id", "document.document_id")
                                                                                      ->join("assignment", "incoming_transmittal_detail.incoming_transmittal_detail_id", "assignment.incoming_transmittal_detail_id")
                                                                                      ->join("comment", "assignment.assignment_id", "comment.assignment_id")

                                                                                      ->join("incoming_transmittal", "incoming_transmittal_detail.incoming_transmittal_id", "incoming_transmittal.incoming_transmittal_id")

                                                                                      ->join("ref_document_status", "incoming_transmittal_detail.document_status_id", "ref_document_status.document_status_id")
                                                                                      ->join("ref_issue_status", "incoming_transmittal_detail.issue_status_id", "ref_issue_status.issue_status_id")

                                                                                      ->join("sys_users", "comment.user_id", "sys_users.id")

                                                                                      ->where("assignment.assignment_id", $assignmentId)
                                                                                      ->where("comment.user_id", $rowClone->user_id)
                                                                                      ->get();
                        # ---------------
                        foreach($data["content"] AS $row_email) {
                            $title              = "Incoming Transmittal Notification";
                            $data["title"]      = $title;
                            $data["inc_no"]     = $row_email->incoming_no;
                            $emails             = $row_email->email;
                            $data["contents"]   = $row_email;
                            // dd($row_email);
                            # ---------------
                            Mail::send('email.assignment-notification', $data, function($message) use ($title, $emails) {
                                $message->to($emails, "")->subject($title);
                                # ---------------
                                $message->from(env("MAIL_USERNAME"), 'Automatic Mail System');
                            });
                        }
                    }
                }
            }

            // update to comment where status 0
            DB::table("comment")
                ->where("assignment_id", $assignmentId)
                ->where("status", 0)
                ->update([
                    "created_by" =>  0,
                    "created_at" => null
                ]);



            // Empty Comment Temp By Clone
            $this->emptyTempByClone();

            $this->logModel->createLog("UPDATE ASSIGNMENT IN COMMENT TABLE (" . $assignmentId . ")", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            # ------------------------
            $this->logModel->createError($e->getMessage(), "UPDATE ASSIGNMENT IN COMMENT TABLE", "");
            # ------------------------
            return array("status" => false, "id" => 0);
        }
    }

    public function updateToComment($request)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table('comment')
                ->where('assignment_id', $request->statusAssignment)
                ->where('status', "!=", 2)
                ->delete();

            $this->logModel->createLog("UPDATE ASSIGNMENT IN COMMENT TABLE (" . $request->statusAssignment . ")", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            # ------------------------
            $this->logModel->createError($e->getMessage(), "UPDATE ASSIGNMENT IN COMMENT TABLE", "");
            # ------------------------
            return array("status" => false, "id" => 0);
        }
    }

    public function getDataDocExistingById($id)
    {
        $query  = DB::table($this->table)
            ->select(
                "$this->table.*",
                "a.name as document_type_name",
                "b.name as vendor_name",
                "c.name as area_name",
                "d.project_name",
                "e.name as document_status",
                "f.name as issue_status",
                "g.document_url",
                "g.document_file",
                "g.document_file_revision",
                "h.incoming_no",
                "h.receive_date",
                "h.sender_date",
                "i.name as department_name",
                "l.full_name as approval_name",
                DB::Raw("concat(j.full_name,' - (', k.name, ')') as pic_name"),
                db::Raw("(CASE $this->table.status WHEN 1 THEN 'Unissued' WHEN 2 THEN 'Waiting for reviewer' WHEN 3 THEN 'Waiting for compiler' WHEN 4 THEN 'Waiting for return' WHEN 5 THEN 'Waiting for approval' WHEN 6 THEN 'Done' WHEN 99 THEN 'Stored' END) AS status_code")
            )
            ->leftjoin("ref_document_type as a", "$this->table.document_type_id", "a.document_type_id")
            ->leftjoin("ref_vendor as b", "$this->table.vendor_id", "b.vendor_id")
            ->leftjoin("ref_area as c", "$this->table.area_id", "c.area_id")
            ->leftjoin("project as d", "$this->table.project_id", "d.project_id")
            ->leftjoin("ref_document_status as e", "$this->table.document_status_id", "e.document_status_id")
            ->leftjoin("ref_issue_status as f", "$this->table.issue_status_id", "f.issue_status_id")
            ->leftjoin("incoming_transmittal_detail as g", "$this->table.incoming_transmittal_detail_id", "g.incoming_transmittal_detail_id")
            ->leftjoin("incoming_transmittal as h", "g.incoming_transmittal_id", "h.incoming_transmittal_id")
            ->leftjoin("ref_department as i", "$this->table.department_id", "i.department_id")
            ->leftjoin("sys_users as j", "$this->table.pic_id", "j.id")
            ->leftjoin("ref_position as k", "j.position_id", "k.position_id")
            ->leftjoin("sys_users as l", "$this->table.approved_by", "l.id")
            ->where("$this->table.document_id", $id)
            ->first();

        return $query;
    }

    public function getDataHistoryTransmittal($id)
    {
        $query  = DB::table("assignment as a")
            ->select(
                "a.assignment_id",
                "a.document_id",
                "a.incoming_transmittal_detail_id",
                "b.document_url",
                "b.document_file",
                "c.incoming_no",
                "c.receive_date",
                "d.name as issue_status_name"
            )
            ->join("incoming_transmittal_detail as b", "a.incoming_transmittal_detail_id", "b.incoming_transmittal_detail_id")
            ->join("incoming_transmittal as c", "b.incoming_transmittal_id", "c.incoming_transmittal_id")
            ->leftjoin("ref_issue_status as d", "b.issue_status_incoming_id", "d.issue_status_id")
            ->where("a.document_id", $id)
            ->where("c.status", "!=", 3)
            ->groupBy("a.assignment_id")
            ->orderBY("a.assignment_id", "DESC")
            ->get();

        return $query;
    }

    public function getDataHistoryComment($id)
    {
        $query  = DB::table("assignment as a")
            ->select(
                "a.assignment_id",
                "a.document_id",
                "a.incoming_transmittal_detail_id",
                "d.document_url",
                "d.document_file",
                "d.document_file_2",
                "c.incoming_no",
                "c.receive_date",
                "d.comment_id",
                "d.user_id",
                "d.start_date",
                "d.end_date",
                "d.remark",
                "d.role",
                "d.order_no",
                "d.status",
                "d.created_by",
                "d.created_at",
                "d.updated_at",
                "d.updated_by",
                "e.full_name as comment_user",
                "ref_return_status.name AS return_code"
            )
            ->join("incoming_transmittal_detail as b", "a.incoming_transmittal_detail_id", "b.incoming_transmittal_detail_id")
            ->join("incoming_transmittal as c", "b.incoming_transmittal_id", "c.incoming_transmittal_id")
            ->join("comment as d", "d.assignment_id", "a.assignment_id")
            ->join("sys_users as e", "d.user_id", "e.id")
            ->leftjoin("ref_return_status", "d.return_status_id", "ref_return_status.return_status_id")
            ->where("a.document_id", $id)
            ->orderBY("a.assignment_id", "DESC")
            ->get();

        return $query;
    }

    public function getDataHistoryDocument($id)
    {
        $query  = DB::table("incoming_transmittal_detail as a")
            ->select(
                "c.incoming_no",
                "c.sender_date",
                "c.receive_date",
                "a.document_url",
                "a.document_file",
                "a.document_file_revision",
                "d.name as issue_status",
                "c.status AS status_incoming"
            )
            ->join("document as b", "a.document_id", "b.document_id")
            ->join("incoming_transmittal as c", "a.incoming_transmittal_id", "c.incoming_transmittal_id")
            ->join("ref_issue_status as d", "d.issue_status_id", "a.issue_status_incoming_id")
            ->where("a.document_id", $id)
            ->orderBY("a.incoming_transmittal_id", "DESC")
            ->get();

        return $query;
    }

    public function getDocChangeLog($id)
    {
        $query  = DB::table("document_change_log as a")
            ->select(
                "a.*",
                "b.full_name"
            )
            ->join("sys_users as b", "a.changed_by", "b.id")
            ->where("a.document_id", $id)
            ->orderBY("a.changed_at", "DESC")
            ->get();

        return $query;
    }

    public function updateApprovalDocument($request)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table($this->table)
                ->where("document_id", $request->document_id)
                ->update([
                    "approved_by" => $request->approval_by
                ]);

            /* ----------
             Logs
            ----------------------- */
            $this->logModel->createLog("UPDATE APPROVAL DOCUMENT (" .  $request->document_id . ")", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "UPDATE APPROVAL DOCUMENT FAILED", "");
            # ---------------
            return array("status" => false, "id" => 0);
        }
    }

    public function updateDeadlineDocument($request)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table($this->table)
                ->where("document_id", $request->document_id)
                ->update([
                    "deadline" => (!empty($request->deadline)) ? setYMD($request->deadline, "/") : null
                ]);

            /* ----------
             Logs
            ----------------------- */
            $this->logModel->createLog("UPDATE DEADLINE DOCUMENT (" .  $request->document_id . ")", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "UPDATE APPROVAL DEADLINE FAILED", "");
            # ---------------
            return array("status" => false, "id" => 0);
        }
    }

    public function checkDuplicateDocumentNo($id)
    {
        $query  = DB::table("document as a")
            ->select("a.*","b.name as status_name")
            ->leftjoin("ref_issue_status as b", "a.issue_status_id", "b.issue_status_id")
            ->where("a.document_no", $id)
            ->first();

        return $query;
    }

    public function createTempVdrl($request)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            // DELETE DOCUMENT TEMP TABLE
            DB::table('document_temp')->where('user_id',  Auth::user()->id)->delete();
            # ------------------------
            if ($request->hasFile('upload_file')) {
                Excel::load($request->file('upload_file')->getRealPath(), function ($reader) use ($request) {
                    // GET CONFIG   
                    $qCon = DB::table("sys_config")
                            ->select("*")
                            ->first();

                    foreach ($reader->toarray() as $key => $row) {

                        if ($row['document_no'] != "" && $row['document_no'] != null) {
                            $qDoc = $this->checkDuplicateDocumentNo($row['document_no']);

                            if ($qDoc == null) {
                                $status = 1;
                                $note = 'New document';
                            } else {
                                if ($qDoc->issue_status_id == 4 || $qDoc->issue_status_id == 6 || $qDoc->issue_status_id == 9) {
                                    $status = 3;
                                    $note = "The document can't be updated because the status is " . $qDoc->status_name;
                                } else {
                                    $status = 2;
                                    $note = 'The document will be updated';
                                }
                            }
    
                            DB::table("document_temp")
                                ->insert([
                                    "document_no" => $row['document_no'],
                                    "document_title" => $row['document_title'],
                                    "document_description" => $row['description'],
                                    "project_id" => $request->project_id,
                                    "vendor_id" => $request->vendor_id,
                                    "ref_no" => $row['ref_number'],
                                    "incoming_transmittal_detail_id" => 0,
                                    "reviewer" => $row['reviewer'],
                                    "approver" => $row['approver'],
                                    "observer" => $row['observer'],
                                    "note" => $note,
                                    "status" => $status,
                                    "is_idc" => $request->is_document_idc,
                                    "user_id" => Auth::user()->id,
                                    "approved_by" => $qCon->default_approval_id,
                                    "type" => $row['type'],
                                    "area" => $row['area'],
                                    "pic" => $row['pic'],
                                    "departemen" => $row['departemen']
                                ]);
                        }
                    }
                });
            }
            /* ----------
             Logs
            ----------------------- */
            $this->logModel->createLog("UPLOAD DOCUMENT VDRL TO TEMP TABLE", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            throw $e;
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "UPLOAD DOCUMENT VDRL TO TEMP TABLE FAILED", "");
            # ---------------
            return array("status" => false, "id" => 0);
        }
    }

    public function getDocumentViewTemp($id)
    {
        $kode = base64_decode($id);
        list($project_id, $vendor_id, $user_id, $date) = explode("|", $kode);

        $query  = DB::table("document_temp as doc_temp")
            ->leftJoin('ref_area as ref_a', 'ref_a.area_id', '=', 'doc_temp.area')
            ->leftJoin('ref_document_type as ref_doc', 'ref_doc.document_type_id', '=', 'doc_temp.type')
            ->leftJoin('sys_users as users', 'users.id', '=', 'doc_temp.pic')
            ->leftJoin('ref_department as depart', 'depart.department_id', '=', 'doc_temp.departemen')
            ->select('doc_temp.*', 'ref_a.name as area_name', 'ref_doc.name as doc_type_name', 'users.full_name as pic_name', 'depart.name as department_name')
            ->where("doc_temp.project_id", $project_id)
            ->where("doc_temp.vendor_id", $vendor_id)
            ->where("doc_temp.user_id", Auth::user()->id)
            ->orderBy("doc_temp.status", "DESC")
            ->get();

        return $query;
    }

    public function getDocumentTemp($id)
    {
        $kode = base64_decode($id);
        list($project_id, $vendor_id, $user_id, $date) = explode("|", $kode);

        $query  = DB::table("document_temp")
            ->select('*')
            ->where("project_id", $project_id)
            ->where("vendor_id", $vendor_id)
            ->where("user_id", Auth::user()->id)
            ->get();

        return $query;
    }

    public function getDocumentTempIsReady($id)
    {
        $kode = base64_decode($id);
        list($project_id, $vendor_id, $user_id, $date) = explode("|", $kode);

        $query  = DB::table("document_temp")
            ->select('*')
            ->where("project_id", $project_id)
            ->where("vendor_id", $vendor_id)
            ->whereIn("status", [1,2])
            ->where("user_id", Auth::user()->id)
            ->get();

        return $query;
    }

    public function getDocumentByNomor($nomor)
    {
        $query  = DB::table("document")
            ->select('*')
            ->where("document_no", $nomor)
            ->first();

        return $query;
    }

    public function createUploadVdrl($request)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            # ------------------------
            $qTemp = $this->getDocumentTempIsReady($request->id);
            foreach ($qTemp as $row) {
                if ($row->status == 1) {
                    // NEW DOCUMENT
                    $docId         = DB::table("document")
                    ->insertGetId([
                        "document_no" => $row->document_no,
                        "document_title" => $row->document_title,
                        "document_description" => $row->document_description,
                        "project_id" => $row->project_id,
                        "vendor_id" => $row->vendor_id,
                        "status" => 1, // NEW
                        "ref_no" => $row->ref_no,
                        "incoming_transmittal_detail_id" => $row->incoming_transmittal_detail_id,
                        "issue_status_id"=> $row->is_idc == "YES" ? 1 : 0,
                        "document_status_id"=> $row->is_idc == "YES" ? 94 : 0,
                        "created_by" => Auth::user()->id,
                        "created_at" => Carbon::now()->toDateTimeString(),
                        "approved_by" => $row->approved_by,
                        "document_type_id"=> $row->type, // default 1
                        "area_id" => $row->area, // new
                        "pic_id" => $row->pic, // new
                        "department_id" => $row->departemen, // new
                    ]);

                    // INSERT TO TABLE DOCUMENT LOG
                    DB::table("document_change_log")
                        ->insert([
                            "document_id" => $docId,
                            "document_no" => $row->document_no,
                            "document_title" => $row->document_title,
                            "changed_by" => Auth::user()->id,
                            "changed_at" => Carbon::now()->toDateTimeString()
                        ]);

                    // $docId = 1;
                    $this->createAssignment($docId, $row->reviewer, $row->approver, $row->observer);
                } else {
                    // UPDATE DOCUMENT
                    $qDoc = $this->getDocumentByNomor($row->document_no);
                    DB::table("document")
                        ->where("document_no", $row->document_no)
                        ->update([
                            "document_title" => $row->document_title,
                            "document_description" => $row->document_description,
                        ]);

                    // // INSERT TO TABLE DOCUMENT LOG
                    DB::table("document_change_log")
                    ->insert([
                        // "document_id" => $qDoc->document_id,
                        "document_no" => $row->document_no,
                        "document_title" => $row->document_title,
                        "changed_by" => Auth::user()->id,
                        "changed_at" => Carbon::now()->toDateTimeString()
                    ]);

                    // Update Assignment
                    $this->UpdateAssignment($qDoc->document_id, $row->reviewer, $row->approver, $row->observer);
                }
                
            }
            /* ----------
             Logs
            ----------------------- */
            $this->logModel->createLog("UPLOAD FILE VDRL", Auth::user()->id, $request);
            # ------------------------

            // DELETE DOCUMENT TEMP TABLE
            DB::table('document_temp')->where('user_id',  Auth::user()->id)->delete();

            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            throw $e;
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "UPLOAD FILE VDRL FAILED", "");
            # ---------------
            return array("status" => false, "id" => 0);
        }
    }

    public function createAssignment($idDoc, $reviewer, $approver, $observer )
    {
        if ($approver != null) {
            $assignmentId         = DB::table("assignment")
                ->insertGetId([
                    "document_id" =>  $idDoc,
                    "incoming_transmittal_detail_id" => 0,
                    "created_by" => Auth::user()->id,
                    "created_at" => Carbon::now()->toDateTimeString(),
                ]);

            if ($reviewer == null) {
                $noApprover = 1;
            } else {
                $arrayRev = explode(",", $reviewer);
                $noApprover = count($arrayRev) + 1;
            }

            DB::table("comment")
                ->insert([
                    "assignment_id" => $assignmentId,
                    "user_id" => $approver,
                    "role" => 'APPROVER',
                    "status" => 0, // NEW
                    "order_no" => $noApprover
                ]);

            if ($reviewer != null) {
                $array = explode(",", $reviewer);
                foreach ($array as $index => $row) {
                    $no = ++$index;
                    # ------------------------
                    DB::table("comment")
                        ->insert([
                            "assignment_id" => $assignmentId,
                            "user_id" => $row,
                            "role" => 'REVIEWER',
                            "status" => 0, // NEW
                            "order_no" => $no
                        ]);
                }
            }

            if ($observer != null) {
                $arrayob = explode(",", $observer);
                foreach ($arrayob as $index => $val) {
                    $no = ++$index;
                    # ------------------------
                    DB::table("comment")
                        ->insert([
                            "assignment_id" => $assignmentId,
                            "user_id" => $val,
                            "role" => 'OBSERVER',
                            "status" => 0, // NEW
                            "order_no" => $no
                        ]);
                }
            }
        }
    }

    public function checkAssignmentByIdDoc($id, $trans)
    {
        $query  = DB::table("assignment")
            ->select("*")
            ->where("document_id", $id)
            ->where("incoming_transmittal_detail_id", $trans)
            ->first();

        return $query;
    }

    public function checkRoleApproverByUserId($assignmentId)
    {
        $query  = DB::table("comment")
            ->select("*")
            ->where("assignment_id", $assignmentId)
            ->where("role", "APPROVER")
            ->get();

        return $query;
    }

    public function checkCommentByAssignmentIdAndUserId($assignmentId, $userId)
    {
        $query  = DB::table("comment")
            ->select("*")
            ->where("assignment_id", $assignmentId)
            ->where("user_id", $userId)
            ->first();

        return $query;
    }

    public function checkCommentReviewerByAssignmentIdAndUserId($assignmentId, $userId)
    {
        $query  = DB::table("comment")
            ->select("*")
            ->where("assignment_id", $assignmentId)
            ->where("user_id", $userId)
            ->where("role", "REVIEWER")
            ->first();

        return $query;
    }

    public function UpdateAssignment($idDoc, $reviewer, $approver, $observer)
    {
        if ($approver != null) {
            $qDocument = $this->getDataById($idDoc);
            
            $qAssigment = $this->checkAssignmentByIdDoc($idDoc, $qDocument->incoming_transmittal_detail_id);

            if ($qAssigment == null) {
                // CREATE ASSIGNMENT

                $assignmentId         = DB::table("assignment")
                ->insertGetId([
                    "document_id" =>  $idDoc,
                    "incoming_transmittal_detail_id" => $qDocument->incoming_transmittal_detail_id,
                    "created_by" => Auth::user()->id,
                    "created_at" => Carbon::now()->toDateTimeString(),
                ]);

                if ($reviewer == null) {
                    $noApprover = 1;
                } else {
                    $arrayRev = explode(",", $reviewer);
                    $noApprover = count($arrayRev) + 1;
                }

                DB::table("comment")
                    ->insert([
                        "assignment_id" => $assignmentId,
                        "user_id" => $approver,
                        "role" => 'APPROVER',
                        "status" => 0, // NEW
                        "order_no" => $noApprover
                    ]);

                if ($reviewer != null) {
                    $array = explode(",", $reviewer);
                    foreach ($array as $index => $row) {
                        $no = ++$index;
                        # ------------------------
                        DB::table("comment")
                            ->insert([
                                "assignment_id" => $assignmentId,
                                "user_id" => $row,
                                "role" => 'REVIEWER',
                                "status" => 0, // NEW
                                "order_no" => $no
                            ]);
                    }
                }

                if ($observer != null) {
                    $array = explode(",", $observer);
                    foreach ($array as $index => $row) {
                        $no = ++$index;
                        # ------------------------
                        DB::table("comment")
                            ->insert([
                                "assignment_id" => $assignmentId,
                                "user_id" => $row,
                                "role" => 'OBSERVER',
                                "status" => 0, // NEW
                                "order_no" => $no
                            ]);
                    }
                }
            } else {
                // UPDATE ASSIGNMENT

                if ($reviewer == null) {
                    $noApprover = 1;
                } else {
                    $arrayRev = explode(",", $reviewer);
                    $noApprover = count($arrayRev) + 1;
                }

                // CHECK SUDAH ADA ATAU BELUM APPROVER NYA
                $qCheckRole = $this->checkRoleApproverByUserId($qAssigment->assignment_id);

                if (count($qCheckRole) == 0) {
                    DB::table("comment")
                    ->insert([
                        "assignment_id" => $qAssigment->assignment_id,
                        "user_id" => $approver,
                        "role" => 'APPROVER',
                        "status" => 1, // NEW
                        "order_no" => $noApprover
                    ]);
                } else {
                    // DELETE APPROVER WHERE STATUS 0
                    DB::table('comment')
                        ->where('assignment_id',  $qAssigment->assignment_id)
                        ->whereIn('status', [0,1])
                        ->where('role', "APPROVER")
                        ->delete();

                    $qCheckApprover = $this->checkRoleApproverByUserId($qAssigment->assignment_id);

                    if (count($qCheckApprover) == 0) {
                        DB::table("comment")
                        ->insert([
                            "assignment_id" => $qAssigment->assignment_id,
                            "user_id" => $approver,
                            "role" => 'APPROVER',
                            "status" => 1, // NEW
                            "order_no" => $noApprover
                        ]);
                    } else {
                        // DB::table("comment")
                        //     ->where("assignment_id", $qAssigment->assignment_id)
                        //     ->where("status", 0)
                        //     ->where("role", "APPROVER")
                        //     ->update([
                        //         "user_id" => $approver
                        //     ]);
                    }

                    
                }

                if ($reviewer != null) {
                    DB::table('comment')
                    ->where('role',  'REVIEWER')
                    ->where('status',  '!=', 2)
                    ->where('assignment_id',  $qAssigment->assignment_id)
                    ->delete();

                    $array = explode(",", $reviewer);
                    foreach ($array as $index => $row) {
                        $no = ++$index;
                        $role = "REVIEWER";
                        $qComment = $this->checkCommentReviewerByAssignmentIdAndUserId($qAssigment->assignment_id, $row);

                        if ($qComment == null) {
                            # ------------------------
                            DB::table("comment")
                            ->insert([
                                "assignment_id" => $qAssigment->assignment_id,
                                "user_id" => $row,
                                "role" => $role,
                                "status" => 1, // NEW
                                "order_no" => $no
                            ]);
                        }
                    }
                }

                DB::table('comment')
                ->where('role',  'OBSERVER')
                ->where('assignment_id',  $qAssigment->assignment_id)
                ->delete();

                if ($observer != null) {
                    $array = explode(",", $observer);
                    foreach ($array as $index => $row) {
                        
                        $no = ++$index;
                        $role = "OBSERVER";
                        # ------------------------
                        DB::table("comment")
                        ->insert([
                            "assignment_id" => $qAssigment->assignment_id,
                            "user_id" => $row,
                            "role" => $role,
                            "status" => 1, // NEW
                            "order_no" => $no
                        ]);
                    }
                }
            }

        }
    }

    // REPORT
    public function getSummaryReport($params)
    {
        try {
            list($project_id, $document_type_id, $document_status_id, $document_issue_id, $area_id, $type) = explode("|", base64_decode($params));
            # -----------------
            // if ($type == "SUMMARY_ISSUE") {
            //     $field  = "ref_document_status.name AS document_status_name";
            //     $group  = "document_status_name";
            // } elseif ($type == "SUMMARY_DOCUMENT") {
                $field  = "ref_issue_status.name AS issue_status_name";
                $group  = "issue_status_name";
            // } else {
            //     $field  = "ref_document_type.name AS document_type";
            //     $group  = "document_type";
            // }
            # -----------------
            $query  = DB::table($this->table)
                ->select("project.project_name", "ref_vendor.name", "$field", DB::RAW("COUNT($this->table.document_id) AS number_of_documents"), DB::RAW("COUNT(IF($this->table.status = '4', 1, NULL)) AS waiting_for_return"))
                ->leftjoin("incoming_transmittal_detail", "$this->table.incoming_transmittal_detail_id", "incoming_transmittal_detail.incoming_transmittal_detail_id")
                ->leftjoin("incoming_transmittal", "incoming_transmittal_detail.incoming_transmittal_id", "incoming_transmittal.incoming_transmittal_id")
                ->leftjoin("outgoing_transmittal_detail", "$this->table.outgoing_transmittal_detail_id", "outgoing_transmittal_detail.outgoing_transmittal_detail_id")
                ->leftjoin("outgoing_transmittal", "outgoing_transmittal_detail.outgoing_transmittal_id", "outgoing_transmittal.outgoing_transmittal_id")
                ->leftJoin("ref_document_type", "document.document_type_id", "ref_document_type.document_type_id")
                ->leftJoin("project", "document.project_id", "project.project_id")
                ->leftJoin("ref_department", "$this->table.department_id", "ref_department.department_id")
                ->leftJoin("ref_document_status", "incoming_transmittal_detail.document_status_id", "ref_document_status.document_status_id")
                ->leftJoin("ref_issue_status", "incoming_transmittal_detail.issue_status_id", "ref_issue_status.issue_status_id")
                ->leftJoin("ref_return_status", "incoming_transmittal_detail.return_status_id", "ref_return_status.return_status_id")
                ->leftJoin("ref_return_status AS ref_return_status_outgoing", "outgoing_transmittal_detail.return_status_id", "ref_return_status_outgoing.return_status_id")
                ->leftJoin("ref_vendor", "$this->table.vendor_id", "ref_vendor.vendor_id")
                ->groupBy("project.project_name", "ref_vendor.name", "$group")
                ->where("$this->table.status", "!=", 0);

            if ($project_id != 0) {
                $query->where("document.project_id", $project_id);
            }
            if ($document_type_id != 0) {
                $query->where("document.document_type_id", $document_type_id);
            }
            if ($document_status_id != 0) {
                $query->where("document.document_status_id", $document_status_id);
            }
            if ($document_issue_id != 0) {
                $query->where("document.issue_status_id", $document_issue_id);
            }
            if ($area_id != 0) {
                $query->where("document.area_id", $area_id);
            }

            return $query;
        } catch (\Exception $e) {
            return array("status" => false, "error_log" => $e->getMessage());
        }
    }

    public function getDetailReport($params)
    {
        try {
            list($project_id, $document_type_id, $document_status_id, $document_issue_id, $area_id, $type) = explode("|", base64_decode($params));
            # -----------------
            $query  = DB::table($this->table)
                ->select(
                    "$this->table.document_no",
                    "$this->table.ref_no",
                    "$this->table.document_title",
                    "$this->table.document_description",
                    "project.project_name",
                    "ref_document_type.name AS document_type",
                    "sys_users.full_name AS pic_name",
                    "ref_department.name AS pic_dept_name",
                    "ref_document_status.name AS document_status_name",
                    "ref_issue_status.name AS issue_status_name",
                    "outgoing_transmittal.outgoing_no",
                    "outgoing_transmittal.subject as outgoing_subject",
                    "outgoing_transmittal.sender_date as outgoing_sender_date",
                    "ref_return_status_outgoing.name AS return_status_outgoing_name",
                    "incoming_transmittal.return_date_plan",
                    "incoming_transmittal.return_date_actual",
                    "incoming_transmittal.receive_date AS incoming_receive_date",
                    "incoming_transmittal.subject AS incoming_subject",
                    "incoming_transmittal.incoming_no",
                    "ref_return_status.name AS return_status_name",
                    "incoming_transmittal_detail.remark",
                    db::Raw("(CASE $this->table.status WHEN 1 THEN 'Unissued' WHEN 2 THEN 'Waiting for reviewer' WHEN 3 THEN 'Waiting for compiler' WHEN 4 THEN 'Waiting for return' WHEN 5 THEN 'Waiting for approval' WHEN 7 THEN 'Waiting for view' WHEN 99 THEN 'Stored' WHEN 88 THEN 'Reject' WHEN 6 THEN 'Done' END) AS status_code"),
                    "ref_vendor.name AS vendor_name"
                )
                ->leftjoin("incoming_transmittal_detail", "$this->table.incoming_transmittal_detail_id", "incoming_transmittal_detail.incoming_transmittal_detail_id")
                ->leftjoin("incoming_transmittal", "incoming_transmittal_detail.incoming_transmittal_id", "incoming_transmittal.incoming_transmittal_id")
                ->leftjoin("outgoing_transmittal_detail", "$this->table.outgoing_transmittal_detail_id", "outgoing_transmittal_detail.outgoing_transmittal_detail_id")
                ->leftjoin("outgoing_transmittal", "outgoing_transmittal_detail.outgoing_transmittal_id", "outgoing_transmittal.outgoing_transmittal_id")
                ->leftJoin("ref_document_type", "document.document_type_id", "ref_document_type.document_type_id")
                ->leftJoin("project", "document.project_id", "project.project_id")
                ->leftJoin("ref_department", "$this->table.department_id", "ref_department.department_id")
                ->leftJoin("ref_document_status", "incoming_transmittal_detail.document_status_id", "ref_document_status.document_status_id")
                ->leftJoin("ref_issue_status", "incoming_transmittal_detail.issue_status_id", "ref_issue_status.issue_status_id")
                ->leftJoin("ref_return_status", "incoming_transmittal_detail.return_status_id", "ref_return_status.return_status_id")
                ->leftJoin("ref_return_status AS ref_return_status_outgoing", "outgoing_transmittal_detail.return_status_id", "ref_return_status_outgoing.return_status_id")
                ->leftJoin("sys_users", "document.pic_id", "sys_users.id")
                ->leftJoin("ref_vendor", "document.vendor_id", "ref_vendor.vendor_id")
                ->where("$this->table.status", "!=", 0);

            if ($project_id != 0) {
                $query->where("document.project_id", $project_id);
            }
            if ($document_type_id != 0) {
                $query->where("document.document_type_id", $document_type_id);
            }
            if ($document_status_id != 0) {
                $query->where("document.document_status_id", $document_status_id);
            }
            if ($document_issue_id != 0) {
                $query->where("document.issue_status_id", $document_issue_id);
            }
            if ($area_id != 0) {
                $query->where("document.area_id", $area_id);
            }

            return $query;
        } catch (\Exception $e) {
            return array("status" => false, "error_log" => $e->getMessage());
        }
    }

    public function approveIFI($request)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table($this->table)
                ->where("document_id", $request->document_id)
                ->update([
                    "status" => $request->status,
                    "approved_comment"=>$request->remark_approval,
                    "approved_by"=>Auth::user()->id,
                    "approved_at"=>Carbon::now()->toDateTimeString(),
                ]);
            /* ----------
             Logs
            ----------------------- */
            $this->logModel->createLog("APPROVE IFI (" .  $request->document_id . ")", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "APPROVE IFI FAILED", "");
            # ---------------
            return array("status" => false, "id" => 0);
        }
    }

    public function updateStatus($id)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table($this->table)
                ->where("document_id", $id)
                ->update([
                    "status" => 6,
                    ]);
            /* ----------
             Logs
            ----------------------- */
            $this->logModel->createLog("Update Status Shop Drawing (" .  $id . ")", Auth::user()->id);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "UPDATE STATUS SHOPE DRAWING FAILED", "");
            # ---------------
            return array("status" => false, "id" => 0);
        }
    }

    public function updateStatusCancle($request)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table($this->table)
                ->where("document_id", $request->document_id)
                ->update([
                    "status" => 6
                    ]);
            /* ----------
             Logs
            ----------------------- */
            $this->logModel->createLog("Update Status Shop Drawing (" .  $request->document_id . ")", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "UPDATE STATUS SHOPE DRAWING FAILED", "");
            # ---------------
            return array("status" => false, "id" => 0);
        }
    }

    public function resetAssignment($assignment_id, $user_id)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table("comment")
                ->where("assignment_id", $assignment_id)
                ->where("user_id", $user_id)
                ->update([
                    "status" => 1,
                ]);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            # ------------------------
            $this->logModel->createError($e->getMessage(), "RESET ASSIGNMENT FAILED", "");
            # ------------------------
            return array("status" => false, "id" => 0);
        }
    }

    public function get($id) {
        $query  = DB::table("document")->select("*")->where("document_id", $id)->first();

        return $query;
    }

    public function get_document_vendor($project_id) {
        try {
            $result     = DB::table("document")->select("document.vendor_id", "name AS vendor_name")
                                               ->join("ref_vendor", "document.vendor_id", "ref_vendor.vendor_id")
                                               ->where("document.project_id", $project_id)
                                               ->groupBy("document.vendor_id", "ref_vendor.name")
                                               ->orderBy("document.vendor_id")
                                               ->get();

            return array("status"=>true, "data"=>$result);
        } catch (\Exception $e) {
            return array("status"=>false, "data"=>[]);
        }
    }

    public function get_document_vendor_qty($project_id, $vendor_id, $issue_status_id) {
        try {
            $result     = DB::table("document")->select(DB::RAW("COUNT(document_id) AS unit"))
                                               ->where("project_id", $project_id)
                                               ->where("vendor_id", $vendor_id)
                                               ->where("issue_status_id", $issue_status_id)
                                               ->where("status", "!=", 0)
                                               ->first();

            return array("status"=>true, "data"=>$result);
        } catch (\Exception $e) {
            return array("status"=>false, "data"=>[]);
        }
    }

    public function get_document_transmittal_vendor_qty($project_id, $vendor_id, $issue_status_id) {
        try {
            $result     = DB::table("incoming_transmittal_detail")->select(DB::RAW("COUNT(*) AS unit"))
                                               ->join("document", "incoming_transmittal_detail.document_id", "document.document_id")
                                               ->join("incoming_transmittal", "incoming_transmittal_detail.incoming_transmittal_id", "incoming_transmittal.incoming_transmittal_id")
                                               ->where("incoming_transmittal.status", 2)
                                               ->where("document.status", "!=", 0)
                                               ->where("document.project_id", $project_id)
                                               ->where("document.vendor_id", $vendor_id)
                                               ->where("document.issue_status_id", $issue_status_id)
                                               ->first();

            return array("status"=>true, "data"=>$result);
        } catch (\Exception $e) {
            return array("status"=>false, "data"=>[]);
        }
    }

    public function getIFIContructionCollections()
    {
        try {
            $query  = DB::table($this->table)
                ->select(
                    "$this->table.document_id",
                    "$this->table.document_no",
                    "$this->table.document_title",
                    "$this->table.deadline",
                    DB::Raw("DATE_FORMAT($this->table.deadline, '%d-%m-%Y') AS deadline"),
                    "a.name as document_type_name",
                    "b.name as vendor_name",
                    "c.name as area_name",
                    "d.project_name",
                    "$this->table.status",
                    DB::Raw("concat(e.name,' - ', f.name) as issue_status"),
                    "e.name as document_status",
                    db::Raw("(CASE $this->table.status WHEN 1 THEN 'Unissued' WHEN 2 THEN 'Waiting for reviewer' WHEN 3 THEN 'Waiting for compiler' WHEN 4 THEN 'Waiting for return' WHEN 5 THEN 'Waiting for approval' WHEN 7 THEN 'Waiting for view' WHEN 99 THEN 'Stored' WHEN 88 THEN 'Reject' WHEN 6 THEN 'Done' END) AS status_code"),
                    db::Raw("COUNT(h.comment_id) AS unit"),
                    db::Raw("COUNT(IF((h.status = '2'),1,NULL)) AS done"),
                    db::Raw("COUNT(IF((h.status = '1'),1,NULL)) AS progress"),
                    db::Raw("GROUP_CONCAT(i.full_name ORDER BY h.order_no) AS list_name"),
                    db::Raw("GROUP_CONCAT(h.status ORDER BY h.order_no) AS list_status")
                )
                ->leftjoin("ref_document_type as a", "$this->table.document_type_id", "a.document_type_id")
                ->leftjoin("ref_vendor as b", "$this->table.vendor_id", "b.vendor_id")
                ->leftjoin("ref_area as c", "$this->table.area_id", "c.area_id")
                ->leftjoin("project as d", "$this->table.project_id", "d.project_id")
                ->leftjoin("ref_document_status as e", "$this->table.document_status_id", "e.document_status_id")
                ->leftjoin("ref_issue_status as f", "$this->table.issue_status_id", "f.issue_status_id")
                ->leftJoin('assignment as g', function ($join) {
                    $join->on("$this->table.document_id", "g.document_id");
                    $join->on("$this->table.incoming_transmittal_detail_id", "g.incoming_transmittal_detail_id");
                })
                ->leftjoin("comment as h", "g.assignment_id", "h.assignment_id")
                ->leftjoin("sys_users as i", "h.user_id", "i.id")
                // ->where("$this->table.status", "!=", 0)
                ->where("$this->table.issue_status_id", 18)
                ->groupBy(
                    "$this->table.document_id",
                    "$this->table.document_no",
                    "$this->table.document_title",
                    "deadline",
                    "a.name",
                    "b.name",
                    "c.name",
                    "d.project_name",
                    "e.name",
                    "f.name",
                    "$this->table.status"
                )
                ->orderBy("$this->table.document_id", "DESC");

            if (session()->has("SES_SEARCH_IFI_DOCUMENT_NO") != "") {
                $query->where("$this->table.document_no", "LIKE", "%" . session()->get("SES_SEARCH_IFI_DOCUMENT_NO") . "%");
            }

            if (session()->has("SES_SEARCH_IFI_DOCUMENT_TITLE") != "") {
                $query->where("$this->table.document_title", "LIKE", "%" . session()->get("SES_SEARCH_IFI_DOCUMENT_TITLE") . "%");
            }

            if (session()->has("SES_SEARCH_IFI_DOCUMENT_STATUS")) {
                if (session()->get("SES_SEARCH_IFI_DOCUMENT_STATUS") != "0") {
                    $query->where("$this->table.status", session()->get("SES_SEARCH_IFI_DOCUMENT_STATUS"));
                }
            }

            if (session()->has("SES_SEARCH_IFI_DOCUMENT_VENDOR")) {
                if (session()->get("SES_SEARCH_IFI_DOCUMENT_VENDOR") != "0") {
                    $query->where("$this->table.vendor_id", session()->get("SES_SEARCH_IFI_DOCUMENT_VENDOR"));
                }
            }

            $result = $query->paginate(PAGINATION);

            return array("status" => true, "data" => $result);
        } catch (\Exception $e) {
            return array("status" => false, "data" => []);
        }
    }

    public function getDataHistoryMigration($id)
    {
        $document = DB::table("document")->select("original_document_no")->where("document_id", $id)->first();
        $query = [];
        if($document && $document->original_document_no){
            $query  = DB::table("document as a")
                        ->select(
                            "a.document_no", "a.document_title",
                            "b.name AS issue_name", "c.name AS revision_code",
                            "url_file", "file_migration"
                        )
                        ->join("ref_issue_status as b", "a.issue_status_id", "b.issue_status_id")
                        ->join("ref_document_status as c", "a.document_status_id", "c.document_status_id")
                        ->where("a.original_document_no", $document->original_document_no)
                        ->orderBY("a.document_id", "ASC")
                        ->get();
        }
        return $query;
    }    

    public function getdataDocument()
    {
        $query_sp = DB::statement("CALL sp_current_vdrl()");
        $query_temp_tab = DB::select("SELECT * from temp_current_vdrl");

        return ($query_temp_tab);
    }

    public function updateDocumentStatus($request)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table($this->table)
                ->where("document_id", $request->document_id)
                ->update([
                    "status" => 4, // Waiting for return
                    "updated_by" => Auth::user()->id,
                    "updated_at" => Carbon::now()->toDateTimeString(),
                ]);
            /* ----------
             Logs
            ----------------------- */
            $this->logModel->createLog("CHANGE DOCUMENT STATUS (" . $request->document_id . ")", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "DELETE CHANGE DOCUMENT STATUS FAILED", "");
            # ---------------
            return array("status" => false, "id" => 0);
        }
    }
}
