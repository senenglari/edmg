<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use View;
use DB;
use Auth;
use Validator;
use Excel;
use PHPExcel;
use PHPExcel_IOFactory;
use Yajra\Datatables\Datatables;
use App\Model\Dashboard\DashboardModel;
use App\Model\Reference\ReferenceModel;
use App\Model\Reference\VendorModel;
use App\Model\Sys\SysModel;
use App\Model\UserManagement\UserModel;

class DashboardController extends Controller
{
    protected $PROT_SideMenu, $PROT_Parent, $PROT_ModuleId, $PROT_ModuleName;

    public function __construct() {
        # ---------------
        $uri                    = \Request::segment(1);
        # ---------------
        $this->qDashboard       = new DashboardModel;
        $this->qReference       = new ReferenceModel;
        $this->qVendor          = new VendorModel;
        $this->qUser            = new UserModel;
        # ---------------
        $this->PROT_Parent      = "Dashboard";
        $this->PROT_ModuleName  = "Dashboard";
        $this->PROT_ModuleId    = 1;
        # ---------------
        View::share(array("SHR_Parent"=>$this->PROT_Parent, "SHR_Module"=>$this->PROT_ModuleName));
    }

    public function index(Request $request) {
//        return auth()->user();
        $nama_vendor            = ($request->vendor_id != 0) ? " ~ " . $this->qVendor->getVendor($request->vendor_id)["data"]->name : "";
        $data["title"]          = "Dashboard" . $nama_vendor;
        $data["parent"]         = ucwords(strtolower($this->PROT_Parent));
        $data["form_act"]       = "/";
        $data["url_ifc"]        = url('/') . "/preview_ifc";
        $data["url_ifa"]        = url('/') . "/preview_ifa";
        $data["url_ifu"]        = url('/') . "/preview_ifu";
        $data["url_afc"]        = url('/') . "/preview_afc";

        /* ----------
        Data
        ----------------------- */
        $priv                   = $this->qUser->cekPrivilege(Auth::user()->id, $this->PROT_ModuleId);

        if(count($priv) > 0) {
            session(["SES_DASHBOARD_VENDOR_SELECTED" => $request->vendor_id]);
            session(['is_chat_admin' => (bool) true]);
            # --------------------
            $selectVendor           = $this->qReference->getSelectVendor();
            $data["summary"]        = $this->qDashboard->getSummary();
            $data["ifc_list"]       = $this->qDashboard->getIFCList();
            $data["ifa_list"]       = $this->qDashboard->getIFAList();
            $data["afd_list"]       = $this->qDashboard->getAFDList();
            $data["afc_list"]       = $this->qDashboard->getAFCList();
            # --------------------
            $data["vendor"]         = form_search_select(array("name" => "vendor_id", "label" => "Vendor", "withnull"=>"yes", "source" => $selectVendor, "value" => ($request->session()->has("SES_DASHBOARD_VENDOR_SELECTED")) ? $request->session()->get("SES_DASHBOARD_VENDOR_SELECTED") : ""));
            /* ----------
            View
            ----------------------- */
            // dd($data["ifc_list"]["data"][43]);
            return view("dashboard.dashboard", $data);
        } else {
            session(['is_chat_admin' => (bool) false]);
            return view("dashboard.dashboard-guest", $data);
        }
    }

    public function preview_ifc() {
        $data["title"]              = "IFC ~ Issue for Comment";
        $data["periode"]            = date("d/m/Y");
        # ---------------
        $data["url_data"]           = url('/') . "/preview_json_ifc";
        # ---------------
        $data["column_unit"]        = 4;
        $data["content_center"]     = "0,2,3";
        $data["content_left"]       = "1,4";
        $data["content_right"]      = "";
        $data["token"]              = "";
        # ---------------
        return view("default.report-datatable", $data);
    }

    public function preview_json_ifc(){
        $query  = $this->qDashboard->getIFC();
        
        return Datatables::of($query)->make(true); 
    }

    public function preview_ifa() {
        $data["title"]              = "IFA ~ Issue for Approval";
        $data["periode"]            = date("d/m/Y");
        # ---------------
        $data["url_data"]           = url('/') . "/preview_json_ifa";
        # ---------------
        $data["column_unit"]        = 4;
        $data["content_center"]     = "0,2,3";
        $data["content_left"]       = "1,4";
        $data["content_right"]      = "";
        $data["token"]              = "";
        # ---------------
        return view("default.report-datatable", $data);
    }

    public function preview_json_ifa(){
        $query  = $this->qDashboard->getIFA();
        
        return Datatables::of($query)->make(true); 
    }

    public function preview_ifu() {
        $data["title"]              = "IFA ~ Issue for Use";
        $data["periode"]            = date("d/m/Y");
        # ---------------
        $data["url_data"]           = url('/') . "/preview_json_ifu";
        # ---------------
        $data["column_unit"]        = 4;
        $data["content_center"]     = "0,2,3";
        $data["content_left"]       = "1,4";
        $data["content_right"]      = "";
        $data["token"]              = "";
        # ---------------
        return view("default.report-datatable", $data);
    }

    public function preview_json_ifu(){
        $query  = $this->qDashboard->getAFD();
        
        return Datatables::of($query)->make(true); 
    }

    public function preview_afc() {
        $data["title"]              = "AFC ~ Approved for Construction";
        $data["periode"]            = date("d/m/Y");
        # ---------------
        $data["url_data"]           = url('/') . "/preview_json_afc";
        # ---------------
        $data["column_unit"]        = 4;
        $data["content_center"]     = "0,2,3";
        $data["content_left"]       = "1,4";
        $data["content_right"]      = "";
        $data["token"]              = "";
        # ---------------
        return view("default.report-datatable", $data);
    }

    public function preview_json_afc(){
        $query  = $this->qDashboard->getAFC();
        
        return Datatables::of($query)->make(true); 
    }
}
