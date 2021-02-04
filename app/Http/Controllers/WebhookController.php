<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\SignatureValidator;
use LINE\LINEBot\Event\FollowEvent;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Event\MessageEvent\ImageMessage;
use LINE\LINEBot\Event\MessageEvent\LocationMessage;
use LINE\LINEBot\Event\MessageEvent\FileMessage;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;


use LINE\LINEBot\Event\PostbackEvent;
use LINE\LINEBot\Event\UnfollowEvent;

class WebhookController extends Controller
{
    public function webhook(Request $request)
    {
        $httpClient = new CurlHTTPClient(env('LINE_ACCESS_TOKEN'));
        $bot = new LINEBot($httpClient, ['channelSecret' => env('LINE_CHANNEL_SECRET')]);

        $signature = $_SERVER['HTTP_'.HTTPHeader::LINE_SIGNATURE];


        logger()->info("getContent");
        logger()->info(print_r($request->getContent()));
        if (!SignatureValidator::validateSignature($request->getContent(), env('LINE_CHANNEL_SECRET'), $signature)) {
            logger()->warning("abort:400");
            abort(400);
        }

        $events = $bot->parseEventRequest($request->getContent(), $signature);
        foreach ($events as $event) {
            $reply_token = $event->getReplyToken();
            $reply_message = 'その操作はサポートしてません。.[' . get_class($event) . '][' . $event->getType() . ']';

            switch (true){
                
                //友達登録＆ブロック解除
                case $event instanceof FollowEvent:

                    $line_id = $event->getUserId();
                    $rsp = $bot->getProfile($line_id);
                    if (!$rsp->isSucceeded()) {
                        logger()->info('failed to get profile. skip processing.');
                        $reply_message = "failed to get profile. skip processing.";
                    } else {
                        $profile = $rsp->getJSONDecodedBody();
                        $reply_message = "lineID:".$line_id."\n"."displayName:".$profile['displayName'];
                    }

                    break;
                
                //メッセージの受信
                case $event instanceof TextMessage:
                    $reply_message = $event->getText()."(オウム返し)";
                    break;

                case $event instanceof ImageMessage:
                    $reply_message = "画像を送りましたね。";

                    $replyToken = $this->imageMessage->getReplyToken();
                    $contentProvider = $this->imageMessage->getContentProvider();
                    if ($contentProvider->isExternal()) {
                        $this->bot->replyMessage(
                            $replyToken,
                            new ImageMessageBuilder(
                                $contentProvider->getOriginalContentUrl(),
                                $contentProvider->getPreviewImageUrl()
                            )
                        );
                        return;
                    }
                    
                    $contentId = $this->imageMessage->getMessageId();
                    $image = $this->bot->getMessageContent($contentId)->getRawBody();
            
                    $path = storage_path('app');

                    $tempFilePath = tempnam(storage_path('app/public/'), "IMG_");

                    $filePath = $tempFilePath . '.jpg';
                    $fh = fopen($filePath, 'x');
                    fwrite($fh, $image);
                    fclose($fh);


                    $filename = basename($filePath);
                    $url = env('APP_URL')
                    .\Illuminate\Support\Facades\Storage::url('app/public/'.$filename);
                    $this->bot->replyMessage($replyToken, new ImageMessageBuilder($url, $url));

                    break;

                case $event instanceof FileMessage:
                    $reply_message = "ファイルを送りましたね。";
                    break;
               /* 
                case $event instanceof AccountLinkEvent:

                    break;
*/
                //位置情報の受信
                case $event instanceof LocationMessage:
                    $reply_message = "あなたの現在地：\n".$event->getAddress();
                    break;

                //選択肢とか選んだ時に受信するイベント
                case $event instanceof PostbackEvent:
                    return "";
                    break;

                //ブロック
                case $event instanceof UnfollowEvent:
                    return "";
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
