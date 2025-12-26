<?php

/* @var $telegram Telegram */

define(
    "KEY_START_MENU", $telegram->buildKeyBoard([
    [
        $telegram->buildKeyboardButton('🕹 شروع بازی آنلاین')
    ],
    [
        $telegram->buildKeyboardButton('👥 دوستانه'),
        $telegram->buildKeyboardButton('🌟 امتیازات')
    ],
    [
        $telegram->buildKeyboardButton('💰 سکه'),
        $telegram->buildKeyboardButton('📜 دوستان'),
        $telegram->buildKeyboardButton('👤 پروفایل')
    ],
    [
        $telegram->buildKeyboardButton('📚 راهنمای بازی'),
        $telegram->buildKeyboardButton('🪩 سرور'),
        //        $telegram->buildKeyboardButton('⛔️ قوانین ربات'),
    ],
    /*[
        $telegram->buildKeyboardButton('🎁 دوستات رو دعوت کن و هدیه بگیر 🎁')
    ]*/
])
);

define(
    "KEY_BACK_TO_START_MENU", $telegram->buildKeyBoard([
    [
        $telegram->buildKeyboardButton('♨️ بازگشت به منوی اصلی')
    ]
])
);

define(
    "KEY_GAME_ON_MENU", $telegram->buildKeyBoard([
    [
        $telegram->buildKeyboardButton('📯 جادو ها'),
        $telegram->buildKeyboardButton('💭 پیام خصوصی'),
        $telegram->buildKeyboardButton('📵 گزارش تخلف'),
    ],
    [
        $telegram->buildKeyboardButton('➕ درخواست'),
        $telegram->buildKeyboardButton('🎬 رسانه'),
        $telegram->buildKeyboardButton('⏏️ خروج از بازی'),
    ],
])
);

/*define("KEY_GAME_ON_MENU", $telegram->buildKeyBoard([
    [
        $telegram->buildKeyboardButton('🪄 جادوها'),
        $telegram->buildKeyboardButton('📨 پیام خصوصی')
    ],
    [
        $telegram->buildKeyboardButton('▶️ خروج از بازی'),
        $telegram->buildKeyboardButton('🚫 گزارش تخلف')
    ],
]));*/

define(
    "KEY_GAME_SALAMAT_MENU", $telegram->buildKeyBoard([
    [
        $telegram->buildKeyboardButton('▶️ خروج از بازی'),
        $telegram->buildKeyboardButton('🚫 گزارش تخلف')
    ]
])
);

define(
    "KEY_GAME_END_MENU", $telegram->buildKeyBoard([
    [
        $telegram->buildKeyboardButton('🧩 بازی شانسی'),
        $telegram->buildKeyboardButton('🚫 گزارش تخلف')
    ],
    [
        $telegram->buildKeyboardButton('▶️ خروج از بازی')
    ]
])
);

define(
    "KEY_HOST_GAME_MENU", $telegram->buildKeyBoard([
    [
        $telegram->buildKeyboardButton('شروع با همین تعداد'),
    ],
    [
        $telegram->buildKeyboardButton('▶️ خروج از بازی'),
    ]
])
);

define(
    "KEY_GUST_GAME_MENU", $telegram->buildKeyBoard([
    [
        $telegram->buildKeyboardButton('▶️ خروج از بازی'),
    ]
])
);

define(
    "KEY_SHOP_MENU", $telegram->buildInlineKeyBoard([
    [
        $telegram->buildInlineKeyboardButton('100 سکه ، ' . number_format(PLAN_1 / 10) . ' تومان', '', 'charge-' . PLAN_1),
    ],
    [
        $telegram->buildInlineKeyboardButton('200 سکه ، ' . number_format(PLAN_2 / 10) . ' تومان', '', 'charge-' . PLAN_2),
        $telegram->buildInlineKeyboardButton('400 سکه ، ' . number_format(PLAN_3 / 10) . ' تومان', '', 'charge-' . PLAN_3),
    ],
    [
        $telegram->buildInlineKeyboardButton('800 سکه ، ' . number_format(PLAN_4 / 10) . ' تومان', '', 'charge-' . PLAN_4),
        $telegram->buildInlineKeyboardButton('1000 سکه ، ' . number_format(PLAN_5 / 10) . ' تومان', '', 'charge-' . PLAN_5),
    ],
    [
        $telegram->buildInlineKeyboardButton('3000 سکه ، ' . number_format(PLAN_6 / 10) . ' تومان', '', 'charge-' . PLAN_6),
        $telegram->buildInlineKeyboardButton('5000 سکه ، ' . number_format(PLAN_7 / 10) . ' تومان', '', 'charge-' . PLAN_7),
    ],
])
);

define(
    "KEY_MAGIC_GAME", $telegram->buildInlineKeyBoard([
    [
        $telegram->buildInlineKeyboardButton('🎭 جادوی اثبات (2,4)', '', 'magic-1'),


        $telegram->buildInlineKeyboardButton('🛡 جادوی محفوظ (6)', '', 'magic-3'),
    ],
    [
        $telegram->buildInlineKeyboardButton('🤷🏻‍♂ جادوی حذف رای (5)', '', 'magic-4'),

        $telegram->buildInlineKeyboardButton('🧏🏻‍♂ جادوی جاسوس' . ' (5)', '', 'magic-5'),
    ],
    [
        $telegram->buildInlineKeyboardButton('👨🏻‍💻 جادوی ضدهک (4)', '', 'magic-6'),

        $telegram->buildInlineKeyboardButton('🥱 جادوی بیداری ' . '(6)', '', 'magic-7'),
    ],
    [
        $telegram->buildInlineKeyboardButton('🤐 جادو حقیقت ' . '(4)', '', 'magic-8'),

        $telegram->buildInlineKeyboardButton('🔫 جادوی تشخیص تیر' . '(3)', '', 'magic-9'),
    ],
    [
        $telegram->buildInlineKeyboardButton('〽️ جادوی برای دیگران', '', 'magic_other'),
    ],
    [
        $telegram->buildInlineKeyboardButton('⛔️ انصراف', '', 'cancel_2')
    ],
])
);


define(
    "KEY_GENDER_MENU", $telegram->buildInlineKeyBoard([
    [
        $telegram->buildInlineKeyboardButton('🙋🏻‍♂️ آقا', '', 'select_gender-man'),
        $telegram->buildInlineKeyboardButton('🙋🏻‍♀️ خانم', '', 'select_gender-woman')
    ],
    [
        $telegram->buildInlineKeyboardButton('🙋🏻 سایر', '', 'select_gender-other')
    ]
])
);

