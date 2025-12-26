<?php
/** @noinspection ALL */
include CLASS_DIR . "/Server.php";
include CLASS_DIR . "/Role.php";
include CLASS_DIR . "/User.php";

//error_log("soucreeeee ce");
use library\Role;
use library\Server;
use library\User;

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

$token_bot = require(CONFIG_DIR . '/bots.php');
try {

    foreach ($servers as $server) {

//        var_dump($servers);

        $TOKEN_API = $token_bot[$server->bot];
        $BOT_ID = $server->bot;

        $server = new Server($server->id);

        $users_server = $server->users();

        $selector = new Role($server);
        $day = $server->day();
        $used_parts = [];
        $previous_day = $day - 1;
        $receiver_id = null;
        $recieved_part = null;
        $check_for_ehdagar_heal = false;
        if ($server->role_exists(ROLE_Ehdagar)) {
            $serialized_used_parts = $server->setUserId(ROLE_Ehdagar)->getMetaUser('used_parts');
            $used_parts = unserialize($serialized_used_parts);
            // Calculate the previous day for 
            // Check if there was a transplant receiver selected the previous day
            if (isset($used_parts[$previous_day]) && isset($used_parts[$previous_day]['receiver'])) {
                $receiver_id = $used_parts[$previous_day]['receiver'];
                $recieved_part = $used_parts[$previous_day]['part'];
                if (isset($recieved_part) && !$used_parts[$previous_day]['notified'] && isset($reciever_id)) {
                    $available_parts = ['heart' => '🫀 قلب', 'eye' => '👁 چشم', 'hand' => '✍🏻 دست', 'lung' => '🫁 ریه'];
                    $recieved_part_text = $available_parts[$recieved_part];
                    $footer[$reciever_id][] = 'اهداگر به شما ' . '<b>' . $recieved_part_text . '</b>' . ' را اهدا کرد .';
                    // Update the notified flag
                    $used_parts[$previous_day]['notified'] = true;
                    $serialized_used_parts = serialize($used_parts);
                    $server->setUserId(ROLE_Ehdagar)->updateMetaUser('used_parts', $serialized_used_parts);
                    if ($recieved_part == 'heart') {
                        $check_for_ehdagar_heal = true;
                    }
                }
            }
        }
        $server->updateMeta('is', 'on');
		
        switch ($server->getStatus()) {

            case 'night':
				NIGHT_MODE:
				if ($server->role_exists(ROLE_Shahzadeh)) {
					$shahzadeh = $selector->getUser(ROLE_Shahzadeh);
					if($shahzadeh->dead() && $server->setUserId(ROLE_Shahzadeh)->getMetaUser('isoldkilled') == 0)
					{
						$server->setUserId(ROLE_Shahzadeh)->updateMetaUser('isoldkilled', 1);
					
						goto LIGHT_MODE;
					}
				}
				
                
                if ($server->getStatus() == 'court-2')
                    continue 2;

                $bazpors_select = $selector->user()->select(ROLE_Bazpors);
                $dozd_select = $selector->user()->select(ROLE_Dozd);
                if ($selector->getUser(ROLE_Dozd)->dead() || $bazpors_select->is($selector->getUser(ROLE_Dozd)) || $dozd_select->getRoleId() == ROLE_Bazpors || $dozd_select->getRoleId() == ROLE_Bazpors) {
                    $dozd_select = new User(0);
                }

                add_filter('send_massage_text', 'number_to_english', 11);

                if ($server->role_exists(ROLE_Framason)) {

                    $select_framason = unserialize($selector->getString()->select(ROLE_Framason, 'power', false));
                    if ($select_framason != false && count($select_framason) > 0) {
                        $framason_team = '';
                        foreach ($select_framason as $item) {
                            $temp = new User(string_decodeOld($item), $server->getId());
                            if ($temp->dead()) {
                                $framason_team .= "<b><s>" . $temp->get_name() . "</s></b>" . '  : [ ماسون ]' . "\n";
                            } else {
                                $framason_team .= "<b>" . $temp->get_name() . "</b>" . '  : [ ماسون ]' . "\n";
                            }
                        }
                        $framason_team .= "\n \n";
                    }

                }

                foreach ($users_server as $user) {

                    if (!$user->is_user_in_game())
                        continue;

                    $keyboard = [];

                    $user_role = $user->get_role();

                    $message = ($day % 2 == 0 ? '🌕' : '🌙') . ' #شب ' . $number_to_word->numberToWords($day) . ($day % 2 == 0 ? ' ، امشب ماه کامل است.' : '') . "\n";

                    $message .= '🎭 نقش شما: ' . $user_role->icon . "\n";
                    $user->genderChange($message)->setStatus('night');

                    switch ($user_role->id) {
                        case ROLE_Dozd:

                            if ($user->dead()) {

                                $message .= '💬 چت : فقط با کشته شده ها' . "\n";

                            } elseif ($user->is($bazpors_select)) {

                                $message .= '🗞 مأموریت: شما امشب زندانی هستید.' . "\n";
                                $message .= '💬 چت : فقط با بازپرس' . "\n";

                            } elseif ($dozd_select->getUserId() > 0) {

                                $message .= '🔖 مأموریت : از قدرت نقشی که دزده اید استفاده کنید.' . "\n";
                                $message .= 'نقش دزدیده شده: ' . $dozd_select->get_role()->icon . "\n \n";

                                $message .= $server->showTeam($user->getUserId());

                                $message .= '💬 چت : فقط با تیم' . "\n";

                            } else {

                                $message .= '🔖 مأموریت : به شهر رفته تا از آنها دزدی کنید.' . "\n \n";

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

                            } elseif ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId()) && $dozd_select->getUserId() > 0 && !$dozd_select->dead()) {

                                $selector->set($dozd_select->getUserId(), ROLE_Dozd, 'select-2');

                                $role = $dozd_select->get_role();
                                $role_id = $role->id;

                                $emojis = Emoji\detect_emoji($role->icon);

                                switch ($role_id) {

                                    /*case ROLE_Pezeshk:

                                        $power = $selector->getString()->select(ROLE_Dozd, $role_id);

                                        break;*/

                                    case ROLE_TofangDar:

                                        $power = $selector->getString()->select($role_id, 'count');
                                        $i = 0;

                                        break;

                                    case ROLE_Zambi:

                                        $power = $server->getMeta('zambi') != 'use';

                                        break;

                                    case ROLE_Bakreh:
                                    case ROLE_Shahrvand:
                                    case ROLE_Sahere:
                                    case ROLE_TelefonChi:
                                    case ROLE_Police:
                                    case ROLE_Shield:
                                    case ROLE_Big_Khab:
                                    case ROLE_Naghel:
                                    case ROLE_Joker:
                                    case ROLE_Bazmandeh:
                                    case ROLE_Fadaii:
                                    case ROLE_Ghazi:
                                    case ROLE_Jalad:
                                    case ROLE_Didban:
                                    case ROLE_Mohaghegh:
                                    case ROLE_Karagah:
                                    case ROLE_Senator:
                                    case ROLE_MosaferZaman:
                                    case ROLE_Fereshteh:
                                    case ROLE_KhabarNegar:
                                    case ROLE_Shahrdar:
                                        $user->SendMessageHtml($message);
                                        continue 3;
                                }

                                foreach ($users_server as $item) {

                                    switch ($role_id) {

                                        case ROLE_Pezeshk:

                                            if (!$item->dead()) {
                                                $keyboard[] = [
                                                    $telegram->buildInlineKeyboardButton((isset($emojis[0]) ? $emojis[0]['emoji'] : '👈') . ' ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . $role_id . '-' . $server->getId() . '-' . $item->getUserId())
                                                ];
                                            }

                                            break;
                                        case ROLE_Ehdagar:

                                            if (!$item->dead()) {
                                                $keyboard[] = [
                                                    $telegram->buildInlineKeyboardButton((isset($emojis[0]) ? $emojis[0]['emoji'] : '👈') . ' ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . $role_id . '-' . $server->getId() . '-' . $item->getUserId())
                                                ];
                                            }

                                            break;
                                        case ROLE_Fereshteh:

                                            if ($item->dead() && $item->get_role()->group_id == 2) {
                                                $keyboard[] = [
                                                    $telegram->buildInlineKeyboardButton((isset($emojis[0]) ? $emojis[0]['emoji'] : '👈') . ' ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . $role_id . '-' . $server->getId() . '-' . $item->getUserId())
                                                ];
                                            }

                                            break;

                                        case ROLE_TofangDar:

                                            if ($item->check($user)) {

                                                $keyboard[$i][] = $telegram->buildInlineKeyboardButton('⚪️ ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-tofang_dar_1-' . $server->getId() . '-' . $item->getUserId());

                                                if ($power < 3) {

                                                    $keyboard[$i][] = $telegram->buildInlineKeyboardButton('🔴 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-tofang_dar_2-' . $server->getId() . '-' . $item->getUserId());

                                                }

                                                $i++;

                                            }

                                            break;

                                        case ROLE_Keshish:

                                            if (!is_server_meta($server->getId(), 'dalghak', ROLE_Dalghak)) {

                                                $keyboard[][] = $telegram->buildInlineKeyboardButton('🤡 خندیدن', '', $day . '/server-' . $server->league_id . '-dalghak-' . $server->getId() . '-' . $user->getUserId());
                                                break 2;

                                            }

                                            break;

                                        case ROLE_Zambi:

                                            if (!$status_zambi && !$item->is($user)) {

                                                $keyboard[] = [
                                                    $telegram->buildInlineKeyboardButton('🧟‍♂️ ' . $item->get_name() . ($item->dead() ? '☠️' : ''), '', $day . '/server-' . $server->league_id . '-' . $role_id . '-' . $server->getId() . '-' . $item->getUserId())
                                                ];

                                            }

                                            break;

                                        default:

                                            if ($item->check($user)) {

                                                $keyboard[] = [
                                                    $telegram->buildInlineKeyboardButton((isset($emojis[0]) ? $emojis[0]['emoji'] : '👈') . ' ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . $role_id . '-' . $server->getId() . '-' . $item->getUserId())
                                                ];

                                            }

                                            break;

                                    }

                                }

                                if (count($keyboard) > 0)
                                    SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');
                                else
                                    SendMessage($user->getUserId(), $message, null, null, 'html');

                            } elseif ($user->check($bazpors_select) && apply_filters('filter_mafia', $user->getUserId())) {

                                $selector->delete(ROLE_Dozd);
                                $last_select = get_server_meta($server->getId(), 'last-select', ROLE_Dozd);

                                foreach ($users_server as $item) {

                                    if ($item->check($user) && $item->get_role()->group_id != 2 && !$item->is($last_select)) {

                                        $keyboard[] = [
                                            $telegram->buildInlineKeyboardButton('🚷 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_Dozd . '-' . $server->getId() . '-' . $item->getUserId())
                                        ];

                                    }

                                }

                                SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

                            } else
                                $user->SendMessageHtml();

                            break;
                        default:

                            require BASE_DIR . '/temp/night.php';

                            break;

                    }

                }

                if (in_array($server->league_id, [4, 5])) {
                    $server->charge(70);
                } else {
                    $server->charge(40);
                }
				
				
					
				
				
				$server->setStatus('light')->deleteMeta('hack')
						->deleteMeta('sleep')->deleteMeta('bakreh')->magicVotes()->resetMessage();


                if ($bazpors_select->getUserId() > 0) {
                    $selector->set($bazpors_select->getUserId(), ROLE_Bazpors);
                }

                remove_filter('send_massage_text', 'number_to_english', 11);

                break;
            case 'light':
				LIGHT_MODE:
				
				if ($server->role_exists(ROLE_Shahzadeh)) {
					$shahzadeh = $selector->getUser(ROLE_Shahzadeh);
					if($shahzadeh->dead() && $server->setUserId(ROLE_Shahzadeh)->getMetaUser('isoldkilled') == 0)
					{
						$server->setUserId(ROLE_Shahzadeh)->updateMetaUser('isoldkilled', 1);
					
						goto NIGHT_MODE;
					}
				}
				
                require BASE_DIR . '/temp/light.php';
                
                
                $server->magicsOff();
                // ╔══════ Check Status Game ═══════════════════════════════════════════════════════════════════╗
                if (check_status_game($server->server(), ($names . $report)))
                    continue 2;
                // ╚══════ End Check Status Game ═══════════════════════════════════════════════════════════════╝
                $select_nonva = $server->getMeta('Nonva');
                if ($select_nonva > 0 && $select_nonva + 4 <= $day) {

                    if ($server->getCountMostaghel() > 0) {
                        if (result_server($server->server(), 3))
                            continue 2;
                        ;
                    } elseif ($server->getCountAmazing() > 0) {
                        if (result_server($server->server(), 4))
                            continue 2;
                        ;
                    } else {
                        if (result_server($server->server(), 2))
                            continue 2;
                    }
                }
                // ╔══════ Report Dead Body ═════════════════════════════════════════════════════════════════════╗
                $dead_body_message = '';
                $users_online = 0;
                $id = 1;
                $users_server_rand = $users_server;
                shuffle($users_server_rand);

                $Zodiac = $selector->getUser(ROLE_Killer);
                foreach ($users_server_rand as $user) {

                    $prefix = '';

                    if (is_server_meta($server->getId(), 'friend', $user->getUserId())) {

                        $prefix = get_emoji_for_friendly(get_server_meta($server->getId(), 'friend', $user->getUserId()));

                    }

                    $role = $user->get_role();
                    if ($user->dead()) {
                        // killer
                        if ($user->killed()) {

                            if ($role->id == ROLE_Shayad && $select_shayad->getUserId() > 0)
                                $footer[$Zodiac->getUserId()][] = "نقش " . $user->get_name() . " : <s>" . (emoji_group($select_shayad->get_role()->group_id)) . " " . $select_shayad->get_role()->icon . "</s>";
                            elseif ($role->group_id == 2)
                                $footer[$Zodiac->getUserId()][] = "نقش " . $user->get_name() . '🔴' . " : <s>" . ' عضو مافیا' . "</s>";
                            else
                                $footer[$Zodiac->getUserId()][] = "نقش " . $user->get_name() . " : <s>" . (emoji_group($role->group_id)) . " " . $role->icon . "</s>";

                            $dead_body_message .= "<s>" . "<b>" . tr_num($id, 'fa') . ".</b>" . $prefix . ' ' . $user->get_name() . '⚪️' . " " . ' نامعلوم' . "</s>" . "\n";
                        } elseif ($role->id == ROLE_Shayad && $select_shayad->getUserId() > 0) {

                            $dead_body_message .= "<s>" . "<b>" . tr_num($id, 'fa') . ".</b> " . $prefix . ' ' . $user->get_name() . " " . (emoji_group($select_shayad->get_role()->group_id)) . " " . $select_shayad->get_role()->icon . "</s>" . "\n";

                        } elseif ($role->group_id == 2) {

                            $dead_body_message .= "<s>" . "<b>" . tr_num($id, 'fa') . ".</b>" . $prefix . ' ' . $user->get_name() . '🔴' . " " . ' عضو مافیا' . "</s>" . "\n";

                        } else {

                            $dead_body_message .= "<s>" . "<b>" . tr_num($id, 'fa') . ".</b> " . $prefix . ' ' . $user->get_name() . " " . (emoji_group($role->group_id)) . " " . $role->icon . "</s>" . "\n";

                        }

                    } else {

                        $dead_body_message .= "<b>" . tr_num($id, 'fa') . ".</b>" . $prefix . ' ' . "<code>" . $user->get_name() . "</code>" . "\n";
                        $users_online++;

                    }

                    $user->genderChange($dead_body_message);

                    $id++;

                }
                // ╚══════ End Report Dead Body ═════════════════════════════════════════════════════════════════╝

                // $user_greenway = get_server_meta_user($server->getId(), 'card-gree_nway', $day);
                // $user_greenway = get_server_meta($server->getId(), 'card-gree_nway', $day);

                // ╔══════ Building Body Template Message ════════════════════════════════════════════════════════╗
                $message .= $names . "\n"; // Report Roles Who Select Who

                // Card 2 HERE
                $user_card_2 = get_server_meta_user($server->getId(), 'card-2', $day);


                if ($user_card_2) {
                    // $card_test_message = "server : {$server->getId()} \n";
                    // $card_test_message .= "day : {$day} \n";
                    // $card_test_message .= "user_card_2 : {$user_card_2} \n";

                    $user_leaked = new User((int) $user_card_2, $server->getId());

                    // $card_test_message .= "user_leaked : {$user_leaked->get_name()} \n";

                    if ($user_leaked->get_role()->group_id == 1) {
                        $message .= "🃏 کارت افشا : <b>{$user_leaked->get_name()}</b> 🟢 عضو شهروند است.\n";
                    } elseif ($user_leaked->get_role()->group_id == 2) {
                        $message .= "🃏 کارت افشا : <b>{$user_leaked->get_name()}</b> 🔴 عضو مافیا است.\n";
                    } else {
                        $message .= "🃏 کارت افشا : <b>{$user_leaked->get_name()}</b> 🟡 مستقل / شگفت انگیز است.\n";
                    }
                    $message .= "\n";

                    // SendMessage( 202910544, $card_test_message, null, null, 'html' );
                }




                $message .= $dead_body_message . "\n"; // List People (Name People With Role If his dead)
                // ╚══════ End Building Body Template Message ════════════════════════════════════════════════════╝

                // ╔══════ Footer ══════════════════════════════════════════════════════════════════════════════╗
                if ($select_keshish != 'on') {
                    $doctor = $selector->getUser(ROLE_Pezeshk);
                    $kar_agah = $selector->getUser(ROLE_Karagah);
                    $god_father = $selector->getUser(ROLE_Godfather);
                    $killer = $selector->getUser(ROLE_Killer);

                    $jalad_ex = $server->role_exists(ROLE_Jalad);
                    if ($jalad_ex) {
                        $jalad = $selector->getUser(ROLE_Jalad);
                        $jalad_targets = json_decode($selector->getString()->select(ROLE_Jalad, 'targets', false));
                        $select_jalad = $selector->select(ROLE_Jalad);
                        if ($select_jalad->getUserId() > 0) {

                            $jalad_target = new User($jalad_targets[0], $server->getId());

                            if ($jalad_target->dead())
                                $jalad_target = new User($jalad_targets[1], $server->getId());

                            $filter_role = [
                                $selector->getUser(ROLE_Mohaghegh)->getUserId(),
                                $selector->getUser(ROLE_Karagah)->getUserId(),
                                $selector->getUser(ROLE_Senator)->getUserId(),
                                $selector->getUser(ROLE_EynakSaz)->getUserId(),
                                $selector->getUser(ROLE_Godfather)->getUserId(),
                                $selector->getUser(ROLE_Bazpors)->getUserId(),
                            ];

                            $random_role = $server->randomUser(array_merge([$jalad_targets[0], $jalad_targets[1]], $filter_role), [3, 4]);
                            if ($jalad_target->is($jalad_targets[0])) {
                                update_server_meta(
                                    $server->getId(),
                                    'targets',
                                    json_encode([
                                        $random_role->getUserId(),
                                        $jalad_targets[1]
                                    ]),
                                    ROLE_Jalad
                                );
                            } else {
                                update_server_meta(
                                    $server->getId(),
                                    'targets',
                                    json_encode([
                                        $jalad_targets[0],
                                        $random_role->getUserId(),
                                    ]),
                                    ROLE_Jalad
                                );
                            }
                        }

                    }

                    foreach ($users_server as $user) {
                        // Shield (magic)
                        $__user_role = $user->get_role();
                        if (isset($shield) && count($shield) > 0) {
                            foreach ($shield as $item) {
                                if (in_array($__user_role->id, $item['role'])) {
                                    $footer[$user->getUserId()][] = 'حمله شما موفقیت آمیز نبود.';
                                } elseif ($select_bazmondeh->is($item['user_id']) && $user->is($item['user_id'])) {
                                    $footer[$user->getUserId()][] = 'جلیقه جان شما را از یک حمله نجات داد.';
                                } elseif ($user->is($item['user_id'])) {
                                    $footer[$user->getUserId()][] = '🛡شما از یک حمله نجات پیدا کردید.';
                                }
                            }
                        }

                        // Head Doctor
                        if ($heal_doctor === true && $doctor->is($user)) {

                            if ($select_doctor->is($user)) {

                                $footer[$user->getUserId()][] = 'شما جان خود را از یک حمله نجات دادید.';

                            } elseif (!empty($select_doctor->user()->name)) {

                                $temp = 'شما جان [[user]] را از یک حمله نجات دادید.';
                                $footer[$user->getUserId()][] = __replace__($temp, [
                                    '[[user]]' => "<u>" . $select_doctor->user()->name . "</u>"
                                ]);

                            }

                        }

                        if ($heal_doctor === true && $user->is($select_doctor) && !$select_doctor->is($doctor)) {

                            $footer[$user->getUserId()][] = 'پزشک شما را از یک حمله نجات داد.';

                        }



                        // Status Kar agah
                        if ($select_karagah instanceof User && $select_karagah->getUserId() > 0 && $kar_agah->is($user) && isset($message_kar_agah)) {
                            $footer[$user->getUserId()][] = __replace__($message_kar_agah, ['[[user]]' => "<u>" . $select_karagah->get_name() . "</u>"]);
                        }

                        // Status Attack
                        if ($heal_doctor === true && in_array($__user_role->id, $heal_doctor_user)) {
                            $footer[$user->getUserId()][] = 'حمله شما موفقیت آمیز نبود.';
                        }

                        // Status User (If he dead)
                        if (in_array($user->getUserId(), $dead_body)) {

                            switch ($__user_role->id) {

                                case ROLE_Nonva:

                                    $server->updateMeta('Nonva', $day);

                                    break;

                                case ROLE_Sagher:

                                    $sagher_regent = $selector->select(ROLE_Sagher, 'regent');
                                    if ($sagher_regent->getUserId() > 0 && !$sagher_regent->dead()) {

                                        $sagher_regent->changeRole(ROLE_Sagher);
                                        $selector->delete(ROLE_Sagher, 'regent'); // Edit Sam

                                    }

                                    break;

                            }

                            $footer[$user->getUserId()][] = 'شما کشته شدید.';

                            if ($jalad_ex && in_array($user->getUserId(), $jalad_targets) && !$jalad->dead()) {

                                $footer[$jalad->getUserId()][] = 'یکی از اهداف شما کشته شد. شما جوکر هستید.';
                                $jalad->changeRole(ROLE_Joker);

                            }

                        }

                        // -------------------------------------------------------------------------------------
//                        error_log("sourceeeee");
                        switch ($__user_role->id) {
                            case ROLE_MineLayer:
                                // Ensure functions are declared only once
                                if (!function_exists('checkTargetSelection')) {
                                    function checkTargetSelection($target) {
                                        global $selector;
                                        $select_1 = $selector->select(ROLE_MineLayer, 'select-2-0', $target->getUserId());
                                        $select_2 = $selector->select(ROLE_MineLayer, 'select-2-1', $target->getUserId());
                                        $select_3 = $selector->select(ROLE_MineLayer, 'select-2-2', $target->getUserId());
                                        return ($select_1 && $select_2 && $select_3);
                                    }
                                }

                                if (!function_exists('evaluateMineDefusal')) {
                                    function evaluateMineDefusal($target) {
                                        global $selector;
                                        $mine_location_obj = $selector->select(ROLE_MineLayer, 'select-3', $target->getUserId());
                                        $mine_location = $mine_location_obj ? $mine_location_obj->getUserId() : null;
                                        if (!$mine_location) {
                                            return false;
                                        }
                                        $select_1 = $selector->select(ROLE_MineLayer, 'select-2-0', $target->getUserId());
                                        $select_2 = $selector->select(ROLE_MineLayer, 'select-2-1', $target->getUserId());
                                        $select_3 = $selector->select(ROLE_MineLayer, 'select-2-2', $target->getUserId());
                                        $selected_houses = [
                                            $select_1 ? $select_1->getUserId() : 0,
                                            $select_2 ? $select_2->getUserId() : 0,
                                            $select_3 ? $select_3->getUserId() : 0,
                                        ];
                                        return in_array($mine_location, $selected_houses);
                                    }
                                }

                                // Get the target selected by the MineLayer
                                $mine_layer_target = $selector->user()->select(ROLE_MineLayer);
                                $mine_layer_player = $selector->getUser(ROLE_MineLayer);

//                                if ($mine_layer_target instanceof User && $mine_layer_target->getUserId() > 0) {
//                                    // Check if the target has completed their house selections
//                                    $has_completed_selection = checkTargetSelection($mine_layer_target);
//                                    if ($has_completed_selection) {
//                                        // Determine if the target successfully defused the mine
//                                        $is_defused = evaluateMineDefusal($mine_layer_target);
//
//                                        if ($is_defused) {
//                                            // Target successfully defused the mine
//                                            $message_mine_layer_target = '[ [ mine_layer_target ] ] موفق به خنثی کردن مین شد.' . "\n \n";
//                                            $message_mine_layer_player = '[ [ mine_layer_target ] ] موفق به خنثی کردن مین شما شد.' . "\n \n";
//
//                                            // Use __replace__ function for message formatting
//                                            $message_mine_layer_target = __replace__($message_mine_layer_target, [
//                                                '[ [ mine_layer_target ] ]' => "<u>" . $mine_layer_target->get_name() . "</u>"
//                                            ]);
//
//                                            $message_mine_layer_player = __replace__($message_mine_layer_player, [
//                                                '[ [ mine_layer_target ] ]' => "<u>" . $mine_layer_target->get_name() . "</u>"
//                                            ]);
//
//                                            // Add messages to footer for specific users
//                                            $footer[$mine_layer_target->getUserId()][] = $message_mine_layer_target;
//                                            $footer[$mine_layer_player->getUserId()][] = $message_mine_layer_player;
//
//                                        } else {
//                                            // Target failed to defuse the mine and is killed
//                                            $mine_layer_target->kill();
//
//                                            $dead_body[] = $mine_layer_target->getUserId();
//
//                                            $message_mine_layer_target = 'شما توسط مین‌گذار کشته شدید.' . "\n \n";
//                                            $message_mine_layer_player = '[ [ mine_layer_target ] ] توسط مین شما کشته شد.' . "\n \n";
//
//                                            $message_mine_layer_player = __replace__($message_mine_layer_player, [
//                                                '[ [ mine_layer_target ] ]' => "<u>" . $mine_layer_target->get_name() . "</u>"
//                                            ]);
//
//                                            // Add messages to footer for specific users
//                                            $footer[$mine_layer_target->getUserId()][] = $message_mine_layer_target;
//                                            $footer[$mine_layer_player->getUserId()][] = $message_mine_layer_player;
//
//                                            // Handle any role-specific logic if needed
//                                            switch ($mine_layer_target->get_role()->id) {
//                                                case ROLE_Bazpors:
//                                                    $selector->delete(ROLE_Bazpors);
//                                                    break;
//                                                // Add other roles if necessary
//                                            }
//
//                                            // Add the target to the dead body list
////                                            $dead_body[] = $mine_layer_target->getUserId();
//                                        }
//
//                                        // Clean up any selections or data related to the MineLayer action
//                                        $selector->delete(ROLE_MineLayer);
//                                        // Delete target's selections
//                                        $selector->delete(ROLE_MineLayer, 'select-2-0');
//                                        $selector->delete(ROLE_MineLayer, 'select-2-1');
//                                        $selector->delete(ROLE_MineLayer, 'select-2-2');
//                                        $selector->delete(ROLE_MineLayer, 'select-3', $mine_layer_target->getUserId());
//
//                                    } else {
//                                        // Target has not completed their selections
//                                        // You may want to send a reminder or handle accordingly
//                                    }
//                                }
                                break;
                            case ROLE_Nato:

                                // Status Nato
                                if ($select_nato instanceof User && $select_nato->getUserId() > 0) {
                                    $temp = '🔍 [[user]] نقشش ( [[role]] ) است .';
                                    $footer[$user->getUserId()][] = __replace__($temp, [
                                        '[[user]]' => $select_nato->get_name(),
                                        '[[role]]' => $select_nato->get_role()->icon
                                    ]);
                                }

                                break;

                            case ROLE_Didban:

                                // ║ Did ban
                                $select_did_ban = $selector->user()->select(ROLE_Didban);
                                // Status Did Ban
                                if ($select_did_ban instanceof User && $select_did_ban->getUserId() > 0) {

                                    $attacker = [];
                                    $temp = [];
                                    $attackers = $server->getListAttacker($select_did_ban->getUserId());

                                    $forbidden_list = [
                                        ROLE_Godfather,
                                        ROLE_Hacker,
                                        ROLE_HardFamia,
                                        ROLE_Didban,
                                        ROLE_Bazpors,
                                        ROLE_Naghel
                                    ];

                                    if (count($attackers) > 0) {

                                        foreach ($attackers as $item) {

                                            $user_role = $item->get_role();
                                            if (count($attackers) == 1) {

                                                if ($user_role->id == ROLE_Godfather) {

                                                    $users_group_2 = $server->roleByGroup(2);

                                                    foreach ($users_group_2 as $__user) {

                                                        if (!$__user->dead() && $__user->getRoleId() != ROLE_Godfather) {

                                                            $user_attacker = $__user;
                                                            break;

                                                        }

                                                    }

                                                    if ($user_attacker instanceof User) {

                                                        $attacker[] = $user_attacker->getUserId();

                                                    } else {

                                                        $attacker[] = $god_father->getUserId();

                                                    }

                                                } elseif (!in_array($user_role->id, $forbidden_list)) {

                                                    $attacker[] = $item->getUserId();

                                                }

                                            } elseif ($user_role->id == ROLE_Godfather) {

                                                $users_group_2 = $server->roleByGroup(2);

                                                foreach ($users_group_2 as $__user) {

                                                    if (!$__user->dead() && $__user->getRoleId() != ROLE_Godfather) {

                                                        $user_attacker = $__user;
                                                        break;

                                                    }

                                                }

                                                if ($user_attacker instanceof User) {

                                                    $attacker[] = $user_attacker->getUserId();

                                                } else {

                                                    $attacker[] = $god_father->getUserId();

                                                }

                                            } elseif (!in_array($user_role->id, $forbidden_list)) {

                                                $attacker[] = $item->getUserId();

                                            }

                                        }

                                    }
                                    if (count($attacker) == 1) {

                                        $user_name = name($attacker[0], $server->getId());

                                        if (!empty($user_name)) {

                                            if ($select_did_ban->is($attacker[0])) {

                                                $footer[$user->getUserId()][] = "<u>$user_name</u>" . ' دیشب از خودش دفاع کرد .';

                                            } else {

                                                $footer[$user->getUserId()][] = "<u>$user_name</u>" . ' به خانه ی هدف شما رفت.';

                                            }

                                        } else {

                                            $footer[$user->getUserId()][] = 'مشکلی رخ داد!';

                                        }

                                    } elseif (count($attacker) > 1) {

                                        $users = [];
                                        foreach ($attacker as $item) {

                                            if (!in_array($item, $users)) {

                                                $users[] = $item;
                                                $user_name = name($item, $server->getId());
                                                if ($select_did_ban->is($item) && !empty($user_name)) {

                                                    $footer[$user->getUserId()][] = "<u>$user_name</u>" . ' دیشب از خودش دفاع کرد .';

                                                } elseif (!empty($user_name)) {

                                                    $temp[] = $user_name;

                                                }

                                            }

                                        }

                                        if (count($temp) > 0) {

                                            $footer[$user->getUserId()][] = implode(' و ', $temp) . ' به خانه ی هدف شما رفتند.';

                                        } else {

                                            $footer[$user->getUserId()][] = 'مشکلی رخ داد 2!';

                                        }

                                    } elseif (count($attacker) == 0) {

                                        $footer[$user->getUserId()][] = 'دیشب هیچکس به خانه هدف شما نرفت.';

                                    }

                                }

                                break;

                            case ROLE_Mohaghegh:

                                // Status Mohaghegh
                                if ($select_mohaghegh instanceof User && $select_mohaghegh->getUserId() > 0) {

                                    $user_role = $select_mohaghegh->get_role();

                                    $list = $server->setUserId(ROLE_Mohaghegh)->getMetaUser('list');
                                    $roles = empty($list) ? [] : unserialize($list);

                                    $wheres = ' AND ';
                                    if (count($roles) > 0) {

                                        foreach ($roles as $item) {
                                            $wheres .= ' `role` . `id` != ' . $item . ' AND ';
                                        }

                                    }

                                    $where = '`role`.`group_id` != ' . $user_role->group_id . '  AND `role`.`id` != ' . $user_role->id . $wheres . ' `role` . `id` != ' . ROLE_Mohaghegh . ' and ';
                                    $random_role = get_random_role($server->getId(), $where);

                                    $roles = array_merge($roles, [$user_role->id, $random_role->id]);

                                    if ($select_tohmat_zan instanceof User && $select_tohmat_zan->getUserId() > 0 && $select_tohmat_zan->is($select_mohaghegh)) {
                                        $where = '`role` . `group_id` != ' . $user_role->group_id . ' and `role` . `id` != ' . $user_role->id . ' and `role` . `id` != ' . ROLE_Mohaghegh . ' and ';
                                        $user_role = get_random_role($server->getId(), $where);

                                    }

                                    $rand = (bool) rand(0, 1);
                                    $temp = '[ [ user ] ] میتواند( [ [ role_2 ] ] ) یا( [ [ role_1 ] ] ) باشد . ';
                                    $footer[$user->getUserId()][] = __replace__($temp, [
                                        '[ [ user ] ]' => "<u>" . $select_mohaghegh->get_name() . "</u>",
                                        '[ [ role_1 ] ]' => $rand ? $user_role->icon : $random_role->icon,
                                        '[ [ role_2 ] ]' => $rand ? $random_role->icon : $user_role->icon,
                                    ]);

                                }

                                break;

                            case ROLE_Hacker:

                                // Status Hacker
                                if ($select_hacker instanceof User && $select_hacker->getUserId() > 0) {

                                    if (rand(0, 1) == 1) {

                                        $footer[$user->getUserId()][] = '✅ شما موفق شدید ' . $select_hacker->get_name() . ' را هک کنید . ';
                                        $footer[$select_hacker->getUserId()][] = '🧑🏻‍💻 شما هک شدید . ';
                                        $server->updateMeta('hack', $select_hacker->getUserId());

                                    } else {

                                        $footer[$user->getUserId()][] = '❌ شما موفق نشدید ' . $select_hacker->get_name() . ' را هک کنید . ';

                                    }

                                }

                                break;

                            case ROLE_MosaferZaman:

                                if (count($dead_body) > 0) {

                                    add_server_meta($server->getId(), 'targets', serialize($dead_body), ROLE_MosaferZaman);

                                }

                                break;

                        }

                    }
                }
                // ╚══════ End Footer ══════════════════════════════════════════════════════════════════════════╝
                // ╔══════ Send Message To People ═════════════════════════════════════════════════════════════╗
                $temp_message = $message;
                $card_voting_day = false;
                if ($server->getMeta('card-4') == $day) {
                    $card_voting_day = true;
                }
                foreach ($users_server as $user) {
                    if (!$user->is_user_in_game() || $user->sleep())
                        continue;

                    $message = $temp_message;

                    if (isset($footer[$user->getUserId()])) {

                        foreach ($footer[$user->getUserId()] as $item) {

                            $message .= /*"<code>" .*/
                                $item /* . "</code>"*/. "\n";

                        }

                    }

                    // if ( $server->getMeta('card-4') == $day) {

                    // }
                    // else {

                    // }
                    if ($card_voting_day) {
                        $message .= "\n" . '🗳 رای گیری  👈⏱  ۵ ثانیه';
                    } else
                        $message .= "\n" . '💬 چت کردن  👈⏱  ۵ ثانیه';
                    if (ceil($users_online / 2) == 1) {

                        $user->setStatus('playing_game')->SendMessageHtml($message);

                    } else {

                        $user->SendMessageHtml($message);

                    }

                }
                // ╚══════ End Send Message To People ═════════════════════════════════════════════════════════╝

                // ╔══════ Delete All Select Roles ═════════════════════════════════════════════════════════════╗
                $selector->clear();
                // ╚══════ End Delete All Select Roles ══════════════════════════════════════════════════════════╝

                if (ceil($users_online / 2) == 1) {
					
					$server->nextDay()->setStatus('night')->charge(40);

                    $bazpors = $selector->getUser(ROLE_Bazpors);
                    if ($bazpors->getUserId() > 0 && !$bazpors->dead() && $server->setUserId(ROLE_Bazpors)->getMetaUser('status') != 'no-body') {

                        $message = 'انتخاب کنید چه کسی را زندانی میکنید:';

                        foreach ($users_server as $user) {

                            if ($user->check($bazpors) && $server->setUserId($user->getUserId())->getMetaUser('no-vote') != 'on') {

                                $keyboard[] = [
                                    $telegram->buildInlineKeyboardButton('🔗 ' . $user->get_name(), '', ($day + 1) . '/server-' . $server->league_id . '-question-' . $server->getId() . '-' . $user->getUserId())
                                ];

                            }

                        }

                        SendMessage($bazpors->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');
                    }

                    $gambeler = $selector->getUser(ROLE_Gambeler);
                    if ($gambeler->getUserId() > 0 && !$gambeler->dead()) {

                        $message = 'انتخاب کنید با چه کسی میخواهید بازی کنید:';

                        foreach ($users_server as $user) {

                            if ($user->check($gambeler)) {

                                $keyboard[] = [
                                    $telegram->buildInlineKeyboardButton('🎮 ' . $user->get_name(), '', ($day + 1) . '/server-' . $server->league_id . '-' . ROLE_Gambeler . '-' . $server->getId() . '-' . $user->getUserId())
                                ];

                            }

                        }

                        $gambeler->setKeyboard($telegram->buildInlineKeyBoard($keyboard))->SendMessageHtml($message);
                    }

                    $minelayer = $selector->getUser(ROLE_MineLayer);
                    if ($minelayer->getUserId() > 0 && !$minelayer->dead()) {

                        $message = 'یک نفر را برای مین گذاری انتخاب کنید:';

                        foreach ($users_server as $user) {

                            if ($user->check($minelayer) && !$user->is($chatid)) {

                                $keyboard[] = [
                                    $telegram->buildInlineKeyboardButton('💣 ' . $user->get_name(), '', ($day + 1) . '/server-' . $server->league_id . '-' . ROLE_MineLayer . '-' . $server->getId() . '-' . $user->getUserId())
                                ];

                            }

                        }

                        $minelayer->setKeyboard($telegram->buildInlineKeyBoard($keyboard))->SendMessageHtml($message);
                    }



                    $message = '💬 چت : فعال برای همه' . "\n";
                    $message .= '🗳 امروز امکان رای گیری وجود ندارد . ' . "\n";
                    $message .= 'زمان 👈⏱  40 ثانیه';
                    $server->sendMessageHtml($message);

                } else {
                    if ($card_voting_day)
                        $server->nextDay()->setStatus('voting')->charge(5)->clearVotesMeta();
                    else
                        $server->nextDay()->setStatus('message')->charge(5);

                }

                break;
            case 'message':
                $message = '💬 چت : فعال برای همه' . "\n";

                if (in_array($server->league_id, [4, 5])) {
                    $message .= '🗳 رای گیری  👈⏱ ۹۰ ثانیه';
                } else {
                    $message .= '🗳 رای گیری  👈⏱ ۴۵ ثانیه';
                }

                foreach ($users_server as $user) {
                    if ($user->sleep() || !$user->is_user_in_game())
                        continue;
                    $user->setStatus('playing_game')->SendMessageHtml($message);
                }
                $shahrdar = $selector->getUser(ROLE_Shahrdar);
                // $shahrdar_power = $server->getMeta('shahrdar');
                if ($shahrdar->getUserId() > 0 && !$shahrdar->dead() && !$server->getMeta('shahrdar')) {
                    $keyboard = [];
                    $message = 'انتخاب کنید آیا امروز نقش خود را برای همه افشا میکنید ؟:';

                    $keyboard[][] = $telegram->buildInlineKeyboardButton('🤚🏻 اعلام نقش', '', $day . '/server-' . $server->league_id . '-' . ROLE_Shahrdar . '-' . $server->getId() . '-0');

                    $shahrdar->setKeyboard($telegram->buildInlineKeyBoard($keyboard))->SendMessageHtml($message);
                }


                if (in_array($server->league_id, [4, 5])) {
                    $server->charge(90);
                } else {
                    $server->charge(45);
                }

                $server->setStatus('voting')->clearVotesMeta();
                break;
            case 'voting':

                $server->deleteMeta('court')->deleteMeta('accused')->deleteMeta('is');
                $card_mafia_day = ($server->getMeta('card-mafia_day') == $day) ? true : false;

                if ($card_mafia_day) {
                    $message = "#کارت\n";
                    $message .= "😈 امروز جشن مافیاست ، رای گیری برگزار نمی‌شود .\n\n";
                    $message .= '🌙 شب : ۴۵ ثانیه';

                } else {
                    $message = '🗳 ‏انتخاب کنید امروز به چه کسی رای میدهید تا به دادگاه احضار شود . ' . "\n";
                    $message .= 'برای احضار به دادگاه [ [ number ] ] رای لازم است . ' . "\n";
                    if (in_array($server->league_id, [4, 5])) {
                        $message .= '⏱ مدت زمان رای گیری : ۶۰ ثانیه';
                    } else {
                        $message .= '⏱ مدت زمان رای گیری : ۴۵ ثانیه';
                    }
                    __replace__($message, [
                        '[ [ number ] ]' => ceil($server->getPeopleAlive() / 2)
                    ]);
                }




                if ((($server->role_exists(ROLE_Dalghak) || $server->role_exists(ROLE_Dozd)) && $server->setUserId(ROLE_Dalghak)->getMetaUser('dalghak') == 'on') || $card_mafia_day) {

                    if ($server->setUserId(ROLE_Dalghak)->getMetaUser('dalghak') == 'on')
                        $server->setUserId(ROLE_Dalghak)->updateMetaUser('dalghak', 'use');

                    foreach ($users_server as $user) {

                        if (!$user->is_user_in_game() || $user->sleep())
                            continue;

                        $keyboard = [];

                        if ($user->getRoleId() == ROLE_Bazpors && !$user->dead()) {
                            foreach ($users_server as $item) {

                                if ($item->check($user)) {

                                    if ($server->setUserId($item->getUserId())->getMetaUser('no-vote') != 'on') {

                                        $keyboard[][] = $telegram->buildInlineKeyboardButton('🔗 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-question-' . $server->getId() . '-' . $item->getUserId());

                                    }

                                }

                            }

                            $user->setKeyboard($telegram->buildInlineKeyBoard($keyboard))->SendMessageHtml($message);

                        } else {
                            $user->SendMessageHtml($message);
                        }

                    }

                } else {

                    $user_greenway = get_server_meta_user($server->getId(), 'card-green_way', $day);

                    $red_carpet = get_server_meta_user($server->getId(), 'card-red_carpet', $day);
                    if ($red_carpet)
                        $user_red_carpet = new User((int) $red_carpet, $server->getId());

                    if ($red_carpet && $user_red_carpet->getUserId() > 0 && !$user_red_carpet->dead()) {


                        $message = "#کارت \n🥵 کارت فرش قرمز در دستان <b>{$user_red_carpet->get_name()}</b> است و امروز رای گیری انجام نمیشود . \n\n";
                        if (in_array($server->league_id, [4, 5])) {
                            $message .= '⏱ دادگاه : ۶۰ ثانیه';
                        } else {
                            $message .= '⏱ دادگاه : ۴۵ ثانیه';
                        }
                    } else
                        $user_red_carpet = null;


                    foreach ($users_server as $user) {
                        if (!$user->is_user_in_game() || $user->sleep())
                            continue;

                        $keyboard = [];

                        $user->setStatus('voting');
                        if (!$user->dead()) {

                            $keyboard = [];
                            $i = 0;
                            $x = 0;



                            foreach ($users_server as $item) {
                                if ($item->check($user) && !$item->dead()) {

                                    if ($server->setUserId($item->getUserId())->getMetaUser('no-vote') != 'on') {

                                        if ($item->getUserId() == $user_greenway) {
                                            $text = '🤠 ' . $item->get_name();
                                        } else {
                                            $text = '🗳 ' . $item->get_name();
                                        }

                                        switch ($user->getRoleId()) {

                                            case ROLE_Bazpors:
                                                if (!$user_red_carpet)
                                                    $keyboard[$i][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-vote-' . $server->getId() . '-' . $item->getUserId());

                                                $keyboard[$i][] = $telegram->buildInlineKeyboardButton('🔗 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-question-' . $server->getId() . '-' . $item->getUserId());
                                                $i++;

                                                break;

                                            case ROLE_Gambeler:

                                                if (!$user_red_carpet)
                                                    $keyboard[$x][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-vote-' . $server->getId() . '-' . $item->getUserId());

                                                $keyboard[$x][] = $telegram->buildInlineKeyboardButton('🎮 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_Gambeler . '-' . $server->getId() . '-' . $item->getUserId());
                                                $x++;

                                                break;

                                            case ROLE_MineLayer:

                                                if (!$user_red_carpet)
                                                    $keyboard[$x][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-vote-' . $server->getId() . '-' . $item->getUserId());

                                                $keyboard[$x][] = $telegram->buildInlineKeyboardButton('💣 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_MineLayer . '-' . $server->getId() . '-' . $item->getUserId());
                                                $x++;

                                                break;
                                            case ROLE_MineLayerMafia:
                                                $power = intval($selector->getInt()->select(ROLE_MineLayerMafia, 'mine')) ?? 0;
                                                if (!$user_red_carpet)
                                                    $keyboard[$x][] = $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-vote-' . $server->getId() . '-' . $item->getUserId());
                                                if ($item->get_role()->group_id != 2 && $power > 0) {
                                                    $keyboard[$x][] = $telegram->buildInlineKeyboardButton('💣 ' . $item->get_name(), '', $day . '/server-' . $server->league_id . '-' . ROLE_MineLayerMafia . '-' . $server->getId() . '-' . $item->getUserId());
                                                }
                                                $x++;

                                                break;

                                            default:
                                                if (!$user_red_carpet) {
                                                    $keyboard[] = [
                                                        $telegram->buildInlineKeyboardButton($text, '', $day . '/server-' . $server->league_id . '-vote-' . $server->getId() . '-' . $item->getUserId())
                                                    ];
                                                } else
                                                    $keyboard[] = [];
                                                break;
                                        }

                                    }

                                }

                            }

                            $user->setKeyboard($telegram->buildInlineKeyBoard($keyboard));

                        }
                        $user->SendMessageHtml();

                    }
                }
                if (in_array($server->league_id, [4, 5])) {
                    $server->charge(60);
                } else {
                    $server->charge(45);
                }


                if ($card_mafia_day) {
                    $server->setStatus('night')->deleteMeta('court');
                } elseif ($user_red_carpet) {
                    $server->updateMeta('court', 'close');
                    $server->updateMeta('is', 'on');
                    $server->setStatus('court-redcarpet')->resetMessage();
                } else {
                    $server->setStatus('court')->resetMessage();
                }


                break;
            case 'court-redcarpet':
                if ($server->getMeta('shahrdar') == 'on')
                    $server->updateMeta('shahrdar', 'used');
                $user_red_carpet = get_server_meta_user($server->getId(), 'card-red_carpet', $day);
                if ($user_red_carpet) {
                    $accused = new User((int) $user_red_carpet, $server->getId());
                    update_server_meta($server->getId(), 'accused', $accused->getUserId());
                    $message = "⚖️ <u>{$accused->get_name()}</u> به دادگاه فراخوانده شد . \n";
                    $message .= 'متهم ۱۵ ثانیه فرصت دارد تا از خود دفاع کند .' . "\n";
                    $message .= '💬 چت : فقط برای متهم';

                    foreach ($users_server as $item) {

                        if ($item->is_user_in_game() && !is_server_meta($server->getId(), 'message-sended', $item->getUserId()) && !$item->sleep()) {

                            $result = SendMessage($item->getUserId(), $message, null, null, 'html');
                            if (isset($result->message_id)) {

                                add_server_meta($server->getId(), 'message-sended', 'sended', $item->getUserId());
                                $item->setStatus('voting');

                            }

                        }
                    }
                    $server->setStatus('court-2')->charge(15)->clearVotesMeta();
                } else {
                    $server->setStatus('court')->resetMessage();
                }
                break;

            case 'court':
                if ($server->getMeta('shahrdar') == 'on')
                    $server->updateMeta('shahrdar', 'used');

                $vote_users = $server->getInt()->votes();
                $status = 'night';
                $ceil = ceil($server->getPeopleAlive() / 2);
                foreach ($vote_users as $key => $value) {
                    if ($ceil <= count($value)) {
                        $status = 'court-2';
                        break;
                    }
                }
                if ($status == 'night') {
					
                    if (!is_server_meta($server->getId(), 'accused') || (int) get_server_meta($server->getId(), 'accused') <= 0) {
						if ($server->role_exists(ROLE_Shahzadeh)) {
							$shahzadeh = $selector->getUser(ROLE_Shahzadeh);
							if($shahzadeh->dead() && $server->setUserId(ROLE_Shahzadeh)->getMetaUser('isoldkilled') == 0)
							{
								$server->setUserId(ROLE_Shahzadeh)->updateMetaUser('isoldkilled', 1);
								goto LIGHT_MODE;
							}
						}
						
                        goto NIGHT_MODE;

                    }

                } else {

                    if (!is_server_meta($server->getId(), 'accused') || (int) get_server_meta($server->getId(), 'accused') <= 0) {

                        if ($server->getMeta('is-court') != 'on') {

                            $server->updateMeta('is-court', 'on')->charge(20);

                        } else {

                            $server->charge(5)->setStatus('court-2')->deleteMeta('court-2');

                        }

                    }

                }

                break;
            case 'court-2':
                if ($server->getMeta('shahrdar') == 'on')
                    $server->updateMeta('shahrdar', 'used');
                $accused = $server->accused();

                if ($accused->user_exists()) {

                    $message = 'تصمیم نهایی خود را بگیرید و انتخاب کنید از کدام رای استفاده میکنید . ' . "\n";
                    $message .= 'متهم: ' . $accused->get_name() . "\n \n";
                    $message .= '⏱ پایان رای گیری مجدد : ۱۵ ثانیه';

                    foreach ($users_server as $user) {

                        if (!$user->is_user_in_game() || $user->sleep())
                            continue;

                        $keyboard = [];

                        if ($user->check($accused)) {

                            $keyboard[] = [

                                $telegram->buildInlineKeyboardButton('بی‌گناه', '', $day . '/server-' . $server->league_id . '-^court-' . $server->getId() . '-' . $user->getUserId()),

                                $telegram->buildInlineKeyboardButton('گناهکار', '', $day . '/server-' . $server->league_id . '-court-' . $server->getId() . '-' . $user->getUserId())

                            ];

                            switch ($user->getRoleId()) {

                                case ROLE_Ghazi:

                                    if (!is_server_meta($server->getId(), 'ghazi', ROLE_Ghazi)) {

                                        $keyboard[][] = $telegram->buildInlineKeyboardButton('❌ ابطال', '', $day . '/server-' . $server->league_id . '-pass_voting-' . $server->getId() . '-' . $user->getUserId());

                                    }

                                    break;
                                case ROLE_Fadaii:

                                    $keyboard[][] = $telegram->buildInlineKeyboardButton('فدا شدن', '', $day . '/server-' . $server->league_id . '-fadaii-' . $server->getId() . '-' . $user->getUserId());

                                    break;

                                case ROLE_Big_Khab:

                                    if (!is_server_meta($server->getId(), 'bigKhan', ROLE_Big_Khab)) {
                                        $keyboard[] = [
                                            $telegram->buildInlineKeyboardButton('🟢 بی‌گناه', '', $day . '/server-' . $server->league_id . '-big_khab-' . $server->getId() . '-' . 2),
                                            $telegram->buildInlineKeyboardButton('🔴 گناهکار', '', $day . '/server-' . $server->league_id . '-big_khab-' . $server->getId() . '-' . 1),
                                        ];
                                    }

                                    break;

                            }

                            SendMessage($user->getUserId(), $message, $telegram->buildInlineKeyBoard($keyboard), null, 'html');

                        } else
                            $user->SendMessageHtml();

                    }

                    $server->setStatus('court-3')->charge(15);
                } else
				{
                    goto NIGHT_MODE;
				}

                break;
            case 'court-3':

                $accused = $server->accused();

                if ($accused->user_exists()) {

                    $court = $server->getGuilt();
                    $_court = $server->getInnocent();

                    $message = '🪧 نتیجه آراء' . "\n \n";
                    foreach ($court as $item) {
                        $message .= '[ [ user ] ]' . ' رای به ' . "<b>گناهکاری</b>" . ' داد . ' . "\n";
                        __replace__($message, [
                            '[ [ user ] ]' => $item->get_name()
                        ]);
                    }

                    foreach ($_court as $item) {
                        $message .= '[ [ user ] ]' . ' رای به بی‌گناهی داد . ' . "\n";
                        __replace__($message, [
                            '[ [ user ] ]' => $item->get_name()
                        ]);
                    }

                    $message .= "\n";
                    $role_user = $accused->get_role();

                    if ($role_user->id == ROLE_Shayad) {

                        $select_shayad = $selector->user()->select(ROLE_Shayad, 'select', false);
                        if ($select_shayad->getUserId() > 0) {

                            $role_user = $select_shayad->get_role();

                        }

                    }

                    $select_big_khan = $selector->user()->select(ROLE_Big_Khab);
                    if ($select_big_khan->getUserId() > 0) {

                        if ($select_big_khan->getUserId() == 1) {

                            $message .= '❗️بزرگ خاندان رای به ' . "<b>گناهکاری</b>" . ' داد . ' . "\n";
                            $message .= $accused->kill()->get_name() . ' اعدام شد . ' . "\n";
                            $message .= '♨️ نقش : ' . ($role_user->group_id == 2 ? 'عضو مافیا' : $role_user->icon) . (emoji_group($role_user->group_id));

                            switch ($role_user->id) {
                                case ROLE_Joker:

                                    if (count($court) > 0) {
                                        $rand = array_rand($court);
                                        $user_rand = $court[$rand];
                                        $message .= "\n \n" . '[ [ user ] ] توسط جوکر کشته شد . ' . "\n \n";
                                        __replace__($message, [
                                            '[ [ user ] ]' => "<u>" . $user_rand->kill()->get_name() . "</u>",
                                        ]);
                                        $user_rand->getRoleId() != ROLE_Bazpors || $selector->delete(ROLE_Bazpors);
                                    }

                                    $server->updateMeta('joker', 'yes');
                                    break;
                                case ROLE_Bazpors:
                                    $selector->delete(ROLE_Bazpors);
                                    break;
                                case ROLE_Mashooghe:
                                    $server->setUserId(ROLE_Godfather)->updateMetaUser('super-god-father', 'on');
                                    break;
                                case ROLE_Bakreh:
                                    $server->updateMeta('bakreh', 'on');
                                    break;
                                case ROLE_Kaboy:
                                    $select_kaboy = $selector->user()->select(ROLE_Kaboy);

                                    if ($select_kaboy instanceof User && $select_kaboy->getUserId() > 0) {

                                        $message .= '[ [ kaboy ] ] توسط کابوی کشته شد . ' . "\n \n";
                                        __replace__($message, [
                                            '[ [ kaboy ] ]' => "<u>" . $select_kaboy->kill()->get_name() . "</u>"
                                        ]);
                                        switch ($select_kaboy->get_role()->id) {

                                            case ROLE_Bazpors:
                                                $selector->delete(ROLE_Bazpors);
                                                break;

                                        }

                                    }

                                    break;
                                case ROLE_Terrorist:
                                    $select_terrorist = $selector->user()->select(ROLE_Terrorist);

                                    if ($select_terrorist instanceof User && $select_terrorist->getUserId() > 0) {

                                        $message .= '[ [ terrorist ] ] توسط تروریست ، ترور شد . ' . "\n \n";
                                        __replace__($message, [
                                            '[ [ terrorist ] ]' => "<u>" . $select_terrorist->kill()->get_name() . "</u>"
                                        ]);
                                        switch ($select_terrorist->get_role()->id) {

                                            case ROLE_Bazpors:
                                                $selector->delete(ROLE_Bazpors);
                                                break;

                                        }

                                    }

                                    break;
                            }

                        } else {

                            $message .= '❗️بزرگ خاندان رای به ' . "<b><u>بیگناهی</u></b>" . ' داد . ' . "\n";
                            $message .= $accused->get_name() . ' تبرئه شد . ';

                        }

                        add_server_meta($server->getId(), 'bigKhan', 'use', ROLE_Big_Khab);
                        $selector->delete(ROLE_Big_Khab);

                    } else {

                        if (count($court) > count($_court)) {

                            $ghazi = $selector->getUser(ROLE_Ghazi);
                            if (!$server->role_exists(ROLE_Ghazi) || $ghazi->dead() || $server->getMeta('ghazi') == 'use' || $server->setUserId(ROLE_Ghazi)->getMetaUser('ghazi') != 'use') {

                                $fadaii = $selector->getUser(ROLE_Fadaii);
                                if (!$server->role_exists(ROLE_Fadaii) || $fadaii->dead() || $server->setUserId(ROLE_Fadaii)->getMeta('fadaii') != 'use') {

                                    $server->setUserId(ROLE_Ghazi)->deleteMetaUser('ghazi')->setUserId(ROLE_Fadaii)->deleteMetaUser('fadaii');

                                    switch ($role_user->id) {
                                        case ROLE_Joker:
                                            $rand = array_rand($court);
                                            $user_rand = $court[$rand];
                                            $message .= '[ [ user ] ] توسط جوکر کشته شد . ' . "\n \n";
                                            __replace__($message, [
                                                '[ [ user ] ]' => "<u>" . $user_rand->kill()->get_name() . "</u>",
                                            ]);
                                            $server->updateMeta('joker', 'yes');
                                            $user_rand->getRoleId() != ROLE_Bazpors || $selector->delete(ROLE_Bazpors);
                                            break;
                                        case ROLE_Bazpors:
                                            $selector->delete(ROLE_Bazpors);
                                            break;
                                        case ROLE_Mashooghe:
                                            $server->setUserId(ROLE_Godfather)->updateMetaUser('super-god-father', 'on');
                                            break;
                                        case ROLE_Bakreh:
                                            $server->updateMeta('bakreh', 'on');
                                            break;
                                        case ROLE_Kaboy:
                                            $select_kaboy = $selector->user()->select(ROLE_Kaboy);

                                            if ($select_kaboy instanceof User && $select_kaboy->getUserId() > 0) {

                                                $message .= '[ [ kaboy ] ] توسط کابوی کشته شد . ' . "\n \n";
                                                __replace__($message, [
                                                    '[ [ kaboy ] ]' => "<u>" . $select_kaboy->kill()->get_name() . "</u>"
                                                ]);
                                                switch ($select_kaboy->get_role()->id) {

                                                    case ROLE_Bazpors:
                                                        $selector->delete(ROLE_Bazpors);
                                                        break;

                                                }

                                            }
                                        case ROLE_Terrorist:
                                            $select_terrorist = $selector->user()->select(ROLE_Terrorist);

                                            if ($select_terrorist instanceof User && $select_terrorist->getUserId() > 0) {

                                                $message .= '[ [ terrorist ] ] توسط تروریست کشته شد . ' . "\n \n";
                                                __replace__($message, [
                                                    '[ [ terrorist ] ]' => "<u>" . $select_terrorist->kill()->get_name() . "</u>"
                                                ]);
                                                switch ($select_terrorist->get_role()->id) {
                                                    case ROLE_Bazpors:
                                                        $selector->delete(ROLE_Bazpors);
                                                        break;
                                                }

                                            }

                                            break;

                                        case ROLE_Ankabot:

                                            $selector->delete(ROLE_Ankabot);
                                            $selector->delete(ROLE_Ankabot, 'select-2');

                                            break;

                                    }

                                    $message .= $accused->get_name() . ' محکوم به اعدام شد . ' . "\n";
                                    if ($selector->select(ROLE_Ahangar, 'last-select')->is($accused) || $selector->select(ROLE_Ahangar, 'last-select-2')->is($accused)) {
                                        $message .= '🛡 زره مانع از اعدام شد . ' . "\n";
                                    } else
                                        $accused->kill();
                                } else {

                                    $server->updateMeta('fadaii', 'use')->setUserId(ROLE_Fadaii)->updateMetaUser('fadaii', 'off');
                                    $message .= 'فدایی از اعدام [ [ user ] ] جلوگیری کرد و به جای او اعدام میشود . ' . "\n";
                                    $message .= '[ [ user ] ] در بازی می‌ماند . ' . "\n \n";
                                    $message .= '[ [ fadaii ] ] کشته شده . ' . "\n";
                                    __replace__($message, [
                                        '[ [ user ] ]' => $accused->get_name(),
                                        '[ [ fadaii ] ]' => $fadaii->kill()->get_name()
                                    ]);
                                    $role_user = $fadaii->get_role();

                                }

                                $message .= '♨️ نقش : ' . ($role_user->group_id == 2 ? 'عضو مافیا' : $role_user->icon) . (emoji_group($role_user->group_id));

                            } else {

                                $message .= 'رای گیری توسط قاضی < u>ابطال </u > شد . ' . "\n";
                                $message .= '[ [ user ] ] در بازی می‌ماند . ';

                                __replace__($message, [
                                    '[ [ user ] ]' => $accused->get_name()
                                ]);

                                $server->setUserId(ROLE_Ghazi)->updateMetaUser('ghazi', 'off')->updateMeta('ghazi', 'use');

                            }

                        } else {

                            $message .= '[ [ user ] ] تبرئه شد . ';
                            __replace__($message, [
                                '[ [ user ] ]' => $accused->get_name()
                            ]);

                            $server->setUserId(ROLE_Ghazi)->getMetaUser('ghazi') != 'use' || $server->deleteMetaUser('ghazi');
                            get_server_meta($server->getId(), 'fadaii') != 'use' || $server->deleteMeta('fadaii');
                        }

                    }

                    if ($accused->dead()) {

                        if ($server->role_exists(ROLE_MosaferZaman)) {

                            $select_mosafer_zaman = $selector->getString()->select(ROLE_MosaferZaman, 'targets');
                            $targets = unserialize($select_mosafer_zaman) ?? [];
                            $targets[] = $accused->getUserId();
                            add_server_meta($server->getId(), 'targets', serialize($targets), ROLE_MosaferZaman);

                        }

                        if ($server->role_exists(ROLE_Jalad)) {

                            $jalad = $selector->getUser(ROLE_Jalad);
                            if (!$jalad->dead()) {

                                $targets = json_decode($selector->getString()->select(ROLE_Jalad, 'targets', false), true);
                                $random_role = new User($targets[0], $server->getId());
                                $random_role_2 = new User($targets[1], $server->getId());

                                if ($random_role->dead() && $random_role_2->dead()) {

                                    $message .= "\n \n" . 'ماموریت جلاد به پایان رسید . ' . "\n";
                                    $message .= ' < b>' . $jalad->kill()->get_name() . ' </b > ' . ' از بازی میره بیرون . ' . "\n";
                                    $message .= '♨️ نقش : جلاد 🪓🟡';
                                    $selector->set(10, ROLE_Jalad, 'power');

                                }
                            }

                        }

                        if ($server->role_exists(ROLE_Ankabot)) {

                            if ($accused->is($selector->select(ROLE_Ankabot))) {

                                $select_ankabot = $selector->select(ROLE_Ankabot, 'select-2');

                            } elseif ($accused->is($selector->select(ROLE_Ankabot, 'select-2'))) {

                                $select_ankabot = $selector->select(ROLE_Ankabot);

                            }

                            if ($select_ankabot instanceof User && $select_ankabot->getUserId() > 0) {

                                $message .= "\n \n" . "<u>" . $select_ankabot->get_name() . "</u>" . ' توسط تار عنکبوت کشته شد . ' . "\n";
                                $role_user = $select_ankabot->kill()->get_role();
                                $message .= '♨️ نقش : ' . ($role_user->group_id == 2 ? 'عضو مافیا' : $role_user->icon) . (emoji_group($role_user->group_id));
                                switch ($role_user->id) {

                                    case ROLE_Bazpors:
                                        $selector->delete(ROLE_Bazpors);
                                        break;

                                    case ROLE_Kaboy:
                                        $select_kaboy = $selector->user()->select(ROLE_Kaboy);

                                        if ($select_kaboy instanceof User && $select_kaboy->getUserId() > 0) {

                                            $message .= '[ [ kaboy ] ] توسط کابوی کشته شد . ' . "\n \n";
                                            __replace__($message, [
                                                '[ [ kaboy ] ]' => "<u>" . $select_kaboy->kill()->get_name() . "</u>"
                                            ]);
                                            switch ($select_kaboy->get_role()->id) {

                                                case ROLE_Bazpors:
                                                    $selector->delete(ROLE_Bazpors);
                                                    break;

                                            }

                                        }

                                        break;
                                    case ROLE_Terrorist:
                                        $select_terrorist = $selector->user()->select(ROLE_Terrorist);

                                        if ($select_terrorist instanceof User && $select_terrorist->getUserId() > 0) {

                                            $message .= '[ [ terrorist ] ] توسط تروریست کشته شد . ' . "\n \n";
                                            __replace__($message, [
                                                '[ [ terrorist ] ]' => "<u>" . $select_terrorist->kill()->get_name() . "</u>"
                                            ]);
                                            switch ($select_terrorist->get_role()->id) {

                                                case ROLE_Bazpors:
                                                    $selector->delete(ROLE_Bazpors);
                                                    break;

                                            }

                                        }

                                        break;
                                }

                            }

                            $selector->delete(ROLE_Ankabot);
                            $selector->delete(ROLE_Ankabot, 'select-2');

                        }

                        switch ($accused->getRoleId()) {

                            case ROLE_Sagher:

                                $sagher_regent = $selector->select(ROLE_Sagher, 'regent');
                                if ($sagher_regent->getUserId() > 0 && !$sagher_regent->dead()) {

                                    $sagher_regent->changeRole(ROLE_Sagher);
                                    $selector->delete(ROLE_Sagher, 'regent');

                                }

                                break;

                        }

                    }

                    // ║ Hazard
                    if ($server->role_exists(ROLE_Hazard)) {

                        $type = $selector->getInt()->select(ROLE_Hazard, 'type');
                        if ($type == 1 || ($type == 2 && $accused->dead())) {

                            $select_hazard = $selector->select(ROLE_Hazard);
                            if ($select_hazard->getUserId() > 0 && $select_hazard->is($accused)) {

                                $selector->set((intval($selector->getInt()->select(ROLE_Hazard, 'heart', false)) + 1), ROLE_Hazard, 'heart');
                                if ($type == 2) {
                                    $selector->set(1, ROLE_Hazard, 'power');
                                }

                            } elseif ($select_hazard->getUserId() > 0) {

                                $selector->set($selector->getInt()->select(ROLE_Hazard, 'warning', false) + 1, ROLE_Hazard, 'warning');

                            }

                            $selector->delete(ROLE_Hazard);
                            $selector->delete(ROLE_Hazard, 'type');

                        }

                    }

                    $server->sendMessageHtml($message)->deleteMeta('accused');

                }

                if (check_status_game(get_server($server->getId())))
                    continue 2;
                # Cards Here 
                $server->deleteMeta('type-card')->deleteMeta('select-card');
                if ($accused->user_exists() && $accused->dead() && $server->league_id == 2 && in_array($accused->get_role()->group_id, [1, 2])) {
                    $keyboard = [];
                    try {
                        $message = "🔖 شما فرصت استفاده از یکی از کارت های آخر بازی را دارید . \n";
                        $message .= "🎲 برای دریافت کارت دکمه زیر رو بزنید تا یکی از کارت های باقیمانده بصورت شانسی به شما داده شود .";
                        $keyboard[][] = $telegram->buildInlineKeyboardButton('🃏 کارت شانسی 🎲', '', $day . '/server-' . $server->league_id . '-cards-' . $server->getId() . '-0-rand');

                        $accused->setKeyboard($telegram->buildInlineKeyBoard($keyboard))->SendMessageHtml($message);
                        // SendMessage( $accused->getUserId(), $message, $telegram->buildInlineKeyBoard( $keyboard ), null, 'html' );

                        // $server_cards = json_decode( $server->getMeta( 'cards' ), true );
                        // $server_cards = count( $server_cards ) > 0 ? $server_cards : [];
                        // $all_cards = get_cards();


                        // $message     = '🔖 شما فرصت استفاده از یکی از کارت های آخر بازی را دارید ، کارت خود را انتخاب کنید : ' . "\n \n" . 'کارت های باقیمانده:';

                        // foreach ($all_cards as $card )
                        // {
                        //     if ($card->is_active == 1 &&  !$server_cards[ "card-{$card->id}" ]  ) {
                        //         $keyboard[] = [
                        //             $telegram->buildInlineKeyboardButton( '🃏 '. $card->name, '', $day . '/server-' . $server->league_id . '-cards-' . $server->getId() . '-0-' . $card->id ),
                        //         ];
                        //     }
                        // }


                        // if (count($keyboard))
                        //     SendMessage( $accused->getUserId(), $message, $telegram->buildInlineKeyBoard( $keyboard ), null, 'html' );




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
                }
                // file_put_contents(BASE_DIR . '/cards_test.txt', implode("\n", $fwrite));
				
				
				$server->setStatus('night')->charge(15)->deleteMeta('court');
			

                break;
            case 'chatting':
                $server->close();
                break;

        }

        $server->deleteMeta('is');

    }
} catch (Exception | Throwable $e) {

    $message = "<b>🔴 WARNING ERROR ON WEB 🔴</b>" . "\n";
    $message .= "<b>👉 Error File : { " . $e->getFile() . ':' . "<code>" . $e->getLine() . "</code>" . " }</b>" . "\n";
    if (isset($server) && $server instanceof Server && $server->getId() > 0) {

        $message .= "<i>ERROR Server: {" . $server->getId() . "}</i>" . "\n \n";

    }
    $message .= "<b>👾 Error Content:</b>" . "\n \n";
    $message .= "<b><code>" . $e->getMessage() . "</code></b>";
    SendMessage(ADMIN_LOG, $message, null, null, 'html');
    SendMessage("6645079982", $message, null, null, 'html');
}

// if ( file_exists( 'error_log' ) ) unlink( 'error_log' );

exit('SUCCESS');