<?php
/** @noinspection ALL */


use library\Role;
use library\Server;
use library\User;


switch ($user_role->id) {

    // ........... GROUP 1 ...........
    case ROLE_Karagah:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            $message .= '💬 چت : غیرفعال ' . "\n";

        } else {

            $message .= '🗞 مأموریت : یک نفر را انتخاب کنید تا در صورت مشکوک بودن به شما اطلاع داده شود .' . "\n";
            $message .= '💬 چت : غیرفعال ' . "\n";

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";
        if ($user->check($bazpors_select) && !$user->is($dozd_select)) {

            foreach ($users_server as $item) {

                if ($item->check($user)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔦 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-search-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessage();
        break;
    case ROLE_Pezeshk:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } else {


            $message .= '🗞 مأموریت : انتخاب کنید میخواهید جان چه کسی را نجات دهید .' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($user->check($bazpors_select) && !$user->is($dozd_select)) {

            $status_doctor = is_server_meta($server->getId(), 'doctor', ROLE_Pezeshk);
            $shahrdar_used = false;
            if ($server->getMeta('shahrdar')) {
                $shahrdar = $selector->getUser(ROLE_Shahrdar);
                $shahrdar_used = true;
            }
            foreach ($users_server as $item) {

                if (!$item->dead() && (!$user->is($item) || !$status_doctor) && (!$shahrdar_used || !$item->is($shahrdar))) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('💉 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-heal-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');


        } else
            $user->SendMessage();


        if ($day == 1 && $server->role_exists(ROLE_Zambi)) {

            $message = '❗️ یک زامبی در شهر وجود داره و تا قبل اینکه به مافیا تبدیل بشه فرصت داری درمانش کنی.' . " \n \n";

            $zambi = $selector->getUser(ROLE_Zambi);
            $random_role = $server->randomUser([$zambi->getUserId(), $user->getUserId()]);
            $random_role_2 = $server->randomUser([
                $zambi->getUserId(),
                $random_role->getUserId(),
                $user->getUserId()
            ]);
            $random_role_3 = $server->randomUser([
                $zambi->getUserId(),
                $random_role->getUserId(),
                $random_role_2->getUserId(),
                $user->getUserId()
            ]);


            $targets = [];
            $targets[] = $zambi;
            $targets[] = $random_role;
            $targets[] = $random_role_2;
            $targets[] = $random_role_3;

            shuffle($targets);

            $message .= '🧟 افراد مشکوک به زامبی : ';
            foreach ($targets as $id => $target) {
                $message .= '<u><b>' . $target->get_name() . '</b></u>' . ($id + 1 != count($targets) ? " و " : '');
            }

            $user->SendMessageHtml($message);

        }

        break;
    case ROLE_Ehdagar:
        // error_log("I am here");
        // $message.="تست اهدا گر";
        // Fetch the serialized 'used_parts' data and unserialize it

        // Initialize an array to keep track of available parts


        // Remove parts selected on previous days and find selected part for today
        if ($user->dead() && false) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

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
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ((!$user->is($bazpors_select)) && !$user->is($dozd_select) && $day > 1) {

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
                SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');
            } else {
                $user->SendMessageHtml("تمامی قطعات برای امروز انتخاب شده‌اند.");
            }

        } else {
            $user->SendMessageHtml($message);
        }
        break;
    case ROLE_Bazpors:

        if (!$user->dead()) {

            if ($user->is($dozd_select)) {

                $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
                $message .= 'امشب فعالیتی ندارید.' . "\n";
                $message .= '💬 چت : غیرفعال ' . "\n";
                $user->SendMessageHtml();

            } elseif ($bazpors_select->getUserId() > 0 && $day == 1) {

                $message .= 'متهم : [[user]]' . "\n";
                $message .= '💬 چت : فقط با متهم' . "\n";
                $message .= '❗️ شما امشب نمیتوانید او را محکوم کنید.' . "\n";
                $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";
                __replace__($message, [
                    '[[user]]' => $bazpors_select->get_name()
                ]);
                $user->SendMessage();

            } elseif ($bazpors_select->getUserId() > 0 && !$bazpors_select->dead()) {

                $message .= 'متهم : [[user]]' . "\n";

                $message .= '💬 چت : فقط با متهم' . "\n";

                $bazpors_status = $server->setUserId(ROLE_Bazpors)->getMetaUser('status');

                if ($bazpors_status != 'no-body') {

                    $message .= '❗️تصمیم بگیرید که او را محکوم به مرگ میکنید یا نه!' . "\n";

                } else {

                    $message .= '❗️شما توانایی محکوم کردن کسی را ندارید.' . "\n";

                }

                $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

                __replace__($message, [
                    '[[user]]' => $bazpors_select->get_name()
                ]);

                if ($bazpors_status != 'no-body') {

                    SendMessage(
                        $user->getUserId(),
                        $message,
                        $telegram->buildInlineKeyBoard([
                            [
                                $telegram->buildInlineKeyboardButton('⚖️ محکوم', '', $day . '/server-' . $server->league_id . '-bazpors_kill-' . $server->getId() . '-' . $bazpors_select->getUserId()),
                                $telegram->buildInlineKeyboardButton('⭕️ آزاد', '', $day . '/server-' . $server->league_id . '-bazpors_release-' . $server->getId() . '-' . $bazpors_select->getUserId()),
                            ]
                        ])
                    );

                } else
                    $user->SendMessage();

            } else {

                $message .= 'شما کسی را زندانی نکردید .' . "\n";
                $message .= '💬 چت : غیرفعال' . "\n";
                $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";
                $user->SendMessage();

            }

        } else {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";
            $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";
            $user->SendMessage();

        }

        break;
    case ROLE_Sahere:

        if ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($day == 1 || $user->is($dozd_select)) {

            $message .= '💬 چت : غیرفعال' . "\n";

        } else {

            $message .= '💬 چت : کشته شده ها' . "\n";

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        $user->SendMessage();

        break;
    case ROLE_Sniper:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } else {

            if ($day == 1) {

                $message .= 'امشب تفنگ خود را آماده میکنید.' . "\n";

            } else {

                $message .= 'شما امشب میتوانید یک نفر را هدف گلوله قرار دهید.' . "\n";

            }

            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($day == 1 || $user->dead() || $user->is($bazpors_select) || $user->is($dozd_select)) {

            $user->SendMessage();

        } else {

            foreach ($users_server as $item) {

                if ($item->check($user)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-fight-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        }

        break;
    case ROLE_Didban:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } else {

            $message .= '🗞 مأموریت : یک نفر را انتخاب کنید و خانه او را بپایید .' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($user->check($bazpors_select) && !$user->is($dozd_select)) {

            foreach ($users_server as $item) {

                if ($item->check($user)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('👀 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-did_ban-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_Mohaghegh:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            $message .= '💬 چت : غیرفعال ' . "\n";

        } else {

            $message .= '🗞 مأموریت : یک نفر را انتخاب کنید تا نقش او را حدس بزنید.' . "\n";
            $message .= '💬 چت : غیرفعال ' . "\n";

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($user->check($bazpors_select) && !$user->is($dozd_select)) {

            foreach ($users_server as $item) {

                if ($item->check($user)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔎 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-search_mohaghegh-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_Police:

        $police_count = $selector->getInt()->select(ROLE_Police, 'police-count');

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } else {

            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

            if (2 - $police_count != 0) {

                $message .= '🗞 مأموریت : انتخاب کنید امشب هوشیار می‌مانید یا نه !' . "\n";

                $message .= 'تعداد هوشیاری : ';
                $message .= str_repeat('🟦 ', 2 - $police_count) . "\n";

            } else {

                $message .= '🗞 مأموریت : فرصت هوشیاری شما به اتمام رسیده .' . "\n";

            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($police_count == 2 || $user->is($bazpors_select) || $user->dead() || $user->is($dozd_select)) {

            $user->SendMessageHtml();

        } else {

            $keyboard[][] = $telegram->buildInlineKeyboardButton('👮🏻‍♂️ هوشیار بمانید', '', $day . '/server-' . $server->league_id . '-police-' . $server->getId() . '-' . $user->getUserId());

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        }

        break;
    case ROLE_Keshish:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } elseif ($server->getMeta('keshish') == 'use' || $day == 1) {

            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } else {

            $message .= '🗞 مأموریت : انتخاب کنید امشب قصد دعا کردن برای شهر را دارید یا نه .' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($user->check($bazpors_select) && !$user->is($dozd_select) && $server->getMeta('keshish') != 'use' && $day > 1) {

            $keyboard[] = [
                $telegram->buildInlineKeyboardButton('دعا کردن 🤲🏻', '', $day . '/server-' . $server->league_id . '-keshish-' . $server->getId() . '-' . $user->getUserId())
            ];

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_Kalantar:

        $power = $selector->getInt()->select(ROLE_Kalantar, 'power');

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } elseif ($power < 2 && $day > 1) {

            $message .= 'ماموریت: انتخاب کنید چه کسی را میخواهید حق رای دادن را از او بگیرید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } else {

            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($user->check($bazpors_select) && !$user->is($dozd_select) && $power < 2 && $day > 1) {

            $last_select = $selector->user()->select(ROLE_Kalantar, 'last-select');
            foreach ($users_server as $item) {

                if ($item->check($user) && !$last_select->is($item)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('👨‍✈️ ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-kalantar-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            $user->setKeyboard($telegram->buildInlineKeyBoard($keyboard))->SendMessageHtml($message);

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_Kaboy:

        $selector->delete(ROLE_Kaboy);
        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } else {

            $message .= '🗞 مأموریت : یک نفر که حدس میزنید مافیا باشد را انتخاب کنید تا در صورت اعدام ، او نیز با شما کشته شود .' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($user->check($bazpors_select) && !$user->is($dozd_select)) {

            foreach ($users_server as $item) {

                if ($item->check($user)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🕴 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-kaboy-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();
        break;
    case ROLE_TofangDar:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } elseif ($day == 1) {

            $message .= '🗞 ماموریت : شما امشب نمیتوانید به کسی تفنگ بدهید.' . "\n";
            $message .= '⚪️: تیر مشقی' . "\n";
            $message .= '🔴: تیر جنگی' . "\n";
            $message .= '💬 چت : غیرفعال ' . "\n";

        } else {

            $message .= '🗞 ماموریت : انتخاب کنید به چه کسی تفنگ جنگی یا تفنگ مشقی میدهید .' . "\n";
            $message .= '⚪️: تیر مشقی' . "\n";
            $message .= '🔴: تیر جنگی' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($user->check($bazpors_select) && !$user->is($dozd_select) && $day > 1) {

            $tir = $selector->getInt()->select(ROLE_TofangDar, 'count');
            $i = 0;

            foreach ($users_server as $item) {
                if ($item->check($user)) {

                    $keyboard[$i][] = $telegram->buildInlineKeyboardButton('⚪️ ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-tofang_dar_1-' . $server->getId() . '-' . $item->getUserId());

                    if ($tir < 3) {

                        $keyboard[$i][] = $telegram->buildInlineKeyboardButton('🔴 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-tofang_dar_2-' . $server->getId() . '-' . $item->getUserId());

                    }

                    $i++;

                }
            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_EynakSaz:
        $count_eynak = 3;
        $eynak_saz = $selector->GetInt()->select(ROLE_EynakSaz, 'eynak');

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            $message .= '💬 چت : غیرفعال ' . "\n";

        } elseif ($eynak_saz == 0) {

            $message .= '🗞 مأموریت: انتخاب کنید به چه کسی عینک میدهید.' . "\n";
            $message .= str_repeat('👓 ', $count_eynak - $eynak_saz);
            $message .= ' شما سه عینک دارید .' . "\n";
            $message .= '💬 چت : غیرفعال ' . "\n";

        } elseif ($eynak_saz == 1) {

            $message .= '🗞 مأموریت: انتخاب کنید به چه کسی عینک میدهید.' . "\n";
            $message .= str_repeat('👓 ', $count_eynak - $eynak_saz);
            $message .= ' شما دو عینک دارید .' . "\n";
            $message .= '💬 چت : غیرفعال ' . "\n";

        } elseif ($eynak_saz == 2) {

            $message .= '🗞 مأموریت: انتخاب کنید به چه کسی عینک میدهید.' . "\n";
            $message .= str_repeat('👓 ', $count_eynak - $eynak_saz);
            $message .= ' شما یک عینک دارید .' . "\n";
            $message .= '💬 چت : غیرفعال ' . "\n";

        } else {

            $message .= '❗️ عینک های شما تمام شده است.' . "\n";
            $message .= '💬 چت : غیرفعال ' . "\n";

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($user->check($bazpors_select) && !$user->is($dozd_select) && $count_eynak != $eynak_saz) {

            foreach ($users_server as $item) {

                if ($item->check($user)) {

                    $keyboard[][] = $telegram->buildInlineKeyboardButton('👓 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-eynak-' . $server->getId() . '-' . $item->getUserId());

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();
        break;
    case ROLE_Fereshteh:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } else {

            if (!is_server_meta($server->getId(), 'fereshteh', ROLE_Fereshteh) && $day > 1) {

                $message .= '🗞 مأموریت : یک نفر را از دنیای مردگان انتخاب کنید تا او را زنده کنید و به شهر برگردد.' . "\n";

            }

            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 ۴۵ ثانیه' . "\n";

        if ($user->check($bazpors_select) && !$user->is($dozd_select) && $day != 1 && !is_server_meta($server->getId(), 'fereshteh', ROLE_Fereshteh)) {

            foreach ($users_server as $item) {

                if (!$item->is($user) && $item->dead() && $item->get_role()->group_id == 1 && $item->is_user_in_game()) {

                    if (
                        $item->getRoleId() != ROLE_Fadaii || !is_server_meta($server->getId(), 'fadaii', ROLE_Fadaii)
                    ) {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('👰‍♀️ ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-healed-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

            }

            if (count($keyboard) > 0) {

                SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

            } else {

                $user->SendMessageHtml();

            }

        } else
            $user->SendMessageHtml();
        break;

    case ROLE_Cobcob:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } else {

            //اگر کوب کوب مرده بود و یک شب از مردنش گذشته بود ازش بپرسه میخواد برگرده یا نه
            if (!is_server_meta($server->getId(), 'cobcob', ROLE_Cobcob) && get_server_meta($server->getId(), 'day_of_kill', ROLE_Cobcob)+1 < $day) {

                $message .= '🗞 مأموریت : آیا میخواهید امشب به بازی برگردید ؟.' . "\n";

            }

            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 ۴۵ ثانیه' . "\n";

        if ($user->check($bazpors_select) && !$user->is($dozd_select) && $day != 1 && !is_server_meta($server->getId(), 'cobcob', ROLE_Cobcob)  && get_server_meta($server->getId(), 'day_of_kill', $item->getUserId() )+1 < $day ) {

            foreach ($users_server as $item) {

                if ($item->is($user) && $item->dead() && $item->get_role()->group_id == 1 && $item->is_user_in_game()) {

                    if (
                        $item->getRoleId() != ROLE_Fadaii || !is_server_meta($server->getId(), 'fadaii', ROLE_Fadaii)
                    ) {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('👰‍♀️ ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-cobcob-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

            }

            if (count($keyboard) > 0) {

                SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

            } else {

                $user->SendMessageHtml();

            }

        } else
            $user->SendMessageHtml();
        break;
    case ROLE_Memar:
        $memar_count = $selector->getInt()->select(ROLE_Memar, 'select-count');

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } else {

            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

            if (2 - $memar_count != 0) {

                $message .= '🗞 مأموریت : یک نفر را انتخاب کنید تا در خانه ی او شروع به ساخت و ساز کنید.' . "\n";

                $message .= 'تعداد چکش ها : ';
                $message .= str_repeat('🔨 ', 2 - $memar_count) . "\n";

            } else {

                $message .= '🗞 مأموریت : شما فرصت ساخت و ساز ندارید.' . "\n";

            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($memar_count == 2 || $user->is($bazpors_select) || $user->dead() || $user->is($dozd_select)) {

            $user->SendMessageHtml();

        } else {

            $power = $selector->select(ROLE_Memar, 'power');

            foreach ($users_server as $item) {

                if (!$item->dead() && (!$item->is($user) || !$power->is($user))) {

                    $keyboard[][] = $telegram->buildInlineKeyboardButton('🏗 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-memar-' . $server->getId() . '-' . $item->getUserId());

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        }

        break;
    case ROLE_Bodygard:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } else {

            $message .= '🗞 مأموریت : انتخاب کنید میخواهید از جان چه کسی محافظت کنید .' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($user->check($bazpors_select) && !$user->is($dozd_select)) {

            $select_bodygard = $selector->select(ROLE_Bodygard, 'power');

            foreach ($users_server as $item) {

                if (!$item->dead() && (!$user->is($item) || !$item->is($select_body_gard))) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('💂‍♀️ ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_Bodygard . '-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessage();

        break;
    case ROLE_Shield:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } else {

            $heart = $server->getMeta('heart-shield') ?? 0;
            $message .= '🔖 ماموریت : ' . str_repeat('❤️ ', 2 - $heart) . str_repeat('🤍 ', $heart) . ' شما ' . $number_to_word->NumbersToWord(2 - $heart) . ' جان دارید .' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        $user->SendMessageHtml();

        break;
    case ROLE_KhabarNegar:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } else {

            $message .= '🔖 ماموریت : ' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($user->check($bazpors_select) && !$user->is($dozd_select)) {

            foreach ($users_server as $item) {

                if ($item->check($user)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('📸 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-khabar_negar-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessage();
        break;
    case ROLE_Zambi:

        $status_zambi = $server->getMeta('zambi') != 'use';
        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } else {

            if ($status_zambi) {

                $message .= '🧟‍♂️ شما درمان نشده اید.' . "\n";

            } else {

                $message .= '🗞 مأموریت : انتخاب کنید نقش چه کسی را میخواهید بگیرید.' . "\n";

            }
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if (!$status_zambi && $user->check($bazpors_select) && !$user->is($dozd_select)) {

            foreach ($users_server as $item) {

                if (!$item->is($user)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🧟‍♂️ ' . $item->get_name() . ($item->dead() ? '☠️' : ''), '', $day . '/server-' . $server->league_id . '-zambi-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_Senator:

        $status_senator = $server->getMeta('senator') != 'use';
        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            $message .= '💬 چت : غیرفعال ' . "\n";

        } else {

            if ($status_senator && $day > 1) {

                $message .= '❗️لیست خود را انتخاب کنید .' . "\n";
                $message .= '🗞 مأموریت : برای تایید استعلام لازم است حداقل ۴ نفر را انتخاب کنید.' . "\n";

            }
            $message .= '💬 چت : غیرفعال ' . "\n";

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($day > 1 && $status_senator && $user->check($bazpors_select) && !$user->is($dozd_select)) {

            foreach ($users_server as $item) {

                if ($item->check($user)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🧾 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_Senator . '-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_TelefonChi:


        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } else {

            if (count($server->getDeadUsers()) > 0) {
                $message .= '🗞 مأموریت : انتخاب کنید میخواهید تماس بین چه کسانی بر قرار شود.' . "\n";
            }
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if (count($server->getDeadUsers()) > 0 && $user->check($bazpors_select) && !$user->is($dozd_select)) {

            foreach ($users_server as $item) {

                if ($item->check($user)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('📞 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_TelefonChi . '-' . $server->getId() . '-' . $item->getUserId())
                    ];

                } elseif ($item->dead() && $item->is_user_in_game()) {
                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('📱 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_TelefonChi . '-' . $server->getId() . '-' . $item->getUserId())
                    ];
                }

            }

            $keyboard[] = [
                $telegram->buildInlineKeyboardButton('☎️ بر قراری ارتباط', '', $day . '/server-' . $server->league_id . '-' . ROLE_TelefonChi . '-' . $server->getId() . '-' . $user->getUserId())
            ];

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_Jadogar:


        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } else {

            $message .= '🗞 مأموریت : انتخاب کنید تا مسیر حمله را تغییر دهید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($user->check($bazpors_select) && !$user->is($dozd_select)) {

            $select_jadogar = $selector->select(ROLE_Jadogar, 'power');
            foreach ($users_server as $item) {

                if (!$item->dead() && !$select_jadogar->is($item)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🪄 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_Jadogar . '-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }


            if (count($keyboard) > 1) {

                $user->setKeyboard($telegram->buildInlineKeyBoard($keyboard));

            }

        }

        $user->SendMessageHtml();

        break;
    case ROLE_MosaferZaman:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } elseif ($server->getMeta('mosafer') == 'use' || $day == 1) {

            $message .= '💬 چت : غیرفعال ' . "\n";

        } else {

            $message .= '🗞 مأموریت : انتخاب کنید تا افرادی که شب قبل کشته شدند زنده شوند.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";


        if ($user->check($bazpors_select) && !$user->is($dozd_select) && $server->getMeta('mosafer') != 'use' && $day > 1) {

            $select_mosafer_zaman = $selector->getString()->select(ROLE_MosaferZaman, 'targets');
            $targets = unserialize($select_mosafer_zaman) ?? [];

            if (count($targets) > 0) {

                $keyboard[] = [
                    $telegram->buildInlineKeyboardButton('✝️ زنده کردن', '', $day . '/server-' . $server->league_id . '-' . ROLE_MosaferZaman . '-' . $server->getId() . '-' . $user->getUserId())
                ];

                SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

            } else {
                $user->SendMessageHtml($message);
            }

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_Framason:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } else {

            $message .= '🗞 مأموریت : انتخاب کنید تا در صورت شهروند بودن هدف او را به تیم خود دعوت کنید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($user->check($bazpors_select) && !$user->is($dozd_select)) {

            foreach ($users_server as $item) {

                if ($item->check($user) && !in_array($item->encode(), $select_framason)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🪬 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_Framason . '-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_Ahangar:

        $ahangar_count = $selector->getInt()->select(ROLE_Ahangar, 'select-count');

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } else {

            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

            if (2 - $ahangar_count != 0) {

                $message .= '🗞 مأموریت : یک نفر را انتخاب کنید تا به او یک زره بدهید تا از او محافظت کنید.' . "\n";

                $message .= 'تعداد زره ها : ';
                $message .= str_repeat('🛡 ', 2 - $ahangar_count) . "\n";

            } else {

                $message .= '🗞 مأموریت : شما فرصت دادن زره ندارید.' . "\n";

            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if (!$server->isFullMoon() || $ahangar_count == 2 || $user->is($bazpors_select) || $user->dead() || $user->is($dozd_select)) {

            $user->SendMessageHtml();

        } else {

            $last_select = $selector->select(ROLE_Ahangar, 'last-select');

            foreach ($users_server as $item) {

                if (!$item->is($last_select) && $item->check($user)) {

                    $keyboard[][] = $telegram->buildInlineKeyboardButton('🛡 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_Ahangar . '-' . $server->getId() . '-' . $item->getUserId());

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        }

        break;
    case ROLE_Tardast:

        $power = $selector->select(ROLE_Tardast);

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } else {

            if (isset($framason_team) && in_array($user->encode(), $select_framason)) {

                $message .= '🪬 شما یک ماسون هستید .' . "\n \n \n";
                $message .= $framason_team;
                $message .= '💬 چت : فقط با تیم ماسونی ' . "\n";

            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

            if ($power->getUserId() <= 0 && $day > 1) {
                $message .= '🗞 ماموریت : یک نفر را انتخاب کنید و قابلیت او را از بین ببرید .';
            } elseif ($day == 1) {
                $message .= '🗞 ماموریت : اکنون زمان مناسبی برای تردستی نیست .';
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($power->getUserId() <= 0 && $day > 1 && $user->check($bazpors_select) && !$user->is($dozd_select)) {

            foreach ($users_server as $item) {

                if ($item->check($user)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🤙🏻 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_Tardast . '-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    // ........... GROUP 2 ...........
    case ROLE_Terrorist:

        $selector->delete(ROLE_Terrorist);
        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } else {

            $message .= '🗞 مأموریت : یک نفر را انتخاب کنید تا در صورت اعدام ، او نیز با شما کشته شود . .' . "\n \n";

            $message .= $server->showTeam($user->getUserId());

            $message .= '💬 چت : فقط با تیم' . "\n";
        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";


        if ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId())) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🧨 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-terrorist-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_Godfather:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= '🗞 مأموریت: شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } else {

            $message .= ' 🗞 مأموریت: یک نفر را انتخاب کنید تا خود یا معشوقه به آن حمله کنید .' . "\n \n";


            $message .= $server->showTeam($user->getUserId());


            $message .= '💬 چت : فقط با تیم' . "\n";

        }


        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";


        if ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId())) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-god-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_Mashooghe:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= '🗞 مأموریت: شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } else {

            $message .= '🗞 مأموریت: یک نفر را انتخاب کنید تا به آن حمله کنید . در صورت انتخاب هدف توسط گادفادر ، شما به هدف حمله میکنید .' . "\n \n";

            $message .= $server->showTeam($user->getUserId());

            $message .= '💬 چت : فقط با تیم' . "\n";
        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId())) {

            $role = $server->get_priority();

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . ($role->id == $user_role->id ? 'god' : 'mashooghe') . '-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');
        } else
            $user->SendMessageHtml();
        break;
    case ROLE_Nato:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= '🗞 مأموریت: شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } else {

            $message .= '🗞 مأموریت: یک نفر را انتخاب کنید تا نقش دقیق او به شما نشان داده شود.' . "\n \n";

            $message .= $server->showTeam($user->getUserId());

            $message .= '💬 چت : فقط با تیم' . "\n";

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        $role = $server->get_priority();

        if ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId()) && $role->id == $user_role->id) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-god-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } elseif ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId())) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔍 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . 'nato' . '-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_TohmatZan:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= '🗞 مأموریت: شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } else {

            $message .= '🗞 مأموریت: یک نفر را انتخاب کنید تا استعلام او برای کاراگاه یا محقق اشتباه نشان داده شود.' . "\n \n";

            $message .= $server->showTeam($user->getUserId());

            $message .= '💬 چت : فقط با تیم' . "\n";

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        $role = $server->get_priority();

        if ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId()) && $role->id == $user_role->id) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-god-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } elseif ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId())) {

            $last_select = $selector->user()->select(ROLE_TohmatZan, 'last-select');

            foreach ($users_server as $item) {

                if ($item->check($user) && !$last_select->is($item)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('👻 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . 'tohmat' . '-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_Hacker:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= '🗞 مأموریت: شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } else {

            $message .= '🗞 مأموریت: یک نفر را انتخاب کنید تا در صورت هک شدن به شما اطلاع داده شود.' . "\n \n";

            $message .= $server->showTeam($user->getUserId());

            $message .= '💬 چت : فقط با تیم' . "\n";

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";


        $role = $server->get_priority();

        if ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId()) && $role->id == $user_role->id) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-god-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } elseif ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId())) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton(' 🧑🏻‍💻 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . 'hacker' . '-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_HardFamia:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= '🗞 مأموریت: شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } else {
            $message .= '🗞 مأموریت : شما میتوانید یک نفر را انتخاب کنید تا در هر صورت کشته شود .' . "\n \n";

            $message .= $server->showTeam($user->getUserId());

            $message .= '💬 چت : فقط با تیم' . "\n";
        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        $role = $server->get_priority();

        $power = intval($selector->getInt()->select(ROLE_HardFamia, 'power'));
        $result = $power == 0 || ($power == 1 && !$selector->getUser(ROLE_Godfather)->dead());

        if ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId()) && $role->id == $user_role->id) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-god-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } elseif ($user->check($bazpors_select) && $result && apply_filters('filter_mafia', $user->getUserId())) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔪 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . 'hard_mafia' . '-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_Gorkan:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= '🗞 مأموریت: شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif(!$server->isFullMoon()) {
            $message .= '🗞 مأموریت : شما میتوانید یک نفر را انتخاب کنید تا در هر صورت کشته شود .' . "\n \n";
        }


        $role = $server->get_priority();

        $power = intval($selector->getInt()->select(ROLE_Gorkan, 'power'));
        $result = $power == 0 || ($power == 1 && !$selector->getUser(ROLE_Godfather)->dead());

        if ($user->check($bazpors_select)) {

            foreach ($users_server as $item) {

                if ($item->check($user)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('⚰️' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-god-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_AfsonGar:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= '🗞 مأموریت: شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } else {

            $message .= '🗞 مأموریت : یک نفر را انتخاب کنید تا قدر او را غیرفعال کنید.' . "\n \n";

            $message .= $server->showTeam($user->getUserId());

            $message .= '💬 چت : فقط با تیم' . "\n";

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        $role = $server->get_priority();

        if ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId()) && $role->id == $user_role->id) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-god-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } elseif ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId())) {

            $last_select = $selector->user()->select(ROLE_AfsonGar, 'last-select');
            foreach ($users_server as $item) {

                if ($item->check($user) && !$last_select->is($item) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🦹🏻 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . 'afson_gar' . '-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();


        break;
    case ROLE_Noche:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= '🗞 مأموریت: شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } else {

            $message .= "\n" . $server->showTeam($user->getUserId());

            $message .= '💬 چت : فقط با تیم' . "\n";

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        $role = $server->get_priority();

        if (
            $user->check($bazpors_select) && $role->id == $user_role->id && apply_filters('filter_mafia', $user->getUserId())
        ) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . ($role->id == $user_role->id ? 'god' : 'mashooghe') . '-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_BAD_DOCTOR:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= '🗞 مأموریت: شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } else {

            $message .= '🗞 مأموریت : میتوانید از یک نفر در برابر حملات محافظت کنید.' . "\n \n";
            $message .= $server->showTeam($user->getUserId());
            $message .= '💬 چت : فقط با تیم' . "\n";

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        $role = $server->get_priority();

        if ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId()) && $role->id == $user_role->id) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-god-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } elseif ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId())) {


            $status_doctor = is_server_meta($server->getId(), 'doctor', ROLE_BAD_DOCTOR);

            foreach ($server->roleByGroup(2) as $item) {

                if (!$item->dead() && (!$user->is($item) || !$status_doctor)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🩹 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-doctor-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_Tobchi:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= '🗞 مأموریت: شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } else {
            $message .= '🗞 مأموریت : اگر قصد پرتاب توپ جنگی دارید هدف را برای شلیک انتخاب کنید.' . "\n \n";

            $message .= $server->showTeam($user->getUserId());

            $message .= '💬 چت : فقط با تیم' . "\n";
        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        $role = $server->get_priority();

        if (
            $user->check($bazpors_select) && ($server->getMeta('tobchi') != 'use' || $role->id == $user_role->id) && apply_filters('filter_mafia', $user->getUserId())
        ) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton(($role->id == $user_role->id ? '🔫 ' : '💣 ') . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . ($role->id == $user_role->id ? 'god' : 'tobchi') . '-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_ShekarChi:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= '🗞 مأموریت: شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } else {
            $message .= '🗞 مأموریت : انتخاب کنید تا به یکی از اهداف هدف شما حمله کنید.' . "\n \n";

            $message .= $server->showTeam($user->getUserId());

            $message .= '💬 چت : فقط با تیم' . "\n";
        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        $role = $server->get_priority();

        if ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId()) && $role->id == $user_role->id) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-god-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } elseif ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId())) {

            $select_shekar_chi = $selector->user()->select(ROLE_ShekarChi, 'last-select');

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2 && !$select_shekar_chi->is($item)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🕶 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_ShekarChi . '-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_ShabKhosb:

        $power_shabkhosb = $selector->getInt()->select(ROLE_ShabKhosb, 'power');

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= '🗞 مأموریت: شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } else {
            $message .= '🗞 مأموریت : یک نفر را انتخاب کنید تا به خواب عمیق فرو برود.' . "\n \n";

            $message .= $server->showTeam($user->getUserId());

            $message .= '💬 چت : فقط با تیم' . "\n";
        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        $role = $server->get_priority();

        if ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId()) && $role->id == $user_role->id) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-god-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } elseif ($user->check($bazpors_select) && $power_shabkhosb < 2 && apply_filters('filter_mafia', $user->getUserId())) {

            $last_select = get_server_meta($server->getId(), 'last-user', ROLE_ShabKhosb);

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    if (!$item->is($last_select)) {

                        $keyboard[] = [
                            $telegram->buildInlineKeyboardButton('💆‍♂ ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . 'sleep' . '-' . $server->getId() . '-' . $item->getUserId())
                        ];

                    }

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_MozakarehKonandeh:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= '🗞 مأموریت: شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } else {

            if ($day == 1)
                $message .= '🔖 مأموریت : زمان مناسبی برای مذاکره نیست!' . "\n \n";
            else
                $message .= '🔖 مأموریت : یک نفر را انتخاب کنید تا مذاکره انجام شود .' . "\n \n";


            $message .= $server->showTeam($user->getUserId());

            $message .= '💬 چت : فقط با تیم' . "\n";
        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        $role = $server->get_priority();


        if ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId()) && $role->id == $user_role->id) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-god-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } elseif ($user->check($bazpors_select) && !is_server_meta($server->getId(), 'mozakereh') && apply_filters('filter_mafia', $user->getUserId()) && $server->getCountDeadTerror() > 0) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🤝 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-mozakereh-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_Dalghak:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= '🗞 مأموریت: شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } else {
            $message .= '🔖 مأموریت : منحل کردن شهر.' . "\n \n";

            $message .= $server->showTeam($user->getUserId());

            $message .= '💬 چت : فقط با تیم' . "\n";
        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        $role = $server->get_priority();


        if ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId()) && $role->id == $user_role->id) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-god-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } elseif ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId()) && !is_server_meta($server->getId(), 'dalghak', ROLE_Dalghak)) {

            $keyboard[][] = $telegram->buildInlineKeyboardButton('🤡 خندیدن', '', $day . '/server-' . $server->league_id . '-dalghak-' . $server->getId() . '-' . $user->getUserId());

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_Yakoza:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= '🗞 مأموریت: شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } else {

            if ($day > 1) {
                $message .= '🔖 مأموریت : یک نفر را انتخاب کنید تا به جای شما به مافیا تبدیل شود.' . "\n \n";
            }

            $message .= $server->showTeam($user->getUserId());

            $message .= '💬 چت : فقط با تیم' . "\n";
        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        $role = $server->get_priority();


        if ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId()) && $role->id == $user_role->id) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-god-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } elseif ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId()) && $day > 1) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🎴 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_Yakoza . '-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_Shayad:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $selector->delete(ROLE_Shayad);
            $message .= '🗞 مأموریت: شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } else {

            $selector->delete(ROLE_Shayad);
            $message .= '🔖 مأموریت : یک نفر را انتخاب کنید تا پس از مرگتان نقش او نمایش داده شود.' . "\n \n";

            $message .= $server->showTeam($user->getUserId());

            $message .= '💬 چت : فقط با تیم' . "\n";

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        $role = $server->get_priority();


        if ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId()) && $role->id == $user_role->id) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-god-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } elseif ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId())) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('👹 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_Shayad . '-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_ShahKosh:

        $power = $selector->getInt()->select(ROLE_ShahKosh, 'power');

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= '🗞 مأموریت: شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } else {

            if ($power < 2 && $day > 1 && $server->isFullMoon()) {
                $message .= '🔖 مأموریت : یک نفر را انتخاب کنید تا در صورت حدس درست نقش او، کشته شود.' . "\n \n";
            } else {
                $message .= '🗞 ماموریت : امشب فعالیتی ندارید .' . "\n \n";
            }

            $message .= $server->showTeam($user->getUserId());

            if ($server->isFullMoon()) {
                $message .= '💬 چت : امکان چت با تیم وجود ندارد' . "\n";
            } else {
                $message .= '💬 چت : فقط با تیم' . "\n";
            }


        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        $role = $server->get_priority();


        if ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId()) && $role->id == $user_role->id) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-god-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } elseif ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId()) && $power < 2 && $day > 1 && $server->isFullMoon()) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🧛🏿‍♂ ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_ShahKosh . '-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_Dam:

        $power = $selector->getInt()->select(ROLE_Dam, 'power');

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= '🗞 مأموریت: شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } else {

            if ($power < 2) {
                $message .= '🔖 مأموریت : یک نفر را انتخاب کنید تا در صورتی که کسی به خانه او برود کشته شود.' . "\n \n";
            }

            $message .= $server->showTeam($user->getUserId());
            $message .= '💬 چت : فقط با تیم' . "\n";

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        $role = $server->get_priority();


        if ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId()) && $role->id == $user_role->id) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 2) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🔫 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-god-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } elseif ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId()) && $power < 2) {

            foreach ($users_server as $item) {

                if ($item->check($user)) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🧱 ' . $item->get_name() . ($item->get_role()->group_id == 2 ? '🔴' : ''), '', $day . '/server-' . $server->league_id . '-' . ROLE_Dam . '-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    // ........... GROUP 3 ...........
    case ROLE_Killer:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            $message .= '💬 چت : غیرفعال ' . "\n";

        } else {

            $message .= '🗞 مأموریت : شما میتوانید یک نفر را برای حمله کردن به او انتخاب کنید .' . "\n";
            if ($server->getMeta('killer') == 'on') {

                $message .= '⚔️ شما امشب میتوانید به دو نفر حمله کنید 😉' . "\n";

            }
            if (in_array($server->league_id, MOSTAGHEL_TEAM)) {
                $message .= "\n" . $server->showTeam($user->getUserId(), 3);
                $message .= '💬 چت : فقط با تیم' . "\n";
            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($user->check($bazpors_select) && !$user->is($dozd_select)) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 3) {

                    $keyboard[] = [

                        $telegram->buildInlineKeyboardButton('☠️' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-kill-' . $server->getId() . '-' . $item->getUserId())

                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_Ashpaz:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            $message .= '💬 چت : غیرفعال ' . "\n";

        } else {

            $message .= '🗞 مأموریت : شما میتوانید یک نفر را انتخاب کنید تا ان را مسموم کنید .' . "\n";

            if (in_array($server->league_id, MOSTAGHEL_TEAM)) {
                $message .= "\n" . $server->showTeam($user->getUserId(), 3);
                $message .= '💬 چت : فقط با تیم' . "\n";
            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($user->check($bazpors_select) && !$user->is($dozd_select)) {

            $last_select = $selector->user()->select(ROLE_Ashpaz, 'last-select');
            foreach ($users_server as $item) {

                if ($item->check($user) && !$last_select->is($item) && $item->get_role()->group_id != 3) {

                    $keyboard[] = [

                        $telegram->buildInlineKeyboardButton('👨🏻‍🍳 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_Ashpaz . '-' . $server->getId() . '-' . $item->getUserId())

                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_Bazmandeh:

        $bazmandeh_shield = $selector->getInt()->select($user->getUserId(), 'shield-2');

        $role = $server->get_priority(3);

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            $message .= '💬 چت : غیرفعال ' . "\n";

        } elseif ($server->league_id == LEAGUE_MOSTAGHEL && $role->id == $user_role->id) {

            $message .= '🗞 مأموریت : شما میتوانید یک نفر را برای حمله کردن به او انتخاب کنید .' . "\n";
            if (in_array($server->league_id, MOSTAGHEL_TEAM))
                $message .= "\n" . $server->showTeam($user->getUserId(), 3);
            $message .= '💬 چت : غیرفعال ' . "\n";

        } elseif ($bazmandeh_shield == 0) {

            $message .= '🗞 ماموریت : هدف زنده ماندن تا آخر بازی‌ست !' . "\n";
            $message .= '🦺🦺 شما دو جلیقه دارید .' . "\n";

            if (in_array($server->league_id, MOSTAGHEL_TEAM)) {
                $message .= "\n" . $server->showTeam($user->getUserId(), 3);
                $message .= '💬 چت : فقط با تیم' . "\n";
            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } elseif ($bazmandeh_shield == 1) {

            $message .= '🗞 ماموریت : هدف زنده ماندن تا آخر بازی‌ست !' . "\n";
            $message .= '🦺 شما یک جلیقه دارید .' . "\n";
            if (in_array($server->league_id, MOSTAGHEL_TEAM)) {
                $message .= "\n" . $server->showTeam($user->getUserId(), 3);
                $message .= '💬 چت : فقط با تیم' . "\n";
            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        } else {

            $message .= '🗞 ماموریت : هدف زنده ماندن تا آخر بازی‌ست !' . "\n";
            if (in_array($server->league_id, MOSTAGHEL_TEAM)) {
                $message .= "\n" . $server->showTeam($user->getUserId(), 3);
                $message .= '💬 چت : فقط با تیم' . "\n";
            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($server->league_id == LEAGUE_MOSTAGHEL && $role->id == $user_role->id) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 3) {

                    $keyboard[] = [

                        $telegram->buildInlineKeyboardButton('☠️' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-kill-' . $server->getId() . '-' . $item->getUserId())

                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } elseif ($user->check($bazpors_select) && !$user->is($dozd_select) && $bazmandeh_shield < 2) {

            $keyboard[][] = $telegram->buildInlineKeyboardButton('🦺 تن کردن', '', $day . '/server-' . $server->league_id . '-bazmandeh-' . $server->getId() . '-' . $user->getUserId());

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_Gorg:

        $heart = (int) $selector->getInt()->select(ROLE_Gorg, 'heart');

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            $message .= '💬 چت : غیرفعال ' . "\n";

        } else {

            if ($day == 1) {

                $message .= '🔖ماموریت : امشب نمیتوانید بخروشید.' . "\n";

            } else {

                $message .= '🔖ماموریت : درصورتی که قصد حمله دارید ، هدف را انتخاب کرده و بخروشید .' . "\n";

            }

            if (2 - $heart != 0) {


                $message .= str_repeat('❤️  ', 2 - $heart);
                $message .= str_repeat('🤍 ', $heart);
                $message .= ' شما ' . $number_to_word->NumbersToWord(2 - $heart) . ' جان دارید .' . "\n";


            }

            if (in_array($server->league_id, MOSTAGHEL_TEAM)) {
                $message .= "\n" . $server->showTeam($user->getUserId(), 3);
                $message .= '💬 چت : فقط با تیم' . "\n";
            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($user->check($bazpors_select) && !$user->is($dozd_select) && $day > 1) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 3) {

                    $keyboard[] = [
                        $telegram->buildInlineKeyboardButton('🐺 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-gorg-' . $server->getId() . '-' . $item->getUserId())
                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_Joker:

        $role = $server->get_priority(3);

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            $message .= '💬 چت : غیرفعال ' . "\n";

        } elseif ($server->league_id == LEAGUE_MOSTAGHEL && $role->id == $user_role->id) {

            $message .= '🗞 مأموریت : شما میتوانید یک نفر را برای حمله کردن به او انتخاب کنید .' . "\n";
            if (in_array($server->league_id, MOSTAGHEL_TEAM))
                $message .= "\n" . $server->showTeam($user->getUserId(), 3);
            $message .= '💬 چت : غیرفعال ' . "\n";

        } else {

            if (in_array($server->league_id, MOSTAGHEL_TEAM)) {
                $message .= "\n" . $server->showTeam($user->getUserId(), 3);
                $message .= '💬 چت : فقط با تیم' . "\n";
            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($server->league_id == LEAGUE_MOSTAGHEL && $role->id == $user_role->id) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 3) {

                    $keyboard[] = [

                        $telegram->buildInlineKeyboardButton('☠️' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-kill-' . $server->getId() . '-' . $item->getUserId())

                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_Jalad:

        $targets = $selector->getString()->select(ROLE_Jalad, 'targets', false);

        if (empty($targets)) {

            $filter_role = [
                $selector->getUser(ROLE_Mohaghegh)->getUserId(),
                $selector->getUser(ROLE_Karagah)->getUserId(),
                $selector->getUser(ROLE_Senator)->getUserId(),
                $selector->getUser(ROLE_EynakSaz)->getUserId(),
                $selector->getUser(ROLE_Godfather)->getUserId(),
                $selector->getUser(ROLE_Bazpors)->getUserId(),
            ];

            $random_role = $server->randomUser(array_merge([$user->getUserId()], $filter_role), [3, 4]);
            $random_role_2 = $server->randomUser(array_merge([
                $user->getUserId(),
                $random_role->getUserId()
            ], $filter_role), [3, 4]);

            add_server_meta(
                $server->getId(),
                'targets',
                json_encode([
                    $random_role->getUserId(),
                    $random_role_2->getUserId()
                ]),
                ROLE_Jalad
            );

        } else {

            $targets = json_decode($targets, true);
            $random_role = new User($targets[0], $server->getId());
            $random_role_2 = new User($targets[1], $server->getId());

        }

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= '🗞 مأموریت : اهداف شما: ' . ($random_role->dead() ? "<b><s>" . $random_role->get_name() . "</s></b>" : "<b>" . $random_role->get_name() . "</b>") . ' و ' . ($random_role_2->dead() ? "<b><s>" . $random_role_2->get_name() . "</s></b>" : "<b>" . $random_role_2->get_name() . "</b>") . ' شما باید ان ها را اعدام کنید.' . "\n";
            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } else {

            $message .= '🗞 مأموریت : اهداف شما: ' . ($random_role->dead() ? "<b><s>" . $random_role->get_name() . "</s></b>" : "<b>" . $random_role->get_name() . "</b>") . ' و ' . ($random_role_2->dead() ? "<b><s>" . $random_role_2->get_name() . "</s></b>" : "<b>" . $random_role_2->get_name() . "</b>") . ' شما باید ان ها را اعدام کنید.' . "\n";
            if (in_array($server->league_id, MOSTAGHEL_TEAM)) {
                $message .= "\n" . $server->showTeam($user->getUserId(), 3);
                $message .= '💬 چت : فقط با تیم' . "\n";
            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($day == 2) {
            $user->setKeyboard(
                $telegram->buildInlineKeyBoard([
                    [
                        $telegram->buildInlineKeyboardButton('🔄 تعویض هدف', '', $day . '/server-' . $server->league_id . '-' . ROLE_Jalad . '-' . $server->getId() . '-' . $user->getUserId())
                    ]
                ])
            );
        }

        $user->SendMessageHtml();

        break;
    case ROLE_Ankabot:

        $selector->delete(ROLE_Ankabot);
        $selector->delete(ROLE_Ankabot, 'select-2');

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            $message .= '💬 چت : غیرفعال ' . "\n";

        } else {

            $message .= '🗞 مأموریت : دو نفر را انتخاب کنید تا در صورت اعدام شدن یکی از انها، دیگری نیز همراه او بمیرد.' . "\n";
            if (in_array($server->league_id, MOSTAGHEL_TEAM)) {
                $message .= "\n" . $server->showTeam($user->getUserId(), 3);
                $message .= '💬 چت : فقط با تیم' . "\n";
            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($user->check($bazpors_select) && !$user->is($dozd_select)) {

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 3) {

                    $keyboard[] = [

                        $telegram->buildInlineKeyboardButton('🕸 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_Ankabot . '-' . $server->getId() . '-' . $item->getUserId())

                    ];

                }

            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    case ROLE_Hazard:

        $power = $selector->getInt()->select(ROLE_Hazard, 'warning', false);
        $select_hazard = $selector->select(ROLE_Hazard);
        if ($select_hazard->getUserId() > 0) {

            $selector->set(++$power, ROLE_Hazard, 'warning');
            $selector->delete(ROLE_Hazard);

        }

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            $message .= '💬 چت : غیرفعال ' . "\n";

        } else {

            if ($power < 4) {

                $message .= '🎲 تعداد شانس قمار باقیمانده: ' . (4 - $power) . "\n";
                if ($selector->getInt()->select(ROLE_Hazard, 'power') == 1) {
                    $message .= '🔫 تعداد شات : 1' . "\n";
                }

                $heart = $selector->getInt()->select(ROLE_Hazard, 'heart', false);
                if ($heart > 0) {
                    $message .= '🛡 تعداد زره : ' . ($heart) . "\n";
                }

                $message .= '🗞 مأموریت : هدف و نوع قمار را انتخاب کنید .' . "\n";

            }
            if (in_array($server->league_id, MOSTAGHEL_TEAM)) {
                $message .= "\n" . $server->showTeam($user->getUserId(), 3);
                $message .= '💬 چت : فقط با تیم' . "\n";
            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($user->check($bazpors_select) && !$user->is($dozd_select) && $power < 4) {

            $user->setKeyboard(
                $telegram->buildInlineKeyBoard([
                    [
                        $telegram->buildInlineKeyboardButton('قمار برای دفاعیه', '', $day . '/server-' . $server->league_id . '-' . ROLE_Hazard . '-' . $server->getId() . '-1')
                    ],
                    [
                        $telegram->buildInlineKeyboardButton('قمار برای اعدام', '', $day . '/server-' . $server->league_id . '-' . ROLE_Hazard . '-' . $server->getId() . '-2')
                    ],
                ])
            );

        }

        $user->SendMessageHtml();

        break;
    case ROLE_Neron:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            $message .= '💬 چت : غیرفعال ' . "\n";

        } else {

            $message .= '🗞 مأموریت : شما میتوانید یک نفر را برای نفت ریختن بر روی او انتخاب کنید .' . "\n";
            if (in_array($server->league_id, MOSTAGHEL_TEAM)) {
                $message .= "\n" . $server->showTeam($user->getUserId(), 3);
                $message .= '💬 چت : فقط با تیم' . "\n";
            } else {
                $message .= '💬 چت : غیرفعال ' . "\n";
            }

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($user->check($bazpors_select) && !$user->is($dozd_select)) {

            $power = unserialize($selector->getString()->select(ROLE_Neron, 'power', false));

            foreach ($users_server as $item) {

                if ($item->check($user) && $item->get_role()->group_id != 3 && !in_array($item->getUserId(), $power)) {

                    $keyboard[] = [

                        $telegram->buildInlineKeyboardButton('🛢️' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_Neron . '-' . $server->getId() . '-' . $item->getUserId())

                    ];

                }

            }

            if (count($power) > 0 && $day > 1) {
                $keyboard[][] = $telegram->buildInlineKeyboardButton('🔥فندک زدن', '', $day . '/server-' . $server->league_id . '-' . ROLE_Neron . '-' . $server->getId() . '-123');
            }

            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

        } else
            $user->SendMessageHtml();

        break;
    // ........... GROUP 4 ...........
    case ROLE_Sagher:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            $message .= '💬 چت : غیرفعال ' . "\n";

        } else {

            $message .= '🗞 مأموریت : اگر امشب قصد استفاده از معجون دارید نوع آن را انتخاب کنید.' . "\n";
            $message .= '💬 چت : غیرفعال ' . "\n";

        }

        if ($day == 1) {

            $power = [

                'magic-1' => true,
                'magic-2' => true,
                'magic-3' => true,
                'magic-4' => true,
                'magic-5' => true,
                'magic-6' => true,
                'magic-7' => true,
                'magic-8' => true,
                'magic-9' => true,

            ];
            add_server_meta($server->getId(), 'power', serialize($power), $user_role->id);

        } else {

            $power = unserialize($selector->getString()->select($user_role->id, 'power'));

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        if ($user->check($bazpors_select) && !$user->is($dozd_select)) {

            if ($power['magic-1']) {
                $keyboard[0][] = $telegram->buildInlineKeyboardButton('🧪 مرگ', '', $day . '/server-' . $server->league_id . '-' . $user_role->id . '-' . $server->getId() . '-1');
            }
            if ($power['magic-2']) {
                $keyboard[0][] = $telegram->buildInlineKeyboardButton('🧪 جنون‌آور', '', $day . '/server-' . $server->league_id . '-' . $user_role->id . '-' . $server->getId() . '-2');
            }
            if ($power['magic-3']) {
                $keyboard[0][] = $telegram->buildInlineKeyboardButton('🧪 بیماری', '', $day . '/server-' . $server->league_id . '-' . $user_role->id . '-' . $server->getId() . '-3');
            }
            if ($power['magic-4']) {
                $keyboard[(count($keyboard[0]) == 0 ? 0 : 1)][] = $telegram->buildInlineKeyboardButton('🧪 شهرکُش', '', $day . '/server-' . $server->league_id . '-' . $user_role->id . '-' . $server->getId() . '-4');
            }
            if ($power['magic-5']) {
                $keyboard[(count($keyboard[0]) == 0 ? 0 : 1)][] = $telegram->buildInlineKeyboardButton('🧪 مافیاکُش', '', $day . '/server-' . $server->league_id . '-' . $user_role->id . '-' . $server->getId() . '-5');
            }
            if ($power['magic-6']) {
                $keyboard[(count($keyboard[1]) == 0 ? (count($keyboard[0]) == 0 ? 0 : 1) : 2)][] = $telegram->buildInlineKeyboardButton('🧪 نامیرایی', '', $day . '/server-' . $server->league_id . '-' . $user_role->id . '-' . $server->getId() . '-6');
            }
            if ($power['magic-7']) {
                $keyboard[(count($keyboard[1]) == 0 ? (count($keyboard[0]) == 0 ? 0 : 1) : 2)][] = $telegram->buildInlineKeyboardButton('🧪 افشاگر', '', $day . '/server-' . $server->league_id . '-' . $user_role->id . '-' . $server->getId() . '-7');
            }
            /*if ( $power[ 'magic-8' ] )
            {
                $keyboard[ ( count( $keyboard[ 1 ] ) == 0 ? ( count( $keyboard[ 0 ] ) == 0 ? 0 : 1 ) : 2 ) ][] = $telegram->buildInlineKeyboardButton( '🧪 بیماری', '', $day . '/server-' . $server->league_id . '-' . $user_role->id . '-' . $server->getId() . '-8' );
            }*/
            if ($power['magic-9']) {
                $keyboard[(count($keyboard[2]) == 0 ? (count($keyboard[1]) == 0 ? (count($keyboard[0]) == 0 ? 0 : 1) : 2) : 3)][] = $telegram->buildInlineKeyboardButton('🧪شگفتی', '', $day . '/server-' . $server->league_id . '-' . $user_role->id . '-' . $server->getId() . '-9');
            }


            $user->setKeyboard($telegram->buildInlineKeyBoard($keyboard));

        }

        $user->SendMessageHtml();

        break;
    case ROLE_Gambeler:

        if ($day == 1) {
            add_server_meta($server->getId(), 'power', 7, ROLE_Gambeler);
        }

        $select_gambeler = $selector->select(ROLE_Gambeler);

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            $message .= '💬 چت : غیرفعال ' . "\n";

        } else {

            $power = $selector->getInt()->select(ROLE_Gambeler, 'power');
            $message .= '🪙 تعداد کوین باقیمانده : ' . $power . "\n";
            if ($select_gambeler->getUserId() > 0) {
                $message .= '🗞 مأموریت : انتخاب کنید سنگ ✊ یا کاغذ ✋ یا قیچی ✌️' . "\n";
            }
            $message .= '💬 چت : غیرفعال ' . "\n";

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";


        if ($user->check($bazpors_select) && !$user->is($dozd_select) && $select_gambeler->getUserId() > 0) {

            $user->setKeyboard(
                $telegram->buildInlineKeyBoard([
                    [
                        $telegram->buildInlineKeyboardButton('قیچی ✌️', '', $day . '/server-' . $server->league_id . '-' . ROLE_Gambeler . '-' . $server->getId() . '-1'),
                        $telegram->buildInlineKeyboardButton('کاغذ ✋', '', $day . '/server-' . $server->league_id . '-' . ROLE_Gambeler . '-' . $server->getId() . '-2'),
                        $telegram->buildInlineKeyboardButton('سنگ ✊', '', $day . '/server-' . $server->league_id . '-' . ROLE_Gambeler . '-' . $server->getId() . '-3'),
                    ]
                ])
            );

            $temp = '🤹🏽‍♂ سلام ' . "<b>{$select_gambeler->get_name()}</b>" . "\n";
            $temp .= 'گمبلر شما را به عنوان حریف بازی خود انتخاب کرده است .' . "\n";
            $temp .= 'تنها در صورت برد ، زنده خواهید ماند .' . "\n \n \n";
            $temp .= 'انتخاب کنید 👇🏻';


            $select_gambeler->setKeyboard(
                $telegram->buildInlineKeyBoard([
                    [
                        $telegram->buildInlineKeyboardButton('قیچی ✌️', '', $day . '/server-' . $server->league_id . '-' . ROLE_Gambeler . '-' . $server->getId() . '-1'),
                        $telegram->buildInlineKeyboardButton('کاغذ ✋', '', $day . '/server-' . $server->league_id . '-' . ROLE_Gambeler . '-' . $server->getId() . '-2'),
                        $telegram->buildInlineKeyboardButton('سنگ ✊', '', $day . '/server-' . $server->league_id . '-' . ROLE_Gambeler . '-' . $server->getId() . '-3'),
                    ]
                ])
            )->SendMessageHtml($temp);

            /*$select_gambeler->setKeyboard(
                $telegram->buildInlineKeyBoard( [
                    [
                        $telegram->buildInlineKeyboardButton( 'قیچی ✌️', '', $day . '/server-' . $server->league_id . '-' . ROLE_Gambeler . '-' . $server->getId() . '-1' ),
                        $telegram->buildInlineKeyboardButton( 'کاغذ ✋', '', $day . '/server-' . $server->league_id . '-' . ROLE_Gambeler . '-' . $server->getId() . '-2' ),
                        $telegram->buildInlineKeyboardButton( 'سنگ ✊', '', $day . '/server-' . $server->league_id . '-' . ROLE_Gambeler . '-' . $server->getId() . '-3' ),
                    ]
                ] )
            )->SendMessageHtml( $temp );*/

        }

        $user->SendMessageHtml();

        break;
    // ........... GROUP 1 3 ...........
    case ROLE_Shahrvand:
    case ROLE_Bakreh:
    case ROLE_Fadaii:
    case ROLE_Ghazi:
    case ROLE_Naghel:
    case ROLE_Big_Khab:
    case ROLE_Nonva:
    case ROLE_Shahrdar:

        if ($user->dead()) {

            $message .= '💬 چت : فقط با کشته شده ها' . "\n";

        } elseif ($user->is($bazpors_select)) {

            $message .= 'شما امشب زندانی هستید.' . "\n";
            $message .= '💬 چت : فقط با بازپرس' . "\n";

        } elseif ($user->is($dozd_select)) {

            $message .= '🚷 قابلیت شما توسط یک فرد ناشناس دزدیده شده است .' . "\n";
            $message .= 'امشب فعالیتی ندارید.' . "\n";
            $message .= '💬 چت : غیرفعال ' . "\n";

        } else {

            $message .= '💬 چت : غیرفعال ' . "\n";

        }

        $message .= '🌞 روز 👈🏻 40 ثانیه' . "\n";

        $user->SendMessageHtml();

        break;

}
$eye_flag = $server->setUserId(ROLE_Ehdagar)->getMetaUser('eye_shown');
if ($recieved_part === 'eye' && isset($eye_flag) && $eye_flag != 'true') {
    $message = "شما چشم اهدایی دریافت کرده‌اید. لطفاً یک نفر را برای مشاهده نقشش در طول شب انتخاب کنید.";
    $receiver_user = new User($receiver_id, $server->getId());

    // Check if a user was selected the previous night
    if (isset($used_parts[$previous_day]['selected_user'])) {
        $selected_user_id = $used_parts[$previous_day]['selected_user'];
    }

    // Preparing the list of users to select from
    $users_server = $server->users();
    $keyboard = [];
    foreach ($users_server as $user) {
        if (!$user->dead() && $user->is_user_in_game()) {
            $isSelected = ($user->getUserId() == $selected_user_id) ? '✔️' : '';
            $keyboard[] = [
                $telegram->buildInlineKeyboardButton('👁️ ' . $user->get_name() . " $isSelected", '', $day . '/server-' . $server->league_id . '-eye_select-' . $server->getId() . '-' . $user->getUserId())
            ];
        }
    }
    $eye_flag = $server->setUserId(ROLE_Ehdagar)->updateMetaUser('eye_shown', 'true');
    // Send the message with the list
    SendMessage($receiver_user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');
}