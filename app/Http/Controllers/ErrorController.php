<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use View;
use DB;
use Auth;
use Validator;
use App\Model\ErrorModel;
use App\Model\Master\UserModel;

class ErrorController extends Controller
{
    public function getError($id) {
    	$qUser 			= new UserModel;
    	# --------------
    	$data["error"] 	= ErrorModel::find($id);
    	$data["user"]	= $qUser->getProfile($data["error"]->created_by)->first();
    	# --------------
    	return view("default.error", $data);
    }

    public function getErrorPage() {
        return view("default.error_page");
    }
}
