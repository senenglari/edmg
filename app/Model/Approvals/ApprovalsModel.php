<?php

namespace App\Model\Approvals;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use Storage;
use File;
use DataTables;
use Mail;
use Carbon\Carbon;
use App\Model\Sys\LogModel;
use App\Model\Sys\SysModel;

class ApprovalsModel extends Model
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
                                "$this->table.*",
                                "d.project_name",
                                "a.name as document_type_name",
                                "b.name as vendor_name",
                                "c.name as area_name",
                                "d.project_name",
                                "f.name as issue_status"
                            )
                            ->join("ref_document_type as a", "$this->table.document_type_id", "a.document_type_id")
                            ->leftjoin("ref_vendor as b", "$this->table.vendor_id", "b.vendor_id")
                            ->leftjoin("ref_area as c", "$this->table.area_id", "c.area_id")
                            ->leftjoin("project as d", "$this->table.project_id", "d.project_id")
                            ->leftjoin("ref_issue_status as f", "$this->table.issue_status_id", "f.issue_status_id")
                            ->whereRaw("((document.approved_by = ".Auth::user()->id." AND document.issue_status_id in(4,11)) OR (document.approved_design_by = ".Auth::user()->id. " and document.issue_status_id=999999999))")
                            // ->whereIn("$this->table.issue_status_id", [4,11,12])
                            ->orderBy("$this->table.document_id", "ASC");

            if (session()->has("SES_SEARCH_APPROVALS_PROJECT_NAME") != "") {
                $query->where("d.project_name", "LIKE", "%" . session()->get("SES_SEARCH_APPROVALS_PROJECT_NAME") . "%");
            }

            if (session()->has("SES_SEARCH_APPROVALS_NO") != "") {
                $query->where("$this->table.document_no", "LIKE", "%" . session()->get("SES_SEARCH_APPROVALS_NO") . "%");
            }

            if (session()->has("SES_SEARCH_APPROVALS_TITLE") != "") {
                $query->where("$this->table.document_title", "LIKE", "%" . session()->get("SES_SEARCH_APPROVALS_TITLE") . "%");
            }

            if (session()->has("SES_SEARCH_APPROVALS_TYPE")) {
                if (session()->get("SES_SEARCH_APPROVALS_TYPE") != "0") {
                    $query->where("$this->table.document_type_id", session()->get("SES_SEARCH_APPROVALS_TYPE"));
                }
            }

            if (session()->has("SES_SEARCH_APPROVALS_VENDOR")) {
                if (session()->get("SES_SEARCH_APPROVALS_VENDOR") != "0") {
                    $query->where("$this->table.vendor_id", session()->get("SES_SEARCH_APPROVALS_VENDOR"));
                }
            }

            if (session()->has("SES_SEARCH_APPROVALS_AREA")) {
                if (session()->get("SES_SEARCH_APPROVALS_AREA") != "0") {
                    $query->where("$this->table.area_id", session()->get("SES_SEARCH_APPROVALS_AREA"));
                }
            }

            $result = $query->paginate(PAGINATION);

            return array("status" => true, "data" => $result);
        } catch (\Exception $e) {
            throw $e;
            return array("status" => false, "data" => []);
        }
    }

    public function getHeader($id)
    {
        $query  = DB::table($this->table)
                        ->select(
                            "$this->table.*",
                            "d.project_name",
                            "a.name as document_type_name",
                            "b.name as vendor_name",
                            "c.name as area_name",
                            "d.project_name",
                            "f.name as issue_status",
                            "z.document_url",
                            "z.document_file",
                            "z.document_file_2",
                            "z.assignment_id",
                            "y.incoming_transmittal_detail_id",
                            "y.document_url as document_url_incoming",
                            "y.document_file as document_file_incoming",
                            "y.document_file_revision",
                            "x.incoming_no"
                        )
                        ->join("assignment as z", "$this->table.incoming_transmittal_detail_id", "z.incoming_transmittal_detail_id")
                        ->join("incoming_transmittal_detail as y", "$this->table.incoming_transmittal_detail_id", "y.incoming_transmittal_detail_id")
                        ->join("incoming_transmittal as x", "y.incoming_transmittal_id", "x.incoming_transmittal_id")
                        ->join("ref_document_type as a", "$this->table.document_type_id", "a.document_type_id")
                        ->leftjoin("ref_vendor as b", "$this->table.vendor_id", "b.vendor_id")
                        ->leftjoin("ref_area as c", "$this->table.area_id", "c.area_id")
                        ->leftjoin("project as d", "$this->table.project_id", "d.project_id")
                        ->leftjoin("ref_issue_status as f", "$this->table.issue_status_id", "f.issue_status_id")
                        ->where("$this->table.document_id", $id)
                        ->first();

        return $query;
    }

    public function getDetail($id)
    {
        $query  = DB::table("comment")
                        ->select(
                            "comment.*",
                            "sys_users.name as user_name",
                            db::Raw("DATE_FORMAT(comment.updated_at, '%d/%m/%Y %H:%i:%s') as tanggal_log")
                        )
                        ->leftjoin("sys_users", "sys_users.id", "=", "comment.user_id")
                        ->where("comment.assignment_id", $id)
                        ->get();

        return $query;
    }

    public function saveApprovals($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            if($request->issue_status_id==4) {
                /* ----------
                Proses dari AFD
                ------------------ */
                DB::table("document")
                        ->where("document_id", $request->idData)
                        ->update([
                            "approved_design_comment"=>$request->approved_comment,
                            "issue_status_id"=>$request->issue_status_id,
                            "approved_design_at"=>now(),
                            "created_approved_by"=>Auth::user()->id,
                            "created_approved_at"=>now()
                        ]);

                $idApproval = DB::table("approval")
                                    ->insertGetId([
                                        "assignment_id"=>$request->assignment_id,
                                        "user_id"=>Auth::user()->id,
                                        "remark"=>$request->approved_comment,
                                        "issue_status_id"=>$request->issue_status_id,
                                        "role"=>"DESIGN",
                                        "status"=>2,
                                        "order_no"=>1,
                                        "created_by"=>$request->created_design_by,
                                        "created_at"=>$request->created_design_at,
                                        "updated_by"=>Auth::user()->id,
                                        "updated_at"=>now()
                                    ]);

                DB::table("incoming_transmittal_detail")
                        ->where("incoming_transmittal_detail_id", $request->incoming_transmittal_detail_id)
                        ->update([
                            "issue_status_id"=>$request->issue_status_id
                        ]);

                $qDataNextApproval  = DB::table("document")
                                        ->select("*", "sys_users.email", "sys_users.full_name")
                                        ->leftjoin("sys_users", "sys_users.id", "=", "document.approved_by")
                                        ->where("document_id", $request->idData)
                                        ->first();

                if(!empty($qDataNextApproval)) {
                    /* ----------
                     Send Email
                    ----------------------- */
                    if($this->sysModel->getConfig()->email_status == 1) {
                        $title              = "Approved for Construction Notification";
                        $emailsAddress      = $qDataNextApproval->email;
                        $fullName           = $qDataNextApproval->full_name;
                        $data["title"]      = "Approved for Construction Notification";
                        $data["inc_no"]     = $request->incoming_no;
                        $data["content"]    = DB::table("incoming_transmittal_detail")
                                                    ->select("document.document_no", "document.document_title"
                                                            , "ref_document_status.name AS document_status_name", "ref_issue_status.name AS issue_status_name")
                                                  ->join("document", "incoming_transmittal_detail.document_id", "document.document_id")
                                                  ->leftJoin("ref_document_status", "incoming_transmittal_detail.document_status_id", "ref_document_status.document_status_id")
                                                  ->leftJoin("ref_issue_status", "incoming_transmittal_detail.issue_status_id", "ref_issue_status.issue_status_id")
                                                  ->where("incoming_transmittal_detail.incoming_transmittal_detail_id", $request->incoming_transmittal_detail_id)
                                                  ->get();

                        # ---------------
                        Mail::send('email.approval-notification', $data, function($message) use ($title, $emailsAddress, $fullName){
                            $message->to($emailsAddress, $fullName)->subject($title);
                            # ---------------
                            $message->from(env("MAIL_USERNAME"), 'Automatic Mail System');
                        });
                    }
                                 
                }
            } else {
                $statusDocument = ($request->issue_status_id==8) ? 3 : 6;

                if($request->issue_status_awal==12) {
                    /* ---------
                    Jika sebelumnya AFD
                    --------------------- */
                    DB::table("document")
                        ->where("document_id", $request->idData)
                        ->update([
                            "approved_design_comment"=>$request->approved_comment,
                            "issue_status_id"=>$request->issue_status_id,
                            // "document_status_id"=>28,
                            "status"=>$statusDocument,
                            "approved_design_at"=>now()
                        ]);

                    $idApproval = DB::table("approval")
                                        ->insertGetId([
                                            "assignment_id"=>$request->assignment_id,
                                            "user_id"=>Auth::user()->id,
                                            "remark"=>$request->approved_comment,
                                            "issue_status_id"=>$request->issue_status_id,
                                            "role"=>"DESIGN",
                                            "status"=>2,
                                            "order_no"=>1,
                                            "created_by"=>$request->created_design_by,
                                            "created_at"=>$request->created_design_at,
                                            "updated_by"=>Auth::user()->id,
                                            "updated_at"=>now()
                                        ]);
                } else {
                    DB::table("document")
                        ->where("document_id", $request->idData)
                        ->update([
                            "approved_comment"=>$request->approved_comment,
                            "issue_status_id"=>$request->issue_status_id,
                            // "document_status_id"=>28,
                            "status"=>$statusDocument,
                            "approved_at"=>now()
                        ]);

                    $noorder = (empty($request->approved_design_by)) ? 1 : 2;
                    $idApproval = DB::table("approval")
                                        ->insertGetId([
                                            "assignment_id"=>$request->assignment_id,
                                            "user_id"=>Auth::user()->id,
                                            "remark"=>$request->approved_comment,
                                            "issue_status_id"=>$request->issue_status_id,
                                            "role"=>"APPROVAL",
                                            "status"=>2,
                                            "order_no"=>$noorder,
                                            "created_by"=>$request->created_approved_by,
                                            "created_at"=>$request->created_approved_at,
                                            "updated_by"=>Auth::user()->id,
                                            "updated_at"=>now()
                                        ]);
                }
                
                DB::table("incoming_transmittal_detail")
                        ->where("incoming_transmittal_detail_id", $request->incoming_transmittal_detail_id)
                        ->update([
                            "issue_status_id"=>$request->issue_status_id
                        ]);
            }

            /* ----------
             Upload File
            ----------------------- */
                $file_url   = "";
                $file_name  = "";
                # ------------------------
                if(!empty($request->document_file_revision)) {
                    $file_content   = file_get_contents($request->document_file_revision->getRealPath());
                    $file_url       = $request->document_url_incoming;
                    # ------------------------
                    $file_name      = "Document_Revision_" . $request->incoming_transmittal_detail_id . "." . $request->document_file_revision->getClientOriginalExtension();
                    # ------------------------
                    Storage::disk("uploads")->put($file_url . $file_name, $file_content);
                    # ------------------------
                    DB::table("approval")
                        ->where("approval_id", $idApproval)
                        ->update([
                            "document_url"=>$file_url,
                            "document_file"=>$file_name
                        ]);

                    DB::table("incoming_transmittal_detail")
                        ->where("incoming_transmittal_detail_id", $request->incoming_transmittal_detail_id)
                        ->update([
                            "document_file_revision"=>$file_name
                        ]);
                }
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("UPDATE APPROVALS DOCUMENT (" . $request->idData . ")", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "UPDATE APPROVALS DOCUMENT FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }

    
}
