<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use View;
use Auth;
use Validator;
use Hash;
use App\User;
use App\Model\UserManagement\MenuModel;
use App\Model\UserManagement\UserModel;
// use App\Model\Master\MasterModel;

class ExpiredPasswordController extends Controller
{
	protected $PROT_SideMenu, $PROT_Parent, $PROT_ModuleId, $PROT_ModuleName;

    public function __construct(Request $request) {
        # ---------------
        $uri                      = "/expired_password";
        # ---------------
        $qMenu                    = new MenuModel;
        $rs                       = $qMenu->getParentMenu($uri);
        # ---------------
        $this->PROT_Parent        = "Home";
        $this->PROT_ModuleName    = "Home";
        $this->PROT_ModuleId      = "1";
        # ---------------
        View::share(array("SHR_Parent"=>$this->PROT_Parent, "SHR_Module"=>$this->PROT_ModuleName, "SHR_ModuleId"=>$this->PROT_ModuleId));
    }

    public function expired_password() {
        $id                    = Auth::user()->id;
        $data["label"]         = "User";
        $data["title"]         = "Update your password";
        $data["parent"]        = ucwords(strtolower($this->PROT_Parent));
        $data["form_act"]      = "/change_expired_password";
        /* ----------
         Source
        ----------------------- */
        $qUser                 = User::find($id);
        /* ----------
         Fields
        ----------------------- */
        $data["fields"][]      = form_hidden(array("name"=>"id", "label"=>"ID", "readonly"=>"readonly", "value"=>$id));
        $data["fields"][]      = form_text(array("name"=>"name", "label"=>"Name", "readonly"=>"readonly", "value"=>$qUser->name));
        $data["fields"][]      = form_hidden(array("name"=>"email", "label"=>"Alamat Email", "readonly"=>"readonly", "value"=>$qUser->email));
        $data["fields"][]      = form_password(array("name"=>"current_password", "label"=>"Current Password", "mandatory"=>"yes", "first_selected"=>"yes"));
        $data["fields"][]      = form_password(array("name"=>"new_password", "label"=>"New Password", "mandatory"=>"yes"));
        $data["fields"][]      = form_password(array("name"=>"password_confirm", "label"=>"Password Confirm", "mandatory"=>"yes"));
        # ---------------
        $data["buttons"][]     = form_button_submit(array("name"=>"button_save", "label"=>"Update"));
        $data["buttons"][]     = form_button_cancel(array("name"=>"button_cancel", "label"=>"Cancel"));
        # ---------------
        return view("auth.passwords.change", $data);
    }

    public function change_expired_password(Request $request) {
        $rules = array(
        			'id' => 'required',
                    'current_password' => 'required|min:3',
                    //'new_password' => 'required|min:3|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/',
                    'new_password' => [
                        'required',
                        'string',
                        'min:6',             // must be at least 10 characters in length
                        'regex:/[a-z]/',      // must contain at least one lowercase letter
                        'regex:/[A-Z]/',      // must contain at least one uppercase letter
                        'regex:/[0-9]/',      // must contain at least one digit
                        'regex:/[@$!%*#?&]/', // must contain a special character
                    ],
                    'password_confirm' => 'required|min:3|same:new_password',
        );

        $messages = [
                    'id.required' => 'ID harus diisi',
                    'current_password.required' => 'Passsword saat ini harus diisi',
                    'new_password.required' => 'Password baru harus diisi',
                    'password_confirm.required' => 'Konfirmasi Password baru harus diisi',
                    'password_confirm.same' => 'Konfirmasi Password harus sama',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect("/expired_password")
                ->withErrors($validator)
                ->withInput();
        } else {
            if(Hash::check($request->current_password, Auth::User()->password)) {
                if($request->current_password == $request->new_password) {
                    session()->flash("error_message", "New password cannot be same");
                    # ---------------
                    return redirect("/expired_password");
                } else {
                    $qUser      = new UserModel;
                    # ---------------
                    $qUser->updatePassword($request);
                    # ---------------
                    return redirect("/");
                }
            } else {
                session()->flash("error_message", "Incorrect current password");
                # ---------------
                return redirect("/expired_password");
            }
        }
    }
}
