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
            $data["title"]         = ($this->isVendor == "YES") ? "Add Outgoing Transmittal" : "Add Incoming Transmittal";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = ($this->isVendor == "YES") ? "/vendor_outgoing/save" : "/incoming/save";
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
            $selectDocumentStatus     = [];
            $selectDocumentStatusIFI  = array(array("id"=>120, "name"=>"0A"));
            // $selectDocumentStatus  = $this->qReference->getSelectDocumentStatus();
            # ---------------
            $data["items"]            = $this->qIncoming->getItemTemp();
            // $this->qIncoming->emptyTemp();
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name"=>"vendor_id", "label"=>"Vendor", "mandatory"=>"yes", "value"=>Auth::user()->vendor_id));
            $data["fields"][]      = form_text(array("name"=>"incoming_no", "label"=>($this->isVendor == "YES") ? "Outgoing Number" : "Incoming Number", "mandatory"=>"yes", "first_selected"=>"yes"));
            $data["fields"][]      = form_hidden(array("name"=>"receive_date", "label"=>($this->isVendor == "YES") ? "Sending Date" : "Receive Date", "mandatory"=>"yes", "value"=>date("d/m/Y")));
            $data["fields"][]      = form_hidden(array("name"=>"sender_date", "label"=>"Sender Date", "mandatory"=>"yes", "value"=>date("d/m/Y")));
            $data["fields"][]      = form_text(array("name"=>"subject", "label"=>"Subject", "mandatory"=>"yes", "value"=>""));
            $data["fields"][]      = form_hidden(array("name"=>"return_date_plan", "label"=>"Return Plan Date", "value"=>date("d/m/Y")));
            $data["fields"][]      = form_hidden(array("name"=>"return_date_actual", "label"=>"Return Plan Actual", "value"=>date("d/m/Y")));  
            $data["fields"][]      = form_text(array("name"=>"description", "label"=>"Remark"));          
            $data["fields"][]      = form_upload(array("name"=>"receipt", "label"=>"Receipt"));
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
            # ---------------
            $file                  = asset("/uploads").$data["header"]->receipt_url . $data["header"]->receipt_file;
            $delete_file           = url('')."/incoming/delete_receipt/" . $id_enc;
            /* ----------
             Fields
            ----------------------- */
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
            return view("incoming.form-edit", $data);
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

    public function update(Request $request) {
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

    public function approve($id) {
        try {
            $data["title"]         = "Approve Incoming Transmittal";
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

    public function get_document_status($id)
    {
        $tipe = DB::table("ref_document_status")->select('document_status_id AS id', 'name AS name')
                                                ->where('issue_status_id', $id)
                                                ->where('status', 1)
                                                ->get(['id', 'name']);

        return response()->json(['data' => $tipe->toArray()]);
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
