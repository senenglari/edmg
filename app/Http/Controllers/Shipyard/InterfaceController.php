<?php

namespace App\Http\Controllers\Shipyard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use View;
use Auth;
use Validator;
use Hash;
use App\User;
use App\Model\Sys\SysModel;
use App\Model\UserManagement\MenuModel;
use App\Model\Shipyard\InterfaceModel;
use App\Model\Reference\ReferenceModel;

use App\Model\Sys\LogModel;

class InterfaceController extends Controller
{
    protected $PROT_SideMenu, $PROT_Parent, $PROT_ModuleId, $PROT_ModuleName;

    public function __construct(Request $request)
    {
        # ---------------
        $uri                      = getUrl() . "/index";
        # ---------------
        $this->qMenu              = new MenuModel;
        $this->qInterface         = new InterfaceModel;
        $this->qReference         = new ReferenceModel;
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
            $data["form_act"]         = "/interface_data/index";
            $data["active_page"]      = (empty($page)) ? 1 : $page;
            $data["offset"]           = (empty($data["active_page"])) ? 0 : ($data["active_page"] - 1) * Auth::user()->perpage;
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
            $data["table_header"]   = array(
                array(
                    "label" => "ID", "name" => "interface_data_id", "status" => "status", "align" => "center", "item-align" => "center", "item-format" => "checkbox", "item-class" => "", "width" => "5%", "add-style" => ""
                ),
                array(
                    "label" => "Folder Name", "name" => "folder_name", "status" => "status", "align" => "center", "item-align" => "left", "item-format" => "normal", "item-class" => "", "width" => "", "add-style" => ""
                )
            );
            # ---------------
            $data["query"]         = $this->qInterface->getCollections();
            $data["select"]        = $data["query"]["data"];
            $data["pagging"]       = getPagging($data["select"]);
            # ---------------
            # Advance Search
            # ---------------
            if (isset($request->module_id)) {
                $folder_name              = ($request->folder_name != "") ? session(["SES_SEARCH_INTERFACE_FOLDER_NAME" => $request->folder_name]) : $request->session()->forget("SES_SEARCH_INTERFACE_FOLDER_NAME");
                # ---------------
                return redirect("/interface_data/index");
            }
            # ---------------
            if ($request->session()->has("SES_SEARCH_INTERFACE_FOLDER_NAME")) {
                array_push($data["filtered_info"], "NAME");
            }
            # ---------------
            $data["adv_search"]    = true;
            $data["hide_simple_search"] = true;
            # ---------------
            $data["fields"][]      = form_hidden(array("name" => "module_id", "label" => "Module ID", "value" => "INTERFACE"));
            $data["fields"][]      = form_search_text(array("name" => "folder_name", "label" => "Folder Name", "value" => ($request->session()->has("SES_SEARCH_INTERFACE_FOLDER_NAME")) ? $request->session()->get("SES_SEARCH_INTERFACE_FOLDER_NAME") : ""));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_search", "label" => "&nbsp;&nbsp;Search&nbsp;&nbsp;"));
            $data["buttons"][]     = form_action_button(array("name" => "button_clear", "label" => "&nbsp;&nbsp;Clear&nbsp;&nbsp;", "url" => "/interface_data/unfilter"));
            # ---------------
            return view("default.list-interface", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE INTERFACE", "");
            # ---------------
            return view("error.405");
        }
    }

    public function unfilter()
    {
        session()->forget("SES_SEARCH_INTERFACE_FOLDER_NAME");
        # ---------------
        return redirect("/interface_data/index");
    }

    public function add()
    {
        try {
            $data["title"]         = "Add New Folder";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/interface_data/save";
            /* ----------
             Model
            ----------------------- */
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_text(array("name" => "folder_name", "label" => "Folder Name", "mandatory" => "yes", "first_selected" => "yes"));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Save&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));
            # ---------------
            return view("default.form", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE INTERFACE", "");
            # ---------------
            return view("error.405");
        }
    }

    public function save(Request $request)
    {
        try {
            $rules["folder_name"]               = 'required|';
            $messages["folder_name.required"]   = 'Folder Name is required';

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("/interface_data/add")
                    ->withErrors($validator)
                    ->withInput();
            } else {
                $response   = $this->qInterface->createData($request);

                if ($response["status"]) {
                    session()->flash("success_message", "Successfully Saved");
                } else {
                    session()->flash("error_message", "Failed to save");
                }
            }
            # ---------------
            return redirect("/interface_data/index");
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE INTERFACE", "");
            # ---------------
            return view("error.405");
        }
    }

    // public function upload($id) {
    //     try {
    //         $data["title"]         = "Upload Document";
    //         $data["form_act"]      = "/interface_data/attach_item";
    //         /* ----------
    //          Model
    //         ----------------------- */
    //         $id                   = decodedData($id);
    //          /* ----------
    //          Source
    //         ----------------------- */
    //         $qInterface               = InterfaceModel::find($id);
    //         $selectIssueStatus        = $this->qReference->getSelectIssueStatus();
    //         $selectDocumentStatus     = [];
    //         $data["items"]            = $this->qInterface->getItemInterface($id);
    //         /* ----------
    //          Fields
    //         ----------------------- */
    //         if($qInterface->status=="2") {
    //             session()->flash("error_message", "Data Cannot Be Processed");
    //             return redirect("/interface_data/index");
    //         }else{
    //         $data["fields"][]      = form_hidden(array("name"=>"id", "label"=>"ID", "readonly"=>"readonly", "value"=>$id));
    //         $data["fields"][]      = form_hidden(array("name"=>"_method", "label"=>"Method", "readonly"=>"readonly", "value"=>"PUT"));
    //         $data["fields"][]      = form_text(array("name"=>"folder_name", "label"=>"Folder Name", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$qInterface->folder_name));

    //         /* ----------
    //          Modal Fields
    //         ----------------------- */
    //         $data["fields_modal"][]= form_hidden(array("name"=>"id", "label"=>"ID", "readonly"=>"readonly", "value"=>$id));
    //         $data["fields_modal"][]= form_hidden(array("name"=>"folder_name", "label"=>"Folder Name", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$qInterface->folder_name));
    //         $data["fields_modal"][]= form_hidden(array("name"=>"interface_data_id", "label"=>"ID", "readonly"=>"readonly", "value"=>$id));
    //         $data["fields_modal"][]= form_upload(array("name"=>"document_file", "label"=>"Document"));
    //         $data["fields_modal"][]= form_text(array("name"=>"document_no", "label"=>"Document Number"));
    //         $data["fields_modal"][]= form_text(array("name"=>"document_title", "label"=>"Document Name"));
    //         $data["fields_modal"][]= form_select(array("name"=>"issue_status_id", "label"=>"Issue Status", "withnull"=>"yes", "source"=>$selectIssueStatus, "value"=>0));
    //         $data["fields_modal"][]= form_select(array("name"=>"document_status_id", "label"=>"Revision Number", "withnull"=>"yes", "source"=>$selectDocumentStatus));
    //         # ---------------
    //         $data["buttons"][]     = form_button_cancel(array("name"=>"button_cancel", "label"=>"Cancel"));
    //         # ---------------
    //         $data["attach_url"]    = "/interface_data/attach_item";
    //         $data["delete_url"]    = "/interface_data/delete_item";
    //         # ---------------
    //         return view("interface.form-upload", $data);
    //         }
    //     } catch (\Exception $e) {
    //         $this->logModel->createError($e->getMessage(), "PAGE USER", "");
    //         throw $e;
    //         # ---------------
    //         return view("error.405");
    //     }        
    // }

    public function attach_item(Request $request)
    {
        try {
            $id = base64_encode($request->interface_data_id . '|' . $request->interface_data_subfolder_id);

            $dataConfig     = $this->sysModel->getConfig();
            $extention      = $dataConfig->attachment_extention;
            $max_size       = $dataConfig->attachment_max_size;
            $documentno     = $this->qInterface->cekdocument_no($request->document_no);

            if ($max_size > 0) {
                if (!empty($extention)) {
                    $validate_message   = "Attachment extention must $extention & maximum size " . number_format($max_size, 0) . " kb";
                    $rules              = array(
                        "document_file" => "required|mimes:$extention|max:$max_size",
                    );
                } else {
                    $validate_message   = "Attachment maximum size " . number_format($max_size, 0) . " kb";
                    $rules = array(
                        "document_file" => "required|max:$max_size",
                    );
                }
            } else {
                if (!empty($extention)) {
                    $validate_message   = "Attachment extention must $extention";
                    $rules              = array(
                        "document_file" => "required|mimes:$extention",
                    );
                } else {
                    $validate_message   = "Attachment is required kb";
                    $rules = array(
                        "document_file" => "required",
                    );
                }
            }

            $messages = [];

            $validator = Validator::make($request->all(), $rules, $messages);

            if (count($documentno) != 0) {
                $response   = [
                    "status" => ERROR_STATUS_CODE,
                    "message" => "Document number already exists",
                    "data" => "Document number already exists",
                ];
            } else {
                if ($validator->fails()) {
                    $response   = [
                        "status" => ERROR_STATUS_CODE,
                        "message" => $validate_message,
                        "data" => [],
                    ];
                } else {

                    $true_status    = "T";

                    if ($true_status == "T") {
                        $response   = $this->qInterface->attachItem($request);
                        if ($response["status"]) {
                            $items  = $this->qInterface->getItemSubfolder($id);
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
            }
            # ---------------
        } catch (\Exception $e) {
            throw $e;
            $response   = [
                "status" => ERROR_STATUS_CODE,
                "message" => "Error !!!",
                "data" => [],
            ];
        }

        return response()->json($response, GLOBAL_SUCCESS_RESPONSE);
    }

    public function delete_item($detail_id, $id)
    {
        $response   = $this->qInterface->deleteItem($detail_id);
        if ($response["status"]) {
            $items  = $this->qInterface->getItemInterface($id);
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

    public function delete_subfolder($detail_id, $id)
    {
        $filesubfolder   = $this->qInterface->getDataItemSubFolder($detail_id);
        if (count($filesubfolder) > 0) {
            $response   = [
                "status" => ERROR_STATUS_CODE,
            ];
        } else {
            $response   = $this->qInterface->deleteSub($detail_id);
            if ($response["status"]) {
                // $items  = $this->qInterface->getSubfolder($id);
                $response   = [
                    "status" => SUCCESS_STATUS_CODE,
                    // "data" => $items
                ];
            } else {
                $response   = [
                    "status" => ERROR_STATUS_CODE,
                    // "data" => [],
                ];
            }
        }

        return response()->json($response, GLOBAL_SUCCESS_RESPONSE);
    }

    public function approve($id)
    {
        try {
            $data["title"]         = "Upload Document";
            $data["form_act"]      = "/interface_data/save_approve";
            /* ----------
                 Model
                ----------------------- */
            $id                   = decodedData($id);
            /* ----------
                 Source
                ----------------------- */
            $qInterface               = InterfaceModel::find($id);
            $selectIssueStatus        = $this->qReference->getSelectIssueStatus();
            $selectDocumentStatus     = [];
            $data["items"]            = $this->qInterface->getItemInterface($id);
            $qSelectStatus             = getSelectStatusInterface();
            /* ----------
                 Fields
                ----------------------- */
            if ($qInterface->status == "2") {
                session()->flash("error_message", "Data Has Been Approved");
                return redirect("/interface_data/index");
            } else {
                $data["fields"][]      = form_hidden(array("name" => "id", "label" => "ID", "readonly" => "readonly", "value" => $id));
                $data["fields"][]      = form_hidden(array("name" => "_method", "label" => "Method", "readonly" => "readonly", "value" => "POST"));
                $data["fields"][]      = form_text(array("name" => "folder_name", "label" => "Folder Name", "mandatory" => "yes", "readonly" => "readonly", "value" => $qInterface->folder_name));
                $data["fields"][]      = form_select(array("name" => "status", "label" => "Status", "mandatory" => "yes", "source" => $qSelectStatus));
                # ---------------
                $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Save&nbsp;&nbsp;"));
                $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));
                # ---------------
                return view("interface.form-approve", $data);
            }
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            throw $e;
            # ---------------
            return view("error.405");
        }
    }

    public function save_approve(Request $request)
    {
        try {
            $rules["folder_name"]               = 'required|';
            $messages["folder_name.required"]   = 'Folder Name is required';

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("/interface_data/approve" . decodedData($request->id))
                    ->withErrors($validator)
                    ->withInput();
            } else {
                $response   = $this->qInterface->approveData($request);

                if ($response["status"]) {
                    session()->flash("success_message", "Successfully Approve");
                } else {
                    session()->flash("error_message", "Failed to approve");
                }
            }
            # ---------------
            return redirect("/interface_data/index");
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE INTERFACE", "");
            # ---------------
            return view("error.405");
        }
    }

    public function detail($id)
    {
        try {
            $data["form_act"]      = "/interface_data/detail";
            /* ----------
                 Model
                ----------------------- */
            $id                   = decodedData($id);
            $data["idfolder"]     = $id;
            /* ----------
                 Source
                ----------------------- */
            $qInterface               = InterfaceModel::find($id);
            $data["title"]            = $qInterface->folder_name;
            $selectIssueStatus        = $this->qReference->getSelectIssueStatus();
            $selectDocumentStatus     = [];
            $qInterface               = InterfaceModel::find($id);
            $data["items"]            = $this->qInterface->getSubfolder($id);
            $data["title"]            = $qInterface->folder_name;
            $data["form_act_search"]  = url("/") . "/interface_data/get_subfolder_search/" . $id;
            $data["subfolder"]        = $this->qInterface->getDataFolder($id);
            $data["callback"]         = url("/") . "/interface_data/add_subfolder/" . $id;
            $data["detail"]           = url("/") . "/interface_data/detail_subfolder/";
            // $data["items"]            = $this->qInterface->getDetailItemSubFolder($id);
            // $data["form_act_search"]  = url("/") . "/interface_data/get_subfolder_search_detail/".$id;
            // $data["detail"]           = url("/") . "/interface_data/detail/";
            $data["upload_subfolder"] = url("/") . "/interface_data/upload/";
            // dd($data["items"]);
            /* ----------
                 Fields
                ----------------------- */
            $data["fields"][]      = form_hidden(array("name" => "id", "label" => "ID", "readonly" => "readonly", "value" => $id));
            $data["fields"][]      = form_hidden(array("name" => "_method", "label" => "Method", "readonly" => "readonly", "value" => "POST"));
            $data["fields"][]      = form_text(array("name" => "folder_name", "label" => "Folder Name", "mandatory" => "yes", "readonly" => "readonly", "value" => $qInterface->folder_name));
            # ---------------
            $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));
            # ---------------
            return view("interface.form-detail", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            throw $e;
            # ---------------
            return view("error.405");
        }
    }

    public function edit($id)
    {
        try {
            $data["form_act"]         = "/interface_data/attach_item";
            $data["callback"]         = url("/") . "/interface_data/add_subfolder/" . $id;
            $data["detail"]           = url("/") . "/interface_data/detail_subfolder/";
            $data["upload_subfolder"] = url("/") . "/interface_data/upload_subfolder/";
            $data["delete"]           = "/interface_data/delete_subfolder";
            $data["delete_url"]    = "/interface_data/delete_item";

            /* ----------
                 Model
                ----------------------- */
            $id                   = decodedData($id);
            $data["idfolder"]     = $id;

            /* ----------
                 Source
                ----------------------- */
            $qInterface               = InterfaceModel::find($id);
            $data["items"]            = $this->qInterface->getSubfolder($id);
            // $data["fileSubFolder"]    = $this->qInterface->getDataItemSubFolder($id);
            // dd($data["items"]);
            $data["title"]            = $qInterface->folder_name;
            $data["form_act_search"]  = url("/") . "/interface_data/get_subfolder_search/" . $id;
            $data["form_act_detail"]  = url("/") . "/interface_data/get_subfolder_search/" . $id;


            // dd($data["items"]);

            /* ----------
                 Fields
                ----------------------- */
            if ($qInterface->status == 1) {
                $data["fields"][]      = form_hidden(array("name" => "id", "label" => "ID", "readonly" => "readonly", "value" => $id));
                $data["fields"][]      = form_hidden(array("name" => "_method", "label" => "Method", "readonly" => "readonly", "value" => "PUT"));
                $data["fields"][]      = form_text(array("name" => "folder_name", "label" => "Folder Name", "mandatory" => "yes", "readonly" => "readonly", "value" => $qInterface->folder_name));
                # ---------------
                $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));
            } else {
                session()->flash("error_message", "Edit folder can not be processed, because the folder has been deleted");
                return redirect("/interface_data/index");
            }
            # ---------------
            $data["attach_url"]    = "/interface_data/attach_item";
            $data["delete_url"]    = "/interface_data/delete_item";
            # ---------------
            return view("interface.form-edit", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            throw $e;
            # ---------------
            return view("error.405");
        }
    }

    public function add_subfolder($id)
    {
        try {
            $data["title"]         = "Add Subfolder";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/interface_data/save_subfolder";
            /* ----------
                 Model
                ----------------------- */
            $id                   = decodedData($id);
            /* ----------
                Source
                 ----------------------- */
            $qInterface               = InterfaceModel::find($id);
            // dd($qInterface);
            $data["id"]               = $qInterface;
            // $data["items"]            = $this->qInterface->getItemInterface($id);
            /* ----------
                Fields
                    ----------------------- */
            $data["fields"][]      = form_hidden(array("name" => "id", "label" => "ID", "readonly" => "readonly", "value" => $id));
            $data["fields"][]      = form_hidden(array("name" => "_method", "label" => "Method", "readonly" => "readonly", "value" => "POST"));
            $data["fields"][]      = form_text(array("name" => "folder_name", "label" => "Folder Name", "mandatory" => "yes", "readonly" => "readonly", "value" => $qInterface->folder_name));
            $data["fields"][]      = form_text(array("name" => "subfolder_name", "label" => "Subfolder Name", "mandatory" => "yes", "first_selected" => "yes"));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Save&nbsp;&nbsp;"));
            $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));

            # ---------------
            return view("default.form", $data);
        } catch (\Exception $e) {
            throw $e;
            $this->logModel->createError($e->getMessage(), "PAGE INTERFACE", "");
            # ---------------
            return view("error.405");
        }
    }

    public function save_subfolder(Request $request)
    {
        // dd($request);
        try {
            $rules["folder_name"]               = 'required|';
            $messages["folder_name.required"]   = 'Folder Name is required';

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("/interface_data/add_subfolder" . encodedData($request->id))
                    ->withErrors($validator)
                    ->withInput();
            } else {
                $response   = $this->qInterface->createSubfolder($request);
                if ($response["status"]) {
                    session()->flash("success_message", "Successfully to add subfolder");
                } else {
                    session()->flash("error_message", "Failed to to add subfolder");
                }
            }
            # ---------------
            return redirect("/interface_data/edit/" . encodedData($request->id));
        } catch (\Exception $e) {
            throw ($e);
            $this->logModel->createError($e->getMessage(), "PAGE INTERFACE", "");
            # ---------------
            return view("error.405");
        }
    }

    public function detail_subfolder($id)
    {
        try {
            $data["form_act"]      = "/interface_data/detail_subfolder/";

            /* ----------
                     Model
                    ----------------------- */
            $kode                   = base64_decode($id);
            list($idfolder, $idsubfolder) = explode("|", $kode);

            // dd($kode);
            /* ----------
                     Source
                    ----------------------- */
            $data["items"]            = $this->qInterface->getItemSubFolder($id);
            $qInterface               = $this->qInterface->getDataSubFolder($idsubfolder);
            $data["title"]            = $qInterface->subfolder_name;
            /* ----------
                     Fields
                    ----------------------- */
            $data["fields"][]      = form_hidden(array("name" => "id", "label" => "ID", "readonly" => "readonly", "value" => $id));
            $data["fields"][]      = form_hidden(array("name" => "_method", "label" => "Method", "readonly" => "readonly", "value" => "POST"));
            $data["fields"][]      = form_text(array("name" => "folder_name", "label" => "Folder Name", "mandatory" => "yes", "readonly" => "readonly", "value" => $qInterface->folder_name));
            $data["fields"][]      = form_text(array("name" => "folder_name", "label" => "Sub Folder Name", "mandatory" => "yes", "readonly" => "readonly", "value" => $qInterface->subfolder_name));
            # ---------------
            $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));
            # ---------------
            return view("interface.form-detail_subfolder", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE SUBFOLDER", "");
            throw $e;
            # ---------------
            return view("error.405");
        }
    }

    public function upload_subfolder($id)
    {
        try {
            $data["form_act"]      = "/interface_data/attach_item";
            /* ----------
                    Model
                    ----------------------- */
            $kode                   = base64_decode($id);
            list($idfolder, $idsubfolder) = explode("|", $kode);
            /* ----------
                    Source
                    ----------------------- */
            $qInterface               = $this->qInterface->getDataSubFolder($idsubfolder);
            $data["title"]            = $qInterface->subfolder_name;
            $selectIssueStatus        = $this->qReference->getSelectIssueStatus();
            $selectDocumentStatus     = [];
            $data["items"]            = $this->qInterface->getItemSubFolder($id);

            /* ----------
                    Fields
                    ----------------------- */
            $data["fields"][]      = form_hidden(array("name" => "id", "label" => "ID", "readonly" => "readonly", "value" => $id));
            $data["fields"][]      = form_hidden(array("name" => "_method", "label" => "Method", "readonly" => "readonly", "value" => "PUT"));
            $data["fields"][]      = form_text(array("name" => "folder_name", "label" => "Folder Name", "mandatory" => "yes", "readonly" => "readonly", "value" => $qInterface->folder_name));
            $data["fields"][]      = form_text(array("name" => "subfolder_name", "label" => "Subfolder Name", "mandatory" => "yes", "readonly" => "readonly", "value" => $qInterface->subfolder_name));

            /* ----------
                    Modal Fields
                    ----------------------- */
            $data["fields_modal"][] = form_hidden(array("name" => "interface_data_id", "label" => "ID", "readonly" => "readonly", "value" => $qInterface->interface_data_id));
            $data["fields_modal"][] = form_hidden(array("name" => "folder_name", "label" => "Folder Name", "mandatory" => "yes", "readonly" => "readonly", "value" => $qInterface->folder_name));
            $data["fields_modal"][] = form_hidden(array("name" => "interface_data_subfolder_id", "label" => "ID Subfolder", "readonly" => "readonly", "value" => $qInterface->interface_data_subfolder_id));
            $data["fields_modal"][] = form_hidden(array("name" => "subfolder_name", "label" => "Subfolder Name", "mandatory" => "yes", "readonly" => "readonly", "value" => $qInterface->subfolder_name));
            $data["fields_modal"][] = form_upload(array("name" => "document_file", "label" => "Document"));
            $data["fields_modal"][] = form_text(array("name" => "document_no", "label" => "Document Number"));
            $data["fields_modal"][] = form_text(array("name" => "document_title", "label" => "Document Name"));
            $data["fields_modal"][] = form_select(array("name" => "issue_status_id", "label" => "Issue Status", "withnull" => "yes", "source" => $selectIssueStatus, "value" => 0));
            $data["fields_modal"][] = form_select(array("name" => "document_status_id", "label" => "Revision Number", "withnull" => "yes", "source" => $selectDocumentStatus));
            $data["fields_modal"][] = form_text(array("name" => "remark", "label" => "Remark"));
            # ---------------
            $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));
            # ---------------
            $data["attach_url"]    = "/interface_data/attach_item";
            $data["delete_url"]    = "/interface_data/delete_item";
            # ---------------
            return view("interface.form-upload", $data);
        } catch (\Exception $e) {
            throw ($e);
            $this->logModel->createError($e->getMessage(), "PAGE UPLOAD SUBFOLDER", "");
            # ---------------
            return view("error.405");
        }
    }

    public function get_subfolder_search($id, $textSearch = null)
    {
        $items   = $this->qInterface->getSubfolderSearch($id, $textSearch);
        $response   = [
            "data" => $items
        ];

        return response()->json($response, GLOBAL_SUCCESS_RESPONSE);
    }

    public function get_subfolder_search_detail($id, $textSearch = null)
    {
        $items   = $this->qInterface->getSubfolderSearchDetail($id, $textSearch);
        dd($items);
        $response   = [
            "data" => $items
        ];

        return response()->json($response, GLOBAL_SUCCESS_RESPONSE);
    }

    public function delete($id)
    {
        try {
            $data["form_act"]      = "/interface_data/update_delete";
            /* ----------
             Model
            ----------------------- */
            $id                   = decodedData($id);
            $data["idfolder"]     = $id;
            /* ----------
             Source
            ----------------------- */
            $qInterface               = InterfaceModel::find($id);
            $data["title"]            = $qInterface->folder_name;
            $qSubFolder               = $this->qInterface->getSubfolder($id);
            // dd(count($qSubFolder));
            // dd($data["items"]);
            /* ----------
             Fields
            ----------------------- */
            if (count($qSubFolder) <= 0 && $qInterface->status == 1) {
                $data["fields"][]      = form_hidden(array("name" => "id", "label" => "ID", "readonly" => "readonly", "value" => $id));
                $data["fields"][]      = form_hidden(array("name" => "_method", "label" => "Method", "readonly" => "readonly", "value" => "POST"));
                $data["fields"][]      = form_text(array("name" => "folder_name", "label" => "Folder Name", "mandatory" => "yes", "readonly" => "readonly", "value" => $qInterface->folder_name));
                # ---------------
                $data["buttons"][]     = form_button_submit(array("name" => "button_save", "label" => "&nbsp;&nbsp;Delete&nbsp;&nbsp;"));
                $data["buttons"][]     = form_button_cancel(array("name" => "button_cancel", "label" => "Cancel"));
            } else {
                session()->flash("error_message", "Delete folder can not be processed, because there is already a sub or the folder has been deleted");
                return redirect("/interface_data/index");
            }
            # ---------------
            return view("interface.form-delete-folder", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            throw $e;
            # ---------------
            return view("error.405");
        }
    }

    public function update_delete(Request $request)
    {
        try {
            $rules = array();

            $messages = [];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("/interface_data/delete/" . decodedData($request->id))
                    ->withErrors($validator)
                    ->withInput();
            } else {
                $qInterface    = new InterfaceModel;

                $qResult           = $qInterface->deleteFolder($request);

                if ($qResult["status"]) {
                    session()->flash("success_message", "Folder successfully deleted");
                } else {
                    session()->flash("error_log", "Folder  failed to deleted");
                    session()->flash("id_log", $qResult["id"]);
                }
                # ---------------
                return redirect("/interface_data/index");
            }
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE INTERFACE", "");
            # ---------------
            return view("error.405");
        }
    }
}
