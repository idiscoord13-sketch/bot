<?php

/** @noinspection ALL */

use library\DownloadManager;
use library\Media as Media;
use library\Role;
use library\Server;
use library\Text;
use library\User;

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
else
{

    if ( ! check_time_chat( $chat_id, 1 ) )
    {
        throw new ExceptionMessage( 'لطفاً از ارسال پیام های تکراری بپرهیزید.' );
    }

}


switch ( strtolower( $text ) )
{
    case '/start':
    case '♨️ بازگشت به منوی اصلی':
        apply_filters( 'filter_user_in_game', $chat_id );
        do_action( 'start' );
        update_status( '' );
        break;

    case '/startgame':
    case '🕹 شروع بازی آنلاین':

        do_action( 'join_channel' );
        do_action( 'check_ban' );
        if ( empty( get_game()->server_id ) )
        {

            if ( ! empty( user()->name ) )
            {

                for ( $i = $BOT_ID; $i < count( $token_bot ); $i ++ )
                {

                    if ( get_count_members_bots( $BOT_ID ) >= 40 )
                    {

                        $message = '⚠️ بنظر میرسد این سرور پر یا شلوغ شده است .' . "\n";
                        $message .= 'لطفا از ربات های خلوت تر استفاده کنید 👇' . "\n \n \n";
                        $message .= '';

                        $redirect_bot = null;
                        foreach ( $token_bot as $index => $token )
                        {

                            $count_members = get_count_members_bots( $index );
                            $bot           = bot( 'GetMe', [], $token );
                            $message       .= 'وضعیت: ' . '@' . $bot->username . ' : ' . get_status_servers_bots( $count_members ) . "\n";
                            if ( $count_members < 35 && $redirect_bot === null )
                            {

                                $redirect_bot = $index;

                            }

                        }

                        if ( $redirect_bot === null )
                        {

                            for ( $i = $BOT_ID; $i != 0; $i -- )
                            {

                                $count_members = get_count_members_bots( $i );
                                if ( $count_members < 35 )
                                {

                                    $redirect_bot = $i;
                                    break;

                                }

                            }

                        }

                        if ( $redirect_bot !== null )
                        {

                            $bot     = bot( 'GetMe', [], $token_bot[ $redirect_bot ] );
                            $message .= "\n" . '💢 ما ' . "<a href='https://t.me/" . $bot->username . "'>ربات شماره " . ( $redirect_bot + 1 ) . "</a>" . ' را برای شما پیشنهاد میکنیم.';

                        }
                        else
                        {

                            $message .= '⛔️ متاسفانه تمامی سرور ها پر هستند.';

                        }

                        $telegram->sendMessage( [
                            'chat_id'                  => $chat_id,
                            'text'                     => $message,
                            'parse_mode'               => 'html',
                            'disable_web_page_preview' => true
                        ] );

                        exit();

                    }
                    else
                    {

                        break;

                    }

                }


                $join = $user->get_meta( 'join' );
                switch ( $join )
                {
                    case 'asking':

                        $message = '🎮 سناریو بازی شما روی ❓همیشه بپرس تنظیم شده است .' . "\n";
                        $message .= 'نوع بازی مدنظر خود را برای شروع انتخاب کنید 👇🏻';

                        $point = $user->get_point();
                        foreach ( get_games() as $game )
                        {
                            if ( $game->point >= 0 && $game->point <= $point && date( 'H' ) >= ( $game->start_time ?? 0 ) && date( 'H' ) <= ( $game->end_time ?? 23 ) )
                            {
                                if($game->name == 'easy' && $point < 1500 )
                                {
                                    $keyboard[][] = $telegram->buildInlineKeyboardButton( $game->icon, '', 'join_server-' . $game->id );
                                }
                                elseif (  $game->name != 'easy' )
                                {
                                    // For users with 1500 points or more, add the game
                                    $keyboard[][] = $telegram->buildInlineKeyboardButton( $game->icon, '', 'join_server-' . $game->id );
                                }
                            }
                            // Removed 'else break;' to prevent premature loop termination
                        }

                        SendMessage( $chat_id, $message, $telegram->buildInlineKeyBoard( $keyboard ) );

                        break;

                    case 'random':
                    case 'priority':
                    default:

                        // if ( has_coin( $chat_id, 2 ) )
                        // {

                        //     $message = '♨️ نقش خود را از قبل بازی انتخاب کنید .';
                        //     SendMessage(
                        //         $chat_id, $message, $telegram->buildInlineKeyBoard( [
                        //         [
                        //             $telegram->buildInlineKeyboardButton( '🟢 نقش شهروند', '', 'select_role_game-1' ),
                        //         ],
                        //         [
                        //             $telegram->buildInlineKeyboardButton( '🔴 نقش مافیا', '', 'select_role_game-2' ),
                        //         ],
                        //         [
                        //             $telegram->buildInlineKeyboardButton( '🟡 نقش مستقل', '', 'select_role_game-3' ),
                        //         ],
                        //         [ $telegram->buildInlineKeyboardButton( '🟣 شگفت انگیز', '', 'select_role_game-4' ), ],
                        //         [
                        //             $telegram->buildInlineKeyboardButton( '🎲 نقش تصادفی ( رایگان )', '', 'select_role_game-0' ),
                        //         ],
                        //     ] )
                        //     );

                        // }
                        // else
                        // {

                        // switch ( $join )
                        // {
                        //     case 'random':
                        //         break;
                        //     case 'priority':
                        //     default:
                        //         $priority = $user->get_meta( 'priority' );
                        //         $priority = empty( $priority ) ? $user->get_game()->id : $priority;
                        //         $server   = Server::getServerByLeague( $priority );
                        //         break;
                        // }
                        $server = Server::getServerOrderByLeague( get_league_user( $chat_id )->id );

                        if ( $server->getId() > 0 )
                        {

                            add_player_to_server( $chat_id, 0, 0, $server->getId() );

                        }
                        else
                        {

                            $user->addToServerByLeague();

                        }

                        // }

                        break;


                }

            }
            else
            {
                do_action( 'start' );
            }

        }
        else
        {
            $message = '⚙️ شما هم اکنون در حال بازی هستید!';
            SendMessage( $chat_id, $message, KEY_GAME_ON_MENU );
        }

        break;

    case '🔄 پیگیری تراکنش':
    case '/paybot':

        $message = 'لطفا لینک یا کد تراکنش خود را اینجا ارسال کنید.';
        $user->SendMessageHtml( $message )->setStatus( 'get_auth_code' );

        break;

    case '/easy':
        do_action( 'join_channel' );
        do_action( 'check_ban' );
        apply_filters( 'filter_user_in_game', $chat_id );
        if ( ! empty( user()->name ) )
        {
            if ( empty( get_game()->server_id ) )
            {

                if ( $user->get_point() < 1500  )
                {
                    $server_id = get_server_by_league( 1 );
                    add_player_to_server( $chat_id, 0, 0, $server_id );

                }else
                {

                    $message = '⚠️خطا !
بازی ساده فقط برای امتیاز کمتر از ۱۵۰۰ مجاز است .
 از بازی سخت یا ویژه استفاده کنید .';
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

    case '/hard':
        do_action( 'join_channel' );
        do_action( 'check_ban' );
        apply_filters( 'filter_user_in_game', $chat_id );
        if ( ! empty( user()->name ) )
        {
            if ( empty( get_game()->server_id ) )
            {

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

    /*case '/mostaghel':

        do_action('join_channel');
        do_action('check_ban');
        apply_filters('filter_user_in_game', $chat_id);

        if ( !empty(user()->name) )
        {
            if ( empty(get_game()->server_id) )
            {

                $server_league = get_league(3);
                if ( $user->get_point() >= $server_league->point )
                {

                    $server_id = get_server_by_league(3);
                    add_player_to_server($chat_id, 0, 0, $server_id);

                }
                else
                {

                    $message = '⚠️ خطا ! شما امتیاز کافی برای ورود به بازی سخت را ندارید .';
                    SendMessage($chat_id, $message);

                }

            }
            else
            {

                $message = '⚙️ شما هم اکنون در حال بازی هستید!';
                SendMessage($chat_id, $message, KEY_GAME_ON_MENU);

            }
        }
        else
        {
            do_action('start');
        }

        break;*/

    case '/special':

        do_action( 'join_channel' );
        do_action( 'check_ban' );
        apply_filters( 'filter_user_in_game', $chat_id );

        if ( ! empty( user()->name ) )
        {
            if ( empty( get_game()->server_id ) )
            {

                $server_league = get_league( 4 );
                if ( $user->get_point() >= $server_league->point )
                {

                    $server_id = get_server_by_league( 4 );
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

    case '/exit':
    case '⏏️ خروج از بازی':
    case '▶️ خروج از بازی':


        if ( $user->user_on_game() )
        {

            $server = new Server( $user->getServerId() );

            if ( $server->exists() )
            {

                if ( $server->getStatus() == 'chatting' )
                {

                    if ( logout_server( $chat_id ) )
                    {

                        $message = '🔸 شما از بازی خارج شدید .' . "\n \n" . 'منوی اصلی 👇';
                        $user->setKeyboard( KEY_START_MENU )->SendMessageHtml()->setStatus( '' );
                        $message = $user->get_league()->emoji . ' ' . '<u>' . $user->user()->name . '</u>' . ' از بازی خارج شد.';
                        foreach ( $server->users() as $user_game )
                        {

                            if ( ! $user->is( $user_game ) && $user_game->is_ban() && $user_game->is_user_in_game() ) $user_game->SendMessageHtml( $message );

                        }

                    }
                    else
                    {

                        $message = '❌ در خروج از بازی مشکلی پیش آمد!';
                        SendMessage( $chat_id, $message );

                    }

                }
                elseif ( $server->status == 'started' )
                {

                    if ( $user->dead() )
                    {

                        $message = 'هنوز بازی به پایان نرسیده .' . "\n" . '❗️درصورت خارج شدن ، امتیاز آخر بازی برات محسوب نمیشه .' . "\n" . 'آیا مطمئنی که میخوای از بازی خارج بشی ؟';
                        SendMessage(
                            $chat_id, $message, $telegram->buildInlineKeyBoard( [
                            [
                                $telegram->buildInlineKeyboardButton( 'آره مطمئنم', '', 'exit_game' ),
                                $telegram->buildInlineKeyboardButton( 'نه ، بی‌خیال', '', 'stay_server' ),
                            ]
                        ] )
                        );

                    }
                    else
                    {

                        $message = '⛔️ شما هم اکنون در حال بازی هستید و نمیتوانید از بازی خارج شوید.';
                        SendMessage( $chat_id, $message, KEY_GAME_ON_MENU );

                    }

                }
                elseif ( ! is_server_meta( $server->getId(), 'friend', $chat_id ) || ($server->getUserId() == $chat_id && $server->type == 'private') )
                {


                    if ( logout_server( $chat_id ) )
                    {

                        if ( $server->getUserId() == $chat_id && $server->server()->type == 'private')
                        {

                            /*$message = '🔸 شما از بازی خارج شدید .' . "\n" . 'منوی اصلی 👇';
                            foreach ( $server->users() as $item )
                            {

                                $item->logout()->SendMessageHtml(KEY_START_MENU)->setStatus('');

                            }*/
                            $server->close();
                            $message = '🔸 شما از بازی خارج شدید .' . "\n \n" . 'منوی اصلی 👇';
                            $user->setKeyboard( KEY_START_MENU )->SendMessageHtml()->setStatus( '' );

                        }
                        else
                        {
                            $number_emojis = [
                                1 => '۱',
                                2 => '۲',
                                3 => '۳',
                                4 => '۴',
                                5 => '۵',
                                6 => '۶',
                                7 => '۷',
                                8 => '۸',
                                9 => '۹',
                                10 => '۱۰',
                                11 => '۱۱',
                                12 => '۱۲',
                                13 => '۱۳',
                                14 => '۱۴',
                                15 => '۱۵',
                                16 => '۱۶',
                                17 => '۱۷',
                                18 => '۱۸',
                                19 => '۱۹',
                                20 => '۲۰',
                            ];
                            $message = '🔸 شما از بازی خارج شدید .' . "\n \n" . 'منوی اصلی 👇';
                            $user->setKeyboard( KEY_START_MENU )->SendMessageHtml()->setStatus( '' );
                            $message = $user->get_league()->emoji . ' ' . '<u>' . $user->user()->name . '</u>' . ' از بازی خارج شد.';


                            $mess = '🎲 درحال جستجوی بازیکن برای شروع ...' . "\n";
                            $mess .= '🔸 نوع بازی :  ' . $server_league->icon . ' ، ' . tr_num( $server_league->count, 'fa' ) . ' نفره' . "\n \n";
                            $mess .= '👥 لیست افراد در صف انتظار' . "\n";

                            /*$message      = '📍 شما به بازی نوع ' . "<u>" . $server_league->icon . '،' . $server_league->count . ' نفره' . "</u>" . ' پیوستید .' . "\n";
                            $message      .= '🔰 در حال جستجوی بازیکن آنلاین ... لطفا منتظر بمانید .' . "\n \n";
                            $message      .= 'اعضای بازی : ' . "\n";*/


                            $users_server = $server->users();
                            $keyboard = [];
                            $i = 0;
                            $i2 = 0;
                            $id=1;

                            foreach ( $users_server as $item )
                            {
                                if ( ! $user->is( $item ) && $item->is_ban() && $item->is_user_in_game() ){

                                    $user_game = new User( $item->getUserId() );
                                    $prefix    = '';
//
//                                if ( is_server_meta( $server_id, 'friend', $user_game->getUserId() ) )
//                                {
//
//                                    $prefix = get_emoji_for_friendly( get_server_meta( $server_id, 'friend', $user_game->getUserId() ) );
//
//                                }
//
//                        $message .= $i . '- ' . $prefix . ' ' . $user_game->get_league()->emoji . ' ' .$user_game->user()->name . "\n";
                                    $keyboard[$i][$i2] = $telegram->buildInlineKeyboardButton($number_emojis[$id] . '-'. $user_game->get_league()->emoji . ' ' . $user_game->user()->name , '', '/');
                                    $id=$id+1;
                                    $i2++;
                                    if ($i2 % 2 === 0) {
                                        $i++;
                                        $i2=0;
                                    }

                                }

                            }


                            while ($id<=get_league( $server->league_id )->count){
//                        $message .= '-'.$i;
                                $keyboard[$i][$i2] = $telegram->buildInlineKeyboardButton(''.$number_emojis[$id], '', '/');
                                $id=$id+1;
                                $i2++;
                                if ($i2 % 2 === 0) {
                                    $i++;
                                    $i2=0;
                                }
                            }
                            $keyboard2=$keyboard;
                            foreach ($keyboard as $key => $values) {
                                if (count($keyboard)%2===0 || (count($keyboard)%2!==0) && $key!==(count($keyboard)-1)){
                                    $temp = $keyboard[$key][0];
                                    $keyboard[$key][0] = $keyboard[$key][1];
                                    $keyboard[$key][1] = $temp;
                                }

                            }

                            foreach ( $server->users() as $user_game )
                            {






                                if ( ! $user->is( $user_game ) && $user_game->is_ban() && $user_game->is_user_in_game() ){

//                                    $user_game->setKeyboard($telegram->buildInlineKeyBoard( $keyboard ))->SendMessageHtml($mess.'****');
//                                                                    $newKeyboard = [
//                                    [
//                                        ['text' => 'دکمه 1', 'callback_data' => 'button1'],
//                                        ['text' => 'دکمه 2', 'callback_data' => 'button2']
//                                    ],
//                                    [
//                                        ['text' => 'تست 🔙', 'callback_data' => 'back']
//                                    ]
//                                ];
                                    EditMessageText( $user_game->getUserId(),get_game($user_game->getUserId())->first_chat_id,$mess , $telegram->buildInlineKeyBoard( $keyboard ), null, 'html' );
                                    if ( is_server_meta( $server->getId(), 'friend', $user_game->getUserId() ) ) {
                                           $user_game->SendMessageHtml( $message );
                                    }

                                }

                            }


                        }

                    }
                    else
                    {

                        $message = '❌ در خروج از بازی مشکلی پیش آمد!';
                        SendMessage( $chat_id, $message );

                    }

                }
                else
                {

                    throw new ExceptionWarning( 'نمیتوانید از بازی خارج شوید.' );

                }

            }
            else
            {

                $message = '🔸 شما از بازی خارج شدید .' . "\n \n" . 'منوی اصلی 👇';
                $user->setKeyboard( KEY_START_MENU )->SendMessageHtml( $message )->setStatus( '' )->logout();
//                throw new ExceptionWarning('در شاناسایی سرور شما خطایی رخ داد.');

            }

        }
        else
        {

            do_action( 'start' );

        }

        break;

    case 'شروع با همین تعداد':

        if ( is_user_row_in_game( $chat_id ) )
        {

            $server = is_user_in_which_server( $chat_id );
            if ( isset( $server->status ) && $server->status == 'opened' && $server->user_id == $chat_id )
            {

                if ( $server->count >= 2 )
                {

                    $users_server = get_users_by_server( $server->id );
                    $new_server   = find_server( $server->league_id );

                    if ( isset( $new_server ) )
                    {

                        $league_new_server = get_league( $new_server->league_id );

                        if ( $new_server->count + $server->count <= $league_new_server->count )
                        {

                            $message                = '';
                            $emoji_number_by_server = (int) get_server_meta( $new_server->id, 'emoji-number' );
                            foreach ( $users_server as $id => $item )
                            {

                                $user_game   = new User( $item->user_id, $new_server->id );
                                $message     .= "<b>" . ( $new_server->count + ( $id + 1 ) ) . ".</b>" . "<u><b>" . $user_game->get_league()->emoji . $user_game->user()->name . "</b></u>" . ' به این بازی پیوست.' . "\n";
                                $new_users[] = $item->user_id;

                                switch ( $server->league_id )
                                {

                                    case 1:

                                        if ( $server->count > 3 )
                                        {

                                            add_server_meta( $new_server->id, 'get-point', 'friend', $item->user_id );

                                        }

                                        break;

                                    case 2:
                                    default:

                                        if ( $server->count > 5 )
                                        {

                                            add_server_meta( $new_server->id, 'get-point', 'friend', $item->user_id );

                                        }

                                        break;
                                }

                                add_server_meta( $new_server->id, 'friend', $emoji_number_by_server, $item->user_id );
                                logout_server( $item->user_id );
                                add_player_to_server( $item->user_id, 0, 0, $new_server->id, false );

                            }

                            add_emoji_for_friendly( $new_server->id );

                            $users_new_server = get_users_by_server( $new_server->id );

                            foreach ( $users_new_server as $id => $item )
                            {

                                $user_game   = new User( $item->user_id, $new_server->id );
                                $new_message .= ( $id + 1 ) . '- ' . $user_game->get_league()->emoji . $user_game->user()->name . "\n";
                                if ( !in_array( $item->user_id, $new_users ) )
                                {

                                    SendMessage( $item->user_id, $message, KEY_GAME_ON_MENU, null, 'html' );

                                }

                            }

                            $message = '🎲 درحال جستجوی بازیکن برای شروع ...' . "\n";
                            $message .= '🔸 نوع بازی :  ' . $league_new_server->icon . ' ، ' . tr_num( $league_new_server->count, 'fa' ) . ' نفره' . "\n \n";
                            $message .= '👥 لیست افراد در صف انتظار' . "\n" . $new_message;

                            update_server( $server->id, [
                                'status' => 'closed'
                            ] );

                        }
                        else
                        {

                            $message                = "<u>♨️ بازی شروع شد .</u>" . ' در حال جستجوی کاربران آنلاین ...';
                            $emoji_number_by_server = (int) get_server_meta( $server->id, 'emoji-number' );
                            add_emoji_for_friendly( $server->id );

                            foreach ( $users_server as $item )
                            {

                                add_server_meta( $server->id, 'friend', $emoji_number_by_server, $item->user_id );
                                switch ( $server->league_id )
                                {

                                    case 1:

                                        if ( $server->count > 3 )
                                        {

                                            add_server_meta( $server->id, 'get-point', 'friend', $item->user_id );

                                        }

                                        break;
                                    case 2:
                                    default:

                                        if ( $server->count > 5 )
                                        {

                                            add_server_meta( $server->id, 'get-point', 'friend', $item->user_id );

                                        }

                                        break;
                                }

                            }

                            update_server( $server->id, [

                                // 'user_id' => null,
                                'type'    => 'public'

                            ] );

                        }

                    }
                    else
                    {

                        $message                = "<u>♨️ بازی شروع شد .</u>" . ' در حال جستجوی کاربران آنلاین ...';
                        $emoji_number_by_server = (int) get_server_meta( $server->id, 'emoji-number' );
                        add_emoji_for_friendly( $server->id );

                        foreach ( $users_server as $item )
                        {

                            add_server_meta( $server->id, 'friend', $emoji_number_by_server, $item->user_id );
                            switch ( $server->league_id )
                            {
                                case 1:

                                    if ( $server->count > 3 )
                                    {

                                        add_server_meta( $server->id, 'get-point', 'friend', $item->user_id );

                                    }

                                    break;
                                case 2:
                                default:

                                    if ( $server->count > 5 )
                                    {

                                        add_server_meta( $server->id, 'get-point', 'friend', $item->user_id );

                                    }

                                    break;
                            }

                        }
                        update_server( $server->id, [
                            // 'user_id' => null,
                            'type'    => 'public'
                        ] );

                    }

                    if ( ! empty( $message ) && is_string( $message ) )
                    {

                        foreach ( $users_server as $item )
                        {

                            SendMessage( $item->user_id, $message, KEY_GAME_ON_MENU, null, 'html' );

                        }

                    }

                }
                else
                {

                    $message = '⚠️ خطا ! برای شروع بازی دوستانه لازم است حداقل یک نفر را دعوت کنید .';
                    Message();

                }

            }
            else
            {

                $message = '⚠️ خطا ! شما قابلیت شروع بازی را ندارید.';
                Message();

            }

        }
        else
        {

            do_action( 'start' );

        }


        break;

    case '/report':
    case '📵 گزارش تخلف':
    case '🚫 گزارش تخلف':
        if ( is_user_row_in_game( $chat_id ) )
        {
            $server = is_user_in_which_server( $chat_id );
            if ( isset( $server ) )
            {
                $message      = '⚠️ فرد متخلف رو انتخاب کن.' . "\n \n";
                $message      .= '❗️ اگه گزارشت تایید بشه 🌟3 امتیاز میگیری و اگه الکی گزارش کنی خودت مسدود میشی 😉' . "\n \n";
                $server_id    = get_game()->server_id;
                $users_server = get_users_by_server( $server_id );
                $keyboard     = [];
                foreach ( $users_server as $item )
                {
                    if ( $item->user_id != $chat_id )
                    {
                        $text       = 'گزارش کردن 👈🏻 ' . $item->name;
                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton( $text, '', 'report-' . $item->user_id )
                        ];
                    }
                }
                $keyboard[][] = $telegram->buildInlineKeyboardButton( '⛔️ انصراف', '', 'cancel_2' );
                SendMessage( $chat_id, $message, $telegram->buildInlineKeyBoard( $keyboard ) );
            }
            else
            {
                $message = 'بازی هنوز شروع نشده است.';
                Message();
            }
        }
        break;

    case '💭 پیام خصوصی':
    case '📨 پیام خصوصی':

        if ( is_user_row_in_game( $chat_id ) )
        {
            $message   = '💭 با استفاده از این گزینه میتونی به هر کسی که بخوای بدون اینکه بقیه متوجه بشن پیام خصوصی بفرستی.' . "\n \n";
            $message   .= '💰 هزینه هر پیام خصوصی <b>5 سکه</b> هست .' . "\n";
            $message   .= '❓ مخاطب خودت رو انتخاب کن :';
            $server_id = get_game()->server_id;
            $server    = new Server( $server_id );

            $users_server = get_users_by_server( $server_id );
            $keyboard     = [];
            foreach ( $users_server as $item )
            {
                if ( $item->user_id != $chat_id )
                {
                    $text       = '📨 ' . $item->name;
                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton( $text, '', 'get_send_message-' . $item->user_id )
                    ];
                }
            }
            $keyboard[][] = $telegram->buildInlineKeyboardButton( '⛔️ انصراف', '', 'cancel_2' );
            SendMessage( $chat_id, $message, $telegram->buildInlineKeyBoard( $keyboard ), null, 'html' );
        }

        break;

    case '➕ درخواست':

        if ( $user->user_on_game() )
        {

            $message = '➕ با استفاده از این گزینه میتونی به هر کسی که بخوای بدون اینکه بقیه متوجه بشن درخواست دوستی بفرستی.' . "\n";
            $message .= ' ❗️در هر بازی تنها به دونفر میتوانید درخواست ارسال کنید.' . "\n \n";
            $message .= '🔅 مخاطب خودت رو انتخاب کن :';

            if ( $user->countFriendRequest() > 5 )
            {
                $message .= "\n \n" . '<b><u>⚠️ توجه شما 5 درخواست دوستی داشتید، برای ارسال درخواست دوستی جدید باید 50 سکه پرداخت کنید.</u></b>';
            }

            $server = $user->server();

            $keyboard = [];
            foreach ( $server->users() as $item )
            {
                if ( ! $item->is( $user ) )
                {
                    $text       = '➕ ' . ( $item->get_name() ?? $item->user()->name );
                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton( $text, '', 'request_add_friend-' . $item->getUserId() )
                    ];
                }
            }
            $keyboard[][] = $telegram->buildInlineKeyboardButton( '⛔️ انصراف', '', 'cancel' );
            $user->setKeyboard( $telegram->buildInlineKeyBoard( $keyboard ) )->SendMessageHtml( $message );

        }

        break;

    case '/profile':
    case '🔖 پروفایل':
    case '👤 پروفایل':


        $User = new User( $chat_id );

        $game_count  = $User->getCountGame();
        $opration    = $User->getResultWinGame();
        $role        = get_user_meta( $chat_id, 'role' );
        $point       = get_point( $chat_id );
        $user_league = get__league_user( $chat_id );

        if ( get_user_meta( $chat_id, 'dice-date' ) != date( 'Y-m-d' ) )
        {

            update_user_meta( $chat_id, 'dice-count', 0 );
            update_user_meta( $chat_id, 'dice-date', date( 'Y-m-d' ) );

        }

        $dice_user = (int) get_user_meta( $chat_id, 'dice-count' );

        $dart = $User->get_meta( 'dart' );
        $today      = date( 'Y-m-d' );
        $today_star = (int) $link->get_var( "SELECT count(`selected`) FROM `bestplayer_daily` WHERE `created_at` = '{$today}' and `selected` = '{$chat_id}'" );
        $total_start = $User->get_meta( 'total_start' );
        $message = '💢 پروفایل بازیکن ' . "\n \n";
        $message .= '➖ نام شما : ' . $User->user()->name . "\n";
        $message .= '➖ شناسه شما : ' . '`' . $chat_id . '`' . "\n";
        $message .= '➖ امتیاز : ' . $point . "\n";
        $message .= '➖ لیگ شما : ' . $user_league->icon . "\n";
        $message .= '➖ رتبه در بازی : ' . ( $point > 0 ? get_rank_user_in_global( $chat_id ) : 'ندارید' ) . "\n";
        $message .= '➖ تعداد موجودی سکه: ' . $User->get_coin() . "\n";
        $message .= '➖ ستاره: ' . $today_star .' / '. $total_start . "\n";
        $message .= '➖ تعداد کل بازی‌ها : ' . (int) get_user_meta( $chat_id, 'game-count' ) . "\n";
        $message .= '➖ درصد برد: ' . ( $game_count > 0 ? ceil( $opration ) : 0 ) . '%' . "\n";
        $message .= '➖ شانس دارت : ' . $dice_user . ' از 5' . "\n";
        $message .= '➖ نقش مورد علاقه : ' . ( isset( $role ) ? get_role( $role )->icon : 'انتخاب نشده است' ) . "\n";
        $message .= '➖ جنسیت : ' . $User->gender() . "\n";
        $message .= '➖ سناریو : ' . $User->getPriority() . "\n";
        $message .= '➖ حریم خصوصی : ' . ( $User->get_meta( 'privacy' ) == 'unlook' ? 'باز 🔓' : 'قفل 🔒' ) . "\n";
        $message .= '➖ اشتراک : ' . ( $User->haveSubscribe() ? 'فعال است' : 'فعال نیست' ) . "\n";
        $message .= '➖ بازی شانسی : ' . ( $dart == 'dart' || empty( $dart ) ? '🎯 دارت' : ( $dart == 'boling' ? '🎳 بولینگ' : ( $dart == 'tas' ? '🎲 تاس' : ( $dart == 'car' ? '🎰' : ( $dart == 'penalti' ? '⚽ پنالتی' : ( $dart == 'bascetbal' ? '🏀 بسکتبال' : '' ) ) ) ) ) ) . "\n";
        $message .= '➖ ساخته شده در : ' . jdate( 'Y/m/d ➖ H:i' , ((int) strtotime($User->user()->created_at))) . "\n";
        $message .= " \n".' بروزرسانی در : ' . jdate( 'Y/m/d ➖ H:i' );

        add_filter( 'send_massage_text', function ( $text ) {
            return tr_num( $text, 'en', '.' );
        }, 11 );
        // $profile_photo =  new CURLFile(realpath(BASE_DIR . '/images/profile.jpg'));
        // SendPhoto(
        //     $chat_id,
        //     $profile_photo,
        //     $message,
        //     $telegram->buildInlineKeyBoard( [
        //         [
        //             $telegram->buildInlineKeyboardButton( ( $user_league->emoji . ' تغییر لیگ' ), '', 'change_league' ),
        //             $telegram->buildInlineKeyboardButton( '✏️ تغییر نام', '', 'change_name' ),
        //         ],
        //         [
        //             $telegram->buildInlineKeyboardButton( '⚙️ تنظیمات بیشتر', '', 'more_profile' )
        //         ],

        //     ]),
        //     null,'MarkDown',

        // );
        SendMessage(

            $chat_id, $message, $telegram->buildInlineKeyBoard( [
            [
                $telegram->buildInlineKeyboardButton( ( $user_league->emoji . ' تغییر لیگ' ), '', 'change_league' ),
                $telegram->buildInlineKeyboardButton( '✏️ تغییر نام', '', 'change_name' ),
            ],
            [
                $telegram->buildInlineKeyboardButton( '⚙️ تنظیمات بیشتر', '', 'more_profile' )
            ],

        ] )
        );

        break;

    case '/coin':
    case '/shop':
    case '💰 سکه':
    case '💰 فروشگاه':

        $message = '💰 موجودی سکه شما : [[coin]]

با استفاده از سکه میتونید :

۱- ⛱ برای خودتون لیگ اختصاصی با ایموجی دلخواه انتخاب کنید.

۲- 🪄 توی بازی از جادوهای مختلف استفاده کنید تا برنده بشید .

۳- ♨️ نقش دلخواه خودتون رو در هر بازی انتخاب کنید .

۴- 📨  توی بازی پیام خصوصی بدون محدودیت کلمات انگلیسی بفرستید .

۵ - 🎁 به دوستاتون سکه هدیه بدین که توضحیش رو میتونید اینجا /cointransfer ببینید . 

۶- 🌟 عضو کاربرای vip ربات بشین و از خدمات پشتیبانی سریعتر استفاده کنید .

انتخاب کنید از کدام بسته خرید میکنید : 👇';
        $message = str_replace( '[[coin]]', user()->coin, $message );
        SendMessage( $chat_id, $message, KEY_SHOP_MENU );
        break;

    case '🔗 دوستان':
    case '🗂 دوستان':
    case '📜 دوستان':
    case '/friend':
    case '/friends':

        $keyboard = [];

        $message      = '🗂 لیست دوستان شما در زیر نمایش داده شده است:' . "\n \n";
        $message      .= '📌 شما حداکثر میتوانید 40 نفر را به عنوان دوستانه اضافه کنید.' . "\n";
        $count_friend = $user->countFriend();
        if ( $user->countFriend() > 0 )
        {
            $message .= '🏷 شما در حال حاضر ' . $count_friend . ' نفر در لیست دوستان خود دارید.' . "\n \n";
        }
        else
        {
            $message .= '🏷 در حال حاضر لیست دوستان شما خالی است.' . "\n \n";
        }
        $message .= '📝 راهنما وضعیت:' . "\n";
        $message .= '➖ <b>آفلاین</b> 🔴  ( داخل هیچ بازی نیست)' . "\n";
        $message .= '➖ <b>آنلاین درحال بازی</b> 🟢 ( درحال بازی )' . "\n";
        $message .= '➖ <b>آنلاین منتظر</b> 🟣 ( توی لیست انتظار در حال پر شدن بازی )' . "\n";
        $message .= '➖ <b>آنلاین خارج از بازی</b> 🟡 ( آنلاین هست اما منتظر شروع بازی نیست )' . "\n";
        $message .= '➖ <b>وضعیت خاموش</b>  ⚫️ ( حریم شخصی فعاله و امکان چک کردن وضعیت وجود نداره)' . "\n \n";
        $message .= '====== انتخاب کنید با کدام دوستتان کار دارید ======';

        $keyboard[][] = $telegram->buildInlineKeyboardButton( ( $user->get_meta( 'status' ) == 'hide' ? 'وضعیت شما در حالت خاموش قرار دارد ⚫️' : 'وضعیت شما برای دوستانتان نمایش داده میشود ✅' ), '', 'change_status_friend' );
        $keyboard[][] = $telegram->buildInlineKeyboardButton( ( $user->get_meta( 'profile' ) == 'hide' ? 'وضعیت پروفایل شما در حالت خاموش قرار دارد ⚫️' : 'وضعیت پروفایل شما برای دوستانتان نمایش داده میشود ✅' ), '', 'change_status_friend_profile' );
        foreach ( $user->friends() as $friend )
        {
            $keyboard[][] = $telegram->buildInlineKeyboardButton( $friend->toStringFriend(), '', 'manage_friends-' . $friend->getUserId() );
        }
        $user->setKeyboard( $telegram->buildInlineKeyBoard( $keyboard ) )->SendMessageHtml();

        break;

    case '/close':

        $message = '✅ پنل ربات با موفقیت بسته شد.';
        $user->setKeyboard( $telegram->buildKeyBoardHide() )->SendMessageHtml( $message );


        break;

    case '/open':

        $message = '✅ پنل ربات با موفقیت باز شد.';
        $user->setKeyboard( $user->user_on_game() ? KEY_GAME_ON_MENU : KEY_START_MENU )->SendMessageHtml( $message );


        break;

    case '🌐 سرور':
    case '🔅 سرور':
    case '/stats':
    case '🪩 سرور':
    case '/status':

        $message = '🌐 سرور' . "\n \n";
        $message .= 'رنگ جلوی هر ربات میزان خلوت یا شلوغ بودن آن است .' . "\n \n";
        $message .= '🟢 خلوت ' . "\n";
        $message .= '🟡 متوسط' . "\n";
        $message .= '🟠 شلوغ ' . "\n";
        $message .= '🔴 غیرقابل استفاده' . "\n \n";

        foreach ( $token_bot as $index => $token )
        {
            $bot     = bot( 'GetMe', [], $token );
            $message .= 'وضعیت: ' . '@' . $bot->username . ' : ' . get_status_servers_bots( get_count_members_bots( $index ) ) . "\n";
        }

        $message .= "\n" . '💡 به جهت دریافت کیفیت و سرعت بهتر از ربات های خلوت استفاده کنید تا به مشکل نخورید .';

        add_filter( 'send_massage_text', function ( $text ) {
            return tr_num( $text, 'en', '.' );
        }, 11 );
        html();

        break;

    case '/rankings':
    case '🌟 امتیازات':
        // Helper functions to retrieve data from DB
        function get_top_rank_points_and_league( int $limit = 10 ) : array {
            global $link;

            $query = "
        SELECT 
            u.user_id,
            u.name AS user_name,
            u.point,
            l.id AS league_id,
            l.icon AS league_icon,
            l.name AS league_name
        FROM users u
        LEFT JOIN league l ON l.point = (
            SELECT MAX(l2.point)
            FROM league l2
            WHERE l2.point <= u.point
        )
        ORDER BY u.point DESC
        LIMIT {$limit};
    ";

            $users = $link->get_result($query);
            return $users;
        }

        // Prepare the message header
        $message = '📊 لیست برترین های ایرانی مافیا ' . "\n \n";

        // Fetch the top 10 ranked users and their leagues using the optimized query
        $list_users = get_top_rank_points_and_league(10);

        // Group users by their leagues
        $leagues = [];
        foreach ($list_users as $user) {
            $leagues[$user->league_id][] = $user; // Group users by their league
        }

        $x = 1;
        $user_list = [];

        // Process each league and its users
        foreach ($leagues as $league_id => $users) {
            $league_icon = $users[0]->league_icon;  // Get league icon from the first user in the group (includes league name)

            // Add the league icon and name to the message
            $message .= $league_icon . ' 👇' . "\n";

            foreach ($users as $user) {
                if (!empty($user->user_name)) {

                    // Assign ranking emoji based on position
                    switch ($x) {
                        case 1: $emoji_rank = '🥇'; break;
                        case 2: $emoji_rank = '🥈'; break;
                        case 3: $emoji_rank = '🥉'; break;
                        default: $emoji_rank = ''; break;
                    }

                    // Format the ranking message for each user
                    $message .= ($chat_id == $user->user_id ? '👈 ' : '[[' . $x . ']]  '). "<b>" .'‏'. $user->user_name .'‏'. "</b>" .
                        ($chat_id == $user->user_id ? ' (شما)' : ' ') . '      - [[point]] 🌟' . $emoji_rank . "\n";

                    __replace__($message, [
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
                        '[[point]]' => "<b>" . tr_num($user->point, 'fa', '.') . "</b>",
                    ]);

                    // Check if the current user is in the top 10
                    if ($user->user_id == $chat_id) {
                        $rank = $x;
                    }

                    $x++;
                    $user_list[] = $user->user_id;
                }

                // Limit to top 10 users
                if ($x > 10) {
                    break 2;
                }
            }
            $message .= "\n";
        }

        // Display user's own rank and points
        $rank = get_rank_user_in_global($chat_id); // Get the user's rank
        $result = $rank > 5 ? $rank : (new NumberToWord())->numberToWords($rank); // Convert rank to words if needed
        $user_points = get_point($chat_id); // Get the user's points

        // Now, add the missing lines for the user's rank, points, and extra info (with actual values)
        $message .= "\n" . '🔹رتبه شما : <b>' . tr_num($result, 'fa', '.') . '</b>';
        $message .= "\n" . '🔸امتیاز شما : <b>' . tr_num($user_points, 'fa', '.') . '</b>' . "\n \n";
        $message .= '❗️نحوه امتیاز گرفتن : /help_score' . "\n";
        $message .= '<a href="https://t.me/iranimafia/89">❗️تمامی لیگ های بازی</a>' . "\n \n";
        $message .= '@iranimafia';

        $emoji = '';
        add_filter('filter_league_user', function ($query) use (&$emoji) {
            $emoji = $query->emoji;
        }, 1);

//    $user_league = get__league_user($chat_id);

        // Send the formatted message to the user
        $telegram->sendMessage([
            'chat_id'                  => $chat_id,
            'text'                     => $message,
            'parse_mode'               => 'html',
            'reply_markup'             => $telegram->buildInlineKeyBoard([
                [$telegram->buildInlineKeyboardButton('📊 برترین های بازی ' . '✔️', '', 'rank_top_all')],
                [
                    $telegram->buildInlineKeyboardButton('📆 هفتگی', '', 'rank_top_week'),
                    $telegram->buildInlineKeyboardButton('📅 روزانه', '', 'rank_top_today'),
                    $telegram->buildInlineKeyboardButton(( $emoji . ' لیگ من' ), '', 'rank_top_my_league')
                ]
            ]),
            'disable_web_page_preview' => true,
        ]);

        break;





    case '🧩 بازی شانسی':

        $server = is_user_in_which_server( $chat_id );
        if ( isset( $server->id ) )
        {

            $server = new Server( $server->id );
            if ( $server->getStatus() == 'chatting' )
            {

                if ( dead( $server->getId(), $chat_id ) && ( get_role_by_user( $server->getId(), ROLE_Joker ) != $chat_id || ! is_server_meta( $server->getId(), 'joker' ) ) )
                {

                    if ( get_user_meta( $chat_id, 'dice-date' ) != date( 'Y-m-d' ) )
                    {
                        update_user_meta( $chat_id, 'dice-count', 0 );
                        update_user_meta( $chat_id, 'dice-date', date( 'Y-m-d' ) );
                    }

                    $dice_user = (int) get_user_meta( $chat_id, 'dice-count' );
                    if ( $dice_user < 5 )
                    {

                        $dice_user_time = (int) get_user_meta( $chat_id, 'dice-time' );
                        if ( $dice_user_time <= time() )
                        {

                            $users_server = $server->users();

                            $dart = $user->get_meta( 'dart' );

                            switch ( $dart )
                            {

                                case 'boling':
                                case 'tas':
                                case 'dart':
                                default:

                                    $result      = bot( 'sendDice', [
                                        'chat_id' => $chat_id,
                                        'emoji'   => $dart == 'dart' || empty( $dart ) ? '🎯' : ( $dart == 'boling' ? '🎳' : ( $dart == 'tas' ? '🎲' : '' ) )
                                    ] );
                                    $point       = $result->dice->value;
                                    $user_league = get__league_user( $chat_id );
                                    if ( isset( $point ) )
                                    {

                                        $point --;

                                        switch ( $server->league_id )
                                        {

                                            case 1:

                                                if ( $point == 5 )
                                                {

                                                    $point   = 4;
                                                    $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'dart' || empty( $dart ) ? '🎯' : ( $dart == 'boling' ? '🎳' : ( $dart == 'tas' ? '🎲' : '' ) ) ) . ' +[[point]] سکه💰 جایزه🎉';
                                                    $user->add_coin( $point );

                                                }
                                                elseif ( $point != 0 )
                                                {

                                                    $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'dart' || empty( $dart ) ? '🎯' : ( $dart == 'boling' ? '🎳' : ( $dart == 'tas' ? '🎲' : '' ) ) ) . ' +[[point]] امتیاز🌟 جایزه🎉';
                                                    $user->add_point( $point );

                                                }
                                                else
                                                {

                                                    $message = 'به هدف نخورد ☹️' . "\n" . 'فدای سرت ، دفعه بعد ...';

                                                }


                                                break;

                                            default:
                                            case 2:

                                                if ( $point == 5 )
                                                {

                                                    $point   = 7;
                                                    $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'dart' || empty( $dart ) ? '🎯' : ( $dart == 'boling' ? '🎳' : ( $dart == 'tas' ? '🎲' : '' ) ) ) . ' +[[point]] سکه💰 جایزه🎉';
                                                    $user->add_coin( $point );

                                                }
                                                elseif ( $point != 0 )
                                                {

                                                    $point   *= 2;
                                                    $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'dart' || empty( $dart ) ? '🎯' : ( $dart == 'boling' ? '🎳' : ( $dart == 'tas' ? '🎲' : '' ) ) ) . ' +[[point]] امتیاز🌟 جایزه🎉';
                                                    $user->add_point( $point );

                                                }
                                                else
                                                {

                                                    $message = 'به هدف نخورد ☹️' . "\n" . 'فدای سرت ، دفعه بعد ...';

                                                }

                                                break;

                                            case 4:

                                                if ( $point == 5 )
                                                {

                                                    $point   = 8;
                                                    $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'dart' || empty( $dart ) ? '🎯' : ( $dart == 'boling' ? '🎳' : ( $dart == 'tas' ? '🎲' : '' ) ) ) . ' +[[point]] سکه💰 جایزه🎉';
                                                    $user->add_coin( $point );

                                                }
                                                elseif ( $point != 0 )
                                                {

                                                    $point   *= 2;
                                                    $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'dart' || empty( $dart ) ? '🎯' : ( $dart == 'boling' ? '🎳' : ( $dart == 'tas' ? '🎲' : '' ) ) ) . ' +[[point]] امتیاز🌟 جایزه🎉';
                                                    $user->add_point( $point );

                                                }
                                                else
                                                {

                                                    $message = 'به هدف نخورد ☹️' . "\n" . 'فدای سرت ، دفعه بعد ...';

                                                }


                                                break;

                                        }

                                        __replace__( $message, [
                                            '[[user]]'  => user()->name,
                                            '[[point]]' => "<u>" . $point . "</u>"
                                        ] );

                                        if ( $point != 0 )
                                        {

                                            /* @var $item helper\Users */
                                            add_filter( 'send_massage_text', function ( $text ) {
                                                return tr_num( $text, 'en', '.' );
                                            }, 11 );

                                            foreach ( $users_server as $item )
                                            {

                                                if ( ! $item->is( $user ) && $item->is_user_in_game() )
                                                {

                                                    $item->SendMessageHtml( $message );

                                                }

                                            }

                                            update_user_meta( $chat_id, 'dice-count', ( $dice_user + 1 ) );

                                        }
                                        update_user_meta( $chat_id, 'dice-time', ( time() + 60 ) );
                                        $telegram->sendMessage( [
                                            'chat_id'    => $chat_id,
                                            'text'       => $message,
                                            'parse_mode' => 'html'
                                        ] );


                                    }
                                    else
                                    {

                                        $message = '⚠️ مشکلی پیش آمد! لطفا با پشتیبانی تماس بگیرید.';
                                        $telegram->sendMessage( [
                                            'chat_id' => $chat_id,
                                            'text'    => $message
                                        ] );

                                    }

                                    break;

                                case 'car':
                                case 'penalti':
                                case 'bascetbal':

                                    $result      = bot( 'sendDice', [
                                        'chat_id' => $chat_id,
                                        'emoji'   => $dart == 'car' ? '🎰' : ( $dart == 'penalti' ? '⚽' : ( $dart == 'bascetbal' ? '🏀' : '' ) )
                                    ] );
                                    $point       = $result->dice->value;
                                    $user_league = get__league_user( $chat_id );

                                    if ( isset( $point ) )
                                    {

                                        $point --;

                                        switch ( $server->league_id )
                                        {

                                            case 1:

                                                switch ( $dart )
                                                {

                                                    case 'bascetbal':

                                                        if ( $point > 3 )
                                                        {

                                                            if ( rand( 0, 1 ) == 1 )
                                                            {
                                                                $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'car' ? '🎰' : ( $dart == 'penalti' ? '⚽' : ( $dart == 'bascetbal' ? '🏀' : '' ) ) ) . ' +[[point]] سکه💰 جایزه🎉';
                                                                $user->add_coin( 3 );
                                                            }
                                                            else
                                                            {
                                                                $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'car' ? '🎰' : ( $dart == 'penalti' ? '⚽' : ( $dart == 'bascetbal' ? '🏀' : '' ) ) ) . ' +[[point]] امتیاز🌟 جایزه🎉';
                                                                $user->add_point( 3 );
                                                            }

                                                        }
                                                        else
                                                        {

                                                            $message = 'به هدف نخورد ☹️' . "\n" . 'فدای سرت ، دفعه بعد ...';

                                                        }

                                                        break;

                                                    case 'penalti':

                                                        if ( $point > 0 )
                                                        {

                                                            if ( rand( 0, 1 ) == 1 )
                                                            {
                                                                $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'car' ? '🎰' : ( $dart == 'penalti' ? '⚽' : ( $dart == 'bascetbal' ? '🏀' : '' ) ) ) . ' +[[point]] سکه💰 جایزه🎉';
                                                                $user->add_coin( 3 );
                                                            }
                                                            else
                                                            {
                                                                $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'car' ? '🎰' : ( $dart == 'penalti' ? '⚽' : ( $dart == 'bascetbal' ? '🏀' : '' ) ) ) . ' +[[point]] امتیاز🌟 جایزه🎉';
                                                                $user->add_point( 3 );
                                                            }

                                                        }
                                                        else
                                                        {

                                                            $message = 'به هدف نخورد ☹️' . "\n" . 'فدای سرت ، دفعه بعد ...';

                                                        }

                                                        break;

                                                    default:

                                                        if ( $point == 5 )
                                                        {

                                                            if ( rand( 0, 1 ) == 1 )
                                                            {
                                                                $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'car' ? '🎰' : ( $dart == 'penalti' ? '⚽' : ( $dart == 'bascetbal' ? '🏀' : '' ) ) ) . ' +[[point]] سکه💰 جایزه🎉';
                                                                $user->add_coin( 3 );
                                                            }
                                                            else
                                                            {
                                                                $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'car' ? '🎰' : ( $dart == 'penalti' ? '⚽' : ( $dart == 'bascetbal' ? '🏀' : '' ) ) ) . ' +[[point]] امتیاز🌟 جایزه🎉';
                                                                $user->add_point( 3 );
                                                            }

                                                        }
                                                        else
                                                        {

                                                            $message = 'به هدف نخورد ☹️' . "\n" . 'فدای سرت ، دفعه بعد ...';

                                                        }

                                                        break;

                                                }

                                                break;

                                            case 2:

                                                switch ( $dart )
                                                {

                                                    case 'bascetbal':

                                                        if ( $point > 3 )
                                                        {

                                                            if ( rand( 0, 1 ) == 1 )
                                                            {
                                                                $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'car' ? '🎰' : ( $dart == 'penalti' ? '⚽' : ( $dart == 'bascetbal' ? '🏀' : '' ) ) ) . ' +[[point]] سکه💰 جایزه🎉';
                                                                $user->add_coin( 5 );
                                                            }
                                                            else
                                                            {
                                                                $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'car' ? '🎰' : ( $dart == 'penalti' ? '⚽' : ( $dart == 'bascetbal' ? '🏀' : '' ) ) ) . ' +[[point]] امتیاز🌟 جایزه🎉';
                                                                $user->add_point( 5 );
                                                            }

                                                        }
                                                        else
                                                        {

                                                            $message = 'به هدف نخورد ☹️' . "\n" . 'فدای سرت ، دفعه بعد ...';

                                                        }

                                                        break;

                                                    case 'penalti':

                                                        if ( $point > 0 )
                                                        {

                                                            if ( rand( 0, 1 ) == 1 )
                                                            {
                                                                $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'car' ? '🎰' : ( $dart == 'penalti' ? '⚽' : ( $dart == 'bascetbal' ? '🏀' : '' ) ) ) . ' +[[point]] سکه💰 جایزه🎉';
                                                                $user->add_coin( 5 );
                                                            }
                                                            else
                                                            {
                                                                $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'car' ? '🎰' : ( $dart == 'penalti' ? '⚽' : ( $dart == 'bascetbal' ? '🏀' : '' ) ) ) . ' +[[point]] امتیاز🌟 جایزه🎉';
                                                                $user->add_point( 5 );
                                                            }

                                                        }
                                                        else
                                                        {

                                                            $message = 'به هدف نخورد ☹️' . "\n" . 'فدای سرت ، دفعه بعد ...';

                                                        }

                                                        break;

                                                    default:

                                                        if ( $point == 5 )
                                                        {

                                                            if ( rand( 0, 1 ) == 1 )
                                                            {
                                                                $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'car' ? '🎰' : ( $dart == 'penalti' ? '⚽' : ( $dart == 'bascetbal' ? '🏀' : '' ) ) ) . ' +[[point]] سکه💰 جایزه🎉';
                                                                $user->add_coin( 5 );
                                                            }
                                                            else
                                                            {
                                                                $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'car' ? '🎰' : ( $dart == 'penalti' ? '⚽' : ( $dart == 'bascetbal' ? '🏀' : '' ) ) ) . ' +[[point]] امتیاز🌟 جایزه🎉';
                                                                $user->add_point( 5 );
                                                            }

                                                        }
                                                        else
                                                        {

                                                            $message = 'به هدف نخورد ☹️' . "\n" . 'فدای سرت ، دفعه بعد ...';

                                                        }

                                                        break;

                                                }

                                                break;

                                            case 4:

                                                switch ( $dart )
                                                {

                                                    case 'bascetbal':

                                                        if ( $point > 3 )
                                                        {

                                                            if ( rand( 0, 1 ) == 1 )
                                                            {
                                                                $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'car' ? '🎰' : ( $dart == 'penalti' ? '⚽' : ( $dart == 'bascetbal' ? '🏀' : '' ) ) ) . ' +[[point]] سکه💰 جایزه🎉';
                                                                $user->add_coin( 6 );
                                                            }
                                                            else
                                                            {
                                                                $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'car' ? '🎰' : ( $dart == 'penalti' ? '⚽' : ( $dart == 'bascetbal' ? '🏀' : '' ) ) ) . ' +[[point]] امتیاز🌟 جایزه🎉';
                                                                $user->add_point( 6 );
                                                            }

                                                        }
                                                        else
                                                        {

                                                            $message = 'به هدف نخورد ☹️' . "\n" . 'فدای سرت ، دفعه بعد ...';

                                                        }

                                                        break;

                                                    case 'penalti':

                                                        if ( $point > 0 )
                                                        {

                                                            if ( rand( 0, 1 ) == 1 )
                                                            {
                                                                $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'car' ? '🎰' : ( $dart == 'penalti' ? '⚽' : ( $dart == 'bascetbal' ? '🏀' : '' ) ) ) . ' +[[point]] سکه💰 جایزه🎉';
                                                                $user->add_coin( 6 );
                                                            }
                                                            else
                                                            {
                                                                $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'car' ? '🎰' : ( $dart == 'penalti' ? '⚽' : ( $dart == 'bascetbal' ? '🏀' : '' ) ) ) . ' +[[point]] امتیاز🌟 جایزه🎉';
                                                                $user->add_point( 6 );
                                                            }

                                                        }
                                                        else
                                                        {

                                                            $message = 'به هدف نخورد ☹️' . "\n" . 'فدای سرت ، دفعه بعد ...';

                                                        }

                                                        break;

                                                    default:

                                                        if ( $point == 5 )
                                                        {

                                                            if ( rand( 0, 1 ) == 1 )
                                                            {
                                                                $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'car' ? '🎰' : ( $dart == 'penalti' ? '⚽' : ( $dart == 'bascetbal' ? '🏀' : '' ) ) ) . ' +[[point]] سکه💰 جایزه🎉';
                                                                $user->add_coin( 6 );
                                                            }
                                                            else
                                                            {
                                                                $message = $user_league->emoji . ' [[user]] ' . ( $dart == 'car' ? '🎰' : ( $dart == 'penalti' ? '⚽' : ( $dart == 'bascetbal' ? '🏀' : '' ) ) ) . ' +[[point]] امتیاز🌟 جایزه🎉';
                                                                $user->add_point( 6 );
                                                            }

                                                        }
                                                        else
                                                        {

                                                            $message = 'به هدف نخورد ☹️' . "\n" . 'فدای سرت ، دفعه بعد ...';

                                                        }

                                                        break;

                                                }

                                                break;

                                        }

                                        __replace__( $message, [
                                            '[[user]]'  => user()->name,
                                            '[[point]]' => "<u>" . ( $point > 0 ? 5 : 0 ) . "</u>"
                                        ] );

                                        if ( $point > 0 )
                                        {

                                            /* @var $item helper\Users */
                                            add_filter( 'send_massage_text', function ( $text ) {
                                                return tr_num( $text, 'en', '.' );
                                            }, 11 );

                                            foreach ( $users_server as $item )
                                            {

                                                if ( ! $item->is( $user ) && $item->is_user_in_game() )
                                                {

                                                    $item->SendMessageHtml( $message );

                                                }

                                            }

                                            update_user_meta( $chat_id, 'dice-count', ( $dice_user + 1 ) );

                                        }
                                        update_user_meta( $chat_id, 'dice-time', ( time() + 60 ) );

                                        $telegram->sendMessage( [
                                            'chat_id'    => $chat_id,
                                            'text'       => $message,
                                            'parse_mode' => 'html'
                                        ] );


                                    }
                                    else
                                    {

                                        $message = '⚠️ مشکلی پیش آمد! لطفا با پشتیبانی تماس بگیرید.';
                                        $telegram->sendMessage( [
                                            'chat_id' => $chat_id,
                                            'text'    => $message
                                        ] );

                                    }

                                    break;

                            }

                        }
                        else
                        {

                            $message = '⚠️خطا ! امکان پرتاب دارت برای شما فراهم نیست .' . "\n";
                            $message .= 'بعد از [[time]] امتحان کنید .';
                            __replace__( $message, [
                                '[[time]]' => time_to_string( $dice_user_time ) ?? 'Nan'
                            ] );
                            $telegram->sendMessage( [
                                'chat_id' => $chat_id,
                                'text'    => $message
                            ] );

                        }

                    }
                    else
                    {

                        $message = '⚠️ خطا ! شما از تمامی فرصت های روزانه دارت خود استفاده کرده اید.';
                        $telegram->sendMessage( [
                            'chat_id' => $chat_id,
                            'text'    => $message
                        ] );

                    }

                }
                else
                {

                    $message = '⚠️خطا ! امکان پرتاب دارت برای شما فراهم نیست .';

                    $telegram->sendMessage( [
                        'chat_id' => $chat_id,
                        'text'    => $message
                    ] );

                }

            }

        }

        break;

    case'📯 جادو ها':
    case '🪄 جادوها':
        if ( is_user_row_in_game( $chat_id ) )
        {
            $server = is_user_in_which_server( $chat_id );

            if ( isset( $server->status ) && $server->status == 'started' )
            {
                $message = '‼️نکات مهم :' . "\n";
                $message .= '♻️ در هر بازی از سه جادو و از هر جادو تنها یک بار میتوانید استفاده کنید.' . "\n";
                $message .= '🔅 اعداد مقابل هر جادو ، تعداد سکه مورد نیاز برای استفاده از آن است .' . "\n \n";
                $message .= '📯 جادوی مدنظر را انتخاب کنید :';
                SendMessage( $chat_id, $message, KEY_MAGIC_GAME );
            }
            else
            {
                $message = 'بازی هنوز شروع نشده است.';
                Message();
            }
        }
        break;

    case '💌 trدعوت':
    case '💯 trدعوت':
        $message = 'دوستات رو به ایرانی ‌مافیا دعوت کن ، هدیه بگیر 🤩' . "\n \n";
        $message .= '🎁 با دعوت از دوستانتان به بازی ایرانی مافیا هم شما و هم آن‌ ها هدیه می‌گیرید. ' . "\n \n";
        $message .= 'با استفاده از دکمه های زیر لینک دعوت اختصاصی خودتون و یا بنر اشتراک آنلاین برای دوستانتون بفرستید تا پس از دعوت و ورود آن ها به ربات و به پایان رساندن یک بازی موفق 10💰 سکه هدیه دریافت کنید .' . "\n \n";
        $message .= '🔸 شما تا کنون [[count]] نفر را به ایرانی مافیا دعوت کرده اید .';
        add_filter( 'send_massage_text', function ( $text ) {
            return tr_num( $text, 'en', '.' );
        }, 11 );
        SendMessage(
            $chat_id, __replace__( $message, [
            '[[user_id]]' => $chat_id,
            '[[count]]'   => count( $user->subUsers() )
        ] ), $telegram->buildInlineKeyBoard( [
            [
                $telegram->buildInlineKeyboardButton( '➕ دریافت لینک اختصاصی', '', 'get_link_sub_user' ),
                $telegram->buildInlineKeyboardButton( '📡 اشتراک گذاری آنلاین', '', '', $chat_id )
            ]
        ] )
        );
        break;

    case '/media':
    case '🎞 رسانه':
    case '🎬 رسانه':

        if ( $user->haveSubscribe() )
        {

            if ( $user->user_on_game() )
            {

                $message = 'لطفا نوع رسانه رو انتخاب کنید.';
                $user->setKeyboard(
                    $telegram->buildInlineKeyBoard( [
                        [
                            $telegram->buildInlineKeyboardButton( '🎙 ویس', '', 'media_group-voice' ),
                            $telegram->buildInlineKeyboardButton( '🌠 گیف', '', 'media_group-video' ),
                        ]
                    ] )
                )->SendMessageHtml( $message );

            }
            else
            {
                throw new ExceptionWarning( 'شما در هیچ سروری نمی باشید.' );
            }

        }
        else
        {
            throw new ExceptionWarning( 'شما هیچ اشتراکی ندارید.' );
        }

        break;

    case '/code':
        $message = '♨️ کوپن خود را وارد کنید.';
        SendMessage( $chat_id, $message, KEY_BACK_TO_START_MENU );
        update_status( 'get_coupon_code' );
        break;

    case '/server':
        $server_id = get_game()->server_id ?? - 1;
        $telegram->sendMessage( [
            'chat_id' => $chat_id,
            'text'    => $server_id
        ] );
        break;

    // جادو ها
    case '/magic1':

        if ( is_user_row_in_game( $chat_id ) )
        {

            $server = is_user_in_which_server( $chat_id );

            if ( $server->status == 'started' )
            {

                $user_role = get_role_user_server( $server->id, $chat_id );
                if ( $user_role->group_id == 1 )
                {

                    $user           = user();
                    $bazpors_select = get_server_meta( $server->id, 'select', ROLE_Bazpors );
                    $bazpors        = get_role_by_user( $server->id, ROLE_Bazpors );

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

                    }
                    elseif ( get_server_meta( $server->id, 'accused' ) == $chat_id )
                    {

                        if ( has_coin( $chat_id, 4 ) )
                        {

                            if ( add_magic( $server->id, $chat_id, 1 ) )
                            {

                                if ( demote_coin( $chat_id, 4 ) )
                                {

                                    $users_server = get_users_by_server( $server->id );
                                    $message      = '🪄 جادوی اعلام نقش ' . "\n";
                                    $message      .= '🟢 ' . "<u>" . $user->name . "</u>" . ' جزو گروه شهروند است .';
                                    foreach ( $users_server as $item )
                                    {
                                        if ( is_user_in_game( $server->id, $item->user_id ) )
                                        {
                                            SendMessage( $item->user_id, $message, null, null, 'html' );
                                        }
                                    }
                                    DeleteMessage( $chat_id, $message_id );

                                }
                                else
                                {

                                    $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                    Message();

                                }

                            }
                            else
                            {

                                $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';
                                Message();

                            }

                        }
                        else
                        {

                            $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';
                            Message();

                        }

                    }
                    else
                    {

                        $message = '⚠️ خطا ، الان نمیتوانید از این جادو استفاده کنید .';
                        Message();

                    }


                }
                elseif ( $user_role->id == ROLE_Shayad )
                {

                    if ( get_server_meta( $server->id, 'accused' ) == $chat_id )
                    {


                        if ( has_coin( $chat_id, 4 ) )
                        {

                            if ( add_magic( $server->id, $chat_id, 1 ) )
                            {

                                if ( demote_coin( $chat_id, 4 ) )
                                {

                                    $user         = user();
                                    $users_server = get_users_by_server( $server->id );
                                    $message      = '🪄 جادوی اعلام نقش ' . "\n";
                                    $message      .= '🟢 ' . "<u>" . $user->name . "</u>" . ' جزو گروه شهروند است .';
                                    foreach ( $users_server as $item )
                                    {
                                        if ( is_user_in_game( $server->id, $item->user_id ) )
                                        {
                                            SendMessage( $item->user_id, $message, null, null, 'html' );
                                        }
                                    }
                                    DeleteMessage( $chat_id, $message_id );

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

                        }
                        else
                        {

                            $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';
                            EditMessageText( $chatid, $messageid, $message );

                        }

                    }
                    else
                    {

                        $message = '⚠️ خطا ، الان نمیتوانید از این جادو استفاده کنید .';
                        EditMessageText( $chatid, $messageid, $message );

                    }

                }
                else
                {

                    $message = '⚠️ خطا ، شما نمیتوانید از این جادو استفاده کنید .';
                    Message();

                }

            }
            else
            {

                $message = 'بازی هنوز شروع نشده است.';
                Message();

            }

        }
        else
        {

            DeleteMessage( $chat_id, $message_id );

        }

        break;

    /*case '/magic2':

        if ( $user->user_on_game() )
        {

            $server = $user->server();

            if ( $server->status == 'started' )
            {

                if ( $user->has_coin( 3 ) )
                {

                    $keyboard  = [];
                    $message   = '♨️ انتخاب کنید میخواهید از نقش چه کسی مطلع شوید .';
                    $user_role = $user->get_role();
                    foreach ( $server->users() as $item )
                    {

                        if ( $item->check( $user ) )
                        {

                            if ( $user_role->group_id != 2 )
                            {
                                $keyboard[][] = $telegram->buildInlineKeyboardButton( $item->get_league()->emoji . $item->get_name(), '', 'magic2-' . $item->getUserId() );
                            }
                            elseif ( $item->get_role()->group_id != 2 )
                            {
                                $keyboard[][] = $telegram->buildInlineKeyboardButton( $item->get_league()->emoji . $item->get_name(), '', 'magic2-' . $item->getUserId() );
                            }

                        }

                    }
                    $keyboard[][] = $telegram->buildInlineKeyboardButton( '⛔️ انصراف', '', 'cancel' );
                    $user->setKeyboard( $telegram->buildInlineKeyBoard( $keyboard ) )->SendMessageHtml( $message );


                }
                else
                {

                    throw new ExceptionWarning( 'شما سکه کافی برای استفاده از این جادو را ندارید .' );

                }

            }
            else
            {
                throw new ExceptionWarning( 'بازی هنوز شروع نشده است.' );
            }


        }

        break;*/

    case '/magic3':

        if ( is_user_row_in_game( $chat_id ) )
        {

            $server = is_user_in_which_server( $chat_id );

            if ( $server->status == 'started' )
            {

                if ( has_coin( $chat_id, 6 ) )
                {

                    if ( add_magic( $server->id, $chat_id, 3 ) )
                    {

                        if ( demote_coin( $chat_id, 6 ) )
                        {

//                            $message = '🛡جادوی محفوظ فعال شد .' . "\n" . 'شما برای ' . "<u>یک شب</u>" . ' از خطر حملات در امان خواهید بود .';
                            $message = "📯<b><u>جادوی محفوظ</u></b>  ، فعال شد ✅";
                            html();
                            add_server_meta( $server->id, 'shield', 'on', $chat_id );

                        }
                        else
                        {

                            $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                            Message();

                        }

                    }
                    else
                    {

                        $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';
                        Message();

                    }

                }
                else
                {

                    $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                    Message();

                }

            }
            else
            {

                $message = 'بازی هنوز شروع نشده است.';
                Message();

            }

        }
        else
        {

            DeleteMessage( $chat_id, $message_id );

        }

        break;

    case '/magic4':

        if ( is_user_row_in_game( $chat_id ) )
        {

            $server = is_user_in_which_server( $chat_id );
            if ( $server->status == 'started' )
            {

                if ( has_coin( $chat_id, 5 ) )
                {

                    if ( add_magic( $server->id, $chat_id, 4 ) )
                    {

                        if ( demote_coin( $chat_id, 5 ) )
                        {

                            $message = "📯 <b><u>جادوی حذف رای</u></b>  ، فعال شد ✅";
//                            $message = '🤷🏻‍♂️ جادوی حذف رای فعال شد .' . "\n" . 'نام شما در رای‌گیری ' . "<u>بعدی</u>" . ' قرار نمیگیرد.';
                            html();
                            add_server_meta( $server->id, 'no-vote', 'on', $chat_id );

                        }
                        else
                        {

                            $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                            Message();

                        }

                    }
                    else
                    {

                        $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';
                        Message();

                    }

                }
                else
                {

                    $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                    Message();

                }

            }
            else
            {

                $message = 'بازی هنوز شروع نشده است.';
                Message();

            }

        }
        else
        {

            DeleteMessage( $chat_id, $message_id );

        }

        break;

    case '/magic5':

        if ( is_user_row_in_game( $chat_id ) )
        {

            $server = is_user_in_which_server( $chat_id );
            if ( $server->status == 'started' )
            {

                if ( get_server_meta( $server->id, 'is' ) != 'on' )
                {

                    if ( has_coin( $chat_id, 5 ) )
                    {

                        if ( add_magic( $server->id, $chat_id, 5 ) )
                        {

                            if ( demote_coin( $chat_id, 5 ) )
                            {

                                $message = "📯<b><u>جادوی جاسوس</u></b>  ، فعال شد ✅";
//                                $message = '🧏🏻‍♂️ جادوی جاسوس فعال شد .' . "\n" . 'شما از تمامی حملات به شما در آینده خبردار خواهید شد.';
                                html();
                                add_server_meta( $server->id, 'warning', 'on', $chat_id );
                                $server       = new Server( $server->id );
                                $filter_roles = [
                                    ROLE_Sniper,
                                    ROLE_Godfather,
                                    ROLE_Mashooghe,
                                    ROLE_HardFamia,
                                    ROLE_Tobchi,
                                    ROLE_Killer,
                                    ROLE_Gorg
                                ];

                                foreach ( $server->getListAttacker( $chat_id ) as $item )
                                {

                                    $role = $item->get_role();
                                    if ( ! $item->is( $chat_id ) && in_array( $role->id, $filter_roles ) )
                                    {

                                        switch ( $role->id )
                                        {

                                            case ROLE_Mashooghe:
                                            case ROLE_Godfather:
                                                $name_role = 'اعضای مافیا';
                                                break;
                                            default:
                                                $name_role = remove_emoji( $role->name );
                                                break;

                                        }

                                        $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>" . $name_role . "</u>" . ' قصد حمله به شما را دارد .';
                                        // $item->SendMessageHtml();

                                    }

                                }

                            }
                            else
                            {

                                $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                Message();

                            }

                        }
                        else
                        {

                            $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';
                            Message();

                        }

                    }
                    else
                    {

                        $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                        Message();

                    }

                }
                else
                {

                    AnswerCallbackQuery( $dataid, '⚠️ مجددا امتحان کنید', true );

                }

            }
            else
            {

                $message = 'بازی هنوز شروع نشده است.';
                Message();

            }

        }
        else
        {

            DeleteMessage( $chat_id, $message_id );

        }

        break;

    case '/magic6':

        if ( is_user_row_in_game( $chat_id ) )
        {

            $server = is_user_in_which_server( $chat_id );

            if ( $server->status == 'started' )
            {

                if ( is_user_hacked( $chat_id, $server->id ) )
                {

                    if ( has_coin( $chat_id, 4 ) )
                    {

                        if ( add_magic( $server->id, $chat_id, 6 ) )
                        {

                            if ( demote_coin( $chat_id, 4 ) )
                            {

                                delete_server_meta( $server->id, 'hack' );
                                $message = "📯<b><u>جادوی ضدهک</u></b>  ، فعال شد ✅";
//                                $message = '🪄 جادوی ضدهک فعال شد .' . "\n" . '🗣 اکنون میتوانید صحبت کنید و رای بدهید .';

                            }
                            else
                            {

                                $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';

                            }

                        }
                        else
                        {

                            $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';

                        }

                    }
                    else
                    {

                        $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';

                    }

                }
                else
                {

                    $message = '⚠️خطا ! شما نمیتوانید از این جادو استفاده کنید .';

                }

                Message();
            }
            else
            {

                $message = 'بازی هنوز شروع نشده است.';
                Message();

            }

        }
        else
        {

            DeleteMessage( $chat_id, $message_id );

        }

        break;

    case '/magic7':

        if ( is_user_row_in_game( $chat_id ) )
        {

            $server = is_user_in_which_server( $chat_id );

            if ( $server->status == 'started' )
            {


                $user = new User( $chat_id, $server->id );

                if ( $user->sleep() )
                {

                    if ( has_coin( $chat_id, 6 ) )
                    {

                        if ( add_magic( $server->id, $chat_id, 7 ) )
                        {

                            if ( demote_coin( $chat_id, 6 ) )
                            {

                                delete_server_meta( $server->id, 'sleep' );
                                $message = "📯<b><u>جادوی بیدار شدن</u></b>  ، فعال شد ✅";

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

                    }
                    else
                    {

                        $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                        EditMessageText( $chatid, $messageid, $message );

                    }

                }
                else
                {

                    $message = '⚠️خطا ! شما نمیتوانید از این جادو استفاده کنید .';

                }

                Message();
            }
            else
            {

                $message = 'بازی هنوز شروع نشده است.';
                Message();

            }

        }
        else
        {

            DeleteMessage( $chat_id, $message_id );

        }

        break;

    case '/magic8':

        if ( $user->user_on_game() )
        {

            $server = $user->server();

            if ( $server->status == 'started' )
            {

                $accused = $server->accused();

                if ( $server->getStatus() == 'court-3' && $accused->getUserId() > 0 && ! $accused->is( $user ) && $user->get_role()->group_id == 1 )
                {

                    if ( $user->has_coin( 4 ) )
                    {

                        if ( add_magic( $server->getId(), $user->getUserId(), 8 ) )
                        {

                            if ( $user->demote_coin( 4 ) )
                            {

                                $message = '🪄 جادو حقیقت:' . "<a href='tg://user?id=" . hash_user_id( $user->getUserId() ) . "'> </a>" . "\n";
                                $message .= '🔴 یکی از اعضای شهر ادعای نقش ' . "<b><u>" . $accused->get_name() . "</u></b>" . ' را دارد.';

                                $server->setUserId( $user->getUserId() )->addChat( '🪄 جادو حقیقت استفاده کرد.' );

                                foreach ( $server->users() as $item )
                                {

                                    if ( $item->sleep() || ! $item->is_user_in_game() ) continue;

                                    $item->SendMessageHtml( $message );


                                }

                                $message = "📯<b><u>جادوی حقیقت</u></b>  ، فعال شد ✅";


                            }
                            else
                            {

                                $message = '⚠️ شما سکه کافی برای استفاده از این جادو را ندارید .';

                            }

                        }
                        else
                        {

                            $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';

                        }

                    }
                    else
                    {

                        $message = '⚠️ شما سکه کافی برای استفاده از این جادو را ندارید .';

                    }

                }
                else
                {

                    $message = '⚠️خطا ! شما نمیتوانید از این جادو استفاده کنید .';

                }

            }
            else
            {

                $message = 'بازی هنوز شروع نشده است.';

            }
            $user->SendMessageHtml( $message );

        }

        break;

    case '/magic9':

        if ( $user->user_on_game() )
        {

            $server   = $user->server();
            $selector = new \library\Role( $server );

            if ( $selector->select( ROLE_TofangDar )->is( $user ) )
            {

                if ( $user->has_coin( 3 ) )
                {

                    if ( add_magic( $server->getId(), $user->getUserId(), 9 ) )
                    {

                        if ( $user->demote_coin( 3 ) )
                        {

                            $type    = (int) $server->setUserId( ROLE_TofangDar )->getMetaUser( 'type' );
                            $message = "📯<b><u>جادو تشخیص تیر</u></b>  ، فعال شد ✅" . "\n \n";
                            if ( $type == 2 )
                            {
                                $message .= ' فشنگ دریافت شده از نوع ( 🔴 جنگی ) است .';
                            }
                            else
                            {
                                $message .= ' فشنگ دریافت شده از نوع ( ⚪️ مشقی ) است .';
                            }


                        }
                        else
                        {

                            $message = '⚠️ شما سکه کافی برای استفاده از این جادو را ندارید .';

                        }

                    }
                    else
                    {

                        $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';

                    }

                }
                else
                {

                    $message = '⚠️ شما سکه کافی برای استفاده از این جادو را ندارید .';

                }

            }
            else
            {

                $message = '⚠️خطا ! شما نمیتوانید از این جادو استفاده کنید .';

            }
            $user->SendMessageHtml( $message );

        }


        break;

    case '/role':
    case 'تقلب':
        if ( $chat_id == ADMIN_LOG || $chat_id == ADMIN_ID )
        {
            if ( is_user_row_in_game( $chat_id ) )
            {
                $server = is_user_in_which_server( $chat_id );
                if ( isset( $server->status ) && $server->status == 'started' )
                {
                    $message = '⚜️ لیست حمله کننده ها:' . "\n \n";
                    for ( $i = 1; $i <= 3; $i ++ )
                    {

                        $attackers = get_attacker_list( $server->id, $i );

                        foreach ( $attackers as $attacker )
                        {

                            $user_role = get_role_by_user( $server->id, $attacker->user_id );
                            if ( $user_role > 0 )
                            {
                                $user          = new User( (int) $user_role, (int) $server->id );
                                $attacked_user = new User( (int) $attacker->meta_value, (int) $server->id );
                                $message       .= group_name( $i ) . ' : ( <u>' . user( $user->getUserId() )->name . '</u> ) با نقش ( ' . $user->get_role()->icon . ' ) قصد حمله به ( <u>' . $attacked_user->user()->name . '</u> ) را دارد.' . "\n \n";
                            }

                        }

                    }
                    SendMessage( $chat_id, $message, null, null, 'html' );
                }
                else
                {
                    $message = '😐 هنوز که شروع نشده';
                    Message();
                }
            }
        }
        break;

    case '/link':
    case 'لینک':

        if ( is_user_row_in_game( $chat_id ) )
        {

            $server = is_user_in_which_server( $chat_id );
            if ( isset( $server->status ) )
            {

                $code    = $server->id;
                $i       = rand( 1, 9 );
                $message = 'https://telegram.me/' . GetMe()->username . '?start=server-' . string_encode( $code ) . '-' . $i;
                $telegram->sendMessage( [
                    'chat_id'    => $chat_id,
                    'text'       => $message,
                    'parse_mode' => 'html'
                ] );

            }
            else
            {

                $message = '😐 هنوز که شروع نشده';
                Message();

            }

        }
        elseif ( $chat_id == ADMIN_LOG )
        {


            $new_link = $telegram->endpoint( 'createChatInviteLink', [
                'chat_id' => GP_REPORT,
            ] );

            $telegram->sendMessage( [
                'chat_id' => ADMIN_LOG,
                'text'    => 'REPORT ' . $new_link[ 'result' ][ 'invite_link' ]
            ] );

        }

        break;

    default:

        $filter = new Text( $text, $user );

        switch ( status() )
        {
            case 'get_name':

                if ( isset( $text ) && ! empty( $text ) )
                {

                    if ( $filter->filter_name() )
                    {

                        try
                        {

                            $user->changeName( trim( remove_emoji( $text ) ) )->setStatus( '' );
                            $message = 'نام مستعار شما به (' . $text . ') تغییر یافت ✅' . "\n \n";
                            $message .= '❗️درصورت نیاز میتوانید در منوی پروفایل آن را تغییر دهید .' . "\n \n";
                            $message .= '🕹 هم اکنون میتوانید جهت شروع بازی بر روی دکمه شروع بازی کلیک نمایید.';
                            SendMessage( $chat_id, $message, KEY_START_MENU );

                        }
                        catch ( Exception $exception )
                        {

                            $message = '🚫 متاسفم این اسم مورد تایید ما نمی باشد.';
                            SendMessage( $chat_id, $message, KEY_START_MENU );

                        }

                    }

                }
                else
                {

                    throw new ExceptionWarning( 'شما تنها مجاز به متن برای اسم هستید.' );

                }


                break;

            case 'change_name':
                if ( isset( $text ) && ! empty( $text ) )
                {

                    if ( $filter->filter_name() )
                    {

                        try
                        {

                            $user->changeName( trim( remove_emoji( $text ) ) )->setStatus( '' );
                            $message = '✅ نام مستعار شما به « [[name]] » تغییر یافت .' . "\n \n";
                            $message .= 'منوی اصلی 👇';
                            SendMessage(
                                $chat_id, __replace__( $message, [
                                '[[name]]' => trim( remove_emoji( $text ) )
                            ] ), KEY_START_MENU
                            );

                        }
                        catch ( Exception $exception )
                        {

                            $message = '🚫 متاسفم این اسم مورد تایید ما نمی باشد.';
                            SendMessage( $chat_id, $message, KEY_START_MENU );

                        }

                    }

                }
                else
                {

                    throw new ExceptionWarning( 'شما تنها مجاز به متن برای اسم هستید.' );

                }

                break;


            case 'check_latin_name':
                if ( isset( $text ) && ! empty( $text ) )
                {
                    if ( $filter->filter_latin_name($user) )
                    {
                        if ($user->has_coin(2500)) {
                            $name_id = $user->storeLatinName( trim( remove_emoji( $text ) ) );
                            $user->setStatus('buy_latin_name');
                            $message = '❗️نام مورد نظر در حال حاضر آزاد و مورد تایید است .'."\n";
                            $message .= 'برای تایید نهایی و خرید دکمه پرداخت را بزنید .'."\n \n";
                            $message .= 'سکه مورد نیاز جهت خرید : 2500'."";
                            $keyboard[][] = $telegram->buildInlineKeyboardButton('✅ پرداخت', '', 'buy_latin_name-'.$name_id);
                            $keyboard[][] = $telegram->buildInlineKeyboardButton('📛 انصراف', '', 'forget_latin_names');
                            SendMessage($chat_id, $message, $telegram->buildInlineKeyBoard($keyboard));
                        } else {
                            $user->setStatus( '' );
                            $message = '❗️نام مورد نظر در حال حاضر آزاد و مورد تایید است اما متاسفانه سکه کافی برای خرید آن را ندارید.'. "\n \n";
                            $message .= 'سکه مورد نیاز جهت خرید : 2500'. "";
                            $keyboard[][] = $telegram->buildInlineKeyboardButton('📛 انصراف', '', 'menu_start');
                            SendMessage($chat_id, $message, $telegram->buildInlineKeyBoard($keyboard));
                        }

                    }

                }
                else
                {

                    throw new ExceptionWarning( 'شما تنها مجاز به متن برای اسم هستید.' );

                }

                break;

            // ---------------------------------
            case 'get_users_server':

                if ( ! is_user_row_in_game( $chat_id ) )
                {

                    update_status( '' );
                    do_action( 'start' );
                    exit();

                }

                /** @var $update \helper\Update */
                if ( ! empty( $text ) && is_string( $text ) )
                {

                    /*if ( $filter->filter_chat() )
                    {

                        $User   = new User($chat_id);
                        $Server = new Server($User->getServerId(), $User->getUserId());

                        if ( $Server->setUserId($User->getUserId())->addChat($text) )
                        {

                            if ( $User->isDeleteMessage() ) DeleteMessage($chat_id, $message_id);

                            $user_league = $User->get_league();

                            $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <u><b>' . user($User->getUserId())->name . '</b></u> : ' . $text;

                            $users_server = $Server->users();

                            $User->setKeyboard(( $User->is($Server->server()->user_id) ? KEY_HOST_GAME_MENU : null ))->SendMessageHtml();

                            foreach ( $users_server as $item )
                            {

                                if ( !$item->is($chat_id) )
                                {

                                    $item->setKeyboard(( $item->is($Server->server()->user_id) ? KEY_HOST_GAME_MENU : null ))->SendMessageHtml();

                                }

                            }

                            do_action('report_game', $message, $chat_id);

                        }
                        else
                        {

                            throw new ErrorException('متاسفانه خطایی سیستمی رخ داد، لطفا با پشتیبانی تماس بگیرید.');

                        }

                    }*/


                    if ( $filter->emoji() )
                    {

                        USERS_SERVER:
                        if ( !is_english( $text ) )
                        {

                            if ( apply_filters( 'emoji_checker', $text, 4 ) )
                            {

                                $message = apply_filters( 'filter_text_chat', $text, $chat_id );
                                if ( $message === true )
                                {

                                    if ( check_time_chat( $chat_id ) )
                                    {

                                        $User   = new User( $chat_id );
                                        $Server = new Server( $User->getServerId(), $User->getUserId() );

                                        if ( $Server->setUserId( $User->getUserId() )->addChat( $text ) )
                                        {

                                            if ( $User->isDeleteMessage() ) DeleteMessage( $chat_id, $message_id );

                                            $user_league = $User->get_league();

                                            if ( $User->is( ADMIN_ID ) )
                                            {
                                                $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <b>' . "<u>" . user( $User->getUserId() )->name . "</u>" . '</b> : ' . $text;
                                            }
                                            else
                                            {
                                                $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <b>' . user( $User->getUserId() )->name . '</b> : ' . $text;
                                            }

                                            $users_server = $Server->users();

                                            $User->setKeyboard( ( $User->is( $Server->server()->user_id ) && $Server->server()->type == "private" ? KEY_HOST_GAME_MENU : null ) )->SendMessageHtml();

                                            foreach ( $users_server as $item )
                                            {

                                                if ( ! $item->is( $chat_id ) )
                                                {

                                                    $item->setKeyboard( ( $item->is( $Server->server()->user_id ) && $Server->server()->type == "private" ? KEY_HOST_GAME_MENU : null ) )->SendMessageHtml();

                                                }

                                            }

                                            do_action( 'report_game', $message, $chat_id );

                                        }
                                        else
                                        {

                                            warning_message( 'مشکل فنی رخ داده است، با پشتیبانی تماس بگیرید.' );

                                        }

                                    }
                                    else
                                    {

                                        warning_message( 'هر 2 ثانیه یک بار میتوانید پیام ارسال کنید❗' );

                                    }

                                }
                                else
                                {

                                    html();

                                }

                            }
                            else
                            {

                                warning_message( 'نمیتوانید بیشتر از 4 ایموجی استفاده کنید!' );

                            }

                        }
                        else
                        {

                            warning_message( 'فقط میتوانید از کلمات فارسی استفاده کنید!' );

                        }

                    }
                    elseif ( is_persian( $text ) )
                    {

                        goto USERS_SERVER;

                    }
                    else
                    {

                        throw new ExceptionWarning( 'تنها مجاز به ارسال 4 ایموجی می باشید.' );

                    }

                }
                elseif ( $user->is( ADMIN_LOG ) || $user->is( ADMIN_ID ) || $user->haveSubscribe() )
                {

                    if ( check_time_chat( $chat_id ) )
                    {


                        if ( $user->isDeleteMessage() ) DeleteMessage( $chat_id, $message_id );

                        $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user->get_league()->emoji . ' <b>' . $user->user()->name . '</b>';

//
                        bot( 'copyMessage', [

                            'chat_id'      => $user->getUserId(),
                            'from_chat_id' => $user->getUserId(),
                            'message_id'   => $message_id,
                            'caption'      => $message,
                            'parse_mode'   => 'html'

                        ] );
                        $server = new Server( $user->getServerId() );

                        foreach ( $server->users() as $user_on_game )
                        {

                            if ( ! $user_on_game->is( $chat_id ) ) bot( 'copyMessage', [

                                'chat_id'      => $user_on_game->getUserId(),
                                'from_chat_id' => $user->getUserId(),
                                'message_id'   => $message_id,
                                'caption'      => $message,
                                'parse_mode'   => 'html'

                            ] );

                        }

                    }
                    else
                    {

                        warning_message( 'هر 2 ثانیه یک بار میتوانید پیام ارسال کنید❗' );

                    }

                }
                else
                {

                    warning_message( 'شما فقط مجاز به ارسال متن هستید' );

                }

                break;

            case 'game_started':
            case 'playing_game':

                if ( ! is_user_row_in_game( $chat_id ) )
                {

                    update_status( '' );
                    do_action( 'start' );
                    exit();

                }

                PlayingGame:
                if ( ! empty( $text ) && is_string( $text ) )
                {

                    if ( $filter->emoji() )
                    {

                        PLAYING_GAME:
                        if ( !is_english( $text ) )
                        {

                            if ( apply_filters( 'emoji_checker', $text, 4 ) )
                            {

                                $last_text = $text;
                                $message   = apply_filters( 'filter_text_chat', $text, $chat_id );

                                if ( $message === true )
                                {

                                    if ( check_time_chat( $chat_id ) )
                                    {

                                        $User   = new User( $chat_id );
                                        $Server = new Server( $User->getServerId(), $User->getUserId() );
                                        if ( $Server->getStatus() == 'light' ) goto ChatNight;
                                        if ( $User->is( $Server->getMeta( 'mute' ) ) )
                                        {
                                            throw new ExceptionMessage( '🃏 کارت روز سکوت به شما اجازه صحبت کردن نمی دهد.' );
                                        }

                                        if ( in_array( $User->encode(), unserialize( ( get_server_meta( $Server->getId(), 'select', ROLE_Naghel ) ?? [] ) ) ) && ! $User->dead() )
                                        {
                                            throw new ExceptionWarning( 'شما لال شدید و امکان صحبت کردن را ندارید .' );
                                        }

                                        if (  $User->silence( $Server->day() ) ) {
                                            warning_message( '🃏 شما کارت روز سکوت دریافت کردید ! امروز قادر به حرف زدن نیستید .' );
                                        }
                                        elseif ( ! $User->hacked() )
                                        {

                                            $user_league = $User->get_league();


                                            if ( $User->is( ADMIN_ID ) )
                                            {
                                                $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <b>' . "<u>" . user( $User->getUserId() )->name . "</u>" . '</b> : ' . $text;
                                            }
                                            else
                                            {
                                                $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <b>' . user( $User->getUserId() )->name . '</b> : ' . $text;
                                            }

                                            $users_server = $Server->users();

                                            $selector = new Role( $Server->getId() );

                                            if ( $Server->role_exists( ROLE_Sahere ) )
                                            {

                                                $sahere = $selector->getUser( ROLE_Sahere );

                                            }
                                            else
                                            {

                                                $sahere = 0;

                                            }

                                            if ( $Server->setUserId( $User->getUserId() )->addChat( $text ) )
                                            {

                                                if ( $User->isDeleteMessage() ) DeleteMessage( $chat_id, $message_id );

                                                if ( ! $User->dead() && ( ! $User->is( $sahere ) || substr( $last_text, 0, 1 ) != '.' ) )
                                                {

                                                    $User->SendMessageHtml();

                                                    foreach ( $users_server as $item )
                                                    {

                                                        if ( ! $item->is( $User ) && $item->is_user_in_game() && ! $item->sleep() )
                                                        {

                                                            $item->SendMessageHtml();

                                                        }

                                                    }

                                                }
                                                else
                                                {

                                                    $users_server = $Server->getDeadUsers();

                                                    if ( $sahere instanceof User )
                                                    {

                                                        $status_sahere = ! $sahere->dead();

                                                    }
                                                    else
                                                    {

                                                        $status_sahere = false;

                                                    }

//                                                    $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <u><b>' . user($User->getUserId())->name . '</b></u> (<i><b>' . ( $User->is($sahere) && $status_sahere ? 'ساحره' : 'مرده' ) . '</b></i>) : ' . $text;
                                                    if ( $User->is( ADMIN_ID ) )
                                                    {
                                                        $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <b>' . "<u>" . user( $User->getUserId() )->name . "</u>" . '</b> (<i><b>' . ( $User->is( $sahere ) && $status_sahere ? 'ساحره' : 'مرده' ) . '</b></i>) : ' . $text;
                                                    }
                                                    else
                                                    {
                                                        $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <b>' . user( $User->getUserId() )->name . '</b> (<i><b>' . ( $User->is( $sahere ) && $status_sahere ? 'ساحره' : 'مرده' ) . '</b></i>) : ' . $text;
                                                    }

                                                    foreach ( $users_server as $item )
                                                    {

                                                        if ( $item->is_user_in_game() && ! $item->sleep() )
                                                        {

                                                            $item->SendMessageHtml();

                                                        }

                                                    }

                                                    if ( $sahere instanceof User && ! $sahere->in_prisoner() && $sahere->is_user_in_game() && ! $sahere->dead() )
                                                    {

                                                        $sahere->SendMessageHtml();

                                                    }

                                                }

                                                do_action( 'report_game', $message, $chat_id );

                                            }
                                            else
                                            {

                                                warning_message( 'مشکل فنی رخ داده است، با پشتیبانی تماس بگیرید.' );

                                            }

                                        }
                                        else
                                        {

                                            warning_message( '🧑🏻‍💻 شما توسط هکر هک شده اید و امروز قادر به صحبت نیستید.' );

                                        }

                                    }
                                    else
                                    {

                                        warning_message( 'هر 2 ثانیه یک بار میتوانید پیام ارسال کنید❗' );

                                    }

                                }
                                else
                                {

                                    html();

                                }

                            }
                            else
                            {

                                warning_message( 'نمیتوانید بیشتر از 4 ایموجی استفاده کنید!' );

                            }

                        }
                        else
                        {

                            warning_message( 'فقط میتوانید از کلمات فارسی استفاده کنید!' );

                        }

                    }
                    elseif ( is_persian( $text ) )
                    {

                        goto PLAYING_GAME;

                    }
                    else
                    {

                        throw new ExceptionWarning( 'تنها مجاز به ارسال 4 ایموجی می باشید.' );

                    }

                }
                elseif ( $user->is( ADMIN_LOG ) || $user->is( ADMIN_ID ) )
                {

                    if ( check_time_chat( $chat_id ) )
                    {

                        if ( ! $user->hacked() )
                        {


                            $server = new Server( $user->getServerId() );

                            $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user->get_league()->emoji . ' <b>' . $user->user()->name . '</b>';
                            bot( 'copyMessage', [

                                'chat_id'      => $user->getUserId(),
                                'from_chat_id' => $user->getUserId(),
                                'message_id'   => $message_id,
                                'caption'      => $message,
                                'parse_mode'   => 'html'

                            ] );

                            foreach ( $server->users() as $item )
                            {

                                if ( ! $item->is( $user ) && $item->is_user_in_game() && ! $item->sleep() ) bot( 'copyMessage', [

                                    'chat_id'      => $item->getUserId(),
                                    'from_chat_id' => $user->getUserId(),
                                    'message_id'   => $message_id,
                                    'caption'      => $message,
                                    'parse_mode'   => 'html'

                                ] );

                            }


                        }
                        else
                        {

                            warning_message( '🧑🏻‍💻 شما توسط هکر هک شده اید و امروز قادر به صحبت نیستید.' );

                        }

                    }
                    else
                    {

                        warning_message( 'هر 2 ثانیه یک بار میتوانید پیام ارسال کنید❗' );

                    }

                }
                else
                {

                    warning_message( 'شما فقط مجاز به ارسال متن هستید' );

                }

                break;

            case 'call_chi':

                if ( ! is_user_row_in_game( $chat_id ) )
                {

                    update_status( '' );
                    do_action( 'start' );
                    exit();

                }

                if ( ! empty( $text ) && is_string( $text ) )
                {

                    if ( $filter->emoji() )
                    {

                        CALL_CHI_GAME:
                        if ( !is_english( $text ) )
                        {

                            if ( apply_filters( 'emoji_checker', $text, 4 ) )
                            {

                                $last_text = $text;
                                $message   = apply_filters( 'filter_text_chat', $text, $chat_id );

                                if ( $message === true )
                                {

                                    if ( check_time_chat( $chat_id ) )
                                    {

                                        $User     = new User( $chat_id );
                                        $Server   = $user->server();
                                        $selector = new Role( $Server->getId() );

                                        $select_dead_telefon_chi = $selector->select( ROLE_TelefonChi, 'dead-select' );
                                        $select_telefon_chi      = $selector->select( ROLE_TelefonChi );

                                        if ( $Server->setUserId( $User->getUserId() )->addChat( $text ) )
                                        {

                                            if ( $User->isDeleteMessage() ) DeleteMessage( $chat_id, $message_id );

//                                                    $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <u><b>' . user($User->getUserId())->name . '</b></u> (<i><b>' . ( $User->is($sahere) && $status_sahere ? 'ساحره' : 'مرده' ) . '</b></i>) : ' . $text;


                                            if ( $select_dead_telefon_chi->is_user_in_game() )
                                            {

                                                $user_league = get__league_user( $chat_id );
                                                if ( $User->is( ADMIN_ID ) )
                                                {
                                                    $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <b>' . "<u>" . user( $User->getUserId() )->name . "</u>" . '</b>' . ( $User->dead() ? '(مرده)' : '' ) . ': ' . $text;
                                                }
                                                else
                                                {
                                                    $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <b>' . user( $User->getUserId() )->name . '</b>' . ( $User->dead() ? '(مرده)' : '' ) . ' : ' . $text;
                                                }

                                                $select_dead_telefon_chi->SendMessageHtml( $message );
                                                $select_telefon_chi->SendMessageHtml( $message );
                                            }
                                            else
                                            {
                                                throw new ExceptionWarning( 'الان نمیتونی چت کنی!' );
                                            }


                                        }
                                        else
                                        {

                                            warning_message( 'مشکل فنی رخ داده است، با پشتیبانی تماس بگیرید.' );

                                        }


                                    }
                                    else
                                    {

                                        warning_message( 'هر 2 ثانیه یک بار میتوانید پیام ارسال کنید❗' );

                                    }

                                }
                                else
                                {

                                    html();

                                }

                            }
                            else
                            {

                                warning_message( 'نمیتوانید بیشتر از 4 ایموجی استفاده کنید!' );

                            }

                        }
                        else
                        {

                            warning_message( 'فقط میتوانید از کلمات فارسی استفاده کنید!' );

                        }

                    }
                    elseif ( is_persian( $text ) )
                    {

                        goto CALL_CHI_GAME;

                    }
                    else
                    {

                        throw new ExceptionWarning( 'تنها مجاز به ارسال 4 ایموجی می باشید.' );

                    }

                }
                else
                {

                    warning_message( 'شما فقط مجاز به ارسال متن هستید' );

                }

                break;

            case 'night':

                if ( ! is_user_row_in_game( $chat_id ) )
                {

                    update_status( '' );
                    do_action( 'start' );
                    exit();

                }

                ChatNight:
                if ( ! empty( $text ) && is_string( $text ) )
                {

                    if ( $filter->emoji() )
                    {

                        NIGHT:
                        if ( !is_english( $text ) )
                        {

                            if ( apply_filters( 'emoji_checker', $text, 4 ) )
                            {

                                $message = apply_filters( 'filter_text_chat', $text, $chat_id );
                                if ( $message === true )
                                {

                                    if ( check_time_chat( $chat_id ) )
                                    {

                                        $User   = new User( $chat_id );
                                        $Server = new Server( $User->getServerId(), $User->getUserId() );

                                        if ( $User->is( $Server->getMeta( 'mute' ) ) )
                                        {
                                            throw new ExceptionMessage( '🃏 کارت روز سکوت به شما اجازه صحبت کردن نمی دهد.' );
                                        }

                                        $user_role   = $User->get_role();
                                        $user_league = $User->get_league();
                                        $selector    = new Role( $Server->getId() );

                                        if ( $Server->role_exists( ROLE_Sahere ) )
                                        {

                                            $sahere = $selector->getUser( ROLE_Sahere );

                                        }
                                        else
                                        {

                                            $sahere = 0;

                                        }

                                        $bazpors = $selector->getUser( ROLE_Bazpors );

                                        $bazpors_select = $selector->user()->select( ROLE_Bazpors );

                                        $team_framason = unserialize( $selector->getString()->select( ROLE_Framason, 'power' ) );

                                        $status_server = $Server->getStatus();
                                        if ( $User->dead() )
                                        {

                                            if ( $Server->setUserId( $User->getUserId() )->addChat( $text ) )
                                            {

                                                if ( $User->isDeleteMessage() ) DeleteMessage( $chat_id, $message_id );

                                                $users_server = $Server->getDeadUsers();

                                                if ( $sahere instanceof User )
                                                {

                                                    $status_sahere = ! $sahere->dead();

                                                }
                                                else
                                                {

                                                    $status_sahere = false;

                                                }


                                                if ( $User->is( ADMIN_ID ) )
                                                {
                                                    $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <b>' . "<u>" . user( $User->getUserId() )->name . "</u>" . '</b> (<i><b>مرده</b></i>) : ' . $text;
                                                }
                                                else
                                                {
                                                    $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <b>' . user( $User->getUserId() )->name . '</b> (<i><b>مرده</b></i>) : ' . $text;
                                                }


                                                $User->SendMessageHtml( $message );

                                                foreach ( $users_server as $item )
                                                {

                                                    if ( $item->is_user_in_game() && ! $item->sleep() && ! $item->is( $User ) )
                                                    {

                                                        $item->SendMessageHtml();

                                                    }

                                                }

                                                if ( $sahere instanceof User && ! $sahere->in_prisoner() && $sahere->is_user_in_game() && ! $sahere->dead() )
                                                {

                                                    $sahere->SendMessageHtml();

                                                }

                                                do_action( 'report_game', $message, $chat_id );

                                            }
                                            else
                                            {

                                                warning_message( 'مشکل فنی رخ داده است، با پشتیبانی تماس بگیرید.' );

                                            }

                                        }
                                        elseif ( ( $User->is( $bazpors_select ) || $user_role->id == ROLE_Bazpors ) && $status_server == 'light' )
                                        {

                                            if ( $bazpors->is( $User ) )
                                            {

                                                $message = '<u><b>بازپرس</b></u> : ' . $text;

                                            }
                                            elseif ( $bazpors_select > 0 )
                                            {


                                                if ( $User->is( ADMIN_ID ) )
                                                {
                                                    $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <b>' . "<i>" . $bazpors_select->user()->name . "</i>" . '</b> : ' . $text;
                                                }
                                                else
                                                {
                                                    $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <b>' . $bazpors_select->user()->name . '</b> : ' . $text;
                                                }

                                            }


                                            if ( $Server->setUserId( $User->getUserId() )->addChat( $text ) )
                                            {

                                                if ( $User->isDeleteMessage() ) DeleteMessage( $chat_id, $message_id );

                                                if ( ! $bazpors->dead() )
                                                {

                                                    $bazpors->SendMessageHtml();

                                                }

                                                if ( $bazpors_select instanceof User && ! $bazpors_select->dead() )
                                                {

                                                    $bazpors_select->SendMessageHtml();

                                                }

                                                do_action( 'report_game', $message, $chat_id );

                                            }
                                            else
                                            {

                                                warning_message( 'مشکل فنی رخ داده است، با پشتیبانی تماس بگیرید.' );

                                            }


                                        }
                                        elseif ( $sahere instanceof User && $sahere->is( $User ) && $status_server == 'light' && count( $Server->getDeadUsers() ) > 0 )
                                        {

                                            if ( $Server->setUserId( $User->getUserId() )->addChat( $text ) )
                                            {

                                                if ( $User->isDeleteMessage() ) DeleteMessage( $chat_id, $message_id );

                                                $users_server = $Server->getDeadUsers();


                                                if ( $User->is( ADMIN_ID ) )
                                                {
                                                    $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <b>' . "<u>" . user( $User->getUserId() )->name . "</u>" . '</b> (<i><b>ساحره</b></i>) : ' . $text;
                                                }
                                                else
                                                {
                                                    $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <b>' . user( $User->getUserId() )->name . '</b> (<i><b>ساحره</b></i>) : ' . $text;
                                                }

                                                $User->SendMessageHtml();

                                                foreach ( $users_server as $item )
                                                {

                                                    if ( $item->is_user_in_game() && $item->dead() && ! $item->sleep() )
                                                    {

                                                        $item->SendMessageHtml();

                                                    }

                                                }

                                                do_action( 'report_game', $message, $chat_id );


                                            }
                                            else
                                            {

                                                warning_message( 'مشکل فنی رخ داده است، با پشتیبانی تماس بگیرید.' );

                                            }

                                        }
                                        elseif ( $user_role->group_id == 2 && $status_server == 'light' && ( $user_role->id != ROLE_ShahKosh || ! $Server->isFullMoon() ) )
                                        {

                                            if ( $Server->setUserId( $User->getUserId() )->addChat( $text ) )
                                            {

                                                if ( $User->isDeleteMessage() ) DeleteMessage( $chat_id, $message_id );


                                                if ( $User->is( ADMIN_ID ) )
                                                {
                                                    $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' ' . "<u>" . user( $User->getUserId() )->name . "</u>" . ' (<b><i>' . ( trim( remove_emoji( $user_role->icon ) ) ) . '</i></b>) : ' . $text;
                                                }
                                                else
                                                {
                                                    $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' ' . user( $User->getUserId() )->name . ' (<b><i>' . ( trim( remove_emoji( $user_role->icon ) ) ) . '</i></b>) : ' . $text;
                                                }


                                                $User->SendMessageHtml();

                                                $role_group_2 = $Server->roleByGroup( 2 );
                                                foreach ( $role_group_2 as $user )
                                                {

                                                    if ( $user->is_user_in_game() && $user->check( $bazpors_select ) && ! $user->is( $User ) && ( ! $Server->role_exists( ROLE_ShahKosh ) || ! $Server->isFullMoon() ) )
                                                    {

                                                        $user->SendMessageHtml();

                                                    }
                                                }

                                                do_action( 'report_game', $message, $chat_id );

                                            }
                                            else
                                            {

                                                warning_message( 'مشکل فنی رخ داده است، با پشتیبانی تماس بگیرید.' );

                                            }

                                        }
                                        elseif ( $user_role->group_id == 3 && $status_server == 'light' && in_array( $Server->league_id, MOSTAGHEL_TEAM ) )
                                        {

                                            if ( $Server->setUserId( $User->getUserId() )->addChat( $text ) )
                                            {

                                                if ( $User->isDeleteMessage() ) DeleteMessage( $chat_id, $message_id );


                                                if ( $User->is( ADMIN_ID ) )
                                                {
                                                    $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' ' . "<u>" . user( $User->getUserId() )->name . "</u>" . ' (<b><i>' . ( trim( remove_emoji( $user_role->icon ) ) ) . '</i></b>) : ' . $text;
                                                }
                                                else
                                                {
                                                    $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' ' . user( $User->getUserId() )->name . ' (<b><i>' . ( trim( remove_emoji( $user_role->icon ) ) ) . '</i></b>) : ' . $text;
                                                }


                                                $User->SendMessageHtml();

                                                $role_group_2 = $Server->roleByGroup( 3 );
                                                foreach ( $role_group_2 as $user )
                                                {

                                                    if ( $user->is_user_in_game() && $user->check( $bazpors_select ) && ! $user->is( $User ) )
                                                    {

                                                        $user->SendMessageHtml();

                                                    }
                                                }

                                                do_action( 'report_game', $message, $chat_id );

                                            }
                                            else
                                            {

                                                warning_message( 'مشکل فنی رخ داده است، با پشتیبانی تماس بگیرید.' );

                                            }

                                        }
                                        elseif ( in_array( $User->encode(), $team_framason ) && $status_server == 'light' )
                                        {

                                            if ( $Server->setUserId( $User->getUserId() )->addChat( $text . ' [🪬ماسون]' ) )
                                            {

                                                if ( $User->isDeleteMessage() ) DeleteMessage( $chat_id, $message_id );


                                                if ( $User->is( ADMIN_ID ) )
                                                {
                                                    $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' ' . "<u>" . user( $User->getUserId() )->name . "</u>" . ': ' . $text;
                                                }
                                                else
                                                {
                                                    $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' ' . user( $User->getUserId() )->name . ': ' . $text;
                                                }


                                                $User->SendMessageHtml();

                                                foreach ( $team_framason as $user )
                                                {

                                                    $user = new User( string_decodeOld( $user ), $Server->getId() );
                                                    if ( $user->is_user_in_game() && $user->check( $bazpors_select ) && ! $user->is( $User ) )
                                                    {

                                                        $user->SendMessageHtml();

                                                    }

                                                }

                                            }
                                            else
                                            {

                                                warning_message( 'مشکل فنی رخ داده است، با پشتیبانی تماس بگیرید.' );

                                            }

                                        }
                                        else
                                        {

                                            throw new ExceptionWarning( 'هم اکنون، نمیتونی چت کنی!' );

                                        }

                                    }
                                    else
                                    {

                                        warning_message( 'هر 2 ثانیه یک بار میتوانید پیام ارسال کنید❗' );

                                    }

                                }
                                else
                                {

                                    html();

                                }

                            }
                            else
                            {

                                warning_message( 'نمیتوانید بیشتر از 4 ایموجی استفاده کنید!' );

                            }

                        }
                        else
                        {

                            warning_message( 'فقط میتوانید از کلمات فارسی استفاده کنید!' );

                        }

                    }
                    elseif ( is_persian( $text ) )
                    {

                        goto NIGHT;

                    }
                    else
                    {

                        throw new ExceptionWarning( 'تنها مجاز به ارسال 4 ایموجی می باشید.' );

                    }

                }
                elseif ( $user->is( ADMIN_LOG ) || $user->is( ADMIN_ID ) )
                {

                    if ( check_time_chat( $chat_id ) )
                    {


                        $message  = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user->get_league()->emoji . ' <b>' . $user->user()->name . '</b>';
                        $server   = new Server( $user->getServerId() );
                        $selector = new Role( $server->getId() );

                        if ( $user->dead() )
                        {

                            if ( $user->isDeleteMessage() ) DeleteMessage( $chat_id, $message_id );

                            bot( 'copyMessage', [

                                'chat_id'      => $user->getUserId(),
                                'from_chat_id' => $user->getUserId(),
                                'message_id'   => $message_id,
                                'caption'      => $message,
                                'parse_mode'   => 'html'

                            ] );

                            foreach ( $server->getDeadUsers() as $item )
                            {

                                if ( $item->is_user_in_game() && ! $item->is( $user ) ) bot( 'copyMessage', [

                                    'chat_id'      => $item->getUserId(),
                                    'from_chat_id' => $user->getUserId(),
                                    'message_id'   => $message_id,
                                    'caption'      => $message,
                                    'parse_mode'   => 'html'

                                ] );

                            }


                        }
                        elseif ( $user_role->group_id == 2 && $server->getStatus() == 'light' )
                        {


                            if ( $user->isDeleteMessage() ) DeleteMessage( $chat_id, $message_id );

                            bot( 'copyMessage', [

                                'chat_id'      => $user->getUserId(),
                                'from_chat_id' => $user->getUserId(),
                                'message_id'   => $message_id,
                                'caption'      => $message,
                                'parse_mode'   => 'html'

                            ] );

                            $role_group_2   = $Server->roleByGroup( 2 );
                            $bazpors_select = $selector->user()->select( ROLE_Bazpors );

                            foreach ( $role_group_2 as $item )
                            {

                                if ( $item->check( $bazpors_select ) && $user->check( $item ) ) bot( 'copyMessage', [

                                    'chat_id'      => $item->getUserId(),
                                    'from_chat_id' => $user->getUserId(),
                                    'message_id'   => $message_id,
                                    'caption'      => $message,
                                    'parse_mode'   => 'html'

                                ] );

                            }

                        }
                        else
                        {

                            warning_message( 'الان نمیتونی چت کنی!' );

                        }


                    }
                    else
                    {

                        warning_message( 'هر 2 ثانیه یک بار میتوانید پیام ارسال کنید❗' );

                    }

                }
                else
                {

                    warning_message( 'شما فقط مجاز به ارسال متن هستید' );

                }

                break;

            case 'voting':

                if ( ! is_user_row_in_game( $chat_id ) )
                {

                    update_status( '' );
                    do_action( 'start' );
                    exit();

                }

                if ( ! empty( $text ) && is_string( $text ) )
                {

                    if ( $filter->emoji() )
                    {

                        VOTING:
                        if ( $filter->emoji() )
                        {

                            if ( !is_english( $text ) )
                            {

                                if ( apply_filters( 'emoji_checker', $text, 4 ) )
                                {

                                    $last_text = $text;
                                    $message   = apply_filters( 'filter_text_chat', $text, $chat_id );
                                    if ( $message === true )
                                    {

                                        if ( check_time_chat( $chat_id ) )
                                        {

                                            $User   = new User( $chat_id );
                                            $Server = new Server( $User->getServerId(), $User->getUserId() );

                                            if ( $User->is( $Server->getMeta( 'mute' ) ) )
                                            {
                                                throw new ExceptionMessage( '🃏 کارت روز سکوت به شما اجازه صحبت کردن نمی دهد.' );
                                            }

                                            if ( in_array( $User->encode(), unserialize( ( get_server_meta( $Server->getId(), 'select', ROLE_Naghel ) ?? [] ) ) ) && ! $User->dead() )
                                            {
                                                throw new ExceptionWarning( 'شما لال شدید و امکان صحبت کردن را ندارید .' );
                                            }
                                            if (  $User->silence( $Server->day() ) ) {
                                                warning_message( '🃏 شما کارت روز سکوت دریافت کردید ! امروز قادر به حرف زدن نیستید .' );
                                            }
                                            elseif ( ! $User->hacked() )
                                                // if ( ! $User->hacked() )
                                            {

                                                $accused     = $Server->accused();
                                                $user_league = $User->get_league();
                                                $selector    = new Role( $Server->getId() );

                                                if ( $Server->role_exists( ROLE_Sahere ) )
                                                {

                                                    $sahere = $selector->getUser( ROLE_Sahere );

                                                }
                                                else
                                                {

                                                    $sahere = 0;

                                                }

                                                if ( $accused->is( $User ) || $Server->getStatus() != 'court-2' )
                                                {

                                                    if ( $User->is( ADMIN_ID ) )
                                                    {
                                                        $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <b>' . "<u>" . user( $User->getUserId() )->name . "</u>" . '</b> : ' . $text;
                                                    }
                                                    else
                                                    {
                                                        $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <b>' . user( $User->getUserId() )->name . '</b> : ' . $text;
                                                    }

                                                    $users_server = $Server->users();

                                                    if ( $Server->setUserId( $User->getUserId() )->addChat( $text ) )
                                                    {

                                                        if ( $User->isDeleteMessage() ) DeleteMessage( $chat_id, $message_id );
                                                        if ( ! $User->dead() && ( ! $User->is( $sahere ) || substr( $last_text, 0, 1 ) != '.' ) )
                                                        {

                                                            $User->SendMessageHtml();

                                                            foreach ( $users_server as $item )
                                                            {

                                                                if ( ! $item->is( $User ) && $item->is_user_in_game() && ! $item->sleep() )
                                                                {

                                                                    $item->SendMessageHtml();

                                                                }

                                                            }

                                                        }
                                                        else
                                                        {

                                                            $users_server = $Server->getDeadUsers();

                                                            if ( $sahere instanceof User )
                                                            {

                                                                $status_sahere = ! $sahere->dead();

                                                            }
                                                            else
                                                            {

                                                                $status_sahere = false;

                                                            }


                                                            if ( $User->is( ADMIN_ID ) )
                                                            {
                                                                $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <b>' . "<u>" . user( $User->getUserId() )->name . "</u>" . '</b> (<i><b>' . ( $User->is( $sahere ) && $status_sahere ? 'ساحره' : 'مرده' ) . '</b></i>) : ' . $text;
                                                            }
                                                            else
                                                            {
                                                                $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <b>' . user( $User->getUserId() )->name . '</b> (<i><b>' . ( $User->is( $sahere ) && $status_sahere ? 'ساحره' : 'مرده' ) . '</b></i>) : ' . $text;
                                                            }

                                                            foreach ( $users_server as $item )
                                                            {

                                                                if ( $item->is_user_in_game() && ! $item->sleep() )
                                                                {

                                                                    $item->SendMessageHtml();

                                                                }

                                                            }

                                                            if ( $sahere instanceof User && ! $sahere->in_prisoner() && $sahere->is_user_in_game() && ! $sahere->dead() )
                                                            {

                                                                $sahere->SendMessageHtml();

                                                            }

                                                        }

                                                        do_action( 'report_game', $message, $chat_id );

                                                    }
                                                    else
                                                    {

                                                        warning_message( 'مشکل فنی رخ داده است، با پشتیبانی تماس بگیرید.' );

                                                    }

                                                }
                                                elseif ( $User->dead() )
                                                {

                                                    if ( $Server->setUserId( $User->getUserId() )->addChat( $text ) )
                                                    {

                                                        if ( $User->isDeleteMessage() ) DeleteMessage( $chat_id, $message_id );
                                                        $users_server = $Server->getDeadUsers();

                                                        if ( $sahere instanceof User )
                                                        {

                                                            $status_sahere = ! $sahere->dead();

                                                        }
                                                        else
                                                        {

                                                            $status_sahere = false;

                                                        }


                                                        if ( $User->is( ADMIN_ID ) )
                                                        {
                                                            $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <b>' . "<u>" . user( $User->getUserId() )->name . "</u>" . '</b> (<i><b>مرده</b></i>) : ' . $text;
                                                        }
                                                        else
                                                        {
                                                            $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <b>' . user( $User->getUserId() )->name . '</b> (<i><b>مرده</b></i>) : ' . $text;
                                                        }

                                                        foreach ( $users_server as $item )
                                                        {

                                                            if ( $item->is_user_in_game() && ! $item->sleep() )
                                                            {

                                                                $item->SendMessageHtml();

                                                            }

                                                        }

                                                        if ( $sahere instanceof User && ! $sahere->in_prisoner() && $sahere->is_user_in_game() && ! $sahere->dead() )
                                                        {

                                                            $sahere->SendMessageHtml();

                                                        }

                                                        do_action( 'report_game', $message, $chat_id );

                                                    }
                                                    else
                                                    {

                                                        warning_message( 'مشکل فنی رخ داده است، با پشتیبانی تماس بگیرید.' );

                                                    }

                                                }
                                                else
                                                {

                                                    warning_message( 'الان نمیتونی چت کنی.' );

                                                }

                                            }
                                            else
                                            {

                                                warning_message( '🧑🏻‍💻 شما توسط هکر هک شده اید و امروز قادر به صحبت نیستید.' );

                                            }

                                        }
                                        else
                                        {

                                            warning_message( 'هر 2 ثانیه یک بار میتوانید پیام ارسال کنید❗' );

                                        }

                                    }
                                    else
                                    {

                                        Message();

                                    }

                                }
                                else
                                {

                                    warning_message( 'نمیتوانید بیشتر از 4 ایموجی استفاده کنید!' );

                                }

                            }
                            else
                            {

                                warning_message( 'فقط میتوانید از کلمات فارسی استفاده کنید!' );

                            }

                        }
                        else
                        {

                            warning_message( 'فقط میتوانید از کلمات فارسی استفاده کنید!' );

                        }

                    }
                    elseif ( is_persian( $text ) )
                    {

                        goto VOTING;

                    }
                    else
                    {

                        throw new ExceptionWarning( 'تنها مجاز به ارسال 4 ایموجی می باشید.' );

                    }

                }
                elseif ( $user->is( ADMIN_LOG ) || $user->is( ADMIN_ID ) )
                {

                    if ( check_time_chat( $chat_id ) )
                    {

                        if ( ! $user->hacked() )
                        {


                            $server  = new Server( $user->getServerId() );
                            $accused = $server->accused();


                            $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user->get_league()->emoji . ' <b>' . $user->user()->name . '</b>';

                            if ( $accused->is( $user ) || $server->getStatus() != 'court-2' )
                            {

                                if ( $user->isDeleteMessage() ) DeleteMessage( $chat_id, $message_id );

                                bot( 'copyMessage', [

                                    'chat_id'      => $user->getUserId(),
                                    'from_chat_id' => $user->getUserId(),
                                    'message_id'   => $message_id,
                                    'caption'      => $message,
                                    'parse_mode'   => 'html'

                                ] );


                                foreach ( $server->users() as $item )
                                {

                                    if ( ! $item->is( $user ) && $item->is_user_in_game() && ! $item->sleep() ) bot( 'copyMessage', [

                                        'chat_id'      => $item->getUserId(),
                                        'from_chat_id' => $user->getUserId(),
                                        'message_id'   => $message_id,
                                        'caption'      => $message,
                                        'parse_mode'   => 'html'

                                    ] );

                                }


                            }
                            elseif ( $user->dead() )
                            {

                                if ( $user->isDeleteMessage() ) DeleteMessage( $chat_id, $message_id );

                                bot( 'copyMessage', [

                                    'chat_id'      => $user->getUserId(),
                                    'from_chat_id' => $user->getUserId(),
                                    'message_id'   => $message_id,
                                    'caption'      => $message,
                                    'parse_mode'   => 'html'

                                ] );

                                foreach ( $server->getDeadUsers() as $item )
                                {

                                    if ( $item->is_user_in_game() && ! $item->is( $user ) ) bot( 'copyMessage', [

                                        'chat_id'      => $item->getUserId(),
                                        'from_chat_id' => $user->getUserId(),
                                        'message_id'   => $message_id,
                                        'caption'      => $message,
                                        'parse_mode'   => 'html'

                                    ] );

                                }

                            }
                            else
                            {

                                warning_message( 'الان نمیتونی چت کنی.' );

                            }


                        }
                        else
                        {

                            warning_message( '🧑🏻‍💻 شما توسط هکر هک شده اید و امروز قادر به صحبت نیستید.' );

                        }

                    }
                    else
                    {

                        warning_message( 'هر 2 ثانیه یک بار میتوانید پیام ارسال کنید❗' );

                    }

                }
                else
                {

                    warning_message( 'شما فقط مجاز به ارسال متن هستید' );

                }
                break;

            case 'last_chat':

                if ( ! is_user_row_in_game( $chat_id ) )
                {

                    update_status( '' );
                    do_action( 'start' );
                    exit();

                }

                if ( ! empty( $text ) && is_string( $text ) )
                {

                    if ( $filter->emoji() )
                    {

                        LAST_CHAT:
                        if ( !is_english( $text ) )
                        {

                            if ( apply_filters( 'emoji_checker', $text, 4 ) )
                            {

                                $message = apply_filters( 'filter_text_chat', $text, $chat_id );
                                if ( $message === true )
                                {

                                    if ( check_time_chat( $chat_id ) )
                                    {

                                        $User   = new User( $chat_id );
                                        $Server = new Server( $User->getServerId(), $User->getUserId() );

                                        if ( $Server->setUserId( $User->getUserId() )->addChat( $text ) )
                                        {

                                            if ( $User->isDeleteMessage() ) DeleteMessage( $chat_id, $message_id );
                                            $user_league = $User->get_league();


                                            if ( $User->is( ADMIN_ID ) )
                                            {
                                                $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <b>' . "<u>" . user( $User->getUserId() )->name . "</u>" . ( $User->dead() ? '(مرده)' : '' ) . '</b> : ' . $text;
                                            }
                                            else
                                            {
                                                $message = "<a href='tg://user?id=" . hash_user_id() . "'> </a>" . $user_league->emoji . ' <b>' . user( $User->getUserId() )->name . ( $User->dead() ? '(مرده)' : '' ) . '</b> : ' . $text;
                                            }

                                            $users_server = $Server->users();

                                            $User->SendMessageHtml();

                                            foreach ( $users_server as $item )
                                            {

                                                if ( ! $item->is( $User ) && $item->is_user_in_game() && ! $item->sleep() )
                                                {

                                                    $item->SendMessageHtml();

                                                }

                                            }

                                            do_action( 'report_game', $message, $chat_id );

                                        }
                                        else
                                        {

                                            warning_message( 'مشکل فنی رخ داده است، با پشتیبانی تماس بگیرید.' );

                                        }

                                    }
                                    else
                                    {

                                        warning_message( 'هر 2 ثانیه یک بار میتوانید پیام ارسال کنید❗' );

                                    }

                                }
                                else
                                {

                                    html();

                                }

                            }
                            else
                            {

                                warning_message( 'نمیتوانید بیشتر از 4 ایموجی استفاده کنید!' );

                            }

                        }
                        else
                        {

                            warning_message( 'فقط میتوانید از کلمات فارسی استفاده کنید!' );

                        }

                    }
                    elseif ( is_persian( $text ) )
                    {

                        goto LAST_CHAT;

                    }
                    else
                    {

                        throw new ExceptionWarning( 'تنها مجاز به ارسال 4 ایموجی می باشید.' );

                    }

                }
                else
                {

                    warning_message( 'شما فقط مجاز به ارسال متن هستید' );

                }

                break;

            case 'send_note_report':

                if ( isset( $text ) && is_string( $text ) )
                {

                    if ( $filter->is_persian() )
                    {

                        if ( mb_strlen( $text, 'UTF-8' ) <= 250 )
                        {

                            $data      = explode( '-', data( $chat_id ) );
                            $server    = is_user_in_which_server( $chat_id );
                            $server_id = $server->id ?? - 2;
                            $report    = get_report( $chat_id, $data[ 2 ], $server_id );
                            if ( empty( $report ) || $report->server_id == 0 )
                            {
                                if ( check_ban( $data[ 2 ] ) )
                                {
                                    $user = user( $data[ 2 ] );

                                    $message = '⚠️ گزارش تخلف [[user]] ارسال شد .' . "\n \n";
                                    $message .= 'نوع تخلف : [[wg]]' . "\n \n";
                                    $message .= 'در صورت تایید ، نتیجه آن اعلام خواهد شد.';
                                    SendMessage(
                                        $chat_id, __replace__( $message, [
                                        '[[user]]' => "<u>" . $user->name . "</u>",
                                        '[[wg]]'   => apply_filters( 'filter_report_name', $data[ 3 ] ),
                                    ] ), null, null, 'html'
                                    );

                                    add_filter( 'filter_token', function () {
                                        global $token_bot;
                                        return $token_bot[ 0 ];
                                    } );

                                    $reports    = get_report_by_server( $server_id, $data[ 2 ] );
                                    $message_id = null;
                                    $message    = '❗️گزارش جدید ' . "\n \n";

                                    if ( count( $reports ) > 0 )
                                    {

                                        /* @var $report \helper\Report */
                                        foreach ( $reports as $report )
                                        {

                                            $message .= '🟩 گزارش کننده : [[user]] `[[user_id]]`' . "\n";
                                            $message .= '🟨 [[wg]]' . "\n";

                                            __replace__( $message, [
                                                '[[user_id]]' => $report->user_id,
                                                '[[user]]'    => user( $report->user_id )->name,
                                                '[[wg]]'      => apply_filters( 'filter_report_name', $report->type ),
                                            ] );

                                            if ( $message_id == null && ! empty( $report->message_id ) )
                                            {

                                                $message_id = $report->message_id;

                                            }

                                        }

                                    }


                                    $message .= '🟩 گزارش کننده : [[user]] `[[user_id]]`' . "\n";
                                    $message .= '🟨 [[wg]]' . "\n";
                                    $message .= "\n" . '🟥 گزارش شده : [[user_wg]] `[[user_wg_id]]`' . "\n";
                                    $message .= '📝 یادداشت : ' . ( 'دارد' ) . "\n";

                                    add_filter( 'send_massage_text', function ( $text ) {
                                        return tr_num( $text, 'en', '.' );
                                    }, 11 );

                                    if ( isset( $chat_id ) && isset( $data[ 2 ] ) && isset( $server_id ) && $data[ 3 ] )
                                    {

                                        $report_id = add_report( $chat_id, $data[ 2 ], $server_id, $data[ 3 ], $message_id, $text );

                                        if ( ! empty( $report_id ) && is_numeric( $report_id ) )
                                        {

                                            $keyboard = $telegram->buildInlineKeyBoard( [ [ $telegram->buildInlineKeyboardButton( '💭 پیام ها ، ⛔️ اعمال مسدودی', '', 'block-' . $report_id ) ] ] );

                                            if ( count( $reports ) > 0 && $message_id > 0 )
                                            {

                                                EditMessageText(
                                                    GP_MANAGER, $message_id, __replace__( $message, [
                                                    '[[user]]'       => user()->name,
                                                    '[[user_wg]]'    => $user->name,
                                                    '[[user_id]]'    => $chat_id,
                                                    '[[user_wg_id]]' => $user->user_id,
                                                    '[[wg]]'         => apply_filters( 'filter_report_name', $data[ 3 ] ),
                                                ] ), $keyboard
                                                );

                                            }
                                            else
                                            {

                                                $messageid  = SendMessage(
                                                    GP_MANAGER, __replace__( $message, [
                                                    '[[user]]'       => user()->name,
                                                    '[[user_wg]]'    => $user->name,
                                                    '[[user_id]]'    => $chat_id,
                                                    '[[user_wg_id]]' => $user->user_id,
                                                    '[[wg]]'         => apply_filters( 'filter_report_name', $data[ 3 ] ),
                                                ] ), $keyboard
                                                );
                                                $message_id = $messageid->message_id;

                                            }

                                            update_report( $data[ 2 ], $server_id, [

                                                'message_id' => $message_id,

                                            ] );

                                        }
                                        else
                                        {

                                            throw new Exception( 'ERROR ON INSERT REPORT' );

                                        }

                                    }
                                    else
                                    {

                                        throw new Exception( 'ERROR ON SCAN REPORT' );

                                    }

                                }
                                else
                                {

                                    throw new ExceptionWarning( 'شخص مورد نظر در حال حاضر مسدود است.' );

                                }

                            }
                            else
                            {

                                update_status( '' );
                                update_data( '' );
                                throw new ExceptionWarning( 'شما قبلا این فرد را گزارش کرده اید.' );

                            }

                            update_status( '' );
                            update_data( '' );

                        }
                        else
                        {

                            throw new ExceptionWarning( 'یادداشت نمیتواند بیشتر از 250 کاراکتر باشد.' );

                        }

                    }
                    else
                    {

                        throw new ExceptionWarning( 'تنها مجاز به استفاده از کلمات فارسی هستید.' );

                    }

                }
                else
                {

                    throw new ExceptionWarning( 'تنها مجاز به ارسال متن هستید.' );

                }

                break;

            // --------------------------------------------------------------------
            case 'get_token_recovery_account':
                if ( ! is_null( $text ) && is_string( $text ) )
                {
                    $token = get_token_security_user( $text );
                    if ( is_numeric( $token ) )
                    {
                        $info = GetChat( $token );
                        $json = json_encode( $info );
                        $info = json_decode(
                            __replace__( $json, [
                                '<' => '',
                                '>' => ''
                            ] )
                        );
//                        if ( empty( $info->first_name ) && strlen( $info->first_name ) == 0 && ! isset( $info->last_name ) && ! isset( $info->username ) || $chat_id == "606555711" )
                        if ( empty( $info->first_name ) && strlen( $info->first_name ) == 0 && ! isset( $info->last_name ) && ! isset( $info->username )  )
                        {
                            $user_move = user( $chat_id );
                            $point_move = get_point( $chat_id );
                            move_account( $token, $chat_id );
                            $message = '🔔 کد اعتبارسنجی شما تایید شد  .' . "\n";
                            $message .= '[[point]] امتیاز از [[user]] برای شما انتقال یافت.';
                            SendMessage(
                                $chat_id, __replace__( $message, [
                                    '[[point]]' => $point_move,
                                    '[[user]]'  => $user_move->name
                                ] )
                            );
                            update_status( '' );

                        }
                        else
                        {

                            $message = '⚠️ خطا ، حساب قبلی هنوز delete account نشده است..';
                            Message();

                        }

                    }

                }
                break;

            case 'name_vip_league':
            case 'change_name_vip_league':
                if ( ! is_null( $text ) && is_string( $text ) && is_persian( $text ) && mb_strlen( $text, 'UTF-8' ) <= 15 )
                {
                    if ( !is_english( $text ) )
                    {
                        if ( apply_filters( 'emoji_checker', $text, 0 ) && mb_strlen( remove_emoji( $text ), 'UTF-8' ) == mb_strlen( $text, 'UTF-8' ) )
                        {

                            $status_user = status();
                            if ( ( $status_user == 'name_vip_league' && add_vip_league( $chat_id, data(), $text ) ) || ( $status_user == 'change_name_vip_league' && update_name_vip_league( $chat_id, data(), $text ) ) )
                            {

                                $message = '♨️ نام لیگ شما ثبت شد .' . "\n \n";
                                $message .= 'از هم اکنون میتوانید در قسمت پروفایل ، لیگ مورد نظر خود را برای استفاده انتخاب کنید .';
                                update_status( '' );

                            }
                            else
                            {

                                $message = 'خطا سیستمی رخ داد .. لطفا با پشتیبانی تماس بگیرید.';

                            }

                        }
                        else
                        {

                            $message = '⚠️ خطا، نمیتوانید از ایموجی استفاده کنید!';

                        }

                    }
                    else
                    {

                        $message = '⚠️ خطا، فقط میتوانید از کلمات فارسی استفاده کنید!';

                    }

                }
                else
                {

                    $message = 'اسم باید فارسی و بین 1 و 15 کاراکتر باشد!';

                }

                Message();
                break;

            case 'get_vip_emoji_league':

                if ( isset( $message->dice ) )
                {
                    $text = $message->dice->emoji;
                }

                if ( isset( $text ) && ! empty( $text ) )
                {

                    if ( apply_filters( 'emoji_checker', $text, 1 ) && ! is_persian( $text ) )
                    {

                        $league = get_vip_league_by_emoji( $text );
                        if ( isset( $league->id ) && isset( $league->coin ) )
                        {

                            $message = '🔸لیگ انتخاب شده : [[league_name]]' . "\n";
                            $message .= '🔸سکه مورد نیاز برای خرید : [[league_coin]]' . "\n";
                            $message .= '🔸موجودی سکه شما : [[coin]]' . "\n \n";
                            $message .= 'برای ادامه مراحل خرید تایید را بزنید 👇';
                            add_filter( 'send_massage_text', function ( $text ) {
                                return tr_num( $text, 'en', '.' );
                            }, 11 );
                            SendMessage(
                                $chat_id, __replace__( $message, [
                                '[[league_name]]' => $league->emoji,
                                '[[league_coin]]' => $league->coin,
                                '[[coin]]'        => user()->coin
                            ] ), $telegram->buildInlineKeyBoard( [
                                [
                                    $telegram->buildInlineKeyboardButton( '✅ تایید خرید', '', 'buy_vip_league-' . $league->id ),
                                    $telegram->buildInlineKeyboardButton( '💰 افزایش سکه', '', 'shop' ),
                                ]
                            ] )
                            );
                            update_status( '' );

                        }
                        else
                        {

                            $message = '⚠️ این لیگ مورد پذیرش نیست، لطفا دوباره امتحان کنید!';
                            SendMessage(
                                $chat_id, $message, $telegram->buildInlineKeyBoard( [
                                [
                                    $telegram->buildInlineKeyboardButton( '📛 انصراف', '', 'profile' ),
                                ],
                                [
                                    $telegram->buildInlineKeyboardButton( '➕درخواست آزاد کردن لیگ', '', 'releae_league-' . $text ),
                                ]
                            ] )
                            );

                        }

                    }
                    else
                    {
                        $message = '⚠️ خطا، شما فقط مجاز به ارسال یک ایموجی هستید!';
                        Message();
                    }

                }
                else
                {
                    $message = '⚠️ خطا، تنها مجاز به ارسال ایموجی هستید!';
                    Message();
                }

                break;

            case 'get_send_message':
                if ( ! is_null( $text ) && is_string( $text ) )
                {
                    if ( mb_strlen( $text, 'UTF-8' ) <= 250 )
                    {
                        $message_id = add_prive_chat( data(), ( get_game()->server_id ?? 0 ), $text );
                        $message    = '📨 پیش نمایش ' . "<u>پیام خصوصی</u>" . ' به [[user]]' . "\n \n" . $text;
                        SendMessage(
                            $chat_id, __replace__( $message, [
                            '[[user]]' => user( data() )->name
                        ] ), $telegram->buildInlineKeyBoard( [
                            [
                                $telegram->buildInlineKeyboardButton( '✖️انصراف', '', 'cancel_2' ),
                                $telegram->buildInlineKeyboardButton( '✔️ ارسال', '', 'send_message-' . $message_id ),
                            ]
                        ] ), null, 'html'
                        );
                        update_status( 'reset' );
                    }
                    else
                    {
                        $message = '⚠️ حداکثر تعداد کاراکتر برای ارسال میتواند 250 تا باشد.';
                        Message();
                    }
                }
                else
                {
                    $message = '⚠️ پیام شما فقط میتواند فارسی باشد.';
                    Message();
                }
                break;

            case 'get_coupon_code':

                $coupon = get_coupon( trim( $text ) );
                if ( isset( $coupon ) && ( is_null( $coupon->time ) || $coupon->time >= time() ) && ! is_user_used_coupon( $chat_id, $coupon->name ) )
                {
                    /** @var $user User */
                    $point = (int) $coupon->rang;
                    if ( $user->get_point_daily_today() >= $point )
                    {

                        if ( $coupon->user == 1 )
                        {

                            delete_coupon( $coupon->name );

                            DeleteMessage( CHNNEL_ID, $coupon->post_id );

                        }
                        elseif ( $coupon->user != 0 )
                        {

                            update_coupon( $coupon->name, [
                                'user' => ( (int) $coupon->user - 1 )
                            ] );

                            $message = '🔔 #کوپن جدید ساخته شد: ' . "\n \n" . "➡️ <code>[[coupon]]</code> ⬅️" . "\n \n";
                            $message .= '➖ تعداد سکه : [[coin]] 💰' . "\n";
                            $message .= '➖ حداقل امتیاز روزانه برای استفاده : [[point]] امتیاز 🌟' . "\n";
                            $message .= '➖ محدودیت تعداد : [[count]] نفر' . "\n";
                            $message .= '➖ مهلت استفاده : <b>[[date]]</b>' . "\n \n";
                            $message .= "<a href='https://t.me/iranimafia/154'>چگونه از کوپن استفاده کنم❓</a>";

                            $telegram->editMessageText( [
                                'chat_id'                  => CHNNEL_ID,
                                'disable_web_page_preview' => true,
                                'message_id'               => $coupon->post_id,
                                'text'                     => __replace__( $message, [
                                    '[[coupon]]' => $coupon->name,
                                    '[[coin]]'   => $coupon->coin,
                                    '[[count]]'  => ( (int) $coupon->user - 1 ),
                                    '[[point]]'  => $coupon->rang,
                                    '[[date]]'   => $coupon->time != null ? tr_num( jdate( 'Y/m/d', $coupon->time ) ) : 'تا پایان امروز',
                                ] ),
                                'reply_markup'             => $telegram->buildInlineKeyBoard( [
                                    [
                                        $telegram->buildInlineKeyboardButton( '♨️ وارد کردن کوپن ♨️', 'https://telegram.me/' . GetMe()->username . '?start=code' )
                                    ]
                                ] ),
                                'parse_mode'               => 'html'
                            ] );

                        }
                        add_coin( $chat_id, $coupon->coin );
                        add_used_coupon( $chat_id, $coupon->name );
                        $message = '🎉 تبریک ، شما تعداد [[coin]] سکه رایگان دریافت کردید.';
                        if ( is_user_row_in_game( $chat_id ) )
                        {
                            update_status( 'reset' );
                        }

                    }
                    else
                    {
                        $message = '⚠️ خطا ! برای استفاده از کوپن باید حداقل ' . $point . ' امتیاز روزانه داشته باشید .';
                    }
                }
                else
                {
                    $message = '❌ این کوپن نامعتبر است!';
                }
                add_filter( 'send_massage_text', function ( $text ) {
                    return tr_num( $text, 'en', '.' );
                }, 11 );
                SendMessage(
                    $chat_id, __replace__( $message, [
                        '[[coin]]' => $coupon->coin ?? 0
                    ] )
                );

                break;

            default:

                $server = is_user_in_which_server( $chat_id );
                if ( isset( $server->id ) )
                {

                    if ( $user->sleep() ) goto ChatNight;
                    switch ( get_server_meta( $server->id, 'status' ) )
                    {
                        case 'light':
                        case 'message':
                            goto ChatNight;
                        case 'voting':
                        case 'court':
                        case 'court-2':
                        case 'court-3':
                            goto VOTING;
                        default:
                            goto PlayingGame;
                    }

                }

                $message = 'منوی اصلی 👇';
                SendMessage( $chat_id, $message, KEY_START_MENU );

                break;

            // ----------------------------- Payment

            case 'get_auth_code':

                $text = str_replace( 'https://irmafiabot.com/payment/pay/', '', $text );
                $text = str_replace( 'http://irmafiabot.com/payment/pay/', '', $text );

                $res = file_get_contents( URL_VERIFY . "?Authority={$text}&Status=OK&bot=" . $BOT_ID );

                if ( $res != false )
                {

                    $message = 'پرداخت شما تایید شد✅' . "\n \n";
                    $message .= 'روز خوبی داشته باشید🤝';

                }
                else
                {

                    $message = '⚠️ پرداخت شما تایید یا رد شده است' . "\n \n";
                    $message .= '✅ و نیاز به پیگیری تراکنش ندارد';

                }

                $user->setStatus( '' )->SendMessageHtml( $message );

                break;

        }

        break;
}