<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use DB;
use Mail;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function forgot() {
        return view("auth.forgot");
    }

    public function send_forgot(Request $request) {
        try {
            $validate   = DB::table("sys_users")->select("*")->where("email", $request->email)->where("user_status", 1)->get();

            if(count($validate) > 0) {
                $title                          = "Password Reset";
                $email_address                  = $request->email;
                $email_name                     = $request->full_name;
                $random_pass                    = rand(100000, 999999);
                $data["title"]                  = "Password Reset";
                $data["new_password"]           = $random_pass;
                # ---------------
                DB::table("sys_users")
                            ->where("email", $request->email)
                            ->update([
                                "password"=>bcrypt($random_pass),
                                "password_changed_at"=>NULL,
                                "session_id"=>"",
                            ]);
                # ---------------
                Mail::send('email.password-reset', $data, function($message) use ($title, $email_address, $email_name) {
                    $message->to($email_address, $email_name)->subject($title);
                    # ---------------
                    $message->from(env("MAIL_USERNAME"), 'Automatic Mail System');
                }); 
                # ---------------
                session()->flash("success_message", "Please check your email to see your new password");
                # ---------------
                return redirect("/login");
            } else {
                session()->flash("error_message", "Email is not registered");
                # ---------------
                return redirect("/forgot_password");
            }
        } catch (\Exception $e) {
            throw $e;
            return view("error.405");
        }
    }
}
