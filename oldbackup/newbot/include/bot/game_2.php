<?php
/** @noinspection ALL */


if (!isset($data[3])) {
    AnswerCallbackQuery($dataid, '⚠️خطا، در شناسایی سرور مشکلی رخ داده است.', true);
    throw new Exception("ERROR ON SCANNING SERVER");
}

use library\Role;
use library\Server;
use library\User;

$server = new Server($data[3]);
$user = new User($chatid, $server->getId());
$current_user = $user;
$body_parts = ['hand', 'eye', 'lung', 'heart'];
$user_select = new User(0, $server->getId());
// Check if $data[4] is set and is one of the body parts
if (isset($data[4]) && in_array($data[4], $body_parts)) {
    $user_select = $data[4]; // Store the body part as a string
} else {
    // Otherwise, create a User object as before
    $user_select = new User($data[4] ?? 0, $server->getId());
}
$users_server = $server->users();
$day = $server->day();
$selector = new Role($server->getId());


if ($user->dead() && $data[2] != 'cards' && $data[2] != "cobcob") {
    if ($user->get_role()->id != ROLE_Ehdagar) {
        AnswerCallbackQuery($dataid, '⚠️خطا، شما مرده اید!', true);
        exit();
    }
} elseif ($server->getStatus() == 'closed') {
    AnswerCallbackQuery($dataid, '📛 این سرور بسته شده است.', true);
    exit();
} elseif ($data_day[0] != $day) {
    AnswerCallbackQuery($dataid, '🚸 این پنل منقضی شده است. لطفا از پنل های جدید استفاده کنید.', true);
    exit();
} elseif ($server->getMeta('is') == 'on') {
      if ($user->get_role()->id == ROLE_Bazpors) {
        AnswerCallbackQuery($dataid, 'یکم عجله کردی برای انتخاب کردن،بیشتر فکر کن شاید تصمیم بهتری گرفتی 😉', true);

    }else{
        AnswerCallbackQuery($dataid, '⚠️ مجددا امتحان کنید', true);
    }
    exit();
}

$user_red_carpet = null;
$user_red_carpet = get_server_meta_user($server->getId(), 'card-red_carpet', $day);


$keyboard = [];
switch ($data[2]) {

    // ............ GROUP 1 ............
    // کارآگاه
    case ROLE_Karagah:
    case 'search':

        $select = $selector->user()->select(ROLE_Karagah);

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_Karagah)->answerCallback();

            foreach ($users_server as $user) {
                if ($user->check($chatid)) {

                    $text = '🔦 ' . $user->get_name() . ($user->is($user_select) ? '✔️' : '');
                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-search-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }
            }

        } else {

            $selector->delete(ROLE_Karagah);

            foreach ($users_server as $user) {

                if ($user->check($chatid)) {

                    $text = '🔦 ' . $user->get_name();
                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-search-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
        break;
    // پزشک
    case ROLE_Pezeshk:
    case 'heal':

        $select = $selector->user()->select(ROLE_Pezeshk); // get select old 
        // $user_select  new user selectid
        $status_doctor = is_server_meta($server->getId(), 'doctor', ROLE_Pezeshk);
        $status_doctor_day = get_server_meta($server->getId(), 'doctor', ROLE_Pezeshk);


        if ($day == 1) {

            $select_2 = $selector->user()->select(ROLE_Pezeshk, 'select-2');

            if ($select->is($user_select)) {

                $selector->delete(ROLE_Pezeshk);
                $select->setUserId(0);

            } elseif ($select_2->is($user_select)) {

                $selector->delete(ROLE_Pezeshk, 'select-2');
                $select_2->setUserId(0);

            } elseif ($select instanceof User && $select->getUserId() <= 0) {

                $selector->set($user_select->getUserId(), ROLE_Pezeshk)->answerCallback();
                $select->setUserId($user_select->getUserId());

            } else {

                $selector->set($user_select->getUserId(), ROLE_Pezeshk, 'select-2')->answerCallback();
                $select_2->setUserId($user_select->getUserId());

            }


            if ($select->is($selector->getUser(ROLE_Pezeshk)) || $select_2->is($selector->getUser(ROLE_Pezeshk))) {
                $server->setUserId(ROLE_Pezeshk)->updateMetaUser('doctor', $day);
            } else {
                $selector->delete(ROLE_Pezeshk, 'doctor');
            }

            foreach ($users_server as $user) {

                $text = '💉️ ' . $user->get_name() . ($user->is($select) || $user->is($select_2) ? '✔️' : '');
                $keyboard[] = [
                    $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_Pezeshk . '-' . $server->getId() . '-' . $user->getUserId())
                ];

            }


        } else {

            if (!$select->is($user_select)) {

                $selector->set($user_select->getUserId(), ROLE_Pezeshk)->answerCallback(function (User $user) {
                    return '💉 شما ' . $user->get_name() . ' را نجات دادید.';
                });


                if ($current_user->getUserId() == $user_select->getUserId()) { // نجات خودش
                    $server->setUserId(ROLE_Pezeshk)->updateMetaUser('doctor', $day);
                } elseif ($day == $status_doctor_day) {

                    $selector->delete(ROLE_Pezeshk, 'doctor');
                    $status_doctor = false;

                }

                $shahrdar_used = false;
                if ($server->getMeta('shahrdar')) {
                    $shahrdar = $selector->getUser(ROLE_Shahrdar);
                    $shahrdar_used = true;
                }

                foreach ($users_server as $user) {

                    if (!$user->dead() && (!$user->is($chatid) || !$status_doctor) && (!$shahrdar_used || !$user->is($shahrdar))) {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('💉 ' . $user->get_name() . ($user->is($user_select) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-heal-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            } else {
                // $server->setUserId( ROLE_Pezeshk )->updateMetaUser( 'doctor', $day );
                $selector->delete(ROLE_Pezeshk);


                if ($day == $status_doctor_day) {
                    $status_doctor = false;
                    $selector->delete(ROLE_Pezeshk, 'doctor');
                }
                foreach ($users_server as $user) {
                    if (!$user->dead() && (!$user->is($chatid) || !$status_doctor)) {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('💉 ' . $user->get_name() . ($user->is($user_select) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-heal-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }
                }

            }

        }


        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
        break;

    case ROLE_Ehdagar:
    case 'transplant':
        // $select = $selector->user()->select(68, 'used_parts');

        // بررسی اینکه آیا کاربر انتخاب شده ROLE_Ehdagar وجود دارد و نقش ROLE_Ehdagar وجود دارد
        if ($server->role_exists(ROLE_Ehdagar)) {

            // رسیدگی به گزینه‌های مختلف پیوند
            switch ($user_select) {
                case 'heart':
                    $response = 'روند پیوند قلب آغاز شد.';
                    break;
                case 'eye':
                    $response = 'روند پیوند چشم آغاز شد.';
                    break;
                case 'hand':
                    $response = 'روند پیوند دست آغاز شد.';
                    break;
                case 'lung':
                    $response = 'روند پیوند ریه آغاز شد.';
                    break;
                default:
                    $response = 'گزینه پیوند نامعتبر انتخاب شده است.';
                    break;
            }

            // ذخیره پاسخ در متا به عنوان 'used_parts'
            $serialized_used_parts = $server->setUserId(ROLE_Ehdagar)->getMetaUser('used_parts');
            $used_parts = unserialize($serialized_used_parts);
            if (!is_array($used_parts)) {
                $used_parts = [];
            }

            // Add the new part with the day to the array
            $used_parts[$day] = ['part' => $user_select, 'notified' => false];
            $serialized_used_parts = serialize($used_parts);
            $server->setUserId(ROLE_Ehdagar)->updateMetaUser('used_parts', $serialized_used_parts);

            // ساخت لیستی از کاربران زنده برای انتخاب گیرنده پیوند
            $keyboard = [];
            foreach ($users_server as $user) {
                // Check if the user is not a receiver on previous days
                $isPreviousReceiver = false;
                foreach ($used_parts as $used_day => $parts) {
                    if ($used_day != $day && $parts['receiver'] == $user->getUserId()) {
                        $isPreviousReceiver = true;
                        break;
                    }
                }

                // Add the user to the keyboard if not a previous receiver
                if (!$isPreviousReceiver && $user->check($chatid)) {
                    // $selected = ($user_select->getUserId() == $user->getUserId()) ? '✔️' : '';
                    $selected = '';
                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton(
                            '⛑ ' . $user->get_name() . ' ' . $selected,
                            '',
                            $day . '/server-' . $server->league_id . '-select_receiver-' . $server->getId() . '-' . $user->getUserId()
                        )
                    ];
                }
            }



            // در صورتی که کاربر زنده‌ای موجود نباشد
            if (empty($keyboard)) {
                AnswerCallbackQuery($dataid, 'کاربر زنده‌ای برای پیوند موجود نیست.');
                $keyboard[] = [
                    $telegram->buildInlineKeyboardButton(
                        '↪️ ' . 'برگشت ',
                        '',
                        $day . '/server-' . $server->league_id . '-back_to_part-' . $server->getId() . '-' . $user->getUserId()
                    )
                ];
                EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
            } else {
                $keyboard[] = [
                    $telegram->buildInlineKeyboardButton(
                        '↪️ ' . 'برگشت ',
                        '',
                        $day . '/server-' . $server->league_id . '-back_to_part-' . $server->getId() . '-' . $user->getUserId()
                    )
                ];
                EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            }

        } else {
            AnswerCallbackQuery($dataid, 'نقش Ehdagar یافت نشد یا کاربری انتخاب نشده است.');
        }
        break;
    case 'back_to_part':
        if ($current_user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($current_user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($current_user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            if (isset($framason_team) && in_array($current_user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } else {

            if ($day > 1) {
                $message .= '🗞 مأموریت : انتخاب کنید میخواهید کدام عضو را  اهدا کنید. .' . "\n";
            }
            if (isset($framason_team) && in_array($current_user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($current_user->check($bazpors_select) && !$current_user->is($dozd_select) && $day > 1) {

            // Fetch the serialized 'used_parts' data and unserialize it
            $serialized_used_parts = $server->setUserId(ROLE_Ehdagar)->getMetaUser('used_parts');
            $used_parts = unserialize($serialized_used_parts);

            // Initialize an array to keep track of available parts
            $available_parts = ['heart' => '🫀 قلب', 'eye' => '👁 چشم', 'hand' => '✍🏻 دست', 'lung' => '🫁 ریه'];

            // Remove parts selected on previous days and find selected part for today
            $selected_part_for_today = '';
            foreach ($used_parts as $used_day => $parts_info) {
                if ($used_day != $day && isset($parts_info['part'])) {
                    unset($available_parts[$parts_info['part']]);
                }
                if ($used_day == $day) {
                    $selected_part_for_today = $parts_info['part'];
                }
            }

            // Build keyboard buttons for each available part
            $keyboard = [];
            foreach ($available_parts as $part => $label) {
                $selected = ($selected_part_for_today == $part) ? '✔️' : '';
                $keyboard[] = [$telegram->buildInlineKeyboardButton($label . ' ' . $selected, '', $day . '/server-' . $server->league_id . '-transplant-' . $server->getId() . '-' . $part)];
            }

            // Send the message with the keyboard if there are available parts
            if (!empty($keyboard)) {
                EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
            } else {
                $user->SendMessageHtml("تمامی قطعات برای امروز انتخاب شده‌اند.");
            }

        } else {
            $user->SendMessageHtml($message);
        }
        break;
    case 'select_receiver':
        // Fetch the serialized 'used_parts' data and unserialize it
        $serialized_used_parts = $server->setUserId(ROLE_Ehdagar)->getMetaUser('used_parts');
        $used_parts = unserialize($serialized_used_parts);

        // Check if the array for the current day already exists
        if (array_key_exists($day, $used_parts)) {
            // Update the array for the current day with the selected receiver
            $used_parts[$day]['receiver'] = $user_select->getUserId();
        } else {
            // If the array for the current day does not exist, create a new entry
            $used_parts[$day] = ['receiver' => $user_select->getUserId()];
        }

        // Serialize and save the updated 'used_parts' data
        $server->setUserId(ROLE_Ehdagar)->updateMetaUser('used_parts', serialize($used_parts));
        $keyboard = [];
        foreach ($users_server as $user) {
            // Check if the user is not a receiver on previous days
            $isPreviousReceiver = false;
            foreach ($used_parts as $used_day => $parts) {
                if ($used_day != $day && $parts['receiver'] == $user->getUserId()) {
                    $isPreviousReceiver = true;
                    break;
                }
            }

            // Add the user to the keyboard if not a previous receiver
            if (!$isPreviousReceiver && $user->check($chatid)) {
                $selected = ($user_select->getUserId() == $user->getUserId()) ? '✔️' : '';
                $keyboard[] = [
                    $telegram->buildInlineKeyboardButton(
                        '⛑ ' . $user->get_name() . ' ' . $selected,
                        '',
                        $day . '/server-' . $server->league_id . '-select_receiver-' . $server->getId() . '-' . $user->getUserId()
                    )
                ];
            }
        }

        if (empty($keyboard)) {
            AnswerCallbackQuery($dataid, 'کاربر زنده‌ای برای پیوند موجود نیست.');
            $keyboard[] = [
                $telegram->buildInlineKeyboardButton(
                    '↪️ ' . 'برگشت ',
                    '',
                    $day . '/server-' . $server->league_id . '-back_to_part-' . $server->getId() . '-' . $user->getUserId()
                )
            ];
            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
        } else {
            $keyboard[] = [
                $telegram->buildInlineKeyboardButton(
                    '↪️ ' . 'برگشت ',
                    '',
                    $day . '/server-' . $server->league_id . '-back_to_part-' . $server->getId() . '-' . $user->getUserId()
                )
            ];
            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        }
        // Respond to the callback query
        AnswerCallbackQuery($dataid, 'شما با موفقیت گیرنده انتخابی را ثبت کردید.');
        break;
    case 'eye_select':
        $day_key = $day - 1; // Since the action is for the previous day

        // Load the existing used_parts array
        $used_parts = unserialize($server->setUserId(ROLE_Ehdagar)->getMetaUser('used_parts'));
        $selected_user_id = $user_select->getUserId();
        if (isset($used_parts[$day_key]) && isset($used_parts[$day_key]['part'])) {
            if ($used_parts[$day_key]['part'] == 'eye') {
                $used_parts[$day_key]['selected_user'] = $selected_user_id->getUserId();
            }
        }
        $serialized_used_parts = serialize($used_parts);
        $server->setUserId(ROLE_Ehdagar)->updateMetaUser('used_parts', $serialized_used_parts);
        // Prepare the keyboard with user choices
        $keyboard = [];
        foreach ($users_server as $user) {
            if ($user->check($chatid)) {
                $isSelected = ($user->getUserId() == $selected_user_id) ? '✔️' : '';
                $text = '👁 ' . $user->get_name() . $isSelected;
                $keyboard[] = [
                    $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-eye_select-' . $server->getId() . '-' . $user->getUserId())
                ];
            }
        }

        // Send or update the message with the new keyboard
        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        // Rest of the code...
        break;


    // اسنایپر
    case ROLE_Sniper:
    case 'fight':

        $select = $selector->user()->select(ROLE_Sniper);

        $kalantar = $selector->getUser(ROLE_Kalantar);

        if ($select->getUserId() > 0 && $server->role_exists(ROLE_Kalantar) && !$kalantar->dead()) {

            AnswerCallbackQuery($dataid, '❌ امکان تغییر هدف وجود ندارد .');

        } else {

            if (!$select->is($user_select)) {

                if ($user_select->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اسنایپر</u>" . ' قصد حمله به شما را دارد .';
                    $user_select->SendMessageHtml();

                }

                if ($select->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اسنایپر</u>" . 'از حمله به شما منصرف شد.';
                    $select->SendMessageHtml();

                }

                $selector->set($user_select->getUserId(), ROLE_Sniper)->answerCallback();

                foreach ($users_server as $user) {

                    if ($user->check($chatid)) {

                        $text = '🔫 ' . $user->get_name() . ($user->is($user_select) ? '✔️' : '');
                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-fight-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

                if ($server->role_exists(ROLE_Kalantar) && !$kalantar->dead()) {

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

            } else {

                if ($select->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اسنایپر</u>" . 'از حمله به شما منصرف شد.';
                    $select->SendMessageHtml();

                }

                $selector->delete(ROLE_Sniper);

                foreach ($users_server as $user) {

                    if ($user->check($chatid)) {

                        $text = '🔫 ' . $user->get_name();
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
    case ROLE_Bazpors:
    case 'question':

        if (!$user_select->dead()) {

            if ($selector->user()->select(ROLE_TohmatZan, 'last-select')->is($user) && $server->role_exists(ROLE_TohmatZan)) {

                AnswerCallbackQuery($dataid, '❌ شما امروز نمیتوانید کسی را زندانی کنید .', true);

                exit();

            }

            $status = $user->getStatus();
            $status_server = $server->getStatus();

            if (in_array($status_server, ['voting', 'court', 'court-2', 'court-3', 'night'])) {

                $selector->set($user_select->getUserId(), ROLE_Bazpors)->answerCallback();

                $i = 0;

                $user_vote = $selector->getInt()->select($selector->getUser(ROLE_Bazpors)->getUserId(), 'vote');


                foreach ($users_server as $item) {

                    if ($item->check($chatid) && get_server_meta($server->getId(), 'no-vote', $item->getUserId()) != 'on') {

                        if (!$user_red_carpet && $day != 1) {
                            $text = '🗳 ' . $item->get_name() . ($item->is($user_vote) ? '✔️' : '');
                            $keyboard[$i][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-vote-' . $server->getId() . '-' . $item->getUserId());
                        }
                        $text = '🔗 ' . $item->get_name() . ' ' . ($item->is($user_select) ? '✔️' : '');
                        $keyboard[$i][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-question-' . $server->getId() . '-' . $item->getUserId());
                        $i++;

                    }
                }
                EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            } elseif ($status == 'game_started' || ceil($server->getPeopleAlive() / 2) == 1 || $server->setUserId(ROLE_Dalghak)->getMetaUser('dalghak') == 'use') {

                $selector->set($user_select->getUserId(), ROLE_Bazpors)->answerCallback();

                foreach ($users_server as $item) {

                    if ($item->check($chatid)) {

                        $text = '🔗 ' . $item->get_name() . ' ' . ($item->is($user_select) ? '✔️' : '');
                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-question-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

                EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

            } else {

                AnswerCallbackQuery($dataid, '⚠️خطا، هم اکنون نمیتوانید کسی را زندانی کنید!');

            }

        } else {

            AnswerCallbackQuery($dataid, '⚠️ خطا، کاربری که انتخاب کرده اید مرده است.');

        }

        break;
    // بازپرس - دستور محکوم
    case 'bazpors_kill':

        $selector->set($user_select->getUserId(), ROLE_Bazpors, 'kill')->answerCallback(function (User $user) {
            return '💢 ' . $user->get_name() . ' پس از اعلام صبح اعدام خواهد شد.';
        });

        if ($user_select->spy()) {

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

        if ($user_select->spy()) {

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
    case ROLE_Ghazi:
    case 'pass_voting':
        if ($server->getStatus() == 'court-3') {

            if (!is_server_meta($server->getId(), 'ghazi')) {

                if (!is_server_meta($server->getId(), 'ghazi', ROLE_Ghazi)) {

                    $server->setUserId(ROLE_Ghazi)->updateMetaUser('ghazi', 'use');
                    $selector->delete($chatid, 'vote');
                    $accused = $server->accused();
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

                } else {

                    AnswerCallbackQuery($dataid, '⛔️ شما قبلا از قدرت خود استفاده کرده اید.');

                }

            } else {

                delete_server_meta($server->getId(), 'ghazi', ROLE_Ghazi);
                $selector->delete($chatid, 'vote');
                $accused = $server->accused();
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

        } else {

            $selector->answerCallback(function () {
                return '🔴 اکنون نمیتوانید از رای گیری جلوگیری کنید';
            });

        }
        break;
    // پلیس
    case ROLE_Police:
    case 'police':

        $police_status = is_server_meta($server->getId(), 'select', ROLE_Police);

        if (!$police_status) {

            $selector->set($user->getUserId(), ROLE_Police);
            $keyboard[][] = $telegram->buildInlineKeyboardButton('👮🏻‍♂️ هوشیار بمانید ✔️', '', $day . '/server-' . $server->league_id . '-police-' . $server->getId() . '-' . $user->getUserId());

        } else {

            $selector->delete(ROLE_Police);
            $keyboard[][] = $telegram->buildInlineKeyboardButton('👮🏻‍♂️ هوشیار بمانید', '', $day . '/server-' . $server->league_id . '-police-' . $server->getId());

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
        break;
    // دیدبان
    case ROLE_Didban:
    case 'did_ban':
        $select = $selector->user()->select(ROLE_Didban);

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_Didban)->answerCallback();

            foreach ($users_server as $item) {

                if ($item->check($chatid)) {

                    $text = '👀 ' . $item->get_name() . ($item->is($user_select) ? '✔️ ' : '');
                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-did_ban-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_Didban);
            foreach ($users_server as $item) {

                if ($item->check($chatid)) {

                    $text = '👀 ' . $item->get_name();
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

        $selector->set($user_select->getUserId(), ROLE_TofangDar)->set(1, ROLE_TofangDar, 'type')->answerCallback(function (User $user) {
            return 'شما یک فشنگ مشقی در اختیار ' . $user->get_name() . ' قرار دادید .';
        });

        $message = '🤵🏻‍♂تفنگدار یک فشنگ در اختیار ' . $user_select->get_name() . ' قرار داد .';
        foreach ($users_server as $item) {

            if ($item->check($user_select) && $item->is_ban()) {

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

        $selector->set($user_select->getUserId(), ROLE_TofangDar)->set(2, ROLE_TofangDar, 'type')->answerCallback(function (User $user) {
            return 'شما یک فشنگ جنگی در اختیار ' . $user->get_name() . ' قرار دادید .';
        });

        $message = '🤵🏻‍♂تفنگدار یک فشنگ در اختیار ' . $user_select->get_name() . ' قرار داد .';
        foreach ($users_server as $item) {

            if ($item->check($user_select)) {

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

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_TofangDar, 'attacker')->answerCallback();

            if ($user_select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>تفنگدار</u>" . ' قصد حمله به شما را دارد .';
                $user_select->SendMessageHtml();

            }

            if ($select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>تفنگدار</u>" . 'از حمله به شما منصرف شد.';
                $select->SendMessageHtml();

            }

            foreach ($users_server as $item) {

                if ($item->check($chatid)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name() . ($item->is($user_select) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-tofang_dar-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_TofangDar, 'attacker');

            if ($user_select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>تفنگدار</u>" . 'از حمله به شما منصرف شد.';
                $user_select->SendMessageHtml();

            }

            foreach ($users_server as $item) {

                if ($item->check($chatid)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-tofang_dar-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
        break;
    // محقق
    case ROLE_Mohaghegh:
    case 'search_mohaghegh':
        $select = $selector->user()->select(ROLE_Mohaghegh);

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_Mohaghegh)->answerCallback();

            foreach ($users_server as $item) {

                if ($item->check($chatid)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔎 ' . $item->get_name() . ($item->is($user_select) ? '✔️ ' : ''), '', $day . '/server-' . $server->league_id . '-search_mohaghegh-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_Mohaghegh);

            foreach ($users_server as $item) {

                if ($item->check($chatid)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔎 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-search_mohaghegh-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
        break;
    // معمار
    case ROLE_Memar:
    case 'memar':

        $select = $selector->user()->select(ROLE_Memar);
        $power = $selector->select(ROLE_Memar, 'power');

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_Memar)->answerCallback();

            foreach ($users_server as $item) {

                if (!$item->dead() && (!$item->is($chatid) || !$power->is($chatid))) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🏗 ' . $item->get_name() . ($item->is($user_select) ? '🔨' : ''), '', $day . '/server-' . $server->league_id . '-memar-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_Memar);

            foreach ($users_server as $item) {

                if (!$item->dead() && (!$item->is($chatid) || !$power->is($chatid))) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🏗 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-memar-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
        break;
    // کشیش
    case ROLE_Keshish:
    case 'keshish':

        if (!is_server_meta($server->getId(), 'keshish')) {

            if ($selector->getString()->select(ROLE_Keshish) != 'on') {

                update_server_meta($server->getId(), 'select', 'on', ROLE_Keshish);
                $selector->answerCallback(function () {
                    return 'فردا همه منزه هستند.';
                });

                $keyboard[] = [
                    $telegram->buildInlineKeyboardButton('✔️ دعا کردن 🤲🏻', '', $day . '/server-' . $server->league_id . '-keshish-' . $server->getId() . '-' . $user->getUserId())
                ];

            } else {

                $selector->delete(ROLE_Keshish);

                $keyboard[] = [
                    $telegram->buildInlineKeyboardButton('دعا کردن 🤲🏻', '', $day . '/server-' . $server->league_id . '-keshish-' . $server->getId() . '-' . $user->getUserId())
                ];

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        } else {

            $selector->answerCallback(function () {
                return '🚫 شما قبلا از قدرت خود استفاده کرده اید.';
            });

        }

        break;
    // فدایی
    case ROLE_Fadaii:
    case 'fadaii':

        if ($server->getStatus() == 'court-3') {

            $accused = $server->accused();

            $keyboard[] = [
                $telegram->buildInlineKeyboardButton('بی‌گناه', '', $day . '/server-' . $server->league_id . '-^court-' . $server->getId() . '-' . $accused->getUserId()),
                $telegram->buildInlineKeyboardButton('گناهکار', '', $day . '/server-' . $server->league_id . '-court-' . $server->getId() . '-' . $accused->getUserId()),
            ];

            if (!is_server_meta($server->getId(), 'fadaii')) {

                add_server_meta($server->getId(), 'fadaii', 'use');
                $selector->delete($chatid, 'vote');
                $keyboard[][] = $telegram->buildInlineKeyboardButton('فدایی شدن ✔️', '', $day . '/server-' . $server->league_id . '-fadaii-' . $server->getId() . '-' . $user->getUserId());

            } else {

                delete_server_meta($server->getId(), 'fadaii');
                $selector->delete($chatid, 'vote');
                $keyboard[][] = $telegram->buildInlineKeyboardButton('فدایی شدن', '', $day . '/server-' . $server->league_id . '-fadaii-' . $server->getId() . '-' . $user->getUserId());

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        } else {

            $selector->answerCallback(function () {
                return '🔴 اکنون نمیتوانید از رای گیری جلوگیری کنید';
            });

        }

        break;
    // کلانتر
    case ROLE_Kalantar:
    case 'kalantar':

        $select = $selector->user()->select(ROLE_Kalantar);

        $last_select = $selector->user()->select(ROLE_Kalantar, 'last-select');

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_Kalantar)->answerCallback();

            foreach ($users_server as $item) {

                if ($item->check($user) && !$last_select->is($item)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('👨‍✈️ ' . $item->get_name() . ($item->is($user_select) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-kalantar-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_Kalantar);

            foreach ($users_server as $item) {

                if ($item->check($user) && !$last_select->is($item)) {

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
            $chatid,
            $messageid,
            $telegram->buildInlineKeyBoard([
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
            $chatid,
            $messageid,
            $telegram->buildInlineKeyBoard([
                [
                    $telegram->buildInlineKeyboardButton('👍 تایید ', '', $day . '/server-' . $server->league_id . '-kalantar_ok-' . $server->getId() . '-' . $user->getUserId()),
                    $telegram->buildInlineKeyboardButton('👎 عدم تایید' . '✔️', '', $day . '/server-' . $server->league_id . '-kalantar_false-' . $server->getId() . '-' . $user->getUserId()),
                ]
            ])
        );
        $selector->delete(ROLE_Kalantar, 'power-select');

        break;
    // کابوی
    case ROLE_Kaboy:
    case 'kaboy':
        $select = $selector->user()->select(ROLE_Kaboy);

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_Kaboy)->answerCallback();

            foreach ($users_server as $item) {

                if ($item->check($chatid)) {

                    $text = '🕴 ' . $item->get_name() . ($item->is($user_select) ? '✔️' : '');
                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-kaboy-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_Kaboy);

            foreach ($users_server as $item) {

                if ($item->check($chatid)) {

                    $text = '🕴 ' . $item->get_name();
                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-kaboy-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }
        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
        break;
    // عینک ساز
    case ROLE_EynakSaz:
    case 'eynak':

        if ($selector->getInt()->select(ROLE_EynakSaz) <= 0) {

            $selector->set($user_select->getUserId(), ROLE_EynakSaz)->answerCallback(function (User $user) {
                return 'شما یک عینک در اختیار ' . $user->get_name() . ' قرار دادید .';
            });

            foreach ($users_server as $item) {

                if ($item->check($user_select)) {

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

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_EynakSaz, 'attacker')->answerCallback();

            foreach ($users_server as $item) {

                if ($item->check($chatid)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔍 ' . $item->get_name() . ($item->is($user_select) ? ' ✔️' : ''), '', $day . '/server-' . $server->league_id . '-eynak_2-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_EynakSaz, 'attacker');

            foreach ($users_server as $item) {

                if ($item->check($chatid)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔍 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-eynak_2-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    // فرشته
    case ROLE_Fereshteh:
    case 'healed':
        $select = $selector->user()->select(ROLE_Fereshteh);

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_Fereshteh)->answerCallback();

            foreach ($users_server as $item) {


                if (!$item->is($chatid) && $item->dead() && $item->get_role()->group_id == 1 && $item->is_user_in_game()) {

                    if ($item->getRoleId() != ROLE_Fadaii || !is_server_meta($server->getId(), 'fadaii', ROLE_Fadaii)) {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('👰‍♀️ ' . $item->get_name() . ($item->is($user_select) ? '✔️ ' : ''), '', $day . '/server-' . $server->league_id . '-healed-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

            }

        } else {

            $selector->delete(ROLE_Fereshteh);

            foreach ($users_server as $item) {

                if (!$item->is($chatid) && $item->dead() && $item->get_role()->group_id == 1 && $item->is_user_in_game()) {

                    if ($item->getRoleId() != ROLE_Fadaii || !is_server_meta($server->getId(), 'fadaii', ROLE_Fadaii)) {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('👰‍♀️ ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-healed-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

            }


        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
        break;

    // کوب کوب
    case ROLE_Cobcob:
    case 'cobcob':
        $select = $selector->user()->select(ROLE_Cobcob);

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_Cobcob)->answerCallback();

            foreach ($users_server as $item) {


                if ($item->is($chatid) && $item->dead() && $item->get_role()->group_id == 1 && $item->is_user_in_game() && get_server_meta($server->getId(), 'day_of_kill', $item->getUserId() )+1 < $day) {

                    if ($item->getRoleId() != ROLE_Fadaii || !is_server_meta($server->getId(), 'fadaii', ROLE_Fadaii)) {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('👰‍♀️ ' . $item->get_name() . ($item->is($user_select) ? '✔️ ' : ''), '', $day . '/server-' . $server->league_id . '-cobcob-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

            }

        } else {

            $selector->delete(ROLE_Cobcob);

            foreach ($users_server as $item) {

                if ($item->is($chatid) && $item->dead() && $item->get_role()->group_id == 1 && $item->is_user_in_game() && get_server_meta($server->getId(), 'day_of_kill', $item->getUserId() )+1 < $day ) {

                    if ($item->getRoleId() != ROLE_Fadaii || !is_server_meta($server->getId(), 'fadaii', ROLE_Fadaii)) {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('👰‍♀️ ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-cobcob-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

            }


        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
        break;

    // بادیگارد
    case ROLE_Bodygard:
    case 'bodygard':

        $select = $selector->user()->select(ROLE_Bodygard);
        $select_bodygard = $selector->select(ROLE_Bodygard, 'power');

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_Bodygard)->answerCallback();

            foreach ($users_server as $user) {

                if (!$user->dead() && (!$user->is($chatid) || !$select_bodygard->is($user))) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('💂‍♀️ ' . $user->get_name() . ($user->is($user_select) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . ROLE_Bodygard . '-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_Bodygard);

            foreach ($users_server as $user) {

                if (!$user->dead() && (!$user->is($chatid) || !$select_bodygard->is($user))) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('💂‍♀️ ' . $user->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_Bodygard . '-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    // خبرنگار
    case ROLE_KhabarNegar:
    case 'khabar_negar':

        $select = $selector->user()->select(ROLE_KhabarNegar);

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_KhabarNegar)->answerCallback();

            foreach ($users_server as $user) {

                if ($user->check($chatid)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('📸 ' . $user->get_name() . ($user->is($user_select) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-khabar_negar-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_KhabarNegar);

            foreach ($users_server as $user) {

                if ($user->check($chatid)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('📸 ' . $user->get_name(), '', $day . '/server-' . $server->league_id . '-khabar_negar-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    // زامبی
    case ROLE_Zambi:
    case 'zambi':

        $select = $selector->user()->select(ROLE_Zambi);

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_Zambi)->answerCallback();

            foreach ($users_server as $user) {

                if (!$user->is($chatid)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🧟‍♂️ ' . $user->get_name() . ($user->dead() ? '☠️' : '') . ($user->is($user_select) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-zambi-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_Zambi);

            foreach ($users_server as $user) {

                if (!$user->is($chatid)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🧟‍♂️ ' . $user->get_name() . ($user->dead() ? '☠️' : ''), '', $day . '/server-' . $server->league_id . '-zambi-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    // بزرگ خاندان
    case ROLE_Big_Khab:
    case 'big_khab':

        if ($server->getStatus() == 'court-3') {

            $accused = $server->accused();

            $keyboard[] = [
                $telegram->buildInlineKeyboardButton('بی‌گناه', '', $day . '/server-' . $server->league_id . '-^court-' . $server->getId() . '-' . $accused->getUserId()),
                $telegram->buildInlineKeyboardButton('گناهکار', '', $day . '/server-' . $server->league_id . '-court-' . $server->getId() . '-' . $accused->getUserId()),
            ];


            $select = $selector->user()->select(ROLE_Big_Khab);

            if ($select->is($user_select)) {
                $selector->delete(ROLE_Big_Khab);
            } else {
                $selector->set($user_select->getUserId(), ROLE_Big_Khab);
            }

            $selector->delete($chatid, 'vote');

            $select = $selector->user()->select(ROLE_Big_Khab);
            $keyboard[] = [
                $telegram->buildInlineKeyboardButton(('🟢 بی‌گناه' . ($select->getUserId() == 2 ? '✔️' : '')), '', $day . '/server-' . $server->league_id . '-big_khab-' . $server->getId() . '-' . 2),
                $telegram->buildInlineKeyboardButton(('🔴 گناهکار' . ($select->getUserId() == 1 ? '✔️' : '')), '', $day . '/server-' . $server->league_id . '-big_khab-' . $server->getId() . '-' . 1),
            ];

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        } else {

            $selector->answerCallback(function () {
                return '🔴 اکنون نمیتوانید از قدرت خود استفاده کنید';
            });

        }

        break;
    // سناتور
    case ROLE_Senator:


        $select_senator = $selector->getString()->select(ROLE_Senator);
        $arr_senator = empty($select_senator) ? [] : unserialize($select_senator);

        if (!in_array($user_select->getUserId(), $arr_senator) && count($arr_senator) < 4) {

            $arr_senator[] = $user_select->getUserId();

        } elseif (array_search($user_select->getUserId(), $arr_senator)) {

            unset($arr_senator[array_search($user_select->getUserId(), $arr_senator)]);

        }

        update_server_meta($server->getId(), 'select', serialize($arr_senator), ROLE_Senator);

        foreach ($users_server as $user) {

            if ($user->check($chatid)) {

                $keyboard[] = [
                    $telegram->buildInlineKeyboardButton('🧾 ' . $user->get_name() . (in_array($user->getUserId(), $arr_senator) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . ROLE_Senator . '-' . $server->getId() . '-' . $user->getUserId())
                ];

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    // تلفن چی
    case ROLE_TelefonChi:

        $select_dead_telefon_chi = $selector->select(ROLE_TelefonChi, 'dead-select');
        $select_telefon_chi = $selector->select(ROLE_TelefonChi);

        if (!$user_select->dead()) {

            if ($user_select->is($chatid)) {

                if ($select_dead_telefon_chi->getUserId() > 0 && $select_dead_telefon_chi->is_user_in_game()) {

                    if ($select_telefon_chi->getUserId() > 0) {

                        $bazpors_select = $selector->user()->select(ROLE_Bazpors);

                        $message = 'ارتباط شما با ' . "<u>" . $select_telefon_chi->get_name() . "</u>" . ' توسط تلفنچی ☎️ برقرار شد .' . "\n \n" . 'شما هم اکنون میتونید با یکدیگر صحبت کنید.';
                        $select_dead_telefon_chi->setStatus('call_chi')->SendMessageHtml($message);

                        if (!$bazpors_select->is($select_telefon_chi)) {
                            $message = 'ارتباط شما با ' . "<u>" . $select_dead_telefon_chi->get_name() . "</u>" . ' توسط تلفنچی ☎️ برقرار شد .' . "\n \n" . 'شما هم اکنون میتونید با یکدیگر صحبت کنید.';
                            $select_telefon_chi->setStatus('call_chi')->SendMessageHtml($message);
                        }

                        AnswerCallbackQuery($dataid, ' تماس تلفنی با موفقیت صورت گرفت✅');

                    } else {
                        AnswerCallbackQuery($dataid, '⚠️ شما شخص زنده را انتخاب نکردید!');
                    }

                } else {
                    AnswerCallbackQuery($dataid, '⚠️ شما شخص مرده را انتخاب نکردید!');
                }

            } else {

                if ($select_telefon_chi->is($user_select)) {
                    $selector->delete(ROLE_TelefonChi);
                    $select_telefon_chi->setUserId(0);
                } else {
                    $selector->set($user_select->getUserId(), ROLE_TelefonChi);
                    $select_telefon_chi->setUserId($user_select->getUserId());
                }

            }

        } elseif ($user_select->is_user_in_game()) {

            if ($select_dead_telefon_chi->is($user_select)) {
                $selector->delete(ROLE_TelefonChi, 'dead-select');
                $select_dead_telefon_chi->setUserId(0);
            } else {
                $selector->set($user_select->getUserId(), ROLE_TelefonChi, 'dead-select');
                $select_dead_telefon_chi->setUserId($user_select->getUserId());
            }

        }

        if (!$user_select->is($chatid) || $select_dead_telefon_chi->getUserId() <= 0 || $select_telefon_chi->getUserId() <= 0) {

            foreach ($users_server as $item) {

                if ($item->check($user)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('📞 ' . $item->get_name() . ($item->is($select_telefon_chi) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . ROLE_TelefonChi . '-' . $server->getId() . '-' . $item->getUserId())
                    ];

                } elseif ($item->dead() && $item->is_user_in_game()) {
                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('📱 ' . $item->get_name() . ($item->is($select_dead_telefon_chi) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . ROLE_TelefonChi . '-' . $server->getId() . '-' . $item->getUserId())
                    ];
                }

            }

            $keyboard[] = [
                $telegram->buildInlineKeyboardButton('☎️ بر قراری ارتباط', '', $day . '/server-' . $server->league_id . '-' . ROLE_TelefonChi . '-' . $server->getId() . '-' . $user->getUserId())
            ];


        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    // جادو گر
    case ROLE_Jadogar:

        $select = $selector->user()->select(ROLE_Jadogar);
        $select_2 = $selector->user()->select(ROLE_Jadogar, 'select-2');

        if ($select->is($user_select)) {

            $selector->delete(ROLE_Jadogar);
            $select->setUserId(0);

        } elseif ($select_2->is($user_select)) {

            $selector->delete(ROLE_Jadogar, 'select-2');
            $select_2->setUserId(0);

        } elseif ($select instanceof User && $select->getUserId() == 0) {

            $selector->set($user_select->getUserId(), ROLE_Jadogar);
            $select->setUserId($user_select->getUserId());

        } else {

            $selector->set($user_select->getUserId(), ROLE_Jadogar, 'select-2');
            $select_2->setUserId($user_select->getUserId());

        }

        $select_jadogar = $selector->select(ROLE_Jadogar, 'power');

        foreach ($users_server as $user) {

            if (!$user->dead() && !$select_jadogar->is($user)) {

                $text = '🪄 ' . $user->get_name() . (($user->is($select) || $user->is($select_2)) ? '✔️' : '');
                $keyboard[] = [
                    $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_Jadogar . '-' . $server->getId() . '-' . $user->getUserId())
                ];

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    // مسافر زمان
    case ROLE_MosaferZaman:


        if (!is_server_meta($server->getId(), 'mosafer')) {

            if ($selector->getString()->select(ROLE_MosaferZaman) != 'on') {

                update_server_meta($server->getId(), 'select', 'on', ROLE_MosaferZaman);
                $selector->answerCallback(function () {
                    return 'فردا همه افرادی که مرده اند زنده می شوند.';
                });

                $keyboard[] = [
                    $telegram->buildInlineKeyboardButton('✝️ زنده کردن' . '✔️', '', $day . '/server-' . $server->league_id . '-' . ROLE_MosaferZaman . '-' . $server->getId() . '-' . $user->getUserId())
                ];

            } else {

                $selector->delete(ROLE_MosaferZaman);

                $keyboard[] = [
                    $telegram->buildInlineKeyboardButton('✝️ زنده کردن', '', $day . '/server-' . $server->league_id . '-' . ROLE_MosaferZaman . '-' . $server->getId() . '-' . $user->getUserId())
                ];

            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        } else {

            $selector->answerCallback(function () {
                return '🚫 شما قبلا از قدرت خود استفاده کرده اید.';
            });

        }

        break;
    // فراماسون
    case ROLE_Framason:

        $select = $selector->user()->select(ROLE_Framason);
        $select_framason = unserialize($selector->getString()->select(ROLE_Framason, 'power'));

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_Framason)->answerCallback();

            foreach ($users_server as $user) {

                if ($user->check($chatid) && !in_array($user->encode(), $select_framason)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🪬️ ' . $user->get_name() . ($user->is($user_select) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . ROLE_Framason . '-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_Framason);

            foreach ($users_server as $user) {

                if ($user->check($chatid) && !in_array($user->encode(), $select_framason)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🪬️ ' . $user->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_Framason . '-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    // آهنگر
    case ROLE_Ahangar:

        $select = $selector->user()->select(ROLE_Ahangar);
        $last_select = $selector->select(ROLE_Ahangar, 'last-select');

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_Ahangar)->answerCallback();

            foreach ($users_server as $item) {

                if (!$last_select->is($item) && $item->check($chatid)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🛡 ' . $item->get_name() . ($item->is($user_select) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . ROLE_Ahangar . '-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_Ahangar);

            foreach ($users_server as $item) {

                if (!$last_select->is($item) && $item->check($chatid)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🛡 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_Ahangar . '-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
        break;
    // تر دست
    case ROLE_Tardast:

        $select = $selector->user()->select(ROLE_Tardast);

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_Tardast)->answerCallback();

            foreach ($users_server as $user) {

                if ($user->check($chatid)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🤙🏻 ' . $user->get_name() . ($user->is($user_select) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . ROLE_Tardast . '-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_Tardast);

            foreach ($users_server as $user) {

                if ($user->check($chatid)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🤙🏻 ' . $user->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_Tardast . '-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    // ............ GROUP 2 ............
    // گاد فادر
    case ROLE_Godfather:
    case 'god':


        $user_role = $user->get_role();

        $select = $selector->user()->select(ROLE_Godfather);
        $bazpors_select = $selector->user()->select(ROLE_Bazpors);
        $select_mashoghe = $selector->user()->select(ROLE_Mashooghe);

        if ($server->setUserId(ROLE_Godfather)->getMetaUser('super-god-father') == 'on') {

            $select_2 = $selector->user()->select(ROLE_Godfather, 'select-2');

            if ($select->is($user_select)) {

                $selector->delete(ROLE_Godfather);
                if ($user_select->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . 'از حمله به شما منصرف شدند.';
                    $user_select->SendMessageHtml();

                }

            } elseif ($select_2->is($user_select)) {

                $selector->delete(ROLE_Godfather, 'select-2');
                if ($user_select->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . 'از حمله به شما منصرف شدند.';
                    $user_select->SendMessageHtml();

                }

            } elseif ($select instanceof User && $select->getUserId() <= 0) {

                $selector->set($user_select->getUserId(), ROLE_Godfather);

                if ($user_select->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . ' قصد حمله به شما را دارد .';
                    $user_select->SendMessageHtml();

                }

                if ($select->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . 'از حمله به شما منصرف شدند.';
                    $select->SendMessageHtml();

                }

            } else {

                $selector->set($user_select->getUserId(), ROLE_Godfather, 'select-2');

                if ($user_select->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . ' قصد حمله به شما را دارد .';
                    $user_select->SendMessageHtml();

                }

                if ($select_2->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . 'از حمله به شما منصرف شدند.';
                    $select_2->SendMessageHtml();

                }

            }


            $select = $selector->user()->select(ROLE_Godfather);
            $select_2 = $selector->user()->select(ROLE_Godfather, 'select-2');

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';

            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2) {

                    $text = '🔫 ' . $user->get_name() . ($select->is($user) || $select_2->is($user) ? '✔️' : '');

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-god-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        } else {

            if (!$select->is($user_select)) {

                $selector->set($user_select->getUserId(), ROLE_Godfather);
                if (!$user_select->is($select_mashoghe)) {


                    if ($select_mashoghe->getUserId() > 0 && $select_mashoghe->spy()) {

                        $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . 'از حمله به شما منصرف شدند.';
                        $select_mashoghe->SendMessageHtml();

                    }

                    if ($user_select->spy()) {

                        $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . ' قصد حمله به شما را دارد .';
                        $user_select->SendMessageHtml();

                    }
                }

                if ($select->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . 'از حمله به شما منصرف شدند.';
                    $select->SendMessageHtml();

                }

                $role_group_2 = $server->roleByGroup(2);
                $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                foreach ($role_group_2 as $user) {

                    if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                        $user->SendMessageHtml();

                    }
                }

                foreach ($users_server as $user) {

                    if ($user->check($chatid) && $user->get_role()->group_id != 2) {

                        $text = '🔫 ' . $user->get_name() . ($user_select->is($user) ? '✔️' : '');

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-god-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            } else {

                $selector->delete(ROLE_Godfather);

                if ($select->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . 'از حمله به شما منصرف شدند.';
                    $select->SendMessageHtml();

                }

                if ($select_mashoghe->getUserId() > 0 && $select_mashoghe->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . ' قصد حمله به شما را دارد .';
                    $select_mashoghe->SendMessageHtml();

                }

                $role_group_2 = $server->roleByGroup(2);
                $message = user()->name . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
                foreach ($role_group_2 as $user) {

                    if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                        $user->SendMessageHtml();

                    }
                }

                foreach ($users_server as $user) {

                    if ($user->check($chatid) && $user->get_role()->group_id != 2) {

                        $text = '🔫 ' . $user->get_name();

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-god-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }

        }

        if (isset($keyboard)) {
            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
        }


        break;
    // معشوقه
    case ROLE_Mashooghe:
    case 'mashooghe':

        $god_father_select = $selector->user()->select(ROLE_Godfather);
        $select = $selector->user()->select(ROLE_Mashooghe); // انتخاب قبلی
        $bazpors_select = $selector->user()->select(ROLE_Bazpors);

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_Mashooghe)->answerCallback();

            if ($god_father_select->getUserId() <= 0) {

                if ($user_select->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . ' قصد حمله به شما را دارد .';
                    $user_select->SendMessageHtml($message);

                }

                if ($select->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . 'از حمله به شما منصرف شدند.';
                    $select->SendMessageHtml($message);

                }

            }


            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2) {

                    $text = '🔫 ' . $user->get_name() . ($user_select->is($user) ? '✔️' : '');

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-mashooghe-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        } else {

            if ($god_father_select->getUserId() <= 0) {

                if ($select->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>اعضای مافیا</u>" . 'از حمله به شما منصرف شدند.';
                    $select->SendMessageHtml();

                }

            }

            $selector->delete(ROLE_Mashooghe);

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2) {

                    $text = '🔫 ' . $user->get_name();

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-mashooghe-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    // تروریست
    case ROLE_Terrorist:
    case 'terrorist':
        $select = $selector->user()->select(ROLE_Terrorist);
        $bazpors_select = $selector->user()->select(ROLE_Bazpors);

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_Terrorist)->answerCallback();

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }
            foreach ($users_server as $item) {

                if ($item->check($chatid) && $item->get_role()->group_id != 2) {

                    $text = '🧨 ' . $item->get_name() . ($item->is($user_select) ? '✔️' : '');
                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-terrorist-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_Terrorist);


            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($users_server as $item) {

                if ($item->check($chatid) && $item->get_role()->group_id != 2) {

                    $text = '🧨 ' . $item->get_name();
                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-terrorist-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }
        }
        $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
        break;

        //min gozar

    // Mine Layer

    // ناتو
    case 'nato':


        $select = $selector->user()->select(ROLE_Nato);
        $bazpors_select = $selector->user()->select(ROLE_Bazpors);

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_Nato)->answerCallback();

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2) {

                    $text = '🔍 ' . $user->get_name() . ($user_select->is($user) ? '✔️' : '');

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-nato-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_Nato);

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2) {

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

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_Hacker)->answerCallback();

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2) {

                    $text = '🧑🏻‍💻 ' . $user->get_name() . ($user_select->is($user) ? '✔️' : '');

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-hacker-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_Hacker);

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2) {

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


        $select = $selector->user()->select(ROLE_HardFamia);
        $bazpors_select = $selector->user()->select(ROLE_Bazpors);

        if (!$select->is($user_select)) {

            if ($user_select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>مافیا حرفه ای</u>" . ' قصد حمله به شما را دارد .';
                $user_select->SendMessageHtml();

            }

            if ($select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>مافیا حرفه ای</u>" . 'از حمله به شما منصرف شدند.';
                $select->SendMessageHtml();

            }

            $selector->set($user_select->getUserId(), ROLE_HardFamia)->answerCallback();

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2) {

                    $text = '🔪 ' . $user->get_name() . ($user_select->is($user) ? '✔️' : '');

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-hard_mafia-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        } else {

            if ($select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>مافیا حرفه ای</u>" . 'از حمله به شما منصرف شدند.';
                $select->SendMessageHtml();

            }

            $selector->delete(ROLE_HardFamia);

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔪 ' . $user->get_name(), '', $day . '/server-' . $server->league_id . '-hard_mafia-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    case 'gorkan':

        $select = $selector->user()->select(ROLE_Gorkan);
        $bazpors_select = $selector->user()->select(ROLE_Bazpors);

        if (!$select->is($user_select)) {

            if ($user_select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>مستقل ,گورکن</u>" . ' قصد حمله به شما را دارد .';
                $user_select->SendMessageHtml();

            }

            if ($select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>مستقل ,گورکن</u>" . 'از حمله به شما منصرف شدند.';
                $select->SendMessageHtml();

            }

            $selector->set($user_select->getUserId(), ROLE_Gorkan)->answerCallback();

            $role_group_2 = $server->roleByGroup(3);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 3) {

                    $text = '🔪 ' . $user->get_name() . ($user_select->is($user) ? '✔️' : '');

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-gorkan-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        } else {

            if ($select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>مستقل ,گورکن</u>" . 'از حمله به شما منصرف شدند.';
                $select->SendMessageHtml();

            }

            $selector->delete(ROLE_Gorkan);

            $role_group_2 = $server->roleByGroup(3);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 3) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔪 ' . $user->get_name(), '', $day . '/server-' . $server->league_id . '-gorkan-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    // تهمت زن
    case 'tohmat':


        $select = $selector->user()->select(ROLE_TohmatZan);
        $bazpors_select = $selector->user()->select(ROLE_Bazpors);

        $last_select = $selector->user()->select(ROLE_TohmatZan, 'last-select');

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_TohmatZan)->answerCallback();

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && !$last_select->is($user)) {

                    $text = '👻 ' . $user->get_name() . ($user_select->is($user) ? '✔️' : '');

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-tohmat-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_TohmatZan);

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && !$last_select->is($user)) {

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


        $select = $selector->user()->select(ROLE_AfsonGar);
        $bazpors_select = $selector->user()->select(ROLE_Bazpors);

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_AfsonGar)->answerCallback();

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            $last_select = $selector->user()->select(ROLE_AfsonGar, 'last-select');
            foreach ($users_server as $user) {

                if ($user->check($chatid) && !$last_select->is($user) && $user->get_role()->group_id != 2) {

                    $text = '🦹🏻 ' . $user->get_name() . ($user_select->is($user) ? '✔️' : '');

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-afson_gar-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_AfsonGar);

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            $last_select = $selector->user()->select(ROLE_AfsonGar, 'last-select');
            foreach ($users_server as $user) {

                if ($user->check($chatid) && !$last_select->is($user) && $user->get_role()->group_id != 2) {

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
    case ROLE_BAD_DOCTOR:
    case 'doctor':


        $select = $selector->user()->select(ROLE_BAD_DOCTOR);
        $bazpors_select = $selector->user()->select(ROLE_Bazpors);

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_BAD_DOCTOR)->answerCallback();

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>دکتر لکتر</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            $status_doctor = is_server_meta($server->getId(), 'doctor', ROLE_BAD_DOCTOR);

            foreach ($server->roleByGroup(2) as $item) {

                if (!$item->dead() && (!$item->is($chatid) || !$status_doctor)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🩹 ' . $item->get_name() . ($user_select->is($item) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-doctor-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_BAD_DOCTOR);

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($server->roleByGroup(2) as $item) {

                if (!$item->dead() && (!$item->is($chatid) || !$status_doctor)) {

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


        $select = $selector->user()->select(ROLE_Tobchi);
        $bazpors_select = $selector->user()->select(ROLE_Bazpors);

        if (!$select->is($user_select)) {

            if ($user_select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>توپچی</u>" . ' قصد حمله به شما را دارد .';
                $user_select->SendMessageHtml();

            }

            if ($select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>توپچی</u>" . 'از حمله به شما منصرف شدند.';
                $select->SendMessageHtml();

            }

            $selector->set($user_select->getUserId(), ROLE_Tobchi)->answerCallback();

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2) {

                    $text = '💣 ' . $user->get_name() . ($user_select->is($user) ? '✔️' : '');

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-tobchi-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        } else {

            if ($select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>توپچی</u>" . 'از حمله به شما منصرف شدند.';
                $select->SendMessageHtml();

            }

            $selector->delete(ROLE_Tobchi);

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2) {

                    $text = '💣 ' . $user->get_name();

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


        $select = $selector->user()->select(ROLE_ShekarChi);
        $bazpors_select = $selector->user()->select(ROLE_Bazpors);
        $select_shekar_chi = $selector->user()->select(ROLE_ShekarChi, 'last-select');

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_ShekarChi)->answerCallback();

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }

            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2 && !$select_shekar_chi->is($user)) {

                    $text = '🕶 ' . $user->get_name() . ($user_select->is($user) ? '✔️' : '');

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_ShekarChi . '-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_ShekarChi);

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2 && !$select_shekar_chi->is($user)) {

                    $text = '🕶 ' . $user->get_name();

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_ShekarChi . '-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    // دزد
    case ROLE_Dozd:


        $select = $selector->user()->select(ROLE_Dozd);
        $bazpors_select = $selector->user()->select(ROLE_Bazpors);
        $select_dozd = $selector->user()->select(ROLE_Dozd, 'last-select');

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_Dozd)->answerCallback();

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }

            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2 && !$select_dozd->is($user)) {

                    $text = '🚷 ' . $user->get_name() . ($user_select->is($user) ? '✔️' : '');

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_Dozd . '-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_Dozd);

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2 && !$select_dozd->is($user)) {

                    $text = '🚷 ' . $user->get_name();

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_Dozd . '-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    // شب خسب
    case 'sleep':


        $select = $selector->user()->select(ROLE_ShabKhosb);
        $bazpors_select = $selector->user()->select(ROLE_Bazpors);
        $last_select = get_server_meta($server->getId(), 'last-user', ROLE_ShabKhosb);

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_ShabKhosb)->answerCallback();

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }

            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2) {

                    if (!$user->is($last_select)) {

                        $text = '💆‍♂ ' . $user->get_name() . ($user_select->is($user) ? '✔️' : '');

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-sleep-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }

        } else {

            $selector->delete(ROLE_ShabKhosb);

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2) {

                    if (!$user->is($last_select)) {

                        $text = '💆‍♂ ' . $user->get_name();

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

        $select = $selector->user()->select(ROLE_MozakarehKonandeh);
        $bazpors_select = $selector->user()->select(ROLE_Bazpors);

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_MozakarehKonandeh)->answerCallback();

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }

            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2) {

                    $text = '🤝 ' . $user->get_name() . ($user_select->is($user) ? '✔️' : '');

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-mozakereh-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_MozakarehKonandeh);

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2) {

                    $text = '🤝 ' . $user->get_name();

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

        $select = $selector->user()->select(ROLE_Dalghak);
        $bazpors_select = $selector->user()->select(ROLE_Bazpors);

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_Dalghak)->answerCallback();
            $keyboard[][] = $telegram->buildInlineKeyboardButton('🤡 خندیدن ✔️', '', $day . '/server-' . $server->league_id . '-dalghak-' . $server->getId() . '-' . $user->getUserId());

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }

            }

        } else {

            $selector->delete(ROLE_Dalghak);
            $keyboard[][] = $telegram->buildInlineKeyboardButton('🤡 خندیدن', '', $day . '/server-' . $server->league_id . '-dalghak-' . $server->getId() . '-' . $user->getUserId());

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    // یاکوزا
    case ROLE_Yakoza:

        $select = $selector->user()->select(ROLE_Yakoza);
        $bazpors_select = $selector->user()->select(ROLE_Bazpors);

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_Yakoza)->answerCallback();

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }

            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2) {

                    $text = '🎴 ' . $user->get_name() . ($user_select->is($user) ? '✔️' : '');

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_Yakoza . '-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_Yakoza);

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2) {

                    $text = '🎴 ' . $user->get_name();

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_Yakoza . '-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    // شیاد
    case ROLE_Shayad:

        $select = $selector->user()->select(ROLE_Shayad);
        $bazpors_select = $selector->user()->select(ROLE_Bazpors);

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_Shayad)->answerCallback();

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }

            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2) {

                    $text = '👹 ' . $user->get_name() . ($user_select->is($user) ? '✔️' : '');

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_Shayad . '-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_Shayad);

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2) {

                    $text = '👹 ' . $user->get_name();

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_Shayad . '-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    // شاه کش
    case ROLE_ShahKosh:

        $bazpors_select = $selector->user()->select(ROLE_Bazpors);

        $selector->set($user_select->getUserId(), ROLE_ShahKosh)->answerCallback();

        $role_group_2 = $server->roleByGroup(2);
        $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
        foreach ($role_group_2 as $user) {

            if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                $user->SendMessageHtml();

            }

        }

        $filter_role = [ROLE_Karagah, ROLE_Mohaghegh, ROLE_EynakSaz, ROLE_Senator, ROLE_Bazpors];
        foreach (get_keyboard_roles_by_group_and_game(1, $server->league_id) as $item) {
            if (!in_array($item->id, $filter_role)) {
                $keyboard[][] = $telegram->buildInlineKeyboardButton($item->icon, '', $day . '/server-' . $server->league_id . '-shah_2-' . $server->getId() . '-' . $item->id);
            }
        }
        foreach (get_keyboard_roles_by_group_and_game(3, $server->league_id) as $item) {
            $keyboard[][] = $telegram->buildInlineKeyboardButton($item->icon, '', $day . '/server-' . $server->league_id . '-shah_2-' . $server->getId() . '-' . $item->id);
        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    case 'shah_2':

        $select = $selector->select(ROLE_ShahKosh, 'select-role');
        AnswerCallbackQuery($dataid, '');

        if (!$select->is($user_select)) {
            $selector->set($user_select->getUserId(), ROLE_ShahKosh, 'select-role');
        } else {
            $selector->delete(ROLE_ShahKosh, 'select-role');
            $user_select->setUserId(0);
        }

        $filter_role = [ROLE_Karagah, ROLE_Mohaghegh, ROLE_EynakSaz, ROLE_Senator, ROLE_Bazpors];
        foreach (get_keyboard_roles_by_group_and_game(1, $server->league_id) as $item) {
            if (!in_array($item->id, $filter_role)) {
                $keyboard[][] = $telegram->buildInlineKeyboardButton($item->icon . ($user_select->is($item->id) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-shah_2-' . $server->getId() . '-' . $item->id);
            }
        }
        foreach (get_keyboard_roles_by_group_and_game(3, $server->league_id) as $item) {
            $keyboard[][] = $telegram->buildInlineKeyboardButton($item->icon . ($user_select->is($item->id) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-shah_2-' . $server->getId() . '-' . $item->id);
        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    // دام
    case ROLE_Dam:

        $select = $selector->user()->select(ROLE_Dam);
        $bazpors_select = $selector->user()->select(ROLE_Bazpors);

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_Dam)->answerCallback();

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }

            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2) {

                    $text = '🧱 ' . $user->get_name() . ($user->get_role()->group_id == 2 ? '🔴' : '') . ($user_select->is($user) ? '✔️' : '');

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_Dam . '-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        } else {

            $selector->delete(ROLE_Dam);

            $role_group_2 = $server->roleByGroup(2);
            $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
            foreach ($role_group_2 as $user) {

                if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                    $user->SendMessageHtml();

                }
            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 2) {

                    $text = '🧱 ' . $user->get_name() . ($user->get_role()->group_id == 2 ? '🔴' : '');

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_Dam . '-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    // ............ GROUP 3 ............
    // زودیاک
    case ROLE_Killer:
    case 'kill':

        $bazpors_select = $selector->user()->select(ROLE_Bazpors);
        $select = $selector->user()->select(ROLE_Killer);

        if ($server->getMeta('killer') == 'on') {

            $select_2 = $selector->user()->select(ROLE_Killer, 'select-2');

            if ($select->is($user_select)) {

                $selector->delete(ROLE_Killer);
                if ($user_select->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>زودیاک</u>" . 'از حمله به شما منصرف شدند.';
                    $user_select->SendMessageHtml();

                }
                $select->setUserId(0);

            } elseif ($select_2->is($user_select)) {

                $selector->delete(ROLE_Killer, 'select-2');
                if ($user_select->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>زودیاک</u>" . 'از حمله به شما منصرف شدند.';
                    $user_select->SendMessageHtml();

                }
                $select_2->setUserId(0);

            } elseif ($select instanceof User && $select->getUserId() <= 0) {

                $selector->set($user_select->getUserId(), ROLE_Killer)->answerCallback();

                if ($user_select->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>زودیاک</u>" . ' قصد حمله به شما را دارد .';
                    $user_select->SendMessageHtml();

                }

                if ($select->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>زودیاک</u>" . 'از حمله به شما منصرف شدند.';
                    $select->SendMessageHtml();

                }

                $select->setUserId($user_select->getUserId());

            } else {

                $selector->set($user_select->getUserId(), ROLE_Killer, 'select-2')->answerCallback();

                if ($user_select->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>زودیاک</u>" . ' قصد حمله به شما را دارد .';
                    $user_select->SendMessageHtml();

                }

                if ($select_2->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>زودیاک</u>" . 'از حمله به شما منصرف شدند.';
                    $select_2->SendMessageHtml();

                }

                $select_2->setUserId($user_select->getUserId());

            }

            if (in_array($server->league_id, MOSTAGHEL_TEAM) && $user->get_role()->group_id == 3) {

                $role_group_2 = $server->roleByGroup(3);
                $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                foreach ($role_group_2 as $user) {

                    if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                        $user->SendMessageHtml();

                    }

                }

            }


            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 3) {

                    $text = '☠️ ' . $user->get_name() . ($user->is($select) || $user->is($select_2) ? '✔️' : '');
                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-kill-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }


        } else {

            if (!$select->is($user_select)) {

                if ($user_select->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>زودیاک</u>" . ' قصد حمله به شما را دارد .';
                    $user_select->SendMessageHtml();

                }

                if ($select->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>زودیاک</u>" . 'از حمله به شما منصرف شدند.';
                    $select->SendMessageHtml();

                }

                if (in_array($server->league_id, MOSTAGHEL_TEAM) && $user->get_role()->group_id == 3) {
                    $role_group_2 = $server->roleByGroup(3);
                    $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                    foreach ($role_group_2 as $user) {

                        if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                            $user->SendMessageHtml();

                        }

                    }
                }

                $selector->set($user_select->getUserId(), ROLE_Killer)->answerCallback();

                foreach ($users_server as $user) {

                    if ($user->check($chatid) && $user->get_role()->group_id != 3) {

                        $text = '☠️ ' . $user->get_name() . ($user->is($user_select) ? '✔️' : '');
                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-kill-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            } else {

                if ($select->spy()) {

                    $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>زودیاک</u>" . 'از حمله به شما منصرف شد.';
                    $select->SendMessageHtml();

                }

                if (in_array($server->league_id, MOSTAGHEL_TEAM) && $user->get_role()->group_id == 3) {

                    $role_group_2 = $server->roleByGroup(3);
                    $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
                    foreach ($role_group_2 as $user) {

                        if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                            $user->SendMessageHtml();

                        }

                    }

                }

                $selector->delete(ROLE_Killer);

                foreach ($users_server as $user) {

                    if ($user->check($chatid) && $user->get_role()->group_id != 3) {

                        $text = '☠️ ' . $user->get_name();
                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-kill-' . $server->getId() . '-' . $user->getUserId())
                        ];

                    }

                }

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    // آشپز
    case ROLE_Ashpaz:

        $select = $selector->user()->select(ROLE_Ashpaz);
        $bazpors_select = $selector->user()->select(ROLE_Bazpors);
        $last_select = $selector->user()->select(ROLE_Ashpaz, 'last-select');

        if (!$select->is($user_select)) {

            if ($user_select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>آشپز</u>" . ' قصد حمله به شما را دارد.';
                $user_select->SendMessageHtml();

            }

            if ($select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>آشپز</u>" . 'از حمله به شما منصرف شدند.';
                $select->SendMessageHtml();

            }

            if (in_array($server->league_id, MOSTAGHEL_TEAM) && $user->get_role()->group_id == 3) {
                $role_group_2 = $server->roleByGroup(3);
                $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                foreach ($role_group_2 as $user) {

                    if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                        $user->SendMessageHtml();

                    }

                }
            }

            $selector->set($user_select->getUserId(), ROLE_Ashpaz)->answerCallback();

            foreach ($users_server as $user) {

                if ($user->check($chatid) && !$last_select->is($user) && $user->get_role()->group_id != 3) {

                    $text = '👨🏻‍🍳 ' . $user->get_name() . ($user->is($user_select) ? '✔️' : '');
                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_Ashpaz . '-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        } else {

            if ($select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>آشپز</u>" . 'از حمله به شما منصرف شد.';
                $select->SendMessageHtml();

            }

            $selector->delete(ROLE_Ashpaz);

            foreach ($users_server as $user) {

                if ($user->check($chatid) && !$last_select->is($user) && $user->get_role()->group_id != 3) {

                    $text = '👨🏻‍🍳 ' . $user->get_name();
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

        $select = $selector->user()->select(ROLE_Ankabot);
        $select_2 = $selector->user()->select(ROLE_Ankabot, 'select-2');

        if ($select->is($user_select)) {

            $selector->delete(ROLE_Ankabot);
            $select->setUserId(0);

            if ($user_select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>عنکبوت</u>" . 'از حمله به شما منصرف شد.';
                $user_select->SendMessageHtml();

            }

        } elseif ($select_2->is($user_select)) {

            $selector->delete(ROLE_Ankabot, 'select-2');
            $select_2->setUserId(0);

            if ($user_select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>عنکبوت</u>" . 'از حمله به شما منصرف شد.';
                $user_select->SendMessageHtml();

            }

        } elseif ($select instanceof User && $select->getUserId() == 0) {

            $selector->set($user_select->getUserId(), ROLE_Ankabot);
            $select->setUserId($user_select->getUserId());

            if ($user_select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>عنکبوت</u>" . ' قصد حمله به شما را دارد .';
                $user_select->SendMessageHtml();

            }

            if ($select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>عنکبوت</u>" . 'از حمله به شما منصرف شد.';
                $select->SendMessageHtml();

            }


            if (in_array($server->league_id, MOSTAGHEL_TEAM) && $user->get_role()->group_id == 3) {
                $role_group_2 = $server->roleByGroup(3);
                $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                foreach ($role_group_2 as $user) {

                    if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                        $user->SendMessageHtml();

                    }

                }
            }

        } else {

            $selector->set($user_select->getUserId(), ROLE_Ankabot, 'select-2');
            $select_2->setUserId($user_select->getUserId());

            if ($user_select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>عنکبوت</u>" . ' قصد حمله به شما را دارد .';
                $user_select->SendMessageHtml();

            }

            if ($select_2->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>عنکبوت</u>" . 'از حمله به شما منصرف شد.';
                $select_2->SendMessageHtml();

            }


            if (in_array($server->league_id, MOSTAGHEL_TEAM) && $user->get_role()->group_id == 3) {
                $role_group_2 = $server->roleByGroup(3);
                $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                foreach ($role_group_2 as $user) {

                    if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                        $user->SendMessageHtml();

                    }

                }
            }

        }


        foreach ($users_server as $user) {

            if ($user->check($chatid) && $user->get_role()->group_id != 3) {

                $text = '🕸 ' . $user->get_name() . (($user->is($select) || $user->is($select_2)) ? '✔️' : '');
                $keyboard[] = [
                    $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_Ankabot . '-' . $server->getId() . '-' . $user->getUserId())
                ];

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));


        break;
    // بازمانده
    case ROLE_Bazmandeh:
    case 'bazmandeh':
        $select = $selector->user()->select(ROLE_Bazmandeh);

        if (!$select->is($user_select)) {

            $selector->set($chatid, ROLE_Bazmandeh)->answerCallback(function () {
                return '🦺 شما امشب جلیقه دارید.';
            });

            $keyboard[][] = $telegram->buildInlineKeyboardButton('🦺 تن کردن' . '✔️', '', $day . '/server-' . $server->league_id . '-bazmandeh-' . $server->getId() . '-' . $user->getUserId());

        } else {

            $selector->delete(ROLE_Bazmandeh);

            $keyboard[][] = $telegram->buildInlineKeyboardButton('🦺 تن کردن', '', $day . '/server-' . $server->league_id . '-bazmandeh-' . $server->getId() . '-' . $user->getUserId());
        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
        break;
    // گرگ نما
    case ROLE_Gorg:
    case 'gorg':

        $select = $selector->user()->select(ROLE_Gorg);
        $bazpors_select = $selector->user()->select(ROLE_Bazpors);

        if (!$select->is($user_select)) {

            if ($user_select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>گرگ</u>" . ' قصد حمله به شما را دارد .';
                $user_select->SendMessageHtml();

            }

            if ($select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>گرگ</u>" . 'از حمله به شما منصرف شدند.';
                $select->SendMessageHtml();

            }

            $selector->set($user_select->getUserId(), ROLE_Gorg)->answerCallback();


            if (in_array($server->league_id, MOSTAGHEL_TEAM) && $user->get_role()->group_id == 3) {
                $role_group_2 = $server->roleByGroup(3);
                $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                foreach ($role_group_2 as $user) {

                    if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                        $user->SendMessageHtml();

                    }

                }
            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 3) {

                    $text = '🐺 ' . $user->get_name() . ($user->is($user_select) ? '✔️' : '');
                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-gorg-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        } else {

            if ($select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>گرگ</u>" . 'از حمله به شما منصرف شد.';
                $select->SendMessageHtml();

            }

            $selector->delete(ROLE_Gorg);

            if (in_array($server->league_id, MOSTAGHEL_TEAM) && $user->get_role()->group_id == 3) {

                $role_group_2 = $server->roleByGroup(3);
                $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
                foreach ($role_group_2 as $user) {

                    if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                        $user->SendMessageHtml();

                    }

                }

            }

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 3) {

                    $text = '🐺 ' . $user->get_name();
                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-gorg-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    // هازارد
    case ROLE_Hazard:

        switch ($user_select->getUserId()) {

            case 1:
            case 2:

                $power = $selector->getInt()->select(ROLE_Hazard, 'power');

                if ($power == 1) {

                    $i = 0;
                    foreach ($users_server as $user) {

                        if ($user->check($chatid) && $user->get_role()->group_id != 3) {

                            $keyboard[$i][] = $telegram->buildInlineKeyboardButton('🎲 ' . $user->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_Hazard . '-' . $server->getId() . '-' . $user->getUserId());
                            $keyboard[$i++][] = $telegram->buildInlineKeyboardButton('🔫 ' . $user->get_name(), '', $day . '/server-' . $server->league_id . '-' . 'hazard_shot-' . $server->getId() . '-' . $user->getUserId());

                        }

                    }

                } else {

                    foreach ($users_server as $user) {

                        if ($user->check($chatid) && $user->get_role()->group_id != 3) {

                            $keyboard[][] = $telegram->buildInlineKeyboardButton('🎲 ' . $user->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_Hazard . '-' . $server->getId() . '-' . $user->getUserId());

                        }

                    }

                }

                $selector->set($user_select->getUserId(), ROLE_Hazard, 'type');

                break;

            default:

                $select = $selector->user()->select(ROLE_Hazard);
                $bazpors_select = $selector->user()->select(ROLE_Bazpors);

                if (!$select->is($user_select)) {

                    $selector->set($user_select->getUserId(), ROLE_Hazard)->answerCallback();

                    if (in_array($server->league_id, MOSTAGHEL_TEAM) && $user->get_role()->group_id == 3) {
                        $role_group_2 = $server->roleByGroup(3);
                        $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                        foreach ($role_group_2 as $user) {

                            if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                                $user->SendMessageHtml();

                            }

                        }
                    }

                    $power = $selector->getInt()->select(ROLE_Hazard, 'power');

                    if ($power == 1) {

                        $select_hazard = $selector->select(ROLE_Hazard, 'select-2');
                        $i = 0;
                        foreach ($users_server as $user) {

                            if ($user->check($chatid) && $user->get_role()->group_id != 3) {

                                $keyboard[$i][] = $telegram->buildInlineKeyboardButton('🎲 ' . $user->get_name() . ($user->is($user_select) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . ROLE_Hazard . '-' . $server->getId() . '-' . $user->getUserId());
                                $keyboard[$i++][] = $telegram->buildInlineKeyboardButton('🔫 ' . $user->get_name() . ($user->is($select_hazard) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . ROLE_Hazard . '_shot-' . $server->getId() . '-' . $user->getUserId());

                            }

                        }

                    } else {

                        foreach ($users_server as $user) {

                            if ($user->check($chatid) && $user->get_role()->group_id != 3) {

                                $keyboard[][] = $telegram->buildInlineKeyboardButton('🎲 ' . $user->get_name() . ($user->is($user_select) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . ROLE_Hazard . '-' . $server->getId() . '-' . $user->getUserId());

                            }

                        }

                    }


                } else {

                    $selector->delete(ROLE_Hazard);
                    $selector->delete(ROLE_Hazard, 'type');

                    if (in_array($server->league_id, MOSTAGHEL_TEAM) && $user->get_role()->group_id == 3) {

                        $role_group_2 = $server->roleByGroup(3);
                        $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
                        foreach ($role_group_2 as $user) {

                            if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                                $user->SendMessageHtml();

                            }

                        }

                    }

                    $keyboard = [
                        [
                            $telegram->buildInlineKeyboardButton('قمار برای دفاعیه', '', $day . '/server-' . $server->league_id . '-' . ROLE_Hazard . '-' . $server->getId() . '-1')
                        ],
                        [
                            $telegram->buildInlineKeyboardButton('قمار برای اعدام', '', $day . '/server-' . $server->league_id . '-' . ROLE_Hazard . '-' . $server->getId() . '-2')
                        ],
                    ];

                }

                break;

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    case 'hazard_shot':

        $select = $selector->user()->select(ROLE_Hazard, 'select-2');
        $select_hazard = $selector->select(ROLE_Hazard);
        $i = 0;

        if (!$select->is($user_select)) {

            $selector->set($user_select->getUserId(), ROLE_Hazard, 'select-2')->answerCallback();
            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 3) {

                    $keyboard[$i][] = $telegram->buildInlineKeyboardButton('🎲 ' . $user->get_name() . ($user->is($select_hazard) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . ROLE_Hazard . '-' . $server->getId() . '-' . $user->getUserId());
                    $keyboard[$i++][] = $telegram->buildInlineKeyboardButton('🔫 ' . $user->get_name() . ($user->is($user_select) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-hazard_shot-' . $server->getId() . '-' . $user->getUserId());

                }

            }

        } else {

            $selector->delete(ROLE_Hazard, 'select-2');
            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 3) {

                    $keyboard[$i][] = $telegram->buildInlineKeyboardButton('🎲 ' . $user->get_name() . ($user->is($select_hazard) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . ROLE_Hazard . '-' . $server->getId() . '-' . $user->getUserId());
                    $keyboard[$i++][] = $telegram->buildInlineKeyboardButton('🔫 ' . $user->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_Hazard . '_shot-' . $server->getId() . '-' . $user->getUserId());

                }

            }

        }


        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    // جلاد
    case ROLE_Jalad:

        $select = $selector->select(ROLE_Jalad);
        if ($select->is($user_select)) {
            $keyboard = $telegram->buildInlineKeyBoard([
                [
                    $telegram->buildInlineKeyboardButton('🔄 تعویض هدف', '', $day . '/server-' . $server->league_id . '-' . ROLE_Jalad . '-' . $server->getId() . '-' . $user->getUserId())
                ]
            ]);
            $selector->delete(ROLE_Jalad);
        } else {
            $keyboard = $telegram->buildInlineKeyBoard([
                [
                    $telegram->buildInlineKeyboardButton('🔄 تعویض هدف ✔️', '', $day . '/server-' . $server->league_id . '-' . ROLE_Jalad . '-' . $server->getId() . '-' . $user->getUserId())
                ]
            ]);
            $selector->set($user_select->getUserId(), ROLE_Jalad);
        }
        EditKeyboard($chatid, $messageid, $keyboard);

        break;
    // نرون
    case ROLE_Neron:

        $bazpors_select = $selector->user()->select(ROLE_Bazpors);
        $select = $selector->user()->select(ROLE_Neron);
        $power = unserialize($selector->getString()->select(ROLE_Neron, 'power', false));

        if (!$select->is($user_select)) {

            if ($user_select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>نرون</u>" . ' قصد نفتی کردن شما را دارد .';
                $user_select->SendMessageHtml();

            }

            if ($select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>نرون</u>" . 'از نفتی کردن شما منصرف شدند.';
                $select->SendMessageHtml();

            }

            if (in_array($server->league_id, MOSTAGHEL_TEAM) && $user->get_role()->group_id == 3) {
                $role_group_2 = $server->roleByGroup(3);
                $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)، <u>' . $user_select->get_name() . '</u> را انتخاب کرد.';
                foreach ($role_group_2 as $user) {

                    if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                        $user->SendMessageHtml();

                    }

                }
            }

            $selector->set($user_select->getUserId(), ROLE_Neron)->answerCallback();

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 3 && !in_array($user->getUserId(), $power)) {

                    $text = '🛢️ ' . $user->get_name() . ($user->is($user_select) ? '✔️' : '');
                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_Neron . '-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        } else {

            if ($select->spy()) {

                $message = '🧏🏻‍♂️ جادوی جاسوس ' . "\n" . "<u>نرون</u>" . 'از نفتی کردن شما منصرف شد.';
                $select->SendMessageHtml();

            }

            if (in_array($server->league_id, MOSTAGHEL_TEAM) && $user->get_role()->group_id == 3) {

                $role_group_2 = $server->roleByGroup(3);
                $message = $user->get_name() . ' (<b><i>' . trim(remove_emoji($user->get_role()->icon)) . '</i></b>)' . ' هیچکس را انتخاب نکرد .';
                foreach ($role_group_2 as $user) {

                    if ($user->check($bazpors_select) && $user->is_user_in_game()) {

                        $user->SendMessageHtml();

                    }

                }

            }

            $selector->delete(ROLE_Neron);

            foreach ($users_server as $user) {

                if ($user->check($chatid) && $user->get_role()->group_id != 3 && !in_array($user->getUserId(), $power)) {

                    $text = '🛢️ ' . $user->get_name();
                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_Neron . '-' . $server->getId() . '-' . $user->getUserId())
                    ];

                }

            }

        }

        if (count($power) > 0 && $day > 1) {
            $keyboard[][] = $telegram->buildInlineKeyboardButton('🔥فندک زدن ' . ($user_select->is(123) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . ROLE_Neron . '-' . $server->getId() . '-123');
        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    // ............ GROUP 4 ............
    // ساغر
    case ROLE_Sagher:

        $type = $selector->getInt()->select($data[2], 'type');

        if ($user_select->is($type)) {
            $selector->delete($data[2], 'type');
            $user_select->setUserId(0);
        }

        switch ($user_select->getUserId()) {

            case 1:
            case 2:
            case 3:
            case 7:
            case 8:

                foreach ($server->users() as $item) {

                    if ($item->check($chatid) && $item->get_role()->group_id != 4) {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('🧪 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . $data[2] . '-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

                add_server_meta($server->getId(), 'type', $user_select->getUserId(), $data[2]);

                break;

            case 4:
            case 5:
            case 0:
            case 6:
            case 9:

                $power = unserialize($selector->getString()->select($data[2], 'power'));

                if ($power['magic-1']) {
                    $keyboard[0][] = $telegram->buildInlineKeyboardButton('🧪 مرگ' . ($user_select->is(1) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . $data[2] . '-' . $server->getId() . '-1');
                }
                if ($power['magic-2']) {
                    $keyboard[0][] = $telegram->buildInlineKeyboardButton('🧪 جنون‌آور' . ($user_select->is(2) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . $data[2] . '-' . $server->getId() . '-2');
                }
                if ($power['magic-3']) {
                    $keyboard[0][] = $telegram->buildInlineKeyboardButton('🧪 بیماری' . ($user_select->is(3) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . $data[2] . '-' . $server->getId() . '-3');
                }
                if ($power['magic-4']) {
                    $keyboard[(count($keyboard[0]) == 0 ? 0 : 1)][] = $telegram->buildInlineKeyboardButton('🧪 شهرکُش' . ($user_select->is(4) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . $data[2] . '-' . $server->getId() . '-4');
                }
                if ($power['magic-5']) {
                    $keyboard[(count($keyboard[0]) == 0 ? 0 : 1)][] = $telegram->buildInlineKeyboardButton('🧪 مافیاکُش' . ($user_select->is(5) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . $data[2] . '-' . $server->getId() . '-5');
                }
                if ($power['magic-6']) {
                    $keyboard[(count($keyboard[1]) == 0 ? (count($keyboard[0]) == 0 ? 0 : 1) : 2)][] = $telegram->buildInlineKeyboardButton('🧪 نامیرایی' . ($user_select->is(6) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . $data[2] . '-' . $server->getId() . '-6');
                }
                if ($power['magic-7']) {
                    $keyboard[(count($keyboard[1]) == 0 ? (count($keyboard[0]) == 0 ? 0 : 1) : 2)][] = $telegram->buildInlineKeyboardButton('🧪 افشاگر' . ($user_select->is(7) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . $data[2] . '-' . $server->getId() . '-7');
                }
                /*if ( $power[ 'magic-8' ] )
                {
                    $keyboard[ ( count( $keyboard[ 1 ] ) == 0 ? ( count( $keyboard[ 0 ] ) == 0 ? 0 : 1 ) : 2 ) ][] = $telegram->buildInlineKeyboardButton( '🧪 بیماری', '', $day . '/server-' . $server->league_id . '-' . $data[ 2 ] . '-' . $server->getId() . '-8' );
                }*/
                if ($power['magic-9']) {
                    $keyboard[(count($keyboard[2]) == 0 ? (count($keyboard[1]) == 0 ? (count($keyboard[0]) == 0 ? 0 : 1) : 2) : 3)][] = $telegram->buildInlineKeyboardButton('🧪شگفتی' . ($user_select->is(9) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . $data[2] . '-' . $server->getId() . '-9');
                }

                // if ( $power[ 'magic-1' ] )
                // {
                //     $keyboard[ 0 ][] = $telegram->buildInlineKeyboardButton( '🧪 مرگ', '', $day . '/server-' . $server->league_id . '-' . $data[ 2 ] . '-' . $server->getId() . '-1' );
                // }
                // if ( $power[ 'magic-2' ] )
                // {
                //     $keyboard[ 0 ][] = $telegram->buildInlineKeyboardButton( '🧪 جنون‌آور', '', $day . '/server-' . $server->league_id . '-' . $data[ 2 ] . '-' . $server->getId() . '-2' );
                // }
                // if ( $power[ 'magic-3' ] )
                // {
                //     $keyboard[ 0 ][] = $telegram->buildInlineKeyboardButton( '🧪 بیماری', '', $day . '/server-' . $server->league_id . '-' . $data[ 2 ] . '-' . $server->getId() . '-3' );
                // }
                // if ( $power[ 'magic-4' ] )
                // {
                //     $keyboard[ ( count( $keyboard[ 0 ] ) == 0 ? 0 : 1 ) ][] = $telegram->buildInlineKeyboardButton( '🧪 شهرکُش' . ( $user_select->is( 4 ) ? '✔️' : '' ), '', $day . '/server-' . $server->league_id . '-' . $data[ 2 ] . '-' . $server->getId() . '-4' );
                // }
                // if ( $power[ 'magic-5' ] )
                // {
                //     $keyboard[ ( count( $keyboard[ 0 ] ) == 0 ? 0 : 1 ) ][] = $telegram->buildInlineKeyboardButton( '🧪 مافیاکُش' . ( $user_select->is( 5 ) ? '✔️' : '' ), '', $day . '/server-' . $server->league_id . '-' . $data[ 2 ] . '-' . $server->getId() . '-5' );
                // }
                // if ( $power[ 'magic-6' ] )
                // {
                //     $keyboard[ ( count( $keyboard[ 1 ] ) == 0 ? ( count( $keyboard[ 0 ] ) == 0 ? 0 : 1 ) : 2 ) ][] = $telegram->buildInlineKeyboardButton( '🧪 نامیرایی' . ( $user_select->is( 6 ) ? '✔️' : '' ), '', $day . '/server-' . $server->league_id . '-' . $data[ 2 ] . '-' . $server->getId() . '-6' );
                // }
                // if ( $power[ 'magic-7' ] )
                // {
                //     $keyboard[ ( count( $keyboard[ 1 ] ) == 0 ? ( count( $keyboard[ 0 ] ) == 0 ? 0 : 1 ) : 2 ) ][] = $telegram->buildInlineKeyboardButton( '🧪 افشاگر', '', $day . '/server-' . $server->league_id . '-' . $user_role->id . '-' . $server->getId() . '-7' );
                // }
                // /*if ( $power[ 'magic-8' ] )
                // {
                //     $keyboard[ ( count( $keyboard[ 1 ] ) == 0 ? ( count( $keyboard[ 0 ] ) == 0 ? 0 : 1 ) : 2 ) ][] = $telegram->buildInlineKeyboardButton( '🧪 بیماری', '', $day . '/server-' . $server->league_id . '-' . $user_role->id . '-' . $server->getId() . '-8' );
                // }*/
                // if ( $power[ 'magic-9' ] )
                // {
                //     $keyboard[ ( count( $keyboard[ 2 ] ) == 0 ? ( count( $keyboard[ 1 ] ) == 0 ? ( count( $keyboard[ 0 ] ) == 0 ? 0 : 1 ) : 2 ) : 3 ) ][] = $telegram->buildInlineKeyboardButton( '🧪شگفتی' . ( $user_select->is( 9 ) ? '✔️' : '' ), '', $day . '/server-' . $server->league_id . '-' . $user_role->id . '-' . $server->getId() . '-9' );
                // }

                add_server_meta($server->getId(), 'type', $user_select->getUserId(), $data[2]);

                break;


            default:

                $select = $selector->user()->select($data[2]);

                if (!$select->is($user_select)) {

                    $selector->set($user_select->getUserId(), $data[2])->answerCallback();

                    foreach ($users_server as $user) {

                        if ($user->check($chatid) && $user->get_role()->group_id != 4) {

                            $text = '🧪 ' . $user->get_name() . ($user->is($user_select) ? '✔️' : '');
                            $keyboard[] = [
                                $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . $data[2] . '-' . $server->getId() . '-' . $user->getUserId())
                            ];

                        }

                    }

                } else {

                    $selector->delete($data[2]);
                    $selector->delete($data[2], 'type');

                    $power = unserialize($selector->getString()->select($data[2], 'power'));

                    if ($power['magic-1']) {
                        $keyboard[0][] = $telegram->buildInlineKeyboardButton('🧪 مرگ' . ($user_select->is(1) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . $data[2] . '-' . $server->getId() . '-1');
                    }
                    if ($power['magic-2']) {
                        $keyboard[0][] = $telegram->buildInlineKeyboardButton('🧪 جنون‌آور' . ($user_select->is(2) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . $data[2] . '-' . $server->getId() . '-2');
                    }
                    if ($power['magic-3']) {
                        $keyboard[0][] = $telegram->buildInlineKeyboardButton('🧪 بیماری' . ($user_select->is(3) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . $data[2] . '-' . $server->getId() . '-3');
                    }
                    if ($power['magic-4']) {
                        $keyboard[(count($keyboard[0]) == 0 ? 0 : 1)][] = $telegram->buildInlineKeyboardButton('🧪 شهرکُش' . ($user_select->is(4) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . $data[2] . '-' . $server->getId() . '-4');
                    }
                    if ($power['magic-5']) {
                        $keyboard[(count($keyboard[0]) == 0 ? 0 : 1)][] = $telegram->buildInlineKeyboardButton('🧪 مافیاکُش' . ($user_select->is(5) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . $data[2] . '-' . $server->getId() . '-5');
                    }
                    if ($power['magic-6']) {
                        $keyboard[(count($keyboard[1]) == 0 ? (count($keyboard[0]) == 0 ? 0 : 1) : 2)][] = $telegram->buildInlineKeyboardButton('🧪 نامیرایی' . ($user_select->is(6) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . $data[2] . '-' . $server->getId() . '-6');
                    }
                    if ($power['magic-7']) {
                        $keyboard[(count($keyboard[1]) == 0 ? (count($keyboard[0]) == 0 ? 0 : 1) : 2)][] = $telegram->buildInlineKeyboardButton('🧪 افشاگر' . ($user_select->is(7) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . $data[2] . '-' . $server->getId() . '-7');
                    }
                    /*if ( $power[ 'magic-8' ] )
                    {
                        $keyboard[ ( count( $keyboard[ 1 ] ) == 0 ? ( count( $keyboard[ 0 ] ) == 0 ? 0 : 1 ) : 2 ) ][] = $telegram->buildInlineKeyboardButton( '🧪 بیماری', '', $day . '/server-' . $server->league_id . '-' . $data[ 2 ] . '-' . $server->getId() . '-8' );
                    }*/
                    if ($power['magic-9']) {
                        $keyboard[(count($keyboard[2]) == 0 ? (count($keyboard[1]) == 0 ? (count($keyboard[0]) == 0 ? 0 : 1) : 2) : 3)][] = $telegram->buildInlineKeyboardButton('🧪شگفتی' . ($user_select->is(9) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . $data[2] . '-' . $server->getId() . '-9');
                    }

                }

                break;


        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    // گمبلر
    case ROLE_Gambeler:
        switch ($user_select->getUserId()) {

            case 1:
            case 2:
            case 3:

                if ($user->getRoleId() == ROLE_Gambeler) {
                    $selector->set($user_select->getUserId(), ROLE_Gambeler, 'select-3');
                } else {
                    $selector->set($user_select->getUserId(), ROLE_Gambeler, 'select-2');
                }

                $keyboard = [
                    [
                        $telegram->buildInlineKeyboardButton('قیچی ✌️' . ($user_select->is(1) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . ROLE_Gambeler . '-' . $server->getId() . '-1'),
                        $telegram->buildInlineKeyboardButton('کاغذ ✋' . ($user_select->is(2) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . ROLE_Gambeler . '-' . $server->getId() . '-2'),
                        $telegram->buildInlineKeyboardButton('سنگ ✊' . ($user_select->is(3) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-' . ROLE_Gambeler . '-' . $server->getId() . '-3'),
                    ]
                ];

                break;


            default:

                $select = $selector->user()->select(ROLE_Gambeler);

                if ($day == 1 || ceil($server->getPeopleAlive() / 2) == 1) {

                    if (!$select->is($user_select)) {

                        $selector->set($user_select->getUserId(), ROLE_Gambeler)->answerCallback();

                        foreach ($users_server as $user) {

                            if (!$user->is($chatid)) {

                                $text = '🎮 ' . $user->get_name() . ($user->is($user_select) ? '✔️' : '');
                                $keyboard[] = [
                                    $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_Gambeler . '-' . $server->getId() . '-' . $user->getUserId())
                                ];

                            }

                        }

                    } else {

                        $selector->delete(ROLE_Gambeler);
                        foreach ($users_server as $user) {

                            if (!$user->is($chatid)) {

                                $text = '🎮 ' . $user->get_name();
                                $keyboard[] = [
                                    $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_Gambeler . '-' . $server->getId() . '-' . $user->getUserId())
                                ];

                            }

                        }

                    }

                } elseif ($user->getStatus() == 'voting') {

                    if (!$select->is($user_select)) {

                        $selector->set($user_select->getUserId(), ROLE_Gambeler)->answerCallback();

                    } else {

                        $selector->delete(ROLE_Gambeler);
                        $user_select->setUserId(0);

                    }

                    $i = 0;
                    $user_vote = $selector->getInt()->select($selector->getUser(ROLE_Gambeler)->getUserId(), 'vote');
                    foreach ($users_server as $item) {

                        if ($item->check($chatid) && $server->setUserId($item->getUserId())->getMetaUser('no-vote') != 'on') {
                            if (!$user_red_carpet) {
                                $text = '🗳 ' . $item->get_name() . ($item->is($user_vote) ? '✔️' : '');
                                $keyboard[$i][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-vote-' . $server->getId() . '-' . $item->getUserId());
                            }
                            $text = '🎮 ' . $item->get_name() . ($item->is($user_select) ? '✔️' : '');
                            $keyboard[$i][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_Gambeler . '-' . $server->getId() . '-' . $item->getUserId());
                            $i++;

                        }

                    }


                } else {
                    AnswerCallbackQuery($dataid, '🔴 الان نمیتونی هدفت را تغییر بدی');
                    die();
                }

                break;

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;

    case ROLE_MineLayer:
        switch ($user_select->getUserId()) {

            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:
            case 10:

            if ($user->getRoleId() == ROLE_MineLayer) {
//                $selector->delete(ROLE_MineLayer);
                $selector->delete(ROLE_MineLayer, 'select-3');
                // The MineLayer is selecting the mine location
                // Store the MineLayer's selection under their own user ID
                $selector->set($user_select->getUserId(), ROLE_MineLayer, 'select-3', $user->getUserId());

                // Build the keyboard for the MineLayer
                $mine_selection = $selector->select(ROLE_MineLayer, 'select-3', $user->getUserId())->getUserId();

                // Build the keyboard
                $houses = [];
                $number_emojis = [
                    1 => '1⃣',
                    2 => '2⃣',
                    3 => '3⃣',
                    4 => '4⃣',
                    5 => '5⃣',
                    6 => '6⃣',
                    7 => '7⃣',
                    8 => '8⃣',
                    9 => '9⃣',
                    10 => '🔟',
                ];

                for ($i = 1; $i <= 10; $i++) {
                    $is_selected = ($i == $mine_selection);
                    // Use the emoji representation of the number
                    $text = $number_emojis[$i] . ($is_selected ? ' ✔️' : '');
                    $houses[] = $telegram->buildInlineKeyboardButton(
                        $text,
                        '',
                        $day . '/server-' . $server->league_id . '-' . ROLE_MineLayer . '-' . $server->getId() . '-' . $i
                    );
                }

                // Split the buttons into rows
                $keyboard = array_chunk($houses, 5);

                // Update the message and keyboard
                EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

//                // Optionally, when the MineLayer has made their selection, proceed to the next step
//                if ($mine_selection > 0) {
//                    $user->SendMessage('شما یک مین در خانه شماره ' . $mine_selection . ' قرار دادید.');
//                }

            } else {
                // The target is selecting houses to defuse the mine

                // Retrieve existing selections for this target user
                $select = $selector->select(ROLE_MineLayer, 'select-2-0', $user->getUserId());
                $select_2 = $selector->select(ROLE_MineLayer, 'select-2-1', $user->getUserId());
                $select_3 = $selector->select(ROLE_MineLayer, 'select-2-2', $user->getUserId());

                // Extract the selected house numbers
                $select_value = $select ? $select->getUserId() : 0;
                $select_2_value = $select_2 ? $select_2->getUserId() : 0;
                $select_3_value = $select_3 ? $select_3->getUserId() : 0;

                // For tracking the order of selection, store in an array
                $selections = [];
                if ($select_value > 0) $selections[] = $select_value;
                if ($select_2_value > 0) $selections[] = $select_2_value;
                if ($select_3_value > 0) $selections[] = $select_3_value;

                $selected_house = $user_select->getUserId();

                // Check if the house is already selected
                if (in_array($selected_house, $selections)) {
                    // Remove the selection
                    if ($selected_house == $select_value) {
                        $selector->delete(ROLE_MineLayer, 'select-2-0', $user->getUserId());
                        $select_value = 0;
                    } elseif ($selected_house == $select_2_value) {
                        $selector->delete(ROLE_MineLayer, 'select-2-1', $user->getUserId());
                        $select_2_value = 0;
                    } elseif ($selected_house == $select_3_value) {
                        $selector->delete(ROLE_MineLayer, 'select-2-2', $user->getUserId());
                        $select_3_value = 0;
                    }
                    // Remove from selections array
                    $selections = array_diff($selections, [$selected_house]);
                } else {
                    // Add selection if less than 3 selections
                    if (count($selections) < 3) {
                        if ($select_value == 0) {
                            $selector->set($selected_house, ROLE_MineLayer, 'select-2-0', $user->getUserId());
                            $select_value = $selected_house;
                        } elseif ($select_2_value == 0) {
                            $selector->set($selected_house, ROLE_MineLayer, 'select-2-1', $user->getUserId());
                            $select_2_value = $selected_house;
                        } elseif ($select_3_value == 0) {
                            $selector->set($selected_house, ROLE_MineLayer, 'select-2-2', $user->getUserId());
                            $select_3_value = $selected_house;
                        }
                        $selections[] = $selected_house;
                    } else {
                        // Replace the oldest selection
                        $oldest_selection = array_shift($selections);
                        // Delete the oldest selection
                        if ($oldest_selection == $select_value) {
                            $selector->delete(ROLE_MineLayer, 'select-2-0', $user->getUserId());
                            $selector->set($selected_house, ROLE_MineLayer, 'select-2-0', $user->getUserId());
                            $select_value = $selected_house;
                        } elseif ($oldest_selection == $select_2_value) {
                            $selector->delete(ROLE_MineLayer, 'select-2-1', $user->getUserId());
                            $selector->set($selected_house, ROLE_MineLayer, 'select-2-1', $user->getUserId());
                            $select_2_value = $selected_house;
                        } elseif ($oldest_selection == $select_3_value) {
                            $selector->delete(ROLE_MineLayer, 'select-2-2', $user->getUserId());
                            $selector->set($selected_house, ROLE_MineLayer, 'select-2-2', $user->getUserId());
                            $select_3_value = $selected_house;
                        }
                        $selections[] = $selected_house;
                    }
                }

                // Build the keyboard with updated selections
                $houses = [];
                $number_emojis = [
                    1 => '1⃣',
                    2 => '2⃣',
                    3 => '3⃣',
                    4 => '4⃣',
                    5 => '5⃣',
                    6 => '6⃣',
                    7 => '7⃣',
                    8 => '8⃣',
                    9 => '9⃣',
                    10 => '🔟',
                ];

                for ($i = 1; $i <= 10; $i++) {
                    // Only show checkmarks for the target's selections
                    $is_selected = in_array($i, $selections);
                    // Use the emoji representation of the number
                    $text = $number_emojis[$i] . ($is_selected ? ' ✔️' : '');
                    $houses[] = $telegram->buildInlineKeyboardButton(
                        $text,
                        '',
                        $day . '/server-' . $server->league_id . '-' . ROLE_MineLayer . '-' . $server->getId() . '-' . $i
                    );
                }

                // Split the buttons into rows
                $keyboard = array_chunk($houses, 5);

                // Update the message and keyboard
                EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));



                // Check if all three selections are made
                if (count($selections) == 3) {
                    // All selections made
                    // Proceed to handle the outcome
                    // For example, you can call a function to process the selections
//                    processMineDefusal($user_id, $selections);
                }

            }

            break;
            default:

                $select = $selector->user()->select(ROLE_MineLayer);

                if ($day == 1 || ceil($server->getPeopleAlive() / 2) == 1) {

                    if (!$select->is($user_select)) {

                        $selector->set($user_select->getUserId(), ROLE_MineLayer)->answerCallback();

                        foreach ($users_server as $user) {

                            if (!$user->is($chatid) && !$user->dead()) {

                                $text = '💣 ' . $user->get_name() . ($user->is($user_select) ? '✔️' : '');
                                $keyboard[] = [
                                    $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_MineLayer . '-' . $server->getId() . '-' . $user->getUserId())
                                ];

                            }

                        }

                    } else {

                        $selector->delete(ROLE_MineLayer);
                        foreach ($users_server as $user) {

                            if (!$user->is($chatid)) {

                                $text = '💣 ' . $user->get_name();
                                $keyboard[] = [
                                    $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_MineLayer . '-' . $server->getId() . '-' . $user->getUserId())
                                ];

                            }

                        }

                    }

                } elseif ($user->getStatus() == 'voting') {

                    if (!$select->is($user_select)) {

                        $selector->set($user_select->getUserId(), ROLE_MineLayer)->answerCallback();

                    } else {

                        $selector->delete(ROLE_MineLayer);
                        $user_select->setUserId(0);

                    }

                    $i = 0;
                    $user_vote = $selector->getInt()->select($selector->getUser(ROLE_MineLayer)->getUserId(), 'vote');
                    foreach ($users_server as $item) {

                        if ($item->check($chatid) && $server->setUserId($item->getUserId())->getMetaUser('no-vote') != 'on') {
                            if (!$user_red_carpet) {
                                $text = '🗳 ' . $item->get_name() . ($item->is($user_vote) ? '✔️' : '');
                                $keyboard[$i][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-vote-' . $server->getId() . '-' . $item->getUserId());
                            }
                            $text = '💣 ' . $item->get_name() . ($item->is($user_select) ? '✔️' : '');
                            $keyboard[$i][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_MineLayer . '-' . $server->getId() . '-' . $item->getUserId());
                            $i++;

                        }

                    }


                } else {
                    AnswerCallbackQuery($dataid, '🔴 الان نمیتونی هدفت را تغییر بدی');
                    die();
                }

                break;

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;
    case ROLE_MineLayerMafia:
//        $power = intval($selector->getInt()->select(ROLE_MineLayer, 'mine'));
//        $selector->set($power - 1, ROLE_MineLayerMafia, 'mine');
        switch ($user_select->getUserId()) {

            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:
            case 10:

                if ($user->getRoleId() == ROLE_MineLayerMafia) {
//                    $selector->delete(ROLE_MineLayerMafia);
                    $selector->delete(ROLE_MineLayerMafia, 'select-4');
                    // The MineLayer is selecting the mine location
                    // Store the MineLayer's selection under their own user ID
                    $selector->set($user_select->getUserId(), ROLE_MineLayerMafia, 'select-4', $user->getUserId());

                    // Build the keyboard for the MineLayer
                    $mine_selection = $selector->select(ROLE_MineLayerMafia, 'select-4', $user->getUserId())->getUserId();

                    // Build the keyboard
                    $houses = [];
                    $number_emojis = [
                        1 => '1⃣',
                        2 => '2⃣',
                        3 => '3⃣',
                        4 => '4⃣',
                        5 => '5⃣',
                        6 => '6⃣',
                        7 => '7⃣',
                        8 => '8⃣',
                        9 => '9⃣',
                        10 => '🔟',
                    ];

                    for ($i = 1; $i <= 10; $i++) {
                        $is_selected = ($i == $mine_selection);
                        // Use the emoji representation of the number
                        $text = $number_emojis[$i] . ($is_selected ? ' ✔️' : '');
                        $houses[] = $telegram->buildInlineKeyboardButton(
                            $text,
                            '',
                            $day . '/server-' . $server->league_id . '-' . ROLE_MineLayerMafia . '-' . $server->getId() . '-' . $i
                        );
                    }

                    // Split the buttons into rows
                    $keyboard = array_chunk($houses, 5);

                    // Update the message and keyboard
                    EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

//                // Optionally, when the MineLayer has made their selection, proceed to the next step
//                if ($mine_selection > 0) {
//                    $user->SendMessage('شما یک مین در خانه شماره ' . $mine_selection . ' قرار دادید.');
//                }

                } else {

                    // The target is selecting houses to defuse the mine

                    // Retrieve existing selections for this target user
                    $select = $selector->select(ROLE_MineLayerMafia, 'select-3-0', $user->getUserId());
                    $select_2 = $selector->select(ROLE_MineLayerMafia, 'select-3-1', $user->getUserId());
                    $select_3 = $selector->select(ROLE_MineLayerMafia, 'select-3-2', $user->getUserId());

                    // Extract the selected house numbers
                    $select_value = $select ? $select->getUserId() : 0;
                    $select_2_value = $select_2 ? $select_2->getUserId() : 0;
                    $select_3_value = $select_3 ? $select_3->getUserId() : 0;

                    // For tracking the order of selection, store in an array
                    $selections = [];
                    if ($select_value > 0) $selections[] = $select_value;
                    if ($select_2_value > 0) $selections[] = $select_2_value;
                    if ($select_3_value > 0) $selections[] = $select_3_value;

                    $selected_house = $user_select->getUserId();

                    // Check if the house is already selected
                    if (in_array($selected_house, $selections)) {
                        // Remove the selection
                        if ($selected_house == $select_value) {
                            $selector->delete(ROLE_MineLayerMafia, 'select-3-0', $user->getUserId());
                            $select_value = 0;
                        } elseif ($selected_house == $select_2_value) {
                            $selector->delete(ROLE_MineLayerMafia, 'select-3-1', $user->getUserId());
                            $select_2_value = 0;
                        } elseif ($selected_house == $select_3_value) {
                            $selector->delete(ROLE_MineLayerMafia, 'select-3-2', $user->getUserId());
                            $select_3_value = 0;
                        }
                        // Remove from selections array
                        $selections = array_diff($selections, [$selected_house]);
                    } else {
                        // Add selection if less than 3 selections
                        if (count($selections) < 3) {
                            if ($select_value == 0) {
                                $selector->set($selected_house, ROLE_MineLayerMafia, 'select-3-0', $user->getUserId());
                                $select_value = $selected_house;
                            } elseif ($select_2_value == 0) {
                                $selector->set($selected_house, ROLE_MineLayerMafia, 'select-3-1', $user->getUserId());
                                $select_2_value = $selected_house;
                            } elseif ($select_3_value == 0) {
                                $selector->set($selected_house, ROLE_MineLayerMafia, 'select-3-2', $user->getUserId());
                                $select_3_value = $selected_house;
                            }
                            $selections[] = $selected_house;
                        } else {
                            // Replace the oldest selection
                            $oldest_selection = array_shift($selections);
                            // Delete the oldest selection
                            if ($oldest_selection == $select_value) {
                                $selector->delete(ROLE_MineLayerMafia, 'select-3-0', $user->getUserId());
                                $selector->set($selected_house, ROLE_MineLayerMafia, 'select-3-0', $user->getUserId());
                                $select_value = $selected_house;
                            } elseif ($oldest_selection == $select_2_value) {
                                $selector->delete(ROLE_MineLayerMafia, 'select-3-1', $user->getUserId());
                                $selector->set($selected_house, ROLE_MineLayerMafia, 'select-3-1', $user->getUserId());
                                $select_2_value = $selected_house;
                            } elseif ($oldest_selection == $select_3_value) {
                                $selector->delete(ROLE_MineLayerMafia, 'select-3-2', $user->getUserId());
                                $selector->set($selected_house, ROLE_MineLayerMafia, 'select-3-2', $user->getUserId());
                                $select_3_value = $selected_house;
                            }
                            $selections[] = $selected_house;
                        }
                    }

                    $select = $selector->select(ROLE_MineLayerMafia, 'select-3-0', $user->getUserId());
                    $select_2 = $selector->select(ROLE_MineLayerMafia, 'select-3-1', $user->getUserId());
                    $select_3 = $selector->select(ROLE_MineLayerMafia, 'select-3-2', $user->getUserId());

//                    error_log($select->getUserId()  ." select");
//                    error_log($select_2->getUserId() . " select2");
//                    error_log($select_3->getUserId() .  " select3");

//                    $telegram->sendMessage([
//                        'chat_id'    => $chatid,
//                        'text'       => $select->getUserId() . " select" . $select_2->getUserId() . " select2" . $select_3->getUserId() .  " select3",
//                        'parse_mode' => 'html'
//                    ]);

                    // Build the keyboard with updated selections
                    $houses = [];
                    $number_emojis = [
                        1 => '1⃣',
                        2 => '2⃣',
                        3 => '3⃣',
                        4 => '4⃣',
                        5 => '5⃣',
                        6 => '6⃣',
                        7 => '7⃣',
                        8 => '8⃣',
                        9 => '9⃣',
                        10 => '🔟',
                    ];

                    for ($i = 1; $i <= 10; $i++) {
                        // Only show checkmarks for the target's selections
                        $is_selected = in_array($i, $selections);
                        // Use the emoji representation of the number
                        $text = $number_emojis[$i] . ($is_selected ? ' ✔️' : '');
                        $houses[] = $telegram->buildInlineKeyboardButton(
                            $text,
                            '',
                            $day . '/server-' . $server->league_id . '-' . ROLE_MineLayerMafia . '-' . $server->getId() . '-' . $i
                        );
                    }

                    // Split the buttons into rows
                    $keyboard = array_chunk($houses, 5);

                    // Update the message and keyboard
                    EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));



                    // Check if all three selections are made
                    if (count($selections) == 3) {
                        // All selections made
                        // Proceed to handle the outcome
                        // For example, you can call a function to process the selections
//                    processMineDefusal($user_id, $selections);
                    }

                }

                break;
            default:

                $select = $selector->user()->select(ROLE_MineLayerMafia);

                if ($day == 1 || ceil($server->getPeopleAlive() / 2) == 1) {

                    if (!$select->is($user_select)) {

                        $selector->set($user_select->getUserId(), ROLE_MineLayerMafia)->answerCallback();

                        foreach ($users_server as $user) {

                            if (!$user->is($chatid) && !$user->dead() && $user->get_role()->group_id != 2 ) {

                                $text = '💣 ' . $user->get_name() . ($user->is($user_select) ? '✔️' : '');
                                $keyboard[] = [
                                    $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_MineLayerMafia . '-' . $server->getId() . '-' . $user->getUserId())
                                ];
                            }

                        }

                    } else {

                        $selector->delete(ROLE_MineLayerMafia);
                        foreach ($users_server as $user) {

                            if (!$user->is($chatid) && $user->get_role()->group_id != 2) {

                                $text = '💣 ' . $user->get_name();
                                $keyboard[] = [
                                    $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_MineLayerMafia . '-' . $server->getId() . '-' . $user->getUserId())
                                ];

                            }

                        }

                    }

                } elseif ($user->getStatus() == 'voting') {
//                    $mine = $selector->getInt()->select(ROLE_Gambeler, 'mine', false);

                    if (!$select->is($user_select)) {

                        $selector->set($user_select->getUserId(), ROLE_MineLayerMafia)->answerCallback();

                    } else {

                        $selector->delete(ROLE_MineLayerMafia);
                        $user_select->setUserId(0);

                    }
                    $power = intval($selector->getInt()->select(ROLE_MineLayerMafia, 'mine')) ?? 0;
                    $i = 0;
                    $user_vote = $selector->getInt()->select($selector->getUser(ROLE_MineLayerMafia)->getUserId(), 'vote');
                    foreach ($users_server as $item) {

                        if ($item->check($chatid) && $server->setUserId($item->getUserId())->getMetaUser('no-vote') != 'on') {
                            if (!$user_red_carpet) {
                                $text = '🗳 ' . $item->get_name() . ($item->is($user_vote) ? '✔️' : '');
                                $keyboard[$i][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-vote-' . $server->getId() . '-' . $item->getUserId());
                            }
                            if ($item->get_role()->group_id != 2 && $power > 0) {
                                $text = '💣 ' . $item->get_name() . ($item->is($user_select) ? '✔️' : '');
                            $keyboard[$i][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_MineLayerMafia . '-' . $server->getId() . '-' . $item->getUserId());
                        }
                            $i++;
                        }

                    }


                } else {
                    AnswerCallbackQuery($dataid, '🔴 الان نمیتونی هدفت را تغییر بدی');
                    die();
                }

                break;

        }

        EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        break;

    case MineDefuse:
        $selector->set($user_select->getUserId(), ROLE_MineLayer, 'chosen-for-bomb');
        break;
    case ROLE_Shahrdar:

        if ($server->getMeta('shahrdar')) {
            AnswerCallbackQuery($dataid, '🔴 قبلا خودت به همه معرفی کردی', true);
        } elseif ($server->getStatus() != 'voting') {
            AnswerCallbackQuery($dataid, '🚸 این پنل منقضی شده است. لطفا از پنل های جدید استفاده کنید.', true);
            exit();
        } else {
            $keyboard = [];

            foreach ($users_server as $item) {
                if (!$item->is_user_in_game() || $item->sleep())
                    continue;

                $message = "#معرفی \n\n";
                $message .= "شهردار تصمیم به افشای نقش گرفته \n";
                $message .= "امروز شاهد وتو شهردار خواهیم بود . \n\n";
                $message .= "👨🏻‍🦰 شهردار : {$user->get_name()} \n";
                // $message = $user->get_name() . " 👨🏻‍🦰 شهردار است\n";
                // $message .= "حق وتو دارد و رای او 3 عدد حساب خواهند شد . \n";
                $item->SendMessageHtml($message);

            }
            $server->updateMeta('shahrdar', 'on');

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
        }

        break;
    // ............ SYSTEM ............
    // رای گیری
    case 'vote':

        if (!check_time_chat($user->getUserId(), 1, 'vote')) {
            AnswerCallbackQuery($dataid, '⚠️ لطفا مجددا تلاش کنید.');
            exit();
        }


        if (!$user_select->dead()) {

            if ($user->hacked()) {

                AnswerCallbackQuery($dataid, '🧑🏻‍💻 شما توسط هکر هک شده اید و امروز قادر به رای دادن نیستید.', true);

                exit();

            }

            if (in_array($user->encode(), unserialize((get_server_meta($server->getId(), 'select', ROLE_Naghel) ?? []))) && !$user->dead()) {
                AnswerCallbackQuery($dataid, '⚠️ شما لال شدید و امکان رای دادن ندارید.');
                exit();
            }

            if ($selector->user()->select(ROLE_Kalantar, 'last-select')->is($user) && $server->role_exists(ROLE_Kalantar)) {
                AnswerCallbackQuery($dataid, '❌ شما امروز نمیتوانید رای بدهید.', true);

                exit();
            }
            $user_greenway = '';
            $user_greenway = get_server_meta_user($server->getId(), 'card-green_way', $day);

            if ($user_select->getUserId() == $user_greenway) {
                AnswerCallbackQuery($dataid, '🃏این بازیکن کارت مسیر سبز دریافت کرده!', true);

                exit();
            }

            $user_status = $user->getStatus();
            $shahrdar_used = false;
            $is_user_shahrdar = false;
            if ($user->get_role()->id == ROLE_Shahrdar)
                $is_user_shahrdar = true;

            if ($server->getMeta('shahrdar') == 'on') {
                $shahrdar = $selector->getUser(ROLE_Shahrdar);
                $shahrdar_used = true;

            }

            if ($user_status == 'voting' && !$server->is()) {

                $user_vote = $selector->user()->select($user->getUserId(), 'vote');
                $used_parts = unserialize($server->setUserId(ROLE_Ehdagar)->getMetaUser('used_parts'));
                $previous_day = $day - 1;
                $hasHandTransplant = isset($used_parts[$previous_day]) && $used_parts[$previous_day]['part'] == 'hand' && $used_parts[$previous_day]['receiver'] == $user->getUserId();
                if (!$user_vote->is($user_select)) {

                    if ($server->setUserId($user_select->getUserId())->getMetaUser('no-vote') != 'on') {

                        if ($server->getMeta('court') != 'close' && $server->getStatus() != 'night') {

                            AnswerCallbackQuery($dataid, '🗳 شما به ' . $user_select->get_name() . ' رای دادید.');


                            if ($shahrdar_used && $is_user_shahrdar) {
                                // add_server_meta( $server->getId(), 'vote', $user_select->getUserId(), $chatid );
                                delete_server_meta($server->getId(), 'vote', $chatid);

                                $selector->set($user_select->getUserId(), $chatid, 'vote');
                                $link->insert('server_meta', [
                                    'user_id' => $chatid,
                                    'server_id' => $server->getId(),
                                    'meta_key' => 'vote',
                                    'meta_value' => $user_select->getUserId()
                                ]);
                                $link->insert('server_meta', [
                                    'user_id' => $chatid,
                                    'server_id' => $server->getId(),
                                    'meta_key' => 'vote',
                                    'meta_value' => $user_select->getUserId()
                                ]);

                            } else {
                                $selector->set($user_select->getUserId(), $chatid, 'vote');
                            }
                            if ($hasHandTransplant) {
                                // Count vote twice if user has hand transplant
                                $link->insert('server_meta', [
                                    'user_id' => $chatid,
                                    'server_id' => $server->getId(),
                                    'meta_key' => 'vote',
                                    'meta_value' => $user_select->getUserId()
                                ]);
                            }
                            $votes = get_votes_by_server($server->getId());
                            $vote_users = [];
                            $user_vote_index = 1;

                            foreach ($votes as $id => $vote) {

                                if (isset($vote->meta_value) && isset($vote->user_id) && $vote->user_id > 0) {

                                    $vote_users[$vote->meta_value][] = $vote->user_id;

                                    if ($chatid == $vote->user_id) {

                                        $user_vote_index = $id + 1;

                                    }

                                }

                            }
                            $user_greenway = '';
                            $user_greenway = get_server_meta_user($server->getId(), 'card-green_way', $day);



                            switch ($user->getRoleId()) {

                                case ROLE_Bazpors:


                                    $i = 0;
                                    $bazpors_select = $selector->user()->select(ROLE_Bazpors);

                                    foreach ($users_server as $item) {

                                        if ($item->check($chatid) && $server->setUserId($item->getUserId())->getMetaUser('no-vote') != 'on') {
                                            if ($item->getUserId() == $user_greenway) {
                                                $text = '🤠 ' . $item->get_name();
                                            } else
                                                $text = '🗳 ' . $item->get_name() . ($item->is($user_select) ? '✔️' : '');


                                            // $text             = '🗳 ' . $item->get_name() . ( $item->is( $user_select ) ? '✔️' : '' );
                                            if (!$user_red_carpet) {
                                                $keyboard[$i][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-vote-' . $server->getId() . '-' . $item->getUserId());
                                            }
                                            $text = '🔗 ' . $item->get_name() . ' ' . ($bazpors_select->is($item) ? '✔️' : '');
                                            $keyboard[$i][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-question-' . $server->getId() . '-' . $item->getUserId());
                                            $i++;

                                        }

                                    }


                                    break;

                                case ROLE_Gambeler:

                                    $i = 0;
                                    $select_gambeler = $selector->user()->select(ROLE_Gambeler);

                                    foreach ($users_server as $item) {

                                        if ($item->check($chatid) && $server->setUserId($item->getUserId())->getMetaUser('no-vote') != 'on') {
                                            if ($item->getUserId() == $user_greenway) {
                                                $text = '🤠 ' . $item->get_name();
                                            } else
                                                $text = '🗳 ' . $item->get_name() . ($item->is($user_select) ? '✔️' : '');
                                            if (!$user_red_carpet) {
                                                // $text             = '🗳 ' . $item->get_name() . ( $item->is( $user_select ) ? '✔️' : '' );
                                                $keyboard[$i][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-vote-' . $server->getId() . '-' . $item->getUserId());
                                            }
                                            $text = '🎮 ' . $item->get_name() . ' ' . ($select_gambeler->is($item) ? '✔️' : '');
                                            $keyboard[$i][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_Gambeler . '-' . $server->getId() . '-' . $item->getUserId());
                                            $i++;

                                        }

                                    }
                                    break;

                                case ROLE_MineLayer:

                                    $i = 0;
                                    $select_minelayer = $selector->user()->select(ROLE_MineLayer);

                                    foreach ($users_server as $item) {

                                        if ($item->check($chatid) && $server->setUserId($item->getUserId())->getMetaUser('no-vote') != 'on') {
                                            if ($item->getUserId() == $user_greenway) {
                                                $text = '🤠 ' . $item->get_name();
                                            } else
                                                $text = '🗳 ' . $item->get_name() . ($item->is($user_select) ? '✔️' : '');
                                            if (!$user_red_carpet) {
                                                // $text             = '🗳 ' . $item->get_name() . ( $item->is( $user_select ) ? '✔️' : '' );
                                                $keyboard[$i][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-vote-' . $server->getId() . '-' . $item->getUserId());
                                            }
                                            $text = '💣 ' . $item->get_name() . ' ' . ($select_minelayer->is($item) ? '✔️' : '');
                                            $keyboard[$i][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_MineLayer . '-' . $server->getId() . '-' . $item->getUserId());
                                            $i++;

                                        }

                                    }

                                    break;
                                case ROLE_MineLayerMafia:

                                    $i = 0;
                                    $select_minelayer = $selector->user()->select(ROLE_MineLayerMafia);

                                    foreach ($users_server as $item) {

                                        if ($item->check($chatid) && $server->setUserId($item->getUserId())->getMetaUser('no-vote') != 'on') {
                                            if ($item->getUserId() == $user_greenway) {
                                                $text = '🤠 ' . $item->get_name();
                                            } else
                                                $text = '🗳 ' . $item->get_name() . ($item->is($user_select) ? '✔️' : '');
                                            if (!$user_red_carpet) {
                                                // $text             = '🗳 ' . $item->get_name() . ( $item->is( $user_select ) ? '✔️' : '' );
                                                $keyboard[$i][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-vote-' . $server->getId() . '-' . $item->getUserId());
                                            }
                                            if ($item->get_role()->group_id != 2){
                                                $text = '💣 ' . $item->get_name() . ' ' . ($select_minelayer->is($item) ? '✔️' : '');
                                            $keyboard[$i][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-' . ROLE_MineLayerMafia . '-' . $server->getId() . '-' . $item->getUserId());
                                        }
                                            $i++;

                                        }

                                    }

                                    break;
                                default:

                                    // $card_greenway = $server->getMeta('card-green_way');
                                    foreach ($users_server as $item) {

                                        if ($item->check($chatid) && $server->setUserId($item->getUserId())->getMetaUser('no-vote') != 'on') {

                                            if ($item->getUserId() == $user_greenway) {
                                                $text = '🤠 ' . $item->get_name();
                                            } else
                                                $text = '🗳 ' . $item->get_name() . ' ' . ($user_select->is($item) ? '[[' . $user_vote_index . ']] ✔️' : '');
                                            if (!$user_red_carpet) {
                                                $keyboard[] = [
                                                    $telegram->buildInlineKeyboardButton(
                                                        __replace__($text, [
                                                            '[[10]]' => '🔟',
                                                            '[[11]]' => '1️⃣1️⃣',
                                                            '[[12]]' => '1️⃣2️⃣',
                                                            '[[13]]' => '1️⃣3️⃣',
                                                            '[[1]]' => '1️⃣',
                                                            '[[2]]' => '2️⃣',
                                                            '[[3]]' => '3️⃣',
                                                            '[[4]]' => '4️⃣',
                                                            '[[5]]' => '5️⃣',
                                                            '[[6]]' => '6️⃣',
                                                            '[[7]]' => '7️⃣',
                                                            '[[8]]' => '8️⃣',
                                                            '[[9]]' => '9️⃣',
                                                        ]),
                                                        '',
                                                        $day . '/server-' . $server->league_id . '-vote-' . $server->getId() . '-' . $item->getUserId()
                                                    )
                                                ];
                                            }

                                        }

                                    }

                                    break;

                            }


                            if ($user_vote->getUserId() > 0) {

                                $message = '▪️ [[user]] رای خود را پس گرفت و به [[user2]] رای داد .' . "\n";

                            } else {

                                $message = '▪️ [[user]] به [[user2]] رای داد . ' . "\n";

                            }
                            // '[[user]]'  => "<b>" . $user->get_name() . "</b>" . ($is_user_shahrdar && $shahrdar_used) ? ' ( شهردار 👨🏻‍🦰 ) ' : '',
                            $vote_message_user = "<b>" . $user->get_name() . "</b>";
                            if ($is_user_shahrdar && $shahrdar_used) {
                                $vote_message_user = "<b>" . $user->get_name() . "</b>" . ' ( شهردار 👨🏻‍🦰 ) ';
                            }
                            if ($hasHandTransplant) {
                                // Count vote twice if user has hand transplant
                                $vote_message_user = "<b>" . $user->get_name() . "</b>" . ' ( ✍🏻 ) ';
                            }
                            __replace__($message, [
                                '[[user]]' => $vote_message_user,
                                '[[user2]]' => "<b><u>" . $user_select->get_name() . "</u></b>",
                            ]);

                            foreach ($vote_users as $key => $value) {

                                $message .= '<b>' . '[[user]] ' . '[[count]]' . '</b>' . ' رای' . "\n";
                                __replace__($message, [
                                    '[[user]]' => "<u>" . name((int) $key, $server->getId()) . "</u>",
                                    '[[count]]' => "<u>" . count($value) . "</u>",
                                ]);

                            }

                            foreach ($users_server as $user) {
                                // if (!$user_red_carpet) {

                                (!$user->is_user_in_game() || $user->dead() || $user->sleep()) || $user->SendMessageHtml();
                                // }

                                if ($user->is($chatid) && count($keyboard)) {

                                    EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

                                }

                            }

                            // -------------------------------------------------------------------------------------------------------------

                            if (get_server_meta($server->getId(), 'status') != 'night') {

                                $status = 'night';
                                $ceil = ceil($server->getPeopleAlive() / 2);

                                // if ($user_red_carpet) {
                                //     $accused = new User( (int) $user_red_carpet, $server->getId() );
                                //     $server->updateMeta( 'court', 'close' );
                                //     $server->updateMeta( 'is', 'on' );
                                //     $message = "⚖️ <u>{$accused->get_name()}</u> به دادگاه فراخوانده شد . \n";
                                //     $message .= 'متهم ۱۵ ثانیه فرصت دارد تا از خود دفاع کند .' . "\n";
                                //     $message .= '💬 چت : فقط برای متهم';

                                //     if ( ! is_server_meta( $server->getId(), 'accused' ) )
                                //     {

                                //         update_server_meta( $server->getId(), 'accused', $accused->getUserId() );

                                //     }

                                //     $status = 'court-2';

                                // }
                                // else {
                                foreach ($vote_users as $key => $value) {

                                    if ($ceil <= count($value)) {

                                        $server->updateMeta('court', 'close');
                                        $server->updateMeta('is', 'on');
                                        $message = '⚖️ [[user]] به دادگاه فراخوانده شد .' . "\n";
                                        $message .= 'متهم ۱۵ ثانیه فرصت دارد تا از خود دفاع کند .' . "\n";
                                        $message .= '💬 چت : فقط برای متهم';

                                        $accused = new User((int) $key, $server->getId());

                                        __replace__($message, [
                                            '[[user]]' => "<u>" . $accused->get_name() . "</u>"
                                        ]);

                                        if (!is_server_meta($server->getId(), 'accused')) {

                                            update_server_meta($server->getId(), 'accused', $accused->getUserId());

                                        }

                                        $status = 'court-2';

                                        break;

                                    }

                                }
                                // }


                                if ($status == 'court-2') {


                                    sleep(rand(0.1, 0.9));

                                    if (get_server_meta($server->getId(), 'status') != 'court-2') {

                                        $server->setStatus($status)->charge(15)->clearVotesMeta();

                                        foreach ($users_server as $item) {

                                            if ($item->is_user_in_game() && !is_server_meta($server->getId(), 'message-sended', $item->getUserId()) && !$item->sleep()) {

                                                $result = SendMessage($item->getUserId(), $message, null, null, 'html');
                                                if (isset($result->message_id)) {

                                                    add_server_meta($server->getId(), 'message-sended', 'sended', $item->getUserId());
                                                    $item->setStatus('voting');

                                                }

                                            }

                                        }

                                    }

                                    $server->deleteMeta('is');

                                }

                            } else {

                                AnswerCallbackQuery($dataid, '🔴 هم اکنون امکان رای گیری وجود ندارد.');

                            }

                        } else {

                            AnswerCallbackQuery($dataid, '🔴 هم اکنون امکان رای گیری وجود ندارد.');

                        }
                    } else {

                        AnswerCallbackQuery($dataid, '⛔️ این کاربر از جادو استفاده کرده است.');

                    }

                } else {

                    AnswerCallbackQuery($dataid, '⛔️ نمیتوانید به این کاربر رای بدهید.');

                }

            } else {

                AnswerCallbackQuery($dataid, '🔴 هم اکنون امکان رای گیری وجود ندارد.');

            }

        } else {

            AnswerCallbackQuery($dataid, '🔴 این کاربر مرده است.');

        }

        break;
    // رای به گناه
    // رای به بی گناه
    case 'court':
    case '^court':

        if ($user->hacked()) {
            AnswerCallbackQuery($dataid, '🧑🏻‍💻 شما توسط هکر هک شده اید و امروز قادر به رای دادن نیستید.', true);
            exit();
        }

        if (in_array($user->encode(), unserialize((get_server_meta($server->getId(), 'select', ROLE_Naghel) ?? []))) && !$user->dead()) {
            AnswerCallbackQuery($dataid, '⚠️ شما لال شدید و امکان رای دادن ندارید.');
            exit();
        }

        try {

            $vote = get_server_meta($server->getId(), 'vote', $chatid);

            if ($vote == $data[2]) {
                delete_server_meta($server->getId(), 'vote', $chatid);
                $vote = '';
            } else {
                update_server_meta($server->getId(), 'vote', $data[2], $chatid);
                $vote = $data[2];
            }

            $keyboard = [
                [
                    $telegram->buildInlineKeyboardButton(($vote == '^court' ? '✔️' : '') . 'بی‌گناه', '', $day . '/server-' . $data[1] . '-^court-' . $server->getId() . '-' . $user_select->getUserId()),
                    $telegram->buildInlineKeyboardButton(($vote == 'court' ? '✔️ ' : '') . 'گناهکار', '', $day . '/server-' . $data[1] . '-court-' . $server->getId() . '-' . $user_select->getUserId()),
                ]
            ];

            switch ($user->getRoleId()) {
                case ROLE_Ghazi:
                    if (!is_server_meta($server->getId(), 'ghazi')) {

                        delete_server_meta($server->getId(), 'ghazi', ROLE_Ghazi);
                        $keyboard[][] = $telegram->buildInlineKeyboardButton('❌ ابطال', '', $day . '/server-' . $server->league_id . '-pass_voting-' . $server->getId());

                    }
                    break;
                case ROLE_Fadaii:

                    delete_server_meta($server->getId(), 'fadaii');
                    $keyboard[][] = $telegram->buildInlineKeyboardButton('فدا شدن', '', $day . '/server-' . $server->league_id . '-fadaii-' . $server->getId());

                    break;

                case ROLE_Big_Khab:

                    if (!is_server_meta($server->getId(), 'bigKhan', ROLE_Big_Khab)) {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton(('🟢 بی‌گناه'), '', $day . '/server-' . $server->league_id . '-big_khab-' . $server->getId() . '-' . 2),
                            $telegram->buildInlineKeyboardButton(('🔴 گناهکار'), '', $day . '/server-' . $server->league_id . '-big_khab-' . $server->getId() . '-' . 1),
                        ];
                        $selector->delete(ROLE_Big_Khab);

                    }

                    break;
            }

            EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));

        } catch (Exception $exception) {

            throw new Exception('ERROR ON INSERT VOTE 2 IN BOT! Message: ' . $exception->getMessage());

        }
        break;
    // کارت ها:
    case 'cards':

        try {


            $server_cards = json_decode($server->getMeta('cards'), true);
            $server_cards = count($server_cards) > 0 ? $server_cards : [];
            $card_id = $data[5];

            if ($server_cards["card-{$card_id}"]) {
                AnswerCallbackQuery($dataid, '🃏 این کارت قبلا استفاده شده است!');
            } elseif ($server->getMeta('select-card')) {
                AnswerCallbackQuery($dataid, '🃏 ❗️امکان تغییر کارت وجود ندارد .');
            } elseif ($server->getStatus() == 'light') {
                AnswerCallbackQuery($dataid, '🃏 شب است و امکان انتخاب کارت وجود ندارد .');
            } else {
                $all_cards = get_cards();


                if ($card_id == 'rand') {
                    $available_cards = [];
                    foreach ($all_cards as $card) {
                        if ($card->is_active == 1 && !$server_cards["card-{$card->id}"]) {
                            $available_cards[] = $card;
                        }
                    }

                    if (count($available_cards)) {
                        $card_rand = array_rand($available_cards);
                        $select_card_rand = $available_cards[$card_rand];
                        $message = $select_card_rand->name . ' انتخاب شد';
                        $card_id = $select_card_rand->id;

                    } else
                        $message = '🃏 کارتهای بازی تمام شد است .';
                }
                if ($message)
                    EditMessageText($chatid, $messageid, $message);


                switch ($card_id) {

                    // بی خوابی
                    case 1:
                        // if ( $server->getStatus() ==  'light')  {   
                        // setStatus( 'light' )
                        $server->resetSelect()->setStatus('light')->charge(10)
                            ->deleteMeta('hack')->deleteMeta('sleep')->deleteMeta('bakreh')
                            ->resetMessage()->sendMessageHtml("🃏 کاربر <s>" . $user->get_name() . "</s> از کارت 😵<b> بی‌خوابی </b> استفاده کرد. \nبازی شب نمیشود و مستقیم به روز بعد میرویم. \n \n  💬 چت : فعال برای همه \n 🌞 روز 👈🏻 10 ثانیه");
                        $server_cards['card-1'] = true;
                        $server->updateMeta('select-card', 'on');
                        // EditMessageText( $chatid, $messageid, $message );
                        // AnswerCallbackQuery( $dataid, '🃏 شما کارت 😵 بی‌خوابی را استفاده کردید.' );
                        // }


                        break;

                    // افشای نقش
                    case 2:
                    // فرش قرمز
                    case 5:
                    // مسیر سبز
                    case 6:
                    // روز سکوت
                    case 7:

                        if ($user_select->getUserId() > 0) {
                            AnswerCallbackQuery($dataid, '🟢 شما ' . $user_select->get_name() . ' را انتخاب کردید');
                            $server->updateMeta('select-card', 'on');
                            $server_cards['card-' . $card_id] = true;
                            switch ($card_id) {
                                case 2:
                                case '2':
                                    // افشای نقش
                                    update_server_meta($server->getId(), 'card-2', $day, $user_select->getUserId());
                                    $server->charge(10)->sendMessageHtml("🃏 کاربر <s>" . $user->get_name() . "</s> از کارت 🗣 افشا استفاده کرد.  \nفردی که کارت گرفته روز بعد ساید او برای همه افشا میشود . \n \n  💬 چت : فعال برای همه \n 🌙 شب 👈🏻 10 ثانیه");

                                    break;

                                case 5:
                                case '5':
                                    // فرش قرمز
                                    update_server_meta($server->getId(), 'card-red_carpet', $day + 1, $user_select->getUserId());
                                    $server->charge(10)->sendMessageHtml("🃏 کاربر <s>" . $user->get_name() . "</s> از کارت 🥵 <b>فرش قرمز</b> استفاده کرد. \nفردی که فرش قرمز گرفته روز بعد مستقیم به دفاعیه میرود . \n \n  💬 چت : فعال برای همه \n 🌙 شب 👈🏻 10 ثانیه");

                                    break;
                                case 6:
                                case '6':
                                    // مسیر سبز
                                    update_server_meta($server->getId(), 'card-green_way', $day + 1, $user_select->getUserId());
                                    $server->charge(10)->sendMessageHtml("🃏 کاربر <s>" . $user->get_name() . "</s> از کارت 🤠 <b>مسیر سبز</b> استفاده کرد. \nفردی که مسیر سبز گرفته به هیچ عنوان فردا در دفاعیه نمیرود. \n \n  💬 چت : فعال برای همه \n 🌙 شب 👈🏻 10 ثانیه");

                                    break;

                                case 7:
                                case '7':
                                    // روز سکوت
                                    update_server_meta($server->getId(), 'card-silence', $day + 1, $user_select->getUserId());
                                    // $user_select->SendMessageHtml('🃏 شما کارت روز سکوت دریافت کردید ! امروز قادر به حرف زدن نیستید .');
                                    $server->charge(10)->sendMessageHtml("🃏 کاربر <s>" . $user->get_name() . "</s> از کارت 🤫 <b>روز سکوت</b> استفاده کرد. \n<b>{$user_select->get_name()}</b> به هیچ عنوان فردا قادر به صحبت نخواهد بود . \n \n  💬 چت : فعال برای همه \n 🌙 شب 👈🏻 10 ثانیه");

                                    break;

                                default:
                                    # code...
                                    break;
                            }

                        }



                        break;

                    // جشن مافیا
                    case 3:

                        $server->updateMeta('card-mafia_day', $day + 1);
                        $server->updateMeta('select-card', 'on');
                        $server_cards['card-3'] = true;
                        $server->charge(10)->sendMessageHtml("🃏 کاربر <s>" . $user->get_name() . "</s> از کارت 😈 <b> جشن مافیا</b> استفاده کرد. \nروز بعد هیچکسی از بازی بیرون نمیره . \n \n  💬 چت : فعال برای همه \n 🌙 شب 👈🏻 10 ثانیه");

                        // AnswerCallbackQuery( $dataid, '🃏 شما کارت جشن مافیا را استفاده کردید.' );
                        // EditMessageText( $chatid, $messageid, $message );

                        break;

                    // روز محاکمه
                    case 4:

                        $server->updateMeta('card-4', $day);
                        $server_cards['card-4'] = true;
                        $server->charge(10)->sendMessageHtml("🃏 کاربر <s>" . $user->get_name() . "</s> از کارت 😵‍💫 <b>روز محاکمه</b> استفاده کرد. \nروز بعد به محض این که روز شود رای گیری میشود و صحبتی انجام نمیشود. \n \n  💬 چت : فعال برای همه \n 🌙 شب 👈🏻 10 ثانیه");
                        $server->updateMeta('select-card', 'on');

                        break;

                    // شهر در امان
                    case 8:

                        $server->updateMeta('card-city_safe', $day);
                        $server->updateMeta('select-card', 'on');
                        $server_cards['card-8'] = true;
                        $server->charge(10)->sendMessageHtml("🃏 کاربر <s>" . $user->get_name() . "</s> از کارت 👽 <b>شهر در امان</b> استفاده کرد. \nدر شب بعد هر شهروندی به هر دلیلی مورد حمله قرار بگیرد ، کشته نمیشود . \n \n  💬 چت : فعال برای همه \n 🌙 شب 👈🏻 10 ثانیه");

                        // EditMessageText( $chatid, $messageid, $message );
                        // AnswerCallbackQuery( $dataid, '🃏 شما کارت شهر در امان را استفاده کردید.' );

                        break;

                    // default:



                    //     break;

                }
                $keyboard = [];
                if (in_array($card_id, [2, 5, 6, 7])) {
                    foreach ($server->users() as $item) {
                        if (!$item->dead()) {
                            $keyboard[][] = $telegram->buildInlineKeyboardButton('🃏 ' . $item->get_name() . ($item->is($user_select) ? '✔️' : ''), '', $day . '/server-' . $server->league_id . '-cards-' . $server->getId() . '-' . $item->getUserId() . '-' . $card_id);
                        }
                    }
                }
                // else {
                //     foreach ($all_cards as $card )
                //         {
                //             if ($card->is_active == 1  &&  !$server_cards[ "card-{$card->id}" ]  ) { // &&  !$server_cards[ "card-{$card->id}" ] 
                //                 $keyboard[] = [
                //                     $telegram->buildInlineKeyboardButton( $card->name . ( $card_id == $card->id ? '✔️' : '' ) , '', $day . '/server-' . $server->league_id . '-cards-' . $server->getId() . '-0-' . $card->id ),
                //                 ];
                //             }
                //         }
                // }
                EditKeyboard($chatid, $messageid, $telegram->buildInlineKeyBoard($keyboard));
                $server->updateMeta('cards', json_encode($server_cards));
            }



        } catch (Exception | Throwable $e) {

            $message = "<b>🔴 WARNING ERROR ON CARDS 🔴</b>" . "\n";
            $message .= "<b>👉 Error File : { " . $e->getFile() . ':' . "<code>" . $e->getLine() . "</code>" . " }</b>" . "\n";
            if (isset($server) && $server instanceof Server && $server->getId() > 0) {

                $message .= "<i>ERROR Server: {" . $server->getId() . "}</i>" . "\n \n";

            }
            $message .= "<b>👾 Error Content:</b>" . "\n \n";
            $message .= "<b><code>" . $e->getMessage() . "</code></b>";
            SendMessage(202910544, $message, null, null, 'html');

        }

        break;
    // -----------------------------------------------------------

}

if (get_server_meta($server->getId(), 'is-online', $chatid) == 'no') {

    add_server_meta($server->getId(), 'is-online', 'yes', $chatid);

}

