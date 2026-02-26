<?php

namespace App\Model\Transmittal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Input;
use DB;
use Auth;
use Storage;
use File;
use DataTables;
use Fpdf;
use Mail;
use Excel;
use App\Mail\OutgoingMail;
use Carbon\Carbon;
use App\Model\Sys\LogModel;
use App\Model\Sys\SysModel;
use PDF;
use ZipArchive;

class OutgoingModel extends Model
{
    protected $table        = "outgoing_transmittal";
    protected $primaryKey   = "outgoing_transmittal_id";

    public function __construct() {
        $this->logModel     = new LogModel;   
        $this->sysModel     = new SysModel;
    }

    public function getCollections() {
        try {
            // $query  = DB::table($this->table)
            //                     ->select("$this->table.*", DB::RAW("DATE_FORMAT($this->table.sender_date, '%d/%m/%Y') AS sen_date")
            //                             , DB::RAW("COUNT(outgoing_transmittal_detail.document_id) AS unit"))
            //                     ->leftjoin("outgoing_transmittal_detail", "$this->table.outgoing_transmittal_id", "outgoing_transmittal_detail.outgoing_transmittal_id")
            //                     ->orderBy("$this->table.outgoing_transmittal_id", "DESC")
            //                     ->groupBy("$this->table.outgoing_transmittal_id");

            $query  = DB::table($this->table)
                                ->select("$this->table.*", DB::RAW("DATE_FORMAT($this->table.sender_date, '%d/%m/%Y') AS sen_date"), "ref_vendor.name AS vendor_name", DB::RAW("IF(ISNULL($this->table.sender_date), 'Draft', 'Sent') AS status_code"))
                                ->join("ref_vendor", "$this->table.vendor_id", "ref_vendor.vendor_id")
                                ->orderBy("$this->table.outgoing_transmittal_id", "DESC");

            if(session()->has("SES_SEARCH_OUTGOING_NO") != "") {
                $query->where("$this->table.outgoing_no", "LIKE", "%" . session()->get("SES_SEARCH_OUTGOING_NO") . "%");
            }

            if(session()->has("SES_SEARCH_OUTGOING_SUBJECT") != "") {
                $query->where("$this->table.subject", "LIKE", "%" . session()->get("SES_SEARCH_OUTGOING_SUBJECT") . "%");
            }

            if(session()->has("SES_SEARCH_OUTGOING_SENDING") != "") {
                $query->where("$this->table.sender_date", setYMD(session()->get("SES_SEARCH_OUTGOING_SENDING"), "/"));
            }

            if(Auth::user()->vendor_id != 0) {
                $query->where("$this->table.vendor_id", Auth::user()->vendor_id);
            } else {
                if (session()->has("SES_SEARCH_OUTGOING_VENDOR")) {
                    if (session()->get("SES_SEARCH_OUTGOING_VENDOR") != "0") {
                        $query->where("$this->table.vendor_id", session()->get("SES_SEARCH_OUTGOING_VENDOR"));
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

    public function getCollection_Vendors() {
        try {
            // $query  = DB::table($this->table)
            //                     ->select("$this->table.*", DB::RAW("DATE_FORMAT($this->table.sender_date, '%d/%m/%Y') AS sen_date")
            //                             , DB::RAW("COUNT(outgoing_transmittal_detail.document_id) AS unit"))
            //                     ->leftjoin("outgoing_transmittal_detail", "$this->table.outgoing_transmittal_id", "outgoing_transmittal_detail.outgoing_transmittal_id")
            //                     ->orderBy("$this->table.outgoing_transmittal_id", "DESC")
            //                     ->groupBy("$this->table.outgoing_transmittal_id");

            $query  = DB::table($this->table)
                                ->select("$this->table.*", DB::RAW("DATE_FORMAT($this->table.sender_date, '%d/%m/%Y') AS sen_date"), "ref_vendor.name AS vendor_name", DB::RAW("IF(ISNULL($this->table.sender_date), 'Draft', 'Sent') AS status_code"))
                                ->join("ref_vendor", "$this->table.vendor_id", "ref_vendor.vendor_id")
                                ->whereRaw("NOT ISNULL($this->table.sender_date)")
                                ->orderBy("$this->table.outgoing_transmittal_id", "DESC");

            if(session()->has("SES_SEARCH_OUTGOING_NO") != "") {
                $query->where("$this->table.outgoing_no", "LIKE", "%" . session()->get("SES_SEARCH_OUTGOING_NO") . "%");
            }

            if(session()->has("SES_SEARCH_OUTGOING_SUBJECT") != "") {
                $query->where("$this->table.subject", "LIKE", "%" . session()->get("SES_SEARCH_OUTGOING_SUBJECT") . "%");
            }

            if(session()->has("SES_SEARCH_OUTGOING_SENDING") != "") {
                $query->where("$this->table.sender_date", setYMD(session()->get("SES_SEARCH_OUTGOING_SENDING"), "/"));
            }

            if(Auth::user()->vendor_id != 0) {
                $query->where("$this->table.vendor_id", Auth::user()->vendor_id);
            } else {
                if (session()->has("SES_SEARCH_OUTGOING_VENDOR")) {
                    if (session()->get("SES_SEARCH_OUTGOING_VENDOR") != "0") {
                        $query->where("$this->table.vendor_id", session()->get("SES_SEARCH_OUTGOING_VENDOR"));
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

    public function saveOutgoingHeader($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            $qValidation    = DB::table("outgoing_transmittal")->where("outgoing_no", $request->outgoing_no)->get();

            if(count($qValidation) > 0) {
                return array("status"=>false, "message"=>"Outgoing number is already taken", "id"=>0);
            }
            # ------------------------
            $id     = DB::table("outgoing_transmittal")
                        ->insertGetId([
                            "outgoing_no"=>$request->outgoing_no,
                            "project_id"=>$request->project_id,
                            "vendor_id"=>$request->vendor_id,
                            "email_address"=>$request->email_address,
                            "cc_email_address"=>$request->cc_email_address,
                            "subject"=>$request->subject,
                            "content"=>$request->content,
                            "created_by"=>Auth::user()->id,
                            "created_at"=>now()
                        ]);

            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("INSERT OUTGOING TRANSMITTAL (" . $id . ")", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "message"=>"", "id"=>$id);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "INSERT OUTGOING TRANSMITTAL FAILED", "");
            # ---------------
            return array("status"=>false, "message"=>FAILED_MESSAGE, "id"=>0);
        }
    }

    public function updateOutgoingHeader($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table("outgoing_transmittal")
                ->where("outgoing_transmittal_id", "=", $request->idData)
                ->update([
                    "outgoing_no"=>$request->outgoing_no,
                    "project_id"=>$request->project_id,
                    "vendor_id"=>$request->vendor_id,
                    "email_address"=>$request->email_address,
                    "cc_email_address"=>$request->cc_email_address,
                    "subject"=>$request->subject,
                    "content"=>$request->content,
                    "created_by"=>Auth::user()->id,
                    "created_at"=>now()
                ]);

            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("UPDATE OUTGOING TRANSMITTAL (" . $request->idData . ")", Auth::user()->id, $request);
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
            $id    = $this->logModel->createError($e->getMessage(), "UPDATE OUTGOING TRANSMITTAL FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }

    // public function printOutgoing($request) {
    //     $qDataOutgoing    = DB::table("outgoing_transmittal")
    //                             ->select("outgoing_transmittal.*", "ref_vendor.name as name_vendor", "project.project_name as name_project")
    //                             ->leftjoin("ref_vendor", "outgoing_transmittal.vendor_id", "ref_vendor.vendor_id")
    //                             ->leftjoin("project", "outgoing_transmittal.project_id", "project.project_id")
    //                             ->where("outgoing_transmittal_id", $request->idData)
    //                             ->first();

    //     # ----------------
    //     Fpdf::AddPage();
    //     # ----------------
    //     Fpdf::SetLineWidth(0.3);
    //     Fpdf::SetFont("Arial", "B", 10);
    //     Fpdf::Cell(50, 5, env('APPS_COMPANY_NAME'), 0, 0, 'L');
    //     Fpdf::Ln();
    //     Fpdf::SetFont("Arial", "B", 8);
    //     Fpdf::Cell(170, 5, 'OUTGOING TRANSMITTAL', 0, 0, 'L');
    //     Fpdf::Ln(8);
    //     Fpdf::SetFont("Arial", "", 8);
    //     Fpdf::Cell(30, 5, 'Project Name', 0, 0, 'L');
    //     Fpdf::Cell(3, 5, ':', 0, 0, 'L');
    //     Fpdf::Cell(60, 5, $qDataOutgoing->name_project, 0, 0, 'L');
    //     Fpdf::Cell(5, 5, ' ', 0, 0, 'L');
    //     Fpdf::Cell(30, 5, 'Outgoing No', 0, 0, 'L');
    //     Fpdf::Cell(3, 5, ':', 0, 0, 'L');
    //     Fpdf::Cell(50, 5, $qDataOutgoing->outgoing_no, 0, 0, 'L');
    //     Fpdf::Ln();
    //     Fpdf::Cell(30, 5, 'Vendor Name', 0, 0, 'L');
    //     Fpdf::Cell(3, 5, ':', 0, 0, 'L');
    //     Fpdf::Cell(60, 5, $qDataOutgoing->name_vendor, 0, 0, 'L');
    //     Fpdf::Cell(5, 5, ' ', 0, 0, 'L');
    //     Fpdf::Cell(30, 5, 'Sending Date', 0, 0, 'L');
    //     Fpdf::Cell(3, 5, ':', 0, 0, 'L');
    //     Fpdf::Cell(50, 5, displayDMY($qDataOutgoing->sender_date), 0, 0, 'L');
    //     Fpdf::Ln();
    //     Fpdf::Cell(30, 5, 'Subject', 0, 0, 'L');
    //     Fpdf::Cell(3, 5, ':', 0, 0, 'L');
    //     Fpdf::Cell(150, 5, $qDataOutgoing->subject, 0, 0, 'L');
    //     Fpdf::Ln(8);
    //     Fpdf::SetFont("Arial", "B", 8);
    //     Fpdf::Cell(30, 5, 'Review Results', 0, 0, 'L');
    //     Fpdf::Ln();
            
    //     $pathDir        = DOCUMENT_PATH_OUTGOING . '/' .  $request->idData ;
    //     Storage::disk('uploads')->makeDirectory($pathDir, '0755', true,true);

    //     $file_url       = DOCUMENT_DIR_OUTGOING . '/' . $request->idData . "/";
    //     $file_name      = "Outgoing_". $request->idData . ".pdf";
        
    //     Fpdf::Output($file_url.$file_name,'F');

    // }

    // public function printReviewResult($request) {
    //     $qDataHeader    = DB::table("outgoing_transmittal_detail")
    //                             ->select("outgoing_transmittal_detail.*", "outgoing_transmittal.outgoing_no", "document.document_no", "document.document_title", "document.document_id", "incoming_transmittal_detail.document_url", "incoming_transmittal_detail.document_file", "ref_document_status.name AS document_status_name", "ref_issue_status.name AS issue_status_name", "ref_return_status.name AS return_status_name")
    //                             ->join("outgoing_transmittal", "outgoing_transmittal.outgoing_transmittal_id", "outgoing_transmittal_detail.outgoing_transmittal_id")
    //                             ->join("incoming_transmittal_detail", "incoming_transmittal_detail.incoming_transmittal_detail_id", "outgoing_transmittal_detail.incoming_transmittal_detail_id")
    //                             ->join("document", "document.document_id", "incoming_transmittal_detail.document_id")
    //                             ->leftJoin("ref_document_status", "outgoing_transmittal_detail.document_status_id", "ref_document_status.document_status_id")
    //                             ->leftJoin("ref_issue_status", "outgoing_transmittal_detail.issue_status_id", "ref_issue_status.issue_status_id")
    //                             ->leftJoin("ref_return_status", "outgoing_transmittal_detail.return_status_id", "ref_return_status.return_status_id")
    //                             ->where("outgoing_transmittal_detail.outgoing_transmittal_id", $request->idData)
    //                             ->orderBy("outgoing_transmittal_detail.outgoing_transmittal_detail_id")
    //                             ->get();

    //     foreach($qDataHeader as $rowHeader) {
    //         $qDataComment  = DB::table("assignment")
    //                                 ->select("comment.*", "sys_users.name as user_name")
    //                                 ->join("comment", "comment.assignment_id", "=", "assignment.assignment_id")
    //                                 ->leftjoin("sys_users", "sys_users.id", "=", "comment.user_id")
    //                                 ->where("assignment.incoming_transmittal_detail_id", $rowHeader->incoming_transmittal_detail_id)
    //                                 ->get();

    //         # ----------------
    //         Fpdf::AddPage();
    //         # ----------------
    //         Fpdf::SetLineWidth(0.3);
    //         Fpdf::SetFont("Arial", "B", 10);
    //         Fpdf::Cell(50, 5, env('APPS_COMPANY_NAME'), 0, 0, 'L');
    //         Fpdf::Ln();
    //         Fpdf::SetFont("Arial", "B", 8);
    //         Fpdf::Cell(170, 5, 'OUTGOING TRANSMITTAL', 0, 0, 'L');
    //         Fpdf::Ln();
    //         Fpdf::Cell(100, 5, 'NO : ' . $rowHeader->outgoing_no, 0, 0, 'L');
    //         Fpdf::Ln(8);
    //         Fpdf::SetFont("Arial", "", 8);
    //         Fpdf::Cell(30, 5, 'Document Number', 0, 0, 'L');
    //         Fpdf::Cell(3, 5, ':', 0, 0, 'L');
    //         Fpdf::Cell(60, 5, $rowHeader->document_no, 0, 0, 'L');
    //         Fpdf::Cell(5, 5, ' ', 0, 0, 'L');
    //         Fpdf::Cell(30, 5, 'Document Status', 0, 0, 'L');
    //         Fpdf::Cell(3, 5, ':', 0, 0, 'L');
    //         Fpdf::Cell(50, 5, $rowHeader->document_status_name, 0, 0, 'L');
    //         Fpdf::Ln();
    //         Fpdf::Cell(30, 5, 'Document Title', 0, 0, 'L');
    //         Fpdf::Cell(3, 5, ':', 0, 0, 'L');
    //         Fpdf::Cell(60, 5, $rowHeader->document_title, 0, 0, 'L');
    //         Fpdf::Cell(5, 5, ' ', 0, 0, 'L');
    //         Fpdf::Cell(30, 5, 'Issue Status', 0, 0, 'L');
    //         Fpdf::Cell(3, 5, ':', 0, 0, 'L');
    //         Fpdf::Cell(50, 5, $rowHeader->issue_status_name, 0, 0, 'L');
    //         Fpdf::Ln(8);
    //         Fpdf::SetFont("Arial", "B", 8);
    //         Fpdf::Cell(30, 5, 'Review Results', 0, 0, 'L');
    //         Fpdf::Ln();
    //         Fpdf::SetFont("Arial", "", 8);
    //         $nourut = 0;
    //         foreach($qDataComment as $rowComment) {
    //             $nourut++;
    //             $remark    = $rowComment->remark; 
                
    //             Fpdf::Cell(5, 5, $nourut, 0, 0, 'L');
    //             Fpdf::MultiCell(180, 5, $remark, 0, 'J');

    //             if($remark=="") {
    //               Fpdf::Ln();
    //             }
                
    //         }
    //         # ----------------
    //     }
        
    //     $pathDir        = DOCUMENT_PATH_OUTGOING . '/' .  $request->idData ;
    //     Storage::disk('uploads')->makeDirectory($pathDir, '0755', true,true);

    //     $file_url       = DOCUMENT_DIR_OUTGOING . '/' . $request->idData . "/";
    //     $file_name      = "Review_Result_". $request->idData . ".pdf";
        
    //     Fpdf::Output($file_url.$file_name,'F');
    // }

    public function printOutGoing($data) {
        $pdf = PDF::loadView('email.transmittal-letter', $data);
        $pdf->setPaper('A4', 'portrait');
        Storage::disk("uploads")->put($data['file_url_and_file_name'], $pdf->output());
        // return $pdf->stream($data['title'].'.pdf',array('Attachment'=>0));
    }

    public function updateOutgoingDetail($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            $actual     = date("Y-m-d");
            /* ----------
              CC
            ----------------------- */
                $qCC    = DB::table("outgoing_transmittal_detail")->select(DB::RAW("GROUP_CONCAT(DISTINCT(sys_users.email)) AS cc_email_address"))
                                                                  ->join("incoming_transmittal_detail", "outgoing_transmittal_detail.incoming_transmittal_detail_id", "incoming_transmittal_detail.incoming_transmittal_detail_id")
                                                                  ->join("assignment", "incoming_transmittal_detail.incoming_transmittal_detail_id", "assignment.incoming_transmittal_detail_id")
                                                                  ->join("comment", "assignment.assignment_id", "comment.assignment_id")
                                                                  ->join("sys_users", "comment.user_id", "sys_users.id")
                                                                  ->where("outgoing_transmittal_detail.outgoing_transmittal_id", $request->idData)
                                                                  ->first();
                $cc_email_address   = $qCC->cc_email_address;
            /* ----------
              Header
            ----------------------- */
                DB::table("outgoing_transmittal")
                                ->where("outgoing_transmittal_id", "=", $request->idData)
                                ->update([
                                    "email_address"=>$request->email_address,
                                    "cc_email_address"=>$request->cc_email_address,
                                    "subject"=>$request->subject,
                                    "content"=>$request->content,
                                    "sender_date"=>$actual,
                                    "status_email"=>$request->status_email,
                                ]);
                # -------------------
                DB::statement("UPDATE   document INNER JOIN incoming_transmittal_detail ON document.document_id = incoming_transmittal_detail.document_id
                               INNER    JOIN outgoing_transmittal_detail ON incoming_transmittal_detail.incoming_transmittal_detail_id = outgoing_transmittal_detail.incoming_transmittal_detail_id
                               SET      document.outgoing_transmittal_detail_id = outgoing_transmittal_detail.outgoing_transmittal_detail_id
                                        , document.status = 4
                               WHERE    outgoing_transmittal_detail.outgoing_transmittal_id = '$request->idData'");
                # -------------------
                DB::statement("UPDATE   incoming_transmittal INNER JOIN incoming_transmittal_detail ON incoming_transmittal.incoming_transmittal_id = incoming_transmittal_detail.incoming_transmittal_id
                               INNER    JOIN outgoing_transmittal_detail ON incoming_transmittal_detail.incoming_transmittal_detail_id = outgoing_transmittal_detail.incoming_transmittal_detail_id
                               SET      incoming_transmittal.return_date_actual = '$actual'
                               WHERE    outgoing_transmittal_detail.outgoing_transmittal_id = '$request->idData'");
            /* ----------
              Email
            ----------------------- */
                if($this->sysModel->getConfig()->email_status == 1) {
                    $title              = $request->subject;
                    $data["out_no"]     = $request->outgoing_no;
                    $data["subject"]    = $request->subject;
                    $data["content"]    = $request->content;
                    $emails             = explode(",", str_replace(" ", "", $request->email_address));
                    $email_cc           = explode(",", str_replace(" ", "", $cc_email_address));
                    # ---------------
                    $data["detail"]     = DB::table("outgoing_transmittal_detail")->select("document.document_no", "document.document_title"
                                                                                            , "ref_return_status.name AS return_status_name", "ref_issue_status.name AS issue_status_name", "ref_document_status.name AS document_status_name")
                                                                                    ->join("incoming_transmittal_detail", "outgoing_transmittal_detail.incoming_transmittal_detail_id", "incoming_transmittal_detail.incoming_transmittal_detail_id")
                                                                                    ->join("document", "incoming_transmittal_detail.document_id", "document.document_id")
                                                                                    ->join("ref_return_status", "outgoing_transmittal_detail.return_status_id", "ref_return_status.return_status_id")
                                                                                    ->join("ref_issue_status", "outgoing_transmittal_detail.issue_status_id", "ref_issue_status.issue_status_id")
                                                                                    ->leftJoin("ref_document_status", "outgoing_transmittal_detail.document_status_id", "ref_document_status.document_status_id")
                                                                                    ->where("outgoing_transmittal_detail.outgoing_transmittal_id", $request->idData)
                                                                                    ->get();
                    # --- untuk isi lampiran pdf ---                                                              
                    $data['title'] = 'TRANSMITTAL-LETTER '.$title;
                    $data['logo_medco']               = public_path() . "/app/img/icon/logo_medco.png";
                    $data['logo_hanochem']            = public_path() . "/app/img/icon/hanochem.png";
                    $data['logo_kanan_tengah']        = public_path() . "/app/img/icon/logo_kanan_tengah.png";
                    $data['logo_kanan_pojok']         = public_path() . "/app/img/icon/logo_kanan_pojok.png";
                    $data['logo_kiri']                = public_path() . "/app/img/icon/hanochem.png";
            
                    $data['project_name']               = $request->project_name;
                    $data['vendor_name']                = $request->vendor_name;
                    $data['vendor_pic']                 = $request->vendor_pic;
                    $data['vendor_address']             = $request->vendor_address;
                    $data['vendor_phone_number']        = $request->vendor_phone_number;
                    $data['file_url_and_file_name']     = DOCUMENT_DIR_OUTGOING . '/' . $request->idData . "/".$data["title"].'.pdf';
                    $data['issue_status_name']          = str_replace("Re-", "", array_column($data["detail"]->toArray(), 'issue_status_name'));
                    # ------------------------
                    $this->printOutGoing($data);

                    $fileName   = public_path('uploads') . $data['file_url_and_file_name'];
                    // $zip        = new ZipArchive;
                    // $compress   = false;
                    // $fileName   = public_path('uploads') . DOCUMENT_DIR_OUTGOING . '/' .$data["title"] . ".zip";
                    // $directory  = public_path('uploads') . DOCUMENT_DIR_OUTGOING . "/";
                    
                    // if($zip->open($fileName, ZipArchive::CREATE) === TRUE){
                    //     $location   = public_path('uploads') . DOCUMENT_DIR_OUTGOING . '/' . $request->idData;
                    //     $files      = File::files($location);
                    //     foreach ($files as $key => $value) {    
                    //         $relativeNameInZipFile = basename($value);
                    //         $compress = $zip->addFile($value, $relativeNameInZipFile);
                    //     }
                    // }

                    // if($compress) {
                    //     $zip->close();    
                    // } 

                    # ---------------
                    if($request->status_email > 1) {
                            Mail::send('email.outgoing-notification', $data, function($message) use ($title, $emails, $email_cc, $fileName){
                            $message->to($emails)->subject($title)
                                    ->attach($fileName)
                            ;
                            $message->cc($email_cc);
                            # ---------------
                            $message->from(env("MAIL_USERNAME"), 'Automatic Mail System');
                        });
                    }
                }
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("UPDATE OUTGOING TRANSMITTAL (" . $request->idData . ")", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);


            /* ----------
             Create Folder & File Name PDF Outgoing Transmittal
            ----------------------- */
            // $pathDir        = DOCUMENT_DIR_OUTGOING . '/' .  $request->idData ;
            // Storage::disk('uploads')->makeDirectory($pathDir, '0755', true,true);

            // $doc_url        = $pathDir ."/";
            // $file_url       = public_path("uploads") .'/'. $pathDir ."/";
            // $file_name      = "Outgoing_Transmittal_". $request->idData . ".pdf";
            
            // DB::table("outgoing_transmittal")
            //                 ->where("outgoing_transmittal_id", "=", $request->idData)
            //                 ->update([
            //                     "document_url_review"=>$doc_url,
            //                     "document_file_review"=>$file_name,
            //                     "status_email"=>$request->status_email
            //                 ]);

            // $qDataOutgoing    = DB::table("outgoing_transmittal")
            //                         ->select("outgoing_transmittal.*", "ref_vendor.name as name_vendor", "project.project_name as name_project")
            //                         ->leftjoin("ref_vendor", "outgoing_transmittal.vendor_id", "ref_vendor.vendor_id")
            //                         ->leftjoin("project", "outgoing_transmittal.project_id", "project.project_id")
            //                         ->where("outgoing_transmittal_id", $request->idData)
            //                         ->first();

            // # ----------------
            // Fpdf::AddPage();
            // # ----------------
            // Fpdf::SetLineWidth(0.3);
            // Fpdf::SetFont("Arial", "B", 10);
            // Fpdf::Cell(50, 5, env('APPS_COMPANY_NAME'), 0, 0, 'L');
            // Fpdf::Ln();
            // Fpdf::SetFont("Arial", "B", 8);
            // Fpdf::Cell(170, 5, 'OUTGOING TRANSMITTAL', 0, 0, 'L');
            // Fpdf::Ln(8);
            // Fpdf::SetFont("Arial", "", 8);
            // Fpdf::Cell(30, 5, 'Project Name', 0, 0, 'L');
            // Fpdf::Cell(3, 5, ':', 0, 0, 'L');
            // Fpdf::Cell(60, 5, $qDataOutgoing->name_project, 0, 0, 'L');
            // Fpdf::Cell(5, 5, ' ', 0, 0, 'L');
            // Fpdf::Cell(30, 5, 'Outgoing No', 0, 0, 'L');
            // Fpdf::Cell(3, 5, ':', 0, 0, 'L');
            // Fpdf::Cell(50, 5, $qDataOutgoing->outgoing_no, 0, 0, 'L');
            // Fpdf::Ln();
            // Fpdf::Cell(30, 5, 'Vendor Name', 0, 0, 'L');
            // Fpdf::Cell(3, 5, ':', 0, 0, 'L');
            // Fpdf::Cell(60, 5, $qDataOutgoing->name_vendor, 0, 0, 'L');
            // Fpdf::Cell(5, 5, ' ', 0, 0, 'L');
            // Fpdf::Cell(30, 5, 'Sending Date', 0, 0, 'L');
            // Fpdf::Cell(3, 5, ':', 0, 0, 'L');
            // Fpdf::Cell(50, 5, displayDMY($qDataOutgoing->sender_date), 0, 0, 'L');
            // Fpdf::Ln();
            // Fpdf::Cell(30, 5, 'Subject', 0, 0, 'L');
            // Fpdf::Cell(3, 5, ':', 0, 0, 'L');
            // Fpdf::Cell(150, 5, $qDataOutgoing->subject, 0, 0, 'L');
            // Fpdf::Ln(8);
            // Fpdf::SetFont("Arial", "", 8);
            // Fpdf::Cell(40, 5, 'Document Number', 1, 0, 'C');
            // Fpdf::Cell(90, 5, 'Document Title', 1, 0, 'C');
            // Fpdf::Cell(20, 5, 'Doc Status', 1, 0, 'C');
            // Fpdf::Cell(20, 5, 'Issue Status', 1, 0, 'C');
            // Fpdf::Cell(20, 5, 'Return Status', 1, 0, 'C');
            // Fpdf::Ln();
            
            // $qDataHeader    = DB::table("outgoing_transmittal_detail")
            //                         ->select("outgoing_transmittal_detail.*", "outgoing_transmittal.outgoing_no", "document.document_no", "document.document_title", "document.document_id", "document.document_id", "assignment.document_url", "assignment.document_file", "assignment.document_file_2", "ref_document_status.name AS document_status_name", "ref_issue_status.name AS issue_status_name", "ref_return_status.name AS return_status_name")
            //                         ->join("outgoing_transmittal", "outgoing_transmittal.outgoing_transmittal_id", "outgoing_transmittal_detail.outgoing_transmittal_id")
            //                         ->join("incoming_transmittal_detail", "incoming_transmittal_detail.incoming_transmittal_detail_id", "outgoing_transmittal_detail.incoming_transmittal_detail_id")
            //                         ->join("document", "document.document_id", "incoming_transmittal_detail.document_id")
            //                         ->join("assignment", "assignment.incoming_transmittal_detail_id", "outgoing_transmittal_detail.incoming_transmittal_detail_id")
            //                         ->leftJoin("ref_document_status", "outgoing_transmittal_detail.document_status_id", "ref_document_status.document_status_id")
            //                         ->leftJoin("ref_issue_status", "outgoing_transmittal_detail.issue_status_id", "ref_issue_status.issue_status_id")
            //                         ->leftJoin("ref_return_status", "outgoing_transmittal_detail.return_status_id", "ref_return_status.return_status_id")
            //                         ->where("outgoing_transmittal_detail.outgoing_transmittal_id", $request->idData)
            //                         ->orderBy("outgoing_transmittal_detail.outgoing_transmittal_detail_id")
            //                         ->get();

            // foreach($qDataHeader as $rowDocument) {
            //     Fpdf::Cell(40, 5, $rowDocument->document_no, 1, 0, 'C');
            //     Fpdf::Cell(90, 5, $rowDocument->document_title, 1, 0, 'C');
            //     Fpdf::Cell(20, 5, $rowDocument->document_status_name, 1, 0, 'C');
            //     Fpdf::Cell(20, 5, $rowDocument->issue_status_name, 1, 0, 'C');
            //     Fpdf::Cell(20, 5, $rowDocument->return_status_name, 1, 0, 'C');
            //     Fpdf::Ln();

            //     DB::table("document")
            //         ->where("document_id", "=", $rowDocument->document_id)
            //         ->update([
            //             "outgoing_transmittal_detail_id"=>$rowDocument->outgoing_transmittal_detail_id
            //         ]);
            // }

            // // foreach($qDataHeader as $rowHeader) {
            // //     $qDataComment  = DB::table("assignment")
            // //                             ->select("comment.*", "sys_users.name as user_name")
            // //                             ->join("comment", "comment.assignment_id", "=", "assignment.assignment_id")
            // //                             ->leftjoin("sys_users", "sys_users.id", "=", "comment.user_id")
            // //                             ->where("assignment.incoming_transmittal_detail_id", $rowHeader->incoming_transmittal_detail_id)
            // //                             ->get();

            // //     # ----------------
            // //     Fpdf::AddPage();
            // //     # ----------------
            // //     Fpdf::SetLineWidth(0.3);
            // //     Fpdf::SetFont("Arial", "B", 10);
            // //     Fpdf::Cell(50, 5, env('APPS_COMPANY_NAME'), 0, 0, 'L');
            // //     Fpdf::Ln();
            // //     Fpdf::SetFont("Arial", "B", 8);
            // //     Fpdf::Cell(170, 5, 'OUTGOING TRANSMITTAL', 0, 0, 'L');
            // //     Fpdf::Ln();
            // //     Fpdf::Cell(100, 5, 'NO : ' . $rowHeader->outgoing_no, 0, 0, 'L');
            // //     Fpdf::Ln(8);
            // //     Fpdf::SetFont("Arial", "", 8);
            // //     Fpdf::Cell(30, 5, 'Document Number', 0, 0, 'L');
            // //     Fpdf::Cell(3, 5, ':', 0, 0, 'L');
            // //     Fpdf::Cell(60, 5, $rowHeader->document_no, 0, 0, 'L');
            // //     Fpdf::Cell(5, 5, ' ', 0, 0, 'L');
            // //     Fpdf::Cell(30, 5, 'Document Status', 0, 0, 'L');
            // //     Fpdf::Cell(3, 5, ':', 0, 0, 'L');
            // //     Fpdf::Cell(50, 5, $rowHeader->document_status_name, 0, 0, 'L');
            // //     Fpdf::Ln();
            // //     Fpdf::Cell(30, 5, 'Document Title', 0, 0, 'L');
            // //     Fpdf::Cell(3, 5, ':', 0, 0, 'L');
            // //     Fpdf::Cell(60, 5, $rowHeader->document_title, 0, 0, 'L');
            // //     Fpdf::Cell(5, 5, ' ', 0, 0, 'L');
            // //     Fpdf::Cell(30, 5, 'Issue Status', 0, 0, 'L');
            // //     Fpdf::Cell(3, 5, ':', 0, 0, 'L');
            // //     Fpdf::Cell(50, 5, $rowHeader->issue_status_name, 0, 0, 'L');
            // //     Fpdf::Ln(8);
            // //     Fpdf::SetFont("Arial", "B", 8);
            // //     Fpdf::Cell(30, 5, 'Review Results', 0, 0, 'L');
            // //     Fpdf::Ln();
            // //     Fpdf::SetFont("Arial", "", 8);
            // //     $nourut = 0;
            // //     foreach($qDataComment as $rowComment) {
            // //         $nourut++;
            // //         $remark    = $rowComment->remark; 
                    
            // //         Fpdf::Cell(5, 5, $nourut, 0, 0, 'L');
            // //         Fpdf::MultiCell(180, 5, $remark, 0, 'J');

            // //         if($remark=="") {
            // //           Fpdf::Ln();
            // //         }
                    
            // //     }
            // //     # ----------------
            // // }
            
            // /* ----------
            //  Create File PDF Outgoing Transmittal
            // ----------------------- */
            // // $pathDir        = DOCUMENT_DIR_OUTGOING . '/' .  $request->idData ;
            // // Storage::disk('uploads')->makeDirectory($pathDir, '0755', true,true);

            // // $file_url       = public_path("uploads") .'/'. DOCUMENT_DIR_OUTGOING .'/'.$request->idData ."/";
            // // $file_name      = "Outgoing_Transmittal_". $request->idData . ".pdf";
            
            // Fpdf::Output($file_url.$file_name,'F');


            // /* ----------
            //  Send Email
            // ----------------------- */
            // if($request->status_email > 1) {    
            //     if($this->sysModel->getConfig()->email_status == 1) {
            //         DB::table("outgoing_transmittal")
            //             ->where("outgoing_transmittal_id", "=", $request->idData)
            //             ->update([
            //                 "sender_date"=>now()
            //             ]);

            //         $files = [];
            //         foreach($qDataHeader as $rowAttach) {
            //             array_push($files, public_path('uploads') . $rowAttach->document_url . $rowAttach->document_file);
            //         }

            //         foreach($qDataHeader as $rowAttach) {
            //             array_push($files, public_path('uploads') . $rowAttach->document_url . $rowAttach->document_file_2);
            //         }

            //         array_push($files, $file_url.$file_name);
              
            //         $details = [
            //             'title' => $request->subject,
            //             'body' => $request->content,
            //             'files' => $files
            //         ];
                    
            //         $reqEmail       = str_replace(" ", "", $request->email_address);
            //         $emailAddress   = explode(",", $reqEmail);

            //         if(!empty($request->cc_email_address)) {
            //             $reqCcEmail       = str_replace(" ", "", $request->cc_email_address);
            //             $ccEmailAddress   = explode(",", $reqCcEmail);

            //             Mail::to($emailAddress)
            //                 ->cc($ccEmailAddress)
            //                 ->send(new OutgoingMail($details));
            //         } else {
            //             Mail::to($emailAddress)
            //                 ->send(new OutgoingMail($details));
            //         }
            //     }
            // }
            // /* ----------
            //  Logs
            // ----------------------- */
            //     $this->logModel->createLog("UPDATE OUTGOING TRANSMITTAL (" . $request->idData . ")", Auth::user()->id, $request);
            // # ------------------------
            // DB::commit();
            // # ------------------------
            // return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "UPDATE OUTGOING TRANSMITTAL FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }

    public function resendEmailOutgoing($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            $qDataHeader    = DB::table("outgoing_transmittal_detail")
                                    ->select("outgoing_transmittal_detail.*", "assignment.document_url", "assignment.document_file", "assignment.document_file_2")
                                    ->join("incoming_transmittal_detail", "incoming_transmittal_detail.incoming_transmittal_detail_id", "outgoing_transmittal_detail.incoming_transmittal_detail_id")
                                    ->join("assignment", "assignment.incoming_transmittal_detail_id", "outgoing_transmittal_detail.incoming_transmittal_detail_id")
                                    ->where("outgoing_transmittal_detail.outgoing_transmittal_id", $request->idData)
                                    ->orderBy("outgoing_transmittal_detail.outgoing_transmittal_detail_id")
                                    ->get();

            /* ----------
             Send Email
            ----------------------- */
            if($this->sysModel->getConfig()->email_status == 1) {

                if($request->status_email < 2) {
                    DB::table("outgoing_transmittal")
                        ->where("outgoing_transmittal_id", "=", $request->idData)
                        ->update([
                            "sender_date"=>now()
                        ]);
                }

                $files = [];
                foreach($qDataHeader as $rowAttach) {
                    array_push($files, public_path('uploads') . $rowAttach->document_url . $rowAttach->document_file);
                }

                foreach($qDataHeader as $rowAttach) {
                    array_push($files, public_path('uploads') . $rowAttach->document_url . $rowAttach->document_file_2);
                }

                array_push($files, public_path('uploads') . $request->document_url_review . $request->document_file_review);
          
                $details = [
                    'title' => $request->subject,
                    'body' => $request->content,
                    'files' => $files
                ];
                
                $reqEmail       = str_replace(" ", "", $request->email_address);
                $emailAddress   = explode(",", $reqEmail);

                if(!empty($request->cc_email_address)) {
                    $reqCcEmail       = str_replace(" ", "", $request->cc_email_address);
                    $ccEmailAddress   = explode(",", $reqCcEmail);

                    Mail::to($emailAddress)
                        ->cc($ccEmailAddress)
                        ->send(new OutgoingMail($details));
                } else {
                    Mail::to($emailAddress)
                        ->send(new OutgoingMail($details));
                }
            }
            
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("RESEND EMAIL OUTGOING TRANSMITTAL (" . $request->idData . ")", Auth::user()->id, $request);
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
            $id    = $this->logModel->createError($e->getMessage(), "RESEND EMAIL OUTGOING TRANSMITTAL FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }

    public function insertOutgoingDetail($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            /* ----------
             Upload File
            ----------------------- */
                $file_name          = null;
                $file_name_crs      = null;
                if(!empty($request->document_file)) {
                    $default_file   = $request->document_file;
                    $file_url       = DOCUMENT_DIR_OUTGOING . '/' . $request->idData . "/";
                    # ------------------------
                    $file_content   = file_get_contents($request->document_file->getRealPath());
                    $file           = $request->file('document_file')->getClientOriginalName();
                    $file_name      = pathinfo($file,PATHINFO_FILENAME);
                    $file_name      = $file_name . "_" . date("YmdHis") . "." . $default_file->getClientOriginalExtension();
                    # ------------------------
                    Storage::disk("uploads")->put($file_url . $file_name, $file_content);    
                }
                # ------------------------
                if(!empty($request->document_crs)) {
                    $default_file_crs   = $request->document_crs;
                    # ------------------------
                    $file_content_crs   = file_get_contents($request->document_crs->getRealPath());
                    $file_crs           = $request->file('document_crs')->getClientOriginalName();
                    $file_name_crs      = pathinfo($file_crs,PATHINFO_FILENAME);
                    $file_name_crs      = $file_name_crs . "_" . date("YmdHis") . "." . $default_file_crs->getClientOriginalExtension();
                    # ------------------------
                    Storage::disk("uploads")->put($file_url . $file_name_crs, $file_content_crs);
                }
            /* ----------
             CRS
            ----------------------- */
                // Excel::load(Input::file('document_crs'), function($file) {
                //     dd($file);
                //     $sheet1 = $file->setActiveSheetIndex(0);

                //     Excel::create('New Doc', function($excel) use($sheet1) {
                //         $excel->addExternalSheet($sheet1);
                //     })->export('xls');
                // });

                // dd("x");
                // for($index = 0;$index < $countfiles;$index++){
                //     if(isset($_FILES['document_crs']['name'][$index]) && $_FILES['document_crs']['name'][$index] != ''){
                //         $filename   = $_FILES['document_crs']['name'][$index];
                //         $ext        = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                //         $valid_ext  = array("xls","xlsx");
                //         # ---------------
                //         if(in_array($ext, $valid_ext)){
                //             $path   = $file_url . $filename;
                //             if(move_uploaded_file($_FILES['document_crs']['tmp_name'][$index], $path)){
                //                 $files_arr[]    = $path;
                //             }
                //         }
                //     }
                // }
                // Excel::load(Input::file('document_crs[0]'), function($file) {
                //     $sheet1 = $file->setActiveSheetIndex(0);

                //     Excel::create('New Doc', function($excel) use($sheet1) {
                //         $excel->addExternalSheet($sheet1);
                //     })->export('xls');
                // });
            /* ----------
             Create
            ----------------------- */
                $query  = DB::table("incoming_transmittal_detail")
                                ->select("*")
                                ->where("incoming_transmittal_detail_id", $request->incoming_transmittal_detail_id)
                                ->first();
                # ------------------------
                $id     = DB::table("outgoing_transmittal_detail")
                            ->insert([
                                "outgoing_transmittal_id"=>$request->idData,
                                "incoming_transmittal_detail_id"=>$request->incoming_transmittal_detail_id,
                                "return_status_id"=>$request->return_status_id,
                                "issue_status_id"=>$query->issue_status_id,
                                "document_status_id"=>$query->document_status_id,
                                "document_url"=>$file_url,
                                "document_file"=>$file_name,
                                "document_crs"=>$file_name_crs
                            ]);
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("INSERT OUTGOING TRANSMITTAL DETAIL (" . $id . ")", Auth::user()->id, $request);
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
            $id    = $this->logModel->createError($e->getMessage(), "INSERT OUTGOING TRANSMITTAL DETAIL FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }

    public function removeOutgoingDetail($id) {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table("outgoing_transmittal_detail")
                ->where("outgoing_transmittal_detail_id", "=", $id)
                ->delete();

            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("DELETE OUTGOING TRANSMITTAL DETAIL (" . $id . ")", Auth::user()->id, $id);
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
            $id    = $this->logModel->createError($e->getMessage(), "DELETE OUTGOING TRANSMITTAL DETAIL FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }

    public function getHeader($id) {
        $query      = DB::table($this->table)->select("*")
                                           ->where("$this->primaryKey", $id)
                                           ->first();

        return $query;
    }

    // public function getDetail($id)
    // {
    //     $query  = DB::table($this->table)
    //                     ->select(
    //                         "$this->table.*",
    //                         "d.project_name",
    //                         "a.name as document_type_name",
    //                         "b.name as vendor_name",
    //                         "c.name as area_name",
    //                         "d.project_name",
    //                         "f.name as issue_status",
    //                         "y.document_url",
    //                         "y.document_file",
    //                         "z.assignment_id"
    //                     )
    //                     ->join("assignment as z", "$this->table.incoming_transmittal_detail_id", "z.incoming_transmittal_detail_id")
    //                     ->join("incoming_transmittal_detail as y", "$this->table.incoming_transmittal_detail_id", "y.incoming_transmittal_detail_id")
    //                     ->join("ref_document_type as a", "$this->table.document_type_id", "a.document_type_id")
    //                     ->leftjoin("ref_vendor as b", "$this->table.vendor_id", "b.vendor_id")
    //                     ->leftjoin("ref_area as c", "$this->table.area_id", "c.area_id")
    //                     ->leftjoin("project as d", "$this->table.project_id", "d.project_id")
    //                     ->leftjoin("ref_issue_status as f", "$this->table.issue_status_id", "f.issue_status_id")
    //                     ->where("$this->table.document_id", $id)
    //                     ->first();

    //     return $query;
    // }

    public function getDetail($id) {
        \DB::enableQueryLog();
        $query      = DB::table("outgoing_transmittal_detail")
                            ->select("outgoing_transmittal_detail.*", "outgoing_transmittal_detail.document_url AS outgoing_document_url", "outgoing_transmittal_detail.document_file AS outgoing_document_file", "outgoing_transmittal_detail.document_crs AS outgoing_document_crs", "outgoing_transmittal_detail.document_file_2 AS outgoing_document_file_2", "document.document_no", "document.document_title", "assignment.document_url", "assignment.document_file", "ref_document_status.name AS document_status_name", "ref_issue_status.name AS issue_status_name", "ref_return_status.name AS return_status_name")
                            ->join("incoming_transmittal_detail", "incoming_transmittal_detail.incoming_transmittal_detail_id", "outgoing_transmittal_detail.incoming_transmittal_detail_id")

                            ->join("incoming_transmittal", "incoming_transmittal_detail.incoming_transmittal_id", "incoming_transmittal.incoming_transmittal_id")
                            
                            ->join("document", "document.document_id", "incoming_transmittal_detail.document_id")
                            ->join("assignment", "assignment.incoming_transmittal_detail_id", "outgoing_transmittal_detail.incoming_transmittal_detail_id")
                            ->leftJoin("ref_document_status", "outgoing_transmittal_detail.document_status_id", "ref_document_status.document_status_id")
                            ->leftJoin("ref_issue_status", "outgoing_transmittal_detail.issue_status_id", "ref_issue_status.issue_status_id")
                            ->leftJoin("ref_return_status", "outgoing_transmittal_detail.return_status_id", "ref_return_status.return_status_id")
                            ->where("outgoing_transmittal_detail.outgoing_transmittal_id", $id)

                            ->where("incoming_transmittal.status", "!=", 3)

                            ->orderBy("outgoing_transmittal_detail.outgoing_transmittal_detail_id")
                            ->get();
        // dd(\DB::getQueryLog());
        return $query;
    }

    // public function emptyTemp() {
    //     DB::table("outgoing_transmittal_detail_temp")->where("outgoing_transmittal_detail_temp.created_by", Auth::user()->id)->delete();
    // }

    public function getSummaryReport($params) {
        try {
            list($sender_date_start, $sender_date_end) = explode("|", base64_decode($params));
            # -----------------
            $query  = DB::table($this->table)
                            ->select("$this->table.outgoing_no", "$this->table.sender_date", "ref_vendor.name AS vendor_name", "$this->table.email_address"
                                    , "$this->table.cc_email_address", "$this->table.subject", "$this->table.content", DB::RAW("COUNT(outgoing_transmittal_detail.outgoing_transmittal_detail_id) AS number_of_documents"))
                            ->join("outgoing_transmittal_detail", "$this->table.outgoing_transmittal_id", "outgoing_transmittal_detail.outgoing_transmittal_id")
                            ->join("ref_vendor", "$this->table.vendor_id", "ref_vendor.vendor_id")
                            ->groupBy("$this->table.outgoing_no", "$this->table.sender_date", "vendor_name", "$this->table.email_address", "$this->table.cc_email_address", "$this->table.subject", "$this->table.content")
                            ->orderBy("$this->table.sender_date");

            if($sender_date_start != '') {
                if($sender_date_end != '') {
                    $query->where("$this->table.sender_date", ">=", setYMD($sender_date_start, "/"));
                    $query->where("$this->table.sender_date", "<=", setYMD($sender_date_end, "/"));
                } else {
                    $query->where("$this->table.sender_date", setYMD($sender_date_end, "/"));
                }
                
            }

            return $query;
        } catch (\Exception $e) {
            return array("status"=>false, "error_log"=>$e->getMessage());
        }
    }

    public function getDetailReport($params) {
        try {
            list($sender_date_start, $sender_date_end) = explode("|", base64_decode($params));
            # -----------------
            $query  = DB::table($this->table)
                            ->select("$this->table.outgoing_no", "$this->table.sender_date", "ref_vendor.name AS vendor_name", "$this->table.email_address"
                                    , "$this->table.subject", "$this->table.content", "document.document_no", "document.document_title", "ref_return_status.name AS return_code")
                            ->join("outgoing_transmittal_detail", "$this->table.outgoing_transmittal_id", "outgoing_transmittal_detail.outgoing_transmittal_id")
                            ->leftjoin("incoming_transmittal_detail", "outgoing_transmittal_detail.incoming_transmittal_detail_id", "incoming_transmittal_detail.incoming_transmittal_detail_id")
                            ->leftjoin("document", "incoming_transmittal_detail.document_id", "document.document_id")
                            ->join("ref_vendor", "$this->table.vendor_id", "ref_vendor.vendor_id")
                            ->leftjoin("ref_return_status", "outgoing_transmittal_detail.return_status_id", "ref_return_status.return_status_id")
                            ->orderBy("$this->table.sender_date");

            if($sender_date_start != '') {
                if($sender_date_end != '') {
                    $query->where("$this->table.sender_date", ">=", setYMD($sender_date_start, "/"));
                    $query->where("$this->table.sender_date", "<=", setYMD($sender_date_end, "/"));
                } else {
                    $query->where("$this->table.sender_date", setYMD($sender_date_end, "/"));
                }
                
            }

            return $query;
        } catch (\Exception $e) {
            return array("status"=>false, "error_log"=>$e->getMessage());
        }
    }
}
