<?php

namespace App\Http\Controllers\Document;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Document\DocumentModel;
use App\Model\Project\ProjectModel;
use DB;
use View;
use Auth;
use Validator;
use Hash;
use Excel;
use PHPExcel;
use PHPExcel_IOFactory;
use Yajra\Datatables\Datatables;
use App\User;
use App\Model\UserManagement\MenuModel;
use App\Model\UserManagement\UserModel;
use App\Model\Reference\ReferenceModel;
use App\Model\Reference\VendorModel;
use App\Model\Reference\IssueStatusModel;
use App\Model\Sys\LogModel;
use App\Model\Sys\SysModel;

class DocumentController extends Controller
{
    protected $PROT_SideMenu, $PROT_Parent, $PROT_ModuleId, $PROT_ModuleName;

    public function __construct(Request $request)
    {
        # ---------------
        $uri                      = getUrl() . "/index";
        # ---------------
        $this->qMenu              = new MenuModel;
        $this->qUser              = new UserModel;
        $this->qReference         = new ReferenceModel;
        $this->qDocument          = new DocumentModel;
        $this->qVendor            = new VendorModel;
        $this->qProject           = new ProjectModel;
        $this->IssueStatus        = new IssueStatusModel;
        $this->logModel           = new LogModel;
        $this->sysModel           = new SysModel;
        # ---------------
        $rs                       = $this->qMenu->getParentMenu($uri);
        # ---------------
        $this->PROT_Parent        = (count($rs) > 0) ? $rs[0]->parent_name : '';
        $this->PROT_ModuleName    = (count($rs) > 0) ? $rs[0]->name : '';
        $this->PROT_ModuleId      = (count($rs) > 0) ? $rs[0]->id : '';
        $this->filter             = "";
        # ---------------
        View::share(array("SHR_Parent" => $this->PROT_Parent, "SHR_Module" => $this->PROT_ModuleName, "SHR_ModuleId" => $this->PROT_ModuleId));
    }

    // public function index(Request $request) {
    //     try {
    //         /* ----------
    //          Page Setting
    //         ----------------------- */
    //             $data["title"]            = ucwords(strtolower($this->PROT_ModuleName));
    //             $data["parent"]           = ucwords(strtolower($this->PROT_Parent));
    //             $data["form_act"]           = "/jurnal/index/";
    //             # ---------------------
    //             $data["filtered_info"]      = [];
    //         # ---------------
    //         # Advance Search
    //         # ---------------
    //             if(isset($request->module_id)) {
    //                 $project_name      = ($request->project_name != "") ? session(["SES_SEARCH_DOCUMENT_PROJECT_NAME" => $request->project_name]) : $request->session()->forget("SES_SEARCH_DOCUMENT_PROJECT_NAME");
    //             $document_no       = ($request->document_no  != "") ? session(["SES_SEARCH_DOCUMENT_NO" => $request->document_no]) : $request->session()->forget("SES_SEARCH_DOCUMENT_NO");
    //             $document_title    = ($request->document_title  != "") ? session(["SES_SEARCH_DOCUMENT_TITLE" => $request->document_title]) : $request->session()->forget("SES_SEARCH_DOCUMENT_TITLE");
    //             $document_type     = ($request->document_type  != "") ? session(["SES_SEARCH_DOCUMENT_TYPE" => $request->document_type]) : $request->session()->forget("SES_SEARCH_DOCUMENT_TYPE");
    //             $vendor_id         = ($request->vendor_id  != "") ? session(["SES_SEARCH_DOCUMENT_VENDOR" => $request->vendor_id]) : $request->session()->forget("SES_SEARCH_DOCUMENT_VENDOR");
    //             $area_id           = ($request->area_id  != "") ? session(["SES_SEARCH_DOCUMENT_AREA" => $request->area_id]) : $request->session()->forget("SES_SEARCH_DOCUMENT_AREA");
    //             $status            = ($request->status  != "") ? session(["SES_SEARCH_DOCUMENT_STATUS" => $request->status]) : $request->session()->forget("SES_SEARCH_DOCUMENT_STATUS");
    //             $issue_status      = ($request->issue_status  != "") ? session(["SES_SEARCH_DOCUMENT_ISSUE_STATUS" => $request->issue_status]) : $request->session()->forget("SES_SEARCH_DOCUMENT_ISSUE_STATUS");
    //             $doc_status        = ($request->status_doc  != "") ? session(["SES_SEARCH_DOCUMENT_DOC_STATUS" => $request->status_doc]) : $request->session()->forget("SES_SEARCH_DOCUMENT_DOC_STATUS");
    //             $deadline_awal     = ($request->deadline_awal  != "") ? session(["SES_SEARCH_DOCUMENT_DEADLINE_AWAL" => $request->deadline_awal]) : $request->session()->forget("SES_SEARCH_DOCUMENT_DEADLINE_AWAL");
    //             $deadline_akhir    = ($request->deadline_akhir  != "") ? session(["SES_SEARCH_DOCUMENT_DEADLINE_AKHIR" => $request->deadline_akhir]) : $request->session()->forget("SES_SEARCH_DOCUMENT_DEADLINE_AKHIR");
    //                 # ---------------
    //                 return redirect($data["form_act"]);
    //             }
    //             # ---------------
    //             if ($request->session()->has("SES_SEARCH_DOCUMENT_PROJECT_NAME")) {
    //                 array_push($data["filtered_info"], "PROJECT NAME");
    //                 # ---------------
    //                 $this->filter           .= "project_name=" . session()->get("SES_SEARCH_DOCUMENT_PROJECT_NAME") . '&';
    //             }
    //             # ---------------
    //             if ($request->session()->has("SES_SEARCH_DOCUMENT_NO")) {
    //                 array_push($data["filtered_info"], "DOCUMENT NO");
    //                 # ---------------
    //                 $this->filter           .= "document_no=" . session()->get("SES_SEARCH_DOCUMENT_NO") . '&';
    //             }
    //             # ---------------
    //             if ($request->session()->has("SES_SEARCH_DOCUMENT_TITLE")) {
    //                 array_push($data["filtered_info"], "DOCUMENT TITLE");
    //                 # ---------------
    //                 $this->filter           .= "document_title=" . session()->get("SES_SEARCH_DOCUMENT_TITLE") . '&';   
    //             }

    //             if ($request->session()->has("SES_SEARCH_DOCUMENT_TYPE")) {
    //                 if ($request->session()->get("SES_SEARCH_DOCUMENT_TYPE") != "0") {
    //                     array_push($data["filtered_info"], "DOCUMENT TYPE");
    //                     # ---------------
    //                     $this->filter           .= "document_type_id=" . session()->get("SES_SEARCH_DOCUMENT_TYPE") . '&';
    //                 }
    //             }

    //             if ($request->session()->has("SES_SEARCH_DOCUMENT_VENDOR")) {
    //                 if ($request->session()->get("SES_SEARCH_DOCUMENT_VENDOR") != "0") {
    //                     array_push($data["filtered_info"], "VENDOR");
    //                     # ---------------
    //                     $this->filter           .= "vendor_id=" . session()->get("SES_SEARCH_DOCUMENT_VENDOR") . '&';   
    //                 }
    //             }

    //             if ($request->session()->has("SES_SEARCH_DOCUMENT_AREA")) {
    //                 if ($request->session()->get("SES_SEARCH_DOCUMENT_AREA") != "0") {
    //                     array_push($data["filtered_info"], "AREA");
    //                     # ---------------
    //                     $this->filter           .= "area_id=" . session()->get("SES_SEARCH_DOCUMENT_AREA") . '&';
    //                 }
    //             }

    //             if ($request->session()->has("SES_SEARCH_DOCUMENT_STATUS")) {
    //                 if ($request->session()->get("SES_SEARCH_DOCUMENT_STATUS") != "0") {
    //                     array_push($data["filtered_info"], "STATUS");
    //                     # ---------------
    //                     $this->filter           .= "status=" . session()->get("SES_SEARCH_DOCUMENT_STATUS") . '&';
    //                 }
    //             }

    //             if ($request->session()->has("SES_SEARCH_DOCUMENT_ISSUE_STATUS")) {
    //                 if ($request->session()->get("SES_SEARCH_DOCUMENT_ISSUE_STATUS") != "0") {
    //                     array_push($data["filtered_info"], "ISSUE STATUS");
    //                     # ---------------
    //                     $this->filter           .= "issue_status_id=" . session()->get("SES_SEARCH_DOCUMENT_ISSUE_STATUS") . '&';
    //                 }
    //             }

    //             if ($request->session()->has("SES_SEARCH_DOCUMENT_DOC_STATUS")) {
    //                 if ($request->session()->get("SES_SEARCH_DOCUMENT_DOC_STATUS") != "0") {
    //                     array_push($data["filtered_info"], "DOCUMENT STATUS");
    //                     # ---------------
    //                     $this->filter           .= "document_status_id=" . session()->get("SES_SEARCH_DOCUMENT_ISSUE_STATUS") . '&';
    //                 }
    //             }

    //             if ($request->session()->has("SES_SEARCH_DOCUMENT_DEADLINE_AWAL")) {
    //                 array_push($data["filtered_info"], "DEADLINE AWAL");
    //                 # ---------------
    //                     $this->filter           .= "deadline_awal=" . session()->get("SES_SEARCH_DOCUMENT_DEADLINE_AWAL") . '&';
    //             }

    //             if ($request->session()->has("SES_SEARCH_DOCUMENT_DEADLINE_AKHIR")) {
    //                 array_push($data["filtered_info"], "DEADLINE AKHIR");
    //                 # ---------------
    //                     $this->filter           .= "deadline_akhir=" . session()->get("SES_SEARCH_DOCUMENT_DEADLINE_AKHIR") . '&';
    //             }
    //             # ---------------
    //             $data["form_ajax_url"]      = url('/') . "/api/jurnal/get_list?" . $this->filter;
    //         /* ------------
    //         Action Menu
    //         -------------------- */
    //             $data["action"]             = $this->qMenu->getActionMenu(Auth::user()->id, $this->PROT_ModuleId);
    //         /* ----------
    //          Collections
    //         ----------------------- */

    //         /* ----------
    //          Tables
    //         ----------------------- */
    //             $data["table_header"]               = ["No Voucher", "Tanggal Transaksi", "Keterangan", "Debet", "Kredit", "Cab Input", "Status"];
    //             $data["table_center"]               = "0,1,2,6,7";
    //             $data["table_right"]                = "4,5";
    //             $data["table_badge"]                = "7";
    //             $data["table_order"]                = 2;
    //             $data["table_list"]                 = array(array("data"=>"no_tiket"), array("data"=>"list_id"), array("data"=>"tanggal_transaksi"), array("data"=>"keterangan_tiket"), array("data"=>"debet"), array("data"=>"kredit"), array("data"=>"nm_cab"), array("data"=>"status_code"));
    //             $data["table_data"]                 = json_encode($data["table_list"]);
    //         /* ----------
    //          Filters
    //         ----------------------- */
    //             $data["fields"][]                   = form_hidden(array("name"=>"module_id", "label"=>"Module ID", "value"=>"SEARCH"));
    //             # ---------------
    //             $data["buttons"][]                  = form_button_submit(array("name"=>"button_search", "label"=>"&nbsp;&nbsp;&nbsp;&nbsp;Filter&nbsp;&nbsp;&nbsp;&nbsp;", "icon"=>"fa fa-filter"));
    //             $data["buttons"][]                  = form_action_button(array("name"=>"button_clear", "label"=>"&nbsp;&nbsp;Clear&nbsp;&nbsp;", "url"=>"/jurnal/unfilter/"));
    //         # ---------------
    //         return view("default.list-datatable", $data);
    //     } catch (\Exception $e) {
    //         throw $e;
    //         $this->logModel->createError($e->getMessage(), "PAGE DOCUMENT", "");
    //         # ---------------
    //         return view("error.405");
    //     }
    // }

    public function index(Request $request)
    {
        try {
            $data["title"]            = ucwords(strtolower($this->PROT_ModuleName));
            $data["parent"]           = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]         = "/document/index";
            $data["active_page"]      = (empty($page)) ? 1 : $page;
            $data["offset"]           = (empty($data["active_page"])) ? 0 : ($data["active_page"] - 1) * Auth::user()->perpage;
            /* ----------
             Source
            ----------------------- */

            # ---------------
            $data["filtered_info"]  = array();

            $qStatusDoc             = $this->qReference->getSelectDocumentStatus();
            $qIssueStatus           = $this->qReference->getSelectIssueStatus();
            $qVendor                = $this->qReference->getSelectVendor();
            $qArea                  = $this->qReference->getSelectArea();
            $qDocType               = $this->qReference->getSelectDocumentType();
            $qStatus                = getSelectStatusDocument();
            # ---------------
            $data["action"]         = $this->qMenu->getActionMenu(Auth::user()->id, $this->PROT_ModuleId);
            /* ----------
             Table header
            ----------------------- */
            $data["table_header"]   = array(
                array(
                    "label" => "ID", "name" => "document_id", "align" => "center", "item-align" => "center", "item-format" => "checkbox", "item-class" => "", "width" => "20px", "add-style" => ""
                ),
                array(
                    "label" => "Document No", "name" => "document_no", "align" => "center", "item-align" => "center", "item-format" => "normal", "item-class" => "", "width" => "20%", "add-style" => ""
                ),
                array(
                    "label" => "Document Title", "name" => "document_title", "align" => "center", "item-align" => "left", "item-format" => "normal", "item-class" => "", "width" => "", "add-style" => ""
                ),
                array(
                    "label" => "Vendor", "name" => "vendor_name", "align" => "center", "item-align" => "left", "item-format" => "normal", "item-class" => "", "width" => "12%", "add-style" => ""
                ),
                array(
                    "label" => "Deadline", "name" => "deadline", "align" => "center", "item-align" => "center", "item-format" => "normal", "item-class" => "", "width" => "10%", "add-style" => ""
                ),
                array(
                    "label" => "Issue Status", "name" => "issue_status", "align" => "center", "item-align" => "center", "item-format" => "flag", "item-class" => "", "width" => "10%", "add-style" => ""
                ),
                array(
                    "label" => "Progress", "name" => "unit", "align" => "center", "item-align" => "center", "item-format" => "progress", "item-class" => "", "width" => "10%", "add-style" => ""
                ),
                array(
                    "label" => "Status", "name" => "status_code", "align" => "center", "item-align" => "center", "item-format" => "flag", "item-class" => "", "width" => "5%", "add-style" => ""
                ),
            );
            # ---------------
            $data["query"]         = $this->qDocument->getCollections();
            $data["select"]        = $data["query"]["data"];
            $data["pagging"]       = getPagging($data["select"]);
            # ---------------
            # Advance Search
            # ---------------
            if (isset($request->module_id)) {
                $project_name      = ($request->project_name != "") ? session(["SES_SEARCH_DOCUMENT_PROJECT_NAME" => $request->project_name]) : $request->session()->forget("SES_SEARCH_DOCUMENT_PROJECT_NAME");
                $document_no       = ($request->document_no  != "") ? session(["SES_SEARCH_DOCUMENT_NO" => $request->document_no]) : $request->session()->forget("SES_SEARCH_DOCUMENT_NO");
                $document_title    = ($request->document_title  != "") ? session(["SES_SEARCH_DOCUMENT_TITLE" => $request->document_title]) : $request->session()->forget("SES_SEARCH_DOCUMENT_TITLE");
                $document_type     = ($request->document_type  != "") ? session(["SES_SEARCH_DOCUMENT_TYPE" => $request->document_type]) : $request->session()->forget("SES_SEARCH_DOCUMENT_TYPE");
                $vendor_id         = ($request->vendor_id  != "") ? session(["SES_SEARCH_DOCUMENT_VENDOR" => $request->vendor_id]) : $request->session()->forget("SES_SEARCH_DOCUMENT_VENDOR");
                $area_id           = ($request->area_id  != "") ? session(["SES_SEARCH_DOCUMENT_AREA" => $request->area_id]) : $request->session()->forget("SES_SEARCH_DOCUMENT_AREA");
                $status            = ($request->status  != "") ? session(["SES_SEARCH_DOCUMENT_STATUS" => $request->status]) : $request->session()->forget("SES_SEARCH_DOCUMENT_STATUS");
                $issue_status      = ($request->issue_status  != "") ? session(["SES_SEARCH_DOCUMENT_ISSUE_STATUS" => $request->issue_status]) : $request->session()->forget("SES_SEARCH_DOCUMENT_ISSUE_STATUS");
                $doc_status        = ($request->status_doc  != "") ? session(["SES_SEARCH_DOCUMENT_DOC_STATUS" => $request->status_doc]) : $request->session()->forget("SES_SEARCH_DOCUMENT_DOC_STATUS");
                $deadline_awal     = ($request->deadline_awal  != "") ? session(["SES_SEARCH_DOCUMENT_DEADLINE_AWAL" => $request->deadline_awal]) : $request->session()->forget("SES_SEARCH_DOCUMENT_DEADLINE_AWAL");
                $deadline_akhir    = ($request->deadline_akhir  != "") ? session(["SES_SEARCH_DOCUMENT_DEADLINE_AKHIR" => $request->deadline_akhir]) : $request->session()->forget("SES_SEARCH_DOCUMENT_DEADLINE_AKHIR");

                # ---------------
                return redirect("/document/index");
            }
            # ---------------
            if ($request->session()->has("SES_SEARCH_DOCUMENT_PROJECT_NAME")) {
                array_push($data["filtered_info"], "PROJECT NAME");
            }
            # ---------------
            if ($request->session()->has("SES_SEARCH_DOCUMENT_NO")) {
                array_push($data["filtered_info"], "DOCUMENT NO");
            }
            # ---------------
            if ($request->session()->has("SES_SEARCH_DOCUMENT_TITLE")) {
                array_push($data["filtered_info"], "DOCUMENT TITLE");
            }

            if ($request->session()->has("SES_SEARCH_DOCUMENT_TYPE")) {
                if ($request->session()->get("SES_SEARCH_DOCUMENT_TYPE") != "0") {
                    array_push($data["filtered_info"], "DOCUMENT TYPE");
                }
            }

            if ($request->session()->has("SES_SEARCH_DOCUMENT_VENDOR")) {
                if ($request->session()->get("SES_SEARCH_DOCUMENT_VENDOR") != "0") {
                    array_push($data["filtered_info"], "VENDOR");
                }
            }

            if ($request->session()->has("SES_SEARCH_DOCUMENT_AREA")) {
                if ($request->session()->get("SES_SEARCH_DOCUMENT_AREA") != "0") {
                    array_push($data["filtered_info"], "AREA");
                }
            }

            if ($request->session()->has("SES_SEARCH_DOCUMENT_STATUS")) {
                if ($request->session()->get("SES_SEARCH_DOCUMENT_STATUS") != "0") {
                    array_push($data["filtered_info"], "STATUS");
                }
            }

            if ($request->session()->has("SES_SEARCH_DOCUMENT_ISSUE_STATUS")) {
                if ($request->session()->get("SES_SEARCH_DOCUMENT_ISSUE_STATUS") != "0") {
                    array_push($data["filtered_info"], "ISSUE STATUS");
                }
            }

            if ($request->session()->has("SES_SEARCH_DOCUMENT_DOC_STATUS")) {
                if ($request->session()->get("SES_SEARCH_DOCUMENT_DOC_STATUS") != "0") {
                    array_push($data["filtered_info"], "DOCUMENT STATUS");
                }
            }

            if ($request->session()->has("SES_SEARCH_DOCUMENT_DEADLINE_AWAL")) {
                array_push($data["filtered_info"], "START DATE");
            }

            if ($request->session()->has("SES_SEARCH_DOCUMENT_DEADLINE_AKHIR")) {
                array_push($data["filtered_info"], "END DATE");
            }

            # ---------------
            $data["adv_search"]    = true;
            $data["hide_simple_search"] = true;
            # ---------------
            $data["fields"][]      = form_hidden(array("name" => "module_id", "label" => "Module ID", "value" => "DOCUMENT"));
            $data["fields"][]      = form_search_text(array("name" => "project_name", "label" => "Project Name", "value" => ($request->session()->has("SES_SEARCH_DOCUMENT_PROJECT_NAME")) ? $request->session()->get("SES_SEARCH_DOCUMENT_PROJECT_NAME") : ""));
            $data["fields"][]      = form_search_text(array("name" => "document_no", "label" => "Document No", "value" => ($request->session()->has("SES_SEARCH_DOCUMENT_NO")) ? $request->session()->get("SES_SEARCH_DOCUMENT_NO") : ""));
            $data["fields"][]      = form_search_text(array("name" => "document_title", "label" => "Document Title", "value" => ($request->session()->has("SES_SEARCH_DOCUMENT_TITLE")) ? $request->session()->get("SES_SEARCH_DOCUMENT_TITLE") : ""));
            $data["fields"][]      = form_search_select(array("name" => "document_type", "label" => "Document Type", "source" => $qDocType,  "withnull" => "yes", "value" => ($request->session()->has("SES_SEARCH_DOCUMENT_TYPE")) ? $request->session()->get("SES_SEARCH_DOCUMENT_TYPE") : ""));
            $data["fields"][]      = form_search_select(array("name" => "vendor_id", "label" => "Vendor", "source" => $qVendor,  "withnull" => "yes", "value" => ($request->session()->has("SES_SEARCH_DOCUMENT_VENDOR")) ? $request->session()->get("SES_SEARCH_DOCUMENT_VENDOR") : ""));
            $data["fields"][]      = form_search_select(array("name" => "area_id", "label" => "Area", "source" => $qArea,  "withnull" => "yes", "value" => ($request->session()->has("SES_SEARCH_DOCUMENT_AREA")) ? $request->session()->get("SES_SEARCH_DOCUMENT_AREA") : ""));
            $data["fields"][]      = form_search_select(array("name" => "status", "label" => "Status", "source" => $qStatus,  "withnull" => "yes", "value" => ($request->session()->has("SES_SEARCH_DOCUMENT_STATUS")) ? $request->session()->get("SES_SEARCH_DOCUMENT_STATUS") : ""));
            $data["fields"][]      = form_search_select(array("name" => "status_doc", "label" => "Revision Number", "source" => $qStatusDoc,  "withnull" => "yes", "value" => ($request->session()->has("SES_SEARCH_DOCUMENT_DOC_STATUS")) ? $request->session()->get("SES_SEARCH_DOCUMENT_DOC_STATUS") : ""));
            $data["fields"][]      = form_search_select(array("name" => "issue_status", "label" => "Issue Status", "source" => $qIssueStatus,  "withnull" => "yes", "value" => ($request->session()->has("SES_SEARCH_DOCUMENT_ISSUE_STATUS")) ? $request->session()->get("SES_SEARCH_DOCUMENT_ISSUE_STATUS") : ""));
            $data["fields"][]      = form_search_datepicker(array("name" => "deadline_awal", "label" => "Start Date", "value" => ($request->session()->has("SES_SEARCH_DOCUMENT_DEADLINE_AWAL")) ? $request->session()->get("SES_SEARCH_DOCUMENT_DEADLINE_AWAL") : ""));
            $data["fields"][]      = form_search_datepicker(array("name" => "deadline_akhir", "label" => "End Date", "value" => ($request->session()->has("SES_SEARCH_DOCUMENT_DEADLINE_AKHIR")) ? $request->session()->get("SES_SEARCH_DOCUMENT_DEADLINE_AKHIR") : ""));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_search", "label" => "&nbsp;&nbsp;Search&nbsp;&nbsp;"));
            $data["buttons"][]     = form_action_button(array("name" => "button_clear", "label" => "&nbsp;&nbsp;Clear&nbsp;&nbsp;", "url" => "/document/unfilter"));
            # ---------------
            return view("document.list", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE DOCUMENT", "");
            # ---------------
            return view("error.405");
        }
    }

    public function unfilter()
    {
        session()->forget("SES_SEARCH_DOCUMENT_PROJECT_NAME");
        session()->forget("SES_SEARCH_DOCUMENT_NO");
        session()->forget("SES_SEARCH_DOCUMENT_TITLE");
        session()->forget("SES_SEARCH_DOCUMENT_TYPE");
        session()->forget("SES_SEARCH_DOCUMENT_VENDOR");
        session()->forget("SES_SEARCH_DOCUMENT_AREA");
        session()->forget("SES_SEARCH_DOCUMENT_STATUS");
        session()->forget("SES_SEARCH_DOCUMENT_ISSUE_STATUS");
        session()->forget("SES_SEARCH_DOCUMENT_DOC_STATUS");
        session()->forget("SES_SEARCH_DOCUMENT_DEADLINE_AWAL");
        session()->forget("SES_SEARCH_DOCUMENT_DEADLINE_AKHIR");
        # ---------------
        return redirect("/document/index");
    }

    public function add()
    {
        try {

            $data["title"]         = "Add Document";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/document/save";
            /* ----------
             Model
            ----------------------- */
            $selectProject         = $this->qReference->getSelectProject();
            $selectDocType         = $this->qReference->getSelectDocumentType();
            $selectVendor          = $this->qReference->getSelectVendor();
            $selectArea            = $this->qReference->getSelectArea();
            $selectDepartemen      = $this->qReference->getSelectDepartment();
            $selectUser            = $this->qReference->getSelectUser();
            $valueApproval         = $this->sysModel->getConfig()->default_approval_id;
            $selectRole            = getSelectRoleAssignment();
            # ---------------
            $this->qDocument->emptyTemp();
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_select(array("name" => "project_id", "label" => "Project",  "source" => $selectProject));
            $data["fields"][]      = form_select(array("name" => "vendor_id", "label" => "Vendor",  "mandatory" => "yes", "source" => $selectVendor));
            $data["fields"][]      = form_text(array("name" => "document_no", "label" => "Document Number", "mandatory" => "yes", "first_selected" => "yes"));
            $data["fields"][]      = form_text(array("name" => "document_title", "label" => "Title", "mandatory" => "yes"));
            $data["fields"][]      = form_textarea(array("name" => "document_description", "label" => "Description"));
            $data["fields"][]      = form_select(array("name" => "document_type", "label" => "Type", "mandatory" => "yes", "source" => $selectDocType));
            $data["fields"][]      = form_select(array("name" => "area_id", "label" => "Area", "mandatory" => "yes", "source" => $selectArea));
            $data["fields"][]      = form_text(array("name" => "ref_no", "label" => "Ref Number"));
            $data["fields"][]      = form_select(array("name" => "pic", "label" => "PIC", "mandatory" => "yes", "source" => $selectUser));
            $data["fields"][]      = form_select(array("name" => "departemen_id", "label" => "Departemen",  "mandatory" => "yes", "source" => $selectDepartemen));
            $data["fields"][]      = form_select(array("name" => "approval_by", "label" => "Approval By", "mandatory" => "yes", "value" => $valueApproval, "source" => $selectUser));
            /* ----------
             Modal Fields
            ----------------------- */
            $data["fields_modal"][] = form_select(array("name" => "user_id", "label" => "User", "mandatory" => "yes", "source" => $selectUser));
            $data["fields_modal"][] = form_select(array("name" => "role", "label" => "Role", "mandatory" => "yes", "source" => $selectRole));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Save&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));
            # ---------------
            $data["save_modal_url"]    = "/document/save_user_temp";
            $data["delete_modal_url"]    = "/document/delete_user_temp";
            # ---------------
            return view("document.form-add", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE DOCUMENT", "");
            # ---------------
            return view("error.405");
        }
    }

    public function save_user_temp(Request $request)
    {
        $response   = $this->qDocument->addUserTemp($request);

        if ($response["status"]) {
            $items  = $this->qDocument->getUserTemp();

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

    public function delete_user_temp($id)
    {
        $response   = $this->qDocument->deleteUserTemp($id);

        if ($response["status"]) {
            $items  = $this->qDocument->getUserTemp();

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

    public function save(Request $request)
    {
        try {
            $rules = array(
                'document_no' => 'required|',
                'document_title' => 'required|',
            );

            $messages = [
                'document_no.required' => 'Document number is required',
                'document_title.required' => 'Document title is required',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("/document/add")
                    ->withErrors($validator)
                    ->withInput();
            } else {

                $checkDocNo = $this->qDocument->checkDuplicateDocumentNo($request->document_no);

                if ($checkDocNo == 0) {
                    $response   = $this->qDocument->saveDocument($request);
                    if ($response["status"]) {
                        session()->flash("success_message", SUCCESS_MESSAGE);
                    } else {
                        session()->flash("error_message", FAILED_MESSAGE);
                    }
                    # ---------------
                    return redirect("/document/index");
                } else {
                    return redirect("/document/add")
                        ->withErrors("Document number already exists")
                        ->withInput();

                    # ---------------
                    return redirect("/document/add");
                }
            }
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE ADD INCOMING", "");
            # ---------------
            return view("error.405");
        }
    }

    public function edit($id_enc)
    {
        try {

            $data["title"]         = "Edit Document";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/document/update";

            $id                    = decodedData($id_enc);
            /* ----------
             Model
            ----------------------- */
            $selectProject         = $this->qReference->getSelectProject();
            $selectDocType         = $this->qReference->getSelectDocumentType();
            $selectVendor          = $this->qReference->getSelectVendor();
            $selectArea            = $this->qReference->getSelectArea();
            $selectDepartemen      = $this->qReference->getSelectDepartment();
            $selectUser            = $this->qReference->getSelectUser();
            # ---------------
            $qData = $this->qDocument->getDataById($id);

            if ($qData->status == 6) {
                session()->flash("error_message", "Documents cannot be changed");
                # ---------------
                return redirect("/document/index");
            }

            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name" => "id_enc", "label" => "ID Encrypt", "mandatory" => "yes", "readonly" => "readonly", "value" => $id_enc));
            $data["fields"][]      = form_hidden(array("name" => "document_id", "label" => "Document Id", "mandatory" => "yes", "readonly" => "readonly", "value" => $id));
            $data["fields"][]      = form_select(array("name" => "project_id", "label" => "Project", "withnull" => "withnull", "source" => $selectProject, "value" => $qData->project_id));
            $data["fields"][]      = form_select(array("name" => "vendor_id", "label" => "Vendor", "withnull" => "withnull",  "mandatory" => "yes", "source" => $selectVendor, "value" => $qData->vendor_id));
            $data["fields"][]      = form_hidden(array("name" => "document_no_old", "label" => "Document Number Old", "mandatory" => "yes", "first_selected" => "yes", "value" => $qData->document_no));
            $data["fields"][]      = form_text(array("name" => "document_no", "label" => "Document Number", "mandatory" => "yes", "first_selected" => "yes", "value" => $qData->document_no));
            $data["fields"][]      = form_hidden(array("name" => "document_title_old", "label" => "Title Old", "mandatory" => "yes", "value" => $qData->document_title));
            $data["fields"][]      = form_text(array("name" => "document_title", "label" => "Title", "mandatory" => "yes", "value" => $qData->document_title));
            $data["fields"][]      = form_textarea(array("name" => "document_description", "label" => "Description", "value" => $qData->document_description));
            $data["fields"][]      = form_select(array("name" => "document_type", "label" => "Type", "withnull" => "withnull",  "mandatory" => "yes", "source" => $selectDocType, "value" => $qData->document_type_id));
            $data["fields"][]      = form_select(array("name" => "area_id", "label" => "Area", "withnull" => "withnull",  "mandatory" => "yes", "source" => $selectArea, "value" => $qData->area_id));
            $data["fields"][]      = form_text(array("name" => "ref_no", "label" => "Ref Number", "value" => $qData->ref_no));
            $data["fields"][]      = form_select(array("name" => "pic", "label" => "PIC", "withnull" => "withnull",  "mandatory" => "yes", "source" => $selectUser, "value" => $qData->pic_id));
            $data["fields"][]      = form_select(array("name" => "departemen_id", "label" => "Departemen", "withnull" => "withnull",  "mandatory" => "yes", "source" => $selectDepartemen, "value" => $qData->department_id));
            $data["fields"][]      = form_hidden(array("name" => "approval_by", "label" => "Approval By", "mandatory" => "yes", "source" => $selectUser, "value" => $qData->approved_by));

            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Update&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));

            # ---------------
            return view("default.form", $data);
        } catch (\Exception $e) {
            throw $e;
            $this->logModel->createError($e->getMessage(), "PAGE DOCUMENT", "");
            # ---------------
            return view("error.405");
        }
    }

    public function update(Request $request)
    {
        try {
            $rules = array(
                'document_no' => 'required|',
                'document_title' => 'required|',
            );

            $messages = [
                'document_no.required' => 'Document number is required',
                'document_title.required' => 'Document title is required',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("/document/edit/" . $request->id_enc)
                    ->withErrors($validator)
                    ->withInput();
            } else {
                if ($request->document_no_old != $request->document_no) {
                    $checkDocNo = $this->qDocument->checkDuplicateDocumentNo($request->document_no);

                    if ($checkDocNo > 0) {
                        return redirect("/document/edit/" . $request->id_enc)
                            ->withErrors("Document number already exists")
                            ->withInput();
                    }
                }

                $response   = $this->qDocument->updateDocument($request);

                if ($response["status"]) {
                    session()->flash("success_message", SUCCESS_MESSAGE);
                } else {
                    session()->flash("error_message", FAILED_MESSAGE);
                }
                # ---------------
                return redirect("/document/index");
            }
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE EDIT DOCUMENT", "");
            # ---------------
            return view("error.405");
        }
    }

    public function delete($id_enc)
    {
        try {
            $data["title"]         = "Delete Document";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/document/remove";
            /* ----------
             Model
            ----------------------- */
            $id                 = decodedData($id_enc);
            $qData = $this->qDocument->getDataById($id);

            if ($qData->status == 6) {
                session()->flash("error_message", "Documents cannot be deleted");
                # ---------------
                return redirect("/document/index");
            }
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name" => "id_enc", "label" => "ID", "value" => $id_enc));
            $data["fields"][]      = form_hidden(array("name" => "document_id", "label" => "Document ID", "mandatory" => "yes", "value" => $id));
            $data["fields"][]      = form_text(array("name" => "project_name", "label" => "Project Name", "mandatory" => "yes", "value" => $qData->project_name, "readonly" => "readonly"));
            $data["fields"][]      = form_text(array("name" => "vendor_name", "label" => "Vendor Name", "mandatory" => "yes", "value" => $qData->vendor_name, "readonly" => "readonly"));
            $data["fields"][]      = form_text(array("name" => "document_no", "label" => "Document Number", "mandatory" => "yes", "value" => $qData->document_no, "readonly" => "readonly"));
            $data["fields"][]      = form_text(array("name" => "document_title", "label" => "Document Title", "mandatory" => "yes", "value" => $qData->document_title, "readonly" => "readonly"));

            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Delete&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));

            # ---------------
            return view("default.form", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE DOCUMENT", "");
            # ---------------
            return view("error.405");
        }
    }

    public function remove(Request $request)
    {
        try {
            $rules = array(
                'document_no' => 'required|',
            );

            $messages = [
                'document_no.required' => 'Document number is required',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("/document/delete/" . $request->id_enc)
                    ->withErrors($validator)
                    ->withInput();
            } else {
                $response   = $this->qDocument->removeDocument($request);

                if ($response["status"]) {
                    session()->flash("success_message", SUCCESS_MESSAGE);
                } else {
                    session()->flash("error_message", FAILED_MESSAGE);
                }
                # ---------------
                return redirect("/document/index");
            }
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE DELETE DOCUMENT", "");
            # ---------------
            return view("error.405");
        }
    }
    public function assignment($id_enc)
    {
        try {

            $data["title"]         = "Assignment Document";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/document/update_assignment";

            $id                    = decodedData($id_enc);
            /* ----------
             Model
            ----------------------- */
            $selectUser            = $this->qReference->getSelectUser();
            $selectRole            = getSelectRoleAssignment();

            $qData                 =  $this->qDocument->getDataById($id);

            if ($qData->status == 6) {
                session()->flash("error_message", "The document can't be assigned");
                # ---------------
                return redirect("/document/index");
            }
            // Empty table comment temp
            $this->qDocument->emptyTempByClone();
            // Clone to comment temp
            $this->qDocument->cloneCommentTemp($id, $qData->assignment_id);

            /* ----------
             Fields
            ----------------------- */
            $data["status_nonaktif"]    = $qData->status_nonaktif;
            $data["id_of_assignment"]   = $qData->status_nonaktif . "@" . $qData->assignment_id . "@" . $id_enc;
            # ---------------------
            $data["fields"][]      = form_text(array("name" => "project_name", "label" => "Project", "value" => $qData->project_name, "readonly" => "readonly"));
            $data["fields"][]      = form_text(array("name" => "vendor_name", "label" => "Vendor", "value" => $qData->vendor_name, "readonly" => "readonly"));
            $data["fields"][]      = form_text(array("name" => "document_no", "label" => "Document Number", "value" => $qData->document_no, "readonly" => "readonly"));
            $data["fields"][]      = form_text(array("name" => "document_title", "label" => "Title", "value" => $qData->document_title, "readonly" => "readonly"));
            $data["fields"][]      = form_hidden(array("name" => "start_date", "label" => "Assignment Start Date", "mandatory" => "mandatory", "value" => date('d/m/Y')));
            $data["fields"][]      = form_hidden(array("name" => "id_of_assignment", "label" => "Assignment ID", "mandatory" => "mandatory", "value" => $qData->assignment_id));


            /* ----------
             Modal Fields
            ----------------------- */
            $data["fields_modal"][] = form_hidden(array("name" => "document_id", "label" => "Document Id", "value" => $qData->document_id));
            $data["fields_modal"][] = form_hidden(array("name" => "assignment_id", "label" => "Assignment Id", "value" => $qData->assignment_id));
            $data["fields_modal"][] = form_select(array("name" => "user_id", "label" => "User", "mandatory" => "yes", "source" => $selectUser));
            $data["fields_modal"][] = form_select(array("name" => "role_id", "label" => "Role", "mandatory" => "yes", "source" => $selectRole));
            # ---------------
            $data['selectOrder'] = getSelectOrderAssignment();
            $data['selectRole'] = getSelectRoleAssignment();
            # ---------------
            $data["save_clone_comment_temp_url"]    = "/document/save_clone_comment_temp";
            $data["delete_clone_comment_temp_url"]  = "/document/delete_clone_comment_temp";
            $data["activate_url"]                   = "/comments/activate";
            # ---------------

            $data['statusAssignment'] = $qData->assignment_id;
            $data['idDoc'] = $qData->document_id;
            $data['status_document'] = $qData->status;
            $data['assignment_id'] = $qData->assignment_id;
            $data['document_id'] = $qData->document_id;
            /* ----------
             GET COMMENT TEMP BY CLONE
            ----------------------- */
            $data['comment']    = $this->qDocument->getCommentTempByClone();

            // dd($data['comment']);
            return view("document.form-assignment", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE DOCUMENT", "");
            throw $e;
            # ---------------
            return view("error.405");
        }
    }

    public function save_clone_comment_temp(Request $request)
    {
        $response   = $this->qDocument->addUserCloneTemp($request);

        if ($response["status"]) {
            $items  = $this->qDocument->getUserCloneTemp();

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

    public function delete_clone_comment_temp($id)
    {
        $response   = $this->qDocument->deleteUserCloneTemp($id);

        if ($response["status"]) {
            $items  = $this->qDocument->getUserCloneTemp();

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

    public function update_assignment(Request $request)
    {
        try {
            if ($request->comment_temp_id != null) {
                // update ke comment temp lalu update ke comment
                $response   = $this->qDocument->updateTempCommentToComment($request);
            } else {
                // Langsung ke comment
                $response   = $this->qDocument->updateToComment($request);
            }

            if ($response["status"]) {
                session()->flash("success_message", SUCCESS_MESSAGE);
            } else {
                session()->flash("error_message", FAILED_MESSAGE);
            }
            # ---------------
            return redirect("/document/index");
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE EDIT DOCUMENT", "");
            # ---------------
            return view("error.405");
        }
    }

    public function detail($id_enc)
    {
        try {

            $data["title"]         = "Document Detail";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/document/index";

            $id                    = decodedData($id_enc);

            $data['document']       = $this->qDocument->getDataDocExistingById($id);
            $data['transmittal']    = $this->qDocument->getDataHistoryTransmittal($id);
            $data['comment']        = $this->qDocument->getDataHistoryComment($id);
            $data['historyDoc']     = $this->qDocument->getDataHistoryDocument($id);
            $data['docLog']         = $this->qDocument->getDocChangeLog($id);
            $data['migration']      = $this->qDocument->getDataHistoryMigration($id);

            # ---------------
            return view("document.detail", $data);
        } catch (\Exception $e) {
            throw $e;
            $this->logModel->createError($e->getMessage(), "PAGE DOCUMENT", "");
            # ---------------
            return view("error.405");
        }
    }

    public function change_approval($id_enc)
    {
        try {

            $data["title"]         = "Change Approval";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/document/update_approval";

            $id                    = decodedData($id_enc);
            /* ----------
             Model
            ----------------------- */
            $selectUser            = $this->qReference->getSelectUser();
            # ---------------
            $qData = $this->qDocument->getDataById($id);
            if ($qData->status == 6) {
                session()->flash("error_message", "Documents cannot be changed");
                # ---------------
                return redirect("/document/index");
            }

            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name" => "id_enc", "label" => "ID Encrypt", "mandatory" => "yes", "readonly" => "readonly", "value" => $id_enc));
            $data["fields"][]      = form_hidden(array("name" => "document_id", "label" => "Document Id", "mandatory" => "yes", "readonly" => "readonly", "value" => $id));
            $data["fields"][]      = form_text(array("name" => "project_name", "label" => "Project", "readonly" => "readonly", "value" => $qData->project_name));
            $data["fields"][]      = form_text(array("name" => "vendor_name", "label" => "Vendor",  "readonly" => "readonly", "value" => $qData->vendor_name));
            $data["fields"][]      = form_text(array("name" => "document_no", "label" => "Document Number",  "readonly" => "readonly", "value" => $qData->document_no));
            $data["fields"][]      = form_text(array("name" => "document_title", "label" => "Title", "readonly" => "readonly", "value" => $qData->document_title));
            $data["fields"][]      = form_select(array("name" => "approval_by", "label" => "Approval By", "mandatory" => "yes", "source" => $selectUser, "value" => $qData->approved_by));

            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Save&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));

            # ---------------
            return view("default.form", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE DOCUMENT", "");
            # ---------------
            return view("error.405");
        }
    }

    public function update_approval(Request $request)
    {
        try {
            $rules = array(
                'approval_by' => 'required|'
            );

            $messages = [
                'document_no.required' => 'Approval by is required'
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("/document/change_approval/" . $request->id_enc)
                    ->withErrors($validator)
                    ->withInput();
            } else {
                $response   = $this->qDocument->updateApprovalDocument($request);

                if ($response["status"]) {
                    session()->flash("success_message", SUCCESS_MESSAGE);
                } else {
                    session()->flash("error_message", FAILED_MESSAGE);
                }
                # ---------------
                return redirect("/document/index");
            }
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE EDIT DOCUMENT", "");
            # ---------------
            return view("error.405");
        }
    }

    public function change_deadline($id_enc)
    {
        try {

            $data["title"]         = "Change Deadline";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/document/update_deadline";

            $id                    = decodedData($id_enc);
            /* ----------
             Model
            ----------------------- */
            # ---------------
            $qData = $this->qDocument->getDataById($id);
            if ($qData->status == 6) {
                session()->flash("error_message", "Documents cannot be changed");
                # ---------------
                return redirect("/document/index");
            }

            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name" => "id_enc", "label" => "ID Encrypt", "mandatory" => "yes", "readonly" => "readonly", "value" => $id_enc));
            $data["fields"][]      = form_hidden(array("name" => "document_id", "label" => "Document Id", "mandatory" => "yes", "readonly" => "readonly", "value" => $id));
            $data["fields"][]      = form_text(array("name" => "project_name", "label" => "Project", "readonly" => "readonly", "value" => $qData->project_name));
            $data["fields"][]      = form_text(array("name" => "vendor_name", "label" => "Vendor",  "readonly" => "readonly", "value" => $qData->vendor_name));
            $data["fields"][]      = form_text(array("name" => "document_no", "label" => "Document Number",  "readonly" => "readonly", "value" => $qData->document_no));
            $data["fields"][]      = form_text(array("name" => "document_title", "label" => "Title", "readonly" => "readonly", "value" => $qData->document_title));
            $data["fields"][]      = form_datepicker(array("name" => "deadline", "label" => "Deadline", "mandatory" => "yes", "value" => displayDMY($qData->deadline)));

            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Save&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));

            # ---------------
            return view("default.form", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE DOCUMENT", "");
            # ---------------
            return view("error.405");
        }
    }

    public function update_deadline(Request $request)
    {
        try {
            $rules = array(
                'deadline' => 'required|'
            );

            $messages = [
                'deadline.required' => 'Deadline by is required'
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("/document/change_deadline/" . $request->id_enc)
                    ->withErrors($validator)
                    ->withInput();
            } else {
                $response   = $this->qDocument->updateDeadlineDocument($request);

                if ($response["status"]) {
                    session()->flash("success_message", SUCCESS_MESSAGE);
                } else {
                    session()->flash("error_message", FAILED_MESSAGE);
                }
                # ---------------
                return redirect("/document/index");
            }
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE EDIT DOCUMENT", "");
            # ---------------
            return view("error.405");
        }
    }

    public function upload_vdrl()
    {
        try {
            $data["title"]         = "Upload VDRL";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/document/temp_vdrl";

            /* ----------
             Model
            ----------------------- */
            $selectProject         = $this->qReference->getSelectProject();
            $selectVendor          = $this->qReference->getSelectVendor();
            $selectIsDocumentIdc   = getSelectIsDocumentIdc();

            $template_upload_vdrl  = url('') . '/uploads/template/Template Upload VDRL.xlsx';

            /* ----------
             Fields
            ----------------------- */


            $data["fields"][]      = form_select(array("name" => "project_id", "label" => "Project",  "source" => $selectProject));
            $data["fields"][]      = form_select(array("name" => "vendor_id", "label" => "Vendor",  "mandatory" => "yes", "source" => $selectVendor));
            $data["fields"][]      = form_select(array("name" => "is_document_idc", "label" => "For IDC",  "mandatory" => "yes", "source" => $selectIsDocumentIdc));
            $data["fields"][]      = form_upload(array("name" => "upload_file", "label" => "File", "mandatory" => "mandatory"));
            $data["fields"][]      = "<div class=\"form-group\">
                                        <label class=\"col-md-3 control-label\">Template</label>
                                        <div class=\"col-md-9\">
                                            <a href=\" $template_upload_vdrl \" class=\"btn btn-sm btn-warning m-r-5\">Download Template</a>
                                        </div>
                                    </div>";
            $data["fields"][]      = "<div class=\"form-group\">
                                        <label class=\"col-md-3 control-label\">Current VDRL</label>
                                        <div class=\"col-md-9\">
                                            <a href=\" download_current_vdrl \" class=\"btn btn-sm btn-warning m-r-5\">Download Current VDRL</a>
                                        </div>
                                    </div>";
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Save&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));

            # ---------------
            return view("default.form", $data);
        } catch (\Exception $e) {
            throw $e;
            $this->logModel->createError($e->getMessage(), "PAGE DOCUMENT", "");
            # ---------------
            return view("error.405");
        }
    }

    public function temp_vdrl(Request $request)
    {
        $rules = array(
            'upload_file' => 'required|mimes:xls,xlsx'
        );

        $messages = [
            'upload_file.mimes' => 'File must be excel (xls,xlsx)',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect("/document/upload_vdrl")
                ->withErrors($validator)
                ->withInput();
        } else {
            $response   = $this->qDocument->createTempVdrl($request);
            # ---------------
            if ($response["status"]) {
                $kode = $request->project_id . '|' . $request->vendor_id . '|' . Auth::user()->id . '|' . date('Ymd');
                $encrypt = base64_encode($kode);

                session()->flash("success_message", SUCCESS_MESSAGE);
                return redirect("/document/view_temp/" . $encrypt);
            } else {
                session()->flash("error_message", FAILED_MESSAGE);
                return redirect("/document/index");
            }
        }
    }

    public function view_temp($id)
    {
        try {
            $data["title"]         = "List Document VDRL";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/document/save_vdrl";

            $qVendor    = $kode = base64_decode($id);
            list($project_id, $vendor_id, $user_id, $date) = explode("|", $kode);

            $data['id']           = $id;
            $data['temp']         = $this->qDocument->getDocumentViewTemp($id);
            $tempReady            = $this->qDocument->getDocumentTempIsReady($id)->count();
            $data['project']      = $this->qProject->getDataById($project_id)->project_name;
            $data['vendor']       = $this->qVendor->getVendor($vendor_id)['data']->name;

            $data["fields"][]      = form_hidden(array("name" => "id", "label" => "ID", "value" => $id));
            $data["fields"][]      = form_hidden(array("name" => "vendor_id", "label" => "Vendor", "value" => $project_id));
            $data["fields"][]      = form_hidden(array("name" => "project_id", "label" => "Project", "value" => $vendor_id));

            $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));
            if ($tempReady != 0) {
                $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Upload&nbsp;&nbsp;"));
            }

            # ---------------
            return view("document.list-temp", $data);
        } catch (\Exception $e) {
            throw $e;
            $this->logModel->createError($e->getMessage(), "PAGE DOCUMENT", "");
            # ---------------
            return view("error.405");
        }
    }

    public function save_vdrl(Request $request)
    {
        $rules = array();

        $messages = [];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect("/document/view_temp/" . $request->id)
                ->withErrors($validator)
                ->withInput();
        } else {
            $qTemp = $this->qDocument->getDocumentTempIsReady($request->id);
            if (count($qTemp) == 0) {
                session()->flash("error_message", "The document can't be uploaded because nothing is ready");
            } else {
                $response   = $this->qDocument->createUploadVdrl($request);
                # ---------------
                if ($response["status"]) {
                    session()->flash("success_message", SUCCESS_MESSAGE);
                } else {
                    session()->flash("error_message", FAILED_MESSAGE);
                }
            }

            # ---------------
            return redirect("/document/index");
        }
    }

    // REPORT
    public function report()
    {
        try {
            $data["title"]         = "Document Report";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/document/report_result";
            /* ----------
             Model
            ----------------------- */
            $selectProject         = $this->qReference->getSelectProject();
            $selectDocumentType    = $this->qReference->getSelectDocumentType();
            $selectDocumentStatus  = $this->qReference->getSelectDocumentStatus();
            $selectIssueStatus     = $this->qReference->getSelectIssueStatus();
            $selectArea            = $this->qReference->getSelectArea();
            $selectType            = array(
                array("id" => "SUMMARY_ISSUE", "name" => "SUMMARY")
                , array("id" => "SUMMARY_DOCUMENT", "name" => "SUMMARY 2")
                // , array("id" => "SUMMARY_DOCUMENT_2", "name" => "SUMMARY 3")
                , array("id" => "DETAIL", "name" => "DETAIL")
            );
            /* ----------
             Source
            ----------------------- */

            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name" => "document_type_id", "label" => "Document Type", "withnull" => "yes", "source" => $selectDocumentType));
            $data["fields"][]      = form_hidden(array("name" => "document_status_id", "label" => "Document Status", "withnull" => "yes", "source" => $selectDocumentStatus));
            $data["fields"][]      = form_hidden(array("name" => "document_issue_id", "label" => "Issue Status", "withnull" => "yes", "source" => $selectIssueStatus));
            $data["fields"][]      = form_hidden(array("name" => "area_id", "label" => "Area", "withnull" => "yes", "source" => $selectArea));
            $data["fields_ex_1"][]      = form_select(array("name" => "project_id", "label" => "Project Name", "withnull" => "yes", "source" => $selectProject));
            $data["fields_ex_2"][]      = form_select(array("name" => "type", "label" => "Report Type", "source" => $selectType, "value" => "DETAIL"));
            # ---------------
            $data["buttons"][] = form_button_submit(array("name" => "button_window", "label" => "&nbsp;&nbsp;Preview&nbsp;&nbsp;"));
            # ---------------
            return view("default.form-report-document", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE REPORT DOCUMENT", "");
            # ---------------
            return view("error.405");
        }
    }

    public function report_result(Request $request)
    {
        $data["title"]              = "DOCUMENT REPORT";
        $data["periode"]            = date("d/m/Y");
        $params                     = base64_encode($request->project_id . "|" . $request->document_type_id . "|" . $request->document_status_id . "|" . $request->document_issue_id . "|" . $request->area_id . "|" . $request->type);
        # ---------------
        if ($request->type == "DETAIL") {
            $data["url_data"]           = url('/') . "/document/report_detail_json/" . $params;
            # ---------------
            $data["column_unit"]        = 2;
            $data["content_center"]     = "0,1,8,9,10,11,12,13,14,15,17,18";
            $data["content_right"]      = "";
            $data["token"]              = "";
        } else if ($request->type == "SUMMARY_ISSUE") {
            $data["url_data"]           = url('/') . "/document/report_summary_json/" . $params;
            # ---------------
            $data["column_unit"]        = 2;
            $data["content_center"]     = "0,1,2,3,4";
            $data["content_right"]      = "";
            $data["token"]              = "";
        } else if ($request->type == "SUMMARY_DOCUMENT") {
            $objPHPExcel    = new PHPExcel();
            # -------------------
            # DOCUMENT INFO
            # -------------------
            $objPHPExcel->getProperties()->setCreator(env("APPS_COPYRIGHT"))
                                        ->setLastModifiedBy("Administrator")
                                        ->setTitle("Lap Doc")
                                        ->setSubject("Lap Doc")
                                        ->setDescription("Lap Doc");
            # -------------------
            # ISSUE
            # -------------------
                # TITLE
                # -------------------
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue("A1", "LAPORAN DOKUMEN (CURRENT ISSUE STATUS)")
                    ->setCellValue('A2', "PERIODE : " . date("d/m/Y H:i:s"));
                # -------------------
                # SET REPORTS
                # -------------------
                $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue("A3", "VENDOR NAME");
                # -------------------
                # SET REPORTS
                # -------------------
                $objPHPExcel->getActiveSheet()->freezePane("B4");
                $objPHPExcel->getActiveSheet()->getStyle("A1:A2")->applyFromArray(setPHPExcel_Title());
                $objPHPExcel->getActiveSheet()->getStyle("A3:O3")->applyFromArray(setPHPExcel_Header());
                $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(50);
                # -------------------
                $dataIssue  = $this->IssueStatus->get_all();

                $Cols       = "B";
                foreach($dataIssue["data"] as $row_issue) {
                    $objPHPExcel->setActiveSheetIndex(0)
                                ->setCellValue($Cols . "3", $row_issue->issue_status_name); $Cols++;
                }                
                # -------------------
                $dataProject    = $this->qProject->get_all();

                $Row    = 4;
                foreach($dataProject["data"] as $row_project) {
                    $dataVendor     = $this->qDocument->get_document_vendor($row_project->project_id);

                    foreach($dataVendor["data"] as $row_vendor) {
                        $objPHPExcel->getActiveSheet()->getStyle("A5:A" . $Row)->applyFromArray(setPHPExcel_Content_Left());

                        $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue("A" . $Row, $row_vendor->vendor_name);

                        $dataIssue  = $this->IssueStatus->get_all();

                        $Cols       = "B";
                        foreach($dataIssue["data"] as $row_issue) {
                            $qUnit      = $this->qDocument->get_document_vendor_qty($row_project->project_id, $row_vendor->vendor_id, $row_issue->issue_status_id);
                            $dataunit   = $qUnit["data"];

                            $objPHPExcel->getActiveSheet()->getStyle($Cols . $Row)->applyFromArray(setPHPExcel_Content_Center());

                            $objPHPExcel->setActiveSheetIndex(0)
                                        ->setCellValue($Cols . $Row, $dataunit->unit); 

                            $Cols++;
                        }

                        $Row++;             
                    }
                }
                # -------------------
                # SET SHEET
                # -------------------
                $objPHPExcel->getActiveSheet()->setTitle("Doc");
                $objPHPExcel->setActiveSheetIndex(0);
            # -------------------
            # DOWNLOAD
            # -------------------
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="Lap Doc.xls"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header('Pragma: public'); // HTTP/1.0
            # -------------------
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            # -------------------
            exit;
        } else {
            $objPHPExcel    = new PHPExcel();
            # -------------------
            # DOCUMENT INFO
            # -------------------
            $objPHPExcel->getProperties()->setCreator(env("APPS_COPYRIGHT"))
                                        ->setLastModifiedBy("Administrator")
                                        ->setTitle("Lap Doc")
                                        ->setSubject("Lap Doc")
                                        ->setDescription("Lap Doc");
            # -------------------
            # ISSUE
            # -------------------
                # TITLE
                # -------------------
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue("A1", "LAPORAN DOKUMEN")
                    ->setCellValue('A2', "PERIODE : " . date("d/m/Y H:i:s"));
                # -------------------
                # SET REPORTS
                # -------------------
                $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue("A3", "PROJECT NAME")
                            ->setCellValue("B3", "VENDOR NAME");
                # -------------------
                # SET REPORTS
                # -------------------
                $objPHPExcel->getActiveSheet()->freezePane("C4");
                $objPHPExcel->getActiveSheet()->getStyle("A1:A2")->applyFromArray(setPHPExcel_Title());
                $objPHPExcel->getActiveSheet()->getStyle("A3:O3")->applyFromArray(setPHPExcel_Header());
                $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(50);
                $objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(50);
                # -------------------
                $dataIssue  = $this->IssueStatus->get_all();

                $Cols       = "C";
                foreach($dataIssue["data"] as $row_issue) {
                    $objPHPExcel->setActiveSheetIndex(0)
                                ->setCellValue($Cols . "3", $row_issue->issue_status_name); $Cols++;
                }                
                # -------------------
                $dataProject    = $this->qProject->get_all();

                $Row    = 4;
                foreach($dataProject["data"] as $row_project) {
                    $dataVendor     = $this->qDocument->get_document_vendor($row_project->project_id);

                    foreach($dataVendor["data"] as $row_vendor) {
                        $objPHPExcel->getActiveSheet()->getStyle("A5:B" . $Row)->applyFromArray(setPHPExcel_Content_Left());

                        $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue("A" . $Row, $row_project->project_name)
                                    ->setCellValue("B" . $Row, $row_vendor->vendor_name);

                        $dataIssue  = $this->IssueStatus->get_all();

                        $Cols       = "C";
                        foreach($dataIssue["data"] as $row_issue) {
                            $qUnit      = $this->qDocument->get_document_transmittal_vendor_qty($row_project->project_id, $row_vendor->vendor_id, $row_issue->issue_status_id);
                            $dataunit   = $qUnit["data"];

                            $objPHPExcel->getActiveSheet()->getStyle($Cols . $Row)->applyFromArray(setPHPExcel_Content_Center());

                            $objPHPExcel->setActiveSheetIndex(0)
                                        ->setCellValue($Cols . $Row, $dataunit->unit); 

                            $Cols++;
                        }

                        $Row++;             
                    }
                }
                # -------------------
                # SET SHEET
                # -------------------
                $objPHPExcel->getActiveSheet()->setTitle("Doc");
                $objPHPExcel->setActiveSheetIndex(0);
            # -------------------
            # DOWNLOAD
            # -------------------
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="Lap Doc.xls"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header('Pragma: public'); // HTTP/1.0
            # -------------------
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            # -------------------
            exit;
        }
        # ---------------
        return view("default.report-datatable", $data);
    }

    public function report_summary_json($params)
    {
        $query  = $this->qDocument->getSummaryReport($params);

        return Datatables::of($query)->make(true);
    }

    public function report_detail_json($params)
    {
        $query  = $this->qDocument->getDetailReport($params);

        return Datatables::of($query)->make(true);
    }

    public function reset_assignment($doc_id, $assignment_id, $user_id) {
        $encode     = encodedData(base64_decode($doc_id));
        # ---------------
        $response   = $this->qDocument->resetAssignment(base64_decode($assignment_id), base64_decode($user_id));
        # ---------------
        if ($response["status"]) {
            session()->flash("success_message", SUCCESS_MESSAGE);
        } else {
            session()->flash("error_message", FAILED_MESSAGE);
        }
        # ---------------
        return redirect("/document/assignment/" . $encode);
    }

    public function download_current_vdrl() {
        $qDocument      = new DocumentModel;
        $objPHPExcel    = new PHPExcel();

        $data           = $this->qDocument->getdataDocument();
        // dd($data);

        if (count($data) > 0) {
            # ---------------
            $objPHPExcel->getProperties()->setCreator(env("APPS_COPYRIGHT"))
                ->setLastModifiedBy("Administrator")
                ->setTitle("Current Vdrl")
                ->setSubject("Current Vdrl")
                ->setDescription("Current Vdrl");
            # -------------------
            # TITLE
            # -------------------
            $Row        = 2;
            # -------------------
            # -------------------
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue("A1", "DOCUMENT_NO")
                ->setCellValue("B1", "DOCUMENT_TITLE")
                ->setCellValue("C1", "DESCRIPTION")
                ->setCellValue("D1", "REF_NUMBER")
                ->setCellValue("E1", "REVIEWER")
                ->setCellValue("F1", "APPROVER")
                ->setCellValue("G1", "OBSERVER")
                ;

            # -------------------
            $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(25);

            # -------------------
            $objPHPExcel->getActiveSheet()->getStyle("A1:A1")->applyFromArray(setPHPExcel_Title());
            $objPHPExcel->getActiveSheet()->getStyle("A1:G1")->applyFromArray(setPHPExcel_Header());
            $objPHPExcel->getActiveSheet()->getStyle("A2:G" . (count($data)+1))->applyFromArray(setPHPExcel_Content_left());
            # -------------------
            $No     = 1;
            $Start  = $Row;
            foreach($data as $row) {

                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue("A" . $Row, $row->document_no)
                    ->setCellValue("B" . $Row, $row->document_title)
                    ->setCellValue("C" . $Row, $row->document_description)
                    ->setCellValue("D" . $Row, $row->ref_no)
                    ->setCellValue("E" . $Row, $row->reviewer)
                    ->setCellValue("F" . $Row, $row->approver)
                    ->setCellValue("G" . $Row, $row->observer);

                $End = $Row;
                $Row++;
                $No++;
            }

            # -------------------
            # SET SHEET
            # -------------------
            $objPHPExcel->getActiveSheet()->setTitle("Report");
            $objPHPExcel->setActiveSheetIndex(0);
            # -------------------
            # DOWNLOAD
            # -------------------
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="Current Vdrl.xls"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header('Pragma: public'); // HTTP/1.0
            # -------------------
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            # -------------------
            exit;
        } else {
            $objPHPExcel->getProperties()->setCreator(env("APPS_COPYRIGHT"))
                ->setLastModifiedBy("Administrator")
                ->setTitle("Current Vdrl")
                ->setSubject("Current Vdrl")
                ->setDescription("Current Vdrl");
            # -------------------
            # TITLE
            # -------------------
            $Row        = 7;
            # -------------------
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue("A1",'TIDAK ADA DATA');

            # -------------------
            # SET SHEET
            # -------------------
            $objPHPExcel->getActiveSheet()->setTitle("Report");
            $objPHPExcel->setActiveSheetIndex(0);
            # -------------------
            # DOWNLOAD
            # -------------------
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="Current Vdrl.xls"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header('Pragma: public'); // HTTP/1.0
            # -------------------
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
        }
    }

    public function change_document_status($id_enc)
    {
        try {

            $data["title"]         = "Change Document Status";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/document/update_document_status";

            $id                    = decodedData($id_enc);
            /* ----------
             Model
            ----------------------- */
            $selectProject         = $this->qReference->getSelectProject();
            $selectDocType         = $this->qReference->getSelectDocumentType();
            $selectVendor          = $this->qReference->getSelectVendor();
            $selectArea            = $this->qReference->getSelectArea();
            $selectDepartemen      = $this->qReference->getSelectDepartment();
            $selectUser            = $this->qReference->getSelectUser();
            # ---------------
            $qData = $this->qDocument->getDataById($id);
            # can change document status only for ifu-approved/ifa-approved issues and status done
            if (($qData->issue_status_id == 17 || $qData->issue_status_id == 19) && $qData->status == 6) {
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name" => "id_enc", "label" => "ID Encrypt", "mandatory" => "yes", "readonly" => "readonly", "value" => $id_enc));
            $data["fields"][]      = form_hidden(array("name" => "document_id", "label" => "Document Id", "mandatory" => "yes", "readonly" => "readonly", "value" => $id));
            $data["fields"][]      = form_select(array("name" => "project_id", "label" => "Project", "withnull" => "withnull", "readonly" => "readonly", "source" => $selectProject, "value" => $qData->project_id));
            $data["fields"][]      = form_select(array("name" => "vendor_id", "label" => "Vendor", "withnull" => "withnull",  "readonly" => "readonly", "source" => $selectVendor, "value" => $qData->vendor_id));
            $data["fields"][]      = form_hidden(array("name" => "document_no_old", "label" => "Document Number Old", "readonly" => "readonly", "first_selected" => "yes", "value" => $qData->document_no));
            $data["fields"][]      = form_text(array("name" => "document_no", "label" => "Document Number", "readonly" => "readonly", "first_selected" => "yes", "value" => $qData->document_no));
            $data["fields"][]      = form_hidden(array("name" => "document_title_old", "label" => "Title Old", "readonly" => "readonly", "value" => $qData->document_title));
            $data["fields"][]      = form_text(array("name" => "document_title", "label" => "Title", "readonly" => "readonly", "value" => $qData->document_title));
            $data["fields"][]      = form_text(array("name" => "status_name", "label" => "Status", "readonly" => "readonly", "value" => "Waiting for return"));

            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Update&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));

            # ---------------
            return view("default.form", $data);
            }else{
            session()->flash("error_message", "Change document status only for IFU-Approved/AFC-Approved issues and status done");
            # ---------------
            return redirect("/document/index");
            }
        } catch (\Exception $e) {
            throw $e;
            $this->logModel->createError($e->getMessage(), "PAGE DOCUMENT", "");
            # ---------------
            return view("error.405");
        }
    }

    public function update_document_status(Request $request)
    {
        try {
            $rules = array(
                // 'document_no' => 'required|',
                // 'document_title' => 'required|',
            );

            $messages = [
                // 'document_no.required' => 'Document number is required',
                // 'document_title.required' => 'Document title is required',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("/document/change_document_status/" . $request->id_enc)
                    ->withErrors($validator)
                    ->withInput();
            } else {
                if ($request->document_no_old != $request->document_no) {
                    $checkDocNo = $this->qDocument->checkDuplicateDocumentNo($request->document_no);

                    if ($checkDocNo > 0) {
                        return redirect("/document/change_document_status/" . $request->id_enc)
                            ->withErrors("Document number already exists")
                            ->withInput();
                    }
                }

                $response   = $this->qDocument->updateDocumentStatus($request);

                if ($response["status"]) {
                    session()->flash("success_message", SUCCESS_MESSAGE);
                } else {
                    session()->flash("error_message", FAILED_MESSAGE);
                }
                # ---------------
                return redirect("/document/index");
            }
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE EDIT DOCUMENT", "");
            # ---------------
            return view("error.405");
        }
    }
}
