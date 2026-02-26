<?php

namespace App\Http\Controllers\Project;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Project\ProjectModel;
use DB;
use View;
use Auth;
use Validator;
use Hash;
use App\User;
use App\Model\UserManagement\MenuModel;
use App\Model\UserManagement\UserModel;
use App\Model\Reference\ReferenceModel;
use App\Model\Sys\LogModel;

class ProjectController extends Controller
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
        $this->qProject           = new ProjectModel;
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
            $data["form_act"]         = "/project/index";
            $data["active_page"]      = (empty($page)) ? 1 : $page;
            $data["offset"]           = (empty($data["active_page"])) ? 0 : ($data["active_page"] - 1) * Auth::user()->perpage;
            /* ----------
             Source
            ----------------------- */

            # ---------------
            $data["filtered_info"]  = array();

            $qCompany                = $this->qReference->getSelectCompany();
            # ---------------
            $data["action"]         = $this->qMenu->getActionMenu(Auth::user()->id, $this->PROT_ModuleId);
            /* ----------
             Table header
            ----------------------- */
            $data["table_header"]   = array(
                array(
                    "label" => "ID", "name" => "project_id", "align" => "center", "item-align" => "center", "item-format" => "checkbox", "item-class" => "", "width" => "5%", "add-style" => ""
                ),
                array(
                    "label" => "Company", "name" => "company_name", "align" => "center", "item-align" => "center", "item-format" => "normal", "item-class" => "", "width" => "18%", "add-style" => ""
                ),
                array(
                    "label" => "Project Code", "name" => "project_code", "align" => "center", "item-align" => "center", "item-format" => "normal", "item-class" => "", "width" => "12%", "add-style" => ""
                ),
                array(
                    "label" => "Project Name", "name" => "project_name", "align" => "center", "item-align" => "left", "item-format" => "normal", "item-class" => "", "width" => "18%", "add-style" => ""
                ),
                array(
                    "label" => "Contract No", "name" => "contract_no", "align" => "center", "item-align" => "center", "item-format" => "normal", "item-class" => "", "width" => "15%", "add-style" => ""
                ),
                array(
                    "label" => "Start Date", "name" => "st_date", "align" => "center", "item-align" => "center", "item-format" => "normal", "item-class" => "", "width" => "12%", "add-style" => ""
                ),
                array(
                    "label" => "End Date", "name" => "ed_date", "align" => "center", "item-align" => "center", "item-format" => "normal", "item-class" => "", "width" => "12%", "add-style" => ""
                ),
                array(
                    "label" => "Status", "name" => "status_code", "align" => "center", "item-align" => "center", "item-format" => "flag", "item-class" => "", "width" => "8%", "add-style" => ""
                )
            );
            # ---------------
            $data["query"]         = $this->qProject->getCollections();
            $data["select"]        = $data["query"]["data"];
            $data["pagging"]       = getPagging($data["select"]);
            # ---------------
            # Advance Search
            # ---------------
            if (isset($request->module_id)) {
                $company_id        = ($request->company_id != "") ? session(["SES_SEARCH_PROJECT_COMPANY" => $request->company_id]) : $request->session()->forget("SES_SEARCH_PROJECT_COMPANY");
                $project_code      = ($request->project_code  != "") ? session(["SES_SEARCH_PROJECT_CODE" => $request->project_code]) : $request->session()->forget("SES_SEARCH_PROJECT_CODE");
                $project_name      = ($request->project_name  != "") ? session(["SES_SEARCH_PROJECT_NAME" => $request->project_name]) : $request->session()->forget("SES_SEARCH_PROJECT_NAME");
                $contract_no       = ($request->contract_no  != "") ? session(["SES_SEARCH_PROJECT_CONTRACT_NO" => $request->contract_no]) : $request->session()->forget("SES_SEARCH_PROJECT_CONTRACT_NO");
                $end_date          = ($request->end_date  != "") ? session(["SES_SEARCH_PROJECT_END_DATE" => $request->subject]) : $request->session()->forget("SES_SEARCH_PROJECT_END_DATE");
                # ---------------
                return redirect("/project/index");
            }
            # ---------------

            if ($request->session()->has("SES_SEARCH_PROJECT_COMPANY")) {
                if ($request->session()->get("SES_SEARCH_PROJECT_COMPANY") != "0") {
                    array_push($data["filtered_info"], "COMPANY");
                }
            }

            if ($request->session()->has("SES_SEARCH_PROJECT_CODE")) {
                array_push($data["filtered_info"], "PROJECT CODE");
            }
            # ---------------
            if ($request->session()->has("SES_SEARCH_PROJECT_NAME")) {
                array_push($data["filtered_info"], "PROJECT NAME");
            }
            # ---------------
            if ($request->session()->has("SES_SEARCH_PROJECT_CONTRACT_NO")) {
                array_push($data["filtered_info"], "CONTRACT NO");
            }

            if ($request->session()->has("SES_SEARCH_PROJECT_END_DATE")) {
                array_push($data["filtered_info"], "END DATE");
            }


            # ---------------
            $data["adv_search"]    = true;
            $data["hide_simple_search"] = true;
            # ---------------
            $data["fields"][]      = form_hidden(array("name" => "module_id", "label" => "Module ID", "value" => "PROJECT"));
            $data["fields"][]      = form_search_select(array("name" => "company_id", "label" => "Company", "source" => $qCompany,  "withnull" => "yes", "value" => ($request->session()->has("SES_SEARCH_PROJECT_COMPANY")) ? $request->session()->get("SES_SEARCH_PROJECT_COMPANY") : ""));
            $data["fields"][]      = form_search_text(array("name" => "project_code", "label" => "Project Code", "value" => ($request->session()->has("SES_SEARCH_PROJECT_CODE")) ? $request->session()->get("SES_SEARCH_PROJECT_CODE") : ""));
            $data["fields"][]      = form_search_text(array("name" => "project_name", "label" => "Project Name", "value" => ($request->session()->has("SES_SEARCH_PROJECT_NAME")) ? $request->session()->get("SES_SEARCH_PROJECT_NAME") : ""));
            $data["fields"][]      = form_search_text(array("name" => "contract_no", "label" => "Contract No", "value" => ($request->session()->has("SES_SEARCH_PROJECT_CONTRACT_NO")) ? $request->session()->get("SES_SEARCH_PROJECT_CONTRACT_NO") : ""));
            $data["fields"][]      = form_search_datepicker(array("name" => "end_date", "label" => "End Date", "value" => ($request->session()->has("SES_SEARCH_PROJECT_END_DATE")) ? $request->session()->get("SES_SEARCH_PROJECT_END_DATE") : ""));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_search", "label" => "&nbsp;&nbsp;Search&nbsp;&nbsp;"));
            $data["buttons"][]     = form_action_button(array("name" => "button_clear", "label" => "&nbsp;&nbsp;Clear&nbsp;&nbsp;", "url" => "/project/unfilter"));
            # ---------------
            return view("default.list", $data);
        } catch (\Exception $e) {
            throw $e;
            $this->logModel->createError($e->getMessage(), "PAGE LIST PROJECT", "");
            # ---------------
            return view("error.405");
        }
    }

    public function unfilter()
    {
        session()->forget("SES_SEARCH_PROJECT_COMPANY");
        session()->forget("SES_SEARCH_PROJECT_CODE");
        session()->forget("SES_SEARCH_PROJECT_NAME");
        session()->forget("SES_SEARCH_PROJECT_CONTRACT_NO");
        session()->forget("SES_SEARCH_PROJECT_END_DATE");
        # ---------------
        return redirect("/project/index");
    }

    public function add()
    {
        try {
            $data["title"]         = "Add Project";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/project/save";
            /* ----------
             Model
            ----------------------- */
            $selectCompany        = $this->qReference->getSelectCompany();
            $selectVendor         = $this->qReference->getSelectVendor();
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]       = form_select(array("name" => "company_id", "label" => "Company", "mandatory" => "yes", "source" => $selectCompany));
            $data["fields"][]       = form_text(array("name" => "project_code", "label" => "Project Code", "mandatory" => "yes", "first_selected" => "yes"));
            $data["fields"][]       = form_text(array("name" => "project_name", "label" => "Project Name", "mandatory" => "yes"));
            $data["fields"][]       = form_text(array("name" => "contract_no", "label" => "Contract No", "mandatory" => "yes"));
            $data["fields"][]       = form_select(array("name" => "vendor_id", "label" => "Vendor", "mandatory" => "yes", "source" => $selectVendor));
            $data["fields"][]       = form_datepicker(array("name" => "start_date", "label" => "Start Date", "mandatory" => "yes", "value" => date("d/m/Y")));
            $data["fields"][]       = form_datepicker(array("name" => "end_date", "label" => "End Date", "mandatory" => "yes", "value" => date("d/m/Y")));
            $data["fields"][]       = form_textarea(array("name" => "description", "label" => "Description"));


            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Save&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));

            # ---------------
            return view("default.form", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE FORM ADD PROJECT", "");
            # ---------------
            return view("error.405");
        }
    }

    public function save(Request $request)
    {
        try {
            $rules = array(
                'company_id' => 'required|',
                'project_code' => 'required|',
                'project_name' => 'required|',
                'contract_no' => 'required|',
                'start_date' => 'required|',
                'end_date' => 'required|',
            );

            $messages = [
                'company_id.required' => 'Company is required',
                'project_code.required' => 'Project code is required',
                'project_name.required' => 'Project name is required',
                'contract_no.required' => 'Contract no is required',
                'start_date.required' => 'Start date is required',
                'end_date.required' => 'End date is required',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("/project/add")
                    ->withErrors($validator)
                    ->withInput();
            } else {
                $response   = $this->qProject->saveProject($request);

                if ($response["status"]) {
                    session()->flash("success_message", SUCCESS_MESSAGE);
                } else {
                    session()->flash("error_message", FAILED_MESSAGE);
                }
                # ---------------
                return redirect("/project/index");
            }
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE SAVE PROJECT", "");
            # ---------------
            return view("error.405");
        }
    }

    public function edit($id)
    {
        try {
            $data["title"]         = "Edit Project";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/project/update";
            /* ----------
             Model
            ----------------------- */
            $selectCompany        = $this->qReference->getSelectCompany();
            $selectVendor         = $this->qReference->getSelectVendor();
            $selectStatus         = getSelectStatusActiveInactive();
            $code                 = decodedData($id);
            $qData                = $this->qProject->getDataById($code);
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name" => "id", "label" => "ID", "value" => $id));
            $data["fields"][]      = form_hidden(array("name" => "project_id", "label" => "Project ID", "mandatory" => "yes", "value" => $code));
            $data["fields"][]      = form_select(array("name" => "company_id", "label" => "Company", "mandatory" => "yes", "source" => $selectCompany, "value" => $qData->company_id));
            $data["fields"][]      = form_text(array("name" => "project_code", "label" => "Project Code", "mandatory" => "yes", "first_selected" => "yes", "value" => $qData->project_code));
            $data["fields"][]      = form_text(array("name" => "project_name", "label" => "Project Name", "mandatory" => "yes", "value" => $qData->project_name));
            $data["fields"][]      = form_text(array("name" => "contract_no", "label" => "Contract No", "mandatory" => "yes", "value" => $qData->contract_no));
            $data["fields"][]      = form_select(array("name" => "vendor_id", "label" => "Vendor", "mandatory" => "yes", "source" => $selectVendor, "value" => $qData->vendor_id));
            $data["fields"][]      = form_datepicker(array("name" => "start_date", "label" => "Start Date", "mandatory" => "yes", "value" => displayDMY($qData->start_date)));
            $data["fields"][]      = form_datepicker(array("name" => "end_date", "label" => "End Date", "mandatory" => "yes", "value" => displayDMY($qData->end_date)));
            $data["fields"][]      = form_textarea(array("name" => "description", "label" => "Description", "value" => $qData->project_description));
            $data["fields"][]      = form_radio(array("name" => "status", "label" => "Status", "value" => $qData->status, "mandatory" => "yes", "source" => $selectStatus));


            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Update&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));

            # ---------------
            return view("default.form", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE FORM EDIT PROJECT", "");
            # ---------------
            return view("error.405");
        }
    }

    public function update(Request $request)
    {
        try {
            $rules = array(
                'company_id' => 'required|',
                'project_code' => 'required|',
                'project_name' => 'required|',
                'contract_no' => 'required|',
                'start_date' => 'required|',
                'end_date' => 'required|',
            );

            $messages = [
                'company_id.required' => 'Company is required',
                'project_code.required' => 'Project code is required',
                'project_name.required' => 'Project name is required',
                'contract_no.required' => 'Contract no is required',
                'start_date.required' => 'Start date is required',
                'end_date.required' => 'End date is required',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("/project/edit/" . $request->id)
                    ->withErrors($validator)
                    ->withInput();
            } else {
                $response   = $this->qProject->updateProject($request);

                if ($response["status"]) {
                    session()->flash("success_message", SUCCESS_MESSAGE);
                } else {
                    session()->flash("error_message", FAILED_MESSAGE);
                }
                # ---------------
                return redirect("/project/index");
            }
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE UPDATE PROJECT", "");
            # ---------------
            return view("error.405");
        }
    }

    public function delete($id)
    {
        try {
            $data["title"]         = "Delete Project";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/project/remove";
            /* ----------
             Model
            ----------------------- */
            $code                 = decodedData($id);
            $qData                = $this->qProject->getDataById($code);
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name" => "id", "label" => "ID", "value" => $id));
            $data["fields"][]      = form_hidden(array("name" => "project_id", "label" => "Project ID", "mandatory" => "yes", "value" => $code));
            $data["fields"][]      = form_text(array("name" => "company_name", "label" => "Company", "mandatory" => "yes", "value" => $qData->company_name, "readonly" => "readonly"));
            $data["fields"][]      = form_text(array("name" => "project_code", "label" => "Project Code", "mandatory" => "yes", "value" => $qData->project_code, "readonly" => "readonly"));
            $data["fields"][]      = form_text(array("name" => "project_name", "label" => "Project Name", "mandatory" => "yes", "value" => $qData->project_name, "readonly" => "readonly"));

            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Delete&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));

            # ---------------
            return view("default.form", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE FORM DELETE PROJECT", "");
            # ---------------
            return view("error.405");
        }
    }

    public function remove(Request $request)
    {
        try {
            $rules = array(
                'company_name' => 'required|',
                'project_code' => 'required|',
                'project_name' => 'required|',
            );

            $messages = [
                'company_name.required' => 'Company is required',
                'project_code.required' => 'Project code is required',
                'project_name.required' => 'Project name is required',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("/project/delete/" . $request->id)
                    ->withErrors($validator)
                    ->withInput();
            } else {
                $response   = $this->qProject->removeProject($request);

                if ($response["status"]) {
                    session()->flash("success_message", SUCCESS_MESSAGE);
                } else {
                    session()->flash("error_message", FAILED_MESSAGE);
                }
                # ---------------
                return redirect("/project/index");
            }
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE REMOVE PROJECT", "");
            # ---------------
            return view("error.405");
        }
    }
}
