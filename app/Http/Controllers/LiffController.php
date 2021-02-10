<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LinePhoto;
use App\Models\LineUser;

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
        $request->session()->put('displayName', $ret["displayName"] ?? "");
        $request->session()->put('statusMessage', $ret["statusMessage"] ?? "");
        $request->session()->put('pictureUrl', $ret["pictureUrl"] ?? "");

        //レスポンス
        return $ret;
    }

    public function TaionList(Request $request)
    {
        $userId = $request->session()->get('userId');
        if(!$userId){
            abort(403);
        }

        $query = LinePhoto::query();
        $query->select(
             "line_users.line_name"
            ,"line_users.name_sei"
            ,"line_users.name_mei"
            ,"line_photos.line_id"
            ,"line_photos.url"
            ,"line_photos.created_at"
        );
        $query->join('line_users','line_users.line_id','=','line_photos.line_id');
        $list = $query->orderBy('line_photos.id','desc')->paginate(10);
        return view('liff.taionlist',compact('list'));

    }

    public function userlist(Request $request)
    {
        $userId = $request->session()->get('userId');
        if(!$userId){
            abort(403);
        }

        $list = LineUser::all()->paginate(10);
        return view('liff.userlist', 'list');
    }

    public function userUpdate(Request $request)
    {
        var_dump($request);
        exit();
    }
}
