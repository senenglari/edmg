<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use View;
use Auth;
use Validator;
use App\Model\WikiModel;

class WikiController extends Controller
{
    public function what($id) {
    	$qWiki 	= new WikiModel;
    	# -----------------
    	$data["wiki"] 	= $qWiki->getWikiByMenuId($id);
    	# -----------------
    	return view("wiki.what", $data);
    }
}
