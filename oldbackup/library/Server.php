<?php

namespace library;

use Base;

if ( ! class_exists( 'library\Server' ) )

{

    /**

     * Class Server

     * @package library

     * @property int $cron

     * @property string $type

     * @property int $league_id

     * @property int $count

     * @property int $bot

     * @property string $status

     * @property string $created_at

     * @property string $updated_at

     */

    class Server

    {

        use Base;

        /**

         * @return false|object|null

         */

        public function getStatus()

        {

            return $this->getMeta( 'status' );

        }

        /**

         * @param object $users

         * @return User[]

         */

        private function buildingUsers( object $users ) : array

        {

            $list_users = [];

            foreach ( $users as $user )

            {

                if ( isset( $user->user_id ) )

                {

                    $list_users[] = new User( $user->user_id, $this->id );;

                }

            }
            // shuffle($list_users);
            return $list_users;

        }

        /**

         * @return User[]

         */

        public function users() : array

        {

            return $this->buildingUsers( (object) get_users_by_server( $this->id ) );

        }

        /**

         * @param string $key

         * @return false|object|null

         */

        public function getMeta( string $key )

        {

            return get_server_meta( $this->id, $key );

        }

        /**

         * @param string $key

         * @return false|object|null

         */

        public function getMetaUser( string $key )

        {

            return get_server_meta( $this->id, $key, $this->user_id );

        }

        /**

         * @param string $key

         * @param string $value

         * @return $this

         * @throws \Exception

         */

        public function updateMeta( string $key, string $value ) : Server

        {

            update_server_meta( $this->id, $key, $value );

            return $this;

        }

        /**

         * @return $this

         * @throws \Exception

         */

        public function resetSelect() : Server

        {

            global $link;

            $link->where( 'server_id', $this->id )->Where( 'meta_key', 'kill' )->delete( 'server_meta' );

            $link->where( 'server_id', $this->id )->Where( 'meta_key', 'select' )->delete( 'server_meta' );

            $link->where( 'server_id', $this->id )->Where( 'meta_key', 'select-2' )->delete( 'server_meta' );

            $link->where( 'server_id', $this->id )->Where( 'meta_key', 'attacker' )->delete( 'server_meta' );

            return $this;

        }

        /**

         * @param string $key

         * @param string $value

         * @return $this

         * @throws \Exception

         */

        public function updateMetaUser( string $key, string $value ) : Server

        {

            update_server_meta( $this->id, $key, $value, $this->user_id );

            return $this;

        }

        /**

         * @param string $key

         * @return $this

         * @throws \Exception

         */

        public function deleteMeta( string $key ) : Server

        {

            delete_server_meta( $this->id, $key );

            return $this;

        }

        /**

         * @param string $key

         * @return $this

         * @throws \Exception

         */

        public function deleteMetaUser( string $key ) : Server

        {

            delete_server_meta( $this->id, $key, $this->user_id );

            return $this;

        }

        /**

         * @param int $seconds

         * @return $this

         * @throws \Exception

         */

        public function charge( int $seconds ) : Server

        {

            $this->updateMeta( 'next-time', time() + $seconds );

            return $this;

        }

        /**

         * @param string $status

         * @return $this

         * @throws \Exception

         */

        public function setStatus( string $status ) : Server

        {

            $this->updateMeta( 'status', $status );

            return $this;

        }

        /**

         * @return $this

         * @throws \Exception

         */

        public function clearVotes() : Server

        {

            /*$voter = new Vote($this);

            $voter->clear();*/

            return $this;

        }

        /**

         * @return $this

         * @throws \Exception

         */

        public function clearVotesMeta() : Server

        {

            global $link;

            $link->where( 'server_id', $this->id )->where( 'meta_key', 'vote' )->delete( 'server_meta' );

            return $this;

        }

        /**

         * @return int

         */

        public function day() : int

        {

            return (int) $this->getMeta( 'day' );

        }

        /**

         * @param int $id

         * @return User[]

         */

        public function roleByGroup( int $id ) : array

        {

            return $this->buildingUsers( (object) get_role_by_group( $this->id, $id ) );

        }

        /**

         * @param int $user_id

         * @param int $group_id

         * @return string

         * @throws \Exception

         */

        public function showTeam( int $user_id, int $group_id = 2 ) : string

        {

            $message        = '';

            $bazpors_select = ( new Role( $this->id ) )->user()->select( ROLE_Bazpors );

            $shot           = $this->get_priority();

            foreach ( $this->roleByGroup( $group_id ) as $item )

            {

                if ( ! $item->is( $user_id ) )

                {

                    if ( ! $item->dead() )

                    {

                        $message .= '[[role]] : ' . $item->get_name() . ' ' . ( $item->is( $bazpors_select ) ? '{ زندانی }' : '' ) . ' ' . ( $shot->id == $item->getRoleId() ? ( '🔫' . ( $this->setUserId( ROLE_Godfather )->getMetaUser( 'super-god-father' ) == 'on' ? '🔫' : '' ) ) : '' ) . "\n";

                    }

                    else

                    {

                        $message .= "<s>" . '[[role]] : ' . $item->get_name() . "☠️</s>" . "\n";

                    }

                }

                else

                {

                    $message .= '[[role]] : ' . $item->get_name() . ' {شما} ' . ( $shot->id == $item->getRoleId() ? '🔫' . ( $this->setUserId( ROLE_Godfather )->getMetaUser( 'super-god-father' ) == 'on' ? '🔫' : '' ) : '' ) . "\n";

                }

                __replace__( $message, [

                    '[[role]]' => "<b>" . $item->get_role()->icon . "</b>"

                ] );

            }

            return $message . "\n";

        }

        /**

         * @param callable $callable

         * @param int $group_id

         * @return $this

         */

        public function teamCall( callable $callable, int $group_id = 2 )

        {

            foreach ( $this->roleByGroup( $group_id ) as $user )

            {

                $this->setUserId( $user->getUserId() );

                call_user_func( $callable, $user, $this );

            }

            return $this;

        }

        /**

         * @return User[][]|int[][]

         */

        public function votes() : array

        {

            $votes      = get_votes_by_server( $this->id );

            $vote_users = [];

            foreach ( $votes as $vote )

            {

                if ( isset( $vote->meta_value ) && isset( $vote->user_id ) && $vote->user_id > 0 )

                {

                    $vote_users[ $vote->meta_value ][] = $this->isObject ? new User( $vote->user_id, $this->id ) : $vote->user_id;

                }

            }

            return $vote_users;

        }

        /**

         * @return User[]

         */

        public function getDeadUsers() : array

        {

            return $this->buildingUsers( (object) get_dead_body( $this->id ) );

        }

        /**

         * @return int

         */

        public function getPeopleAlive() : int

        {

            return count( get_users_by_server( $this->id ) ) - count( $this->getDeadUsers() );

        }

        /**

         * @var bool

         */

        private bool $isObject = true;

        /**

         * @return $this

         */

        public function getObject()

        {

            $this->isObject = true;

            return $this;

        }

        /**

         * @return $this

         */

        public function getInt()

        {

            $this->isObject = false;

            return $this;

        }

        private bool $persianInt = false;

        /**

         * @param bool $persianInt

         * @return Server

         */

        public function PersianInt() : Server

        {

            $this->persianInt = true;

            return $this;

        }

        /**

         * @param string $message

         * @return $this

         */

        public function sendMessage( string $message ) : Server

        {

            if ( ! $this->persianInt )

            {

                add_filter( 'send_massage_text', function ( $text ) {

                    return tr_num( $text, 'en', '.' );

                }, 11 );

            }

            foreach ( $this->users() as $user )

            {

                ! $user->is_user_in_game() || $user->SendMessage( $message );

            }

            return $this;

        }

        /**

         * @param string $message

         * @return $this

         */

        public function sendMessageHtml( string $message ) : Server

        {

            foreach ( $this->users() as $user )

            {

                ( ! $user->is_user_in_game() || $user->sleep() ) || $user->SendMessageHtml( $message );

            }

            return $this;

        }

        /**

         * @return int

         */

        public function getCountCity() : int

        {

            return $this->getCountGroup( 1 );

        }

        /**

         * @return int

         */

        public function getCountDeadCity() : int

        {

            return $this->getCountDeadGroup( 1 );

        }

        /**

         * @return int

         */

        public function getCountAmazing() : int

        {

            return $this->getCountGroup( 4 );

        }

        /**

         * @return int

         */

        public function getCountDeadAmazing() : int

        {

            return $this->getCountDeadGroup( 4 );

        }

        /**

         * @param int $group_id

         * @return int

         */

        public function getCountGroup( int $group_id ) : int

        {

            global $link;

            return (int) $link->get_var(

                "SELECT COUNT(*) FROM `role` 

                                                INNER JOIN `server_role` ON 

                                                    `server_role`.`server_id` = {$this->id} AND 

                                                    `server_role`.`role_id` = `role`.`id` AND 

                                                    `role`.`group_id` = {$group_id}"

            );

        }

        /**

         * @param int $group_id

         * @return int

         */

        public function getCountDeadGroup( int $group_id ) : int

        {

            global $link;

            return (int) $link->get_var(

                "SELECT COUNT(*) FROM `role` 

    INNER JOIN `server_role` ON `server_role`.`server_id` = {$this->id} AND `server_role`.`role_id` = `role`.`id` AND `role`.`group_id` = {$group_id}

    INNER JOIN `server_meta` ON `server_meta`.`server_id` = {$this->id} AND `server_meta`.`user_id` = `server_role`.`user_id` AND 

                                `server_meta`.`meta_key` = 'status' AND ( `server_meta`.`meta_value` = 'dead' OR `server_meta`.`meta_value` = 'killed')" 

            );

        }

        /**

         * @return int

         */

        public function getCountTerror() : int

        {

            return $this->getCountGroup( 2 );

        }

        /**

         * @return int

         */

        public function getCountDeadTerror() : int

        {

            return $this->getCountDeadGroup( 2 );

        }

        /**

         * @return int

         */

        public function getCountMostaghel() : int

        {

            return $this->getCountGroup( 3 );

        }

        /**

         * @return int

         */

        public function getCountDeadMostaghel() : int

        {

            return $this->getCountDeadGroup( 3 );

        }

        /**

         * @param string $prefix

         * @return bool

         * @throws \Exception

         */

        public function checkStatusGame( string $prefix = '' ) : bool

        {

            return check_status_game( get_server( $this->id ), $prefix );

        }

        /**

         * @return bool

         * @throws \Exception

         */

        public function close() : bool

        {

            

            

            foreach ( $this->users() as $user )

            {

                if ( $user->get_meta( 'game-count' ) <= 1 )

                {

                    $sub_user = $user->get_meta( 'sub_user' );

                    if ( ! empty( $sub_user ) && is_numeric( $sub_user ) && user_exists( $sub_user ) )

                    {

                        $message = '💰 10 سکه هدیه ی دعوت برای شما اضافه شد .' . "\n" . 'امیدواریم اوقات خوشی را در این ربات تجربه کنید 🌷';

                        $user->SendMessageHtml( $message )->add_coin( 0 );

                        $message = '💡 کاربر [[user]] شناسه شما را به عنوان دعوت کننده وارد کرد و 💰 10 سکه به حساب شما اضافه شد.';

                        SendMessage(

                            $sub_user, __replace__( $message, [

                            '[[user]]' => "<u>" . $user->user()->name . "</u>"

                        ] ), null, null, 'html'

                        );

                        add_coin( $sub_user, 10 );

                        $log = 'کاربر [user] کد کاربر را وارد کرد و 10 سکه دریافت کرد.';

                        add_log(

                            'sub_user', __replace__( $log, [

                            '[user]' => $sub_user

                        ] ), $user->getUserId()

                        );

                        $sub_user = new User( $sub_user );

                        $sub_user->update_meta( 'user-count', ( (int) $user->get_meta( 'user-count' ) + 1 ) );

                    }

                }

                if ( ! $user->removeRole()->is_user_in_game() ) continue;

                $message = '🔚 بازی بسته شد .' . "\n \n" . 'منوی اصلی 👇';

                $user->setKeyboard( KEY_START_MENU )->SendMessage( $message )->logout()->setStatus( '' );

            }

            $this->remove();

            return true;

        }

        /**

         * @return $this

         * @throws \Exception

         */

        private function remove() : Server

        {

            global $link;

            $link->where( 'id', $this->id )->delete( 'server' );

            $link->where( 'server_id', $this->id )->delete( 'server_meta' );

            $link->where( 'server_id', $this->id )->delete( 'server_role' );

            $link->where( 'server_id', $this->id )->delete( 'user_name' );

            $link->where( 'server_id', $this->id )->delete( 'names' );

            return $this;

        }

        /**

         * @throws \Exception

         */

        public function logout() : void

        {

            foreach ( $this->users() as $user )

            {

                ! $user->is_user_in_game() || logout_server( $user->getUserId() );

            }

        }

        /**

         * @param string $message

         * @return bool

         * @throws \Exception

         */

        public function addChat( string $message ) : bool

        {

//            do_action('report_game', $message, $this->user_id);

            return add_chat( $this->user_id, $this->id, $message );

        }

        /**

         * @param int $group_id

         * @return false|\helper\Role|object|null

         */

        public function get_priority( int $group_id = 2 )

        {

            return get_priority_attacker( $this->id, $group_id );

        }

        /**

         * @param int $role_id

         * @return bool

         */

        public function role_exists( int $role_id ) : bool

        {

            return role_exists( $role_id, $this->id );

        }

        /**

         * @param callable $callable

         * @return $this

         */

        public function usersCall( callable $callable ) : Server

        {

            foreach ( $this->users() as $user )

            {

                $this->setUserId( $user->getUserId() );

                ! $user->is_user_in_game() || call_user_func( $callable, $user, $this );

            }

            return $this;

        }

        /**

         * @return $this

         * @throws \Exception

         */

         public function magicsOff() : Server

        {
            foreach ( $this->users() as $user )
            {
                if ( $this->setUserId( $user->getUserId() )->getMetaUser( 'warning' ) == 'on' )
                {

                    $this->updateMetaUser( 'warning', 'off' );

                }
                if ( $this->setUserId( $user->getUserId() )->getMetaUser( 'shield' ) == 'on' )
                {

                    $this->updateMetaUser( 'shield', 'off' );

                }
                // if ( $this->setUserId( $user->getUserId() )->getMetaUser( 'no-vote' ) == 'on' )
                // {

                //     $this->updateMetaUser( 'no-vote', 'off' );

                // }

            }

            return $this;

        }
        public function magicVotes() : Server

        {

            foreach ( $this->users() as $user )

            {

                if ( $this->setUserId( $user->getUserId() )->getMetaUser( 'no-vote' ) == 'on' )

                {

                    $this->updateMetaUser( 'no-vote', 'off' );

                }

            }

            return $this;

        }

        /**

         * @return $this

         * @throws \Exception

         */

        public function nextDay() : Server

        {

            $this->updateMeta( 'day', $this->day() + 1 );

            return $this;

        }

        /**

         * @return User[]

         */

        public function getGuilt() : array

        {

            global $link;

            $result = $link->get_result( "SELECT * FROM `server_meta` WHERE `server_id` = {$this->id} AND `meta_key` = 'vote' AND `meta_value` = 'court'" );

            return $this->buildingUsers( (object) $result );

        }

        /**

         * @return User[]

         */

        public function getInnocent() : array

        {

            global $link;

            $result = $link->get_result( "SELECT * FROM `server_meta` WHERE `server_id` = {$this->id} AND `meta_key` = 'vote' AND `meta_value` = '^court'" );

            return $this->buildingUsers( (object) $result );

        }

        /**

         * @param int $user_id

         * @param string $select

         * @return array|User[]

         */

        public function getListAttacker( int $user_id, string $select = 'select' ) : array

        {

            return get_list_attacker_by_user( $user_id, $this->id, $select );

        }

        /**

         * @return Role[]

         */

        public function selects() : array

        {

            global $link;

            $results = $link->get_result( "SELECT * FROM `server_meta` WHERE `server_id` = {$this->id} AND `meta_key` = 'select'" );

            $users   = [];

            foreach ( $results as $item )

            {

                $users[] = ( new Role( $this->id, $item->user_id ) )->setSelect( $item->meta_value );

            }

            return $users;

        }

        /**

         * @param int $user_id

         * @return $this

         * @throws \Exception

         */

        public function kill( int $user_id ) : Server

        {

            kill( $this->id, $user_id );

            return $this;

        }

        /**

         * @param int $id

         * @return array|false

         */

        public function listAttacker( int $id = 2 )

        {

            return get_attacker_list( $this->id, $id );

        }

        /**

         * @return bool

         */

        public function is() : bool

        {

            return $this->getMeta( 'is' ) == 'on';

        }

        /**

         * @return User

         */

        public function accused() : User

        {

            return new User( (int) $this->getMeta( 'accused' ), $this->getId() );

        }

        /**

         * @return $this

         * @throws \Exception

         */

        public function resetMessage() : Server

        {

            global $link;

            $link->where( 'server_id', $this->id )->where( 'meta_key', 'message-sended' )->delete( 'server_meta' );

            return $this;

        }

        /**

         * @return false|\helper\Server|object|null

         */

        public function server()

        {

            return get_server( $this->id );

        }

        /**

         * @return false|object|null

         */

        public function get_league()

        {

            return get_league( $this->league_id ?? 1 );

        }

        /**

         * @return int

         */

        public function count() : int

        {

            return get_count_member_on_server( $this->id );

        }

        /**

         * @return Server

         */

        public static function getServerOrderByID() : Server

        {

            global $link;

            $server = $link->get_row( "SELECT * FROM `server` WHERE `status` = 'opened' AND `type` = 'public' ORDER BY `count` DESC , `id` ASC LIMIT 1" );

            return new Server( $server->id ?? 0 );

        }

        /**

         * @param int $game_id

         * @return Server

         */

        public static function getServerOrderByLeague( int $game_id ) : Server

        {

            global $link;

            $server = $link->get_row( "SELECT * FROM `server` WHERE `status` = 'opened' AND `league_id` <= {$game_id} AND `type` = 'public' ORDER BY `count` DESC , `id` ASC LIMIT 1" );

            return new Server( $server->id ?? 0 );

        }

        /**

         * @param int $league

         * @return Server

         * @throws \Exception

         */

        public static function getServerByLeague( int $league ) : Server

        {

            $server = get_server_by_league( $league );

            return new Server( $server );

        }

        /**

         * @return bool

         */

        public function isFullMoon() : bool

        {

            return $this->day() % 2 == 0;

        }

        /**

         * @var array|string[]

         */

        protected array $status_server = [

            'started' => 'در حال بازی',

            'closed'  => 'بسته شده',

            'opened'  => 'در حال عضوگیری'

        ];

        /**

         * @return string

         */

        public function toStringStatusServer() : string

        {

            return $this->status_server[ $this->status ] ?? 'نا مشخص';

        }

        /**

         * @var array|string[]

         */

        protected array $status_game = [

            'night'    => 'در حال شب شدن',

            'light'    => 'در حال روز شدن',

            'message'  => 'چت کردن',

            'voting'   => 'در حال رای گیری',

            'court'    => 'دادگاه',

            'court-2'  => 'دادگاه',

            'court-3'  => 'دادگاه دوم',

            'chatting' => 'چت آخر بازی',

            'none'     => 'هنوز ظرفیت تکمیل نشده است'

        ];

        /**

         * @return string

         */

        public function toStringStatusGame() : string

        {

            return $this->status_game[ $this->getStatus() ?? 'none' ];

        }

        /**

         * @return int

         */

        public function getUserId() : ?int

        {

            return $this->user_id;

        }

        /**

         * @return User[]

         */

        public function usersByGame() : array

        {

            global $link;

            return $this->buildingUsers( (object) $link->get_result( "SELECT * FROM `user_game` WHERE `server_id` = {$this->id}" ) );

        }

        /**

         * @return int

         */

        public function who_is_shot() : int

        {

            $selector = new Role( $this );

            if ( $selector->user()->select( ROLE_Godfather )->getUserId() > 0 )

            {

                $god_father = $selector->getUser( ROLE_Godfather );

                if ( ! $god_father->dead() && ! $god_father->in_prisoner() )

                {

                    $mashoghe = $selector->getUser( ROLE_Mashooghe );

                    if ( $mashoghe->dead() || $mashoghe->in_prisoner() )

                        return ROLE_Godfather;

                    else

                        return ROLE_Mashooghe;

                }

            }

            elseif ( $selector->user()->select( ROLE_Mashooghe )->getUserId() > 0 )

            {

                return ROLE_Mashooghe;

            }

            return $this->get_priority()->id;

        }

        /**

         * @return bool

         */

        public function exists() : bool

        {

            return (bool) get_server( $this->id );

        }

        /**

         * @param array $users

         * @param array $groups_id

         * @return User

         */

        public function randomUser( array $users = [], array $groups_id = [] ) : User

        {

            $users_server = $this->users();

            $rand_user    = array_rand( $users_server );

            while ( is_null($users_server[ $rand_user ]) || in_array( $users_server[ $rand_user ]->getUserId(), $users ) || in_array( $users_server[ $rand_user ]->get_role()->group_id, $groups_id ) ) $rand_user = array_rand( $users_server );

            return $users_server[ $rand_user ];

        }

        /**

         * @return int[]

         * @throws \Exception

         */

        public function getTargetsJalad()

        {

            $target = $this->setUserId( ROLE_Jalad )->getMetaUser( 'jalad' );

            if ( empty( $target ) )

            {

                $rand_user_1 = $this->randomUser();

                $rand_user_2 = $this->randomUser( [ $rand_user_1->getUserId() ] );

                $target = json_encode( [ $rand_user_1->getUserId(), $rand_user_2->getUserId() ] );

                $this->updateMetaUser( 'jalad', $target );

            }

            return json_decode( $target );

        }

        /**

         * @param int $user_id

         * @return void

         * @throws \Exception

         */

        public function checkJaladTarget( int $user_id )

        {

            $targets  = $this->getTargetsJalad();

            $selector = new Role( $this );

            $jalad    = $selector->getUser( ROLE_Jalad );

            if ( isset( $targets ) && is_array( $targets ) && in_array( $user_id, $targets ) )

            {

                $jalad->changeRole( ROLE_Joker );

            }

        }

        /**

         * @return bool

         * @throws \Exception

         */

        public function checkJaladTargets() : bool

        {

            $targets = $this->getTargetsJalad();

            if ( isset( $targets ) && is_array( $targets ) && count( $targets ) == 2 )

            {

                $selector = new Role( $this );

                $jalad    = $selector->getUser( ROLE_Jalad );

                if ( ! $jalad->dead() && dead( $this->id, $targets[ 0 ] ) && dead( $this->id, $targets[ 1 ] ) )

                {

                    $this->updateMeta( 'jalad', 'on' );

                    $jalad->kill();

                    return true;

                }

            }

            return false;

        }

        /**

         * @return void

         * @throws \Exception

         */

        public function clearSelect( array $groups = [ 2 ] )

        {

            global $link;

            $selects = $link->get_result( "SELECT * FROM `server_meta` WHERE `server_id` = {$this->id} AND `meta_key` = 'select'" );

            foreach ( $selects as $select )

            {

                if ( ! in_array( get_role( $select->user_id )->group_id, $groups ) )

                {

                    $link->where( 'id', $select->id )->delete( 'server_meta' );

                }

            }

        }

    }

}