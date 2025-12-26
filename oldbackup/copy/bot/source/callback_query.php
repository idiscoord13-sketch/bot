<?php
/** @noinspection ALL */

use library\Media;
use library\Server;
use library\User;


switch ($data[0]) {

    case 'charge':


        switch ($data[1]) {
            case PLAN_1:
                $coin = 100;
                break;
            case PLAN_2:
                $coin = 200;
                break;
            case PLAN_3:
                $coin = 400;
                break;
            case PLAN_4:
                $coin = 800;
                break;
            case PLAN_5:
                $coin = 1000;
                break;
            case PLAN_6:
                $coin = 3000;
                break;
            case PLAN_7:
                $coin = 5000;
                break;
        }
        $auth = factor($data[1], URL_VERIFY . "?bot=" . $BOT_ID, 'شارژ حساب ' . $chatid);
        add_factor($chatid, $data[1], $auth, $coin);

        $message = '♨️ [[name]] عزیز 
شما بسته [[coin]] سکه ، [[amount]] تومان را برای خرید انتخاب کرده اید .

❗️لطفا به نکات زیر دقت کنید :
۱. بعد از پرداخت کمی صبر کنید تا فیش خرید صادر شود .
۲. بهتر است برای پرداخت از مرورگر گوگل کروم استفاده کنید.
۳. درصورت مشکل در انتقال به درگاه بهتر است فیلترشکن را خاموش کنید.
۴. درصورت برداشت وجه از حساب و ناموفق بودن تراکنش ، مبلغ برداشتی برگشت داده میشود.

*با کپی کردن لینک زیر میتوانید از بقیه بخواهید برای شما خرید انجام دهند .*

`' . zarinpal_link($auth) . '`

درصورت تایید ، دکمه زیر را انتخاب کنید 👇
';


        __replace__($message, [
            '[[name]]' => user()->name,
            '[[coin]]' => $coin,
            '[[amount]]' => number_format($data[1] / 10)
        ]);
        //        $message .= '<a href="' . zarinpal_link($auth) . '"> </a>';
        $telegram->editMessageText([

            'chat_id' => $user->getUserId(),
            'text' => $message,
            'message_id' => $messageid,
            'reply_markup' => $telegram->buildInlineKeyBoard([
                [
                    $telegram->buildInlineKeyboardButton('پرداخت از طریق درگاه اینترنتی', zarinpal_link($auth))
                ]
            ]),
            'parse_mode' => 'MarkDown'

        ]);

        break;

    case 'server':
        require BASE_DIR . '/source/game_2.php';
        break;

    case 'best_player':
        // require BASE_DIR . '/source/best_player.php';



        if ($data[1] == 'close') {
            AnswerCallbackQuery($dataid, '⚠️ امکان تغییر وجود ندارد');
        } elseif ($data[2] == $chatid) {
            AnswerCallbackQuery($dataid, '⚠️ نمیشه به خودت ستاره بدی ');
        } else {
            try {
                // $fopen = fopen(BASE_DIR  . '/best_player.txt', 'a');
                // $fwrite = "best_player \n";
                // $fwrite .= "chatid : {$chatid}\n";
                // $fwrite .= "serverID : {$data[ 1 ]}\n";


                $server = new Server($data[1]);
                // $fwrite .= "server : {$server->getStatus()}\n";


                // $server->getStatus() == 'chatting'  $server->exists()
                if ($server and $server->getStatus() == 'chatting') {



                    // DeleteMessage($chatid, $messageid);
                    $selected = $data[2];
                    $selected_role = $data[3];
                    $best_player = new User($selected);
                    $today = date('Y-m-d');

                    $check_multi_vote = (int) $link->get_var("SELECT count(*) FROM `bestplayer_daily` WHERE `user_id` = '{$chatid}' AND `selected` = '{$selected}' AND `created_at` = '{$today}' ");

                    if ($check_multi_vote && $check_multi_vote >= 2) {
                        AnswerCallbackQuery($dataid, "⚠️ در یک روز بیش از 2 بار نمی شود به یک نفر ستاره داد");
                    } else {
                        $total_start = (int) $best_player->get_meta('total_start');
                        $best_player->update_meta('total_start', $total_start + 1);
                        $link->insert('bestplayer_daily', [
                            'user_id' => $chatid,
                            'selected' => $selected,
                            'selected_role' => $selected_role,
                            'created_at' => $today,
                        ]);
                        $keyboard = [];
                        $users_server = $server->users();
                        $i = 0;
                        $i2 = 0;
                        foreach ($users_server as $item) {

                            $keyboard[$i][] = $telegram->buildInlineKeyboardButton('⭐️ ' . $item->get_name() . ($item->is($selected) ? '✔️' : ''), '', '/best_player-close-0-0');
                            $i2++;
                            if ($i2 % 2 === 0)
                                $i++;

                        }
                        if (count($keyboard)) {
                            AnswerCallbackQuery($dataid, '⭐️ شما ' . $best_player->get_name() . ' را انتخاب کردید');
                            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
                        }
                    }
                } else {
                    AnswerCallbackQuery($dataid, '📛 این سرور بسته شده است.', true);
                    // AnswerCallbackQuery( $dataid, '🚸 این پنل منقضی شده است. لطفا از پنل های جدید استفاده کنید.', true );
                }
                // fwrite($fopen, $fwrite);
                // fclose($fopen);
            } catch (Exception | Throwable $e) {
                AnswerCallbackQuery($dataid, '📛 این سرور بسته شده است.', true);

                $message = "<b>🔴 WARNING ERROR ON CARDS 🔴</b>" . "\n";
                $message .= "<b>👉 Error File : { " . $e->getFile() . ':' . "<code>" . $e->getLine() . "</code>" . " }</b>" . "\n";
                if (isset($server) && $server instanceof Server && $server->getId() > 0) {

                    $message .= "<i>ERROR Server: {" . $server->getId() . "}</i>" . "\n \n";

                }
                $message .= "<b>👾 Error Content:</b>" . "\n \n";
                $message .= "<b><code>" . $e->getMessage() . "</code></b>";
                SendMessage(202910544, $message, null, null, 'html');

            }

        }

        break;

    case 'new_report':
    case 'report':
        $message = 'نوع تخلف [[user]] را مشخص کنید.';
        __replace__($message, ['[[user]]' => "<u>" . user($data[1])->name . "</u>"]);
        if ($data[0] == 'new_report') {
            SendMessage(
                $chatid,
                $message,
                $telegram->buildInlineKeyBoard([
                    [$telegram->buildInlineKeyboardButton('استفاده از الفاظ رکیک', '', 'wg-' . $chatid . '-' . $data[1] . '-' . apply_filters('filter_report_name', 'استفاده از الفاظ رکیک'))],
                    [$telegram->buildInlineKeyboardButton('تقلب در بازی', '', 'wg-' . $chatid . '-' . $data[1] . '-' . apply_filters('filter_report_name', 'تقلب در بازی'))],
                    [$telegram->buildInlineKeyboardButton('لو دادن نقش خود یا دیگران', '', 'wg-' . $chatid . '-' . $data[1] . '-' . apply_filters('filter_report_name', 'لو دادن نقش خود یا دیگران'))],
                    [$telegram->buildInlineKeyboardButton('ارسال شماره یا آیدی ', '', 'wg-' . $chatid . '-' . $data[1] . '-' . apply_filters('filter_report_name', 'ارسال شماره یا آیدی'))],
                    [$telegram->buildInlineKeyboardButton('ایجاد اختلال در نظم بازی', '', 'wg-' . $chatid . '-' . $data[1] . '-' . apply_filters('filter_report_name', 'ایجاد اختلال در نظم بازی'))],
                    [$telegram->buildInlineKeyboardButton('تبلیغات', '', 'wg-' . $chatid . '-' . $data[1] . '-' . apply_filters('filter_report_name', 'تبلیغات'))],
                    [$telegram->buildInlineKeyboardButton('اسم نامتعارف', '', 'wg-' . $chatid . '-' . $data[1] . '-' . apply_filters('filter_report_name', 'اسم نامتعارف'))],
                ]),
                null,
                'html'
            );
        } else {
            EditMessageText(
                $chatid,
                $messageid,
                $message,
                $telegram->buildInlineKeyBoard([
                    [$telegram->buildInlineKeyboardButton('استفاده از الفاظ رکیک', '', 'wg-' . $chatid . '-' . $data[1] . '-' . apply_filters('filter_report_name', 'استفاده از الفاظ رکیک'))],
                    [$telegram->buildInlineKeyboardButton('تقلب در بازی', '', 'wg-' . $chatid . '-' . $data[1] . '-' . apply_filters('filter_report_name', 'تقلب در بازی'))],
                    [$telegram->buildInlineKeyboardButton('لو دادن نقش خود یا دیگران', '', 'wg-' . $chatid . '-' . $data[1] . '-' . apply_filters('filter_report_name', 'لو دادن نقش خود یا دیگران'))],
                    [$telegram->buildInlineKeyboardButton('ارسال شماره یا آیدی ', '', 'wg-' . $chatid . '-' . $data[1] . '-' . apply_filters('filter_report_name', 'ارسال شماره یا آیدی'))],
                    [$telegram->buildInlineKeyboardButton('ایجاد اختلال در نظم بازی', '', 'wg-' . $chatid . '-' . $data[1] . '-' . apply_filters('filter_report_name', 'ایجاد اختلال در نظم بازی'))],
                    [$telegram->buildInlineKeyboardButton('تبلیغات', '', 'wg-' . $chatid . '-' . $data[1] . '-' . apply_filters('filter_report_name', 'تبلیغات'))],
                    [$telegram->buildInlineKeyboardButton('اسم نامتعارف', '', 'wg-' . $chatid . '-' . $data[1] . '-' . apply_filters('filter_report_name', 'اسم نامتعارف'))], /*[
$telegram->buildInlineKeyboardButton('سایر موارد - ارسال به پشتیبانی', '', 'wg-سایر موارد - ارسال به پشتیبانی')
],*/
                ]),
                null,
                'html'
            );
        }
        break;

    case 'wg':

        switch ($data[3]) {

            case 'C2':
            case 'C3':
            case 'C5':

                $user_report = new User($data[2]);

                $message = '📝 درصورت نیاز یک یادداشت برای علت گزارش [[user]] بنویسید.' . "\n";
                if (isset($data[4]) && $data[4] == 1) {
                    $message = '📝 درصورت نیاز یک یادداشت برای علت گزارش بنویسید.' . "\n";
                }
                $message .= 'گزارشات دارای یادداشت سریعتر بررسی میشه.' . "\n \n";
                $message .= 'پیام خود را ارسال کنید 👇';

                EditMessageText(
                    $chatid,
                    $messageid,
                    __replace__($message, [
                        '[[user]]' => $user_report->user()->name
                    ]),
                    $telegram->buildInlineKeyBoard([
                        [
                            $telegram->buildInlineKeyboardButton('📤 ارسال بدون یادداشت', '', str_replace('wg', 'wg_2', $Data))
                        ]
                    ])
                );
                $user->setStatus('send_note_report')->setData($Data);

                break;

            default:
                goto REPORT_FINALLY;

        }

        break;

    case 'wg_2':

        REPORT_FINALLY:
        $server_id = (int) get_game()->server_id;
        $report = get_report($chatid, $data[2], $server_id);
        if (empty($report) || $report->server_id == 0) {
            if (check_ban($data[2])) {
                $user = user($data[2]);

                $message = '⚠️ گزارش تخلف [[user]] ارسال شد .' . "\n \n";
                if (isset($data[4]) && $data[4] == 1) {
                    $message = '⚠️ گزارش تخلف ارسال شد .' . "\n \n";
                }
                $message .= 'نوع تخلف : [[wg]]' . "\n \n";
                $message .= 'در صورت تایید ، نتیجه آن اعلام خواهد شد.';
                EditMessageText(
                    $chatid,
                    $messageid,
                    __replace__($message, [
                        '[[user]]' => "<u>" . $user->name . "</u>",
                        '[[wg]]' => apply_filters('filter_report_name', $data[3]),
                    ]),
                    null,
                    null,
                    'html'
                );

                add_filter('filter_token', function () {
                    global $token_bot;
                    return $token_bot[0];
                });

                $reports = get_report_by_server($server_id, $data[2]);
                $message_id = null;
                $message = '❗️گزارش جدید ' . "\n \n";

                if (count($reports) > 0) {

                    /* @var $report \helper\Report */
                    foreach ($reports as $report) {

                        $message .= '🟩 گزارش کننده : [[user]] `[[user_id]]`' . "\n";
                        $message .= '🟨 [[wg]]' . "\n";

                        __replace__($message, [
                            '[[user_id]]' => $report->user_id,
                            '[[user]]' => user($report->user_id)->name,
                            '[[wg]]' => apply_filters('filter_report_name', $report->type),
                        ]);

                        if ($message_id == null && !empty($report->message_id)) {

                            $message_id = $report->message_id;

                        }

                    }

                }


                $message .= '🟩 گزارش کننده : [[user]] `[[user_id]]`' . "\n";
                $message .= '🟨 [[wg]]' . "\n";
                $message .= "\n" . '🟥 گزارش شده : [[user_wg]] `[[user_wg_id]]`' . "\n";
                $message .= '📝 یادداشت : ' . (!is_note_by_server($server_id) ? 'ندارد' : 'دارد') . "\n";

                add_filter('send_massage_text', function ($text) {
                    return tr_num($text, 'en', '.');
                }, 11);

                $server_id = get_game()->server_id;
                if (isset($chatid) && isset($data[2]) && isset($server_id) && $data[3]) {

                    $report_id = add_report($chatid, $data[2], $server_id, $data[3]);
                    if (isset($report_id) && !empty($report_id)) {

                        $keyboard = $telegram->buildInlineKeyBoard([[$telegram->buildInlineKeyboardButton('💭 پیام ها ، ⛔️ اعمال مسدودی', '', 'block-' . $report_id)]]);

                        if (count($reports) > 0 && $message_id > 0) {

                            EditMessageText(
                                GP_MANAGER,
                                $message_id,
                                __replace__($message, [
                                    '[[user]]' => user()->name,
                                    '[[user_wg]]' => $user->name,
                                    '[[user_id]]' => $chatid,
                                    '[[user_wg_id]]' => $user->user_id,
                                    '[[wg]]' => apply_filters('filter_report_name', $data[3]),
                                ]),
                                $keyboard,
                                null,
                                'MarkDown'
                            );

                        } else {

                            $messageid = SendMessage(
                                GP_MANAGER,
                                __replace__($message, [
                                    '[[user]]' => user()->name,
                                    '[[user_wg]]' => $user->name,
                                    '[[user_id]]' => $chatid,
                                    '[[user_wg_id]]' => $user->user_id,
                                    '[[wg]]' => apply_filters('filter_report_name', $data[3]),
                                ]),
                                $keyboard
                            );
                            $message_id = $messageid->message_id;

                        }

                        update_report($data[2], $server_id, [

                            'message_id' => $message_id

                        ]);
                        if (status() == 'send_note_report')
                            update_status('');

                    }

                } else {

                    $message = 'خطا سیستمی رخ داد .. لطفا با پشتیبانی تماس بگیرید.';
                    Message();

                }

            } else {
                $message = '⚠️ خطا ، شخص مورد نظر در حال حاضر مسدود است.';
                EditMessageText($chatid, $messageid, $message);
            }
        } else {
            $message = '⚠️ خطا ، شما قبلا این فرد را گزارش کرده اید.';
            EditMessageText($chatid, $messageid, $message);
        }

        break;

    case 'stay_server':
        DeleteMessage($chatid, $messageid);
        SendMessage($chatid, '🏳️ اوکی ، به کارت ادامه بده .');
        break;

    case 'exit_game':

        if ($user->user_on_game()) {

            $server = new Server($user->getServerId());
            if ($server->exists()) {

                if ($user->dead()) {

                    if (leave_server($chat_id)) {

                        $message = '🔸 شما از بازی خارج شدید .' . "\n \n" . 'منوی اصلی 👇';
                        $user->setKeyboard(KEY_START_MENU)->SendMessageHtml()->setStatus('');
                        $message = $user->get_league()->emoji . ' ' . '<u>' . $user->get_name() . '</u>' . ' از بازی خارج شد.';
                        foreach ($server->users() as $user_game) {

                            if (!$user->is($user_game) && $user_game->is_ban() && $user_game->is_user_in_game())
                                $user_game->SendMessageHtml($message);

                        }

                    } else {

                        $message = '❌ در خروج از بازی مشکلی پیش آمد!';
                        SendMessage($chat_id, $message);

                    }

                    DeleteMessage($chatid, $messageid);

                } else {

                    $message = '⛔️ شما هم اکنون در حال بازی هستید و نمیتوانید از بازی خارج شوید.';
                    SendMessage($chat_id, $message, KEY_GAME_ON_MENU);

                }

            } else {

                throw new ExceptionWarning('در شاناسایی سرور شما خطایی رخ داد.');

            }

        } else {

            DeleteMessage($chatid, $messageid);
            do_action('start');

        }

        break;

    case 'change_name':
        do_action('check_ban');
        apply_filters('filter_user_in_game', $chatid);
        $message = 'شما تصمیم به تغییر نام خود گرفتید .' . "\n \n";
        $message .= '❓شرایط نام انتخابی :' . "\n";
        $message .= '❕فقط حروف فارسی مجاز است .' . "\n";
        $message .= '❕نام شما کمتر از ۳ کلمه و بیشتر از ۱۵ کلمه نباشد .' . "\n";
        $message .= '❕استفاده از اعراب مجاز است .' . "\n \n";
        $message .= '🔅 نام خود را ارسال کنید :';

        $last_name = $user->user()->name;
        foreach ($user->getNames() as $name) {

            $keyboard[][] = $telegram->buildInlineKeyboardButton('👤 ' . $name->name . ($last_name == $name->name ? ' ✔️' : ''), '', 'set_name-' . $name->id);

        }

        $keyboard[][] = $telegram->buildInlineKeyboardButton('📛 انصراف', '', 'menu_start');
        SendMessage($chat_id, $message, $telegram->buildInlineKeyBoard($keyboard));
        update_status('change_name');
        break;

    case 'set_name':

        do_action('check_ban');
        apply_filters('filter_user_in_game', $chatid);

        $name = $user->getNameByID($data[1]);

        update_user(['name' => trim(remove_emoji($name)), 'status' => '']);

        $message = '✅ نام مستعار شما به « [[name]] » تغییر یافت .' . "\n \n";
        $message .= 'منوی اصلی 👇';
        SendMessage($chat_id, __replace__($message, ['[[name]]' => trim(remove_emoji($name))]), KEY_START_MENU);
        DeleteMessage($chatid, $messageid);

        break;

    case 'menu_start':
        DeleteMessage($chatid, $messageid);
        $message = 'حله .' . "\n" . 'منوی اصلی 👇';
        Message();
        update_status('');
        break;

    case 'change_league':
        apply_filters('filter_user_in_game', $chatid);
        $message = '⛱ لیگ های شخصی خودتون رو بسازید .' . "\n \n";
        $message .= 'از لیست پایین میتونید لیگتون رو عوض کنید.' . "\n";
        $message .= 'یا لیگ منحصر جدید اضافه کنید .' . "\n \n";
        $message .= 'انتخاب کنید 👇';

        $user_vip_league = get_vip_league_user($chatid);
        $league_user = user()->league;
        $league = get__league_user($chatid);

        $i = 1;
        $row = 0;

        $keyboard[$row][] = $telegram->buildInlineKeyboardButton($league->icon . (is_null($league_user) ? ' ✔️' : ''), '', 'change_vip_league-0');

        if ($user->get_rank_week() <= 10 && $user->get_rank_week() > 0) {

            $i++;
            $keyboard[$row][] = $telegram->buildInlineKeyboardButton('⚡️ برترین هفتگی' . (intval($league_user) === 0 && !is_null($league_user) ? ' ✔️' : ''), '', 'change_vip_league-*');

        }

        $i = 3;

        foreach ($user_vip_league as $item) {

            if ($i++ % 3 == 0)
                $row++;
            $keyboard[$row][] = $telegram->buildInlineKeyboardButton($item->emoji . ' ' . $item->name . ($league_user == $item->id ? ' ✔️' : ''), '', 'change_vip_league-' . $item->id);

        }

        $keyboard[][] = $telegram->buildInlineKeyboardButton('➕ اضافه کردن لیگ منحصر', '', 'vip_league');


        SendMessage($chatid, $message, $telegram->buildInlineKeyBoard($keyboard));
        break;

    case 'change_vip_league':

        apply_filters('filter_user_in_game', $chatid);

        if (check_time_chat($chatid, 3)) {

            $last_league_user = user()->league;

            if ($data[1] == '*') {

                update_user([

                    'league' => 0

                ]);

            } elseif ($data[1] == 0) {

                update_user(['league' => null]);

            } elseif (is_numeric($data[1]) && $data[1] > 0) {

                update_user([

                    'league' => $data[1]

                ]);

            }

            $message = '⛱ لیگ های شخصی خودتون رو بسازید .' . "\n \n";
            $message .= 'از لیست پایین میتونید لیگتون رو عوض کنید.' . "\n";
            $message .= 'یا لیگ منحصر جدید اضافه کنید .' . "\n \n";
            $message .= 'انتخاب کنید 👇';

            $user_vip_league = get_vip_league_user($chatid);
            $league_user = user()->league;
            $league = get__league_user($chatid);

            $i = 1;
            $row = 0;

            $keyboard[$row][] = $telegram->buildInlineKeyboardButton($league->icon . (is_null($league_user) ? ' ✔️' : ''), '', 'change_vip_league-0');

            if ($user->get_rank_week() <= 10 && $user->get_rank_week() > 0) {

                $i++;
                $keyboard[$row][] = $telegram->buildInlineKeyboardButton('⚡️ برترین هفتگی' . (intval($league_user) === 0 && !is_null($league_user) ? ' ✔️' : ''), '', 'change_vip_league-*');

            }

            $i = 3;

            if ($data[1] == $last_league_user) {

                foreach ($user_vip_league as $item) {

                    if ($i++ % 3 == 0)
                        $row++;
                    $keyboard[$row][] = $telegram->buildInlineKeyboardButton($item->emoji . ' ' . $item->name . ($last_league_user == $item->id ? ' ✏️' : ''), '', 'change_vip_league-' . $item->id);

                }

                $user->setStatus('change_name_vip_league')->setData($last_league_user);

            } else {

                foreach ($user_vip_league as $item) {

                    if ($i++ % 3 == 0)
                        $row++;
                    $keyboard[$row][] = $telegram->buildInlineKeyboardButton($item->emoji . ' ' . $item->name . ($league_user == $item->id ? ' ✔️' : ''), '', 'change_vip_league-' . $item->id);

                }

            }


            $keyboard[][] = $telegram->buildInlineKeyboardButton('➕ اضافه کردن لیگ منحصر', '', 'vip_league');

            EditMessageText($chatid, $messageid, $message, $telegram->buildInlineKeyBoard($keyboard));

        } else {
            AnswerCallbackQuery($dataid, '⚠️ هر 3 ثانیه یک بار میتوانید لیگتان را تغییر بدهید.');
        }


        break;

    case 'vip_league':
        apply_filters('filter_user_in_game', $chatid);
        update_status('get_vip_emoji_league');
        $message = 'ایموجی موردعلاقه خودتون رو از کیبورد انتخاب کنید و بفرستید  👇';
        EditMessageText($chatid, $messageid, $message);
        break;

    case 'select_vip_league':
        $league = get_vip_league($data[1]);
        if (is_object($league)) {
            $message = '🔸لیگ انتخاب شده : [[league_name]]' . "\n";
            $message .= '🔸سکه مورد نیاز برای خرید : [[league_coin]]';
            $message .= '🔸موجودی سکه شما : [[coin]]' . "\n \n";
            $message .= 'برای ادامه مراحل خرید تایید را بزنید 👇';
            EditMessageText(
                $chatid,
                $messageid,
                __replace__($message, [
                    'league_name' => $league->emoji,
                    'league_coin' => $league->coin,
                    'coin' => user()->coin
                ]),
                $telegram->buildInlineKeyBoard([
                    [
                        $telegram->buildInlineKeyboardButton('✅ تایید خرید', '', 'buy_vip_league-' . $league->id),
                        $telegram->buildInlineKeyboardButton('💰 افزایش سکه', '', 'buy_vip_league-' . $league->id),
                    ]
                ])
            );
        }
        break;

    case 'buy_vip_league':

        $league = get_vip_league($data[1]);
        $user = new User($chatid, -1);
        if ($user->has_coin($league->coin)) {

            $message = 'لیگ شما ثبت شد . ' . "\n \n";
            $message .= '➖ یک نام برای لیگ خود انتخاب کنید :';
            add_log('vip_league', 'لیگ جدیدی خریداری کرده است: ' . $league->emoji, $chatid);
            update_data($data[1]);
            update_status('name_vip_league');
            EditMessageText($chatid, $messageid, $message);

        } else {

            SendMessage($chatid, '⚠️ متاسفانه موجودی سکه شما برای خرید این لیگ کافی نیست.');
            $coin = [
                100 => PLAN_1,
                200 => PLAN_2,
                400 => PLAN_3,
                800 => PLAN_4,
                1000 => PLAN_5,
                3000 => PLAN_6,
                5000 => PLAN_7,
            ];
            foreach ($coin as $amount => $plan) {
                if ($league->coin - $user->coin <= $amount) {
                    break;
                }
            }


            $auth = factor($plan, URL_VERIFY . "?bot=" . $BOT_ID, 'خرید لیگ منحصر ' . $chatid);
            add_factor($chatid, $plan, $auth, $amount);

            $message = '♨️ [[name]] عزیز 
شما بسته [[coin]] سکه ، [[amount]] تومان را برای خرید انتخاب کرده اید .

❗️لطفا به نکات زیر دقت کنید :
۱. بعد از پرداخت کمی صبر کنید تا فیش خرید صادر شود .
۲. بهتر است برای پرداخت از مرورگر گوگل کروم استفاده کنید.
۳. درصورت مشکل در انتقال به درگاه بهتر است فیلترشکن را خاموش کنید.
۴. درصورت برداشت وجه از حساب و ناموفق بودن تراکنش ، مبلغ برداشتی برگشت داده میشود.

*با کپی کردن لینک زیر میتوانید از بقیه بخواهید برای شما خرید انجام دهند .*

`' . zarinpal_link($auth) . '`

درصورت تایید ، دکمه زیر را انتخاب کنید 👇
';

            __replace__($message, [
                '[[name]]' => user()->name,
                '[[coin]]' => $amount,
                '[[amount]]' => number_format($plan / 10)
            ]);
            //            $message .= '<a href="' . zarinpal_link($auth) . '"> </a>';

            $telegram->sendMessage([

                'chat_id' => $user->getUserId(),
                'text' => $message,
                'reply_markup' => $telegram->buildInlineKeyBoard([
                    [
                        $telegram->buildInlineKeyboardButton('پرداخت از طریق درگاه اینترنتی', zarinpal_link($auth))
                    ]
                ]),
                'parse_mode' => 'MarkDown'

            ]);

        }
        break;

    case 'shop':
        $message = '💰 موجودی سکه شما : [[coin]]

با استفاده از سکه میتونید :

۱- ⛱ برای خودتون لیگ اختصاصی با ایموجی دلخواه انتخاب کنید.

۲- 🪄 توی بازی از جادوهای مختلف استفاده کنید تا برنده بشید .

۳- ♨️ نقش دلخواه خودتون رو در هر بازی انتخاب کنید .

۴- 📨  توی بازی پیام خصوصی بدون محدودیت کلمات انگلیسی بفرستید .

۵ - 🎁 به دوستاتون سکه هدیه بدین که توضحیش رو میتونید اینجا /cointransfer ببینید . 

۶- 🌟 عضو کاربرای vip ربات بشین و از خدمات پشتیبانی سریعتر استفاده کنید .

انتخاب کنید از کدام بسته خرید میکنید : 👇';
        $message = str_replace('[[coin]]', user()->coin, $message);
        SendMessage($chatid, $message, KEY_SHOP_MENU);
        break;

    case 'select_role':
        $message = '🔸 نقش مورد علاقه خود را انتخاب کنید تا در پروفایل شما نمایش داده شود .';
        SendMessage(
            $chatid,
            $message,
            $telegram->buildInlineKeyBoard([
                [$telegram->buildInlineKeyboardButton('🟢 انتخاب نقش از تیم شهروند', '', 'roles-1')],
                [$telegram->buildInlineKeyboardButton('🔴 انتخاب نقش از تیم مافیا', '', 'roles-2')],
                [$telegram->buildInlineKeyboardButton('🟡 انتخاب نقش از مستقل', '', 'roles-3')],
            ])
        );
        break;

    case 'roles':
        $league_id = get_league_user($chatid)->id;
        $roles = get_keyboard_roles_by_group_and_game($data[1], $league_id);
        $i = 0;
        $x = 3;
        $row = 0;
        $keyboard = [];
        foreach ($roles as $role) {
            $keyboard[$row][$i] = $telegram->buildInlineKeyboardButton($role->icon, '', 'change_role-' . $role->id);
            $i++;
            if ($i == $x) {
                $i = 0;
                $row++;
            }
        }
        $message = 'انتخاب کنید 👇';
        EditMessageText($chatid, $messageid, $message, $telegram->buildInlineKeyBoard($keyboard));
        break;

    case 'change_role':
        update_user_meta($chatid, 'role', $data[1]);
        $message = '♨️ نقش مورد علاقه شما به پروفایل اضافه شد . ' . "\n \n" . 'منوی اصلی 👇';
        EditMessageText($chatid, $messageid, $message);
        break;

    case 'rank_top_all':


        $message = '📊 لیست برترین های ایرانی مافیا ' . "\n \n";
        $list_users = get_top_rank_points();
        $leagues = [];
        foreach ($list_users as $id => $user) {
            $user_league = get__league_user($user->user_id);
            $leagues[$user_league->id][] = $user;
        }

        $x = 1;

        foreach ($leagues as $league_id => $item) {
            $league = get__league($league_id);
            $message .= $league->icon . ' 👇' . "\n";
            foreach ($item as $user) {
                if (!empty($user->user->name)) {

                    switch ($x) {
                        case 1:

                            $emoji_rank = '🥇';

                            break;

                        case 2:

                            $emoji_rank = '🥈';

                            break;

                        case 3:

                            $emoji_rank = '🥉';

                            break;

                        default:
                            $emoji_rank = '';
                            break;

                    }

                    $message .= ($chat_id == $user->user_id ? '👈 ' : '[[' . $x . ']]  ') . "<b>" . $user->user->name . "</b>" . ($chat_id == $user->user_id ? ' (شما)' : ' ') . '      - [[point]] 🌟' . ($emoji_rank) . "\n";
                    __replace__($message, [
                        '[[10]]' => '🔟',
                        '[[1]]' => '1️⃣',
                        '[[2]]' => '2️⃣',
                        '[[3]]' => '3️⃣',
                        '[[4]]' => '4️⃣',
                        '[[5]]' => '5️⃣',
                        '[[6]]' => '6️⃣',
                        '[[7]]' => '7️⃣',
                        '[[8]]' => '8️⃣',
                        '[[9]]' => '9️⃣',
                        '[[point]]' => "<b>" . tr_num($user->point, 'fa', '.') . "</b>",
                    ]);
                    if ($user->user_id == $chat_id) {
                        $rank = $x;
                    }
                    $x++;
                    $user_list[] = $user->user_id;
                }
                if ($x > 10) {
                    break 2;
                }
            }
            $message .= "\n";
        }


        $message .= "\n" . '🔹رتبه شما : [[rank]]';

        $message .= "\n" . '🔸امتیاز شما : [[point]]' . "\n \n";
        $message .= '❗️نحوه امتیاز گرفتن : /help_score' . "\n";
        $message .= '<a href="https://t.me/iranimafia/89">❗️تمامی لیگ های بازی</a>' . "\n \n";
        $message .= '@iranimafia';

        $number_to_word = new NumberToWord();
        $rank = get_rank_user_in_global($chat_id);
        $result = $rank > 5 ? $rank : $number_to_word->numberToWords($rank);

        __replace__($message, [
            '[[point]]' => "<b>" . tr_num(get_point($chat_id), 'fa', '.') . "</b>",
            '[[rank]]' => "<b>" . tr_num($result, 'fa', '.') . "</b>"
        ]);

        $emoji = '';
        add_filter('filter_league_user', function ($query) {
            global $emoji;
            $emoji = $query->emoji;
        }, 1);
        $user_league = get__league_user($chatid);

        EditMessageText(
            $chatid,
            $messageid,
            $message,
            $telegram->buildInlineKeyBoard([
                [$telegram->buildInlineKeyboardButton('📊 برترین های بازی ' . '✔️', '', 'rank_top_all')],
                [
                    $telegram->buildInlineKeyboardButton('📆 هفتگی', '', 'rank_top_week'),
                    $telegram->buildInlineKeyboardButton('📅 روزانه', '', 'rank_top_today'),
                    $telegram->buildInlineKeyboardButton(($emoji . ' لیگ من'), '', 'rank_top_my_league'),
                ]
            ]),
            null,
            'html'
        );
        break;

    case 'rank_top_my_league':


        $message = '📈 لیست رقابت امتیازات نزدیک به شما' . "\n \n";
        $league = get__league_user($chatid);
        $next_league = get__league($league->id + 1);
        $user_point = (int) get_point($chatid);
        $list_up_users = get_rank_up_user($user_point, $next_league->point ?? $league->point, 'ASC', 4);
        $list_up_users = array_reverse($list_up_users);

        $x = 1;
        $users_list = [];
        $message .= $league->icon . ' 👇' . "\n";
        foreach ($list_up_users as $user) {
            $user_info = get_user($user->user_id);
            if (!empty($user_info->name)) {
                $users_list[] = $user->user_id;
                $message .= ($chatid == $user->user_id ? '👈 ' : '[[' . $x . ']]  ') . "<b>" . $user_info->name . "</b>" . ($chatid == $user->user_id ? ' (شما)' : ' ') . '      - [[point]] 🌟' . "\n";
                __replace__($message, [
                    '[[10]]' => '🔟',
                    '[[1]]' => '1️⃣',
                    '[[2]]' => '2️⃣',
                    '[[3]]' => '3️⃣',
                    '[[4]]' => '4️⃣',
                    '[[5]]' => '5️⃣',
                    '[[6]]' => '6️⃣',
                    '[[7]]' => '7️⃣',
                    '[[8]]' => '8️⃣',
                    '[[9]]' => '9️⃣',
                    '[[point]]' => "<b>" . tr_num(get_point($user->user_id), 'fa', '.') . "</b>",
                ]);
                $x++;
                if ($x == 6)
                    break;
            }
        }

        $list_down_users = get_rank_down_user($user_point, $users_list, (10 - count($users_list)));

        foreach ($list_down_users as $user) {
            $user = get_user($user->user_id);
            if (!empty($user->name)) {
                $message .= ($chatid == $user->user_id ? '👈 ' : '[[' . $x . ']]  ') . "<b>" . $user->name . "</b>" . ($chatid == $user->user_id ? ' (شما)' : ' ') . '      - [[point]] 🌟' . "\n";
                __replace__($message, [
                    '[[10]]' => '🔟',
                    '[[1]]' => '1️⃣',
                    '[[2]]' => '2️⃣',
                    '[[3]]' => '3️⃣',
                    '[[4]]' => '4️⃣',
                    '[[5]]' => '5️⃣',
                    '[[6]]' => '6️⃣',
                    '[[7]]' => '7️⃣',
                    '[[8]]' => '8️⃣',
                    '[[9]]' => '9️⃣',
                    '[[point]]' => "<b>" . tr_num(get_point($user->user_id), 'fa', '.') . "</b>",
                ]);
                $x++;
                if ($x > 10)
                    break;
            }
        }


        $message .= "\n" . '🔹رتبه شما : [[rank]]' . "\n";
        $message .= '🔸امتیاز شما : [[point]]' . "\n \n";

        $message .= '❗️نحوه امتیاز گرفتن : /help_score' . "\n";
        $message .= '<a href="https://t.me/iranimafia/89">❗️تمامی لیگ های بازی</a>' . "\n \n";
        $message .= '@iranimafia';

        $rank = get_rank_user_in_league($chatid);

        $number_to_word = new NumberToWord();
        $result = $rank >= 10 ? $rank : $number_to_word->numberToWords($rank);

        __replace__($message, [
            '[[point]]' => "<b>" . tr_num(get_point($chatid), 'fa', '') . "</b>",
            '[[rank]]' => "<b>" . tr_num($result, 'fa', '') . "</b>",
        ]);

        $emoji = '';
        add_filter('filter_league_user', function ($query) {
            global $emoji;
            $emoji = $query->emoji;
        }, 1);
        $user_league = get__league_user($chatid);

        EditMessageText(
            $chatid,
            $messageid,
            $message,
            $telegram->buildInlineKeyBoard([
                [$telegram->buildInlineKeyboardButton('📊 برترین های بازی ', '', 'rank_top_all')],
                [
                    $telegram->buildInlineKeyboardButton('📆 هفتگی', '', 'rank_top_week'),
                    $telegram->buildInlineKeyboardButton('📅 روزانه', '', 'rank_top_today'),
                    $telegram->buildInlineKeyboardButton(($emoji . ' لیگ من') . '✔️', '', 'rank_top_my_league'),
                ]
            ]),
            null,
            'html'
        );
        break;

    case 'rank_top_week':


        $number_to_word = new NumberToWord();

        $message = '📆 لیست برترین های هفتگی ایرانی مافیا' . "\n \n" /*. '🔻 هفته #' . $number_to_word->numberToWords(get_option('week')) . "\n \n"*/
        ;
        $list_users = get_top_rank_points_week();

        $x = 1;
        foreach ($list_users as $item) {
            $name = $item->user()->name;
            $message .= ($chatid == $item->getUserId() ? '👈 ' : '[[' . $x . ']]  ') . $item->league()->emoji . ' ' . "<b>" . (empty($name) ? 'بینام' : $name) . "</b>" . ($chatid == $item->getUserId() ? ' (شما)' : ' ') . '      - [[point]] 🌟' . "\n";
            __replace__($message, [
                '[[10]]' => '🔟',
                '[[1]]' => '1️⃣',
                '[[2]]' => '2️⃣',
                '[[3]]' => '3️⃣',
                '[[4]]' => '4️⃣',
                '[[5]]' => '5️⃣',
                '[[6]]' => '6️⃣',
                '[[7]]' => '7️⃣',
                '[[8]]' => '8️⃣',
                '[[9]]' => '9️⃣',
                '[[point]]' => "<b>" . tr_num($item->get_point_user_week(), 'fa', '.') . "</b>",
            ]);
            $x++;
        }

        $rank = get_rank_user_week($chatid);
        $point = (int) get_point_user_week($chatid);

        if ($rank && $point > 0) {

            $message .= "\n" . '🔹رتبه شما : [[rank]]';

        }

        if ($point > 0) {

            $message .= "\n" . '🔸امتیاز شما : [[point]]' . "\n \n";

        } else {

            $message .= "\n";

        }

        $message .= '❗️نحوه امتیاز گرفتن : /help_score' . "\n";
        $message .= '<a href="https://t.me/iranimafia/89">❗️تمامی لیگ های بازی</a>' . "\n \n";
        $message .= '@iranimafia';

        $number_to_word = new NumberToWord();
        $result = $rank >= 10 ? $rank : $number_to_word->numberToWords($rank);

        __replace__($message, [
            '[[point]]' => "<b>" . tr_num($point, 'fa') . "</b>",
            '[[rank]]' => "<b>" . tr_num($result ?? 0, 'fa') . "</b>",
        ]);

        $emoji = '';
        add_filter('filter_league_user', function ($query) {
            global $emoji;
            $emoji = $query->emoji;
        }, 1);
        $user_league = get__league_user($chatid);

        EditMessageText(
            $chatid,
            $messageid,
            $message,
            $telegram->buildInlineKeyBoard([
                [$telegram->buildInlineKeyboardButton('📊 برترین های بازی ', '', 'rank_top_all')],
                [
                    $telegram->buildInlineKeyboardButton('📆 هفتگی ' . '✔️', '', 'rank_top_week'),
                    $telegram->buildInlineKeyboardButton('📅 روزانه', '', 'rank_top_today'),
                    $telegram->buildInlineKeyboardButton(($emoji . ' لیگ من'), '', 'rank_top_my_league'),
                ]
            ]),
            null,
            'html'
        );
        break;

    case 'rank_top_today':


        $message = '📅 لیست برترین های روزانه ایرانی مافیا' . "\n \n";
        $list_users = get_top_rank_points_today();
        $today = date('Y-m-d');
        $x = 1;
        /** @var \library\User $item */
        foreach ($list_users as $item) {

            $name = $item->user()->name;
            $message .= ($chatid == $item->getUserId() ? '👈 ' : '[[' . $x . ']]  ') . ($item->league()->emoji) . ' ' . "<b>" . (empty($name) ? 'بینام' : $name) . "</b>" . ($chatid == $item->getUserId() ? ' (شما)' : ' ') . '      - [[point]] 🌟' . "\n";
            __replace__($message, [
                '[[10]]' => '🔟',
                '[[1]]' => '1️⃣',
                '[[2]]' => '2️⃣',
                '[[3]]' => '3️⃣',
                '[[4]]' => '4️⃣',
                '[[5]]' => '5️⃣',
                '[[6]]' => '6️⃣',
                '[[7]]' => '7️⃣',
                '[[8]]' => '8️⃣',
                '[[9]]' => '9️⃣',
                '[[point]]' => "<b>" . tr_num($item->get_point_daily_today(), 'fa') . "</b>",
            ]);
            $x++;
        }

        $rank = get_rank_user_today($chatid);

        if ($rank) {
            $message .= "\n" . '🔹رتبه شما : [[rank]]';
        }

        $message .= "\n" . '🔸امتیاز شما : [[point]]' . "\n \n";

        $best_player_today = $link->get_row("SELECT `selected` , count(`selected`) as `star` FROM `bestplayer_daily` WHERE `created_at` = '{$today}' GROUP BY `selected` ORDER by `star` DESC");
        $best_player = new User((int) $best_player_today->selected);
        $message .= "⭐️ برترین ستاره : " . $best_player->league()->emoji . " <b>" . $best_player->user()->name . "</b>     -    <b>" . tr_num($best_player_today->star, 'fa') . "</b> \n \n";


        $message .= '❗️نحوه امتیاز گرفتن : /help_score' . "\n";
        $message .= '<a href="https://t.me/iranimafia/89">❗️تمامی لیگ های بازی</a>' . "\n \n";
        $message .= '@iranimafia';


        $number_to_word = new NumberToWord();
        $result = $rank >= 10 ? $rank : $number_to_word->numberToWords($rank);

        __replace__($message, [
            '[[point]]' => "<b>" . tr_num((int) get_point_user_day($chatid, date('Y-m-d'), '='), 'fa') . "</b>",
            '[[rank]]' => "<b>" . tr_num($result ?? 0, 'fa') . "</b>",
        ]);

        $emoji = '';
        add_filter('filter_league_user', function ($query) {
            global $emoji;
            $emoji = $query->emoji;
        }, 1);
        $user_league = get__league_user($chatid);


        EditMessageText(
            $chatid,
            $messageid,
            $message,
            $telegram->buildInlineKeyBoard([
                [$telegram->buildInlineKeyboardButton('📊 برترین های بازی ', '', 'rank_top_all')],
                [
                    $telegram->buildInlineKeyboardButton('📆 هفتگی', '', 'rank_top_week'),
                    $telegram->buildInlineKeyboardButton('📅 روزانه ' . '✔️', '', 'rank_top_today'),
                    $telegram->buildInlineKeyboardButton(($emoji . ' لیگ من'), '', 'rank_top_my_league'),
                ]
            ]),
            null,
            'html'
        );
        break;

    case 'move_account':
        do_action('check_ban');
        $token = token_security_user($chatid);
        $message = '‼️شما قصد انتقال حساب خود را دارید . لطفاً با دقت مراحل زیر را انجام دهید .' . "\n \n";
        $message .= '➖ کدی که به شما داده می شود را در جایی خارج از تلگرام ذخیره کنید تا پس از حذف حساب فعلی آن را گم نکنید .' . "\n";
        $message .= '➖ برای انتقال اکانت باید حتما اکانت فعلی شما Delete Account شود.' . "\n";
        $message .= '➖ انتقال شامل امتیاز، سکه و همه مشخصات اکانت فعلی می باشد' . "\n";
        $message .= '➖ لطفا کد اعتبار سنجی را به کسی ندهید. درغیراینصورت پشتیبانی ربات هیچ مسئولیتی را نمی‌پذیرد. ' . "\n";
        $message .= '➖ این کد نهایتا تا 48 ساعت فعال می باشد.' . "\n";
        $message .= '➖ جمع بستن امتیازات ممکن نیست از این رو فقط اکانت های بلااستفاده می‌توانند مقصد انتقال باشند .' . "\n \n";
        $message .= 'Your Token:' . "\n";
        $message .= '`[[token]]`' . "\n \n";
        $message .= 'Tap to copy : برای کپی، روی کد بزنید';
        EditMessageText($chatid, $messageid, __replace__($message, ['[[token]]' => $token]), json_encode($callback_query->message->reply_markup));
        break;

    case 'recovery_account':
        do_action('check_ban');
        apply_filters('filter_user_in_game', $chat_id);
        DeleteMessage($chatid, $message);
        $message = '🔰 لطفاً کد اعتبارسنجی خود را وارد کنید تا درصورت تایید ، حساب شما انتقال داده شود .';
        SendMessage($chatid, $message, $telegram->buildKeyBoard([[$telegram->buildKeyboardButton('♨️ بازگشت به منوی اصلی')]]));
        update_status('get_token_recovery_account');
        break;

    case 'profile':

        update_status('');
        $chat_id = $chatid;
        $user = user();
        $User = new User($chat_id, 0);

        $game_count = $User->getCountGame();
        $opration = $User->getResultWinGame();
        $role = get_user_meta($chat_id, 'role');
        $point = get_point($chat_id);
        $user_league = get__league_user($chat_id);

        if (get_user_meta($chat_id, 'dice-date') != date('Y-m-d')) {

            update_user_meta($chat_id, 'dice-count', 0);
            update_user_meta($chat_id, 'dice-date', date('Y-m-d'));

        }

        $dice_user = (int) get_user_meta($chat_id, 'dice-count');

        $dart = $User->get_meta('dart');

        $today = date('Y-m-d');
        $today_star = (int) $link->get_var("SELECT  count(`selected`)  FROM `bestplayer_daily` WHERE `created_at` = '{$today}' and `selected` = '{$chat_id}' ");
        $total_start = $User->get_meta('total_start');


        $message = '💢 پروفایل بازیکن ' . "\n \n";
        $message .= '➖ نام شما : ' . $user->name . "\n";
        $message .= '➖ شناسه شما : ' . '`' . $chat_id . '`' . "\n";
        $message .= '➖ امتیاز : ' . $point . "\n";
        $message .= '➖ لیگ شما : ' . $user_league->icon . "\n";
        $message .= '➖ رتبه در بازی : ' . ($point > 0 ? get_rank_user_in_global($chat_id) : 'ندارید') . "\n";
        $message .= '➖ تعداد موجودی سکه: ' . $user->coin . "\n";
        $message .= '➖ ستاره: ' . $today_star . ' / ' . $total_start . "\n";
        $message .= '➖ تعداد کل بازی‌ها : ' . (int) get_user_meta($chat_id, 'game-count') . "\n";
        $message .= '➖ درصد برد: ' . ($game_count > 0 ? ceil($opration) : 0) . '%' . "\n";
        $message .= '➖ شانس دارت : ' . $dice_user . ' از 5' . "\n";
        $message .= '➖ نقش مورد علاقه : ' . (isset($role) ? get_role($role)->icon : 'انتخاب نشده است') . "\n";
        $message .= '➖ جنسیت : ' . $User->gender() . "\n";
        $message .= '➖ سناریو : ' . $User->getPriority() . "\n";
        $message .= '➖ حریم خصوصی : ' . ($User->get_meta('privacy') == 'unlook' ? 'باز 🔓' : 'قفل 🔒') . "\n";
        $message .= '➖ اشتراک : ' . ($User->haveSubscribe() ? 'فعال است' : 'فعال نیست') . "\n";
        $message .= '➖ بازی شانسی : ' . ($dart == 'dart' || empty($dart) ? '🎯 دارت' : ($dart == 'boling' ? '🎳 بولینگ' : ($dart == 'tas' ? '🎲 تاس' : ($dart == 'car' ? '🎰' : ($dart == 'penalti' ? '⚽ پنالتی' : ($dart == 'bascetbal' ? '🏀 بسکتبال' : '')))))) . "\n \n";
        $message .= 'بروزرسانی در : ' . tr_num(jdate('Y/m/d ➖ H:i'));

        EditMessageText(
            $chat_id,
            $messageid,
            $message,
            $telegram->buildInlineKeyBoard([
                [
                    $telegram->buildInlineKeyboardButton(($user_league->emoji . ' تغییر لیگ'), '', 'change_league'),
                    $telegram->buildInlineKeyboardButton('✏️ تغییر نام', '', 'change_name'),
                ],
                [
                    $telegram->buildInlineKeyboardButton('⚙️ تنظیمات بیشتر', '', 'more_profile')
                ],
            ]),
            null,
            'MarkDown'
        );

        break;

    case 'more_profile':

        $point = get_point($chatid);
        EditKeyboard(
            $chatid,
            $messageid,
            $telegram->buildInlineKeyBoard([
                [
                    $telegram->buildInlineKeyboardButton('🎭 انتخاب نقش مورد علاقه', '', 'select_role'),
                    $telegram->buildInlineKeyboardButton(($point >= 100 ? '♻️ انتقال به اکانت جدید' : ' ♻️ بازیابی حساب کاربری'), '', ($point >= 100 ? 'move_account' : 'recovery_account')),
                ],
                [
                    $telegram->buildInlineKeyboardButton('🔅 جنسیت ' . (empty($user->get_meta('gender')) ? '' : ': ' . $user->gender()), '', 'change_gender'),
                    $telegram->buildInlineKeyboardButton('🎮 سناریو بازی', '', 'setting'),
                ],
                [
                    $telegram->buildInlineKeyboardButton('📇 اشتراک', '', 'subscribe'),
                    $telegram->buildInlineKeyboardButton(($user->get_meta('privacy') == 'unlook' ? ' حریم خصوصی: باز 🔓' : 'حریم خصوصی: قفل 🔒'), '', 'privacy'),
                ],
                [
                    $telegram->buildInlineKeyboardButton('🧩 بازی شانسی', '', 'dart'),
                    $telegram->buildInlineKeyboardButton('👤 پروفایل', '', 'profile')
                ]
//                ,
//                [
//                    $telegram->buildInlineKeyboardButton('🎁 چالش روزانه', '', 'dailychallenge'),
//                ]
            ])
        );

        break;
    case 'dailychallenge':
        $number_of_coins = $user->get_point_daily_today();
        $last_time = $user->get_meta('last_lottery_entry');
        $isToday = $last_time ? date('Y-m-d', $last_time) === date('Y-m-d') : false;
        // $number_of_coins= 45;
        $message = '';
        if ($number_of_coins < 40) {
            $message = "برای استفاده از چالش باید حداقل ۴۰ امتیاز روزانه داشته باشی.";
            EditMessageText(
                $chatid,
                $messageid,
                $message,
                $telegram->buildInlineKeyBoard([
                    [
                        $telegram->buildInlineKeyboardButton('👤 پروفایل', '', 'profile')
                    ]
                ])
            );
        } else if ($isToday && false) {
            $message = "فرصت امروزت رو استفاده کردی ! ";
            EditMessageText(
                $chatid,
                $messageid,
                $message,
                $telegram->buildInlineKeyBoard([
                    [
                        $telegram->buildInlineKeyboardButton('👤 پروفایل', '', 'profile')
                    ]
                ])
            );
        } else {
            $currentUnix = time();
            $user->update_meta('last_lottery_entry', $currentUnix);
            $message = '🎁 #چالش روزانه ' . "\n \n";
            $message .= 'شما با کسب امتیاز مورد نیاز فرصت استفاده از این چالش رو بدست آوردی و میتونی یکی از دکمه های جایزه رو انتخاب کنی 🎉' . "\n";
            $message .= 'جوایز این بخش شانسیه ، سه تا خونه پوچ داریم و ۶ تا جایزه که از ۵ سکه تا ۵۰ سکه هست .' . "\n";
            $message .= 'بزن روی یکی و شانس خودتو امتحان کن 😉' . "\n";
            $dart = $user->get_meta('dart');
            EditMessageText(
                $chatid,
                $messageid,
                $message,
                $telegram->buildInlineKeyBoard([
                    [
                        $telegram->buildInlineKeyboardButton('🎁', '', 'enterlottery'),
                        $telegram->buildInlineKeyboardButton('🎁', '', 'enterlottery'),
                        $telegram->buildInlineKeyboardButton('🎁', '', 'enterlottery'),
                    ],
                    [
                        $telegram->buildInlineKeyboardButton('🎁', '', 'enterlottery'),
                        $telegram->buildInlineKeyboardButton('🎁', '', 'enterlottery'),
                        $telegram->buildInlineKeyboardButton('🎁', '', 'enterlottery'),
                    ],
                    [
                        $telegram->buildInlineKeyboardButton('🎁', '', 'enterlottery'),
                        $telegram->buildInlineKeyboardButton('🎁', '', 'enterlottery'),
                        $telegram->buildInlineKeyboardButton('🎁', '', 'enterlottery'),
                    ],
                    [
                        $telegram->buildInlineKeyboardButton('👤 پروفایل', '', 'profile')
                    ]
                ])
            );
        }

        break;
    case 'enterlottery':
        $last_time = $user->get_meta('last_lottery_entry');
        $isToday = $last_time ? date('Y-m-d', $last_time) === date('Y-m-d') : false;
        $get_number_of_tries = $user->get_meta('number_of_tries'.date('Y-m-d', $last_time));
        $get_number_of_tries = $get_number_of_tries? $get_number_of_tries : 0;
        $get_number_of_tries = $get_number_of_tries +1;
        if ($isToday && $get_number_of_tries >= 2) {
            $message = "فرصت امروزت رو استفاده کردی ! ";
            EditMessageText(
                $chatid,
                $messageid,
                $message,
                $telegram->buildInlineKeyBoard([
                    [
                        $telegram->buildInlineKeyboardButton('👤 پروفایل', '', 'profile')
                    ]
                ])
            );
            break;
        }
        // $currentUnix = time();
        // $user->update_meta('last_lottery_entry', $currentUnix);

        $chance = mt_rand(1, 100);
        $added_coin = 0;
        // 75% chance to return a number between 5 and 50
        if ($chance <= 66) {
            $added_coin = mt_rand(0, 5);
            $array = [5, 15, 25, 40, 45, 50];
            $added_coin = $array[$added_coin];
        }
        if ($added_coin > 0) {
            $user->add_coin($added_coin);
            $message = "تبریک 🎉" . $added_coin . " سکه برنده شدی !😍";
        } else {
            $message = "خونه پوچ بود ! امروز برنده نشدی ☹️";
        }
        $user->update_meta('number_of_tries'.date('Y-m-d', $last_time),$get_number_of_tries);
        EditMessageText(
            $chatid,
            $messageid,
            $message,
            $telegram->buildInlineKeyBoard(
                [
                    [
                        $telegram->buildInlineKeyboardButton('👤 پروفایل', '', 'profile')
                    ]
                ]
            )
        );
        break;
    case 'privacy':

        $privacy = $user->get_meta('privacy');
        $user->update_meta('privacy', ($privacy == 'unlook' ? 'look' : 'unlook'));
        $User = $user;
        $point = get_point($chat_id);
        $user_league = get__league_user($chat_id);

        EditKeyboard(
            $chatid,
            $messageid,
            $telegram->buildInlineKeyBoard([
                [
                    $telegram->buildInlineKeyboardButton('🎭 انتخاب نقش مورد علاقه', '', 'select_role'),
                    $telegram->buildInlineKeyboardButton(($point >= 100 ? '♻️ انتقال به اکانت جدید' : ' ♻️ بازیابی حساب کاربری'), '', ($point >= 100 ? 'move_account' : 'recovery_account')),
                ],
                [
                    $telegram->buildInlineKeyboardButton('🔅 جنسیت ' . (empty($user->get_meta('gender')) ? '' : ': ' . $user->gender()), '', 'change_gender'),
                    $telegram->buildInlineKeyboardButton('🎮 سناریو بازی', '', 'setting'),
                ],
                [
                    $telegram->buildInlineKeyboardButton('📇 اشتراک', '', 'subscribe'),
                    $telegram->buildInlineKeyboardButton(($user->get_meta('privacy') == 'unlook' ? ' حریم خصوصی: باز 🔓' : 'حریم خصوصی: قفل 🔒'), '', 'privacy'),
                ],
                [
                    $telegram->buildInlineKeyboardButton('🧩 بازی شانسی', '', 'dart'),
                    $telegram->buildInlineKeyboardButton('👤 پروفایل', '', 'profile')
                ]
            ])
        );

        break;

    case 'move_coin':

        $coin = $data[1];
        $user_id = $data[2];
        $user_coin = user()->coin;
        if (has_coin($chatid, $coin)) {

            demote_coin($chatid, $coin);
            add_coin($user_id, $coin);
            $log = 'تعداد [coin] به کاربر [user] ارسال کرد.';
            add_log('coin', __replace__($log, ['[coin]' => $coin, '[user]' => $user_id]), $chatid);
            $message = '🪙 ' . "<u><b>" . '[[coin]] سکه ' . "</b></u>" . ' از طرف شما برای [[user]] ارسال شد ✅';
            add_filter('send_massage_text', function ($text) {
                return tr_num($text, 'en', '.');
            }, 11);
            EditMessageText(
                $chatid,
                $messageid,
                __replace__($message, [
                    '[[coin]]' => $coin,
                    '[[user]]' => "<u><b>" . user($user_id)->name . "</b></u>"
                ]),
                null,
                null,
                'html'
            );
            $message = '♨️ [[coin]] سکه از طرف [[user]] برای شما ارسال شد .';
            add_filter('send_massage_text', function ($text) {
                return tr_num($text, 'en', '.');
            }, 11);
            SendMessage(
                $user_id,
                __replace__($message, [
                    '[[coin]]' => $coin,
                    '[[user]]' => "<u>" . user($chatid)->name . "</u>"
                ]),
                null,
                null,
                'html'
            );

        } else {
            $message = 'متاسفانه موجودی سکه شما کافی نیست.';
            EditMessageText($chatid, $messageid, $message);
        }

        break;

    case 'move_coin_anonymous':

        $coin = $data[1];
        $user_id = $data[2];
        $user_coin = user()->coin;
        if (has_coin($chatid, $coin)) {

            demote_coin($chatid, $coin);
            add_coin($user_id, $coin);
            $log = 'تعداد [coin] به کاربر [user] ارسال کرد.';
            add_log('coin', __replace__($log, ['[coin]' => $coin, '[user]' => $user_id]), $chatid);
            $message = '🪙 ' . "<u><b>" . '[[coin]] سکه ' . "</b></u>" . ' از طرف شما برای [[user]] ارسال شد ✅';
            add_filter('send_massage_text', function ($text) {
                return tr_num($text, 'en', '.');
            }, 11);
            EditMessageText(
                $chatid,
                $messageid,
                __replace__($message, [
                    '[[coin]]' => $coin,
                    '[[user]]' => "<u><b>" . user($user_id)->name . "</b></u>"
                ]),
                null,
                null,
                'html'
            );
            $message = '♨️ [[coin]] ' . 'سکه برای شما ارسال شد.';
            add_filter('send_massage_text', function ($text) {
                return tr_num($text, 'en', '.');
            }, 11);
            SendMessage(
                $user_id,
                __replace__($message, [
                    '[[coin]]' => $coin,
                    '[[user]]' => "<u>" . user($chatid)->name . "</u>"
                ]),
                null,
                null,
                'html'
            );

        } else {
            $message = 'متاسفانه موجودی سکه شما کافی نیست.';
            EditMessageText($chatid, $messageid, $message);
        }

        break;

    case 'cancel':
        update_status('');
        $message = '🏳️ اوکی ، به کارت ادامه بده . ';
        EditMessageText($chatid, $messageid, $message);
        break;

    case 'cancel_2':
        $message = '🏳️ اوکی ، به کارت ادامه بده . ';
        EditMessageText($chatid, $messageid, $message);
        break;

    case 'send_message':

        $user_coin = user()->coin;

        if ($user_coin >= 5) {

            $chat = get_private_chat($data[1]);

            if ($chat->user_id == 5231959346 || (isset($chat->user_id) && check_ban($chat->user_id))) {

                if (add_chat($chatid, $chat->server_id, $chat->text, $chat->user_id, get__league_user($chat->user_id)->emoji)) {

                    $message = '📨  ' . "<u>پیام خصوصی</u>" . ' به [[user]]' . "\n \n" . $chat->text . "\n \n" . '✔️ ارسال شد .';
                    EditMessageText($chatid, $messageid, __replace__($message, ['[[user]]' => user($chat->user_id)->name]), null, null, 'html');
                    $message = '📨  ' . "<u>پیام خصوصی</u>" . ' از طرف [[user]]' . "\n \n" . $chat->text . "\n \n" . '♨️درصورت نیاز به گزارش میتوانید از دکمه زیر استفاده کنید .';
                    SendMessage($chat->user_id, __replace__($message, ['[[user]]' => user()->name]), $telegram->buildInlineKeyBoard([[$telegram->buildInlineKeyboardButton('🚫 گزارش', '', 'report-' . $chatid)]]), null, 'html');
                    demote_coin($chatid, 5);
                    update_status('reset');
                    delete_private_chat($data[1]);

                } else {
                    $message = 'خطا سیستمی رخ داد .. لطفا با پشتیبانی تماس بگیرید.';
                    Message();
                }

            } else {
                $message = '⚠️ خطا، کاربر مسدود می باشد.';
                EditMessageText($chatid, $messageid, $message, null, null, 'html');
            }

        } else {
            $message = '❗️ موجودی سکه شما برای ارسال پیام خصوصی کافی نیست.';
            EditMessageText($chatid, $messageid, $message);
        }


        break;

    case 'get_send_message':
        $user_coin = user()->coin;
        if ($user_coin >= 5) {
            $message = '📨 شما قصد ارسال پیام خصوصی به [[user]] را دارید . ' . "\n" . 'لطفا پیام خود را بنویسید :';
            EditMessageText($chatid, $messageid, __replace__($message, ['[[user]]' => "<u>" . user($data[1])->name . "</u>"]), null, null, 'html');
            update_status('get_send_message');
            update_data($data[1]);
        } else {
            $message = '❗️ موجودی سکه شما برای ارسال پیام خصوصی کافی نیست.';
            EditMessageText($chatid, $messageid, $message);
        }
        break;

    case 'change_gender':

        $message = '💢 لطفا جنیست خود را انتخاب کنید:' . "\n \n";
        $message .= '👨🏻‍✈️ ایموجی نقش شما در بازی بر اساس جنسیت شما تغییر خواهد کرد .';
        SendMessage($chatid, $message, KEY_GENDER_MENU);

        break;

    case 'select_gender':


        $user->update_meta('gender', $data[1]);
        $message = '✔️ جنیست شما تغییر کرد. هم اکنون میتوانید از پروفایل آن را مشاهده کنید.';
        EditMessageText($chatid, $messageid, $message);

        break;

    case 'get_link_sub_user':
        $message = '💎 تجربه ی یک بازی متفاوت آنلاین' . "\n \n";
        $message .= '<b>تا حالا بازی مافیا رو توی تلگرام داخل ربات انجام دادی؟🤔</b>' . "\n \n";
        $message .= '🎮 اگه حوصلت توی تلگرام سر رفته و دنبال یه سرگرمی جذاب هستی همین الان بازی مافیا رو استارت کن 😍👌' . "\n \n";
        $message .= 'https://telegram.me/' . GetMe()->username . '?start=' . string_encode($chatid);
        $telegram->sendMessage([
            'chat_id' => $chatid,
            'text' => $message,
            'parse_mode' => 'html',
            'disable_web_page_preview' => true
        ]);
        break;

    // ------- magic -----------------------

    /*case 'magic2':

        $chat_id = $chatid;
        $server  = is_user_in_which_server( $chat_id );
        if (
            add_magic( $server->id, $chat_id, 2 )
        )
        {

            if ( demote_coin( $chat_id, 3 ) )
            {

                $server    = is_user_in_which_server( $chatid );
                $user_role = get_role_user_server( $server->id, $data[1] );
                $message   = '🪄 جادوی دقیق ' . "\n";
                $message   .= '🔍 نقش دقیق [[user]] ( [[role]] ) است .';
                __replace__( $message, [
                    '[[user]]' => "<u>" . user( $data[1] )->name . "</u>",
                    '[[role]]' => $user_role->icon
                ] );

                SendMessage( $chatid, $message, null, null, 'html' );

                DeleteMessage( $chatid, $messageid );


            }
            else
            {
                $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                EditMessageText( $chatid, $messageid, $message );
            }

        }
        else
        {
            $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';
            EditMessageText( $chatid, $messageid, $message );
        }

        break;*/

    case 'magic':

        $chat_id = $chatid;
        switch ($data[1]) {

            case 1:

                if (is_user_row_in_game($chat_id)) {

                    $server = is_user_in_which_server($chat_id);

                    if (isset($server->id)) {

                        $user_role = get_role_user_server($server->id, $chat_id);
                        if ($user_role->group_id == 1) {


                            $user = user();
                            $bazpors_select = get_server_meta($server->id, 'select', ROLE_Bazpors);
                            $bazpors = get_role_by_user($server->id, ROLE_Bazpors);

                            if ($bazpors_select == $chat_id && get_server_meta($server->id, 'status') == 'light' ) {
                                $message = '📯 این جادو بصورت موقت غیرفعاله !';
                                EditMessageText($chatid, $messageid, $message);
                                // if (has_coin($chat_id, 2)) {

                                //     if (add_magic($server->id, $chat_id, 1)) {

                                //         if (demote_coin($chat_id, 2)) {


                                //             $message = '🟢 جادوی اعلام نقش ' . "\n";
                                //             $message .= '[[user]] نقشش ([[role]]) است .';
                                //             SendMessage(
                                //                 $bazpors,
                                //                 __replace__($message, [
                                //                     '[[user]]' => "<u>" . $user->name . "</u>",
                                //                     '[[role]]' => $user_role->icon
                                //                 ]),
                                //                 null,
                                //                 null,
                                //                 'html'
                                //             );
                                //             SendMessage($chat_id, $message, null, null, 'html');
                                //             DeleteMessage($chatid, $messageid);

                                //         } else {

                                //             $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                //             EditMessageText($chatid, $messageid, $message);

                                //         }

                                //     } else {

                                //         $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';
                                //         EditMessageText($chatid, $messageid, $message);

                                //     }

                                // } else {

                                //     $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                //     EditMessageText($chatid, $messageid, $message);

                                // }

                            } elseif (get_server_meta($server->id, 'accused') == $chat_id) {

                                if (has_coin($chat_id, 4)) {

                                    if (add_magic($server->id, $chat_id, 1)) {

                                        if (demote_coin($chat_id, 4)) {

                                            $users_server = get_users_by_server($server->id);
                                            $message = '🪄 جادوی اعلام نقش ' . "\n";
                                            $message .= '🟢 ' . "<u>" . $user->name . "</u>" . ' جزو گروه شهروند است .';
                                            foreach ($users_server as $item) {
                                                if (is_user_in_game($server->id, $item->user_id)) {
                                                    SendMessage($item->user_id, $message, null, null, 'html');
                                                }
                                            }
                                            DeleteMessage($chatid, $messageid);

                                        } else {

                                            $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                            EditMessageText($chatid, $messageid, $message);

                                        }

                                    } else {

                                        $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';
                                        EditMessageText($chatid, $messageid, $message);

                                    }

                                } else {

                                    $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';
                                    EditMessageText($chatid, $messageid, $message);

                                }

                            } else {

                                $message = '⚠️ خطا ، الان نمیتوانید از این جادو استفاده کنید .';
                                EditMessageText($chatid, $messageid, $message);

                            }


                        } elseif ($user_role->id == ROLE_Shayad) {

                            if (get_server_meta($server->id, 'accused') == $chat_id) {


                                if (has_coin($chat_id, 4)) {

                                    if (add_magic($server->id, $chat_id, 1)) {

                                        if (demote_coin($chat_id, 4)) {

                                            $user = user();
                                            $users_server = get_users_by_server($server->id);
                                            $message = '🪄 جادوی اعلام نقش ' . "\n";
                                            $message .= '🟢 ' . "<u>" . $user->name . "</u>" . ' جزو گروه شهروند است .';
                                            foreach ($users_server as $item) {
                                                if (is_user_in_game($server->id, $item->user_id)) {
                                                    SendMessage($item->user_id, $message, null, null, 'html');
                                                }
                                            }
                                            DeleteMessage($chatid, $messageid);

                                        } else {

                                            $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                            EditMessageText($chatid, $messageid, $message);

                                        }

                                    } else {

                                        $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';
                                        EditMessageText($chatid, $messageid, $message);

                                    }

                                } else {

                                    $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';
                                    EditMessageText($chatid, $messageid, $message);

                                }

                            } else {

                                $message = '⚠️ خطا ، الان نمیتوانید از این جادو استفاده کنید .';
                                EditMessageText($chatid, $messageid, $message);

                            }

                        } else {

                            $message = '⚠️ خطا ، شما نمیتوانید از این جادو استفاده کنید .';
                            EditMessageText($chatid, $messageid, $message);

                        }
                    }

                } else {

                    DeleteMessage($chatid, $messageid);

                }

                break;
            case 2:


                if ($user->user_on_game()) {

                    $server = $user->server();

                    if ($server->status == 'started') {

                        if ($user->has_coin(3)) {

                            $keyboard = [];
                            $message = '♨️ انتخاب کنید میخواهید از نقش چه کسی مطلع شوید .';
                            $user_role = $user->get_role();
                            foreach ($server->users() as $item) {

                                if ($item->check($user)) {

                                    if ($user_role->group_id != 2) {
                                        $keyboard[][] = $telegram->buildInlineKeyboardButton($item->get_league()->emoji . $item->get_name(), '', 'magic2-' . $item->getUserId());
                                    } elseif ($item->get_role()->group_id != 2) {
                                        $keyboard[][] = $telegram->buildInlineKeyboardButton($item->get_league()->emoji . $item->get_name(), '', 'magic2-' . $item->getUserId());
                                    }

                                }

                            }
                            $keyboard[][] = $telegram->buildInlineKeyboardButton('⛔️ انصراف', '', 'cancel');
                            EditMessageText($chatid, $messageid, $message, $telegram->buildInlineKeyBoard($keyboard));

                        } else {

                            throw new ExceptionWarning('شما سکه کافی برای استفاده از این جادو را ندارید .');

                        }

                    } else {
                        throw new ExceptionWarning('بازی هنوز شروع نشده است.');
                    }


                }

                break;
            case 3:

                if (is_user_row_in_game($chat_id)) {

                    $server = is_user_in_which_server($chat_id);

                    if (isset($server->id)) {

                        if (has_coin($chat_id, 6)) {

                            if (add_magic($server->id, $chat_id, 3)) {

                                if (demote_coin($chat_id, 6)) {

                                    $message = "📯<b><u>جادوی محفوظ</u></b>  ، فعال شد ✅";
                                    //                                    $message = '🛡جادوی محفوظ فعال شد .' . "\n" . 'شما برای ' . "<u>یک شب</u>" . ' از خطر حملات در امان خواهید بود .';
                                    EditMessageText($chatid, $messageid, $message, null, null, 'html');
                                    add_server_meta($server->id, 'shield', 'on', $chat_id);

                                } else {

                                    $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                    EditMessageText($chatid, $messageid, $message);

                                }

                            } else {
                                $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';
                                EditMessageText($chatid, $messageid, $message);
                            }

                        } else {

                            $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                            EditMessageText($chatid, $messageid, $message);

                        }

                    } else {
                        $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                        EditMessageText($chatid, $messageid, $message);
                    }

                } else {

                    DeleteMessage($chatid, $messageid);

                }

                break;
            case 4:

                if (is_user_row_in_game($chat_id)) {

                    $server = is_user_in_which_server($chat_id);
                    if (isset($server->id)) {

                        if (has_coin($chat_id, 5)) {

                            if (add_magic($server->id, $chat_id, 4)) {

                                if (demote_coin($chat_id, 5)) {

                                    $message = "📯 <b><u>جادوی حذف رای</u></b>  ، فعال شد ✅";
                                    //                                    $message = '🤷🏻‍♂️ جادوی حذف رای فعال شد .' . "\n" . 'نام شما در رای‌گیری ' . "<u>بعدی</u>" . ' قرار نمیگیرد.';
                                    EditMessageText($chatid, $messageid, $message, null, null, 'html');
                                    add_server_meta($server->id, 'no-vote', 'on', $chat_id);
                                } else {
                                    $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                    EditMessageText($chatid, $messageid, $message);
                                }

                            } else {

                                $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';
                                EditMessageText($chatid, $messageid, $message);

                            }

                        } else {
                            $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                            EditMessageText($chatid, $messageid, $message);
                        }

                    } else {

                        $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                        EditMessageText($chatid, $messageid, $message);

                    }

                } else {

                    DeleteMessage($chatid, $messageid);

                }

                break;
            case 5:

                if (is_user_row_in_game($chat_id)) {

                    $server = is_user_in_which_server($chat_id);
                    if (isset($server->id)) {

                        if (get_server_meta($server->id, 'is') != 'on') {

                            if (has_coin($chat_id, 5)) {

                                if (add_magic($server->id, $chat_id, 5)) {

                                    if (demote_coin($chat_id, 5)) {

                                        $message = "📯<b><u>جادوی جاسوس</u></b>  ، فعال شد ✅";
                                        //                                        $message = '🧏🏻‍♂️ جادوی جاسوس فعال شد .' . "\n" . 'شما از تمامی حملات به شما در آینده خبردار خواهید شد.';
                                        EditMessageText($chatid, $messageid, $message, null, null, 'html');
                                        add_server_meta($server->id, 'warning', 'on', $chat_id);

                                        $server = new Server($server->id);
                                        $filter_roles = [
                                            ROLE_Sniper,
                                            ROLE_Godfather,
                                            ROLE_Mashooghe,
                                            ROLE_HardFamia,
                                            ROLE_Tobchi,
                                            ROLE_Killer,
                                            ROLE_Gorg
                                        ];

                                        foreach ($server->getListAttacker($chatid) as $item) {

                                            $role = $item->get_role();
                                            if (!$item->is($chatid) && in_array($role->id, $filter_roles)) {

                                                switch ($role->id) {

                                                    case ROLE_Mashooghe:
                                                    case ROLE_Godfather:
                                                        $name_role = 'اعضای مافیا';
                                                        break;
                                                    default:
                                                        $name_role = remove_emoji($role->name);
                                                        break;

                                                }

                                                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>" . $name_role . "</u>" . ' قصد حمله به شما را دارد .';
                                                $item->SendMessageHtml($message);

                                            }

                                        }

                                    } else {
                                        $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                        EditMessageText($chatid, $messageid, $message);
                                    }

                                } else {

                                    $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';
                                    EditMessageText($chatid, $messageid, $message);

                                }

                            } else {
                                $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                EditMessageText($chatid, $messageid, $message);
                            }

                        } else {

                            AnswerCallbackQuery($dataid, '⚠️ مجددا امتحان کنید', true);
                            SendMessage(56288741, "کد 2", KEY_GAME_ON_MENU, null, 'html');


                        }

                    } else {
                        $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                        EditMessageText($chatid, $messageid, $message);
                    }

                } else {

                    DeleteMessage($chatid, $messageid);

                }

                break;
            case 6:

                if (is_user_row_in_game($chat_id)) {

                    $server = is_user_in_which_server($chat_id);

                    if (isset($server->id)) {

                        if (is_user_hacked($chat_id, $server->id)) {

                            if (has_coin($chat_id, 4)) {

                                if (add_magic($server->id, $chat_id, 6)) {

                                    if (demote_coin($chat_id, 4)) {

                                        delete_server_meta($server->id, 'hack');
                                        $message = "📯<b><u>جادوی ضدهک</u></b>  ، فعال شد ✅";
                                        //                                        $message = '🪄 جادوی ضدهک فعال شد .' . "\n" . '🗣 اکنون میتوانید صحبت کنید و رای بدهید .';

                                    } else {

                                        $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                        EditMessageText($chatid, $messageid, $message);

                                    }

                                } else {
                                    $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';
                                    EditMessageText($chatid, $messageid, $message);
                                }

                            } else {

                                $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                EditMessageText($chatid, $messageid, $message);

                            }

                        } else {

                            $message = '⚠️خطا ! شما نمیتوانید از این جادو استفاده کنید .';

                        }

                        EditMessageText($chatid, $messageid, $message);
                    }
                } else {

                    DeleteMessage($chatid, $messageid);

                }

                break;
            case 7:

                if (is_user_row_in_game($chat_id)) {

                    $server = is_user_in_which_server($chat_id);

                    if (isset($server->id)) {

                        $user = new User($chat_id, $server->id);

                        if ($user->sleep()) {

                            if (has_coin($chat_id, 6)) {

                                if (add_magic($server->id, $chat_id, 7)) {

                                    if (demote_coin($chat_id, 6)) {

                                        delete_server_meta($server->id, 'sleep');
                                        $message = "📯<b><u>جادوی بیدار شدن</u></b>  ، فعال شد ✅";
                                        $user->setStatus('playing_game');

                                    } else {

                                        $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                        EditMessageText($chatid, $messageid, $message);

                                    }

                                } else {
                                    $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';
                                    EditMessageText($chatid, $messageid, $message);
                                }

                            } else {

                                $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                EditMessageText($chatid, $messageid, $message);

                            }

                        } else {

                            $message = '⚠️خطا ! شما نمیتوانید از این جادو استفاده کنید .';

                        }

                        EditMessageText($chatid, $messageid, $message);
                    }
                } else {

                    DeleteMessage($chatid, $messageid);

                }

                break;
            case 8:

                if ($user->user_on_game()) {

                    $server = $user->server();
                    $accused = $server->accused();

                    if ($server->getStatus() == 'court-3' && $accused->getUserId() > 0 && !$accused->is($user) && $user->get_role()->group_id == 1) {

                        if ($user->has_coin(4)) {

                            if (add_magic($server->getId(), $user->getUserId(), 8)) {

                                if ($user->demote_coin(4)) {

                                    $message = '🪄 جادو حقیقت:' . "<a href='tg://user?id=" . hash_user_id($user->getUserId()) . "'> </a>" . "\n";
                                    $message .= '🔴 یکی از اعضای شهر ادعای نقش ' . "<b><u>" . $accused->get_name() . "</u></b>" . ' را دارد.';

                                    $server->setUserId($user->getUserId())->addChat('🪄 جادو حقیقت استفاده کرد.');

                                    foreach ($server->users() as $item) {

                                        if ($item->sleep() || !$item->is_user_in_game())
                                            continue;

                                        $item->SendMessageHtml($message);


                                    }

                                    $message = "📯<b><u>جادوی حقیقت</u></b>  ، فعال شد ✅";


                                } else {

                                    $message = '⚠️ شما سکه کافی برای استفاده از این جادو را ندارید .';

                                }

                            } else {

                                $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';

                            }

                        } else {

                            $message = '⚠️ شما سکه کافی برای استفاده از این جادو را ندارید .';

                        }

                    } else {

                        $message = '⚠️خطا ! شما نمیتوانید از این جادو استفاده کنید .';

                    }
                    EditMessageText($chatid, $messageid, $message);

                } else {

                    DeleteMessage($chatid, $messageid);

                }

                break;
            case 9:

                if ($user->user_on_game()) {

                    $server = $user->server();
                    $selector = new \library\Role($server);

                    if ($selector->select(ROLE_TofangDar)->is($user)) {

                        if ($user->has_coin(3)) {

                            if (add_magic($server->getId(), $user->getUserId(), 9)) {

                                if ($user->demote_coin(3)) {

                                    $type = (int) $server->setUserId(ROLE_TofangDar)->getMetaUser('type');
                                    $message = "📯<b><u>جادو تشخیص تیر</u></b>  ، فعال شد ✅" . "\n \n";
                                    if ($type == 2) {
                                        $message .= ' فشنگ دریافت شده از نوع ( 🔴 جنگی ) است .';
                                    } else {
                                        $message .= ' فشنگ دریافت شده از نوع ( ⚪️ مشقی ) است .';
                                    }


                                } else {

                                    $message = '⚠️ شما سکه کافی برای استفاده از این جادو را ندارید .';

                                }

                            } else {

                                $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';

                            }

                        } else {

                            $message = '⚠️ شما سکه کافی برای استفاده از این جادو را ندارید .';

                        }

                    } else {

                        $message = '⚠️خطا ! شما نمیتوانید از این جادو استفاده کنید .';

                    }
                    EditMessageText($chatid, $messageid, $message);

                } else {

                    DeleteMessage($chatid, $messageid);

                }

                break;

        }

        break;

    case 'magic_other':

        if ($user->user_on_game()) {

            $server = $user->server();
            $message = '📍 انتخاب کنید برای چه کسی میخواهید جادو فعال کنید:';
            foreach ($server->users() as $user_game) {
                $keyboard[][] = $telegram->buildInlineKeyboardButton('🔮 ' . $user_game->get_name(), '', 'magic_other_user-' . $user_game->getUserId());
            }
            EditMessageText($user->getUserId(), $messageid, $message, $telegram->buildInlineKeyBoard($keyboard));

        } else {

            AnswerCallbackQuery($dataid, '⛔️ شما داخل هیچ بازی نیستید');
            DeleteMessage($user->getUserId(), $messageid);

        }

        break;

    case 'magic_other_user':

        if ($user->user_on_game()) {

            $message = '‼️نکات مهم :' . "\n";
            $message .= '♻️ در هر بازی از سه جادو و از هر جادو تنها یک بار میتوانید استفاده کنید.' . "\n";
            $message .= '🔅 اعداد مقابل هر جادو ، تعداد سکه مورد نیاز برای استفاده از آن است .' . "\n \n";
            $message .= '📯 جادوی مدنظر را انتخاب کنید :';
            EditMessageText(
                $user->getUserId(),
                $messageid,
                $message,
                $telegram->buildInlineKeyBoard([
                    [
                        $telegram->buildInlineKeyboardButton('🛡 جادوی محفوظ (6)', '', 'magic_user-3-' . $data[1]),
                    ],
                    [
                        $telegram->buildInlineKeyboardButton('🤷🏻‍♂ جادوی حذف رای (5)', '', 'magic_user-4-' . $data[1]),
                    ],
                    [
                        $telegram->buildInlineKeyboardButton('🧏🏻‍♂ جادوی جاسوس' . ' (5)', '', 'magic_user-5-' . $data[1]),
                    ],
                    [
                        $telegram->buildInlineKeyboardButton('⛔️ انصراف', '', 'cancel_2')
                    ],
                ])
            );

        } else {

            AnswerCallbackQuery($dataid, '⛔️ شما داخل هیچ بازی نیستید');
            DeleteMessage($user->getUserId(), $messageid);

        }

        break;

    case 'magic_user':

        if ($user->user_on_game()) {


            $server = $user->server();

            if ($user->is(ADMIN_LOG) || add_magic($server->getId(), $user->getUserId(), 0)) {
                $user_magic = new User($data[2], $server->getId());

                switch ($data[1]) {

                    case 3:

                        if (has_coin($user->getUserId(), 6)) {

                            if (add_magic($server->getId(), $user_magic->getUserId(), 3)) {

                                if (demote_coin($user->getUserId(), 6)) {

                                    $message = "📯<b><u>جادوی محفوظ</u></b>  ، فعال شد ✅";
                                    EditMessageText($chatid, $messageid, $message, null, null, 'html');
                                    add_server_meta($server->getId(), 'shield', 'on', $user_magic->getUserId());
                                    $message = "📯 " . "<u><b>" . $user->get_name() . "</b></u>" . " جادوی " . "<b>محفوظ</b>" . " را برای شما فعال کرد ✅";
                                    $user_magic->SendMessageHtml($message);

                                } else {

                                    $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                    EditMessageText($chatid, $messageid, $message);

                                }

                            } else {
                                $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';
                                EditMessageText($chatid, $messageid, $message);
                            }

                        } else {

                            $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                            EditMessageText($chatid, $messageid, $message);

                        }


                        break;
                    case 4:


                        if (has_coin($user->getUserId(), 5)) {

                            if (add_magic($server->getId(), $user_magic->getUserId(), 4)) {

                                if (demote_coin($user->getUserId(), 5)) {

                                    $message = "📯 <b><u>جادوی حذف رای</u></b>  ، فعال شد ✅";
                                    EditMessageText($chatid, $messageid, $message, null, null, 'html');
                                    add_server_meta($server->getId(), 'no-vote', 'on', $user_magic->getUserId());
                                    $message = "📯 " . "<u><b>" . $user->get_name() . "</b></u>" . " جادوی " . "<b>حذف رای</b>" . " را برای شما فعال کرد ✅";
                                    $user_magic->SendMessageHtml($message);

                                } else {
                                    $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                    EditMessageText($chatid, $messageid, $message);
                                }

                            } else {

                                $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';
                                EditMessageText($chatid, $messageid, $message);

                            }

                        } else {
                            $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                            EditMessageText($chatid, $messageid, $message);
                        }

                        break;
                    case 5:

                        if (get_server_meta($server->getId(), 'is') != 'on') {

                            if (has_coin($user->getUserId(), 5)) {

                                if (add_magic($server->getId(), $user_magic->getUserId(), 5)) {

                                    if (demote_coin($user->getUserId(), 5)) {

                                        $message = "📯<b><u>جادوی جاسوس</u></b>  ، فعال شد ✅";
                                        EditMessageText($chatid, $messageid, $message, null, null, 'html');
                                        add_server_meta($server->getId(), 'warning', 'on', $user_magic->getUserId());
                                        $message = "📯 " . "<u><b>" . $user->get_name() . "</b></u>" . " جادوی " . "<b>جاسوس</b>" . " را برای شما فعال کرد ✅";
                                        $user_magic->SendMessageHtml($message);

                                    } else {
                                        $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                        EditMessageText($chatid, $messageid, $message);
                                    }

                                } else {

                                    $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';
                                    EditMessageText($chatid, $messageid, $message);

                                }

                            } else {
                                $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                EditMessageText($chatid, $messageid, $message);
                            }

                        } else {

                            AnswerCallbackQuery($dataid, '⚠️ مجددا امتحان کنید', true);
                            SendMessage(56288741, "کد 3", KEY_GAME_ON_MENU, null, 'html');


                        }

                        break;

                }

            } else {

                AnswerCallbackQuery($dataid, '⛔️ تنها یک بار میتوانید برای دیگران جادو فعال کنید.');
                DeleteMessage($user->getUserId(), $messageid);

            }

        } else {

            AnswerCallbackQuery($dataid, '⛔️ شما داخل هیچ بازی نیستید');
            DeleteMessage($user->getUserId(), $messageid);

        }


        break;


    // ---- Select Role ----------

    case 'join_server':
        
        $filename = 'chat_data.json';
        $checkId = checkChatId($filename, $chatid);
        if ($checkId) {
            
            // $message = '⚠️ خطا: شما قبلا نقشی انتخاب نکردید و نمی توانید مجدد نوع بازی را انتخاب کنید' . "\n";
            // $message .= ' جهت ادامه لطفا شروع بازی را انتخاب کنید 👇';
            // EditMessageText( $chatid, $messageid, $message );
            AnswerCallbackQuery( $dataid, '❌ لیست نقش های قبلی که باز کرده بودید حذف گردید' );
            
            $messageid = getMessageChatId($filename, $chatid);
            DeleteMessage( $chatid, $messageid );
            clearUserData($filename, $chatid);
            continue;
        }

        $user->update_meta('league', $data[1]);
        if (has_coin($chatid, 2)) {

            $message = '♨️ نقش خود را از قبل بازی انتخاب کنید .';
            if ($data[1] > 1) {
                EditMessageText(
                    $chatid,
                    $messageid,
                    $message,
                    $telegram->buildInlineKeyBoard([
                        [$telegram->buildInlineKeyboardButton('🟢 نقش شهروند', '', 'select_role_game-1'),],
                        [$telegram->buildInlineKeyboardButton('🔴 نقش مافیا', '', 'select_role_game-2'),],
                        [$telegram->buildInlineKeyboardButton('🟡 نقش مستقل', '', 'select_role_game-3'),],
                        [$telegram->buildInlineKeyboardButton('🟣 شگفت انگیز', '', 'select_role_game-4'),],
                        [$telegram->buildInlineKeyboardButton('🎲 نقش تصادفی ( رایگان )', '', 'select_role_game-0'),],
                    ])
                );
            } else {
                EditMessageText(
                    $chatid,
                    $messageid,
                    $message,
                    $telegram->buildInlineKeyBoard([
                        [$telegram->buildInlineKeyboardButton('🟢 نقش شهروند', '', 'select_role_game-1'),],
                        [$telegram->buildInlineKeyboardButton('🔴 نقش مافیا', '', 'select_role_game-2'),],
                        [$telegram->buildInlineKeyboardButton('🟡 نقش مستقل', '', 'select_role_game-3'),],
                        [$telegram->buildInlineKeyboardButton('🟣 شگفت انگیز', '', 'select_role_game-4'),],
                        [$telegram->buildInlineKeyboardButton('🎲 نقش تصادفی ( رایگان )', '', 'select_role_game-0'),],
                    ])
                );
            }

        } else {

            $server = Server::getServerByLeague($data[1]);
            add_player_to_server($chatid, 0, 0, $server->getId());

        }

        break;

    case 'select_role_game':

        $join = $user->get_meta('join');

        if ($data[1] == 0) // random
        {

            DeleteMessage($chatid, $messageid);

            switch ($join) {
                case 'random':
                    $server = Server::getServerOrderByLeague(get_league_user($chatid)->id);
                    break;
                case 'priority':
                default:
                    $priority = $user->get_meta('priority');
                    $priority = empty($priority) ? $user->get_game()->id : $priority;
                    $server = Server::getServerByLeague($priority);
                    break;
                case 'asking':
                    $server = new Server(get_server_by_league($user->get_meta('league') ?? 1));
                    break;
            }

            if ($server->getId() <= 0) {


                $server = new Server(get_server_by_league($user->get_game()->id));


            }

            add_player_to_server($chatid, 0, 0, $server->getId());
            $user->delete_meta('league');


        } else {

            $keyboard = [];
            $user_league = get_league_user();
            $join = $user->get_meta('join');
            if ($join == 'asking') {
                $roles = get_keyboard_roles_by_group_and_game($data[1], ($user->get_meta('league') ?? $user_league->id));
            } elseif ($join == 'priority') {
                $roles = get_keyboard_roles_by_group_and_game($data[1], ($user->get_meta('priority') ?? $user_league->id));
            } else {
                $roles = get_keyboard_roles_by_group_and_game($data[1], $user_league->id);
            }
            /* @var $role \helper\Role */
            foreach ($roles as $role) {

                $gp = ($role->group_id == 1 ? '🟢' : ($role->group_id == 2 ? '🔴' : ($role->group_id == 3 ? '🟡' : '🟣')));
                $name = $gp . ' ' . $role->icon . ' (' . $role->coin . ')';
                if (is_numeric($role->level)) {

                    $keyboard[$role->level][] = $telegram->buildInlineKeyboardButton($name, '', 'select_role_server-' . $data[1] . '-' . $role->id);

                }

            }

            switch ($data[1]) {

                case 1:

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔴', '', 'select_role_game-2'),
                        $telegram->buildInlineKeyboardButton('🟣', '', 'select_role_game-4'),
                        $telegram->buildInlineKeyboardButton('🟡', '', 'select_role_game-3'),
                    ];

                    break;

                case 2:

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🟡', '', 'select_role_game-3'),
                        $telegram->buildInlineKeyboardButton('🟣', '', 'select_role_game-4'),
                        $telegram->buildInlineKeyboardButton('🟢', '', 'select_role_game-1'),
                    ];

                    break;

                case 3:

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔴', '', 'select_role_game-2'),
                        $telegram->buildInlineKeyboardButton('🟣', '', 'select_role_game-4'),
                        $telegram->buildInlineKeyboardButton('🟢', '', 'select_role_game-1'),
                    ];

                    break;

                case 4:

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🟢', '', 'select_role_game-1'),
                        $telegram->buildInlineKeyboardButton('🔴', '', 'select_role_game-2'),
                        $telegram->buildInlineKeyboardButton('🟡', '', 'select_role_game-3'),
                    ];

                    break;

            }

            $keyboard[][] = $telegram->buildInlineKeyboardButton('🎲 نقش تصادفی ( رایگان )', '', 'select_role_game-0');

            $message = '💰 موجودی سکه شما : ' . user()->coin . "\n \n";
            $message .= 'اعداد مقابل هر نقش ، <u>سکه مورد نیاز</u> برای خرید هر نقش است .' . "\n \n";
            $message .= 'نقش خود را انتخاب کنید 👇';

            EditMessageText($chatid, $messageid, $message, $telegram->buildInlineKeyBoard(array_values($keyboard)), null, 'html');
            
            $filename = 'chat_data.json';
            updateUserData($filename, $chatid, $messageid);

        }
        
        $filename = 'chat_data.json';
        $checkId = checkChatId($filename, $chatid);
        if ($checkId) {
            $keyboard[][] = $telegram->buildInlineKeyboardButton( $game->icon, '', 'join_server-' . $game->id );
        }

        break;

    case 'select_role_server':
        
        $filename = 'chat_data.json';
        $checkId = checkChatId($filename, $chatid);
        if ($checkId) {
            clearUserData($filename, $chatid);
        }

        $join = $user->get_meta('join');
        DeleteMessage($chatid, $messageid);
        if ($data[2] != 'random' && has_coin($chatid, get_role($data[2])->coin)) {

            switch ($join) {
                case 'random':
                    $server = Server::getServerOrderByLeague(get_league_user($chatid)->id);
                    break;
                case 'priority':
                default:
                    $priority = $user->get_meta('priority');
                    $priority = empty($priority) ? $user->get_game()->id : $priority;
                    $server = Server::getServerByLeague($priority);
                    break;
                case 'asking':
                    $server = new Server(get_server_by_league($user->get_meta('league') ?? 1));
                    break;
            }

            if ($server->getId() <= 0) {


                $server = new Server(get_server_by_league($user->get_game()->id));


            }

            add_player_to_server($chatid, $data[1], $data[2], $server->getId());

        } else {

            switch ($join) {
                case 'random':
                    $server = Server::getServerOrderByLeague(get_league_user($chatid)->id);
                    break;
                case 'priority':
                default:
                    $priority = $user->get_meta('priority');
                    $priority = empty($priority) ? $user->get_game()->id : $priority;
                    $server = Server::getServerByLeague($priority);
                    break;
                case 'asking':
                    $server = new Server(get_server_by_league($data[2]));
                    break;
            }

            if ($server->getId() <= 0) {


                $server = new Server(get_server_by_league($user->get_game()->id));


            }

            add_player_to_server($chatid, 0, 0, $server->getId());

        }

        break;

    // ----- Setting --------------

    case 'setting':
        $join = $user->get_meta('join');
        $message = '🎮 سناریو بازی خودتون رو انتخاب کنید .

۱- ♻️ تصادفی 
➖ در این حالت به اولین بازی در حال عضوگیری می‌پیوندید .

۲-📯 اولویت بندی 
➖ انتخاب کنید کدام نوع بازی برای شما اولویت دارد تا به آن متصل شوید . 

۳- ❓ همیشه بپرس 
➖ همیشه قبل از هر بازی از شما میپرسد چه نوع سناریو میخواهید بازی کنید .';
        EditMessageText(
            $chatid,
            $messageid,
            $message,
            $telegram->buildInlineKeyBoard([
                [$telegram->buildInlineKeyboardButton('♻️ تصادفی' . ($join == 'random' ? '✔️' : ''), '', 'server_select-random')],
                [$telegram->buildInlineKeyboardButton('🎮 اولویت بندی' . (empty($join) || $join == 'priority' ? '✔️' : ''), '', 'server_select-priority')],
                [$telegram->buildInlineKeyboardButton('❓ همیشه بپرس' . ($join == 'asking' ? '✔️' : ''), '', 'server_select-asking')],
                [$telegram->buildInlineKeyboardButton('👤 پروفایل', '', 'profile')],
            ])
        );
        break;

    case 'server_select':
        $user->update_meta('join', $data[1]);
        EditKeyboard(
            $chatid,
            $messageid,
            $telegram->buildInlineKeyBoard([
                [$telegram->buildInlineKeyboardButton('♻️ تصادفی' . ($data[1] == 'random' ? '✔️' : ''), '', 'server_select-random')],
                [$telegram->buildInlineKeyboardButton('🎮 اولویت بندی' . ($data[1] == 'priority' ? '✔️' : ''), '', 'server_select-priority')],
                [$telegram->buildInlineKeyboardButton('❓ همیشه بپرس' . ($data[1] == 'asking' ? '✔️' : ''), '', 'server_select-asking')],
                [$telegram->buildInlineKeyboardButton('👤 پروفایل', '', 'profile')],
            ])
        );

        if ($data[1] == 'priority') {

            $keyboard = [];

            $priority = $user->get_meta('priority');
            $league_user = $user->get_game();
            $point_user = $user->get_point();

            foreach (get_games() as $game) {

                if ($game->point >= 0 && $game->point <= $point_user && date('H') >= ($game->start_time ?? 0) && date('H') <= ($game->end_time ?? 23)) {

                    $keyboard[][] = $telegram->buildInlineKeyboardButton($game->icon . (empty($priority) ? ($game->id == $league_user->id ? '✔️' : '') : ($game->id == $priority ? '✔️' : '')), '', 'server_select_priority-' . $game->name);

                }

            }

            $keyboard[][] = $telegram->buildInlineKeyboardButton('👤 پروفایل', '', 'profile');

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        } else {

            AnswerCallbackQuery($dataid, 'تنظیمات شما اعمال شد ✅');

        }

        break;

    case 'server_select_priority':

        $keyboard = [];
        $point_user = $user->get_point();

        foreach (get_games() as $game) {

            if ($game->point >= 0 && $game->point <= $point_user && date('H') >= ($game->start_time ?? 0) && date('H') <= ($game->end_time ?? 23)) {

                $keyboard[][] = $telegram->buildInlineKeyboardButton($game->icon . ($game->name == $data[1] ? '✔️' : ''), '', 'server_select_priority-' . $game->name);

            }

            if ($game->name == $data[1]) {

                $user->update_meta('priority', $game->id);
                AnswerCallbackQuery($dataid, 'سناریو شما برای بازی ' . $game->icon . ' انتخاب شد✅');

            }

        }

        $keyboard[][] = $telegram->buildInlineKeyboardButton('👤 پروفایل', '', 'profile');

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;

    // ---------- change status friend ----

    case 'change_status_friend':

        $status = $user->get_meta('status');
        $user->update_meta('status', ($status == 'hide' ? 'public' : 'hide'));
        $keyboard = [];
        $keyboard[][] = $telegram->buildInlineKeyboardButton(($status != 'hide' ? 'وضعیت شما در حالت خاموش قرار دارد ⚫️' : 'وضعیت شما برای دوستانتان نمایش داده میشود ✅'), '', 'change_status_friend');
        $keyboard[][] = $telegram->buildInlineKeyboardButton(($user->get_meta('profile') == 'hide' ? 'وضعیت پروفایل شما در حالت خاموش قرار دارد ⚫️' : 'وضعیت پروفایل شما برای دوستانتان نمایش داده میشود ✅'), '', 'change_status_friend_profile');
        foreach ($user->friends() as $friend) {
            $keyboard[][] = $telegram->buildInlineKeyboardButton($friend->toStringFriend(), '', 'manage_friends-' . $friend->getUserId());
        }
        EditKeyboard($user->getUserId(), $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;

    case 'change_status_friend_profile':

        $status = $user->get_meta('profile');
        $user->update_meta('profile', ($status == 'hide' ? 'public' : 'hide'));
        $keyboard = [];
        $keyboard[][] = $telegram->buildInlineKeyboardButton(($user->get_meta('status') == 'hide' ? 'وضعیت شما در حالت خاموش قرار دارد ⚫️' : 'وضعیت شما برای دوستانتان نمایش داده میشود ✅'), '', 'change_status_friend');
        $keyboard[][] = $telegram->buildInlineKeyboardButton(($status != 'hide' ? 'وضعیت پروفایل شما در حالت خاموش قرار دارد ⚫️' : 'وضعیت پروفایل شما برای دوستانتان نمایش داده میشود ✅'), '', 'change_status_friend_profile');
        foreach ($user->friends() as $friend) {
            $keyboard[][] = $telegram->buildInlineKeyboardButton($friend->toStringFriend(), '', 'manage_friends-' . $friend->getUserId());
        }
        EditKeyboard($user->getUserId(), $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;

    case 'friends':

        $message = '🗂 لیست دوستان شما در زیر نمایش داده شده است:' . "\n \n";
        $message .= '📌 شما حداکثر میتوانید 40 نفر را به عنوان دوستانه اضافه کنید.' . "\n";
        $count_friend = $user->countFriend();
        if ($user->countFriend() > 0) {
            $message .= '🏷 شما در حال حاضر ' . $count_friend . ' نفر در لیست دوستان خود دارید.' . "\n \n";
        } else {
            $message .= '🏷 در حال حاضر لیست دوستان شما خالی است.' . "\n \n";
        }
        $message .= '📝 راهنما وضعیت:' . "\n";
        $message .= '➖ <b>آفلاین</b> 🔴  ( داخل هیچ بازی نیست)' . "\n";
        $message .= '➖ <b>آنلاین درحال بازی</b> 🟢 ( درحال بازی )' . "\n";
        $message .= '➖ <b>آنلاین منتظر</b> 🟣 ( توی لیست انتظار در حال پر شدن بازی )' . "\n";
        $message .= '➖ <b>آنلاین خارج از بازی</b> 🟡 ( آنلاین هست اما منتظر شروع بازی نیست )' . "\n";
        $message .= '➖ <b>وضعیت خاموش</b>  ⚫️ ( حریم شخصی فعاله و امکان چک کردن وضعیت وجود نداره)' . "\n \n";
        $message .= '====== انتخاب کنید با کدام دوستتان کار دارید ======';

        $keyboard[][] = $telegram->buildInlineKeyboardButton(($user->get_meta('status') == 'hide' ? 'وضعیت شما در حالت خاموش قرار دارد ⚫️' : 'وضعیت شما برای دوستانتان نمایش داده میشود ✅'), '', 'change_status_friend');
        $keyboard[][] = $telegram->buildInlineKeyboardButton(($user->get_meta('profile') == 'hide' ? 'وضعیت پروفایل شما در حالت خاموش قرار دارد ⚫️' : 'وضعیت پروفایل شما برای دوستانتان نمایش داده میشود ✅'), '', 'change_status_friend_profile');
        foreach ($user->friends() as $friend) {
            $keyboard[][] = $telegram->buildInlineKeyboardButton($friend->toStringFriend(), '', 'manage_friends-' . $friend->getUserId());
        }
        EditMessageText($user->getUserId(), $messageid, $message, $telegram->buildInlineKeyBoard($keyboard));

        break;

    case 'manage_friends':

        $friend = new User($data[1]);
        $message = '👤 شما ' . "<b><u>" . $friend->user()->name . "</u></b>" . ' را انتخاب کرده اید.' . "\n \n";
        $message .= '🪧 انتخاب کنید میخواهید چه کاری انجام دهید؟';

        EditMessageText(
            $user->getUserId(),
            $messageid,
            $message,
            $telegram->buildInlineKeyBoard([
                [
                    $telegram->buildInlineKeyboardButton('♻️ درخواست بازی', '', 'request_join_game-' . $friend->getUserId()),
                    $telegram->buildInlineKeyboardButton('🗑 حذف از لیست', '', 'delete_friend-' . $friend->getUserId()),
                ],
                [
                    $telegram->buildInlineKeyboardButton('📨 پیام خصوصی', '', 'get_send_message-' . $friend->getUserId()),
                    $telegram->buildInlineKeyboardButton('👤 پروفایل', '', 'get_profile-' . $friend->getUserId()),
                ],
                [
                    $telegram->buildInlineKeyboardButton('↪️ بازگشت به عقب', '', 'friends'),
                ]
            ])
        );

        break;

    case 'get_profile':

        $friend = new User($data[1]);

        if ($friend->get_meta('profile') == 'hide') {
            AnswerCallbackQuery($dataid, '❌ پروفایل این کاربر مخفی می باشد.');
            die();
        }

        $user_friend = $friend->user();

        $game_count = $friend->getCountGame();
        $opration = $friend->getResultWinGame();
        $role = $friend->get_meta('role');
        $point = $friend->get_point();
        $user_league = $friend->get_league();

        $dice_user = intval($friend->get_meta('dice-count'));

        $message = '💢 پروفایل بازیکن: ' . "<b><u>" . $user_friend->name . "</u></b>" . "\n \n";
        $message .= '➖ نام : ' . $user_friend->name . "\n";
        $message .= '➖ امتیاز : ' . $point . "\n";
        $message .= '➖ لیگ : ' . $user_league->icon . "\n";
        $message .= '➖ رتبه در بازی : ' . ($point > 0 ? get_rank_user_in_global($friend->getUserId()) : 'ندارید') . "\n";
        $message .= '➖ تعداد کل بازی‌ها : ' . intval($friend->get_meta('game-count')) . "\n";
        $message .= '➖ درصد برد: ' . ($game_count > 0 ? ceil($opration) : 0) . '%' . "\n";
        $message .= '➖ شانس دارت : ' . $dice_user . ' از 5' . "\n";
        $message .= '➖ نقش مورد علاقه : ' . (isset($role) ? get_role($role)->icon : 'انتخاب نشده است') . "\n";
        $message .= '➖ جنسیت : ' . $friend->gender();

        EditMessageText(
            $user->getUserId(),
            $messageid,
            $message,
            $telegram->buildInlineKeyBoard([
                [
                    $telegram->buildInlineKeyboardButton('↪️ بازگشت به عقب', '', 'manage_friends-' . $friend->getUserId()),
                ]
            ])
        );

        break;

    case 'delete_friend':

        $friend = new User($data[1]);
        $message = ' ( ' . "<b><u>" . $friend->user()->name . "</u></b>" . ' ) آیا از انجام عملیات اطمینان دارید؟' . "\n \n";
        $message .= '⚠️ شما قصد حذف کردن دوست خود دارید!';
        EditMessageText(
            $user->getUserId(),
            $messageid,
            $message,
            $telegram->buildInlineKeyBoard([
                [
                    $telegram->buildInlineKeyboardButton('❌ انصراف', '', 'manage_friends-' . $friend->getUserId()),
                    $telegram->buildInlineKeyboardButton('✅ اطمینان دارم', '', 'delete_friend_2-' . $friend->getUserId()),
                ],
            ])
        );

        break;

    case 'delete_friend_2':

        $friend = new User($data[1]);
        if ($user->removeFriend($friend)) {
            $message = '✅ کاربر ' . "<b><u>" . $friend->user()->name . "</u></b>" . ' با موفقیت از لیست دوستان شما حذف شد.';
            EditMessageText($user->getUserId(), $messageid, $message);
        } else {
            throw new ExceptionError('خطایی هنگام حذف رخ داد، با پشتیبانی تماس بگیرید.');
        }

        break;

    case 'request_join_game':

        $friend = new User($data[1]);

        $status = $friend->getCodeStatusFriend();
        if ($status == 2 || $status == 3) {

            if (!$user->user_on_game()) {

                $message = '💯 دوست شما ' . "<b><u>" . $user->get_league()->emoji . $user->user()->name . "</u></b>" . ' درخواست بازی با شما را دارد آیا به او می پیوندید؟' . "\n \n";

                if ($friend->user_on_game()) {
                    $message .= '⚠️ برای پیوستن به بازی باید نخست از بازی جاری خود خارج شوید!';
                }

                $friend->setKeyboard(
                    $telegram->buildInlineKeyBoard([
                        [
                            $telegram->buildInlineKeyboardButton('↗️ پیوستن به ' . $user->user()->name, '', 'join_request_friend-' . $user->getUserId()),
                            $telegram->buildInlineKeyboardButton('❌ رد درخواست', '', 'reject_request_friend-' . $user->getUserId()),
                        ]
                    ])
                )->SendMessageHtml($message);

                $message = 'درخواست بازی شما برای ' . "<b><u>" . $friend->user()->name . "</u></b>" . ' با موفقیت ارسال شد✅';
                EditMessageText($user->getUserId(), $messageid, $message);

            } else {
                AnswerCallbackQuery($dataid, '🔴 زمانی که داخل بازی هستید نمیتوانید درخواست بازی ارسال کنید.');
            }

        } else {
            AnswerCallbackQuery($dataid, '⚠️ شما نمیتوانید درخواست بازی به دوستتان ارسال کنید❗️');
        }

        break;

    case 'join_request_friend':

        if (!$user->user_on_game()) {

            if ($user->is_ban()) {

                $friend = new User($data[1]);


                if (!$friend->user_on_game()) {

                    if ($friend->is_ban()) {

                        $game_id = $friend->get_game()->id;

                        $users_server = [];
                        $users_server[] = $user;
                        $users_server[] = $friend;
                        $new_server = new Server(get_server_by_league($game_id));

                        $league_new_server = $new_server->get_league();

                        if ($new_server->count() + 2 > $league_new_server->count) {
                            $new_server = new Server(create_server($game_id));
                        }

                        $message = '';
                        $emoji_number_by_server = (int) get_server_meta($new_server->getId(), 'emoji-number');
                        foreach ($users_server as $id => $user_game) {

                            $message .= "<b>" . ($new_server->count + ($id + 1)) . ".</b>" . "<u><b>" . $user_game->get_league()->emoji . $user_game->user()->name . "</b></u>" . ' به این بازی پیوست.' . "\n";
                            $new_users[] = $user_game->getUserId();

                            switch ($server->league_id) {

                                case 1:

                                    if ($server->count > 3) {

                                        add_server_meta($new_server->getId(), 'get-point', 'friend', $user_game->getUserId());

                                    }

                                    break;

                                case 2:
                                default:

                                    if ($server->count > 5) {

                                        add_server_meta($new_server->getId(), 'get-point', 'friend', $user_game->getUserId());

                                    }

                                    break;

                            }

                            add_server_meta($new_server->getId(), 'friend', $emoji_number_by_server, $user_game->getUserId());
                            add_player_to_server($user_game->getUserId(), 0, 0, $new_server->getId(), false);

                        }

                        add_emoji_for_friendly($new_server->getId());

                        $users_new_server = get_users_by_server($new_server->getId());
                        foreach ($users_new_server as $id => $item) {

                            $user_game = new User($item->user_id, $new_server->getId());
                            $new_message .= ($id + 1) . '- ' . $user_game->get_league()->emoji . $user_game->user()->name . "\n";
                            if (!in_array($item->user_id, $new_users)) {

                                SendMessage($item->user_id, $message, KEY_GAME_ON_MENU, null, 'html');

                            }

                        }

                        $message = '🎲 درحال جستجوی بازیکن برای شروع ...' . "\n";
                        $message .= '🔸 نوع بازی :  ' . $league_new_server->icon . ' ، ' . tr_num($league_new_server->count, 'fa') . ' نفره' . "\n \n";
                        $message .= '👥 لیست افراد در صف انتظار' . "\n" . $new_message;
                        $user->SendMessageHtml($message);
                        $friend->SendMessageHtml($message);

                        $message = '✅ شما درخواست بازی ' . "<b><u>" . $friend->user()->name . "</u></b>" . ' را قبول کردید.';
                        EditMessageText($user->getUserId(), $messageid, $message, null, null, 'html');


                    } else {
                        AnswerCallbackQuery($dataid, '⚠️ متاسفم، ' . $friend->user()->name . ' مسدود می باشد.', true);
                    }

                } else {

                    AnswerCallbackQuery($dataid, '⚠️ متاسفم، ' . $friend->user()->name . ' قبلا به بازی دیگیری پیوسته است.❗️');

                }

            } else {
                AnswerCallbackQuery($dataid, '⚠️ متاسفم، شما مسدود می باشید.', true);
            }

        } else {
            AnswerCallbackQuery($dataid, '⚠️ نخست باید از بازی خارج شوید❗️');
        }

        break;

    case 'reject_request_friend':

        $friend = new User($data[1]);

        $message = '♨️ دوست شما ' . "<b><u>" . $user->user()->name . "</u></b>" . ' درخواست بازی شما را رد کرد.';
        $friend->SendMessageHtml($message);

        $message = '🛑 شما درخواست بازی کاربر ' . "<b><u>" . $user->user()->name . "</u></b>" . ' رد کردید.';
        EditMessageText($user->getUserId(), $messageid, $message);

        break;

    case 'request_add_friend':

        if ($user->user_on_game()) {

            $server = $user->server();
            $request = intval($server->setUserId($user->getUserId())->getMetaUser('request'));
            if ($request < 2) {

                if (!$user->isFriend($data[1])) {

                    if ($user->has_coin(($user->countFriendRequest() > 5 ? 50 : 0))) {

                        $user->requestFriend($data[1]);
                        $friend = new User($data[1], $server->getId());

                        if ($friend->get_meta('status') != 'hide') {

                            $message = '✉️ شما یک درخواست دوستی از طرف ' . "<b><u>" . $user->get_league()->emoji . $user->user()->name . "</u></b>" . ' دارید❗️' . "\n \n";
                            $message .= '🔖 آیا درخواست دوستی او را قبول میکنید؟';
                            $friend->setKeyboard(
                                $telegram->buildInlineKeyBoard([
                                    [
                                        $telegram->buildInlineKeyboardButton('✅ قبول میکنم', '', 'accept_request_add_friend-' . $user->getUserId() . '-0'),
                                        $telegram->buildInlineKeyboardButton('رد کردن ❌', '', 'reject_request_add_friend-' . $user->getUserId()),
                                    ]
                                ])
                            )->SendMessageHtml($message);
                            $message = 'درخواست دوستی شما برای ' . "<b><u>" . $friend->user()->name . "</u></b>" . ' ارسال شد✅';
                            $server->setUserId($user->getUserId())->updateMetaUser('request', $request + 1);
                            EditMessageText($user->getUserId(), $messageid, $message);

                        } else {
                            throw new ExceptionWarning('نمیتوانید به این کاربر درخواست دوستی ارسال کنید.');
                        }

                    } else {
                        throw new ExceptionWarning('موجودی شما کافی نمی باشد.');
                    }

                } else {
                    throw new ExceptionWarning('این کاربر قبلا در لیست دوستان شما می باشد.');
                }


            } else {
                throw new ExceptionWarning('شما تنها میتوانید 2 درخواست دوستی ارسال کنید.');
            }


        } else {

            throw new ExceptionError('شما داخل هیچ سروری نیستید.');

        }

        break;

    case 'accept_request_add_friend':

        $friend = new User($data[1], -1);

        if (check_time_chat($chatid, 3, 'request-friend')) {

            if (!$friend->isFriend($user->getUserId())) {

                if ($user->get_meta('status') != 'hide') {

                    if ($friend->demote_coin($data[2])) {

                        if ($friend->add_friend($user)) {

                            $message = "<b><u>" . $user->user()->name . "</u></b>" . 'درخواست دوستی شما را قبول کرد✅';
                            $friend->SendMessageHtml($message);
                            EditMessageText($user->getUserId(), $messageid, 'درخواست دوستی ' . "<b><u>" . $friend->user()->name . "</u></b>" . ' با موفقیت قبول شد✅');

                        } else {

                            $message = '⛔️ درخواست دوستی ' . "<b><u>" . $user->user()->name . "</u></b>" . ' به دلیل پر بودن لیست شما یا او موفق نبود.';
                            $friend->SendMessageHtml($message)->add_coin($data[2]);
                            EditMessageText($user->getUserId(), $messageid, '⛔️ درخواست دوستی ' . "<b><u>" . $friend->user()->name . "</u></b>" . ' به دلیل پر بودن لیست شما یا او موفق نبود.');

                        }

                    } else {

                        $message = '⛔️ موجودی شما برای درخواست دوستی کاربر ' . "<b><u>" . $user->user()->name . "</u></b>" . ' کم بود.';
                        $friend->setKeyboard(
                            $telegram->buildInlineKeyBoard([
                                [
                                    $telegram->buildInlineKeyboardButton('🔄 مجدد درخواست', '', 'request_add_friend-' . $user->getUserId() . '-50'),
                                    $telegram->buildInlineKeyboardButton('انصراف ❌', '', 'cancel'),
                                ]
                            ])
                        );
                        EditMessageText($user->getUserId(), $messageid, '⛔️ درخواست دوستی به دلیل عدم موجودی لغو شد.');

                    }

                } else {
                    throw new ExceptionWarning('شما حریم خصوصی خود را مخفی کرده اید.');
                }

            } else {
                throw new ExceptionWarning('این کاربر قبلا جز لیست دوستان شما می باشد.');
            }

        } else {
            throw new ExceptionWarning('هر 3 ثانیه یک بار میتوانید درخواست دوستی قبول کنید.');
        }

        break;

    case 'reject_request_add_friend':

        $friend = new User($data[1], -1);

        if (check_time_chat($chatid, 3, 'request-friend')) {

            $message = '❌ ' . "<b><u>" . $user->user()->name . "</u></b>" . ' درخواست دوستی شما را رد کرد.';
            $friend->SendMessageHtml($message);
            EditMessageText($user->getUserId(), $messageid, '❌ درخواست دوستی ' . "<b><u>" . $friend->user()->name . "</u></b>" . ' رد شد.');

        } else {
            throw new ExceptionWarning('هر 3 ثانیه یک بار میتوانید درخواست دوستی قبول کنید.');
        }

        break;

    // ---------- release league ----

    case 'releae_league':

        if (check_time_chat($chatid, 20, 'league')) {

            $message = '✔️ درخواست آزاد کردن لیگ برای پشتیبانی ارسال گردید.' . "\n \n";
            $message .= '🔔نتیجه آزاد شدن یا رد شدن لیگ از طریق ربات به شما ارسال می گردد.';
            EditMessageText($chatid, $messageid, $message);

            add_filter('filter_token', function () {
                global $token_bot;
                return $token_bot[0];
            });
            add_filter('send_massage_text', function ($text) {
                return tr_num($text, 'en', '.');
            }, 11);
            $message = '📯 درخواست بازشدن لیگ جدید .' . "\n \n";
            $message .= '➖ درخواست کننده : ' . $user->name . ' ' . "`$chatid`" . "\n";
            $message .= '➖ لیگ پیشنهادی : ' . $data[1] . "\n \n";
            $message .= '⚙️نوع عملیات را انتخاب کنید:';
            SendMessage(
                GP_REQUEST_LEAGUE,
                $message,
                $telegram->buildInlineKeyBoard([
                    [
                        $telegram->buildInlineKeyboardButton('✅ تایید لیگ', '', 'accept_league-' . $data[1]),
                        $telegram->buildInlineKeyboardButton('❌ رد کردن لیگ', '', 'reject_league-' . $chatid . '-' . $data[1]),
                    ],
                    [
                        $telegram->buildInlineKeyboardButton('☑️ قبلا اضافه شده', '', 'add_league-' . $chatid . '-' . $data[1]),
                    ]
                ])
            );


        } else {
            throw new ExceptionWarning('هر 20 ثانیه یک بار میتوانید درخواست آزاد شدن لیگی را بدهید.');
        }

        break;

    // ---------- Send Media And Buy Subscribe ----

    case 'subscribe':

        if ($user->haveSubscribe()) {

            $message = '📇 لیست اشتراک های فعال شما:' . "\n \n";

            foreach ($user->subscribes() as $subscribe) {

                switch ($subscribe->type) {

                    case 'voice':

                        $message .= '🏷 اشتراک نوع: ' . '<b>🎙 ویس</b>' . "\n";

                        break;

                    case 'video':

                        $message .= '🏷 اشتراک نوع: ' . '<b>🌠 گیف</b>' . "\n";

                        break;

                    case 'all':

                        $message .= '🏷 اشتراک نوع: ' . '<b><u>⭐️ ویس و گیف</u></b>' . "\n";

                        break;

                }

                $message .= '🏷 تاریخ فعال سازی: ' . '<b>' . jdate('Y/m/d', strtotime($subscribe->created_at)) . '</b>' . ' ✅' . "\n";
                $message .= '🏷 فعال تا تاریخ: ' . '<b>' . jdate('Y/m/d', strtotime($subscribe->ended_at)) . '</b>' . ' ✅' . "\n \n";

            }

            $message .= '<b>📮 برای تمدید اشتراک یکی از پلن های زیر را انتخاب کنید.</b>';

        } else {
            $message = '<b>🔴 شما هیچ اشتراکی ندارید.</b>' . "\n \n";
            $message .= '1️⃣ اشتراک یک ماهه
📯 فعال سازی ویس
💰۴۰۰ سکه 

2️⃣ اشتراک یک ماهه
📯فعال سازی گیف
💰۴۷۰ سکه

3️⃣ اشتراک یک ماهه
📯 فعال سازی گیف و ویس
💰۸۰۰ سکه

<b><u>⚠️توجه در صورت تمدید اشتراک تنها همان اشتراک شما تمدید می شود.
 لازم به ذکر است که اشتراک خریداری شده قابل بازگشت نیست!</u></b>

<b>💰 برای خرید اشتراک یکی از پلن های زیر را انتخاب کنید.</b>
';
        }

        $telegram->telegram()->editMessageText(
            $user->getUserId(),
            $messageid,
            $message,
            $telegram->buildInlineKeyBoard([
                [
                    $telegram->buildInlineKeyboardButton('🛍 اشتراک یک ماهه ویس', '', 'buy_subscribe-voice-31-400'),
                    $telegram->buildInlineKeyboardButton('🛍 اشتراک یک ماهه گیف', '', 'buy_subscribe-video-31-470'),
                ],
                [
                    $telegram->buildInlineKeyboardButton('🛍 اشتراک یک ماهه ویس و گیف', '', 'buy_subscribe-all-31-800'),
                ],
                [
                    $telegram->buildInlineKeyboardButton('🎞 مشاهده رسانه های آزاد', '', '', 'media'),
                ],
                [
                    $telegram->buildInlineKeyboardButton('👤 پروفایل', '', 'profile'),
                ],
                /*
                    [
                        $telegram->buildInlineKeyboardButton( '🛍 اشتراک سه ماهه ویس', '', 'buy_subscribe-voice-93' ),
                        $telegram->buildInlineKeyboardButton( '🛍 اشتراک سه ماهه گیف', '', 'buy_subscribe-video-93' ),
                    ],
                    [
                        $telegram->buildInlineKeyboardButton( '🛍 اشتراک سه ماهه ویس و گیف', '', 'buy_subscribe-all-93' ),
                    ],*/
            ])
        );

        break;

    case 'media':


        if ($user->user_on_game()) {

            if (check_time_chat($chatid, 3, 'message')) {

                $media = Media::find($data[1]);

                if (isset($media->id)) {

                    if ($user->haveSubscribeType($media->type)) {

                        $server = $user->server();

                        $caption = $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user->get_league()->emoji . ' <b>' . user()->name . ($user->dead() ? '(مرده)' : '') . '</b>';

                        switch (status($user->getUserId())) {

                            case 'last_chat':
                            case 'get_users_server':


                                foreach ($server->users() as $item) {

                                    if ($item->is_user_in_game()) {

                                        bot('Send' . strtoupper($media->type), [
                                            'chat_id' => $item->getUserId(),
                                            $media->type => $media->url,
                                            'caption' => $caption,
                                            'parse_mode' => 'html',
                                        ]);

                                    }

                                }

                                break;

                            case 'game_started':
                            case 'playing_game':
                            default:

                                if (!$user->hacked()) {

                                    if ($user->dead()) {

                                        foreach ($server->getDeadUsers() as $item) {

                                            if ($item->is_user_in_game()) {

                                                bot('Send' . strtoupper($media->type), [
                                                    'chat_id' => $item->getUserId(),
                                                    $media->type => $media->url,
                                                    'caption' => $caption,
                                                    'parse_mode' => 'html',
                                                ]);

                                            }

                                        }

                                    } else {

                                        foreach ($server->users() as $item) {

                                            if ($item->is_user_in_game() && !$item->sleep()) {
                                                bot('Send' . strtoupper($media->type), [

                                                    'chat_id' => $item->getUserId(),
                                                    $media->type => $media->url,
                                                    'caption' => $caption,
                                                    'parse_mode' => 'html',

                                                ]);
                                            }

                                        }

                                    }

                                } else {

                                    warning_message('🧑🏻‍💻 شما توسط هکر هک شده اید و امروز قادر به صحبت نیستید.');

                                }

                                break;

                            case 'night':

                                $selector = new \library\Role($server->getId());

                                if ($user->dead()) {

                                    foreach ($server->getDeadUsers() as $item) {

                                        if ($item->is_user_in_game()) {

                                            bot('Send' . strtoupper($media->type), [
                                                'chat_id' => $item->getUserId(),
                                                $media->type => $media->url,
                                                'caption' => $caption,
                                                'parse_mode' => 'html',
                                            ]);

                                        }

                                    }


                                } elseif ($user->get_role()->group_id == 2 && $server->getStatus() == 'light') {

                                    $role_group_2 = $server->roleByGroup(2);
                                    $bazpors_select = $selector->user()->select(ROLE_Bazpors);

                                    foreach ($role_group_2 as $item) {

                                        if ($item->check($bazpors_select) && $item->is_user_in_game() && (!$server->role_exists(ROLE_ShahKosh) || !$server->isFullMoon())) {
                                            bot('Send' . strtoupper($media->type), [
                                                'chat_id' => $item->getUserId(),
                                                $media->type => $media->url,
                                                'caption' => $caption,
                                                'parse_mode' => 'html',
                                            ]);
                                        }

                                    }

                                } elseif ($user->get_role()->group_id == 3 && in_array($server->league_id, MOSTAGHEL_TEAM) && $server->getStatus() == 'light') {

                                    $role_group_2 = $server->roleByGroup(3);
                                    $bazpors_select = $selector->user()->select(ROLE_Bazpors);

                                    foreach ($role_group_2 as $item) {

                                        if ($item->check($bazpors_select) && $item->is_user_in_game()) {
                                            bot('Send' . strtoupper($media->type), [
                                                'chat_id' => $item->getUserId(),
                                                $media->type => $media->url,
                                                'caption' => $caption,
                                                'parse_mode' => 'html',
                                            ]);
                                        }

                                    }

                                } else {

                                    throw new ExceptionWarning('الان نمیتونی چت کنی!');

                                }

                                break;

                            case 'voting':

                                if (!$user->hacked()) {

                                    $accused = $server->accused();

                                    if ($user->dead()) {


                                        foreach ($server->getDeadUsers() as $item) {

                                            if ($item->is_user_in_game()) {
                                                bot('Send' . strtoupper($media->type), [
                                                    'chat_id' => $item->getUserId(),
                                                    $media->type => $media->url,
                                                    'caption' => $caption,
                                                    'parse_mode' => 'html',
                                                ]);
                                            }

                                        }

                                    } elseif ($accused->is($user) || $server->getStatus() != 'court-2') {


                                        foreach ($server->users() as $item) {

                                            if ($item->is_user_in_game() && !$item->sleep()) {
                                                bot('Send' . strtoupper($media->type), [
                                                    'chat_id' => $item->getUserId(),
                                                    $media->type => $media->url,
                                                    'caption' => $caption,
                                                    'parse_mode' => 'html',
                                                ]);
                                            }

                                        }


                                    } else {

                                        throw new ExceptionWarning('الان نمیتونی چت کنی!');

                                    }


                                } else {

                                    warning_message('🧑🏻‍💻 شما توسط هکر هک شده اید و امروز قادر به صحبت نیستید.');

                                }

                                break;

                        }


                        AnswerCallbackQuery($dataid, '✅ با موفقیت ارسال شد.');

                    } else {
                        AnswerCallbackQuery($dataid, '⚠️ شما اشتراک این رسانه را ندارید❗️');
                    }

                } else {
                    AnswerCallbackQuery($dataid, '🔴 این رسانه دیگر وجود ندارد.');
                    DeleteMessage($chatid, $messageid);
                }

            } else {
                AnswerCallbackQuery($dataid, '⚠️ هر 3 ثانیه یک بار میتوانید رسانه ارسال کنید.', true);
            }

        } else {
            AnswerCallbackQuery($dataid, '🔴 شما در هیچ سروری نمی باشید.');
        }

        break;

    case 'media_page':

        if (isset($data[1]) && isset($data[2]) && isset($data[3])) {

            $page = $data[1];
            $action = $data[2];

            $action != 'next' ? --$page : ++$page;

            if ($page == 0) {
                AnswerCallbackQuery($dataid, '⛔️ انجام این عملیات ممکن نیست.');
                die();
            }

            $result = Media::getMediaWithType($data[3], $page);

            if (count($result) > 0) {

                foreach ($result as $media) {

                    $text = '';
                    switch ($media->type) {

                        case 'voice':

                            $text .= '🎙 ';

                            break;

                        case 'video':

                            $text .= '🌠';

                            break;

                    }

                    $text .= $media->title;
                    $keyboard[][] = $telegram->buildInlineKeyboardButton($text, '', 'media-' . $media->id);

                }


                $keyboard[][] = $telegram->buildInlineKeyboardButton('⏭ صفحه بعدی', '', 'media_page-' . $page . '-next-' . $data[3]);

                if ($page != 1) {
                    $keyboard[][] = $telegram->buildInlineKeyboardButton('⏮ صفحه قبل', '', 'media_page-' . $page . '-last-' . $data[3]);
                }

                $keyboard[][] = $telegram->buildInlineKeyboardButton('➡️ برگشت', '', 'media_home');

                EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            } else {
                AnswerCallbackQuery($dataid, '❌ هیچ رسانه دیگری وجود ندارد.');
            }

        } else {
            AnswerCallbackQuery($dataid, '❌ خطایی رخ داد.');
        }

        break;

    case 'media_group':

        if ($user->haveSubscribeType($data[1])) {

            foreach (Media::getMediaWithType($data[1]) as $media) {

                $text = '';
                switch ($media->type) {

                    case 'voice':

                        $text .= '🎙 ';

                        break;

                    case 'video':

                        $text .= '🖼';

                        break;

                }

                $text .= $media->title;
                $keyboard[][] = $telegram->buildInlineKeyboardButton($text, '', 'media-' . $media->id);

            }

            $keyboard[][] = $telegram->buildInlineKeyboardButton('⏭ صفحه بعدی', '', 'media_page-' . 1 . '-next-' . $data[1]);
            $keyboard[][] = $telegram->buildInlineKeyboardButton('➡️ برگشت', '', 'media_home');

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        } else {
            AnswerCallbackQuery($dataid, '⚠️ شما اشتراک این رسانه را ندارید❗️');
        }

        break;

    case 'media_home':

        EditKeyboard(
            $chatid,
            $messageid,
            $telegram->buildInlineKeyBoard([
                [
                    $telegram->buildInlineKeyboardButton('🎙 ویس', '', 'media_group-voice'),
                    $telegram->buildInlineKeyboardButton('🌠 گیف', '', 'media_group-video'),
                ]
            ])
        );

        break;

    case 'buy_subscribe':

        $type = $data[1];
        $day = $data[2];
        $coin = $data[3];

        switch ($type) {

            case 'voice':

                $type_fa = 'ویس';

                break;

            case 'video':

                $type_fa = 'گیف';

                break;

            case 'all':

                $type_fa = 'ویس و گیف';

                break;

        }
        $message = '🛍 شما در حال خرید اشتراک ' . "<b>" . tr_num($day, 'fa') . ' روزه' . "</b>" . ' استفاده از ' . "<b><u>" . $type_fa . "</u></b>" . ' هستید ایا از انجام خرید خود اطمینان دارید؟';
        $telegram->telegram()->editMessageText(
            $user->getUserId(),
            $messageid,
            $message,
            $telegram->buildInlineKeyBoard([
                [
                    $telegram->buildInlineKeyboardButton('✅ تایید', '', 'buy_subscribe_2-' . $data[1] . '-' . $data[2] . '-' . $data[3]),
                    $telegram->buildInlineKeyboardButton('⛔️ انصراف', '', 'cancel'),
                ]
            ])
        );

        break;

    case 'buy_subscribe_2':

        $type = $data[1];
        $day = $data[2];
        $coin = $data[3];

        if ($user->has_coin($coin) && $user->demote_coin($coin) && $user->addSubscribe($type, $day, $coin)) {

            switch ($type) {

                case 'voice':

                    $type_fa = 'ویس';

                    break;

                case 'video':

                    $type_fa = 'گیف';

                    break;

                case 'all':

                    $type_fa = 'ویس و گیف';

                    break;

            }

            $message = '<b>📌 اشتراک ' . $day . ' روزه ' . "<u>" . $type_fa . "</u>" . ' برای شما با موفقیت فعال شد✅</b>' . "\n \n";
            $message .= '<b><s>♦️اشتراک فعال شده برای آیدی ' . "<code>" . $user->getUserId() . "</code>" . ' می باشد.</s></b>';
            $telegram->telegram()->editMessageText($user->getUserId(), $messageid, $message);

        } else {
            $telegram->telegram()->answerCallbackQuery($dataid, '🚫 موجودی شما برای خرید اشتراک کافی نمی باشد.');
        }

        break;

    // -------------------------------------

    case 'dart':

        $message = '🧩 بازی شانسی خودتون رو انتخاب کنید :' . "\n \n";
        $message .= '<b>امتیازدهی مانند دارت👇🏻</b>' . "\n";
        $message .= 'بولینگ 🎳' . "\n";
        $message .= 'تاس 🎲' . "\n";
        $message .= 'دارت 🎯' . "\n \n";
        $message .= '<b>امتیازدهی ۵ سکه یا ۵ امتیاز 👇🏻</b>' . "\n";
        $message .= 'ماشین پولی 🎰' . "\n";
        $message .= 'پنالتی ⚽️' . "\n";
        $message .= 'بسکتبال 🏀';
        $dart = $user->get_meta('dart');
        EditMessageText(
            $chatid,
            $messageid,
            $message,
            $telegram->buildInlineKeyBoard([
                [
                    $telegram->buildInlineKeyboardButton('بولینگ 🎳' . ($dart == 'boling' ? '✔️' : ''), '', 'change_dart-boling'),
                    $telegram->buildInlineKeyboardButton('تاس 🎲' . ($dart == 'tas' ? '✔️' : ''), '', 'change_dart-tas'),
                    $telegram->buildInlineKeyboardButton('دارت 🎯' . ($dart == 'dart' || empty($dart) ? '✔️' : ''), '', 'change_dart-dart'),
                ],
                [
                    $telegram->buildInlineKeyboardButton('ماشین پولی 🎰' . ($dart == 'car' ? '✔️' : ''), '', 'change_dart-car'),
                    $telegram->buildInlineKeyboardButton('پنالتی ⚽️' . ($dart == 'penalti' ? '✔️' : ''), '', 'change_dart-penalti'),
                    $telegram->buildInlineKeyboardButton('بسکتبال 🏀' . ($dart == 'bascetbal' ? '✔️' : ''), '', 'change_dart-bascetbal'),
                ],
                [
                    $telegram->buildInlineKeyboardButton('👤 پروفایل', '', 'profile')
                ]
            ])
        );


        break;

    case 'change_dart':

        $dart = $data[1];

        if ($dart == 'car') {
            AnswerCallbackQuery($dataid, 'این بخش بعدا فعال می شود');
            die();
        }

        $user->update_meta('dart', $dart);
        EditKeyboard(
            $chatid,
            $messageid,
            $telegram->buildInlineKeyBoard([
                [
                    $telegram->buildInlineKeyboardButton('بولینگ 🎳' . ($dart == 'boling' ? '✔️' : ''), '', 'change_dart-boling'),
                    $telegram->buildInlineKeyboardButton('تاس 🎲' . ($dart == 'tas' ? '✔️' : ''), '', 'change_dart-tas'),
                    $telegram->buildInlineKeyboardButton('دارت 🎯' . ($dart == 'dart' || empty($dart) ? '✔️' : ''), '', 'change_dart-dart'),
                ],
                [
                    $telegram->buildInlineKeyboardButton('ماشین پولی 🎰' . ($dart == 'car' ? '✔️' : ''), '', 'change_dart-car'),
                    $telegram->buildInlineKeyboardButton('پنالتی ⚽️' . ($dart == 'penalti' ? '✔️' : ''), '', 'change_dart-penalti'),
                    $telegram->buildInlineKeyboardButton('بسکتبال 🏀' . ($dart == 'bascetbal' ? '✔️' : ''), '', 'change_dart-bascetbal'),
                ],
                [
                    $telegram->buildInlineKeyboardButton('👤 پروفایل', '', 'profile')
                ]
            ])
        );

        break;

    default:

        AnswerCallbackQuery($dataid, '🔄 این بخش بزودی فعال می شود🤝', true);

        break;
}