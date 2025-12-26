<?php

/** @noinspection ALL */

use library\User;

if ( isset( $message->reply_to_message->entities ) && isset( $message->reply_to_message->entities[0] ) && $message->reply_to_message->entities[0]->type == 'text_link' )
{
    $address_link_hash = tr_num( $message->reply_to_message->entities[0]->url );
    $address_link      = explode( '=', $address_link_hash );
    $address_link      = end( $address_link );
    $user_id           = string_decode( $address_link );

    if ( isset( $user_id ) && !empty( $user_id ) && is_numeric( $user_id ) && user_exists( $user_id ) && $chat_id != $user_id )
    {

        if ( isset( $text ) && is_string( $text ) )
        {

            $user = new User( $chat_id );

            if ( preg_match( '/🪄 جادو حقیقت:/', $update->message->reply_to_message->text ) && !in_array( $text, [ 'گ', 'گزارش', '/report' ] ) ) die();

            switch ( $text )
            {

                case '/magic3':

                    if ( $user->user_on_game() )
                    {

                        $server = $user->server();

                        if ( $user->is( ADMIN_LOG ) || add_magic( $server->getId(), $user->getUserId(), 0 ) )
                        {
                            $user_magic = new User( $user_id, $server->getId() );


                            if ( has_coin( $user->getUserId(), 5 ) )
                            {

                                if ( add_magic( $server->getId(), $user_magic->getUserId(), 3 ) )
                                {

                                    if ( demote_coin( $user->getUserId(), 5 ) )
                                    {

                                        $message = "📯<b><u>جادوی محفوظ</u></b>  ، فعال شد ✅";
                                        $user->SendMessageHtml( $message );
                                        add_server_meta( $server->getId(), 'shield', 'on', $user_magic->getUserId() );
                                        $message = "📯 " . "<u><b>" . $user->get_name() . "</b></u>" . " جادوی " . "<b>محفوظ</b>" . " را برای شما فعال کرد ✅";
                                        $user_magic->SendMessageHtml( $message );

                                    }
                                    else
                                    {

                                        $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                        $user->SendMessageHtml( $message );

                                    }

                                }
                                else
                                {
                                    $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';
                                    $user->SendMessageHtml( $message );
                                }

                            }
                            else
                            {

                                $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                $user->SendMessageHtml( $message );

                            }


                        }
                        else
                        {

                            $user->SendMessageHtml( '⛔️ تنها یک بار میتوانید برای دیگران جادو فعال کنید.' );

                        }

                    }
                    else
                    {

                        $user->SendMessageHtml( '⛔️ شما داخل هیچ بازی نیستید' );

                    }

                    break;

                case '/magic4':

                    if ( $user->user_on_game() )
                    {

                        $server = $user->server();

                        if ( $user->is( ADMIN_LOG ) || add_magic( $server->getId(), $user->getUserId(), 0 ) )
                        {

                            $user_magic = new User( $user_id, $server->getId() );


                            if ( has_coin( $user->getUserId(), 4 ) )
                            {

                                if ( add_magic( $server->getId(), $user_magic->getUserId(), 4 ) )
                                {

                                    if ( demote_coin( $user->getUserId(), 4 ) )
                                    {

                                        $message = "📯 <b><u>جادوی حذف رای</u></b>  ، فعال شد ✅";
                                        $user->SendMessageHtml( $message );
                                        add_server_meta( $server->getId(), 'no-vote', 'on', $user_magic->getUserId() );
                                        $message = "📯 " . "<u><b>" . $user->get_name() . "</b></u>" . " جادوی " . "<b>حذف رای</b>" . " را برای شما فعال کرد ✅";
                                        $user_magic->SendMessageHtml( $message );

                                    }
                                    else
                                    {
                                        $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                        $user->SendMessageHtml( $message );
                                    }

                                }
                                else
                                {

                                    $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';
                                    $user->SendMessageHtml( $message );

                                }

                            }
                            else
                            {
                                $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                $user->SendMessageHtml( $message );
                            }

                        }
                        else
                        {

                            $user->SendMessageHtml( '⛔️ تنها یک بار میتوانید برای دیگران جادو فعال کنید.' );

                        }

                    }
                    else
                    {

                        $user->SendMessageHtml( '⛔️ شما داخل هیچ بازی نیستید' );

                    }

                    break;

                case '/magic5':

                    if ( $user->user_on_game() )
                    {

                        $server = $user->server();

                        if ( $user->is( ADMIN_LOG ) || add_magic( $server->getId(), $user->getUserId(), 0 ) )
                        {

                            $user_magic = new User( $user_id, $server->getId() );


                            if ( get_server_meta( $server->getId(), 'is' ) != 'on' )
                            {

                                if ( has_coin( $user->getUserId(), 4 ) )
                                {

                                    if ( add_magic( $server->getId(), $user_magic->getUserId(), 5 ) )
                                    {

                                        if ( demote_coin( $user->getUserId(), 4 ) )
                                        {

                                            $message = "📯<b><u>جادوی جاسوس</u></b>  ، فعال شد ✅";
                                            $user->SendMessageHtml( $message );
                                            add_server_meta( $server->getId(), 'warning', 'on', $user_magic->getUserId() );
                                            $message = "📯 " . "<u><b>" . $user->get_name() . "</b></u>" . " جادوی " . "<b>جاسوس</b>" . " را برای شما فعال کرد ✅";
                                            $user_magic->SendMessageHtml( $message );

                                        }
                                        else
                                        {
                                            $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                            $user->SendMessageHtml( $message );
                                        }

                                    }
                                    else
                                    {

                                        $message = '⚠️ شما فقط یک بار میتوانید از این جادو استفاده کنید .';
                                        $user->SendMessageHtml( $message );

                                    }

                                }
                                else
                                {
                                    $message = 'شما سکه کافی برای استفاده از این جادو را ندارید .';
                                    $user->SendMessageHtml( $message );
                                }

                            }
                            else
                            {

                                $user->SendMessageHtml( '⚠️ مجددا امتحان کنید' );

                            }

                        }
                        else
                        {

                            $user->SendMessageHtml( '⛔️ تنها یک بار میتوانید برای دیگران جادو فعال کنید.' );

                        }

                    }
                    else
                    {

                        $user->SendMessageHtml( '⛔️ شما داخل هیچ بازی نیستید' );

                    }

                    break;

                case 'گ':
                case 'گزارش':
                case '/report':

                    if ( is_user_row_in_game( $chat_id ) )
                    {

                        $message = 'نوع تخلف [[user]] را مشخص کنید.';
                        $is      = 0;

                        if ( preg_match( '/🪄 جادو حقیقت:/', $update->message->reply_to_message->text ) )
                        {

                            $message = 'نوع تخلف را مشخص کنید.';
                            SendMessage(
                                $chat_id, __replace__( $message, [
                                '[[user]]' => "<u>" . user( $user_id )->name . "</u>"
                            ] ), $telegram->buildInlineKeyBoard( [
                                [
                                    $telegram->buildInlineKeyboardButton( 'تقلب دربازی', '', 'wg_2-' . $chat_id . '-' . $user_id . '-' . apply_filters( 'filter_report_name', 'تقلب در بازی' ) . '-1' )
                                ],
                                [
                                    $telegram->buildInlineKeyboardButton( '⛔️ انصراف', '', 'cancel' )
                                ]
                            ] ), null, 'html'
                            );

                        }
                        else
                        {

                            SendMessage(
                                $chat_id, __replace__( $message, [
                                '[[user]]' => "<u>" . user( $user_id )->name . "</u>"
                            ] ), $telegram->buildInlineKeyBoard( [
                                [
                                    $telegram->buildInlineKeyboardButton( 'استفاده از الفاظ رکیک', '', 'wg-' . $chat_id . '-' . $user_id . '-' . apply_filters( 'filter_report_name', 'استفاده از الفاظ رکیک' ) . '-' . $is )
                                ],
                                [
                                    $telegram->buildInlineKeyboardButton( 'تقلب در بازی', '', 'wg-' . $chat_id . '-' . $user_id . '-' . apply_filters( 'filter_report_name', 'تقلب در بازی' ) . '-' . $is )
                                ],
                                [
                                    $telegram->buildInlineKeyboardButton( 'لو دادن نقش خود یا دیگران', '', 'wg-' . $chat_id . '-' . $user_id . '-' . apply_filters( 'filter_report_name', 'لو دادن نقش خود یا دیگران' ) . '-' . $is )
                                ],
                                [
                                    $telegram->buildInlineKeyboardButton( 'ارسال شماره یا آیدی ', '', 'wg-' . $chat_id . '-' . $user_id . '-' . apply_filters( 'filter_report_name', 'ارسال شماره یا آیدی' ) . '-' . $is )
                                ],
                                [
                                    $telegram->buildInlineKeyboardButton( 'ایجاد اختلال در نظم بازی', '', 'wg-' . $chat_id . '-' . $user_id . '-' . apply_filters( 'filter_report_name', 'ایجاد اختلال در نظم بازی' ) . '-' . $is )
                                ],
                                [
                                    $telegram->buildInlineKeyboardButton( 'تبلیغات', '', 'wg-' . $chat_id . '-' . $user_id . '-' . apply_filters( 'filter_report_name', 'تبلیغات' ) . '-' . $is )
                                ],
                                [
                                    $telegram->buildInlineKeyboardButton( 'اسم نامتعارف', '', 'wg-' . $chat_id . '-' . $user_id . '-' . apply_filters( 'filter_report_name', 'اسم نامتعارف' ) . '-' . $is )
                                ],
                                [
                                    $telegram->buildInlineKeyboardButton( '⛔️ انصراف', '', 'cancel' )
                                ],
                                /*[
                                    $telegram->buildInlineKeyboardButton('سایر موارد - ارسال به پشتیبانی', '', 'wg-سایر موارد - ارسال به پشتیبانی')
                                ],*/
                            ] ), null, 'html'
                            );

                        }

                    }
                    else
                    {

                        $message = '❌ شما داخل هیچ سروری نیستید.';
                        Message();

                    }

                    break;

                case '/friend':
                case '/request':
                case 'درخواست':

                    if ( $user->user_on_game() )
                    {

                        $server  = $user->server();
                        $request = intval( $server->setUserId( $user->getUserId() )->getMetaUser( 'request' ) );
                        if ( $request < 2 )
                        {

                            if ( !$user->isFriend( $user_id ) )
                            {

                                $friends = $user->countFriendRequest();
                                if ( $friends < 5 )
                                {

                                    $user->requestFriend( $user_id );
                                    $friend = new User( $user_id, $server->getId() );

                                    if ( $friend->get_meta( 'status' ) != 'hide' )
                                    {

                                        $message = '✉️ شما یک درخواست دوستی از طرف ' . "<b><u>" . $user->get_league()->emoji . $user->user()->name . "</u></b>" . ' دارید❗️' . "\n \n";
                                        $message .= '🔖 آیا درخواست دوستی او را قبول میکنید؟';
                                        $friend->setKeyboard(
                                            $telegram->buildInlineKeyBoard( [
                                                [
                                                    $telegram->buildInlineKeyboardButton( '✅ قبول میکنم', '', 'accept_request_add_friend-' . $user->getUserId() . '-0' ),
                                                    $telegram->buildInlineKeyboardButton( 'رد کردن ❌', '', 'reject_request_add_friend-' . $user->getUserId() ),
                                                ]
                                            ] )
                                        )->SendMessageHtml( $message );
                                        $message = 'درخواست دوستی شما برای ' . "<b><u>" . $friend->user()->name . "</u></b>" . ' ارسال شد✅';
                                        $server->setUserId( $user->getUserId() )->updateMetaUser( 'request', $request + 1 );

                                    }
                                    else
                                    {
                                        throw new ExceptionWarning( 'نمیتوانید به این کاربر درخواست دوستی ارسال کنید.' );
                                    }

                                }
                                else
                                {

                                    $message = '⚠️ شما هم اکنون 5 درخواست دوستی ارسال کردید.' . "\n";
                                    $message .= 'برای اضافه کردن دوست جدید لازم است 50 سکه پرداخت کنید ، آیا میخواهید این کار را ادامه دهید ؟';
                                    $user->setKeyboard(
                                        $telegram->buildInlineKeyBoard( [
                                            [
                                                $telegram->buildInlineKeyboardButton( '✅ ارسال درخواست', '', 'request_add_friend-' . $user_id ),
                                                $telegram->buildInlineKeyboardButton( 'انصراف ❌', '', 'cancel' ),
                                            ]
                                        ] )
                                    );

                                }
                                $user->SendMessageHtml( $message );

                            }
                            else
                            {
                                throw new ExceptionWarning( 'این کاربر قبلا جز لیست دوستان شما می باشد.' );
                            }


                        }
                        else
                        {
                            throw new ExceptionWarning( 'شما تنها میتوانید 2 درخواست دوستی ارسال کنید.' );
                        }


                    }
                    else
                    {

                        throw new ExceptionError( 'شما داخل هیچ سروری نیستید.' );

                    }

                    break;

                default:

                    if ( ( $chat_id == ADMIN_LOG || $chat_id == ADMIN_ID ) && ( preg_match( '/\/ban+/', $text ) || preg_match( '/مسدود+/', $text ) ) )
                    {

                        $time_ban  = 1;
                        $data_text = explode( ' ', $text );
                        if ( isset( $data_text[1] ) && is_numeric( $text ) )
                        {
                            $time_ban = $data_text[1];
                        }

                        /*     if ( is_user_row_in_game($chat_id) )
                             {

                                 $server = new Server($server->id);

                                 $user_name = user($user_id)->name;
                                 foreach ( $server->users() as $item )
                                 {

                                     $message = '🚫 بنا به درخواست ادمین کاربر ' . "<u>" . $user_name . "</u>" . ' مسدود شد!';

                                     if ( $item->is($user_id) )
                                     {

                                         $message = '🚫 شما به درخواست ادمین به مدت ' . "<u>" . $time_ban . " ساعت</u>" . ' مسدود شدید.';
                                         $item->setKeyboard(KEY_START_MENU)->SendMessageHtml($message)->logout()->kill()->baned(time(), time() + ( 3600 * $time_ban ), $chat_id);

                                     }
                                     else
                                     {

                                         $item->SendMessageHtml($message);

                                     }


                                 }

                             }
                             else
                             {*/

                        $user_select = new User( $user_id );
                        $message     = '🚫 شما به درخواست ادمین به مدت ' . "<u>" . $time_ban . " ساعت</u>" . ' مسدود شدید.';
                        $user_select->setKeyboard( KEY_START_MENU )->SendMessageHtml( $message )->logout()->baned( time(), time() + ( 3600 * $time_ban ), $chat_id );
                        $message = '🚫 بنا به درخواست ادمین کاربر ' . "<u>" . $user_select->user()->name . "</u>" . ' مسدود شد!';
                        SendMessage( $chat_id, $message, null, null, 'html' );

//            }

                    }
                    elseif ( ( $chat_id == ADMIN_LOG || $chat_id == ADMIN_ID ) && $text == '/unban' || $text == 'ان بن' )
                    {

                        $user_select = new User( $user_id );
                        $message     = '🌐 پیام سرور :' . "\n \n";
                        $message     .= '⏱ زمان مسدودیت اکانت شما به پایان رسید.' . "\n";
                        $message     .= '🔸 ' . "<u>لطفا به قوانین ربات پایبند باشید</u>" . ' 🌷' . "\n \n";
                        $message     .= '➖ درصورت نیاز نام خود را در بازی عوض کنید .' . "\n";
                        $message     .= 'قوانین ربات :  /ghavanin';
                        $user_select->unban()->SendMessageHtml( $message );

                        $message = '♨️ کاربر از مسدودیت خارج شد.';
                        SendMessage( $chat_id, $message );

                    }
                    elseif ( $text == 'کاربر' || $text == 'ایدی' && ( $chat_id == ADMIN_LOG || $chat_id == ADMIN_ID ) )
                    {

                        $telegram->sendMessage( [
                            'chat_id' => $chat_id,
                            'text'    => $user_id
                        ] );

                    }
                    elseif ( preg_match( '/سکه+/', $text ) && preg_match( "/^\d+ سکه$/", tr_num( $text ) ) && preg_match( '/\d+/', tr_num( $text ), $coin ) )
                    {

                        if ( $user_id == ADMIN_LOG )
                        {
                            throw new ExceptionWarning( 'ارسال سکه به کاربر مسدود شده است.' );
                        }

                        $number = $coin[0];
                        if ( $number >= 1 )
                        {
                            $message = 'مطمعنی میخوای [[number]] سکه بفرستی برای ' . "<u>[[user]]</u>" . '؟';
                            __replace__( $message, [
                                '[[user]]'   => user( $user_id )->name,
                                '[[number]]' => $number
                            ] );

                            SendMessage(
                                $chat_id, $message, $telegram->buildInlineKeyBoard( [
                                [
                                    $telegram->buildInlineKeyboardButton( '✖️ انصراف', '', 'cancel' ),
                                    $telegram->buildInlineKeyboardButton( '✅آره ، مطمئنم', '', 'move_coin-' . $number . '-' . $user_id ),
                                ],
                                [
                                    $telegram->buildInlineKeyboardButton( '💬 ارسال ناشناس', '', 'move_coin_anonymous-' . $number . '-' . $user_id ),
                                ]
                            ] ), null, 'html'
                            );
                        }

                    }
                    else
                    {

                        if ( $user->is_ban() )
                        {

                            $server     = is_user_in_which_server( $chat_id );
                            $message_id = add_prive_chat( $user_id, $server->id ?? - 2, $text );
                            $message    = '📨 پیش نمایش ' . "<u>پیام خصوصی</u>" . ' به [[user]]' . "\n \n" . $text;
                            SendMessage(
                                $chat_id, __replace__( $message, [
                                '[[user]]' => user( $user_id )->name
                            ] ), $telegram->buildInlineKeyBoard( [
                                [
                                    $telegram->buildInlineKeyboardButton( '✖️انصراف', '', 'cancel' ),
                                    $telegram->buildInlineKeyboardButton( '✔️ ارسال', '', 'send_message-' . $message_id ),
                                ]
                            ] ), null, 'html'
                            );

                        }
                        else
                        {
                            throw new ExceptionWarning( 'شما مسدود هستید.' );
                        }

                    }

                    break;

            }

        }


        exit();

    }

}
