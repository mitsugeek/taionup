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
        //パラメータの取得
        if($_SERVER["REQUEST_METHOD"] != "POST") { echo "no post!"; return ""; }
        $input = file_get_contents("php://input");
        if(empty($input)) { echo "no input!";  return "";}
        $data = json_decode($input, true);
        if(!isset($data['token'])){ echo "no token!";  return ""; }
        $token = $data['token'];

        //API実行
        $curl=curl_init("https://api.line.me/v2/profile");
        curl_setopt($curl,CURLOPT_POST, TRUE);
        curl_setopt($curl,CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, FALSE);  // オレオレ証明書対策
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);
        $output= curl_exec($curl);
        $ret = json_decode($output, true);

        //結果を格納
        $request->session()->put('userId', $ret["userId"]);
        $request->session()->put('displayName', $ret["displayName"]);
        $request->session()->put('statusMessage', $ret["statusMessage"]);
        $request->session()->put('pictureUrl', $ret["pictureUrl"]);

        //レスポンス
        return $ret;
    }
}
