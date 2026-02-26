<?php

namespace App\Http\Controllers\Approvals;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Approvals\ApprovalsModel;
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
use App\Model\Sys\LogModel;

class ApprovalsController extends Controller
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
        $this->qApprovals         = new ApprovalsModel;
        $this->logModel           = new LogModel;
        # ---------------
        $rs                       = $this->qMenu->getParentMenu($uri);
        # ---------------
        $this->PROT_Parent        = (count($rs) > 0) ? $rs[0]->parent_name : '';
        $this->PROT_ModuleName    = (count($rs) > 0) ? $rs[0]->name : '';
        $this->PROT_ModuleId      = (count($rs) > 0) ? $rs[0]->id : '';
        # ---------------
        View::share(array("SHR_Parent" => $this->PROT_Parent, "SHR_Module" => $this->PROT_ModuleName, "SHR_ModuleId" => $this->PROT_ModuleId));
    }

    public function index(Request $request)
    {
        try {
            $data["title"]            = ucwords(strtolower($this->PROT_ModuleName));
            $data["parent"]           = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]         = "/approvals/index";
            $data["active_page"]      = (empty($page)) ? 1 : $page;
            $data["offset"]           = (empty($data["active_page"])) ? 0 : ($data["active_page"] - 1) * Auth::user()->perpage;
            /* ----------
             Source
            ----------------------- */

            # ---------------
            $data["filtered_info"]  = array();

            $qVendor                = $this->qReference->getSelectVendor();
            $qArea                  = $this->qReference->getSelectArea();
            $qDocType               = $this->qReference->getSelectDocumentType();
            # ---------------
            $data["action"]         = $this->qMenu->getActionMenu(Auth::user()->id, $this->PROT_ModuleId);
            /* ----------
             Table header
            ----------------------- */
            $data["table_header"]   = array(
                array(
                    "label" => "ID", "name" => "document_id", "align" => "center", "item-align" => "center", "item-format" => "checkbox", "item-class" => "", "width" => "5%", "add-style" => ""
                ),
                array(
                    "label" => "Project Name 1", "name" => "project_name", "align" => "center", "item-align" => "left", "item-format" => "normal", "item-class" => "", "width" => "12%", "add-style" => ""
                ),
                array(
                    "label" => "Document No", "name" => "document_no", "align" => "center", "item-align" => "left", "item-format" => "normal", "item-class" => "", "width" => "15%", "add-style" => ""
                ),
                array(
                    "label" => "Document Title", "name" => "document_title", "align" => "center", "item-align" => "left", "item-format" => "normal", "item-class" => "", "width" => "20%", "add-style" => ""
                ),
                array(
                    "label" => "Type", "name" => "document_type_name", "align" => "center", "item-align" => "left", "item-format" => "normal", "item-class" => "", "width" => "10%", "add-style" => ""
                ),
                array(
                    "label" => "Vendor", "name" => "vendor_name", "align" => "center", "item-align" => "center", "item-format" => "normal", "item-class" => "", "width" => "13%", "add-style" => ""
                ),
                array(
                    "label" => "Area", "name" => "area_name", "align" => "center", "item-align" => "center", "item-format" => "normal", "item-class" => "", "width" => "10%", "add-style" => ""
                ),
                array(
                    "label" => "Issue Status", "name" => "issue_status", "align" => "center", "item-align" => "center", "item-format" => "flag", "item-class" => "", "width" => "10%", "add-style" => ""
                )
            );
            # ---------------
            $data["query"]         = $this->qApprovals->getCollections();
            $data["select"]        = $data["query"]["data"];
            $data["pagging"]       = getPagging($data["select"]);
            # ---------------
            # Advance Search
            # ---------------
            if (isset($request->module_id)) {
                $project_name      = ($request->project_name != "") ? session(["SES_SEARCH_APPROVALS_PROJECT_NAME" => $request->project_name]) : $request->session()->forget("SES_SEARCH_APPROVALS_PROJECT_NAME");
                $document_no       = ($request->document_no  != "") ? session(["SES_SEARCH_APPROVALS_NO" => $request->document_no]) : $request->session()->forget("SES_SEARCH_APPROVALS_NO");
                $document_title    = ($request->document_title  != "") ? session(["SES_SEARCH_APPROVALS_TITLE" => $request->document_title]) : $request->session()->forget("SES_SEARCH_APPROVALS_TITLE");
                $document_type     = ($request->document_type  != "") ? session(["SES_SEARCH_APPROVALS_TYPE" => $request->document_type]) : $request->session()->forget("SES_SEARCH_APPROVALS_TYPE");
                $vendor_id         = ($request->vendor_id  != "") ? session(["SES_SEARCH_APPROVALS_VENDOR" => $request->vendor_id]) : $request->session()->forget("SES_SEARCH_APPROVALS_VENDOR");
                $area_id           = ($request->area_id  != "") ? session(["SES_SEARCH_APPROVALS_AREA" => $request->area_id]) : $request->session()->forget("SES_SEARCH_APPROVALS_AREA");
                # ---------------
                return redirect("/approvals/index");
            }
            # ---------------
            if ($request->session()->has("SES_SEARCH_APPROVALS_PROJECT_NAME")) {
                array_push($data["filtered_info"], "PROJECT NAME");
            }
            # ---------------
            if ($request->session()->has("SES_SEARCH_APPROVALS_NO")) {
                array_push($data["filtered_info"], "DOCUMENT NO");
            }
            # ---------------
            if ($request->session()->has("SES_SEARCH_APPROVALS_TITLE")) {
                array_push($data["filtered_info"], "DOCUMENT TITLE");
            }

            if ($request->session()->has("SES_SEARCH_APPROVALS_TYPE")) {
                if ($request->session()->get("SES_SEARCH_APPROVALS_TYPE") != "0") {
                    array_push($data["filtered_info"], "DOCUMENT TYPE");
                }
            }

            if ($request->session()->has("SES_SEARCH_APPROVALS_VENDOR")) {
                if ($request->session()->get("SES_SEARCH_APPROVALS_VENDOR") != "0") {
                    array_push($data["filtered_info"], "VENDOR");
                }
            }

            if ($request->session()->has("SES_SEARCH_APPROVALS_AREA")) {
                if ($request->session()->get("SES_SEARCH_APPROVALS_AREA") != "0") {
                    array_push($data["filtered_info"], "AREA");
                }
            }

            # ---------------
            $data["adv_search"]    = true;
            $data["hide_simple_search"] = true;
            # ---------------
            $data["fields"][]      = form_hidden(array("name" => "module_id", "label" => "Module ID", "value" => "DOCUMENT"));
            $data["fields"][]      = form_search_text(array("name" => "project_name", "label" => "Project Name", "value" => ($request->session()->has("SES_SEARCH_APPROVALS_PROJECT_NAME")) ? $request->session()->get("SES_SEARCH_APPROVALS_PROJECT_NAME") : ""));
            $data["fields"][]      = form_search_text(array("name" => "document_no", "label" => "Document No", "value" => ($request->session()->has("SES_SEARCH_APPROVALS_NO")) ? $request->session()->get("SES_SEARCH_APPROVALS_NO") : ""));
            $data["fields"][]      = form_search_text(array("name" => "document_title", "label" => "Document Title", "value" => ($request->session()->has("SES_SEARCH_APPROVALS_TITLE")) ? $request->session()->get("SES_SEARCH_APPROVALS_TITLE") : ""));
            $data["fields"][]      = form_search_select(array("name" => "document_type", "label" => "Document Type", "source" => $qDocType,  "withnull" => "yes", "value" => ($request->session()->has("SES_SEARCH_APPROVALS_TYPE")) ? $request->session()->get("SES_SEARCH_APPROVALS_TYPE") : ""));
            $data["fields"][]      = form_search_select(array("name" => "vendor_id", "label" => "Vendor", "source" => $qVendor,  "withnull" => "yes", "value" => ($request->session()->has("SES_SEARCH_APPROVALS_VENDOR")) ? $request->session()->get("SES_SEARCH_APPROVALS_VENDOR") : ""));
            $data["fields"][]      = form_search_select(array("name" => "area_id", "label" => "Area", "source" => $qArea,  "withnull" => "yes", "value" => ($request->session()->has("SES_SEARCH_APPROVALS_AREA")) ? $request->session()->get("SES_SEARCH_APPROVALS_AREA") : ""));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_search", "label" => "&nbsp;&nbsp;Search&nbsp;&nbsp;"));
            $data["buttons"][]     = form_action_button(array("name" => "button_clear", "label" => "&nbsp;&nbsp;Clear&nbsp;&nbsp;", "url" => "/approvals/unfilter"));
            # ---------------
            return view("default.list", $data);
        } catch (\Exception $e) {
            throw $e;
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            # ---------------
            return view("error.405");
        }
    }

    public function unfilter()
    {
        session()->forget("SES_SEARCH_APPROVALS_PROJECT_NAME");
        session()->forget("SES_SEARCH_APPROVALS_NO");
        session()->forget("SES_SEARCH_APPROVALS_TITLE");
        session()->forget("SES_SEARCH_APPROVALS_TYPE");
        session()->forget("SES_SEARCH_APPROVALS_VENDOR");
        session()->forget("SES_SEARCH_APPROVALS_AREA");
        # ---------------
        return redirect("/approvals/index");
    }

    public function addcomments($id)
    {
        try {
            $data["title"]         = "Approval";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/approvals/save";
            # ---------------
            $idData                    = decodedData($id);
            /* ----------
             Model
            ----------------------- */
            $data["header"]        = $this->qApprovals->getHeader($idData);
            $data["detail"]        = $this->qApprovals->getDetail($data["header"]->assignment_id);
            
            if($data["header"]->issue_status_id == 12) {
                $qIssueStatus          = $this->qReference->getSelectIssueStatusComments(STATUS_APPROVAL_AFD);
                $approvedComment       = $data["header"]->approved_design_comment;
            } else if($data["header"]->issue_status_id == 11) {
                $qIssueStatus          = $this->qReference->getSelectIssueStatusComments(STATUS_APPROVAL_ADM);
                $approvedComment       = $data["header"]->approved_comment;
            } else {
                $qIssueStatus          = $this->qReference->getSelectIssueStatusComments(STATUS_APPROVAL_AFC);
                $approvedComment       = $data["header"]->approved_comment;
            }
            
            # ---------------
            $fileReview1           = (!empty($data["header"]->document_file)) ? asset("/uploads") . $data["header"]->document_url . $data["header"]->document_file : "";
            $fileReview2           = (!empty($data["header"]->document_file_2)) ? asset("/uploads") . $data["header"]->document_url . $data["header"]->document_file_2 : "";
            $fileIncoming          = (!empty($data["header"]->document_file_incoming)) ? asset("/uploads") . $data["header"]->document_url_incoming . $data["header"]->document_file_incoming : "";
            $fileRevision          = (!empty($data["header"]->document_file_revision)) ? asset("/uploads") . $data["header"]->document_url_incoming . $data["header"]->document_file_revision : "";
            $fileDocument          = (!empty($data["header"]->document_file_revision)) ? $fileRevision : $fileIncoming;

            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name" => "id", "label" => "Comment ID", "readonly" => "readonly", "value" => $id));
            $data["fields"][]      = form_hidden(array("name" => "idData", "label" => "Comment ID", "readonly" => "readonly", "value" => $idData));
            $data["fields"][]      = form_hidden(array("name" => "incoming_transmittal_detail_id", "label" => "Incoming Transmittal Detail ID", "readonly" => "readonly", "value" => $data["header"]->incoming_transmittal_detail_id));
            $data["fields"][]      = form_hidden(array("name" => "incoming_transmittal_detail_id", "label" => "Incoming Transmittal Detail ID", "readonly" => "readonly", "value" => $data["header"]->incoming_transmittal_detail_id));
            $data["fields"][]      = form_hidden(array("name" => "incoming_no", "label" => "Incoming Number", "readonly" => "readonly", "value" => $data["header"]->incoming_no));
            $data["fields"][]      = form_hidden(array("name" => "assignment_id", "label" => "Assigment Id", "readonly" => "readonly", "value" => $data["header"]->assignment_id));
            $data["fields"][]      = form_hidden(array("name" => "created_approved_by", "label" => "Created Approved Id", "readonly" => "readonly", "value" => $data["header"]->created_approved_by));
            $data["fields"][]      = form_hidden(array("name" => "created_approved_at", "label" => "Created Approved At", "readonly" => "readonly", "value" => $data["header"]->created_approved_at));
            $data["fields"][]      = form_hidden(array("name" => "created_design_by", "label" => "Created Design Id", "readonly" => "readonly", "value" => $data["header"]->created_design_by));
            $data["fields"][]      = form_hidden(array("name" => "created_design_at", "label" => "Created Design At", "readonly" => "readonly", "value" => $data["header"]->created_design_at));
            $data["fields"][]      = form_hidden(array("name" => "approved_design_by", "label" => "Created Design At", "readonly" => "readonly", "value" => $data["header"]->approved_design_by));
            $data["fields"][]      = form_hidden(array("name" => "document_url_incoming", "label" => "Document URL", "readonly" => "readonly", "value" => $data["header"]->document_url_incoming));
            $data["fields"][]      = form_hidden(array("name" => "issue_status_awal", "label"=>"Issue Status Awal", "readonly" => "readonly", "value"=>$data["header"]->issue_status_id));
            $data["fields"][]      = form_text(array("name" => "project_name", "label" => "Project Name", "readonly" => "readonly", "value" => $data["header"]->project_name));
            $data["fields"][]      = form_text(array("name" => "document_no", "label" => "Document No", "readonly" => "readonly", "value" => $data["header"]->document_no));
            $data["fields"][]      = form_text(array("name" => "document_title", "label" => "Document Title", "readonly" => "readonly", "value" => $data["header"]->document_title));
            // $data["fields"][]      = form_text(array("name" => "document_type_name", "label" => "Type", "readonly" => "readonly", "value" => $data["header"]->document_type_name));
            $data["fields"][]      = form_text(array("name" => "vendor_name", "label" => "Vendor", "readonly" => "readonly", "value" => $data["header"]->vendor_name));
            // $data["fields"][]      = form_text(array("name" => "area_name", "label" => "Area", "readonly" => "readonly", "value" => $data["header"]->area_name));
            $data["fields"][]      = form_text(array("name" => "issue_status", "label" => "Current Issue Status", "readonly" => "readonly", "value" => $data["header"]->issue_status));
            $data["fields"][]       = form_file(array("label" => "Document File", "value" => $fileDocument));
            // $data["fields"][]       = form_file(array("label" => "Document File Review 1", "value" => $fileReview1));
            // $data["fields"][]       = form_file(array("label" => "Document File Review 2", "value" => $fileReview2));
            if($data["header"]->issue_status_id == 12) {
                // JIKA AFD
                $data["fields"][]       = form_hidden(array("name"=>"document_file_revision", "label"=>"Document Revision"));
            } else {
                $data["fields"][]       = form_upload(array("name"=>"document_file_revision", "label"=>"Document Revision"));
            }
            $data["fields"][]       = form_select(array("name"=>"issue_status_id", "label"=>"Next Rev", "source"=>$qIssueStatus));
            $data["fields"][]       = form_textarea(array("name" => "approved_comment", "label" => "Remark", "mandatory" => "yes", "value" => $approvedComment));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Save&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));
            # ---------------
            return view("approvals.addcomments", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE COMMENTS", "");
            # ---------------
            return view("error.405");
        }
    }

    public function save(Request $request) {
        try {
            $rules = array(
                'approved_comment' => 'required|',
            );

            $messages = [
                'approved_comment.required' => 'Comment is required',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("/approvals/addcomments/".$request->id)
                            ->withErrors($validator)
                            ->withInput();
            } else {
                $response   = $this->qApprovals->saveApprovals($request);

                if($response["status"]) {
                    session()->flash("success_message", SUCCESS_MESSAGE);
                } else {
                    session()->flash("error_message", FAILED_MESSAGE);
                }
                # ---------------
                return redirect("/approvals/index");
            }
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE ADD COMMENTS", "");
            # ---------------
            return view("error.405");
        }
    }
    
}
