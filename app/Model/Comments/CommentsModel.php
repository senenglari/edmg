<?php

namespace App\Model\Comments;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use Storage;
use File;
use DataTables;
use Mail;
use Zipper;
use ZipArchive;
use App\Mail\OutgoingMail;
use Carbon\Carbon;
use App\Model\Sys\LogModel;
use App\Model\Sys\SysModel;

class CommentsModel extends Model
{
    protected $table        = "comment";
    protected $primaryKey   = "comment_id";

    public function __construct()
    {
        $this->logModel     = new LogModel;
        $this->sysModel     = new SysModel;
    }

public function getCollections()
{
    $query = DB::table($this->table)
        ->select(
            "$this->table.comment_id",
            "$this->table.assignment_id",
            "$this->table.user_id",
            "$this->table.role",
            "$this->table.status",
            "y.document_no",
            "y.document_title",
            "y.issue_status_id",
            "y.note_backdoor",
            "a.name as document_type_name",
            "b.name as vendor_name",
            "c.name as area_name",
            "d.project_name",
            "f.name as issue_status",
            DB::RAW("DATE_FORMAT(y.deadline, '%d/%m/%Y') AS t_end_date")
        )

        ->join("assignment as z", "$this->table.assignment_id", "z.assignment_id")

        ->join("document as y", function ($join) {
            $join->on("z.document_id", "y.document_id");
            $join->on("y.incoming_transmittal_detail_id", "z.incoming_transmittal_detail_id");
        })

        ->leftjoin("incoming_transmittal_detail", "z.incoming_transmittal_detail_id", "incoming_transmittal_detail.incoming_transmittal_detail_id")
        ->leftjoin("incoming_transmittal", "incoming_transmittal_detail.incoming_transmittal_id", "incoming_transmittal.incoming_transmittal_id")

        ->leftjoin("ref_document_type as a", "y.document_type_id", "a.document_type_id")
        ->leftjoin("ref_vendor as b", "y.vendor_id", "b.vendor_id")
        ->leftjoin("ref_area as c", "y.area_id", "c.area_id")
        ->leftjoin("project as d", "y.project_id", "d.project_id")
        ->leftjoin("ref_issue_status as f", "y.issue_status_id", "f.issue_status_id")

        ->where("$this->table.user_id", Auth::user()->id)

        ->where(function($q){

            $q->where("comment.role","REVIEWER")

            ->orWhere(function($q2){

                $q2->where("comment.role","APPROVER")
                   ->whereRaw("
                        NOT EXISTS (
                            SELECT 1
                            FROM comment c2
                            WHERE c2.assignment_id = comment.assignment_id
                            AND c2.role = 'REVIEWER'
                            AND c2.status != 2
                        )
                   ");

            });

        })

        ->where("comment.status", 1)
        ->whereIn("y.status", [2,3])
        ->where("comment.status_nonaktif", "!=", 1)
        ->where("incoming_transmittal.status", "!=", 3)

        ->orderBy("comment.comment_id", "ASC");

    $data = $query->paginate(10);

    return [
        "data" => $data
    ];
}
    public function getHeader($id)
    {
        $query  = DB::table($this->table)
                        ->select(
                            "$this->table.*",
                            "d.project_name",
                            "y.document_no",
                            "y.document_title",
                            "y.issue_status_id as issue_dokumen",
                            "a.name as document_type_name",
                            "b.name as vendor_name",
                            "c.name as area_name",
                            "d.project_name",
                            "f.name as issue_status",
                            "x.document_url",
                            "x.document_file",
                            "x.document_crs",
                            "z.document_id",
                            "y.incoming_transmittal_detail_id",
                            "w.incoming_no",
                            "w.incoming_transmittal_id",
                            "y.approved_design_by",
                            "e.name as document_status_id",
                            "y.issue_status_id"
                        )
                        ->join("assignment as z", "$this->table.assignment_id", "z.assignment_id")
                        ->join("document as y", "z.document_id", "y.document_id")
                        ->join("incoming_transmittal_detail as x", "x.incoming_transmittal_detail_id", "y.incoming_transmittal_detail_id")
                        ->join("incoming_transmittal as w", "w.incoming_transmittal_id", "x.incoming_transmittal_id")
                        ->leftjoin("ref_document_type as a", "y.document_type_id", "a.document_type_id")
                        ->leftjoin("ref_vendor as b", "y.vendor_id", "b.vendor_id")
                        ->leftjoin("ref_area as c", "y.area_id", "c.area_id")
                        ->leftjoin("project as d", "y.project_id", "d.project_id")
                        ->leftjoin("ref_issue_status as f", "y.issue_status_id", "f.issue_status_id")
                        ->leftjoin("ref_document_status as e", "y.document_status_id", "e.document_status_id")
                        ->where("$this->table.comment_id", $id)
                        ->first();

        return $query;
    }

    public function getDetail($id)
    {
        $query  = DB::table($this->table)
                        ->select(
                            "$this->table.*",
                            "sys_users.name as user_name",
                            db::Raw("DATE_FORMAT($this->table.updated_at, '%d/%m/%Y %H:%i:%s') as tanggal_log"),
                            "ref_return_status.name AS return_code"
                        )
                        ->leftjoin("sys_users", "sys_users.id", "=", "$this->table.user_id")
                        ->leftjoin("ref_return_status", "$this->table.return_status_id", "ref_return_status.return_status_id")
                        ->where("$this->table.assignment_id", $id)
                        ->get();

        return $query;
    }

    public function getMaxComment($id)
    {
        $query  = DB::table($this->table)
                        ->select("order_no")
                        ->where("$this->table.assignment_id", $id)
                        ->orderBy("order_no", "DESC")
                        ->first();

        return $query;
    }

    public function getCommentDetailUser($assignment_id, $user_id)
    {
        $query  = DB::table($this->table)
                        ->select("*")
                        ->where("$this->table.assignment_id", $assignment_id)
                        ->where("$this->table.user_id", $user_id)
                        ->orderBy("order_no", "ASC")
                        ->first();

        return $query;
    }





public function saveComments_oldd($request)
{
    DB::beginTransaction();

    try {

        if ($request->status_approval == "APPROVER") {

            DB::table($this->table)
                ->where($this->primaryKey, $request->idData)
                ->update([
                    "remark" => $request->remark,
                    "issue_status_id" => $request->issue_status_id,
                    "return_status_id" => $request->return_status_id,
                    "status" => 2,
                    "updated_by" => Auth::id(),
                    "updated_at" => now()
                ]);

            DB::table("incoming_transmittal_detail")
                ->where("incoming_transmittal_detail_id", $request->incoming_transmittal_detail_id)
                ->update([
                    "issue_status_id" => $request->issue_status_id
                ]);

            /* ======================================================
               WORKFLOW DOCUMENT STATUS
               ====================================================== */
//dd($request->return_status_id);
            if ($request->return_status_id == RETURN_REJECT) {

                $rejectStatus = getNextStatusReject($request->issue_status_id);

                DB::table("document")
                    ->where("document_id", $request->document_id)
                    ->update([
                        "issue_status_id" => $rejectStatus,
                        "status" => 2
                    ]);

                /* ======================================================
                   AUTO CREATE OUTGOING TRANSMITTAL DRAFT
                   ====================================================== */

                $doc = DB::table("document")
                    ->where("document_id", $request->document_id)
                    ->first();

                if ($doc) {

                    $transmittalId = DB::table("outgoing_transmittal")->insertGetId([
                        "project_id" => $doc->project_id,
                        "vendor_id" => $doc->vendor_id,
                        "outgoing_no" => "AUTO-REV-" . date("YmdHis"),
                        "subject" => "REVISION REQUIRED - " . $doc->document_no,
                        "content" => "Please upload revised document based on review comments",
                        "status_code" => 0, // draft
                        "created_by" => Auth::id(),
                        "created_at" => now()
                    ]);

                    DB::table("outgoing_transmittal_detail")->insert([
                        "outgoing_transmittal_id" => $transmittalId,
                        "incoming_transmittal_detail_id" => $request->incoming_transmittal_detail_id,
                        "created_at" => now()
                    ]);

                }

            } else {

                $approveStatus = getNextStatusApprove($request->issue_status_id);

                if ($approveStatus == STATUS_DONE) {

                    DB::table("document")
                        ->where("document_id", $request->document_id)
                        ->update([
                            "issue_status_id" => $approveStatus,
                            "status" => 6
                        ]);

                } else {

                    DB::table("document")
                        ->where("document_id", $request->document_id)
                        ->update([
                            "issue_status_id" => $approveStatus,
                            "status" => 3
                        ]);

                }

            }

        } else {

            DB::table($this->table)
                ->where($this->primaryKey, $request->idData)
                ->update([
                    "remark" => $request->remark,
                    "return_status_id" => $request->return_status_id,
                    "status" => 2,
                    "updated_by" => Auth::id(),
                    "updated_at" => now()
                ]);

        }

        /* ======================================================
           LOG
           ====================================================== */

        $this->logModel->createLog(
            "UPDATE COMMENTS (" . $request->idData . ")",
            Auth::id(),
            $request
        );

        DB::commit();

        return ["status" => true, "id" => 0];

    } catch (\Exception $e) {

        DB::rollback();

        $this->logModel->createError(
            $e->getMessage(),
            "UPDATE COMMENTS FAILED",
            ""
        );

        return ["status" => false, "id" => 0];
    }
}





public function saveComments($request)
{

    DB::beginTransaction();

    try {

        if ($request->status_approval == "APPROVER") {

            /* ===============================
               UPDATE COMMENT
            =============================== */

            DB::table($this->table)
                ->where($this->primaryKey, $request->idData)
                ->update([
                    "remark" => $request->remark,
                    "issue_status_id" => $request->issue_status_id,
                    "return_status_id" => $request->return_status_id,
                    "status" => 2,
                    "updated_by" => Auth::id(),
                    "updated_at" => now()
                ]);

            DB::table("incoming_transmittal_detail")
                ->where("incoming_transmittal_detail_id", $request->incoming_transmittal_detail_id)
                ->update([
                    "issue_status_id" => $request->issue_status_id
                ]);


            /* ===============================
               CHECK REVISION FLOW
            =============================== */

            // Update document status sesuai pilihan user
            DB::table("document")
                ->where("document_id", $request->document_id)
                ->update([
                    "issue_status_id" => $request->issue_status_id,
                    // Status dokumen tetap, tidak diubah ke 2/3/6 secara paksa
                ]);

            // Jika perlu create outgoing transmittal, tetap gunakan status yang dipilih user
            $doc = DB::table("document")
                ->where("document_id", $request->document_id)
                ->first();

            // Determine if next status is external (IFC/IFA/IFR/IFI) or internal (RE-xxx)
            $externalStatuses = [STATUS_IFC, STATUS_IFA, STATUS_IFR, STATUS_IFI]; // 1, 3, 5, 7
            $isExternalFlow = in_array((int)$request->issue_status_id, $externalStatuses);

            if ($doc && ($request->return_status_id == RETURN_COMMENT || $request->return_status_id == RETURN_REJECT)) {

                if ($isExternalFlow) {
                    /* ===============================
                       EXTERNAL FLOW: Create Outgoing Company draft (REV-xxx pattern)
                       Flow: Outgoing Company → admin sends → vendor responds → new Incoming → approve → auto assignment
                    =============================== */

                    // 1. Create outgoing company transmittal draft
                    $transmittalId = DB::table("outgoing_transmittal")->insertGetId([
                        "outgoing_no" => "REV-" . date("YmdHis") . "-" . $request->document_id,
                        "sender_date" => NULL,
                        "vendor_id" => $doc->vendor_id,
                        "project_id" => $doc->project_id,
                        "email_address" => "",
                        "cc_email_address" => "",
                        "subject" => "NEW CYCLE REQUIRED - " . $doc->document_no,
                        "content" => "Document requires new external review cycle",
                        "status_email" => 0,
                        "created_by" => Auth::id(),
                        "created_at" => now()
                    ]);
                    DB::table("outgoing_transmittal_detail")->insert([
                        "outgoing_transmittal_id" => $transmittalId,
                        "incoming_transmittal_detail_id" => $request->incoming_transmittal_detail_id,
                        "return_status_id"   => $request->return_status_id,
                        "issue_status_id"    => $request->issue_status_id,
                        "document_status_id" => $request->document_status_id,
                        "document_url" => null,
                        "document_file" => null,
                        "document_file_2" => null,
                        "document_crs" => null
                    ]);

                } else {
                    /* ===============================
                       INTERNAL FLOW: Keep current behavior (AUTO-REV-xxx)
                    =============================== */

                    $transmittalId = DB::table("outgoing_transmittal")->insertGetId([
                        "outgoing_no" => "AUTO-REV-" . date("YmdHis"),
                        "sender_date" => NULL,
                        "vendor_id" => $doc->vendor_id,
                        "project_id" => $doc->project_id,
                        "email_address" => "",
                        "cc_email_address" => "",
                        "subject" => "REVISION REQUIRED - " . $doc->document_no,
                        "content" => "Please upload revised document",
                        "status_email" => 0,
                        "created_by" => Auth::id(),
                        "created_at" => now()
                    ]);
                    DB::table("outgoing_transmittal_detail")->insert([
                        "outgoing_transmittal_id" => $transmittalId,
                        "incoming_transmittal_detail_id" => $request->incoming_transmittal_detail_id,
                        "return_status_id"   => $request->return_status_id,
                        "issue_status_id"    => $request->issue_status_id,
                        "document_status_id" => $request->document_status_id,
                        "document_url" => null,
                        "document_file" => null,
                        "document_file_2" => null,
                        "document_crs" => null
                    ]);
                }
            }

        } else {

            /* ===============================
               NON APPROVER COMMENT
            =============================== */

            DB::table($this->table)
                ->where($this->primaryKey, $request->idData)
                ->update([
                    "remark" => $request->remark,
                    "return_status_id" => $request->return_status_id,
                    "status" => 2,
                    "updated_by" => Auth::id(),
                    "updated_at" => now()
                ]);

        }


        /* ===============================
           LOG
        =============================== */

        $this->logModel->createLog(
            "UPDATE COMMENTS (" . $request->idData . ")",
            Auth::id(),
            $request
        );


        DB::commit();

        return ["status" => true, "id" => 0];

    } catch (\Exception $e) {

        DB::rollback();

        $this->logModel->createError(
            $e->getMessage(),
            "UPDATE COMMENTS FAILED",
            ""
        );

        return ["status" => false, "id" => 0];
    }
}





    public function download_versi_windows($user_id) {
        try {
            $query      = DB::select("SELECT  document.document_id, incoming_transmittal_detail.document_url, incoming_transmittal_detail.document_file, incoming_transmittal_detail.document_crs
                                      FROM    incoming_transmittal_detail INNER JOIN document ON incoming_transmittal_detail.document_id = document.document_id
                                      INNER   JOIN assignment ON incoming_transmittal_detail.incoming_transmittal_detail_id = assignment.incoming_transmittal_detail_id
                                      INNER   JOIN comment ON assignment.assignment_id = comment.assignment_id
                                      WHERE   document.status = 2 AND comment.status = 1 AND comment.user_id = '$user_id'");

            $zip        = new ZipArchive;
            $fileName   = public_path('uploads') . "\download\ATTACHMENT_" . date("Ymd") . "_" . date("His") . ".zip";
            $directory  = public_path('uploads') . DOCUMENT_DIR_COMMENT . "/";
            
            foreach($query as $row) {
                if($zip->open($fileName, ZipArchive::CREATE) === TRUE){
                    $location   = public_path('uploads') . str_replace("//", "/", $row->document_url);
                    $location   = str_replace("/", "\\", $location);
                    $files      = File::files($location);
                    
                    foreach ($files as $key => $value) {
                        $relativeNameInZipFile = basename($value);

                        if(($relativeNameInZipFile == $row->document_file) || ($relativeNameInZipFile == $row->document_crs)) {
                            $zip->addFile($value, $relativeNameInZipFile);
                        }
                    }
                }
            }

            $zip->close();
        
            return response()->download($fileName);
        } catch (\Exception $e) {
            throw $e;
            return array("status"=>false, "id"=>0);
        }
    }

    public function download($user_id) {
        try {
            $query      = DB::select("SELECT  document.document_id, incoming_transmittal_detail.document_url, incoming_transmittal_detail.document_file, incoming_transmittal_detail.document_crs
                                      FROM    incoming_transmittal_detail INNER JOIN document ON incoming_transmittal_detail.document_id = document.document_id
                                      INNER   JOIN assignment ON incoming_transmittal_detail.incoming_transmittal_detail_id = assignment.incoming_transmittal_detail_id
                                      INNER   JOIN comment ON assignment.assignment_id = comment.assignment_id
                                      WHERE   document.status = 2 AND comment.status = 1 AND comment.user_id = '$user_id'");

            $zip        = new ZipArchive;
            $compress   = false;
            $fileName   = public_path('uploads') . "/download/ATTACHMENT_" . date("Ymd") . "_" . date("His") . ".zip";
            $directory  = public_path('uploads') . DOCUMENT_DIR_COMMENT . "/";
            
            foreach($query as $row) {
                if($zip->open($fileName, ZipArchive::CREATE) === TRUE){
                    $location   = public_path('uploads') . $row->document_url;
                    $files      = File::files($location);
                    
                    foreach ($files as $key => $value) {
                        $relativeNameInZipFile = basename($value);

                        if(($relativeNameInZipFile == $row->document_file) || ($relativeNameInZipFile == $row->document_crs)) {
                            $compress = $zip->addFile($value, $relativeNameInZipFile);
                        }
                    }
                }
            }

            if($compress) {
                $zip->close();    
            }            
            return response()->download($fileName);
        } catch (\Exception $e) {
            throw $e;
            return array("status"=>false, "id"=>0);
        }
    }

    public function download_attachment($id) {
        try {
            $query      = DB::select("SELECT  document.document_id, incoming_transmittal_detail.document_url, incoming_transmittal_detail.document_file, incoming_transmittal_detail.document_crs
                                      FROM    incoming_transmittal_detail INNER JOIN document ON incoming_transmittal_detail.document_id = document.document_id
                                      INNER   JOIN assignment ON incoming_transmittal_detail.incoming_transmittal_detail_id = assignment.incoming_transmittal_detail_id
                                      INNER   JOIN comment ON assignment.assignment_id = comment.assignment_id
                                      WHERE   document.status = 2 AND comment.status = 1 AND comment.comment_id = '$id'");

            $zip        = new ZipArchive;
            $compress   = false;
            $fileName   = public_path('uploads') . "/download/ATTACHMENT_FILES_" . date("Ymd") . "_" . date("His") . ".zip";
            $directory  = public_path('uploads') . DOCUMENT_DIR_COMMENT . "/";
            
            foreach($query as $row) {
                if($zip->open($fileName, ZipArchive::CREATE) === TRUE){
                    $location   = public_path('uploads') . $row->document_url;
                    $files      = File::files($location);
                    
                    foreach ($files as $key => $value) {
                        $relativeNameInZipFile = basename($value);

                        if(($relativeNameInZipFile == $row->document_file) || ($relativeNameInZipFile == $row->document_crs)) {
                            $compress = $zip->addFile($value, $relativeNameInZipFile);
                        }
                    }
                }
            }

            if($compress) {
                $zip->close();    
            }            
        
            return response()->download($fileName);
        } catch (\Exception $e) {
            throw $e;
            return array("status"=>false, "id"=>0);
        }
    }

    public function getCollectionsClient()
    {
        try {
            $query  = DB::table($this->table)
                            ->select(
                                "$this->table.*",
                                "d.project_name",
                                "y.document_no",
                                "y.document_title",
                                "a.name as document_type_name",
                                "b.name as vendor_name",
                                "c.name as area_name",
                                "d.project_name",
                                "f.name as issue_status",
                                "h.subject",
                                DB::RAW("DATE_FORMAT(y.deadline, '%d/%m/%Y') AS t_end_date"),
                                db::Raw("(CASE $this->table.status_download WHEN 0 THEN 'Not yet' WHEN 1 THEN 'Already' END) AS status_download")
                            )
                            ->join("assignment as z", "$this->table.assignment_id", "z.assignment_id")
                            ->join("document as y", "z.document_id", "y.document_id")
                            ->leftjoin("ref_document_type as a", "y.document_type_id", "a.document_type_id")
                            ->leftjoin("ref_vendor as b", "y.vendor_id", "b.vendor_id")
                            ->leftjoin("ref_area as c", "y.area_id", "c.area_id")
                            ->leftjoin("project as d", "y.project_id", "d.project_id")
                            ->leftjoin("ref_issue_status as f", "y.issue_status_id", "f.issue_status_id")
                            ->leftjoin("incoming_transmittal_detail as g", "g.incoming_transmittal_detail_id", "z.incoming_transmittal_detail_id")
                            ->leftjoin("incoming_transmittal as h", "h.incoming_transmittal_id", "g.incoming_transmittal_id")
                            ->where("$this->table.user_id", Auth::user()->id)
                            ->whereIn("$this->table.status", [1,2])
                            ->where("y.status", 2)
                            // ->whereRaw("(comment.status = 1 OR (comment.status < 2 AND comment.role='APPROVER'))")
                            ->orderBy("h.subject", "ASC");


            if (session()->has("SES_SEARCH_CLIENT_TRANSMITTAL_PROJECT_NAME") != "") {
                $query->where("d.project_name", "LIKE", "%" . session()->get("SES_SEARCH_CLIENT_TRANSMITTAL_PROJECT_NAME") . "%");
            }

            if (session()->has("SES_SEARCH_CLIENT_TRANSMITTAL_NO") != "") {
                $query->where("y.document_no", "LIKE", "%" . session()->get("SES_SEARCH_CLIENT_TRANSMITTAL_NO") . "%");
            }

            if (session()->has("SES_SEARCH_CLIENT_TRANSMITTAL_TITLE") != "") {
                $query->where("y.document_title", "LIKE", "%" . session()->get("SES_SEARCH_CLIENT_TRANSMITTAL_TITLE") . "%");
            }

            if (session()->has("SES_SEARCH_CLIENT_TRANSMITTAL_TYPE")) {
                if (session()->get("SES_SEARCH_CLIENT_TRANSMITTAL_TYPE") != "0") {
                    $query->where("y.document_type_id", session()->get("SES_SEARCH_CLIENT_TRANSMITTAL_TYPE"));
                }
            }

            if (session()->has("SES_SEARCH_CLIENT_TRANSMITTAL_VENDOR")) {
                if (session()->get("SES_SEARCH_CLIENT_TRANSMITTAL_VENDOR") != "0") {
                    $query->where("y.vendor_id", session()->get("SES_SEARCH_CLIENT_TRANSMITTAL_VENDOR"));
                }
            }

            if (session()->has("SES_SEARCH_CLIENT_TRANSMITTAL_AREA")) {
                if (session()->get("SES_SEARCH_CLIENT_TRANSMITTAL_AREA") != "0") {
                    $query->where("y.area_id", session()->get("SES_SEARCH_CLIENT_TRANSMITTAL_AREA"));
                }
            }
            if (session()->has("SES_SEARCH_CLIENT_TRANSMITTAL_DOWNLOAD")) {
                if (session()->get("SES_SEARCH_CLIENT_TRANSMITTAL_DOWNLOAD") != "0") {
                    if (session()->get("SES_SEARCH_CLIENT_TRANSMITTAL_DOWNLOAD") == 1) {
                        $status = session()->get("SES_SEARCH_CLIENT_TRANSMITTAL_DOWNLOAD");
                    } elseif (session()->get("SES_SEARCH_CLIENT_TRANSMITTAL_DOWNLOAD") == 99) {
                        $status = 0;
                    }
                    $query->where("$this->table.status_download", $status);
                }
            }

            $result = $query->paginate(PAGINATION);

            return array("status" => true, "data" => $result);
        } catch (\Exception $e) {
            throw $e;
            return array("status" => false, "data" => []);
        }
    }

    public function downloadClient($user_id) {
        try {
            $query      = DB::select("SELECT  document.ref_no, document.document_id, incoming_transmittal_detail.document_url, incoming_transmittal_detail.document_file, incoming_transmittal_detail.document_crs
                                      FROM    incoming_transmittal_detail INNER JOIN document ON incoming_transmittal_detail.document_id = document.document_id
                                      INNER   JOIN assignment ON incoming_transmittal_detail.incoming_transmittal_detail_id = assignment.incoming_transmittal_detail_id
                                      INNER   JOIN comment ON assignment.assignment_id = comment.assignment_id
                                      WHERE   document.status = 2 AND comment.status = 1 AND comment.status_download = 0 AND comment.user_id = '$user_id'");

            if(count($query) > 0) {
                $zip        = new ZipArchive;
                $compress   = false;
                $fileName   = public_path('uploads') . "/download/ATTACHMENT_" . date("Ymd") . "_" . date("His") . ".zip";
                $directory  = public_path('uploads') . DOCUMENT_DIR_COMMENT . "/";
                
                foreach($query as $row) {
                    if($zip->open($fileName, ZipArchive::CREATE) === TRUE){
                        $location   = public_path('uploads') . $row->document_url;
                        $files      = File::files($location);
                        
                        foreach ($files as $key => $value) {
                            // $relativeNameInZipFile = basename($value);

                            // if(($relativeNameInZipFile == $row->document_file) || ($relativeNameInZipFile == $row->document_crs)) {
                            //     $compress = $zip->addFile($value, $relativeNameInZipFile);
                            // }
                            $relativeNameInZipFile = basename($value);
                            $pathName   = $value;
                            $oldName    = $relativeNameInZipFile;
                            // if($row->ref_no){
                            //     rename($location.$relativeNameInZipFile, $location.'/'.$row->ref_no.'.'.$value->getExtension());
                            //     $pathName   = $location.'/'.$row->ref_no.'.'.$value->getExtension();
                            //     $oldName    = $row->ref_no.'.'.$value->getExtension();
                            // }
                            if(($relativeNameInZipFile == $row->document_file) || ($relativeNameInZipFile == $row->document_crs)) {
                                $compress = $zip->addFile($pathName, $oldName);
                            }
                        }
                    }
                }

                if($compress) {
                    $zip->close();    
                }            
                 // UPDATE STATUS DOWNLOAD
                DB::table("comment as a")
                    ->join("assignment as b", "a.assignment_id", "b.assignment_id")
                    ->join("document as c", "b.document_id", "c.document_id")
                    ->where("c.status", 2)
                    ->where("a.user_id", $user_id)
                    ->where("a.status", 1)
                    ->where("a.status_download", 0)
                    ->update([
                        "a.status_download"=>1
                    ]);
                
                # ------------------------
                DB::commit();
                # ------------------------
            
                return response()->download($fileName);
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw $e;
            return array("status"=>false, "id"=>0);
        }
    }

    public function activateComment($assignment_id) {
        try {
            DB::table("assignment")->where("assignment_id", $assignment_id)->update(["status_nonaktif" => 0]);
            DB::table("comment")->where("assignment_id", $assignment_id)->update(["status_nonaktif" => 0]);
        } catch (\Exception $e) {
            return array("status"=>false, "id"=>0);
        }
    }

    public function inactivateComment($assignment_id) {
        try {
            DB::table("assignment")->where("assignment_id", $assignment_id)->update(["status_nonaktif" => 1]);
            DB::table("comment")->where("assignment_id", $assignment_id)->update(["status_nonaktif" => 1]);
        } catch (\Exception $e) {
            return array("status"=>false, "id"=>0);
        }
    }

    public function getCollectionsIdc()
    {
        try {
            $userid = Auth::user()->id;
            
            // \DB::enableQueryLog();
            $query_sp  = DB::statement("CALL sp_list_comment_idc()");
            $query     = DB::table("temp_list_reviewer")
                            ->select("*")
                            ->where("user_id", Auth::user()->id)
                            ->orderby("comment_id", "ASC");
                            
            // dd(DB::getQueryLog());
            if (session()->has("SES_SEARCH_COMMENTS_IDC_PROJECT_NAME") != "") {
                $query->where("project_name", "LIKE", "%" . session()->get("SES_SEARCH_COMMENTS_IDC_PROJECT_NAME") . "%");
            }

            if (session()->has("SES_SEARCH_COMMENTS_IDC_NO") != "") {
                $query->where("document_no", "LIKE", "%" . session()->get("SES_SEARCH_COMMENTS_IDC_NO") . "%");
            }

            if (session()->has("SES_SEARCH_COMMENTS_IDC_TITLE") != "") {
                $query->where("document_title", "LIKE", "%" . session()->get("SES_SEARCH_COMMENTS_IDC_TITLE") . "%");
            }

            if (session()->has("SES_SEARCH_COMMENTS_IDC_TYPE")) {
                if (session()->get("SES_SEARCH_COMMENTS_IDC_TYPE") != "0") {
                    $query->where("document_type_id", session()->get("SES_SEARCH_COMMENTS_IDC_TYPE"));
                }
            }

            if (session()->has("SES_SEARCH_COMMENTS_IDC_AREA")) {
                if (session()->get("SES_SEARCH_COMMENTS_IDC_AREA") != "0") {
                    $query->where("area_id", session()->get("SES_SEARCH_COMMENTS_IDC_AREA"));
                }
            }

            $result = $query->paginate(PAGINATION);

            
            return array("status" => true, "data" => $result);
        } catch (\Exception $e) {
            throw $e;
            return array("status" => false, "data" => []);
        }
    }
}
// C:\xampp\htdocs\document_management_system\public\uploads/comments/55/10_20220719115444.jpg