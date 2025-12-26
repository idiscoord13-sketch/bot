<?php



require_once __DIR__ . '/../config.php';
const TOKEN_API = '2125201956:AAGkJwxRjyVh7963BbWrwMMKRzTejuFcBGY';
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../lib/plugin.php';

$telegram = new Telegram(TOKEN_API, false);
$link     = new Tdb(HOST, USERNAME, PASSWORD, DB_NAME);


$token_bot = require( BASE_DIR . '/bots.php' );
include BASE_DIR . "/library/User.php";
require_once BASE_DIR . '/plugin/plugin.php';

try
{


    if ( date('H:i') == '23:59' )
    {

        add_filter('filter_token', function() {
            return '5222799322:AAFY-k0i29yEXxTEzDJpta6UdI83vupezGY';
        });

        $list_users = get_top_rank_points_today(5);
        $today = date('Y-m-d');
        $coin = 25;
        foreach ( $list_users as $user )
        {

            $message = '🥳 تبریک [[user]]' . "\n \n";
            $message .= 'شما با کسب [[point]] امتیاز رتبه [[rank]] در رقابت روزانه را کسب کردید.' . "\n";
            $message .= 'پاداش شما [[coin]] سکه است و به حساب شما اضافه شد. 🌷';

            $result = (object) $telegram->sendMessage([
                'chat_id'    => $user->getUserId(),
                'text'       => __replace__($message, [
                    '[[user]]'  => $user->user()->name,
                    '[[point]]' => $user->get_point_daily_today(),
                    '[[rank]]'  => $user->get_rank_today(),
                    '[[coin]]'  => $coin
                ]),
                'parse_mode' => 'html'
            ]);

            if ( isset($result->ok) && $result->ok == false )
            {

                foreach ( $token_bot as $token )
                {

                    $telegram_user = new Telegram($token, false);
                    $result        = (object) $telegram_user->sendMessage([
                        'chat_id'    => $user->getUserId(),
                        'text'       => __replace__($message, [
                            '[[user]]'  => $user->user()->name,
                            '[[point]]' => $user->get_point_daily_today(),
                            '[[rank]]'  => $user->get_rank_today(),
                            '[[coin]]'  => $coin
                        ]),
                        'parse_mode' => 'html'
                    ]);

                    if ( isset($result->ok) && $result->ok == true ) break;

                }

            }

            SendMessage(ADMIN_LOG, __replace__($message, [
                '[[user]]'  => $user->user()->name,
                '[[point]]' => $user->get_point_daily_today(),
                '[[rank]]'  => $user->get_rank_today(),
                '[[coin]]'  => $coin
            ]));

            $user->add_coin($coin);
            $coin -= 5;

        }
        
        $link->query( "DELETE FROM `point_daily` WHERE `created_at` <= '" . date( 'Y-m-d', strtotime( '-2 day' ) ) . "'" );
        $link->where( 'status', 'closed' )->delete( 'server' );
        $link->where( 'status', 2 )->delete( 'bans' );

        $best_player_today = $link->get_result( "SELECT `selected` , count(`selected`) as `star` FROM `bestplayer_daily` WHERE `created_at` = '{$today}' GROUP BY `selected` ORDER by `star` DESC" );
        foreach ( $best_player_today as $user) {
            $message = '🥳 تبریک ' . "\n \n";
            $message .= 'شما امروز ⭐️ [[star]] ⭐️ ستاره کسب کردید و پاداش شما  [[coin]] سکه است که حساب شما اضافه شد . 🌷' . "\n";
            $coin = 0;
            if ($user->star < 5 ) 
                continue;

            $coin = floor($user->star / 5);
            
            
            if ($coin  > 0) {
                add_coin( $user->selected, $coin );
                    
                $result = (object) $telegram->sendMessage([
                    'chat_id'    => $user->selected,
                    'text'       => __replace__($message, [
                        '[[star]]' => $user->star,
                        '[[coin]]'  => $coin
                    ]),
                    'parse_mode' => 'html'
                ]);

                if ( isset($result->ok) && $result->ok == false )
                {

                    foreach ( $token_bot as $token )
                    {

                        $telegram_user = new Telegram($token, false);
                        $result        = (object) $telegram_user->sendMessage([
                            'chat_id'    => $user->selected,
                            'text'       => __replace__($message, [
                                '[[star]]' => $user->star,
                                '[[coin]]'  => $coin
                            ]),
                            'parse_mode' => 'html'
                        ]);

                        if ( isset($result->ok) && $result->ok == true ) break;

                    }

                }
                $message .= "userID : {$user->selected}";
                SendMessage(ADMIN_LOG, __replace__($message, [
                    // '[[user]]'  => $user->selected,
                    '[[star]]' => $user->star,
                    '[[coin]]'  => $coin
                ]));
            }
        }
        
    }

} catch ( Exception|ErrorException|Throwable|ArithmeticError  $e )
{
    $message = "<u>ERROR ON FILE CRON JOB POINT!</u>" . "\n \n";
    $message .= "<i>ERROR LINE: {" . $e->getLine() . "}</i>" . "\n \n";
    $message .= "<i>ERROR File: {" . $e->getFile() . "}</i>" . "\n \n";
    $message .= "<i>ERROR Code: {" . $e->getCode() . "}</i>" . "\n \n";
    $message .= "<b>CONTACT ERROR: [" . $e->getMessage() . "]</b>";
    SendMessage(ADMIN_LOG, $message, null, null, 'html');
}

exit('SUCCESS');