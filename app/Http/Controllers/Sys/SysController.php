<?php

namespace App\Http\Controllers\Sys;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use View;
use Auth;
use Mail;
use App\Model\UserManagement\MenuModel;
use App\Model\Reference\ReferenceModel;
use App\Model\Sys\SysModel;
use App\Model\Sys\LogModel;

class SysController extends Controller
{
    protected $PROT_SideMenu, $PROT_Parent, $PROT_ModuleId, $PROT_ModuleName;

    public function __construct(Request $request) {
        $uri                      = "/maintenance_mode";
        # ---------------
        $this->sysModel           = new SysModel;
        $this->logModel           = new LogModel;
        $this->referenceModel     = new ReferenceModel;
        $qMenu                    = new MenuModel;
        $rs                       = $qMenu->getParentMenu($uri);
        # ---------------
        $this->PROT_Parent        = "Home";
        $this->PROT_ModuleName    = "Home";
        $this->PROT_ModuleId      = "1";
        # ---------------
        View::share(array("SHR_Parent"=>$this->PROT_Parent, "SHR_Module"=>$this->PROT_ModuleName, "SHR_ModuleId"=>$this->PROT_ModuleId));
    }

    public function maintenance_mode() {
        return view("default.maintenance");
    }

    public function url_injection() {
        return view("default.forbidden");
    }

    public function index() {
        try {
            $data["title"]        = "Config";
            $data["parent"]       = ucwords(strtolower($this->PROT_Parent));
            $data["form_act"]     = "/config/update";
            /* ----------
             Model
            ----------------------- */

            /* ----------
             Source
            ----------------------- */
            $dataConfig            = $this->sysModel->getConfig();
            $selectMaintenance     = getSelectMaintenanceMode();
            $selectUser            = $this->referenceModel->getSelectUser();
            $selectExtention       = $this->referenceModel->getSelectExtention();
            $selectEmail           = $this->referenceModel->getSelectEmail();
            $sel                   = explode(",", $dataConfig->attachment_extention);
            $selEmail              = explode(",", $dataConfig->document_controll_email_address_notification);
            /* ----------
             Fields
            ----------------------- */
            $data["fields"][]      = form_select(array("name"=>"maintenance_sys_mode", "label"=>"Maintenance Mode", "source"=>$selectMaintenance, "value"=>$dataConfig->maintenance_sys_mode));
            $data["fields"][]      = form_number(array("name"=>"password_expired", "label"=>"Expired password (days)", "value"=>$dataConfig->password_expired));
            $data["fields"][]      = form_number(array("name"=>"max_due_days", "label"=>"Deadline (days)", "value"=>$dataConfig->max_due_days));
            $data["fields"][]      = form_number(array("name"=>"return_max_due_days", "label"=>"Return Deadline (days)", "value"=>$dataConfig->return_max_due_days));
            $data["fields"][]      = form_select(array("name"=>"email_status", "label"=>"Email Notification Status", "source"=>$selectMaintenance, "value"=>$dataConfig->email_status));
            $data["fields"][]      = form_select(array("name"=>"default_approval_id", "label"=>"Default Approval By", "source"=>$selectUser, "value"=>$dataConfig->default_approval_id));
            $data["fields"][]      = form_multi_select(array("name"=>"attachment_extention[]", "label"=>"Attacment Type", "source"=>$selectExtention, "value"=>$sel));
            $data["fields"][]      = form_multi_select(array("name"=>"document_controll_email_address_notification[]", "label"=>"DC Email Notification", "source"=>$selectEmail, "value"=>$selEmail));
            $data["fields"][]      = form_currency(array("name"=>"attachment_max_size", "label"=>"Attacment maximum size (kb)", "value"=>setComma($dataConfig->attachment_max_size)));
            # ---------------
            $data["buttons"][]     = form_button_submit(array("name"=>"button_save", "label"=>"Update"));
            $data["buttons"][]     = form_button_cancel(array("name"=>"button_cancel", "label"=>"Cancel"));
            # ---------------
            return view("default.form", $data);
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE CONFIG", "");
            # ---------------
            return view("error.405");
        }
    }

    public function update(Request $request) {
        try {
            $response   = $this->sysModel->updateData($request);

            if($response["status"]) {
                session()->flash("success_message", "Successfully updated");
            } else {
                session()->flash("error_message", "Failed to updated");
            }
            # ---------------
            return redirect("/config/index");
        } catch (\Exception $e) {
            $this->logModel->createError($e->getMessage(), "PAGE UPDATE CONFIG", "");
            # ---------------
            return view("error.405");
        }
    }

    public function send_test_email() {
        try {
            $title              = "Email Testing";
            $data["title"]      = $title;
            # ---------------
            Mail::send('email.sending-test', $data, function($message) use ($title){
                $message->to("arif@mitrafin.co.id")->subject($title);
                $message->to("reski.rona@forel-hanochem.com")->subject($title);
                $message->to("norashikin@franklin.com.sg")->subject($title);
                # ---------------
                $message->from(env("MAIL_USERNAME"), 'Automatic Mail System');
            });
            # ---------------
            return "Success !!";
        } catch (\Exception $e) {
            return "Failed !!";
            return view("error.405");
        }
    }
}
