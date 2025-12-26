<?php


namespace library;


use ExceptionWarning;
use FilterWords;

class Text
{
    /**
     * @var string
     */
    private string $text;

    /**
     * @var User|null
     */
    private ?User $user;

    /**
     * Text constructor.
     * @param string $text
     * @param User|null $user
     */
    public function __construct( string $text, ?User $user )
    {
        $this->text = $text;
        $this->user = $user;
    }

    /**
     * @return bool
     */
    public function emoji() : bool
    {
        return apply_filters( 'emoji_checker', $this->text, 1 ) || apply_filters( 'emoji_checker', $this->text, 2 ) || apply_filters( 'emoji_checker', $this->text, 3 ) || apply_filters( 'emoji_checker', $this->text, 4 );
    }

    /**
     * @var string[]
     */
    protected array $words = [
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
        '?',
        '؟',
        '﷼',
        'ٖ‌﷼‌ٕ‌',
        '◕',
        '‿',
        '◠',
        '═',
        '▪︎',
        '▪',
        '؏',
        'ღ',
        '⍟',
        '߷',
        '⍟',
        '∆',
        '∆',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        '0',
        '༆',
        '¤',
        '◕',
        '‿',
        '◠',
        '҉',
        '𝔻',
        '𝕠',
        '𝕟',
        '𝕥',
        '𝕔',
        '𝕣',
        '𝕪',
        '𝕗',
        '𝕚',
        '𝕖',
        '𝕕',
        '𝕎',
        '𝕒',
        '𝕤',
        '𝕦',
        '𝕣',
        '𝕗',
        '𝕙',
        '𝕤',
        '𝕘',
        '𝕜',
        'ℍ',
        '𝔸',
        '𝕄',
        '𝕀',
        '𝔻',
        '𝕊',
        'ℤ',
        '◉',
        '๛',
        '̸',
        '/',
        '༒',
        '༒',
        '﹏',
        '₩',
        "ᵏ",
        "ʰ",
        "ᵃ",
        "ʳ",
        "ᵉ",
        "ʲ",
        "ʰ",
        "ᵃ",
        "ˡ",
        "ⁿ",
        "ᵒ",
        "ᵏ",
        "ℤ",
        "ᵃ",
        "◉",
        "๛",
        "̸",
        "/",
        "༒",
        "༒",
        "﹏",
        ")",
        ":",
        "ㅤ",
        "٨",
        "٦",
        "٢",
        "⁹",
        "⁴",
        "⁵",
        "١",
        "٤",
        "٥",
        "³",
        "⁷",
        "¹",
        "⁰",
        "⁷",
        "⁶",
        "٢",
    ];

    /**
     * @param string $text
     * @return bool
     */
    public function name( string $text )
    {
        $filter = new FilterWords( $this->words );
        if ( ! $filter->wordsfilter( $text ) )
        {
            return '⚠️ خطا ! استفاده‌ از کلمه ( ' . $filter->getWarningWords() . ' ) غیرمجاز است.';
        }
        return true;
    }

    /**
     * @return string
     */
    public function getText() : string
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return Text
     */
    public function setText( string $text ) : Text
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser() : ?User
    {
        return $this->user;
    }

    /**
     * @var string
     */
    protected string $parent = "/^[پچجحخهعغفقثصضشسیبلاتنمکگوئدذرزطظژؤإأءًٌٍَُِّ\s]+$/u";

    /**
     * @return bool
     */
    public function is_persian() : bool
    {
        return (bool) preg_match( $this->parent, $this->text );
    }

    /**
     * @return bool
     * @throws \ExceptionWarning
     */
    public function filter_name() : bool
    {

        if ( ! preg_match( '/^[پچجحخهعغفقثصضشسیبلاتنمکگوئدذرزطظژؤإآأ\s]{3,}+$/u', $this->text ) && mb_strlen( $this->text, 'utf8' ) <= 3 )
        {
            throw new ExceptionWarning( 'نام وارد شده باید حداقل دارای 3 حرف فارسی باشد.' );
        }

        // php
        $persian_alpha_codepoints = '\x{0621}-\x{0628}\x{062A}-\x{063A}\x{0641}-\x{0642}\x{0644}-\x{0648}\x{064E}-\x{0651}\x{0655}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06BE}\x{06CC}ـ';


        if ( ! preg_match( '/^[' . $persian_alpha_codepoints . '\s]+$/u', $this->text ) )
        {
            throw new ExceptionWarning( 'نام وارد شده باید حتما حروف فارسی باشد.' );
        }

        if ( ! preg_match( '/^[' . $persian_alpha_codepoints . '\s]{3,}+$/u', $this->text ) )
        {
            throw new ExceptionWarning( 'نام وارد شده باید حداقل دارای 3 حرف فارسی باشد.' );
        }

        if ( ! preg_match( '/^[' . $persian_alpha_codepoints . '\s]{3,15}+$/u', $this->text ) )
        {
            throw new ExceptionWarning( 'نام وارد شده باید بین 3 تا 15 حرف فارسی باشد.' );
        }

        if ( preg_match( '/\n/', $this->text ) )
        {
            throw new ExceptionWarning( 'نام شما دارای کاراکتر غیرمجاز است.' );
        }


        $data_encoded = json_decode( file_get_contents( BASE_DIR . '/words.json' ), true );
        $filter       = new FilterWords( $data_encoded[ 'word' ] );
        if ( ! $filter->wordsfilter( $this->text, false ) )
        {
            throw new ExceptionWarning( 'استفاده‌ از کلمه ( ' . $filter->getWarningWords() . ' ) غیرمجاز است.' );
        }

        $data   = file_get_contents( BASE_DIR . '/vip.json' );
        $filter = new FilterWords( json_decode( $data, true ) );
        if ( ! $filter->wordsfilter( $input ) )
        {
            throw new ExceptionWarning( 'استفاده‌ از کلمه ( ' . $filter->getWarningWords() . ' ) غیرمجاز است.' );
        }


        if ( $this->user->getUserId() != ADMIN_ID && preg_match( '/ممرضا/u', $this->text ) )
        {
            throw new ExceptionWarning( 'استفاده از این اسم غیرمجاز است.' );
        }

        return true;
    }

    public function filter_latin_name($user) : bool
    {
        if ( $user->checkLatinNameRepeated( $this->text ) )
        {
            throw new ExceptionWarning( '❗️نام مورد نظر تکراری است .'."\n".'لطفا نام دیگری را امتحان کنید .' );
        }
        if ( $user->get_point() < 1000 )
        {
            $user->setStatus( '' );
            throw new ExceptionWarning( "شما باید حداقل دارای 1000 امتیاز باشید. امتیاز شما: $user->get_point() " );
        }


        if (!preg_match('/^[A-Za-z\s]+$/u', $this->text)) {
            throw new ExceptionWarning('نام وارد شده باید تنها شامل حروف انگلیسی  باشد.');
        }


        if (!preg_match('/^[A-Za-z\s]{3,}$/u', $this->text)) {
            throw new ExceptionWarning('نام وارد شده باید حداقل دارای 3 حرف انگلیسی باشد.');
        }

        if (!preg_match('/^[A-Za-z\s]{3,10}$/u', $this->text)) {
            throw new ExceptionWarning('نام وارد شده باید بین 3 تا 10 حرف انگلیسی باشد.');
        }

        if ( preg_match( '/\n/', $this->text ) )
        {
            throw new ExceptionWarning( 'نام شما دارای کاراکتر غیرمجاز است.' );
        }


        $data_encoded = json_decode( file_get_contents( BASE_DIR . '/words.json' ), true );
        $filter       = new FilterWords( $data_encoded[ 'word' ] );
        if ( ! $filter->wordsfilter( $this->text, false ) )
        {
            throw new ExceptionWarning( 'استفاده‌ از کلمه ( ' . $filter->getWarningWords() . ' ) غیرمجاز است.' );
        }

        $data   = file_get_contents( BASE_DIR . '/vip.json' );
        $filter = new FilterWords( json_decode( $data, true ) );
        if ( ! $filter->wordsfilter( $input ) )
        {
            throw new ExceptionWarning( 'استفاده‌ از کلمه ( ' . $filter->getWarningWords() . ' ) غیرمجاز است.' );
        }


        if ( $this->user->getUserId() != ADMIN_ID && preg_match( '/ممرضا/u', $this->text ) )
        {
            throw new ExceptionWarning( 'استفاده از این اسم غیرمجاز است.' );
        }

        return true;
    }

    /**
     * @param string $string
     * @return string
     */
    private function delete_emoji( string $string ) : string
    {
        return str_replace( '‍️', '', remove_emoji( self::removeEmoji( $string ) ) );
    }

    /**
     * @return bool
     * @throws \ExceptionWarning
     */
    public function filter_chat() : bool
    {

        if ( $this->user->is( ADMIN_LOG ) ) return true;

        if ( ! check_time_chat( $this->user->getUserId() ) ) throw new ExceptionWarning( 'هر 2 ثانیه یک بار میتوانید پیام ارسال کنید.' );

        if ( ! self::isPersian( $this->delete_emoji( $this->text ) ) && ! empty( $this->delete_emoji( $this->text ) ) ) throw new ExceptionWarning( 'فقط میتوانید از کلمات فارسی استفاده کنید.' );

        if ( ! apply_filters( 'emoji_checker', $this->text, 4 ) ) throw new ExceptionWarning( 'نمیتوانید بیشتر از 4 ایموجی استفاده کنید.' );

        global $text;
        $text = str_replace( [ '،', '.' ], '', $text );
        $text = str_replace( "\n", ' ', $text );

        if (
            empty( $text ) || empty(
            str_replace( [
                '،',
                '.',
                ' '
            ], '', $this->text )
            )
        ) throw new ExceptionWarning( 'متن پیام باید دارای حروف فارسی باشد.' );

        if ( mb_strlen( $this->text, 'UTF-8' ) >= 100 ) throw new ExceptionWarning( 'متن پیام شما نمیتواند بیشتر از 100 کاراکتر باشد.' );

        if ( ! self::check_big_words( $this->text ) ) throw new ExceptionWarning( 'استفاده از کلمات بیش از ده حرف غیرمجاز است.' );

        $chat = $this->user->setLimit( 1 )->chats();
        if ( isset( $chat ) && $chat[ 0 ]->text == $this->text ) throw new ExceptionWarning( 'ارسال پیام تکراری امکان‌پذیر نیست.' );

        $filter_words = new FilterWords( json_decode( file_get_contents( BASE_DIR . '/words.json' ), true )[ 'word' ] );

        if ( ! $filter_words->wordsfilter( $this->text, false ) ) throw new ExceptionWarning( 'استفاده‌ از کلمه ( ' . $filter_words->getWarningWords() . ' ) غیرمجاز است.' );

        $filter_words = new FilterWords( json_decode( file_get_contents( BASE_DIR . '/vip.json' ), true ) );
        if ( ! $filter_words->wordsfilter( $input ) ) throw new ExceptionWarning( 'استفاده‌ از کلمه ( ' . $filter_words->getWarningWords() . ' ) غیرمجاز است.' );

        if ( tr_num( $this->text ) != $this->text || tr_num( $this->text, 'fa', '.' ) != $this->text ) throw new ExceptionWarning( 'نمیتوانید از اعداد استفاده کنید.' );

        if ( $this->user->getUserId() != ADMIN_ID && ( preg_match( '/ممرضا/', $this->text ) ) ) throw new ExceptionWarning( 'استفاده از این اسم غیرمجاز است.' );

//        $list_emoji = [ '✔️', '✅', '⚠️', '👺', '👾', '🤘', '♨️', '🪄', '🟢', '🖕🏻' ];
        if ( $this->user->getUserId() != ADMIN_ID && preg_match( '/✔️/u', $this->text ) || preg_match( '/✅/u', $this->text ) || preg_match( '/⚠️/u', $this->text ) || preg_match( '/👺/u', $this->text ) || preg_match( '/👾/u', $this->text ) || preg_match( '/🤘/u', $this->text ) || preg_match( '/♨️/u', $this->text ) || preg_match( '/🪄/u', $this->text ) || preg_match( '/🟢/u', $this->text ) || preg_match( '/🖕🏻/u', $this->text ) ) throw new ExceptionWarning( 'در این متن از ایموجی غیر مجاز استفاده شده است.' );

        return true;
    }

    /**
     * @param $string
     * @return string
     */
    public static function removeEmoji( $string ) : string
    {
        return preg_replace(
            '/[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0077}\x{E006C}\x{E0073}\x{E007F})|[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0073}\x{E0063}\x{E0074}\x{E007F})|[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0065}\x{E006E}\x{E0067}\x{E007F})|[\x{1F3F4}](?:\x{200D}\x{2620}\x{FE0F})|[\x{1F3F3}](?:\x{FE0F}\x{200D}\x{1F308})|[\x{0023}\x{002A}\x{0030}\x{0031}\x{0032}\x{0033}\x{0034}\x{0035}\x{0036}\x{0037}\x{0038}\x{0039}](?:\x{FE0F}\x{20E3})|[\x{1F415}](?:\x{200D}\x{1F9BA})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F466})|[\x{1F469}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F469})|[\x{1F469}\x{1F468}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F468})|[\x{1F469}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F48B}\x{200D}\x{1F469})|[\x{1F469}\x{1F468}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F48B}\x{200D}\x{1F468})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9BD})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9BC})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9AF})|[\x{1F575}\x{1F3CC}\x{26F9}\x{1F3CB}](?:\x{FE0F}\x{200D}\x{2640}\x{FE0F})|[\x{1F575}\x{1F3CC}\x{26F9}\x{1F3CB}](?:\x{FE0F}\x{200D}\x{2642}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2695}\x{FE0F})|[\x{1F471}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F9CF}\x{1F647}\x{1F926}\x{1F937}\x{1F46E}\x{1F482}\x{1F477}\x{1F473}\x{1F9B8}\x{1F9B9}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F9DE}\x{1F9DF}\x{1F486}\x{1F487}\x{1F6B6}\x{1F9CD}\x{1F9CE}\x{1F3C3}\x{1F46F}\x{1F9D6}\x{1F9D7}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93C}\x{1F93D}\x{1F93E}\x{1F939}\x{1F9D8}](?:\x{200D}\x{2640}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B0})|[\x{1F471}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F9CF}\x{1F647}\x{1F926}\x{1F937}\x{1F46E}\x{1F482}\x{1F477}\x{1F473}\x{1F9B8}\x{1F9B9}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F9DE}\x{1F9DF}\x{1F486}\x{1F487}\x{1F6B6}\x{1F9CD}\x{1F9CE}\x{1F3C3}\x{1F46F}\x{1F9D6}\x{1F9D7}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93C}\x{1F93D}\x{1F93E}\x{1F939}\x{1F9D8}](?:\x{200D}\x{2642}\x{FE0F})|[\x{1F441}](?:\x{FE0F}\x{200D}\x{1F5E8}\x{FE0F})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F8}\x{1F1F9}\x{1F1FA}](?:\x{1F1FF})|[\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F0}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1FA}](?:\x{1F1FE})|[\x{1F1E6}\x{1F1E8}\x{1F1F2}\x{1F1F8}](?:\x{1F1FD})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F7}\x{1F1F9}\x{1F1FF}](?:\x{1F1FC})|[\x{1F1E7}\x{1F1E8}\x{1F1F1}\x{1F1F2}\x{1F1F8}\x{1F1F9}](?:\x{1F1FB})|[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1ED}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F7}\x{1F1FB}](?:\x{1F1FA})|[\x{1F1E6}\x{1F1E7}\x{1F1EA}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FE}](?:\x{1F1F9})|[\x{1F1E6}\x{1F1E7}\x{1F1EA}\x{1F1EC}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F7}\x{1F1F8}\x{1F1FA}\x{1F1FC}](?:\x{1F1F8})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EB}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F0}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1F7})|[\x{1F1E6}\x{1F1E7}\x{1F1EC}\x{1F1EE}\x{1F1F2}](?:\x{1F1F6})|[\x{1F1E8}\x{1F1EC}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F3}](?:\x{1F1F5})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1EE}\x{1F1EF}\x{1F1F2}\x{1F1F3}\x{1F1F7}\x{1F1F8}\x{1F1F9}](?:\x{1F1F4})|[\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}](?:\x{1F1F3})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F4}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FF}](?:\x{1F1F2})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1EE}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1F1})|[\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1ED}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FD}](?:\x{1F1F0})|[\x{1F1E7}\x{1F1E9}\x{1F1EB}\x{1F1F8}\x{1F1F9}](?:\x{1F1EF})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EB}\x{1F1EC}\x{1F1F0}\x{1F1F1}\x{1F1F3}\x{1F1F8}\x{1F1FB}](?:\x{1F1EE})|[\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1ED})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EA}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}](?:\x{1F1EC})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F9}\x{1F1FC}](?:\x{1F1EB})|[\x{1F1E6}\x{1F1E7}\x{1F1E9}\x{1F1EA}\x{1F1EC}\x{1F1EE}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F7}\x{1F1F8}\x{1F1FB}\x{1F1FE}](?:\x{1F1EA})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1EE}\x{1F1F2}\x{1F1F8}\x{1F1F9}](?:\x{1F1E9})|[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F8}\x{1F1F9}\x{1F1FB}](?:\x{1F1E8})|[\x{1F1E7}\x{1F1EC}\x{1F1F1}\x{1F1F8}](?:\x{1F1E7})|[\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F6}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}\x{1F1FF}](?:\x{1F1E6})|[\x{00A9}\x{00AE}\x{203C}\x{2049}\x{2122}\x{2139}\x{2194}-\x{2199}\x{21A9}-\x{21AA}\x{231A}-\x{231B}\x{2328}\x{23CF}\x{23E9}-\x{23F3}\x{23F8}-\x{23FA}\x{24C2}\x{25AA}-\x{25AB}\x{25B6}\x{25C0}\x{25FB}-\x{25FE}\x{2600}-\x{2604}\x{260E}\x{2611}\x{2614}-\x{2615}\x{2618}\x{261D}\x{2620}\x{2622}-\x{2623}\x{2626}\x{262A}\x{262E}-\x{262F}\x{2638}-\x{263A}\x{2640}\x{2642}\x{2648}-\x{2653}\x{265F}-\x{2660}\x{2663}\x{2665}-\x{2666}\x{2668}\x{267B}\x{267E}-\x{267F}\x{2692}-\x{2697}\x{2699}\x{269B}-\x{269C}\x{26A0}-\x{26A1}\x{26AA}-\x{26AB}\x{26B0}-\x{26B1}\x{26BD}-\x{26BE}\x{26C4}-\x{26C5}\x{26C8}\x{26CE}-\x{26CF}\x{26D1}\x{26D3}-\x{26D4}\x{26E9}-\x{26EA}\x{26F0}-\x{26F5}\x{26F7}-\x{26FA}\x{26FD}\x{2702}\x{2705}\x{2708}-\x{270D}\x{270F}\x{2712}\x{2714}\x{2716}\x{271D}\x{2721}\x{2728}\x{2733}-\x{2734}\x{2744}\x{2747}\x{274C}\x{274E}\x{2753}-\x{2755}\x{2757}\x{2763}-\x{2764}\x{2795}-\x{2797}\x{27A1}\x{27B0}\x{27BF}\x{2934}-\x{2935}\x{2B05}-\x{2B07}\x{2B1B}-\x{2B1C}\x{2B50}\x{2B55}\x{3030}\x{303D}\x{3297}\x{3299}\x{1F004}\x{1F0CF}\x{1F170}-\x{1F171}\x{1F17E}-\x{1F17F}\x{1F18E}\x{1F191}-\x{1F19A}\x{1F201}-\x{1F202}\x{1F21A}\x{1F22F}\x{1F232}-\x{1F23A}\x{1F250}-\x{1F251}\x{1F300}-\x{1F321}\x{1F324}-\x{1F393}\x{1F396}-\x{1F397}\x{1F399}-\x{1F39B}\x{1F39E}-\x{1F3F0}\x{1F3F3}-\x{1F3F5}\x{1F3F7}-\x{1F3FA}\x{1F400}-\x{1F4FD}\x{1F4FF}-\x{1F53D}\x{1F549}-\x{1F54E}\x{1F550}-\x{1F567}\x{1F56F}-\x{1F570}\x{1F573}-\x{1F57A}\x{1F587}\x{1F58A}-\x{1F58D}\x{1F590}\x{1F595}-\x{1F596}\x{1F5A4}-\x{1F5A5}\x{1F5A8}\x{1F5B1}-\x{1F5B2}\x{1F5BC}\x{1F5C2}-\x{1F5C4}\x{1F5D1}-\x{1F5D3}\x{1F5DC}-\x{1F5DE}\x{1F5E1}\x{1F5E3}\x{1F5E8}\x{1F5EF}\x{1F5F3}\x{1F5FA}-\x{1F64F}\x{1F680}-\x{1F6C5}\x{1F6CB}-\x{1F6D2}\x{1F6D5}\x{1F6E0}-\x{1F6E5}\x{1F6E9}\x{1F6EB}-\x{1F6EC}\x{1F6F0}\x{1F6F3}-\x{1F6FA}\x{1F7E0}-\x{1F7EB}\x{1F90D}-\x{1F93A}\x{1F93C}-\x{1F945}\x{1F947}-\x{1F971}\x{1F973}-\x{1F976}\x{1F97A}-\x{1F9A2}\x{1F9A5}-\x{1F9AA}\x{1F9AE}-\x{1F9CA}\x{1F9CD}-\x{1F9FF}\x{1FA70}-\x{1FA73}\x{1FA78}-\x{1FA7A}\x{1FA80}-\x{1FA82}\x{1FA90}-\x{1FA95}]/u', '', $string
        );
    }

    /**
     * @param string $string
     * @param int $words
     * @return bool
     */
    public static function check_big_words( string $string, int $words = 10 ) : bool
    {
        $words_text = explode( ' ', $string );
        foreach ( $words_text as $word )
        {

            if ( mb_strlen( $word, 'UTF-8' ) > $words )
            {

                return false;

            }

        }
        return true;
    }

    /**
     * @param string $string
     * @return bool
     */
    public static function isPersian( string $string )
    {
        return (bool) preg_match( '/^[\x{0621}-\x{0628}\x{062A}-\x{063A}\x{0641}-\x{0642}\x{0644}-\x{0648}\x{064E}-\x{0651}\x{0655}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06BE}\x{06CC}.؟!\s]+$/u', $string );
    }

}