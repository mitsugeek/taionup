<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LiffController extends Controller
{
    //
    public function home(Request $request)
    {
        return view("liff.home",[]);
    }
}
