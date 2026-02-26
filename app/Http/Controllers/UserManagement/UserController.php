<?php

namespace App\Http\Controllers\UserManagement;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Datatables;
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

class UserController extends Controller
{
    protected $PROT_SideMenu, $PROT_Parent, $PROT_ModuleId, $PROT_ModuleName;

    public function __construct(Request $request) {
        # ---------------
        $uri                      = getUrl() . "/index";
        # ---------------
        $this->qMenu              = new MenuModel;
        $this->qUser              = new UserModel;
        $this->qReference         = new ReferenceModel;
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
            $data["form_act"]         = "/user/index";
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
                                                    ,"name"=>"id"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"center"
                                                          ,"item-format"=>"checkbox"
                                                            ,"item-class"=>""
                                                              ,"width"=>"5%"
                                                                ,"add-style"=>""),
                                            array("label"=>"Email"
                                                    ,"name"=>"email"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"left"
                                                          ,"item-format"=>"normal"
                                                            ,"item-class"=>""
                                                              ,"width"=>"20%"
                                                                ,"add-style"=>""),
                                            array("label"=>"Name"
                                                    ,"name"=>"name"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"left"
                                                          ,"item-format"=>"normal"
                                                            ,"item-class"=>""
                                                              ,"width"=>"20%"
                                                                ,"add-style"=>""),
                                            array("label"=>"Department"
                                                    ,"name"=>"department_name"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"left"
                                                          ,"item-format"=>"normal"
                                                            ,"item-class"=>""
                                                              ,"width"=>""
                                                                ,"add-style"=>""),
                                            array("label"=>"Discipline"
                                                    ,"name"=>"discipline_name"
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
                                                              ,"width"=>"15%"
                                                                ,"add-style"=>""),
                                            array("label"=>"Status"
                                                    ,"name"=>"status_code"
                                                      ,"align"=>"center"
                                                        ,"item-align"=>"center"
                                                          ,"item-format"=>"flag"
                                                            ,"item-class"=>""
                                                              ,"width"=>"10%"
                                                                ,"add-style"=>""));
            # ---------------
            $data["query"]         = $this->qUser->getCollections();
            $data["select"]        = $data["query"]["data"];
            $data["pagging"]       = getPagging($data["select"]);
            # ---------------
            # Advance Search
            # ---------------
            if(isset($request->module_id)) {
                $email             = ($request->email != "") ? session(["SES_SEARCH_USER_EMAIL" => $request->email]) : $request->session()->forget("SES_SEARCH_USER_EMAIL");
                $nama              = ($request->nama != "") ? session(["SES_SEARCH_USER_NAMA" => $request->nama]) : $request->session()->forget("SES_SEARCH_USER_NAMA");
                # ---------------
                return redirect("/user/index");
            }
            # ---------------
            if($request->session()->has("SES_SEARCH_USER_EMAIL")) {
                array_push($data["filtered_info"], "EMAIL");
            }
            if($request->session()->has("SES_SEARCH_USER_NAMA")) {
                array_push($data["filtered_info"], "NAMA");
            }
            # ---------------
            $data["adv_search"]    = true;
            $data["hide_simple_search"] = true;
            # ---------------
            $data["fields"][]      = form_hidden(array("name"=>"module_id", "label"=>"Module ID", "value"=>"USER"));
            $data["fields"][]      = form_search_text(array("name"=>"email", "label"=>"Email", "value"=>($request->session()->has("SES_SEARCH_USER_EMAIL")) ? $request->session()->get("SES_SEARCH_USER_EMAIL") : ""));
            $data["fields"][]      = form_search_text(array("name"=>"nama", "label"=>"Nama", "value"=>($request->session()->has("SES_SEARCH_USER_NAMA")) ? $request->session()->get("SES_SEARCH_USER_NAMA") : ""));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_search", "label"=>"&nbsp;&nbsp;Search&nbsp;&nbsp;"));
            $data["buttons"][]     = form_action_button(array("name"=>"button_clear", "label"=>"&nbsp;&nbsp;Clear&nbsp;&nbsp;", "url"=>"/user/unfilter"));
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
        session()->forget("SES_SEARCH_USER_EMAIL");
        session()->forget("SES_SEARCH_USER_NAMA");
        # ---------------
        return redirect("/user/index");
    }

    public function add() {
        try {
            $data["title"]         = "Add User";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/user/save";
            /* ----------
             Model
            ----------------------- */
            $selectDisc            = $this->qReference->getSelectDisciplineConcat();
            $selectPosition        = $this->qReference->getSelectPosition();
            $selectVendor          = $this->qReference->getSelectVendor();
            /* ----------
             Tabs
            ----------------------- */

            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_text(array("name"=>"name", "label"=>"User Name", "mandatory"=>"yes", "first_selected"=>"yes"));
            $data["fields"][]      = form_text(array("name"=>"full_name", "label"=>"Full Name", "mandatory"=>"yes"));
            $data["fields"][]      = form_email(array("name"=>"email", "label"=>"Email", "mandatory"=>"yes"));
            // $data["fields"][]      = form_select(array("name"=>"department_id", "label"=>"Department", "source"=>$selectDept));
            $data["fields"][]      = form_select(array("name"=>"discipline_id", "label"=>"Discipline", "source"=>$selectDisc));
            $data["fields"][]      = form_select(array("name"=>"position_id", "label"=>"Position", "source"=>$selectPosition));
            $data["fields"][]      = form_select(array("name"=>"vendor_id", "label"=>"Vendor", "withnull"=>"yes", "source"=>$selectVendor));
            $data["fields"][]      = form_text(array("name"=>"password", "label"=>"Password", "mandatory"=>"yes", "value"=>env("APPS_PWDDEF")));
            $data["fields"][]      = form_text(array("name"=>"password_confirm", "label"=>"Konfirmasi Password", "value"=>env("APPS_PWDDEF"), "mandatory"=>"yes"));
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
            $rules = array(
                            'name' => 'required|',
                            'email' => 'required|email:rfc|unique:sys_users',
                            'password' => 'required|min:6',
                            'password_confirm' => 'required|min:6|same:password',
            );

            $messages = [
                            'email.required' => 'Email harus diisi',
                            'name.required' => 'Nama harus diisi',
                            'password.required' => 'Password harus diisi',
                            'password_confirm.required' => 'Konfirmasi Password harus diisi',
                            'password_confirm.same' => 'Konfirmasi Password harus sama',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("/user/add")
                    ->withErrors($validator)
                    ->withInput();
            } else {
                $response   = $this->qUser->createData($request);

                if($response["status"]) {
                    session()->flash("success_message", "Successfully Saved");
                } else {
                    session()->flash("error_message", "Failed to save");
                }
                # ---------------
                return redirect("/user/index");
            }
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            # ---------------
            return view("error.405");
        }
    }

    public function edit($id) {
        try {
            $data["title"]        = "Edit User";
            $data["parent"]       = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]     = "/user/update";
            # ---------------
            $id                   = decodedData($id);
            /* ----------
             Model
            ----------------------- */
            $selectDisc            = $this->qReference->getSelectDisciplineConcat();
            $selectPosition        = $this->qReference->getSelectPosition();
            $selectVendor          = $this->qReference->getSelectVendor();
            /* ----------
             Source
            ----------------------- */
            $qUser                 = User::find($id);
            $qStatus               = getSelectStatusUser();
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name"=>"id", "label"=>"ID", "readonly"=>"readonly", "value"=>$id));
            $data["fields"][]      = form_hidden(array("name"=>"_method", "label"=>"Method", "readonly"=>"readonly", "value"=>"PUT"));
            $data["fields"][]      = form_text(array("name"=>"name", "label"=>"User Name", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$qUser->name));
            $data["fields"][]      = form_text(array("name"=>"full_name", "label"=>"Full Name", "mandatory"=>"yes", "value"=>$qUser->full_name));
            $data["fields"][]      = form_email(array("name"=>"email", "label"=>"Email", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$qUser->email));
            // $data["fields"][]      = form_select(array("name"=>"department_id", "label"=>"Department", "source"=>$selectDept, "value"=>$qUser->department_id));
            $data["fields"][]      = form_select(array("name"=>"discipline_id", "label"=>"Discipline", "source"=>$selectDisc, "value"=>$qUser->discipline_id."|".$qUser->department_id));
            $data["fields"][]      = form_select(array("name"=>"position_id", "label"=>"Position", "source"=>$selectPosition, "value"=>$qUser->position_id));
            $data["fields"][]      = form_select(array("name"=>"vendor_id", "label"=>"Vendor", "source"=>$selectVendor, "value"=>$qUser->vendor_id));
            $data["fields"][]      = form_radio(array("name"=>"user_status", "label"=>"Status", "mandatory"=>"yes", "source"=>$qStatus, "value"=>$qUser->user_status));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_save", "label"=>"Update"));
            $data["buttons"][]     = form_button_cancel(array("name"=>"button_cancel", "label"=>"Cancel"));
            # ---------------
            return view("default.form", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");

            throw $e;
            # ---------------
            return view("error.405");
        }
    }

    public function update(Request $request) {
        try {
            $rules = array(
                        'id' => 'required|',
            );

            $messages = [
                        'id.required' => 'ID harus diisi',
                        // 'email.required' => 'Email harus diisi',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("/user/edit/" . encodedData($request->input("id")))
                            ->withErrors($validator)
                            ->withInput();
            } else {
                $response   = $this->qUser->updateData($request);

                if($response["status"]) {
                    session()->flash("success_message", "Successfully updated");
                } else {
                    session()->flash("error_message", "Failed to updated");
                }
                # ---------------
                return redirect("/user/index");
            }
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            # ---------------
            return view("error.405");
        }
    }

    public function changepassword() {
        try {
            $id                    = Auth::user()->id;
            $data["label"]         = "User";
            $data["title"]         = "Change Password";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/user/updatepassword";
            /* ----------
             Source
            ----------------------- */
            $qUser                 = User::find($id);
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name"=>"id", "label"=>"ID", "readonly"=>"readonly", "value"=>$id));
            $data["fields"][]      = form_text(array("name"=>"name", "label"=>"User Name", "readonly"=>"readonly", "value"=>$qUser->name));
            $data["fields"][]      = form_text(array("name"=>"email", "label"=>"Email", "readonly"=>"readonly", "value"=>$qUser->email));
            $data["fields"][]      = form_password(array("name"=>"current_password", "label"=>"Current Password", "mandatory"=>"yes", "first_selected"=>"yes"));
            $data["fields"][]      = form_password(array("name"=>"new_password", "label"=>"New Password", "mandatory"=>"yes"));
            $data["fields"][]      = form_password(array("name"=>"password_confirm", "label"=>"Password Confirm", "mandatory"=>"yes"));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_save", "label"=>"Update"));
            $data["buttons"][]     = form_button_cancel(array("name"=>"button_cancel", "label"=>"Cancel"));
            # ---------------
            return view("auth.passwords.change", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            # ---------------
            return view("error.405");
        }
    }

    public function updatepassword(Request $request) {
        try {
            $rules = array(
                        'current_password' => 'required|min:6',
                        'new_password' => [
                            'required',
                            'string',
                            'min:6',             // must be at least 10 characters in length
                            'regex:/[a-z]/',      // must contain at least one lowercase letter
                            'regex:/[A-Z]/',      // must contain at least one uppercase letter
                            'regex:/[0-9]/',      // must contain at least one digit
                            'regex:/[@$!%*#?&]/', // must contain a special character
                        ],
                        //'new_password' => 'required|min:6|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/',
                        'password_confirm' => 'required|min:6|same:new_password',
            );

            $messages = [
                        'current_password.required' => 'Passsword saat ini harus diisi',
                        'new_password.required' => 'Password baru harus diisi',
                        'password_confirm.required' => 'Konfirmasi Password baru harus diisi',
                        'password_confirm.same' => 'Konfirmasi Password harus sama',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect("user/changepassword")
                    ->withErrors($validator)
                    ->withInput();
            } else {
                if(Hash::check($request->current_password, Auth::User()->password)) {
                    if($request->current_password == $request->new_password) {
                        session()->flash("error_message", "New password cannot be the same");
                        # ---------------
                        return redirect("/expired_password");
                    } else {
                        $response   = $this->qUser->updatePassword($request);

                        if($response["status"]) {
                            session()->flash("success_message", "Successfully updated");
                        } else {
                            session()->flash("error_message", "Failed to updated");
                        }
                        # ---------------
                        return redirect("/");
                    }
                } else {
                    session()->flash("error_message", "Incorrect current password");
                    # ---------------
                    return redirect("user/changepassword");
                }
            }
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            # ---------------
            return view("error.405");
        }
    }

    public function reset($id) {
        try {
            $data["title"]         = "Reset Password";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/user/reset_password";
            # ---------------
            $id                    = decodedData($id);
            /* ----------
             Source
            ----------------------- */
            $qUser                 = User::find($id);
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name"=>"id", "label"=>"ID", "readonly"=>"readonly", "value"=>$id));
            $data["fields"][]      = form_hidden(array("name"=>"_method", "label"=>"Method", "readonly"=>"readonly", "value"=>"PUT"));
            $data["fields"][]      = form_text(array("name"=>"name", "label"=>"User Name", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$qUser->name));
            $data["fields"][]      = form_text(array("name"=>"full_name", "label"=>"Full Name", "readonly"=>"readonly", "mandatory"=>"yes", "value"=>$qUser->full_name));
            $data["fields"][]      = form_email(array("name"=>"email", "label"=>"Email", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$qUser->email));
            $data["fields"][]      = form_text(array("name"=>"password", "label"=>"Password", "mandatory"=>"yes", "value"=>env("APPS_PWDDEF")));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_save", "label"=>"Reset"));
            $data["buttons"][]     = form_button_cancel(array("name"=>"button_cancel", "label"=>"Cancel"));
            # ---------------
            return view("default.form", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            # ---------------
            return view("error.405");
        }
    }

    public function reset_password(Request $request) {
        try {
            $response   = $this->qUser->resetPassword($request);

            if($response["status"]) {
                session()->flash("success_message", "Successfully updated");
            } else {
                session()->flash("error_message", "Failed to updated");
            }
            # ---------------
            return redirect("/user/index");
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            # ---------------
            return view("error.405");
        }
    }

    public function privilege($id) {
        try {
            $data["title"]         = "Privilege";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/user/update_privilege";
            # ---------------
            $id                    = decodedData($id);
            /* ----------
             Source
            ----------------------- */
            $qUser                 = User::find($id);
            $menu                  = $this->qMenu->getAllPrivileges($id);
            $data["menu"]          = $menu["data"];
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_hidden(array("name"=>"id", "label"=>"ID", "readonly"=>"readonly", "value"=>$id));
            $data["fields"][]      = form_text(array("name"=>"name", "label"=>"User Name", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$qUser->name));
            $data["fields"][]      = form_text(array("name"=>"full_name", "label"=>"Full Name", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$qUser->full_name));
            $data["fields"][]      = form_text(array("name"=>"email", "label"=>"Email", "mandatory"=>"yes", "readonly"=>"readonly", "value"=>$qUser->email));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_save", "label"=>"Update"));
            $data["buttons"][]     = form_button_cancel(array("name"=>"button_cancel", "label"=>"Cancel"));
            # ---------------
            return view("user.form-privilege", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE PRIVILEGE", "");
            # ---------------
            return view("error.405");
        }
    }

    public function update_privilege(Request $request) {
        try {
            $response   = $this->qMenu->updatePrivilege($request);

            if($response["status"]) {
                session()->flash("success_message", "Successfully updated");
            } else {
                session()->flash("error_message", "Failed to updated");
            }
            # ---------------
            return redirect("/user/index");
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE USER", "");
            # ---------------
            return view("error.405");
        }
    }

    public function duplicate($id) {
        try {
            $data["title"]         = "Duplicate";
            $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]      = "/user/create_duplicate";
            # ---------------
            $id                    = decodedData($id);
            /* ----------
             Source
            ----------------------- */
            $qUser                 = User::find($id);
            $selectDisc            = $this->qReference->getSelectDisciplineConcat();
            $selectPosition        = $this->qReference->getSelectPosition();
            $menu                  = $this->qMenu->getAllPrivileges($id);
            $data["menu"]          = $menu["data"];
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_text(array("name"=>"name", "label"=>"User Name", "mandatory"=>"yes", "first_selected"=>"yes"));
            $data["fields"][]      = form_text(array("name"=>"full_name", "label"=>"Full Name", "mandatory"=>"yes"));
            $data["fields"][]      = form_text(array("name"=>"email", "label"=>"Email", "mandatory"=>"yes"));
            $data["fields"][]      = form_select(array("name"=>"discipline_id", "label"=>"Discipline", "source"=>$selectDisc));
            $data["fields"][]      = form_select(array("name"=>"position_id", "label"=>"Position", "source"=>$selectPosition));
            $data["fields"][]      = form_text(array("name"=>"password", "label"=>"Password", "mandatory"=>"yes", "value"=>env("APPS_PWDDEF")));
            $data["fields"][]      = form_text(array("name"=>"password_confirm", "label"=>"Konfirmasi Password", "value"=>env("APPS_PWDDEF"), "mandatory"=>"yes"));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_save", "label"=>"Save"));
            $data["buttons"][]     = form_button_cancel(array("name"=>"button_cancel", "label"=>"Cancel"));
            # ---------------
            return view("user.form-privilege", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE PRIVILEGE", "");
            # ---------------
            return view("error.405");
        }
    }

    public function create_duplicate(Request $request) {
        try {
            $response   = $this->qUser->createDuplicate($request);

            if($response["status"]) {
                session()->flash("success_message", "Successfully updated");
            } else {
                session()->flash("error_message", "Failed to updated");
            }
            # ---------------
            return redirect("/user/index");
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE DUPLICATE PRIVILEGE", "");
            # ---------------
            return view("error.405");
        }
    }

    public function preview() {
        $data["title"]              = "USER LISTS";
        $data["periode"]            = date("d/m/Y");
        # ---------------
        $data["url_data"]           = url('/') . "/user/preview_json";
        # ---------------
        $data["column_unit"]        = 6;
        $data["content_center"]     = "0,1,6";
        $data["content_right"]      = "";
        $data["token"]              = "";
        # ---------------
        return view("default.report-datatable", $data);
    }

    public function preview_json(){
        $query  = $this->qUser->getListUsers();
        
        return Datatables::of($query)->make(true); 
    }
}
