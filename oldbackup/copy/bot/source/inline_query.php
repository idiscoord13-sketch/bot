<?php

/** @noinspection ALL */

use library\User;

if ( !is_admin() )
{


    switch ( $query )
    {

        default:
            $User = new User( $chatid );

            if ( $User->registed() )
            {

                $user = $User->user();

                if ( !is_null( $user->league ) )
                {

                    $league      = get_vip_league_user_by_id( $user->league );
                    $league_name = $league->emoji . ' ' . $league->name;

                }
                else
                {

                    $league_name = $User->get_league()->icon;

                }

                if ( empty( $User->getServerId() ) )
                {

                    $games = get_games();
                    foreach ( $games as $item )
                    {

                        if ( $item->point >= 0 )
                        {

                            if ( is_null( $item->start_time ) || in_array( $User->getUserId(), [ ADMIN_LOG, ADMIN_ID, '321415151' ] ) || date( 'H' ) >= $item->start_time && date( 'H' ) <= $item->end_time )
                            {

                                $message = __replace__( $item->content, [
                                    '[[user]]'   => "<b><u>" . $user->name . "</u></b>",
                                    '[[point]]'  => apply_filters( 'send_massage_text', $User->get_point() ),
                                    '[[league]]' => "<a href='https://telegram.me/iranimafia/89'>" . $league_name . "</a>"
                                ] );

                                $result[] = [
                                    'type'                  => 'article',
                                    'id'                    => $item->id,
                                    'title'                 => $item->title,
                                    'input_message_content' => [
                                        'message_text'             => $message,
                                        'parse_mode'               => 'html',
                                        'disable_web_page_preview' => true
                                    ],
                                    'description'           => $item->description,
                                    'thumb_url'             => SRC_URL . 'files/games/' . $item->image,
                                    'reply_markup'          => [
                                        'inline_keyboard' => [
                                            [
                                                [
                                                    'text'          => '▶️ ساخت بازی',
                                                    'callback_data' => 'create_game-' . $item->id . '-' . $User->getUserId()
                                                ]
                                            ]
                                        ]
                                    ]
                                ];

                            }

                        }

                    }

                }
                else
                {

                    if ( is_numeric( $User->getServerId() ) )
                    {

                        $Server = new Library\Server( $User->getServerId() );

                        if ( $Server->server()->user_id == $User->getUserId() )
                        {

                            $code = $Server->getId();
                            $i    = rand( 1, 9 );

                            $league = get_league( $Server->league_id );

                            if ( $league->point >= 0 )
                            {

                                $message = __replace__( $league->content, [
                                    '[[user]]'   => "<b><u>" . $user->name . "</u></b>",
                                    '[[point]]'  => apply_filters( 'send_massage_text', $User->get_point() ),
                                    '[[league]]' => "<a href='https://telegram.me/iranimafia/89'>" . $league_name . "</a>"
                                ] );

                                $result[] = [
                                    'type'                  => 'article',
                                    'id'                    => $league->id,
                                    'title'                 => $league->title,
                                    'input_message_content' => [
                                        'message_text'             => $message,
                                        'parse_mode'               => 'html',
                                        'disable_web_page_preview' => true
                                    ],
                                    'description'           => $league->description,
                                    'thumb_url'             => SRC_URL . 'files/games/' . $league->image,
                                    'reply_markup'          => [
                                        'inline_keyboard' => [
                                            [
                                                [
                                                    'text' => '↗️ پیوستن به بازی ↗️',
                                                    'url'  => 'https://telegram.me/' . GetMe()->username . '?start=server-' . string_encode( $code ) . '-' . $i
                                                ]
                                            ]
                                        ]
                                    ]
                                ];

                            }

                        }

                    }
                    else
                    {

                        throw new Exception( 'ERROR ON CREATE SERVER FRIEND USER : ' . $fromid );

                    }

                }

                bot( 'answerInlineQuery', [
                    'inline_query_id' => $inline_query->id,
                    'cache_time'      => 1,
                    'results'         => json_encode( $result ),
                ] );

            }
            else
            {

                bot( 'answerInlineQuery', [
                    'inline_query_id'     => $inline_query->id,
                    'cache_time'          => 1,
                    'results'             => json_encode( [] ),
                    'switch_pm_text'      => ' برای شروع یک بازی دوستانه اول باید ثبت نام کنید',
                    'switch_pm_parameter' => ''
                ] );

            }

            break;

        case 'banner':

            $caption  = "<u>بسیار مافیا باید ، تا پخته شود خامی.</u>" . "\n \n";
            $caption  .= 'زنگ خطر شهر به صدا در آمده است و مافیا تلاش در تسخیر شهر دارد .' . "\n";
            $caption  .= '🔍 وقتی چشم‌ها را باز می‌کنید ، به دنیایی پرتاب می‌شوید که قواعد مافیا بر آن حکم‌فرماست .' . "\n";
            $caption  .= 'بازی جذاب مافیا را از همین لحظه در ربات ایرانی مافیا و در کنار کاربران آنلاین تجربه کنید ‌😎' . "\n \n";
            $caption  .= '♨️ ربات بازی :' . "\n";
            $caption  .= '➖ @iranimafiabot' . "\n";
            $caption  .= '♨️ کانال رسمی : ' . "\n";
            $caption  .= '➖ @iranimafia';
            $result[] = [
                'type'          => 'photo',
                'id'            => 1,
                'title'         => '💥 بنر تبلیغاتی 💥',
                'photo_file_id' => 'AgACAgQAAxkBAAL8eWHgLTiIxCTUgwvW--FaMCCbwJPiAAIwuDEbbKEBUwj7dEcoT3znAQADAgADcwADIwQ',
                'description'   => '👈 برای اشتراک گذاری بنر کلیک کنید.',
                'caption'       => $caption,
                'parse_mode'    => 'html'
            ];
            bot( 'answerInlineQuery', [
                'inline_query_id' => $inline_query->id,
                'cache_time'      => 1,
                'results'         => json_encode( $result ),
            ] );

            break;

        case $chatid:
            $User = new User( $chatid );

            if ( $User->registed() )
            {

                $username = 'iranimafiabot';
                $message  = '💎 تجربه ی یک بازی متفاوت آنلاین' . "\n \n";
                $message  .= '<b>تا حالا بازی مافیا رو توی تلگرام داخل ربات انجام دادی؟🤔</b>' . "\n \n";
                $message  .= '🎮 اگه حوصلت توی تلگرام سر رفته و دنبال یه سرگرمی جذاب هستی همین الان بازی مافیا رو استارت کن 😍👌' . "\n \n";
                $message  .= 'https://telegram.me/' . $username . '?start=' . string_encode( $chatid );
                $result[] = [
                    'type'                  => 'article',
                    'id'                    => 1,
                    'title'                 => 'اشتراک گذاری متنی',
                    'input_message_content' => [
                        'message_text'             => $message,
                        'parse_mode'               => 'html',
                        'disable_web_page_preview' => true
                    ],
                    'description'           => 'برای اشتراک گذاری به صورت متنی کلیک کنید.',
                ];

                $message = '💎 تجربه ی یک بازی متفاوت آنلاین' . "\n \n";
                $message .= '<b>تا حالا بازی مافیا رو توی تلگرام داخل ربات انجام دادی؟🤔</b>' . "\n \n";
                $message .= '🎮 اگه حوصلت توی تلگرام سر رفته و دنبال یه سرگرمی جذاب هستی همین الان بازی مافیا رو استارت کن 😍👌' . "\n \n";
                $message .= 'https://telegram.me/' . $username . '?start=' . string_encode( $chatid );
                /*$result[] = [
                    'type' => 'article',
                    'id' => 2,
                    'title' => 'اشتراک گذاری تبلیغاتی',
                    'input_message_content' => [
                        'message_text' => $message,
                        'parse_mode' => 'html',
                        'disable_web_page_preview' => true
                    ],
                    'description' => 'برای اشتراک گذاری به صورت تبلیغاتی کلیک کنید.',
                    'thumb_url' => SRC_URL . 'files/games/profile.jpg',
                ];*/
                $result[] = [
                    'type'          => 'photo',
                    'id'            => 2,
                    'title'         => 'اشتراک گذاری تبلیغاتی',
                    'photo_file_id' => 'AgACAgQAAxkBAAFpt_hiEUP-h_3o7W4rowR2K6IqCrhvQQACXLgxG8QaiFDTrbnk_bBAhgEAAwIAA20AAyME',
                    'description'   => 'برای اشتراک گذاری به صورت تبلیغاتی کلیک کنید.',
                    'caption'       => $message,
                    'parse_mode'    => 'html'
                ];

                $message  = '💎 تجربه ی یک بازی متفاوت آنلاین' . "\n \n";
                $message  .= '<b>تا حالا بازی مافیا رو توی تلگرام داخل ربات انجام دادی؟🤔</b>' . "\n \n";
                $message  .= '🎮 اگه حوصلت توی تلگرام سر رفته و دنبال یه سرگرمی جذاب هستی همین الان بازی مافیا رو استارت کن 😍👌';
                $result[] = [
                    'type'          => 'photo',
                    'id'            => 3,
                    'title'         => 'اشتراک گذاری حرفه ای',
                    'photo_file_id' => 'AgACAgQAAxkBAAFpt_hiEUP-h_3o7W4rowR2K6IqCrhvQQACXLgxG8QaiFDTrbnk_bBAhgEAAwIAA20AAyME',
                    'description'   => 'برای اشتراک گذاری به صورت حرفه ای کلیک کنید.',
                    'caption'       => $message,
                    'parse_mode'    => 'html',
                    'reply_markup'  => [
                        'inline_keyboard' => [
                            [
                                [
                                    'text' => '🎮 شروع بازی 🎮',
                                    'url'  => 'https://telegram.me/' . $username . '?start=' . string_encode( $chatid )
                                ]
                            ]
                        ]
                    ]
                ];

                /*$result[] = [
                    'type' => 'article',
                    'id' => 3,
                    'title' => 'اشتراک گذاری حرفه ای',
                    'input_message_content' => [
                        'message_text' => $message,
                        'parse_mode' => 'html',
                        'disable_web_page_preview' => true
                    ],
                    'description' => 'برای اشتراک گذاری به صورت حرفه ای کلیک کنید.',
                    'thumb_url' => SRC_URL . 'files/games/profile.jpg',
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [
                                [
                                    'text' => '🎮 شروع بازی 🎮',
                                    'url' => 'https://telegram.me/' . $username . '?start=' . $chatid
                                ]
                            ]
                        ]
                    ]
                ];*/

                $result = bot( 'answerInlineQuery', [
                    'inline_query_id' => $inline_query->id,
                    'cache_time'      => 1,
                    'results'         => json_encode( $result ),
                ] );
            }
            else
            {

                bot( 'answerInlineQuery', [
                    'inline_query_id'     => $inline_query->id,
                    'cache_time'          => 1,
                    'results'             => json_encode( [] ),
                    'switch_pm_text'      => ' برای شروع یک بازی دوستانه اول باید ثبت نام کنید',
                    'switch_pm_parameter' => ''
                ] );

            }
            break;

        case 'media':

            $user = new User( $chatid );

            if ( $user->is( ADMIN_LOG ) || $user->is( ADMIN_ID ) || $user->haveSubscribe() )
            {


                $medias = $link->get_result( 'SELECT * FROM `media`' );

                $result = [];
                foreach ( $medias as $media )
                {
                    $result[] = [
                        'type'       => 'voice',
                        'id'         => $media->id,
                        'voice_url'  => $media->url,
                        'title'      => $media->title,
                        'parse_mode' => 'html'
                    ];
                }


                $telegram->answerInlineQuery( [

                    'inline_query_id' => $inline_query->id,
                    'cache_time'      => 1,
                    'results'         => json_encode( $result ),

                ] );

            }
            else
            {

                $telegram->answerInlineQuery( [

                    'inline_query_id'     => $inline_query->id,
                    'cache_time'          => 1,
                    'switch_pm_text'      => 'خرید اشتراک VIP',
                    'switch_pm_parameter' => 'buy-sub',

                ] );

            }

            break;

    }

}