<?php

use library\User;


if ( $fromid == $data[2] )
{

    $User = new User($fromid);

    $league_game = get_league($data[1]);


    if ( $league_game->point <= $User->get_point() )
    {


        if ( empty($User->getServerId()) )
        {

            $server_id = add_user_server($User->getUserId(), $league_game->id);

            if ( is_numeric($server_id) )
            {

                $message = 'بازی دوستانه شما ایجاد شد.' . "\n \n" . '⏰ مدت زمان عضوگیری: 30 دقیقه';
                SendMessage($User->setStatus('get_users_server')->getUserId(), $message, KEY_HOST_GAME_MENU);

                update_server($server_id, [
                    'type' => 'private'
                ]);

                $code = $server_id;
                $i    = rand(1, 9);

                /** @var $update \helper\Update */
                bot('editMessageReplyMarkup', [

                    'inline_message_id' => $update->callback_query->inline_message_id,
                    'reply_markup'      => $telegram->buildInlineKeyBoard([
                        [
                            $telegram->buildInlineKeyboardButton('↗️ پیوستن به بازی ↗️', 'https://telegram.me/' . GetMe()->username . '?start=server-' . string_encode($server_id) . '-' . $i)
                        ]
                    ])

                ]);

            }
            else
            {

                AnswerCallbackQuery($dataid, '🤕 متاسفم مشکلی رخ داد، لطفا دوباره تلاش کنید.');

                throw new Exception('ERROR ON CREATE SERVER FRIEND USER : ' . $fromid);

            }

        }
        else
        {

            AnswerCallbackQuery($dataid, '🚫 شما قبلا به یک بازی پیوسته اید.');

        }

    }
    else
    {

        AnswerCallbackQuery($dataid, '🚫 شما امتیاز کافی برای ساخت بازی ' . $league_game->icon . ' را ندارید.');

    }

}
else
{

    AnswerCallbackQuery($dataid, '🚫 تنها سازنده بازی میتواند بازی را شروع کند.');

}