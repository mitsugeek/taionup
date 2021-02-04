<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\SignatureValidator;
use LINE\LINEBot\Event\FollowEvent;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Event\MessageEvent\LocationMessage;
use LINE\LINEBot\Event\PostbackEvent;
use LINE\LINEBot\Event\UnfollowEvent;

class LineWebhookController extends Controller
{
    public function webhook(Request $request)
    {
        $httpClient = new CurlHTTPClient(env('LINE_ACCESS_TOKEN'));
        $bot = new LINEBot($httpClient, ['channelSecret' => env('LINE_CHANNEL_SECRET')]);

        $signature = $_SERVER['HTTP_'.HTTPHeader::LINE_SIGNATURE];
        if (!SignatureValidator::validateSignature($request->getContent(), env('LINE_CHANNEL_SECRET'), $signature)) {
            abort(400);
        }

        $events = $bot->parseEventRequest($request->getContent(), $signature);
        foreach ($events as $event) {
            $reply_token = $event->getReplyToken();
            $reply_message = 'その操作はサポートしてません。.[' . get_class($event) . '][' . $event->getType() . ']';

            switch (true){
                
                //友達登録＆ブロック解除
                case $event instanceof FollowEvent:
                    break;
                
                //メッセージの受信
                case $event instanceof TextMessage:
                    break;
                
                //位置情報の受信
                case $event instanceof LocationMessage:
                    break;

                //選択肢とか選んだ時に受信するイベント
                case $event instanceof PostbackEvent:
                    break;

                //ブロック
                case $event instanceof UnfollowEvent:
                    break;

                default:
                    $body = $event->getEventBody();
                    logger()->warning('Unknown event. ['. get_class($event) . ']', compact('body'));
            }
            
            $bot->replyText($reply_token, $reply_message);
        }


        return "";
    }
}
