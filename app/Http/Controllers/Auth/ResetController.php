<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Hash;
use Auth;
use App\Model\Master\UserModel;

class ResetController extends Controller
{
    public function reset_password() {
        $data["email"]  = session()->get("SES_MAIL");

        return view("auth.passwords.reset", $data);
    }

    public function update_password(Request $request) {
        $rules = array(
            'current_password' => 'required|min:6',
            'new_password' => 'required|min:6',
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
            return redirect("/reset")
                ->withErrors($validator)
                ->withInput();
        } else {
            if(Hash::check($request->current_password, Auth::User()->password)) {
                $qUser      = new UserModel;
                # ---------------
                $qUser->changePasswordByEmail($request);
                # ---------------
                return redirect("/");
            } else {
                session()->flash("RESET_SES_MESSAGE", "Password saat ini tidak sesuai");
                # ---------------
                return redirect("/reset");
            }
        }
    }
}