<?php

namespace App\Http\Controllers\Document;

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
use App\Model\Document\DocumentModel;
use App\Model\Sys\LogModel;
use App\Model\Sys\SysModel;

class InformationController extends Controller
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
        $this->logModel           = new LogModel;
        $this->sysModel           = new SysModel;
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
            $data["form_act"]         = "/information/index";
            $data["active_page"]      = (empty($page)) ? 1 : $page;
            $data["offset"]           = (empty($data["active_page"])) ? 0 : ($data["active_page"]-1) * Auth::user()->perpage;
            /* ----------
             Source
            ----------------------- */

            # ---------------
            $data["filtered_info"]    = array();
            $qStatus                  = getSelectStatusDocument();
            $qVendor                  = $this->qReference->getSelectVendor();
            # ---------------
            $data["action"]           = $this->qMenu->getActionMenu(Auth::user()->id, $this->PROT_ModuleId);
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
                                                "label" => "Vendor", "name" => "vendor_name", "align" => "center", "item-align" => "left", "item-format" => "normal", "item-class" => "", "width" => "20%", "add-style" => ""
                                            ),
                                            array(
                                                "label" => "Status", "name" => "status_code", "align" => "center", "item-align" => "center", "item-format" => "flag", "item-class" => "", "width" => "10%", "add-style" => ""
                                            ),);
            # ---------------
            $data["query"]         = $this->qDocument->getIFICollections();
            $data["select"]        = $data["query"]["data"];
            $data["pagging"]       = getPagging($data["select"]);
            # ---------------
            # Advance Search
            # ---------------
            if (isset($request->module_id)) {
                $document_no       = ($request->document_no  != "") ? session(["SES_SEARCH_IFI_DOCUMENT_NO" => $request->document_no]) : $request->session()->forget("SES_SEARCH_IFI_DOCUMENT_NO");
                $document_title    = ($request->document_title  != "") ? session(["SES_SEARCH_IFI_DOCUMENT_TITLE" => $request->document_title]) : $request->session()->forget("SES_SEARCH_IFI_DOCUMENT_TITLE");
                $status            = ($request->status  != "") ? session(["SES_SEARCH_IFI_DOCUMENT_STATUS" => $request->status]) : $request->session()->forget("SES_SEARCH_IFI_DOCUMENT_STATUS");
                $vendor_id         = ($request->vendor_id  != "") ? session(["SES_SEARCH_IFI_DOCUMENT_VENDOR" => $request->vendor_id]) : $request->session()->forget("SES_SEARCH_IFI_DOCUMENT_VENDOR");
                # ---------------
                return redirect("/information/index");
            }
            # ---------------
            if ($request->session()->has("SES_SEARCH_IFI_DOCUMENT_NO")) {
                array_push($data["filtered_info"], "DOCUMENT NO");
            }
            # ---------------
            if ($request->session()->has("SES_SEARCH_IFI_DOCUMENT_TITLE")) {
                array_push($data["filtered_info"], "DOCUMENT TITLE");
            }

            if ($request->session()->has("SES_SEARCH_IFI_DOCUMENT_STATUS")) {
                if ($request->session()->get("SES_SEARCH_IFI_DOCUMENT_STATUS") != "0") {
                    array_push($data["filtered_info"], "STATUS");
                }
            }
            if ($request->session()->has("SES_SEARCH_IFI_DOCUMENT_VENDOR")) {
                if ($request->session()->get("SES_SEARCH_IFI_DOCUMENT_VENDOR") != "0") {
                    array_push($data["filtered_info"], "VENDOR");
                }
            }
            # ---------------
            $data["adv_search"]    = true;
            $data["hide_simple_search"] = true;
            # ---------------
            $data["fields"][]      = form_hidden(array("name" => "module_id", "label" => "Module ID", "value" => "DOCUMENT_IFI"));
            $data["fields"][]      = form_search_text(array("name" => "document_no", "label" => "Document No", "value" => ($request->session()->has("SES_SEARCH_IFI_DOCUMENT_NO")) ? $request->session()->get("SES_SEARCH_IFI_DOCUMENT_NO") : ""));
            $data["fields"][]      = form_search_text(array("name" => "document_title", "label" => "Document Title", "value" => ($request->session()->has("SES_SEARCH_IFI_DOCUMENT_TITLE")) ? $request->session()->get("SES_SEARCH_IFI_DOCUMENT_TITLE") : ""));
            $data["fields"][]      = form_search_select(array("name" => "vendor_id", "label" => "Vendor", "source" => $qVendor,  "withnull" => "yes", "value" => ($request->session()->has("SES_SEARCH_IFI_DOCUMENT_VENDOR")) ? $request->session()->get("SES_SEARCH_IFI_DOCUMENT_VENDOR") : ""));
            $data["fields"][]      = form_search_select(array("name" => "status", "label" => "Status", "source" => $qStatus,  "withnull" => "yes", "value" => ($request->session()->has("SES_SEARCH_IFI_DOCUMENT_STATUS")) ? $request->session()->get("SES_SEARCH_IFI_DOCUMENT_STATUS") : ""));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_search", "label" => "&nbsp;&nbsp;Search&nbsp;&nbsp;"));
            $data["buttons"][]     = form_action_button(array("name" => "button_clear", "label" => "&nbsp;&nbsp;Clear&nbsp;&nbsp;", "url" => "/information/unfilter"));
            # ---------------
            return view("document.list", $data);
        } catch (\Exception $e) {
            throw $e;
            $this->logModel->createError($e->getMessage(), "PAGE INFORMATION", "");
            # ---------------
            return view("error.405");
        }
    }

    public function detail($id) {
        try {
            $data["title"]         = "Detail";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/information/index";
            # ---------------
            $id                    = decodedData($id);
            $qSelectStatus         = array(array("id"=>6, "name"=>"Approve")
                                          , array("id"=>88, "name"=>"Reject"));
            /* ----------
             Model
            ----------------------- */
            $data['document']      = $this->qDocument->getDataDocExistingById($id);
            $userName              = $this->qUser->getProfile($data['document']->approved_by)->first();
            $file                  = (!empty($data['document']->document_file)) ? asset("/uploads").$data['document']->document_url . $data["document"]->document_file : "";
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_text(array("name"=>"document_no", "label"=>"Document Number", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$data["document"]->document_no));
            $data["fields"][]      = form_text(array("name"=>"document_title", "label"=>"Document Title", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$data["document"]->document_title));
            $data["fields"][]      = form_text(array("name"=>"incoming_no", "label"=>"Incoming No", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$data["document"]->incoming_no));
            $data["fields"][]      = form_date(array("name"=>"incoming_date", "label"=>"Receive Date", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>displayDMY($data["document"]->receive_date)));
            $data["fields"][]      = form_file(array("name"=>"document", "label"=>"Document IFI", "readonly"=>"readonly", "value"=>$file));
            $data["fields"][]      = form_select(array("name"=>"status", "label"=>"Status", "source"=>$qSelectStatus, "readonly"=>"readonly", "value"=>$data['document']->status));
            if($data['document']->approved_by != 0) {
                $data["fields"][]      = form_text(array("name"=>"approved_by", "label"=>"By", "readonly"=>"readonly", "value"=>$userName->full_name));
            }
            $data["fields"][]      = form_textarea(array("name"=>"remark_approval", "label"=>"Notes", "readonly"=>"readonly", "value"=>$data['document']->approved_comment));
            # ---------------
            $data["buttons"][]     = form_button_cancel(array("name"=>"button_cancel", "label"=>"Cancel"));
            # ---------------
            return view("default.form", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            # ---------------
            return view("error.405");
        }        
    }

    public function approve($id) {
        try {
            $data["title"]         = "Approve";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/information/save_approve";
            # ---------------
            $id                    = decodedData($id);
            /* ----------
             Model
            ----------------------- */
            $data['document']      = $this->qDocument->getDataDocExistingById($id);
            $file                  = (!empty($data['document']->document_file)) ? asset("/uploads").$data['document']->document_url . $data["document"]->document_file : "";
            $qSelectStatus         = array(array("id"=>6, "name"=>"Approve")
                                          , array("id"=>88, "name"=>"Reject"));

            if($data['document']->status != 7) {
                session()->flash("error_message", "Documents IFI cannot be approve");
                # ---------------
                return redirect("/information/index");
            }
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name"=>"document_id", "label"=>"Document ID", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$data["document"]->document_id));
            $data["fields"][]      = form_text(array("name"=>"document_no", "label"=>"Document Number", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$data["document"]->document_no));
            $data["fields"][]      = form_text(array("name"=>"document_title", "label"=>"Document Title", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$data["document"]->document_title));
            $data["fields"][]      = form_text(array("name"=>"incoming_no", "label"=>"Incoming No", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$data["document"]->incoming_no));
            $data["fields"][]      = form_date(array("name"=>"incoming_date", "label"=>"Receive Date", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>displayDMY($data["document"]->receive_date)));
            $data["fields"][]      = form_file(array("name"=>"document", "label"=>"Document IFI", "readonly"=>"readonly", "value"=>$file));
            $data["fields"][]      = form_select(array("name"=>"status", "label"=>"Status", "source"=>$qSelectStatus, "value"=>6));
            $data["fields"][]      = form_textarea(array("name"=>"remark_approval", "label"=>"Notes", "value"=>""));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_save", "label"=>"&nbsp;&nbsp;Save&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name"=>"button_cancel", "label"=>"Cancel"));
            # ---------------
            return view("default.form", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            # ---------------
            return view("error.405");
        }        
    }

    public function save_approve(Request $request) {
        try {
            $response   = $this->qDocument->approveIFI($request);

            if($response["status"]) {
                session()->flash("success_message", SUCCESS_MESSAGE);
            } else {
                session()->flash("error_message", FAILED_MESSAGE);
            }
            # ---------------
            return redirect("/information/index");
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE APPROVE IFI", "");
            # ---------------
            return view("error.405");
        }
    }

    public function unfilter() {
        session()->forget("SES_SEARCH_IFI_DOCUMENT_NO");
        session()->forget("SES_SEARCH_IFI_DOCUMENT_TITLE");
        session()->forget("SES_SEARCH_IFI_DOCUMENT_STATUS");
        session()->forget("SES_SEARCH_IFI_DOCUMENT_VENDOR");
        # ---------------
        return redirect("/information/index");
    }
}
