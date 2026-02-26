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
use App\Model\Reference\CountryModel;
use App\Model\Sys\LogModel;

class CountryController extends Controller
{
    protected $PROT_SideMenu, $PROT_Parent, $PROT_ModuleId, $PROT_ModuleName;

    public function __construct(Request $request) {
        # ---------------
        $uri                      = getUrl() . "/index";
        # ---------------
        $this->qMenu              = new MenuModel;
        $this->qCountry           = new CountryModel;
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
            $data["form_act"]         = "/country/index";
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
                                                    ,"name"=>"country_id"
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
                                                              ,"width"=>"40%"
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
            $data["query"]         = $this->qCountry->getCollections();
            $data["select"]        = $data["query"]["data"];
            $data["pagging"]       = getPagging($data["select"]);
            # ---------------
            # Advance Search
            # ---------------
            if(isset($request->module_id)) {
                $name              = ($request->name != "") ? session(["SES_SEARCH_COUNTRY_NAME" => $request->name]) : $request->session()->forget("SES_SEARCH_COUNTRY_NAME");
                # ---------------
                return redirect("/country/index");
            }
            # ---------------
            if($request->session()->has("SES_SEARCH_COUNTRY_NAME")) {
                array_push($data["filtered_info"], "NAME");
            }
            # ---------------
            $data["adv_search"]    = true;
            $data["hide_simple_search"] = true;
            # ---------------
            $data["fields"][]      = form_hidden(array("name"=>"module_id", "label"=>"Module ID", "value"=>"COUNTRY"));
            $data["fields"][]      = form_search_text(array("name"=>"name", "label"=>"Name", "value"=>($request->session()->has("SES_SEARCH_COUNTRY_NAME")) ? $request->session()->get("SES_SEARCH_COUNTRY_NAME") : ""));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_search", "label"=>"&nbsp;&nbsp;Search&nbsp;&nbsp;"));
            $data["buttons"][]     = form_action_button(array("name"=>"button_clear", "label"=>"&nbsp;&nbsp;Clear&nbsp;&nbsp;", "url"=>"/country/unfilter"));
            # ---------------
            return view("default.list", $data);
        } catch (\Exception $e) {
            throw $e;
            $this->logModel->createError($e->getMessage(), "PAGE COUNTRY", "");
            # ---------------
            return view("error.405");
        }
    }

    public function unfilter() {
        session()->forget("SES_SEARCH_DEPARTMENT_NAME");
        # ---------------
        return redirect("/country/index");
    }

    public function add() {
        try {
            $data["title"]         = "Add Country";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/country/save";
            /* ----------
             Model
            ----------------------- */

            /* ----------
             Tabs
            ----------------------- */

            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_text(array("name"=>"name", "label"=>"Name", "mandatory"=>"yes", "first_selected"=>"yes"));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_save", "label"=>"&nbsp;&nbsp;Save&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name"=>"button_cancel", "label"=>"Cancel"));
            # ---------------
            return view("default.form", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE COUNTRY", "");
            # ---------------
            return view("error.405");
        }        
    }

    public function save(Request $request) {
        try {
            $response   = $this->qCountry->createData($request);

            if($response["status"]) {
                session()->flash("success_message", "Successfully Saved");
            } else {
                session()->flash("error_message", "Failed to save");
            }
            # ---------------
            return redirect("/country/index");
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE COUNTRY", "");
            # ---------------
            return view("error.405");
        }
    }

    public function edit($id) {
        try {
            $data["title"]        = "Edit Country";
            $data["parent"]       = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]     = "/country/update";
            # ---------------
            $id                   = decodedData($id);
            /* ----------
             Model
            ----------------------- */

            /* ----------
             Source
            ----------------------- */
            $qCountry              = CountryModel::find($id);
            $qStatus               = getSelectStatusUser();
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name"=>"id", "label"=>"ID", "readonly"=>"readonly", "value"=>$id));
            $data["fields"][]      = form_hidden(array("name"=>"_method", "label"=>"Method", "readonly"=>"readonly", "value"=>"PUT"));
            $data["fields"][]      = form_text(array("name"=>"name", "label"=>"Name", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$qCountry->name));
            $data["fields"][]      = form_radio(array("name"=>"status", "label"=>"Status", "mandatory"=>"yes", "source"=>$qStatus, "value"=>$qCountry->status));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_save", "label"=>"Update"));
            $data["buttons"][]     = form_button_cancel(array("name"=>"button_cancel", "label"=>"Cancel"));
            # ---------------
            return view("default.form", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE COUNTRY", "");
            # ---------------
            return view("error.405");
        }
    }

    public function update(Request $request) {
        try {
            $response   = $this->qCountry->updateData($request);

            if($response["status"]) {
                session()->flash("success_message", "Successfully updated");
            } else {
                session()->flash("error_message", "Failed to updated");
            }
            # ---------------
            return redirect("/country/index");
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE UPDATE COUNTRY", "");
            # ---------------
            return view("error.405");
        }
    }
}
