<?php

namespace App\Model\UserManagement;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use App\Model\Sys\LogModel;

class MenuModel extends Model
{
    protected $table = "sys_menus";

    public function __construct() {
        $this->logModel     = new LogModel;   
    }

    public function getMenu($user_id=null, $level=null, $parent=null) {
            $query  = DB::table("sys_menus")
                                ->join("sys_user_menus", "sys_menus.id", "=", "sys_user_menus.menu_id")
                                ->where("sys_menus.status", 1)
                                ->select("sys_menus.id", "sys_menus.name", "sys_menus.icon", "sys_menus.url", "sys_menus.parent", "sys_menus.order")
                                ->orderBy("sys_menus.order", "ASC");

        if($user_id != null) {
            $query->where("sys_user_menus.user_id", $user_id);
        }

        if($level != null) {
            $query->where("sys_menus.level", $level);
        }

        if($parent != null) {
            $query->where("sys_menus.parent", $parent);
        }

        $result = $query->get();

        return $result;
    }

    public function getActionMenu($user_id, $parent) {
        $query  = DB::table("sys_menus")
                            ->join("sys_user_menus", "sys_menus.id", "=", "sys_user_menus.menu_id")
                            ->select("sys_menus.id", "sys_menus.name", "sys_menus.url", "sys_menus.icon")
                            ->where("sys_user_menus.user_id", "=", $user_id)
                            ->where("sys_menus.level", "=", 3)
                            ->where("sys_menus.status", "=", 1)
                            ->where("sys_menus.parent", "=", $parent)
                            ->where("sys_menus.position", "=", "sidebar")
                            ->orderBy("sys_menus.order", "ASC")
                            ->get();

           return $query;
    }

    public function getParentMenu($url) {
        $query  = DB::table("vw_structure_menu")
                            ->select("id", "url", "name", "level", "parent_id", "parent_name", "parent_level")
                            ->where("url", "=", $url)
                            ->get();

        return $query;
    }

    public function getMenuById($id) {
        $query  = DB::table("sys_menus")
                            ->select("*")
                            ->where("id", "=", $id)
                            ->first();

        return $query;
    }

    public function getAllPrivileges($id) {
        try {
            $query_1    = DB::table("sys_menus")->select("sys_menus.*", DB::RAW("sys_menus.id AS menu_id"), DB::RAW("SUBSTRING_INDEX(sys_menus.icon, '|', 1) AS menu_method"), DB::RAW("SUBSTRING_INDEX(sys_menus.icon, '|', -1) AS menu_icon"))
                                               ->where("sys_menus.status", 1)                                               
                                               ->where("sys_menus.level", 1)
                                               ->orderBy("sys_menus.order", "ASC")
                                               ->get();

            $menu       = [];
            $submenu_2  = [];
            $submenu_3  = [];
            foreach($query_1 as $row_1) {
                $exist_1  = DB::table("sys_user_menus")->where("user_id", $id)->where("menu_id", $row_1->menu_id)->count();

                $query_2  = DB::table("sys_menus")->select("sys_menus.*", DB::RAW("sys_menus.id AS menu_id"), DB::RAW("SUBSTRING_INDEX(sys_menus.icon, '|', 1) AS menu_method"), DB::RAW("SUBSTRING_INDEX(sys_menus.icon, '|', -1) AS menu_icon"))
                                                   ->where("sys_menus.status", 1)                                               
                                                   ->where("sys_menus.level", 2)
                                                   ->where("sys_menus.parent", $row_1->menu_id)
                                                   ->orderBy("sys_menus.order", "ASC")
                                                   ->get();

                if(count($query_2) > 0) {
                    $submenu_2  = [];
                    foreach($query_2 as $row_2) {
                        $exist_2  = DB::table("sys_user_menus")->where("user_id", $id)->where("menu_id", $row_2->menu_id)->count();

                        $query_3  = DB::table("sys_menus")->select("sys_menus.*", DB::RAW("sys_menus.id AS menu_id"), DB::RAW("SUBSTRING_INDEX(sys_menus.icon, '|', 1) AS menu_method"), DB::RAW("SUBSTRING_INDEX(sys_menus.icon, '|', -1) AS menu_icon"))
                                                       ->where("sys_menus.status", 1)                                               
                                                       ->where("sys_menus.level", 3)
                                                       ->where("sys_menus.parent", $row_2->menu_id)
                                                       ->orderBy("sys_menus.order", "ASC")
                                                       ->get();

                        if(count($query_3) > 0) {
                            $submenu_3  = [];

                            foreach($query_3 as $row_3) {
                                $exist_3  = DB::table("sys_user_menus")->where("user_id", $id)->where("menu_id", $row_3->menu_id)->count();

                                $submenu_3[]   = [
                                    "menu_id"=>$row_3->menu_id,
                                    "menu_name"=>$row_3->name,
                                    "menu_checklist"=>($exist_3 > 0) ? 1 : 0,
                                    "menu_url"=>$row_3->url,
                                    "menu_icon"=>$row_3->menu_icon,
                                    "menu_method"=>$row_3->menu_method,
                                    "menu_level"=>3,
                                    "menu_parent"=>$row_2->menu_id,
                                ];
                            }

                            $submenu_2[]   = [
                                "menu_id"=>$row_2->menu_id,
                                "menu_name"=>$row_2->name,
                                "menu_checklist"=>($exist_2 > 0) ? 1 : 0,
                                "menu_url"=>$row_2->url,
                                "menu_level"=>2,
                                "menu_parent"=>$row_1->menu_id,
                                "submenu_count_children"=>0,
                                "submenu_count"=>count($query_3),
                                "children"=>$submenu_3
                            ];
                        } else {
                            $submenu_2[]   = [
                                "menu_id"=>$row_2->menu_id,
                                "menu_name"=>$row_2->name,
                                "menu_checklist"=>($exist_2 > 0) ? 1 : 0,
                                "menu_url"=>$row_2->url,
                                "menu_level"=>2,
                                "submenu_count"=>0,
                                "submenu_count_children"=>0,
                                "menu_parent"=>$row_1->menu_id,
                                "children"=>[]
                            ];
                        }
                    }

                    $menu[]   = [
                        "menu_id"=>$row_1->menu_id,
                        "menu_name"=>$row_1->name,
                        "menu_checklist"=>($exist_1 > 0) ? 1 : 0,
                        "menu_url"=>$row_1->url,
                        "menu_icon"=>$row_1->icon,
                        "menu_level"=>1,
                        "submenu_count"=>count($query_2),
                        "submenu_count_children"=>0,
                        "children"=>$submenu_2
                    ];
                } else {
                    $query_3  = DB::table("sys_menus")->select("sys_menus.*", DB::RAW("sys_menus.id AS menu_id"))
                                                   ->where("sys_menus.status", 1)                                               
                                                   ->where("sys_menus.level", 3)
                                                   ->where("sys_menus.parent", $row_1->menu_id)
                                                   ->orderBy("sys_menus.order", "ASC")
                                                   ->get();
                    $submenu_2  = [];
                    $submenu_3  = [];
                    foreach($query_3 as $row_3) {
                        $exist_3       = DB::table("sys_user_menus")->where("user_id", $id)->where("menu_id", $row_3->menu_id)->count();

                        $submenu_3[]   = [
                            "menu_id"=>$row_3->menu_id,
                            "menu_name"=>$row_3->name,
                            "menu_checklist"=>($exist_3 > 0) ? 1 : 0,
                            "menu_url"=>$row_3->url,
                            "menu_icon"=>$row_3->icon,
                            "menu_level"=>3,
                            "menu_parent"=>$row_1->menu_id,
                        ];
                    }

                    $menu[]   = [
                        "menu_id"=>$row_1->menu_id,
                        "menu_name"=>$row_1->name,
                        "menu_checklist"=>($exist_1 > 0) ? 1 : 0,
                        "menu_url"=>$row_1->url,
                        "menu_icon"=>$row_1->icon,
                        "menu_level"=>1,
                        "submenu_count"=>0,
                        "submenu_count_children"=>count($submenu_3),
                        "children"=>$submenu_3
                    ];
                }
            }

            return array("status"=>true, "data"=>$menu);
        } catch (\Exception $e) {
            return array("status"=>false, "error_log"=>$e->getMessage());
        }
    }

    public function updatePrivilege($request) {
        DB::beginTransaction();
        # ------------------------
        try {
            DB::table("sys_user_menus")->where("user_id", $request->id)->delete();
            # ------------------------
            $Unit   = count($request->input("check_menu"));
            # ---------------
            for($i=0; $i<$Unit; $i++) {
                DB::table("sys_user_menus")
                            ->insert([
                                "menu_id"=>$request->check_menu[$i],
                                "user_id"=>$request->id,
                            ]);
            }
            /* ----------
             Logs
            ----------------------- */
                $this->logModel->createLog("SET PRIVILEGE (" . $request->id . ") ", Auth::user()->id, $request);
            # ------------------------
            DB::commit();
            # ------------------------
            return array("status"=>true, "id"=>0);
        } catch (\Exception $e) {
            DB::rollback();
            /* ----------
             Error
            ----------------------- */
            $id   = $this->logModel->createError($e->getMessage(), "SET PRIVILEGE FAILED", "");
            # ---------------
            return array("status"=>false, "id"=>0);
        }
    }

    public function getAccessStatus($menu_url, $user_id) {
        $query  = DB::table("sys_menus")->select("sys_menus.*")
                                        ->join("sys_user_menus", "sys_menus.id", "sys_user_menus.menu_id")
                                        ->where("sys_user_menus.user_id", $user_id)
                                        ->where("sys_menus.url", $menu_url)
                                        ->get();

        return $query;
    }
}
