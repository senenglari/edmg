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
use App\Model\Reference\DisciplineModel;
use App\Model\Sys\LogModel;

class DisciplineController extends Controller
{
    protected $PROT_SideMenu, $PROT_Parent, $PROT_ModuleId, $PROT_ModuleName;

    public function __construct(Request $request) {
        # ---------------
        $uri                      = getUrl() . "/index";
        # ---------------
        $this->qMenu              = new MenuModel;
        $this->qReference         = new ReferenceModel;
        $this->qDiscipline        = new DisciplineModel;
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
            $data["form_act"]         = "/discipline/index";
            $data["active_page"]      = (empty($page)) ? 1 : $page;
            $data["offset"]           = (empty($data["active_page"])) ? 0 : ($data["active_page"]-1) * Auth::user()->perpage;
            /* ----------
             Source
            ----------------------- */
            $selectDiscpiline         = $this->qReference->getSelectDiscipline();
            $selectDepartment         = $this->qReference->getSelectDepartment();
            # ---------------
            $data["filtered_info"]    = array();
            # ---------------
            $data["action"]           = $this->qMenu->getActionMenu(Auth::user()->id, $this->PROT_ModuleId);
            /* ----------
             Table header
            ----------------------- */
            $data["table_header"]   = array(array("label"=>"ID"
                                                    ,"name"=>"discipline_id"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"center"
                                                          ,"item-format"=>"checkbox"
                                                            ,"item-class"=>""
                                                              ,"width"=>"5%"
                                                                ,"add-style"=>""),
                                            array("label"=>"Code"
                                                    ,"name"=>"code"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"center"
                                                          ,"item-format"=>"normal"
                                                            ,"item-class"=>""
                                                              ,"width"=>"15%"
                                                                ,"add-style"=>""),
                                            array("label"=>"Name"
                                                    ,"name"=>"discipline_name"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"left"
                                                          ,"item-format"=>"normal"
                                                            ,"item-class"=>""
                                                              ,"width"=>""
                                                                ,"add-style"=>""),
                                            array("label"=>"Department Name"
                                                    ,"name"=>"department_name"
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
            $data["query"]         = $this->qDiscipline->getCollections();
            $data["select"]        = $data["query"]["data"];
            $data["pagging"]       = getPagging($data["select"]);
            # ---------------
            # Advance Search
            # ---------------
            if(isset($request->module_id)) {
                $discipline_id     = ($request->discipline_name != "") ? session(["SES_SEARCH_DISCIPLINE_NAME" => $request->discipline_name]) : $request->session()->forget("SES_SEARCH_DISCIPLINE_NAME");
                $department_id     = ($request->department_id != "") ? session(["SES_SEARCH_DEPARTMENT_ID" => $request->department_id]) : $request->session()->forget("SES_SEARCH_DEPARTMENT_ID");
                # ---------------
                return redirect("/discipline/index");
            }
            # ---------------
            if($request->session()->has("SES_SEARCH_DISCIPLINE_NAME")) {
                array_push($data["filtered_info"], "DISCIPLINE");
            }
            if($request->session()->has("SES_SEARCH_DEPARTMENT_ID")) {
                if($request->session()->get("SES_SEARCH_DEPARTMENT_ID") !== "0") {
                    array_push($data["filtered_info"], "DEPARTMENT");
                }
            }
            # ---------------
            $data["adv_search"]    = true;
            $data["hide_simple_search"] = true;
            # ---------------
            $data["fields"][]      = form_hidden(array("name"=>"module_id", "label"=>"Module ID", "value"=>"DISCIPLINE"));
            $data["fields"][]      = form_search_text(array("name"=>"discipline_name", "label"=>"Discipline", "value"=>($request->session()->has("SES_SEARCH_DISCIPLINE_NAME")) ? $request->session()->get("SES_SEARCH_DISCIPLINE_NAME") : ""));
            $data["fields"][]      = form_search_select(array("name"=>"department_id", "label"=>"Department", "source"=>$selectDepartment, "withnull"=>"yes", "value"=>($request->session()->has("SES_SEARCH_DEPARTMENT_ID")) ? $request->session()->get("SES_SEARCH_DEPARTMENT_ID") : ""));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_search", "label"=>"&nbsp;&nbsp;Search&nbsp;&nbsp;"));
            $data["buttons"][]     = form_action_button(array("name"=>"button_clear", "label"=>"&nbsp;&nbsp;Clear&nbsp;&nbsp;", "url"=>"/discipline/unfilter"));
            # ---------------
            return view("default.list", $data);
        } catch (\Exception $e) {
            throw $e;
            $this->logModel->createError($e->getMessage(), "PAGE DISCIPLINE", "");
            # ---------------
            return view("error.405");
        }
    }

    public function unfilter() {
        session()->forget("SES_SEARCH_DISCIPLINE_NAME");
        session()->forget("SES_SEARCH_DEPARTMENT_ID");
        # ---------------
        return redirect("/discipline/index");
    }

    public function add() {
        try {
            $data["title"]         = "Add Discipline";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/discipline/save";
            /* ----------
             Model
            ----------------------- */
            $selectDepartment      = $this->qReference->getSelectDepartment();
            /* ----------
             Tabs
            ----------------------- */

            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_text(array("name"=>"code", "label"=>"Code", "mandatory"=>"yes", "first_selected"=>"yes"));
            $data["fields"][]      = form_text(array("name"=>"name", "label"=>"Name", "mandatory"=>"yes"));
            $data["fields"][]      = form_select(array("name"=>"department_id", "label"=>"Department", "source"=>$selectDepartment));
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

    public function save(Request $request) {
        try {
            $response   = $this->qDiscipline->createData($request);

            if($response["status"]) {
                session()->flash("success_message", "Successfully Saved");
            } else {
                session()->flash("error_message", "Failed to save");
            }
            # ---------------
            return redirect("/discipline/index");
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE DISCIPLINE", "");
            # ---------------
            return view("error.405");
        }
    }

    public function edit($id) {
        try {
            $data["title"]        = "Edit Discipline";
            $data["parent"]       = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]     = "/discipline/update";
            # ---------------
            $id                   = decodedData($id);
            /* ----------
             Model
            ----------------------- */
            $selectDepartment      = $this->qReference->getSelectDepartment();
            /* ----------
             Source
            ----------------------- */
            $qDiscipline           = DisciplineModel::find($id);
            $qStatus               = getSelectStatusUser();
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name"=>"id", "label"=>"ID", "readonly"=>"readonly", "value"=>$id));
            $data["fields"][]      = form_hidden(array("name"=>"_method", "label"=>"Method", "readonly"=>"readonly", "value"=>"PUT"));
            $data["fields"][]      = form_text(array("name"=>"code", "label"=>"Code", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$qDiscipline->code));
            $data["fields"][]      = form_text(array("name"=>"name", "label"=>"User Name", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$qDiscipline->name));
            $data["fields"][]      = form_select(array("name"=>"department_id", "label"=>"Department", "source"=>$selectDepartment, "value"=>$qDiscipline->department_id));
            $data["fields"][]      = form_radio(array("name"=>"status", "label"=>"Status", "mandatory"=>"yes", "source"=>$qStatus, "value"=>$qDiscipline->status));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_save", "label"=>"Update"));
            $data["buttons"][]     = form_button_cancel(array("name"=>"button_cancel", "label"=>"Cancel"));
            # ---------------
            return view("default.form", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            # ---------------
            return view("error.405");
        }
    }

    public function update(Request $request) {
        try {
            $response   = $this->qDiscipline->updateData($request);

            if($response["status"]) {
                session()->flash("success_message", "Successfully updated");
            } else {
                session()->flash("error_message", "Failed to updated");
            }
            # ---------------
            return redirect("/discipline/index");
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE UPDATE DISCIPLINE", "");
            # ---------------
            return view("error.405");
        }
    }
}
