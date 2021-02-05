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

    public function getUserAPI(Request $request)
    {
        $token = $request->token;
        $curl=curl_init("https://api.line.me/v2/profile");
        curl_setopt($curl,CURLOPT_POST, TRUE);
        curl_setopt($curl,CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, FALSE);  // オレオレ証明書対策
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);
        $output= curl_exec($curl);
        $ret = json_decode($output, true);

        $request->session()->put('userId', $ret["userId"]);
        $request->session()->put('displayName', $ret["displayName"]);
        $request->session()->put('statusMessage', $ret["statusMessage"]);
        $request->session()->put('pictureUrl', $ret["pictureUrl"]);

        return $ret;
    }
}
