<?php

namespace App\Http\Controllers\Reference;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use View;
use Auth;
use Validator;
use Hash;
use App\User;
use App\Model\UserManagement\MenuModel;
use App\Model\Reference\ReferenceModel;
use App\Model\Reference\VendorModel;
use App\Model\Sys\LogModel;

class VendorController extends Controller
{
    protected $PROT_SideMenu, $PROT_Parent, $PROT_ModuleId, $PROT_ModuleName;

    public function __construct(Request $request) {
        # ---------------
        $uri                      = getUrl() . "/index";
        # ---------------
        $this->qMenu              = new MenuModel;
        $this->qReference         = new ReferenceModel;
        $this->qVendor            = new VendorModel;
        $this->logModel           = new LogModel;
        # ---------------
        $rs                       = $this->qMenu->getParentMenu($uri);
        # ---------------
        $this->PROT_Parent        = (count($rs) > 0) ? $rs[0]->parent_name : '';
        $this->PROT_ModuleName    = (count($rs) > 0) ? $rs[0]->name : '';
        $this->PROT_ModuleId      = (count($rs) > 0) ? $rs[0]->id : '';
        # ---------------
        View::share(array("SHR_Parent"=>$this->PROT_Parent, "SHR_Module"=>$this->PROT_ModuleName, "SHR_ModuleId"=>$this->PROT_ModuleId));
    }

    public function index(Request $request)
    {
        try {
            $data["title"]            = ucwords(strtolower($this->PROT_ModuleName));
            $data["parent"]           = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]         = "/vendor/index";
            $data["active_page"]      = (empty($page)) ? 1 : $page;
            $data["offset"]           = (empty($data["active_page"])) ? 0 : ($data["active_page"]-1) * Auth::user()->perpage;
            /* ----------
             Source
            ----------------------- */

            # ---------------
            $data["filtered_info"]    = array();
            # ---------------
            $data["action"]           = $this->qMenu->getActionMenu(Auth::user()->id, $this->PROT_ModuleId);
            /* ----------
             Table header
            ----------------------- */
            $data["table_header"]   = array(array("label"=>"ID"
                                                    ,"name"=>"vendor_id"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"center"
                                                          ,"item-format"=>"checkbox"
                                                            ,"item-class"=>""
                                                              ,"width"=>"5%"
                                                                ,"add-style"=>""),
                                            array("label"=>"Name"
                                                    ,"name"=>"name"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"left"
                                                          ,"item-format"=>"normal"
                                                            ,"item-class"=>""
                                                              ,"width"=>""
                                                                ,"add-style"=>""),
                                            array("label"=>"Phone Number"
                                                    ,"name"=>"phone_number"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"center"
                                                          ,"item-format"=>"normal"
                                                            ,"item-class"=>""
                                                              ,"width"=>"15%"
                                                                ,"add-style"=>""),
                                            array("label"=>"Email Address"
                                                    ,"name"=>"email_address"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"left"
                                                          ,"item-format"=>"normal"
                                                            ,"item-class"=>""
                                                              ,"width"=>"15%"
                                                                ,"add-style"=>""),
                                            array("label"=>"Status"
                                                    ,"name"=>"status_code"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"center"
                                                          ,"item-format"=>"flag"
                                                            ,"item-class"=>""
                                                              ,"width"=>"15%"
                                                                ,"add-style"=>""));
            # ---------------
            $data["query"]         = $this->qVendor->getCollections();
            $data["select"]        = $data["query"]["data"];
            $data["pagging"]       = getPagging($data["select"]);
            # ---------------
            # Advance Search
            # ---------------
            if(isset($request->module_id)) {
                $name              = ($request->name != "") ? session(["SES_SEARCH_VENDOR_NAME" => $request->name]) : $request->session()->forget("SES_SEARCH_COMPANY_NAME");
                # ---------------
                return redirect("/vendor/index");
            }
            # ---------------
            if($request->session()->has("SES_SEARCH_VENDOR_NAME")) {
                array_push($data["filtered_info"], "NAME");
            }
            # ---------------
            $data["adv_search"]    = true;
            $data["hide_simple_search"] = true;
            # ---------------
            $data["fields"][]      = form_hidden(array("name"=>"module_id", "label"=>"Module ID", "value"=>"VENDOR"));
            $data["fields"][]      = form_search_text(array("name"=>"name", "label"=>"Name", "value"=>($request->session()->has("SES_SEARCH_VENDOR_NAME")) ? $request->session()->get("SES_SEARCH_VENDOR_NAME") : ""));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_search", "label"=>"&nbsp;&nbsp;Search&nbsp;&nbsp;"));
            $data["buttons"][]     = form_action_button(array("name"=>"button_clear", "label"=>"&nbsp;&nbsp;Clear&nbsp;&nbsp;", "url"=>"/vendor/unfilter"));
            # ---------------
            return view("default.list", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE COMPANY", "");
            # ---------------
            return view("error.405");
        }
    }

    public function unfilter() {
        session()->forget("SES_SEARCH_VENDOR_NAME");
        # ---------------
        return redirect("/vendor/index");
    }

    public function add() {
        try {
            $data["title"]         = "Add Vendor";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/vendor/save";
            /* ----------
             Model
            ----------------------- */
            $selectCountry         = $this->qReference->getSelectCountry();
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_text(array("name"=>"name", "label"=>"Name", "mandatory"=>"yes", "first_selected"=>"yes"));
            $data["fields"][]      = form_text(array("name"=>"address", "label"=>"Address"));
            $data["fields"][]      = form_number(array("name"=>"phone_number", "label"=>"Phone Number"));
            $data["fields"][]      = form_number(array("name"=>"fax_number", "label"=>"Fax Number"));
            $data["fields"][]      = form_email(array("name"=>"email_address", "label"=>"Email Address"));
            $data["fields"][]      = form_text(array("name"=>"pic", "label"=>"PIC Name"));
            $data["fields"][]      = form_select(array("name"=>"country_id", "label"=>"Country", "source"=>$selectCountry, "value"=>77));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_save", "label"=>"&nbsp;&nbsp;Save&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name"=>"button_cancel", "label"=>"Cancel"));
            # ---------------
            return view("default.form", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE VENDOR", "");
            # ---------------
            return view("error.405");
        }        
    }

    public function save(Request $request) {
        try {
            $rules["name"]               = 'required|';
            $messages["name.required"]   = 'Name is required';

            if(!empty($request->email_address)) {
                $rules["email_address"]               = "required|email:rfc";
                $messages["email_address.required"]   = 'Email is required';
            }

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("/vendor/add")
                            ->withErrors($validator)
                            ->withInput();
            } else {
                $response   = $this->qVendor->createData($request);

                if($response["status"]) {
                    session()->flash("success_message", "Successfully Saved");
                } else {
                    session()->flash("error_message", "Failed to save");
                }
            }
            # ---------------
            return redirect("/vendor/index");
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE VENDOR", "");
            # ---------------
            return view("error.405");
        }
    }

    public function edit($id) {
        try {
            $data["title"]        = "Edit Vendor";
            $data["parent"]       = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]     = "/vendor/update";
            # ---------------
            $id                   = decodedData($id);
            /* ----------
             Model
            ----------------------- */
            $selectCountry         = $this->qReference->getSelectCountry();
            /* ----------
             Source
            ----------------------- */
            $qVendor               = VendorModel::find($id);
            $qStatus               = getSelectStatusUser();
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name"=>"id", "label"=>"ID", "readonly"=>"readonly", "value"=>$id));
            $data["fields"][]      = form_hidden(array("name"=>"_method", "label"=>"Method", "readonly"=>"readonly", "value"=>"PUT"));
            $data["fields"][]      = form_text(array("name"=>"name", "label"=>"Name", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$qVendor->name));
            $data["fields"][]      = form_text(array("name"=>"address", "label"=>"Address", "value"=>$qVendor->address));
            $data["fields"][]      = form_number(array("name"=>"phone_number", "label"=>"Phone Number", "value"=>$qVendor->phone_number));
            $data["fields"][]      = form_number(array("name"=>"fax_number", "label"=>"Fax Number", "value"=>$qVendor->fax_number));
            $data["fields"][]      = form_email(array("name"=>"email_address", "label"=>"Email Address", "value"=>$qVendor->email_address));
            $data["fields"][]      = form_text(array("name"=>"pic", "label"=>"PIC Name", "value"=>$qVendor->pic));
            $data["fields"][]      = form_select(array("name"=>"country_id", "label"=>"Country", "source"=>$selectCountry, "value"=>$qVendor->country_id));
            $data["fields"][]      = form_radio(array("name"=>"status", "label"=>"Status", "mandatory"=>"yes", "source"=>$qStatus, "value"=>$qVendor->status));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_save", "label"=>"Update"));
            $data["buttons"][]     = form_button_cancel(array("name"=>"button_cancel", "label"=>"Cancel"));
            # ---------------
            return view("default.form", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE VENDOR", "");
            # ---------------
            return view("error.405");
        }
    }

    public function update(Request $request) {
        try {
            $rules["name"]               = 'required|';
            $messages["name.required"]   = 'Name is required';

            if(!empty($request->email_address)) {
                $rules["email_address"]               = "required|email:rfc";
                $messages["email_address.required"]   = 'Email is required';
            }

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("/vendor/edit/" . encodedData($request->input("id")))
                            ->withErrors($validator)
                            ->withInput();
            } else {
                $response   = $this->qVendor->updateData($request);

                if($response["status"]) {
                    session()->flash("success_message", "Successfully updated");
                } else {
                    session()->flash("error_message", "Failed to updated");
                }
            }
            # ---------------
            return redirect("/vendor/index");
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE UPDATE VENDOR", "");
            # ---------------
            return view("error.405");
        }
    }
}
