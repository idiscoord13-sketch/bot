<?php


//add_filter('chat_id',function (){});
//add_filter('message_start',function (){});
//add_filter('keyboard_start',function (){});
//add_filter('reply_start',function (){});
//add_filter('mode_start',function (){});

add_filter('message_join_channel', function() {
    $message = '💢 کاربر گرامی ' . "\n" . ' برای استفاده از بازی آنلاین لازم است در کانال رسمی ما عضو شوید و سپس مجددا امتحان کنید .' . "\n";
    $channel = GetChat(CHNNEL_ID);
    $message .= '💡 @' . $channel->username . "\n";
    $message .= "<u>عضویت در کانال اجباری است .</u>" . "\n \n";
    $message .= '💯 آپدیت ها و تغییرات بازی ' . "\n";
    $message .= '🎭 اطلاع از نقش های جدید' . "\n";
    $message .= '💰 شرکت در چالش های سکه رایگان' . "\n";
    $message .= '🔖 توضیحات نقش های مختلف بازی ' . "\n";
    $message .= '💡 @' . $channel->username;
    return $message;
});