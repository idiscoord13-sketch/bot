<?php

error_reporting(1);

// new directory
const BASE_DIR = __DIR__;
const INCLUDE_DIR = BASE_DIR . '/include';
const CORE_DIR = INCLUDE_DIR . '/core';
const WP_DIR = CORE_DIR . '/wordpress';
const TEL_DIR = CORE_DIR . '/telegram';
const TRAIT_DIR = CORE_DIR . '/traits';
const PACK_DIR = CORE_DIR . '/other';
const CONTROLL_DIR = CORE_DIR . '/controller';
const CONFIG_DIR = INCLUDE_DIR . '/config';
const CLASS_DIR = INCLUDE_DIR . '/class';
const BOT_DIR = INCLUDE_DIR . '/bot';
const FUNC_DIR = INCLUDE_DIR . '/functions';
const LIB_DIR = FUNC_DIR . '/lib';
const PLUGIN_DIR = FUNC_DIR . '/plugin';
const FILES_DIR = BASE_DIR . '/files';
const SRC_URL = 'https://bot.irmafiabot.com/newbot';

// old directory

/*
const SRC_DIR = BASE_DIR . '/src';
const LIB_DIR = BASE_DIR . '/lib';
const FILES_DIR = BASE_DIR . '/files';
const SOURCE_DIR = BASE_DIR . '/source';
const PLUGINS_DIR = BASE_DIR . '/plugin';
const LANGUAGE_DIR = BASE_DIR . '/language';
const SRC_URL = 'https://bot.irmafiabot.com/';
*/
// database

$users_db = [ 'iranimaf_black', 'iranimaf_black' ]; // iranimaf_black

const HOST= 'localhost'; // localhost
define( "USERNAME", 'iranimaf_black'); // iranimaf_black
const PASSWORD = 'F{e.087U@QXH&;}?'; // F{e.087U@QXH&;}?
const DB_NAME = 'iranimaf_main'; // iranimaf_main

// source
const FONTS = [
    'normal' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
    'bold' => '𝗔𝗕𝗖𝗗𝗘𝗙𝗚𝗛𝗜𝗝𝗞𝗟𝗠𝗡𝗢𝗣𝗤𝗥𝗦𝗧𝗨𝗩𝗪𝗫𝗬𝗭𝗮𝗯𝗰𝗱𝗲𝗳𝗴𝗵𝗶𝗷𝗸𝗹𝗺𝗻𝗼𝗽𝗾𝗿𝘀𝘁𝘶𝘷𝘸𝘹𝘺𝘇',
    'italic' => '𝘈𝘉𝘊𝘋𝘌𝘍𝘎𝘏𝘐𝘑𝘒𝘓𝘔𝘕𝘖𝘗𝘘𝘙𝘚𝘛𝘜𝘝𝘞𝘟𝘠𝘡𝘢𝘣𝘤𝘥𝘦𝘧𝘨𝘩𝘪𝘫𝘬𝘭𝘮𝘯𝘰𝘱𝘲𝘳𝘴𝘵𝘶𝘷𝘸𝘹𝘺𝘻',
    'bold_italic' => '𝘼𝘽𝘾𝘿𝙀𝙁𝙂𝙃𝙄𝙅𝙆𝙇𝙈𝙉𝙊𝙋𝙌𝙍𝙎𝙏𝙐𝙑𝙒𝙓𝙔𝙕𝙖𝙗𝙘𝙙𝙚𝙛𝙜𝙝𝙞𝙟𝙠𝙡𝙢𝙣𝙤𝙥𝙦𝙧𝙨𝙩𝙪𝙫𝙬𝙭𝙮𝙯',
    'monospace' => '𝙰𝙱𝙲𝙳𝙴𝙵𝙶𝙷𝙸𝙹𝙺𝙻𝙼𝙽𝙾𝙿𝚀𝚁𝚂𝚃𝚄𝚅𝚆𝚇𝚈𝚉𝚊𝚋𝚌𝚍𝚎𝚏𝚐𝚑𝚒𝚓𝚔𝚕𝚖𝚗𝚘𝚙𝚚𝚛𝚜𝚝𝚞𝚟𝚠𝚡𝚢𝚣',
    'double_struck' => '𝔸𝔹ℂ𝔻𝔼𝔽𝔾ℍ𝕀𝕁𝕂𝕃𝕄ℕ𝕆ℙℚℝ𝕊𝕋𝕌𝕍𝕎𝕏𝕐ℤ𝕒𝕓𝕔𝕕𝕖𝕗𝕘𝕙𝕚𝕛𝕜𝕝𝕞𝕟𝕠𝕡𝕢𝕣𝕤𝕥𝕦𝕧𝕨𝕩𝕪𝕫',
    'sans_serif' => '𝖠𝖡𝖢𝖣𝖤𝖥𝖦𝖧𝖨𝖩𝖪𝖫𝖬𝖭𝖮𝖯𝖰𝖱𝖲𝖳𝖴𝖵𝖶𝖷𝖸𝖹𝖺𝖻𝖼𝖽𝖾𝖿𝗀𝗁𝗂𝗃𝗄𝗅𝗆𝗇𝗈𝗉𝗊𝗋𝗌𝗍𝗎𝗏𝗐𝗑𝗒𝗓',
    'wide' => 'ＡＢＣＤＥＦＧＨＩＪＫＬＭＮＯＰＱＲＳＴＵＶＷＸＹＺａｂｃｄｅｆｇｈｉｊｋｌｍｎｏｐｑｒｓｔｕｖｗｘｙｚ',
    'superscript' => 'ᴬᴮᶜᴰᴱᶠᴳᴴᴵᴶᴷᴸᴹᴺᴼᴾᵠᴿˢᵀᵁⱽᵂˣʸᶻᵃᵇᶜᵈᵉᶠᵍʰⁱʲᵏˡᵐⁿᵒᵖᵠʳˢᵗᵘᵛʷˣʸᶻ',
    'small_caps' => 'ᴀʙᴄᴅᴇꜰɢʜɪᴊᴋʟᴍɴᴏᴘϙʀsᴛᴜᴠᴡxʏᴢᴀʙᴄᴅᴇꜰɢʜɪᴊᴋʟᴍɴᴏᴘϙʀsᴛᴜᴠᴡxʏᴢ',
    'two' => '𝐀𝐁𝐂𝐃𝐄𝐅𝐆𝐇𝐈𝐉𝐊𝐋𝐌𝐍𝐎𝐏𝐐𝐑𝐒𝐓𝐔𝐕𝐖𝐗𝐘𝐙𝐚𝐛𝐜𝐝𝐞𝐟𝐠𝐡𝐢𝐣𝐤𝐥𝐦𝐧𝐨𝐩𝐪𝐫𝐬𝐭𝐮𝐯𝐰𝐱𝐲𝐳',
    'tree' => '𝑨𝑩𝑪𝑫𝑬𝑭𝑮𝑯𝑰𝑱𝑲𝑳𝑴𝑵𝑶𝑷𝑄𝑹𝑺𝑻𝑼𝑽𝑾𝑿𝒀𝒁𝒂𝒃𝒄𝒅𝒆𝒇𝒈𝒉𝒊𝒋𝒌𝒍𝒎𝒏𝒐𝒑𝒒𝒓𝒔𝒕𝒖𝒗𝒘𝒙𝒚𝒛',
];

//const TOKEN_API = '2109091573:AAHbPeHA3gkqbDOISrSTZbAKv-NWwSUiH7U';
const ADMIN_LOG = 2082435505;
const BACKUP_DB = false;
const VERSION = '1.0.0';
//date.timezone = "Etc/GMT+3:30"

date_default_timezone_set('Etc/GMT+2:30');

// plugin


const FILE_VERIFY_NAME = 'verify.php';
const URL_VERIFY = SRC_URL . '/' . FILE_VERIFY_NAME;
const MERCHANT_ID = '11e89763-1c06-4a9c-884c-f2c2a33c8284';
const ADMIN_ID = 2082435505;
const CHNNEL_ID = -1001744116825;
const GP_MANAGER        = - 1001713930364;
const GP_REPORT         = - 1001686383416;
const GP_SUPPORT        = - 1001635860906;
const GP_REQUEST_LEAGUE = - 1001321635581;
const GP_VIP_SUPPORT    = - 1001475371777;
const GP_CHAT           = - 1001721665696;
const GP_PAYMENY        = - 1001542741650;

const LEAGUE_MOSTAGHEL = 3;
const MOSTAGHEL_TEAM   = [ 4 , 5 ];


const PLAN_1 = 128700;
const PLAN_2 = 243100;
const PLAN_3 = 457600;
const PLAN_4 = 858000;
const PLAN_5 = 1072500;
const PLAN_6 = 3003000;
const PLAN_7 = 4332900;



// role


// === Group 1 ===
const ROLE_Karagah      = 1;
const ROLE_Pezeshk      = 2;
const ROLE_Bazpors      = 3;
const ROLE_Shahrvand    = 4;
const ROLE_Big_Khab     = 36;
const ROLE_Sahere       = 5;
const ROLE_Sniper       = 6;
const ROLE_Ghazi        = 11;
const ROLE_Police       = 13;
const ROLE_Didban       = 14;
const ROLE_TofangDar    = 16;
const ROLE_Mohaghegh    = 17;
const ROLE_Kaboy        = 18;
const ROLE_Kalantar     = 19;
const ROLE_Keshish      = 20;
const ROLE_Bakreh       = 21;
const ROLE_Fereshteh    = 22;
const ROLE_Fadaii       = 23;
const ROLE_Bodygard     = 24;
const ROLE_KhabarNegar  = 25;
const ROLE_Zambi        = 32;
const ROLE_EynakSaz     = 34;
const ROLE_Shield       = 37;
const ROLE_Memar        = 41;
const ROLE_Naghel       = 47;
const ROLE_Senator      = 49;
const ROLE_TelefonChi   = 50;
const ROLE_Jadogar      = 51;
const ROLE_MosaferZaman = 53;
const ROLE_Framason     = 59;
const ROLE_Nonva        = 61;
const ROLE_Ahangar      = 62;
const ROLE_Tardast      = 65;
const ROLE_Shahrdar      = 67;
const ROLE_Ehdagar       = 68;
const ROLE_Cobcob       = 72;

// === Group 2 ===
const ROLE_Godfather         = 7;
const ROLE_Mashooghe         = 8;
const ROLE_Nato              = 12;
const ROLE_Hacker            = 15;
const ROLE_AfsonGar          = 26;
const ROLE_HardFamia         = 27;
const ROLE_TohmatZan         = 28;
const ROLE_Noche             = 33;
const ROLE_MozakarehKonandeh = 35;
const ROLE_BAD_DOCTOR        = 40;
const ROLE_Tobchi            = 42;
const ROLE_ShabKhosb         = 29;
const ROLE_Dalghak           = 39;
const ROLE_ShekarChi         = 46;
const ROLE_Dozd              = 48;
const ROLE_Yakoza            = 52;
const ROLE_Shayad            = 54;
const ROLE_ShahKosh          = 55;
const ROLE_Dam               = 60;
const ROLE_Terrorist         = 66;
// === Group 3 ===
const ROLE_Killer    = 9;
const ROLE_Joker     = 10;
const ROLE_Bazmandeh = 31;
const ROLE_Gorg      = 43;
const ROLE_Jalad     = 38;
const ROLE_Ashpaz    = 44;
const ROLE_Ankabot   = 45;
const ROLE_Hazard    = 58;
const ROLE_Neron     = 63;
// === Group 4 ===
const ROLE_Sagher   = 56;
const ROLE_Gambeler = 57;
const ROLE_Gesica   = 64;
const ROLE_Gorkan   = 69;
const ROLE_MineLayer = 70;
const ROLE_MineLayerMafia = 71;
