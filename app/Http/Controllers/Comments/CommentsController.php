<?php

namespace App\Http\Controllers\Comments;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Comments\CommentsModel;
use DB;
use View;
use Auth;
use Validator;
use Hash;
use Zipper;
use Yajra\Datatables\Datatables;
use App\User;
use App\Model\UserManagement\MenuModel;
use App\Model\UserManagement\UserModel;
use App\Model\Reference\ReferenceModel;
use App\Model\Sys\SysModel;
use App\Model\Sys\LogModel;

class CommentsController extends Controller
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
        $this->qComments          = new CommentsModel;
        $this->sysModel           = new SysModel;
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
            $data["form_act"]         = "/comments/index";
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
                    "label" => "ID", "name" => "comment_id", "align" => "center", "item-align" => "center", "item-format" => "checkbox", "item-class" => "", "width" => "5%", "add-style" => ""
                ),
                array(
                    "label" => "Project Name", "name" => "project_name", "align" => "center", "item-align" => "left", "item-format" => "normal", "item-class" => "", "width" => "12%", "add-style" => ""
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
                    "label" => "Deadline", "name" => "t_end_date", "align" => "center", "item-align" => "center", "item-format" => "normal", "item-class" => "", "width" => "10%", "add-style" => ""
                ),
                array(
                    "label" => "Issue Status", "name" => "issue_status", "align" => "center", "item-align" => "center", "item-format" => "flag", "item-class" => "", "width" => "10%", "add-style" => ""
                )
            );
            # ---------------
            $data["query"]         = $this->qComments->getCollections();
            $data["select"]        = $data["query"]["data"];
            $data["pagging"]       = getPagging($data["select"]);
            # ---------------
            # Advance Search
            # ---------------
            if (isset($request->module_id)) {
                $project_name      = ($request->project_name != "") ? session(["SES_SEARCH_COMMENTS_PROJECT_NAME" => $request->project_name]) : $request->session()->forget("SES_SEARCH_COMMENTS_PROJECT_NAME");
                $document_no       = ($request->document_no  != "") ? session(["SES_SEARCH_COMMENTS_NO" => $request->document_no]) : $request->session()->forget("SES_SEARCH_COMMENTS_NO");
                $document_title    = ($request->document_title  != "") ? session(["SES_SEARCH_COMMENTS_TITLE" => $request->document_title]) : $request->session()->forget("SES_SEARCH_COMMENTS_TITLE");
                $document_type     = ($request->document_type  != "") ? session(["SES_SEARCH_COMMENTS_TYPE" => $request->document_type]) : $request->session()->forget("SES_SEARCH_COMMENTS_TYPE");
                $vendor_id         = ($request->vendor_id  != "") ? session(["SES_SEARCH_COMMENTS_VENDOR" => $request->vendor_id]) : $request->session()->forget("SES_SEARCH_COMMENTS_VENDOR");
                $area_id           = ($request->area_id  != "") ? session(["SES_SEARCH_COMMENTS_AREA" => $request->area_id]) : $request->session()->forget("SES_SEARCH_COMMENTS_AREA");
                # ---------------
                return redirect("/comments/index");
            }
            # ---------------
            if ($request->session()->has("SES_SEARCH_COMMENTS_PROJECT_NAME")) {
                array_push($data["filtered_info"], "PROJECT NAME");
            }
            # ---------------
            if ($request->session()->has("SES_SEARCH_COMMENTS_NO")) {
                array_push($data["filtered_info"], "DOCUMENT NO");
            }
            # ---------------
            if ($request->session()->has("SES_SEARCH_COMMENTS_TITLE")) {
                array_push($data["filtered_info"], "DOCUMENT TITLE");
            }

            if ($request->session()->has("SES_SEARCH_COMMENTS_TYPE")) {
                if ($request->session()->get("SES_SEARCH_COMMENTS_TYPE") != "0") {
                    array_push($data["filtered_info"], "DOCUMENT TYPE");
                }
            }

            if ($request->session()->has("SES_SEARCH_COMMENTS_VENDOR")) {
                if ($request->session()->get("SES_SEARCH_COMMENTS_VENDOR") != "0") {
                    array_push($data["filtered_info"], "VENDOR");
                }
            }

            if ($request->session()->has("SES_SEARCH_COMMENTS_AREA")) {
                if ($request->session()->get("SES_SEARCH_COMMENTS_AREA") != "0") {
                    array_push($data["filtered_info"], "AREA");
                }
            }

            # ---------------
            $data["adv_search"]    = true;
            $data["hide_simple_search"] = true;
            # ---------------
            $data["fields"][]      = form_hidden(array("name" => "module_id", "label" => "Module ID", "value" => "DOCUMENT"));
            $data["fields"][]      = form_search_text(array("name" => "project_name", "label" => "Project Name", "value" => ($request->session()->has("SES_SEARCH_COMMENTS_PROJECT_NAME")) ? $request->session()->get("SES_SEARCH_COMMENTS_PROJECT_NAME") : ""));
            $data["fields"][]      = form_search_text(array("name" => "document_no", "label" => "Document No", "value" => ($request->session()->has("SES_SEARCH_COMMENTS_NO")) ? $request->session()->get("SES_SEARCH_COMMENTS_NO") : ""));
            $data["fields"][]      = form_search_text(array("name" => "document_title", "label" => "Document Title", "value" => ($request->session()->has("SES_SEARCH_COMMENTS_TITLE")) ? $request->session()->get("SES_SEARCH_COMMENTS_TITLE") : ""));
            $data["fields"][]      = form_search_select(array("name" => "document_type", "label" => "Document Type", "source" => $qDocType,  "withnull" => "yes", "value" => ($request->session()->has("SES_SEARCH_COMMENTS_TYPE")) ? $request->session()->get("SES_SEARCH_COMMENTS_TYPE") : ""));
            $data["fields"][]      = form_search_select(array("name" => "vendor_id", "label" => "Vendor", "source" => $qVendor,  "withnull" => "yes", "value" => ($request->session()->has("SES_SEARCH_COMMENTS_VENDOR")) ? $request->session()->get("SES_SEARCH_COMMENTS_VENDOR") : ""));
            $data["fields"][]      = form_search_select(array("name" => "area_id", "label" => "Area", "source" => $qArea,  "withnull" => "yes", "value" => ($request->session()->has("SES_SEARCH_COMMENTS_AREA")) ? $request->session()->get("SES_SEARCH_COMMENTS_AREA") : ""));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_search", "label" => "&nbsp;&nbsp;Search&nbsp;&nbsp;"));
            $data["buttons"][]     = form_action_button(array("name" => "button_clear", "label" => "&nbsp;&nbsp;Clear&nbsp;&nbsp;", "url" => "/comments/unfilter"));
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
        session()->forget("SES_SEARCH_COMMENTS_PROJECT_NAME");
        session()->forget("SES_SEARCH_COMMENTS_NO");
        session()->forget("SES_SEARCH_COMMENTS_TITLE");
        session()->forget("SES_SEARCH_COMMENTS_TYPE");
        session()->forget("SES_SEARCH_COMMENTS_VENDOR");
        session()->forget("SES_SEARCH_COMMENTS_AREA");
        # ---------------
        return redirect("/comments/index");
    }

    public function addcomments($id)
    {
        try {
            $data["title"]         = "Comments";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/comments/save";
            # ---------------
            $idData                = decodedData($id);
            /* ----------
             Model
            ----------------------- */
            $data["header"]        = $this->qComments->getHeader($idData);
            $data["detail"]        = $this->qComments->getDetail($data["header"]->assignment_id);
            $qDataComment          = $this->qComments->getMaxComment($data["header"]->assignment_id);
            $selectUser            = $this->qReference->getSelectUser();
            $selectReturnStatus    = $this->qReference->getSelectReturnStatus();
            $qUserComment          = $this->qComments->getCommentDetailUser($data["header"]->assignment_id, Auth::user()->id);            
            
            if($data["header"]->issue_dokumen == 3 || $data["header"]->issue_dokumen == 8) {
                $qIssueStatus          = $this->qReference->getSelectIssueStatusComments(STATUS_APPROVAL_IFA);
            } else if($data["header"]->issue_dokumen == 4 || $data["header"]->issue_dokumen == 9 || $data["header"]->issue_dokumen == 12 || $data["header"]->issue_dokumen == 16) {
                $qIssueStatus          = $this->qReference->getSelectIssueStatusComments(STATUS_APPROVAL_AFC_IFU);
            } else if($data["header"]->issue_dokumen == 13 || $data["header"]->issue_dokumen == 14) {
                $qIssueStatus          = $this->qReference->getSelectIssueStatusComments(STATUS_APPROVAL_IFI);
            } else if($data["header"]->issue_dokumen == 10 || $data["header"]->issue_dokumen == 15) {
                $qIssueStatus          = $this->qReference->getSelectIssueStatusComments(STATUS_APPROVAL_IFC);
            } else {
                $qIssueStatus          = $this->qReference->getSelectIssueStatusComments(STATUS_APPROVAL_IDC);
            }
            // $stsApproval           = ($qDataComment->order_no == $data["header"]->order_no) ? "APPROVAL" : "-";
            $stsApproval           = ($qUserComment->role == 'APPROVER') ? "APPROVER" : "-";
            # ---------------
            $file                  = (!empty($data["header"]->document_file)) ? asset("/uploads") . $data["header"]->document_url . $data["header"]->document_file : "";
            $file_crs              = (!empty($data["header"]->document_crs)) ? asset("/uploads") . $data["header"]->document_url . $data["header"]->document_crs : "";
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name" => "id", "label" => "Comment ID", "readonly" => "readonly", "value" => $id));
            $data["fields"][]      = form_hidden(array("name" => "idData", "label" => "Comment ID", "readonly" => "readonly", "value" => $idData));
            $data["fields"][]      = form_hidden(array("name" => "order_no", "label" => "Order", "readonly" => "readonly", "value" => $data["header"]->order_no));
            $data["fields"][]      = form_hidden(array("name" => "assignment_id", "label" => "Assignment ID", "readonly" => "readonly", "value" => $data["header"]->assignment_id));
            $data["fields"][]      = form_hidden(array("name" => "role", "label" => "Role", "readonly" => "readonly", "value" => $data["header"]->role));
            $data["fields"][]      = form_hidden(array("name" => "document_id", "label" => "Document ID", "readonly" => "readonly", "value" => $data["header"]->document_id));
            $data["fields"][]      = form_hidden(array("name" => "incoming_transmittal_detail_id", "label" => "Incoming Transmittal Detail ID", "readonly" => "readonly", "value" => $data["header"]->incoming_transmittal_detail_id));
            $data["fields"][]      = form_hidden(array("name" => "incoming_transmittal_id", "label" => "Incoming Transmittal ID", "readonly" => "readonly", "value" => $data["header"]->incoming_transmittal_id));
            $data["fields"][]      = form_hidden(array("name" => "incoming_no", "label" => "Incoming Number", "readonly" => "readonly", "value" => $data["header"]->incoming_no));
            $data["fields"][]      = form_hidden(array("name" => "status_approval", "label" => "Status Approval", "readonly" => "readonly", "value" => $stsApproval));
            $data["fields"][]      = form_text(array("name" => "project_name", "label" => "Project Name", "readonly" => "readonly", "value" => $data["header"]->project_name));
            $data["fields"][]      = form_text(array("name" => "document_no", "label" => "Document No", "readonly" => "readonly", "value" => $data["header"]->document_no));
            $data["fields"][]      = form_text(array("name" => "document_title", "label" => "Document Title", "readonly" => "readonly", "value" => $data["header"]->document_title));
            $data["fields"][]      = form_hidden(array("name" => "document_type_name", "label" => "Type", "readonly" => "readonly", "value" => $data["header"]->document_type_name));

            $data["fields"][]      = form_text(array("name" => "vendor_name", "label" => "Vendor", "readonly" => "readonly", "value" => $data["header"]->vendor_name));
            $data["fields"][]      = form_hidden(array("name" => "area_name", "label" => "Area", "readonly" => "readonly", "value" => $data["header"]->area_name));
            $data["fields"][]      = form_text(array("name" => "issue_status", "label" => "Current Issue Status", "readonly" => "readonly", "value" => $data["header"]->issue_status));
            $data["fields"][]      = form_text(array("name" => "document_status_id", "label" => "Revision Number", "readonly" => "readonly", "value" => $data["header"]->document_status_id));
            $data["fields"][]       = form_file(array("label" => "Document File", "value" => $file));
            $data["fields"][]       = form_file(array("label" => "CRS File", "value" => $file_crs));
            if(!empty($data["header"]->remark_before)) {
                $data["fields"][]      = form_textarea(array("name" => "remark_before", "label" => "Remark Before", "readonly" => "readonly", "value" => $data["header"]->remark_before));
            }
            // if($data["header"]->role=="APPROVER") {
            // if($stsApproval == "APPROVAL") {
            if($qUserComment->role == 'APPROVER') {
                $data["fields"][]      = form_select(array("name"=>"issue_status_id", "label"=>"Next Rev", "source"=>$qIssueStatus));
            }
            $data["fields_1"][]      = form_select(array("name"=>"approved_design_by", "label" => "Approval By", "source" => $selectUser, "value" => $data["header"]->approved_design_by));
            $data["fields_2"][]      = form_upload(array("name"=>"document_file", "label"=>"Review Result"));
            $data["fields_2"][]      = form_upload(array("name"=>"document_file_2", "label"=>"CRS"));
            $data["fields_2"][]      = form_select(array("name"=>"return_status_id", "label"=>"Return Code", "source"=>$selectReturnStatus));
            $data["fields_2"][]      = form_textarea(array("name" => "remark", "label" => "Remark", "value" => $data["header"]->remark));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Save&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));
            # ---------------
            return view("comments.addcomments", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE COMMENTS", "");
            # ---------------
            return view("error.405");
        }
    }

    public function save(Request $request) {
        try {
            $dataConfig     = $this->sysModel->getConfig();
            $extention      = $dataConfig->attachment_extention;
            $max_size       = $dataConfig->attachment_max_size;

            $rules = array(

            );

            if($max_size > 0) {
                if(!empty($extention)) {
                    $validate_message   = "Attachment extention must $extention & maximum size " . number_format($max_size, 0) . " kb"; 

                    if(!empty($request->document_file)) {
                        $rules["document_file"] = "required|mimes:$extention|max:$max_size";
                    }

                    if(!empty($request->document_file_2)) {
                        $rules["document_file_2"] = "required|mimes:$extention|max:$max_size";
                    }
                } 
            } else {
                if(!empty($extention)) {
                    $validate_message   = "Attachment extention must $extention"; 
                    if(!empty($request->document_file)) {
                        $rules["document_file"] = "required|mimes:$extention";
                    }

                    if(!empty($request->document_file_2)) {
                        $rules["document_file_2"] = "required|mimes:$extention";
                    }
                } 
            } 

            $messages = [

            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("/comments/addcomments/".$request->id)
                            ->withErrors($validate_message)
                            ->withInput();
            } else {
                $response   = $this->qComments->saveComments($request);

                if($response["status"]) {
                    session()->flash("success_message", SUCCESS_MESSAGE);
                } else {
                    session()->flash("error_message", FAILED_MESSAGE);
                }
                # ---------------
                if($request->issue_status != 'IDC') {
                return redirect("/comments/index");
                }else{
                return redirect("/comments_idc/index");
                }
            }
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE ADD COMMENTS", "");
            # ---------------
            return view("error.405");
        }
    }

    public function download() {
        try {
            $response   = $this->qComments->download(Auth::user()->id);

            return $response;
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "DOWNLOAD COMMENT", "");
            throw $e;
            # ---------------
            return view("error.405");
        }
    }

    public function download_attachment($id) {
        try {
            $id         = decodedData($id);
            $response   = $this->qComments->download_attachment($id);

            return $response;
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "DOWNLOAD ATTACHMENT COMMENT", "");
            throw $e;
            # ---------------
            return view("error.405");
        }
    }    

    public function index_client(Request $request)
    {
        try {
            $data["title"]            = ucwords(strtolower($this->PROT_ModuleName));
            $data["parent"]           = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]         = "/client_transmittal/index";
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
            $qStatsuDownload        = array(array("id"=>1, "name"=>"Already"), array("id"=>99, "name"=>"Not Yet"));
            # ---------------
            $data["action"]         = $this->qMenu->getActionMenu(Auth::user()->id, $this->PROT_ModuleId);
            /* ----------
             Table header
            ----------------------- */
            $data["table_header"]   = array(
                array(
                    "label" => "ID", "name" => "comment_id", "align" => "center", "item-align" => "center", "item-format" => "checkbox", "item-class" => "", "width" => "5%", "add-style" => ""
                ),
                array(
                    "label" => "Project Name", "name" => "project_name", "align" => "center", "item-align" => "left", "item-format" => "normal", "item-class" => "", "width" => "12%", "add-style" => ""
                ),
                array(
                    "label" => "Document No", "name" => "document_no", "align" => "center", "item-align" => "left", "item-format" => "normal", "item-class" => "", "width" => "15%", "add-style" => ""
                ),
                array(
                    "label" => "Document Title", "name" => "document_title", "align" => "center", "item-align" => "left", "item-format" => "normal", "item-class" => "", "width" => "20%", "add-style" => ""
                ),
                array(
                    "label" => "Subject", "name" => "subject", "align" => "center", "item-align" => "left", "item-format" => "normal", "item-class" => "", "width" => "10%", "add-style" => ""
                ),
                array(
                    "label" => "Vendor", "name" => "vendor_name", "align" => "center", "item-align" => "center", "item-format" => "normal", "item-class" => "", "width" => "13%", "add-style" => ""
                ),
                array(
                    "label" => "Deadline", "name" => "t_end_date", "align" => "center", "item-align" => "center", "item-format" => "normal", "item-class" => "", "width" => "10%", "add-style" => ""
                ),
                array(
                    "label" => "Issue", "name" => "issue_status", "align" => "center", "item-align" => "center", "item-format" => "flag", "item-class" => "", "width" => "7%", "add-style" => ""
                ),
                array(
                    "label" => "Download", "name" => "status_download", "align" => "center", "item-align" => "center", "item-format" => "flag", "item-class" => "", "width" => "10%", "add-style" => ""
                )
            );
            # ---------------
            $data["query"]         = $this->qComments->getCollectionsClient();
            $data["select"]        = $data["query"]["data"];
            $data["pagging"]       = getPagging($data["select"]);
            # ---------------
            # Advance Search
            # ---------------
            if (isset($request->module_id)) {
                $project_name      = ($request->project_name != "") ? session(["SES_SEARCH_CLIENT_TRANSMITTAL_PROJECT_NAME" => $request->project_name]) : $request->session()->forget("SES_SEARCH_CLIENT_TRANSMITTAL_PROJECT_NAME");
                $document_no       = ($request->document_no  != "") ? session(["SES_SEARCH_CLIENT_TRANSMITTAL_NO" => $request->document_no]) : $request->session()->forget("SES_SEARCH_CLIENT_TRANSMITTAL_NO");
                $document_title    = ($request->document_title  != "") ? session(["SES_SEARCH_CLIENT_TRANSMITTAL_TITLE" => $request->document_title]) : $request->session()->forget("SES_SEARCH_CLIENT_TRANSMITTAL_TITLE");
                $document_type     = ($request->document_type  != "") ? session(["SES_SEARCH_CLIENT_TRANSMITTAL_TYPE" => $request->document_type]) : $request->session()->forget("SES_SEARCH_CLIENT_TRANSMITTAL_TYPE");
                $vendor_id         = ($request->vendor_id  != "") ? session(["SES_SEARCH_CLIENT_TRANSMITTAL_VENDOR" => $request->vendor_id]) : $request->session()->forget("SES_SEARCH_CLIENT_TRANSMITTAL_VENDOR");
                $area_id           = ($request->area_id  != "") ? session(["SES_SEARCH_CLIENT_TRANSMITTAL_AREA" => $request->area_id]) : $request->session()->forget("SES_SEARCH_CLIENT_TRANSMITTAL_AREA");
                $status_download   = ($request->status_download  != "") ? session(["SES_SEARCH_CLIENT_TRANSMITTAL_DOWNLOAD" => $request->status_download]) : $request->session()->forget("SES_SEARCH_CLIENT_TRANSMITTAL_DOWNLOAD");
                # ---------------
                return redirect("/client_transmittal/index");
            }
            # ---------------
            if ($request->session()->has("SES_SEARCH_CLIENT_TRANSMITTAL_PROJECT_NAME")) {
                array_push($data["filtered_info"], "PROJECT NAME");
            }
            # ---------------
            if ($request->session()->has("SES_SEARCH_CLIENT_TRANSMITTAL_NO")) {
                array_push($data["filtered_info"], "DOCUMENT NO");
            }
            # ---------------
            if ($request->session()->has("SES_SEARCH_CLIENT_TRANSMITTAL_TITLE")) {
                array_push($data["filtered_info"], "DOCUMENT TITLE");
            }

            if ($request->session()->has("SES_SEARCH_CLIENT_TRANSMITTAL_TYPE")) {
                if ($request->session()->get("SES_SEARCH_CLIENT_TRANSMITTAL_TYPE") != "0") {
                    array_push($data["filtered_info"], "DOCUMENT TYPE");
                }
            }

            if ($request->session()->has("SES_SEARCH_CLIENT_TRANSMITTAL_VENDOR")) {
                if ($request->session()->get("SES_SEARCH_CLIENT_TRANSMITTAL_VENDOR") != "0") {
                    array_push($data["filtered_info"], "VENDOR");
                }
            }

            if ($request->session()->has("SES_SEARCH_CLIENT_TRANSMITTAL_AREA")) {
                if ($request->session()->get("SES_SEARCH_CLIENT_TRANSMITTAL_AREA") != "0") {
                    array_push($data["filtered_info"], "AREA");
                }
            }
            if ($request->session()->has("SES_SEARCH_CLIENT_TRANSMITTAL_DOWNLOAD")) {
                if ($request->session()->get("SES_SEARCH_CLIENT_TRANSMITTAL_DOWNLOAD") != "0") {
                    array_push($data["filtered_info"], "DOWNLOAD");
                }
            }

            # ---------------
            $data["adv_search"]    = true;
            $data["hide_simple_search"] = true;
            # ---------------
            $data["fields"][]      = form_hidden(array("name" => "module_id", "label" => "Module ID", "value" => "CLIENT_TRANSMITTAL"));
            $data["fields"][]      = form_search_text(array("name" => "project_name", "label" => "Project Name", "value" => ($request->session()->has("SES_SEARCH_CLIENT_TRANSMITTAL_PROJECT_NAME")) ? $request->session()->get("SES_SEARCH_CLIENT_TRANSMITTAL_PROJECT_NAME") : ""));
            $data["fields"][]      = form_search_text(array("name" => "document_no", "label" => "Document No", "value" => ($request->session()->has("SES_SEARCH_CLIENT_TRANSMITTAL_NO")) ? $request->session()->get("SES_SEARCH_CLIENT_TRANSMITTAL_NO") : ""));
            $data["fields"][]      = form_search_text(array("name" => "document_title", "label" => "Document Title", "value" => ($request->session()->has("SES_SEARCH_CLIENT_TRANSMITTAL_TITLE")) ? $request->session()->get("SES_SEARCH_CLIENT_TRANSMITTAL_TITLE") : ""));
            $data["fields"][]      = form_search_select(array("name" => "document_type", "label" => "Document Type", "source" => $qDocType,  "withnull" => "yes", "value" => ($request->session()->has("SES_SEARCH_CLIENT_TRANSMITTAL_TYPE")) ? $request->session()->get("SES_SEARCH_CLIENT_TRANSMITTAL_TYPE") : ""));
            $data["fields"][]      = form_search_select(array("name" => "vendor_id", "label" => "Vendor", "source" => $qVendor,  "withnull" => "yes", "value" => ($request->session()->has("SES_SEARCH_CLIENT_TRANSMITTAL_VENDOR")) ? $request->session()->get("SES_SEARCH_CLIENT_TRANSMITTAL_VENDOR") : ""));
            $data["fields"][]      = form_search_select(array("name" => "status_download", "label" => "Download Status", "source" => $qStatsuDownload,  "withnull" => "yes", "value" => ($request->session()->has("SES_SEARCH_CLIENT_TRANSMITTAL_DOWNLOAD")) ? $request->session()->get("SES_SEARCH_CLIENT_TRANSMITTAL_DOWNLOAD") : ""));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_search", "label" => "&nbsp;&nbsp;Search&nbsp;&nbsp;"));
            $data["buttons"][]     = form_action_button(array("name" => "button_clear", "label" => "&nbsp;&nbsp;Clear&nbsp;&nbsp;", "url" => "/client_transmittal/unfilter"));
            # ---------------
            return view("default.list", $data);
        } catch (\Exception $e) {
            throw $e;
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            # ---------------
            return view("error.405");
        }
    }

    public function download_client() {
        try {
            $response   = $this->qComments->downloadClient(Auth::user()->id);

            if($response) {
                return $response;
            } else {
                echo "<script>alert('Tidak ada data yang bisa di download'); history.go(-1); </script>";
            }
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "DOWNLOAD COMMENT", "");
            throw $e;
            # ---------------
            return view("error.405");
        }
    }

    public function unfilter_client()
    {
        session()->forget("SES_SEARCH_CLIENT_TRANSMITTAL_PROJECT_NAME");
        session()->forget("SES_SEARCH_CLIENT_TRANSMITTAL_NO");
        session()->forget("SES_SEARCH_CLIENT_TRANSMITTAL_TITLE");
        session()->forget("SES_SEARCH_CLIENT_TRANSMITTAL_TYPE");
        session()->forget("SES_SEARCH_CLIENT_TRANSMITTAL_VENDOR");
        session()->forget("SES_SEARCH_CLIENT_TRANSMITTAL_DOWNLOAD");
        # ---------------
        return redirect("/client_transmittal/index");
    }

    public function activate($params) {
        try {
            list($status, $assignment_id, $page_id) = explode("@", $params);

            if($status == 1) {
                $this->qComments->activateComment($assignment_id);
            } else {
                $this->qComments->inactivateComment($assignment_id);
            }
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "ACTIVATE COMMENT", "");
            # ---------------
            return view("error.405");
        }
    } 
    
    /* ----------
        Comments IDC
    ----------------------- */
    
    public function index_idc(Request $request)
    {
        try {
            $data["title"]            = ucwords(strtolower($this->PROT_ModuleName));
            $data["parent"]           = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]         = "/comments_idc/index";
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
                    "label" => "ID", "name" => "comment_id", "align" => "center", "item-align" => "center", "item-format" => "checkbox", "item-class" => "", "width" => "5%", "add-style" => ""
                ),
                array(
                    "label" => "Project Name", "name" => "project_name", "align" => "center", "item-align" => "left", "item-format" => "normal", "item-class" => "", "width" => "12%", "add-style" => ""
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
                // array(
                //     "label" => "Deadline", "name" => "t_end_date", "align" => "center", "item-align" => "center", "item-format" => "normal", "item-class" => "", "width" => "10%", "add-style" => ""
                // ),
                array(
                    "label" => "Issue Status", "name" => "issue_status", "align" => "center", "item-align" => "center", "item-format" => "flag", "item-class" => "", "width" => "10%", "add-style" => ""
                )
            );
            # ---------------
            $data["query"]         = $this->qComments->getCollectionsIdc();
            $data["select"]        = $data["query"]["data"];
            $data["pagging"]       = getPagging($data["select"]);
            # ---------------
            # Advance Search
            # ---------------
            if (isset($request->module_id)) {
                $project_name      = ($request->project_name != "") ? session(["SES_SEARCH_COMMENTS_IDC_PROJECT_NAME" => $request->project_name]) : $request->session()->forget("SES_SEARCH_COMMENTS_IDC_PROJECT_NAME");
                $document_no       = ($request->document_no  != "") ? session(["SES_SEARCH_COMMENTS_IDC_NO" => $request->document_no]) : $request->session()->forget("SES_SEARCH_COMMENTS_IDC_NO");
                $document_title    = ($request->document_title  != "") ? session(["SES_SEARCH_COMMENTS_IDC_TITLE" => $request->document_title]) : $request->session()->forget("SES_SEARCH_COMMENTS_IDC_TITLE");
                $document_type     = ($request->document_type  != "") ? session(["SES_SEARCH_COMMENTS_IDC_TYPE" => $request->document_type]) : $request->session()->forget("SES_SEARCH_COMMENTS_IDC_TYPE");
                $area_id           = ($request->area_id  != "") ? session(["SES_SEARCH_COMMENTS_IDC_AREA" => $request->area_id]) : $request->session()->forget("SES_SEARCH_COMMENTS_IDC_AREA");
                # ---------------
                return redirect("/comments_idc/index");
            }
            # ---------------
            if ($request->session()->has("SES_SEARCH_COMMENTS_IDC_PROJECT_NAME")) {
                array_push($data["filtered_info"], "PROJECT NAME");
            }
            # ---------------
            if ($request->session()->has("SES_SEARCH_COMMENTS_IDC_NO")) {
                array_push($data["filtered_info"], "DOCUMENT NO");
            }
            # ---------------
            if ($request->session()->has("SES_SEARCH_COMMENTS_IDC_TITLE")) {
                array_push($data["filtered_info"], "DOCUMENT TITLE");
            }

            if ($request->session()->has("SES_SEARCH_COMMENTS_IDC_TYPE")) {
                if ($request->session()->get("SES_SEARCH_COMMENTS_IDC_TYPE") != "0") {
                    array_push($data["filtered_info"], "DOCUMENT TYPE");
                }
            }

            if ($request->session()->has("SES_SEARCH_COMMENTS_IDC_AREA")) {
                if ($request->session()->get("SES_SEARCH_COMMENTS_IDC_AREA") != "0") {
                    array_push($data["filtered_info"], "AREA");
                }
            }

            # ---------------
            $data["adv_search"]    = true;
            $data["hide_simple_search"] = true;
            # ---------------
            $data["fields"][]      = form_hidden(array("name" => "module_id", "label" => "Module ID", "value" => "DOCUMENT"));
            $data["fields"][]      = form_search_text(array("name" => "project_name", "label" => "Project Name", "value" => ($request->session()->has("SES_SEARCH_COMMENTS_IDC_PROJECT_NAME")) ? $request->session()->get("SES_SEARCH_COMMENTS_IDC_PROJECT_NAME") : ""));
            $data["fields"][]      = form_search_text(array("name" => "document_no", "label" => "Document No", "value" => ($request->session()->has("SES_SEARCH_COMMENTS_IDC_NO")) ? $request->session()->get("SES_SEARCH_COMMENTS_IDC_NO") : ""));
            $data["fields"][]      = form_search_text(array("name" => "document_title", "label" => "Document Title", "value" => ($request->session()->has("SES_SEARCH_COMMENTS_IDC_TITLE")) ? $request->session()->get("SES_SEARCH_COMMENTS_IDC_TITLE") : ""));
            $data["fields"][]      = form_search_select(array("name" => "document_type", "label" => "Document Type", "source" => $qDocType,  "withnull" => "yes", "value" => ($request->session()->has("SES_SEARCH_COMMENTS_IDC_TYPE")) ? $request->session()->get("SES_SEARCH_COMMENTS_IDC_TYPE") : ""));
            $data["fields"][]      = form_search_select(array("name" => "area_id", "label" => "Area", "source" => $qArea,  "withnull" => "yes", "value" => ($request->session()->has("SES_SEARCH_COMMENTS_IDC_AREA")) ? $request->session()->get("SES_SEARCH_COMMENTS_IDC_AREA") : ""));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_search", "label" => "&nbsp;&nbsp;Search&nbsp;&nbsp;"));
            $data["buttons"][]     = form_action_button(array("name" => "button_clear", "label" => "&nbsp;&nbsp;Clear&nbsp;&nbsp;", "url" => "/comments_idc/unfilter"));
            # ---------------
            return view("default.list", $data);
        } catch (\Exception $e) {
            throw $e;
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            # ---------------
            return view("error.405");
        }
    }

    public function unfilter_idc()
    {
        session()->forget("SES_SEARCH_COMMENTS_IDC_PROJECT_NAME");
        session()->forget("SES_SEARCH_COMMENTS_IDC_NO");
        session()->forget("SES_SEARCH_COMMENTS_IDC_TITLE");
        session()->forget("SES_SEARCH_COMMENTS_IDC_TYPE");
        session()->forget("SES_SEARCH_COMMENTS_IDC_AREA");
        # ---------------
        return redirect("/comments_idc/index");
    }

    public function addcomments_idc($id)
    {
        try {
            $data["title"]         = "Comments";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/comments_idc/save";
            # ---------------
            $idData                = decodedData($id);
            /* ----------
             Model
            ----------------------- */
            $data["header"]        = $this->qComments->getHeader($idData);
            $data["detail"]        = $this->qComments->getDetail($data["header"]->assignment_id);
            $qDataComment          = $this->qComments->getMaxComment($data["header"]->assignment_id);
            $selectUser            = $this->qReference->getSelectUser();
            $selectReturnStatus    = $this->qReference->getSelectReturnStatus();
            $qUserComment          = $this->qComments->getCommentDetailUser($data["header"]->assignment_id, Auth::user()->id);            
            $qIssueStatus          = $this->qReference->getSelectIssueStatusComments(STATUS_APPROVAL_IDC);
            // $stsApproval           = ($qDataComment->order_no == $data["header"]->order_no) ? "APPROVAL" : "-";
            $stsApproval           = ($qUserComment->role == 'APPROVER') ? "APPROVER" : "-";
            # ---------------
            $file                  = (!empty($data["header"]->document_file)) ? asset("/uploads") . $data["header"]->document_url . $data["header"]->document_file : "";
            $file_crs              = (!empty($data["header"]->document_crs)) ? asset("/uploads") . $data["header"]->document_url . $data["header"]->document_crs : "";
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name" => "id", "label" => "Comment ID", "readonly" => "readonly", "value" => $id));
            $data["fields"][]      = form_hidden(array("name" => "idData", "label" => "Comment ID", "readonly" => "readonly", "value" => $idData));
            $data["fields"][]      = form_hidden(array("name" => "order_no", "label" => "Order", "readonly" => "readonly", "value" => $data["header"]->order_no));
            $data["fields"][]      = form_hidden(array("name" => "assignment_id", "label" => "Assignment ID", "readonly" => "readonly", "value" => $data["header"]->assignment_id));
            $data["fields"][]      = form_hidden(array("name" => "role", "label" => "Role", "readonly" => "readonly", "value" => $data["header"]->role));
            $data["fields"][]      = form_hidden(array("name" => "document_id", "label" => "Document ID", "readonly" => "readonly", "value" => $data["header"]->document_id));
            $data["fields"][]      = form_hidden(array("name" => "incoming_transmittal_detail_id", "label" => "Incoming Transmittal Detail ID", "readonly" => "readonly", "value" => $data["header"]->incoming_transmittal_detail_id));
            $data["fields"][]      = form_hidden(array("name" => "incoming_transmittal_id", "label" => "Incoming Transmittal ID", "readonly" => "readonly", "value" => $data["header"]->incoming_transmittal_id));
            $data["fields"][]      = form_hidden(array("name" => "incoming_no", "label" => "Incoming Number", "readonly" => "readonly", "value" => $data["header"]->incoming_no));
            $data["fields"][]      = form_hidden(array("name" => "status_approval", "label" => "Status Approval", "readonly" => "readonly", "value" => $stsApproval));
            $data["fields"][]      = form_text(array("name" => "project_name", "label" => "Project Name", "readonly" => "readonly", "value" => $data["header"]->project_name));
            $data["fields"][]      = form_text(array("name" => "document_no", "label" => "Document No", "readonly" => "readonly", "value" => $data["header"]->document_no));
            $data["fields"][]      = form_text(array("name" => "document_title", "label" => "Document Title", "readonly" => "readonly", "value" => $data["header"]->document_title));
            $data["fields"][]      = form_hidden(array("name" => "document_type_name", "label" => "Type", "readonly" => "readonly", "value" => $data["header"]->document_type_name));
            $data["fields"][]      = form_hidden(array("name" => "area_name", "label" => "Area", "readonly" => "readonly", "value" => $data["header"]->area_name));
            $data["fields"][]      = form_text(array("name" => "issue_status_id", "label" => "Issue Status ID", "readonly" => "readonly", "value" => $data["header"]->issue_status_id));
            $data["fields"][]      = form_hidden(array("name" => "issue_status", "label" => "Current Issue Status", "readonly" => "readonly", "value" => $data["header"]->issue_status));
            $data["fields"][]      = form_hidden(array("name" => "document_status_id", "label" => "Revision Number", "readonly" => "readonly", "value" => $data["header"]->document_status_id));
            $data["fields"][]       = form_file(array("label" => "Document File", "value" => $file));
            $data["fields"][]       = form_file(array("label" => "CRS File", "value" => $file_crs));
            if(!empty($data["header"]->remark_before)) {
                $data["fields"][]      = form_textarea(array("name" => "remark_before", "label" => "Remark Before", "readonly" => "readonly", "value" => $data["header"]->remark_before));
            }
            // if($data["header"]->role=="APPROVER") {
            // if($stsApproval == "APPROVAL") {
            // if($qUserComment->role == 'APPROVER') {
            //     $data["fields"][]      = form_select(array("name"=>"issue_status_id", "label"=>"Next Rev", "source"=>$qIssueStatus));
            // }
            $data["fields_1"][]      = form_select(array("name"=>"approved_design_by", "label" => "Approval By", "source" => $selectUser, "value" => $data["header"]->approved_design_by));
            $data["fields_2"][]      = form_upload(array("name"=>"document_file", "label"=>"Review Result"));
            $data["fields_2"][]      = form_upload(array("name"=>"document_file_2", "label"=>"CRS"));
            $data["fields_2"][]      = form_select(array("name"=>"return_status_id", "label"=>"Return Code", "source"=>$selectReturnStatus));
            $data["fields_2"][]      = form_textarea(array("name" => "remark", "label" => "Remark", "value" => $data["header"]->remark));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Save&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));
            # ---------------
            return view("comments.addcomments", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE COMMENTS", "");
            # ---------------
            return view("error.405");
        }
    }
}
