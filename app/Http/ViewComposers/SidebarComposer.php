<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use DB;
use Auth;
use App\Model\UserManagement\MenuModel;

class SidebarComposer
{
    public function compose(View $view) {
        $Menu           = array();
        $qMenus         = new MenuModel;
        # ---------------
        $qParent        = $qMenus->getMenu(Auth::user()->id, 1);

        foreach($qParent as $rowParent) {
            $qChild         = $qMenus->getMenu(Auth::user()->id, 2, $rowParent->id);
            $Count          = count($qChild);
            # ---------------
            array_push($Menu, array("id"=>$rowParent->id, "name"=>$rowParent->name, "level"=>1, "url"=>$rowParent->url, "parent"=>$rowParent->name, "icon"=>$rowParent->icon, "child"=>$Count));
            # ---------------
            foreach($qChild as $rowChild) {
                array_push($Menu, array("id"=>$rowChild->id, "name"=>$rowChild->name, "level"=>2, "url"=>$rowChild->url, "parent"=>$rowParent->name, "icon"=>"fa-circle-o", "child"=>"0"));
            }
        }

        $view->with('Menu', $Menu);
    }
}
