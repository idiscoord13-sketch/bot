<?php

/*class Language {

    private $UserLng;
    private $langSelected;
    public $lang = array();


    public function __construct($userLanguage){

        $this->UserLng = $userLanguage;
        //construct lang file
        $langFile = '/path/to/ini/files/'. $this->UserLng . '.ini';
        if(!file_exists($langFile)){
            throw new Execption("Language could not be loaded"); //or default to a language
        }

        $this->lang = parse_ini_file($langFile);
    }

    public function userLanguage(){
        return $this->lang;
    }

}*/


class Language
{

    private $UserLng;
    private $langSelected;
    public $lang = array();


    public function __construct($userLanguage)
    {
        $this->UserLng = $userLanguage;
    }

    public function userLanguage($text = '')
    {
        switch ($this->UserLng) {
            /*
            ------------------
            Language: English
            ------------------
            */
            case "en":
                $lang['PAGE_TITLE'] = 'My website page title';
                $lang['HEADER_TITLE'] = 'My website header title';
                $lang['SITE_NAME'] = 'My Website';
                $lang['SLOGAN'] = 'My slogan here';
                $lang['HEADING'] = 'Heading';

                // Menu

                $lang['MENU_LOGIN'] = 'Login';
                $lang['MENU_SIGNUP'] = 'Sign up';
                $lang['MENU_FIND_RIDE'] = 'Find Ride';
                $lang['MENU_ADD_RIDE'] = 'Add Ride';
                $lang['MENU_LOGOUT'] = 'Logout';

                break;

            /*
            ------------------
            Language: Italian
            ------------------
            */

            case "it":
                $lang['PAGE_TITLE'] = 'Il titolo della mia pagina';
                $lang['HEADER_TITLE'] = 'Il mio titolo';
                $lang['SITE_NAME'] = 'Il nome del mio sito';
                $lang['SLOGAN'] = 'Uno slogan';
                $lang['HEADING'] = 'Heading';

                // Menu

                $lang['MENU_LOGIN'] = 'Entra';
                $lang['MENU_SIGNUP'] = 'Registrati';
                $lang['MENU_FIND_RIDE'] = 'Trova gruppi';
                $lang['MENU_ADD_RIDE'] = 'Aggiungi gruppo';
                $lang['MENU_LOGOUT'] = 'Esci';

                break;
        }
        if ($text == ''){
            return $lang;
        }
        return $lang[$text] ?? '';
    }
}