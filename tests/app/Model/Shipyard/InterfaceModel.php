<?php

namespace App\Model\Shipyard;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use Storage;
use File;
use Carbon\Carbon;
use App\Model\Sys\LogModel;

class InterfaceModel extends Model
{
    protected $table        = "interface_data";
    protected $primaryKey   = "interface_data_id";

    public function __construct()
    {
        $this->logModel     = new LogModel;
    }

    public function getCollections()
    {
        try {
            $query  = DB::table($this->table)
                ->select("$this->table.interface_data_id", "$this->table.folder_name", "status")
                ->where("status", 1)
                ->orderBy("$this->table.interface_data_id", "DESC");

            if (session()->has("SES_SEARCH_INTERFACE_FOLDER_NAME") != "") {
                $query->where("$this->table.folder_name", "LIKE", "%" . session()->get("SES_SEARCH_INTERFACE_FOLDER_NAME") . "%");
            }

            $result = $query->paginate(PAGINATION);

            return array("status" => true, "data" => $result);
        } catch (\Exception $e) {
            return array("status" => false, "data" => []);
        }
    }

    public function getFolder($id)
    {
        try {
            $query  = DB::table($this->table)
                ->select("*")
                ->where("$this->table.interface_data_id", $id)
                ->first();

            return array("status" => true, "data" => $query);
        } catch (\Exception $e) {
            return array("status" => false, "data" => []);
        }
    }

    public function getItemInterface($id)
    {
        $query      = DB::table("interface_data_detail")->select(
            "interface_data_detail.*",
            "ref_document_status.name AS document_status_name",
            "ref_issue_status.name AS issue_status_name",
            "interface_data_detail.status as status_detail"
        )
            ->leftjoin("interface_data", "interface_data.interface_data_id", "interface_data_detail.interface_data_id")
            ->leftjoin("interface_data_subfolder", "interface_data_subfolder.interface_data_subfolder_id", "interface_data_detail.interface_data_subfolder_id")
            ->leftJoin("ref_document_status", "interface_data_detail.document_status_id", "ref_document_status.document_status_id")
            ->leftJoin("ref_issue_status", "interface_data_detail.issue_status_id", "ref_issue_status.issue_status_id")
            ->where("interface_data_subfolder.interface_data_subfolder_id", $id)
            ->where("interface_data_detail.status", 1)
            ->orderBy("interface_data_detail.interface_data_detail_id")
            ->get();

        return $query;
    }

    public function getSubFolderItem($id)
    {
        $query      = DB::table("interface_data_subfolder")->select(
            "interface_data_subfolder.*",
            "sys_users.name AS name"
        )
            ->leftjoin("sys_users", "sys_users.id", "interface_data_subfolder.created_by")
            ->where("interface_data_subfolder.interface_data_subfolder_id", $id)
            ->where("interface_data_subfolder.status", 1)
            ->orderBy("interface_data_subfolder_id.interface_data_subfolder_id")
            ->get();

        return $query;
    }

    public function createData($request)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table($this->table)
                ->insert([
                    "folder_name" => $request->folder_name,
                    "status" => 1,
                    "created_by" => Auth::user()->id,
                    "created_at" => Carbon::now()->toDateTimeString()
                ]);

            $new_dir            = public_path("uploads") . "/interface//" . $request->folder_name;
            File::makeDirectory($new_dir, 0777, true, true);

            /* ----------
             Logs
            ----------------------- */
            $this->logModel->createLog("CREATE INTERFACE", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id   = $this->logModel->createError($e->getMessage(), "CREATE INTERFACE FAILED", "");
            # ---------------
            return array("status" => false, "id" => 0);
        }
    }

    public function attachItem($request)
    {
        // dd($request);
        DB::beginTransaction();
        # ------------------------
        try {
            /* ----------
             Upload File
            ----------------------- */

            $default_file   = $request->document_file;
            $file_url       = DOCUMENT_INTERFACE . '/' . $request->folder_name . "/" . $request->subfolder_name . "/";
            # ------------------------
            $file_content   = file_get_contents($request->document_file->getRealPath());
            $file           = $request->file('document_file')->getClientOriginalName();
            $file_name      = pathinfo($file, PATHINFO_FILENAME);
            $file_name      = $file_name  . "." . $default_file->getClientOriginalExtension();
            # ------------------------
            Storage::disk("uploads")->put($file_url . $file_name, $file_content);
            # ------------------------

            # ------------------------
            $id     = DB::table("interface_data_detail")
                ->insertGetId([
                    "interface_data_id" => $request->interface_data_id,
                    "interface_data_subfolder_id" => $request->interface_data_subfolder_id,
                    "document_no" => (!empty($request->document_no)) ? $request->document_no : "",
                    "document_title" => (!empty($request->document_title)) ? $request->document_title : "",
                    "document_url" => $file_url,
                    "document_file" => $file_name,
                    "issue_status_id" => $request->issue_status_id,
                    "document_status_id" => $request->document_status_id,
                    "remark" => $request->remark,
                    "status" => 1,
                    "created_by" => Auth::user()->id,
                    "created_at" => Carbon::now()->toDateTimeString()
                ]);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            throw $e;
            DB::rollback();
            # ------------------------
            $this->logModel->createError($e->getMessage(), "ATTACH DOCUMENT IN TABLE", "");
            # ------------------------
            return array("status" => false, "id" => 0, "message" => $e->getMessage());
        }
    }

    public function cekdocument_no($id)
    {
        $query  = DB::table("interface_data_detail")
            ->select("document_no")
            ->where("document_no", $id);
        $result = $query->get();

        return $result;
    }


    public function getItem()
    {
        $query      = DB::table("interface_data_detail")->select("interface_data_detail.*", "ref_document_status.name AS document_status_name", "ref_issue_status.name AS issue_status_name")
            ->leftJoin("ref_document_status", "interface_data_detail.document_status_id", "ref_document_status.document_status_id")
            ->leftJoin("ref_issue_status", "interface_data_detail.issue_status_id", "ref_issue_status.issue_status_id")
            ->where("interface_data_detail.created_by", Auth::user()->id)
            ->orderBy("interface_data_detail.interface_data_detail_id")
            ->get();

        return $query;
    }

    public function deleteItem($id)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table("interface_data_detail")
                ->where("interface_data_detail_id", $id)
                ->where("created_by", Auth::user()->id)
                ->update([
                    "status" => 0,
                    "deleted_by" => Auth::user()->id,
                    "deleted_at" => Carbon::now()->toDateTimeString()
                ]);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            # ------------------------
            $this->logModel->createError($e->getMessage(), "DELETE ITEM", "");
            # ------------------------
            return array("status" => false, "id" => 0);
        }
    }

    public function deleteSub($id)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table("interface_data_subfolder")
                ->where("interface_data_subfolder_id", $id)
                ->where("created_by", Auth::user()->id)
                ->update([
                    "status" => 0,
                    "deleted_by" => Auth::user()->id,
                    "deleted_at" => Carbon::now()->toDateTimeString()
                ]);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            # ------------------------
            $this->logModel->createError($e->getMessage(), "DELETE ITEM", "");
            # ------------------------
            return array("status" => false, "id" => 0);
        }
    }

    public function approveData($request)
    {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table($this->table)
                ->where("interface_data_id", $request->id)
                ->update([
                    "folder_name" => $request->folder_name,
                    "status" => $request->status,
                    "approved_by" => Auth::user()->id,
                    "approved_at" => Carbon::now()->toDateTimeString()
                ]);
            /* ----------
             Logs
            ----------------------- */
            $this->logModel->createLog("APPROVE INTERFACE", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {

            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id   = $this->logModel->createError($e->getMessage(), "APPROVE INTERFACE FAILED", "");
            # ---------------
            return array("status" => false, "id" => 0);
        }
    }

    public function getSubfolder($id)
    {
        $query      = DB::table("interface_data_subfolder")->select("interface_data_subfolder.*", "sys_users.name as name")
            ->leftjoin("interface_data", "interface_data.interface_data_id", "interface_data_subfolder.interface_data_id")
            ->leftjoin("sys_users", "sys_users.id", "interface_data_subfolder.created_by")
            ->where("interface_data.interface_data_id", $id)
            ->where("interface_data_subfolder.status", 1)
            ->orderBy("interface_data_subfolder.interface_data_subfolder_id")
            ->get();

        return $query;
    }

    public function getSubfolderSearch($id, $textSearch)
    {
        $query      = DB::table("interface_data_subfolder")
            ->select("interface_data_subfolder.*", "sys_users.name as name")
            ->leftjoin("interface_data", "interface_data.interface_data_id", "interface_data_subfolder.interface_data_id")
            ->leftjoin("sys_users", "sys_users.id", "interface_data_subfolder.created_by")
            ->where("interface_data.interface_data_id", $id)
            ->where("interface_data_subfolder.subfolder_name", "LIKE", "%" . $textSearch . "%")
            ->orderBy("interface_data_subfolder.interface_data_subfolder_id")
            ->get();

        return $query;
    }

    public function createSubfolder($request)
    {
        // dd($request);
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table("interface_data_subfolder")
                ->insert([
                    "subfolder_name" => $request->subfolder_name,
                    "interface_data_id" => $request->id,
                    "status" => 1,
                    "created_by" => Auth::user()->id,
                    "created_at" => Carbon::now()->toDateTimeString()
                ]);

            $new_dir            = public_path("uploads") . "/interface//" . $request->folder_name . "/" . $request->subfolder_name;
            File::makeDirectory($new_dir, 0777, true, true);

            /* ----------
             Logs
            ----------------------- */
            $this->logModel->createLog("CREATE SUBFOLDER INTERFACE", Auth::user()->id, $request);
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
            $id   = $this->logModel->createError($e->getMessage(), "CREATE INTERFACE FAILED", "");
            # ---------------
            return array("status" => false, "id" => 0);
        }
    }

    public function getDataSubFolder($id)
    {
        $query      = DB::table("interface_data_subfolder")->select("interface_data.*", "interface_data_subfolder.*")
            ->leftjoin("interface_data", "interface_data_subfolder.interface_data_id", "interface_data.interface_data_id")
            ->where("interface_data_subfolder.interface_data_subfolder_id", $id)
            ->first();

        return $query;
    }

    public function getItemSubFolder($id)
    {
        $kode   = base64_decode($id);
        list($idfolder, $idsubfolder) = explode("|", $kode);
        // dd($idfolder, $idsubfolder);
        $query      = DB::table("interface_data_detail")->select(
            "interface_data_detail.*",
            "interface_data_subfolder.*",
            "ref_document_status.name AS document_status_name",
            "ref_issue_status.name AS issue_status_name",
            "interface_data_detail.status as status_detail"
        )
            ->leftjoin("interface_data_subfolder", "interface_data_subfolder.interface_data_subfolder_id", "interface_data_detail.interface_data_subfolder_id")
            ->leftjoin("interface_data", "interface_data.interface_data_id", "interface_data_detail.interface_data_id")
            ->leftJoin("ref_document_status", "interface_data_detail.document_status_id", "ref_document_status.document_status_id")
            ->leftJoin("ref_issue_status", "interface_data_detail.issue_status_id", "ref_issue_status.issue_status_id")
            ->where("interface_data.interface_data_id", $idfolder)
            ->where("interface_data_subfolder.interface_data_subfolder_id", $idsubfolder)
            ->where("interface_data_detail.status", 1)
            ->orderBy("interface_data_detail.interface_data_detail_id");
        $result = $query->get();

        return $result;
    }

    public function getDataFolder($id)
    {
        $query      = DB::table("interface_data_subfolder")->select("interface_data.*", "interface_data_subfolder.*")
            ->leftjoin("interface_data", "interface_data.interface_data_id", "interface_data_subfolder.interface_data_id")
            ->where("interface_data.interface_data_id", $id)
            ->orderBy("interface_data.interface_data_id")
            ->get();

        return $query;
    }

    public function getDetailItemSubFolder($id)
    {

        $query      = DB::table("interface_data_detail")->select(
            "interface_data_detail.*",
            "interface_data_subfolder.*",
            "ref_document_status.name AS document_status_name",
            "ref_issue_status.name AS issue_status_name",
            "interface_data_detail.status as status_detail"
        )
            ->leftjoin("interface_data_subfolder", "interface_data_subfolder.interface_data_subfolder_id", "interface_data_detail.interface_data_subfolder_id")
            //    ->leftjoin(
            //     DB::raw("(select interface_data_subfolder_id, subfolder_name from interface_data_subfolder group by interface_data_subfolder_id, interface_data_id) b"),
            //         function ($JOIN) {
            //             $JOIN->ON('interface_data_detail.interface_data_subfolder_id', 'b.interface_data_subfolder_id');
            //         }
            //     )
            ->leftjoin("interface_data", "interface_data.interface_data_id", "interface_data_detail.interface_data_id")
            ->leftJoin("ref_document_status", "interface_data_detail.document_status_id", "ref_document_status.document_status_id")
            ->leftJoin("ref_issue_status", "interface_data_detail.issue_status_id", "ref_issue_status.issue_status_id")
            ->where("interface_data.interface_data_id", $id)
            ->orderBy("interface_data_detail.interface_data_detail_id")
            ->groupBy("interface_data_detail.interface_data_subfolder_id")
            ->groupBy("interface_data_detail.interface_data_id");

        $result = $query->get();

        return $result;
    }

    public function getSubfolderSearchDetail($id, $textSearch)
    {
        $query      = DB::table("interface_data_subfolder")
            ->select(
                "interface_data_subfolder.*",
                "interface_data_detail.*",
                "ref_document_status.name AS document_status_name",
                "ref_issue_status.name AS issue_status_name",
                "interface_data_detail.status as status_detail"
            )
            ->leftjoin("interface_data", "interface_data.interface_data_id", "interface_data_subfolder.interface_data_id")
            ->leftjoin("interface_data_detail", "interface_data_detail.interface_data_id", "interface_data.interface_data_id")
            ->leftJoin("ref_document_status", "interface_data_detail.document_status_id", "ref_document_status.document_status_id")
            ->leftJoin("ref_issue_status", "interface_data_detail.issue_status_id", "ref_issue_status.issue_status_id")
            ->where("interface_data.interface_data_id", $id)
            ->where("interface_data_subfolder.subfolder_name", "LIKE", "%" . $textSearch . "%")
            ->orderBy("interface_data_subfolder.interface_data_subfolder_id")
            ->groupBy("interface_data_detail.interface_data_id")
            ->get();

        return $query;
    }

    public function deleteFolder($request)
    {

        DB::beginTransaction();
        # ------------------------
        try {
            DB::table("interface_data")
                ->where("interface_data_id", $request->id)
                ->where("created_by", Auth::user()->id)
                ->update([
                    "status" => 0,
                    "deleted_by" => Auth::user()->id,
                    "deleted_at" => Carbon::now()->toDateTimeString()
                ]);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status" => true, "id" => 0);
        } catch (\Exception $e) {
            DB::rollback();
            # ------------------------
            $this->logModel->createError($e->getMessage(), "DELETE FOLDER", "");
            # ------------------------
            return array("status" => false, "id" => 0);
        }
    }

    public function getDataItemSubFolder($id)
    {

        $query      = DB::table("interface_data_detail")->select(
            "interface_data_detail.*",
            "interface_data_subfolder.*",
            "ref_document_status.name AS document_status_name",
            "ref_issue_status.name AS issue_status_name",
            "interface_data_detail.status as status_detail"
        )
            ->leftjoin("interface_data_subfolder", "interface_data_subfolder.interface_data_subfolder_id", "interface_data_detail.interface_data_subfolder_id")
            ->leftjoin("interface_data", "interface_data.interface_data_id", "interface_data_detail.interface_data_id")
            ->leftJoin("ref_document_status", "interface_data_detail.document_status_id", "ref_document_status.document_status_id")
            ->leftJoin("ref_issue_status", "interface_data_detail.issue_status_id", "ref_issue_status.issue_status_id")
            ->where("interface_data_subfolder.interface_data_subfolder_id", $id)
            ->orderBy("interface_data_detail.interface_data_detail_id")
            ->groupBy("interface_data_detail.interface_data_subfolder_id")
            ->groupBy("interface_data_detail.interface_data_id");

        $result = $query->get();

        return $result;
    }
}
