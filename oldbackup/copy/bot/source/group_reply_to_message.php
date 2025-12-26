<?php
/** @noinspection ALL */


if ( preg_match( '/سکه+/', $text ) && preg_match( "/^\d+ سکه$/", tr_num( $text ) ) && preg_match( '/\d+/', tr_num( $text ), $coin ) )
{

    if ( $BOT_ID == 0 )
    {

        if ( isset( $message->from->id ) )
        {
            $from_id = $message->from->id;
        }

        if ( is_numeric( $from_id ) )
        {

            if ( user_exists( $from_id ) && isset( $message->reply_to_message->from->id ) && is_numeric( $message->reply_to_message->from->id ) && user_exists( $message->reply_to_message->from->id ) )
            {


                $user   = new \library\User( $from_id );
                $number = intval( $coin[0] );
                if ( $number >= 1 )
                {

//                    if ( $user->move_coin($message->reply_to_message->from->id, $number) )
//                    {

                    $message = 'مطمعنی میخوای [[number]] سکه بفرستی برای ' . "<u>[[user]]</u>" . '؟';
                    SendMessage(
                        $update->message->chat->id, __replace__( $message, [
                        '[[user]]'   => user( $update->message->reply_to_message->from->id )->name,
                        '[[number]]' => $number
                    ] ), $telegram->buildInlineKeyBoard( [
                        [
                            $telegram->buildInlineKeyboardButton( '✖️ انصراف', '', 'cancel-' . $user->getUserId() ),
                            $telegram->buildInlineKeyboardButton( '✅آره ، مطمئنم', '', 'move_coin-' . $user->getUserId() . '-' . $number . '-' . $update->message->reply_to_message->from->id ),
                        ]
                    ] ), null, 'html'
                    );

                    /*$message = '🪙 ' . "<u><b>" . '[[coin]] سکه ' . "</b></u>" . ' از طرف شما برای [[user]] ارسال شد ✅';
                    SendMessage($update->message->chat->id, __replace__($message, [
                        '[[coin]]' => $number,
                        '[[user]]' => "<u>" . user($update->message->reply_to_message->from->id)->name . "</u>"
                    ]), null, null, 'html');*/

//                    }

                }

            }

        }

    }

}


if ( $text == 'پروفایل' || $text == '/profile' || $text == '/profile@iranimafiabot' )
{

    if ( $BOT_ID == 0 )
    {

        if ( isset( $message->from->id ) )
        {
            $from_id = $message->from->id;
        }

        if ( is_numeric( $from_id ) )
        {

            if ( user_exists( $from_id ) && isset( $message->reply_to_message->from->id ) && is_numeric( $message->reply_to_message->from->id ) && user_exists( $message->reply_to_message->from->id ) )
            {

                if ( check_ban( $from_id ) )
                {

                    $friend = new \library\User( $message->reply_to_message->from->id );

                    if ( $friend->get_meta( 'privacy' ) == 'unlook' )
                    {

                        $game_count  = $friend->getCountGame();
                        $opration    = $friend->getResultWinGame();
                        $role        = $friend->get_meta( 'role' );
                        $point       = $friend->get_point();
                        $user_league = $friend->get_league();

                        $dice_user = intval( $friend->get_meta( 'dice-count' ) );

                        $message = '💢 پروفایل بازیکن: ' . "<b><u>" . $friend->user()->name . "</u></b>" . "\n \n";
                        $message .= '➖ نام : ' . $friend->user()->name . "\n";
                        $message .= '➖ امتیاز : ' . $point . "\n";
                        $message .= '➖ لیگ : ' . $user_league->icon . "\n";
                        $message .= '➖ رتبه در بازی : ' . ( $point > 0 ? get_rank_user_in_global( $friend->getUserId() ) : 'ندارید' ) . "\n";
                        $message .= '➖ تعداد کل بازی‌ها : ' . intval( $friend->get_meta( 'game-count' ) ) . "\n";
                        $message .= '➖ درصد برد: ' . ( $game_count > 0 ? ceil( $opration ) : 0 ) . '%' . "\n";
                        $message .= '➖ شانس دارت : ' . $dice_user . ' از 5' . "\n";
                        $message .= '➖ نقش مورد علاقه : ' . ( isset( $role ) ? get_role( $role )->icon : 'انتخاب نشده است' ) . "\n";
                        $message .= '➖ جنسیت : ' . $friend->gender();
                        SendMessage( $chat_id, $message, null, null, 'html' );

                    }
                    else
                    {
                        SendMessage( $chat_id, '⚠️ خطا ! حریم خصوصی این کاربر در حالت قفل 🔒 قرار دارد .' );
                    }

                }
                else
                {
                    SendMessage( $chat_id, '⚠️ خطا ! اکانت شما در حال حاضر مسدود است و نمی‌توانید از این قابلیت استفاده کنید.' );
                }


            }

        }

    }

}