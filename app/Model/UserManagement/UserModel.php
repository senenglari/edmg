<?php

namespace App\Model\UserManagement;

use App\Model\Chatting\Conversation;
use App\Model\Chatting\Message;
use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use Carbon\Carbon;
use App\Model\Sys\LogModel;

class UserModel extends Model
{
    protected $table    = "sys_users";

    public function __construct() {
        $this->logModel     = new LogModel;   
    }

    public function getCollections() {
        try {
            $query  = DB::table("vw_users")
                                ->select("vw_users.id", "vw_users.email", "vw_users.name", "vw_users.avatar", "vw_users.user_status", "vw_users.perpage"
                                        , "vw_users.status_code", "vw_users.discipline_name", "vw_users.department_name", DB::RAW("IFNULL(ref_vendor.name, '-') AS vendor_name"))
                                ->leftJoin("ref_vendor", "vw_users.vendor_id", "ref_vendor.vendor_id")
                                ->orderBy("vw_users.id", "DESC");

            if(session()->has("SES_SEARCH_USER_EMAIL") != "") {
                $query->where("vw_users.email", "LIKE", "%" . session()->get("SES_SEARCH_USER_EMAIL") . "%");
            }

            if(session()->has("SES_SEARCH_USER_NAMA") != "") {
                $query->where("vw_users.name", "LIKE", "%" . session()->get("SES_SEARCH_USER_NAMA") . "%");
            }

            $result = $query->paginate(PAGINATION);

            return array("status"=>true, "data"=>$result);
        } catch (\Exception $e) {
            return array("status"=>false, "data"=>[]);
        }
    }

    public function getProfile($id) {
        $query  = DB::table("vw_users")
                            ->select("id", "email", "name", "avatar", "full_name"
                                    , "user_status", "perpage", "status_code", "discipline_name", "department_name")
                            ->where("id", $id)
                            ->orderBy("id", "DESC");

        $result = $query->get();

        return $result;
    }    

    public function createData($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            list($discipline_id, $department_id) = explode("|", $request->discipline_id);
            # ------------------------
            $id     = DB::table($this->table)
                            ->insertGetId([
                                "email"=>$request->email,
                                "name"=>$request->name,
                                "full_name"=>$request->full_name,
                                "department_id"=>$department_id,
                                "discipline_id"=>$discipline_id,
                                "position_id"=>$request->position_id,
                                "vendor_id"=>$request->vendor_id,
                                "password"=>bcrypt($request->password),
                                "user_status"=>1,
                                "avatar"=>"unknown.png",
                                "perpage"=>env("APPS_PERPAGE"),
                            ]);
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("CREATE USER (" . $id . ") " . strtoupper($request->name), Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id   = $this->logModel->createError($e->getMessage(), "CREATE USER FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }

    public function updateData($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            list($discipline_id, $department_id) = explode("|", $request->discipline_id);
            # ------------------------
            DB::table($this->table)
                    ->where("id", $request->id)
                    ->update([
                        "full_name"=>$request->full_name,
                        "department_id"=>$department_id,
                        "discipline_id"=>$discipline_id,
                        "position_id"=>$request->position_id,
                        "vendor_id"=>$request->vendor_id,
                        "user_status"=>$request->user_status,
                    ]);
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("UPDATE USER (" . $request->id . ") " . strtoupper($request->email), Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "UPDATE USER FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }

    public function updatePassword($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table($this->table)
                    ->where("id", $request->id)
                    ->update([
                        "password"=>bcrypt($request->new_password),
                        "password_changed_at"=>Carbon::now()->toDateTimeString(),
                    ]);
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("CHANGE PASSWORD " . strtoupper($request->email), Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "UPDATE PASSWORD FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }

    public function getUserByNik($nik) {
        $query  = DB::table("sys_users")->select("*")
                                        ->where("nik", $nik)
                                        ->get();

        return $query;
    }

    public function resetPassword($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table($this->table)
                        ->where("id", $request->id)
                        ->update([
                            "password"=>bcrypt($request->password),
                            "password_changed_at"=>NULL,
                            "session_id"=>"",
                        ]);
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("RESET PASSWORD (" . $request->id . ") ", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id    = $this->logModel->createError($e->getMessage(), "RESET PASSWORD FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }

    public function createDuplicate($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            list($discipline_id, $department_id) = explode("|", $request->discipline_id);
            # ------------------------
            $id     = DB::table($this->table)
                            ->insertGetId([
                                "email"=>$request->email,
                                "name"=>$request->name,
                                "full_name"=>$request->full_name,
                                "department_id"=>$department_id,
                                "discipline_id"=>$discipline_id,
                                "position_id"=>$request->position_id,
                                "password"=>bcrypt($request->password),
                                "user_status"=>1,
                                "avatar"=>"unknown.png",
                                "perpage"=>env("APPS_PERPAGE"),
                            ]);
            # ---------------
            $Unit   = count($request->input("check_menu"));
            # ---------------
            for($i=0; $i<$Unit; $i++) {
                DB::table("sys_user_menus")
                            ->insert([
                                "menu_id"=>$request->check_menu[$i],
                                "user_id"=>$id,
                            ]);
            }
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("DUPLICATE USER (" . $id . ") " . strtoupper($request->name), Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id   = $this->logModel->createError($e->getMessage(), "DUPLICATE FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }

    public function getListUsers() {
        try {
            $query  = DB::table("vw_users")->select("id", "name", "email", "full_name", "discipline_name AS discipline", "department_name AS department", "status_code AS status");

            return $query;
        } catch (\Exception $e) {
            return array("status"=>false, "error_log"=>$e->getMessage());
        }
    }

    public function cekPrivilege($user_id, $menu_id) {
        try {
            $query  = DB::table("sys_user_menus")->select("*")->where("menu_id", $menu_id)->where("user_id", $user_id)->get();

            return $query;
        } catch (\Exception $e) {
            return array("status"=>false, "error_log"=>$e->getMessage());
        }
    }

    /**
     * Chat milik user (sebagai customer)
     */
    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'user_id');
    }

    /**
     * Chat yang di-handle admin
     */
    public function handledConversations()
    {
        return $this->hasMany(Conversation::class, 'admin_id');
    }

    /**
     * Semua pesan yang dikirim user
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }
}
