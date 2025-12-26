<?php

defined('BASE_DIR') || die('NO ACCESS');

function Message()
{
    global $chat_id, $chatid, $message;
    $chat_id = $chat_id ?? $chatid;
    SendMessage($chat_id, $message);
}

function html()
{
    global $chat_id, $chatid, $message;
    $chat_id = $chat_id ?? $chatid;
    SendMessage($chat_id, $message, null, null, 'html');
}

function warning_message($text)
{
    global $chat_id, $chatid;
    $chat_id = $chat_id ?? $chatid;
    $message = '⚠️خطا، ' . $text;
    SendMessage($chat_id, $message, null, null, 'html');
}