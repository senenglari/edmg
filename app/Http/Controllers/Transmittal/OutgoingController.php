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
use App\Model\Transmittal\OutgoingModel;
use App\Model\Reference\VendorModel;
use App\Model\Sys\LogModel;

class OutgoingController extends Controller
{
    protected $PROT_SideMenu, $PROT_Parent, $PROT_ModuleId, $PROT_ModuleName;

    public function __construct(Request $request) {
        # ---------------
        $uri                      = getUrl() . "/index";
        # ---------------
        $this->qMenu              = new MenuModel;
        $this->qUser              = new UserModel;
        $this->qReference         = new ReferenceModel;
        $this->qOutgoing          = new OutgoingModel;
        $this->qVendor            = new VendorModel;
        $this->logModel           = new LogModel;
        # ---------------
        $rs                       = $this->qMenu->getParentMenu($uri);
        # ---------------
        $this->PROT_Parent        = (count($rs) > 0) ? $rs[0]->parent_name : '';
        $this->PROT_ModuleName    = (count($rs) > 0) ? $rs[0]->name : '';
        $this->PROT_ModuleId      = (count($rs) > 0) ? $rs[0]->id : '';
        $this->isVendor           = (getUrl() == "/vendor_incoming") ? "YES" : "NO";
        # ---------------
        View::share(array("SHR_Parent"=>$this->PROT_Parent, "SHR_Module"=>$this->PROT_ModuleName, "SHR_ModuleId"=>$this->PROT_ModuleId));
    }

    public function index(Request $request)
    {
        try {
            # ---------------
            $data["title"]            = ucwords(strtolower($this->PROT_ModuleName));
            $data["parent"]           = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]         = ($this->isVendor == "YES") ? "/vendor_incoming/index" : "/outgoing/index";
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
                                                        ,"name"=>"outgoing_transmittal_id"
                                                          ,"align"=>"center"
                                                            ,"item-align"=>"center"
                                                              ,"item-format"=>"checkbox"
                                                                ,"item-class"=>""
                                                                  ,"width"=>"5%"
                                                                    ,"add-style"=>""),
                                                array("label"=>"Incoming No"
                                                        ,"name"=>"outgoing_no"
                                                          ,"align"=>"center"
                                                            ,"item-align"=>"center"
                                                              ,"item-format"=>"normal"
                                                                ,"item-class"=>""
                                                                  ,"width"=>"20%"
                                                                    ,"add-style"=>""),
                                                array("label"=>"Receive Date"
                                                        ,"name"=>"sen_date"
                                                          ,"align"=>"center"
                                                            ,"item-align"=>"center"
                                                              ,"item-format"=>"normal"
                                                                ,"item-class"=>""
                                                                  ,"width"=>"20%"
                                                                    ,"add-style"=>""),
                                                array("label"=>"Vendor"
                                                        ,"name"=>"vendor_name"
                                                          ,"align"=>"center"
                                                            ,"item-align"=>"left"
                                                              ,"item-format"=>"normal"
                                                                ,"item-class"=>""
                                                                  ,"width"=>"20%"
                                                                    ,"add-style"=>""),
                                                array("label"=>"Subject"
                                                        ,"name"=>"subject"
                                                          ,"align"=>"center"
                                                            ,"item-align"=>"left"
                                                              ,"item-format"=>"normal"
                                                                ,"item-class"=>""
                                                                  ,"width"=>""
                                                                    ,"add-style"=>""));
            } else {
                $selectVendor           = $this->qReference->getSelectVendor();

                $data["table_header"]   = array(array("label"=>"ID"
                                                        ,"name"=>"outgoing_transmittal_id"
                                                          ,"align"=>"center"
                                                            ,"item-align"=>"center"
                                                              ,"item-format"=>"checkbox"
                                                                ,"item-class"=>""
                                                                  ,"width"=>"5%"
                                                                    ,"add-style"=>""),
                                                array("label"=>"Outgoing No"
                                                        ,"name"=>"outgoing_no"
                                                          ,"align"=>"center"
                                                            ,"item-align"=>"center"
                                                              ,"item-format"=>"normal"
                                                                ,"item-class"=>""
                                                                  ,"width"=>"20%"
                                                                    ,"add-style"=>""),
                                                array("label"=>"Sending Date"
                                                        ,"name"=>"sen_date"
                                                          ,"align"=>"center"
                                                            ,"item-align"=>"center"
                                                              ,"item-format"=>"normal"
                                                                ,"item-class"=>""
                                                                  ,"width"=>"20%"
                                                                    ,"add-style"=>""),
                                                array("label"=>"Vendor"
                                                        ,"name"=>"vendor_name"
                                                          ,"align"=>"center"
                                                            ,"item-align"=>"left"
                                                              ,"item-format"=>"normal"
                                                                ,"item-class"=>""
                                                                  ,"width"=>"20%"
                                                                    ,"add-style"=>""),
                                                array("label"=>"Subject"
                                                        ,"name"=>"subject"
                                                          ,"align"=>"center"
                                                            ,"item-align"=>"left"
                                                              ,"item-format"=>"normal"
                                                                ,"item-class"=>""
                                                                  ,"width"=>""
                                                                    ,"add-style"=>""),
                                                array("label"=>"Status"
                                                        ,"name"=>"status_code"
                                                          ,"align"=>"center"
                                                            ,"item-align"=>"center"
                                                              ,"item-format"=>"flag"
                                                                ,"item-class"=>""
                                                                  ,"width"=>"10%"
                                                                    ,"add-style"=>""));
            }
            # ---------------
            if($this->isVendor == "YES") {
                $data["query"]         = $this->qOutgoing->getCollection_Vendors();
            } else {
                $data["query"]         = $this->qOutgoing->getCollections();
            }
            $data["select"]        = $data["query"]["data"];
            $data["pagging"]       = getPagging($data["select"]);
            # ---------------
            # Advance Search
            # ---------------
            if(isset($request->module_id)) {
                $outgoing_no       = ($request->outgoing_no != "") ? session(["SES_SEARCH_OUTGOING_NO" => $request->outgoing_no]) : $request->session()->forget("SES_SEARCH_OUTGOING_NO");
                $sending_date      = ($request->sending_date  != "") ? session(["SES_SEARCH_OUTGOING_SENDING" => $request->sending_date]) : $request->session()->forget("SES_SEARCH_OUTGOING_SENDING");
                $subject           = ($request->subject  != "") ? session(["SES_SEARCH_OUTGOING_SUBJECT" => $request->subject]) : $request->session()->forget("SES_SEARCH_OUTGOING_SUBJECT");
                # ---------------
                if($this->isVendor == "YES") {
                    return redirect("/vendor_incoming/index");
                } else {
                    $vendor_id           = ($request->vendor_id  != "0") ? session(["SES_SEARCH_OUTGOING_VENDOR" => $request->vendor_id]) : $request->session()->forget("SES_SEARCH_OUTGOING_VENDOR");

                    return redirect("/outgoing/index");
                }
            }
            # ---------------
            if($request->session()->has("SES_SEARCH_OUTGOING_NO")) {
                if($this->isVendor == "YES") {
                    array_push($data["filtered_info"], "INCOMING NUMBER");
                } else {
                    array_push($data["filtered_info"], "OUTGOING NUMBER");
                }                
            }
            # ---------------
            if($request->session()->has("SES_SEARCH_OUTGOING_SUBJECT")) {
                array_push($data["filtered_info"], "SUBJECT");
            }
            # ---------------
            if($request->session()->has("SES_SEARCH_OUTGOING_SENDING")) {
                if($this->isVendor == "YES") {
                    array_push($data["filtered_info"], "RECEIVE DATE");
                } else {
                    array_push($data["filtered_info"], "SENDING DATE");
                }                
                
            }
            if ($request->session()->has("SES_SEARCH_OUTGOING_VENDOR")) {
                if ($request->session()->get("SES_SEARCH_OUTGOING_VENDOR") != "0") {
                    array_push($data["filtered_info"], "VENDOR");
                }
            }
            # ---------------
            $data["adv_search"]    = true;
            $data["hide_simple_search"] = true;
            # ---------------
            $data["fields"][]      = form_hidden(array("name"=>"module_id", "label"=>"Module ID", "value"=>"INCOMING"));
            $data["fields"][]      = form_search_text(array("name"=>"outgoing_no", "label"=>($this->isVendor == "YES") ? "Incoming Number" : "Outgoing Number", "value"=>($request->session()->has("SES_SEARCH_OUTGOING_NO")) ? $request->session()->get("SES_SEARCH_OUTGOING_NO") : ""));
            $data["fields"][]      = form_search_text(array("name"=>"subject", "label"=>"Subject", "value"=>($request->session()->has("SES_SEARCH_OUTGOING_SUBJECT")) ? $request->session()->get("SES_SEARCH_OUTGOING_SUBJECT") : ""));
            $data["fields"][]      = form_search_datepicker(array("name"=>"sending_date", "label"=>($this->isVendor == "YES") ? "Receive Date" : "Sending Date", "value"=>($request->session()->has("SES_SEARCH_OUTGOING_SENDING")) ? $request->session()->get("SES_SEARCH_OUTGOING_SENDING") : ""));
            if($this->isVendor != "YES") {
              $data["fields"][]      = form_search_select(array("name" => "vendor_id", "label" => "Vendor", "source" => $selectVendor,  "withnull" => "yes", "value" => ($request->session()->has("SES_SEARCH_OUTGOING_VENDOR")) ? $request->session()->get("SES_SEARCH_OUTGOING_VENDOR") : ""));
            }
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_search", "label"=>"&nbsp;&nbsp;Search&nbsp;&nbsp;"));
            $data["buttons"][]     = form_action_button(array("name"=>"button_clear", "label"=>"&nbsp;&nbsp;Clear&nbsp;&nbsp;", "url"=>($this->isVendor == "YES") ? "/vendor_incoming/unfilter" : "/outgoing/unfilter"));
            # ---------------
            //echo 'xx';die;
            return view("default.list", $data);
        } catch (\Exception $e) {
            throw $e;
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            # ---------------
            return view("error.405");
        }
    }

    public function unfilter() {
        session()->forget("SES_SEARCH_OUTGOING_NO");
        session()->forget("SES_SEARCH_OUTGOING_SENDING");
        session()->forget("SES_SEARCH_OUTGOING_SUBJECT");
        session()->forget("SES_SEARCH_OUTGOING_VENDOR");
        # ---------------
        if($this->isVendor == "YES") {
            return redirect("/vendor_incoming/index");
        } else {
            return redirect("/outgoing/index");
        }
    }

    public function add() {
		//echo 'xxx';die;
        try {
            $data["title"]         = "Add Outgoing Transmittal";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/outgoing/save";
            /* ----------
             Model
            ----------------------- */
            $qProject              = $this->qReference->getSelectProject();
            $qVendor               = $this->qReference->getSelectVendor();
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_text(array("name"=>"outgoing_no", "label"=>"Outgoing Number", "mandatory"=>"yes", "first_selected"=>"yes"));
            $data["fields"][]      = form_select(array("name"=>"project_id", "label"=>"Project Name", "mandatory"=>"yes", "source"=>$qProject, "withnull"=>"yes"));
            $data["fields"][]      = form_select(array("name"=>"vendor_id", "label"=>"Vendor", "mandatory"=>"yes", "source"=>$qVendor, "withnull"=>"yes"));
            $data["fields"][]      = form_text(array("name"=>"email_address", "label"=>"Email Address", "mandatory"=>"yes", "value"=>""));
            // $data["fields"][]      = form_text(array("name"=>"cc_email_address", "label"=>"CC Email Address", "value"=>""));
            $data["fields"][]      = form_text(array("name"=>"subject", "label"=>"Subject", "mandatory"=>"yes", "value"=>""));
            $data["fields"][]      = form_textarea(array("name"=>"content", "label"=>"Content", "mandatory"=>"yes", "value"=>""));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Save&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));
            # ---------------
            return view("outgoing.form_add_header", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE OUTGOING", "");
            # ---------------
            return view("error.405");
        }        
    }

    public function save(Request $request) {
        try {
            $rules = array(
                'outgoing_no' => 'required|',
                'project_id' => 'required|',
                'vendor_id' => 'required|',
                'email_address' => 'required|',
                'subject' => 'required|',
                'content' => 'required|',
            );

            $messages = [
                'outgoing_no.required' => 'Outgoing number is required',
                'project_id.required' => 'Project name is required',
                'vendor_id.required' => 'Vendor is required',
                'email_address.required' => 'Email address is required',
                'subject.required' => 'Subject is required',
                'content.required' => 'Content is required',
            ];

            $mail_length    = explode(",", $request->email_address);

            for($m=0; $m<count($mail_length); $m++) {
                if(!filter_var(str_replace(" ", "", $mail_length[$m]), FILTER_VALIDATE_EMAIL)) {
                    session()->flash("error_message", 'Invalid email address ' . $mail_length[$m]);
                    # ---------------
                    return redirect("/outgoing/add")->withInput();
                }
            }

            if(!empty($request->cc_email_address)) {
                $mail_cc_length    = explode(",", $request->cc_email_address);

                for($m=0; $m<count($mail_cc_length); $m++) {
                    if(!filter_var(str_replace(" ", "", $mail_cc_length[$m]), FILTER_VALIDATE_EMAIL)) {
                        session()->flash("error_message", 'Invalid email address ' . $mail_cc_length[$m]);
                        # ---------------
                        return redirect("/outgoing/add")->withInput();
                    }
                }
            }

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("/outgoing/add")
                            ->withErrors($validator)
                            ->withInput();
            } else {
                $response   = $this->qOutgoing->saveOutgoingHeader($request);

                if($response["status"]) {
                    session()->flash("success_message", SUCCESS_MESSAGE);
                } else {
                    session()->flash("error_message", $response["message"]);

                    return redirect("/outgoing/index");
                }

                $id = encodedData($response["id"]);
                # ---------------
                return redirect("/outgoing/editdetail/".$id);
            }
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE ADD OUTGOING", "");
            # ---------------
            return view("error.405");
        }
    }

    public function edit($id) {
        try {
            $data["title"]         = "Edit Outgoing Transmittal";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/outgoing/update";
            # ---------------
            $idData                = decodedData($id);
            /* ----------
             Model
            ----------------------- */
            $data["header"]        = $this->qOutgoing->getHeader($idData);
            $qProject              = $this->qReference->getSelectProject();
            $qVendor               = $this->qReference->getSelectVendor();
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name" => "id", "label" => "Outgoing ID", "readonly" => "readonly", "value" => $id));
            $data["fields"][]      = form_hidden(array("name" => "idData", "label" => "Outgoing ID", "readonly" => "readonly", "value" => $idData));
            $data["fields"][]      = form_text(array("name"=>"outgoing_no", "label"=>"Outgoing Number", "mandatory"=>"yes", "first_selected"=>"yes", "value"=>$data["header"]->outgoing_no));
            $data["fields"][]      = form_select(array("name"=>"project_id", "label"=>"Project Name", "mandatory"=>"yes", "source"=>$qProject, "withnull"=>"yes", "value"=>$data["header"]->project_id));
            $data["fields"][]      = form_select(array("name"=>"vendor_id", "label"=>"Vendor", "mandatory"=>"yes", "source"=>$qVendor, "withnull"=>"yes", "value"=>$data["header"]->vendor_id));
            $data["fields"][]      = form_text(array("name"=>"email_address", "label"=>"Email Address", "mandatory"=>"yes", "value"=>$data["header"]->email_address));
            // $data["fields"][]      = form_text(array("name"=>"cc_email_address", "label"=>"CC Email Address", "value"=>$data["header"]->cc_email_address));
            $data["fields"][]      = form_text(array("name"=>"subject", "label"=>"Subject", "mandatory"=>"yes", "value"=>$data["header"]->subject));
            $data["fields"][]      = form_textarea(array("name"=>"content", "label"=>"Content", "mandatory"=>"yes", "value"=>$data["header"]->content));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Save&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));
            # ---------------
            return view("outgoing.form_add_header", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            # ---------------
            return view("error.405");
        }        
    }

    public function update(Request $request) {
        try {
            $rules = array(
                'outgoing_no' => 'required|',
                'project_id' => 'required|',
                'vendor_id' => 'required|',
                'email_address' => 'required|',
                'subject' => 'required|',
                'content' => 'required|',
            );

            $messages = [
                'outgoing_no.required' => 'Outgoing number is required',
                'project_id.required' => 'Project name is required',
                'vendor_id.required' => 'Vendor is required',
                'email_address.required' => 'Email address is required',
                'subject.required' => 'Subject is required',
                'content.required' => 'Content is required',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("/outgoing/edit/".$request->id)
                            ->withErrors($validator)
                            ->withInput();
            } else {
                $response   = $this->qOutgoing->updateOutgoingHeader($request);

                if($response["status"]) {
                    session()->flash("success_message", SUCCESS_MESSAGE);
                } else {
                    session()->flash("error_message", FAILED_MESSAGE);
                }
                # ---------------
                return redirect("/outgoing/editdetail/".$request->id);
            }
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE ADD OUTGOING", "");
            # ---------------
            return view("error.405");
        }
    }

    public function vendorproject($id)
    {
        $tipe = DB::table('document')
                    ->select('document.vendor_id AS id', 'ref_vendor.name AS name')
                    ->join('ref_vendor', 'ref_vendor.vendor_id', '=', 'document.vendor_id')
                    ->where('project_id', $id)
                    ->groupBy('document.vendor_id')
                    ->get(['id', 'name']);
        return response()->json(['data' => $tipe->toArray()]);
    }

    public function editdetail($id) {
		
        try {
            $data["title"]         = "Edit Outgoing Transmittal";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/outgoing/updatedetail";
            # ---------------
            $idData                = decodedData($id);
            /* ----------
             Model
            ----------------------- */
            $data["header"]        = $this->qOutgoing->getHeader($idData);

            // Guard: jika ini outgoing company (REV-xxx-xxx), redirect ke outgoing company
            if($data["header"] && preg_match('/^REV-[0-9]+-[0-9]+$/', $data["header"]->outgoing_no)) {
                return redirect("/outgoing_company/index");
            }

            if($data["header"]->sender_date != null) {
                session()->flash("error_message", "Outgoing cannot be changed");
                # ---------------
                return redirect("/outgoing/index");
            }

            $data["detail"]        = $this->qOutgoing->getDetail($idData);
            $qProject              = $this->qReference->getSelectProject();
            $qVendor               = $this->qReference->getSelectVendor();
            $qVendorName           = $this->qVendor->getVendor($data["header"]->vendor_id);
            $qStatusEmail          = getSelectStatusEmail();
            $selectDocument        = $this->qReference->getSelectDocumentOutgoing($data["header"]->vendor_id);
            $selectReturnStatus    = $this->qReference->getSelectReturnStatus();
            /* ----------
             Fields
            ----------------------- */
            $project_name   = "";
            $vendor_name    = "";
            $key_project    = array_search($data["header"]->project_id, array_column($qProject, "id"));
            $key_vendor     = array_search($data["header"]->vendor_id, array_column($qVendor, "id"));
            if($key_project >= 0){
                $project_name   = $qProject[$key_project]->name;
            }
            if($key_vendor >= 0){
                $vendor_name    = $qVendor[$key_vendor]->name;
            }
            $data["fields"][]      = form_hidden(array("name" => "id", "label" => "Outgoing ID", "readonly" => "readonly", "value" => $id));
            $data["fields"][]      = form_hidden(array("name" => "idData", "label" => "Outgoing ID", "readonly" => "readonly", "value" => $idData));
            $data["fields"][]      = form_hidden(array("name" => "project_name", "label" => "Outgoing ID", "readonly" => "readonly", "value" => $project_name ));
            $data["fields"][]      = form_hidden(array("name" => "vendor_name", "label" => "Outgoing ID", "readonly" => "readonly", "value" => $vendor_name ));
            $data["fields"][]      = form_hidden(array("name" => "vendor_pic", "label" => "Outgoing ID", "readonly" => "readonly", "value" => $qVendorName["data"]->pic ));
            $data["fields"][]      = form_hidden(array("name" => "vendor_address", "label" => "Outgoing ID", "readonly" => "readonly", "value" => $qVendorName["data"]->address ));
            $data["fields"][]      = form_hidden(array("name" => "vendor_phone_number", "label" => "Outgoing ID", "readonly" => "readonly", "value" => $qVendorName["data"]->phone_number ));

            $data["fields"][]      = form_text(array("name"=>"outgoing_no", "label"=>"Outgoing Number", "mandatory"=>"yes", "readonly" => "readonly", "value"=>$data["header"]->outgoing_no));
            $data["fields"][]      = form_select(array("name"=>"project_id", "label"=>"Project Name", "mandatory"=>"yes", "source"=>$qProject, "withnull"=>"yes", "value"=>$data["header"]->project_id, "readonly" => "readonly"));
            $data["fields"][]      = form_select(array("name"=>"vendor_id", "label"=>"Vendor", "mandatory"=>"yes", "source"=>$qVendor, "withnull"=>"yes", "value"=>$data["header"]->vendor_id, "readonly" => "readonly"));
            $data["fields"][]      = form_text(array("name"=>"email_address", "label"=>"Email Address", "mandatory"=>"yes", "value"=>$data["header"]->email_address));
            // $data["fields"][]      = form_text(array("name"=>"cc_email_address", "label"=>"CC Email Address", "value"=>$data["header"]->cc_email_address));
            $data["fields"][]      = form_text(array("name"=>"subject", "label"=>"Subject", "mandatory"=>"yes", "value"=>$data["header"]->subject));
            $data["fields"][]      = form_textarea(array("name"=>"content", "label"=>"Content", "mandatory"=>"yes", "value"=>$data["header"]->content));
            $data["fields"][]      = form_select(array("name"=>"status_email", "label"=>"Send Email ?", "mandatory"=>"yes", "source"=>$qStatusEmail, "value"=>2));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Save&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel2(array("name"=>"button_cancel", "label"=>"Cancel", "action"=>url('')."/outgoing/index"));
            /* ----------
             Modal Fields
            ----------------------- */
            $data["titlePerson"]        = "Tambah Data Dokumen";
            $data["form_act_modal"]     = "/outgoing/attachdocument";
            $data["fields_modal"][]     = form_hidden(array("name" => "id", "label" => "Outgoing ID", "readonly" => "readonly", "value" => $id));
            $data["fields_modal"][]     = form_hidden(array("name" => "idData", "label" => "Outgoing ID", "readonly" => "readonly", "value" => $idData));
            $data["fields_modal"][]     = form_select(array("name"=>"incoming_transmittal_detail_id", "label"=>"Document Number", "mandatory"=>"yes", "source"=>$selectDocument));
            $data["fields_modal"][]     = form_upload(array("name"=>"document_file", "label"=>"Document File"));
            $data["fields_modal"][]     = form_upload(array("name"=>"document_crs", "label"=>"CRS File"));
            // $data["fields_modal"][]     = form_upload_multi(array("name"=>"document_crs", "label"=>"Document CRS(s)", "mandatory"=>"yes"));
            $data["fields_modal"][]     = form_select(array("name"=>"return_status_id", "label"=>"Return Code", "source"=>$selectReturnStatus));
            # ---------------
            return view("outgoing.form_edit_detail", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            # ---------------
            return view("error.405");
        }        
    }

    public function updatedetail(Request $request) {
        
        try {
            // Guard: jangan proses outgoing company lewat internal flow
            $header = $this->qOutgoing->getHeader($request->idData);
            if($header && preg_match('/^REV-[0-9]+-[0-9]+$/', $header->outgoing_no)) {
                session()->flash("error_message", "This is an Outgoing Company record. Please process from Outgoing Company menu.");
                return redirect("/outgoing_company/index");
            }

            // $this->qOutgoing->printOutgoing($request);
            // $this->qOutgoing->printReviewResult($request);
            $response   = $this->qOutgoing->updateOutgoingDetail($request);
            
            // dd($request);
            // echo 'xx';die;
            
            if($response["status"]) {
                session()->flash("success_message", SUCCESS_MESSAGE);
            } else {
                session()->flash("error_message", FAILED_MESSAGE);
            }
            
            
            # ---------------
            return redirect("/outgoing/index");
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE ADD OUTGOING", "");
            # ---------------
            return view("error.405");
        }
    }

    public function attachdocument(Request $request)
    {   
        try {
            $rules = array(
                'incoming_transmittal_detail_id' => 'required|',
            );

            $messages = [
                'incoming_transmittal_detail_id.required' => 'No Invoice Harus Diisi',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("/outgoing/editdetail/" . $request->id)
                    ->withErrors($validator)
                    ->withInput();
            } else {
                $response   = $this->qOutgoing->insertOutgoingDetail($request);

                if($response["status"]) {
                    session()->flash("success_message", SUCCESS_MESSAGE);
                } else {
                    session()->flash("error_message", FAILED_MESSAGE);
                }
                # ---------------
                return redirect("/outgoing/editdetail/".$request->id);
            }
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE ADD OUTGOING", "");
            # ---------------
            return view("error.405");
        }
    }

    public function deletedocument($iddetail, $idheader)
    {
        try {
            $response   = $this->qOutgoing->removeOutgoingDetail($iddetail);

            if($response["status"]) {
                session()->flash("success_message", SUCCESS_MESSAGE);
            } else {
                session()->flash("error_message", FAILED_MESSAGE);
            }

            $id = encodedData($idheader);
            # ---------------
            return redirect("/outgoing/editdetail/".$id);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE ADD OUTGOING", "");
            # ---------------
            return view("error.405");
        }
    }

    public function detail($id) {
        try {
            $data["title"]         = ($this->isVendor == "YES") ? "Detail Incoming Transmittal" : "Detail Outgoing Transmittal";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/outgoing/index";
            # ---------------
            $idData                = decodedData($id);
            /* ----------
             Model
            ----------------------- */
            $data["header"]        = $this->qOutgoing->getHeader($idData);
            $data["detail"]        = $this->qOutgoing->getDetail($idData);

            $qProject              = $this->qReference->getSelectProject();
            $qVendor               = $this->qReference->getSelectVendor();

            $selectDocument        = $this->qReference->getSelectDocumentOutgoing($data["header"]->vendor_id);
            $selectReturnStatus    = $this->qReference->getSelectReturnStatus();

            $file                  = (!empty($data["header"]->document_file_review)) ? asset("/uploads") . $data["header"]->document_url_review . $data["header"]->document_file_review : "";

            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name" => "id", "label" => "Outgoing ID", "readonly" => "readonly", "value" => $id));
            $data["fields"][]      = form_hidden(array("name" => "idData", "label" => "Outgoing ID", "readonly" => "readonly", "value" => $idData));
            $data["fields"][]      = form_text(array("name"=>"outgoing_no", "label"=>($this->isVendor == "YES") ? "Incoming Number" : "Outgoing Number", "mandatory"=>"yes", "readonly" => "readonly", "value"=>$data["header"]->outgoing_no));
            $data["fields"][]      = form_select(array("name"=>"project_id", "label"=>"Project Name", "mandatory"=>"yes", "source"=>$qProject, "withnull"=>"yes", "value"=>$data["header"]->project_id, "readonly" => "readonly"));
            $data["fields"][]      = form_select(array("name"=>"vendor_id", "label"=>"Vendor", "mandatory"=>"yes", "source"=>$qVendor, "withnull"=>"yes", "value"=>$data["header"]->vendor_id, "readonly" => "readonly"));
            $data["fields"][]      = form_text(array("name"=>"sender_date", "label"=>($this->isVendor == "YES") ? "Receive Date" : "Sending Date", "value"=>displayDMY($data["header"]->sender_date), "readonly" => "readonly"));
            $data["fields"][]      = form_text(array("name"=>"email_address", "label"=>"Email Address", "mandatory"=>"yes", "value"=>$data["header"]->email_address, "readonly" => "readonly"));
            $data["fields"][]      = form_text(array("name"=>"cc_email_address", "label"=>"CC Email Address", "value"=>$data["header"]->cc_email_address, "readonly" => "readonly"));
            $data["fields"][]      = form_text(array("name"=>"subject", "label"=>"Subject", "mandatory"=>"yes", "value"=>$data["header"]->subject, "readonly" => "readonly"));
            $data["fields"][]      = form_textarea(array("name"=>"content", "label"=>"Content", "mandatory"=>"yes", "value"=>$data["header"]->content, "readonly" => "readonly"));
            // $data["fields"][]       = form_file(array("label" => "Document File", "value" => $file));
            # ---------------
            $data["buttons"][]     = form_button_cancel2(array("name"=>"button_cancel", "label"=>"Cancel", "action"=>url('')."/outgoing/index"));
            # ---------------
            return view("outgoing.form_view_detail", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            # ---------------
            return view("error.405");
        }        
    }

    public function resend($id) {
        try {
            $data["title"]         = "Resend Email Outgoing Transmittal";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/outgoing/prosesresend";
            # ---------------
            $idData                = decodedData($id);
            /* ----------
             Model
            ----------------------- */
            $data["header"]        = $this->qOutgoing->getHeader($idData);
            $data["detail"]        = $this->qOutgoing->getDetail($idData);

            $qProject              = $this->qReference->getSelectProject();
            $qVendor               = $this->qReference->getSelectVendor();

            $selectDocument        = $this->qReference->getSelectDocumentOutgoing($data["header"]->vendor_id);
            $selectReturnStatus    = $this->qReference->getSelectReturnStatus();

            $file                  = (!empty($data["header"]->document_file_review)) ? asset("/uploads") . $data["header"]->document_url_review . $data["header"]->document_file_review : "";

            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name" => "id", "label" => "Outgoing ID", "readonly" => "readonly", "value" => $id));
            $data["fields"][]      = form_hidden(array("name" => "idData", "label" => "Outgoing ID", "readonly" => "readonly", "value" => $idData));
            $data["fields"][]      = form_hidden(array("name"=>"document_url_review", "label"=>"Document Url", "readonly" => "readonly", "value"=>$data["header"]->document_url_review));
            $data["fields"][]      = form_hidden(array("name"=>"document_file_review", "label"=>"Document File", "readonly" => "readonly", "value"=>$data["header"]->document_file_review));
            $data["fields"][]      = form_hidden(array("name"=>"status_email", "label"=>"Send Email", "readonly" => "readonly", "value"=>$data["header"]->status_email));
            $data["fields"][]      = form_text(array("name"=>"outgoing_no", "label"=>"Outgoing Number", "mandatory"=>"yes", "readonly" => "readonly", "value"=>$data["header"]->outgoing_no));
            $data["fields"][]      = form_select(array("name"=>"project_id", "label"=>"Project Name", "mandatory"=>"yes", "source"=>$qProject, "withnull"=>"yes", "value"=>$data["header"]->project_id, "readonly" => "readonly"));
            $data["fields"][]      = form_select(array("name"=>"vendor_id", "label"=>"Vendor", "mandatory"=>"yes", "source"=>$qVendor, "withnull"=>"yes", "value"=>$data["header"]->vendor_id, "readonly" => "readonly"));
            $data["fields"][]      = form_text(array("name"=>"sender_date", "label"=>"Sending Date", "value"=>displayDMY($data["header"]->sender_date), "readonly" => "readonly"));
            $data["fields"][]      = form_text(array("name"=>"email_address", "label"=>"Email Address", "mandatory"=>"yes", "value"=>$data["header"]->email_address, "readonly" => "readonly"));
            $data["fields"][]      = form_text(array("name"=>"cc_email_address", "label"=>"CC Email Address", "value"=>$data["header"]->cc_email_address, "readonly" => "readonly"));
            $data["fields"][]      = form_text(array("name"=>"subject", "label"=>"Subject", "mandatory"=>"yes", "value"=>$data["header"]->subject, "readonly" => "readonly"));
            $data["fields"][]      = form_textarea(array("name"=>"content", "label"=>"Content", "mandatory"=>"yes", "value"=>$data["header"]->content, "readonly" => "readonly"));
            $data["fields"][]       = form_file(array("label" => "Document File", "value" => $file));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Resend Email&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel2(array("name"=>"button_cancel", "label"=>"Cancel", "action"=>url('')."/outgoing/index"));
            # ---------------
            return view("outgoing.form_view_detail", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            # ---------------
            return view("error.405");
        }        
    }

    public function prosesresend(Request $request) {
        try {
            $response   = $this->qOutgoing->resendEmailOutgoing($request);

            if($response["status"]) {
                session()->flash("success_message", SUCCESS_MESSAGE);
            } else {
                session()->flash("error_message", FAILED_MESSAGE);
            }
            # ---------------
            return redirect("/outgoing/index");
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE ADD OUTGOING", "");
            # ---------------
            return view("error.405");
        }
    }

    public function report() {
        try {
            $data["title"]         = "Outgoing Transmittal Report";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/outgoing/report_result";
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
            $data["fields"][]      = form_datepicker(array("name"=>"sender_date_start", "label"=>"Sender Date From", "value"=>date("d/m/Y")));
            $data["fields"][]      = form_datepicker(array("name"=>"sender_date_end", "label"=>"Until Sender Date", "value"=>date("d/m/Y")));
            $data["fields"][]      = form_select(array("name"=>"type", "label"=>"Report Type", "source"=>$selectType));
            # ---------------
            $data["buttons"][] = form_button_submit(array("name" => "button_window", "label" => "&nbsp;&nbsp;Preview&nbsp;&nbsp;"));
            # ---------------
            return view("default.form-report", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE REPORT OUTGOING", "");
            # ---------------
            return view("error.405");
        }
    }

    public function report_result(Request $request) {
        $data["title"]              = "OUTGOING TRANSMITTAL REPORT";
        $data["periode"]            = date("d/m/Y");
        $params                     = base64_encode($request->sender_date_start."|".$request->sender_date_end);
        # ---------------
        if($request->type == "SUMMARY") {
            $data["url_data"]           = url('/') . "/outgoing/report_summary_json/" . $params;
            # ---------------
            $data["column_unit"]        = 2;
            $data["content_center"]     = "0,1";
            $data["content_right"]      = "";
            $data["token"]              = "";
        } else {
            $data["url_data"]           = url('/') . "/outgoing/report_detail_json/" . $params;
            # ---------------
            $data["column_unit"]        = 2;
            $data["content_center"]     = "0,1,6,8";
            $data["content_right"]      = "";
            $data["token"]              = "";
        }
        # ---------------
        return view("default.report-datatable", $data);
    }

    public function report_summary_json($params){
        $query  = $this->qOutgoing->getSummaryReport($params);
        
        return Datatables::of($query)->make(true); 
    }

    public function report_detail_json($params){
        $query  = $this->qOutgoing->getDetailReport($params);
        
        return Datatables::of($query)->make(true); 
    }
}
