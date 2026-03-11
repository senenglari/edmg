<?php

namespace App\Http\Controllers\Transmittal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use View;
use Auth;
use Validator;
use Hash;
use Yajra\Datatables\Datatables;
use App\User;
use App\Model\UserManagement\MenuModel;
use App\Model\UserManagement\UserModel;
use App\Model\Reference\ReferenceModel;
use App\Model\Transmittal\IncomingModel;
use App\Model\Document\DocumentModel;
use App\Model\Sys\SysModel;
use App\Model\Sys\LogModel;
use App\Helpers\Constants;
use ZipArchive;
use File;




class IncomingController extends Controller
{
    protected $PROT_SideMenu, $PROT_Parent, $PROT_ModuleId, $PROT_ModuleName;

    public function __construct(Request $request) {
        
     
        # ---------------
        $uri                      = getUrl() . "/index";
        # ---------------
        $this->qMenu              = new MenuModel;
        $this->qUser              = new UserModel;
        $this->qReference         = new ReferenceModel;
        $this->qIncoming          = new IncomingModel;
        $this->qDocument          = new DocumentModel;
        $this->sysModel           = new SysModel;
        $this->logModel           = new LogModel;
        # ---------------
        $rs                       = $this->qMenu->getParentMenu($uri);
        # ---------------
        $this->PROT_Parent        = (count($rs) > 0) ? $rs[0]->parent_name : '';
        $this->PROT_ModuleName    = (count($rs) > 0) ? $rs[0]->name : '';
        $this->PROT_ModuleId      = (count($rs) > 0) ? $rs[0]->id : '';
        $this->isVendor           = (getUrl() == "/vendor_outgoing") ? "YES" : "NO";
        # ---------------
        View::share(array("SHR_Parent"=>$this->PROT_Parent, "SHR_Module"=>$this->PROT_ModuleName, "SHR_ModuleId"=>$this->PROT_ModuleId));
    }




// Hanya load dropdown revision dari tabel yang sudah ada
public function getDocumentStatusByIssueOld($issue_status_id)
{
    $revisions = DB::table('ref_document_status as a')
        ->join('ref_issue_status as b', 'a.issue_status_id', '=', 'b.issue_status_id')
        ->select('a.document_status_id as id', 'a.name', 'b.name as issue_status_name')
        ->where('a.status', 1)
        ->where('a.issue_status_id', $issue_status_id) // hanya yang cocok dengan issue status yang dipilih
        ->orderByRaw('CAST(SUBSTRING(a.name, 2) AS UNSIGNED) ASC') // urut A0, A1, A2, ... A22 dst
        ->get();

    return response()->json([
        'data' => $revisions->toArray()
    ]);
}

public function getDocumentStatusByIssue($issue_status_id)
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


    public function index(Request $request)
    {
      
        try {
            $data["title"]            = ucwords(strtolower($this->PROT_ModuleName));
            $data["parent"]           = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]         = ($this->isVendor == "YES") ? "/vendor_outgoing/index" : "/incoming/index";
            $data["active_page"]      = (empty($page)) ? 1 : $page;
            $data["offset"]           = (empty($data["active_page"])) ? 0 : ($data["active_page"]-1) * Auth::user()->perpage;
            /* ----------
             Source
            ----------------------- */

            # ---------------
            $data["filtered_info"]  = array();
            # ---------------
            $data["action"]         = $this->qMenu->getActionMenu(Auth::user()->id, $this->PROT_ModuleId);
            /* ----------
             Table header
            ----------------------- */
            if($this->isVendor == "YES") {
                $data["table_header"]   = array(array("label"=>"ID"
                                                    ,"name"=>"incoming_transmittal_id"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"center"
                                                          ,"item-format"=>"checkbox"
                                                            ,"item-class"=>""
                                                              ,"width"=>"5%"
                                                                ,"add-style"=>""),
                                            array("label"=>($this->isVendor == "YES") ? "Outgoing No" : "Incoming No"
                                                    ,"name"=>"incoming_no"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"center"
                                                          ,"item-format"=>"normal"
                                                            ,"item-class"=>""
                                                              ,"width"=>"20%"
                                                                ,"add-style"=>""),
                                            array("label"=>($this->isVendor == "YES") ? "Sending Date ": "Receive Date"
                                                    ,"name"=>"rec_date"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"center"
                                                          ,"item-format"=>"normal"
                                                            ,"item-class"=>""
                                                              ,"width"=>"10%"
                                                                ,"add-style"=>""),
                                            array("label"=>"Subject"
                                                    ,"name"=>"subject"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"left"
                                                          ,"item-format"=>"normal"
                                                            ,"item-class"=>""
                                                              ,"width"=>""
                                                                ,"add-style"=>""),
                                            array("label"=>"Number Of Documents"
                                                    ,"name"=>"unit"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"center"
                                                          ,"item-format"=>"normal"
                                                            ,"item-class"=>""
                                                              ,"width"=>"20%"
                                                                ,"add-style"=>""),
                                          array("label"=>"Status"
                                                    ,"name"=>"vendor_status_code"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"center"
                                                          ,"item-format"=>"flag"
                                                            ,"item-class"=>""
                                                              ,"width"=>"8%"
                                                                ,"add-style"=>""));
            } else {
                $selectVendor           = $this->qReference->getSelectVendor();
                $data["table_header"]   = array(array("label"=>"ID"
                                                    ,"name"=>"incoming_transmittal_id"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"center"
                                                          ,"item-format"=>"checkbox"
                                                            ,"item-class"=>""
                                                              ,"width"=>"5%"
                                                                ,"add-style"=>""),
                                            array("label"=>($this->isVendor == "YES") ? "Outgoing No" : "Incoming No"
                                                    ,"name"=>"incoming_no"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"center"
                                                          ,"item-format"=>"normal"
                                                            ,"item-class"=>""
                                                              ,"width"=>"20%"
                                                                ,"add-style"=>""),
                                            array("label"=>($this->isVendor == "YES") ? "Sending Date ": "Receive Date"
                                                    ,"name"=>"rec_date"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"center"
                                                          ,"item-format"=>"normal"
                                                            ,"item-class"=>""
                                                              ,"width"=>"10%"
                                                                ,"add-style"=>""),
                                            array("label"=>"Subject"
                                                    ,"name"=>"subject"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"left"
                                                          ,"item-format"=>"normal"
                                                            ,"item-class"=>""
                                                              ,"width"=>""
                                                                ,"add-style"=>""),
                                            array("label"=>"Vendor"
                                                    ,"name"=>"vendor_name"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"left"
                                                          ,"item-format"=>"normal"
                                                            ,"item-class"=>""
                                                              ,"width"=>""
                                                                ,"add-style"=>""),
                                            array("label"=>"Return Plan Date"
                                                    ,"name"=>"deadline_return"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"center"
                                                          ,"item-format"=>"normal"
                                                            ,"item-class"=>""
                                                              ,"width"=>"12%"
                                                                ,"add-style"=>""),
                                            array("label"=>"Status"
                                                    ,"name"=>"status_code"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"center"
                                                          ,"item-format"=>"flag"
                                                            ,"item-class"=>""
                                                              ,"width"=>"8%"
                                                                ,"add-style"=>""));
            }
            # ---------------
            $data["query"]         = $this->qIncoming->getCollections();
            $data["select"]        = $data["query"]["data"];
            $data["pagging"]       = getPagging($data["select"]);

//            return $this->qIncoming->getCollections();

            # ---------------
            # Advance Search
            # ---------------
            if(isset($request->module_id)) {
                $incoming_no       = ($request->incoming_no != "") ? session(["SES_SEARCH_INCOMING_NO" => $request->incoming_no]) : $request->session()->forget("SES_SEARCH_INCOMING_NO");
                $receive_date      = ($request->receive_date  != "") ? session(["SES_SEARCH_INCOMING_RECEIVE" => $request->receive_date]) : $request->session()->forget("SES_SEARCH_INCOMING_RECEIVE");
                $subject           = ($request->subject  != "") ? session(["SES_SEARCH_INCOMING_SUBJECT" => $request->subject]) : $request->session()->forget("SES_SEARCH_INCOMING_SUBJECT");
                # ---------------
                if($this->isVendor == "YES") {
                    return redirect("/vendor_outgoing/index");
                } else {
                    $vendor_id           = ($request->vendor_id  != "0") ? session(["SES_SEARCH_INCOMING_VENDOR" => $request->vendor_id]) : $request->session()->forget("SES_SEARCH_INCOMING_VENDOR");
                    return redirect("/incoming/index");
                }
            }
            # ---------------
            if($request->session()->has("SES_SEARCH_INCOMING_NO")) {
                if($this->isVendor == "YES") {
                    array_push($data["filtered_info"], "OUTGOING NUMBER");
                } else {
                    array_push($data["filtered_info"], "INCOMING NUMBER");
                }
            }
            # ---------------
            if($request->session()->has("SES_SEARCH_INCOMING_SUBJECT")) {
                array_push($data["filtered_info"], "SUBJECT");
            }
            # ---------------
            if($request->session()->has("SES_SEARCH_INCOMING_RECEIVE")) {
                array_push($data["filtered_info"], "RECIVE DATE");
            }
            if ($request->session()->has("SES_SEARCH_INCOMING_VENDOR")) {
                if ($request->session()->get("SES_SEARCH_INCOMING_VENDOR") != "0") {
                    array_push($data["filtered_info"], "VENDOR");
                }
            }
            # ---------------
            $data["adv_search"]    = true;
            $data["hide_simple_search"] = true;
            # ---------------
            $data["fields"][]      = form_hidden(array("name"=>"module_id", "label"=>"Module ID", "value"=>"INCOMING"));
            $data["fields"][]      = form_search_text(array("name"=>"incoming_no", "label"=>($this->isVendor == "YES") ? "Outgoing Number" : "Incoming Number", "value"=>($request->session()->has("SES_SEARCH_INCOMING_NO")) ? $request->session()->get("SES_SEARCH_INCOMING_NO") : ""));
            $data["fields"][]      = form_search_text(array("name"=>"subject", "label"=>"Subject", "value"=>($request->session()->has("SES_SEARCH_INCOMING_SUBJECT")) ? $request->session()->get("SES_SEARCH_INCOMING_SUBJECT") : ""));
            $data["fields"][]      = form_search_datepicker(array("name"=>"receive_date", "label"=>"Receive Date", "value"=>($request->session()->has("SES_SEARCH_INCOMING_RECEIVE")) ? $request->session()->get("SES_SEARCH_INCOMING_RECEIVE") : ""));
            if($this->isVendor != "YES") {
              $data["fields"][]      = form_search_select(array("name" => "vendor_id", "label" => "Vendor", "source" => $selectVendor,  "withnull" => "yes", "value" => ($request->session()->has("SES_SEARCH_INCOMING_VENDOR")) ? $request->session()->get("SES_SEARCH_INCOMING_VENDOR") : ""));
            }
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_search", "label"=>"&nbsp;&nbsp;Search&nbsp;&nbsp;"));
            $data["buttons"][]     = form_action_button(array("name"=>"button_clear", "label"=>"&nbsp;&nbsp;Clear&nbsp;&nbsp;", "url"=>($this->isVendor == "YES") ? "/vendor_outgoing/unfilter" : "/incoming/unfilter"));
            # ---------------
            
            //echo 'bb';die;
            return view("default.list", $data);
        } catch (\Exception $e) {
            throw $e;
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            # ---------------
            return view("error.405");
        }
    }
    
    
    
// app/Http/Controllers/Transmittal/IncomingController.php

public function showIncomingAssignment($documentId)
{
    // dokumen harus ada dan status IFC/IFA/IFI (external flow)
    $doc = DB::table('document')->where('document_id', $documentId)->first();

    // jika tidak ditemukan, kembali ke daftar dengan pesan
    if (!$doc) {
        return redirect()->route('incoming_company.index')
                         ->with('error_message', 'Dokumen tidak ditemukan.');
    }

    // Accept external flow statuses: IFC (1), IFA (3), IFI (7)
    $externalStatusIds = [STATUS_IFC, STATUS_IFA, STATUS_IFI];
    if (!in_array($doc->issue_status_id, $externalStatusIds)) {
        return redirect()->route('incoming_company.index')
                         ->with('error_message', 'Dokumen bukan external flow (bukan IFC/IFA/IFI).');
    }

    // debug logging (bisa dihapus saat sudah stabil)
    \Log::info('ShowIncomingAssignment called', [
        'documentId' => $documentId,
        'doc_exists' => true,
        'issue_status_id' => $doc->issue_status_id
    ]);

    // === USERS LENGKAP UNTUK MODAL ADD USERS ===
    $users = DB::table('sys_users')
        ->leftJoin('ref_department', 'sys_users.department_id', '=', 'ref_department.department_id')
        ->leftJoin('ref_discipline', 'sys_users.discipline_id', '=', 'ref_discipline.discipline_id')
        ->leftJoin('ref_position', 'sys_users.position_id', '=', 'ref_position.position_id')
        ->where('sys_users.user_status', 1)
        ->select(
            'sys_users.id',
            'sys_users.full_name',
            'ref_department.name as department_name',
            'ref_discipline.name as discipline_name',
            'ref_position.name as position_name'
        )
        ->orderBy('sys_users.full_name')
        ->get();

    // === ROLE KHUSUS Incoming Company ===
    $selectRole = [
        ['id' => 'RESPONSIBLE', 'name' => 'RESPONSIBLE'],
        ['id' => 'OWNER',          'name' => 'OWNER'],
        ['id' => 'APPROVER_COMPANY', 'name' => 'APPROVER COMPANY'],
    ];

    // === DATA ASSIGNMENT YANG SUDAH ADA (untuk tabel) ===
   // === DATA ASSIGNMENT YANG SUDAH ADA (untuk tabel) ===
   
   // Debug: Log document info
   \Log::info('Document Info for Assignment: ' . json_encode([
       'document_id' => $documentId,
       'doc_exists' => $doc ? true : false,
       'doc_issue_status_id' => $doc->issue_status_id ?? null
   ]));
   
   // Debug: Log assignment header
   $assignmentHeader = DB::table('assignment')
       ->where('document_id', $documentId)
       ->where('status_nonaktif', 0)
       ->first();
   \Log::info('Assignment Header: ' . json_encode($assignmentHeader));
   
$comment = DB::table('comment as c')
    ->join('sys_users as u', 'c.user_id', '=', 'u.id')
    ->leftJoin('ref_department as d', 'u.department_id', '=', 'd.department_id')
    ->leftJoin('ref_discipline as ds', 'u.discipline_id', '=', 'ds.discipline_id')
    ->leftJoin('ref_position as p', 'u.position_id', '=', 'p.position_id')
    ->whereExists(function ($q) use ($documentId) {
        $q->select(DB::raw(1))
          ->from('assignment as a')
          ->whereColumn('a.assignment_id', 'c.assignment_id')
          ->where('a.document_id', $documentId)
          ->where('a.status_nonaktif', 0);
    })
    ->whereIn('c.role', ['RESPONSIBLE', 'OWNER', 'APPROVER_COMPANY']) // Filter untuk external flow roles
    ->where('c.status_nonaktif', 0) // Tambah filter non-aktif
    ->select(
        'c.comment_id',              // ← primary key yang benar
        'c.user_id',
        'c.role',
        'c.start_date',
        'c.end_date',
        'c.status',
        'c.order_no',
        'u.full_name',
        'd.name as department_name',
        'ds.name as discipline_name',
        'p.name as position_name'
        // HAPUS 'c.comment_temp_id' karena tidak ada di tabel comment
    )
    ->orderBy('c.order_no')
    ->get();
    
    // Debug: Log comment results
    \Log::info('Comment Query Results: ' . json_encode([
        'count' => $comment->count(),
        'data' => $comment->toArray()
    ]));
    
    // Fallback: Jika tidak ada assignment, coba query langsung ke comment tanpa whereExists
    if ($comment->count() == 0) {
        \Log::info('Trying fallback query without whereExists...');
        
        $comment = DB::table('comment as c')
            ->join('sys_users as u', 'c.user_id', '=', 'u.id')
            ->leftJoin('ref_department as d', 'u.department_id', '=', 'd.department_id')
            ->leftJoin('ref_discipline as ds', 'u.discipline_id', '=', 'ds.discipline_id')
            ->leftJoin('ref_position as p', 'u.position_id', '=', 'p.position_id')
            ->join('assignment as a', 'a.assignment_id', '=', 'c.assignment_id')
            ->where('a.document_id', $documentId)
            ->where('a.status_nonaktif', 0)
            ->whereIn('c.role', ['RESPONSIBLE', 'OWNER', 'APPROVER_COMPANY'])
            ->where('c.status_nonaktif', 0)
            ->select(
                'c.comment_id',
                'c.user_id',
                'c.role',
                'c.start_date',
                'c.end_date',
                'c.status',
                'c.order_no',
                'u.full_name',
                'd.name as department_name',
                'ds.name as discipline_name',
                'p.name as position_name'
            )
            ->orderBy('c.order_no')
            ->get();
            
        \Log::info('Fallback Query Results: ' . json_encode([
            'count' => $comment->count(),
            'data' => $comment->toArray()
        ]));
    }

    // variabel lain yang dibutuhkan blade
    $title                = 'Assignment (Incoming Company) - '.($doc->document_no ?? $documentId);
    $statusAssignment     = '';
    $idDoc                = $documentId;
    $status_document      = $doc->status ?? 0;
    $document_id          = $documentId;
    $assignment_id        = 0;
    $status_nonaktif      = $doc->status_nonaktif ?? 0;

    $form_act = route('incoming_company.assignment.store', $documentId);

    return view('incoming/assignment_ic', compact(
        'title','comment','users','selectRole',
        'statusAssignment','idDoc',
        'status_document','document_id','assignment_id','status_nonaktif',
        'form_act'
    ));
}




public function storeIncomingAssignment(Request $request, $documentId)
{
    // Debug: Tampilkan request data sebelum validation
    \Log::info('Assignment Request Before Validation: ' . json_encode($request->all()));
    
    $request->validate([
        'user_id' => 'required',
        'role' => 'required|in:RESPONSIBLE,OWNER,APPROVER_COMPANY',
    ]);

    $doc = DB::table('document')->where('document_id', $documentId)->first();
    abort_if(!$doc, 404, 'Dokumen tidak ditemukan.');
    
    // Accept external flow statuses: IFC (1), IFA (3), IFI (7)
    $externalStatusIds = [STATUS_IFC, STATUS_IFA, STATUS_IFI];
    abort_if(!in_array($doc->issue_status_id, $externalStatusIds), 403, 'Dokumen bukan external flow (bukan IFC/IFA/IFI).');

    DB::transaction(function () use ($request, $doc) {

        // Ambil atau buat assignment header
        $assignmentId = DB::table('assignment')
            ->where('document_id', $doc->document_id)
            ->where('incoming_transmittal_detail_id', (int) ($doc->incoming_transmittal_detail_id ?? 0))
            ->where('status_nonaktif', 0)
            ->value('assignment_id');

        if (!$assignmentId) {
            $assignmentId = DB::table('assignment')->insertGetId([
                'document_id'                    => (int) $doc->document_id,
                'incoming_transmittal_detail_id' => (int) ($doc->incoming_transmittal_detail_id ?? 0),
                'status_nonaktif'                => 0,
                'created_by'                     => Auth::id() ?? 0,
                'created_at'                     => now(),
            ]);
        }

// print_r($request->role);
// print_r($request->user_id);
//die;

        // Debug: Tampilkan request data
        \Log::info('Assignment Request Data: ' . json_encode($request->all()));
        
        // Simpan user - handle multiple users
        if (is_array($request->user_id)) {
            // Multiple users selected
            foreach ($request->user_id as $idx => $user_id) {
                $role = is_array($request->role) ? ($request->role[$idx] ?? 'RESPONSIBLE') : $request->role;
                
                DB::table('comment')->insert([
                    'assignment_id'   => (int) $assignmentId,
                    'user_id'         => (int) $user_id,
                    'role'            => $role,
                    'start_date'      => null,
                    'end_date'        => null,
                    'remark'          => null,
                    'issue_status_id' => 0,
                    'return_status_id'=> 0,
                    'status'          => 10,
                    'order_no'        => 1,
                    'created_by'      => Auth::id() ?? 0,
                    'created_at'      => now(),
                    'status_nonaktif' => 0,
                ]);
            }
        } else {
            // Single user selected
            DB::table('comment')->insert([
                'assignment_id'   => (int) $assignmentId,
                'user_id'         => (int) $request->user_id,
                'role'            => $request->role,
                'start_date'      => null,
                'end_date'        => null,
                'remark'          => null,
                'issue_status_id' => 0,
                'return_status_id'=> 0,
                'status'          => 10,
                'order_no'        => 1,
                'created_by'      => Auth::id() ?? 0,
                'created_at'      => now(),
                'status_nonaktif' => 0,
            ]);
        }
    });

//echo "www";
//die;
    return redirect()->route('incoming_company.index')
        ->with('success_message', 'Assignment (Incoming Company) berhasil disimpan.');
}  
    
    
 
 
// app/Http/Controllers/Transmittal/IncomingController.php

/**
 * FLOW EXTERNAL - 3 Level Assignment
 * RESPONSIBLE (viewer) -> OWNER (reviewer) -> APPROVER (final approval)
 */
public function createExternalFlow(Request $request)
{
    try {
        DB::beginTransaction();
        
        // 1. Create transmittal company record
        $transmittalId = DB::table('incoming_transmittal')->insertGetId([
            'incoming_no' => $request->incoming_no,
            'vendor_id' => $request->vendor_id,
            'project_id' => $request->project_id,
            'subject' => $request->subject,
            'content' => $request->content,
            'sender_date' => now(),
            'receive_date' => now(),
            'status' => 1, // Active
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);
        
        // 2. Process documents from temp table
        $tempDocuments = DB::table('incoming_transmittal_detail_temp')
            ->where('created_by', Auth::id())
            ->get();
            
        foreach ($tempDocuments as $doc) {
            // Insert to incoming_transmittal_detail
            $detailId = DB::table('incoming_transmittal_detail')->insertGetId([
                'incoming_transmittal_id' => $transmittalId,
                'document_id' => $doc->document_id,
                'document_no' => $doc->document_no,
                'document_title' => $doc->document_title,
                'document_url' => $doc->document_url,
                'document_file' => $doc->document_file,
                'document_crs' => $doc->document_crs,
                'vendor_id' => $doc->vendor_id,
                'project_id' => $doc->project_id,
                'issue_status_id' => $doc->issue_status_id, // IFC, IFA, IFI, atau IFI
                'document_status_id' => $doc->document_status_id,
                'return_status_id' => 1,
                'remark' => $doc->remark,
                'created_by' => Auth::id(),
                'created_at' => now(),
            ]);
            
            // 3. Create assignment header
            $assignmentId = DB::table('assignment')->insertGetId([
                'document_id' => $doc->document_id,
                'incoming_transmittal_detail_id' => $detailId,
                'status_nonaktif' => 0,
                'created_by' => Auth::id(),
                'created_at' => now(),
            ]);
            
            // 4. Auto assign RESPONSIBLE users (viewer role)
            $responsibleUsers = DB::table('sys_users')
                ->where('role', 'RESPONSIBLE')
                ->where('status', 1)
                ->get();
                
            foreach ($responsibleUsers as $user) {
                DB::table('comment')->insert([
                    'assignment_id' => $assignmentId,
                    'user_id' => $user->id,
                    'role' => 'RESPONSIBLE',
                    'start_date' => now(),
                    'end_date' => null,
                    'remark' => 'Auto assigned as viewer',
                    'issue_status_id' => 0,
                    'return_status_id' => 0,
                    'status' => 1, // Active
                    'order_no' => 1,
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                    'status_nonaktif' => 0,
                ]);
            }
        }
        
        // 5. Clear temp table
        DB::table('incoming_transmittal_detail_temp')
            ->where('created_by', Auth::id())
            ->delete();
            
        DB::commit();
        
        return redirect()->route('incoming_company.index')
            ->with('success_message', 'External flow transmittal created successfully');
            
    } catch (\Exception $e) {
        DB::rollback();
        return redirect()->back()
            ->with('error_message', 'Error creating external flow: ' . $e->getMessage());
    }
}

/**
 * Check if all RESPONSIBLE users have completed their review
 * If yes, auto-assign to OWNER users
 */
public function checkResponsibleCompletion($documentId)
{
    $document = DB::table('document')->where('document_id', $documentId)->first();
    $assignment = DB::table('assignment')
        ->where('document_id', $documentId)
        ->where('status_nonaktif', 0)
        ->first();
        
    if (!$document || !$assignment) {
        return false;
    }
    
    // Check if all RESPONSIBLE users have commented (use comment role)
    $responsibleCount = DB::table('comment')
        ->where('assignment_id', $assignment->assignment_id)
        ->where('role', 'RESPONSIBLE')
        ->where('status', 2) // Completed
        ->count();
        
    // total responsible users recorded in comments
    $totalResponsible = DB::table('comment')
        ->where('assignment_id', $assignment->assignment_id)
        ->where('role', 'RESPONSIBLE')
        ->distinct('user_id')
        ->count('user_id');
        
    // If all responsible users completed, assign to OWNER
    if ($responsibleCount >= $totalResponsible) {
        $ownerUsers = DB::table('sys_users')
            ->where('role', 'OWNER')
            ->where('status', 1)
            ->get();
            
        foreach ($ownerUsers as $user) {
            DB::table('comment')->insert([
                'assignment_id' => $assignment->assignment_id,
                'user_id' => $user->id,
                'role' => 'OWNER',
                'start_date' => now(),
                'end_date' => null,
                'remark' => 'Auto assigned after RESPONSIBLE completion',
                'issue_status_id' => 0,
                'return_status_id' => 0,
                'status' => 1, // Active
                'order_no' => 2,
                'created_by' => Auth::id(),
                'created_at' => now(),
                'status_nonaktif' => 0,
            ]);
        }
        
        return true;
    }
    
    return false;
}

/**
 * Check if all OWNER users have completed their review
 * If yes, auto-assign to APPROVER users
 */
public function checkOwnerCompletion($documentId)
{
    $document = DB::table('document')->where('document_id', $documentId)->first();
    $assignment = DB::table('assignment')
        ->where('document_id', $documentId)
        ->where('status_nonaktif', 0)
        ->first();
        
    if (!$document || !$assignment) {
        return false;
    }
    
    // Check if all OWNER users have commented (use comment rows, sys_users has no role field)
    $ownerCount = DB::table('comment')
        ->where('assignment_id', $assignment->assignment_id)
        ->where('role', 'OWNER')
        ->where('status', 2) // Completed
        ->count();
        
    // total owners originally assigned to this document
    $totalOwner = DB::table('comment')
        ->where('assignment_id', $assignment->assignment_id)
        ->where('role', 'OWNER')
        ->distinct('user_id')
        ->count('user_id');
        
    // If all owner users completed, assign to APPROVER COMPANY
    if ($totalOwner > 0 && $ownerCount >= $totalOwner) {
        // advance backdoor stage to Approver (5)
        DB::table('document')->where('document_id',$documentId)->update(['note_backdoor'=>'5']);

        // note: approver assignment removed to avoid sys_users.role lookup
    // stage already moved to 5 above when owners complete
        
        return true;
    }
    
    return false;
}

/**
 * Complete external flow - final approval by APPROVER
 */
public function completeExternalFlow(Request $request, $documentId)
{
    try {
        DB::beginTransaction();
        
        // Update document status to DONE
        DB::table('document')
            ->where('document_id', $documentId)
            ->update([
                'status' => 6, // DONE
                'updated_at' => now(),
            ]);
            
        // Update all comments for this document to completed
        DB::table('comment')
            ->where('document_id', $documentId)
            ->update([
                'status' => 2, // Completed
                'end_date' => now(),
                'updated_at' => now(),
            ]);
            
        DB::commit();
        
        return redirect()->route('incoming_company.index')
            ->with('success_message', 'External flow completed successfully');
            
    } catch (\Exception $e) {
        DB::rollback();
        return redirect()->back()
            ->with('error_message', 'Error completing external flow: ' . $e->getMessage());
    }
}

/**
 * Delete user assignment dari external company
 */
public function deleteAssignmentUser($commentId)
{
    try {
        // Delete comment record
        $deleted = DB::table('comment')
            ->where('comment_id', $commentId)
            ->delete();
            
        if ($deleted) {
            return redirect()->back()
                ->with('success_message', 'User assignment berhasil dihapus');
        } else {
            return redirect()->back()
                ->with('error_message', 'User assignment tidak ditemukan');
        }
        
    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error_message', 'Error menghapus user assignment: ' . $e->getMessage());
    }
}

/**
 * Entry point untuk Assignment Incoming Company
 * Redirect ke halaman assignment dokumen biasa dengan flag ?src=ic
 */
public function incomingCompanyAssignment($documentId)
{
    
    //echo 'xx';die;
    // 1. Pastikan dokumen ada
    $doc = DB::table('document')
        ->where('document_id', $documentId)
        ->first();

    abort_if(!$doc, 404, 'Dokumen tidak ditemukan.');

    // 2. PERUBAHAN SESUAI PERMINTAAN ANDA:
    //    Status boleh 6 (DONE) atau lebih besar (10, 20, 30, dst)
    abort_if($doc->status < 6, 403, 'Dokumen belum selesai (status kurang dari DONE).');

    // 3. Pastikan ada record assignment (header) — jika belum, buat
    $assignmentExists = DB::table('assignment')
        ->where('document_id', $doc->document_id)
        ->where('incoming_transmittal_detail_id', (int) ($doc->incoming_transmittal_detail_id ?? 0))
        ->where('status_nonaktif', 0)
        ->exists();

    if (!$assignmentExists) {
        DB::table('assignment')->insert([
            'document_id'                    => (int) $doc->document_id,
            'incoming_transmittal_detail_id' => (int) ($doc->incoming_transmittal_detail_id ?? 0),
            'status_nonaktif'                => 0,
            'created_by'                     => Auth::id() ?? 0,
            'created_at'                     => now(),
        ]);
    }

    // 4. Encode ID dan redirect ke halaman assignment yang sudah ada + flag src=ic
    $encoded = encodedData($documentId);
    
    die;
    //return redirect()->to(url("/document/assignment/{$encoded}?src=ic"));
}




    

/**
 * AJAX endpoint untuk menambah user di assignment Incoming Company
 */
/**
 * Menambahkan user baru ke assignment via modal di Incoming Company
 * Dipanggil dari AJAX di assignment_ic.blade.php
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */



    /**
     * Detail Transmittal → jika dokumen punya tautan ke transmittal incoming,
     * redirect ke /incoming/detail/{idHeaderTransmittal} (controller existing).
     */
public function incomingCompanyTransmittalDetail($documentId)
{
    $doc = DB::table('document')->where('document_id', $documentId)->first();
    if (!$doc) {
        return redirect()->route('incoming_company.index')->with('warning', 'Dokumen tidak ditemukan.');
    }

    $headerId = null;
    if (!empty($doc->incoming_transmittal_detail_id)) {
        $headerId = DB::table('incoming_transmittal_detail')
            ->where('incoming_transmittal_detail_id', $doc->incoming_transmittal_detail_id)
            ->value('incoming_transmittal_id');
    }

    if ($headerId) {
        return redirect()->to(url('incoming/detail/'.$headerId));
    }

    return redirect()->route('incoming_company.index')
        ->with('warning', 'Dokumen ini belum terhubung ke Transmittal Incoming.');
}
    
    
    
    

    /**
     * List dokumen DONE khusus Incoming Company (untuk tombol Assign & Detail)
     */

public function incomingCompanyIndex(\Illuminate\Http\Request $request)
{
    // Tampilkan dokumen dengan status IFC (1), IFA (3), IFR (5), IFI (7)
    $externalStatusIds = [STATUS_IFC, STATUS_IFA, STATUS_IFR, STATUS_IFI]; // 1, 3, 5, 7
    
    $q = DB::table('document AS d')
        ->leftJoin('ref_vendor AS v', 'v.vendor_id', '=', 'd.vendor_id')
        ->leftJoin('ref_issue_status AS is', 'is.issue_status_id', '=', 'd.issue_status_id')
        ->select(
            'd.document_id',
            'd.document_no',
            'd.document_title',
            'd.status',
            'd.issue_status_id',
            'd.incoming_transmittal_detail_id',
            'v.name AS vendor_name',
            'is.name AS issue_status_name',
            'd.created_at',
            'd.note_backdoor', // actual column name in document table
            // compute human-readable stage for external flow
            DB::RAW("(CASE WHEN d.note_backdoor IS NULL OR d.note_backdoor = '' THEN 'Pending' WHEN d.note_backdoor = '3' THEN 'On Review' WHEN d.note_backdoor = '4' THEN 'Owner' WHEN d.note_backdoor = '5' THEN 'Approver' WHEN d.note_backdoor = '6' THEN 'Done' ELSE d.note_backdoor END) AS backdoor_status"),
            'd.deadline'
        )
        ->whereIn('d.issue_status_id', $externalStatusIds) // IFC, IFA, IFR, IFI
        // Jangan tampilkan dokumen yang masih punya outgoing company BELUM dikirim (draft)
        ->whereNotExists(function ($sub) {
            $sub->select(DB::raw(1))
                ->from('outgoing_transmittal_detail AS otd2')
                ->join('incoming_transmittal_detail AS itd2', 'otd2.incoming_transmittal_detail_id', '=', 'itd2.incoming_transmittal_detail_id')
                ->join('outgoing_transmittal AS ot2', 'ot2.outgoing_transmittal_id', '=', 'otd2.outgoing_transmittal_id')
                ->whereRaw('itd2.document_id = d.document_id')
                ->whereRaw("ot2.outgoing_no REGEXP '^REV-[0-9]+-[0-9]+$'")
                ->whereNull('ot2.sender_date');
        })
        ->orderBy('d.issue_status_id') // Group by status
        ->orderByDesc('d.document_id');

    // Add filters if needed
    if ($request->has('vendor_id') && $request->vendor_id) {
        $q->where('d.vendor_id', $request->vendor_id);
    }
    
    if ($request->has('issue_status_id') && $request->issue_status_id) {
        $q->where('d.issue_status_id', $request->issue_status_id);
    }

    // backdoor status filtering
    if ($request->has('status') && $request->status) {
        switch ($request->status) {
            case 'pending':
                $q->where(function($q2) {
                    $q2->whereNull('d.note_backdoor')->orWhere('d.note_backdoor', '');
                });
                break;
            case 'on_review':
                $q->where('d.note_backdoor', '3');
                break;
            case 'owner':
                $q->where('d.note_backdoor', '4');
                break;
            case 'approver':
                $q->where('d.note_backdoor', '5');
                break;
            case 'done':
                $q->where('d.note_backdoor', '6');
                break;
        }
    }

    $documents = $q->paginate(25)->appends($request->query());

    // Get status options for filter
    // note_backdoor workflow: null/empty -> Pending (IDC External bucket),
    // 3=On Review, 4=Owner, 5=Approver, 6=Done
    $statusOptions = [
        ['id' => STATUS_IFC, 'name' => 'IFC'],
        ['id' => STATUS_IFA, 'name' => 'IFA'], 
        ['id' => STATUS_IFR, 'name' => 'IFR'],
        ['id' => STATUS_IFI, 'name' => 'IFI'],
        ['id' => 'pending', 'name' => 'Pending'],
        ['id' => 'on_review', 'name' => 'On Review'],
        ['id' => 'owner', 'name' => 'Owner'],
        ['id' => 'approver', 'name' => 'Approver'],
        ['id' => 'done', 'name' => 'Done'],
    ];

    $title = 'Incoming Company – External Documents (IFC/IFA/IFR/IFI)';
    return view('incoming.incoming_company_index', compact('documents', 'title', 'statusOptions'));
}
    
    
    
//===========================================================

public function commentCompany($documentId)
{
    // make sure id is numeric
    $doc = DB::table('document')
        ->leftJoin('ref_issue_status as is','is.issue_status_id','=','document.issue_status_id')
        ->where('document.document_id',$documentId)
        ->select('document.*','is.name as issue_status_name')
        ->first();
    if (!$doc) {
        return back()->with('error_message','Document not found');
    }

    $comments = DB::table('comment as c')
        ->join('assignment as a','a.assignment_id','=','c.assignment_id')
        ->join('sys_users as u','u.id','=','c.user_id')
        ->leftJoin('ref_issue_status as ris','c.issue_status_id','=','ris.issue_status_id')
        ->where('a.document_id',$documentId)
        ->where('c.status_nonaktif',0)
        ->orderBy('c.order_no')
        ->get([
            'c.comment_id',
            'c.role',
            'c.remark',
            'c.status',
            // use name instead of numeric id
            DB::raw('ris.name AS revision_status'),
            'c.return_status_id',
            'u.full_name'
        ]);

    $editComment = null;
    $commentIdParam = request('comment_id') ?? request('edit_id');
    if ($commentIdParam) {
        $editComment = DB::table('comment')
            ->where('comment_id', $commentIdParam)
            ->first();
    }



    // return status options from reference table (status = 1)
    $returnStatusOptions = DB::table('ref_return_status')
        ->where('status',1)
        ->pluck('name','return_status_id');

    // role passed from the list page (per-document role from comment table)
    $docRole = request('role', '');

    // compute next-stage selections when the user acts as approver for this document
    $nextStageOptions = [];
    if (in_array($docRole, ['APPROVER_COMPANY','ADMINISTRATOR'])) {
        // always show 6 fixed options: revision (RE-) or accept
        $nextStageOptions = [
            'rev-ifc'    => 'RE-IFC',
            'accept-ifc' => 'IFC',
            'rev-ifa'    => 'RE-IFA',
            'accept-ifa' => 'IFA',
            'rev-ifi'    => 'RE-IFI',
            'accept-ifi' => 'IFI',
        ];
        // store in session for validation during save
        session()->put('nextStageOptions', $nextStageOptions);
    }

    $title = "Comment Company - ".$doc->document_no;

    return view('comments.company_comment', compact(
        'comments','doc','title','editComment','returnStatusOptions','nextStageOptions','docRole'
    ));
}


public function commentCompanyList()
{
    $userId = Auth::id();

    $documents = DB::table('document as d')
    ->join('assignment as a','a.document_id','=','d.document_id')
    ->join('comment as c','c.assignment_id','=','a.assignment_id')
    ->leftJoin('ref_vendor as v','v.vendor_id','=','d.vendor_id')
    ->leftJoin('ref_issue_status as isz','isz.issue_status_id','=','d.issue_status_id')
    ->where('c.user_id',$userId)
    ->whereIn('c.role',['RESPONSIBLE','OWNER','APPROVER_COMPANY'])
    ->where('c.status','<',30)
    ->whereNotIn('d.note_backdoor', ['2', '6'])
    ->where(function($q){
        $q->where('c.role','!=','OWNER')
          ->orWhere('d.note_backdoor','=','4');
    })
    ->where(function($q){
        $q->where('c.role','!=','APPROVER_COMPANY')
          ->orWhere('d.note_backdoor','=','5');
    })
    ->select(
        'c.comment_id',
        'd.document_id',
        'd.document_no',
        'd.document_title',
        'd.issue_status_id',
        'isz.name as issue_status_name',
        'c.return_status_id',
        'v.name as vendor_name',
        'd.note_backdoor',
        'c.role as user_role'
    )
    ->distinct()
    ->orderBy('d.document_id','desc')
    ->paginate(20);


// compute title according to role of first document or default
$title = 'Comment Company';
if ($documents->count()) {
    $firstDocId = $documents[0]->document_id;
    $firstRole = DB::table('comment as c')
        ->join('assignment as a','a.assignment_id','=','c.assignment_id')
        ->where('a.document_id',$firstDocId)
        ->where('c.user_id',$userId)
        ->value('c.role');
    if ($firstRole == 'OWNER') {
        $title = 'Comment Company - Owner Documents';
    } elseif ($firstRole == 'APPROVER_COMPANY') {
        $title = 'Comment Company - Approver Documents';
    } else {
        $title = 'Comment Company - Responsible Documents';
    }
}

return view('comments.company_list',compact(
    'documents','title'
));

}
    
    
    
  public function saveCommentCompany(Request $request)
{
    // allow both new and existing comments
    $rules = [
        'remark'=>'required',
        'status'=>'required|in:10,20,30',
        'return_status_id'=>'nullable|integer',
        'document_id'=>'required|integer'
    ];
    // approver gets next_stage choices — validate only when the field was submitted
    if ($request->filled('next_stage')) {
        $validKeys = array_keys($request->session()->get('nextStageOptions', []));
        if (empty($validKeys)) {
            $validKeys = [
                'rev-ifc',
                'accept-ifc',
                'rev-ifa',
                'accept-ifa',
                'rev-ifi',
                'accept-ifi',
            ];
        }
        $rules['next_stage'] = 'required|in:'.implode(',', $validKeys);
    }
    if ($request->filled('comment_id')) {
        $rules['comment_id'] = 'integer';
    }
    $request->validate($rules);

    // determine value from document
    $issueStatus = DB::table('document')->where('document_id',$request->document_id)->value('issue_status_id');

    // always update existing comment — find by comment_id or by user+document
    $targetCommentId = $request->filled('comment_id') ? $request->comment_id : null;

    if (!$targetCommentId) {
        // find the current user's comment for this document
        $userComment = DB::table('comment as c')
            ->join('assignment as a', 'a.assignment_id', '=', 'c.assignment_id')
            ->where('a.document_id', $request->document_id)
            ->where('c.user_id', Auth::id())
            ->select('c.comment_id')
            ->first();
        $targetCommentId = $userComment ? $userComment->comment_id : null;
    }

    if ($targetCommentId) {
        // update existing comment
        DB::table('comment')
            ->where('comment_id', $targetCommentId)
            ->update([
                'remark'=>$request->remark,
                'status'=>$request->status,
                'issue_status_id'=>$issueStatus,
                'return_status_id'=>$request->return_status_id ?? 0,
                'updated_by'=>Auth::id(),
                'updated_at'=>now()
            ]);
        $existing = DB::table('comment')
            ->where('comment_id', $targetCommentId)
            ->select('role')
            ->first();
        $roleDone = $existing ? $existing->role : null;
    } else {
        return back()->with('error_message','Comment not found for this document');
    }

    // if an OWNER just marked a comment done, jump the backdoor stage directly
    $docId = $request->document_id;
    if ($roleDone === 'OWNER' && $request->status == 30) {
        DB::table('document')->where('document_id', $docId)->update(['note_backdoor' => '5']);
    }

    // RESPONSIBLE: cek semua responsible udah isi return_status_id atau status=30
    if ($roleDone === 'RESPONSIBLE') {
        // Cari assignment terbaru (aktif) untuk document ini
        $latestAssignmentId = DB::table('assignment')
            ->where('document_id', $docId)
            ->max('assignment_id');

        if ($latestAssignmentId) {
            // Hitung RESPONSIBLE dari assignment terbaru saja
            $totalResponsible = DB::table('comment')
                ->where('assignment_id', $latestAssignmentId)
                ->where('role', 'RESPONSIBLE')
                ->count();

            // Hitung yang sudah selesai: status = 30 (Done)
            $doneResponsible = DB::table('comment')
                ->where('assignment_id', $latestAssignmentId)
                ->where('role', 'RESPONSIBLE')
                ->where('status', 30)
                ->count();

            // Semua responsible sudah isi → note_backdoor=4
            if ($totalResponsible > 0 && $doneResponsible >= $totalResponsible) {
                DB::table('document')->where('document_id', $docId)->update(['note_backdoor' => '4']);
            }
        }
    }

    // approver can choose next stage
    if ($request->filled('next_stage')) {
        switch ($request->next_stage) {
            case 'rev-ifc':
            case 'rev-ifa':
            case 'rev-ifi':
                // revision: map to Re- issue_status_id
                $reMap = [
                    'rev-ifc' => $issueStatus, // keep current for IFC
                    'rev-ifa' => 8,             // Re-IFA
                    'rev-ifi' => 14,            // Re-IFI
                ];
                $newIssueStatusId = $reMap[$request->next_stage] ?? $issueStatus;

                DB::table('document')->where('document_id', $docId)->update([
                    'note_backdoor'   => '2',
                    'issue_status_id' => $newIssueStatusId,
                ]);

                // create outgoing transmittal draft for vendor revision
                $incomingDetail = DB::table('incoming_transmittal_detail')
                    ->where('document_id', $docId)
                    ->orderBy('incoming_transmittal_detail_id', 'desc')
                    ->first();

                if ($incomingDetail) {
                    $vendorId = DB::table('incoming_transmittal')
                        ->where('incoming_transmittal_id', $incomingDetail->incoming_transmittal_id)
                        ->value('vendor_id');

                    $docInfo = DB::table('document')
                        ->where('document_id', $docId)
                        ->first();

                    $outgoingId = DB::table('outgoing_transmittal')->insertGetId([
                        'vendor_id'    => $vendorId,
                        'project_id'   => $incomingDetail->project_id ?? 0,
                        'outgoing_no'  => 'REV-' . date('YmdHis') . '-' . $docId,
                        'subject'      => 'REVISION REQUEST - ' . ($docInfo->document_no ?? ''),
                        'content'      => 'Please revise and re-upload document',
                        'status_email' => 1,
                        'created_by'   => Auth::id(),
                        'created_at'   => now(),
                    ]);

                    DB::table('outgoing_transmittal_detail')->insert([
                        'outgoing_transmittal_id'        => $outgoingId,
                        'incoming_transmittal_detail_id' => $incomingDetail->incoming_transmittal_detail_id,
                        'issue_status_id'                => $newIssueStatusId,
                        'return_status_id'               => 0,
                        'document_status_id'             => $docInfo->document_status_id ?? 0,
                    ]);
                }
                break;

            case 'accept-ifc':
            case 'accept-ifa':
            case 'accept-ifi':
                // accept = DONE
                DB::table('document')->where('document_id', $docId)->update(['note_backdoor' => '6']);
                break;
        }
    }
    // other roles handled elsewhere if needed

    // Only re-run owner completion if OWNER saved and approver did NOT choose a next stage
    if ($roleDone === 'OWNER' && !$request->filled('next_stage')) {
        $this->checkOwnerCompletion($docId);
    }

    // clear session copy of options
    session()->forget('nextStageOptions');

    // send user back to the list so the document disappears immediately
    return redirect()->route('comment_company.list')->with('success_message','Comment berhasil disimpan');
}


/**
 * View attachment untuk company comment (sama dengan internal - buka PDF di PDA annotation)
 */
public function downloadAttachmentCompany($commentId)
{
    try {
        $commentId = decodedData($commentId);
        
        // Query untuk mendapatkan document info (sama dengan internal)
        $query = DB::select("SELECT  document.document_id, incoming_transmittal_detail.document_url, incoming_transmittal_detail.document_file, incoming_transmittal_detail.document_crs
                              FROM    incoming_transmittal_detail INNER JOIN document ON incoming_transmittal_detail.document_id = document.document_id
                              INNER   JOIN assignment ON incoming_transmittal_detail.incoming_transmittal_detail_id = assignment.incoming_transmittal_detail_id
                              INNER   JOIN comment ON assignment.assignment_id = comment.assignment_id
                              WHERE   document.status = 2 AND comment.status = 1 AND comment.comment_id = '$commentId'");

        if (empty($query)) {
            return back()->with('error_message', 'Attachment not found');
        }

        $row = $query[0];
        
        // Cek apakah ada document file
        if (!empty($row->document_file)) {
            $filePath = public_path('uploads') . $row->document_url . $row->document_file;
            if (file_exists($filePath)) {
                // Redirect ke PDF viewer / PDA annotation system
                return redirect()->to('/uploads' . $row->document_url . $row->document_file);
            }
        }
        
        // Cek apakah ada CRS file
        if (!empty($row->document_crs)) {
            $filePath = public_path('uploads') . $row->document_url . $row->document_crs;
            if (file_exists($filePath)) {
                // Redirect ke PDF viewer / PDA annotation system
                return redirect()->to('/uploads' . $row->document_url . $row->document_crs);
            }
        }
        
        return back()->with('error_message', 'Attachment file not found');
        
    } catch (\Exception $e) {
        return back()->with('error_message', 'Error viewing attachment: ' . $e->getMessage());
    }
}

/**
 * View document by document ID (untuk tombol di form utama)
 */
public function viewDocumentAttachment($documentId)
{
    try {
        $documentId = decodedData($documentId);
        
        // Query untuk mendapatkan document info by document ID
        $query = DB::select("SELECT  document.document_id, incoming_transmittal_detail.document_url, incoming_transmittal_detail.document_file, incoming_transmittal_detail.document_crs
                              FROM    incoming_transmittal_detail INNER JOIN document ON incoming_transmittal_detail.document_id = document.document_id
                              WHERE   document.document_id = '$documentId'");

        if (empty($query)) {
            return back()->with('error_message', 'Document not found');
        }

        $row = $query[0];
        
        // Cek apakah ada document file
        if (!empty($row->document_file)) {
            $filePath = public_path('uploads') . $row->document_url . $row->document_file;
            if (file_exists($filePath)) {
                // Redirect ke PDF viewer / PDA annotation system
                return redirect()->to('/uploads' . $row->document_url . $row->document_file);
            }
        }
        
        // Cek apakah ada CRS file
        if (!empty($row->document_crs)) {
            $filePath = public_path('uploads') . $row->document_url . $row->document_crs;
            if (file_exists($filePath)) {
                // Redirect ke PDF viewer / PDA annotation system
                return redirect()->to('/uploads' . $row->document_url . $row->document_crs);
            }
        }
        
        return back()->with('error_message', 'Document file not found');
        
    } catch (\Exception $e) {
        return back()->with('error_message', 'Error viewing document: ' . $e->getMessage());
    }
}

/**
 * IDC External - Halaman untuk external users melakukan review dan approval
 */
public function idcExternal($documentId)
{
    try {
        $documentId = base64_decode($documentId);
        
        // Get document info
        $document = DB::table('document AS d')
            ->leftJoin('incoming_transmittal_detail AS itd', 'd.document_id', '=', 'itd.document_id')
            ->leftJoin('ref_issue_status AS ris', 'd.issue_status_id', '=', 'ris.issue_status_id')
            ->leftJoin('ref_document_status AS rds', 'd.document_status_id', '=', 'rds.document_status_id')
            ->where('d.document_id', $documentId)
            ->first();
            
        if (!$document) {
            return back()->with('error_message', 'Document not found');
        }
        
        // Get assignments untuk document ini
        $assignments = DB::table('assignment AS a')
            ->join('comment AS c', 'a.assignment_id', '=', 'c.assignment_id')
            ->join('users AS u', 'c.user_id', '=', 'u.id')
            ->leftJoin('ref_document_status AS rds', 'c.issue_status_id', '=', 'rds.document_status_id')
            ->where('a.document_id', $documentId)
            ->whereIn('c.role', ['RESPONSIBLE', 'OWNER', 'APPROVER_COMPANY'])
            ->where('c.status_nonaktif', 0)
            ->select(
                'a.*',
                'c.*',
                'u.name AS full_name',
                'u.email',
                'rds.name as revision_status_name',
                DB::raw("CASE 
                    WHEN c.status = 10 THEN 'Assigned'
                    WHEN c.status = 20 THEN 'In Progress' 
                    WHEN c.status = 30 THEN 'Done'
                    ELSE 'Unknown'
                END as status_text")
            )
            ->orderByRaw("FIELD(c.role, 'RESPONSIBLE', 'OWNER', 'APPROVER_COMPANY')")
            ->get();
        
        // Check workflow status
        $responsibleCompleted = $assignments->filter(function ($assignment) {
            return $assignment->role == 'RESPONSIBLE' && $assignment->status == 30;
        })->count();
        
        $totalResponsible = $assignments->filter(function ($assignment) {
            return $assignment->role == 'RESPONSIBLE';
        })->count();
        
        $ownerCompleted = $assignments->filter(function ($assignment) {
            return $assignment->role == 'OWNER' && $assignment->status == 30;
        })->count();
        
        $totalOwner = $assignments->filter(function ($assignment) {
            return $assignment->role == 'OWNER';
        })->count();
        
        $approverCompleted = $assignments->filter(function ($assignment) {
            return $assignment->role == 'APPROVER_COMPANY' && $assignment->status == 30;
        })->count();
        
        $totalApprover = $assignments->filter(function ($assignment) {
            return $assignment->role == 'APPROVER_COMPANY';
        })->count();
        
        // Determine workflow status dan user permission
        $allResponsibleCompleted = ($responsibleCompleted >= $totalResponsible) && $totalResponsible > 0;
        $allOwnerCompleted = ($ownerCompleted >= $totalOwner) && $totalOwner > 0;
        $allApproverCompleted = ($approverCompleted >= $totalApprover) && $totalApprover > 0;
        
        // Determine current user role dan permission
        $currentUserAssignment = $assignments->filter(function ($assignment) {
            return $assignment->user_id == Auth::id();
        })->first();
        
        $canApprove = false;
        $workflowStatus = '';
        
        if (!$allResponsibleCompleted) {
            $workflowStatus = 'Menunggu komentar dari RESPONSIBLE users';
            $canApprove = false;
        } elseif (!$allOwnerCompleted) {
            $workflowStatus = 'Menunggu approval dari OWNER';
            $canApprove = $currentUserAssignment && $currentUserAssignment->role == 'OWNER';
        } elseif (!$allApproverCompleted) {
            $workflowStatus = 'Siap untuk approval COMPANY APPROVER';
            $canApprove = $currentUserAssignment && $currentUserAssignment->role == 'APPROVER_COMPANY';
        } else {
            $workflowStatus = 'Semua approval selesai';
            $canApprove = false;
        }
        
        $data = [
            'title' => 'IDC External - ' . $document->document_no,
            'document' => $document,
            'assignments' => $assignments,
            'workflowStatus' => $workflowStatus,
            'canApprove' => $canApprove,
            'currentUserAssignment' => $currentUserAssignment,
            'allResponsibleCompleted' => $allResponsibleCompleted,
            'allOwnerCompleted' => $allOwnerCompleted,
            'allApproverCompleted' => $allApproverCompleted,
            'revisionOptions' => [
                ['id' => 'A0', 'name' => 'A0'],
                ['id' => 'A1', 'name' => 'A1'],
                ['id' => 'A2', 'name' => 'A2'],
                ['id' => 'RE-IFC', 'name' => 'RE-IFC'],
                ['id' => 'RE-IFA', 'name' => 'RE-IFA'],
                ['id' => 'RE-IFI', 'name' => 'RE-IFI'],
                ['id' => 'RE-IFR', 'name' => 'RE-IFR'],
            ]
        ];
        
        return view('incoming.idc_external', $data);
        
    } catch (\Exception $e) {
        return back()->with('error_message', 'Error: ' . $e->getMessage());
    }
}

/**
 * Save IDC External approval/rejection
 */
public function saveIdcExternal(Request $request, $documentId)
{
    try {
        $documentId = base64_decode($documentId);
        
        $request->validate([
            'action' => 'required|in:approve,reject',
            'remark' => 'required|string',
            'revision_status' => 'nullable|integer'
        ]);
        
        // Get current document
        $document = DB::table('document')->where('document_id', $documentId)->first();
        if (!$document) {
            return back()->with('error_message', 'Document not found');
        }
        
        // Get current user assignment untuk menentukan role
        $currentUserAssignment = DB::table('assignment AS a')
            ->join('comment AS c', 'a.assignment_id', '=', 'c.assignment_id')
            ->where('a.document_id', $documentId)
            ->where('c.user_id', Auth::id())
            ->where('c.status_nonaktif', 0)
            ->select('c.role')
            ->first();
        
        $userRole = $currentUserAssignment ? $currentUserAssignment->role : '';
        
        // Update document status based on action
        if ($request->action == 'approve') {
            // determine next backdoor stage based on current value and role
            $currentBackdoor = $document->note_backdoor;
            $nextBackdoor = $currentBackdoor;

            if (empty($currentBackdoor)) {
                // first approval from IDC external
                $nextBackdoor = '3';
            } elseif ($currentBackdoor == '3') {
                // after responsible comments finished move to owner
                $unfinished = DB::table('comment AS c')
                    ->join('assignment AS a', 'c.assignment_id', '=', 'a.assignment_id')
                    ->where('a.document_id', $documentId)
                    ->where('c.role', 'RESPONSIBLE')
                    ->whereNull('c.remark')
                    ->count();
                if ($unfinished == 0) {
                    $nextBackdoor = '4';
                }
            } elseif ($currentBackdoor == '4' && $userRole == 'OWNER') {
                $nextBackdoor = '5';
            } elseif ($currentBackdoor == '5' && $userRole == 'APPROVER_COMPANY') {
                $nextBackdoor = '6';
            }

            // Check if document needs revision (RE- status) or can be DONE
            if ($request->revision_status) {
                // approver selected a revision/issue status – return to internal workflow
                $nextBackdoor = '2';

                // compute new document status based on selected revision value (A0, A1, RE-IFC, etc.)
                $newStatusId = $this->getDocumentStatusId($request->revision_status, $document->issue_status_id);

                // optional: if the revision corresponds to a different issue status, update it as well
                $newIssueStatusId = $document->issue_status_id;
                if (preg_match('/^RE\-/', $request->revision_status)) {
                    $newIssueStatusId = $document->issue_status_id;
                }

                // reset assignment comments to start fresh under internal cycle
                DB::table('comment AS c')
                    ->join('assignment AS a', 'c.assignment_id', '=', 'a.assignment_id')
                    ->where('a.document_id', $documentId)
                    ->whereIn('c.role', ['RESPONSIBLE', 'OWNER', 'APPROVER_COMPANY'])
                    ->update([
                        'c.status' => 10,
                        'c.remark' => null,
                        'c.updated_at' => now()
                    ]);
            } else {
                // if we moved to stage 6 treat as done status
                if ($nextBackdoor === '6') {
                    $newStatusId = 6; // DONE
                } else {
                    // keep existing status unchanged for intermediate stages
                    $newStatusId = $document->status;
                }
                $newIssueStatusId = $document->issue_status_id;
            }
            
            // Update document
            $updateData = [
                'status' => $newStatusId,
                'document_status_id' => $newStatusId,
                'revision_status' => $request->revision_status ?? $document->revision_status,
                'note_backdoor' => $nextBackdoor,
            ];
            if (isset($newIssueStatusId)) {
                $updateData['issue_status_id'] = $newIssueStatusId;
            }
            DB::table('document')
                ->where('document_id', $documentId)
                ->update($updateData);
                
            // Log the approval
            DB::table('document_log')->insert([
                'document_id' => $documentId,
                'user_id' => Auth::id(),
                'action' => 'IDC_EXTERNAL_APPROVE',
                'remark' => $request->remark,
                'created_at' => now()
            ]);
            
            return back()->with('success_message', 'Document approved successfully!');
            
        } else {
            // REJECT - Different workflow based on user role
            if ($userRole == '') {
                // Jika reject di tahap awal IDC (belum ada role) → kembali ke Assignment External
                $newStatusId = 2; // Assigned (kembali ke assignment)
                $message = 'Document rejected! Kembali ke Assignment External.';
                $action = 'IDC_EXTERNAL_REJECT_TO_ASSIGNMENT';
                
            } elseif (in_array($userRole, ['OWNER', 'APPROVER_COMPANY'])) {
                // Jika reject di OWNER/APPROVER COMPANY → revisi ulang ke Internal Flow
                $newStatusId = 2; // Assigned (kembali ke internal)
                $newIssueStatusId = 0; // Reset ke internal flow
                
                // Reset all assignments ke internal flow
                DB::table('comment AS c')
                    ->join('assignment AS a', 'c.assignment_id', '=', 'a.assignment_id')
                    ->where('a.document_id', $documentId)
                    ->whereIn('c.role', ['RESPONSIBLE', 'OWNER', 'APPROVER_COMPANY'])
                    ->update([
                        'c.status' => 10, // Reset to Assigned
                        'c.remark' => null,
                        'c.updated_at' => now()
                    ]);
                
                $message = 'Document rejected! Kembali ke Internal Flow untuk revisi ulang.';
                $action = 'IDC_EXTERNAL_REJECT_TO_INTERNAL';
                
            } else {
                // Default untuk RESPONSIBLE atau role lainnya
                $newStatusId = 2; // Assigned
                $message = 'Document rejected! Kembali ke Assignment External.';
                $action = 'IDC_EXTERNAL_REJECT';
            }
            
            // Update document
            DB::table('document')
                ->where('document_id', $documentId)
                ->update([
                    'status' => $newStatusId,
                    'issue_status_id' => $newIssueStatusId ?? $document->issue_status_id,
                    'updated_at' => now()
                ]);
                
            // Log the rejection
            DB::table('document_log')->insert([
                'document_id' => $documentId,
                'user_id' => Auth::id(),
                'action' => $action,
                'remark' => $request->remark,
                'created_at' => now()
            ]);
            
            return back()->with('success_message', $message);
        }
        
    } catch (\Exception $e) {
        return back()->with('error_message', 'Error: ' . $e->getMessage());
    }
}

/**
 * Quick approve handler for IDC external pending document.
 * Marks note_backdoor=6 so it leaves the IDC external list.
 */
public function approveIdcExternal($documentId)
{
    try {
        $documentId = base64_decode($documentId);
        // determine current note_backdoor and advance one step
        $doc = DB::table('document')->where('document_id', $documentId)->first();
        $next = $doc->note_backdoor;
        if (empty($next)) {
            $next = '3';          // first approval
        } elseif ($next === '3') {
            $next = '4';          // to owner
        } elseif ($next === '4') {
            $next = '5';          // to approver
        } elseif ($next === '5') {
            $next = '6';          // done
        }
        DB::table('document')
            ->where('document_id', $documentId)
            ->update(['note_backdoor' => $next]);
        return redirect()->route('incoming_company.idc_external.list')
            ->with('success_message', 'Document approved successfully!');
    } catch (\Exception $e) {
        $this->logModel->createError($e->getMessage(), "QUICK IDC APPROVE FAILED", "");
        return redirect()->route('incoming_company.idc_external.list')
            ->with('error_message', 'Error: ' . $e->getMessage());
    }
}

/**
 * Incoming Company List - Menampilkan data seperti document list
 */
public function incomingCompanyList(Request $request)
{
    try {
        $data["title"] = "Incoming Company";
        $data["parent"] = "Transmittal";
        $data["form_act"] = "/incoming_company/incoming-company-list";
        $data["active_page"] = 1;
        $data["offset"] = 0;
        
        // Get documents dari incoming_transmittal_detail dengan issue_status_id 1,3,5,7
        $q = DB::table('document AS d')
            ->join('incoming_transmittal_detail AS itd', 'd.document_id', '=', 'itd.document_id')
            ->join('incoming_transmittal AS it', 'itd.incoming_transmittal_id', '=', 'it.incoming_transmittal_id')
            ->leftJoin('ref_vendor AS rv', 'd.vendor_id', '=', 'rv.vendor_id')
            ->leftJoin('ref_document_status AS rds', 'd.document_status_id', '=', 'rds.document_status_id')
            ->leftJoin('ref_issue_status AS ris', 'd.issue_status_id', '=', 'ris.issue_status_id')
            ->whereIn('itd.issue_status_id', [1, 3, 5, 7])
            ->whereNotIn('d.status', [0, 88])
            ->select(
                'd.document_id',
                'd.document_no', 
                'd.document_title',
                'd.deadline',
                DB::raw("DATE_FORMAT(d.deadline, '%d-%m-%Y') AS deadline"),
                'rv.name AS vendor_name',
                'ris.name AS issue_status_name',
                'd.issue_status_id',
                'rds.name AS document_status',
                DB::raw("(CASE d.status WHEN 1 THEN 'Unissued' WHEN 2 THEN 'Waiting for reviewer' WHEN 3 THEN 'Waiting for compiler' WHEN 4 THEN 'Waiting for return' WHEN 5 THEN 'Waiting for approval' WHEN 7 THEN 'Waiting for view' WHEN 99 THEN 'Stored' WHEN 88 THEN 'Reject' WHEN 6 THEN 'Done' END) AS status_code")
            )
            ->orderBy('d.document_id', 'DESC')
            ->groupBy(
                'd.document_id',
                'd.document_no',
                'd.document_title',
                'deadline',
                'vendor_name',
                'issue_status_name',
                'd.issue_status_id',
                'document_status',
                'd.status'
            );
        
        // Handle search
        if($request->has('search') && $request->search != '') {
            $search = $request->search;
            $q->where(function($query) use ($search) {
                $query->where('d.document_no', 'like', "%{$search}%")
                      ->orWhere('d.document_title', 'like', "%{$search}%")
                      ->orWhere('rv.name', 'like', "%{$search}%");
            });
        }
        
        $data["documents"] = $q->paginate(10);
        
        return view('incoming.incoming_company_list', $data);
        
    } catch (\Exception $e) {
        return back()->with('error_message', 'Error: ' . $e->getMessage());
    }
}

/**
 * Get revision options based on issue status
 */
private function getRevisionOptions($issueStatusId)
{
    $revisions = DB::table('ref_document_status')
        ->select('document_status_id as id', 'name')
        ->where('status', 1)
        ->where(function ($q) use ($issueStatusId) {
            $q->where('issue_status_id', 0)
              ->orWhere('issue_status_id', $issueStatusId);
        })
        ->orderByRaw('
            CASE 
                WHEN name REGEXP "^[0-9]+$" THEN CAST(name AS UNSIGNED) 
                ELSE 9999 
            END ASC, name ASC
        ')
        ->get();
        
    return $revisions->toArray();
}

/**
 * Get document status ID by name and issue status
 */
private function getDocumentStatusId($statusName, $issueStatusId)
{
    $status = DB::table('ref_document_status')
        ->where('name', $statusName)
        ->where('status', 1)
        ->where(function ($q) use ($issueStatusId) {
            $q->where('issue_status_id', 0)
              ->orWhere('issue_status_id', $issueStatusId);
        })
        ->first();
        
    return $status ? $status->document_status_id : 6; // Default to DONE if not found
}

/**
 * IDC External List - Daftar dokumen yang perlu direview oleh external users
 */
public function idcExternalList(Request $request)
{
    try {
        $data["title"] = "IDC External";
        $data["parent"] = "Transmittal";
        $data["form_act"] = "/incoming_company/idc-external-list";
        $data["active_page"] = 1;
        $data["offset"] = 0;
        
        // Get documents yang sudah di-assign dan perlu external approval
        $q = DB::table('document AS d')
            ->leftJoin('incoming_transmittal_detail AS itd', 'd.document_id', '=', 'itd.document_id')
            ->leftJoin('incoming_transmittal AS it', 'itd.incoming_transmittal_id', '=', 'it.incoming_transmittal_id')
            ->leftJoin('ref_issue_status AS ris', 'd.issue_status_id', '=', 'ris.issue_status_id')
            ->leftJoin('ref_document_status AS rds', 'd.document_status_id', '=', 'rds.document_status_id')
            ->leftJoin('assignment AS a', 'd.document_id', '=', 'a.document_id')
            ->leftJoin('comment AS c', 'a.assignment_id', '=', 'c.assignment_id')
            ->leftJoin('users AS u', 'c.user_id', '=', 'u.id')
            // pending documents have empty/null backdoor flag
            ->where(function($q2) {
                $q2->whereNull('d.note_backdoor')->orWhere('d.note_backdoor', '');
            })
            // only show external statuses (same set used by incomingCompanyIndex)
            ->whereIn('d.issue_status_id', [STATUS_IFC, STATUS_IFA, STATUS_IFI])
            ->select(
                'd.document_id',
                'd.document_no',
                'd.document_title',
                DB::raw('MAX(d.issue_status_id) AS issue_status_id'),
                DB::raw('MAX(d.document_status_id) AS document_status_id'),
                DB::raw('MAX(d.status) AS status'),
                DB::raw('MAX(d.created_at) AS created_at'),
                'd.note_backdoor',
                'ris.name as issue_status_name',
                'rds.name as document_status_name',
                DB::raw('MAX(it.incoming_no) AS incoming_no'),
                DB::raw('MAX(it.receive_date) AS rec_date'),
                DB::raw("GROUP_CONCAT(DISTINCT CONCAT(c.role, ':', u.name) ORDER BY c.role) as assigned_users"),
                DB::raw("COUNT(CASE WHEN c.role = 'RESPONSIBLE' AND c.status = 30 THEN 1 END) as responsible_completed"),
                DB::raw("COUNT(CASE WHEN c.role = 'RESPONSIBLE' THEN 1 END) as total_responsible"),
                DB::raw("COUNT(CASE WHEN c.role = 'OWNER' AND c.status = 30 THEN 1 END) as owner_completed"),
                DB::raw("COUNT(CASE WHEN c.role = 'OWNER' THEN 1 END) as total_owner"),
                DB::raw("COUNT(CASE WHEN c.role = 'APPROVER_COMPANY' AND c.status = 30 THEN 1 END) as approver_completed"),
                DB::raw("COUNT(CASE WHEN c.role = 'APPROVER_COMPANY' THEN 1 END) as total_approver")
            )
            ->groupBy('d.document_id', 'd.document_no', 'd.document_title', 'd.note_backdoor', 'ris.name', 'rds.name')
            ->orderBy('d.created_at', 'desc');
        
        // Apply filters if exists
        if ($request->filled('document_no')) {
            $q->where('d.document_no', 'like', '%' . $request->document_no . '%');
        }
        
        if ($request->filled('issue_status_id')) {
            $q->where('d.issue_status_id', $request->issue_status_id);
        }
        
        // Get data
        $documents = $q->paginate(Auth::user()->perpage ?? 10);
        
        // Process documents to add workflow status
        $documents->getCollection()->transform(function ($doc) {
            // Check workflow status
            $doc->all_responsible_completed = ($doc->responsible_completed >= $doc->total_responsible) && $doc->total_responsible > 0;
            $doc->all_owner_completed = ($doc->owner_completed >= $doc->total_owner) && $doc->total_owner > 0;
            $doc->all_approver_completed = ($doc->approver_completed >= $doc->total_approver) && $doc->total_approver > 0;
            
            // Determine workflow status
            if (!$doc->all_responsible_completed) {
                $doc->workflow_status = '<span class="label label-warning">Waiting for Responsible Comments</span>';
                $doc->can_approve = false;
            } elseif (!$doc->all_owner_completed) {
                $doc->workflow_status = '<span class="label label-info">Waiting for Owner Approval</span>';
                $doc->can_approve = false;
            } elseif (!$doc->all_approver_completed) {
                $doc->workflow_status = '<span class="label label-primary">Ready for Company Approval</span>';
                $doc->can_approve = true;
            } else {
                $doc->workflow_status = '<span class="label label-success">All Approvals Complete</span>';
                $doc->can_approve = false;
            }
            
            return $doc;
        });
        
        $data["documents"] = $documents;
        
        // Get filters
        $data["issue_status_options"] = DB::table('ref_issue_status')
            ->whereIn('issue_status_id', [1, 3, 7, 5]) // IFC=1, IFA=3, IFI=7, IFR=5
            ->where('status', 1)
            ->pluck('name', 'issue_status_id');
        
        return view('incoming.idc_external_list', $data);
        
    } catch (\Exception $e) {
        // log error and show page with message rather than redirect back (which sends user elsewhere)
        $this->logModel->createError($e->getMessage(), "IDC EXTERNAL LIST ERROR", "");
        // ensure we still have filter options
        $data["documents"] = collect();
        $data["issue_status_options"] = DB::table('ref_issue_status')
            ->whereIn('issue_status_id', [1, 3, 7, 5])
            ->where('status', 1)
            ->pluck('name', 'issue_status_id');
        $data['error_message'] = 'Error: ' . $e->getMessage();
        return view('incoming.idc_external_list', $data);
    }
}

/**
 * Get workflow status text
 */
private function getWorkflowStatus($document)
{
    if ($document->all_responsible_completed) {
        return '<span class="label label-success">Ready for Approval</span>';
    } else {
        return '<span class="label label-warning">Waiting for Comments</span>';
    }
}

/*===================================*/

public function add($id = null)
{
    if($id){
        $id = decodedData($id);
    
        $header = DB::table('incoming_transmittal')
            ->where('incoming_transmittal_id',$id)
            ->first();
    }
    try {
        $data["title"]         = ($this->isVendor == "YES") ? "Add Outgoing Transmittal" : "Add Incoming Transmittal";
        $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
        //$data["form_act"]      = ($this->isVendor == "YES") ? "/vendor_outgoing/save" : "/incoming/save";
        
        if($id){
            $data["form_act"] = ($this->isVendor == "YES")
                ? "/vendor_outgoing/update"
                : "/incoming/update";
        }else{
            $data["form_act"] = ($this->isVendor == "YES")
                ? "/vendor_outgoing/save"
                : "/incoming/save";
        }
                        
        /* ----------
         Model
        ----------------------- */
        $selectDocument           = $this->qReference->getSelectDocumentVendor(Auth::user()->vendor_id);
        $selectIssueStatus        = $this->qReference->getSelectIssueStatusWithoutIFI();
        $selectIssueStatusIFI     = $this->qReference->getSelectIssueStatusIFI();
        $selectReturnStatus       = $this->qReference->getSelectReturnStatus();
        $selectIssueStatusIFIContruction    = $this->qReference->getSelectIssueStatusIFIContruction();
        $selectVendor             = $this->qReference->getSelectVendor();
        $selectProject            = $this->qReference->getSelectProject();
        $selectVendorUser         = $this->qReference->getSelectVendorUser(Auth::user()->vendor_id);
        $selectProjectUser        = $this->qReference->getSelectProjectUser(Auth::user()->vendor_id);
        //$selectDocumentStatus     = [];
        //$selectDocumentStatus = $this->qReference->getSelectDocumentStatus();
        
        $selectDocumentStatus = $this->qReference->getSelectDocumentStatusInternal();
        
        $selectDocumentStatusIFI  = array(array("id"=>120, "name"=>"0A"));
        // $selectDocumentStatus  = $this->qReference->getSelectDocumentStatus();
        # ---------------
        $data["items"]            = $this->qIncoming->getItemTemp();
        // $this->qIncoming->emptyTemp();
        /* ----------
         Fields
        ----------------------- */
        $data["fields"][]      = form_hidden(array("name"=>"vendor_id", "label"=>"Vendor", "mandatory"=>"yes", "value"=>Auth::user()->vendor_id));
        //$data["fields"][]      = form_text(array("name"=>"incoming_no", "label"=>($this->isVendor == "YES") ? "Outgoing Number" : "Incoming Number", "mandatory"=>"yes", "first_selected"=>"yes"));
           
        $data["fields"][] = form_text([
        "name"=>"incoming_no",
        "label"=>"Outgoing Number",
        "mandatory"=>"yes",
        "first_selected"=>"yes",
        "value"=> isset($header->incoming_no) ? $header->incoming_no : ""
        ]);        
           
           
        $data["fields"][]      = form_hidden(array("name"=>"receive_date", "label"=>($this->isVendor == "YES") ? "Sending Date" : "Receive Date", "mandatory"=>"yes", "value"=>date("d/m/Y")));
        $data["fields"][]      = form_hidden(array("name"=>"sender_date", "label"=>"Sender Date", "mandatory"=>"yes", "value"=>date("d/m/Y")));
        //$data["fields"][]      = form_text(array("name"=>"subject", "label"=>"Subject", "mandatory"=>"yes", "value"=>""));
        
        $data["fields"][] = form_text(array(
        "name"=>"subject",
        "label"=>"Subject",
        "mandatory"=>"yes",
        "value"=> isset($header->subject) ? $header->subject : ""
        ));
                        
        $data["fields"][]      = form_hidden(array("name"=>"return_date_plan", "label"=>"Return Plan Date", "value"=>date("d/m/Y")));
        $data["fields"][]      = form_hidden(array("name"=>"return_date_actual", "label"=>"Return Plan Actual", "value"=>date("d/m/Y")));  
        $data["fields"][]      = form_text(array("name"=>"description", "label"=>"Remark"));          
        $data["fields"][]      = form_upload(array("name"=>"receipt", "label"=>"Receipt"));
        
        
        
        $data["fields"][] = form_hidden(array(
        "name"=>"incoming_transmittal_id",
        "value"=> isset($header->incoming_transmittal_id) ? $header->incoming_transmittal_id : ""
        ));
        
        
        /* ----------
         Modal Fields
        ----------------------- */
        $data["fields_modal"][]= form_upload(array("name"=>"document_file", "label"=>"Document File"));
        $data["fields_modal"][]= form_upload(array("name"=>"document_crs", "label"=>"CRS File"));
        $data["fields_modal"][]= form_select(array("name"=>"document_id", "label"=>"Document Number", "source"=>$selectDocument));
        $data["fields_modal"][]= form_select(array("name"=>"issue_status_id", "label"=>"Issue Status", "withnull"=>"yes", "source"=>$selectIssueStatus, "value"=>0));
        $data["fields_modal"][]= form_select(array("name"=>"document_status_id", "label"=>"Revision Number", "withnull"=>"yes", "source"=>$selectDocumentStatus));
        $data["fields_modal"][]= form_hidden(array("name"=>"return_status_id", "label"=>"Return Status", "withnull"=>"yes", "source"=>$selectReturnStatus));
        $data["fields_modal"][]= form_text(array("name"=>"remark", "label"=>"Description"));
        /* ----------
         Modal Fields
        ----------------------- */
        $data["fields_modal_ifi"][]= form_hidden(array("name"=>"project_id", "label"=>"Project", "source"=>$selectProjectUser, "readonly"=>"readonly"));
        $data["fields_modal_ifi"][]= form_select(array("name"=>"project_id_ifi", "label"=>"Project", "source"=>$selectProjectUser, "readonly"=>"readonly"));
        $data["fields_modal_ifi"][]= form_select(array("name"=>"vendor_id_ifi", "label"=>"Vendor", "source"=>$selectVendorUser, "readonly"=>"readonly"));
        $data["fields_modal_ifi"][]= form_upload(array("name"=>"document_file_ifi", "label"=>"Document IFI"));
        $data["fields_modal_ifi"][]= form_text(array("name"=>"document_no_ifi", "label"=>"Document Number"));
        $data["fields_modal_ifi"][]= form_text(array("name"=>"document_name_ifi", "label"=>"Document Name"));
        $data["fields_modal_ifi"][]= form_select(array("name"=>"issue_status_id_ifi", "label"=>"Issue Status", "source"=>$selectIssueStatusIFI, "value"=>STATUS_ONLY_IFI));
        $data["fields_modal_ifi"][]= form_select(array("name"=>"document_status_id_ifi", "label"=>"Revision Number", "source"=>$selectDocumentStatusIFI, "value"=>120));
        $data["fields_modal_ifi"][]= form_hidden(array("name"=>"return_status_id_ifi", "label"=>"Return Status", "withnull"=>"yes", "source"=>$selectReturnStatus));
        $data["fields_modal_ifi"][]= form_text(array("name"=>"remark_ifi", "label"=>"Description"));
        /* ----------
         Modal Fields
        ----------------------- */
        $data["fields_modal_ifi_contruction"][]= form_hidden(array("name"=>"project_id", "label"=>"Project", "source"=>$selectProjectUser, "readonly"=>"readonly"));
        $data["fields_modal_ifi_contruction"][]= form_select(array("name"=>"project_id_ifi_contruction", "label"=>"Project", "source"=>$selectProjectUser, "readonly"=>"readonly"));
        $data["fields_modal_ifi_contruction"][]= form_select(array("name"=>"vendor_id_ifi_contruction", "label"=>"Vendor", "source"=>$selectVendorUser, "readonly"=>"readonly"));
        $data["fields_modal_ifi_contruction"][]= form_upload(array("name"=>"document_file_ifi_contruction", "label"=>"Document IFC"));
        $data["fields_modal_ifi_contruction"][]= form_text(array("name"=>"document_no_ifi_contruction", "label"=>"Document Number"));
        $data["fields_modal_ifi_contruction"][]= form_text(array("name"=>"document_name_ifi_contruction", "label"=>"Document Name"));
        $data["fields_modal_ifi_contruction"][]= form_select(array("name"=>"issue_status_id_ifi_contruction", "label"=>"Issue Status", "source"=>$selectIssueStatusIFIContruction, "value"=>STATUS_ONLY_IFI_CONSTRUCTION));
        $data["fields_modal_ifi_contruction"][]= form_select(array("name"=>"document_status_id_ifi_contruction", "label"=>"Revision Number", "source"=>$selectDocumentStatusIFI, "value"=>120));
        $data["fields_modal_ifi_contruction"][]= form_hidden(array("name"=>"return_status_id_ifi_contruction", "label"=>"Return Status", "withnull"=>"yes", "source"=>$selectReturnStatus));
        $data["fields_modal_ifi_contruction"][]= form_text(array("name"=>"remark_ifi_contruction", "label"=>"Description"));
        # ---------------
        $data["buttons"][]     = form_button_submit(array("name"=>"button_save", "label"=>"&nbsp;&nbsp;Save&nbsp;&nbsp;"));
        $data["buttons"][]     = form_button_cancel(array("name"=>"button_cancel", "label"=>"Cancel"));
        # ---------------
        $data["attach_url"]    = "/incoming/attach_item";
        $data["delete_url"]    = "/incoming/delete_item";
        # ---------------
        return view("incoming.form-add-with-ifi-contruction", $data);
    } catch (\Exception $e) {
        $this->logModel->createError($e->getMessage(), "PAGE USER", "");
        throw $e;
        # ---------------
        return view("error.405");
    }        
}

public function attach_item(Request $request) {
    try {
        $dataConfig     = $this->sysModel->getConfig();
        $extention      = $dataConfig->attachment_extention;
        $max_size       = $dataConfig->attachment_max_size;
        if($max_size > 0) {
            if(!empty($extention)) {
                $validate_message   = "Attachment extention must $extention & maximum size " . number_format($max_size, 0) . " kb"; 
                $rules              = array(
                    "document_file" => "required|mimes:$extention|max:$max_size",
                    // "document_crs" => "required|mimes:$extention|max:$max_size"
                );
            } else {
                $validate_message   = "Attachment maximum size " . number_format($max_size, 0) . " kb";
                $rules = array(
                    "document_file" => "required|max:$max_size",
                    // "document_crs" => "required|max:$max_size"
                );
            }
        } else {
            if(!empty($extention)) {
                $validate_message   = "Attachment extention must $extention"; 
                $rules              = array(
                    "document_file" => "required|mimes:$extention",
                    // "document_crs" => "required|mimes:$extention"
                );
            } else {
                $validate_message   = "Attachment is required kb";
                $rules = array(
                    "document_file" => "required",
                    // "document_crs" => "required"
                );
            }
        }            

        $messages = [
            
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $response   = [
                "status" => ERROR_STATUS_CODE,
                "message" => $validate_message,
                "data" => [],
            ];
        } else {
            // $cek_status     = $this->qDocument->get($request->document_id);
            // $true_status    = "F";

            // if($request->issue_status_id != 13) { //IFI
            //     if($cek_status->issue_status_id == 0) {
            //         $true_status    = "T";
            //     } else {
            //         if($cek_status->issue_status_id != $request->issue_status_id) {
            //             $true_status    = "F";
            //         } else {
            //             $true_status    = "T";
            //         }
            //     }    
            // } else {
                $true_status    = "T";
            // }
                
            if($true_status == "T") {
                $response   = $this->qIncoming->attachItem($request);
            
                if($response["status"]) {
                    $items  = $this->qIncoming->getItemTemp();

                        $response   = [
                            "status" => SUCCESS_STATUS_CODE,
                            "message" => "Success",
                            "data" => $items
                        ];            
                    } else {
                        $response   = [
                            "status" => ERROR_STATUS_CODE,
                            "message" => "Failed !!!",
                            "data" => $response["message"],
                    ];
                    }
                } else {
                    $response   = [
                        "status" => ERROR_STATUS_CODE,
                        "message" => "Invalid issue status",
                        "data" => [],
                    ];
                }
            }
        } catch (\Exception $e) {
            $response   = [
                "status" => ERROR_STATUS_CODE,
                "message" => "Error !!!",
                "data" => [],
            ];
        }

        return response()->json($response, GLOBAL_SUCCESS_RESPONSE);



        // $response   = $this->qIncoming->attachItem($request);
        
        // if($response["status"]) {
        //     $items  = $this->qIncoming->getItemTemp();

        //     $response   = [
        //         "status" => SUCCESS_STATUS_CODE,
        //         "data" => $items
        //     ];            
        // } else {
        //     $response   = [
        //         "status" => ERROR_STATUS_CODE,
        //         "data" => [],
        //     ];
        // }

        // return response()->json($response, GLOBAL_SUCCESS_RESPONSE);
    }

    public function delete_item($id) {
        $response   = $this->qIncoming->deleteItem($id);
        
        if($response["status"]) {
            $items  = $this->qIncoming->getItemTemp();

            $response   = [
                "status" => SUCCESS_STATUS_CODE,
                "data" => $items
            ];            
        } else {
            $response   = [
                "status" => ERROR_STATUS_CODE,
                "data" => [],
            ];
        }

        return response()->json($response, GLOBAL_SUCCESS_RESPONSE);
    }

    public function save(Request $request) {

        try {
            $dataConfig     = $this->sysModel->getConfig();
            $extention      = $dataConfig->attachment_extention;
            $max_size       = $dataConfig->attachment_max_size;

            $rules["incoming_no"]               = 'required|';
            $messages["incoming_no.required"]   = 'Incoming number is required';

            if(!empty($request->receipt)) {
                if($max_size > 0) {
                    if(!empty($extention)) {
                        $rules["receipt"]               = "required|mimes:$extention|max:$max_size";
                        $messages["receipt.required"]   = 'Attachment extention must $extention & maximum size " . number_format($max_size, 0) . " kb"';
                    } else {
                        $rule["receipt"]                = "required|max:$max_size";
                        $messages["receipt.required"]   = "Attachment maximum size " . number_format($max_size, 0) . " kb";
                    }
                } else {
                    if(!empty($extention)) {
                        $rules["receipt"]        = "required|mimes:$extention";
                        $messages["receipt.required"]   = "Attachment extention must $extention";
                    }
                }
            }

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                if($this->isVendor == "YES") {
                    return redirect("/vendor_outgoing/add")
                            ->withErrors($validator)
                            ->withInput();
                } else {
                    return redirect("/incoming/add")
                            ->withErrors($validator)
                            ->withInput();
                }
            } else {
                $response   = $this->qIncoming->saveIncoming($request);
				//dd($response);
                if($response["status"]) {
                    session()->flash("success_message", SUCCESS_MESSAGE);
                } else {
                    session()->flash("error_message", $response["message"]);
                }
                # ---------------
                if($this->isVendor == "YES") {
                    return redirect("/vendor_outgoing/index");
                } else {
                    return redirect("/incoming/index");
                }
            }
        } catch (\Exception $e) {
            throw $e;
            $this->logModel->createError($e->getMessage(), "PAGE ADD INCOMING", "");
            # ---------------
            return view("error.405");
        }
    }

    public function save_idc(Request $request) {
        try {
            $dataConfig     = $this->sysModel->getConfig();
            $extention      = $dataConfig->attachment_extention;
            $max_size       = $dataConfig->attachment_max_size;

            $rules["incoming_no"]               = 'required|';
            $messages["incoming_no.required"]   = 'Incoming number is required';

            if(!empty($request->receipt)) {
                if($max_size > 0) {
                    if(!empty($extention)) {
                        $rules["receipt"]               = "required|mimes:$extention|max:$max_size";
                        $messages["receipt.required"]   = 'Attachment extention must $extention & maximum size " . number_format($max_size, 0) . " kb"';
                    } else {
                        $rule["receipt"]                = "required|max:$max_size";
                        $messages["receipt.required"]   = "Attachment maximum size " . number_format($max_size, 0) . " kb";
                    }
                } else {
                    if(!empty($extention)) {
                        $rules["receipt"]        = "required|mimes:$extention";
                        $messages["receipt.required"]   = "Attachment extention must $extention";
                    }
                }
            }

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                if($this->isVendor == "YES") {
                    return redirect("/vendor_outgoing/add")
                            ->withErrors($validator)
                            ->withInput();
                } else {
                    return redirect("/incoming/add")
                            ->withErrors($validator)
                            ->withInput();
                }
            } else {
                $response   = $this->qIncoming->saveIncoming($request);
                
                if($response["status"]) {
                    session()->flash("success_message", SUCCESS_MESSAGE);
                } else {
                    session()->flash("error_message", $response["message"]);
                }
                # ---------------
                if($this->isVendor == "YES") {
                    return redirect("/vendor_outgoing/index");
                } else {
                    return redirect("/incoming/index");
                }
            }
        } catch (\Exception $e) {
            throw $e;
            $this->logModel->createError($e->getMessage(), "PAGE ADD INCOMING", "");
            # ---------------
            return view("error.405");
        }
    }

    public function detail($id) {
        try {
            $data["title"]         = ($this->isVendor == "YES") ? "Detail Outgoing Transmittal" : "Detail Incoming Transmittal";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/incoming/index";
            # ---------------
            $id                    = decodedData($id);
            /* ----------
             Model
            ----------------------- */
            $data["header"]        = $this->qIncoming->getHeader($id);
            $data["detail"]        = $this->qIncoming->getDetail($id);
            $qSelectStatus         = array(array("id"=>2, "name"=>"Approve")
                                          , array("id"=>3, "name"=>"Reject"));
            # ---------------
            $file                  = (!empty($data["header"]->receipt_file)) ? asset("/uploads").$data["header"]->receipt_url . $data["header"]->receipt_file : "";
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_text(array("name"=>"incoming_no", "label"=>($this->isVendor == "YES") ? "Outgoing Number" : "Incoming Number", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$data["header"]->incoming_no));
            $data["fields"][]      = form_datepicker(array("name"=>"receive_date", "label"=>($this->isVendor == "YES") ? "Sending Date" : "Receive Date", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>displayDMY($data["header"]->receive_date, "/")));
            $data["fields"][]      = form_hidden(array("name"=>"sender_date", "label"=>"Sender Date", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>displayDMY($data["header"]->sender_date, "/")));
            $data["fields"][]      = form_text(array("name"=>"subject", "label"=>"Subject", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$data["header"]->subject));
            $data["fields"][]      = form_datepicker(array("name"=>"return_date_plan", "label"=>"Return Plan Date", "readonly"=>"readonly", "value"=>displayDMY($data["header"]->return_date_plan, "/")));
            $data["fields"][]      = form_hidden(array("name"=>"return_date_actual", "label"=>"Return Plan Actual", "readonly"=>"readonly", "value"=>displayDMY($data["header"]->return_date_actual, "/")));
            $data["fields"][]      = form_text(array("name"=>"remark", "label"=>"Description", "readonly"=>"readonly", "value"=>$data["header"]->remark));    
            $data["fields"][]      = form_file(array("name"=>"receipt", "label"=>"Receipt", "readonly"=>"readonly", "value"=>$file));
            $data["fields"][]      = form_select(array("name"=>"status", "label"=>"Status", "source"=>$qSelectStatus, "withnull"=>"yes", "value"=>$data["header"]->status, "readonly"=>"readonly"));
            $data["fields"][]      = form_textarea(array("name"=>"remark_approval", "label"=>"Notes", "readonly"=>"readonly", "value"=>$data["header"]->remark_approval));
            # ---------------
            return view("incoming.form-detail", $data);
        } catch (\Exception $e) {
            throw $e;
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            # ---------------
            return view("error.405");
        }        
    }

    public function edit($id_enc) {
        try {
            $data["title"]         = ($this->isVendor == "YES") ? "Edit Outgoing Transmittal" : "Edit Incoming Transmittal";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = ($this->isVendor == "YES") ? "/vendor_outgoing/update" : "/incoming/update";
            # ---------------
            $id                    = decodedData($id_enc);
            /* ----------
             Model
            ----------------------- */
            $data["header"]        = $this->qIncoming->getHeader($id);
            $data["detail"]        = $this->qIncoming->getDetail($id);
            
            
            
            $data["items"] = $this->qIncoming->getItemTemp(); // pakai fungsi yang sama dengan add

        // Pastikan attach_url & delete_url sama
        $data["attach_url"] = "/incoming/attach_item";
        $data["delete_url"] = "/incoming/delete_item";
            
            
            # ---------------
            $file                  = asset("/uploads").$data["header"]->receipt_url . $data["header"]->receipt_file;
            $delete_file           = url('')."/incoming/delete_receipt/" . $id_enc;
            /* ----------
             Fields
            ----------------------- */
            
            
            
            $data["fields"][] = form_hidden(array(
            "name" => "incoming_transmittal_id",
            "value" => $data["header"]->incoming_transmittal_id
        ));
            
            $data["fields"][]      = form_hidden(array("name"=>"incoming_transmittal_id", "label"=>"Incoming ID", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$data["header"]->incoming_transmittal_id));
            $data["fields"][]      = form_text(array("name"=>"incoming_no", "label"=>($this->isVendor == "YES") ? "Outgoing Number" : "Incoming Number", "mandatory"=>"yes", "value"=>$data["header"]->incoming_no));
            $data["fields"][]      = form_datepicker(array("name"=>"receive_date", "label"=>($this->isVendor == "YES") ? "Sending Date" : "Receive Date", "mandatory"=>"yes", "value"=>displayDMY($data["header"]->receive_date, "/")));
            $data["fields"][]      = form_datepicker(array("name"=>"sender_date", "label"=>"Sender Date", "mandatory"=>"yes", "value"=>displayDMY($data["header"]->sender_date, "/")));
            $data["fields"][]      = form_text(array("name"=>"subject", "label"=>"Subject", "mandatory"=>"yes", "value"=>$data["header"]->subject));
            $data["fields"][]      = form_datepicker(array("name"=>"return_date_plan", "label"=>"Return Plan Date", "value"=>displayDMY($data["header"]->return_date_plan, "/")));
            $data["fields"][]      = form_datepicker(array("name"=>"return_date_actual", "label"=>"Return Plan Actual", "value"=>displayDMY($data["header"]->return_date_actual, "/")));
            $data["fields"][]      = form_text(array("name"=>"remark", "label"=>"Description", "value"=>$data["header"]->remark));  
            if(empty($data["header"]->receipt_file)) {
                $data["fields"][]      = form_upload(array("name"=>"receipt", "label"=>"Receipt"));
            } else {
                $data["fields"][]      = form_file(array("name"=>"receipt", "label"=>"Receipt", "readonly"=>"readonly", "value"=>$file, "valdelete"=>$delete_file));    
            }
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_save", "label"=>"&nbsp;&nbsp;Update&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name"=>"button_cancel", "label"=>"Cancel"));
            # ---------------
            //return view("incoming.form-edit", $data);
            return view("incoming.form-add-with-ifi-contruction", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            # ---------------
            return view("error.405");
        }        
    }

    public function delete_receipt($id) {
        try {
            $id         = decodedData($id);
            $response   = $this->qIncoming->deleteReceipt($id);

            if($response["status"]) {
                session()->flash("success_message", SUCCESS_MESSAGE);
            } else {
                session()->flash("error_message", FAILED_MESSAGE);
            }
            # ---------------
            return redirect("/incoming/edit/" . encodedData($id));
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE EDIT INCOMING", "");
            # ---------------
            return view("error.405");
        }
    }

    public function updatexxx(Request $request) {
       // dd($_FILES);die;
        /*
          "_token" => "D7AVGIR0uhTYDHwONHVkDNv3LKsfqnpdhTpEu7Ph"
  "vendor_id" => "1"
  "incoming_no" => "AUTO-20260309113828"
  "receive_date" => "09/03/2026"
  "sender_date" => "09/03/2026"
  "subject" => "REVISION REQUIRED - A001"
  "return_date_plan" => "09/03/2026"
  "return_date_actual" => "09/03/2026"
  "description" => ""
  "incoming_transmittal_id" => "7"
  "button_save" => ""
  "text_row_attachment" => "1"
  "document_id" => "1"
  "issue_status_id" => "0"
  "document_status_id" => "0"
  "return_status_id" => ""
  "remark" => ""
  */
  
        try {
            $rules = array(
                'incoming_no' => 'required|',
            );

            $messages = [
                'incoming_no.required' => 'Incoming number is required',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("/incoming/add")
                            ->withErrors($validator)
                            ->withInput();
            } else {
                $response   = $this->qIncoming->updateIncoming($request);

                if($response["status"]) {
                    session()->flash("success_message", SUCCESS_MESSAGE);
                } else {
                    session()->flash("error_message", FAILED_MESSAGE);
                }
                # ---------------
                return redirect("/incoming/index");
            }
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE EDIT INCOMING", "");
            # ---------------
            return view("error.405");
        }
    }
    
    public function update(Request $request) {
    try {
        $id = $request->incoming_transmittal_id; // wajib dari hidden field

        $rules = [
            'incoming_no' => 'required|unique:incoming_transmittal,incoming_no,' . $id . ',incoming_transmittal_id',
        ];
        $messages = [
            'incoming_no.required' => 'Outgoing/Incoming number wajib diisi',
            'incoming_no.unique'   => 'Nomor sudah dipakai',
        ];

        // Validasi receipt kalau di-upload
        if ($request->hasFile('receipt')) {
            $rules['receipt'] = 'file|mimes:pdf,jpg,png,doc,docx|max:10240';
        }

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Panggil model untuk update (kita buat fungsi baru atau modifikasi updateIncoming)
        $response = $this->qIncoming->updateIncomingWithAttach($request, $id);

        if ($response["status"]) {
            session()->flash("success_message", "Update berhasil, termasuk file attach baru.");
        } else {
            session()->flash("error_message", $response["message"] ?? "Gagal update");
        }

        return redirect("/incoming/index"); // atau /vendor_outgoing/index
    } catch (\Exception $e) {
        $this->logModel->createError($e->getMessage(), "UPDATE INCOMING FAILED", "");
        return view("error.405");
    }
}

    public function approve($id) {
        try {
            $data["title"]         = "Internal Document Check (IDC)";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/incoming/save_approve";
            # ---------------
            $id                    = decodedData($id);
            /* ----------
             Model
            ----------------------- */
            $data["header"]        = $this->qIncoming->getHeader($id);
            $data["detail"]        = $this->qIncoming->getDetail($id);
            // dd($data["detail"]);
            $qSelectStatus         = array(array("id"=>2, "name"=>"Approve")
                                          , array("id"=>3, "name"=>"Reject"));
            # ---------------
            $file                  = (!empty($data["header"]->receipt_file)) ? asset("/uploads").$data["header"]->receipt_url . $data["header"]->receipt_file : "";
            /* ----------
             Fields
            ----------------------- */
            if ($data["header"]->status != 1) {
                session()->flash("error_message", "Incomming can't approve");
                # ---------------
                return redirect("/incoming/index");
            }
            # ---------------
            //dd($data["header"]->issue_status_id);
            if($data["header"]->issue_status_id != 1) {
            $data["fields"][]      = form_hidden(array("name"=>"id", "label"=>"ID", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$id));
            $data["fields"][]      = form_hidden(array("name"=>"vendor_email", "label"=>"Vendor Email", "readonly"=>"readonly", "value"=>$data["header"]->email_address));
            $data["fields"][]      = form_text(array("name"=>"incoming_no", "label"=>($this->isVendor == "YES") ? "Outgoing Number" : "Incoming Number", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$data["header"]->incoming_no));
            $data["fields"][]      = form_text(array("name"=>"vendor_name", "label"=>"Vendor Name", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$data["header"]->vendor_name));
            $data["fields"][]      = form_datepicker(array("name"=>"receive_date", "label"=>($this->isVendor == "YES") ? "Sending Date" : "Receive Date", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>displayDMY($data["header"]->receive_date, "/")));
            $data["fields"][]      = form_hidden(array("name"=>"sender_date", "label"=>"Sender Date", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>displayDMY($data["header"]->sender_date, "/")));
            $data["fields"][]      = form_text(array("name"=>"subject", "label"=>"Subject", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$data["header"]->subject));
            $data["fields"][]      = form_datepicker(array("name"=>"return_date_plan", "label"=>"Return Plan Date", "readonly"=>"readonly", "value"=>displayDMY($data["header"]->return_date_plan, "/")));
            $data["fields"][]      = form_hidden(array("name"=>"return_date_actual", "label"=>"Return Plan Actual", "readonly"=>"readonly", "value"=>displayDMY($data["header"]->return_date_actual, "/")));
            $data["fields"][]      = form_text(array("name"=>"remark", "label"=>"Remark", "readonly"=>"readonly", "value"=>$data["header"]->remark));
            $data["fields"][]      = form_file(array("name"=>"receipt", "label"=>"Receipt", "readonly"=>"readonly", "value"=>$file));
            $data["fields"][]      = form_select(array("name"=>"status", "label"=>"Status", "source"=>$qSelectStatus));
            $data["fields"][]      = form_textarea(array("name"=>"remark_approval", "label"=>"Notes", "value"=>$data["header"]->remark_approval));
            }else{
            $data["fields"][]      = form_hidden(array("name"=>"id", "label"=>"ID", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$id));
            $data["fields"][]      = form_hidden(array("name"=>"issue_status_id", "label"=>($this->isVendor == "YES") ? "Outgoing Number" : "Incoming Number", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$data["header"]->issue_status_id));
            $data["fields"][]      = form_text(array("name"=>"incoming_no", "label"=>($this->isVendor == "YES") ? "Outgoing Number" : "Incoming Number", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$data["header"]->incoming_no));
            $data["fields"][]      = form_hidden(array("name"=>"receive_date", "label"=>($this->isVendor == "YES") ? "Sending Date" : "Receive Date", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>displayDMY($data["header"]->receive_date, "/")));
            $data["fields"][]      = form_hidden(array("name"=>"sender_date", "label"=>"Sender Date", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>displayDMY($data["header"]->sender_date, "/")));
            $data["fields"][]      = form_hidden(array("name"=>"return_date_plan", "label"=>"Return Plan Date", "readonly"=>"readonly", "value"=>displayDMY($data["header"]->return_date_plan, "/")));
            $data["fields"][]      = form_hidden(array("name"=>"return_date_actual", "label"=>"Return Plan Actual", "readonly"=>"readonly", "value"=>displayDMY($data["header"]->return_date_actual, "/")));
            $data["fields"][]      = form_select(array("name"=>"status", "label"=>"Status", "source"=>$qSelectStatus));
            $data["fields"][]      = form_textarea(array("name"=>"remark_approval", "label"=>"Notes", "value"=>$data["header"]->remark_approval));
            }
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_save", "label"=>"&nbsp;&nbsp;Process&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name"=>"button_cancel", "label"=>"Cancel"));
            # ---------------
            return view("incoming.form-approve", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            # ---------------
            return view("error.405");
        }        
    }

    public function save_approve(Request $request) {
        try {
            $response   = $this->qIncoming->approveIncoming($request);

            if($response["status"]) {
                session()->flash("success_message", SUCCESS_MESSAGE);
            } else {
                session()->flash("error_message", FAILED_MESSAGE);
            }
            # ---------------
            return redirect("/incoming/index");
        } catch (\Exception $e) {
            throw $e;
            $this->logModel->createError($e->getMessage(), "PAGE APPROVE INCOMING", "");
            # ---------------
            return view("error.405");
        }
    }

    public function report() {
        try {
            $data["title"]         = "Incoming Transmittal Report";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/incoming/report_result";
            /* ----------
             Model
            ----------------------- */
            $selectType            = array(array("id"=>"SUMMARY", "name"=>"SUMMARY")
                                          , array("id"=>"DETAIL", "name"=>"DETAIL"));
            /* ----------
             Source
            ----------------------- */      

            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_datepicker(array("name"=>"receive_date_start", "label"=>"Receive Date From", "value"=>date("d/m/Y")));
            $data["fields"][]      = form_datepicker(array("name"=>"receive_date_end", "label"=>"Until Receive Date", "value"=>date("d/m/Y")));
            $data["fields"][]      = form_select(array("name"=>"type", "label"=>"Report Type", "source"=>$selectType));
            # ---------------
            $data["buttons"][] = form_button_submit(array("name" => "button_window", "label" => "&nbsp;&nbsp;Preview&nbsp;&nbsp;"));
            # ---------------
            return view("default.form-report", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE REPORT INCOMING", "");
            # ---------------
            return view("error.405");
        }
    }

    public function report_result(Request $request) {
        $data["title"]              = "INCOMING TRANSMITTAL REPORT";
        $data["periode"]            = date("d/m/Y");
        $params                     = base64_encode($request->receive_date_start."|".$request->receive_date_end);
        # ---------------
        if($request->type == "SUMMARY") {
            $data["url_data"]           = url('/') . "/incoming/report_summary_json/" . $params;
            # ---------------
            $data["column_unit"]        = 2;
            $data["content_center"]     = "0,1,2,5,6,7";
            $data["content_right"]      = "";
            $data["token"]              = "";
        } else {
            $data["url_data"]           = url('/') . "/incoming/report_detail_json/" . $params;
            # ---------------
            $data["column_unit"]        = 2;
            $data["content_center"]     = "0,1,2,5,6,11,12,13,14";
            $data["content_right"]      = "";
            $data["token"]              = "";
        }
        # ---------------
        return view("default.report-datatable", $data);
    }

    public function report_summary_json($params){
        $query  = $this->qIncoming->getSummaryReport($params);
        
        return Datatables::of($query)->make(true); 
    }

    public function report_detail_json($params){
        $query  = $this->qIncoming->getDetailReport($params);
        
        return Datatables::of($query)->make(true); 
    }

public function get_document_status_old($id)
{
    $tipe = DB::table("ref_document_status")
        ->select('document_status_id AS id', 'name AS name')
        ->where('status', 1)
        ->orderByRaw("CAST(name AS UNSIGNED) ASC")
        ->get();

    return response()->json(['data' => $tipe->toArray()]);
}



public function get_document_status($id)
{
    $tipe = $this->qReference->getSelectDocumentStatusInternal();

    return response()->json(['data' => $tipe]);
}

    public function add_idc() {
        try {
            $data["title"]         = "Add Incoming IDC";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/incoming/save_idc";
            /* ----------
             Model
            ----------------------- */
            $selectDocument           = $this->qReference->getSelectDocumentIdc(Auth::user()->vendor_id);
            $selectIdcDocument        = $this->qReference->getSelectIdcDocument();
            $selectIssueStatus        = $this->qReference->getSelectIssueStatusIDC();
            $selectReturnStatus       = $this->qReference->getSelectReturnStatus();
            $selectVendor             = $this->qReference->getSelectVendor();
            $selectProject            = $this->qReference->getSelectProject();
            $selectDocumentStatus     = [];
            $selectDocumentStatusIFI  = array(array("id"=>120, "name"=>"0A"));
            // $selectDocumentStatus  = $this->qReference->getSelectDocumentStatus();
            # ---------------
            $data["items"]            = $this->qIncoming->getItemTempIDC();
            $number                   = $this->qIncoming->getLastNumberIDC();
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name"=>"vendor_id", "label"=>"Vendor", "mandatory"=>"yes", "value"=>Auth::user()->vendor_id));
            $data["fields"][]      = form_text(array("name"=>"incoming_no", "label"=>"Incoming Number", "mandatory"=>"yes", "readonly"=>"readonly", "first_selected"=>"yes", "value"=> $number));
            $data["fields"][]      = form_hidden(array("name"=>"receive_date", "label"=>($this->isVendor == "YES") ? "Sending Date" : "Receive Date", "mandatory"=>"yes", "value"=>date("d/m/Y")));
            $data["fields"][]      = form_hidden(array("name"=>"sender_date", "label"=>"Sender Date", "mandatory"=>"yes", "value"=>date("d/m/Y")));
            $data["fields"][]      = form_hidden(array("name"=>"subject", "label"=>"Subject", "mandatory"=>"yes", "value"=>"-"));
            $data["fields"][]      = form_hidden(array("name"=>"return_date_plan", "label"=>"Return Plan Date", "value"=>date("d/m/Y")));
            $data["fields"][]      = form_hidden(array("name"=>"return_date_actual", "label"=>"Return Plan Actual", "value"=>date("d/m/Y")));  
            $data["fields"][]      = form_hidden(array("name"=>"description", "label"=>"Remark", "value"=> "-"));          
            $data["fields"][]      = form_hidden(array("name"=>"receipt", "label"=>"Receipt", "value"=> ""));
            /* ----------
             Modal Fields
            ----------------------- */
            $data["fields_modal"][]= form_upload(array("name"=>"document_file", "label"=>"Document File"));
            $data["fields_modal"][]= form_upload(array("name"=>"document_crs", "label"=>"CRS File"));
            $data["fields_modal"][]= form_select(array("name"=>"document_id", "label"=>"Document Number", "source"=>$selectIdcDocument));
            $data["fields_modal"][]= form_select(array("name"=>"issue_status_id", "label"=>"Issue Status", "withnull"=>"yes", "readonly"=>"readonly", "source"=>$selectIssueStatus, "value"=> STATUS_ONLY_IDC));
            $data["fields_modal"][]= form_hidden(array("name"=>"document_status_id", "label"=>"Revision Number", "withnull"=>"yes", "value"=> 1));
            // $data["fields_modal"][]= form_select(array("name"=>"document_status_id", "label"=>"Revision Number", "withnull"=>"yes", "source"=>$selectDocumentStatus));
            $data["fields_modal"][]= form_hidden(array("name"=>"return_status_id", "label"=>"Return Status", "withnull"=>"yes", "source"=>$selectReturnStatus));
            $data["fields_modal"][]= form_text(array("name"=>"remark", "label"=>"Description"));
            
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_save", "label"=>"&nbsp;&nbsp;Save&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name"=>"button_cancel", "label"=>"Cancel"));
            # ---------------
            $data["attach_url"]    = "/incoming/attach_item_idc";
            $data["delete_url"]    = "/incoming/delete_item_idc";
            # ---------------
            return view("idc.form-add", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            throw $e;
            # ---------------
            return view("error.405");
        }        
    }

    public function attach_item_idc(Request $request) {
        try {
            $dataConfig     = $this->sysModel->getConfig();
            $extention      = $dataConfig->attachment_extention;
            $max_size       = $dataConfig->attachment_max_size;

            if($max_size > 0) {
                if(!empty($extention)) {
                    $validate_message   = "Attachment extention must $extention & maximum size " . number_format($max_size, 0) . " kb"; 
                    $rules              = array(
                        "document_file" => "required|mimes:$extention|max:$max_size",
                        // "document_crs" => "required|mimes:$extention|max:$max_size"
                    );
                } else {
                    $validate_message   = "Attachment maximum size " . number_format($max_size, 0) . " kb";
                    $rules = array(
                        "document_file" => "required|max:$max_size",
                        // "document_crs" => "required|max:$max_size"
                    );
                }
            } else {
                if(!empty($extention)) {
                    $validate_message   = "Attachment extention must $extention"; 
                    $rules              = array(
                        "document_file" => "required|mimes:$extention",
                        // "document_crs" => "required|mimes:$extention"
                    );
                } else {
                    $validate_message   = "Attachment is required kb";
                    $rules = array(
                        "document_file" => "required",
                        // "document_crs" => "required"
                    );
                }
            }            

            $messages = [
                
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                $response   = [
                    "status" => ERROR_STATUS_CODE,
                    "message" => $validate_message,
                    "data" => [],
                ];
            } else {
                // $cek_status     = $this->qDocument->get($request->document_id);
                // $true_status    = "F";

                // if($request->issue_status_id != 13) { //IFI
                //     if($cek_status->issue_status_id == 0) {
                //         $true_status    = "T";
                //     } else {
                //         if($cek_status->issue_status_id != $request->issue_status_id) {
                //             $true_status    = "F";
                //         } else {
                //             $true_status    = "T";
                //         }
                //     }    
                // } else {
                    $true_status    = "T";
                // }
                
                if($true_status == "T") {
                    $response   = $this->qIncoming->attachItemIDC($request);
                    
                    if($response["status"]) {
                        $items  = $this->qIncoming->getItemTempIDC();

                        $response   = [
                            "status" => SUCCESS_STATUS_CODE,
                            "message" => "Success",
                            "data" => $items
                        ];            
                    } else {
                        $response   = [
                            "status" => ERROR_STATUS_CODE,
                            "message" => "Failed !!!",
                            "data" => $response["message"],
                    ];
                    }
                } else {
                    $response   = [
                        "status" => ERROR_STATUS_CODE,
                        "message" => "Invalid issue status",
                        "data" => [],
                    ];
                }
            }
        } catch (\Exception $e) {
            $response   = [
                "status" => ERROR_STATUS_CODE,
                "message" => "Error !!!",
                "data" => [],
            ];
        }

        return response()->json($response, GLOBAL_SUCCESS_RESPONSE);



        // $response   = $this->qIncoming->attachItem($request);
        
        // if($response["status"]) {
        //     $items  = $this->qIncoming->getItemTemp();

        //     $response   = [
        //         "status" => SUCCESS_STATUS_CODE,
        //         "data" => $items
        //     ];            
        // } else {
        //     $response   = [
        //         "status" => ERROR_STATUS_CODE,
        //         "data" => [],
        //     ];
        // }

        // return response()->json($response, GLOBAL_SUCCESS_RESPONSE);
    }

    public function delete_item_idc($id) {
        $response   = $this->qIncoming->deleteItemIDC($id);
        
        if($response["status"]) {
            $items  = $this->qIncoming->getItemTempIDC();

            $response   = [
                "status" => SUCCESS_STATUS_CODE,
                "data" => $items
            ];            
        } else {
            $response   = [
                "status" => ERROR_STATUS_CODE,
                "data" => [],
            ];
        }

        return response()->json($response, GLOBAL_SUCCESS_RESPONSE);
    }

    public function vendorproject($id)
    {
        $tipe = DB::table('project')
                    ->select('project.vendor_id AS id', 'ref_vendor.name AS name')
                    ->join('ref_vendor', 'ref_vendor.vendor_id', '=', 'project.vendor_id')
                    ->where('project_id', $id)
                    ->get(['id', 'name']);
        return response()->json(['data' => $tipe->toArray()]);
    }
}
