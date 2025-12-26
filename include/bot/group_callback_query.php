<?php

/** @noinspection ALL */

switch ( $data[0] )
{
    case 'ban':
        $report_id = add_report( $fromid, $data[1], 0, 'ایجاد اختلال در نظم بازی' );
        $report    = get_report_by_id( $report_id );
        $message   = 'مدت زمان بلاک شدن کاربر را مشخص کنید.';
        $keyboard  = [
            [
                $telegram->buildInlineKeyboardButton( '۱ ساعته', '', 'blocked-+1 hour-' . $report_id ),
                $telegram->buildInlineKeyboardButton( '۳ ساعته', '', 'blocked-+3 hour-' . $report_id )
            ],
            [
                $telegram->buildInlineKeyboardButton( '۶ ساعته', '', 'blocked-+6 hour-' . $report_id ),
                $telegram->buildInlineKeyboardButton( '۱۲ ساعته', '', 'blocked-+12 hour-' . $report_id )
            ],
            [ $telegram->buildInlineKeyboardButton( '۲۴ ساعته', '', 'blocked-+24 hour-' . $report_id ) ],
        ];
        if ( $fromid == ADMIN_ID )
        {
            $keyboard = array_merge( $keyboard, [
                [
                    $telegram->buildInlineKeyboardButton( '۳ روزه', '', 'blocked-+3 day-' . $report_id ),
                    $telegram->buildInlineKeyboardButton( 'یک هفته', '', 'blocked-+7 day-' . $report_id )
                ],
                [
                    $telegram->buildInlineKeyboardButton( 'یک ماه', '', 'blocked-+30 day-' . $report_id ),
                    $telegram->buildInlineKeyboardButton( 'یک سال', '', 'blocked-+365 day-' . $report_id )
                ]
            ] );
        }
        $keyboard = array_merge( $keyboard, [
            [ $telegram->buildInlineKeyboardButton( '⚠️ اخطار', '', 'warning-' . $report_id ), ],
            [ $telegram->buildInlineKeyboardButton( '🔙 برگشت منو قبل', '', 'wg-' . $report->user_id . '-' . $report->user_reported . '-x-' . $data[1] ) ],
        ] );
        EditMessageText( $chatid, $messageid, $message, $telegram->buildInlineKeyBoard( $keyboard ) );
        break;

    case 'reject':
        $report = get_report_by_id( $data[1] );

        $reports = get_report_by_server( $report->server_id, $report->user_reported );
        if ( count( $reports ) > 0 )
        {
            /* @var $reportX \helper\Report */
            /* @var $report \helper\Report */
            foreach ( $reports as $reportX )
            {
                $message = '⚠️ گزارش تخلف [[user]] رد شد .' . "\n \n" . '❗️ درصورت ارسال گزارش اشتباه در دفعات بعد ، خودتان مسدود خواهید شد .';
                SendMessage( $reportX->user_id, __replace__( $message, [ '[[user]]' => "<u>" . user( $reportX->user_reported )->name . "</u>" ] ), null, null, 'html' );
            }
        }
        else
        {
            $message = '⚠️ گزارش تخلف [[user]] رد شد .' . "\n \n" . '❗️ درصورت ارسال گزارش اشتباه در دفعات بعد ، خودتان مسدود خواهید شد .';
            SendMessage( $report->user_id, __replace__( $message, [ '[[user]]' => "<u>" . user( $report->user_reported )->name . "</u>" ] ), null, null, 'html' );
        }

        $message = '✅ گزارش [[user]] بررسی شد ' . "\n";
        $message .= "نتیجه : \n ❌ رد گزارش و اخطار به گزارش کننده ." . "\n \n";
        if ( isset( $fromid ) )
        {
            $message .= 'بررسی شده توسط :' . "\n";
            $message .= '👤 : [[admin]]';
        }
        $chats = get_chats( $report->user_reported, 1 );
        EditMessageText(
            $chatid, $messageid, __replace__( $message, [
            '[[user]]'  => '<a href="tg://user?id=' . $report->user_reported . '">' . user( $report->user_reported )->name . '</a>',
            '[[admin]]' => !isset( $fromid ) ? '' : '<a href="tg://user?id=' . $fromid . '">' . $first_name . '</a>'
        ] ), $telegram->buildInlineKeyBoard( [ [ $telegram->buildInlineKeyboardButton( '♻️ پیگیری مجدد گزارش', '', 'get_messages-' . $chats[0]->id . '-' . $report->user_reported ) ] ] ), null, 'html'
        );
        break;

    case 'get_messages':
        if ( $fromid == ADMIN_ID || $fromid == ADMIN_LOG )
        {
            $chats = get_chats_from_id_by_user( $data[2], $data[1] );

            $message = 'گزارش چت کاربر [[user]]:' . "\n \n";
            if ( count( $chats ) > 0 )
            {
                $count = 0;
                foreach ( $chats as $item )
                {
                    if ( is_numeric( $item->to_user ) )
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
                        break;
                    }
                }
                if ( mb_strlen( $message, 'utf8' ) < 3850 )
                {
                    $message .= "\n" . 'تعداد چت های ارسال شده: ' . $count;
                }
            }
            else
            {
                $message = 'متاسفانه چت های این کاربر در دیتابیس حذف شده است.';
            }


            if ( isset( $callback_query->message->entities[1] ) )
            {
                $user_id = $callback_query->message->entities[1]->user->id;
                EditMessageText( $chat_id, $messageid, $message, $telegram->buildInlineKeyBoard( [ [ $telegram->buildInlineKeyboardButton( '🔙 برگشت به حالت قبلی', '', 'back_to_home-' . $data[2] . '-' . $user_id ) ] ] ) );
            }
            else
            {
                EditMessageText( $chat_id, $messageid, $message, $telegram->buildInlineKeyBoard( [ [ $telegram->buildInlineKeyboardButton( '🔙 برگشت به حالت قبلی', '', 'back_to_home' . $data[2] ) ] ] ) );
            }
        }
        else
        {
            AnswerCallbackQuery( $dataid, '⛔️ شما دسترسی ندارید.' );
        }
        break;

    case 'back_to_home':
        $message = '✅ گزارش [[user]] بررسی شد ' . "\n \n";
        if ( isset( $data[2] ) )
        {
            $message .= 'بررسی شده توسط :' . "\n";
            $message .= '👤 : [[admin]]';
        }

        EditMessageText(
            $chatid, $messageid, __replace__( $message, [
            '[[user]]'  => '<a href="tg://user?id=' . $data[1] . '">' . user( $data[1] )->name . '</a>',
            '[[admin]]' => isset( $data[2] ) ? '<a href="tg://user?id=' . $data[2] . '">' . GetChat( $data[2] )->first_name . '</a>' : '<a href="tg://user?id=' . $fromid . '">' . $first_name . '</a>'
        ] ), null, null, 'html'
        );

        break;

    case 'blocked':

        $time   = strtotime( $data[1] );
        $report = get_report_by_id( $data[2] );


        $message = '✅ گزارش [[user]] بررسی شد ' . "\n";
        $message .= "نتیجه : \n  اعمال مسدودی 🚫" . "\n \n";
        $message .= "⏱ مدت زمان: [[date]]" . "\n \n";
        if ( isset( $fromid ) )
        {
            $message .= 'بررسی شده توسط :' . "\n";
            $message .= '👤 : [[admin]]';
        }
        $chats = get_chats( $report->user_reported, 1 );
        EditMessageText(
            $chatid, $messageid, __replace__( $message, [
            '[[user]]'  => '<a href="tg://user?id=' . $report->user_reported . '">' . user( $report->user_reported )->name . '</a>',
            '[[admin]]' => !isset( $fromid ) ? '' : '<a href="tg://user?id=' . $fromid . '">' . $first_name . '</a>',
            '[[date]]'  => "<u>" . __replace__( $data[1], [
                    '+'       => '',
                    'hour'    => 'ساعت',
                    '365 day' => 'یک سال',
                    '30 day'  => 'یک ماه',
                    '7 day'   => 'یک هفته',
                    'day'     => 'روز',
                ] ) . "</u>"
        ] ), $telegram->buildInlineKeyBoard( [ [ $telegram->buildInlineKeyboardButton( '♻️ پیگیری مجدد گزارش', '', 'get_messages-' . $chats[0]->id . '-' . $report->user_reported ) ] ] ), null, 'html'
        );

        remove_filter( 'filter_token', 'set_first_token' );

        add_ban( $report->user_reported, time(), $time, $data[2] );
        $message = '🚫 شما برای [[date]] مسدود شدید.' . "\n \n";
        $message .= '🔸 علت: [[wg]]' . "\n \n";
        $message .= '⚠️ لطفا قوانین ربات را بخوانید 👈 /ghavanin';
        SendMessage(
            $report->user_reported, __replace__( $message, [
            '[[wg]]'   => "<u>" . ( strlen( $report->type ) <= 3 ? apply_filters( 'filter_report_name', $report->type ) : $report->type ) . "</u>",
            '[[date]]' => "<u>" . __replace__( $data[1], [
                    '+'       => '',
                    'hour'    => 'ساعت',
                    '365 day' => 'یک سال',
                    '30 day'  => 'یک ماه',
                    '7 day'   => 'یک هفته',
                    'day'     => 'روز',
                ] ) . "</u>"
        ] ), KEY_START_MENU, null, 'html'
        );
        update_status( '', $report->user_reported );
        update_report( $report->user_reported, $report->server_id, [ 'status' => 'close_by_admin' ] );
        leave_server( $report->user_reported );

        // =========================================================================
        if ( $report->user_id != $report->user_reported && $report->user_id != ADMIN_ID )
        {
            $reports = get_report_by_server( $report->server_id, $report->user_reported );
            if ( count( $reports ) > 0 )
            {
                /* @var $report \helper\Report */
                foreach ( $reports as $reportX )
                {
                    $message = 'گزارش شما تایید شد ✅' . "\n";
                    $message .= '💯 [[user]] به علت گزارش شما مسدود شد .' . "\n \n";
                    $message .= 'پاداش شما : ' . "<b>" . ( empty( $reportX->note ) ? 'سه' : 'چهار' ) . ' امتیاز' . "</b>" . ' 🌟' . "\n \n";
                    $message .= 'از همکاری شما متشکریم 🌷' . "\n" . 'پشتیبانی';
                    /*$message = '🌐 پیام سرور :' . "\n \n";
                    $message .= '🔹' . '[[user]] به دلیل گزارش شما مسدود شد .' . "\n \n";
                    $message .= '🔸' . "<u>" . ' ' . ( empty($reportX->note) ? 'سه' : 'چهار' ) . ' امتیاز ' . "</u>" . ' برای شما اضافه شد .' . "\n \n";
                    $message .= 'از همکاری شما ممنونیم 🌷🤝';*/
                    SendMessage( $reportX->user_id, __replace__( $message, [ '[[user]]' => user( $reportX->user_reported )->name ] ), null, null, 'html' );
                    add_point( $reportX->server_id, $reportX->user_id, ( empty( $reportX->note ) ? 3 : 4 ) );
                }
            }
            else
            {
                /*$message = '🌐 پیام سرور :' . "\n \n";
                $message .= '🔹' . '[[user]] به دلیل گزارش شما مسدود شد .' . "\n \n";
                $message .= '🔸' . "<u>" . ' سه امتیاز ' . "</u>" . ' برای شما اضافه شد .' . "\n \n";
                $message .= 'از همکاری شما ممنونیم 🌷🤝';*/
                $message = 'گزارش شما تایید شد ✅' . "\n";
                $message .= '💯 [[user]] به علت گزارش شما مسدود شد .' . "\n \n";
                $message .= 'پاداش شما : ' . "<b>" . ( empty( $reportX->note ) ? 'سه' : 'چهار' ) . ' امتیاز' . "</b>" . ' 🌟' . "\n \n";
                $message .= 'از همکاری شما متشکریم 🌷' . "\n" . 'پشتیبانی';
                SendMessage( $report->user_id, __replace__( $message, [ '[[user]]' => user( $report->user_reported )->name ] ), null, null, 'html' );
                add_point( $report->server_id, $report->user_id, 3 );
            }
        }


        break;

    case 'warning':
        $report  = get_report_by_id( $data[1] );
        $message = '⚠️ شما به دلیل [[report]] گزارش شده اید .' . "\n \n" . 'در صورت تکرار مسدود خواهید شد .';
        SendMessage( $report->user_reported, __replace__( $message, [ '[[report]]' => "<u>" . strlen( $report->type ) <= 3 ? apply_filters( 'filter_report_name', $report->type ) : $report->type . "</u>" ] ), null, null, 'html' );


        $message = '✅ گزارش [[user]] بررسی شد ' . "\n";
        $message .= "نتیجه : \n  ارسال اخطار ⚠️" . "\n \n";
        if ( isset( $fromid ) )
        {
            $message .= 'بررسی شده توسط :' . "\n";
            $message .= '👤 : [[admin]]';
        }
        $chats = get_chats( $report->user_reported, 1 );
        EditMessageText(
            $chatid, $messageid, __replace__( $message, [
            '[[user]]'  => '<a href="tg://user?id=' . $report->user_reported . '">' . user( $report->user_reported )->name . '</a>',
            '[[admin]]' => !isset( $fromid ) ? '' : '<a href="tg://user?id=' . $fromid . '">' . $first_name . '</a>'
        ] ), $telegram->buildInlineKeyBoard( [ [ $telegram->buildInlineKeyboardButton( '♻️ پیگیری مجدد گزارش', '', 'get_messages-' . $chats[0]->id . '-' . $report->user_reported ) ] ] ), null, 'html'
        );
        break;

    case 'wg':
        /* @var $data [1] User Send Report */
        /* @var $data [2] User Reported */
        /* @var $data [3] type Report */
        if ( $data[3] == 'x' )
        {
            $data[3] = 'ایجاد اختلال در نظم بازی';
        }
        $user          = user( $data[1] );
        $user_reported = user( $data[2] );

        $report = get_report_by_id( $data[4] );

        if ( empty( $report->server_id ) || $report->server_id <= 0 )
        {
            AnswerCallbackQuery( $dataid, 'با عرض پوزش این سرور بسته شده' );
            die();
        }

        $reports    = get_report_by_server( $report->server_id, $data[2] );
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
                    '[[wg]]'      => strlen( $report->type ) <= 3 ? apply_filters( 'filter_report_name', $report->type ) : $report->type . "</u>",
                ] );
                if ( $message_id == null && !empty( $report->message_id ) )
                {
                    $message_id = $report->message_id;
                }
            }
        }
        else
        {

            $message .= '🟩 گزارش کننده : [[user]] `[[user_id]]`' . "\n";
            $message .= '🟨 [[wg]]' . "\n";

        }

        $message .= "\n" . '🟥 گزارش شده : [[user_wg]] `[[user_wg_id]]`' . "\n";
        $message .= '📝 یادداشت : ' . ( !is_note_by_server( $report->server_id ) ? 'ندارد' : 'دارد' ) . "\n";

        EditMessageText(
            GP_MANAGER, $messageid, __replace__( $message, [
            '[[user_wg]]'    => $user_reported->name,
            '[[user]]'       => $user->name,
            '[[user_wg_id]]' => $user_reported->user_id,
            '[[user_id]]'    => $data[2],
            '[[wg]]'         => apply_filters( 'filter_report_name', $data[3] ),
        ] ), $telegram->buildInlineKeyBoard( [ [ $telegram->buildInlineKeyboardButton( '💭 پیام ها ، ⛔️ اعمال مسدودی', '', 'block-' . $data[4] ) ] ] )
            , null,
            'MarkDown'
        );
        break;

    case 'block':
        $report = get_report_by_id( $data[1] );
        if ( empty( $report->user_reported ) )
        {
            throw new ExceptionWarning( 'این گزارش منقضی شده است.' );
        }
        $chats   = array_reverse( get_chats( $report->user_reported ) );
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
                    break;
                }
            }
            if ( mb_strlen( $message, 'utf8' ) < 3850 )
            {
                $message .= "\n" . 'تعداد چت های ارسال شده: ' . $count;
            }
        }
        else
        {
            $message = 'مدت زمان بلاک شدن کاربر را مشخص کنید.';
        }

        $keyboard = [
            [
                $telegram->buildInlineKeyboardButton( '۱ ساعته', '', 'blocked-+1 hour-' . $data[1] ),
                $telegram->buildInlineKeyboardButton( '۳ ساعته', '', 'blocked-+3 hour-' . $data[1] )
            ],
            [
                $telegram->buildInlineKeyboardButton( '۶ ساعته', '', 'blocked-+6 hour-' . $data[1] ),
                $telegram->buildInlineKeyboardButton( '۱۲ ساعته', '', 'blocked-+12 hour-' . $data[1] )
            ],
            [ $telegram->buildInlineKeyboardButton( '۲۴ ساعته', '', 'blocked-+24 hour-' . $data[1] ) ],
        ];
        if ( $fromid == ADMIN_ID )
        {
            $keyboard = array_merge( $keyboard, [
                [
                    $telegram->buildInlineKeyboardButton( '۳ روزه', '', 'blocked-+3 day-' . $data[1] ),
                    $telegram->buildInlineKeyboardButton( 'یک هفته', '', 'blocked-+7 day-' . $data[1] )
                ],
                [
                    $telegram->buildInlineKeyboardButton( 'یک ماه', '', 'blocked-+30 day-' . $data[1] ),
                    $telegram->buildInlineKeyboardButton( 'یک سال', '', 'blocked-+365 day-' . $data[1] )
                ]
            ] );
        }
        $report   = get_report_by_id( $data[1] );
        $keyboard = array_merge( $keyboard, [
            [
                $telegram->buildInlineKeyboardButton( '⚠️ اخطار', '', 'warning-' . $report->id ),
                $telegram->buildInlineKeyboardButton( '❌ رد گزارش', '', 'reject-' . $report->id ),

                $telegram->buildInlineKeyboardButton( '📝 حذف اسم', '', 'delete_name-' . $report->id ),
            ],
            [ $telegram->buildInlineKeyboardButton( '🔙 برگشت منو قبل', '', 'wg-' . $report->user_id . '-' . $report->user_reported . '-' . $report->type . '-' . $data[1] ) ],
        ] );

//        SendMessage($chat_id,json_encode($report),null,null,'html');
        if ( is_note_for_user_by_server( $report->server_id, $report->user_reported ) )
        {

            $message .= "\n \n";
            foreach ( get_notes_for_user_by_server( $report->server_id, $report->user_reported ) as $item )
            {
                // 4096
                $temp = '📝 یادداشت از ' . "<u>" . user( $item->user_id )->name . "</u>" . "\n";
                $temp .= $item->note . "\n";
                if ( mb_strlen( $message, 'UTF-8' ) + mb_strlen( $temp, 'UTF-8' ) <= 4090 ) $message .= $temp;
                else break;

            }

        }

        EditMessageText( $chatid, $messageid, $message, $telegram->buildInlineKeyBoard( $keyboard ), null, 'html' );
        break;

    case 'block_2':
        $message  = 'مدت زمان بلاک شدن کاربر را مشخص کنید.';
        $keyboard = [
            [
                $telegram->buildInlineKeyboardButton( '۱ ساعته', '', 'blocked-+1 hour-' . $data[1] ),
                $telegram->buildInlineKeyboardButton( '۳ ساعته', '', 'blocked-+3 hour-' . $data[1] )
            ],
            [
                $telegram->buildInlineKeyboardButton( '۶ ساعته', '', 'blocked-+6 hour-' . $data[1] ),
                $telegram->buildInlineKeyboardButton( '۱۲ ساعته', '', 'blocked-+12 hour-' . $data[1] )
            ],
            [ $telegram->buildInlineKeyboardButton( '۲۴ ساعته', '', 'blocked-+24 hour-' . $data[1] ) ],
        ];
        if ( $fromid == ADMIN_ID )
        {
            $keyboard = array_merge( $keyboard, [
                [
                    $telegram->buildInlineKeyboardButton( '۳ روزه', '', 'blocked-+3 day-' . $data[1] ),
                    $telegram->buildInlineKeyboardButton( 'یک هفته', '', 'blocked-+7 day-' . $data[1] )
                ],
                [
                    $telegram->buildInlineKeyboardButton( 'یک ماه', '', 'blocked-+30 day-' . $data[1] ),
                    $telegram->buildInlineKeyboardButton( 'یک سال', '', 'blocked-+365 day-' . $data[1] )
                ]
            ] );
        }
        $report   = get_report_by_id( $data[1] );
        $keyboard = array_merge( $keyboard, [
            [
                $telegram->buildInlineKeyboardButton( '⚠️ اخطار', '', 'warning-' . $report->id ),
                $telegram->buildInlineKeyboardButton( '❌ رد گزارش', '', 'reject-' . $report->id ),
                $telegram->buildInlineKeyboardButton( '📝 حذف اسم', '', 'delete_name-' . $report->id ),
            ],
            [ $telegram->buildInlineKeyboardButton( '🔙 برگشت منو قبل', '', 'wg-' . $report->user_id . '-' . $report->user_reported . '-x-' . $data[1] ) ],
        ] );
        EditMessageText( $chatid, $messageid, $message, $telegram->buildInlineKeyBoard( $keyboard ) );
        break;

    case 'unban':
        $message = 'وضعیت کاربر: ✔️ کاربر آزاد';
        EditMessageText( GP_MANAGER, $messageid, $message, $telegram->buildInlineKeyBoard( [ [ $telegram->buildInlineKeyboardButton( '🚫 مسدود کردن کاربر', '', 'ban-' . $data[1] ) ] ] ) );
        $message = '🌐 پیام سرور :' . "\n \n";
        $message .= '⏱ زمان مسدودیت اکانت شما به پایان رسید.' . "\n";
        $message .= '🔸 ' . "<u>لطفا به قوانین ربات پایبند باشید</u>" . ' 🌷' . "\n \n";
        $message .= '➖ درصورت نیاز نام خود را در بازی عوض کنید .' . "\n";
        $message .= 'قوانین ربات :  /ghavanin';
        SendMessage( $data[1], $message, null, null, 'html' );
        unban( $data[1] );
        break;

    case 'show_messages':
        $report  = get_report_by_id( $data[1] );
        $chats   = array_reverse( get_chats( $report->user_reported ) );
        $message = 'گزارش چت کاربر [[user]]:' . "\n \n";
        $count   = 0;
        if ( count( $chats ) > 0 )
        {
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
                if ( $count % 30 == 0 )
                {
                    $message .= "\n" . 'تعداد چت های ارسال شده: ' . $count;
                    SendMessage( GP_MANAGER, $message, null, null, 'html' );
                    $message = '';
                }
            }
            if ( $count % 30 != 0 )
            {
                $message .= "\n" . 'تعداد چت های ارسال شده: ' . $count;
                SendMessage( GP_MANAGER, $message, null, null, 'html' );
            }
            AnswerCallbackQuery( $dataid, '✔️ چت های کاربر به گروه گزارشات ارسال شد.', true );
        }
        else
        {
            AnswerCallbackQuery( $dataid, '♨️ متاسفانه چت های این کاربر یافت نشد.', true );
        }
        break;

    case 'delete_name':

        $report  = get_report_by_id( $data[1] );
        $message = '✅ گزارش [[user]] بررسی شد ' . "\n";
        $message .= "نتیجه : \n  📝 حذف اسم اعمال شد" . "\n \n";
        if ( isset( $fromid ) )
        {
            $message .= 'بررسی شده توسط :' . "\n";
            $message .= '👤 : [[admin]]';
        }
        $chats = get_chats( $report->user_reported, 1 );
        update_user( [ 'name' => '' ], $report->user_reported );
        $link->where( 'user_id', $report->user_reported )->update( 'names', [ 'name' => 'بینام' ] );
        EditMessageText(
            $chatid, $messageid, __replace__( $message, [
            '[[user]]'  => '<a href="tg://user?id=' . $report->user_reported . '">' . user( $report->user_reported )->name . '</a>',
            '[[admin]]' => !isset( $fromid ) ? '' : '<a href="tg://user?id=' . $fromid . '">' . $first_name . '</a>'
        ] ), $telegram->buildInlineKeyBoard( [ [ $telegram->buildInlineKeyboardButton( '♻️ پیگیری مجدد گزارش', '', 'get_messages-' . $chats[0]->id . '-' . $report->user_reported ) ] ] ), null, 'html'
        );

        break;

    case 'cancel':

        if ( $fromid == $data[1] )
        {

            $message = '🏳️ اوکی ، به کارت ادامه بده . ';
            EditMessageText( $chatid, $messageid, $message );

        }
        else
        {
            AnswerCallbackQuery( $dataid, '🚫 شما مجوز این کار را ندارید.' );
        }

        break;

    case 'move_coin':

        if ( check_time_chat( $chatid, 2, 'move-coin' ) )
        {

            if ( $fromid == $data[1] )
            {

                $user_coin = new \library\User( $data[1] );
                $coin      = $data[2];
                $user_id   = $data[3];

                if ( $user_coin->move_coin( $user_id, $coin ) )
                {

                    $message = '🪙 ' . "<u><b>" . '[[coin]] سکه ' . "</b></u>" . ' از طرف شما برای [[user]] ارسال شد ✅';
                    EditMessageText(
                        $chatid, $messageid, __replace__( $message, [
                        '[[coin]]' => $coin,
                        '[[user]]' => "<u>" . user( $user_id )->name . "</u>"
                    ] ), null, null, 'html'
                    );

                }
                else
                {
                    $message = 'متاسفانه موجودی سکه شما کافی نیست.';
                    EditMessageText( $chatid, $messageid, $message );
                }

            }
            else
            {
                AnswerCallbackQuery( $dataid, '🚫 شما مجوز این کار را ندارید.' );
            }

        }
        else
        {
            AnswerCallbackQuery( $dataid, '⚠️ مجدد امتحان کنید.' );
        }

        break;

    case 'accept_league':

        $league = get_vip_league_by_emoji( $data[1] );
        if ( empty( $league->id ) )
        {

            $user = new \library\User( $fromid );

            $id = add_new_vip_league( $data[1], 999, $fromid );
            $user->SendMessageHtml( $id );
            $message = __replace__( $callback_query->message->text, [
                '⚙️نوع عملیات را انتخاب کنید:' => '🔻 قیمت لیگ را وارد کنید.'
            ] );

            if ( preg_match( '/\d+/', $callback_query->message->text, $from_id ) )
            {
                $user->setData( $from_id[0] );
            }

            EditMessageText(
                $chatid, $messageid, $message, $telegram->buildInlineKeyBoard( [
                [
                    $telegram->buildInlineKeyboardButton( '0️⃣', '', 'add_amount_league-' . $id . '-' . 0 . '-' . $data[3] )
                ],
                [
                    $telegram->buildInlineKeyboardButton( '1️⃣', '', 'add_amount_league-' . $id . '-' . 1 . '-' . $data[3] ),
                    $telegram->buildInlineKeyboardButton( '2️⃣', '', 'add_amount_league-' . $id . '-' . 2 . '-' . $data[3] ),
                    $telegram->buildInlineKeyboardButton( '3️⃣', '', 'add_amount_league-' . $id . '-' . 3 . '-' . $data[3] ),
                ],
                [
                    $telegram->buildInlineKeyboardButton( '4️⃣', '', 'add_amount_league-' . $id . '-' . 4 . '-' . $data[3] ),
                    $telegram->buildInlineKeyboardButton( '5️⃣', '', 'add_amount_league-' . $id . '-' . 5 . '-' . $data[3] ),
                    $telegram->buildInlineKeyboardButton( '6️⃣', '', 'add_amount_league-' . $id . '-' . 6 . '-' . $data[3] ),
                ],
                [
                    $telegram->buildInlineKeyboardButton( '7️⃣', '', 'add_amount_league-' . $id . '-' . 7 . '-' . $data[3] ),
                    $telegram->buildInlineKeyboardButton( '8️⃣', '', 'add_amount_league-' . $id . '-' . 8 . '-' . $data[3] ),
                    $telegram->buildInlineKeyboardButton( '9️⃣', '', 'add_amount_league-' . $id . '-' . 9 . '-' . $data[3] ),
                ],
                [
                    $telegram->buildInlineKeyboardButton( '🔂', '', 'delete_amount_league-' . $id . '-' . $data[3] ),
                ],
            ] )
            );


        }
        else
        {
            $message = '❌ این لیگ قبلا جز لیگ های ربات می باشد.';
            EditMessageText( $chatid, $messageid, $message );
        }

        break;

    case 'add_amount_league':

        $message = __replace__( $callback_query->message->text, [
            $data[3] ?? '' => ''
        ] );

        if ( isset( $data[3] ) && $data[3] > 0 )
        {
            $data[3] .= intval( $data[2] );
        }
        else
        {
            $data[3] = intval( $data[2] );
            $message .= "\n \n";
        }

        $message .= intval( $data[3] );

        $id = $data[1];

        EditMessageText(
            $chatid, $messageid, $message, $telegram->buildInlineKeyBoard( [
            [
                $telegram->buildInlineKeyboardButton( '0️⃣', '', 'add_amount_league-' . $id . '-' . 0 . '-' . $data[3] )
            ],
            [
                $telegram->buildInlineKeyboardButton( '1️⃣', '', 'add_amount_league-' . $id . '-' . 1 . '-' . $data[3] ),
                $telegram->buildInlineKeyboardButton( '2️⃣', '', 'add_amount_league-' . $id . '-' . 2 . '-' . $data[3] ),
                $telegram->buildInlineKeyboardButton( '3️⃣', '', 'add_amount_league-' . $id . '-' . 3 . '-' . $data[3] ),
            ],
            [
                $telegram->buildInlineKeyboardButton( '4️⃣', '', 'add_amount_league-' . $id . '-' . 4 . '-' . $data[3] ),
                $telegram->buildInlineKeyboardButton( '5️⃣', '', 'add_amount_league-' . $id . '-' . 5 . '-' . $data[3] ),
                $telegram->buildInlineKeyboardButton( '6️⃣', '', 'add_amount_league-' . $id . '-' . 6 . '-' . $data[3] ),
            ],
            [
                $telegram->buildInlineKeyboardButton( '7️⃣', '', 'add_amount_league-' . $id . '-' . 7 . '-' . $data[3] ),
                $telegram->buildInlineKeyboardButton( '8️⃣', '', 'add_amount_league-' . $id . '-' . 8 . '-' . $data[3] ),
                $telegram->buildInlineKeyboardButton( '9️⃣', '', 'add_amount_league-' . $id . '-' . 9 . '-' . $data[3] ),
            ],
            [
                $telegram->buildInlineKeyboardButton( '🔂', '', 'delete_amount_league-' . $id . '-' . $data[3] ),
            ],
            [
                $telegram->buildInlineKeyboardButton( '☑️ تایید', '', 'insert_league-' . $id . '-' . $data[3] ),
            ],
        ] )
        );

        break;

    case 'delete_amount_league':

        $message = __replace__( $callback_query->message->text, [
            $data[2] => ''
        ] );


        $data[3] = '';
        $id      = $data[1];

        EditMessageText(
            $chatid, $messageid, $message, $telegram->buildInlineKeyBoard( [
            [
                $telegram->buildInlineKeyboardButton( '0️⃣', '', 'add_amount_league-' . $id . '-' . 0 . '-' . $data[3] )
            ],
            [
                $telegram->buildInlineKeyboardButton( '1️⃣', '', 'add_amount_league-' . $id . '-' . 1 . '-' . $data[3] ),
                $telegram->buildInlineKeyboardButton( '2️⃣', '', 'add_amount_league-' . $id . '-' . 2 . '-' . $data[3] ),
                $telegram->buildInlineKeyboardButton( '3️⃣', '', 'add_amount_league-' . $id . '-' . 3 . '-' . $data[3] ),
            ],
            [
                $telegram->buildInlineKeyboardButton( '4️⃣', '', 'add_amount_league-' . $id . '-' . 4 . '-' . $data[3] ),
                $telegram->buildInlineKeyboardButton( '5️⃣', '', 'add_amount_league-' . $id . '-' . 5 . '-' . $data[3] ),
                $telegram->buildInlineKeyboardButton( '6️⃣', '', 'add_amount_league-' . $id . '-' . 6 . '-' . $data[3] ),
            ],
            [
                $telegram->buildInlineKeyboardButton( '7️⃣', '', 'add_amount_league-' . $id . '-' . 7 . '-' . $data[3] ),
                $telegram->buildInlineKeyboardButton( '8️⃣', '', 'add_amount_league-' . $id . '-' . 8 . '-' . $data[3] ),
                $telegram->buildInlineKeyboardButton( '9️⃣', '', 'add_amount_league-' . $id . '-' . 9 . '-' . $data[3] ),
            ],
            [
                $telegram->buildInlineKeyboardButton( '🔂', '', 'delete_amount_league-' . $id . '-' . $data[3] ),
            ],
            [
                $telegram->buildInlineKeyboardButton( '☑️ تایید', '', 'insert_league-' . $id . '-' . $data[3] ),
            ],
        ] )
        );

        break;

    case 'insert_league':

        $user = new \library\User( $fromid );

        $link->where( 'id', $data[1] )->update( 'vip_league', [
            'coin' => intval( $data[2] )
        ] );

        $league = get_vip_league( $data[1] );

        $user_id = data( $user->getUserId() );
        if ( user_exists( $user_id ) )
        {

            $message = '✅ درخواست باز شدن لیگ ( ' . $league->emoji . ' ) توسط پشتیبانی تایید شد .' . "\n \n";
            $message .= 'از هم‌اکنون می‌توانید در قسمت پروفایل اقدام به خرید و فعالسازی لیگ مورد نظر نمایید .';
            SendMessage( $user_id, $message );

        }

        $message = $callback_query->message->text . " \n \n";
        $message .= '✅ با موفقیت اضافه شد.';
        EditMessageText( $chatid, $messageid, $message );


        break;

    case 'reject_league':

        $message = '❌ بنا به دلایلی درخواست باز شدن لیگ ( ' . $data[2] . ' ) توسط پشتیبانی رد شد .';
        SendMessage( $data[1], $message );

        $message = $callback_query->message->text . " \n \n";
        $message .= '❌ با موفقیت رد شد.';
        EditMessageText( $chatid, $messageid, $message );


        break;

    case 'add_league':


        $message = '✅ درخواست باز شدن لیگ ( ' . $data[2] . ' ) توسط پشتیبانی تایید شد .' . "\n \n";
        $message .= 'از هم‌اکنون می‌توانید در قسمت پروفایل اقدام به خرید و فعالسازی لیگ مورد نظر نمایید .';
        SendMessage( $data[1], $message );


        $message = $callback_query->message->text . " \n \n";
        $message .= '☑️ بسته شد.';
        EditMessageText( $chatid, $messageid, $message );


        break;

    case 'join_server':

        $user = new \library\User( $fromid );

        if ( $fromid == $data[2] )
        {

            if ( $user->is_ban() )
            {

                $league_game = get_league( $data[1] );

                if ( $league_game->point <= $user->get_point() )
                {


                    if ( empty( $user->getServerId() ) )
                    {

                        $server_id = add_user_server( $user->getUserId(), $league_game->id );

                        if ( is_numeric( $server_id ) )
                        {

                            $message = 'بازی دوستانه شما ایجاد شد.' . "\n \n" . '⏰ مدت زمان عضوگیری: 30 دقیقه';
                            SendMessage( $user->setStatus( 'get_users_server' )->getUserId(), $message, KEY_HOST_GAME_MENU );

                            update_server( $server_id, [
                                'type' => 'private'
                            ] );

                            $code = $server_id;
                            $i    = rand( 1, 9 );

                            if ( !is_null( $user->league ) )
                            {

                                $league      = get_vip_league_user_by_id( $user->league );
                                $league_name = $league->emoji . ' ' . $league->name;

                            }
                            else
                            {

                                $league_name = $user->get_league()->icon;

                            }

                            $message = __replace__( $league_game->content, [
                                '[[user]]'   => "<b><u>" . $user->user()->name . "</u></b>",
                                '[[point]]'  => apply_filters( 'send_massage_text', $user->get_point() ),
                                '[[league]]' => "<a href='https://telegram.me/iranimafia/89'>" . $league_name . "</a>"
                            ] );

                            EditMessageText(
                                $chatid, $messageid, $message, $telegram->buildInlineKeyBoard( [
                                [
                                    $telegram->buildInlineKeyboardButton( '↗️ پیوستن به بازی ↗️', 'https://telegram.me/' . GetMe()->username . '?start=server-' . string_encode( $server_id ) . '-' . $i )
                                ]
                            ] ), null, 'html'
                            );

                        }
                        else
                        {

                            AnswerCallbackQuery( $dataid, '🤕 متاسفم مشکلی رخ داد، لطفا دوباره تلاش کنید.' );

                            throw new Exception( 'ERROR ON CREATE SERVER FRIEND USER : ' . $fromid );

                        }

                    }
                    else
                    {

                        AnswerCallbackQuery( $dataid, '🚫 شما قبلا به یک بازی پیوسته اید.' );

                    }

                }
                else
                {

                    AnswerCallbackQuery( $dataid, '🚫 شما امتیاز کافی برای ساخت بازی ' . $league_game->icon . ' را ندارید.' );

                }

            }
            else
            {
                SendMessage( $chat_id, 'شما مسدود هستید' );
            }

        }
        else
        {

            AnswerCallbackQuery( $dataid, '🚫 تنها سازنده بازی میتواند بازی را شروع کند.' );

        }

        break;


    case 'rank_top_all':

        if ( !check_time_chat( $chat_id, 2 ) )
        {
            AnswerCallbackQuery( $dataid, '✋ هر 2 ثانیه یک بار میتوانید درخواست ارسال کنید.' );
            die();
        }

        $message    = '📊 لیست برترین های ایرانی مافیا ' . "\n \n";
        $list_users = get_top_rank_points();
        $leagues    = [];
        foreach ( $list_users as $id => $user )
        {
            $user_league                 = get__league_user( $user->user_id );
            $leagues[$user_league->id][] = $user;
        }

        $x = 1;

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

                    $message .= ( $fromid == $user->user_id ? '👈 ' : '[[' . $x . ']]  ' ) . "<b>" . $user->user->name . "</b>" . ( $fromid == $user->user_id ? ' (شما)' : ' ' ) . '      - [[point]] 🌟' . ( $emoji_rank ) . "\n";
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
                    if ( $user->user_id == $fromid )
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
        $rank           = get_rank_user_in_global( $fromid );
        $result         = $rank > 5 ? $rank : $number_to_word->numberToWords( $rank );

        __replace__( $message, [
            '[[point]]' => "<b>" . tr_num( get_point( $fromid ), 'fa', '.' ) . "</b>",
            '[[rank]]'  => "<b>" . tr_num( $result, 'fa', '.' ) . "</b>"
        ] );

        $emoji = '';
        add_filter( 'filter_league_user', function ( $query ) {
            global $emoji;
            $emoji = $query->emoji;
        }, 1 );
        $user_league = get__league_user( $fromid );

        EditMessageText(
            $chatid, $messageid, $message, $telegram->buildInlineKeyBoard( [
            [ $telegram->buildInlineKeyboardButton( '📊 برترین های بازی ' . '✔️', '', 'rank_top_all' ) ],
            [
                $telegram->buildInlineKeyboardButton( '📆 هفتگی', '', 'rank_top_week' ),
                $telegram->buildInlineKeyboardButton( '📅 روزانه', '', 'rank_top_today' ),
                $telegram->buildInlineKeyboardButton( ( $emoji . ' لیگ من' ), '', 'rank_top_my_league' ),
            ]
        ] ), null, 'html'
        );
        break;

    case 'rank_top_my_league':

        if ( !check_time_chat( $chat_id, 2 ) )
        {
            AnswerCallbackQuery( $dataid, '✋ هر 2 ثانیه یک بار میتوانید درخواست ارسال کنید.' );
            die();
        }

        $message       = '📈 لیست رقابت امتیازات نزدیک به شما' . "\n \n";
        $league        = get__league_user( $fromid );
        $next_league   = get__league( $league->id + 1 );
        $user_point    = (int) get_point( $fromid );
        $list_up_users = get_rank_up_user( $user_point, $next_league->point ?? $league->point, 'ASC', 4 );
        $list_up_users = array_reverse( $list_up_users );

        $x          = 1;
        $users_list = [];
        $message    .= $league->icon . ' 👇' . "\n";
        foreach ( $list_up_users as $user )
        {
            $user_info = get_user( $user->user_id );
            if ( !empty( $user_info->name ) )
            {
                $users_list[] = $user->user_id;
                $message      .= ( $fromid == $user->user_id ? '👈 ' : '[[' . $x . ']]  ' ) . "<b>" .'‏'. $user_info->name .'‏'. "</b>" . ( $fromid == $user->user_id ? ' (شما)' : ' ' ) . '      - [[point]] 🌟' . "\n";
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
                    '[[point]]' => "<b>" . tr_num( get_point( $user->user_id ), 'fa', '.' ) . "</b>",
                ] );
                $x ++;
                if ( $x == 6 ) break;
            }
        }

        $list_down_users = get_rank_down_user( $user_point, $users_list, ( 10 - count( $users_list ) ) );

        foreach ( $list_down_users as $user )
        {
            $user = get_user( $user->user_id );
            if ( !empty( $user->name ) )
            {
                $message .= ( $fromid == $user->user_id ? '👈 ' : '[[' . $x . ']]  ' ) . "<b>" . $user->name . "</b>" . ( $fromid == $user->user_id ? ' (شما)' : ' ' ) . '      - [[point]] 🌟' . "\n";
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
                    '[[point]]' => "<b>" . tr_num( get_point( $user->user_id ), 'fa', '.' ) . "</b>",
                ] );
                $x ++;
                if ( $x > 10 ) break;
            }
        }


        $message .= "\n" . '🔹رتبه شما : [[rank]]' . "\n";
        $message .= '🔸امتیاز شما : [[point]]' . "\n";

        $message .= '<a href="https://t.me/iranimafia/89">❗️تمامی لیگ های بازی</a>' . "\n \n";
        $message .= '@iranimafia';

        $rank = get_rank_user_in_league( $fromid );

        $number_to_word = new NumberToWord();
        $result         = $rank >= 10 ? $rank : $number_to_word->numberToWords( $rank );

        __replace__( $message, [
            '[[point]]' => "<b>" . tr_num( get_point( $fromid ), 'fa', '' ) . "</b>",
            '[[rank]]'  => "<b>" . tr_num( $result, 'fa', '' ) . "</b>",
        ] );

        $emoji = '';
        add_filter( 'filter_league_user', function ( $query ) {
            global $emoji;
            $emoji = $query->emoji;
        }, 1 );
        $user_league = get__league_user( $fromid );

        EditMessageText(
            $chatid, $messageid, $message, $telegram->buildInlineKeyBoard( [
            [ $telegram->buildInlineKeyboardButton( '📊 برترین های بازی ', '', 'rank_top_all' ) ],
            [
                $telegram->buildInlineKeyboardButton( '📆 هفتگی', '', 'rank_top_week' ),
                $telegram->buildInlineKeyboardButton( '📅 روزانه', '', 'rank_top_today' ),
                $telegram->buildInlineKeyboardButton( ( $emoji . ' لیگ من' ) . '✔️', '', 'rank_top_my_league' ),
            ]
        ] ), null, 'html'
        );
        break;

    case 'rank_top_week':

        if ( !check_time_chat( $chat_id, 2 ) )
        {
            AnswerCallbackQuery( $dataid, '✋ هر 2 ثانیه یک بار میتوانید درخواست ارسال کنید.' );
            die();
        }

        $number_to_word = new NumberToWord();

        $message    = '📆 لیست برترین های هفتگی ایرانی مافیا' . "\n \n" /*. '🔻 هفته #' . $number_to_word->numberToWords(get_option('week')) . "\n \n"*/
        ;
        $list_users = get_top_rank_points_week();

        $x = 1;
        foreach ( $list_users as $item )
        {
            $name = $item->user()->name;
            $name = empty($name) ? 'بینام' : $name;
            $name = '‏'.$name.'‏';
            
            $message .= ( $fromid == $item->getUserId() ? '👈 ' : '[[' . $x . ']]  ' ) . $item->league()->emoji . ' ' . "<b>" . ( empty( $name ) ? 'بینام' : $name ) . "</b>" . ( $fromid == $item->getUserId() ? ' (شما)' : ' ' ) . '      - [[point]] 🌟' . "\n";
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
                '[[point]]' => "<b>" . tr_num( $item->get_point_user_week(), 'fa', '.' ) . "</b>",
            ] );
            $x ++;
        }

        $rank  = get_rank_user_week( $fromid );
        $point = (int) get_point_user_week( $fromid );

        if ( $rank && $point > 0 )
        {

            $message .= "\n" . '🔹رتبه شما : [[rank]]';

        }

        if ( $point > 0 )
        {

            $message .= "\n" . '🔸امتیاز شما : [[point]]' . "\n \n";

        }
        else
        {

            $message .= "\n";

        }

        $message .= '<a href="https://t.me/iranimafia/89">❗️تمامی لیگ های بازی</a>' . "\n \n";
        $message .= '@iranimafia';

        $number_to_word = new NumberToWord();
        $result         = $rank >= 10 ? $rank : $number_to_word->numberToWords( $rank );

        __replace__( $message, [
            '[[point]]' => "<b>" . tr_num( $point, 'fa' ) . "</b>",
            '[[rank]]'  => "<b>" . tr_num( $result ?? 0, 'fa' ) . "</b>",
        ] );

        $emoji = '';
        add_filter( 'filter_league_user', function ( $query ) {
            global $emoji;
            $emoji = $query->emoji;
        }, 1 );
        $user_league = get__league_user( $fromid );

        EditMessageText(
            $chatid, $messageid, $message, $telegram->buildInlineKeyBoard( [
            [ $telegram->buildInlineKeyboardButton( '📊 برترین های بازی ', '', 'rank_top_all' ) ],
            [
                $telegram->buildInlineKeyboardButton( '📆 هفتگی ' . '✔️', '', 'rank_top_week' ),
                $telegram->buildInlineKeyboardButton( '📅 روزانه', '', 'rank_top_today' ),
                $telegram->buildInlineKeyboardButton( ( $emoji . ' لیگ من' ), '', 'rank_top_my_league' ),
            ]
        ] ), null, 'html'
        );
        break;

    case 'rank_top_today':

        if ( !check_time_chat( $chat_id, 2 ) )
        {
            AnswerCallbackQuery( $dataid, '✋ هر 2 ثانیه یک بار میتوانید درخواست ارسال کنید.' );
            die();
        }

        $message    = '📅 لیست برترین های روزانه ایرانی مافیا' . "\n \n";
        $list_users = get_top_rank_points_today();

        $x = 1;
        /** @var \library\User $item */
        foreach ( $list_users as $item )
        {

            $name    = $item->user()->name;
            $name = empty($name) ? 'بینام' : $name;
            $name = '‏'.$name.'‏';
            $message .= ( $fromid == $item->getUserId() ? '👈 ' : '[[' . $x . ']]  ' ) . ( $item->league()->emoji ) . ' ' . "<b>" . ( empty( $name ) ? 'بینام' : $name ) . "</b>" . ( $fromid == $item->getUserId() ? ' (شما)' : ' ' ) . '      - [[point]] 🌟' . "\n";
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
                '[[point]]' => "<b>" . tr_num( $item->get_point_daily_today(), 'fa' ) . "</b>",
            ] );
            $x ++;
        }

        $rank = get_rank_user_today( $fromid );

        if ( $rank )
        {
            $message .= "\n" . '🔹رتبه شما : [[rank]]';
        }

        $message .= "\n" . '🔸امتیاز شما : [[point]]' . "\n";

        $message .= '<a href="https://t.me/iranimafia/89">❗️تمامی لیگ های بازی</a>' . "\n \n";
        $message .= '@iranimafia';


        $number_to_word = new NumberToWord();
        $result         = $rank >= 10 ? $rank : $number_to_word->numberToWords( $rank );

        __replace__( $message, [
            '[[point]]' => "<b>" . tr_num( (int) get_point_user_day( $fromid, date( 'Y-m-d' ), '=' ), 'fa' ) . "</b>",
            '[[rank]]'  => "<b>" . tr_num( $result ?? 0, 'fa' ) . "</b>",
        ] );

        $emoji = '';
        add_filter( 'filter_league_user', function ( $query ) {
            global $emoji;
            $emoji = $query->emoji;
        }, 1 );
        $user_league = get__league_user( $fromid );


        EditMessageText(
            $chatid, $messageid, $message, $telegram->buildInlineKeyBoard( [
            [ $telegram->buildInlineKeyboardButton( '📊 برترین های بازی ', '', 'rank_top_all' ) ],
            [
                $telegram->buildInlineKeyboardButton( '📆 هفتگی', '', 'rank_top_week' ),
                $telegram->buildInlineKeyboardButton( '📅 روزانه ' . '✔️', '', 'rank_top_today' ),
                $telegram->buildInlineKeyboardButton( ( $emoji . ' لیگ من' ), '', 'rank_top_my_league' ),
            ]
        ] ), null, 'html'
        );
        break;

    default:
        // AnswerCallbackQuery( $dataid, 'این بخش هنوز فعال نشده است .', true );
        break;

}