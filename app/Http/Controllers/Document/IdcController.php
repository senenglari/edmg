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
use App\Model\Document\IdcModel;
use App\Model\Document\DocumentModel;
use App\Model\Sys\SysModel;
use App\Model\Sys\LogModel;

class IdcController extends Controller
{
    protected $PROT_SideMenu, $PROT_Parent, $PROT_ModuleId, $PROT_ModuleName;

    public function __construct(Request $request) {
        # ---------------
        $uri                      = getUrl() . "/index";
        # ---------------
        $this->qMenu              = new MenuModel;
        $this->qUser              = new UserModel;
        $this->qReference         = new ReferenceModel;
        $this->qIdc               = new IdcModel;
        $this->qDocument          = new DocumentModel;
        $this->sysModel           = new SysModel;
        $this->logModel           = new LogModel;
        # ---------------
        $rs                       = $this->qMenu->getParentMenu($uri);
        # ---------------
        $this->PROT_Parent        = (count($rs) > 0) ? $rs[0]->parent_name : '';
        $this->PROT_ModuleName    = (count($rs) > 0) ? $rs[0]->name : '';
        $this->PROT_ModuleId      = (count($rs) > 0) ? $rs[0]->id : '';
        $this->isVendor           = "YES";
        # ---------------
        View::share(array("SHR_Parent"=>$this->PROT_Parent, "SHR_Module"=>$this->PROT_ModuleName, "SHR_ModuleId"=>$this->PROT_ModuleId));
    }

    public function index(Request $request)
    {
        try {
            $data["title"]            = ucwords(strtolower($this->PROT_ModuleName));
            $data["parent"]           = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]         = "/vendor_outgoing/index";
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
            $data["table_header"]   = array(array("label"=>"ID"
                                                ,"name"=>"incoming_transmittal_id"
                                                    ,"align"=>"center"
                                                    ,"item-align"=>"center"
                                                        ,"item-format"=>"checkbox"
                                                        ,"item-class"=>""
                                                            ,"width"=>"5%"
                                                            ,"add-style"=>""),
                                        array("label"=>"Incoming No"
                                                ,"name"=>"incoming_no"
                                                    ,"align"=>"center"
                                                    ,"item-align"=>"center"
                                                        ,"item-format"=>"normal"
                                                        ,"item-class"=>""
                                                            ,"width"=>"20%"
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
            # ---------------
            $data["query"]         = $this->qIdc->getCollections();
            $data["select"]        = $data["query"]["data"];
            $data["pagging"]       = getPagging($data["select"]);
            # ---------------
            # Advance Search
            # ---------------
            if(isset($request->module_id)) {
                $incoming_no       = ($request->incoming_no != "") ? session(["SES_SEARCH_INCOMING_NO" => $request->incoming_no]) : $request->session()->forget("SES_SEARCH_INCOMING_NO");
                $receive_date      = ($request->receive_date  != "") ? session(["SES_SEARCH_INCOMING_RECEIVE" => $request->receive_date]) : $request->session()->forget("SES_SEARCH_INCOMING_RECEIVE");
                $subject           = ($request->subject  != "") ? session(["SES_SEARCH_INCOMING_SUBJECT" => $request->subject]) : $request->session()->forget("SES_SEARCH_INCOMING_SUBJECT");
                # ---------------
                return redirect("/vendor_outgoing/index");
            }
            # ---------------
            if($request->session()->has("SES_SEARCH_INCOMING_NO")) {
                    array_push($data["filtered_info"], "OUTGOING NUMBER");
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
            $data["fields"][]      = form_search_text(array("name"=>"incoming_no", "label"=>"Outgoing Number", "value"=>($request->session()->has("SES_SEARCH_INCOMING_NO")) ? $request->session()->get("SES_SEARCH_INCOMING_NO") : ""));
            $data["fields"][]      = form_search_text(array("name"=>"subject", "label"=>"Subject", "value"=>($request->session()->has("SES_SEARCH_INCOMING_SUBJECT")) ? $request->session()->get("SES_SEARCH_INCOMING_SUBJECT") : ""));
            $data["fields"][]      = form_search_datepicker(array("name"=>"receive_date", "label"=>"Receive Date", "value"=>($request->session()->has("SES_SEARCH_INCOMING_RECEIVE")) ? $request->session()->get("SES_SEARCH_INCOMING_RECEIVE") : ""));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_search", "label"=>"&nbsp;&nbsp;Search&nbsp;&nbsp;"));
            $data["buttons"][]     = form_action_button(array("name"=>"button_clear", "label"=>"&nbsp;&nbsp;Clear&nbsp;&nbsp;", "url"=>"/vendor_outgoing/unfilter"));
            # ---------------
            return view("default.list", $data);
        } catch (\Exception $e) {
            throw $e;
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            # ---------------
            return view("error.405");
        }
    }  

    public function unfilter() {
        session()->forget("SES_SEARCH_INCOMING_NO");
        session()->forget("SES_SEARCH_INCOMING_RECEIVE");
        session()->forget("SES_SEARCH_INCOMING_SUBJECT");
        session()->forget("SES_SEARCH_INCOMING_VENDOR");
        # ---------------
        if($this->isVendor == "YES") {
            return redirect("/vendor_outgoing/index");
        } else {
            return redirect("/incoming/index");
        }
    }

    public function add() {
        try {
            $data["title"]         = "Add Incoming IDC";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/idc/save";
            /* ----------
             Model
            ----------------------- */
            $selectDocument           = $this->qReference->getSelectDocumentIdc(Auth::user()->vendor_id);
            $selectIssueStatus        = $this->qReference->getSelectIssueStatusWithoutIFI();
            $selectIssueStatusIFI     = $this->qReference->getSelectIssueStatusIFI();
            $selectReturnStatus       = $this->qReference->getSelectReturnStatus();
            $selectVendor             = $this->qReference->getSelectVendor();
            $selectProject            = $this->qReference->getSelectProject();
            $selectDocumentStatus     = [];
            $selectDocumentStatusIFI  = array(array("id"=>120, "name"=>"0A"));
            // $selectDocumentStatus  = $this->qReference->getSelectDocumentStatus();
            # ---------------
            $data["items"]            = $this->qIdc->getItemTemp();
            // $this->qIncoming->emptyTemp();
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name"=>"vendor_id", "label"=>"Vendor", "mandatory"=>"yes", "value"=>Auth::user()->vendor_id));
            $data["fields"][]      = form_text(array("name"=>"incoming_no", "label"=>"Incoming Number", "mandatory"=>"yes", "first_selected"=>"yes"));
            /* ----------
             Modal Fields
            ----------------------- */
            $data["fields_modal"][]= form_upload(array("name"=>"document_file", "label"=>"Document File"));
            $data["fields_modal"][]= form_upload(array("name"=>"document_crs", "label"=>"CRS File"));
            $data["fields_modal"][]= form_select(array("name"=>"document_id", "label"=>"Document Number", "source"=>$selectDocument));
            $data["fields_modal"][]= form_text(array("name"=>"issue_status_id", "label"=>"Issue Status", "readonly"=>"readonly", "value"=>"IDC"));
            // $data["fields_modal"][]= form_select(array("name"=>"document_status_id", "label"=>"Revision Number", "withnull"=>"yes", "source"=>$selectDocumentStatus));
            $data["fields_modal"][]= form_hidden(array("name"=>"return_status_id", "label"=>"Return Status", "withnull"=>"yes", "source"=>$selectReturnStatus));
            $data["fields_modal"][]= form_text(array("name"=>"remark", "label"=>"Description"));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_save", "label"=>"&nbsp;&nbsp;Save&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name"=>"button_cancel", "label"=>"Cancel"));
            # ---------------
            $data["attach_url"]    = "/idc/attach_item";
            $data["delete_url"]    = "/idc/delete_item";
            # ---------------
            return view("idc.form-add", $data);
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
                    $response   = $this->qIdc->attachItem($request);
            
                    if($response["status"]) {
                        $items  = $this->qIdc->getItemTemp();

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



        // $response   = $this->qIdc->attachItem($request);
        
        // if($response["status"]) {
        //     $items  = $this->qIdc->getItemTemp();

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
        $response   = $this->qIdc->deleteItem($id);
        
        if($response["status"]) {
            $items  = $this->qIdc->getItemTemp();

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
                $response   = $this->qIdc->saveIncoming($request);

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


}
