<?php


use library\Server;
use library\User;


if ( $data[0] == '/start' && count( $data ) == 2 )
{

    if ( is_user_row_in_game( $chat_id ) )
    {

        $user_bot = get_game()->bot;
        if ( $user_bot != $BOT_ID )
        {

            $message = 'نمیتوانید هنگامی در بازی هستید از ربات دیگری استفاده کنید.' . "\n \n";
            $message .= 'شما باید از این ربات استفاده کنید: ';
            if ( $user_bot == 0 )
            {
                $message .= '@iranimafiabot';
            }
            else
            {
                $message .= '@iranimafia_' . ( $user_bot ) . 'bot';
            }
            throw new ExceptionWarning( $message );

        }

    }

    $data = explode( '-', $data[1] );
    switch ( $data[0] )
    {
        case 'server':

            if ( !empty( user()->name ) )
            {

                do_action( 'join_channel' );
                do_action( 'check_ban' );
                if ( empty( get_game()->server_id ) )
                {

                    $code = $data[1];
                    if ( !is_numeric( $code ) )
                    {

                        $code = string_decode( $code );

                    }
                    else
                    {

                        throw new ExceptionError( 'متاسفانه سرور یافت نشد.' );

                    }

                    if ( is_numeric( $code ) )
                    {

                        $server        = new Server( $code );
                        $User          = new User( $chat_id, $server->getId() );
                        $server_league = $server->get_league();

                        /*if ( $server->getUserId() == ADMIN_ID && $User->get_point() <= $server->get_league()->point )
                        {

                            throw new ExceptionWarning('شما امتیاز کافی برای پیوستن به این سرور را ندارید.');

                        }*/

                        if ( $server instanceof Server && $server->getId() > 0 )
                        {

                            if ( $server->count() < $server_league->count && $server->status == 'opened' )
                            {

                                if ( $User->add_to_game() )
                                {

                                    $user__league = $User->get_league();

                                    /*$message = '📍 شما به بازی نوع ' . "<u>" . $server_league->icon . '،' . $server_league->count . ' نفره' . "</u>" . ' پیوستید .' . "\n";
                                    $message .= '🔰 در حال جستجوی بازیکن آنلاین ... لطفا منتظر بمانید .' . "\n \n";
                                    $message .= 'اعضای بازی : ' . "\n";*/

                                    $message = '🎲 درحال جستجوی بازیکن برای شروع ...' . "\n";
                                    $message .= '🔸 نوع بازی :  ' . $server_league->icon . ' ، ' . tr_num( $server_league->count, 'fa' ) . ' نفره' . "\n \n";
                                    $message .= '👥 لیست افراد در صف انتظار' . "\n";

                                    $users_server = $server->users();

                                    foreach ( $users_server as $id => $item )
                                    {

                                        $message .= $id + 1 . '- ' . $item->get_league()->emoji . ' ' . $item->user()->name . "\n";

                                    }

                                    SendMessage( $chat_id, $message, KEY_GUST_GAME_MENU, null, 'html' );

                                    $message = "<b>" . count( $users_server ) . ".</b>" . "<u><b>" . $user__league->emoji . $User->user()->name . "</b></u>" . ' به این بازی پیوست.';

                                    foreach ( $users_server as $item )
                                    {

                                        if ( !$item->is( $User ) )
                                        {

                                            SendMessage( $item->getUserId(), $message, ( $item->is( $server->server()->user_id ) ? KEY_HOST_GAME_MENU : KEY_GUST_GAME_MENU ), null, 'html' );

                                        }

                                    }

                                    $User->setStatus( 'get_users_server' );


                                    check_server_members( $server->server() );


                                }
                                else
                                {

                                    $message = '⚙️ متاسفانه در اضافه کردن شما به سرور مشکلی پیش امد!.' . "\n \n";
                                    $message .= '💢 لطفا با پشتیبانی گزارش دهید!';
                                    Message();

                                }

                            }
                            else
                            {

                                $message = '⚙️ این سرور پر یا شروع شده است.';
                                Message();

                            }

                        }
                        else
                        {

                            $message = '⚙️ متاسفانه سروری یافت نشد.';
                            Message();

                        }

                    }
                    else
                    {

                        $message = '❌ با عرض پوزش سرور یافت نشد.';
                        Message();

                    }

                }
                else
                {

                    $message = '⚙️ شما هم اکنون در حال بازی هستید!';
                    SendMessage( $chat_id, $message, KEY_GAME_ON_MENU );
                }

            }
            else
            {

                do_action( 'start' );
            }

            break;
        case 'start':
            apply_filters( 'filter_user_in_game', $chat_id );
            do_action( 'start' );
            break;
        case 'easy':
            if ( !empty( user()->name ) )
            {

                if ( empty( get_game()->server_id ) )
                {

                    apply_filters( 'filter_user_in_game', $chat_id );
                    do_action( 'check_ban' );

                    $server_id = get_server_by_league( 1 );

                    add_player_to_server( $chat_id, 0, 0, $server_id );

                }
                else
                {

                    $message = '⚙️ شما هم اکنون در حال بازی هستید!';
                    SendMessage( $chat_id, $message, KEY_GAME_ON_MENU );

                }

            }
            else
            {

                do_action( 'start' );

            }
            break;
        case 'hard':
            if ( !empty( user()->name ) )
            {

                if ( empty( get_game()->server_id ) )
                {

                    do_action( 'check_ban' );
                    apply_filters( 'filter_user_in_game', $chat_id );

                    $server_league = get_league( 2 );
                    if ( $user->get_point() >= $server_league->point )
                    {

                        $server_id = get_server_by_league( 2 );
                        add_player_to_server( $chat_id, 0, 0, $server_id );

                    }
                    else
                    {

                        $message = '⚠️ خطا ! شما امتیاز کافی برای ورود به بازی سخت را ندارید .';
                        SendMessage( $chat_id, $message );

                    }

                }
                else
                {

                    $message = '⚙️ شما هم اکنون در حال بازی هستید!';
                    SendMessage( $chat_id, $message, KEY_GAME_ON_MENU );

                }

            }
            else
            {
                do_action( 'start' );
            }
            break;
        case 'code':
            $message = '♨️ کوپن خود را وارد کنید.';
            SendMessage( $chat_id, $message, KEY_BACK_TO_START_MENU );
            update_status( 'get_coupon_code' );
            break;

        /*case 'challenge':

            if (!empty(user()->name)) {

                do_action('join_channel');
                do_action('check_ban');
                if (empty(get_game()->server_id)) {


                    if ($user->get_point() >= 200) {

                        $server_id = 265;

                        $server = new Server($server_id);

                        if ($server->count() < $server->get_league()->count && $server->status == 'opened') {

                            $login = $link->get_result("SELECT * FROM `challenge` WHERE `user_id` = {$chat_id}");

                            if (count($login) < 1) {

                                add_player_to_server($chat_id, 0, 0, $server_id);
                                $link->insert('challenge', [
                                    'user_id' => $chat_id,
                                    'server_id' => $server_id
                                ]);

                            } else {

                                $message = '♨️ شما قبلا در این چالش شرکت کرده اید🙏';
                                Message();

                            }


                        } else {

                            $message = '♨️ دیر آمدی چالش شروع شد. منتظر چالش بعدی باش.';
                            Message();

                        }

                    } else {

                        $message = '🚫 شما باید حداقل 50 امتیاز برای شرکت در این چالش را داشته باشید.';
                        Message();

                    }


                } else {

                    $message = '⚙️ شما هم اکنون در حال بازی هستید!';
                    SendMessage($chat_id, $message, KEY_GAME_ON_MENU);
                }

            } else {

                do_action('start');
            }

            break;*/

        default:
            apply_filters( 'filter_user_in_game', $chat_id );
            do_action( 'check_ban' );

            $user_id = string_decode( $data[0] );
            if ( is_numeric( $user_id ) && $user_id != $chat_id && user_exists( $user_id ) )
            {

                if ( empty( $user->get_meta( 'sub_user' ) ) )
                {

                    $user->update_meta( 'sub_user', $user_id );

                }

            }

            do_action( 'start' );
            break;
    }
    exit();
}