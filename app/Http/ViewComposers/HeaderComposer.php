<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use DB;
use Auth;

class HeaderComposer
{
    public function compose(View $view) {
        $data["notification"]  = array();
        # ---------------
        $view->with('Header', $data);
    }
}
