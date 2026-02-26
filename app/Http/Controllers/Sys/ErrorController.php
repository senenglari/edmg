<?php

namespace App\Http\Controllers\Sys;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ErrorController extends Controller
{
    public function pageNotFound() { 
        return view('error.404'); 
    }
}
