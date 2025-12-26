<?php


/**
 * @return false|string[]
 */
function get_admins()
{
    return explode(',', file_get_contents(BASE_DIR . '/admins.txt'));
}


add_filter('admin_menu', function () {
    global $telegram;
    $array_filter = array_filter([
        apply_filters('admin_before_keyboard'),
        /*[
            $telegram->buildKeyboardButton('👥 مدیریت مدیران 👥')
        ],
        apply_filters('admin_admins_keyboard'),
        [
            $telegram->buildKeyboardButton('📩 ارسال پیام همگانی 📩'),
            $telegram->buildKeyboardButton('📨 فوروارد پیام همگانی 📨')
        ],*/

        [
            $telegram->buildKeyboardButton('🔍 بررسی کاربر'),
        ],
        [
            $telegram->buildKeyboardButton('📯 تعریف لیگ'),
            $telegram->buildKeyboardButton('📊 آمارگیری'),
            $telegram->buildKeyboardButton('🌐 سرور'),
        ],
        [
            $telegram->buildKeyboardButton('💰 اهدای سکه'),
            $telegram->buildKeyboardButton('⭐️ اهدای امتیاز'),
            $telegram->buildKeyboardButton('⏱ ساخت چالش'),
        ],
        [
            $telegram->buildKeyboardButton('♻️ بازیابی اکانت'),
        ],
        apply_filters('admin_after_keyboard'),
    ]);
    return array_values($array_filter);
});

add_action('panel_admin', function () {
    /** @var \helper\Message $message  */
    /** @var \helper\Update $update  */
    global $link, $chat_id, $text, $telegram, $message, $forward_id, $forward, $message_id, $update, $Data, $chatid, $messageid, $type, $dataid;
    if (is_admin()) {
        try {
            if (ADMIN_LOG == $chat_id || ADMIN_ID == $chat_id) {
                $data = explode(' ', $text);
                switch ($data[0]) {
                    case '/break':
                        file_put_contents(BASE_DIR . '/break.txt', $data[1] . ' ' . $data[2]);
                        $message = '✅ ساعت بریک ربات فعال/تمدید شد.';
                        Message();
                        if (isset($data[3]) && $data[3] == 1) {
                            $link->update("server", [
                                'status' => 'closed',
                                'count' => 0
                            ]);
                            $users = $link->get_result("SELECT * FROM `user_game`");
                            foreach ($users as $user) {
                                $link->where('user_id', $user->user_id)->update("users", [
                                    'status' => null
                                ]);
                            }
                            $link->delete("user_game");
                        }
                        break;
                    case 'سرور':
                    case '/server':

                        if (is_numeric($data[1])) {

                            if (!class_exists('library\Server')) {

                                include BASE_DIR . "/library/Server.php";

                            }

                            $server = new \library\Server($data[1]);
                            $numbser_to_word = new NumberToWord();
                            $message = '♨️ اطلاعات سرور ' . "<u>[[server]]</u>" . ' :' . "\n \n";
                            $message .= '🔰 وضعیت سرور:  [[status]]' . "\n";
                            $message .= '🔰 تعداد اعضا سرور: [[server_count]] نفر' . "\n";
                            $message .= '🔰 تعداد اعضا داخل بازی: [[game_count]] نفر' . "\n";
                            $message .= '🔰 روز بازی: [[day]]' . "\n";
                            $message .= '🔰 نوع بازی: بازی [[name_game]]' . "\n";
                            $message .= '🔰 وضعیت بازی: [[status_game]]' . "\n";
                            $message .= '🔰 سرور ساخته شده در ربات: شماره [[bot]]' . "\n";
                            $message .= '🔰 نوع سرور: [[type]]' . "\n";
                            if ($server->getUserId() !== null) {
                                $message .= '🔰 کاربر سازنده: <code>[[user_id]]</code>' . "\n";
                            }
                            $message .= '🔰 کرون متصل: شماره [[cron]]' . "\n";
                            $message .= '🔰 تاریخ ساخت سرور: ' . "\n";
                            $message .= '[[date]]' . "\n \n";
                            $message .= '⚙️ نوع عملیات را انتخاب کنید:';
                            $telegram->sendMessage([
                                'chat_id' => $chat_id,
                                'text' => __replace__($message, [
                                    '[[server]]' => $server->getId(),
                                    '[[status]]' => $server->toStringStatusServer(),
                                    '[[server_count]]' => $server->count,
                                    '[[game_count]]' => $server->count(),
                                    '[[day]]' => $numbser_to_word->numberToWords($server->day() ?? 0),
                                    '[[name_game]]' => $server->get_league()->icon,
                                    '[[status_game]]' => $server->toStringStatusGame(),
                                    '[[bot]]' => $numbser_to_word->NumbersToWord($server->bot + 1),
                                    '[[type]]' => $server->type == 'public' ? 'آنلاین' : 'دوستانه',
                                    '[[user_id]]' => $server->getUserId(),
                                    '[[cron]]' => $numbser_to_word->NumbersToWord($server->cron),
                                    '[[date]]' => tr_num(jdate('Y-m-d H:i:s', strtotime($server->created_at))),
                                ]),
                                'reply_markup' => $telegram->buildInlineKeyBoard([
                                    [
                                        $telegram->buildInlineKeyboardButton('🔄 بروزرسانی', '', 'refresh_data_server-' . $server->getId()),
                                    ],
                                    [
                                        $telegram->buildInlineKeyboardButton('🗑 بیرون انداختن کاربر', '', 'logout_user_server-' . $server->getId()),
                                        $telegram->buildInlineKeyboardButton('❌ بستن سرور', '', 'close_server-' . $server->getId()),
                                    ],
                                    [
                                        $telegram->buildInlineKeyboardButton('🌐 اعضای داخل سرور', '', 'users_server-' . $server->getId()),
                                        $telegram->buildInlineKeyboardButton('🔦 نقش اعضا بازی', '', 'role_users_server-' . $server->getId()),
                                    ],
                                    [
                                        $telegram->buildInlineKeyboardButton('🔧 سرور مشکل دارد؟', '', 'problems_server-' . $server->getId())
                                    ]
                                ]),
                                'parse_mode' => 'html'
                            ]);

                        }

                        break;
                    case '/name':
                        update_user([
                            'name' => $data[1]
                        ], $chat_id);
                        $message = '✅ نام مستعار شما به « [[name]] » تغییر یافت .' . "\n \n";
                        $message .= 'منوی اصلی 👇';
                        SendMessage($chat_id, __replace__($message, [
                            '[[name]]' => trim(remove_emoji($data[1]))
                        ]));
                        break;
                }
            }

            if ($type == 'private' && isset($message->from)) {
                switch ($text) {
                    case '🏠 برگشت به منو اصلی':
                    case '/start':
                        $message = '🖲 س...لام ادمین گرامی به پنل مدیریت خود خوش آمدید..' . "\n";
                        $message .= '⌨️ با استفاده از منو زیر میتوانید از پنل استفاده کنید.';
                        SendMessage($chat_id, $message, $telegram->buildKeyBoard(
                            apply_filters('admin_menu')
                        ));
                        update_status('');
                        break;
                    case '🚫 بن کردن کاربر':
                        $message = '☑️ ایدی عددی کاربر مورد نظر خود را ارسال کنید.';
                        SendMessage($chat_id, $message);
                        update_status('add_ban');
                        break;
                    case '📛 ان بن کردن کاربر':
                        $message = '☑️ ایدی عددی کاربر مورد نظر خود را ارسال کنید.';
                        SendMessage($chat_id, $message);
                        update_status('delete_ban');
                        break;
                    case '👥 مدیریت مدیران 👥':
                        $message = '⚙️ به بخش مدیریت مدیران خوش آمدید.' . "\n \n";
                        $message .= '⚒نوع عملیات خود را انتخاب کنید.';
                        SendMessage($chat_id, $message, $telegram->buildKeyBoard([
                            [
                                $telegram->buildKeyboardButton('➕ اضافه کردن مدیر'),
                                $telegram->buildKeyboardButton('➖ حذف یک مدیر'),
                            ],
                            [
                                $telegram->buildKeyboardButton('👥 لیست مدیران'),
                            ],
                            [
                                $telegram->buildKeyboardButton('🏠 برگشت به منو اصلی'),
                            ]
                        ]));
                        break;
                    case '➕ اضافه کردن مدیر':
                        $message = '☑️ ایدی عددی کاربر مورد نظر خود را ارسال کنید.';
                        SendMessage($chat_id, $message);
                        update_status('add_admin');
                        break;
                    case '➖ حذف یک مدیر':
                        $message = '🔘 ایدی عددی کاربر مورد نظر خود را ارسال کنید.';
                        SendMessage($chat_id, $message);
                        update_status('delete_admin');
                        break;
                    case '👥 لیست مدیران':
                        if (file_exists(BASE_DIR . '/admins.txt')) {
                            $admin = file_get_contents(BASE_DIR . '/admins.txt');
                            $admins = explode(',', $admin);
                            $message = '👥 لیست مدیران:' . "\n";
                            foreach ($admins as $id => $user_id) {
                                $user = GetChat($user_id);
                                if (isset($user->first_name))
                                    $message .= $id + 1 . '- ' . '<a href="tg://user?id=' . $user_id . '">' . $user_id . '</a>' . "\n \n";
                            }
                        } else {
                            $message = '🚫 هیچ ادمینی وجود ندارد.';
                        }
                        SendMessage($chat_id, $message, null, null, 'html');
                        break;
                    case '📩 ارسال پیام همگانی 📩':
                        $message = "⚙️ پیامی که میخواهید به تمامی کاربرانتان ارسال شود را ارسال کنید." . "\n \n";
                        $message .= '⛔️ توجه داشته باشید به محض ارسال پیام ربات شروع به ارسال پیام به کاربران میکند❗️';
                        SendMessage($chat_id, $message, $telegram->buildKeyBoard([
                            [
                                $telegram->buildKeyboardButton('🏠 برگشت به منو اصلی')
                            ]
                        ]));
                        update_status('send_message');
                        break;
                    case '📨 فوروارد پیام همگانی 📨':
                        $message = "⚙️ پیامی که میخواهید به تمامی کاربرانتان فوروارد شود را ارسال کنید." . "\n \n";
                        $message .= '⛔️ توجه داشته باشید به محض ارسال پیام ربات شروع به ارسال پیام به کاربران میکند❗️';
                        SendMessage($chat_id, $message, $telegram->buildKeyBoard([
                            [
                                $telegram->buildKeyboardButton('🏠 برگشت به منو اصلی')
                            ]
                        ]));
                        update_status('forward_message');
                        break;
                    case '📯 تعریف لیگ':
                        $message = '💢 لطفا ایموجی لیگ جدید را ارسال کنید.';
                        SendMessage($chat_id, $message, $telegram->buildKeyBoard([
                            [
                                $telegram->buildKeyboardButton('🏠 برگشت به منو اصلی')
                            ]
                        ]));
                        update_status('get_name_league');
                        break;
                    case '💰 اهدای سکه':
                        $message = '💢 لطفا آیدی عددی یا یک پیام فوروارد شده از کاربر مورد نظر خورد را ارسال کنید.';
                        SendMessage($chat_id, $message, $telegram->buildKeyBoard([
                            [
                                $telegram->buildKeyboardButton('🏠 برگشت به منو اصلی')
                            ]
                        ]));
                        update_status('get_user_add_coin');
                        break;
                    case '⭐️ اهدای امتیاز':
                        $message = '💢 لطفا آیدی عددی یا یک پیام فوروارد شده از کاربر مورد نظر خورد را ارسال کنید.';
                        SendMessage($chat_id, $message, $telegram->buildKeyBoard([
                            [
                                $telegram->buildKeyboardButton('🏠 برگشت به منو اصلی')
                            ]
                        ]));
                        update_status('get_user_add_point');
                        break;
                    case '⏱ ساخت چالش':
                        $message = '🔻 نوع عملیات خود را انتخاب کنید.';
                        SendMessage($chat_id, $message, $telegram->buildKeyBoard([
                            [
                                $telegram->buildKeyboardButton('☑️ ساخت کد'),
                                $telegram->buildKeyboardButton('♨️ آزاد کردن لیگ')
                            ],
                            [
                                $telegram->buildKeyboardButton('🏠 برگشت به منو اصلی')
                            ]
                        ]));
                        break;
                    case '☑️ ساخت کد':
                        $message = '⚜️ لطفا تعداد سکه که میخواهید برای کوپن اختصاص بدهید را وارد کنید:' . "\n \n";
                        $message .= '🔰 راهنما اگر میخواهید یک کد با نام دلخواه بسازید از این فرمت استفاده کنید:' . "\n";
                        $message .= '`نام کد`|`تعداد سکه`|*تعداد استفاده کننده ها*|*حداقل امتیاز روزانه مورد نیاز*|*مدت زمان کوپن*';
                        SendMessage($chat_id, $message, $telegram->buildKeyBoard([
                            [
                                $telegram->buildKeyboardButton('🏠 برگشت به منو اصلی')
                            ]
                        ]));
                        update_status('get_coupon_name');
                        break;
                    case '♻️ بازیابی اکانت':
                        $message = '💢 لطفا آیدی عددی یا یک پیام فوروارد شده از کاربر مورد نظر خورد را ارسال کنید.';
                        SendMessage($chat_id, $message, $telegram->buildKeyBoard([
                            [
                                $telegram->buildKeyboardButton('🏠 برگشت به منو اصلی')
                            ]
                        ]));
                        update_status('get_user_recovery_token');
                        break;
                    case '🔍 بررسی کاربر':
                        $message = '💢 لطفا آیدی عددی یا یک پیام فوروارد شده از کاربر مورد نظر خورد را ارسال کنید.';
                        SendMessage($chat_id, $message, $telegram->buildKeyBoard([
                            [
                                $telegram->buildKeyboardButton('🏠 برگشت به منو اصلی')
                            ]
                        ]));
                        update_status('get_user_check');
                        break;
                    case '📊 آمارگیری':
                        $message = '📊 گزارش آمار ایرانی مافیا در تاریخ: `[[date]]`' . "\n \n";
                        $message .= '👤 تعداد کاربران ربات: [[count]]' . "\n \n";
                        $message .= '🌐 تعداد بازی های باز: [[game_count]]' . "\n \n";
                        $message .= '👁 تعداد افراد آنلاین: [[count_online]]' . "\n \n";
                        $message .= '🤝 تعداد افراد وارد شده در 24 ساعت اخیر: [[count_today]]';
                        $today = date('Y-m-d');
                        add_filter('send_massage_text', function ($text) {
                            return tr_num($text, 'en', '.');
                        }, 11);
                        SendMessage($chat_id, __replace__($message, [
                            '[[count]]' => $link->get_var("SELECT COUNT(`id`) FROM `users`"),
                            '[[game_count]]' => $link->get_var("SELECT COUNT(`id`) FROM `server` WHERE `status` = 'started'"),
                            '[[count_online]]' => $link->get_var("SELECT COUNT(*) FROM `user_game`"),
                            '[[count_today]]' => $link->get_var("SELECT COUNT(`id`) FROM `users` WHERE `created_at` >= '{$today}'"),
                            '[[date]]' => "\n" . tr_num(jdate('Y-m-d H:i:s'))
                        ]));
                        break;
                    case '♨️ آزاد کردن لیگ':
                        $message = '💢 لطفا ایموجی لیگ را ارسال کنید.';
                        SendMessage($chat_id, $message, $telegram->buildKeyBoard([
                            [
                                $telegram->buildKeyboardButton('🏠 برگشت به منو اصلی')
                            ]
                        ]));
                        update_status('get_emoji_delete');
                        break;
                    case '🌐 سرور':
                    case '/stats':
                    case '/status':
                        global $token_bot;

                        $message = '🌐 سرور' . "\n \n";
                        $message .= 'رنگ جلوی هر ربات میزان خلوت یا شلوغ بودن آن است .' . "\n \n";
                        $message .= '🟢 خلوت ' . "\n";
                        $message .= '🟡 متوسط' . "\n";
                        $message .= '🟠 شلوغ ' . "\n";
                        $message .= '🔴 غیرقابل استفاده' . "\n \n";

                        foreach ($token_bot as $index => $token) {
                            $bot = bot('GetMe', [], $token);
                            $count_bot = get_count_members_bots($index);
                            $message .= 'وضعیت: ' . '@' . $bot->username . ' : ' . get_status_servers_bots($count_bot) . "\n" . $count_bot . ' کاربر' . "\n";
                        }

                        $message .= "\n" . '💡 به جهت دریافت کیفیت و سرعت بهتر از ربات های خلوت استفاده کنید تا به مشکل نخورید .';

                        add_filter('send_massage_text', function ($text) {
                            return tr_num($text, 'en', '.');
                        }, 11);
                        html();
                        break;
                    default:
                        switch (status()) {
                            case 'add_ban':
                                if (isset($message->forward_sender_name)) {
                                    $message = '❌ به دلیل تنظیمات اکانت کاربر، ربات قادر به تشخیص این کاربر نمی باشد.';
                                    SendMessage($chat_id, $message, null, null, 'html');
                                    exit();
                                }

                                if (isset($forward_id)) {
                                    $text = $forward_id;
                                }


                                $user = file_get_contents(BASE_DIR . '/users.txt');
                                $users = explode(',', $user);

                                $admin = file_get_contents(BASE_DIR . '/admins.txt');
                                $admins = explode(',', $admin);

                                $ban = file_get_contents(BASE_DIR . '/bans.txt');
                                $bans = explode(',', $ban);

                                if (in_array($text, $users) && !in_array($text, $admins)) {

                                    $ex = explode('-', $bans);
                                    $ex[] = $text;
                                    file_put_contents('bans.txt', implode('-', $ex));
                                    $message = '🔴 کاربر ' . '<a href="tg://user?id=' . $text . '">' . $text . '</a>' . ' بن شد ✅';
                                    update_status('');
                                } else {
                                    $message = '🚫 این کاربر هنوز ربات را استارت نکرده است.' . "\n \n" . '❌ کاربر شما یافت نشد.';
                                }
                                SendMessage($chat_id, $message, null, null, 'html');
                                break;
                            case 'delete_ban':
                                if (isset($message->forward_sender_name)) {
                                    $message = '❌ به دلیل تنظیمات اکانت کاربر، ربات قادر به تشخیص این کاربر نمی باشد.';
                                    SendMessage($chat_id, $message, null, null, 'html');
                                    exit();
                                }

                                if (isset($forward)) {
                                    $text = $forward_id;
                                }

                                $user = file_get_contents(BASE_DIR . '/users.txt');
                                $users = explode(',', $user);

                                $admin = file_get_contents(BASE_DIR . '/admins.txt');
                                $admins = explode(',', $admin);

                                $ban = file_get_contents(BASE_DIR . '/bans.txt');
                                $bans = explode(',', $ban);

                                if (in_array($text, $users)) {
                                    $key = array_search($text, $bans);
                                    unset($admins[$key]);
                                    file_put_contents('bans.txt', implode('-', $bans));
                                    $message = '🔴 کاربر ' . '<a href="tg://user?id=' . $text . '">' . $text . '</a>' . '  ان بن شد ✅';
                                    update_status('');
                                } else {
                                    $message = '🚫 این کاربر هنوز ربات را استارت نکرده است.' . "\n \n" . '❌ کاربر شما یافت نشد.';
                                }
                                SendMessage($chat_id, $message, null, null, 'html');
                                break;
                            case 'add_admin':
                                if (isset($message->forward_sender_name)) {
                                    $message = '❌ به دلیل تنظیمات اکانت کاربر، ربات قادر به تشخیص این کاربر نمی باشد.';
                                    SendMessage($chat_id, $message, null, null, 'html');
                                    exit();
                                }

                                if (isset($forward)) {
                                    $text = $forward_id;
                                }


                                $user = file_get_contents(BASE_DIR . '/users.txt');
                                $users = explode(',', $user);

                                $admin = file_get_contents(BASE_DIR . '/admins.txt');
                                $admins = explode(',', $admin);

                                if (in_array($text, $users)) {
                                    $ex = explode(',', $admins);
                                    $ex[] = $text;
                                    file_put_contents('admins.txt', implode(',', $ex));
                                    $message = '🔴 کاربر ' . '<a href="tg://user?id=' . $text . '">' . $text . '</a>' . ' به لیست مدیران ربات اضافه گردید✅';
                                    update_status('');
                                } else {
                                    $message = '🚫 این کاربر هنوز ربات را استارت نکرده است.' . "\n \n" . '❌ کاربر شما یافت نشد.';
                                }
                                SendMessage($chat_id, $message, null, null, 'html');
                                break;
                            case 'delete_admin':
                                if (isset($message->forward_sender_name)) {
                                    $message = '❌ به دلیل تنظیمات اکانت کاربر، ربات قادر به تشخیص این کاربر نمی باشد.';
                                    SendMessage($chat_id, $message, null, null, 'html');
                                    exit();
                                }

                                if (isset($forward)) {
                                    $text = $forward_id;
                                }

                                $admin = file_get_contents(BASE_DIR . '/admins.txt');
                                $admins = explode(',', $admin);

                                if (in_array($text, $admins)) {
                                    $key = array_search($text, $admins);
                                    unset($admins[$key]);
                                    file_put_contents('admins.txt', implode(',', $admins));
                                    $message = '🔴 کاربر ' . '<a href="tg://user?id=' . $text . '">' . $text . '</a>' . ' با موفقیت از لیست مدیران حذف گردید❌';
                                    update_status('');
                                } else {
                                    $message = '🚫 این کاربر ادمین نمی باشد.';
                                }
                                SendMessage($chat_id, $message, null, null, 'html');
                                break;
                            case 'send_message':
                                update_status('');
                                $i = 0;
                                $message = '🔰 عملیات ارسال پیام شروع شد.' . "\n" . "⚜️ تعداد ارسال پیام موفق در زیر در حال نمایش می باشد.";
                                $messageid = SendMessage($chat_id, $message, $telegram->buildInlineKeyBoard([
                                    [
                                        $telegram->buildInlineKeyboardButton('▶️ ' . $i . ' ◀️', '', 'send_message')
                                    ]
                                ]));
                                sleep(1);
                                $users = $link->get_result("SELECT * FROM `users`");
                                foreach ($users as $user) {
                                    try {
                                        bot('copyMessage', [
                                            'chat_id' => $user->user_id,
                                            'from_chat_id' => $chat_id,
                                            'message_id' => $message_id,
                                            'disable_notification' => true
                                        ]);
                                    } catch (Exception $e) {
                                        SendMessage(ADMIN_LOG, "<b>ERROR ON SEND ALL MESSAGE: " . $e->getMessage() . "</b>", null, null, 'html');
                                    }
                                    $i++;
                                    EditMessageText($chat_id, $messageid->message_id, $message, $telegram->buildInlineKeyBoard([
                                        [
                                            $telegram->buildInlineKeyboardButton('▶️ ' . $i . ' ◀️', '', 'send_message')
                                        ]
                                    ]));
                                }
                                $message = 'عملیات ارسال پیام با موفقیت به پایان رسید✅';
                                SendMessage($chat_id, $message, $telegram->buildKeyBoard(
                                    apply_filters('admin_menu')
                                ));
                                break;
                            case 'forward_message':
                                if (isset($forward)) {
                                    update_status('');
                                    $i = 0;
                                    $message = '🔰 عملیات فوروارد پیام شروع شد.' . "\n" . "⚜️ تعداد پیام فوروارد شده موفق در زیر در حال نمایش می باشد.";
                                    $messageid = SendMessage($chat_id, $message, $telegram->buildInlineKeyBoard([
                                        [
                                            $telegram->buildInlineKeyboardButton('▶️ ' . $i . ' ◀️', '', 'send_message')
                                        ]
                                    ]));
                                    sleep(1);
                                    $users = $link->get_result("SELECT * FROM `users`");
                                    foreach ($users as $user) {
                                        try {
                                            Forward($user, $chat_id, $message_id);
                                        } catch (Exception $e) {
                                            SendMessage(ADMIN_LOG, "<b>ERROR ON FORWARD ALL: " . $e->getMessage() . "</b>", null, null, 'html');
                                        }
                                        $i++;
                                        EditMessageText($chat_id, $messageid->message_id, $message, $telegram->buildInlineKeyBoard([
                                            [
                                                $telegram->buildInlineKeyboardButton('▶️ ' . $i . ' ◀️', '', 'send_message')
                                            ]
                                        ]));
                                    }
                                    $message = 'عملیات ارسال پیام با موفقیت به پایان رسید✅';
                                } else {
                                    $message = '📛 پیام شما باید از کانال یا فردی فوروارد شده باشد.';
                                }
                                SendMessage($chat_id, $message, $telegram->buildKeyBoard(
                                    apply_filters('admin_menu')
                                ));
                                break;
                            case 'get_name_league':
//                            $emoji = Emoji\is_single_emoji($text);
                                /*if ($emoji) {*/
                                $league = get_vip_league_by_emoji($text);
                                if (!$league) {
                                    $message = '💢 تعداد سکه مورد نیاز برای لیگ ' . $text . ' را وارد کنید.';
                                    SendMessage($chat_id, $message);
                                    update_status('get_coin_new_league');
                                    update_data($text);
                                } else {
                                    $message = '⚠️ خطا، این لیگ قبلا وجود دارد.';
                                    Message();
                                }
                                /*} else {
                                    $message = '⚠️ خطا، شما فقط میتوانید یک ایموجی ارسال کنید.';
                                    Message();
                                }*/
                                break;
                            case 'get_coin_new_league':
                                if (is_numeric($text) && $text >= 0) {
                                    if (add_new_vip_league(data(), $text, $chat_id)) {
                                        $message = '✅ لیگ جدید با موفقیت اضافه شد.';
                                    }
                                } else {
                                    $message = '⚠️ خطا، شما فقط میتوانید یک عدد بزرگ تر از 0 وارد کنید.';
                                }
                                Message();
                                break;
                            case 'get_user_add_coin':
                                if (isset($message->forward_sender_name)) {
                                    $message = '⚠️ به دلیل تنظیمات اکانت این کاربر قادر به تشخیص آن نیستیم!';
                                    Message();
                                    exit();
                                }

                                if (isset($message->forward_from)) {
                                    $text = $message->forward_from->id;
                                }


                                if (is_numeric($text)) {
                                    $message = '👤 مشخصات کاربر مقصد: [[user]]' . "\n \n";
                                    $message .= 'لیگ : [[league]]' . "\n";
                                    $message .= 'نام کاربر در ربات: [[name]]' . "\n \n";
                                    $message .= '💢 تعداد سکه که میخواهید به او ارسال کنید را وارد کنید.';
                                    add_filter('send_massage_text', function ($text) {
                                        return tr_num($text, 'en', '.');
                                    }, 11);
                                    SendMessage($chat_id, __replace__($message, [
                                        '[[user]]' => "<a href='tg://user?id=" . $text . "'>" . $text . "</a>",
                                        '[[league]]' => get__league_user($text)->icon,
                                        '[[name]]' => user($text)->name,
                                    ]), null, null, 'html');
                                    update_status('get_number_coin_user');
                                    update_data($text);
                                } else {
                                    $message = '⚠️ خطا، آیدی عددی تنها میتواند عدد باشد.';
                                    Message();
                                }

                                break;
                            case 'get_number_coin_user':
                                if (is_numeric($text) && $text > 0) {
                                    $user_id = data();
                                    $message = '💢 شما در حال ارسال [[coin]] سکه به کاربر [[user]] هستید.' . "\n \n";
                                    $message .= '⚜️ آیا از انجام این کار اطمینان دارید؟';
                                    add_filter('send_massage_text', function ($text) {
                                        return tr_num($text, 'en', '.');
                                    }, 11);
                                    SendMessage($chat_id, __replace__($message, [
                                        '[[coin]]' => $text,
                                        '[[user]]' => "<a href='tg://user?id=" . $user_id . "'>" . $user_id . "</a>"
                                    ]), $telegram->buildInlineKeyBoard([
                                        [
                                            $telegram->buildInlineKeyboardButton('✅ بله', '', 'send_coin-' . $text . '-' . $user_id),
                                            $telegram->buildInlineKeyboardButton('❌ انصراف', '', 'cancel'),
                                        ]
                                    ]), null, 'html');
                                    update_status('');
                                } else {
                                    $message = 'شما بايد یک عدد ارسال کنید';
                                    Message();
                                }
                                break;
                            case 'get_user_add_point':
                                if (isset($message->forward_sender_name)) {
                                    $message = '⚠️ به دلیل تنظیمات اکانت این کاربر قادر به تشخیص آن نیستیم!';
                                    Message();
                                    exit();
                                }

                                if (isset($message->forward_from)) {
                                    $text = $message->forward_from->id;
                                }


                                if (is_numeric($text)) {
                                    $message = '👤 مشخصات کاربر مقصد: [[user]]' . "\n \n";
                                    $message .= 'لیگ : [[league]]' . "\n";
                                    $message .= 'نام کاربر در ربات: [[name]]' . "\n \n";
                                    $message .= '💢 تعداد <u>امتیازی</u> که میخواهید به او ارسال کنید را وارد کنید.';
                                    add_filter('send_massage_text', function ($text) {
                                        return tr_num($text, 'en', '.');
                                    }, 11);
                                    SendMessage($chat_id, __replace__($message, [
                                        '[[user]]' => "<a href='tg://user?id=" . $text . "'>" . $text . "</a>",
                                        '[[league]]' => get__league_user($text)->icon,
                                        '[[name]]' => user($text)->name,
                                    ]), null, null, 'html');
                                    update_status('get_number_point_user');
                                    update_data($text);
                                } else {
                                    $message = '⚠️ خطا، آیدی عددی تنها میتواند عدد باشد.';
                                    Message();
                                }

                                break;
                            case 'get_number_point_user':
                                if (is_numeric($text) && $text > 0) {
                                    $user_id = data();
                                    $message = '💢 شما در حال ارسال [[coin]] امتیاز به کاربر [[user]] هستید.' . "\n \n";
                                    $message .= '⚜️ آیا از انجام این کار اطمینان دارید؟';
                                    add_filter('send_massage_text', function ($text) {
                                        return tr_num($text, 'en', '.');
                                    }, 11);
                                    SendMessage($chat_id, __replace__($message, [
                                        '[[coin]]' => $text,
                                        '[[user]]' => "<a href='tg://user?id=" . $user_id . "'>" . $user_id . "</a>"
                                    ]), $telegram->buildInlineKeyBoard([
                                        [
                                            $telegram->buildInlineKeyboardButton('✅ بله', '', 'send_point-' . $text . '-' . $user_id),
                                            $telegram->buildInlineKeyboardButton('❌ انصراف', '', 'cancel'),
                                        ]
                                    ]), null, 'html');
                                    update_status('');
                                } else {
                                    $message = 'شما بايد یک عدد ارسال کنید';
                                    Message();
                                }
                                break;
                            case 'get_coupon_name':
                                $data = explode('|', $text);
                                if (count($data) == 1) {
                                    $data[1] = $data[0];
                                    $data[0] = getRandomeString(6);
                                }

                                $count = $data[2] ?? 1;
                                $time = isset($data[4]) ? strtotime($data[4]) : null;
                                $point = $data[3] ?? 25;

                                if ($data[2] > 0 && add_coupon($data[0], $data[1], $chat_id, $point, $count, $time)) {
                                    $message = '✅ کوپن [[coupon]] با موفقیت ساخته شد:' . "\n \n";
                                    $message .= '🔰 تعداد سکه: [[coin]]' . "\n";
                                    $message .= '🔰 تعداد استفاده کننده ها: [[count]]' . "\n";
                                    $message .= '🔰 حداقل امتیاز روزانه مورد نیاز: [[point]]' . "\n";
                                    $message .= '🔰 تاریخ انقضا: [[date]]';

                                    SendMessage($chat_id, __replace__($message, [
                                        '[[coupon]]' => $data[0],
                                        '[[coin]]' => number_format($data[1]),
                                        '[[count]]' => $count,
                                        '[[point]]' => $point,
                                        '[[date]]' => $time === null ? 'ندارد' : jdate('Y-m-d', $time),
                                    ]), $telegram->buildKeyBoard(
                                        apply_filters('admin_menu')
                                    ), null, 'html');

                                    update_status('');

                                    $message = '🔔 #کوپن جدید ساخته شد: ' . "\n \n" . "➡️ <code>[[coupon]]</code> ⬅️" . "\n \n";
                                    $message .= '➖ تعداد سکه : [[coin]] 💰' . "\n";
                                    $message .= '➖ حداقل امتیاز روزانه برای استفاده : [[point]] امتیاز 🌟' . "\n";
                                    $message .= '➖ محدودیت تعداد : [[count]] نفر' . "\n";
                                    $message .= '➖ مهلت استفاده : <b>[[date]]</b>' . "\n \n";
                                    $message .= "<a href='https://t.me/iranimafia/154'>چگونه از کوپن استفاده کنم❓</a>";


                                    $message_id = $telegram->sendMessage([
                                        'chat_id' => CHNNEL_ID,
                                        'text' => __replace__($message, [
                                            '[[coupon]]' => $data[0],
                                            '[[coin]]' => tr_num($data[1]),
                                            '[[count]]' => tr_num($count),
                                            '[[point]]' => tr_num($point),
                                            '[[date]]' => $time !== null ? tr_num(jdate('Y/m/d', $time)) : 'تا پایان امروز',
                                        ]),
                                        'parse_mode' => 'html',
                                        'reply_markup' => $telegram->buildInlineKeyBoard([
                                            [
                                                $telegram->buildInlineKeyboardButton(
                                                    '♨️ وارد کردن کوپن ♨️',
                                                    'https://telegram.me/' . GetMe()->username . '?start=code'
                                                ),
                                            ]
                                        ]),
                                        'disable_web_page_preview' => true
                                    ]);

                                    update_coupon($data[0], [
                                        'post_id' => $message_id['result']['message_id']
                                    ]);

                                } else {

                                    $message = '⚠️ مشکلی در اضافه کردن کوپن به دیتابس پیش آمده است.';
                                    Message();

                                }
                                break;
                            case 'get_user_recovery_token':
                                if (isset($message->forward_sender_name)) {
                                    $message = '⚠️ به دلیل تنظیمات اکانت این کاربر قادر به تشخیص آن نیستیم!';
                                    Message();
                                    exit();
                                }

                                if (isset($message->forward_from)) {
                                    $text = $message->forward_from->id;
                                }

                                if (is_numeric($text)) {
                                    $user = user($text);
                                    if (isset($user)) {
                                        $message = '👤 مشخصات کاربر: [[user]]' . "\n \n";
                                        $message .= 'لیگ : [[league]]' . "\n";
                                        $message .= 'امتیاز : [[point]]' . "\n";
                                        $message .= 'سکه : [[coin]]' . "\n";
                                        $message .= 'نام کاربر در ربات: [[name]]' . "\n";
                                        $message .= 'لیگ های کاربر: [[vip_league]]' . "\n \n";
                                        $message .= '💢 توکن برای جا به جایی حساب:' . "\n \n";
                                        $token = token_security_user($text);
                                        $message .= "<code>{$token}</code>";
                                        $user_vip_league = get_vip_league_user($text);
                                        if (count($user_vip_league) > 0) {
                                            foreach ($user_vip_league as $item) {
                                                $leagues .= $item->emoji . ' ';
                                            }
                                        } else {
                                            $leagues = 'ندارد';
                                        }
                                        add_filter('send_massage_text', function ($text) {
                                            return tr_num($text, 'en', '.');
                                        }, 11);
                                        SendMessage($chat_id, __replace__($message, [
                                            '[[user]]' => "<a href='tg://user?id=" . $text . "'>" . $text . "</a>",
                                            '[[league]]' => get__league_user($text)->icon,
                                            '[[name]]' => $user->name,
                                            '[[point]]' => get_point($text),
                                            '[[coin]]' => $user->coin,
                                            '[[vip_league]]' => $leagues ?? '',
                                        ]), null, null, 'html');
                                        update_status('');
                                    } else {
                                        $message = '⛔️ این کاربر وجود ندارد.';
                                        Message();
                                    }
                                } else {
                                    $message = '⚠️ خطا، آیدی عددی تنها میتواند عدد باشد.';
                                    Message();
                                }

                                break;
                            case 'get_user_check':
                                if (isset($message->forward_sender_name)) {
                                    $message = '⚠️ به دلیل تنظیمات اکانت این کاربر قادر به تشخیص آن نیستیم!';
                                    Message();
                                    exit();
                                }

                                if (isset($message->forward_from)) {
                                    $text = $message->forward_from->id;
                                }

                                if (is_numeric($text)) {
                                    $user = user($text);
                                    if (isset($user)) {
                                        $message = '👤 مشخصات کاربر: [[user]]' . "\n \n";
                                        $message .= 'لیگ : [[league]]' . "\n";
                                        $message .= 'امتیاز : [[point]]' . "\n";
                                        $message .= 'سکه : [[coin]]' . "\n";
                                        $message .= 'نام کاربر در ربات: [[name]]' . "\n";
                                        $message .= 'لیگ های کاربر: [[vip_league]]' . "\n \n";
                                        $user_vip_league = get_vip_league_user($text);
                                        if (count($user_vip_league) > 0) {
                                            foreach ($user_vip_league as $item) {
                                                $leagues .= $item->emoji . ' ';
                                            }
                                        } else {
                                            $leagues = 'ندارد';
                                        }
                                        add_filter('send_massage_text', function ($text) {
                                            return tr_num($text, 'en', '.');
                                        }, 11);
                                        $log = get_log($text, 'reset');
                                        $text_keyboard = isset($log) ? '🔃 بازیابی اکانت' : '🔄 ریست کردن اکانت';
                                        SendMessage($chat_id, __replace__($message, [
                                            '[[user]]' => "<a href='tg://user?id=" . $text . "'>" . $text . "</a>",
                                            '[[league]]' => get__league_user($text)->icon,
                                            '[[name]]' => $user->name,
                                            '[[point]]' => get_point($text),
                                            '[[coin]]' => $user->coin,
                                            '[[vip_league]]' => $leagues ?? '',
                                        ]), $telegram->buildInlineKeyBoard([
                                            [
                                                $telegram->buildInlineKeyboardButton($text_keyboard, '', 'reset-' . $text),
                                            ],
                                            [
                                                $telegram->buildInlineKeyboardButton('❌ انصراف', '', 'cancel'),
                                            ]
                                        ]), null, 'html');
                                        update_status('');
                                    } else {
                                        $message = '⛔️ این کاربر وجود ندارد.';
                                        Message();
                                    }
                                } else {
                                    $message = '⚠️ خطا، آیدی عددی تنها میتواند عدد باشد.';
                                    Message();
                                }
                                break;
                            case 'get_emoji_delete':
                                $league = get_vip_league_by_emoji(trim($text));
                                if (isset($league)) {
                                    $message = '💢 آیا از حذف لیگ [[league]] اطمینان دارید؟' . "\n \n";
                                    $message .= '⚠️ با حذف این لیگ تعداد [[count]] کاربر این لیگ را از دست میدهند.';
                                    SendMessage($chat_id, __replace__($message, [
                                        '[[league]]' => trim($text),
                                        '[[count]]' => $link->get_var("SELECT COUNT(`id`) FROM `user_league` WHERE `emoji` LIKE '{$league->emoji}' AND `coin` = 0")
                                    ]), $telegram->buildInlineKeyboard([
                                        [
                                            $telegram->buildInlineKeyboardButton('✅ تایید', '', 'delete_league-' . $league->id),
                                            $telegram->buildInlineKeyboardButton('❌ انصراف', '', 'cancel'),
                                        ]
                                    ]));
                                } else {
                                    $message = '⚠️ خطا، این ایموجی یافت نشد.';
                                    Message();
                                }
                                break;
                        }
                        do_action(status());
                        do_action('status_admin', status());
                        break;
                }
                do_action($text);
            }

            if ($type == 'private' && isset($update->callback_query)) {
                $data = explode('-', $Data);
                switch ($data[0]) {
                    case 'send_coin':
                        $user_id = $data[2];
                        $coin = $data[1];
                        $message = '⚜️ تعداد [[coin]] سکه به کاربر [[user]] ارسال شد.';
                        EditMessageText($chatid, $messageid, __replace__($message, [
                            '[[coin]]' => $coin,
                            '[[user]]' => "<a href='tg://user?id=" . $user_id . "'>" . $user_id . "</a>"
                        ]), null, null, 'html');
                        $message = '✅ تبریک شما [[coin]] سکه از سمت ادمین دریافت کردید!';
                        SendMessage($user_id, __replace__($message, [
                            '[[coin]]' => $coin,
                        ]), null, null, 'html');
                        add_coin($user_id, $coin);
                        break;
                    case 'send_point':
                        $user_id = $data[2];
                        $coin = $data[1];
                        $message = '⚜️ تعداد [[coin]] امتیاز به کاربر [[user]] ارسال شد.';
                        EditMessageText($chatid, $messageid, __replace__($message, [
                            '[[coin]]' => $coin,
                            '[[user]]' => "<a href='tg://user?id=" . $user_id . "'>" . $user_id . "</a>"
                        ]), null, null, 'html');
                        $message = '✅ تبریک شما [[coin]] امتیاز از سمت ادمین دریافت کردید!';
                        SendMessage($user_id, __replace__($message, [
                            '[[coin]]' => $coin,
                        ]), null, null, 'html');
                        add_point(-1, $user_id, $coin);
                        break;
                    case 'cancel':
                        $message = '⛔️ عملیات لغو شد.';
                        EditMessageText($chatid, $messageid, $message);
                        break;
                    case 'reset':
                        $status = reset_user($data[1]);
                        if ($status['status'] == 200) {
                            $message = $update->callback_query->message->text . "\n \n" . $status['message'];
                            EditMessageText($chatid, $messageid, $message);
                        }
                        break;
                    case 'delete_league':
                        $league_id = $data[1];
                        $league = get_vip_league($league_id);
                        $users = $link->get_result("SELECT * FROM `user_league` WHERE `emoji` LIKE '{$league->emoji}' AND `coin` = 0");
                        $x = 0;
                        foreach ($users as $user) {
                            update_user([
                                'league' => null,
                            ], $user->user_id);
                            $x++;
                        }
                        $link->where('id', $league_id)->delete('vip_league', 1);
                        $link->where('emoji', $league->emoji)->where('coin', 0)->delete('user_league');
                        $message = $update->callback_query->message->text . "\n \n";
                        $message .= '✅ عملیات موفق آمیز بود.' . "\n \n";
                        $message .= '⚜️ تعداد کاربرانی که لیگ آنها تغییر کرده است: [[count]]';
                        EditMessageText($chatid, $messageid, __replace__($message, [
                            '[[count]]' => $x
                        ]));
                        break;
                    // ----------------------------------
                    case 'close_server':
                        if (!class_exists('library\Server')) {

                            include BASE_DIR . "/library/Server.php";

                        }

                        $server = new \library\Server($data[1]);

                        $users_server = $server->usersByGame();

                        foreach ($users_server as $user) {

                            $message = '💢 بنا به درخواست ادمین این سرور بسته شد.';
                            $user->SendMessageHtml($message);

                        }

                        if ($server->close() && tun_off_server($server->getId())) {

                            $message = '<u>✅ سرور شماره ' . $server->getId() . ' با موفقیت بسته شد.</u>' . "\n \n";
                            $message .= '💢 تعداد کاربران خارج شده: ' . count($users_server);
                            $telegram->sendMessage([
                                'chat_id' => $chat_id,
                                'text' => $message,
                                'parse_mode' => 'html'
                            ]);

                        }
                        break;
                    case 'users_server':

                        if (!class_exists('library\Server')) {

                            include BASE_DIR . "/library/Server.php";

                        }

                        $server = new \library\Server($data[1]);

                        $users_server = $server->usersByGame();

                        if (count($users_server) > 0) {

                            $users_server = $server->users();
                            $message = '💢 لیست کاربران در سرور [[server]]:' . "\n \n";
                            foreach ($users_server as $id => $user) {

                                $message .= ($id + 1) . ". " . $user->get_league()->emoji . $user->user()->name . ' - ' . "<code>" . $user->getUserId() . "</code>" . "\n";

                            }

                            $telegram->editMessageText([
                                'chat_id' => $chat_id,
                                'message_id' => $messageid,
                                'text' => __replace__($message, [
                                    '[[server]]' => $server->getId()
                                ]),
                                'parse_mode' => 'html',
                                'reply_markup' => json_encode($update->callback_query->message->reply_markup),
                            ]);

                        } else {

                            $message = '❌ در سرور [[server]] هیچ کاربری یافت نشد.';
                            AnswerCallbackQuery($dataid, __replace__($message, [
                                '[[server]]' => $server->getId()
                            ]));

                        }

                        break;
                    case 'role_users_server':

                        if (!class_exists('library\Server')) {

                            include BASE_DIR . "/library/Server.php";

                        }

                        $server = new \library\Server($data[1]);

                        $users_server = $server->usersByGame();

                        if (count($users_server) > 0) {

                            $message = '💢 نقش کاربران سرور [[server]]:' . "\n \n";

                            if ($server->status == 'started') {

                                $users_server = $server->users();

                                foreach ($users_server as $id => $user) {

                                    $prefix = '';

                                    if (is_server_meta($server->getId(), 'friend', $user->getUserId())) {

                                        $prefix = get_emoji_for_friendly(get_server_meta($server->getId(), 'friend', $user->getUserId()));

                                    }

                                    $role = $user->get_role();
                                    $message .= "<b>" . ($id + 1) . ".</b> " . $prefix . $user->user()->name . ($role->group_id == 1 ? '🟢' : ($role->group_id == 2 ? '🔴' : '🟡')) . " " . $role->icon . '  <code>' . $user->getUserId() . '</code>' . "\n";

                                }

                                $telegram->editMessageText([
                                    'chat_id' => $chat_id,
                                    'message_id' => $messageid,
                                    'text' => __replace__($message, [
                                        '[[server]]' => $server->getId()
                                    ]),
                                    'parse_mode' => 'html',
                                    'reply_markup' => json_encode($update->callback_query->message->reply_markup),
                                ]);

                            } else {

                                $message = '❌ سرور [[server]] هنوز شروع نشده است.';
                                AnswerCallbackQuery($dataid, __replace__($message, [
                                    '[[server]]' => $server->getId()
                                ]));

                            }

                        } else {

                            $message = '❌ در سرور [[server]] هیچ کاربری یافت نشد.';
                            AnswerCallbackQuery($dataid, __replace__($message, [
                                '[[server]]' => $server->getId()
                            ]));

                        }
                        break;
                    case 'logout_user_server':
                        if (!class_exists('library\Server')) {

                            include BASE_DIR . "/library/Server.php";

                        }

                        $server = new \library\Server($data[1]);

                        $users_server = $server->usersByGame();

                        if (count($users_server) > 0) {

                            $keyboard = [];

                            foreach ($users_server as $item) {
                                if (!$item->is_user_in_game()) continue;

                                $keyboard[][] = $telegram->buildInlineKeyboardButton('❌ ' . $item->user()->name, '', 'remove_user-' . $server->getId() . '-' . $item->getUserId());

                            }

                            $keyboard[][] = $telegram->buildInlineKeyboardButton('🔙 برگشت به منو اصلی', '', 'main_menu-' . $server->getId());

                            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

                        } else {

                            $message = '❌ در سرور [[server]] هیچ کاربری یافت نشد.';
                            AnswerCallbackQuery($dataid, __replace__($message, [
                                '[[server]]' => $server->getId()
                            ]));

                        }
                        break;
                    case 'remove_user':

                        if (!class_exists('library\Server')) {

                            include BASE_DIR . "/library/Server.php";

                        }

                        $server = new \library\Server($data[1]);

                        $users_server = $server->usersByGame();

                        $User = new \library\User($data[2], $server->getId());


                        if (count($users_server) > 0) {

                            $keyboard = [];

                            foreach ($users_server as $item) {
                                if (!$item->is_user_in_game()) continue;


                                if (!$item->is($User)) {

                                    $message = '🌐 به درخواست ادمین [[user]] از سرور بیرون انداخته شد.';
                                    $item->SendMessageHtml(__replace__($message, [
                                        '[[user]]' => "<u><b>" . $User->user()->name . "</b></u>"
                                    ]));
                                    $keyboard[][] = $telegram->buildInlineKeyboardButton('❌ ' . $item->user()->name, '', 'remove_user-' . $server->getId() . '-' . $item->getUserId());

                                } else {

                                    $message = '♨️ شما توسط ادمین از این سرور خارج شدید.';
                                    $item->SendMessageHtml($message)->logout();

                                }


                            }

                            $keyboard[][] = $telegram->buildInlineKeyboardButton('🔙 برگشت به منو اصلی', '', 'main_menu-' . $server->getId());


                            if (count($users_server) - 1 == 0) {

                                if ($server->close() && tun_off_server($server->getId())) {

                                    $message = '<u>✅ سرور شماره ' . $server->getId() . ' با موفقیت بسته شد.</u>' . "\n \n";
                                    $message .= '💢 تعداد کاربران خارج شده: ' . count($users_server);
                                    $telegram->sendMessage([
                                        'chat_id' => $chat_id,
                                        'text' => $message,
                                        'parse_mode' => 'html'
                                    ]);

                                }

                            }

                            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

                        } else {

                            $message = '❌ در سرور [[server]] هیچ کاربری یافت نشد.';
                            AnswerCallbackQuery($dataid, __replace__($message, [
                                '[[server]]' => $server->getId()
                            ]));

                        }
                        break;
                    case 'main_menu':
                        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard([
                            [
                                $telegram->buildInlineKeyboardButton('🔄 بروزرسانی', '', 'refresh_data_server-'  . $data[1]),
                            ],
                            [
                                $telegram->buildInlineKeyboardButton('🗑 بیرون انداختن کاربر', '', 'logout_user_server-' . $data[1]),
                                $telegram->buildInlineKeyboardButton('❌ بستن سرور', '', 'close_server-' . $data[1]),
                            ],
                            [
                                $telegram->buildInlineKeyboardButton('🌐 اعضای داخل سرور', '', 'users_server-' . $data[1]),
                                $telegram->buildInlineKeyboardButton('🔦 نقش اعضا بازی', '', 'role_users_server-' . $data[1]),
                            ],
                            [
                                $telegram->buildInlineKeyboardButton('🔧 سرور مشکل دارد؟', '', 'problems_server-' . $data[1])
                            ]
                        ]));
                        break;
                    case 'problems_server':
                        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard([
                            [
                                $telegram->buildInlineKeyboardButton('🏷 عضوگیری درست نمی باشد؟', '', 'get_members_server-' . $data[1]),
                            ],
                            [
                                $telegram->buildInlineKeyboardButton('🏷 کاربران نقش دریافت نکردند؟', '', 'get_roles_server-' . $data[1]),
                            ],
                            [
                                $telegram->buildInlineKeyboardButton('🏷 بازی شروع نشد؟', '', 'start_server-' . $data[1]),
                            ],
                            [
                                $telegram->buildInlineKeyboardButton('🔙 برگشت به منو اصلی', '', 'main_menu-' . $data[1])
                            ]
                        ]));
                        break;
                    case 'get_members_server':
                        if (!class_exists('library\Server')) {

                            include BASE_DIR . "/library/Server.php";

                        }

                        $server = new \library\Server($data[1]);

                        $status = 'started';
                        if ($server->count != $server->count() || $server->status == 'opened') {

                            $status = 'opened';

                        }

                        $server->update([
                            'count' => $server_count = $server->count(),
                            'status' => $status
                        ]);

                        AnswerCallbackQuery($dataid, '✅ اعضا سرور بروزرسانی شد به ' . $server_count . ' کاربر.');
                        break;
                    case 'get_roles_server':
                        if (!class_exists('library\Server')) {

                            include BASE_DIR . "/library/Server.php";

                        }

                        $server = new \library\Server($data[1]);

                        if ($server->get_league()->count == $server->count()) {

                            $server_league = $server->get_league();
                            $roles_users = set_role_user_by_server($server->getId(), $server->league_id);
                            $users_server = get_users_by_server($server->getId());

                            $server->update([
                                'status' => 'started',
                                'count' => $server->count()
                            ]);

                            // دریافت نقش ها
                            $admin_message = 'بازی جدید تکمیل شد. ' . "<code>" . $server->getId() . "</code> " . "<b>بازی " . $server_league->icon . "</b>" . "\n";
                            $admin_message .= 'زمان شروع : ' . jdate('Y-m-d H:i') . "\n \n";
                            $id = 1;
                            foreach ($roles_users as $user_id => $role) {
                                $prefix = '';

                                if (is_server_meta($server->getId(), 'friend', $user_id)) {

                                    $prefix = get_emoji_for_friendly(get_server_meta($server->getId(), 'friend', $user_id));

                                }

                                $admin_message .= "<b>" . $id . ".</b> " . $prefix . user($user_id)->name . ($role->group_id == 1 ? '🟢' : ($role->group_id == 2 ? '🔴' : '🟡')) . " " . $role->icon . '  <code>' . $user_id . '</code>' . "\n";
                                add_role_to_user_for_server($user_id, $server->getId(), $role->id);
                                $message = '🔔 بازی شروع شد.' . "\n";
                                $message .= '🌞 #روز اول :' . "\n";
                                $message .= ' ۲۵ ثانیه وقت داری به بقیه سلام کنی و با اعضای بازی آشنا بشی ' . "\n \n";
                                $message .= '♨️ نقش شما : ' . $role->icon . "\n";
                                $message .= '🔘 گروه : ' . group_name($role->group_id) . "\n";
                                $message .= '🔖 توضیحات نقش : ' . "\n" . $role->detail . "\n \n";
                                $message .= '📚 راهنمای بازی : ' . "\n" . '/help' . "\n" . '💬 چت : فعال ' . "\n";
                                $message .= 'مدت زمان : ⏱ ۲۵ ثانیه';
                                $message_sended = !is_server_meta($server->getId(), 'message-sended', $user_id);
                                if ($role->id != ROLE_Bazpors && $message_sended) {
                                    $result = SendMessage($user_id, $message, null, null, 'html');
                                } elseif ($message_sended) {
                                    $keyboard = [];
                                    foreach ($users_server as $user) {
                                        if ($user->user_id != $user_id) {
                                            $keyboard[] = [
                                                $telegram->buildInlineKeyboardButton(
                                                    '🔗 ' . $user->name,
                                                    '',
                                                    '1/server-' . $server->league_id . '-question-' . $server->getId() . '-' . $user->user_id
                                                )
                                            ];
                                        }
                                    }
                                    $result = SendMessage($user_id, $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');
                                }
                                if (isset($result->message_id)) {
                                    add_server_meta($server->getId(), 'message-sended', 'sended', $user_id);
                                }
                                switch ($server->league_id) {
                                    case 3:
                                        update_status('gap', $user_id);
                                        break;
                                    default:
                                        update_status('game_started', $user_id);
                                        break;
                                }
                                update_user_meta($user_id, 'game-count', (int)get_user_meta($user_id, 'game-count') + 1);
                                $id++;
                                add_server_meta($server->getId(), 'is-online', 'no', $user_id);
                                update_user_meta($user_id, 'count-game', '0');

                            }

                            do_action('report_start_game', $admin_message, $server);
                            // داده های سرور
                            update_server_meta($server->getId(), 'time', time()); // تاریخ شروع شدن بازی

                            switch ($server->league_id) {
                                case 3:
                                    update_server_meta($server->getId(), 'next-time', time() + 15); // تاریخ باز شدن
                                    update_server_meta($server->getId(), 'status', 'welcome'); // وضعیت روز
                                    break;
                                default:
                                    update_server_meta($server->getId(), 'next-time', time() + 25); // تاریخ باز شدن
                                    update_server_meta($server->getId(), 'status', 'night'); // وضعیت روز
                                    break;
                            }
                            update_server_meta($server->getId(), 'day', 1); // روز چندم

                            AnswerCallbackQuery($dataid, '✅ نقش ها با موفقیت داده شد و بازی شروع شد.');
                        } else {

                            AnswerCallbackQuery($dataid, '❌ اعضا این سرور برای گرفتن نقش کافی نیست.');

                        }

                        break;
                    case 'start_server':
                        if (!class_exists('library\Server')) {

                            include BASE_DIR . "/library/Server.php";

                        }

                        $server = new \library\Server($data[1]);

                        if ($server->get_league()->count == $server->count()) {

                            $server->update([
                                'status' => 'started',
                                'count' => $server->count()
                            ]);

                            update_server_meta($server->getId(), 'time', time()); // تاریخ شروع شدن بازی

                            switch ($server->league_id) {
                                case 3:
                                    update_server_meta($server->getId(), 'next-time', time() + 15); // تاریخ باز شدن
                                    update_server_meta($server->getId(), 'status', 'welcome'); // وضعیت روز
                                    break;
                                default:
                                    update_server_meta($server->getId(), 'next-time', time() + 25); // تاریخ باز شدن
                                    update_server_meta($server->getId(), 'status', 'night'); // وضعیت روز
                                    break;
                            }
                            update_server_meta($server->getId(), 'day', 1); // روز چندم

                            AnswerCallbackQuery($dataid, '✅ بازی با موفقیت شروع شد.');
                        } else {

                            AnswerCallbackQuery($dataid, '❌ اعضا این سرور برای شروع بازی کافی نیست.');

                        }

                        break;
                    case 'refresh_data_server':
                        if (!class_exists('library\Server')) {

                            include BASE_DIR . "/library/Server.php";

                        }

                        $server = new \library\Server($data[1]);
                        $numbser_to_word = new NumberToWord();
                        $message = '♨️ اطلاعات سرور ' . "<u>[[server]]</u>" . ' :' . "\n \n";
                        $message .= '🔰 وضعیت سرور:  [[status]]' . "\n";
                        $message .= '🔰 تعداد اعضا سرور: [[server_count]] نفر' . "\n";
                        $message .= '🔰 تعداد اعضا داخل بازی: [[game_count]] نفر' . "\n";
                        $message .= '🔰 روز بازی: [[day]]' . "\n";
                        $message .= '🔰 نوع بازی: بازی [[name_game]]' . "\n";
                        $message .= '🔰 وضعیت بازی: [[status_game]]' . "\n";
                        $message .= '🔰 سرور ساخته شده در ربات: شماره [[bot]]' . "\n";
                        $message .= '🔰 نوع سرور: [[type]]' . "\n";
                        if ($server->getUserId() !== null) {
                            $message .= '🔰 کاربر سازنده: <code>[[user_id]]</code>' . "\n";
                        }
                        $message .= '🔰 کرون متصل: شماره [[cron]]' . "\n";
                        $message .= '🔰 تاریخ ساخت سرور: ' . "\n";
                        $message .= '[[date]]' . "\n \n";
                        $message .= '⚙️ نوع عملیات را انتخاب کنید:';
                        $telegram->editMessageText([
                            'chat_id' => $chatid,
                            'message_id' => $messageid,
                            'text' => __replace__($message, [
                                '[[server]]' => $server->getId(),
                                '[[status]]' => $server->toStringStatusServer(),
                                '[[server_count]]' => $server->count,
                                '[[game_count]]' => $server->count(),
                                '[[day]]' => $numbser_to_word->numberToWords($server->day() ?? 0),
                                '[[name_game]]' => $server->get_league()->icon,
                                '[[status_game]]' => $server->toStringStatusGame(),
                                '[[bot]]' => $numbser_to_word->NumbersToWord($server->bot + 1),
                                '[[type]]' => $server->type == 'public' ? 'آنلاین' : 'دوستانه',
                                '[[user_id]]' => $server->getUserId(),
                                '[[cron]]' => $numbser_to_word->NumbersToWord($server->cron),
                                '[[date]]' => tr_num(jdate('Y-m-d H:i:s', strtotime($server->created_at))),
                            ]),
                            'reply_markup' => $telegram->buildInlineKeyBoard([
                                [
                                    $telegram->buildInlineKeyboardButton('🔄 بروزرسانی', '', 'refresh_data_server-' . $server->getId()),
                                ],
                                [
                                    $telegram->buildInlineKeyboardButton('🗑 بیرون انداختن کاربر', '', 'logout_user_server-' . $server->getId()),
                                    $telegram->buildInlineKeyboardButton('❌ بستن سرور', '', 'close_server-' . $server->getId()),
                                ],
                                [
                                    $telegram->buildInlineKeyboardButton('🌐 اعضای داخل سرور', '', 'users_server-' . $server->getId()),
                                    $telegram->buildInlineKeyboardButton('🔦 نقش اعضا بازی', '', 'role_users_server-' . $server->getId()),
                                ],
                                [
                                    $telegram->buildInlineKeyboardButton('🔧 سرور مشکل دارد؟', '', 'problems_server-' . $server->getId())
                                ]
                            ]),
                            'parse_mode' => 'html'
                        ]);
                        break;
                }
            }

        } catch (Exception | ErrorException | Throwable | ArithmeticError  $e) {
            $message = "<u>ERROR TO LOAD FILE</u>" . "\n";
            $message .= "<i>ERROR LINE: {" . $e->getLine() . "}</i>" . "\n \n";
            $message .= "<i>ERROR ON FILE: {" . $e->getFile() . "}</i>" . "\n \n";
            $message .= "<b>CONTACT ERROR: [" . $e->getMessage() . "]</b>";
            SendMessage(ADMIN_LOG, $message, null, null, 'html');
            $message = '⛔️ خطایی رخ داد. ⚠️ گزارش خطا برای پشتیبانی ارسال شد.';
            SendMessage($chat_id ?? $chatid, $message);
        }
        exit();
    }
});
