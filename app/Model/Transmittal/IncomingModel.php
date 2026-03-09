<?php

namespace App\Model\Transmittal;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use Storage;
use File;
use DataTables;
use Mail;
use Carbon\Carbon;
use App\Model\Sys\SysModel;
use App\Model\Sys\LogModel;
use Fpdf;
use PDF;

class IncomingModel extends Model
{
    protected $table        = "incoming_transmittal";
    protected $primaryKey   = "incoming_transmittal_id";

    public function __construct() {
        $this->logModel     = new LogModel;
        $this->sysModel     = new SysModel;
    }






  private $internalNumericStatuses = [
        1,   // IFC (internal)
        2,   // Re-IFC
        3,   // IFA (internal)
        4,   // Re-IFA
        5,   // IFR (internal)
        6,   // Re-IFR
        7,   // IFI (internal)
        8,   // Re-IFI
        12,  // RE-AFC
        14,  // RE-As-Built
        15,  // PRA-IFC
        16,  // PRA-IFR
        17,  // PRA-IFI
        18,  // PRA-TES
        // Tambah kalau ada status lain yang masuk internal
    ];

    // 2. Mapping prefix untuk INTERNAL (RE-xxx atau PRA-xxx)
    private $prefixMap = [
        1  => 'IFC',         // IFC internal (tanpa RE- kalau approved)
        2  => 'RE-IFC',
        3  => 'IFA',         // IFA internal (tanpa RE- kalau approved)
        4  => 'RE-IFA',
        5  => 'IFR',
        6  => 'RE-IFR',
        7  => 'IFI',
        8  => 'RE-IFI',
        12 => 'RE-AFC',
        14 => 'RE-As-Built',
        15 => 'PRA-IFC',
        16 => 'PRA-IFR',
        17 => 'PRA-IFI',
        18 => 'PRA-TES',
    ];


private function getAutoRevision($document_id, $issue_status_id)
    {
        $cycleLetter = $this->getCurrentCycleLetter($document_id);
        $subNumeric  = $this->getMaxSubNumericForCycle($document_id, $cycleLetter) + 1;

        $isInternal = in_array($issue_status_id, $this->internalNumericStatuses);

        if ($isInternal) {
            // Internal: pakai prefix dari map + cycle + - + sub-numeric
            $prefix = $this->prefixMap[$issue_status_id] ?? 'RE-';  // default RE- kalau gak ada di map
            $newName = "{$prefix} {$cycleLetter}-{$subNumeric}";

            // Cari atau buat record di ref_document_status
            $docStatus = DB::table('ref_document_status')
                ->where('name', $newName)
                ->where('status', 1)
                ->first();

            if (!$docStatus) {
                return DB::table('ref_document_status')->insertGetId([
                    'name'            => $newName,
                    'issue_status_id' => $issue_status_id,
                    'status'          => 1,
                ]);
            }

            return $docStatus->document_status_id;
        }
    }



private function getCurrentCycleLetter($document_id)
{
    $outgoingCount = DB::table('outgoing_transmittal_detail as otd')
        ->join('incoming_transmittal_detail as itd', 'otd.incoming_transmittal_detail_id', '=', 'itd.incoming_transmittal_detail_id')
        ->where('itd.document_id', $document_id)
        ->count();

    return chr(65 + $outgoingCount);  // 0=A, 1=B, 2=C, dst
}

private function getMaxSubNumericForCycle($document_id, $cycleLetter)
{
    return DB::table('incoming_transmittal_detail as itd')
        ->join('ref_document_status as rds', 'itd.document_status_id', '=', 'rds.document_status_id')
        ->where('itd.document_id', $document_id)
        ->where('rds.name', 'like', "%{$cycleLetter}-%")  // cari yang ada - di belakang cycle
        ->max(DB::raw("CAST(SUBSTRING_INDEX(rds.name, '-', -1) AS UNSIGNED)")) ?? -1;
}

    public function getCollections() {
        try {
            $query  = DB::table($this->table)
                                ->select("$this->table.*", DB::RAW("DATE_FORMAT($this->table.receive_date, '%d/%m/%Y') AS rec_date"), DB::RAW("DATE_FORMAT($this->table.sender_date, '%d/%m/%Y') AS sen_date")
                                        , DB::RAW("COUNT(incoming_transmittal_detail.document_id) AS unit"), "ref_vendor.name AS vendor_name", DB::RAW("DATE_FORMAT(return_date_plan, '%d/%m/%Y') AS deadline_return")
                                        , DB::RAW("(CASE $this->table.status WHEN 1 THEN 'New' WHEN 2 THEN 'Approved' WHEN 3 THEN 'Reject' END) AS status_code")
                                        , DB::RAW("(CASE $this->table.status WHEN 1 THEN 'Unassigned' WHEN 2 THEN 'Assigned' WHEN 3 THEN 'Reject' END) AS vendor_status_code"))
                                ->join("incoming_transmittal_detail", "$this->table.incoming_transmittal_id", "incoming_transmittal_detail.incoming_transmittal_id")
                                ->leftjoin("ref_vendor", "$this->table.vendor_id", "ref_vendor.vendor_id")
                                // ->where("$this->table.status", "!=", 3)
                                ->orderBy("$this->table.incoming_transmittal_id", "DESC")
                                ->groupBy("$this->table.incoming_transmittal_id");

            if(session()->has("SES_SEARCH_INCOMING_NO") != "") {
                $query->where("$this->table.incoming_no", "LIKE", "%" . session()->get("SES_SEARCH_INCOMING_NO") . "%");
            }

            if(session()->has("SES_SEARCH_INCOMING_SUBJECT") != "") {
                $query->where("$this->table.subject", "LIKE", "%" . session()->get("SES_SEARCH_INCOMING_SUBJECT") . "%");
            }

            if(session()->has("SES_SEARCH_INCOMING_RECEIVE") != "") {
                $query->where("$this->table.receive_date", setYMD(session()->get("SES_SEARCH_INCOMING_RECEIVE"), "/"));
            }

            if(Auth::user()->vendor_id != 0) {
                $query->where("$this->table.vendor_id", Auth::user()->vendor_id);
            } else {
                if (session()->has("SES_SEARCH_INCOMING_VENDOR")) {
                    if (session()->get("SES_SEARCH_INCOMING_VENDOR") != "0") {
                        $query->where("$this->table.vendor_id", session()->get("SES_SEARCH_INCOMING_VENDOR"));
                    }
                }
            }

            
            $result = $query->paginate(PAGINATION);
            
            return array("status"=>true, "data"=>$result);
        } catch (\Exception $e) {
            throw $e;
            return array("status"=>false, "data"=>[]);
        }
    }
    
    



    public function getHeader($id) {
        $query      = DB::table($this->table)->select("$this->table.*", "incoming_transmittal_detail.issue_status_id", "ref_vendor.name AS vendor_name", "ref_vendor.email_address")
                                           ->leftjoin("ref_vendor", "$this->table.vendor_id", "ref_vendor.vendor_id")
                                           ->join("incoming_transmittal_detail", "incoming_transmittal_detail.incoming_transmittal_id", "=", "incoming_transmittal.incoming_transmittal_id")
                                           ->where("$this->table.$this->primaryKey", $id)
                                           ->first();

        return $query;
    }

    public function getDetail($id) {
        $query      = DB::table("incoming_transmittal_detail")->select("incoming_transmittal_detail.*", "document.document_no", "document.document_title", "ref_document_status.name AS document_status_name"
                                                                        , "ref_issue_status.name AS issue_status_name", "ref_return_status.name AS return_status_name")
                                                               ->join("document", "incoming_transmittal_detail.document_id", "document.document_id")
                                                               ->leftJoin("ref_document_status", "incoming_transmittal_detail.document_status_id", "ref_document_status.document_status_id")
                                                               ->leftJoin("ref_issue_status", "incoming_transmittal_detail.issue_status_id", "ref_issue_status.issue_status_id")
                                                               ->leftJoin("ref_return_status", "incoming_transmittal_detail.return_status_id", "ref_return_status.return_status_id")
                                                               ->where("incoming_transmittal_detail.incoming_transmittal_id", $id)
                                                               ->orderBy("incoming_transmittal_detail.incoming_transmittal_detail_id")
                                                               ->get();

        return $query;
    }

    public function getItemTemp() {
        $query      = DB::table("incoming_transmittal_detail_temp")->select("incoming_transmittal_detail_temp.*", DB::RAW("IFNULL(document.document_no, incoming_transmittal_detail_temp.document_no) AS document_no"), DB::RAW("IFNULL(document.document_title, incoming_transmittal_detail_temp.document_title) as document_title"), "ref_document_status.name AS document_status_name"
                                                                            , "ref_issue_status.name AS issue_status_name", "ref_return_status.name AS return_status_name")
                                                                   ->leftjoin("document", "incoming_transmittal_detail_temp.document_id", "document.document_id")
                                                                   ->leftJoin("ref_document_status", "incoming_transmittal_detail_temp.document_status_id", "ref_document_status.document_status_id")
                                                                   ->leftJoin("ref_issue_status", "incoming_transmittal_detail_temp.issue_status_id", "ref_issue_status.issue_status_id")
                                                                   ->leftJoin("ref_return_status", "incoming_transmittal_detail_temp.return_status_id", "ref_return_status.return_status_id")
                                                                   ->where("incoming_transmittal_detail_temp.document_status_id", "!=", 94)
                                                                   ->where("incoming_transmittal_detail_temp.created_by", Auth::user()->id)
                                                                   ->orderBy("incoming_transmittal_detail_temp.incoming_transmittal_detail_temp_id")
                                                                   ->get();

        return $query;
    }

    public function getItemTempIDC() {
        $query      = DB::table("incoming_transmittal_detail_temp")->select("incoming_transmittal_detail_temp.*", DB::RAW("IFNULL(document.document_no, incoming_transmittal_detail_temp.document_no) AS document_no"), DB::RAW("IFNULL(document.document_title, incoming_transmittal_detail_temp.document_title) as document_title"), "ref_document_status.name AS document_status_name"
                                                                            , "ref_issue_status.name AS issue_status_name", "ref_return_status.name AS return_status_name")
                                                                   ->leftjoin("document", "incoming_transmittal_detail_temp.document_id", "document.document_id")
                                                                   ->leftJoin("ref_document_status", "incoming_transmittal_detail_temp.document_status_id", "ref_document_status.document_status_id")
                                                                   ->leftJoin("ref_issue_status", "incoming_transmittal_detail_temp.issue_status_id", "ref_issue_status.issue_status_id")
                                                                   ->leftJoin("ref_return_status", "incoming_transmittal_detail_temp.return_status_id", "ref_return_status.return_status_id")
                                                                   ->where("incoming_transmittal_detail_temp.issue_status_id", 1)
                                                                   ->where("incoming_transmittal_detail_temp.document_status_id", 94)
                                                                   ->where("incoming_transmittal_detail_temp.created_by", Auth::user()->id)
                                                                   ->orderBy("incoming_transmittal_detail_temp.incoming_transmittal_detail_temp_id")
                                                                   ->get();

        return $query;
    }
    
    
    public function getDocumentStatusByIssue_old($issue_status_id)
{
    $statuses = DB::table('ref_document_status')
        ->select('document_status_id as id', 'name')
        ->where('status', 1)
        ->where(function ($q) use ($issue_status_id) {
            $q->where('issue_status_id', $issue_status_id)
              ->orWhere('issue_status_id', 0); // ambil juga yang umum
        })
        ->whereRaw("name REGEXP '^[0-9]+$$   |^[A-Z][0-9]+   $$'")
        ->orderByRaw('CAST(name AS UNSIGNED) ASC')
        ->get();

    return response()->json(['data' => $statuses->toArray()]);
}


public function getDocumentStatusByIssueoldbaru($issue_status_id)
{
    // Daftar issue_status_id yang dianggap "internal" (numeric revision)
    $internalStatusIds = [2,  4,  6,  8,12,  15, 16, 17, 18]; // sesuaikan dengan ID internal kamu (IFC, Re-IFC, PRA-IFC, dll)

    $revisions = DB::table('ref_document_status')
        ->select('document_status_id as id', 'name')
        ->where('status', 1)
        ->where(function ($q) use ($issue_status_id, $internalStatusIds) {
            if (in_array($issue_status_id, $internalStatusIds)) {
                // Untuk status internal: tampilkan numeric umum (issue_status_id = 0) + yang spesifik kalau ada
                $q->where('issue_status_id', 0) // numeric 0,1,2,3...
                  ->orWhere('issue_status_id', $issue_status_id);
            } else {
                // Untuk status external: hanya yang spesifik issue_status_id
                $q->where('issue_status_id', $issue_status_id);
            }
        })
        ->orderByRaw('
            CASE 
                WHEN name REGEXP "^[0-9]+$" THEN CAST(name AS UNSIGNED) 
                ELSE 9999 
            END ASC, name ASC
        ') // numeric diurutkan dulu (0,1,2...), baru A0, B0 dst
        ->get();

    return response()->json(['data' => $revisions->toArray()]);
}


public function getDocumentStatusByIssue($issue_status_id)
{
    // Cek apakah status ini termasuk INTERNAL (RE- atau PRA-)
    $issueStatus = DB::table('ref_issue_status')
        ->where('issue_status_id', $issue_status_id)
        ->first();

    $isInternal = $issueStatus && (
        stripos($issueStatus->name, 'RE-') !== false ||
        stripos($issueStatus->name, 'PRA-') !== false
    );

    if (!$isInternal) {
        // External: tampilkan murni A0, A1, dst (seperti sebelumnya)
        $revisions = DB::table('ref_document_status')
            ->select('document_status_id as id', 'name')
            ->where('status', 1)
            ->where('issue_status_id', $issue_status_id)
            ->whereRaw("name REGEXP '^[A-Z][0-9]+$'")
            ->orderByRaw('CAST(SUBSTRING(name, 2) AS UNSIGNED) ASC')
            ->get();

        return response()->json(['data' => $revisions->toArray()]);
    }

    // Internal (RE- atau PRA-): ambil cycle terakhir dari external + generate sub-numeric
    // Cari cycle terakhir untuk dokumen ini (dari history incoming terakhir yang external)
    // Asumsi: dokumen_id dari request atau session, atau ambil dari logic lain
    // Untuk contoh ini, kita asumsikan dokumen_id dikirim via request atau ambil max cycle

    $lastCycle = DB::table('incoming_transmittal_detail as itd')
        ->join('ref_document_status as ds', 'itd.document_status_id', '=', 'ds.document_status_id')
        ->where('itd.document_id', $request->document_id ?? 0) // ganti dengan dokumen_id real
        ->whereRaw("ds.name REGEXP '^[A-Z][0-9]+$'") // hanya cycle murni (external)
        ->max(DB::raw("SUBSTRING(ds.name, 1, 2)")); // ambil 'A0', 'A1', dst

    $cycle = $lastCycle ?? 'A0'; // fallback kalau belum pernah external

    // Generate sub-numeric dinamis (0 sampai 10 misalnya)
    $subNumeric = [];
    for ($i = 0; $i <= 10; $i++) {
        $subNumeric[] = [
            'id' => "dummy_{$cycle}_{$i}", // ID dummy, nanti di-attach bisa dihandle
            'name' => "{$cycle}-{$i}",
            'new_revision' => "{$cycle}-{$i}" // teks yang ditampilkan
        ];
    }

    return response()->json(['data' => $subNumeric]);
}


    public function emptyTemp() {
        DB::table("incoming_transmittal_detail_temp")->where("incoming_transmittal_detail_temp.created_by", Auth::user()->id)->delete();
    }

    public function attachItem($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table("incoming_transmittal_detail_temp")->where("document_id", $request->document_id)->where("created_by", Auth::user()->id)->delete();
            /* ----------
             Upload File
            ----------------------- */
            $default_file   = $request->document_file;
            $default_crs    = $request->document_crs;
            $file_crs       = "";
            $file_url       = DOCUMENT_TEMP_DIR . '/' . Auth::user()->id . "/";
            # ------------------------
                $file_content   = file_get_contents($request->document_file->getRealPath());
                $file           = $request->file('document_file')->getClientOriginalName();
                $file_name      = pathinfo($file,PATHINFO_FILENAME);
                $file_name      = $file_name . "_" . date("YmdHis") . "." . $default_file->getClientOriginalExtension();
                # ------------------------
                Storage::disk("uploads")->put($file_url . $file_name, $file_content);
            # ------------------------
            if ($request->hasFile('document_crs')) {
                $file_content_crs   = file_get_contents($request->document_crs->getRealPath());
                $file               = $request->file('document_crs')->getClientOriginalName();
                $file_crs           = pathinfo($file,PATHINFO_FILENAME);
                $file_crs           = $file_crs . "_" . date("YmdHis") . "." . $default_crs->getClientOriginalExtension();
                # ------------------------
                Storage::disk("uploads")->put($file_url . $file_crs, $file_content_crs);
            }
            # ------------------------
            $id     = DB::table("incoming_transmittal_detail_temp")
                            ->insertGetId([
                                "document_id"=>$request->document_id,
                                "document_no"=>(!empty($request->document_no)) ? $request->document_no : "",
                                "document_title"=>(!empty($request->document_name)) ? $request->document_name : "",
                                "document_url"=>$file_url,
                                "document_file"=>$file_name,
                                "document_crs"=>$file_crs,
                                "remark"=>$request->remark,
                                "issue_status_id"=>$request->issue_status_id,
                                "return_status_id"=>$request->return_status_id,
                                "document_status_id"=>$request->document_status_id,
                                "project_id"=>$request->project_id,
                                "vendor_id"=>Auth::user()->vendor_id,
                                "created_by"=>Auth::user()->id
                            ]);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            # ------------------------
            $this->logModel->createError($e->getMessage(), "ATTACH DOCUMENT IN TEMP TABLE", "");
            # ------------------------
            return array("status"=>false, "id"=>0, "message"=>$e->getMessage());
        }
    }

    public function deleteItem($id) {
        DB::beginTransaction();
        # ------------------------
        try {
            $id     = DB::table("incoming_transmittal_detail_temp")->where("incoming_transmittal_detail_temp_id", $id)->where("created_by", Auth::user()->id)->delete();
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            # ------------------------
            $this->logModel->createError($e->getMessage(), "DELETE ITEM IN TEMP", "");
            # ------------------------
            return array("status"=>false, "id"=>0);
        }
    }

    public function saveIncoming($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            $qValidation    = DB::table("$this->table")->where("incoming_no", $request->incoming_no)->get();

            if(count($qValidation) > 0) {
                return array("status"=>false, "message"=>"Incoming number is already taken", "id"=>0);
            }
            # ------------------------
            $qTempIncoming  = DB::table("incoming_transmittal_detail_temp")
                                ->select("*")->where("created_by", Auth::user()->id)
                                ->whereIn("issue_status_id", ['13','18'])->get();




                                                // "issue_status_id"=>2,
                                                // "issue_status_incoming_id"=>2,
                                                
            foreach($qTempIncoming as $row_temp) {
                $id_docs    = DB::table("document")
                                            ->insertGetId([
                                                "document_no"=>$row_temp->document_no,
                                                "document_title"=>$row_temp->document_title,
                                                "status"=>0,
                                                "document_type_id"=>1,
                                                "issue_status_id"=>$row_temp->issue_status_id,
                                                "document_status_id"=>$row_temp->document_status_id,
                                                "vendor_id"=>$row_temp->vendor_id,
                                                "project_id"=>$row_temp->project_id,
                                                "created_by"=>Auth::user()->id,
                                                "created_at"=>Carbon::now()->toDateTimeString(),
                                            ]);
                # ------------------------
                DB::table("incoming_transmittal_detail_temp")
                            ->where("document_no", $row_temp->document_no)
                            ->where("created_by", Auth::user()->id)
                            ->update([
                                "document_id"=>$id_docs,
                            ]);                
            }
            # ------------------------
            $user_id        = Auth::user()->id;
            $receive        = setYMD($request->receive_date, "/");
            $email_status   = "F";
            $return_day_max = $this->sysModel->getConfig()->return_max_due_days;
            // $plan_date      = date('Y-m-d', strtotime("$return_day_max day"));
            $plan_date      = addWorkingDays(date('Y-m-d'), $return_day_max);
            # ------------------------
            $id         = DB::table($this->table)
                                ->insertGetId([
                                    "incoming_no"=>$request->incoming_no,
                                    "receive_date"=>(!empty($request->receive_date)) ? setYMD($request->receive_date, "/") : null,
                                    "sender_date"=>(!empty($request->sender_date)) ? setYMD($request->sender_date, "/") : null,
                                    "subject"=>$request->subject,
                                    "remark"=>$request->description,
                                    "vendor_id"=>$request->vendor_id,
                                    "return_date_plan"=>$plan_date,
                                    "status"=>1, //NEW
                                    //"return_date_plan"=>(!empty($request->return_date_plan)) ? setYMD($request->return_date_plan, "/") : null,
                                    //"return_date_actual"=>(!empty($request->return_date_actual)) ? setYMD($request->return_date_actual, "/") : null,
                                    "created_by"=>Auth::user()->id,
                                    "created_at"=>Carbon::now()->toDateTimeString(),
                                ]);
            /* ----------
             Upload File
            ----------------------- */
                $file_url   = "";
                $file_name  = "";
                # ------------------------
                if(!empty($request->receipt)) {
                    $default_file   = $request->receipt;
                    $file_url       = DOCUMENT_DIR . "/" . $id . "/";
                    # ------------------------
                    $file_content   = file_get_contents($request->receipt->getRealPath());
                    $file           = $request->file('receipt')->getClientOriginalName();
                    $file_name      = pathinfo($file,PATHINFO_FILENAME);
                    $file_name      = $file_name . "_RCV_". str_replace(".", "_", str_replace("/", "_", $request->incoming_no)). "_" . date("YmdHis") . "." . $default_file->getClientOriginalExtension();
                    # ------------------------
                    Storage::disk("uploads")->put($file_url . $file_name, $file_content);
                    # ------------------------
                    DB::table($this->table)
                            ->where("incoming_transmittal_id", $id)
                            ->update([
                                "receipt_url"=>$file_url,
                                "receipt_file"=>$file_name,
                            ]);
                }
                # ------------------------
                $file_url   = "";
                $file_name  = "";
            # ------------------------
            $qTemp      = DB::table("incoming_transmittal_detail_temp")->select("incoming_transmittal_detail_temp.*", "document.vendor_id", "ref_department.name AS dept_name", "document.vendor_id")
                                                                       ->join("document", "incoming_transmittal_detail_temp.document_id", "document.document_id")
                                                                       ->leftjoin("ref_department", "document.department_id", "ref_department.department_id")
                                                                       ->where("incoming_transmittal_detail_temp.created_by", $user_id)
                                                                       ->get();

            foreach($qTemp as $row) {
                // $new_url            = DOCUMENT_DIR . "/" . $id . "/" . str_replace(" ", "_", $row->dept_name);
                // $new_dir            = public_path("/uploads") . DOCUMENT_DIR . "/" . $id . "/" . str_replace(" ", "_", $row->dept_name);
                $new_url            = DOCUMENT_DIR . "/" . $id;
                $new_dir            = public_path("/uploads") . DOCUMENT_DIR . "/" . $id;
                # ------------------------
                $source_dir         = public_path("/uploads") . $row->document_url . $row->document_file;
                $destination_dir    = public_path("/uploads") . $new_url . "/" . $row->document_file;
                # ------------------------
                $source_dir_crs         = public_path("/uploads") . $row->document_url . $row->document_crs;
                $destination_dir_crs    = public_path("/uploads") . $new_url . "/" . $row->document_crs;
                # ------------------------
                if(!File::isDirectory($new_dir)){
                    File::makeDirectory($new_dir, 0777, true, true);
                }
                # ------------------------
                if(File::exists($source_dir)) {
                    $success = File::copy($source_dir, $destination_dir);

                    if($success) {
                        // File::delete($source_dir);
                    } else {
                        return array("status"=>false, "message"=>FAILED_MESSAGE, "id"=>0);
                    }
                }
                # ------------------------
                if($row->document_crs != "") {
                    if(File::exists($source_dir_crs)) {
                        $success = File::copy($source_dir_crs, $destination_dir_crs);

                        if($success) {
                            // File::delete($source_dir_crs);
                        } else {
                            return array("status"=>false, "message"=>FAILED_MESSAGE, "id"=>0);
                        }
                    }
                }
                # ------------------------
                $id_detail = DB::table("incoming_transmittal_detail")
                                        ->insertGetId([
                                            "document_id"=>$row->document_id,
                                            "document_url"=>$new_url . "/",
                                            "document_file"=>$row->document_file,
                                            "document_crs"=>$row->document_crs,
                                            "incoming_transmittal_id"=>$id,
                                            "remark"=>$row->remark,
                                            "vendor_id"=>$row->vendor_id,
                                            "project_id"=>$row->project_id,
                                            "issue_status_incoming_id"=>2,
                                            "issue_status_id"=>2,
                                            "return_status_id"=>$row->return_status_id,
                                            "document_status_id"=>$row->document_status_id,
                                        ]);
                # ------------------------
                DB::table($this->table)
                            ->where("incoming_transmittal_id", $id)
                            ->update([
                                "vendor_id"=>$row->vendor_id,
                            ]);

                // DB::table("document")
                //             ->where("document_id", $row->document_id)
                //             ->update([
                //                 "deadline"=>$plan_date
                //             ]);
                # ------------------------
                // DB::statement("UPDATE document INNER JOIN sys_config SET document.incoming_transmittal_detail_id = '$id_detail', document_status_id = '$row->document_status_id', issue_status_id = '$row->issue_status_id', deadline = ADDDATE('$receive', INTERVAL sys_config.max_due_days DAY), status = 2 WHERE document_id = '$row->document_id'");
                
                // if($row->issue_status_id <= 3) {
                //     /* ----------
                //      Assign ulang hanya untuk status IDC, IFR/IFI, IFA
                //     ----------------------- */
                //     $cek_assign     = DB::table("assignment")->select("assignment_id", "incoming_transmittal_detail_id")->where("document_id", $row->document_id)->orderBy("assignment_id", "DESC")->get();
                    
                //     if(count($cek_assign) > 0) {
                //         $email_status   = "T";
                //         $cek            = DB::table("assignment")->select("assignment_id", "incoming_transmittal_detail_id")->where("document_id", $row->document_id)->where("incoming_transmittal_detail_id", 0)->get();
                //         /* ----------
                //          Assingment
                //         ----------------------- */
                //         if(count($cek) == 0) {
                //             $assignment_id  = $cek_assign[0]->assignment_id;
                //             # ------------------------
                //             $assin_id       = DB::table("assignment")
                //                                     ->insertGetId([
                //                                         "document_id"=>$row->document_id,
                //                                         "incoming_transmittal_detail_id"=>$id_detail,
                //                                         "created_by"=>Auth::user()->id
                //                                     ]);
                //             # ------------------------
                //             DB::statement("INSERT   INTO comment
                //                                     (assignment_id, user_id, start_date, end_date, remark, role, order_no, issue_status_id, status, created_by, created_at, remark_before)
                //                            SELECT   '$assin_id', comment.user_id, '$receive', ADDDATE('$receive', INTERVAL sys_config.max_due_days DAY), ''
                //                                     , comment.role, comment.order_no, '$row->issue_status_id', IF(order_no = 1, 1, 0), IF(order_no = 1, '$user_id', 0), IF(order_no = 1, NOW(), null), remark
                //                            FROM     comment INNER JOIN assignment ON comment.assignment_id = assignment.assignment_id
                //                            INNER    JOIN sys_config
                //                            WHERE    assignment.assignment_id = '$assignment_id'");
                //         } else {
                //             DB::table("assignment")
                //                         ->where("document_id", $row->document_id)
                //                         ->update([
                //                             "incoming_transmittal_detail_id"=>$id_detail,
                //                         ]);
                //             # ------------------------
                //             $assignment_id  = $cek_assign[0]->assignment_id;
                //             # ------------------------
                //             DB::statement("UPDATE   comment INNER JOIN sys_config
                //                            SET      start_date = '$receive', end_date = ADDDATE('$receive', INTERVAL sys_config.max_due_days DAY), status = 1, created_by = '$user_id', created_at = NOW() 
                //                            WHERE    assignment_id = '$assignment_id' and order_no = 1");
                //         }
                //     }
                // }
            }
            /* ----------
             Send Email To Document Controll
            ----------------------- */
//            return $this->sysModel->getConfig()->email_status;
            if($this->sysModel->getConfig()->email_status == 1) {
                $emails             = explode(",", $this->sysModel->getConfig()->document_controll_email_address_notification);
                $title              = "Incoming Transmittal Notification";
                $data["title"]      = $title;
                $data["inc_no"]     = $request->incoming_no;
                $data["content"]    = DB::table("incoming_transmittal_detail")
                                        ->select("document.document_no", "document.document_title" , "ref_document_status.name AS document_status_name", "ref_issue_status.name AS issue_status_name")
                                        ->join("document", "incoming_transmittal_detail.document_id", "document.document_id")
                                        ->leftJoin("ref_document_status", "incoming_transmittal_detail.document_status_id", "ref_document_status.document_status_id")
                                        ->leftJoin("ref_issue_status", "incoming_transmittal_detail.issue_status_id", "ref_issue_status.issue_status_id")
                                        ->where("incoming_transmittal_detail.incoming_transmittal_id", $id)
                                        ->get();
                Mail::send('email.transmittal-incoming-notification', $data, function($message) use ($title, $emails){
                    for($i=0; $i<count($emails); $i++) {
                        $message->to(str_replace(" ", "", $emails[$i]), str_replace(" ", "", $emails[$i]))->subject($title);
                    }
                    # ---------------
                    $message->from(env("MAIL_USERNAME"), 'Automatic Mail System');
                });
            }

            /* ----------
             Send Email
            ----------------------- */
            // if($email_status == "T") {
            //     if($this->sysModel->getConfig()->email_status == 1) {
            //         $qIncomingDetail    = DB::table("incoming_transmittal_detail")->select("sys_users.full_name", "sys_users.email", "comment.order_no", "incoming_transmittal.incoming_no"
            //                                                                                 , DB::RAW("GROUP_CONCAT(document.document_no ORDER BY incoming_transmittal_detail.incoming_transmittal_detail_id ASC SEPARATOR '@') AS doc_no_listing")
            //                                                                                 , DB::RAW("GROUP_CONCAT(document.document_title ORDER BY incoming_transmittal_detail.incoming_transmittal_detail_id ASC SEPARATOR '@') AS doc_title_listing")
            //                                                                                 , DB::RAW("GROUP_CONCAT(ref_issue_status.name ORDER BY incoming_transmittal_detail.incoming_transmittal_detail_id ASC SEPARATOR '@') AS issue_status_listing")
            //                                                                                 , DB::RAW("GROUP_CONCAT(ref_document_status.name ORDER BY incoming_transmittal_detail.incoming_transmittal_detail_id ASC SEPARATOR '@') AS doc_status_listing"))
            //                                                                       ->join("document", "incoming_transmittal_detail.document_id", "document.document_id")
            //                                                                       ->join("incoming_transmittal", "incoming_transmittal_detail.incoming_transmittal_id", "incoming_transmittal.incoming_transmittal_id")
            //                                                                       ->leftJoin("ref_document_status", "incoming_transmittal_detail.document_status_id", "ref_document_status.document_status_id")
            //                                                                       ->leftJoin("ref_issue_status", "incoming_transmittal_detail.issue_status_id", "ref_issue_status.issue_status_id")
            //                                                                       ->leftJoin("assignment", "incoming_transmittal_detail.incoming_transmittal_detail_id", "assignment.incoming_transmittal_detail_id")
            //                                                                       ->leftJoin("comment", "assignment.assignment_id", "comment.assignment_id")
            //                                                                       ->leftJoin("sys_users", "comment.user_id", "sys_users.id")
            //                                                                       ->where("incoming_transmittal_detail.incoming_transmittal_id", $id)
            //                                                                       ->where("comment.order_no", 1)
            //                                                                       ->groupBy("sys_users.full_name", "sys_users.email", "comment.order_no", "incoming_transmittal.incoming_no")
            //                                                                       ->get();
            //         foreach($qIncomingDetail as $dataInc) {
            //             $title                          = "Document Review Notification " . $dataInc->incoming_no;
            //             $email_address                  = $dataInc->email;
            //             $email_name                     = $dataInc->full_name;
            //             # ---------------
            //             $data["title"]                  = "Document Review Notification";
            //             $data["inc_no"]                 = $dataInc->incoming_no;
            //             $data["doc_no_listing"]         = $dataInc->doc_no_listing;
            //             $data["doc_title_listing"]      = $dataInc->doc_title_listing;
            //             $data["issue_status_listing"]   = $dataInc->issue_status_listing;
            //             $data["doc_status_listing"]     = $dataInc->doc_status_listing;
            //             # ---------------
            //             Mail::send('email.incoming-notification', $data, function($message) use ($title, $email_address, $email_name) {
            //                 $message->to($email_address, $email_name)->subject($title);
            //                 # ---------------
            //                 $message->from(env("MAIL_USERNAME"), 'Automatic Mail System');
            //             });    
            //         }
            //     }
            // }
            # ------------------------
            DB::table("incoming_transmittal_detail_temp")->where("created_by", $user_id)->delete();
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("ADD INCOMING (" . $id . ")", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "message"=>"", "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "ADD INCOMING FAILED", "");
            # ---------------
            return array("status"=>false, "message"=>FAILED_MESSAGE, "id"=>0, 'error' => $e->getMessage());
        }
    }
    
    
    
    
    public function updateIncomingWithAttach($request, $id) {
    DB::beginTransaction();
    try {
        $user_id = Auth::user()->id;

        // Update header
        DB::table($this->table)
            ->where("incoming_transmittal_id", $id)
            ->update([
                "incoming_no" => $request->incoming_no,
                "receive_date" => !empty($request->receive_date) ? setYMD($request->receive_date, "/") : null,
                "sender_date" => !empty($request->sender_date) ? setYMD($request->sender_date, "/") : null,
                "subject" => $request->subject,
                "remark" => $request->remark ?? $request->description,
                "return_date_plan" => !empty($request->return_date_plan) ? setYMD($request->return_date_plan, "/") : null,
                "return_date_actual" => !empty($request->return_date_actual) ? setYMD($request->return_date_actual, "/") : null,
                "updated_by" => $user_id,
                "updated_at" => Carbon::now()->toDateTimeString(),
            ]);

        // HANDLE RECEIPT (header) kalau ada upload baru
        if ($request->hasFile('receipt') && $request->file('receipt')->isValid()) {
            $file = $request->file('receipt');
            $file_url = DOCUMENT_DIR . "/" . $id . "/";
            $file_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $file_name = $file_name . "_RCV_UPDATE_" . str_replace([".", "/"], "_", $request->incoming_no) . "_" . date("YmdHis") . "." . $file->getClientOriginalExtension();

            $file_content = file_get_contents($file->getRealPath());
            Storage::disk("uploads")->put($file_url . $file_name, $file_content);

            DB::table($this->table)
                ->where("incoming_transmittal_id", $id)
                ->update([
                    "receipt_url" => $file_url,
                    "receipt_file" => $file_name,
                ]);
        }

        // HANDLE ATTACH DOKUMEN BARU (dari temp table)
        $qTemp = DB::table("incoming_transmittal_detail_temp")
            ->select("incoming_transmittal_detail_temp.*", "document.vendor_id", "ref_department.name AS dept_name")
            ->join("document", "incoming_transmittal_detail_temp.document_id", "document.document_id")
            ->leftJoin("ref_department", "document.department_id", "ref_department.department_id")
            ->where("incoming_transmittal_detail_temp.created_by", $user_id)
            ->get();

        foreach ($qTemp as $row) {
            $new_url = DOCUMENT_DIR . "/" . $id;
            $new_dir = public_path("/uploads") . $new_url;

            if (!File::isDirectory($new_dir)) {
                File::makeDirectory($new_dir, 0777, true, true);
            }

            // Copy file dokumen utama
            $source_dir = public_path("/uploads") . $row->document_url . $row->document_file;
            $destination_dir = public_path("/uploads") . $new_url . "/" . $row->document_file;

            if (File::exists($source_dir)) {
                File::copy($source_dir, $destination_dir);
            }

            // Copy CRS kalau ada
            if ($row->document_crs) {
                $source_crs = public_path("/uploads") . $row->document_url . $row->document_crs;
                $dest_crs = public_path("/uploads") . $new_url . "/" . $row->document_crs;
                if (File::exists($source_crs)) {
                    File::copy($source_crs, $dest_crs);
                }
            }

            // Insert ke detail permanen
            DB::table("incoming_transmittal_detail")->insert([
                "document_id" => $row->document_id,
                "document_url" => $new_url . "/",
                "document_file" => $row->document_file,
                "document_crs" => $row->document_crs,
                "incoming_transmittal_id" => $id,
                "remark" => $row->remark,
                "vendor_id" => $row->vendor_id,
                "project_id" => $row->project_id,
                "issue_status_id" => $row->issue_status_id,
                "document_status_id" => $row->document_status_id,
                "return_status_id" => $row->return_status_id,
                // tambah field lain kalau perlu
            ]);
        }

        // Hapus temp setelah diproses
        DB::table("incoming_transmittal_detail_temp")
            ->where("created_by", $user_id)
            ->delete();

        DB::commit();
        return ["status" => true];
    } catch (\Exception $e) {
        DB::rollback();
        $this->logModel->createError($e->getMessage(), "UPDATE INCOMING WITH ATTACH FAILED", "");
        return ["status" => false, "message" => $e->getMessage()];
    }
}
    

    public function saveIncomingIdc($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            $qValidation    = DB::table("$this->table")->where("incoming_no", $request->incoming_no)->get();
            
            if(count($qValidation) > 0) {
                return array("status"=>false, "message"=>"Incoming number is already taken", "id"=>0);
            }
            # ------------------------
            $qTempIncoming  = DB::table("incoming_transmittal_detail_temp")->select("*")->where("created_by", Auth::user()->id)->where("issue_status_id", STATUS_ONLY_IDC)->get();

            foreach($qTempIncoming as $row_temp) {
                $id_docs    = DB::table("document")
                                            ->insertGetId([
                                                "document_no"=>$row_temp->document_no,
                                                "document_title"=>$row_temp->document_title,
                                                "status"=>0,
                                                "document_type_id"=>1,
                                                "vendor_id"=>Auth::user()->vendor_id,
                                                "project_id"=>$row_temp->project_id,
                                                "created_by"=>Auth::user()->id,
                                                "created_at"=>Carbon::now()->toDateTimeString(),
                                            ]);
                # ------------------------
                DB::table("incoming_transmittal_detail_temp")
                            ->where("document_no", $row_temp->document_no)
                            ->where("created_by", Auth::user()->id)
                            ->update([
                                "document_id"=>$id_docs,
                            ]);                
            }
            # ------------------------
            $user_id        = Auth::user()->id;
            $receive        = setYMD($request->receive_date, "/");
            $email_status   = "F";
            $return_day_max = $this->sysModel->getConfig()->return_max_due_days;
            // $plan_date      = date('Y-m-d', strtotime("$return_day_max day"));
            $plan_date      = addWorkingDaysHardcode(date('Y-m-d'), $return_day_max);
            # ------------------------
            $id         = DB::table($this->table)
                                ->insertGetId([
                                    "incoming_no"=>$request->incoming_no,
                                    "receive_date"=>(!empty($request->receive_date)) ? setYMD($request->receive_date, "/") : null,
                                    "sender_date"=>(!empty($request->sender_date)) ? setYMD($request->sender_date, "/") : null,
                                    "subject"=>$request->subject,
                                    "remark"=>$request->description,
                                    "vendor_id"=>$request->vendor_id,
                                    "return_date_plan"=>$plan_date,
                                    "status"=>1, //NEW
                                    //"return_date_plan"=>(!empty($request->return_date_plan)) ? setYMD($request->return_date_plan, "/") : null,
                                    //"return_date_actual"=>(!empty($request->return_date_actual)) ? setYMD($request->return_date_actual, "/") : null,
                                    "created_by"=>Auth::user()->id,
                                    "created_at"=>Carbon::now()->toDateTimeString(),
                                ]);
            /* ----------
             Upload File
            ----------------------- */
                $file_url   = "";
                $file_name  = "";
                # ------------------------
                if(!empty($request->receipt)) {
                    $default_file   = $request->receipt;
                    $file_url       = DOCUMENT_DIR . "/" . $id . "/";
                    # ------------------------
                    $file_content   = file_get_contents($request->receipt->getRealPath());
                    $file           = $request->file('receipt')->getClientOriginalName();
                    $file_name      = pathinfo($file,PATHINFO_FILENAME);
                    $file_name      = $file_name . "_RCV_". str_replace(".", "_", str_replace("/", "_", $request->incoming_no)). "_" . date("YmdHis") . "." . $default_file->getClientOriginalExtension();
                    # ------------------------
                    Storage::disk("uploads")->put($file_url . $file_name, $file_content);
                    # ------------------------
                    DB::table($this->table)
                            ->where("incoming_transmittal_id", $id)
                            ->update([
                                "receipt_url"=>$file_url,
                                "receipt_file"=>$file_name,
                            ]);
                }
                # ------------------------
                $file_url   = "";
                $file_name  = "";
            # ------------------------
            $qTemp      = DB::table("incoming_transmittal_detail_temp")->select("incoming_transmittal_detail_temp.*", "document.vendor_id", "ref_department.name AS dept_name", "document.vendor_id")
                                                                       ->join("document", "incoming_transmittal_detail_temp.document_id", "document.document_id")
                                                                       ->leftjoin("ref_department", "document.department_id", "ref_department.department_id")
                                                                       ->where("incoming_transmittal_detail_temp.created_by", $user_id)
                                                                       ->get();
            
            foreach($qTemp as $row) {
                // $new_url            = DOCUMENT_DIR . "/" . $id . "/" . str_replace(" ", "_", $row->dept_name);
                // $new_dir            = public_path("/uploads") . DOCUMENT_DIR . "/" . $id . "/" . str_replace(" ", "_", $row->dept_name);
                $new_url            = DOCUMENT_DIR . "/" . $id;
                $new_dir            = public_path("/uploads") . DOCUMENT_DIR . "/" . $id;
                # ------------------------
                $source_dir         = public_path("/uploads") . $row->document_url . $row->document_file;
                $destination_dir    = public_path("/uploads") . $new_url . "/" . $row->document_file;
                # ------------------------
                $source_dir_crs         = public_path("/uploads") . $row->document_url . $row->document_crs;
                $destination_dir_crs    = public_path("/uploads") . $new_url . "/" . $row->document_crs;
                # ------------------------
                if(!File::isDirectory($new_dir)){
                    File::makeDirectory($new_dir, 0777, true, true);
                }
                # ------------------------
                if(File::exists($source_dir)) {
                    $success = File::copy($source_dir, $destination_dir);

                    if($success) {
                        // File::delete($source_dir);
                    } else {
                        return array("status"=>false, "message"=>FAILED_MESSAGE, "id"=>0);
                    }
                }
                # ------------------------
                if($row->document_crs != "") {
                    if(File::exists($source_dir_crs)) {
                        $success = File::copy($source_dir_crs, $destination_dir_crs);

                        if($success) {
                            // File::delete($source_dir_crs);
                        } else {
                            return array("status"=>false, "message"=>FAILED_MESSAGE, "id"=>0);
                        }
                    }
                }
                # ------------------------
                $id_detail = DB::table("incoming_transmittal_detail")
                                        ->insertGetId([
                                            "document_id"=>$row->document_id,
                                            "document_url"=>$new_url . "/",
                                            "document_file"=>$row->document_file,
                                            "document_crs"=>$row->document_crs,
                                            "incoming_transmittal_id"=>$id,
                                            "remark"=>$row->remark,
                                            "issue_status_incoming_id"=>$row->issue_status_id,
                                            "issue_status_id"=>$row->issue_status_id,
                                            "return_status_id"=>$row->return_status_id,
                                            "document_status_id"=>$row->document_status_id,
                                        ]);
                # ------------------------
                DB::table($this->table)
                            ->where("incoming_transmittal_id", $id)
                            ->update([
                                "vendor_id"=>$row->vendor_id,
                            ]);

                // DB::table("document")
                //             ->where("document_id", $row->document_id)
                //             ->update([
                //                 "deadline"=>$plan_date
                //             ]);
                # ------------------------
                // DB::statement("UPDATE document INNER JOIN sys_config SET document.incoming_transmittal_detail_id = '$id_detail', document_status_id = '$row->document_status_id', issue_status_id = '$row->issue_status_id', deadline = ADDDATE('$receive', INTERVAL sys_config.max_due_days DAY), status = 2 WHERE document_id = '$row->document_id'");
                
                // if($row->issue_status_id <= 3) {
                //     /* ----------
                //      Assign ulang hanya untuk status IDC, IFR/IFI, IFA
                //     ----------------------- */
                //     $cek_assign     = DB::table("assignment")->select("assignment_id", "incoming_transmittal_detail_id")->where("document_id", $row->document_id)->orderBy("assignment_id", "DESC")->get();
                    
                //     if(count($cek_assign) > 0) {
                //         $email_status   = "T";
                //         $cek            = DB::table("assignment")->select("assignment_id", "incoming_transmittal_detail_id")->where("document_id", $row->document_id)->where("incoming_transmittal_detail_id", 0)->get();
                //         /* ----------
                //          Assingment
                //         ----------------------- */
                //         if(count($cek) == 0) {
                //             $assignment_id  = $cek_assign[0]->assignment_id;
                //             # ------------------------
                //             $assin_id       = DB::table("assignment")
                //                                     ->insertGetId([
                //                                         "document_id"=>$row->document_id,
                //                                         "incoming_transmittal_detail_id"=>$id_detail,
                //                                         "created_by"=>Auth::user()->id
                //                                     ]);
                //             # ------------------------
                //             DB::statement("INSERT   INTO comment
                //                                     (assignment_id, user_id, start_date, end_date, remark, role, order_no, issue_status_id, status, created_by, created_at, remark_before)
                //                            SELECT   '$assin_id', comment.user_id, '$receive', ADDDATE('$receive', INTERVAL sys_config.max_due_days DAY), ''
                //                                     , comment.role, comment.order_no, '$row->issue_status_id', IF(order_no = 1, 1, 0), IF(order_no = 1, '$user_id', 0), IF(order_no = 1, NOW(), null), remark
                //                            FROM     comment INNER JOIN assignment ON comment.assignment_id = assignment.assignment_id
                //                            INNER    JOIN sys_config
                //                            WHERE    assignment.assignment_id = '$assignment_id'");
                //         } else {
                //             DB::table("assignment")
                //                         ->where("document_id", $row->document_id)
                //                         ->update([
                //                             "incoming_transmittal_detail_id"=>$id_detail,
                //                         ]);
                //             # ------------------------
                //             $assignment_id  = $cek_assign[0]->assignment_id;
                //             # ------------------------
                //             DB::statement("UPDATE   comment INNER JOIN sys_config
                //                            SET      start_date = '$receive', end_date = ADDDATE('$receive', INTERVAL sys_config.max_due_days DAY), status = 1, created_by = '$user_id', created_at = NOW() 
                //                            WHERE    assignment_id = '$assignment_id' and order_no = 1");
                //         }
                //     }
                // }
            }
            /* ----------
             Send Email To Document Controll
            ----------------------- */
            if($this->sysModel->getConfig()->email_status == 1) {
                $emails             = explode(",", $this->sysModel->getConfig()->document_controll_email_address_notification);
                $title              = "Incoming Transmittal Notification";
                $data["title"]      = $title;
                $data["inc_no"]     = $request->incoming_no;
                $data["content"]    = DB::table("incoming_transmittal_detail")->select("document.document_no", "document.document_title"
                                                                                        , "ref_document_status.name AS document_status_name", "ref_issue_status.name AS issue_status_name")
                                                                              ->join("document", "incoming_transmittal_detail.document_id", "document.document_id")
                                                                              ->leftJoin("ref_document_status", "incoming_transmittal_detail.document_status_id", "ref_document_status.document_status_id")
                                                                              ->leftJoin("ref_issue_status", "incoming_transmittal_detail.issue_status_id", "ref_issue_status.issue_status_id")
                                                                              ->where("incoming_transmittal_detail.incoming_transmittal_id", $id)
                                                                              ->get();
                # ---------------
                Mail::send('email.transmittal-incoming-notification', $data, function($message) use ($title, $emails){
                    for($i=0; $i<count($emails); $i++) {
                        $message->to(str_replace(" ", "", $emails[$i]), str_replace(" ", "", $emails[$i]))->subject($title);
                    }
                    # ---------------
                    $message->from(env("MAIL_USERNAME"), 'Automatic Mail System');
                });
            }
            /* ----------
             Send Email
            ----------------------- */
            // if($email_status == "T") {
            //     if($this->sysModel->getConfig()->email_status == 1) {
            //         $qIncomingDetail    = DB::table("incoming_transmittal_detail")->select("sys_users.full_name", "sys_users.email", "comment.order_no", "incoming_transmittal.incoming_no"
            //                                                                                 , DB::RAW("GROUP_CONCAT(document.document_no ORDER BY incoming_transmittal_detail.incoming_transmittal_detail_id ASC SEPARATOR '@') AS doc_no_listing")
            //                                                                                 , DB::RAW("GROUP_CONCAT(document.document_title ORDER BY incoming_transmittal_detail.incoming_transmittal_detail_id ASC SEPARATOR '@') AS doc_title_listing")
            //                                                                                 , DB::RAW("GROUP_CONCAT(ref_issue_status.name ORDER BY incoming_transmittal_detail.incoming_transmittal_detail_id ASC SEPARATOR '@') AS issue_status_listing")
            //                                                                                 , DB::RAW("GROUP_CONCAT(ref_document_status.name ORDER BY incoming_transmittal_detail.incoming_transmittal_detail_id ASC SEPARATOR '@') AS doc_status_listing"))
            //                                                                       ->join("document", "incoming_transmittal_detail.document_id", "document.document_id")
            //                                                                       ->join("incoming_transmittal", "incoming_transmittal_detail.incoming_transmittal_id", "incoming_transmittal.incoming_transmittal_id")
            //                                                                       ->leftJoin("ref_document_status", "incoming_transmittal_detail.document_status_id", "ref_document_status.document_status_id")
            //                                                                       ->leftJoin("ref_issue_status", "incoming_transmittal_detail.issue_status_id", "ref_issue_status.issue_status_id")
            //                                                                       ->leftJoin("assignment", "incoming_transmittal_detail.incoming_transmittal_detail_id", "assignment.incoming_transmittal_detail_id")
            //                                                                       ->leftJoin("comment", "assignment.assignment_id", "comment.assignment_id")
            //                                                                       ->leftJoin("sys_users", "comment.user_id", "sys_users.id")
            //                                                                       ->where("incoming_transmittal_detail.incoming_transmittal_id", $id)
            //                                                                       ->where("comment.order_no", 1)
            //                                                                       ->groupBy("sys_users.full_name", "sys_users.email", "comment.order_no", "incoming_transmittal.incoming_no")
            //                                                                       ->get();
            //         foreach($qIncomingDetail as $dataInc) {
            //             $title                          = "Document Review Notification " . $dataInc->incoming_no;
            //             $email_address                  = $dataInc->email;
            //             $email_name                     = $dataInc->full_name;
            //             # ---------------
            //             $data["title"]                  = "Document Review Notification";
            //             $data["inc_no"]                 = $dataInc->incoming_no;
            //             $data["doc_no_listing"]         = $dataInc->doc_no_listing;
            //             $data["doc_title_listing"]      = $dataInc->doc_title_listing;
            //             $data["issue_status_listing"]   = $dataInc->issue_status_listing;
            //             $data["doc_status_listing"]     = $dataInc->doc_status_listing;
            //             # ---------------
            //             Mail::send('email.incoming-notification', $data, function($message) use ($title, $email_address, $email_name) {
            //                 $message->to($email_address, $email_name)->subject($title);
            //                 # ---------------
            //                 $message->from(env("MAIL_USERNAME"), 'Automatic Mail System');
            //             });    
            //         }
            //     }
            // }
            # ------------------------
            DB::table("incoming_transmittal_detail_temp")->where("created_by", $user_id)->delete();
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("ADD INCOMING (" . $id . ")", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "message"=>"", "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "ADD INCOMING FAILED", "");
            # ---------------
            return array("status"=>false, "message"=>FAILED_MESSAGE, "id"=>0);
        }
    }

    public function approveIncoming($request) {
        // dd($request);
        DB::beginTransaction();
        # ------------------------
        try {
            $receive    = setYMD($request->sender_date, "/");
            $returndate = setYMD($request->return_date_plan, "/");
            $user_id    = Auth::user()->id;
            
   /*         
 $projectEndDate = DB::table('project')
            ->where('project_id', $request->project_id)
            ->value('end_date'); // DATE: YYYY-MM-DD
            $returndate= $projectEndDate;         
     */       
            
			
			$startDate = Carbon::now()->toDateString();            // tanggal approve
			$endDate   = addWorkingDaysHardcode($startDate, 10);   // 10 hari kerja
            # ------------------------
            DB::table($this->table)
                            ->where("incoming_transmittal_id", $request->id)
                            ->update([
                                "status"=>$request->status,
                                "remark_approval"=>$request->remark_approval,
                                "approved_by"=>Auth::user()->id,
                                "approved_at"=>Carbon::now()->toDateTimeString(),
                            ]);
            /* ----------
             Detail
            ----------------------- */
              if($request->status == 2) {
                $qDetail    = DB::table("incoming_transmittal_detail")->select("*")->where("incoming_transmittal_id", $request->id)->get();
                foreach($qDetail as $row) {
                    // DB::statement("UPDATE document INNER JOIN sys_config SET document.incoming_transmittal_detail_id = '$row->incoming_transmittal_detail_id', document_status_id = '$row->document_status_id', issue_status_id = '$row->issue_status_id', deadline = ADDDATE('$receive', INTERVAL sys_config.max_due_days DAY), status = 2 WHERE document_id = '$row->document_id'");

                    /*
                    if($row->issue_status_id == STATUS_ONLY_IFI) {
                        DB::statement("UPDATE document INNER JOIN sys_config SET document.incoming_transmittal_detail_id = '$row->incoming_transmittal_detail_id', document_status_id = '$row->document_status_id', issue_status_id = '$row->issue_status_id', deadline = '$returndate', status = 7 WHERE document_id = '$row->document_id'");
                    } elseif($row->issue_status_id == 6) { // As-Built
                        DB::statement("UPDATE document INNER JOIN sys_config SET document.incoming_transmittal_detail_id = '$row->incoming_transmittal_detail_id', document_status_id = '$row->document_status_id', issue_status_id = '$row->issue_status_id', deadline = '$returndate', status = 6 WHERE document_id = '$row->document_id'");
                    } elseif($row->issue_status_id == 17) { // IFU-Approved
                        DB::statement("UPDATE document INNER JOIN sys_config SET document.incoming_transmittal_detail_id = '$row->incoming_transmittal_detail_id', document_status_id = '$row->document_status_id', issue_status_id = '$row->issue_status_id', deadline = '$returndate', status = 6 WHERE document_id = '$row->document_id'");
                    } elseif($row->issue_status_id == 19) { // AFC-Approved
                        DB::statement("UPDATE document INNER JOIN sys_config SET document.incoming_transmittal_detail_id = '$row->incoming_transmittal_detail_id', document_status_id = '$row->document_status_id', issue_status_id = '$row->issue_status_id', deadline = '$returndate', status = 6 WHERE document_id = '$row->document_id'");
                    } else {
                        DB::statement("UPDATE document INNER JOIN sys_config SET document.incoming_transmittal_detail_id = '$row->incoming_transmittal_detail_id', document_status_id = '$row->document_status_id', issue_status_id = '$row->issue_status_id', deadline = '$returndate', status = 2 WHERE document_id = '$row->document_id'");
                    }
                    
                    */
                    
                    
                    if($row->issue_status_id == STATUS_ONLY_IFI) {
                        DB::statement("UPDATE document INNER JOIN sys_config SET document.incoming_transmittal_detail_id = '$row->incoming_transmittal_detail_id', document_status_id = '$row->document_status_id', issue_status_id = '$row->issue_status_id', status = 7 WHERE document_id = '$row->document_id'");
                    } elseif($row->issue_status_id == 6) { // As-Built
                        DB::statement("UPDATE document INNER JOIN sys_config SET document.incoming_transmittal_detail_id = '$row->incoming_transmittal_detail_id', document_status_id = '$row->document_status_id', issue_status_id = '$row->issue_status_id',  status = 6 WHERE document_id = '$row->document_id'");
                    } elseif($row->issue_status_id == 17) { // IFU-Approved
                        DB::statement("UPDATE document INNER JOIN sys_config SET document.incoming_transmittal_detail_id = '$row->incoming_transmittal_detail_id', document_status_id = '$row->document_status_id', issue_status_id = '$row->issue_status_id',  status = 6 WHERE document_id = '$row->document_id'");
                    } elseif($row->issue_status_id == 19) { // AFC-Approved
                        DB::statement("UPDATE document INNER JOIN sys_config SET document.incoming_transmittal_detail_id = '$row->incoming_transmittal_detail_id', document_status_id = '$row->document_status_id', issue_status_id = '$row->issue_status_id',  status = 6 WHERE document_id = '$row->document_id'");
                    } else {
                        DB::statement("UPDATE document INNER JOIN sys_config SET document.incoming_transmittal_detail_id = '$row->incoming_transmittal_detail_id', document_status_id = '$row->document_status_id', issue_status_id = '$row->issue_status_id',   status = 2 WHERE document_id = '$row->document_id'");
                    }

                    if($row->issue_status_id == STATUS_ONLY_IFI_CONSTRUCTION) {
                        DB::statement("UPDATE document INNER JOIN sys_config SET document.incoming_transmittal_detail_id = '$row->incoming_transmittal_detail_id', document_status_id = '$row->document_status_id', issue_status_id = '$row->issue_status_id', deadline = '$returndate', status = 7 WHERE document_id = '$row->document_id'");
                    }
                    
                    if(($row->issue_status_id == 1) || ($row->issue_status_id == 2) || ($row->issue_status_id == 3) || ($row->issue_status_id == 4) || ($row->issue_status_id == 5) || ($row->issue_status_id == 8) || ($row->issue_status_id == 9) || ($row->issue_status_id == 10) || ($row->issue_status_id == 11) || ($row->issue_status_id == 12) || ($row->issue_status_id == 13) || ($row->issue_status_id == 14) || ($row->issue_status_id == 15) || ($row->issue_status_id == 16) || ($row->issue_status_id == 18)) {
                        /* ----------
                         Assign ulang hanya untuk status IDC, IFR, IFI, IFC, IFA, Re-IFA, Re-IFI, Re-IFC
                        ----------------------- */
                        $cek_assign     = DB::table("assignment")->select("assignment_id", "incoming_transmittal_detail_id")->where("document_id", $row->document_id)->orderBy("assignment_id", "DESC")->get();
                        if(count($cek_assign) > 0) {
                            
                            $email_status   = "T";
                            $cek            = DB::table("assignment")->select("assignment_id", "incoming_transmittal_detail_id")->where("document_id", $row->document_id)->where("incoming_transmittal_detail_id", 0)->get();
                            /* ----------
                             Assingment
                            ----------------------- */
                            if(count($cek) == 0) {
                                DB::table("assignment")->where("document_id", $row->document_id)->where("incoming_transmittal_detail_id", 0)->delete();
                                $assignment_id  = $cek_assign[0]->assignment_id;
                                # ------------------------
                                $assin_id       = DB::table("assignment")
                                                        ->insertGetId([
                                                            "document_id"=>$row->document_id,
                                                            "incoming_transmittal_detail_id"=>$row->incoming_transmittal_detail_id,
                                                            "created_by"=>Auth::user()->id
                                                        ]);
                                # ------------------------
								/*
                                DB::statement("INSERT   INTO comment
                                                        (assignment_id, user_id, start_date, end_date, remark, role, order_no, issue_status_id, status, created_by, created_at, remark_before)
                                               SELECT   '$assin_id', comment.user_id, '$receive', ADDDATE('$receive', INTERVAL sys_config.max_due_days DAY), ''
                                                        , comment.role, comment.order_no, '$row->issue_status_id', 1, '$user_id', NOW(), remark
                                               FROM     comment INNER JOIN assignment ON comment.assignment_id = assignment.assignment_id
                                               INNER    JOIN sys_config
                                               WHERE    assignment.assignment_id = '$assignment_id'");
											   
								*/
								DB::statement("INSERT   INTO comment
									(assignment_id, user_id, start_date, end_date, remark, role, order_no, issue_status_id, status, created_by, created_at, remark_before)
								SELECT   '$assin_id', comment.user_id, '$startDate', '$endDate', ''
										, comment.role, comment.order_no, '$row->issue_status_id', 1, '$user_id', NOW(), remark
								FROM     comment INNER JOIN assignment ON comment.assignment_id = assignment.assignment_id
								WHERE    assignment.assignment_id = '$assignment_id'");
                            } else {
                                DB::table("assignment")
                                            ->where("document_id", $row->document_id)
                                            ->where("incoming_transmittal_detail_id", 0)
                                            ->update([
                                                "incoming_transmittal_detail_id"=>$row->incoming_transmittal_detail_id,
                                            ]);
                                # ------------------------
                                $assignment_id  = $cek_assign[0]->assignment_id;
                                # ------------------------
                                # Paralel
								/*
                                DB::statement("UPDATE   comment INNER JOIN sys_config
                                               SET      start_date = '$receive', end_date = ADDDATE('$receive', INTERVAL sys_config.max_due_days DAY), status = 1, created_by = '$user_id', created_at = NOW() 
                                               WHERE    assignment_id = '$assignment_id'");
											   
								*/
								DB::statement("UPDATE comment
								SET start_date = '$startDate',
									end_date   = '$endDate',
									status     = 1,
									created_by = '$user_id',
									created_at = NOW()
								WHERE assignment_id = '$assignment_id'");
                            }
                        } else {
                            $email_status   = "F";
                        }
                        /* ----------
                         Send Email
                        ----------------------- */
                        if($email_status == "T") {
                            if($this->sysModel->getConfig()->email_status == 1) {
                                $qIncomingDetail    = DB::table("incoming_transmittal_detail")->select(DB::RAW("GROUP_CONCAT(sys_users.email) AS email_destination"), DB::RAW("GROUP_CONCAT(sys_users.full_name) AS full_name"), "incoming_transmittal.incoming_no"
                                                                                                        , DB::RAW("GROUP_CONCAT(document.document_no ORDER BY incoming_transmittal_detail.incoming_transmittal_detail_id ASC SEPARATOR '@') AS doc_no_listing")
                                                                                                        , DB::RAW("GROUP_CONCAT(document.document_title ORDER BY incoming_transmittal_detail.incoming_transmittal_detail_id ASC SEPARATOR '@') AS doc_title_listing")
                                                                                                        , DB::RAW("GROUP_CONCAT(ref_issue_status.name ORDER BY incoming_transmittal_detail.incoming_transmittal_detail_id ASC SEPARATOR '@') AS issue_status_listing")
                                                                                                        , DB::RAW("GROUP_CONCAT(ref_document_status.name ORDER BY incoming_transmittal_detail.incoming_transmittal_detail_id ASC SEPARATOR '@') AS doc_status_listing")
                                                                                                        , DB::RAW("GROUP_CONCAT(CONCAT(DATE_FORMAT(document.deadline, '%d'),'-', LEFT(DATE_FORMAT(document.deadline, '%M'),3),'-',DATE_FORMAT(document.deadline, '%y')) ORDER BY incoming_transmittal_detail.incoming_transmittal_detail_id ASC SEPARATOR '@') AS deadline_listing"))
                                                                                              ->join("document", "incoming_transmittal_detail.document_id", "document.document_id")
                                                                                              ->join("incoming_transmittal", "incoming_transmittal_detail.incoming_transmittal_id", "incoming_transmittal.incoming_transmittal_id")
                                                                                              ->join("ref_document_status", "incoming_transmittal_detail.document_status_id", "ref_document_status.document_status_id")
                                                                                              ->join("ref_issue_status", "incoming_transmittal_detail.issue_status_id", "ref_issue_status.issue_status_id")
                                                                                              ->join("assignment", "incoming_transmittal_detail.incoming_transmittal_detail_id", "assignment.incoming_transmittal_detail_id")
                                                                                              ->join("comment", "assignment.assignment_id", "comment.assignment_id")
                                                                                              ->join("sys_users", "comment.user_id", "sys_users.id")
                                                                                              ->where("incoming_transmittal_detail.incoming_transmittal_id", $row->incoming_transmittal_id)
                                                                                              ->groupBy("sys_users.full_name", "sys_users.email", "incoming_transmittal.incoming_no")
                                                                                              ->get();
                            
                                $dataclient["inc_no"]                  = "";
                                $dataclient["doc_inc_no"]              = "";
                                $dataclient["doc_no_listing"]          = "";
                                $dataclient["doc_title_listing"]       = "";
                                $dataclient["issue_status_listing"]    = "";
                                $dataclient["doc_status_listing"]      = "";
                                $dataclient["deadline_listing"]        = "";
                                $dataclient["title"] = "Document Review Notification";

                                foreach($qIncomingDetail as $i => $dataInc) {
                                    $title                          = "Document Review Notification " . $dataInc->incoming_no;
                                    $email_address                  = $dataInc->email_destination;
                                    $email_name                     = $dataInc->full_name;
                                    # ---------------
                                    $data["title"]                  = "Document Review Notification";
                                    $data["inc_no"]                 = $dataInc->incoming_no;
                                    $data["doc_no_listing"]         = $dataInc->doc_no_listing;
                                    $data["doc_title_listing"]      = $dataInc->doc_title_listing;
                                    $data["issue_status_listing"]   = $dataInc->issue_status_listing;
                                    $data["doc_status_listing"]     = $dataInc->doc_status_listing;
                                    # ---------------
                                    Mail::send('email.incoming-notification', $data, function($message) use ($title, $email_address, $email_name) {
                                        $mailLength     = explode(",", $email_address);
                                        $nameLength     = explode(",", $email_name);
                                        # ---------------
                                        for($i=0; $i<count($mailLength); $i++) {
                                            $message->to(str_replace(" ", "", $mailLength[$i]), str_replace(" ", "", $mailLength[$i]))->subject($title);
                                        }
                                        # ---------------
                                        $message->from(env("MAIL_USERNAME"), 'Automatic Mail System');
                                    });
                                    $characterExplode                       = $i == 0 ? "": "@";
                                    $dataclient["title"]                    = "Document Review Notification";
                                    $dataclient["inc_no"]                  .= " ".$dataInc->incoming_no;
                                    $dataclient["doc_inc_no"]              .= $characterExplode.$dataInc->incoming_no;
                                    $dataclient["doc_no_listing"]          .= $characterExplode.$dataInc->doc_no_listing;
                                    $dataclient["doc_title_listing"]       .= $characterExplode.$dataInc->doc_title_listing;
                                    $dataclient["issue_status_listing"]    .= $characterExplode.$dataInc->issue_status_listing;
                                    $dataclient["doc_status_listing"]      .= $characterExplode.$dataInc->doc_status_listing;
                                    $dataclient["deadline_listing"]        .= $characterExplode.$dataInc->deadline_listing;
                                }
                                $dataclient['logo_medco']               = public_path() . "/app/img/icon/logo_medco.png";
                                $dataclient['logo_hanochem']            = public_path() . "/app/img/icon/hanochem.png";
                                $dataclient['logo_kanan_tengah']        = public_path() . "/app/img/icon/logo_kanan_tengah.png";
                                $dataclient['logo_kanan_pojok']         = public_path() . "/app/img/icon/logo_kanan_pojok.png";
                                $dataclient['logo_kiri']                = public_path() . "/app/img/icon/hanochem.png";

                                $dataclient['from']                     = "CONSORTIUM HTS – CB – MTC";
                                $dataclient['name']                     = "Errin/Jumiah";
                                $dataclient['addressfrom']              = "Jalan Kyai Maja No. 1 Kebayoran Baru, Jakarta Selatan 12120 Phone : +6221-727-86837";
                                $dataclient['to']                       = "Medco E&P Natuna Ltd.";
                                $dataclient['attn']                     = "Bp. Doni L Hakim";
                                $dataclient['addressto']                = "SCBD Area Lot 11A JL Jendral Sudirman, Jakarta 12190 Indonesia";
                                $dataclient['transmittal']              = "";
                                $dataclient['issuedname']               = "Errin/Jumiah";
                                $dataclient['issueddate']               = $request->sender_date ? DB::SELECT("SELECT CONCAT(DATE_FORMAT('$receive', '%d'),'-', LEFT(DATE_FORMAT('$receive', '%M'),3),'-',DATE_FORMAT('$receive', '%y')) AS issueddate")[0]->issueddate : "";
                                $dataclient['issuedsiganture']          = "";
                                $dataclient['remarks']                  = "";
                                $this->printIcoming($dataclient);
                                //code...
                                $title                          = "Document Review Notification";
                                $email_address                  = "mukhnizam.rashid@forel-hanochem.com";
                                //$email_address                  = "Fuadi.Fuadi@energibiz.com";
                                $email_name                     = "FUADI";
                                Mail::send('email.empty-notification', $dataclient, function($message) use ($dataclient, $title, $email_address, $email_name){
                                    $message
                                    ->to($email_address, $email_name)
                                    ->to("arif@mitrafin.co.id", "Arif")
                                    ->subject($title);
//                                        ->attach(public_path('uploads/'.$dataclient["title"].'.pdf'))
//                                            ;
                                    $message->from(env("MAIL_USERNAME"), 'Automatic Mail System');
                                });
                            }
                        }
                    }
                }
            } else {
                $no_rand    = $request->incoming_no . '_REJECT_' . date("dmYHis");
                # ---------------
                DB::table($this->table)
                            ->where("incoming_transmittal_id", $request->id)
                            ->update([
                                "status"=>$request->status,
                                "incoming_no"=>$no_rand,
                            ]);
                /* ----------
                 Jika Reject
                ----------------------- */
                    if($this->sysModel->getConfig()->email_status == 1) {
                        $qIncomingDetail    = DB::table("incoming_transmittal_detail")->select(DB::RAW("GROUP_CONCAT(document.document_no ORDER BY incoming_transmittal_detail.incoming_transmittal_detail_id ASC SEPARATOR '@') AS doc_no_listing")
                                                                                                , DB::RAW("GROUP_CONCAT(document.document_title ORDER BY incoming_transmittal_detail.incoming_transmittal_detail_id ASC SEPARATOR '@') AS doc_title_listing")
                                                                                                , DB::RAW("GROUP_CONCAT(ref_issue_status.name ORDER BY incoming_transmittal_detail.incoming_transmittal_detail_id ASC SEPARATOR '@') AS issue_status_listing")
                                                                                                , DB::RAW("GROUP_CONCAT(ref_document_status.name ORDER BY incoming_transmittal_detail.incoming_transmittal_detail_id ASC SEPARATOR '@') AS doc_status_listing"))
                                                                                              ->join("document", "incoming_transmittal_detail.document_id", "document.document_id")
                                                                                              ->join("incoming_transmittal", "incoming_transmittal_detail.incoming_transmittal_id", "incoming_transmittal.incoming_transmittal_id")
                                                                                              ->join("ref_document_status", "incoming_transmittal_detail.document_status_id", "ref_document_status.document_status_id")
                                                                                              ->join("ref_issue_status", "incoming_transmittal_detail.issue_status_id", "ref_issue_status.issue_status_id")
                                                                                              ->where("incoming_transmittal_detail.incoming_transmittal_id", $request->id)
                                                                                              ->get();
                        # ---------------
                        foreach($qIncomingDetail as $dataInc) {                                                                    
                            $title                          = "Reject Notification " . $request->incoming_no;
                            $email_address                  = $request->vendor_email;
                            # ---------------
                            $data["title"]                  = $title;
                            $data["inc_no"]                 = $request->incoming_no;
                            $data["doc_no_listing"]         = $dataInc->doc_no_listing;
                            $data["doc_title_listing"]      = $dataInc->doc_title_listing;
                            $data["issue_status_listing"]   = $dataInc->issue_status_listing;
                            $data["doc_status_listing"]     = $dataInc->doc_status_listing;
                            $data["ket_reject"]             = $request->remark_approval;
                            # ---------------
                            Mail::send('email.incoming-reject-notification', $data, function($message) use ($title, $email_address) {
                                $message->to($email_address)->subject($title);
                                # ---------------
                                $message->from(env("MAIL_USERNAME"), 'Automatic Mail System');
                            });
                        }
                    }
            }
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("APPROVE INCOMING (" . $request->id . ")", Auth::user()->id, "");
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
            $id    = $this->logModel->createError($e->getMessage(), "APPROVE INCOMING FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }

    public function printIcoming($data) {
        $pdf = PDF::loadView('email.empty', $data);
        $pdf->setPaper('A4', 'landscape');
        Storage::disk("uploads")->put($data['title'].'.pdf', $pdf->output());
        // return $pdf->stream($data['title'].'.pdf',array('Attachment'=>0));

    }

    public function deleteReceipt($id) {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table($this->table)
                        ->where("incoming_transmittal_id", $id)
                        ->update([
                            "receipt_url"=>"",
                            "receipt_file"=>"",
                            "updated_by"=>Auth::user()->id,
                            "updated_at"=>Carbon::now()->toDateTimeString(),
                        ]);
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("DELETE RECEIPT (" . $id . ")", Auth::user()->id, "");
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "DELETE RECEIPT", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }

    public function updateIncoming($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            $user_id    = Auth::user()->id;
            $receive    = setYMD($request->receive_date, "/");
            $id         = $request->incoming_transmittal_id;
            # ------------------------
            DB::table($this->table)
                        ->where("incoming_transmittal_id", $id)
                        ->update([
                            "incoming_no"=>$request->incoming_no,
                            "receive_date"=>(!empty($request->receive_date)) ? setYMD($request->receive_date, "/") : null,
                            "sender_date"=>(!empty($request->sender_date)) ? setYMD($request->sender_date, "/") : null,
                            "subject"=>$request->subject,
                            "remark"=>$request->remark,
                            "return_date_plan"=>(!empty($request->return_date_plan)) ? setYMD($request->return_date_plan, "/") : null,
                            "return_date_actual"=>(!empty($request->return_date_actual)) ? setYMD($request->return_date_actual, "/") : null,
                            "updated_by"=>Auth::user()->id,
                            "updated_at"=>Carbon::now()->toDateTimeString(),
                        ]);
            /* ----------
             Upload File
            ----------------------- */
                $file_url   = "";
                $file_name  = "";
                # ------------------------
                if(!empty($request->receipt)) {
                    $default_file   = $request->receipt;
                    $file_url       = DOCUMENT_DIR . "/" . $id . "/";
                    # ------------------------
                    $file_content   = file_get_contents($request->receipt->getRealPath());
                    $file           = $request->file('receipt')->getClientOriginalName();
                    $file_name      = pathinfo($file,PATHINFO_FILENAME);
                    $file_name      = $file_name . "_RCV_". str_replace(".", "_", str_replace("/", "_", $request->incoming_no)). "_" . date("YmdHis") . "." . $default_file->getClientOriginalExtension();
                    # ------------------------
                    Storage::disk("uploads")->put($file_url . $file_name, $file_content);
                    # ------------------------
                    DB::table($this->table)
                            ->where("incoming_transmittal_id", $id)
                            ->update([
                                "receipt_url"=>$file_url,
                                "receipt_file"=>$file_name,
                            ]);
                }
                # ------------------------
                $file_url   = "";
                $file_name  = "";
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("UPDATE INCOMING (" . $id . ")", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "UPDATE INCOMING FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }

    public function getSummaryReport($params) {
        try {
            list($receive_date_start, $receive_date_end) = explode("|", base64_decode($params));
            # -----------------
            $query  = DB::table($this->table)
                            ->select("$this->table.incoming_no", "$this->table.receive_date", "$this->table.sender_date", "$this->table.subject", "$this->table.remark", "$this->table.return_date_plan", "$this->table.return_date_actual", DB::RAW("COUNT(incoming_transmittal_detail.document_id) AS number_of_documents"))
                            ->join("incoming_transmittal_detail", "$this->table.incoming_transmittal_id", "incoming_transmittal_detail.incoming_transmittal_id")
                            ->groupBy("$this->table.incoming_no", "$this->table.receive_date", "$this->table.sender_date", "$this->table.subject", "$this->table.remark", "$this->table.return_date_plan", "$this->table.return_date_actual")
                            ->orderBy("$this->table.receive_date");

            if($receive_date_start != '') {
                if($receive_date_end != '') {
                    $query->where("$this->table.receive_date", ">=", setYMD($receive_date_start, "/"));
                    $query->where("$this->table.receive_date", "<=", setYMD($receive_date_end, "/"));
                } else {
                    $query->where("$this->table.receive_date", setYMD($receive_date_start, "/"));
                }
                
            }

            return $query;
        } catch (\Exception $e) {
            return array("status"=>false, "error_log"=>$e->getMessage());
        }
    }

    public function getDetailReport($params) {
        try {
            list($receive_date_start, $receive_date_end) = explode("|", base64_decode($params));
            # -----------------
            $query  = DB::table($this->table)
                            ->select("$this->table.incoming_no", "$this->table.receive_date", "$this->table.sender_date", "$this->table.subject", "$this->table.remark", "$this->table.return_date_plan", "$this->table.return_date_actual", "document.document_no AS document_number", "document.document_title", "ref_document_type.name AS document_type"
                                    , "incoming_transmittal_detail.remark AS document_remark", "project.project_name", "ref_document_status.name AS document_status_name", "ref_issue_status.name AS issue_status_name", "ref_return_status.name AS return_status_name")
                            ->join("incoming_transmittal_detail", "$this->table.incoming_transmittal_id", "incoming_transmittal_detail.incoming_transmittal_id")
                            ->join("document", "incoming_transmittal_detail.document_id", "document.document_id")
                            ->leftJoin("ref_document_type", "document.document_type_id", "ref_document_type.document_type_id")
                            ->leftJoin("project", "document.project_id", "project.project_id")
                            ->leftJoin("ref_document_status", "incoming_transmittal_detail.document_status_id", "ref_document_status.document_status_id")
                            ->leftJoin("ref_issue_status", "incoming_transmittal_detail.issue_status_id", "ref_issue_status.issue_status_id")
                            ->leftJoin("ref_return_status", "incoming_transmittal_detail.return_status_id", "ref_return_status.return_status_id")
                            ->orderBy("$this->table.receive_date", "ASC")
                            ->orderBy("$this->table.incoming_no", "ASC");

            if($receive_date_start != '') {
                if($receive_date_end != '') {
                    $query->where("$this->table.receive_date", ">=", setYMD($receive_date_start, "/"));
                    $query->where("$this->table.receive_date", "<=", setYMD($receive_date_end, "/"));
                } else {
                    $query->where("$this->table.receive_date", setYMD($receive_date_start, "/"));
                }
                
            }

            return $query;
        } catch (\Exception $e) {
            return array("status"=>false, "error_log"=>$e->getMessage());
        }
    }

    public function attachItemIDC($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table("incoming_transmittal_detail_temp")
                ->where("document_id", $request->document_id)
                ->where("issue_status_id", 1)
                ->where("document_status_id", 94)
                ->where("created_by", Auth::user()->id)
                ->delete();
            /* ----------
             Upload File
            ----------------------- */
            $default_file   = $request->document_file;
            $default_crs    = $request->document_crs;
            $file_crs       = "";
            $file_url       = DOCUMENT_TEMP_DIR . '/' . Auth::user()->id . "/";
            # ------------------------
                $file_content   = file_get_contents($request->document_file->getRealPath());
                $file           = $request->file('document_file')->getClientOriginalName();
                $file_name      = pathinfo($file,PATHINFO_FILENAME);
                $file_name      = $file_name . "_" . date("YmdHis") . "." . $default_file->getClientOriginalExtension();
                # ------------------------
                Storage::disk("uploads")->put($file_url . $file_name, $file_content);
            # ------------------------
            if($request->document_crs != "") {
                $file_content_crs   = file_get_contents($request->document_crs->getRealPath());
                $file               = $request->file('document_crs')->getClientOriginalName();
                $file_crs           = pathinfo($file,PATHINFO_FILENAME);
                $file_crs           = $file_crs . "_" . date("YmdHis") . "." . $default_crs->getClientOriginalExtension();
                # ------------------------
                Storage::disk("uploads")->put($file_url . $file_crs, $file_content_crs);
            }
            # ------------------------
            $id     = DB::table("incoming_transmittal_detail_temp")
                            ->insertGetId([
                                "document_id"=>$request->document_id,
                                "document_no"=>(!empty($request->document_no)) ? $request->document_no : "",
                                "document_title"=>(!empty($request->document_name)) ? $request->document_name : "",
                                "document_url"=>$file_url,
                                "document_file"=>$file_name,
                                "document_crs"=>$file_crs,
                                "remark"=>$request->remark,
                                "issue_status_id"=> 1,
                                "document_status_id"=> 94,
                                "project_id"=>$request->project_id,
                                "created_by"=>Auth::user()->id
                            ]);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            # ------------------------
            $this->logModel->createError($e->getMessage(), "ATTACH DOCUMENT IN TEMP TABLE", "");
            # ------------------------
            return array("status"=>false, "id"=>0, "message"=>$e->getMessage());
        }
    }

    public function deleteItemIDC($id) {
        DB::beginTransaction();
        # ------------------------
        try {
            $id     = DB::table("incoming_transmittal_detail_temp")->where("incoming_transmittal_detail_temp_id", $id)->where("created_by", Auth::user()->id)->where("issue_status_id", STATUS_ONLY_IDC)->delete();
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            # ------------------------
            $this->logModel->createError($e->getMessage(), "DELETE ITEM IN TEMP", "");
            # ------------------------
            return array("status"=>false, "id"=>0);
        }
    }

    public function getLastNumberIDC(){
        $query  = DB::table("incoming_transmittal")->select('incoming_no')->where('incoming_no', 'like', '%IDC%')->orderBy('incoming_transmittal_id', 'DESC')->first();
        
        $format_number = "IDC-";
        if ($query){
            $output = substr($query->incoming_no,4) + 1; 
            return $format_number.sprintf("%04d", $output);
        }else{
            return $format_number."0001";
        }   
    }
}
