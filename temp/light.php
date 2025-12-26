<?php

/** @noinspection ALL */
use library\Role;
use library\Server;
use library\User;

$message = '🌞 #روز ' . $number_to_word->numberToWords($day + 1) . "\n \n";

$dead_body = [];
$kills = [];
$prison = [];
$footer = [];
$report = '';

// ╔══════ Get Select Role ═══════════════════════════════════════════════════════════════════════╗

// ║ Afson Gar
if ($server->role_exists(ROLE_AfsonGar)) {
    $select_afson_gar = $selector->user()->select(ROLE_AfsonGar);
    if ($select_afson_gar instanceof User && $select_afson_gar->getUserId() > 0) {

        $selector->set($select_afson_gar->getUserId(), ROLE_AfsonGar, 'last-select');
        $filter_role = [string_encode(ROLE_Keshish), string_encode(ROLE_Police)];
        $user_role = $select_afson_gar->getRoleId();
        if (!in_array(string_encode($user_role), $filter_role)) {

            $selector->delete($user_role);
            $footer[$select_afson_gar->getUserId()][] = 'افسونگر شما را از ایفای نقش بازداشت .';

        }

    }
}
$city_safe = 'off';

if ($server->getMeta('card-city_safe') && $server->getMeta('card-city_safe') == $day) {
    $city_safe = 'on';
} else
    $city_safe = 'off';
// $city_safe = false;
// ║ Keshish
$select_keshish = $selector->getString()->select(ROLE_Keshish);
// ║ Shayad
$select_shayad = $selector->user()->select(ROLE_Shayad, 'select', false);

// ║ Killer
$select_killer = $selector->user()->select(ROLE_Killer, 'select', false);
// ║ Dalghak
if (($server->role_exists(ROLE_Dalghak) || $server->role_exists(ROLE_Dozd)) && $select_keshish != 'on') {

    $select_dalghak = $selector->user()->select(ROLE_Dalghak);
    if ($select_dalghak->getUserId() > 0) {

        $message .= 'دلقک شهر را به خود متوجه کرده است.' . "\n";
        $report .= 'دلقک شهر را به خود متوجه کرده است.' . "\n";
        $server->setUserId(ROLE_Dalghak)->updateMetaUser('dalghak', 'on')->clearSelect();

    }

}
// ║ body Gard
if ($server->role_exists(ROLE_Bodygard) && $select_keshish != 'on') {

    // ║ Body Gard
    $select_body_gard = $selector->user()->select(ROLE_ShabKhosb);

    if ($select_body_gard->getUserId() > 0) {

        $body_gard = $selector->getUser(ROLE_Bodygard);
        $list_attacker_body_gard = $server->getListAttacker($select_body_gard->getUserId());

        $filter_role = [ROLE_Godfather, ROLE_Mashooghe, ROLE_Gorg, ROLE_Sniper];

        if ($select_body_gard->is($body_gard)) {

            $safe = false;
            foreach ($list_attacker_body_gard as $item) {

                $user_role = $item->get_role();
                if (in_array($user_role->id, $filter_role)) {

                    if (!$safe) {
                        $footer[$body_gard->getUserId()][] = 'شما از خود دربرابر یک حمله دفاع کردید .';
                        $safe = true;
                    }
                    $selector->delete($user_role->id);
                    if ($user_role->id == ROLE_Godfather) {
                        $item = $selector->getUser($server->who_is_shot());
                    }
                    $footer[$item->getUserId()][] = 'حمله شما موفقیت آمیز نبود.';
                    $selector->delete($user_role->id);

                }

            }
            $selector->set($body_gard->getUserId(), ROLE_Bodygard, 'power');

        } elseif (count($list_attacker_body_gard) > 0) {

            $i = 0;
            foreach ($list_attacker_body_gard as $item) {

                $user_role = $item->get_role();

                if (in_array($user_role->id, $filter_role) && ($user_role->id != ROLE_Gorg || !$server->isFullMoon())) {

                    if ($user_role->id == ROLE_Godfather) {
                        $item = $selector->getUser($server->who_is_shot());
                    }
                    $dead_body[] = $item->kill()->getUserId();
                    $list_attacker[$item->getUserId()][] = [ROLE_Bodygard];
                    $selector->delete($item->getRoleId());
                    $i++;

                }
            }

            if ($i > 0) {

                $temp = $body_gard->get_name() . ' در حال دفاع کشته شد .' . "\n";
                $message .= $temp;
                $report .= $temp;

                add_server_meta($server->getId(), 'bodygard', 'use');

            }

        }

    }
}
// ║ Telefonchi
if ($server->role_exists(ROLE_TelefonChi)) {

    $selector->delete(ROLE_TelefonChi, 'dead-select');
    $selector->delete(ROLE_TelefonChi);

}
// ║ Jadogar
if ($server->role_exists(ROLE_Jadogar) && $select_keshish != 'on') {

    $select_jadogar = $selector->select(ROLE_Jadogar);
    $select_2_jadogar = $selector->select(ROLE_Jadogar, 'select-2');

    if ($select_jadogar->getUserId() > 0 && $select_2_jadogar->getUserId() > 0 && $select_keshish != 'on') {

        $filter_role = [ROLE_Sniper, ROLE_Godfather, ROLE_Mashooghe, ROLE_Gorg, ROLE_Killer, ROLE_HardFamia, ROLE_Tobchi];
        $targets = ['select', 'select-2', 'last-select'];

        foreach ($targets as $item) {

            $list_attacker_jadogar = $server->getListAttacker($select_jadogar->getUserId(), $item);
            $list_attacker_jadogar_2 = $server->getListAttacker($select_2_jadogar->getUserId(), $item);

            foreach ($list_attacker_jadogar as $item) {

                if (in_array($item->getRoleId(), $filter_role)) {

                    $selector->set($select_2_jadogar->getUserId(), $item->getRoleId());

                }

            }

            foreach ($list_attacker_jadogar_2 as $item) {

                if (in_array($item->getRoleId(), $filter_role)) {

                    $selector->set($select_jadogar->getUserId(), $item->getRoleId());

                }

            }

        }

        $jadogar = $selector->getUser(ROLE_Jadogar);
        if ($select_jadogar->is($jadogar) || $select_2_jadogar->is($jadogar)) {

            $selector->set($jadogar->getUserId(), ROLE_Jadogar, 'power');

        }

    }

}
// ║ Saghar
if ($server->role_exists(ROLE_Sagher) && $select_keshish != 'on') {

    $select_saghar = $selector->select(ROLE_Sagher, 'select-2', false);
    if ($select_saghar->getUserId() > 0 && !$select_saghar->dead()) {

        if (!$select_saghar->shield()) {
            if (!($city_safe == 'on' && $select_saghar->get_role()->group_id == 1)) {
                $dead_body[] = $select_saghar->kill()->getUserId();
                $list_attacker[$select_saghar->getUserId()][] = [ROLE_Sagher];
            }

        } else {

            $shield[] = [
                'user_id' => $select_saghar->getUserId(),
                'role' => [ROLE_Sagher]
            ];
            $select_saghar->unShield();

        }

    }

    $type = $selector->getInt()->select(ROLE_Sagher, 'type');
    $power_kill = $selector->select(ROLE_Sagher, 'power-kill', false);

    if ($type > 0) {

        $select_saghar = $selector->select(ROLE_Sagher);
        $power = unserialize($selector->getString()->select(ROLE_Sagher, 'power', false));
        if ((in_array($type, [0, 4, 5, 6, 9])) || $select_saghar->getUserId() > 0) {

            switch ($type) {

                case 1:

                    if ($select_saghar->getUserId() > 0) {

                        if (!$select_saghar->shield()) {
                            if (!($city_safe == 'on' && $select_saghar->get_role()->group_id == 1)) {
                                $dead_body[] = $select_saghar->kill()->getUserId();
                                $list_attacker[$select_saghar->getUserId()][] = [ROLE_Sagher];
                            }

                        } else {

                            $shield[] = [
                                'user_id' => $select_saghar->getUserId(),
                                'role' => [ROLE_Sagher]
                            ];
                            $select_saghar->unShield();

                        }

                        $power['magic-' . $type] = false;

                    }

                    break;

                case 2:

                    if ($select_saghar->getUserId() > 0) {

                        $role_id = $select_saghar->getRoleId();
                        $selector->delete($role_id);
                        $selector->delete($role_id, 'select-2');
                        $footer[$select_saghar->getUserId()][] = 'قابلیت شما توسط معجون از بین رفت .';

                        $power['magic-' . $type] = false;

                    }

                    break;

                case 3:

                    if ($select_saghar->getUserId() > 0) {

                        $selector->set($select_saghar->getUserId(), ROLE_Sagher, 'select-2');
                        $power['magic-' . $type] = false;

                    }

                    break;

                case 4:
                    if ($server->getCountCity() != $server->getCountDeadCity() and $city_safe == 'off') //&& !$city_safe
                    {

                        do {

                            $user_rand = $server->randomUser([], [4, 3, 2]);

                        } while ($user_rand->dead());
                        if (!$user_rand->shield()) {

                            $dead_body[] = $user_rand->kill()->getUserId();
                            $list_attacker[$user_rand->getUserId()][] = [ROLE_Sagher];

                        } else {

                            $shield[] = [
                                'user_id' => $user_rand->getUserId(),
                                'role' => [ROLE_Sagher]
                            ];
                            $user_rand->unShield();

                        }

                        $power['magic-' . $type] = false;

                    }

                    break;

                case 5:

                    if ($server->getCountTerror() != $server->getCountDeadTerror()) {

                        do {

                            $user_rand = $server->randomUser([], [4, 3, 1]);

                        } while ($user_rand->dead());

                        if (!$user_rand->shield()) {

                            $dead_body[] = $user_rand->kill()->getUserId();
                            $list_attacker[$user_rand->getUserId()][] = [ROLE_Sagher];

                        } else {

                            $shield[] = [
                                'user_id' => $user_rand->getUserId(),
                                'role' => [ROLE_Sagher]
                            ];
                            $user_rand->unShield();

                        }

                        $power['magic-' . $type] = false;

                    }

                    break;

                case 6:

                    $saghar = $selector->getUser(ROLE_Sagher);
                    $selector->set($saghar->getUserId(), ROLE_Sagher);

                    $targets = ['select', 'select-2', 'last-select'];

                    foreach ($targets as $target) {

                        $list_attacker_saghar = $server->getListAttacker($saghar->getUserId(), $target);
                        foreach ($list_attacker_saghar as $item) {

                            $role = $item->get_role();

                            if ($role->attacker == 1) {

                                $selector->delete($role->id, $target);

                            }

                        }

                    }

                    if ($selector->select(ROLE_Sagher, 'power-6', false)->getUserId() == 1) {
                        $selector->set(1, ROLE_Sagher, 'power-6');
                    } else {
                        $selector->delete(ROLE_Sagher, 'power-6');
                        $power['magic-' . $type] = false;
                    }

                    break;

                case 7:

                    $message .= '🧪 معجون افشاگر : نقش ' . $select_saghar->get_name() . ' ، ' . $select_saghar->get_role()->icon . ' است.' . "\n";

                    $power['magic-' . $type] = false;

                    break;

                case 8:

                    $selector->set($select_saghar->getUserId(), ROLE_Sagher, 'power-kill');
                    $power['magic-' . $type] = false;

                    break;

                case 9:

                    $user_rand = $server->randomUser([], [3, 4]);
                    while ($user_rand->dead())
                        $user_rand = $server->randomUser([], [3, 4]);

                    $selector->set($user_rand->getUserId(), ROLE_Sagher, 'regent');
                    $power['magic-' . $type] = false;

                    break;

            }

        }

        update_server_meta($server->getId(), 'power', serialize($power), ROLE_Sagher);

    }

    if ($power_kill->getUserId() > 0) {

        $message .= $power_kill->get_name() . ' توسط ساغر مسموم شد .';
        $dead_body[] = $power_kill->kill()->getUserId();
        $selector->delete(ROLE_Sagher, 'power-kill');

    }

}
if ($server->role_exists(ROLE_Ehdagar)) {

}
// ║ Killer Defence
if (empty($server->getMeta('killer')) && $server->role_exists(ROLE_Killer) && $select_keshish != 'on') {

    $list_attacker_killer = $server->getListAttacker($selector->getUser(ROLE_Killer)->getUserId());
    $i = 0;

    foreach ($list_attacker_killer as $item) {

        $user_role = $item->get_role();
        if ($user_role->group_id != 3 && $user_role->attacker == 1) {

            $selector->delete($user_role->id);
            $i++;

        }

    }

    if ($i > 0) {

        $server->updateMeta('killer', 'on');

    }

}
// ║ Doctor
if ($day == 1 && $select_keshish != 'on') {

    if ($server->role_exists(ROLE_Pezeshk)) {

        $select_doctor = $selector->select(ROLE_Pezeshk, 'select-2');
        $list_attacker_doctor = $server->getListAttacker($select_doctor->getUserId());
        foreach ($list_attacker_doctor as $item) {

            $user_role = $item->get_role();
            if ($user_role->attacker == 1 && $user_role->id != ROLE_HardFamia && $user_role->id != ROLE_Gorkan) {

                $selector->delete($user_role->id);
                $heal_doctor = true;
                $heal_doctor_user[] = $user_role->id;

            }

        }

        switch ($select_doctor->getRoleId()) {
            case ROLE_Zambi:

                $server->updateMeta('zambi', 'use');
                $footer[$selector->getUser(ROLE_Pezeshk)->getUserId()][] = 'زامبی توسط شما درمان شد.';
                $footer[$select_doctor->getUserId()][] = 'پزشک شما را درمان کرد ، شما میتوانید حمله کنید!';

                break;

        }

        unset($select_doctor, $list_attacker_doctor);

    }

    if ($server->role_exists(ROLE_Nonva)) {

        $nonva = $selector->getUser(ROLE_Nonva);
        $list_attacker_nonva = $server->getListAttacker($nonva->getUserId());
        if (count($list_attacker_nonva) > 0) {
            $i = 0;
            foreach ($list_attacker_nonva as $item) {

                $user_role = $item->get_role();
                if ($user_role->attacker == 1) {
                    $selector->delete($user_role->id);
                    $i++;
                }

            }

            if ($i > 0)
                $footer[$nonva->getUserId()][] = 'شما از یک حمله جان سالم به‌در بردید .';
        }

    }

}
// ║ Ashpaz Defence
if ($server->role_exists(ROLE_Ashpaz) && $select_keshish != 'on') {

    $ashpaz = $selector->getUser(ROLE_Ashpaz);

    if (!$ashpaz->dead()) {

        $list_attacker_ashpaz = $server->getListAttacker($ashpaz->getUserId());

        foreach ($list_attacker_ashpaz as $item) {
            if (
                !in_array(md5($item->getRoleId()), [
                    md5(ROLE_Bazpors),
                    md5(ROLE_HardFamia),
                    md5(ROLE_Gorkan),
                    md5(ROLE_ShahKosh),
                    md5(ROLE_Mohaghegh),
                    md5(ROLE_EynakSaz),
                    md5(ROLE_Nato),
                    md5(ROLE_MozakarehKonandeh),
                    md5(ROLE_Karagah),
                    md5(ROLE_TofangDar),
                    md5(ROLE_Yakoza),
                    md5(ROLE_Didban),
                    md5(ROLE_KhabarNegar),
                    md5(ROLE_Memar),
                    md5(ROLE_Framason),
                ])
            )
                $selector->delete($item->getRoleId());
        }

    }

}

// ╚══════ End Get Select Role ═════════════════════════════════════════════════════════════════════╝

// ╔══════ Run The Roles ═══════════════════════════════════════════════════════════════════════════╗
$heal_doctor = false;
$heal_doctor_user = [];

if ($select_keshish != 'on') {

    // ║ Doctor
    $select_doctor = $selector->user()->select(ROLE_Pezeshk);
    // ║ God Father
    $select_god_father = $selector->user()->select(ROLE_Godfather);
    // ║ Killer
    $select_killer = $selector->user()->select(ROLE_Killer);
    // ║ Sniper
    $select_sniper = $selector->user()->select(ROLE_Sniper);
    if ($select_sniper->getUserId() > 0 && $server->role_exists(ROLE_Kalantar) && $selector->user()->select(ROLE_Kalantar, 'power-select')->getUserId() <= 0 && !$selector->getUser(ROLE_Kalantar)->dead()) {
        $footer[$selector->getUser(ROLE_Sniper)->getUserId()][] = 'کلانتر از حمله شما جلوگیری کرد.';
        $select_sniper->setUserId(0);
    }
    // ║ bazpors
    $select_bazpors = $selector->user()->select(ROLE_Bazpors);
    // ║ Kar Agah
    $select_karagah = $selector->user()->select(ROLE_Karagah);
    // ║ Mashoghe
    $select_mashoghe = $selector->user()->select(ROLE_Mashooghe);
    if ($select_god_father instanceof User && $select_god_father->getUserId() <= 0) {
        $select_god_father = $select_mashoghe;
    }
    // ║ Nato
    $select_nato = $selector->user()->select(ROLE_Nato);
    // ║ Hacker
    $select_hacker = $selector->user()->select(ROLE_Hacker);
    // ║ Police
    $select_police = $selector->user()->select(ROLE_Police);
    // ║ Tohmat Zan
    $select_tohmat_zan = $selector->user()->select(ROLE_TohmatZan);
    // ║ Mohaghegh
    $select_mohaghegh = $selector->user()->select(ROLE_Mohaghegh);
    // ║ Hard Mafia
    $select_hard_mafia = $selector->user()->select(ROLE_HardFamia);
    $select_gorkan = $selector->user()->select(ROLE_Gorkan);
    // ║ Tofang Dar
    $select_tofang_dar = $selector->user()->select(ROLE_TofangDar);
    // ║ Bazmandeh
    $select_bazmondeh = $selector->user()->select(ROLE_Bazmandeh);
    // ║ Eynak Saz
    $select_eynak_saz = $selector->user()->select(ROLE_EynakSaz);
    // ║ Fereshteh
    $select_fereshteh = $selector->user()->select(ROLE_Fereshteh);
    // ║ Bad Doctor
    $select_bad_doctor = $selector->user()->select(ROLE_BAD_DOCTOR);
    // ║ Memar
    $select_memar = $selector->user()->select(ROLE_Memar);
    // ║ Tobchi
    $select_tobchi = $selector->user()->select(ROLE_Tobchi);
    // ║ Mozakereh Konandeh
    $select_mozakereh = $selector->user()->select(ROLE_MozakarehKonandeh);
    // ║ Kalantar
    $select_kalantar = $selector->user()->select(ROLE_Kalantar);
    // ║ Shab Khosb
    $select_shab_khost = $selector->user()->select(ROLE_ShabKhosb);
    // ║ Ahangar
    $select_ahangar = $selector->user()->select(ROLE_Ahangar);
    // ║ Bazmondeh
    if ($select_bazmondeh instanceof User && $select_bazmondeh->getUserId() > 0) {

        $server->setUserId($select_bazmondeh->getUserId())->updateMetaUser('shield', 'on');

    }

    if ($server->role_exists(ROLE_MineLayer)) {
        $select_mine_layer = $selector->user()->select(ROLE_MineLayer);

        // For each target, get their selections (three houses)
        if ($select_mine_layer instanceof User && $select_mine_layer->getUserId() > 0) {
            $mine_layer_target = $select_mine_layer;

            // Retrieve the three houses selected by the target to defuse the mine
            $select_mine_layer_1 = $selector->select(ROLE_MineLayer, 'select-2-0', $mine_layer_target->getUserId());
            $select_mine_layer_2 = $selector->select(ROLE_MineLayer, 'select-2-1', $mine_layer_target->getUserId());
            $select_mine_layer_3 = $selector->select(ROLE_MineLayer, 'select-2-2', $mine_layer_target->getUserId());

            // Retrieve the mine location set by the MineLayer
            $mine_location_obj = $selector->select(ROLE_MineLayer, 'select-3', $mine_layer_target->getUserId());
            $mine_location = $mine_location_obj ? $mine_location_obj->getUserId() : null;
        }
        if ($mine_layer_target instanceof User && $mine_layer_target->getUserId() > 0) {
            if ($select_mine_layer_1 && $select_mine_layer_2 && $select_mine_layer_3 && $mine_location) {
                // The target has made their selections and the mine location is set

                // Check if the target's selections include the mine location
                $selected_houses = [
                    $select_mine_layer_1->getUserId(),
                    $select_mine_layer_2->getUserId(),
                    $select_mine_layer_3->getUserId(),
                ];

                if (in_array($mine_location, $selected_houses)) {
                    // The target successfully defused the mine
                    $footer[$mine_layer_target->getUserId()][] = 'شما موفق به خنثی کردن مین شدید.';
                    $footer[$selector->getUser(ROLE_MineLayer)->getUserId()][] = $mine_layer_target->get_name() . ' موفق به خنثی کردن مین شما شد.';
                    $temp =  "بمب خنثی شد!" . "\n";
//                    __replace__($temp, ['[[minelayer]]' => $mine_layer_target->kill()->get_name()]);
                    $report .= $temp;
                    $message .= $temp;
                } else {
                    // The target failed to defuse the mine and is killed
                    $mine_layer_target->kill();
                    $dead_body[] = $mine_layer_target->getUserId();
                    $footer[$mine_layer_target->getUserId()][] = 'شما توسط مین‌گذار کشته شدید.';
                    $footer[$selector->getUser(ROLE_MineLayer)->getUserId()][] = $mine_layer_target->get_name() . ' توسط مین شما کشته شد.';
                    $temp =  "[[minelayer]] در بمب‌گذاری منفجر شد." . "\n";
                    __replace__($temp, ['[[minelayer]]' => $mine_layer_target->kill()->get_name()]);
                    $report .= $temp;
                    $message .= $temp;
                }

                // Clean up selections
                $selector->delete(ROLE_MineLayer);
                $selector->delete(ROLE_MineLayer, 'select-2-0', $mine_layer_target->getUserId());
                $selector->delete(ROLE_MineLayer, 'select-2-1', $mine_layer_target->getUserId());
                $selector->delete(ROLE_MineLayer, 'select-2-2', $mine_layer_target->getUserId());
                $selector->delete(ROLE_MineLayer, 'select-3', $mine_layer_target->getUserId());
            }
        } else {
            $footer[$select_mine_layer->getUserId()][] = 'مین گزار هیچ کسی را انتخاب نکرد';
        }
    }


    if ($server->role_exists(ROLE_MineLayerMafia)) {
        $select_mine_layer = $selector->user()->select(ROLE_MineLayerMafia);

        // For each target, get their selections (three houses)
        if ($select_mine_layer instanceof User && $select_mine_layer->getUserId() > 0) {
            $mine_layer_target = $select_mine_layer;

            // Retrieve the three houses selected by the target to defuse the mine
            $select_mine_layer_1 = $selector->select(ROLE_MineLayerMafia, 'select-3-0', $mine_layer_target->getUserId());
            $select_mine_layer_2 = $selector->select(ROLE_MineLayerMafia, 'select-3-1', $mine_layer_target->getUserId());
            $select_mine_layer_3 = $selector->select(ROLE_MineLayerMafia, 'select-3-2', $mine_layer_target->getUserId());

            // Retrieve the mine location set by the MineLayer
            $mine_location_obj = $selector->select(ROLE_MineLayerMafia, 'select-4', $mine_layer_target->getUserId());
            $mine_location = $mine_location_obj ? $mine_location_obj->getUserId() : null;
            error_log("$mine_location mine location");
            error_log($select_mine_layer_1->getUserId()  ."select_mine_layer_1");
            error_log($select_mine_layer_2->getUserId() . "select_mine_layer_2");
            error_log($select_mine_layer_3->getUserId() .  " select_mine_layer_3");


        }
        if ($mine_layer_target instanceof User && $mine_layer_target->getUserId() > 0) {
            $power = $selector->getInt()->select(ROLE_MineLayerMafia, 'mine' ,false) ?? 0;
            $selector->set($power - 1, ROLE_MineLayerMafia, 'mine');
            if ($select_mine_layer_1 && $select_mine_layer_2 && $select_mine_layer_3 && $mine_location) {
                // The target has made their selections and the mine location is set

                // Check if the target's selections include the mine location
                $selected_houses = [
                    $select_mine_layer_1->getUserId(),
                    $select_mine_layer_2->getUserId(),
                    $select_mine_layer_3->getUserId(),
                ];

                if (in_array($mine_location, $selected_houses)) {
                    // The target successfully defused the mine
                    $footer[$mine_layer_target->getUserId()][] = 'شما موفق به خنثی کردن مین شدید.';
                    $footer[$selector->getUser(ROLE_MineLayerMafia)->getUserId()][] = $mine_layer_target->get_name() . ' موفق به خنثی کردن مین شما شد.';
                    $temp =  "بمب خنثی شد!" . "\n";
//                    __replace__($temp, ['[[minelayer]]' => $mine_layer_target->kill()->get_name()]);
                    $report .= $temp;
                    $message .= $temp;
                } else {
                    // The target failed to defuse the mine and is killed
                    $mine_layer_target->kill();
                    $dead_body[] = $mine_layer_target->getUserId();
                    $footer[$mine_layer_target->getUserId()][] = 'شما توسط مین‌گذار کشته شدید.';
                    $footer[$selector->getUser(ROLE_MineLayerMafia)->getUserId()][] = $mine_layer_target->get_name() . ' توسط مین شما کشته شد.';
                    $temp =  "[[minelayer]] در بمب‌گذاری منفجر شد." . "\n";
                    __replace__($temp, ['[[minelayer]]' => $mine_layer_target->kill()->get_name()]);
                    $report .= $temp;
                    $message .= $temp;
                }

                // Clean up selections
                $selector->delete(ROLE_MineLayerMafia);
                $selector->delete(ROLE_MineLayerMafia, 'select-3-0', $mine_layer_target->getUserId());
                $selector->delete(ROLE_MineLayerMafia, 'select-3-1', $mine_layer_target->getUserId());
                $selector->delete(ROLE_MineLayerMafia, 'select-3-2', $mine_layer_target->getUserId());
                $selector->delete(ROLE_MineLayerMafia, 'select-4', $mine_layer_target->getUserId());
            }
        } else {
            $footer[$select_mine_layer->getUserId()][] = 'مین گزار هیچ کسی را انتخاب نکرد';
        }
    }

    // ║ Ahangar
    if ($select_ahangar->getUserId() > 0) {

        $server->setUserId($select_ahangar->getUserId())->updateMetaUser('shield', 'on');

        if ($selector->select(ROLE_Ahangar, 'last-select')->getUserId() <= 0)
            add_server_meta($server->getId(), 'last-select', $select_ahangar->getUserId(), ROLE_Ahangar);
        else
            add_server_meta($server->getId(), 'last-select-2', $select_ahangar->getUserId(), ROLE_Ahangar);

        $footer[$select_ahangar->getUserId()][] = '🛡 شما یک زره دریافت کردید';
        $selector->set($selector->getInt()->select(ROLE_Ahangar, 'select-count', false) + 1, ROLE_Ahangar, 'select-count');
    }
    // ║ Hazard
    if ($server->role_exists(ROLE_Hazard)) {

        $power = $selector->getInt()->select(ROLE_Hazard, 'warning', false);
        $hazard = $selector->getUser(ROLE_Hazard);

        if ($power < 4) {

            $power = $selector->getInt()->select(ROLE_Hazard, 'power');
            if ($power == 1) {

                $select_hazard = $selector->select(ROLE_Hazard, 'select-2');
                if ($select_hazard->getUserId() > 0) {
                    if (!($city_safe == 'on' && $select_hazard->get_role()->group_id == 1)) {
                        $dead_body[] = $select_hazard->kill()->getUserId();
                        $list_attacker[$select_hazard->getUserId()][] = [ROLE_Hazard];
                    }

                }

            }

            $heart = $selector->getInt()->select(ROLE_Hazard, 'heart', false);

            if ($heart > 0 && !$hazard->shield()) {

                $server->setUserId($hazard->getUserId())->updateMetaUser('shield', 'on');
                $selector->set($heart - 1, ROLE_Hazard, 'heart');

            }

        } elseif (!$hazard->dead()) {

            $dead_body[] = $hazard->kill()->getUserId();
            $temp = $hazard->get_name() . ' کشته شد.' . "\n";
            $message .= $temp;
            $report .= $temp;

        }
    }

    // ║ Hard Mafia
    if ($server->role_exists(ROLE_HardFamia)) {
        if ($select_hard_mafia instanceof User && $select_hard_mafia->getUserId() > 0) {

            $selector->set(intval($selector->getInt()->select(ROLE_HardFamia, 'power', false) + 1), ROLE_HardFamia, 'power');
            $select_god_father->setUserId(0);

            if (!($city_safe == 'on' && $select_hard_mafia->get_role()->group_id == 1)) {
                $list_attacker[$select_hard_mafia->getUserId()][] = [
                    ROLE_HardFamia,
                ];

                $dead_body[] = $select_hard_mafia->kill()->getUserId();
            }
            // if ($city_safe == 'on'  AND $select_hard_mafia->get_role()->group_id != 1) {

            // }

        }

    }
    // ║ Hard Mafia
    if ($server->role_exists(ROLE_Gorkan)) {
        if ($select_gorkan instanceof User && $select_gorkan->getUserId() > 0) {

            $selector->set(intval($selector->getInt()->select(ROLE_Gorkan, 'power', false) + 1), ROLE_Gorkan, 'power');
            $select_god_father->setUserId(0);

            if ($server->isFullMoon()) {
                $list_attacker[$select_gorkan->getUserId()][] = [
                    ROLE_Gorkan,
                ];

                $dead_body[] = $select_gorkan->kill()->getUserId();
            }

        }

    }

    // ║ TohmatZan
    if ($server->role_exists(ROLE_TohmatZan)) {

        $selector->delete(ROLE_TohmatZan, 'last-select');
        if ($select_tohmat_zan->getUserId() > 0) {

            if ($select_tohmat_zan->is($selector->getUser(ROLE_Memar)) && $select_memar->getUserId() > 0) {

                $temp = '[[memar]] در آوار کشته شد.' . "\n";
                __replace__($temp, ['[[memar]]' => $select_memar->kill()->get_name()]);
                $dead_body[] = $select_memar->getUserId();
                $message .= $temp;
                $report .= $temp;
                $select_memar->setUserId(0);

            }

            $selector->set($select_tohmat_zan->getUserId(), ROLE_TohmatZan, 'last-select');

        }

    }

    // ║ Tobchi
    if ($server->role_exists(ROLE_Tobchi)) {
        if ($select_tobchi instanceof User && $select_tobchi->getUserId() > 0) {

            $server->updateMeta('tobchi', 'use');
            $select_god_father->setUserId(0);
            /** @var string[] $tobchi_kill */
            $tobchi_kill = [];
            if (!($city_safe == 'on' && $select_tobchi->get_role()->group_id == 1)) {
                $tobchi_kill[] = $select_tobchi->get_name();
                $dead_body[] = $select_tobchi->kill()->getUserId();
            }

            $list_attacker_tobchi = $server->getListAttacker($select_tobchi->getUserId());

            $big_khan = false;
            if (count($list_attacker_tobchi) > 0) {

                foreach ($list_attacker_tobchi as $item) {

                    if ($item->get_role()->id != ROLE_Tobchi && !in_array($item->get_name(), $tobchi_kill)) {

                        switch ($item->getRoleId()) {

                            case ROLE_Godfather:

                                $item = $selector->getUser($server->get_priority(2)->id);

                                break;

                            case ROLE_Big_Khab:

                                $big_khan = true;

                                break;

                        }
                        if (!($city_safe == 'on' && $item->get_role()->group_id == 1)) {
                            $tobchi_kill[] = $item->get_name();
                            $dead_body[] = $item->kill()->getUserId();
                        }

                    }

                }

            }

            if (count($tobchi_kill) == 1) {

                $temp = '[[tobchi]] توسط توپ جنگی کشته شد.' . "\n";
                __replace__($temp, ['[[tobchi]]' => $tobchi_kill[0]]);

            } elseif (count($tobchi_kill) > 0) {

                $temp = '[[tobchi]] توسط توپ جنگی کشته شدند.' . "\n";
                __replace__($temp, ['[[tobchi]]' => implode(' و ', $tobchi_kill)]);

            }

            if ($big_khan && $city_safe == 'off') {
                $temp .= '➖ قاتل : ' . '<u>' . $selector->getUser(ROLE_Tobchi)->get_name() . '</u>';
            }

            $message .= $temp;
            $report .= $temp;
            if ($select_tobchi->is($select_memar)) {

                $topchi = $selector->getUser(ROLE_Tobchi)->kill();
                $temp = '[[memar]] در آوار کشته شد.' . "\n";
                __replace__($temp, ['[[memar]]' => $topchi->get_name()]);
                $dead_body[] = $topchi->getUserId();
                $message .= $temp;
                $report .= $temp;

            }

        }
    }

    // ║ Mozakereh Konandeh
    if ($server->role_exists(ROLE_MozakarehKonandeh)) {

        if ($select_mozakereh instanceof User && $select_mozakereh->getUserId() > 0) {

            $server->updateMeta('mozakereh', 'use');
            $select_god_father->setUserId(0);

            if (
                !in_array($select_mozakereh->getRoleId(), [
                    ROLE_Bodygard,
                    ROLE_EynakSaz,
                    ROLE_Mohaghegh,
                    ROLE_Pezeshk,
                    ROLE_Karagah,
                    ROLE_Bazpors,
                    ROLE_Senator,
                    ROLE_Framason,
                ])
            ) {

                $footer[$selector->getUser(ROLE_MozakarehKonandeh)->getUserId()][] = '<u>' . $select_mozakereh->get_name() . '</u>' . ' به تیم مافیا پیوست .';
                $footer[$select_mozakereh->changeRole(ROLE_Noche)->getUserId()][] = 'دیشب مافیا باهات مذاکره کرد و به مافیا پیوستی !';
                $message .= 'مافیا مذاکره کرده است!' . "\n";
                $report .= 'مافیا مذاکره کرده است!' . "\n";
                $server->setUserId(ROLE_MozakarehKonandeh)->updateMetaUser('mozakereh', $select_mozakereh->getUserId());

            } else {

                $footer[$selector->getUser(ROLE_MozakarehKonandeh)->getUserId()][] = 'مذاکره با ' . '<u>' . $select_mozakereh->get_name() . '</u>' . ' ناموفق بود .';

            }

        }

    }

    // ║ ShabKhosb
    if ($server->role_exists(ROLE_ShabKhosb)) {

        if ($select_shab_khost instanceof User && $select_shab_khost->getUserId() > 0) {

            $server->updateMeta('sleep', $select_shab_khost->getUserId());
            update_server_meta($server->getId(), 'power', (get_server_meta($server->getId(), 'power', ROLE_ShabKhosb) + 1), ROLE_ShabKhosb);
            $select_shab_khost->SendMessage('😴 شما به خواب عمیق فرو رفتید .');
            update_server_meta($server->getId(), 'last-user', $select_shab_khost->getUserId(), ROLE_ShabKhosb);

        }

    }

    // ║ Gorg
    if ($server->role_exists(ROLE_Gorg)) {

        $filter_role = [
            ROLE_Gorg,
            ROLE_TofangDar,
            ROLE_EynakSaz,
            ROLE_Nato,
            ROLE_Karagah,
            ROLE_Kalantar,
            ROLE_Pezeshk,
            ROLE_Naghel
        ];
        $select_gorg = $selector->user()->select(ROLE_Gorg);
        $gorg = $selector->getUser(ROLE_Gorg);

        if (!$gorg->in_prisoner() && $day > 1) {

            if ($select_gorg instanceof User && $select_gorg->getUserId() > 0 && !$gorg->dead()) {

                if (!$server->isFullMoon()) {

                    if (!$select_gorg->is($select_bazpors)) {

                        if (!$select_gorg->is($select_doctor)) {

                            if ($select_gorg->getRoleId() == ROLE_Shield && !is_server_meta($server->getId(), 'heart-shield')) {

                                $footer[$select_gorg->getUserId()][] = 'شما از یک حمله جان سالم به‌در بردید ، شما زخمی هستید.';
                                $server->updateMeta('heart-shield', 1);

                            }
							else if($select_gorg->getRoleId() == ROLE_Shahzadeh && !is_server_meta($server->getId(), 'heart-shahzadeh'))
							{
								$shahzadeh_selfsend = true;
								$footer[$select_gorg->getUserId()][] = 'شما از یک حمله جان سالم به‌در بردید ، شما زخمی هستید.';
                                $server->updateMeta('heart-shahzadeh', 1);
								$temp = "در اتفاقی عجیب فردی از مرگ نجات یافت. 
";
								$message .= $temp;
                                $report .= $temp;
							}
							else {

                                if ($select_police instanceof User && $select_police->getUserId() > 0 && $select_police->is($select_gorg)) {

                                    if (!$gorg->dead()) {

                                        $dead_body[] = $gorg->kill()->getUserId();
                                        $temp = '[[police]] توسط پلیس کشته شد.' . "\n";
                                        __replace__($temp, ['[[police]]' => $gorg->get_name()]);
                                        $message .= $temp;
                                        $report .= $temp;

                                    }

                                    $selector->delete(ROLE_Police);
                                } else {

                                    if ($select_memar instanceof User && $select_memar->getUserId() > 0 && $select_gorg->is($select_memar)) {

                                        $temp = '[[memar]] در آوار کشته شد.' . "\n";
                                        __replace__($temp, ['[[memar]]' => $gorg->kill()->get_name()]);
                                        $dead_body[] = $gorg->getUserId();
                                        $message .= $temp;
                                        $report .= $temp;

                                    } else {

                                        if (!$select_gorg->is($select_bad_doctor)) {

                                            if (!$select_gorg->shield()) {
                                                if (!($city_safe == 'on' && $select_gorg->get_role()->group_id == 1)) {
                                                    $dead_body[] = $select_gorg->kill()->getUserId();
                                                    $list_attacker[$select_gorg->getUserId()][] = [ROLE_Gorg];
                                                }

                                            } else {

                                                $shield[] = [
                                                    'user_id' => $select_gorg->getUserId(),
                                                    'role' => [ROLE_Gorg]
                                                ];
                                                $select_gorg->unShield();

                                            }

                                        } else {

                                            $footer[$select_bad_doctor->getUserId()][] = 'لکتر شما را از یک حمله نجات داد.';
                                            $footer[$selector->getUser(ROLE_BAD_DOCTOR)->getUserId()][] = 'شما ' . $select_bad_doctor->get_name() . ' را از یک حمله نجات دادید .';

                                        }

                                    }

                                }

                            }

                        } else {

                            $heal_doctor = true;
                            $heal_doctor_user[] = ROLE_Gorg;

                        }

                    } else {

                        $footer[$select_bazpors->getUserId()][] = 'دیشب یک نفر سعی در کشتن شما داشت.';

                    }

                } else {

                    $user_role = $select_gorg->get_role();

                    if ($user_role->id == ROLE_Shield && !is_server_meta($server->getId(), 'heart-shield')) {

                        $footer[$user_role->id][] = 'شما از یک حمله جان سالم به‌در بردید ، شما زخمی هستید.';
                        $server->updateMeta('heart-shield', 1);

                    }
					else if($user_role->id == ROLE_Shahzadeh && !is_server_meta($server->getId(), 'heart-shahzadeh'))
					{
						$shahzadeh_selfsend = true;
						$footer[$user_role->id][] = 'شما از یک حمله جان سالم به‌در بردید ، شما زخمی هستید.';
                        $server->updateMeta('heart-shahzadeh', 1);
						$temp = "در اتفاقی عجیب فردی از مرگ نجات یافت. 
";
						$message .= $temp;
                        $report .= $temp;
					}
					elseif ($user_role->id == ROLE_Police && $select_police->getUserId() > 0) {
                        $dead_body[] = $gorg->kill()->getUserId();
                        $temp = '[[police]] توسط پلیس کشته شد.' . "\n";
                        __replace__($temp, ['[[police]]' => $gorg->get_name()]);
                        $message .= $temp;
                        $report .= $temp;
                    } elseif (!$select_gorg->shield()) {

                        if (!($city_safe == 'on' && $select_gorg->get_role()->group_id == 1)) {
                            $dead_body[] = $select_gorg->kill()->getUserId();
                            $list_attacker[$select_gorg->getUserId()][] = [ROLE_Gorg];
                        }
                    } else {

                        $shield[] = [
                            'user_id' => $select_gorg->getUserId(),
                            'role' => [ROLE_Gorg]
                        ];
                        $select_gorg->unShield();

                    }

                    $list_attacker_gorg = $server->getListAttacker($select_gorg->getUserId());
                    if (count($list_attacker_gorg) > 0) {

                        foreach ($list_attacker_gorg as $item) {
                            $user_role = $item->get_role();

                            if (!in_array($user_role->id, $filter_role)) {

                                if ($user_role->id == ROLE_Shield && !is_server_meta($server->getId(), 'heart-shield')) {

                                    $footer[$item->getUserId()][] = 'شما از یک حمله جان سالم به‌در بردید ، شما زخمی هستید.';
                                    $server->updateMeta('heart-shield', 1);

                                }
								else if($user_role->id == ROLE_Shahzadeh && !is_server_meta($server->getId(), 'heart-shahzadeh'))
								{
									$shahzadeh_selfsend = true;
									$footer[$user_role->id][] = 'شما از یک حمله جان سالم به‌در بردید ، شما زخمی هستید.';
									$server->updateMeta('heart-shahzadeh', 1);
									$temp = "در اتفاقی عجیب فردی از مرگ نجات یافت. 
";
									$message .= $temp;
									$report .= $temp;
								}
								elseif ($user_role->id == ROLE_Police && $select_police->getUserId() > 0) {
                                    $dead_body[] = $gorg->kill()->getUserId();
                                    $temp = '[[police]] توسط پلیس کشته شد.' . "\n";
                                    __replace__($temp, ['[[police]]' => $gorg->get_name()]);
                                    $message .= $temp;
                                    $report .= $temp;
                                } else {

                                    if (!$item->shield()) {
                                        if (!($city_safe == 'on' && $item->get_role()->group_id == 1)) {
                                            $dead_body[] = $item->kill()->getUserId();
                                            $list_attacker[$item->getUserId()][] = [ROLE_Gorg];
                                        }
                                    } else {

                                        $shield[] = [
                                            'user_id' => $item->getUserId(),
                                            'role' => [ROLE_Gorg]
                                        ];
                                        $item->unShield();

                                    }

                                }

                            }

                        }

                    }

                }

            } elseif (!$gorg->dead() && $server->isFullMoon()) {

                $list_attacker_gorg = $server->getListAttacker($gorg->getUserId());
                if (count($list_attacker_gorg) > 0) {

                    foreach ($list_attacker_gorg as $item) {

                        $user_role = $item->get_role();
                        if (!in_array($user_role->id, $filter_role) && $user_role->group_id != 3) {

                            if (!$item->shield()) {

                                if ($user_role->id == ROLE_Godfather) {

                                    $item = $selector->getUser($server->get_priority()->id);

                                } elseif ($user_role->id == ROLE_Memar && $select_memar->is($select_gorg)) {
                                    $temp = '[[memar]] در آوار کشته شد.' . "\n";
                                    __replace__($temp, ['[[memar]]' => $gorg->kill()->get_name()]);
                                    $dead_body[] = $gorg->getUserId();
                                    $message .= $temp;
                                    $report .= $temp;

                                }

                                $dead_body[] = $item->kill()->getUserId();
                                $list_attacker[$item->getUserId()][] = [ROLE_Gorg];
                                $footer[$gorg->getUserId()][] = "<u>" . $item->get_name() . "</u>" . ' قصد حمله به شما را داشت که به فنا رفت.';

                            } else {

                                $shield[] = [
                                    'user_id' => $item->getUserId(),
                                    'role' => [ROLE_Gorg]
                                ];
                                $item->unShield();

                            }
                        }

                    }

                }

            }

        }

        $selector->delete(ROLE_Gorg);

    }

    // ║ Khabar Negar
    if ($server->role_exists(ROLE_KhabarNegar)) {

        $select_khabar_negar = $selector->user()->select(ROLE_KhabarNegar);
        $khabar_negar = $selector->getUser(ROLE_KhabarNegar);

        if ($select_khabar_negar->getUserId() > 0) {

            if (is_server_meta($server->getId(), 'mozakereh', ROLE_MozakarehKonandeh)) {

                if ($select_khabar_negar->is(get_server_meta($server->getId(), 'mozakereh', ROLE_MozakarehKonandeh))) {

                    $footer[$khabar_negar->getUserId()][] = 'با ' . "<u>" . $select_khabar_negar->get_name() . "</u>" . ' مذاکره <b>شده</b> است .';

                } else {

                    $footer[$khabar_negar->getUserId()][] = 'با ' . "<u>" . $select_khabar_negar->get_name() . "</u>" . ' مذاکره <b>نشده</b> است .';

                }

            } else {

                $user_attacked = $selector->getUser($select_khabar_negar->getRoleId());
                $select_user_attacked = $selector->user()->select($user_attacked->getRoleId());
                $filter_role = [ROLE_Naghel];

                if ($select_user_attacked->getUserId() > 0 && !in_array($select_khabar_negar->getRoleId(), $filter_role)) {

                    if (!$select_khabar_negar->is($select_user_attacked)) {
                        $footer[$khabar_negar->getUserId()][] = '<u>' . $user_attacked->get_name() . '</u>' . ' به خانه ' . '<u>' . $select_user_attacked->get_name() . '</u>' . ' رفت.';
                    } else {
                        $footer[$khabar_negar->getUserId()][] = '<u>' . $user_attacked->get_name() . '</u>' . ' دیشب از خودش دفاع کرد .';
                    }

                } else {

                    $footer[$khabar_negar->getUserId()][] = '<u>' . $user_attacked->get_name() . '</u>' . ' هیچکاری نکرد.';

                }
            }

        }
    }

    // ║ Doctor | Killer | God Father
    if ($select_doctor instanceof User && $select_doctor->getUserId() > 0) {

        if ($select_god_father instanceof User && !$select_doctor->is($select_god_father)) {

            if (!$select_god_father->is($select_bazpors)) {

                if ($select_bad_doctor instanceof User && $select_bad_doctor->getUserId() > 0 && $select_god_father->is($select_bad_doctor)) {

                    $footer[$select_bad_doctor->getUserId()][] = 'لکتر شما را از یک حمله نجات داد.';
                    $footer[$selector->getUser(ROLE_BAD_DOCTOR)->getUserId()][] = 'شما ' . $select_bad_doctor->get_name() . ' را از یک حمله نجات دادید .';

                } else {

                    if ($select_police instanceof User && $select_police->getUserId() > 0 && $select_god_father->is($select_police)) {

                        $mashoghe = $selector->getUser(ROLE_Mashooghe);
                        if ($server->role_exists(ROLE_Mashooghe) && !$mashoghe->dead() && !$mashoghe->in_prisoner()) {

                            $dead_body[] = $mashoghe->kill()->getUserId();
                            $temp = '[[police]] توسط پلیس کشته شد.' . "\n";
                            __replace__($temp, ['[[police]]' => $mashoghe->get_name()]);
                            $message .= $temp;
                            $report .= $temp;

                        } else {

                            $user_shot = $selector->getUser($server->get_priority()->id);
                            $dead_body[] = $user_shot->kill()->getUserId();
                            $temp = '[[police]] توسط پلیس کشته شد.' . "\n";
                            __replace__($temp, ['[[police]]' => $user_shot->get_name()]);
                            $message .= $temp;
                            $report .= $temp;

                        }
                        $selector->delete(ROLE_Police);

                    } else {
                        if ($select_memar instanceof User && $select_memar->getUserId() > 0 && $select_god_father->is($select_memar)) {

                            $priority = $server->who_is_shot();
                            $user = $selector->getUser($priority);
                            $temp = '[[memar]] در آوار کشته شد.' . "\n";
                            __replace__($temp, ['[[memar]]' => $user->kill()->get_name()]);
                            $dead_body[] = $user->getUserId();
                            $message .= $temp;
                            $report .= $temp;

                        } else {

                            if ($gorg instanceof User && !$gorg->dead() && $select_god_father->is($gorg)) {
                                if (!$server->isFullMoon() && $select_gorg->getUserId() > 0) {

                                    $heart = (int)$selector->getInt()->select(ROLE_Gorg, 'heart');
                                    if ($heart === 1) {

                                        if (!$gorg->shield()) {

                                            $dead_body[] = $gorg->kill()->getUserId();
                                            $list_attacker[$gorg->getUserId()][] = [ROLE_Godfather];

                                        } else {

                                            $shield[] = [
                                                'user_id' => $gorg->getUserId(),
                                                'role' => [$server->who_is_shot()]
                                            ];
                                            $gorg->unShield();

                                        }

                                    } else {

                                        add_server_meta($server->getId(), 'heart', '1', ROLE_Gorg);

                                    }

                                }

                            } else {

                                if (
                                    $select_god_father->getRoleId() == ROLE_Shield && !is_server_meta($server->getId(), 'heart-shield')
                                ) {

                                    $footer[$select_god_father->getUserId()][] = 'شما از یک حمله جان سالم به‌در بردید ، شما زخمی هستید.';
                                    $server->updateMeta('heart-shield', 1);

                                }
								else if($select_god_father->getRoleId() == ROLE_Shahzadeh && !is_server_meta($server->getId(), 'heart-shahzadeh'))
								{
									$shahzadeh_selfsend = true;
									$footer[$select_god_father->getUserId()][] = 'شما از یک حمله جان سالم به‌در بردید ، شما زخمی هستید.';
									$server->updateMeta('heart-shahzadeh', 1);
									$temp = "در اتفاقی عجیب فردی از مرگ نجات یافت. 
";
									$message .= $temp;
									$report .= $temp;
								}
								else {

                                    if (!$select_god_father->shield() || $select_god_father->is($select_killer) || $select_god_father->is($select_sniper)) {
                                        if (!($city_safe == 'on' && $select_god_father->get_role()->group_id == 1)) {
                                            $dead_body[] = $select_god_father->kill()->getUserId();
											

                                            if ($select_god_father->is($select_killer)) {

                                                if ($select_god_father->is($select_sniper)) {

                                                    $list_attacker[$select_god_father->getUserId()][] = [
                                                        ROLE_Godfather,
                                                        ROLE_Killer,
                                                        ROLE_Sniper
                                                    ];

                                                } else {

                                                    $list_attacker[$select_god_father->getUserId()][] = [
                                                        ROLE_Godfather,
                                                        ROLE_Killer
                                                    ];

                                                }

                                            } elseif ($select_god_father->is($select_sniper)) {

                                                $list_attacker[$select_god_father->getUserId()][] = [
                                                    ROLE_Godfather,
                                                    ROLE_Sniper
                                                ];

                                            } else {

                                                $list_attacker[$select_god_father->getUserId()][] = [ROLE_Godfather];

                                            }
                                        }
                                    } else {

                                        if ($select_god_father->is($select_killer)) {

                                            if ($select_god_father->is($select_sniper)) {

                                                $shield[] = [
                                                    'user_id' => $select_god_father->getUserId(),
                                                    'role' => [
                                                        $server->who_is_shot(),
                                                        ROLE_Killer,
                                                        ROLE_Sniper,
                                                    ]
                                                ];

                                            } else {

                                                $shield[] = [
                                                    'user_id' => $select_god_father->getUserId(),
                                                    'role' => [
                                                        $server->who_is_shot(),
                                                        ROLE_Killer,
                                                    ]
                                                ];

                                            }

                                        } elseif ($select_god_father->is($select_sniper)) {

                                            $shield[] = [
                                                'user_id' => $select_god_father->getUserId(),
                                                'role' => [
                                                    $server->who_is_shot(),
                                                    ROLE_Sniper,
                                                ]
                                            ];

                                        } else {

                                            $shield[] = [
                                                'user_id' => $select_god_father->getUserId(),
                                                'role' => [$server->who_is_shot()]
                                            ];

                                        }

                                        $select_god_father->unShield();

                                    }

                                }

                            }

                        }

                    }

                }

            } else {

                if (count($footer[$select_bazpors->getUserId()]) == 0) {
                    $footer[$select_bazpors->getUserId()][] = 'دیشب یک نفر سعی در کشتن شما داشت.';
                }

            }

        } elseif ($select_god_father instanceof User && $select_god_father->getUserId() > 0) {

            $heal_doctor = true;
            $heal_doctor_user[] = ROLE_Godfather;

        }

        if ($selector->select(ROLE_Pezeshk, 'select')->is($selector->getUser(ROLE_Pezeshk)) || $selector->select(ROLE_Pezeshk, 'select-2')->is($selector->getUser(ROLE_Pezeshk))) {
            $server->setUserId(ROLE_Pezeshk)->updateMetaUser('doctor', $day);
        }

    } elseif ($select_god_father instanceof User && $select_god_father->getUserId() > 0) {

        if (!$select_god_father->is($select_bazpors)) {

            if ($select_bad_doctor instanceof User && $select_bad_doctor->getUserId() > 0 && $select_god_father->is($select_bad_doctor)) {

                $footer[$select_bad_doctor->getUserId()][] = 'لکتر شما را از یک حمله نجات داد.';
                $footer[$selector->getUser(ROLE_BAD_DOCTOR)->getUserId()][] = 'شما ' . $select_bad_doctor->get_name() . ' را از یک حمله نجات دادید .';

            } else {

                if ($select_police instanceof User && $select_police->getUserId() > 0 && $select_god_father->is($select_police)) {

                    $mashoghe = $selector->getUser(ROLE_Mashooghe);
                    if ($server->role_exists(ROLE_Mashooghe) && !$mashoghe->dead() && !$mashoghe->in_prisoner()) {

                        $dead_body[] = $mashoghe->kill()->getUserId();
                        $temp = '[[police]] توسط پلیس کشته شد.' . "\n";
                        __replace__($temp, ['[[police]]' => $mashoghe->get_name()]);
                        $message .= $temp;
                        $report .= $temp;

                    } else {

                        $user_shot = $selector->getUser($server->get_priority()->id);
                        $dead_body[] = $user_shot->kill()->getUserId();
                        $temp = '[[police]] توسط پلیس کشته شد.' . "\n";
                        __replace__($temp, ['[[police]]' => $user_shot->get_name()]);
                        $message .= $temp;
                        $report .= $temp;

                    }
                    $selector->delete(ROLE_Police);

                } else {
                    if ($select_memar instanceof User && $select_memar->getUserId() > 0 && $select_god_father->is($select_memar)) {

                        $priority = $server->who_is_shot();
                        $user = $selector->getUser($priority);
                        $temp = '[[memar]] در آوار کشته شد.' . "\n";
                        __replace__($temp, ['[[memar]]' => $user->kill()->get_name()]);
                        $dead_body[] = $user->getUserId();
                        $message .= $temp;
                        $report .= $temp;

                    } else {

                        if ($gorg instanceof User && !$gorg->dead() && $select_god_father->is($gorg)) {
                            if (!$server->isFullMoon() && $select_gorg->getUserId() > 0) {

                                $heart = (int)$selector->getInt()->select(ROLE_Gorg, 'heart');
                                if ($heart === 1) {

                                    if (!$gorg->shield()) {

                                        $dead_body[] = $gorg->kill()->getUserId();
                                        $list_attacker[$gorg->getUserId()][] = [ROLE_Godfather];

                                    } else {

                                        $shield[] = [
                                            'user_id' => $gorg->getUserId(),
                                            'role' => [$server->who_is_shot()]
                                        ];
                                        $gorg->unShield();

                                    }

                                } else {

                                    add_server_meta($server->getId(), 'heart', '1', ROLE_Gorg);

                                }

                            }

                        } else {

                            if (
                                $select_god_father->getRoleId() == ROLE_Shield && !is_server_meta($server->getId(), 'heart-shield')
                            ) {

                                $footer[$select_god_father->getUserId()][] = 'شما از یک حمله جان سالم به‌در بردید ، شما زخمی هستید.';
                                $server->updateMeta('heart-shield', 1);

                            }
							else if($select_god_father->getRoleId() == ROLE_Shahzadeh && !is_server_meta($server->getId(), 'heart-shahzadeh'))
							{
								$shahzadeh_selfsend = true;
									$footer[$select_god_father->getUserId()][] = 'شما از یک حمله جان سالم به‌در بردید ، شما زخمی هستید.';
									$server->updateMeta('heart-shahzadeh', 1);
									$temp = "در اتفاقی عجیب فردی از مرگ نجات یافت. 
";
									$message .= $temp;
									$report .= $temp;
							}
							else {

                                if (!$select_god_father->shield() || $select_god_father->is($select_killer) || $select_god_father->is($select_sniper)) {
                                    if (!($city_safe == 'on' && $select_god_father->get_role()->group_id == 1)) {
                                        $dead_body[] = $select_god_father->kill()->getUserId();


                                        if ($select_god_father->is($select_killer)) {

                                            if ($select_god_father->is($select_sniper)) {

                                                $list_attacker[$select_god_father->getUserId()][] = [
                                                    ROLE_Godfather,
                                                    ROLE_Killer,
                                                    ROLE_Sniper
                                                ];

                                            } else {

                                                $list_attacker[$select_god_father->getUserId()][] = [
                                                    ROLE_Godfather,
                                                    ROLE_Killer
                                                ];

                                            }

                                        } elseif ($select_god_father->is($select_sniper)) {

                                            $list_attacker[$select_god_father->getUserId()][] = [
                                                ROLE_Godfather,
                                                ROLE_Sniper
                                            ];

                                        } else {

                                            $list_attacker[$select_god_father->getUserId()][] = [ROLE_Godfather];

                                        }
                                    }
                                } else {

                                    if ($select_god_father->is($select_killer)) {

                                        if ($select_god_father->is($select_sniper)) {

                                            $shield[] = [
                                                'user_id' => $select_god_father->getUserId(),
                                                'role' => [
                                                    $server->who_is_shot(),
                                                    ROLE_Killer,
                                                    ROLE_Sniper,
                                                ]
                                            ];

                                        } else {

                                            $shield[] = [
                                                'user_id' => $select_god_father->getUserId(),
                                                'role' => [
                                                    $server->who_is_shot(),
                                                    ROLE_Killer,
                                                ]
                                            ];

                                        }

                                    } elseif ($select_god_father->is($select_sniper)) {

                                        $shield[] = [
                                            'user_id' => $select_god_father->getUserId(),
                                            'role' => [$server->who_is_shot(), ROLE_Sniper]
                                        ];

                                    } else {

                                        $shield[] = [
                                            'user_id' => $select_god_father->getUserId(),
                                            'role' => [$server->who_is_shot()]
                                        ];

                                    }

                                    $select_god_father->unShield();

                                }

                            }

                        }

                    }
                }

            }
        } else {

            if (count($footer[$select_bazpors->getUserId()]) == 0) {
                $footer[$select_bazpors->getUserId()][] = 'دیشب یک نفر سعی در کشتن شما داشت.';
            }

        }

    }

    // ║ Killer
    if ($select_killer instanceof User && $select_killer->getUserId() > 0) {

        $killer = $selector->getUser(ROLE_Killer);

        if (!$select_killer->is($select_bazpors)) {

            if (!$select_killer->is($select_doctor)) {

                if ($select_police instanceof User && $select_police->getUserId() > 0 && $select_killer->is($select_police)) {

                    if (!$killer->dead()) {

                        $dead_body[] = $killer->kill()->getUserId();
                        $temp = '[[police]] توسط پلیس کشته شد.' . "\n";
                        __replace__($temp, ['[[police]]' => $killer->get_name()]);
                        $message .= $temp;
                        $report .= $temp;

                    }

                    $selector->delete(ROLE_Police);

                } else {
                    if ($select_memar instanceof User && $select_memar->getUserId() > 0 && $select_killer->is($select_memar)) {

                        $temp = '[[memar]] در آوار کشته شد.' . "\n";
                        __replace__($temp, ['[[memar]]' => $killer->kill()->get_name()]);
                        $dead_body[] = $killer->getUserId();
                        $message .= $temp;
                        $report .= $temp;

                    } else {

                        if ($gorg instanceof User && !$gorg->dead() && $select_killer->is($gorg)) {
                            if (!$server->isFullMoon() && $select_gorg->getUserId() > 0) {

                                $heart = (int)$selector->getInt()->select(ROLE_Gorg, 'heart');
                                if ($heart === 1) {

                                    if (!$gorg->shield()) {

                                        $dead_body[] = $gorg->kill()->getUserId();
                                        $list_attacker[$gorg->getUserId()][] = [ROLE_Killer];

                                    } else {

                                        $shield[] = [
                                            'user_id' => $gorg->getUserId(),
                                            'role' => [$server->who_is_shot()]
                                        ];
                                        $gorg->unShield();

                                    }

                                } else {

                                    add_server_meta($server->getId(), 'heart', '1', ROLE_Gorg);

                                }

                            }

                        } else {

                            if (!$select_killer->is($select_god_father)) {

                                if ($select_bad_doctor instanceof User && $select_bad_doctor->getUserId() > 0 && $select_killer->is($select_bad_doctor)) {

                                    $footer[$select_bad_doctor->getUserId()][] = 'لکتر شما را از یک حمله نجات داد.';
                                    $footer[$selector->getUser(ROLE_BAD_DOCTOR)->getUserId()][] = 'شما ' . $select_bad_doctor->get_name() . ' را از یک حمله نجات دادید .';

                                } else {

                                    if ($select_memar instanceof User && $select_memar->getUserId() > 0 && $select_killer->is($select_memar)) {

                                        $temp = '[[memar]] در آوار کشته شد.' . "\n";
                                        __replace__($temp, ['[[memar]]' => $killer->kill()->get_name()]);
                                        $dead_body[] = $user->getUserId();
                                        $message .= $temp;
                                        $report .= $temp;

                                    } else {

                                        if (
                                            $select_killer->getRoleId() == ROLE_Shield && !is_server_meta($server->getId(), 'heart-shield')
                                        ) {

                                            $footer[$select_killer->getUserId()][] = 'شما از یک حمله جان سالم به‌در بردید ، شما زخمی هستید.';
                                            $server->updateMeta('heart-shield', 1);

                                        }
										else if($select_killer->getRoleId() == ROLE_Shahzadeh && !is_server_meta($server->getId(), 'heart-shahzadeh'))
								{
									$shahzadeh_selfsend = true;
									$footer[$select_killer->getUserId()][] = 'شما از یک حمله جان سالم به‌در بردید ، شما زخمی هستید.';
									$server->updateMeta('heart-shahzadeh', 1);
									$temp = "در اتفاقی عجیب فردی از مرگ نجات یافت. 
";
									$message .= $temp;
									$report .= $temp;
								}
										else {

                                            if (!$select_killer->shield()) {
                                                if (!($city_safe == 'on' && $select_killer->get_role()->group_id == 1)) {
                                                    $dead_body[] = $select_killer->kill('killed')->getUserId();
                                                    $list_attacker[$select_killer->getUserId()][] = [ROLE_Killer];
                                                }

                                            } else {

                                                $shield[] = [
                                                    'user_id' => $select_killer->unShield()->getUserId(),
                                                    'role' => [ROLE_Killer]
                                                ];

                                            }

                                        }

                                    }

                                }

                            }

                        }

                    }
                }

            } else {

                $heal_doctor = true;
                $heal_doctor_user[] = ROLE_Killer;

            }

        } else {

            if (count($footer[$select_bazpors->getUserId()]) == 0) {
                $footer[$select_bazpors->getUserId()][] = 'دیشب یک نفر سعی در کشتن شما داشت.';
            }

        }

        if ($server->getMeta('killer') == 'on') {

            $select_killer = $selector->select(ROLE_Killer, 'select-2');

            if ($select_killer->getUserId() > 0) {

                if (!$select_killer->is($select_bazpors)) {

                    if (!$select_killer->is($select_doctor)) {

                        if ($select_police instanceof User && $select_police->getUserId() > 0 && $select_killer->is($select_police)) {

                            if (!$killer->dead()) {

                                $dead_body[] = $killer->kill()->getUserId();
                                $temp = '[[police]] توسط پلیس کشته شد.' . "\n";
                                __replace__($temp, ['[[police]]' => $killer->get_name()]);
                                $message .= $temp;
                                $report .= $temp;

                            }

                            $selector->delete(ROLE_Police);

                        } else {
                            if ($select_memar instanceof User && $select_memar->getUserId() > 0 && $select_killer->is($select_memar)) {

                                $temp = '[[memar]] در آوار کشته شد.' . "\n";
                                __replace__($temp, ['[[memar]]' => $killer->kill()->get_name()]);
                                $dead_body[] = $killer->getUserId();
                                $message .= $temp;
                                $report .= $temp;

                            } else {

                                if ($gorg instanceof User && !$gorg->dead() && $select_killer->is($gorg)) {
                                    if (!$server->isFullMoon() && $select_gorg->getUserId() > 0) {

                                        $heart = (int)$selector->getInt()->select(ROLE_Gorg, 'heart');
                                        if ($heart === 1) {

                                            if (!$gorg->shield()) {

                                                $dead_body[] = $gorg->kill()->getUserId();
                                                $list_attacker[$gorg->getUserId()][] = [ROLE_Killer];

                                            } else {

                                                $shield[] = [
                                                    'user_id' => $gorg->getUserId(),
                                                    'role' => [$server->who_is_shot()]
                                                ];
                                                $gorg->unShield();

                                            }

                                        } else {

                                            add_server_meta($server->getId(), 'heart', '1', ROLE_Gorg);

                                        }

                                    }

                                } else {

                                    if (!$select_killer->is($select_god_father)) {

                                        if ($select_bad_doctor instanceof User && $select_bad_doctor->getUserId() > 0 && $select_killer->is($select_bad_doctor)) {

                                            $footer[$select_bad_doctor->getUserId()][] = 'لکتر شما را از یک حمله نجات داد.';
                                            $footer[$selector->getUser(ROLE_BAD_DOCTOR)->getUserId()][] = 'شما ' . $select_bad_doctor->get_name() . ' را از یک حمله نجات دادید .';

                                        } else {

                                            if ($select_memar instanceof User && $select_memar->getUserId() > 0 && $select_killer->is($select_memar)) {

                                                $temp = '[[memar]] در آوار کشته شد.' . "\n";
                                                __replace__($temp, ['[[memar]]' => $killer->kill()->get_name()]);
                                                $dead_body[] = $user->getUserId();
                                                $message .= $temp;
                                                $report .= $temp;

                                            } else {

                                                if (
                                                    $select_killer->getRoleId() == ROLE_Shield && !is_server_meta($server->getId(), 'heart-shield')
                                                ) {

                                                    $footer[$select_killer->getUserId()][] = 'شما از یک حمله جان سالم به‌در بردید ، شما زخمی هستید.';
                                                    $server->updateMeta('heart-shield', 1);

                                                }
												else if($select_killer->getRoleId() == ROLE_Shahzadeh && !is_server_meta($server->getId(), 'heart-shahzadeh'))
								{
									$shahzadeh_selfsend = true;
									$footer[$select_killer->getUserId()][] = 'شما از یک حمله جان سالم به‌در بردید ، شما زخمی هستید.';
									$server->updateMeta('heart-shahzadeh', 1);
									$temp = "در اتفاقی عجیب فردی از مرگ نجات یافت. 
";
									$message .= $temp;
									$report .= $temp;
								}
												else {

                                                    if (!$select_killer->shield()) {
                                                        if (!($city_safe == 'on' && $select_killer->get_role()->group_id == 1)) {
                                                            $dead_body[] = $select_killer->kill('killed')->getUserId();
                                                            $list_attacker[$select_killer->getUserId()][] = [ROLE_Killer];
                                                        }

                                                    } else {

                                                        $shield[] = [
                                                            'user_id' => $select_killer->unShield()->getUserId(),
                                                            'role' => [ROLE_Killer]
                                                        ];

                                                    }

                                                }

                                            }

                                        }

                                    }

                                }

                            }
                        }

                    } else {

                        $heal_doctor = true;
                        $heal_doctor_user[] = ROLE_Killer;

                    }

                } else {

                    if (count($footer[$select_bazpors->getUserId()]) == 0) {
                        $footer[$select_bazpors->getUserId()][] = 'دیشب یک نفر سعی در کشتن شما داشت.';
                    }

                }

            }

            $server->updateMeta('killer', 'use');

        }

    }

    // ║ Sniper
    if ($select_sniper instanceof User && $select_sniper->getUserId() > 0) {

        $god_father = $selector->getUser(ROLE_Godfather);
        $sniper = $selector->getUser(ROLE_Sniper);

        if ($sniper->getUserId() > 0) {

            if (!$select_sniper->is($select_bazpors)) {

                if (!$select_sniper->is($select_doctor)) {

                    if ($select_bad_doctor instanceof User && $select_bad_doctor->getUserId() > 0 && $select_sniper->is($select_bad_doctor)) {

                        $footer[$select_bad_doctor->getUserId()][] = 'لکتر شما را از یک حمله نجات داد.';
                        $footer[$selector->getUser(ROLE_BAD_DOCTOR)->getUserId()][] = 'شما ' . $select_bad_doctor->get_name() . ' را از یک حمله نجات دادید .';

                    } else {

                        if ($select_memar instanceof User && $select_memar->getUserId() > 0 && $select_sniper->is($select_memar)) {

                            $temp = '[[memar]] در آوار کشته شد.' . "\n";
                            __replace__($temp, ['[[memar]]' => $sniper->kill()->get_name()]);
                            $dead_body[] = $sniper->getUserId();
                            $message .= $temp;
                            $report .= $temp;

                        } else {

                            if ($select_police instanceof User && $select_police->getUserId() > 0 && $select_sniper->is($select_police)) {

                                $dead_body[] = $sniper->kill()->getUserId();
                                $temp = '[[police]] توسط پلیس کشته شد.' . "\n";
                                __replace__($temp, ['[[police]]' => $sniper->get_name()]);
                                $message .= $temp;
                                $report .= $temp;

                                $selector->delete(ROLE_Police);

                            } else {

                                if ($gorg instanceof User && !$gorg->dead() && $select_sniper->is($gorg)) {
                                    if (!$server->isFullMoon() && $select_gorg->getUserId() > 0) {

                                        $heart = (int)$selector->getInt()->select(ROLE_Gorg, 'heart');
                                        if ($heart === 1) {

                                            if (!$gorg->shield()) {

                                                $dead_body[] = $gorg->kill()->getUserId();
                                                $list_attacker[$gorg->getUserId()][] = [ROLE_Sniper];

                                            } else {

                                                $shield[] = [
                                                    'user_id' => $gorg->getUserId(),
                                                    'role' => [$server->who_is_shot()]
                                                ];
                                                $gorg->unShield();

                                            }

                                        } else {

                                            add_server_meta($server->getId(), 'heart', '1', ROLE_Gorg);

                                        }

                                    }

                                } else {

                                    if (
                                        $select_sniper->getRoleId() == ROLE_Shield && !is_server_meta($server->getId(), 'heart-shield')
                                    ) {

                                        $footer[$select_sniper->getUserId()][] = 'شما از یک حمله جان سالم به‌در بردید ، شما زخمی هستید.';
                                        $server->updateMeta('heart-shield', 1);

                                    }
									else if($select_sniper->getRoleId() == ROLE_Shahzadeh && !is_server_meta($server->getId(), 'heart-shahzadeh'))
								{
									$shahzadeh_selfsend = true;
									$footer[$select_sniper->getUserId()][] = 'شما از یک حمله جان سالم به‌در بردید ، شما زخمی هستید.';
									$server->updateMeta('heart-shahzadeh', 1);
									$temp = "در اتفاقی عجیب فردی از مرگ نجات یافت. 
";
									$message .= $temp;
									$report .= $temp;
								}
									else {

                                        if (!$select_sniper->shield()) {
                                            $user_role = $select_sniper->get_role();
                                            $kalantar = $selector->getUser(ROLE_Kalantar);
                                            if ($server->role_exists(ROLE_Kalantar) && $user_role->group_id == 1 && !$kalantar->dead()) {

                                                $message .= 'کلانتر به دلیل تصمیم اشتباه کشته شد .' . "\n";
                                                $report .= 'کلانتر به دلیل تصمیم اشتباه کشته شد .' . "\n";
                                                $kalantar->kill();
                                                $dead_body[] = $kalantar->getUserId();

                                            } else {


                                                $mustKillSelectSniper = true;
                                                // 
                                                if ($city_safe == 'off' && $user_role->group_id == 1 && $server->setUserId(ROLE_Sniper)->getMetaUser('sniper-warning') == 'use') {

                                                    $message .= 'اسنایپر دست به خودکشی زد.' . "\n";
                                                    $dead_body[] = $sniper->kill()->getUserId();

                                                    $mustKillSelectSniper = false;

                                                } elseif ($city_safe == 'off' && $user_role->group_id == 1) {

                                                    $server->setUserId(ROLE_Sniper)->updateMetaUser('sniper-warning', 'use');

                                                }


                                                if (!$select_sniper->is($select_god_father) && !$select_sniper->is($god_father)) {
                                                    if (!($city_safe == 'on' && $select_sniper->get_role()->group_id == 1)) {
                                                        if ($mustKillSelectSniper) {
                                                            $dead_body[] = $select_sniper->kill()->getUserId();
                                                            $list_attacker[$select_sniper->getUserId()][] = [ROLE_Sniper];
                                                        }
                                                    }

                                                }

                                            }

                                        } else {

                                            $shield[] = [
                                                'user_id' => $select_sniper->unShield()->getUserId(),
                                                'role' => [ROLE_Sniper]
                                            ];

                                        }

                                    }

                                }
                            }

                        }

                    }

                } else {

                    $heal_doctor = true;
                    $heal_doctor_user[] = ROLE_Sniper;

                }

            } else {

                if (count($footer[$select_bazpors->getUserId()]) == 0) {
                    $footer[$select_bazpors->getUserId()][] = 'دیشب یک نفر سعی در کشتن شما داشت.';
                }

            }
        }

    }

    // ║ Bazpors
    if ($select_bazpors instanceof User && $select_bazpors->getUserId() > 0) {

        $kill_bazpors = $selector->user()->select(ROLE_Bazpors, 'kill');
        if ($kill_bazpors->is($select_bazpors)) {
            // if (!($city_safe == 'on' && $select_bazpors->get_role()->group_id == 1)) {
            $dead_body[] = $select_bazpors->kill()->getUserId();

            $temp = '[[bazpors]] توسط بازپرس محکوم به اعدام شد.' . "\n";
            __replace__($temp, ['[[bazpors]]' => $select_bazpors->get_name()]);
            $message .= $temp;
            $report .= $temp;
            // }

            if ($select_bazpors->get_role()->group_id == 1) {

                $server->setUserId(ROLE_Bazpors)->updateMetaUser('status', 'no-body');

            }

        }

    } else {

        $server->setUserId(ROLE_Bazpors)->deleteMetaUser('kill');

    }

    // ║ Kar Agah
    if ($select_karagah instanceof User && $select_karagah->getUserId() > 0) {
        $user_role = $select_karagah->get_role();
        $filter_role = [ROLE_Ashpaz, ROLE_Joker, ROLE_Bazmandeh];

        if ($user_role->id == ROLE_Godfather) {

            if ($server->setUserId($select_karagah->getUserId())->getMetaUser('search') == 'yes') {

                $message_kar_agah = '[[user]] مشکوک است.';

            } else {

                $message_kar_agah = '[[user]] مشکوک نیست.';

            }

        } elseif ($select_tohmat_zan instanceof User && $select_tohmat_zan->getUserId() > 0 && $select_karagah->is($select_tohmat_zan)) {

            if ($select_tohmat_zan->get_role()->group_id == 2) {
                $message_kar_agah = '[[user]] مشکوک نیست.';
            } else {
                $message_kar_agah = '[[user]] مشکوک است.';
            }
        } elseif ($user_role->group_id >= 2 && !in_array($user_role->id, $filter_role)) {

            $message_kar_agah = '[[user]] مشکوک است.';

        } else {

            $message_kar_agah = '[[user]] مشکوک نیست.';

        }

        $server->setUserId($select_karagah->getUserId())->updateMetaUser('search', 'yes');
    }
    //eye select for Ehdagar :
    if ($server->role_exists(ROLE_Ehdagar)) {
        $day_key = $day - 2; // Since the action is for two days ago

        // Load the existing used_parts array
        // $used_parts = unserialize($server->setUserId(ROLE_Ehdagar)->getMetaUser('used_parts'));
        $selected_user_id = isset($used_parts[$day_key]['selected_user']) ? $used_parts[$day_key]['selected_user'] : null;

        if ($selected_user_id) {
            $selected_user = new User($selected_user_id);
            $user_role = $selected_user->get_role();
            $filter_role_eye = [
                ROLE_Mashooghe,
                ROLE_Nato,
                ROLE_Hacker,
                ROLE_AfsonGar,
                ROLE_HardFamia,
                ROLE_Gorkan,
                ROLE_TohmatZan,
                ROLE_Noche,
                ROLE_MozakarehKonandeh,
                ROLE_BAD_DOCTOR,
                ROLE_Tobchi,
                ROLE_ShabKhosb,
                ROLE_Dalghak,
                ROLE_ShekarChi,
                ROLE_Dozd,
                ROLE_Yakoza,
                ROLE_Shayad,
                ROLE_ShahKosh,
                ROLE_Dam,
                ROLE_Terrorist
            ];
            if ($user_role->id == ROLE_Godfather) {
                $footer[$used_parts[$day_key]['reciever']][] = $selected_user->get_name() . ' مشکوک نیست.';
            } elseif (in_array($user_role->id, $filter_role_eye)) {
                $footer[$used_parts[$day_key]['reciever']][] = $selected_user->get_name() . ' مشکوک است.';
            } else {
                // Default case for other roles
                $footer[$used_parts[$day_key]['reciever']][] = $selected_user->get_name() . ' مشکوک نیست.';
            }
        }
    }
    // ║ Police
    if ($select_police instanceof User && $select_police->getUserId() > 0) {

        $police_count = $selector->getInt()->select(ROLE_Police, 'police-count', false);
        $server->setUserId(ROLE_Police)->updateMetaUser('police-count', $police_count + 1);

        // ║ Hacker|Police
        if ($select_police->is($select_hacker)) {

            $hacker = $selector->getUser(ROLE_Hacker)->kill();
            $dead_body[] = $hacker->getUserId();
            $temp = '[[police]] توسط پلیس کشته شد.' . "\n";
            __replace__($temp, ['[[police]]' => $hacker->get_name()]);
            $message .= $temp;
            $report .= $temp;
            $select_hacker = 0;

        }
    }

    // ║ Tofang Dar
    if ($select_tofang_dar instanceof User && $select_tofang_dar->getUserId() > 0) {

        $type = (int)$server->setUserId(ROLE_TofangDar)->getMetaUser('type');
        $server->setUserId(ROLE_TofangDar)->deleteMetaUser('type');
        $select_user_attacked = new User($server->setUserId(ROLE_TofangDar)->getMetaUser('attacker') ?? 0, $server->getId());

        if ($type == 2) {
            $tir = (int)$server->getMetaUser('count');
            $server->updateMetaUser('count', $tir + 1);
        }

        if ($select_user_attacked->getUserId() > 0 && $type > 0) {

            $temp2 = '[[user]] ، [[user_2]] ' . 'را مورد هدف قرار داد ، فشنگ ' . ($type == 1 ? 'مشقی' : 'جنگی') . ' بود.' . "\n";

            if ($type == 2) {

                if (!$select_user_attacked->is($select_bazpors)) {

                    if (!$select_user_attacked->is($select_doctor)) {
                        if ($select_bad_doctor instanceof User && $select_bad_doctor->getUserId() > 0 && $select_user_attacked->is($select_bad_doctor)) {

                            $footer[$select_bad_doctor->getUserId()][] = 'لکتر شما را از یک حمله نجات داد.';
                            $footer[$selector->getUser(ROLE_BAD_DOCTOR)->getUserId()][] = 'شما ' . $select_bad_doctor->get_name() . ' را از یک حمله نجات دادید .';

                        } else {

                            if ($select_memar instanceof User && $select_memar->getUserId() > 0 && $select_user_attacked->is($select_memar)) {

                                $temp = '[[memar]] در آوار کشته شد.' . "\n";
                                __replace__($temp, ['[[memar]]' => $select_tofang_dar->kill()->get_name()]);
                                $dead_body[] = $select_tofang_dar->getUserId();
                                $message .= $temp;
                                $report .= $temp;

                            } else {

                                if ($gorg instanceof User && !$gorg->dead() && $select_user_attacked->is($gorg)) {
                                    if (!$server->isFullMoon() && $select_gorg->getUserId() > 0) {

                                        $heart = (int)$selector->getInt()->select(ROLE_Gorg, 'heart');
                                        if ($heart === 1) {

                                            if (!$gorg->shield()) {

                                                $dead_body[] = $gorg->kill()->getUserId();

                                            } else {

                                                $shield[] = [
                                                    'user_id' => $gorg->getUserId(),
                                                    'role' => [$select_tofang_dar->getRoleId()]
                                                ];
                                                $gorg->unShield();

                                            }

                                        } else {

                                            add_server_meta($server->getId(), 'heart', '1', ROLE_Gorg);

                                        }

                                    }

                                } else {

                                    if (
                                        $select_user_attacked->getRoleId() == ROLE_Shield && !is_server_meta($server->getId(), 'heart-shield')
                                    ) {

                                        $footer[$select_user_attacked->getUserId()][] = 'شما از یک حمله جان سالم به‌در بردید ، شما زخمی هستید.';
                                        $server->updateMeta('heart-shield', 1);

                                    }
									else if($select_user_attacked->getRoleId() == ROLE_Shahzadeh && !is_server_meta($server->getId(), 'heart-shahzadeh'))
								{
									$shahzadeh_selfsend = true;
									$footer[$select_user_attacked->getUserId()][] = 'شما از یک حمله جان سالم به‌در بردید ، شما زخمی هستید.';
									$server->updateMeta('heart-shahzadeh', 1);
									$temp = "در اتفاقی عجیب فردی از مرگ نجات یافت. 
";
									$message .= $temp;
									$report .= $temp;
								}
									else {

                                        if (!$select_user_attacked->shield()) {
                                            if (!($city_safe == 'on' && $select_user_attacked->get_role()->group_id == 1)) {

                                                $dead_body[] = $select_user_attacked->kill()->getUserId();
                                            }

                                        } else {

                                            $shield[] = [
                                                'user_id' => $select_user_attacked->unShield()->getUserId(),
                                                'role' => [$select_tofang_dar->get_role()->id]
                                            ];

                                        }

                                    }

                                }

                            }

                        }

                    } else {
                        $heal_doctor = true;
                        $heal_doctor_user[] = $select_tofang_dar->getRoleId();
                    }

                } else {

                    if (count($footer[$select_bazpors->getUserId()]) == 0) {
                        $footer[$select_bazpors->getUserId()][] = 'دیشب یک نفر سعی در کشتن شما داشت.';
                    }

                }

            }

            $report .= __replace__($temp2, [
                '[[user]]' => $select_tofang_dar->get_name(),
                '[[user_2]]' => $select_user_attacked->get_name()
            ]);

            $server->deleteMetaUser('attacker');

        } else {

            $temp2 = '[[user]] تفنگ داشت اما به کسی شلیک نکرد.' . "\n";
            $report .= __replace__($temp2, ['[[user]]' => $select_tofang_dar->get_name()]);

        }

        $message .= $temp2;
    }

    // ║ Fereshteh
    if ($select_fereshteh instanceof User && $select_fereshteh->getUserId() > 0 && $select_fereshteh->dead() && $select_fereshteh->is_user_in_game()) {

        $temp = '[[user]] زنده شد.' . "\n";

        $report .= __replace__($temp, [
            '[[user]]' => $select_fereshteh->get_name()
        ]);
        $message .= $temp;

        if ($select_tohmat_zan->getRoleId() == ROLE_Fereshteh) {
            $footer[$select_fereshteh->changeRole(ROLE_Noche)->getUserId()][] = 'شما توسط فرشته زنده شدید ، شما مافیا هستید .';
        } elseif ($select_fereshteh->get_role()->group_id == 2) {
            $footer[$select_fereshteh->getUserId()][] = 'شما توسط فرشته زنده شدید.';
        } else {
            $footer[$select_fereshteh->changeRole(ROLE_Shahrvand)->getUserId()][] = 'شما توسط فرشته زنده شدید.';
        }
        $server->setUserId($select_fereshteh->getUserId())->deleteMetaUser('status');
        add_server_meta($server->getId(), 'fereshteh', 'use', ROLE_Fereshteh);

    }

    // ║ God Father
    if ($server->setUserId(ROLE_Godfather)->getMetaUser('super-god-father') == 'on') {

        if ($select_god_father->getUserId() > 0) {

            $select_god_father = new User($server->getMetaUser('select-2') ?? 0, $server->getId());

            if ($select_god_father->getUserId() > 0 && $select_mozakereh->getUserId() <= 0) {

                if ($select_bad_doctor instanceof User && $select_bad_doctor->getUserId() > 0 && $select_god_father->is($select_bad_doctor)) {

                    $footer[$select_bad_doctor->getUserId()][] = 'لکتر شما را از یک حمله نجات داد.';
                    $footer[$selector->getUser(ROLE_BAD_DOCTOR)->getUserId()][] = 'شما ' . $select_bad_doctor->get_name() . ' را از یک حمله نجات دادید .';

                } else {

                    if ($select_god_father instanceof User && !$select_doctor->is($select_god_father)) {

                        if (!$select_god_father->is($select_bazpors)) {

                            if ($select_police instanceof User && $select_police->getUserId() > 0 && $select_god_father->is($select_police)) {

                                $mashoghe = $selector->getUser(ROLE_Mashooghe);
                                if ($server->role_exists(ROLE_Mashooghe) && !$mashoghe->dead() && !$mashoghe->in_prisoner()) {

                                    $dead_body[] = $mashoghe->kill()->getUserId();
                                    $temp = '[[police]] توسط پلیس کشته شد.' . "\n";
                                    __replace__($temp, ['[[police]]' => $mashoghe->get_name()]);
                                    $message .= $temp;
                                    $report .= $temp;

                                } else {

                                    $user_shot = $selector->getUser($server->get_priority()->id);
                                    $dead_body[] = $user_shot->kill()->getUserId();
                                    $temp = '[[police]] توسط پلیس کشته شد.' . "\n";
                                    __replace__($temp, ['[[police]]' => $user_shot->get_name()]);
                                    $message .= $temp;
                                    $report .= $temp;

                                }
                                $selector->delete(ROLE_Police);

                            } else {
                                if ($select_memar instanceof User && $select_memar->getUserId() > 0 && $select_god_father->is($select_memar)) {

                                    $priority = $server->who_is_shot();
                                    $user = $selector->getUser($priority);
                                    $temp = '[[memar]] در آوار کشته شد.' . "\n";
                                    __replace__($temp, ['[[memar]]' => $user->kill()->get_name()]);
                                    $dead_body[] = $user->getUserId();
                                    $message .= $temp;
                                    $report .= $temp;

                                } else {

                                    if ($gorg instanceof User && !$gorg->dead() && $select_god_father->is($gorg) && (!$server->isFullMoon() || $select_gorg->getUserId() > 0)) {

                                        $heart = (int)$selector->getInt()->select(ROLE_Gorg, 'heart');
                                        if ($heart === 1) {

                                            if (!$gorg->shield()) {

                                                $dead_body[] = $gorg->kill()->getUserId();
                                                $list_attacker[$gorg->getUserId()][] = [$server->get_priority()->id];

                                            } else {

                                                $shield[] = [
                                                    'user_id' => $gorg->getUserId(),
                                                    'role' => [$server->who_is_shot()]
                                                ];
                                                $gorg->unShield();

                                            }

                                        } else {

                                            add_server_meta($server->getId(), 'heart', '1', ROLE_Gorg);

                                        }

                                    } else {

                                        if (
                                            $select_god_father->getRoleId() == ROLE_Shield && !is_server_meta($server->getId(), 'heart-shield')
                                        ) {

                                            $footer[$select_god_father->getUserId()][] = 'شما از یک حمله جان سالم به‌در بردید ، شما زخمی هستید.';
                                            $server->updateMeta('heart-shield', 1);

                                        }
										else if($select_god_father->getRoleId() == ROLE_Shahzadeh && !is_server_meta($server->getId(), 'heart-shahzadeh'))
								{
									$shahzadeh_selfsend = true;
									$footer[$select_god_father->getUserId()][] = 'شما از یک حمله جان سالم به‌در بردید ، شما زخمی هستید.';
									$server->updateMeta('heart-shahzadeh', 1);
									$temp = "در اتفاقی عجیب فردی از مرگ نجات یافت. 
";
									$message .= $temp;
									$report .= $temp;
								}
										else {

                                            if (!$select_god_father->shield() || $select_god_father->is($select_killer) || $select_god_father->is($select_sniper)) {
                                                if (!($city_safe == 'on' && $select_god_father->get_role()->group_id == 1)) {
													if($select_god_father->getRoleId() == ROLE_Shahzadeh && is_server_meta($server->getId(), 'heart-shahzadeh'))
													{
														$random_mafia = get_random_mafia($server->getId());
														$dead_body[] = $server->setUserId($random_mafia)->kill()->getUserId();
														$report .= "یک عضو مافیا توسط شاهزاده کشته شد.";
													}
                                                    $dead_body[] = $select_god_father->kill()->getUserId();


                                                    if ($select_god_father->is($select_killer)) {

                                                        if ($select_god_father->is($select_sniper)) {

                                                            $list_attacker[$select_god_father->getUserId()][] = [
                                                                ROLE_Godfather,
                                                                ROLE_Killer,
                                                                ROLE_Sniper
                                                            ];

                                                        } else {

                                                            $list_attacker[$select_god_father->getUserId()][] = [
                                                                ROLE_Godfather,
                                                                ROLE_Killer
                                                            ];

                                                        }

                                                    } elseif ($select_god_father->is($select_sniper)) {

                                                        $list_attacker[$select_god_father->getUserId()][] = [
                                                            ROLE_Godfather,
                                                            ROLE_Sniper
                                                        ];

                                                    } else {

                                                        $list_attacker[$select_god_father->getUserId()][] = [ROLE_Godfather];

                                                    }
                                                }
                                            } else {

                                                if ($select_god_father->is($select_killer)) {

                                                    if ($select_god_father->is($select_sniper)) {

                                                        $shield[] = [
                                                            'user_id' => $select_god_father->getUserId(),
                                                            'role' => [
                                                                $server->who_is_shot(),
                                                                ROLE_Killer,
                                                                ROLE_Sniper,
                                                            ]
                                                        ];

                                                    } else {

                                                        $shield[] = [
                                                            'user_id' => $select_god_father->getUserId(),
                                                            'role' => [
                                                                $server->who_is_shot(),
                                                                ROLE_Killer,
                                                            ]
                                                        ];

                                                    }

                                                } elseif ($select_god_father->is($select_sniper)) {

                                                    $shield[] = [
                                                        'user_id' => $select_god_father->getUserId(),
                                                        'role' => [
                                                            $server->who_is_shot(),
                                                            ROLE_Sniper,
                                                        ]
                                                    ];

                                                } else {

                                                    $shield[] = [
                                                        'user_id' => $select_god_father->getUserId(),
                                                        'role' => [$server->who_is_shot()]
                                                    ];

                                                }

                                                $select_god_father->unShield();

                                            }

                                        }

                                    }

                                }

                            }

                        } else {

                            if (count($footer[$select_bazpors->getUserId()]) == 0) {
                                $footer[$select_bazpors->getUserId()][] = 'دیشب یک نفر سعی در کشتن شما داشت.';
                            }

                        }

                    } elseif ($select_god_father instanceof User && $select_god_father->getUserId() > 0) {

                        $heal_doctor = true;
                        $heal_doctor_user[] = ROLE_Godfather;

                    }

                }

            }

        }

        $server->setUserId(ROLE_Godfather)->deleteMetaUser('super-god-father')->deleteMetaUser('select-2');

    }

    // ║ Bazmondeh
    if ($select_bazmondeh instanceof User && $select_bazmondeh->getUserId() > 0) {

        $bazmandeh_shield = (int)$server->setUserId($select_bazmondeh->getUserId())->deleteMetaUser('shield')->getMetaUser('shield-2');
        $server->updateMetaUser('shield-2', $bazmandeh_shield + 1);

    }

    // ║ Zambi
    if ($server->role_exists(ROLE_Zambi)) {

        $zambi = $selector->getUser(ROLE_Zambi);

        if (!$zambi->dead()) {

            if ($server->getMeta('zambi') != 'use') {

                if ($select_doctor->is($zambi)) {

                    $server->updateMeta('zambi', 'use');
                    $footer[$selector->getUser(ROLE_Pezeshk)->getUserId()][] = 'زامبی توسط شما درمان شد.';
                    $footer[$zambi->getUserId()][] = 'پزشک شما را درمان کرد ، شما میتوانید حمله کنید!';

                } elseif ($day >= 3) {

                    $zambi->changeRole(ROLE_Noche);
                    $temp = 'خبر بد ، زامبی به مافیا تبدیل شد.' . "\n";
                    $message .= $temp;
                    $report .= $temp;

                }

            } else {

                $select_zambi = $selector->user()->select(ROLE_Zambi);

                if ($select_zambi->getUserId() > 0) {

                    $role_id = $select_zambi->get_role();
                    if ($role_id->group_id == 1) {

                        $filter_role = [ROLE_Mohaghegh, ROLE_Karagah, ROLE_EynakSaz];
                        if (!in_array($role_id->id, $filter_role)) {

                            $zambi->changeRole($role_id->id);
                            $select_zambi->changeRole(ROLE_Shahrvand);
                            $footer[$select_zambi->getUserId()][] = 'زامبی نقش شما را گرفت.';

                        }

                    } else {

                        $zambi->kill();
                        $dead_body[] = $zambi->getUserId();
                        $temp = 'خبر بد ، زامبی کشته شد.' . "\n";
                        $message .= $temp;
                        $report .= $temp;

                    }

                }

            }

        }

    }

    // ║ Bad Doctor
    if ($select_bad_doctor instanceof User && $select_bad_doctor->getUserId() > 0) {

        if ($select_bad_doctor->is($selector->getUser(ROLE_BAD_DOCTOR))) {

            $server->setUserId(ROLE_BAD_DOCTOR)->updateMetaUser('doctor', $day);

        }

    }

    // ║ Memar
    if ($select_memar instanceof User && $select_memar->getUserId() > 0) {

        $memar_count = (int)get_server_meta($server->getId(), 'select-count', ROLE_Memar);
        update_server_meta($server->getId(), 'select-count', $memar_count + 1, ROLE_Memar);
        if ($select_memar->is($selector->getUser(ROLE_Memar))) {

            $selector->set($select_memar->getUserId(), ROLE_Memar, 'power');

        }

    }

    // ║ Eynak Saz
    if ($select_eynak_saz instanceof User && $select_eynak_saz->getUserId() > 0) {

        $user_attacker = $selector->select(ROLE_EynakSaz, 'attacker');

        if ($user_attacker->getUserId() > 0) {

            $temp = '🔍 نقش [[user]] ( [[role]] ) است .';

            if ($select_tohmat_zan instanceof User && $select_tohmat_zan->getUserId() > 0 && $user_attacker->is($select_tohmat_zan)) {

                if ($user_attacker->get_role()->group_id == 1) {

                    $where = ' `role`.`id` != ' . $user_attacker->getRoleId() . ' AND `role`.`group_id` = 2 AND ';

                    $footer[$select_eynak_saz->getUserId()][] = __replace__($temp, [
                        '[[role]]' => get_random_role($server->getId(), $where)->icon,
                        '[[user]]' => $user_attacker->get_name()
                    ]);

                } else {
                    $where = ' `role`.`id` != ' . $user_attacker->getRoleId() . ' AND ( `role`.`group_id` = 2 OR `role`.`group_id` = 3 ) AND ';

                    $footer[$select_eynak_saz->getUserId()][] = __replace__($temp, [
                        '[[role]]' => get_random_role($server->getId(), $where)->icon,
                        '[[user]]' => $user_attacker->get_name()
                    ]);

                }

            } else {

                $footer[$select_eynak_saz->getUserId()][] = __replace__($temp, [
                    '[[role]]' => $user_attacker->get_role()->icon,
                    '[[user]]' => $user_attacker->get_name()
                ]);

            }

            $server->deleteMetaUser('attacker');

        }

        $eynak_saz = (int)get_server_meta($server->getId(), 'eynak', ROLE_EynakSaz);
        update_server_meta($server->getId(), 'eynak', $eynak_saz + 1, ROLE_EynakSaz);

    }

    // ║ Kalantar
    $selector->delete(ROLE_Kalantar, 'last-select');
    if ($server->role_exists(ROLE_Kalantar)) {

        if ($select_kalantar->getUserId() > 0) {

            $selector->set($selector->getInt()->select(ROLE_Kalantar, 'power', false) + 1, ROLE_Kalantar, 'power');
            $selector->set($select_kalantar->getUserId(), ROLE_Kalantar, 'last-select');
            $footer[$select_kalantar->getUserId()][] = 'کلانتر حق رای را از شما گرفت.';

        }

    }

    // ║ ShekarChi
    if ($server->role_exists(ROLE_ShekarChi)) {

        $select_shekar_chi = $selector->user()->select(ROLE_ShekarChi);
        if ($select_shekar_chi->getUserId() > 0) {

            $list_attacker_shekar_chi = $server->getListAttacker($select_shekar_chi->getUserId());
            if (count($list_attacker_shekar_chi) > 1) {

                while (count($list_attacker_shekar_chi) != 0) {

                    $rand = array_rand($list_attacker_shekar_chi);
                    if ($list_attacker_shekar_chi[$rand]->get_role()->group_id != 2 && !$list_attacker_shekar_chi[$rand]->is($select_shekar_chi) && !$list_attacker_shekar_chi[$rand]->dead()) {
                        if (!($city_safe == 'on' && $list_attacker_shekar_chi[$rand]->get_role()->group_id == 1)) {
                            $dead_body[] = $list_attacker_shekar_chi[$rand]->kill()->getUserId();
                            $list_attacker[$list_attacker_shekar_chi[$rand]->getUserId()][] = [ROLE_ShekarChi];
                        }
                        break;

                    } else {
                        unset($list_attacker_shekar_chi[$rand]);
                    }

                }

            }

            if ($select_shekar_chi->is($select_memar)) {

                $user = $selector->getUser(ROLE_ShekarChi);
                $temp = '[[memar]] در آوار کشته شد.' . "\n";
                __replace__($temp, ['[[memar]]' => $user->kill()->get_name()]);
                $dead_body[] = $user->getUserId();
                $message .= $temp;
                $report .= $temp;

            }

            $selector->set($select_shekar_chi->getUserId(), ROLE_ShekarChi, 'last-select');

        }

    }

    // ║ Ashpaz
    if ($server->role_exists(ROLE_Ashpaz)) {

        $last_select = $selector->user()->select(ROLE_Ashpaz, 'last-select', false);
        $select_ashpaz = $selector->user()->select(ROLE_Ashpaz);

        if ($day > 1 && $last_select->getUserId() > 0 && !$last_select->dead()) {

            if (!$select_doctor->is($last_select) && !$select_bad_doctor->is($last_select)) {

                if (!$last_select->shield()) {
                    if (!($city_safe == 'on' && $last_select->get_role()->group_id == 1)) {

                        $temp = $last_select->kill()->get_name() . ' توسط آشپز مسموم شد.' . "\n";
                        $dead_body[] = $last_select->getUserId();
                        $report .= $temp;
                        $message .= $temp;
                    }

                } else {
                    $shield[] = [
                        'user_id' => $last_select->unShield()->getUserId(),
                        'role' => [ROLE_Ashpaz]
                    ];
                }

            } else {
                if ($select_doctor->is($last_select)) {
                    $heal_doctor = true;
                    $heal_doctor_user[] = $last_select->getRoleId();
                }
            }
            $selector->delete(ROLE_Ashpaz, 'last-select');

        }

        if ($select_ashpaz->getUserId() > 0) {

            $selector->set($select_ashpaz->getUserId(), ROLE_Ashpaz, 'last-select');

        }

    }

    // ║ Ankabot
    if ($server->role_exists(ROLE_Ankabot)) {

        if ($server->getPeopleAlive() == 2) {

            $select_ankabot = $selector->user()->select(ROLE_Ankabot);
            if ($select_ankabot->getUserId() > 0) {
                if (!($city_safe == 'on' && $select_ankabot->get_role()->group_id == 1)) {
                    $list_attacker[$select_ankabot->getUserId()][] = [ROLE_Ankabot];
                    $dead_body[] = $select_ankabot->kill()->getUserId();
                }

            }

        }

    }

    // ║ Naghel
    if ($server->role_exists(ROLE_Naghel)) {

        $list_attacker_naghel = unserialize($selector->getString()->select(ROLE_Naghel, 'select', false));

        if ($list_attacker_naghel != false && count($list_attacker_naghel) > 0) {

            foreach ($list_attacker_naghel as $item) {
//                    SendMessage("6645079982",$item);
//                $select_naghel = new User(string_decode($item), $server->getId());
                $select_naghel = new User(string_decodeOld($item), $server->getId());
//                SendMessage("6645079982",string_decodeOld($item));
                if (!$select_naghel->is($select_doctor) && !$select_naghel->is($select_bad_doctor) && !$select_naghel->dead()) {

                    if ($select_naghel->get_role()->group_id == 2) {
                        if ($select_naghel->getRoleId() != ROLE_Nato) {
                            $select_naghel = $selector->getUser($server->who_is_shot());
                        }
                    }
                    if (!($city_safe == 'on' && $select_naghel->get_role()->group_id == 1)) {
                        $temp = $select_naghel->kill()->get_name() . ' به علت ابتلا به ویروس کشته شد.' . "\n";
                        $dead_body[] = $select_naghel->getUserId();
                        $message .= $temp;
                        $report .= $temp;
                    }

                }

            }
            $selector->delete(ROLE_Naghel);
        }

        $naghel = $selector->getUser(ROLE_Naghel);
        $filter_role = [ROLE_Naghel, ROLE_Sagher];

        $targets = ['select', 'select-2', 'last-select'];

        foreach ($targets as $target) {

            $list_attacker_naghel = $server->getListAttacker($naghel->getUserId(), $target);
            if (count($list_attacker_naghel) > 0) {

                $list_attacker_naghel_2 = [];
                foreach ($list_attacker_naghel as $item) {

                    if (!in_array($item->getRoleId(), $filter_role) && !in_array($item->encode(), $list_attacker_naghel_2)) {

                        $list_attacker_naghel_2[] = $item->encode();

                    }

                }
            }

        }

        if (count($list_attacker_naghel_2) > 0) {

            add_server_meta($server->getId(), 'select', serialize($list_attacker_naghel_2), ROLE_Naghel);

        }
    }

    // ║ Dozd
    if ($server->role_exists(ROLE_Dozd)) {

        $select_dozd = $selector->select(ROLE_Dozd, 'select-2');
        if ($select_dozd->getUserId() > 0) {

            if ($selector->select($select_dozd->getRoleId())->is($selector->getUser(ROLE_Dozd)->getUserId()))
                add_server_meta($server->getId(), $select_dozd->getRoleId(), 'use', ROLE_Dozd);;
            $selector->set($select_dozd->getUserId(), ROLE_Dozd, 'last-select');
            $selector->delete(ROLE_Dozd);

        }

    }

    // ║ Senator
    if ($server->role_exists(ROLE_Senator)) {

        $select_senator = $selector->getString()->select(ROLE_Senator);
        $arr_senator = empty($select_senator) ? [] : unserialize($select_senator);

        if (count($arr_senator) == 4 && ($select_shab_khost->getUserId() <= 0 || $select_shab_khost->getRoleId() != ROLE_Senator)) {

            $mafia_count = 0;
            $filter_role = [ROLE_Joker, ROLE_Bazmandeh, ROLE_Ashpaz];
            foreach ($arr_senator as $item) {

                $role = get_role_user_server($server->getId(), $item);
                if (!in_array($role->id, $filter_role)) {

                    if ($select_tohmat_zan->is($item) && $role->group_id == 1) {
                        $mafia_count++;
                    } elseif (!$select_tohmat_zan->is($item) && $role->group_id == 2 || $role->group_id == 3) {
                        $mafia_count++;
                    }

                }

            }

            $temp = 'وضعیت لیست ' . "<b>";
            switch ($mafia_count) {

                case 4:
                    $temp .= 'مخوف';
                    break;

                case 3:
                    $temp .= 'هایل';
                    break;

                case 2:
                    $temp .= 'مشکوک';
                    break;

                default:
                    $temp .= 'غیرمشکوک';
                    break;

            }
            $temp .= '</b>' . ' است';

            $footer[$selector->getUser(ROLE_Senator)->getUserId()][] = $temp;
            add_server_meta($server->getId(), 'senator', 'use');

        }

    }

    // ║ Yakoza
    if ($server->role_exists(ROLE_Yakoza)) {

        $filter_role = [ROLE_Karagah, ROLE_Mohaghegh, ROLE_EynakSaz, ROLE_Senator, ROLE_Bazpors, ROLE_Pezeshk, ROLE_Framason];
        $select_yakoza = $selector->select(ROLE_Yakoza);
        if ($select_yakoza->getUserId() > 0 && !$select_yakoza->dead() && !in_array($select_yakoza->getRoleId(), $filter_role)) {

            // if ($city_safe == 'on'  AND $selector->get_role()->group_id != 1) {
            $yakoza = $selector->getUser(ROLE_Yakoza);
            $dead_body[] = $yakoza->kill()->getUserId();

            $temp = $yakoza->get_name() . ' کشته شد.' . "\n";
            $message .= $temp;
            $report .= $temp;

            $footer[$select_yakoza->changeRole(ROLE_Noche)->getUserId()][] = 'شما جایگزین یاکوزا در تیم مافیا هستید.';

            add_server_meta($server->getId(), 'yakoza', 'use');
            // }
        }

    }

    // ║ Shah Kosh
    if ($server->role_exists(ROLE_ShahKosh)) {

        $filter_role = [ROLE_Karagah, ROLE_Mohaghegh, ROLE_EynakSaz, ROLE_Senator, ROLE_Bazpors, ROLE_Framason];
        $select_shah_kosh = $selector->select(ROLE_ShahKosh);

        if ($select_shah_kosh->getUserId() > 0) {

            $select_shah_kosh_role = $selector->select(ROLE_ShahKosh, 'select-role');

            if ($select_shah_kosh_role->getUserId() > 0) {

                if (!in_array($select_shah_kosh_role->getUserId(), $filter_role) && $select_shah_kosh_role->is($select_shah_kosh->getRoleId())) {

                    if (!$select_shah_kosh->shield()) {
                        if (!($city_safe == 'on' && $select_shah_kosh->get_role()->group_id == 1)) {
                            $temp = $select_shah_kosh->get_name() . ' توسط شاه‌کش به فنا رفت .' . "\n";
                            $dead_body[] = $select_shah_kosh->kill()->getUserId();
                            $message .= $temp;
                            $report .= $temp;
                        }

                    } else {

                        $shield[] = [
                            'user_id' => $select_shah_kosh->getUserId(),
                            'role' => [ROLE_ShahKosh]
                        ];
                        $select_shah_kosh->unShield();

                    }

                }

                $selector->set($selector->getInt()->select(ROLE_ShahKosh, 'power', false) + 1, ROLE_ShahKosh, 'power');

            }

        }

    }

    // ║ Gambeler
    if ($server->role_exists(ROLE_Gambeler)) {

        $gambeler = $selector->getUser(ROLE_Gambeler);

        $select_gambeler = $selector->select(ROLE_Gambeler);
        if ($select_gambeler->getUserId() > 0) {

            $select_gambeler_3 = $selector->select(ROLE_Gambeler, 'select-3');

            if ($select_gambeler_3->getUserId() > 0) {

                $select_gambeler_2 = $selector->select(ROLE_Gambeler, 'select-2');

                if ($select_gambeler_2->getUserId() > 0) {

                    $select = function (int $select) {
                        return $select == 1 ? 'قیچی ✌️' : ($select == 2 ? 'کاغذ ✋' : 'سنگ ✊');
                    };

                    $footer[$gambeler->getUserId()][] = 'حریف شما ( ' . $select($select_gambeler_2->getUserId()) . ' ) را انتخاب کرد.';
                    $footer[$select_gambeler->getUserId()][] = 'حریف شما ( ' . $select($select_gambeler_3->getUserId()) . ' ) را انتخاب کرد.';

                    $coin = 0;
                    if ($select_gambeler_3->is($select_gambeler_2)) {

                        $footer[$gambeler->getUserId()][] = 'بازی مساوی شد .';
                        $footer[$select_gambeler->getUserId()][] = 'بازی مساوی شد .';
                        $coin = 1;

                    } else {

                        for ($i = 0; $i <= 1; $i++) {

                            if ($i == 1) {
                                $temp = $select_gambeler_3->getUserId();
                                $select_gambeler_3->setUserId($select_gambeler_2->getUserId());
                                $select_gambeler_2->setUserId($temp);
                            }

                            if ($select_gambeler_2->is(1) && $select_gambeler_3->is(2) || $select_gambeler_2->is(2) && $select_gambeler_3->is(3) || $select_gambeler_2->is(3) && $select_gambeler_3->is(1)) {

                                $footer[$gambeler->getUserId()][] = ($i == 1 ? 'شما بازی را برنده شدید.' : 'شما بازی را باختید.');
                                $footer[$select_gambeler->getUserId()][] = ($i == 1 ? 'شما بازی را باختید.' : 'شما بازی را برنده شدید.');

                                if ($i == 1) {
                                    if (!($city_safe == 'on' && $select_gambeler->get_role()->group_id == 1)) {
                                        $dead_body[] = $select_gambeler->kill()->getUserId();
                                        $list_attacker[$select_gambeler->getUserId()][] = [ROLE_Gambeler];
                                    }

                                } else {
                                    $coin = 2;
                                }

                            }

                        }

                    }

                    if ($coin > 0) {

                        $power = $selector->getInt()->select(ROLE_Gambeler, 'power', false);
                        if ($power - $coin <= 0) {

                            $dead_body[] = $gambeler->kill()->getUserId();
                            $temp = $gambeler->get_name() . ' کشته شد .' . "\n";
                            $message .= $temp;
                            $report .= $temp;
                        } else {
                            $selector->set($power - $coin, ROLE_Gambeler, 'power');
                        }

                    }

                } else {
                    if (!($city_safe == 'on' && $select_gambeler->get_role()->group_id == 1)) {
                        $dead_body[] = $select_gambeler->kill()->getUserId();
                        $list_attacker[$select_gambeler->getUserId()][] = [ROLE_Gambeler];
                    }

                }

            } else {
                $footer[$select_gambeler->getUserId()][] = 'گمبر هیچ چیزی را انتخاب نکرد';
            }

        } else {
            $footer[$gambeler->getUserId()][] = 'خطایی در شناسایی حریف رخ داد';
        }

    }


    if ($server->role_exists(100000000)) {

        $minelayer = $selector->getUser(ROLE_MineLayer);

        $select_minelayer = $selector->select(ROLE_MineLayer);
        if ($select_minelayer->getUserId() > 0) {

            $select_minelayer_3 = $selector->select(ROLE_MineLayer, 'select-3');

            if ($select_minelayer_3->getUserId() > 0) {

                $select_minelayer_2 = $selector->select(ROLE_MineLayer, 'select-2');

                if ($select_minelayer_2->getUserId() > 0) {

                    $select = function (int $select) {
                        return $select == 1 ? 'قیچی ✌️' : ($select == 2 ? 'کاغذ ✋' : 'سنگ ✊');
                    };

                    $footer[$gambeler->getUserId()][] = 'حریف شما ( ' . $select($select_gambeler_2->getUserId()) . ' ) را انتخاب کرد.';
                    $footer[$select_gambeler->getUserId()][] = 'حریف شما ( ' . $select($select_gambeler_3->getUserId()) . ' ) را انتخاب کرد.';

                    $coin = 0;
                    if ($select_minelayer_3->is($select_minelayer_2)) {

                        $footer[$minelayer->getUserId()][] = 'مین خنثی شد.';
                        $footer[$select_minelayer_3->getUserId()][] = 'مین خنثی شد. شما برنده شدید.';
                        $coin = 1;

                    } else {

                        for ($i = 0; $i <= 1; $i++) {

                            if ($i == 1) {
                                $temp = $select_gambeler_3->getUserId();
                                $select_gambeler_3->setUserId($select_gambeler_2->getUserId());
                                $select_gambeler_2->setUserId($temp);
                            }

                            if ($select_gambeler_2->is(1) && $select_gambeler_3->is(2) || $select_gambeler_2->is(2) && $select_gambeler_3->is(3) || $select_gambeler_2->is(3) && $select_gambeler_3->is(1)) {

                                $footer[$gambeler->getUserId()][] = ($i == 1 ? 'شما بازی را برنده شدید.' : 'شما بازی را باختید.');
                                $footer[$select_gambeler->getUserId()][] = ($i == 1 ? 'شما بازی را باختید.' : 'شما بازی را برنده شدید.');

                                if ($i == 1) {
                                    if (!($city_safe == 'on' && $select_gambeler->get_role()->group_id == 1)) {
                                        $dead_body[] = $select_gambeler->kill()->getUserId();
                                        $list_attacker[$select_gambeler->getUserId()][] = [ROLE_Gambeler];
                                    }

                                } else {
                                    $coin = 2;
                                }

                            }

                        }

                    }

                    $mine = $selector->getInt()->select(ROLE_Gambeler, 'mine', false);
                    $selector->set($mine - 1, ROLE_Gambeler, 'mine');


//                } else {
//                    if (!($city_safe == 'on' && $select_gambeler->get_role()->group_id == 1)) {
//                        $dead_body[] = $select_gambeler->kill()->getUserId();
//                        $list_attacker[$select_gambeler->getUserId()][] = [ROLE_Gambeler];
//                    }
//
//                }

                } else {
                    $footer[$select_gambeler->getUserId()][] = 'مین گذار هیچ کسی را انتخاب نکرد';
                }

            } else {
                $footer[$gambeler->getUserId()][] = 'خطایی در شناسایی حریف رخ داد';
            }

        }

    }


    // ║ Framason
    if ($server->role_exists(ROLE_Framason)) {

        $select_framason = $selector->select(ROLE_Framason);

        if ($select_framason->getUserId() > 0) {

            $team_framason = unserialize($selector->getString()->select(ROLE_Framason, 'power', false));
            $framason = $selector->getUser(ROLE_Framason);
            $user_role = $select_framason->get_role();

            if ($user_role->group_id == 1) {

                if ($user_role->id != ROLE_Bazpors) {

                    if ($team_framason != false && count($team_framason) > 0) {
                        $team_framason[] = $select_framason->encode();
                    } else {
                        $team_framason = [
                            $framason->encode(),
                            $select_framason->encode()
                        ];
                    }
                    $footer[$framason->getUserId()][] = $select_framason->get_name() . ' به تیم ماسونی پیوست .';
                    if (!in_array($framason->encode(), $team_framason)) {
                        $team_framason[] = $framason->encode();
                    }
                    update_server_meta($server->getId(), 'power', serialize($team_framason), ROLE_Framason);

                }

            } else {

                if ($day == 1) {
                    $footer[$framason->getUserId()][] = 'هدف شما ماسون نیست .';
                } else {
                    if (!in_array($framason->encode(), $team_framason)) {
                        $team_framason[] = $framason->encode();
                    }
                    $temp = 'متاسفانه مرگ ماسونی در شهر رخ داد ! ' . "\n";
                    if ($select_framason != false && count($team_framason) > 0) {
                        $list_attacker_framason = [];
                        foreach ($team_framason as $item) {

                            $item = new User(string_decodeOld($item));
                            if (!$item->dead()) {
                                $list_attacker_framason[] = $item->kill()->get_name();
                                $dead_body[] = string_decodeOld($item);
                            }

                        }
                        $temp .= implode(' و ', $list_attacker_framason);
                        $temp .= ' کشته شدند .' . "\n";
                    } else {
                        $temp .= $framason->kill()->get_name() . ' کشته شد.' . "\n";
                    }
                    $message .= $temp;
                    $report .= $temp;
                }

            }

        }

    }

    // ║ Dam
    if ($server->role_exists(ROLE_Dam)) {

        $select_dam = $selector->select(ROLE_Dam, 'select');
        $select_2_dam = $selector->select(ROLE_Dam, 'select-2', false);
        $select_3_dam = $selector->select(ROLE_Dam, 'select-3', false);

        if ($select_dam->getUserId() > 0) {

            if ($select_2_dam->getUserId() <= 0) {
                $selector->set($select_dam->getUserId(), ROLE_Dam, 'select-2');
                $selector->set($day + 1, ROLE_Dam, 'power');
            } elseif ($select_3_dam->getUserId() <= 0) {
                $selector->set($select_dam->getUserId(), ROLE_Dam, 'select-3');
                $selector->set($day + 1, ROLE_Dam, 'power-2');
            }

            $power = intval($selector->getInt()->select(ROLE_Dam, 'power', false));
            if ($power < 2) {
                $selector->set($power + 1, ROLE_Dam, 'power');
            }

        }

        $power = $selector->getInt()->select(ROLE_Dam, 'power', false);

        if ($select_2_dam->getUserId() > 0 && $day == $power) {

            $i = 0;
            $list_attacker_dam = $server->getListAttacker($select_2_dam->getUserId());
            if (count($list_attacker_dam) > 0) {

                foreach ($list_attacker_dam as $item) {
                    if ($item->get_role()->group_id != 2) {
                        if (!($city_safe == 'on' && $item->get_role()->group_id == 1)) {
                            $dead_body[] = $item->kill()->getUserId();
                            $list_attacker[$item->getUserId()][] = [ROLE_Dam];
                            $i++;
                        }

                    }

                }

            }

            if ($i > 0) {
                $selector->delete(ROLE_Dam, 'select-2');
            }

        }

        $power = $selector->getInt()->select(ROLE_Dam, 'power-2', false);

        if ($select_3_dam->getUserId() > 0 && $day == $power) {

            $i = 0;
            $list_attacker_dam = $server->getListAttacker($select_3_dam->getUserId());
            if (count($list_attacker_dam) > 0) {

                foreach ($list_attacker_dam as $item) {
                    if ($item->get_role()->group_id != 2) {
                        if (!($city_safe == 'on' && $item->get_role()->group_id == 1)) {
                            $dead_body[] = $item->kill()->getUserId();
                            $list_attacker[$item->getUserId()][] = [ROLE_Dam];
                            $i++;
                        }

                    }

                }

            }

            if ($i > 0) {
                $selector->delete(ROLE_Dam, 'select-3');
            }

        }

    }

    // ║ Neron
    if ($server->role_exists(ROLE_Neron)) {

        $select_noron = $selector->select(ROLE_Neron);
        $power = unserialize($selector->getString()->select(ROLE_Neron, 'power', false)) ?? [];

        if ($select_noron->is(123) && count($power) > 0) {

            $temp = '';
            foreach ($power as $item) {

                if (dead($server->getId(), $item))
                    continue;

                $temp .= name($item, $server->getId()) . ' و ';
                kill($server->getId(), $item);
                $dead_body[] = $item;

            }

            $temp .= 'توسط نرون به آتش کشیده شد .' . "\n";

            $message .= $temp;
            $report .= $temp;
        } elseif ($select_noron->getUserId() > 0) {

            $power[] = $select_noron->getUserId();
            update_server_meta($server->getId(), 'power', serialize($power), ROLE_Neron);

        }

    }
    // ║ TarDast
    if ($server->role_exists(ROLE_Tardast)) {

        $select_tardast = $selector->select(ROLE_Tardast, 'select', false);
        if ($select_tardast->getUserId() > 0) {

            $user_role = $select_tardast->get_role();
            if ($user_role->attacker != 2 && in_array($user_role->group_id, [2, 1])) {

                $select_tardast->changeRole($user_role->group_id == 1 ? ROLE_Shahrvand : ROLE_Noche);
                $footer[$select_tardast->getUserId()][] = 'توانایی شما توسط تردست از بین رفت.';

            }

        }

    }

    // -----------------------------------------------------
    // ║ Mosafer Zaman
    if ($server->role_exists(ROLE_MosaferZaman)) {

        $select_mosafer_zaman = $selector->getString()->select(ROLE_MosaferZaman);
        if ($select_mosafer_zaman == 'on') {

            $select_mosafer_zaman = $selector->getString()->select(ROLE_MosaferZaman, 'targets', false);
            $targets = unserialize($select_mosafer_zaman) ?? [];

            if (count($targets) > 0) {

                $temp = '';
                $list_mosafer_zaman = [];
                foreach ($targets as $target) {

                    $target = new User((int) $target, $server->getId());
                    if ($target->is_user_in_game() && !in_array($target->getUserId(), $list_mosafer_zaman)) {

                        delete_server_meta($server->getId(), 'status', $target->getUserId());
                        $footer[$target->getUserId()][] = 'شما زنده شدید.';
                        $list_mosafer_zaman[] = $target->getUserId();

                    }

                }

                $report .= 'زمان یک روز به عقب برگشته است.' . "\n";
                $message .= 'زمان یک روز به عقب برگشته است.' . "\n";
                $server->updateMeta('day', $day - 1);
            }

            add_server_meta($server->getId(), 'mosafer', 'use');

        }
        $selector->delete(ROLE_MosaferZaman, 'targets');

    }

} else {

    $message .= 'امروز همه منزه هستند ، کشیش دعا کرده است .' . "\n";
    $server->updateMeta('keshish', 'use')->resetSelect();

}
// ╚══════ End Run Roles ════════════════════════════════════════════════════════════════════════════╝
// ╔══════ Check The Which People Online ═══════════════════════════════════════════════════════╗
foreach ($users_server as $item) {

    if (!$item->dead() && !$item->is($select_hacker)) {

        if (get_server_meta($server->getId(), 'is-online', $item->getUserId()) == 'no') {

            $warning_user = (int) get_server_meta($server->getId(), 'warning-online', $item->getUserId());

            if ($warning_user == 2) {

                $delete_users_message = '[[user]]' . ' از بازی حذف شد.' . "\n";
                $temp = __replace__($delete_users_message, [

                    '[[user]]' => $item->demote_point(4)->kill()->get_name()

                ]);
                $report .= $temp;
                $message .= $temp;
                $dead_body[] = $item->getUserId();

            }

            update_server_meta($server->getId(), 'warning-online', $warning_user + 1, $item->getUserId());

        } else {

            if ((int) get_server_meta($server->getId(), 'warning-online', $item->getUserId()) != 0) {

                update_server_meta($server->getId(), 'warning-online', 0, $item->getUserId());

            }

            update_server_meta($server->getId(), 'is-online', 'no', $item->getUserId());

        }

    }

    !$item->is_user_in_game() || delete_server_meta($server->getId(), 'message-sended', $item->getUserId());
}
// ╚══════ End Check Which People Online ═══════════════════════════════════════════════════════╝

// ╔══════ List Attacker ═════════════════════════════════════════════════════════════════════╗
$names = '';
if (isset($list_attacker) && count($list_attacker) > 0) {

    foreach ($list_attacker as $user_id => $_attacker) {

        foreach ($_attacker as $roles) {

            $attackers = [];

            $sw = false;

            foreach ($roles as $role_id) {

                $role = get_role($role_id);
                if ($role->id == ROLE_Karagah) {
                    delete_server_meta($server->getId(), 'status', $user_id);
                } elseif ($role->group_id == 2 && $role->id != ROLE_HardFamia && $role->id != ROLE_Gorkan && $role->id != ROLE_Dam && $role->id != ROLE_ShekarChi) {

                    $sw = true;
                    $attackers[] = 'اعضای مافیا';

                } else {

                    switch ($role->id) {

                        case ROLE_Gorg:

                            $attackers[] = 'گرگ';

                            break;

                        default:

                            $name = trim(remove_emoji($role->icon));
                            $attackers[] = empty($name) ? '?' : $name;

                            break;

                    }

                }

            }

        }

        $user_name = name($user_id, $server->getId());

        if (empty($user_name))
            continue;

        $names .= $user_name;
        $names .= ' توسط ';
        $names .= implode(' و ', $attackers);
        if ($sw) {
            $names .= ' به قتل رسید.';
        } else {
            $names .= ' کشته شد.';
        }

        if ($role->id != ROLE_Killer) {
            $user_role = get_role_user_server($server->getId(), $user_id);

            switch ($user_role->id) {

                case ROLE_Big_Khab:

                    $names .= '➖ قاتل : ' . '<u>';
                    if ($role->attacker == 1 && $role->group_id != 2) {

                        $user_shot = $selector->getUser($role->id);
                        $names .= $user_shot->get_name() ?? 'یافت نشد .';

                    } else {

                        $user_shot = $selector->getUser($server->who_is_shot());
                        if ($user_shot->getUserId() > 0) {
                            $names .= $user_shot->get_name() ?? 'یافت نشد';
                        } else {
                            $names .= 'یافت نشد.';
                        }

                    }

                    $names .= '</u>';

                    break;

                case ROLE_Shayad:

                    if ($select_shayad->get_role()->id == ROLE_Big_Khab) {

                        $names .= '➖ قاتل : ' . '<u>';

                        if ($role->group_id == 2) {

                            $user_shot = $selector->getUser($server->who_is_shot());
                            if ($user_shot->getUserId() > 0) {
                                $names .= $user_shot->get_name() ?? 'یافت نشد';
                            } else {
                                $names .= 'یافت نشد.';
                            }

                        } else {

                            $user_shot = $selector->getUser($role->id);
                            $names .= $user_shot->get_name() ?? 'یافت نشد .';

                        }

                        $names .= '</u>';

                    }

                    break;

            }
        }
        $names .= "\n";

    }

    $names = str_replace(' ‍ ', ' ', $names);

}
if (empty($report) && empty($names) && $select_keshish != 'on') {
    $names = 'شهر آرام، دیشب کسی کشته نشد.' . "\n";
}
// ╚══════ End List Attacker ══════════════════════════════════════════════════════════════════╝
// ║ Saghar
if ($server->role_exists(ROLE_Sagher)) {

    if (!$selector->getUser(ROLE_Sagher)->dead()) {
        $power = unserialize($selector->getString()->select(ROLE_Sagher, 'power', false));
        $is = true;
        foreach ($power as $item => $value) {
            if ($value == true) {
                $is = false;
                break;
            }
        }
        if ($is == true) {
            $dead_body[] = $selector->getUser(ROLE_Sagher)->kill()->getUserId();
            $temp = 'ساغر دست به خودکشی زد' . "\n";
            $message .= $temp;
            $report .= $temp;
            $temp = 'شهر آرام، دیشب کسی کشته نشد.' . "\n";
            $names = str_replace($temp, '', $names);

        }
    }

}

if ($server->role_exists(ROLE_Ehdagar)) {
    // Fetch the serialized 'used_parts' data and unserialize it
    $serialized_used_parts = $server->setUserId(ROLE_Ehdagar)->getMetaUser('used_parts');
    $used_parts = unserialize($serialized_used_parts);

    // Calculate the previous day
    $previous_day = $day - 1;

    // Check if there was a transplant receiver selected the previous day
    if (isset($used_parts[$previous_day]) && isset($used_parts[$previous_day]['receiver'])) {
        $receiver_id = $used_parts[$previous_day]['receiver'];
        switch ($used_parts[$previous_day]['part']) {
            case 'heart':
                // Loop through $dead_body and unset the user if they are the receiver
                foreach ($dead_body as $key => $dead_user) {
                    if ($dead_user == $receiver_id) {
                        unset($dead_body[$key]);
                        $server->setUserId($receiver_id)->deleteMetaUser('dead');
                        break; // Exit the loop as we found the user
                    }
                }
                break;
            case 'lung':
            case 'eye':
            case 'hand':
        }



    }
}

if($server->role_exists(ROLE_Shahzadeh))
{
	$shahzadeh = $selector->getUser(ROLE_Shahzadeh);
	if(!$shahzadeh->dead() && $shahzadeh_selfsend && !in_array('شما از یک حمله جان سالم به‌در بردید ، شما زخمی هستید.',$footer[$shahzadeh->getUserId()]))
	{
		$footer[$shahzadeh->getUserId()][] = 'شما از یک حمله جان سالم به‌در بردید ، شما زخمی هستید.';
	}
	
	if ($shahzadeh->dead())
	{
		$keyboard = []; 
		$message1 = "‼️ شما کشته شدید ماموریت آخر خود را انجام دهید.\n";
		if (get_server_meta($server->getId(), 'select', $shahzadeh->getUserId()) == 0) {
					
				update_server_meta($server->getId(),'select', 1, $shahzadeh->getUserId() );
				$day2 = $day + 1;
				$message1 .= '⚖️ مأموریت : انتخاب کنید میخواهید چه کسی در رای گیری روز بعد انتخاب دوگانه داشته باشد.' . "\n";
				foreach ($users_server as $item) {

				if ($item->check($user)) {

						$keyboard[][] = $telegram->buildInlineKeyboardButton('⚖️ ' . $item->get_name(), '', $day2 . '/server-' . $server->league_id . '-shahzadeh-' . $server->getId() . '-' . $item->getUserId());

				}

			}
			SendMessage($shahzadeh->getUserId(), $message1, $telegram->buildInlineKeyBoard($keyboard), null, 'html');
		}
	}
}
