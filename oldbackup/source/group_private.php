<?php
/** @noinspection ALL */

switch ( $data[0] )
{

    case '/list':

        if ( $chat_id != GP_MANAGER ) die();

        if ( isset( $data[1] ) )
        {

            $ban = get_ban( $data[1] );
            if ( isset( $ban ) && $ban->status == 1 )
            {
                $message = '♨️ وضعیت فعلی کاربر [[user]]' . "\n \n";
                $message .= '🔴 کاربر به مدت [[time]] مسدود است .';
                $date    = time_to_string( $ban->end_time );
                __replace__( $message, [
                    '[[time]]' => $date ?? 'Nan',
                    '[[user]]' => '<a href="tg://user?id=' . $data[1] . '">' . user( $data[1] )->name . '</a>'
                ] );
                SendMessage(
                    GP_MANAGER, $message, $telegram->buildInlineKeyBoard( [
                    [
                        $telegram->buildInlineKeyboardButton( '🟩 آزاد کردن', '', 'unban-' . $data[1] )
                    ]
                ] ), null, 'html'
                );
            }
            else
            {
                $message = '♨️ وضعیت فعلی کاربر [[user]]' . "\n \n" . '🟢 کاربر آزاد است .';
                __replace__( $message, [
                    '[[user]]' => '<a href="tg://user?id=' . $data[1] . '">' . user( $data[1] )->name . '</a>'
                ] );
                SendMessage(
                    GP_MANAGER, $message, $telegram->buildInlineKeyBoard( [
                    [
                        $telegram->buildInlineKeyboardButton( '⛔️ اعمال مسدودی', '', 'ban-' . $data[1] )
                    ]
                ] ), null, 'html'
                );
            }

        }
        else
        {

            SendMessage( GP_MANAGER, 'شما فراموش کردید آیدی عددی کاربر را وارد کنید' );

        }


        break;

    case '/chat':

        if ( $chat_id != GP_MANAGER ) die();

        if ( isset( $data[1] ) )
        {

            $chats   = array_reverse( get_chats( $data[1], ( $data[2] ?? 30 ) ) );
            $message = 'گزارش چت کاربر [[user]]:' . "\n \n";
            if ( count( $chats ) > 0 )
            {
                $count = 0;
                foreach ( $chats as $item )
                {
                    if ( !empty( $item->to_user ) )
                    {
                        $message .= '~~~' . "\n";
                        $message .= '[[time]] - پیام خصوصی به [[league]] [[user]] [[user_id]]: [[text]]' . "\n";
                        $message .= '~~~' . "\n";
                        __replace__( $message, [
                            '[[time]]'    => jdate( 'H:i', $item->created_at ),
                            '[[user]]'    => user( $item->to_user )->name,
                            '[[text]]'    => $item->text,
                            '[[league]]'  => $item->to_user_emoji,
                            '[[user_id]]' => $item->to_user,
                        ] );
                    }
                    else
                    {
                        $message .= '[[time]] - [[league]] [[user]]: [[text]]' . "\n";
                        __replace__( $message, [
                            '[[time]]'   => jdate( 'H:i', $item->created_at ),
                            '[[user]]'   => $item->name,
                            '[[text]]'   => $item->text,
                            '[[league]]' => $item->league
                        ] );
                    }
                    $count ++;
                    if ( mb_strlen( $message, 'utf8' ) >= 3850 )
                    {
                        $message .= "\n" . 'تعداد چت های ارسال شده: ' . $count;
                        SendMessage( $chat_id, $message, null, null, 'html' );
                        $message = '';
                    }
                }
                if ( mb_strlen( $message, 'utf8' ) < 3850 )
                {
                    $message .= "\n" . 'تعداد چت های ارسال شده: ' . $count;
                }
            }
            else
            {
                $message = 'چت های این کاربر یافت نشد.';
            }
            SendMessage( $chat_id, $message, null, null, 'html' );

        }
        else
        {

            SendMessage( GP_MANAGER, 'شما فراموش کردید آیدی عددی کاربر را وارد کنید' );

        }

        break;

    case 'ویتو':

        SendMessage( $chat_id, 'جانم ؟ :)', null, $message_id );

        break;

    case 'ساخت':

        if ( $data[1] == 'بازی' )
        {

            $user = new \library\User( $from_id );

            if ( $user->registed() )
            {


                if ( !is_null( $user->league ) )
                {

                    $league      = get_vip_league_user_by_id( $user->league );
                    $league_name = $league->emoji . ' ' . $league->name;

                }
                else
                {

                    $league_name = $user->get_league()->icon;

                }

                if ( empty( $user->getServerId() ) )
                {

                    $keyboard = [];

                    $point = $user->get_point();

                    foreach ( get_games() as $game )
                    {

                        if ( $game->point >= 0 && $game->point <= $point && date( 'H' ) >= ( $game->start_time ?? 0 ) && date( 'H' ) <= ( $game->end_time ?? 23 ) )
                        {

                            $keyboard[][] = $telegram->buildInlineKeyboardButton( $game->icon, '', 'join_server-' . $game->id . '-' . $from_id );

                        }
                        else break;

                    }

                    $message = '🕹 مایل به ساخت چه نوع بازی هستید ؟';
                    SendMessage( $chat_id, $message, $telegram->buildInlineKeyBoard( $keyboard ), $message_id );


                }
                else
                {

                    SendMessage( $chat_id, 'هنگامی که داخل یک بازی هستید نمیتوانید بازی دوستانه بسازید' );

                }

            }
            else
            {
                SendMessage(
                    $chat_id, ' برای شروع یک بازی دوستانه اول باید ثبت نام کنید', $telegram->buildInlineKeyBoard( [
                    [
                        $telegram->buildInlineKeyboardButton( 'ثبت نام', 'https://t.me/iranimafiabot' )
                    ]
                ] )
                );

            }

        }


        break;

    case 'امتیازات':

        $user = new \library\User( $from_id );

        if ( check_time_chat( $chat_id, '5' ) )
        {

            $message    = '📊 لیست برترین های ایرانی مافیا ' . "\n \n";
            $list_users = get_top_rank_points();
            $leagues    = [];
            foreach ( $list_users as $user )
            {
                $user_league                 = get__league_user( $user->user_id );
                $leagues[$user_league->id][] = $user;
            }

            $x         = 1;
            $user_list = [];

            foreach ( $leagues as $league_id => $item )
            {
                $league  = get__league( $league_id );
                $message .= $league->icon . ' 👇' . "\n";
                foreach ( $item as $user )
                {
                    if ( !empty( $user->user->name ) )
                    {

                        switch ( $x )
                        {
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

                        $message .= ( $from_id == $user->user_id ? '👈 ' : '[[' . $x . ']]  ' ) . "<b>" . $user->user->name . "</b>" . ( $from_id == $user->user_id ? ' (شما)' : ' ' ) . '      - [[point]] 🌟' . ( $emoji_rank ) . "\n";
                        __replace__( $message, [
                            '[[10]]'    => '🔟',
                            '[[1]]'     => '1️⃣',
                            '[[2]]'     => '2️⃣',
                            '[[3]]'     => '3️⃣',
                            '[[4]]'     => '4️⃣',
                            '[[5]]'     => '5️⃣',
                            '[[6]]'     => '6️⃣',
                            '[[7]]'     => '7️⃣',
                            '[[8]]'     => '8️⃣',
                            '[[9]]'     => '9️⃣',
                            '[[point]]' => "<b>" . tr_num( $user->point, 'fa', '.' ) . "</b>",
                        ] );
                        if ( $user->user_id == $from_id )
                        {
                            $rank = $x;
                        }
                        $x ++;
                        $user_list[] = $user->user_id;
                    }
                    if ( $x > 10 )
                    {
                        break 2;
                    }
                }
                $message .= "\n";
            }

            $message .= "\n" . '🔹رتبه شما : [[rank]]';

            $message .= "\n" . '🔸امتیاز شما : [[point]]' . "\n";
            $message .= '<a href="https://t.me/iranimafia/89">❗️تمامی لیگ های بازی</a>' . "\n \n";
            $message .= '@iranimafia';


            $number_to_word = new NumberToWord();
            $rank           = get_rank_user_in_global( $from_id );
            $result         = $rank > 5 ? $rank : $number_to_word->numberToWords( $rank );

            __replace__( $message, [
                '[[point]]' => "<b>" . tr_num( get_point( $from_id ), 'fa', '.' ) . "</b>",
                '[[rank]]'  => "<b>" . tr_num( $result, 'fa', '.' ) . "</b>"
            ] );

            $emoji = '';
            add_filter( 'filter_league_user', function ( $query ) {
                global $emoji;
                $emoji = $query->emoji;
            }, 1 );
            $user_league = get__league_user( $from_id );

            $telegram->sendMessage( [
                'chat_id'                  => $chat_id,
                'text'                     => $message,
                'parse_mode'               => 'html',
                'reply_markup'             => $telegram->buildInlineKeyBoard( [
                    [
                        $telegram->buildInlineKeyboardButton( '📊 برترین های بازی ' . '✔️', '', 'rank_top_all' )
                    ],
                    [
                        $telegram->buildInlineKeyboardButton( '📆 هفتگی', '', 'rank_top_week' ),
                        $telegram->buildInlineKeyboardButton( '📅 روزانه', '', 'rank_top_today' ),
                        $telegram->buildInlineKeyboardButton( ( $emoji . ' لیگ من' ), '', 'rank_top_my_league' ),
                    ]
                ] ),
                'disable_web_page_preview' => true,
                'reply_to_message_id'      => $message_id

            ] );

        }
        else
        {
            SendMessage( $chat_id, '✋ هر 5 ثانیه فقط 1 بار میتوانید درخواست دهید.' );
        }

        break;


}