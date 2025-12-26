<?php


if ( !isset($data[3]) )
{
    AnswerCallbackQuery($dataid, '⚠️خطا، در شناسایی سرور مشکلی رخ داده است.', true);
    throw new Exception("ERROR ON SCANNING SERVER");
}

use library\Role;
use library\Server;
use library\User;

$server       = new Server($data[3]);
$user         = new User($chatid, $server->getId());
$user_select  = new User($data[4] ?? 0, $server->getId());
$users_server = $server->users();
$day          = $server->day();
$selector     = new Role($server->getId());


if ( $user->dead() )
{
    AnswerCallbackQuery($dataid, '⚠️خطا، شما مرده اید!', true);
    exit();
}
elseif ( $server->getStatus() == 'closed' )
{
    AnswerCallbackQuery($dataid, '📛 این سرور بسته شده است.', true);
    exit();
}
elseif ( $data_day[0] != $day )
{
    AnswerCallbackQuery($dataid, '🚸 این پنل منقضی شده است. لطفا از پنل های جدید استفاده کنید.', true);
    exit();
}
elseif ( $server->getMeta('is') == 'on' )
{
    AnswerCallbackQuery($dataid, '⚠️ مجددا امتحان کنید', true);
        SendMessage( 56288741, "کد 1", KEY_GAME_ON_MENU, null, 'html' );

    exit();
}


try
{


    $keyboard = [];
    switch ( $data[2] )
    {

        // ............ GROUP 1 ............
        // کارآگاه
        case 'search':

            $select = $selector->user()->select(ROLE_Karagah);

            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_Karagah)->answerCallback();

                foreach ( $users_server as $user )
                {
                    if ( $user->check($chatid) )
                    {

                        $text       = '🔦 ' . $user->get_name() . ( $user->is($user_select) ? '✔️' : '' );
                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-search-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }
                }

            }
            else
            {

                $selector->delete(ROLE_Karagah);

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) )
                    {

                        $text       = '🔦 ' . $user->get_name();
                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-search-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
            break;
        // پزشک
        case 'heal':

            $select = $selector->user()->select(ROLE_Pezeshk);

            $status_doctor = is_server_meta($server->getId(), 'doctor', ROLE_Pezeshk);

            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_Pezeshk)->answerCallback(function ( User $user ) {
                    return '💉 شما ' . $user->get_name() . ' را نجات دادید.';
                });

                foreach ( $users_server as $user )
                {

                    if ( !$user->dead() && ( !$user->is($chatid) || !$status_doctor ) )
                    {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('💉 ' . $user->get_name() . ( $user->is($user_select) ? '✔️' : '' ), '', $day . '/server-' . $server->league_id . '-heal-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }
            else
            {

                $selector->delete(ROLE_Pezeshk);

                foreach ( $users_server as $user )
                {
                    if ( !$user->dead() && ( !$user->is($chatid) || !$status_doctor ) )
                    {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('💉 ' . $user->get_name(), '', $day . '/server-' . $server->league_id . '-heal-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }
                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
            break;
        // اسنایپر
        case 'fight':

            $select = $selector->user()->select(ROLE_Sniper);

            $kalantar = $selector->getUser(ROLE_Kalantar);

            if ( $select->getUserId() > 0 && $server->role_exists(ROLE_Kalantar) && !$kalantar->dead() )
            {

                AnswerCallbackQuery($dataid, '❌ امکان تغییر هدف وجود ندارد .');

            }
            else
            {

                if ( !$select->is($user_select) )
                {

                    if ( $user_select->spy() )
                    {

                        $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اسنایپر</u>" . ' قصد حمله به شما را دارد .';
                        $user_select->SendMessageHtml();

                    }

                    if ( $select->spy() )
                    {

                        $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اسنایپر</u>" . 'از حمله به شما منصرف شد.';
                        $select->SendMessageHtml();

                    }

                    $selector->set($user_select->getUserId(), ROLE_Sniper)->answerCallback();

                    foreach ( $users_server as $user )
                    {

                        if ( $user->check($chatid) )
                        {

                            $text       = '🔫 ' . $user->get_name() . ( $user->is($user_select) ? '✔️' : '' );
                            $keyboard[] = [
                                $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-fight-' . $server->getId() . '-' . $user->getUserId())
                            ];

                        }

                    }

                    if ( $server->role_exists(ROLE_Kalantar) && !$kalantar->dead() )
                    {

                        $message = 'سلام کلانتر 👨🏻‍✈️' . "\n";
                        $message .= 'اسنایپر قصد حمله به 🔫 ' . "<u><b>" . $user_select->get_name() . "</b></u>" . ' را دارد ، سرنوشت تیر را با تایید یا عدم تایید مشخص کنید 🤫';
                        $kalantar->setKeyboard(
                            $telegram->buildInlineKeyBoard([
                                [
                                    $telegram->buildInlineKeyboardButton('👍 تایید ', '', $day . '/server-' . $server->league_id . '-kalantar_ok-' . $server->getId() . '-' . $user->getUserId()),
                                    $telegram->buildInlineKeyboardButton('👎 عدم تایید', '', $day . '/server-' . $server->league_id . '-kalantar_false-' . $server->getId() . '-' . $user->getUserId()),
                                ]
                            ])
                        )->SendMessageHtml($message);
                        $selector->set($user->getUserId(), ROLE_Kalantar, 'power-select');

                    }

                }
                else
                {

                    if ( $select->spy() )
                    {

                        $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اسنایپر</u>" . 'از حمله به شما منصرف شد.';
                        $select->SendMessageHtml();

                    }

                    $selector->delete(ROLE_Sniper);

                    foreach ( $users_server as $user )
                    {

                        if ( $user->check($chatid) )
                        {

                            $text       = '🔫 ' . $user->get_name();
                            $keyboard[] = [
                                $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-fight-' . $server->getId() . '-' . $user->getUserId())
                            ];

                        }

                    }

                }

                EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            }

            break;
        // بازپرس
        case 'question':

            if ( !$user_select->dead() )
            {

                if ( $selector->user()->select(ROLE_TohmatZan, 'last-select')->is($user) )
                {

                    AnswerCallbackQuery($dataid, '❌ شما امروز نمیتوانید کسی را زندانی کنید .', true);

                    exit();

                }

                $status = $user->getStatus();

                if ( $status == 'voting' )
                {

                    $selector->set($user_select->getUserId(), ROLE_Bazpors)->answerCallback();

                    $i = 0;

                    $user_vote = $selector->getInt()->select($selector->getUser(ROLE_Bazpors)->getUserId(), 'vote');
                    foreach ( $users_server as $item )
                    {

                        if ( $item->check($chatid) && get_server_meta($server->getId(), 'no-vote', $item->getUserId()) != 'on' )
                        {
                            if($server->setUserId(ROLE_Dalghak)->getMetaUser('dalghak') != 'use' ){
                                $text           = '🗳 ' . $item->get_name() . ( $item->is($user_vote) ? '✔️' : '' );
                                $keyboard[$i][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-vote-' . $server->getId() . '-' . $item->getUserId());
                            }

                            $text           = '🔗 ' . $item->get_name() . ' ' . ( $item->is($user_select) ? '✔️' : '' );
                            $keyboard[$i][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-question-' . $server->getId() . '-' . $item->getUserId());
                            $i ++;

                        }
                    }

                    EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

                }
                elseif ( $status == 'game_started' || ceil($server->getPeopleAlive() / 2) == 1 || $server->setUserId(ROLE_Dalghak)->getMetaUser('dalghak') == 'use' )
                {

                    $selector->set($user_select->getUserId(), ROLE_Bazpors)->answerCallback();

                    foreach ( $users_server as $item )
                    {

                        if ( $item->check($chatid) )
                        {

                            $text       = '🔗 ' . $item->get_name() . ' ' . ( $item->is($user_select) ? '✔️' : '' );
                            $keyboard[] = [
                                $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-question-' . $server->getId() . '-' . $item->getUserId())
                            ];

                        }

                    }

                    EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

                }
                else
                {

                    AnswerCallbackQuery($dataid, '⚠️خطا، هم اکنون نمیتوانید کسی را زندانی کنید!');

                }

            }
            else
            {

                AnswerCallbackQuery($dataid, '⚠️ خطا، کاربری که انتخاب کرده اید مرده است.');

            }

            break;
        // بازپرس - دستور محکوم
        case 'bazpors_kill':

            $selector->set($user_select->getUserId(), ROLE_Bazpors, 'kill')->answerCallback(function ( User $user ) {
                return '💢 ' . $user->get_name() . ' پس از اعلام صبح اعدام خواهد شد.';
            });

            if ( $user_select->spy() )
            {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>بازپرس</u>" . ' شما را محکوم کرد .';
                $user_select->SendMessageHtml();

            }

            $keyboard = [
                [
                    $telegram->buildInlineKeyboardButton('⚖️ محکوم' . ' ✔️', '', $day . '/server-' . $server->league_id . '-bazpors_kill-' . $server->getId() . '-' . $user_select->getUserId()),
                    $telegram->buildInlineKeyboardButton('⭕️ آزاد', '', $day . '/server-' . $server->league_id . '-bazpors_release-' . $server->getId() . '-' . $user_select->getUserId()),
                ]
            ];

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            break;
        // بازپرس - دستور آزاد
        case 'bazpors_release':
            $selector->delete(ROLE_Bazpors, 'kill');

            if ( $user_select->spy() )
            {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>بازپرس</u>" . ' از محکوم کردن شما منصرف شد.';
                $user_select->SendMessageHtml();

            }

            $keyboard = [
                [
                    $telegram->buildInlineKeyboardButton('⚖️ محکوم', '', $day . '/server-' . $server->league_id . '-bazpors_kill-' . $server->getId() . '-' . $user_select->getUserId()),
                    $telegram->buildInlineKeyboardButton('⭕️ آزاد' . ' ✔️', '', $day . '/server-' . $server->league_id . '-bazpors_release-' . $server->getId() . '-' . $user_select->getUserId()),
                ]
            ];

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            break;
        // قاضی
        case 'pass_voting':
            if ( $server->getStatus() == 'court-3' )
            {

                if ( !is_server_meta($server->getId(), 'ghazi') )
                {

                    if ( !is_server_meta($server->getId(), 'ghazi', ROLE_Ghazi) )
                    {

                        $server->setUserId(ROLE_Ghazi)->updateMetaUser('ghazi', 'use');
                        $selector->delete($chatid, 'vote');
                        $accused  = $server->accused();
                        $keyboard = [
                            [

                                $telegram->buildInlineKeyboardButton('بی‌گناه', '', $day . '/server-' . $server->league_id . '-^court-' . $server->getId() . '-' . $accused->getUserId()),
                                $telegram->buildInlineKeyboardButton('گناهکار', '', $day . '/server-' . $server->league_id . '-court-' . $server->getId() . '-' . $accused->getUserId()),

                            ],
                            [
                                $telegram->buildInlineKeyboardButton('❌ ابطال ✔️', '', $day . '/server-' . $server->league_id . '-pass_voting-' . $server->getId() . '-' . $user->getUserId())
                            ]
                        ];

                        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

                    }
                    else
                    {

                        AnswerCallbackQuery($dataid, '⛔️ شما قبلا از قدرت خود استفاده کرده اید.');

                    }

                }
                else
                {

                    delete_server_meta($server->getId(), 'ghazi', ROLE_Ghazi);
                    $selector->delete($chatid, 'vote');
                    $accused  = $server->accused();
                    $keyboard = [
                        [

                            $telegram->buildInlineKeyboardButton('بی‌گناه', '', $day . '/server-' . $server->league_id . '-^court-' . $server->getId() . '-' . $accused->getUserId()),
                            $telegram->buildInlineKeyboardButton('گناهکار', '', $day . '/server-' . $server->league_id . '-court-' . $server->getId() . '-' . $accused->getUserId()),

                        ],
                        [
                            $telegram->buildInlineKeyboardButton('❌ ابطال', '', $day . '/server-' . $server->league_id . '-pass_voting-' . $server->getId() . '-' . $user->getUserId())
                        ]
                    ];

                    EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

                }

            }
            else
            {

                $selector->answerCallback(function () {
                    return '🔴 اکنون نمیتوانید از رای گیری جلوگیری کنید';
                });

            }
            break;
        // پلیس
        case 'police':

            $police_status = is_server_meta($server->getId(), 'select', ROLE_Police);

            if ( !$police_status )
            {

                $selector->set($user->getUserId(), ROLE_Police);
                $keyboard[][] = $telegram->buildInlineKeyboardButton('👮🏻‍♂️ هوشیار بمانید ✔️', '', $day . '/server-' . $server->league_id . '-police-' . $server->getId() . '-' . $user->getUserId());

            }
            else
            {

                $selector->delete(ROLE_Police);
                $keyboard[][] = $telegram->buildInlineKeyboardButton('👮🏻‍♂️ هوشیار بمانید', '', $day . '/server-' . $server->league_id . '-police-' . $server->getId());

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
            break;
        // دیدبان
        case 'did_ban':
            $select = $selector->user()->select(ROLE_Didban);

            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_Didban)->answerCallback();

                foreach ( $users_server as $item )
                {

                    if ( $item->check($chatid) )
                    {

                        $text       = '👀 ' . $item->get_name() . ( $item->is($user_select) ? '✔️ ' : '' );
                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-did_ban-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

            }
            else
            {

                $selector->delete(ROLE_Didban);
                foreach ( $users_server as $item )
                {

                    if ( $item->check($chatid) )
                    {

                        $text       = '👀 ' . $item->get_name();
                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-did_ban-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
            break;
        // تفنگ دار با تیر مشقی
        case 'tofang_dar_1':


            EditMessageText($chatid, $messageid, $callback_query->message->text, null, null, 'html');

            $selector->set($user_select->getUserId(), ROLE_TofangDar)->set(1, ROLE_TofangDar, 'type')->answerCallback(function ( User $user ) {
                return 'شما یک فشنگ مشقی در اختیار ' . $user->get_name() . ' قرار دادید .';
            });

            $message = '🤵🏻‍♂تفنگدار یک فشنگ در اختیار ' . $user_select->get_name() . ' قرار داد .';
            foreach ( $users_server as $item )
            {

                if ( $item->check($user_select) && $item->is_ban() )
                {

                    $keyboard[] = [

                        $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-tofang_dar-' . $server->getId() . '-' . $item->getUserId())

                    ];


//                    $item->SendMessageHtml();

                }

            }

            $message = '🤵🏻‍♂ تفنگ دار ، تفنگ را در اختیار شما قرار داده است.' . "\n";
            $message .= 'یک نفر را برای حمله انتخاب کنید 👇';
            SendMessage($user_select->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard));

            break;
        // تفنگ دار با تیر جنگی
        case 'tofang_dar_2':


            EditMessageText($chatid, $messageid, $callback_query->message->text, null, null, 'html');

            $selector->set($user_select->getUserId(), ROLE_TofangDar)->set(2, ROLE_TofangDar, 'type')->answerCallback(function ( User $user ) {
                return 'شما یک فشنگ جنگی در اختیار ' . $user->get_name() . ' قرار دادید .';
            });

            $message = '🤵🏻‍♂تفنگدار یک فشنگ در اختیار ' . $user_select->get_name() . ' قرار داد .';
            foreach ( $users_server as $item )
            {

                if ( $item->check($user_select) )
                {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-tofang_dar-' . $server->getId() . '-' . $item->getUserId())
                    ];

//                    $item->SendMessageHtml();

                }

            }

            $message = '🤵🏻‍♂ تفنگ دار ، تفنگ را در اختیار شما قرار داده است.' . "\n";
            $message .= 'یک نفر را برای حمله انتخاب کنید 👇';
            SendMessage($user_select->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard));

            break;
        // شخصی که تفنگ دریافت کرده است
        case 'tofang_dar':
            $select = $selector->user()->select(ROLE_TofangDar, 'attacker');

            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_TofangDar, 'attacker')->answerCallback();

                if ( $user_select->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>تفنگدار</u>" . ' قصد حمله به شما را دارد .';
                    $user_select->SendMessageHtml();

                }

                if ( $select->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>تفنگدار</u>" . 'از حمله به شما منصرف شد.';
                    $select->SendMessageHtml();

                }

                foreach ( $users_server as $item )
                {

                    if ( $item->check($chatid) )
                    {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name() . ( $item->is($user_select) ? '✔️' : '' ), '', $day . '/server-' . $server->league_id . '-tofang_dar-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

            }
            else
            {

                $selector->delete(ROLE_TofangDar, 'attacker');

                if ( $user_select->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>تفنگدار</u>" . 'از حمله به شما منصرف شد.';
                    $user_select->SendMessageHtml();

                }

                foreach ( $users_server as $item )
                {

                    if ( $item->check($chatid) )
                    {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-tofang_dar-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
            break;
        // محقق
        case 'search_mohaghegh':
            $select = $selector->user()->select(ROLE_Mohaghegh);

            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_Mohaghegh)->answerCallback();

                foreach ( $users_server as $item )
                {

                    if ( $item->check($chatid) )
                    {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('🔎 ' . $item->get_name() . ( $item->is($user_select) ? '✔️ ' : '' ), '', $day . '/server-' . $server->league_id . '-search_mohaghegh-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

            }
            else
            {

                $selector->delete(ROLE_Mohaghegh);

                foreach ( $users_server as $item )
                {

                    if ( $item->check($chatid) )
                    {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('🔎 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-search_mohaghegh-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
            break;
        // معمار
        case 'memar':
            $select = $selector->user()->select(ROLE_Memar);

            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_Memar)->answerCallback();

                foreach ( $users_server as $item )
                {

                    if ( $item->check($chatid) )
                    {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('🏗 ' . $item->get_name() . ( $item->is($user_select) ? '🔨' : '' ), '', $day . '/server-' . $server->league_id . '-memar-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

            }
            else
            {

                $selector->delete(ROLE_Memar);

                foreach ( $users_server as $item )
                {

                    if ( $item->check($chatid) )
                    {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('🏗 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-memar-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
            break;
        // کشیش
        case 'keshish':

            if ( !is_server_meta($server->getId(), 'keshish') )
            {

                if ( $selector->getString()->select(ROLE_Keshish) != 'on' )
                {

                    update_server_meta($server->getId(), 'select', 'on', ROLE_Keshish);
                    $selector->answerCallback(function () {
                        return 'فردا همه منزه هستند.';
                    });

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('✔️ دعا کردن 🤲🏻', '', $day . '/server-' . $server->league_id . '-keshish-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }
                else
                {

                    $selector->delete(ROLE_Keshish);

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('دعا کردن 🤲🏻', '', $day . '/server-' . $server->league_id . '-keshish-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

                EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            }
            else
            {

                $selector->answerCallback(function () {
                    return '🚫 شما قبلا از قدرت خود استفاده کرده اید.';
                });

            }

            break;
        // فدایی
        case 'fadaii':

            if ( $server->getStatus() == 'court-3' )
            {

                $accused = $server->accused();

                $keyboard[] = [
                    $telegram->buildInlineKeyboardButton('بی‌گناه', '', $day . '/server-' . $server->league_id . '-^court-' . $server->getId() . '-' . $accused->getUserId()),
                    $telegram->buildInlineKeyboardButton('گناهکار', '', $day . '/server-' . $server->league_id . '-court-' . $server->getId() . '-' . $accused->getUserId()),
                ];

                if ( !is_server_meta($server->getId(), 'fadaii') )
                {

                    add_server_meta($server->getId(), 'fadaii', 'use');
                    $selector->delete($chatid, 'vote');
                    $keyboard[][] = $telegram->buildInlineKeyboardButton('فدایی شدن ✔️', '', $day . '/server-' . $server->league_id . '-fadaii-' . $server->getId() . '-' . $user->getUserId());

                }
                else
                {

                    delete_server_meta($server->getId(), 'fadaii');
                    $selector->delete($chatid, 'vote');
                    $keyboard[][] = $telegram->buildInlineKeyboardButton('فدایی شدن', '', $day . '/server-' . $server->league_id . '-fadaii-' . $server->getId() . '-' . $user->getUserId());

                }

                EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            }
            else
            {

                $selector->answerCallback(function () {
                    return '🔴 اکنون نمیتوانید از رای گیری جلوگیری کنید';
                });

            }

            break;
        // کلانتر
        case 'kalantar':

            $select = $selector->user()->select(ROLE_Kalantar);

            $last_select = $selector->user()->select(ROLE_Kalantar, 'last-select');

            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_Kalantar)->answerCallback();

                foreach ( $users_server as $item )
                {

                    if ( $item->check($user) && !$last_select->is($item) )
                    {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('👨‍✈️ ' . $item->get_name() . ( $item->is($user_select) ? '✔️' : '' ), '', $day . '/server-' . $server->league_id . '-kalantar-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

            }
            else
            {

                $selector->delete(ROLE_Kalantar);

                foreach ( $users_server as $item )
                {

                    if ( $item->check($user) && !$last_select->is($item) )
                    {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('👨‍✈️ ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-kalantar-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            break;
        // کلانتر تایید
        case 'kalantar_ok':

            EditKeyboard(
                $chatid, $messageid, $telegram->buildInlineKeyBoard([
                [
                    $telegram->buildInlineKeyboardButton('👍 تایید ' . '✔️', '', $day . '/server-' . $server->league_id . '-kalantar_ok-' . $server->getId() . '-' . $user->getUserId()),
                    $telegram->buildInlineKeyboardButton('👎 عدم تایید', '', $day . '/server-' . $server->league_id . '-kalantar_false-' . $server->getId() . '-' . $user->getUserId()),
                ]
            ])
            );
            $selector->set($user->getUserId(), ROLE_Kalantar, 'power-select');

            break;
        // رد کلانتر
        case 'kalantar_false':

            EditKeyboard(
                $chatid, $messageid, $telegram->buildInlineKeyBoard([
                [
                    $telegram->buildInlineKeyboardButton('👍 تایید ', '', $day . '/server-' . $server->league_id . '-kalantar_ok-' . $server->getId() . '-' . $user->getUserId()),
                    $telegram->buildInlineKeyboardButton('👎 عدم تایید' . '✔️', '', $day . '/server-' . $server->league_id . '-kalantar_false-' . $server->getId() . '-' . $user->getUserId()),
                ]
            ])
            );
            $selector->delete(ROLE_Kalantar, 'power-select');

            break;
        case 'terrorist':
            $select = $selector->user()->select(ROLE_Terrorist);
            
            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_Terrorist)->answerCallback();

                foreach ( $users_server as $item )
                {

                    if ( $item->check($chatid) && $item->get_role()->group_id != 2 )
                    {

                        $text       = '🧨 ' . $item->get_name() . ( $item->is($user_select) ? '✔️' : '' );
                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-terrorist-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

            }
            else
            {

                $selector->delete(ROLE_Terrorist);

                foreach ( $users_server as $item )
                {

                    if ( $item->check($chatid) && $item->get_role()->group_id != 2 )
                    {

                        $text       = '🧨 ' . $item->get_name();
                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-terrorist-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }
            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
            break;
        // کابوی
        case 'kaboy':
            $select = $selector->user()->select(ROLE_Kaboy);

            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_Kaboy)->answerCallback();

                foreach ( $users_server as $item )
                {

                    if ( $item->check($chatid) )
                    {

                        $text       = '🕴 ' . $item->get_name() . ( $item->is($user_select) ? '✔️' : '' );
                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-kaboy-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

            }
            else
            {

                $selector->delete(ROLE_Kaboy);

                foreach ( $users_server as $item )
                {

                    if ( $item->check($chatid) )
                    {

                        $text       = '🕴 ' . $item->get_name();
                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-kaboy-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }
            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
            break;
        // عینک ساز
        case 'eynak':

            if ( $selector->getInt()->select(ROLE_EynakSaz) <= 0 )
            {

                $selector->set($user_select->getUserId(), ROLE_EynakSaz)->answerCallback(function ( User $user ) {
                    return 'شما یک عینک در اختیار ' . $user->get_name() . ' قرار دادید .';
                });

                foreach ( $users_server as $item )
                {

                    if ( $item->check($user_select) )
                    {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('🔍 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-eynak_2-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

                $message = '👓 عینک ساز به شما عینک داده و شما میتوانید استعلام یک نفر را بگیرید :';
                SendMessage($user_select->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard));

                EditMessageText($chatid, $messageid, $callback_query->message->text, null, null, 'html');

            }

            break;
        // کسی که عینک دریافت کرده
        case 'eynak_2':

            $select = $selector->user()->select(ROLE_EynakSaz, 'attacker');

            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_EynakSaz, 'attacker')->answerCallback();

                foreach ( $users_server as $item )
                {

                    if ( $item->check($chatid) )
                    {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('🔍 ' . $item->get_name() . ( $item->is($user_select) ? ' ✔️' : '' ), '', $day . '/server-' . $server->league_id . '-eynak_2-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

            }
            else
            {

                $selector->delete(ROLE_EynakSaz, 'attacker');

                foreach ( $users_server as $item )
                {

                    if ( $item->check($chatid) )
                    {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('🔍 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-eynak_2-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            break;
        // فرشته
        case 'healed':
            $select = $selector->user()->select(ROLE_Fereshteh);

            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_Fereshteh)->answerCallback();

                foreach ( $users_server as $item )
                {


                    if ( !$item->is($chatid) && $item->dead() && $item->get_role()->group_id == 1 && $item->is_user_in_game() )
                    {

                        if ( $item->getRoleId() != ROLE_Fadaii || !is_server_meta($server->getId(), 'fadaii', ROLE_Fadaii) )
                        {

                            $keyboard[] = [
                                $telegram->buildInlineKeyboardButton('👰‍♀️ ' . $item->get_name() . ( $item->is($user_select) ? '✔️ ' : '' ), '', $day . '/server-' . $server->league_id . '-healed-' . $server->getId() . '-' . $item->getUserId())
                            ];

                        }

                    }

                }

            }
            else
            {

                $selector->delete(ROLE_Fereshteh);

                foreach ( $users_server as $item )
                {

                    if ( !$item->is($chatid) && $item->dead() && $item->get_role()->group_id == 1 && $item->is_user_in_game() )
                    {

                        if ( $item->getRoleId() != ROLE_Fadaii || !is_server_meta($server->getId(), 'fadaii', ROLE_Fadaii) )
                        {

                            $keyboard[] = [
                                $telegram->buildInlineKeyboardButton('👰‍♀️ ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-healed-' . $server->getId() . '-' . $item->getUserId())
                            ];

                        }

                    }

                }


            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
            break;
        // بادیگارد
        case 'bodygard':

            $select = $selector->user()->select(ROLE_Bodygard);

            $status_bodygard = is_server_meta($server->getId(), 'bodygard', ROLE_Bodygard);

            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_Bodygard)->answerCallback();

                foreach ( $users_server as $user )
                {

                    if ( !$user->dead() && ( !$user->is($chatid) || !$status_bodygard ) )
                    {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('💂‍♀️ ' . $user->get_name() . ( $user->is($user_select) ? '✔️' : '' ), '', $day . '/server-' . $server->league_id . '-bodygard-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }
            else
            {

                $selector->delete(ROLE_Bodygard);

                foreach ( $users_server as $user )
                {

                    if ( !$user->dead() && ( !$user->is($chatid) || !$status_bodygard ) )
                    {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('💂‍♀️ ' . $user->get_name(), '', $day . '/server-' . $server->league_id . '-bodygard-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            break;
        // خبرنگار
        case 'khabar_negar':

            $select = $selector->user()->select(ROLE_KhabarNegar);

            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_KhabarNegar)->answerCallback();

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) )
                    {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('📸 ' . $user->get_name() . ( $user->is($user_select) ? '✔️' : '' ), '', $day . '/server-' . $server->league_id . '-khabar_negar-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }
            else
            {

                $selector->delete(ROLE_KhabarNegar);

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) )
                    {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('📸 ' . $user->get_name(), '', $day . '/server-' . $server->league_id . '-khabar_negar-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            break;
        // زامبی
        case 'zambi':

            $select = $selector->user()->select(ROLE_Zambi);

            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_Zambi)->answerCallback();

                foreach ( $users_server as $user )
                {

                    if ( !$user->is($chatid) )
                    {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('🧟‍♂️ ' . $user->get_name() . ( $user->dead() ? '☠️' : '' ) . ( $user->is($user_select) ? '✔️' : '' ), '', $day . '/server-' . $server->league_id . '-zambi-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }
            else
            {

                $selector->delete(ROLE_Zambi);

                foreach ( $users_server as $user )
                {

                    if ( !$user->is($chatid) )
                    {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('🧟‍♂️ ' . $user->get_name() . ( $user->dead() ? '☠️' : '' ), '', $day . '/server-' . $server->league_id . '-zambi-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            break;
        // بزرگ خاندان
        case 'big_khab':

            if ( $server->getStatus() == 'court-3' )
            {

                $accused = $server->accused();

                $keyboard[] = [
                    $telegram->buildInlineKeyboardButton('بی‌گناه', '', $day . '/server-' . $server->league_id . '-^court-' . $server->getId() . '-' . $accused->getUserId()),
                    $telegram->buildInlineKeyboardButton('گناهکار', '', $day . '/server-' . $server->league_id . '-court-' . $server->getId() . '-' . $accused->getUserId()),
                ];


                $select = $selector->user()->select(ROLE_Big_Khab);

                if ( $select->is($user_select) )
                {
                    $selector->delete(ROLE_Big_Khab);
                }
                else
                {
                    $selector->set($user_select->getUserId(), ROLE_Big_Khab);
                }

                $selector->delete($chatid, 'vote');

                $select     = $selector->user()->select(ROLE_Big_Khab);
                $keyboard[] = [
                    $telegram->buildInlineKeyboardButton(( '🟢 بی‌گناه' . ( $select->getUserId() == 2 ? '✔️' : '' ) ), '', $day . '/server-' . $server->league_id . '-big_khab-' . $server->getId() . '-' . 2),
                    $telegram->buildInlineKeyboardButton(( '🔴 گناهکار' . ( $select->getUserId() == 1 ? '✔️' : '' ) ), '', $day . '/server-' . $server->league_id . '-big_khab-' . $server->getId() . '-' . 1),
                ];

                EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            }
            else
            {

                $selector->answerCallback(function () {
                    return '🔴 اکنون نمیتوانید از قدرت خود استفاده کنید';
                });

            }

            break;
        // ............ GROUP 2 ............
        // گاد فادر
        case 'god':
            $user_role = $user->get_role();


            $select         = $selector->user()->select(ROLE_Godfather);
            $bazpors_select = $selector->user()->select(ROLE_Bazpors);

            $select_mashoghe = $selector->user()->select(ROLE_Mashooghe);

            if ( $server->setUserId(ROLE_Godfather)->getMetaUser('super-god-father') == 'on' )
            {

                $select_2 = $selector->user()->select(ROLE_Godfather, 'select_2');

                if ( $select->is($user_select) )
                {

                    $selector->delete(ROLE_Godfather);
                    if ( $user_select->spy() )
                    {

                        $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . 'از حمله به شما منصرف شدند.';
                        $user_select->SendMessageHtml();

                    }

                }
                elseif ( $select_2->is($user_select) )
                {

                    $selector->delete(ROLE_Godfather, 'select_2');
                    if ( $user_select->spy() )
                    {

                        $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . 'از حمله به شما منصرف شدند.';
                        $user_select->SendMessageHtml();

                    }

                }
                elseif ( $select instanceof User && $select->getUserId() == 0 )
                {

                    $selector->set($user_select->getUserId(), ROLE_Godfather);

                    if ( $user_select->spy() )
                    {

                        $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . ' قصد حمله به شما را دارد .';
                        $user_select->SendMessageHtml();

                    }

                    if ( $select->spy() )
                    {

                        $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . 'از حمله به شما منصرف شدند.';
                        $select->SendMessageHtml();

                    }

                }
                else
                {

                    $selector->set($user_select->getUserId(), ROLE_Godfather, 'select_2');

                    if ( $user_select->spy() )
                    {

                        $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . ' قصد حمله به شما را دارد .';
                        $user_select->SendMessageHtml();

                    }

                    if ( $select_2->spy() )
                    {

                        $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . 'از حمله به شما منصرف شدند.';
                        $select_2->SendMessageHtml();

                    }

                }


                $select   = $selector->user()->select(ROLE_Godfather);
                $select_2 = $selector->user()->select(ROLE_Godfather, 'select_2');

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }
                }

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && $user->get_role()->group_id != 2 )
                    {

                        $text = '🔫 ' . $user->get_name() . ( $select->is($user) || $select_2->is($user) ? '✔️' : '' );

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-god-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }
            else
            {

                if ( !$select->is($user_select) )
                {

                    $selector->set($user_select->getUserId(), ROLE_Godfather);

                    if ( $select_mashoghe->getUserId() > 0 && !$user_select->is($select_mashoghe) && $select_mashoghe->spy() )
                    {

                        $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . 'از حمله به شما منصرف شدند.';
                        $select_mashoghe->SendMessageHtml();

                    }

                    if ( !$user_select->is($select_mashoghe) )
                    {

                        if ( $user_select->spy() )
                        {

                            $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . ' قصد حمله به شما را دارد .';
                            $user_select->SendMessageHtml();

                        }

                        if ( $select_mashoghe->spy() )
                        {

                            $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . 'از حمله به شما منصرف شدند.';
                            $select_mashoghe->SendMessageHtml();

                        }

                    }

                    if ( $select->spy() )
                    {

                        $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . 'از حمله به شما منصرف شدند.';
                        $select->SendMessageHtml();

                    }

                    $role_group_2 = $server->roleByGroup(2);
                    $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                    foreach ( $role_group_2 as $user )
                    {

                        if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                        {

                            $user->SendMessageHtml();

                        }
                    }

                    foreach ( $users_server as $user )
                    {

                        if ( $user->check($chatid) && $user->get_role()->group_id != 2 )
                        {

                            $text = '🔫 ' . $user->get_name() . ( $user_select->is($user) ? '✔️' : '' );

                            $keyboard[] = [
                                $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-god-' . $server->getId() . '-' . $user->getUserId())
                            ];

                        }

                    }

                }
                else
                {

                    $selector->delete(ROLE_Godfather);

                    if ( $select->spy() )
                    {

                        $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . 'از حمله به شما منصرف شدند.';
                        $select->SendMessageHtml();

                    }

                    if ( $select_mashoghe->getUserId() > 0 && $select_mashoghe->spy() )
                    {

                        $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . ' قصد حمله به شما را دارد .';
                        $select_mashoghe->SendMessageHtml();

                    }

                    $role_group_2 = $server->roleByGroup(2);
                    $message      = user()->name . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
                    foreach ( $role_group_2 as $user )
                    {

                        if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                        {

                            $user->SendMessageHtml();

                        }
                    }

                    foreach ( $users_server as $user )
                    {

                        if ( $user->check($chatid) && $user->get_role()->group_id != 2 )
                        {

                            $text = '🔫 ' . $user->get_name();

                            $keyboard[] = [
                                $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-god-' . $server->getId() . '-' . $user->getUserId())
                            ];

                        }

                    }

                }

            }

            if ( isset($keyboard) )
            {
                EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
            }
            break;
        // معشوقه
        case 'mashooghe':


            $god_father        = $selector->getUser(ROLE_Godfather);
            $god_father_select = $selector->user()->select(ROLE_Godfather);
            $select            = $selector->user()->select(ROLE_Mashooghe);
            $bazpors_select    = $selector->user()->select(ROLE_Bazpors);
            $user_role         = $user->get_role();

            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_Mashooghe)->answerCallback();

                if ( $god_father->dead() || $bazpors_select->is($god_father) || $god_father_select->getUserId() <= 0 )
                {

                    if ( $user_select->spy() )
                    {

                        $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . ' قصد حمله به شما را دارد .';
                        $user_select->SendMessageHtml();

                    }

                    if ( $select->spy() )
                    {

                        $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . 'از حمله به شما منصرف شدند.';
                        $select->SendMessageHtml();

                    }

                }

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }
                }

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && $user->get_role()->group_id != 2 )
                    {

                        $text = '🔫 ' . $user->get_name() . ( $user_select->is($user) ? '✔️' : '' );

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-mashooghe-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }
            else
            {

                if ( $god_father->dead() || $bazpors_select->is($god_father) || $god_father_select->getUserId() == 0 )
                {

                    if ( $select->spy() )
                    {

                        $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . 'از حمله به شما منصرف شدند.';
                        $select->SendMessageHtml();

                    }

                }

                $selector->delete(ROLE_Mashooghe);

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }
                }

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && $user->get_role()->group_id != 2 )
                    {

                        $text = '🔫 ' . $user->get_name();

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-mashooghe-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            break;
        // ناتو
        case 'nato':


            $select         = $selector->user()->select(ROLE_Nato);
            $bazpors_select = $selector->user()->select(ROLE_Bazpors);

            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_Nato)->answerCallback();

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }
                }

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && $user->get_role()->group_id != 2 )
                    {

                        $text = '🔍 ' . $user->get_name() . ( $user_select->is($user) ? '✔️' : '' );

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-nato-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }
            else
            {

                $selector->delete(ROLE_Nato);

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }
                }

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && $user->get_role()->group_id != 2 )
                    {

                        $text = '🔍 ' . $user->get_name();

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-nato-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }
            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
            break;
        // هکر
        case 'hacker':


            $select = $selector->user()->select(ROLE_Hacker);

            $bazpors_select = $selector->user()->select(ROLE_Bazpors);

            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_Hacker)->answerCallback();

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }
                }

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && $user->get_role()->group_id != 2 )
                    {

                        $text = '🧑🏻‍💻 ' . $user->get_name() . ( $user_select->is($user) ? '✔️' : '' );

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-hacker-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }
            else
            {

                $selector->delete(ROLE_Hacker);

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }
                }

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && $user->get_role()->group_id != 2 )
                    {

                        $text = '🧑🏻‍💻 ' . $user->get_name();

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-hacker-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
            break;
        // مافیا حرفه ای
        case 'hard_mafia':


            $select         = $selector->user()->select(ROLE_HardFamia);
            $bazpors_select = $selector->user()->select(ROLE_Bazpors);

            if ( !$select->is($user_select) )
            {

                if ( $user_select->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>مافیا حرفه ای</u>" . ' قصد حمله به شما را دارد .';
                    $user_select->SendMessageHtml();

                }

                if ( $select->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>مافیا حرفه ای</u>" . 'از حمله به شما منصرف شدند.';
                    $select->SendMessageHtml();

                }

                $selector->set($user_select->getUserId(), ROLE_HardFamia)->answerCallback();

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }
                }

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && $user->get_role()->group_id != 2 )
                    {

                        $text = '🔪 ' . $user->get_name() . ( $user_select->is($user) ? '✔️' : '' );

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-hard_mafia-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }
            else
            {

                if ( $select->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>مافیا حرفه ای</u>" . 'از حمله به شما منصرف شدند.';
                    $select->SendMessageHtml();

                }

                $selector->delete(ROLE_HardFamia);

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }
                }

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && $user->get_role()->group_id != 2 )
                    {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('🔪 ' . $user->get_name(), '', $day . '/server-' . $server->league_id . '-hard_mafia-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            break;

//        case 'gorkan':
//
//
//            $select         = $selector->user()->select(ROLE_HardFamia);
//            $bazpors_select = $selector->user()->select(ROLE_Bazpors);
//
//            if ( !$select->is($user_select) )
//            {
//
//                if ( $user_select->spy() )
//                {
//
//                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>مستقل ,گورکن</u>" . ' قصد حمله به شما را دارد .';
//                    $user_select->SendMessageHtml();
//
//                }
//
//                if ( $select->spy() )
//                {
//
//                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>مستقل ,گورکن</u>" . 'از حمله به شما منصرف شدند.';
//                    $select->SendMessageHtml();
//
//                }
//
//                $selector->set($user_select->getUserId(), ROLE_HardFamia)->answerCallback();
//
//                $role_group_2 = $server->roleByGroup(2);
//                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
//                foreach ( $role_group_2 as $user )
//                {
//
//                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
//                    {
//
//                        $user->SendMessageHtml();
//
//                    }
//                }
//
//                foreach ( $users_server as $user )
//                {
//
//                    if ( $user->check($chatid) && $user->get_role()->group_id != 2 )
//                    {
//
//                        $text = '🔪 ' . $user->get_name() . ( $user_select->is($user) ? '✔️' : '' );
//
//                        $keyboard[] = [
//                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-gorkan-' . $server->getId() . '-' . $user->getUserId())
//                        ];
//
//                    }
//
//                }
//
//            }
//            else
//            {
//
//                if ( $select->spy() )
//                {
//
//                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>مستقل ,گورکن</u>" . 'از حمله به شما منصرف شدند.';
//                    $select->SendMessageHtml();
//
//                }
//
//                $selector->delete(ROLE_HardFamia);
//
//                $role_group_2 = $server->roleByGroup(2);
//                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
//                foreach ( $role_group_2 as $user )
//                {
//
//                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
//                    {
//
//                        $user->SendMessageHtml();
//
//                    }
//                }
//
//                foreach ( $users_server as $user )
//                {
//
//                    if ( $user->check($chatid) && $user->get_role()->group_id != 2 )
//                    {
//
//                        $keyboard[] = [
//                            $telegram->buildInlineKeyboardButton('🔪 ' . $user->get_name(), '', $day . '/server-' . $server->league_id . '-gorkan-' . $server->getId() . '-' . $user->getUserId())
//                        ];
//
//                    }
//
//                }
//
//            }
//
//            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
//
//            break;
        // تهمت زن
        case 'tohmat':


            $select         = $selector->user()->select(ROLE_TohmatZan);
            $bazpors_select = $selector->user()->select(ROLE_Bazpors);

            $last_select = $selector->user()->select(ROLE_TohmatZan, 'last-select');

            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_TohmatZan)->answerCallback();

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }
                }

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && !$last_select->is($user) && $user->get_role()->group_id != 2 )
                    {

                        $text = '👻 ' . $user->get_name() . ( $user_select->is($user) ? '✔️' : '' );

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-tohmat-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }
            else
            {

                $selector->delete(ROLE_TohmatZan);

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }
                }

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && !$last_select->is($user) && $user->get_role()->group_id != 2 )
                    {

                        $text = '👻 ' . $user->get_name();

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-tohmat-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
            break;
        // افسون گر
        case 'afson_gar':


            $select         = $selector->user()->select(ROLE_AfsonGar);
            $bazpors_select = $selector->user()->select(ROLE_Bazpors);

            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_AfsonGar)->answerCallback();

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }
                }

                $last_select = $selector->user()->select(ROLE_AfsonGar, 'last-select');
                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && !$last_select->is($user) && $user->get_role()->group_id != 2 )
                    {

                        $text = '🦹🏻 ' . $user->get_name() . ( $user_select->is($user) ? '✔️' : '' );

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-afson_gar-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }
            else
            {

                $selector->delete(ROLE_AfsonGar);

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }
                }

                $last_select = $selector->user()->select(ROLE_AfsonGar, 'last-select');
                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && !$last_select->is($user) && $user->get_role()->group_id != 2 )
                    {

                        $text = '🦹🏻 ' . $user->get_name();

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-afson_gar-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }
            }


            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            break;
        // دکتر لکتر
        case 'doctor':


            $select         = $selector->user()->select(ROLE_BAD_DOCTOR);
            $bazpors_select = $selector->user()->select(ROLE_Bazpors);

            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_BAD_DOCTOR)->answerCallback();

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>دکتر لکتر</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }
                }

                $status_doctor = is_server_meta($server->getId(), 'doctor', ROLE_BAD_DOCTOR);

                foreach ( $server->roleByGroup(2) as $item )
                {

                    if ( !$item->dead() && ( !$item->is($chatid) || !$status_doctor ) )
                    {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('🩹 ' . $item->get_name() . ( $user_select->is($item) ? '✔️' : '' ), '', $day . '/server-' . $server->league_id . '-doctor-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

            }
            else
            {

                $selector->delete(ROLE_BAD_DOCTOR);

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }
                }

                foreach ( $server->roleByGroup(2) as $item )
                {

                    if ( !$item->dead() && ( !$item->is($chatid) || !$status_doctor ) )
                    {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('🩹 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-doctor-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }
            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
            break;
        // توبچی
        case 'tobchi':


            $select         = $selector->user()->select(ROLE_Tobchi);
            $bazpors_select = $selector->user()->select(ROLE_Bazpors);

            if ( !$select->is($user_select) )
            {

                if ( $user_select->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>توپچی</u>" . ' قصد حمله به شما را دارد .';
                    $user_select->SendMessageHtml();

                }

                if ( $select->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>توپچی</u>" . 'از حمله به شما منصرف شدند.';
                    $select->SendMessageHtml();

                }

                $selector->set($user_select->getUserId(), ROLE_Tobchi)->answerCallback();

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }
                }

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && $user->get_role()->group_id != 2 )
                    {

                        $text = '💣 ' . $user->get_name() . ( $user_select->is($user) ? '✔️' : '' );

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-tobchi-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }
            else
            {

                if ( $select->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>توپچی</u>" . 'از حمله به شما منصرف شدند.';
                    $select->SendMessageHtml();

                }

                $selector->delete(ROLE_Tobchi);

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }
                }

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && $user->get_role()->group_id != 2 )
                    {

                        $text = '💣 ';

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-tobchi-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            break;
        // شکارچی
        case ROLE_ShekarChi:


            $select            = $selector->user()->select(ROLE_ShekarChi);
            $bazpors_select    = $selector->user()->select(ROLE_Bazpors);
            $select_shekar_chi = $selector->user()->select(ROLE_ShekarChi, 'last-select');

            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_ShekarChi)->answerCallback();

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }

                }

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && $user->get_role()->group_id != 2 && !$select_shekar_chi->is($item) )
                    {

                        $text = '🕶 ' . $user->get_name() . ( $user_select->is($user) ? '✔️' : '' );

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_ShekarChi . '-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }
            else
            {

                $selector->delete(ROLE_ShekarChi);

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }
                }

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && $user->get_role()->group_id != 2 && !$select_shekar_chi->is($item) )
                    {

                        $text = '🕶 ';

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_ShekarChi . '-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            break;
        // شب خسب
        case 'sleep':


            $select         = $selector->user()->select(ROLE_ShabKhosb);
            $bazpors_select = $selector->user()->select(ROLE_Bazpors);
            $last_select    = get_server_meta($server->getId(), 'last-user', ROLE_ShabKhosb);

            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_ShabKhosb)->answerCallback();

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }

                }

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && $user->get_role()->group_id != 2 )
                    {

                        if ( !$user->is($last_select) )
                        {

                            $text = '💆‍♂ ' . $user->get_name() . ( $user_select->is($user) ? '✔️' : '' );

                            $keyboard[] = [
                                $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-sleep-' . $server->getId() . '-' . $user->getUserId())
                            ];

                        }

                    }

                }

            }
            else
            {

                $selector->delete(ROLE_ShabKhosb);

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }
                }

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && $user->get_role()->group_id != 2 )
                    {

                        if ( !$user->is($last_select) )
                        {

                            $text = '💆‍♂ ';

                            $keyboard[] = [
                                $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-sleep-' . $server->getId() . '-' . $user->getUserId())
                            ];

                        }

                    }

                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            break;
        // مذاکره کننده
        case 'mozakereh':

            $select         = $selector->user()->select(ROLE_MozakarehKonandeh);
            $bazpors_select = $selector->user()->select(ROLE_Bazpors);

            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_MozakarehKonandeh)->answerCallback();

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }

                }

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && $user->get_role()->group_id != 2 )
                    {

                        $text = '🤝 ' . $user->get_name() . ( $user_select->is($user) ? '✔️' : '' );

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-mozakereh-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }
            else
            {

                $selector->delete(ROLE_MozakarehKonandeh);

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }
                }

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && $user->get_role()->group_id != 2 )
                    {

                        $text = '🤝 ';

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-mozakereh-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            break;
        // دلقک
        case 'dalghak':

            $select         = $selector->user()->select(ROLE_Dalghak);
            $bazpors_select = $selector->user()->select(ROLE_Bazpors);

            if ( !$select->is($user_select) )
            {

                $selector->set($user_select->getUserId(), ROLE_Dalghak)->answerCallback();
                $keyboard[][] = $telegram->buildInlineKeyboardButton('🤡 خندیدن ✔️', '', $day . '/server-' . $server->league_id . '-dalghak-' . $server->getId() . '-' . $user->getUserId());

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }

                }

            }
            else
            {

                $selector->delete(ROLE_Dalghak);
                $keyboard[][] = $telegram->buildInlineKeyboardButton('🤡 خندیدن', '', $day . '/server-' . $server->league_id . '-dalghak-' . $server->getId() . '-' . $user->getUserId());

                $role_group_2 = $server->roleByGroup(2);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }

                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            break;
        // ............ GROUP 3 ............
        // زودیاک
        case 'kill':

            $select         = $selector->user()->select(ROLE_Killer);
            $bazpors_select = $selector->user()->select(ROLE_Bazpors);

            if ( !$select->is($user_select) )
            {

                if ( $user_select->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>زودیاک</u>" . ' قصد حمله به شما را دارد .';
                    $user_select->SendMessageHtml();

                }

                if ( $select->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>زودیاک</u>" . 'از حمله به شما منصرف شدند.';
                    $select->SendMessageHtml();

                }

                if ( $server->league_id == 3 )
                {
                    $role_group_2 = $server->roleByGroup(3);
                    $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                    foreach ( $role_group_2 as $user )
                    {

                        if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                        {

                            $user->SendMessageHtml();

                        }

                    }
                }

                $selector->set($user_select->getUserId(), ROLE_Killer)->answerCallback();

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && $user->get_role()->group_id != 3 )
                    {

                        $text       = '☠️ ' . $user->get_name() . ( $user->is($user_select) ? '✔️' : '' );
                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-kill-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }
            else
            {

                if ( $select->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>زودیاک</u>" . 'از حمله به شما منصرف شد.';
                    $select->SendMessageHtml();

                }

                $role_group_2 = $server->roleByGroup(3);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }

                }

                $selector->delete(ROLE_Killer);

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && $user->get_role()->group_id != 3 )
                    {

                        $text       = '☠️ ' . $user->get_name();
                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-kill-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
            break;
        // آشپز
        case ROLE_Ashpaz:

            $select         = $selector->user()->select(ROLE_Ashpaz);
            $bazpors_select = $selector->user()->select(ROLE_Bazpors);
            $last_select    = $selector->user()->select(ROLE_Ashpaz, 'last-select');

            if ( !$select->is($user_select) )
            {

                if ( $user_select->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>آشپز</u>" . ' قصد حمله به شما را دارد.';
                    $user_select->SendMessageHtml();

                }

                if ( $select->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>آشپز</u>" . 'از حمله به شما منصرف شدند.';
                    $select->SendMessageHtml();

                }

                $selector->set($user_select->getUserId(), ROLE_Ashpaz)->answerCallback();

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && !$last_select->is($user) )
                    {

                        $text       = '👨🏻‍🍳 ' . $user->get_name() . ( $user->is($user_select) ? '✔️' : '' );
                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_Ashpaz . '-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }
            else
            {

                if ( $select->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>آشپز</u>" . 'از حمله به شما منصرف شد.';
                    $select->SendMessageHtml();

                }

                $selector->delete(ROLE_Ashpaz);

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && !$last_select->is($user) )
                    {

                        $text       = '👨🏻‍🍳 ' . $user->get_name();
                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_Ashpaz . '-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
            break;
        // عنکوت
        case ROLE_Ankabot:

            $select   = $selector->user()->select(ROLE_Ankabot);
            $select_2 = $selector->user()->select(ROLE_Ankabot, 'select-2');

            if ( $select->is($user_select) )
            {

                $selector->delete(ROLE_Ankabot);
                if ( $user_select->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>عنکبوت</u>" . 'از حمله به شما منصرف شد.';
                    $user_select->SendMessageHtml();

                }

            }
            elseif ( $select_2->is($user_select) )
            {

                $selector->delete(ROLE_Ankabot, 'select-2');
                if ( $user_select->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>عنکبوت</u>" . 'از حمله به شما منصرف شد.';
                    $user_select->SendMessageHtml();

                }

            }
            elseif ( $select instanceof User && $select->getUserId() == 0 )
            {

                $selector->set($user_select->getUserId(), ROLE_Ankabot);

                if ( $user_select->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>عنکبوت</u>" . ' قصد حمله به شما را دارد .';
                    $user_select->SendMessageHtml();

                }

                if ( $select->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>عنکبوت</u>" . 'از حمله به شما منصرف شد.';
                    $select->SendMessageHtml();

                }

            }
            else
            {

                $selector->set($user_select->getUserId(), ROLE_Ankabot, 'select-2');

                if ( $user_select->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>عنکبوت</u>" . ' قصد حمله به شما را دارد .';
                    $user_select->SendMessageHtml();

                }

                if ( $select_2->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>عنکبوت</u>" . 'از حمله به شما منصرف شد.';
                    $select_2->SendMessageHtml();

                }

            }

            $select   = $selector->user()->select(ROLE_Ankabot);
            $select_2 = $selector->user()->select(ROLE_Ankabot, 'select-2');

            foreach ( $users_server as $user )
            {

                if ( $user->check($chatid) )
                {

                    $text       = '🕸 ' . $user->get_name() . ( ( $user->is($select) || $user->is($select_2) ) ? '✔️' : '' );
                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_Ankabot . '-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));


            break;
        // بازمانده
        case 'bazmandeh':
            $select = $selector->user()->select(ROLE_Bazmandeh);

            if ( !$select->is($user_select) )
            {

                $selector->set($chatid, ROLE_Bazmandeh)->answerCallback(function () {
                    return '🦺 شما امشب جلیقه دارید.';
                });

                $keyboard[][] = $telegram->buildInlineKeyboardButton('🦺 تن کردن' . '✔️', '', $day . '/server-' . $server->league_id . '-bazmandeh-' . $server->getId() . '-' . $user->getUserId());

            }
            else
            {

                $selector->delete(ROLE_Bazmandeh);

                $keyboard[][] = $telegram->buildInlineKeyboardButton('🦺 تن کردن', '', $day . '/server-' . $server->league_id . '-bazmandeh-' . $server->getId() . '-' . $user->getUserId());
            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
            break;
        // گرگ نما
        case 'gorg':

            $select         = $selector->user()->select(ROLE_Gorg);
            $bazpors_select = $selector->user()->select(ROLE_Bazpors);

            if ( !$select->is($user_select) )
            {

                if ( $user_select->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>گرگ</u>" . ' قصد حمله به شما را دارد .';
                    $user_select->SendMessageHtml();

                }

                if ( $select->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>گرگ</u>" . 'از حمله به شما منصرف شدند.';
                    $select->SendMessageHtml();

                }

                $selector->set($user_select->getUserId(), ROLE_Gorg)->answerCallback();

                if ( $server->league_id == 3 )
                {
                    $role_group_2 = $server->roleByGroup(3);
                    $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                    foreach ( $role_group_2 as $user )
                    {

                        if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                        {

                            $user->SendMessageHtml();

                        }

                    }
                }

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && $user->get_role()->group_id != 3 )
                    {

                        $text       = '🐺 ' . $user->get_name() . ( $user->is($user_select) ? '✔️' : '' );
                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-gorg-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }
            else
            {

                if ( $select->spy() )
                {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>گرگ</u>" . 'از حمله به شما منصرف شد.';
                    $select->SendMessageHtml();

                }

                $selector->delete(ROLE_Gorg);

                $role_group_2 = $server->roleByGroup(3);
                $message      = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
                foreach ( $role_group_2 as $user )
                {

                    if ( $user->check($bazpors_select) && $user->is_user_in_game() )
                    {

                        $user->SendMessageHtml();

                    }

                }

                foreach ( $users_server as $user )
                {

                    if ( $user->check($chatid) && $user->get_role()->group_id != 3 )
                    {

                        $text       = '🐺 ' . $user->get_name();
                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-gorg-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            break;
        // ............ SYSTEM ............
        // رای گیری
        case 'vote':

            if ( !check_time_chat($user->getUserId(), 1, 'vote') )
            {
                AnswerCallbackQuery($dataid, '⚠️ لطفا مجددا تلاش کنید.');
                exit();
            }


            if ( !$user_select->dead() )
            {

                if ( $user->hacked() )
                {

                    AnswerCallbackQuery($dataid, '🧑🏻‍💻 شما توسط هکر هک شده اید و امروز قادر به رای دادن نیستید.', true);

                    exit();

                }

                if ( $selector->select(ROLE_Naghel)->is($chatid) )
                {
                    AnswerCallbackQuery($dataid, '⚠️ شما لال شدید و امکان رای دادن ندارید.');
                    exit();
                }

                if ( $selector->user()->select(ROLE_Kalantar, 'last-select')->is($user) && !$selector->getUser(ROLE_Kalantar)->dead() )
                {
                    AnswerCallbackQuery($dataid, '❌ شما امروز نمیتوانید رای بدهید.', true);

                    exit();
                }

                $user_status = $user->getStatus();

                if ( $user_status == 'voting' && !$server->is() )
                {

                    $user_vote = $selector->user()->select($user->getUserId(), 'vote');

                    if ( !$user_vote->is($user_select) )
                    {

                        if ( $server->setUserId($user_select->getUserId())->getMetaUser('no-vote') != 'on' )
                        {

                            if ( $server->getMeta('court') != 'close' && $server->getStatus() != 'night' )
                            {

                                AnswerCallbackQuery($dataid, '🗳 شما به ' . $user_select->get_name() . ' رای دادید.');

                                $selector->set($user_select->getUserId(), $chatid, 'vote');

                                $votes           = get_votes_by_server($server->getId());
                                $bazpors         = $selector->getUser(ROLE_Bazpors);
                                $vote_users      = [];
                                $user_vote_index = 1;

                                foreach ( $votes as $id => $vote )
                                {

                                    if ( isset($vote->meta_value) && isset($vote->user_id) && $vote->user_id > 0 )
                                    {

                                        $vote_users[$vote->meta_value][] = $vote->user_id;
                                        if ( $chatid == $vote->user_id )
                                        {

                                            $user_vote_index = $id + 1;

                                        }

                                    }

                                }

                                if ( $bazpors->is($chatid) )
                                {

                                    $i              = 0;
                                    $bazpors_select = $selector->user()->select(ROLE_Bazpors);

                                    foreach ( $users_server as $item )
                                    {

                                        if ( $item->check($chatid) && $server->setUserId($item->getUserId())->getMetaUser('no-vote') != 'on' )
                                        {

                                            $text           = '🗳 ' . $item->get_name() . ( $item->is($user_select) ? '✔️' : '' );
                                            $keyboard[$i][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-vote-' . $server->getId() . '-' . $item->getUserId());

                                            $text           = '🔗 ' . $item->get_name() . ' ' . ( $bazpors_select->is($item) ? '✔️' : '' );
                                            $keyboard[$i][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-question-' . $server->getId() . '-' . $item->getUserId());
                                            $i ++;

                                        }

                                    }

                                }
                                else
                                {

                                    foreach ( $users_server as $item )
                                    {

                                        if ( $item->check($chatid) && $server->setUserId($item->getUserId())->getMetaUser('no-vote') != 'on' )
                                        {


                                            $text       = '🗳 ' . $item->get_name() . ' ' . ( $user_select->is($item) ? '[[' . $user_vote_index . ']] ✔️' : '' );
                                            $keyboard[] = [
                                                $telegram->buildInlineKeyboardButton(
                                                    __replace__($text, [
                                                        '[[10]]' => '🔟',
                                                        '[[11]]' => '1️⃣1️⃣',
                                                        '[[12]]' => '1️⃣2️⃣',
                                                        '[[13]]' => '1️⃣3️⃣',
                                                        '[[1]]'  => '1️⃣',
                                                        '[[2]]'  => '2️⃣',
                                                        '[[3]]'  => '3️⃣',
                                                        '[[4]]'  => '4️⃣',
                                                        '[[5]]'  => '5️⃣',
                                                        '[[6]]'  => '6️⃣',
                                                        '[[7]]'  => '7️⃣',
                                                        '[[8]]'  => '8️⃣',
                                                        '[[9]]'  => '9️⃣',
                                                    ]), '', $day . '/server-' . $server->league_id . '-vote-' . $server->getId() . '-' . $item->getUserId()
                                                )
                                            ];

                                        }

                                    }

                                }


                                if ( $user_vote->getUserId() > 0 )
                                {

                                    $message = '▪️ [[user]] رای خود را پس گرفت و به [[user2]] رای داد .' . "\n";

                                }
                                else
                                {

                                    $message = '▪️ [[user]] به [[user2]] رای داد . ' . "\n";

                                }

                                __replace__($message, [
                                    '[[user]]'  => "<b>" . $user->get_name() . "</b>",
                                    '[[user2]]' => "<b><u>" . $user_select->get_name() . "</u></b>",
                                ]);

                                foreach ( $vote_users as $key => $value )
                                {

                                    $message .= '<b>' . '[[user]] ' . '[[count]]' . '</b>' . ' رای' . "\n";
                                    __replace__($message, [
                                        '[[user]]'  => "<u>" . name((int) $key, $server->getId()) . "</u>",
                                        '[[count]]' => "<u>" . count($value) . "</u>",
                                    ]);

                                }

                                foreach ( $users_server as $user )
                                {

                                    ( !$user->is_user_in_game() || $user->dead() || $user->sleep() ) || $user->SendMessageHtml();

                                    if ( $user->is($chatid) )
                                    {

                                        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

                                    }

                                }

                                // -------------------------------------------------------------------------------------------------------------


                                sleep(rand(0.1, 0.9));

                                if ( get_server_meta($server->getId(), 'status') != 'night' )
                                {

                                    $status = 'night';
                                    $ceil   = ceil($server->getPeopleAlive() / 2);
                                    foreach ( $vote_users as $key => $value )
                                    {

                                        if ( $ceil <= count($value) )
                                        {

                                            $server->updateMeta('court', 'close');
                                            $server->updateMeta('is', 'on');
                                            $message = '⚖️ [[user]] به دادگاه فراخوانده شد .' . "\n";
                                            $message .= 'متهم ۱۵ ثانیه فرصت دارد تا از خود دفاع کند .' . "\n";
                                            $message .= '💬 چت : فقط برای متهم';

                                            $accused = new User((int) $key, $server->getId());

                                            __replace__($message, [
                                                '[[user]]' => "<u>" . $accused->get_name() . "</u>"
                                            ]);

                                            if ( !is_server_meta($server->getId(), 'accused') )
                                            {

                                                update_server_meta($server->getId(), 'accused', $accused->getUserId());

                                            }

                                            $status = 'court-2';

                                            break;

                                        }

                                    }

                                    if ( $status == 'court-2' )
                                    {


                                        sleep(rand(0.1, 0.9));

                                        if ( get_server_meta($server->getId(), 'status') != 'night' )
                                        {

                                            $server->setStatus($status)->charge(15)->clearVotesMeta();

                                            foreach ( $users_server as $item )
                                            {

                                                if ( $item->is_user_in_game() && !is_server_meta($server->getId(), 'message-sended', $item->getUserId()) && !$item->sleep() )
                                                {

                                                    $result = SendMessage($item->getUserId(), $message, null, null, 'html');
                                                    if ( isset($result->message_id) )
                                                    {

                                                        add_server_meta($server->getId(), 'message-sended', 'sended', $item->getUserId());
                                                        $item->setStatus('voting');

                                                    }

                                                }

                                            }

                                        }

                                        $server->deleteMeta('is');

                                    }

                                }
                                else
                                {

                                    AnswerCallbackQuery($dataid, '🔴 هم اکنون امکان رای گیری وجود ندارد.');

                                }

                            }
                            else
                            {

                                AnswerCallbackQuery($dataid, '🔴 هم اکنون امکان رای گیری وجود ندارد.');

                            }
                        }
                        else
                        {

                            AnswerCallbackQuery($dataid, '⛔️ این کاربر از جادو استفاده کرده است.');

                        }

                    }

                    else
                    {

                        AnswerCallbackQuery($dataid, '⛔️ نمیتوانید به این کاربر رای بدهید.');

                    }

                }
                else
                {

                    AnswerCallbackQuery($dataid, '🔴 هم اکنون امکان رای گیری وجود ندارد.');

                }

            }
            else
            {

                AnswerCallbackQuery($dataid, '🔴 این کاربر مرده است.');

            }

            break;
        // رای به گناه
        // رای به بی گناه
        case 'court':
        case '^court':

            if ( $user->hacked() )
            {
                AnswerCallbackQuery($dataid, '🧑🏻‍💻 شما توسط هکر هک شده اید و امروز قادر به رای دادن نیستید.', true);
                exit();
            }

            if ( $selector->select(ROLE_Naghel)->is($chatid) )
            {
                AnswerCallbackQuery($dataid, '⚠️ شما لال شدید و امکان رای دادن ندارید.');
                exit();
            }

            try
            {

                if ( update_server_meta($server->getId(), 'vote', $data[2], $chatid) )
                {

                    $keyboard = [
                        [
                            $telegram->buildInlineKeyboardButton(( $data[2] == 'court' ? '' : '✔️ ' ) . 'بی‌گناه', '', $day . '/server-' . $data[1] . '-^court-' . $server->getId() . '-' . $user_select->getUserId()),
                            $telegram->buildInlineKeyboardButton(( $data[2] == 'court' ? '✔️ ' : '' ) . 'گناهکار', '', $day . '/server-' . $data[1] . '-court-' . $server->getId() . '-' . $user_select->getUserId()),
                        ]
                    ];

                    switch ( $user->getRoleId() )
                    {
                        case ROLE_Ghazi:
                            if ( !is_server_meta($server->getId(), 'ghazi') )
                            {

                                delete_server_meta($server->getId(), 'ghazi', ROLE_Ghazi);
                                $keyboard[][] = $telegram->buildInlineKeyboardButton('❌ ابطال', '', $day . '/server-' . $server->league_id . '-pass_voting-' . $server->getId());

                            }
                            break;
                        case ROLE_Fadaii:

                            delete_server_meta($server->getId(), 'fadaii');
                            $keyboard[][] = $telegram->buildInlineKeyboardButton('فدا شدن', '', $day . '/server-' . $server->league_id . '-fadaii-' . $server->getId());

                            break;

                        case ROLE_Big_Khab:

                            if ( !is_server_meta($server->getId(), 'bigKhan', ROLE_Big_Khab) )
                            {

                                $keyboard[] = [
                                    $telegram->buildInlineKeyboardButton(( '🟢 بی‌گناه' ), '', $day . '/server-' . $server->league_id . '-big_khab-' . $server->getId() . '-' . 2),
                                    $telegram->buildInlineKeyboardButton(( '🔴 گناهکار' ), '', $day . '/server-' . $server->league_id . '-big_khab-' . $server->getId() . '-' . 1),
                                ];
                                $selector->delete(ROLE_Big_Khab);

                            }

                            break;
                    }

                    EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

                }
                else
                {

                    throw new Exception('ERROR ON VOTE 2 IN BOT! HELP ME PLEASE!');

                }

            }
            catch ( Exception $exception )
            {

                throw new Exception('ERROR ON INSERT VOTE 2 IN BOT! Message: ' . $exception->getMessage());

            }
            break;
        // -----------------------------------------------------------

    }

    if ( get_server_meta($server->getId(), 'is-online', $chatid) == 'no' )
    {

        add_server_meta($server->getId(), 'is-online', 'yes', $chatid);

    }

}
catch ( Exception $exception )
{

    throw new Exception($exception->getMessage());

}