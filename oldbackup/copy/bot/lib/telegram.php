<?php

defined('BASE_DIR') || die('NO ACCESS');

require SRC_DIR . '/Telegram.php';

if ( !function_exists('str_contains') )
{
    function str_contains( $haystack, $needle )
    {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}

/**
 * @param $method
 * @param array $data
 * @param string $token
 * @return false|array
 */
function bot( $method, array $data = array (), string $token = '' )
{
    if ( !is_array($data) ) return false;
    global $TOKEN_API;

    if ( empty($token) )
    {
        if ( empty($TOKEN_API) )
        {
            $token = TOKEN_API;
        }
        else
        {
            $token = $TOKEN_API;
        }
    }


    global $link, $token_bot;
    if ( isset($data['chat_id']) )
    {

        $user_id         = $data['chat_id'];
        $is_user_in_game = (bool) $link->get_row("SELECT * FROM `user_game` WHERE `user_id` = {$user_id}");
        if ( $is_user_in_game )
        {

            $game_user = $link->get_row("SELECT * FROM `user_game` WHERE `user_id` = {$user_id}");

            if ( $game_user->bot != $BOT_ID )
            {

                $token = $token_bot[$game_user->bot];

            }

        }

    }

    $token = apply_filters('filter_token', $token);


    $url = "https://api.telegram.org/bot" . $token . "/" . $method;
    $ch  = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    curl_close($ch);
    // file_put_contents('error.json', $res);
    return json_decode($res)->result;
}

/**
 * @param $message
 * @return bool
 */
function is_admin_in_message( $message ) : bool
{

    global $link;
    $user = $link->get_row("SELECT * FROM `users` WHERE `user_id` = " . ADMIN_LOG);

    if ( isset($user) )
    {

        if ( preg_match("/$user->name/", $message) )
        {

            return true;

        }

    }

    return false;
}


function SendMessage( $chatID, $text, $keyboard = null, $reply = null, $mode = 'MarkDown' )
{
    if ( !is_string($text) ) return false;
    return bot('SendMessage', [
        'chat_id'             => $chatID,
        'text'                => apply_filters('send_massage_text', $text),
        'parse_mode'          => $mode,
        'reply_to_message_id' => $reply,
        'reply_markup'        => is_null($keyboard) ? null : ( $keyboard ),
        //        'protect_content' => is_admin_in_message($text)
    ]);
}

function EditMessageText( $chatID, $message_id, $text, $keyboard = null, $inline_message_id = null, $mode = 'html' )
{
    return bot('EditMessageText', [
        'chat_id'           => $chatID,
        'message_id'        => $message_id,
        'inline_message_id' => $inline_message_id,
        'disable_web_page_preview' => true,
        'text'              => $text,
        'parse_mode'        => $mode,
        'reply_markup'      => is_null($keyboard) ? null : ( $keyboard ),
    ]);
}

function EditKeyboard( $chatID, $message_id, $keyboard )
{
    return bot('EditMessageReplyMarkup', [
        'chat_id'      => $chatID,
        'message_id'   => $message_id,
        'reply_markup' => $keyboard
    ]);
}

function AnswerCallbackQuery( $callback_query_id, $text, $show_alert = false )
{
    return bot('AnswerCallbackQuery', [
        'callback_query_id' => $callback_query_id,
        'text'              => $text,
        'show_alert'        => $show_alert
    ]);
}

function Forward( $chatID, $from_id, $massege_id )
{
    return bot('ForwardMessage', [
        'chat_id'      => $chatID,
        'from_chat_id' => $from_id,
        'message_id'   => $massege_id
    ]);
}

function SendPhoto( $chatID, $photo, $caption = null, $keyboard = null, $messageID = null, $mode = "html" )
{
    return bot('SendPhoto', [
        'chat_id'             => $chatID,
        'photo'               => $photo,
        'caption'             => $caption,
        'parse_mode'          => $mode,
        'reply_to_message_id' => $messageID,
        'reply_markup'        => is_null($keyboard) ? null : json_encode($keyboard)
    ]);
}

function setChatPhoto( $chatID, $photo )
{
    return bot('setChatPhoto', [
        'chat_id' => $chatID,
        'photo'   => $photo
    ]);
}

function deleteChatPhoto( $chatID )
{
    return bot('deleteChatPhoto', [
        'chat_id' => $chatID
    ]);
}

function SendAudio( $chatID, $audio, $caption = null, $sazande = null, $title = null )
{
    return bot('SendAudio', [
        'chat_id'   => $chatID,
        'audio'     => $audio,
        'caption'   => $caption,
        'performer' => $sazande,
        'title'     => $title
    ]);
}

function SendDocument( $chatID, $document, $caption = null )
{
    return bot('SendDocument', [
        'chat_id'  => $chatID,
        'document' => $document,
        'caption'  => $caption
    ]);
}

function SendSticker( $chatID, $sticker )
{
    return bot('SendSticker', [
        'chat_id' => $chatID,
        'sticker' => $sticker
    ]);
}

function getStickerSet( $name )
{
    return bot('getStickerSet', [
        'name' => $name
    ]);
}

function SendVideo( $chatID, $video, $caption = null, $duration = null )
{
    return bot('SendVideo', [
        'chat_id'  => $chatID,
        'video'    => $video,
        'caption'  => $caption,
        'duration' => $duration
    ]);
}

function SendVoice( $chatID, $voice, $caption = null )
{
    return bot('SendVoice', [
        'chat_id' => $chatID,
        'voice'   => $voice,
        'caption' => $caption
    ]);
}

function SendContact( $chatID, $first_name, $phone_number )
{
    return bot('SendContact', [
        'chat_id'      => $chatID,
        'first_name'   => $first_name,
        'phone_number' => $phone_number
    ]);
}

function GetProfile( $from_id )
{
    $get     = file_get_contents('https://api.telegram.org/bot' . TOKEN_API . '/getUserProfilePhotos?user_id=' . $from_id);
    $decode  = json_decode($get, true);
    $result  = $decode['result'];
    $profile = $result['photos'][0][0]['file_id'];
    return $profile;
}

/**
 * @param $chatID
 * @return false|\helper\User
 */
function GetChat( $chatID )
{
    $get = bot('GetChat', [ 'chat_id' => $chatID ]);
    return $get;
}

function GetMe()
{
    $get = bot('GetMe');
    return $get;
}

function SendAction( $chatID, $action )
{
    $mode = array (
        'typing',
        'upload_photo',
        'record_video',
        'upload_document',
        'find_location',
        'record_video_note',
        'upload_video_note'
    );
    if ( in_array($action, $mode) ) return bot('sendchataction', [
        'chat_id' => $chatID,
        'action'  => $action
    ]);
}

function LeaveChat( $chatID )
{
    return bot('LeaveChat', [
        'chat_id' => $chatID
    ]);
}

function DeleteMessage( $chatID, $messageID )
{
    return bot('deleteMessage', [
        'chat_id'    => $chatID,
        'message_id' => $messageID
    ]);
}

function KickChatMember( $chatID, $fromID, $until_date = null )
{
    return bot('kickChatMember', [
        'chat_id'    => $chatID,
        'user_id'    => $fromID,
        'until_date' => $until_date
    ]);
}

function unbanChatMember( $chatID, $fromID )
{
    return bot('unbanChatMember', [
        'chat_id' => $chatID,
        'user_id' => $fromID
    ]);
}

function getChatMember( $chatID, $fromID )
{
    return bot('getChatMember', [
        'chat_id' => $chatID,
        'user_id' => $fromID
    ]);
}

function PinChatMessage( $chatID, $messageID )
{
    return bot('pinChatMessage', [
        'chat_id'    => $chatID,
        'message_id' => $messageID
    ]);
}

function UnpinChatMessage( $chatID )
{
    return bot('unpinChatMessage', [
        'chat_id' => $chatID,
    ]);
}

function SetChatTitle( $chatID, $Name )
{
    return bot('setChatTitle', [
        'chat_id' => $chatID,
        'title'   => $Name
    ]);
}

function setChatDescription( $chatID, $Description )
{
    return bot('setChatTitle', [
        'chat_id'     => $chatID,
        'description' => $Description
    ]);
}

function getChatAdministrators( $chatID )
{
    return bot('getChatAdministrators', [
        'chat_id' => $chatID,
    ]);
}

function getChatMembersCount( $chatID )
{
    return bot('getChatMembersCount', [
        'chat_id' => $chatID,
    ]);
}

function sendPoll( $chatID, $question, array $options, $notification = null, $messageID = null, $keyboard = null )
{
    return bot('sendPoll', [
        'chat_id'              => $chatID,
        'question'             => $question,
        'options'              => json_encode($options),
        'disable_notification' => $notification,
        'reply_to_message_id'  => $messageID,
        'reply_markup'         => is_null($keyboard) ? null : json_encode($keyboard)
    ]);
}

function stopPoll( $chatID, $messageID, $keyboard = null )
{
    return bot('stopPoll', [
        'chat_id'      => $chatID,
        'message_id'   => $messageID,
        'reply_markup' => is_null($keyboard) ? null : json_encode($keyboard)
    ]);
}

function answerInlineQuery( $answerInlineQuery = array () )
{
    $x = bot("answerInlineQuery", ( $answerInlineQuery ));
    // file_put_contents('error.json', $x);
}

function restrictChatMember( $chat_id, $user_id, $until_date, array $can )
{
    return bot('restrictChatMember', [
        'chat_id'                   => $chat_id,
        'user_id'                   => $user_id,
        'until_date'                => $until_date,
        'can_send_messages'         => $can['can_send_messages'],
        'can_send_media_messages'   => $can['can_send_media_messages'],
        'can_send_other_messages'   => $can['can_send_other_messages'],
        'can_add_web_page_previews' => $can['can_add_web_page_previews']
    ]);
}

function promoteChatMember( $chat_id, $user_id, array $can )
{
    return bot('promoteChatMember', [
        'chat_id'              => $chat_id,
        'user_id'              => $user_id,
        'can_change_info'      => $can['can_change_info'],
        'can_post_messages'    => $can['can_post_messages'],
        'can_edit_messages'    => $can['can_edit_messages'],
        'can_delete_messages'  => $can['can_delete_messages'],
        'can_invite_users'     => $can['can_invite_users'],
        'can_restrict_members' => $can['can_restrict_members'],
        'can_promote_members'  => $can['can_promote_members'],
    ]);
}

function getLink( $chat_id )
{
    $linkToRet = bot('exportChatInviteLink', [
        'chat_id' => $chat_id
    ]);
    return empty($linkToRet) ? null : $linkToRet;
}

function editMessageMedia( $chatID, $messageID, $inlineMessageID, $Media, $keyboard = null )
{
    return bot("editMessageMedia", [
        "chat_id"           => $chatID,
        "message_id"        => $messageID,
        "inline_message_id" => $inlineMessageID,
        "media"             => json_encode($Media),
        "reply_markup"      => is_null($keyboard) ? null : json_encode($keyboard)
    ]);
}

function getFile( $fileID )
{
    $url = bot("getFile", [
        "file_id" => $fileID
    ])->file_path;
    if ( !empty($url) )
    {
        return "https://api.telegram.org/file/bot" . TOKEN_API . "/" . $url;
    }
    return null;
}

function getWebHook( $token )
{
    $json = json_decode(file_get_contents("https://api.telegram.org/bot" . $token . "/getWebhookInfo"));
    if ( $json->ok ) return $json->result->url;
    else
        return false;
}

function setWebHook( $token, $url )
{
    $json = json_decode(file_get_contents("https://api.telegram.org/bot" . $token . "/setwebhook?url=$url"));
    return $json->result;
}

function deleteWebHook( $token )
{
    $json = json_decode(file_get_contents("https://api.telegram.org/bot" . $token . "/deleteWebhook"));
    if ( $json->ok ) return $json->result->url;
    else
        return false;
}

// Custom Code

$filename = 'chat_data.json';

function readData($filename) {
    if (file_exists($filename)) {
        return json_decode(file_get_contents($filename), true);
    }
    return [];
}

function checkChatId($filename, $chatid) {
    $data = readData($filename);

    if (isset($data[$chatid])) {
        return true;
    }
    
    return false;
}

function saveData($filename, $data) {
    file_put_contents($filename, json_encode($data));
}

function getMessageChatId($filename, $chatid) {
    $data = readData($filename);

    if (isset($data[$chatid])) {
        return $data[$chatid]['messageid'];
    } else {
        return "شناسه پیام برای این شناسه چت یافت نشد.";
    }
}

function updateUserData($filename, $chatid, $messageid) {
    $data = readData($filename);

    $data[$chatid] = ['messageid' => $messageid];
    
    saveData($filename, $data);
}

function clearUserData($filename, $chatid) {
    $data = readData($filename);

    if (isset($data[$chatid])) {
        unset($data[$chatid]);
        saveData($filename, $data);
    }
}