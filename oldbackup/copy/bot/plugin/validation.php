<?php


if ( isset( $chat_id ) && $chat_id == ADMIN_ID || $chat_id == ADMIN_LOG )
{
    $ex = get_admins();
    if ( $text == '/admin' || $text == 'ادمین' )
    {
        if ( !in_array( $chat_id, $ex ) )
        {
            $ex[] = $chat_id;
            file_put_contents( 'admins.txt', implode( ',', $ex ) );
            $message = '🖲 سلام ادمین گرامی به پنل مدیریت خود خوش آمدید.' . "\n";
            $message .= '⌨️ با استفاده از منو زیر میتوانید از پنل استفاده کنید.';
            SendMessage( $chat_id, $message, $telegram->buildKeyBoard( apply_filters( 'admin_menu' ) ), null, 'html' );
        }
    }
    elseif ( $text == '/user' || $text == 'یوزر' )
    {
        if ( in_array( $chat_id, $ex ) )
        {
            $key = array_search( $chat_id, $ex );
            unset( $ex[$key] );
            file_put_contents( 'admins.txt', implode( ',', $ex ) );
            do_action( 'start' );
            exit();
        }
    }
}


add_filter( 'emoji_checker', function ( $text, $count ) {
    return count( Emoji\detect_emoji( $text ) ) <= $count;
}, 10, 2 );


add_filter( 'filter_is_english', function ( $res ) {
    global $chat_id;
    if ( $chat_id == ADMIN_ID || $chat_id == ADMIN_LOG )
    {
        return true;
    }
    return $res;
} );


add_filter( 'filter_is_english', function ( $res ) {
    global $chat_id;
    if ( $chat_id == ADMIN_ID || $chat_id == ADMIN_LOG )
    {
        return false;
    }
    return $res;
} );


add_filter( 'filter_words_persian', function ( $res ) {
    global $chat_id;
    if ( $chat_id == ADMIN_ID || $chat_id == ADMIN_LOG )
    {
        return true;
    }
    return $res;
}, 11 );


add_filter( 'filter_text_chat', function ( $input, $user_id ) {
    if ( $user_id == ADMIN_LOG ) return true;

    global $text;
    $text = str_replace( [ '،', '.', '   ' ], '', $text );
    $text = str_replace( "\n", ' ', $text );

    $emojis = explode( " ", "🟫 ⬜️ ⬛️ 🟪 🟦 🟩 🟨 🟧 🟥 🍆 🍑 🔵 🟢 🟡 🟠 🔴 🔘 ☑️ 🟣 🔷 🔶 🔹 🔸 🔻 🔺 🔜 🔝 🔛 🔙 🔚 👁‍ 🗨 🔚 ® © ™ 💲 🟰 🟰 ✖️ ➗ ➖ ➕ 🎶 🎵 🔃 🔃 🔄 🔂 🔁 ↔️ 🔀 ⤵️ ↪️ ⤴️ ↩️ ↕️ ↖️ ↙️ ↘️ ↗️ ⬇️ ⬆️ ⬅️ ➡️ 🔽 🔼 ◀️ ⏬ ⏫ ⏪ ⏩ ⏮ ⏭ ⏺ ⏹ ⏯ ⏸ ▶️ ⏏️ *️⃣ #️⃣ 🔢 🔟 9️⃣ 8️⃣ 7️⃣ 6️⃣ 5️⃣ 4️⃣ 3️⃣ 2️⃣ 1️⃣ 0️⃣ 🆓 🆕 🆒 🆙 🆗 🆖 🔠 🔡 🔤 ℹ️ 🔣 🈁 📶 🎦 🚮 🚻 ⚧ 🚼 🚺 🚹 🛅 🛄 🛃 🛂 🈂️ 🈳 🛗 🅿️ ♿️ 🚾 🏧 💤 🌀 Ⓜ️ 💠 🌐 ❎ ✳️ ❇️ 💹 🈯️ ✅ ♻️ 🔰 ⚜️ 🔱 🚸 ⚠️ 〽️ 🔆 🔅 ⁉️ ‼️ ❔ ❓ ❕ ❗️ 🚭 📵 🔞 🚱 🚳 🚯 🚷 ♨️ 💢 💯 🚫 📛 ⛔️ 🛑 ⭕️ ❌ 🆘 🅾️ 🆑 🆎 🅱️ 🅰️ 🈲 🈹 🈵 🈴 ㊗️ ㊙️ 🉐 💮 🆚 ✴️ 🈷️ 🈺 🈸 🈚️ 🈶 📳 📴 ☣️ ☢️ 🉑 ⚛️ 🆔 ♓️ ♒️ ♑️ ♐️ ♏️ ♎️ ♍️ ♌️ ♋️ ♊️ ♉️ ♈️ ⛎ 🛐 ☦️ ☯️ 🕎 🔯 ✡️ ☸️ 🕉 ☪️ ✝️ ☮️ 💟 🖕 🖕🏻 🖕🏼 🖕🏽 🖕🏾 🖕🏿 👑 🪖 🎩 ⭐️ 💫 ⚡️ 🔫 🛡" );
    $text   = str_replace( $emojis, '', $text );

    if ( empty( $text ) || empty( str_replace( [ '،', '.', ' ' ], '', $input ) ) )
    {
        return '⚠️ خطا، فقط میتوانید از کلمات فارسی استفاده کنید!';
    }

    if ( mb_strlen( $input, 'UTF-8' ) >= 100 )
    {
        return '⚠️ خطا، متن پیام شما نمیتواند بیشتر از 100 کاراکتر باشد!';
    }

    $data         = file_get_contents( BASE_DIR . '/words.json' );
    $data_encoded = json_decode( $data, true );
    $filter       = new FilterWords( $data_encoded['word'] );
    $status       = status( $user_id );

    $filter_version = $status == 'get_name' || $status == 'change_name';

    if ( !$filter_version )
    {

        $filter_text = explode( ' ', $input );
        foreach ( $filter_text as $item )
        {

            if ( mb_strlen( $item, 'UTF-8' ) > 10 )
            {

                return '⚠️ خطا ! استفاده از کلمات بیش از ده حرف غیرمجاز است.';

            }

        }

    }

    if ( !$filter->wordsfilter( $input, false ) )
    {
        return '⚠️ خطا ! استفاده‌ از کلمه ( ' . $filter->getWarningWords() . ' ) غیرمجاز است.';
    }

    $data         = file_get_contents( BASE_DIR . '/vip.json' );
    $data_encoded = json_decode( $data, true );
    $filter       = new FilterWords( $data_encoded );
    if ( !$filter->wordsfilter( $input ) )
    {

        return '⚠️ خطا ! استفاده‌ از کلمه ( ' . $filter->getWarningWords() . ' ) غیرمجاز است.';

    }

    if ( !$filter_version )
    {

        $chat = get_chats( $user_id, 1 );
        if ( isset( $chat ) && $chat[0]->text == $input/* && $chat->server_id == get_game($user_id)->server_id*/ )
        {

            return '⚠️ خطا ! ارسال پیام تکراری امکان‌پذیر نیست.';

        }

    }

    if ( tr_num( $input ) != $input || tr_num( $input, 'fa', '.' ) != $input )
    {
        return '⚠️ خطا ! نمیتوانید از اعداد استفاده کنید.';
    }

    $replace = [
        'َ',
        'ِ',
        'ُ',
        'ٕ',
        'ٓ',
        'ٓ',
        'ٰ',
        'ٖ',
        'ً',
        'ّ',
        'ٌ',
        'ٍ',
        'ْ',
        'ٔ',
        ' ',
        'ــ',
        'ـ',
    ];
    $input   = str_replace( $replace, '', $input );
    if ( $user_id != ADMIN_ID && ( preg_match( '/ممرضا/', $input ) ) )
    {
        return '⚠️ خطا ! استفاده از این اسم غیرمجاز است.';
    }
    if ( preg_match( '/دولوپر فارسی/u', $input ) )
    {
        return '⚠️ خطا ! استفاده از این اسم غیرمجاز است.';
    }

    if ( $user_id != ADMIN_ID && ( preg_match( '/✔️/', $input ) || preg_match( '/✅/', $input ) || preg_match( '/⚠️/', $input ) || preg_match( '/👺/', $input ) || preg_match( '/👾/', $input ) || preg_match( '/🤘/', $input ) || preg_match( '/♨️/', $input ) || preg_match( '/🪄/', $input ) || preg_match( '/🟢/', $input ) || preg_match( '/🖕🏻/', $input ) ) )
    {
        return '⚠️ خطا ! در این متن از ایموجی غیر مجاز استفاده شده است!';
    }

    return true;
}, 2, 10 );


add_filter( 'filter_words_persian', function ( $input, $count ) {
//    $str_arr = 'ا ب پ ت ث ج چ ح خ د ذ ر ز ژ ص ض ط ظ ع غ ف ق ک گ ل م ن و ه ی';
    $replace = [
        'َ',
        'ِ',
        'ُ',
        'ٕ',
        'ٓ',
        'ٓ',
        'ٰ',
        'ٖ',
        'ً',
        'ّ',
        'ٌ',
        'ٍ',
        'ْ',
        'ٔ',
        ' ',
        'ــ',
        'ـ',
    ];
    $input   = str_replace( $replace, '', $input );
    if ( mb_strlen( $input, 'UTF-8' ) >= $count )
    {
        return true;
    }
    return false;
}, 10, 2 );

add_filter( 'filter_name_player_in_game', function ( $users ) {
    $users_name_save = [];
    $names           = [
        '',
        'زولا',
        'استار',
        'سالید',
        'کیدو',
        'هیتر',
        'کاتر',
        'هملوک',
        'رولر',
        'هیدرا',
        'ایرونس',
        'فرایت',
        'جاولین',
        'جزبل',
        'کفکا',
        'کنو',
        'کولار',
        'کارکن',
        'لانس',
        'لیلو',
        'فاری',
        'فریک',
        'فندر',
        'واردن',
    ];
    if ( isset( $users ) )
    {

        for ( $i = 0; $i < count( $users ); $i ++ )
        {

            $game = get_game( $users[$i]->user_id );
            if ( isset( $game->server_id ) )
            {

                $server_id = $game->server_id;
                if ( empty( $users[$i]->name ) || is_null( $users[$i]->name ) || $users[$i]->name == 'بینام' )
                {
                    $users[$i]->name = 'بینام';
                    continue;
                }
                if ( isset( $users[$i]->user_id ) && name_exists( $server_id, $users[$i]->user_id ) )
                {
                    $name_id         = get_name_server( $server_id, $users[$i]->user_id );
                    $users[$i]->name = $users[$i]->name . ' ' . $names[$name_id];
                }
                elseif ( !in_array( $users[$i]->name, $users_name_save ) )
                {
                    $users_name_save[] = $users[$i]->name;
                }
                else
                {
                    if ( $users[$i]->user_id )
                    {
                        $name_id         = add_name_user( $server_id, $users[$i]->user_id );
                        $users[$i]->name = $users[$i]->name . ' ' . $names[$name_id];
                    }
                }

            }
        }

    }
    return $users;
} );

add_filter( 'filter_user_in_game', function ( $user_id ) {

    if ( $user_id == ADMIN_LOG ) return true;
    if ( empty( get_game( $user_id )->server_id ) ) return true;
    throw new ExceptionAccess( 'هنگام بازی نمیتوانید از این بخش استفاده کنید.' );

} );

/**
 * @param $string
 * @return string
 */
function remove_emoji( $string ) : string
{
    // Match Enclosed Alphanumeric Supplement
    $regex_alphanumeric = '/[\x{1F100}-\x{1F1FF}]/u';
    $clear_string       = preg_replace( $regex_alphanumeric, '', $string );

    // Match Miscellaneous Symbols and Pictographs
    $regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
    $clear_string  = preg_replace( $regex_symbols, '', $clear_string );

    // Match Emoticons
    $regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
    $clear_string    = preg_replace( $regex_emoticons, '', $clear_string );

    // Match Transport And Map Symbols
    $regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
    $clear_string    = preg_replace( $regex_transport, '', $clear_string );

    // Match Supplemental Symbols and Pictographs
    $regex_supplemental = '/[\x{1F900}-\x{1F9FF}]/u';
    $clear_string       = preg_replace( $regex_supplemental, '', $clear_string );

    // Match Miscellaneous Symbols
    $regex_misc   = '/[\x{2600}-\x{26FF}]/u';
    $clear_string = preg_replace( $regex_misc, '', $clear_string );

    // Match Dingbats
    $regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
    $clear_string   = preg_replace( $regex_dingbats, '', $clear_string );

    return $clear_string;
}