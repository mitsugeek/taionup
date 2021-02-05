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
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;

use App\Models\LineMessage;
use App\Models\LinePhoto;
use App\Models\LineUser;

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

                        $user = LineUser::where('line_id', $line_id)->first();
                        if(!$user){
                            $user = new LineUser();
                            $user->line_id = $line_id;
                            $user->line_name = $profile['displayName'];
                            $user->save();
                        }

                        $reply_message = $profile['displayName']."さん\n"
                        ."お友達登録ありがとうございます。"
                        ."出勤前に、体温計で体温を測り、\n"
                        ."写真で、体温を取り、\n"
                        ."このトーク画面に投稿をお願い致します。";
                        
                        //$reply_message = "lineID:".$line_id."\n"."displayName:".$profile['displayName'];
                    }

                    break;
                
                //メッセージの受信
                case $event instanceof TextMessage:
                    $line_id = $event->getUserId();
                    $text = $event->getText();
                    $message = new LineMessage();
                    $message->line_id = $line_id;
                    $message->message = $text;
                    $message->save();

                    $reply_token = $event->getReplyToken();
                    $reply_message = $text."(オウム返し)";
                    $bot->replyText($reply_token, $reply_message);
                    return "";
                    break;

                case $event instanceof ImageMessage:
                    $line_id = $event->getUserId();
                    $replyToken = $event->getReplyToken();
                    $contentProvider = $event->getContentProvider();
                    if ($contentProvider->isExternal()) {
                        $bot->replyMessage(
                            $replyToken,
                            new ImageMessageBuilder(
                                $contentProvider->getOriginalContentUrl(),
                                $contentProvider->getPreviewImageUrl()
                            )
                        );
                        return;
                    }
                    
                    $contentId = $event->getMessageId();
                    $image = $bot->getMessageContent($contentId)->getRawBody();
            
                    $tempFilePath = storage_path('app/public/').md5(uniqid(rand(), true))."jpg";
                    $filePath = $tempFilePath . '.jpg';
                    $fh = fopen($filePath, 'x');
                    fwrite($fh, $image);
                    fclose($fh);


                    $filename = basename($filePath);
                    $url = env('APP_URL')
                    .\Illuminate\Support\Facades\Storage::url($filename);
                    $url = str_replace("//","/", $url);

                    $photo = new LinePhoto();
                    $photo->line_id = $line_id;
                    $photo->path = $filePath;
                    $photo->url = $url;
                    $photo->save();


                    logger()->info($url);
                    //$bot->replyMessage($replyToken, new ImageMessageBuilder($url, $url));

                                        
                    $reply_token = $event->getReplyToken();
                    $yes_button = new PostbackTemplateActionBuilder('はい', 'button=1');
                    $no_button = new PostbackTemplateActionBuilder('いいえ', 'button=0');
                    $actions = [$yes_button, $no_button];
                    $button = new ButtonTemplateBuilder('確認', '37.5℃以上ですか？', '', $actions);
                    $button_message = new TemplateMessageBuilder('タイトル', $button);
                    $bot->replyMessage($reply_token, $button_message);

                    return "";
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

                    $postbackdata = $event->getPostbackData();
                    if($postbackdata == "button=1"){
                        $bot->replyText(
                            $event->getReplyToken(),
                            "今日は会社を休みましょう。"
                        );
                    } else if($postbackdata == "button=0"){
                        $bot->replyText(
                            $event->getReplyToken(),
                            "ご回答ありがとうございました。"
                        );
                    }


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
