<?php

namespace App\model\Migration;

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

class MigrationModel extends Model
{
    protected $table        = "document";
    protected $primaryKey   = "document_id";

    public function __construct() {
        $this->logModel     = new LogModel;   
    }

    public function getCollections()
    {
        try {
            $query  = DB::table($this->table)
                ->select(
                    "$this->table.document_id",
                    "$this->table.document_no",
                    "$this->table.document_title",
                    "b.name as vendor_name",
                    "d.project_name",
                    "$this->table.status",
                    DB::Raw("concat(e.name,' - ', f.name) as issue_status"),
                    "e.name as document_status",
                    db::Raw("(CASE $this->table.status WHEN 99 THEN 'Stored' END) AS status_code")
                )
                ->leftjoin("ref_vendor as b", "$this->table.vendor_id", "b.vendor_id")
                ->leftjoin("project as d", "$this->table.project_id", "d.project_id")
                ->leftjoin("ref_document_status as e", "$this->table.document_status_id", "e.document_status_id")
                ->leftjoin("ref_issue_status as f", "$this->table.issue_status_id", "f.issue_status_id")
                ->where("$this->table.status", 99)
                ->orderBy("$this->table.document_id", "DESC");

          
            if (session()->has("SES_SEARCH_MIGRATION_NO") != "") {
                $query->where("$this->table.document_no", "LIKE", "%" . session()->get("SES_SEARCH_MIGRATION_NO") . "%");
            }

            if (session()->has("SES_SEARCH_MIGRATION_TITLE") != "") {
                $query->where("$this->table.document_title", "LIKE", "%" . session()->get("SES_SEARCH_MIGRATION_TITLE") . "%");
            }

            $result = $query->paginate(PAGINATION);

            return array("status" => true, "data" => $result);
        } catch (\Exception $e) {
            return array("status" => false, "data" => []);
        }
    }

    public function checkDuplicateDocumentNo($id)
    {
        $query  = DB::table("document as a")
            ->select("a.document_id", "a.document_no")
            ->where("a.document_no", $id)
            ->first();

        return $query;
    }

    public function createTempMigration($request)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            // DELETE DOCUMENT TEMP TABLE
            DB::table('document_migration_temp')->where('user_id',  Auth::user()->id)->delete();
            # ------------------------
            if ($request->hasFile('upload_file')) {
                $ref_issue_status   = DB::table('ref_issue_status')->select('name')->where('issue_status_id', $request->issue_status_id)->first();
                
                Excel::load($request->file('upload_file')->getRealPath(), function ($reader) use ($request, $ref_issue_status) {

                    // GET CONFIG
                    $qCon = DB::table("sys_config")
                            ->select("*")
                            ->first();

                    foreach ($reader->toarray() as $key => $row) {

                        if ($row['document_no'] != "" && $row['document_no'] != null) {
                            $qDoc = $this->checkDuplicateDocumentNo($row['document_no']);
                            $ref_document_status   = DB::table('ref_document_status')->select('document_status_id')->where('name', $row['revision_no'])->first();
                            $revision_no    = $ref_document_status ? $ref_document_status->document_status_id : '';

                            if ($qDoc == null) {
                                $status = 1;
                                $note = 'New document';
                            } else {
                                $status = 2;
                                $note = 'Documents already exist';
                            }
                            $document_no = $row['document_no'].'_'.$ref_issue_status->name.'_'.$row['revision_no'];
                            DB::table("document_migration_temp")
                                ->insert([
                                    "document_no" => $document_no,
                                    "document_title" => $row['document_title'],
                                    "project_id" => $request->project_id,
                                    "vendor_id" => $request->vendor_id,
                                    "issue_status_id" => $request->issue_status_id,
                                    "revision_no" => $row['revision_no'],
                                    "note" => $note,
                                    "status" => $status,
                                    "user_id" => Auth::user()->id
                                ]);
                        }
                    }
                });
            }
            /* ----------
             Logs
            ----------------------- */
            $this->logModel->createLog("UPLOAD DOCUMENT MIGRATION TO TEMP TABLE", Auth::user()->id, $request);
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
            $id    = $this->logModel->createError($e->getMessage(), "UPLOAD DOCUMENT MIGRATION TO TEMP TABLE FAILED", "");
            # ---------------
            return array("status" => false, "id" => 0);
        }
    }

    public function getDocumentViewTemp($id)
    {
        $kode = base64_decode($id);
        list($project_id, $vendor_id, $issue_status, $user_id, $date) = explode("|", $kode);

        $query  = DB::table("document_migration_temp")
            ->select('*')
            ->where("project_id", $project_id)
            ->where("vendor_id", $vendor_id)
            ->where("issue_status_id", $issue_status)
            ->where("user_id", Auth::user()->id)
            ->orderBy("status", "DESC")
            ->get();

        return $query;
    }

    public function getDocumentTempIsReady($id)
    {
        $kode = base64_decode($id);
        list($project_id, $vendor_id, $issue_status, $user_id, $date) = explode("|", $kode);

        $query  = DB::table("document_migration_temp")
            ->select('document_migration_temp.*', 'ref_document_status.document_status_id as status_document')
            ->leftjoin("ref_document_status", "ref_document_status.name", "=", "document_migration_temp.revision_no")
            ->where("project_id", $project_id)
            ->where("vendor_id", $vendor_id)
            ->where("document_migration_temp.issue_status_id", $issue_status)
            ->where("document_migration_temp.status", 1)
            ->where("user_id", Auth::user()->id)
            ->get();

        return $query;
    }

    public function getDocumentStatus($id)
    {
        $kode = base64_decode($id);
        list($project_id, $vendor_id, $issue_status, $user_id, $date) = explode("|", $kode);

        $query  = DB::table("document_migration_temp")
                    ->select("ref_document_status.document_status_id")
                    ->leftjoin("ref_document_status", "ref_document_status.name", "=", "document_migration_temp.revision_no")
                    ->where("project_id", $project_id)
                    ->where("vendor_id", $vendor_id)
                    ->where("document_migration_temp.issue_status_id", $issue_status)
                    ->where("document_migration_temp.status", 1)
                    ->where("user_id", Auth::user()->id)
                    ->get();

        return $query;
    }

    public function getIssueStatus($id)
    {
        $query  = DB::table("ref_issue_status")
            ->select('*')
            ->where("issue_status_id", $id)
            ->first();

        return $query;
    }

    public function createUploadDocument($request)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            # ------------------------
            $qTemp = $this->getDocumentTempIsReady($request->id);
            // dd($qTemp);

            foreach ($qTemp as $row) {
                // NEW DOCUMENT
                $docId         = DB::table("document")
                ->insertGetId([
                    "document_no" => $row->document_no,
                    "document_title" => $row->document_title,
                    "document_description" => "",
                    "original_document_no"=> $row->original_document_no,
                    "project_id" => $row->project_id,
                    "vendor_id" => $row->vendor_id,
                    "issue_status_id" => $row->issue_status_id,
                    "document_status_id" => $row->status_document,
                    "status" => 99, // STORED
                    "ref_no" => "",
                    "incoming_transmittal_detail_id" => 0,
                    "document_type_id"=> 1,
                    "created_by" => Auth::user()->id,
                    "created_at" => Carbon::now()->toDateTimeString()
                ]);

                 /* ----------
                Upload File
                ----------------------- */
                $doc_name = 'document_migration_' . $row->id;
                if ($request->$doc_name) {
                    $default_file   = $request->$doc_name;
                    $file_url       = DOCUMENT_DIR . '/' . $docId . "/MIGRATION/";
                    # ------------------------
                    $file_content   = file_get_contents($default_file->getRealPath());
                    $file           = $request->file($doc_name)->getClientOriginalName();
                    $file_name      = pathinfo($file,PATHINFO_FILENAME);
                    $file_name      = $file_name . "_" . date("YmdHis") . "." . $default_file->getClientOriginalExtension();
                    # ------------------------
                    Storage::disk("uploads")->put($file_url . $file_name, $file_content);
    
                    // UPDATE 
                    DB::table("document")
                    ->where("document_id", $docId)
                    ->update([
                        "url_file"=>$file_url,
                        "file_migration"=>$file_name,
                    ]);
                }

            }
            /* ----------
             Logs
            ----------------------- */
            $this->logModel->createLog("UPLOAD FILE DOCUMENT MIGRATION", Auth::user()->id, $request);
            # ------------------------

            // DELETE DOCUMENT TEMP TABLE
            DB::table('document_migration_temp')->where('user_id',  Auth::user()->id)->delete();

            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            throw $e;
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "UPLOAD FILE DOCUMENT MIGRATION FAILED", "");
            # ---------------
            return array("status" => false, "id" => 0);
        }
    }

    public function getDataMigrationById($id)
    {
        $query  = DB::table("document")
            ->select('*')
            ->where("document_id", $id)
            ->first();

        return $query;
    }

    public function deleteDocument($id)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            $kode                   = decodedData($id);

            $qData = $this->getDataMigrationById($kode);

            // DELETE FILE
            $path_old = public_path('uploads/') . $qData->url_file;
            $filename_old = $qData->file_migration;
            File::delete($path_old . $filename_old);

            // DELETE DATA
            DB::table('document')->where('document_id', $kode)->delete();
            /* ----------
             Logs
            ----------------------- */
            $this->logModel->createLog("DELETE FILE DOCUMENT MIGRATION", Auth::user()->id, $id);
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
            $id    = $this->logModel->createError($e->getMessage(), "DELETE FILE DOCUMENT MIGRATION FAILED", "");
            # ---------------
            return array("status" => false, "id" => 0);
        }
    }

}
