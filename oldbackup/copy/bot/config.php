<?php

error_reporting(1);

// directory

const BASE_DIR = __DIR__;
const SRC_DIR = BASE_DIR . '/src';
const LIB_DIR = BASE_DIR . '/lib';
const FILES_DIR = BASE_DIR . '/files';
const SOURCE_DIR = BASE_DIR . '/source';
const PLUGINS_DIR = BASE_DIR . '/plugin';
const LANGUAGE_DIR = BASE_DIR . '/language';
const SRC_URL = 'https://bot.irmafiabot.com/';

// database

$users_db = [ 'iranimaf_black', 'iranimaf_black' ]; // iranimaf_black

const HOST= 'localhost'; // localhost
define( "USERNAME", 'iranimaf_black'); // iranimaf_black
const PASSWORD = 'F{e.087U@QXH&;}?'; // F{e.087U@QXH&;}?
const DB_NAME = 'iranimaf_main'; // iranimaf_main
// source

//const TOKEN_API = '2109091573:AAHbPeHA3gkqbDOISrSTZbAKv-NWwSUiH7U';
const ADMIN_LOG = 2082435505;
const BACKUP_DB = false;
const VERSION = '1.0.0';
//date.timezone = "Etc/GMT+3:30"

date_default_timezone_set('Etc/GMT+2:30');

// plugin


const FILE_VERIFY_NAME = 'verify.php';
const URL_VERIFY = SRC_URL . '/' . FILE_VERIFY_NAME;
const MERCHANT_ID = '79631ac7-75ca-4d46-b961-03aa18073db2';
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


const PLAN_1 = 90000;
const PLAN_2 = 170000;
const PLAN_3 = 320000;
const PLAN_4 = 600000;
const PLAN_5 = 750000;
const PLAN_6 = 2100000;
const PLAN_7 = 3300000;



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