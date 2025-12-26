<?php

function id()
{
    global $chat_id;
    return $chat_id;
}


if (isset($text) && $text == '/id') {
    SendMessage(id(), id());
    exit();
}

