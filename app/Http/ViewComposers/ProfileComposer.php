<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use DB;
use Auth;
use App\Model\UserManagement\UserModel;

class ProfileComposer
{
    public function compose(View $view) {
        $qUser          = new UserModel();
        # ---------------
        $result 		= $qUser->getProfile(Auth::user()->id)->first();
        # ---------------
        $view->with('Profile', $result);
    }
}
