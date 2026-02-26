<?php

namespace App\Http\Controllers\Migration;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use View;
use Auth;
use Validator;
use Hash;
use App\User;
use App\Model\UserManagement\MenuModel;
use App\Model\Sys\LogModel;
use App\Model\Migration\MigrationModel;
use App\Model\Project\ProjectModel;
use App\Model\Reference\ReferenceModel;
use App\Model\Reference\VendorModel;

class MigrationController extends Controller
{
    protected $PROT_SideMenu, $PROT_Parent, $PROT_ModuleId, $PROT_ModuleName;

    public function __construct(Request $request) {
        # ---------------
        $uri                      = getUrl() . "/index";
        # ---------------
        $this->qMenu              = new MenuModel;
        $this->qMigration         = new MigrationModel;
        $this->logModel           = new LogModel;
        $this->qReference         = new ReferenceModel;
        $this->qVendor            = new VendorModel;
        $this->qProject            = new ProjectModel;
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
            $data["form_act"]         = "/migration/index";
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
                                                    ,"name"=>"document_id"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"center"
                                                          ,"item-format"=>"checkbox"
                                                            ,"item-class"=>""
                                                              ,"width"=>"5%"
                                                                ,"add-style"=>""),
                                            array("label"=>"Document No"
                                                    ,"name"=>"document_no"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"center"
                                                          ,"item-format"=>"normal"
                                                            ,"item-class"=>""
                                                              ,"width"=>"15%"
                                                                ,"add-style"=>""),
                                            array("label"=>"Document Title"
                                                    ,"name"=>"document_title"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"left"
                                                          ,"item-format"=>"normal"
                                                            ,"item-class"=>""
                                                              ,"width"=>"25%"
                                                                ,"add-style"=>""),
                                            array("label"=>"Vendor"
                                                    ,"name"=>"vendor_name"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"left"
                                                          ,"item-format"=>"normal"
                                                            ,"item-class"=>""
                                                              ,"width"=>"20%"
                                                                ,"add-style"=>""),
                                            array("label"=>"Issue Status"
                                                    ,"name"=>"issue_status"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"left"
                                                          ,"item-format"=>"normal"
                                                            ,"item-class"=>""
                                                              ,"width"=>"20%"
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
            $data["query"]         = $this->qMigration->getCollections();
            $data["select"]        = $data["query"]["data"];
            $data["pagging"]       = getPagging($data["select"]);
            # ---------------
            # Advance Search
            # ---------------
            if(isset($request->module_id)) {
                $doc_no              = ($request->document_no != "") ? session(["SES_SEARCH_MIGRATION_NO" => $request->document_no]) : $request->session()->forget("SES_SEARCH_MIGRATION_NO");
                $doc_name            = ($request->document_title != "") ? session(["SES_SEARCH_MIGRATION_TITLE" => $request->document_title]) : $request->session()->forget("SES_SEARCH_MIGRATION_TITLE");
                # ---------------
                return redirect("/migration/index");
            }
            # ---------------
            if($request->session()->has("SES_SEARCH_MIGRATION_NO")) {
                array_push($data["filtered_info"], "DOCUMENT NO");
            }
            if($request->session()->has("SES_SEARCH_MIGRATION_TITLE")) {
              array_push($data["filtered_info"], "DOCUMENT TITLE");
            }
            # ---------------
            $data["adv_search"]    = true;
            $data["hide_simple_search"] = true;
            # ---------------
            $data["fields"][]      = form_hidden(array("name"=>"module_id", "label"=>"Module ID", "value"=>"DOCUMENT_MIGRATION"));
            $data["fields"][]      = form_search_text(array("name"=>"document_no", "label"=>"Document No", "value"=>($request->session()->has("SES_SEARCH_MIGRATION_NO")) ? $request->session()->get("SES_SEARCH_MIGRATION_NO") : ""));
            $data["fields"][]      = form_search_text(array("name"=>"document_title", "label"=>"Document Title", "value"=>($request->session()->has("SES_SEARCH_MIGRATION_TITLE")) ? $request->session()->get("SES_SEARCH_MIGRATION_TITLE") : ""));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_search", "label"=>"&nbsp;&nbsp;Search&nbsp;&nbsp;"));
            $data["buttons"][]     = form_action_button(array("name"=>"button_clear", "label"=>"&nbsp;&nbsp;Clear&nbsp;&nbsp;", "url"=>"/migration/unfilter"));
            # ---------------
            return view("default.list", $data);
        } catch (\Exception $e) {
            throw $e;
            $this->logModel->createError($e->getMessage(), "PAGE AREA", "");
            # ---------------
            return view("error.405");
        }
    }

    public function unfilter() {
        session()->forget("SES_SEARCH_MIGRATION_NO");
        session()->forget("SES_SEARCH_MIGRATION_TITLE");
        # ---------------
        return redirect("/migration/index");
    }

    public function upload_document()
    {
        try {
            $data["title"]         = "Upload VDRL";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/migration/temp_document";

            /* ----------
             Model
            ----------------------- */
            $selectProject         = $this->qReference->getSelectProject();
            $selectVendor          = $this->qReference->getSelectVendor();
            $selectStatus          = $this->qReference->getSelectIssueStatus();


            // $selectStatus = array(
            //   array("id" => "3", "name" => "IFA"),
            //   array("id" => "10", "name" => "IFC"),
            // );

            $template_migration  = url('') . '/uploads/template/Template Migration.xlsx';

            /* ----------
             Fields
            ----------------------- */


            $data["fields"][]      = form_select(array("name" => "project_id", "label" => "Project",  "source" => $selectProject));
            $data["fields"][]      = form_select(array("name" => "vendor_id", "label" => "Vendor",  "mandatory" => "yes", "source" => $selectVendor));
            $data["fields"][]      = form_select(array("name" => "issue_status_id", "label" => "Issue Status",  "mandatory" => "yes", "source" => $selectStatus));
            $data["fields"][]      = form_upload(array("name" => "upload_file", "label" => "File", "mandatory" => "mandatory"));
            $data["fields"][]     = "<div class=\"form-group\">
                                        <label class=\"col-md-3 control-label\">Template</label>
                                        <div class=\"col-md-9\">
                                            <a href=\" $template_migration \" class=\"btn btn-sm btn-warning m-r-5\">Download Template</a>
                                        </div>
                                    </div>";
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Save&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));

            # ---------------
            return view("default.form", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE MIGRATION", "");
            # ---------------
            return view("error.405");
        }
    }

    public function temp_document(Request $request)
    {
        $rules = array(
            'upload_file' => 'required|mimes:xls,xlsx'
        );

        $messages = [
            'upload_file.mimes' => 'File must be excel (xls,xlsx)',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect("/migration/upload")
                ->withErrors($validator)
                ->withInput();
        } else {
            $response   = $this->qMigration->createTempMigration($request);
            # ---------------
            if ($response["status"]) {
                $kode = $request->project_id . '|' . $request->vendor_id . '|' . $request->issue_status_id . '|' . Auth::user()->id . '|' . date('Ymd');
                $encrypt = base64_encode($kode);

                session()->flash("success_message", SUCCESS_MESSAGE);
                return redirect("/migration/view_temp/" . $encrypt);
            } else {
                session()->flash("error_message", FAILED_MESSAGE);
                return redirect("/migration/index");
            }
        }
    }

    public function view_temp($id)
    {
        try {
            $data["title"]         = "List Document Migration";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/migration/save_document";
            $kode = base64_decode($id);
            list($project_id, $vendor_id, $issue_status, $user_id, $date) = explode("|", $kode);

            $data['id']           = $id;
            $data['temp']         = $this->qMigration->getDocumentViewTemp($id);

            $tempReady            = $this->qMigration->getDocumentTempIsReady($id)->count();

            $data['project']      = $this->qProject->getDataById($project_id)->project_name;
            $data['vendor']       = $this->qVendor->getVendor($vendor_id)['data']->name;
            $data['issue_status'] = $this->qMigration->getIssueStatus($issue_status)->name;

            $data["fields"][]      = form_hidden(array("name" => "id", "label" => "ID", "value" => $id));
            $data["fields"][]      = form_hidden(array("name" => "vendor_id", "label" => "Vendor", "value" => $project_id));
            $data["fields"][]      = form_hidden(array("name" => "project_id", "label" => "Project", "value" => $vendor_id));
            $data["fields"][]      = form_hidden(array("name" => "issue_status_id", "label" => "Issue Status", "value" => $issue_status));

            $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));
            if ($tempReady != 0) {
                $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Upload&nbsp;&nbsp;"));
            }

            # ---------------
            return view("migration.list-temp", $data);
        } catch (\Exception $e) {
            throw $e;
            $this->logModel->createError($e->getMessage(), "PAGE MIGRATION", "");
            # ---------------
            return view("error.405");
        }
    }

    public function save_document(Request $request)
    {
        $rules = array();

        $messages = [];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect("/migration/view_temp/" . $request->id)
                ->withErrors($validator)
                ->withInput();
        } else {
            $qTemp = $this->qMigration->getDocumentTempIsReady($request->id);
            if (count($qTemp) == 0) {
                session()->flash("error_message", "The document can't be uploaded because nothing is ready");
            } else {
                $response   = $this->qMigration->createUploadDocument($request);
                # ---------------
                if ($response["status"]) {
                    session()->flash("success_message", SUCCESS_MESSAGE);
                } else {
                    session()->flash("error_message", FAILED_MESSAGE);
                }
            }

            # ---------------
            return redirect("/migration/index");
        }
    }

    public function delete($id) {
      try {
          $response   = $this->qMigration->deleteDocument($id);

          if($response["status"]) {
              session()->flash("success_message", "Successfully delete");
          } else {
              session()->flash("error_message", "Failed to updated");
          }
          # ---------------
          return redirect("/migration/index");
      } catch (\Exception $e) {
        throw $e;
          $this->logModel->createError($e->getMessage(), "PAGE DELETE MIGRATION", "");
          # ---------------
          return view("error.405");
      }
  }

}
