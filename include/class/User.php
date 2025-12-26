<?php


namespace library;


use ExceptionError;
use ExceptionMessage;
use ExceptionWarning;

if (!class_exists('library\User')) {
    /**
     * Class User
     * @package library
     */
    class User
    {
        /**
         * @var int $user_id
         */
        private int $user_id;

        /**
         * @var int $server_id
         */
        private int $server_id;

        /**
         * @var int $point
         */
        private int $point;

        /**
         * @var int $limit
         */
        private int $limit;

        /**
         * @return int
         */
        public function getUserId(): ?int
        {
            return (int)$this->user_id;
        }

        /**
         * @param int $user_id
         * @return $this
         */
        public function setUserId(int $user_id): User
        {
            $this->user_id = $user_id;
            return $this;
        }

        /**
         * @param int $server_id
         * @return $this
         */
        public function setServerId(int $server_id): User
        {
            $this->server_id = $server_id;
            return $this;
        }

        /**
         * @return int
         */
        public function getServerId(): int
        {
            return $this->server_id;
        }

        /**
         * @param int $limit
         * @return $this
         */
        public function setLimit(int $limit): User
        {
            $this->limit = $limit;
            return $this;
        }

        /**
         * @return int
         */
        public function getLimit(): int
        {
            return $this->limit;
        }

        /**
         * User constructor.
         * @param int $user_id
         * @param int $server_id
         */
        public function __construct(int $user_id, int $server_id = 0)
        {
            $this->user_id = $user_id;
            $this->server_id = $server_id;
            if ($server_id == 0 && $this->user_on_game()) {
                $game = get_game($user_id);
                if (isset($game->server_id)) {
                    $this->server_id = (int)$game->server_id;
                }
            }
        }

        /**
         * @return false|\helper\Users
         */
        public function user()
        {
            return get_user($this->user_id);
        }

        /**
         * @param array $data
         * @return $this
         * @throws \Exception
         */
        public function update_user(array $data): User
        {
            try {
                update_user($data, $this->user_id);
            } catch (\Exception $exception) {
                throw new \Exception("Error To Update User " . $this->user_id);
            }
            return $this;
        }

        /**
         * @return bool
         */
        public function user_on_game(): bool
        {
            return is_user_row_in_game($this->user_id);
        }

        /**
         * @return \helper\Role
         */
        public function get_role()
        {
            return get_role_user_server($this->server_id, $this->user_id);
        }

        /**
         * @return int
         * @throws \Exception
         */
        public function getRoleId(): int
        {
            return $this->get_role()->id ?? 0;
        }

        /**
         * @return string
         */
        public function encode(): string
        {
            return string_encode($this->user_id);
        }

        /**
         * @return int
         */
        public function get_point(): int
        {
            $this->point = (int)get_point($this->user_id);
            return is_numeric($this->point) ? $this->point : 0;
        }

        /**
         * @param int $point
         * @return $this
         * @throws \Exception
         */
        public function add_point(int $point): User
        {
            if ($point >= 0) {
                add_point($this->server_id, $this->user_id, $point);
            } else {
                throw new \Exception("The +point can not be less than 0 User " . $this->user_id);
            }
            return $this;
        }

        /**
         * @param int $point
         * @return $this
         * @throws \Exception
         */
        public function demote_point(int $point): User
        {
            global $link;
            if ($point > 0) {

                if ($this->get_point() - $point >= 0) {

                    $link->where('user_id', $this->user_id)->update('points', [
                        'point' => $this->get_point() - $point,
                    ]);

                }

            } else {
                throw new \Exception("The -point can not be less than 0 User " . $this->user_id);
            }
            return $this;
        }

        /**
         * @return int
         */
        public function get_coin(): int
        {
            return $this->user()->coin ?? 0;
        }

        /**
         * @param int $coin
         * @return $this
         * @throws \Exception
         */
        public function set_coin(int $coin): User
        {
            if ($coin >= 0) {
                return $this->update_user(['coin' => $coin]);
            } else {
                throw new \Exception("The Set point can not be less than 0 User " . $this->user_id);
            }
        }

        /**
         * @param int $point
         * @return $this
         * @throws \Exception
         */
        public function add_coin(int $coin): User
        {
            if ($coin > 0) {
                add_coin($this->user_id, $coin);
            } else {
                throw new \Exception("The coin can not be less than 0 User " . $this->user_id);
            }
            return $this;
        }

        /**
         * @return bool
         * @throws \Exception
         */
        public function is_ban(): bool
        {
            return check_ban($this->user_id);
        }

        /**
         * @return $this
         * @throws \Exception
         */
        public function checkBaned(): User
        {
            if (!$this->is_ban()) {

                global $link;
                $status = $link->get_row("SELECT * FROM `bans` WHERE `user_id` = {$this->user_id} AND `status` = 1 ORDER BY `id` DESC LIMIT 1");
                $date = time_to_string($status->end_time);
                $message = '📛 اکانت شما تا ' . '<u>' . $date . '</u>' . ' دیگر مسدود است .';
                $this->SendMessageHtml($message);
                exit();

            }

            return $this;
        }

        /**
         * @return string|null
         * @throws \Exception
         */
        public function time_ban(): ?string
        {
            if ($this->is_ban()) {
                global $link;
                $time_ban = $link->get_row("SELECT * FROM `bans` WHERE `user_id` = {$this->user_id} AND `status` = 1 ORDER BY `id` DESC LIMIT 1");
                return time_to_string($time_ban->end_time);
            }
            return null;
        }

        /**
         * @return bool
         * @throws \Exception
         */
        public function add_to_game(): bool
        {
            return add_user_to_server($this->server_id, $this->user_id);
        }

        /**
         * @param string $key
         * @return false|object|null
         */
        public function get_meta(string $key)
        {
            return get_user_meta($this->user_id, $key);
        }

        /**
         * @param string $key
         * @param string $value
         * @return $this
         * @throws \Exception
         */
        public function update_meta(string $key, string $value): User
        {
            try {
                update_user_meta($this->user_id, $key, $value);
            } catch (\Exception $exception) {
                throw new \Exception("Error To Update User Meta User : " . $this->user_id . ' Meta Key: ' . $key);
            }
            return $this;
        }

        /**
         * @param string $key
         * @return $this
         * @throws \Exception
         */
        public function delete_meta(string $key): User
        {
            try {
                delete_user_meta($this->user_id, $key);
            } catch (\Exception $exception) {
                throw new \Exception("Error To Delete User Meta User : " . $this->user_id . ' Meta Key: ' . $key);
            }
            return $this;
        }

        /**
         * @return User
         */
        public function update(): User
        {
            $user = new User($this->user_id);
            $this->setServerId($user->getServerId());
            return $this;
        }

        /**
         * @return false|object|null
         */
        public function get_league()
        {
            return get__league_user($this->user_id);
        }

        /**
         * @return false|object|null
         */
        public function league()
        {
            global $link;
            return $link->get_row("SELECT `league`.* FROM `league` INNER JOIN `users` ON `league`.`point` <= `users`.`point` AND `users`.`user_id` = {$this->user_id} ORDER By `point` DESC LIMIT 1");
        }

        /**
         * @return false|object|null
         */
        public function get_game()
        {
            return get_league_user($this->user_id);
        }

        /**
         * @return bool
         */
        public function user_exists(): bool
        {
            return user_exists($this->user_id);
        }

        /**
         * @return false|mixed
         */
        public function get_name()
        {
            return name($this->user_id, $this->server_id);
        }

        /**
         * @param string $name
         * @return $this
         * @throws \Exception
         */
        public function add_name(string $name)
        {
            try {
                add_name($this->user_id, $this->server_id, $name);
            } catch (\Exception $exception) {
                throw new \Exception("ERROR To Add Name User : " . $this->user_id);
            }
            return $this;
        }

        /**
         * @return $this
         * @throws \Exception
         */
        public function reset(): User
        {
            try {
                reset_user($this->user_id);
            } catch (\Exception $exception) {
                throw new \Exception("Error To Reset User : " . $this->user_id);
            }
            return $this;
        }

        /**
         * @return array|false|\helper\Chat
         */
        public function chats()
        {
            return get_chats($this->user_id, $this->limit);
        }

        /**
         * @return string
         * @throws \Exception
         */
        public function token_security(): string
        {
            return token_security_user($this->user_id);
        }

        /**
         * @param User $user
         * @return bool
         * @throws \Exception
         */
        public function move_account(User $user): bool
        {
            try {
                move_account($this->user_id, $user->getUserId());
            } catch (\Exception $e) {
                throw new \Exception("ERROR To Move Account From " . $this->user_id . ' To: ' . $user->getUserId());
            }
            return true;
        }

        /**
         * @param int $start_time
         * @param int $end_time
         * @param int $report_id
         * @return int
         * @throws \Exception
         */
        public function baned(int $start_time, int $end_time, int $report_id): int
        {
            return add_ban($this->user_id, $start_time, $end_time, $report_id);
        }

        /**
         * @return $this
         * @throws \Exception
         */
        public function unban(): User
        {
            try {
                unban($this->user_id);
            } catch (\Exception $exception) {
                throw new \Exception("ERROR To Unban User : " . $this->user_id);
            }
            return $this;
        }

        /**
         * @return bool
         */
        public function is_user_in_game(): bool
        {
            return is_user_in_game($this->server_id, $this->user_id);
        }

        /**
         * @return false|int|string
         */
        public function get_rank_global()
        {
            return get_rank_user_in_global($this->user_id);
        }

        /**
         * @return int
         */
        public function get_point_daily_today(): int
        {
            return (int)get_point_user_day($this->user_id, date('Y-m-d'), '=');
        }

        /**
         * @return false|int|string
         */
        public function get_rank_today()
        {
            return get_rank_user_today($this->user_id);
        }

        /**
         * @return int
         */
        public function get_point_user_week(): int
        {
            return (int)get_point_user_week($this->user_id);
        }

        /**
         * @return int|string
         */
        public function get_rank_week()
        {
            return get_rank_user_week($this->user_id);
        }

        /**
         * @return bool
         */
        public function dead(): bool
        {
            return dead($this->server_id, $this->user_id);
        }

        /**
         * @return bool
         */
        public function killed(): bool
        {
            return killed($this->server_id, $this->user_id);
        }

        private ?string $keyboard = null;

        /**
         * @param null|string $keyboard
         * @return User
         */
        public function setKeyboard(?string $keyboard): User
        {
            $this->keyboard = $keyboard;
            return $this;
        }

        /**
         * @param string|null $message
         * @return $this
         */
        public function SendMessageHtml( $message = null): User
        {
            if ($message === null) {
                global $message;
            }
            SendMessage($this->user_id, $message, $this->keyboard, null, 'html');
            return $this;
        }

        /**
         * @param string|null $message
         * @return $this
         */
        public function SendMessage(?string $message = null): User
        {
            if ($message === null) {
                global $message;
            }
            SendMessage($this->user_id, $message, $this->keyboard);
            return $this;
        }

        /**
         * @return bool
         * @throws \Exception
         */
        public function hacked(): bool
        {
            $this->online();
            return is_user_hacked($this->user_id, $this->server_id);
        }

        /**
         * @return User[]
         */
        public function list_attacker_to_user(): array
        {
            return get_list_attacker_by_user($this->user_id, $this->server_id);
        }

        /**
         * @param string $status
         * @return $this
         * @throws \Exception
         */
        public function setStatus(string $status): User
        {
            return $this->update_user(['status' => $status]);
        }

        /**
         * @param string $data
         * @return $this
         * @throws \Exception
         */
        public function setData(string $data): User
        {
            return $this->update_user(['data' => $data]);
        }

        /**
         * @return $this
         * @throws \Exception
         */
        public function kill($type = 'dead'): User
        {
            kill($this->server_id, $this->user_id, $type);
            return $this;
        }

        /**
         * @return $this
         * @throws \Exception
         */
        public function logout(): User
        {
            global $link;
            logout_server($this->user_id);
            $link->where('user_id', $this->user_id)->delete('user_game');
            return $this;
        }

        /**
         * @return $this
         * @throws \Exception
         */
        public function leave(): User
        {
            leave_server($this->user_id);
            return $this;
        }

        /**
         * @param $user
         * @return bool
         */
        public function is($user): bool
        {
            if ($user instanceof User) {
                return $user->getUserId() == $this->user_id;
            } elseif (isset($user->user_id)) {
                return $user->user_id == $this->user_id;
            } elseif (isset($user['user_id'])) {
                return $user['user_id'] == $this->user_id;
            } elseif (is_numeric($user)) {
                return $user == $this->user_id;
            } else {
                return false;
            }
        }

        /**
         * @param int $role_id
         * @return $this
         * @throws \Exception
         */
        public function changeRole(int $role_id): User
        {
            update_role_to_user_for_server($this->user_id, $this->server_id, $role_id);
            return $this;
        }

        /**
         * @param $user
         * @return bool
         */
        public function check($user): bool
        {
            return !$this->is($user) && !$this->dead();
        }

        /**
         * @return bool
         */
        public function shield(): bool
        {
            return get_server_meta($this->server_id, 'shield', $this->user_id) == 'on';
        }

        /**
         * @return $this
         * @throws \Exception
         */
        public function unShield(): User
        {
            if (is_server_meta($this->server_id, 'shield', $this->user_id)) update_server_meta($this->server_id, 'shield', 'off', $this->user_id);
            return $this;
        }

        /**
         * @return bool
         */
        public function spy(): bool
        {
            return is_server_meta($this->server_id, 'warning', $this->user_id);
        }

        /**
         * @return bool
         */
        public function greenWay($day): bool
        {
            return get_server_meta($this->server_id, 'card-greenway', $this->user_id) == $day;
        }

        /**
         * @return bool
         */
        public function silence($day): bool
        {
            if (get_server_meta($this->server_id, 'card-silence', $this->user_id) == $day)
                return true;
            else
                return false;
            // return get_server_meta( $this->server_id, 'card-silence', $this->user_id ) == 'on';
        }

        /**
         * @return int|string
         */
        public function getStatus()
        {
            return $this->user()->status;
        }

        /**
         * @return string
         */
        public function gender(): string
        {
            $gender = $this->get_meta('gender');
            switch ($gender) {
                case 'man':
                    return '🙋🏻‍♂️ آقا';
                case 'woman':
                    return '🙋🏻‍♀️ خانم';
                case 'other':
                    return '🙋🏻 سایر';
                default:
                    return 'انتخاب نشده است';
            }
        }

        /**
         * @var string[][]
         */
        protected array $roles = [
            ROLE_Hacker => [
                'woman' => '👩🏻‍💻',
                'man' => '👨🏻‍💻',
                'other' => '🧑🏻‍💻'
            ],
            ROLE_AfsonGar => [
                'woman' => '🦹🏻‍♀️',
                'man' => '🦹🏻‍♂️',
                'other' => '🦹🏻'
            ],
            ROLE_Shahrvand => [
                'woman' => '👷🏻‍♀️',
                'man' => '👷🏻‍♂️',
                'other' => '👷🏻'
            ],
            ROLE_Karagah => [
                'woman' => '🕵🏻‍♀️',
                'man' => '🕵🏻‍♂️',
                'other' => '🕵🏻'
            ],
            ROLE_Cobcob => [
                'woman' => '🧑🏿',
                'man' => '🧑🏿',
                'other' => '🧑🏿'
            ],
            ROLE_Keshish => [
                'woman' => '👳🏻‍♀️',
                'man' => '👳🏻‍♂️',
                'other' => '👳🏻'
            ],
            ROLE_Police => [
                'woman' => '👮🏻‍♀️',
                'man' => '👮🏻‍♂️',
                'other' => '👮🏻'
            ],
            ROLE_Ghazi => [
                'woman' => '👩🏻‍⚖️',
                'man' => '👨🏻‍⚖️',
                'other' => '🧑🏻‍⚖️'
            ],
            ROLE_Kalantar => [
                'woman' => '👩🏻‍✈️',
                'man' => '👨🏻‍✈️',
                'other' => '🧑🏻‍✈️'
            ],
            ROLE_Fadaii => [
                'woman' => '🦸‍♀️',
                'man' => '🦸‍♂️',
                'other' => '🦸'
            ],
            ROLE_Bazmandeh => [
                'woman' => '👩🏻‍💼',
                'man' => '👨🏻‍💼',
                'other' => '🧑🏻‍💼'
            ],
            ROLE_TofangDar => [
                'woman' => '🤵🏻‍♀',
                'man' => '🤵🏻‍♂️',
                'other' => '🤵🏻'
            ],
            ROLE_Zambi => [
                'woman' => '🧟‍♀️',
                'man' => '🧟‍♂️',
                'other' => '🧟'
            ],
            ROLE_Pezeshk => [
                'woman' => '👩🏻‍⚕️',
                'man' => '👨🏻‍⚕️',
                'other' => '🧑🏻‍⚕️'
            ],
            ROLE_Sahere => [
                'woman' => '🧙🏻‍♀️',
                'man' => '🧙🏻‍♂️',
                'other' => '🧙🏻'
            ],
            ROLE_ShabKhosb => [
                'woman' => '💆🏻‍♀',
                'man' => '💆🏻‍♂',
                'other' => '💆🏻'
            ],
            ROLE_Fereshteh => [
                'woman' => ' 👰🏻‍♀',
                'man' => ' 👰🏻‍♂',
                'other' => ' 👰🏻',
            ],
            ROLE_Sagher => [
                'woman' => '👩🏻‍🔬',
                'man' => '👨🏻‍🔬',
                'other' => '🧑🏻‍🔬',
            ],
            ROLE_Ahangar => [
                'woman' => '👩🏻‍🏭',
                'man' => '👨🏻‍🏭',
                'other' => '🧑🏻‍🏭',
            ]
        ];

        /**
         * @param string $message
         * @return $this
         */
        public function genderChange(string &$message): User
        {
            if (empty($this->get_meta('gender'))) return $this;

            $role = $this->get_role();
            if (isset($this->roles[$role->id])) {
                $role_without_emoji = trim(remove_emoji($role->icon));
                $message = str_replace($role->icon, $role_without_emoji . ' ' . $this->roles[$role->id][$this->get_meta('gender')], $message);
            }
            return $this;
        }

        /**
         * @return bool
         */
        public function in_prisoner(): bool
        {
            return is_user_in_prisoner($this->server_id, $this->user_id);
        }

        /**
         * @return bool
         */
        public function registed(): bool
        {
            return $this->user_exists() && !empty($this->user()->name);
        }

        /**
         * @return $this
         * @throws \Exception
         */
        public function online(): User
        {
            update_server_meta($this->server_id, 'is-online', 'yes', $this->user_id);
            return $this;
        }

        /**
         * @return $this
         * @throws \Exception
         */
        private function add_user_to_server(): User
        {
            add_player_to_server($this->user_id, 0, 0);
            return $this;
        }

        /**
         * @return $this
         * @throws \Exception
         */
        public function addToServerByLeague(?int $league = null): User
        {
            return $this->add_user_to_server();
        }

        /**
         * @return string
         */
        public function getPriority(): string
        {
            $join = $this->get_meta('join');
            switch ($join) {
                case 'random':
                    return '♻️ تصادفی';
                case 'priority':
                default:
                    $priority = $this->get_meta('priority');
                    $name = empty($priority) ? $this->get_game()->icon : get_league($priority)->icon;
                    return 'بازی ' . $name;
                case 'asking':
                    return '❓ همیشه بپرس';
            }
        }

        /**
         * @return Server
         */
        public function server(): Server
        {
            return new Server($this->server_id);
        }

        /**
         * @var int
         */
        protected int $minPointSpam = 1000;

        /**
         * @return $this
         * @throws \Exception
         */
        public function spam(): User
        {

            if ($this->get_point() <= $this->minPointSpam) {

                $login = (int)$this->get_meta('count-game');
                if ($login != 3) {

                    $this->update_meta('count-game', $login + 1);
                    if ($login == 2) {

                        $message = '⚠️ هشدار، شما تا کنون ' . "<b><u>2 هشدار</u></b>" . ' دریافت کردید.' . "\n \n";
                        $message .= '💢 در صورت خروج از این بازی شما به مدت ' . "<u>5 دقیقه</u>" . ' مسدود می شوید❗️';
                        global $telegram;
                        $telegram->sendMessage([
                            'chat_id' => $this->user_id,
                            'text' => $message,
                            'parse_mode' => 'html'
                        ]);

                    }

                } else {

                    $warning = (int)$this->get_meta('warning-game') + 1;
                    $message = '⛔️ شما به دلیل حجم ترافیکی که ایجاد کرده اید به مدت ' . "<u>" . 5 . 'دقیقه ' . "</u>" . ' مسدود شدید.' . "\n \n";
                    $message .= '⚠️ در صورت تکرار این کار زمان مسدودیت شما طولانی تر می شود.';
                    global $telegram;
                    $telegram->sendMessage([
                        'chat_id' => $this->user_id,
                        'text' => $message,
                        'parse_mode' => 'html'
                    ]);

                    $this->update_meta('count-game', 0)->update_meta('warning-game', $warning + 1)->baned(time(), time() + (60 * 5), -1);
                    die();

                }

            }

            return $this;
        }

        /**
         * @param object $users
         * @return User[]
         */
        private function buildingUsers(object $users): array
        {
            $list_users = [];
            foreach ($users as $user) {
                if (isset($user->user_id)) {
                    $list_users[] = new User($user->user_id, $this->server_id);;
                }
            }
            return $list_users;
        }

        /**
         * @return User[]
         */
        public function subUsers(): array
        {
            global $link;
            return $this->buildingUsers((object)$link->get_result("SELECT * FROM `user_meta` WHERE `meta_value` = {$this->user_id} AND `meta_key` = 'sub_user'"));
        }

        /**
         * @return $this
         * @throws \Exception
         */
        public function removeRole(): User
        {
            global $link;
            $link->where('user_id', $this->user_id)->where('server_id', $this->server_id)->delete('server_role', 1);
            return $this;
        }

        /**
         * @return bool
         */
        public function sleep(): bool
        {
            return $this->is((int)get_server_meta($this->server_id, 'sleep'));
        }

        /**
         * @return $this
         * @throws \Exception
         */
        /*private function filter_names(): User
        {
            add_filter('filter_change_name', function (string $name) {

                global $link, $this;

                $names = $link->get_result("SELECT * FROM `users_names` WHERE `user_id` = {$this->user_id}");
                if (count($names) == 3) {

                    $last_name = $link->get_row("SELECT * FROM `users_names` WHERE `user_id` = {$this->user_id} ORDER BY `created_at` ASC");
                    $link->where('id', $last_name->id)->delete('users_names', 1);

                }

                $link->insert('users_names', [
                    'user_id' => $this->user_id,
                    'name' => $name
                ]);

                return $name;

            }, 9999);

            return $this;
        }*/

        /**
         * @param string $newName
         * @return \library\User
         * @throws \Exception
         */
        public function changeName(string $newName): User
        {
            global $link;
            $names_table = 'users_names';
            $names = $link->get_result("SELECT * FROM `$names_table` WHERE `user_id` = {$this->user_id}");
            if (count($names) == 3) {

                $last_name = $link->get_row("SELECT * FROM `$names_table` WHERE `user_id` = {$this->user_id} ORDER BY `created_at` ASC");
                $link->where('id', $last_name->id)->delete($names_table, 1);

            }

            try {
                $link->insert($names_table, [
                    'user_id' => $this->user_id,
                    'name' => $newName
                ]);

                $this->update_user([
                    'name' => $newName
                ]);

                return $this;
            } catch (\Exception $exception) {
                throw  new \Exception('ERROR ON INSERT NEW NAME USER ' . $this->user_id);
            }
        }

        /**
         * @param string $newName
         * @return \library\User
         * @throws \Exception
         */
        public function changeLatinName($new_name_id): User
        {
            global $link;
            $names_table = 'user_latin_names';
            $pending_names = $link->get_result("SELECT * FROM `$names_table` WHERE `user_id` = {$this->user_id} and `status` = 'pending'");
            $last_pending_name = $link->get_row("SELECT * FROM `$names_table` WHERE `id`= $new_name_id ");
            if (count($pending_names) > 1) {
                $link->where('id', $new_name_id, '!=')->where('status', 'pending')->delete($names_table);
            }

            try {
                $link->where('user_id', $this->user_id)->update($names_table, [
                    'status' => 'done'
                ]);
                $this->update_user([
                    'name' => '‏'.$last_pending_name->name
                ]);

                return $this;
            } catch (\Exception $exception) {
                throw  new \Exception('ERROR ON INSERT NEW NAME USER ' . $this->user_id);
            }
        }

        public function storeLatinName(string $newName)
        {
            global $link;
            try {
                $result = $link->insert('user_latin_names', [
                    'user_id' => $this->user_id,
                    'name' => $newName,
                    'status' => 'pending',
                ]);

                return $result;
            } catch (\Exception $exception) {
                throw  new \Exception('ERROR ON INSERT NEW LATIN NAME ' . $this->user_id);
            }
        }

        public function updateLatinName(int $nameId, string $name, string $font): bool
        {
            global $link;
            $link->where('id', $nameId)->update('user_latin_names', [
                'name' => $name,
                'font' => $font
            ]);
            return true;

        }

        public function deletePendingLatinName(): User
        {
            global $link;
            $link->where('user_id', $this->user_id)->where('status', 'pending')->delete('user_latin_names');
            return $this;
        }

        public function getLatinName()
        {
            global $link;
            $last_pending_name = $link->get_row("SELECT * FROM `user_latin_names` WHERE `user_id` = {$this->user_id} and `status` != 'pending' ORDER BY `created_at` DESC");
            return $last_pending_name->name;
        }

        /**
         * @param int $limit
         * @return array|false
         */
        public function getNames(int $limit = 3)
        {
            global $link;
            return $link->get_result("SELECT * FROM `users_names` WHERE `user_id` = {$this->user_id} ORDER BY `created_at` ASC LIMIT {$limit}");
        }

        public function getLatinNames()
        {
            global $link;
            return $link->get_result("SELECT * FROM `user_latin_names` WHERE `user_id` = {$this->user_id} AND `status` = 'done' ORDER BY `created_at` ASC");
        }

        public function checkLatinNameRepeated($name)
        {
            global $link;
            $result = $link->get_result("SELECT * FROM `user_latin_names` WHERE `name` = '{$name}'");
            if ($result) {
                return true;
            }
            return false;
        }

        public function checkLatinNameExists($name, $userId)
        {
            global $link;
            $result = $link->get_result("SELECT * FROM `user_latin_names` WHERE `user_id` = '{$userId}' and `name` = '{$name}'");
            if ($result) {
                return true;
            }
            return false;
        }

        public function checkAnyLatinNameExists($userId)
        {
            global $link;
            $result = $link->get_result("SELECT * FROM `user_latin_names` WHERE `user_id` = '{$userId}' and `status` = 'done'");
            if ($result) {
                return true;
            }
            return false;
        }

        /**
         * @param int $ID
         * @return false|mixed
         */
        public function getNameByID(int $ID)
        {
            global $link;
            return $link->get_var("SELECT `name` FROM `users_names` WHERE `id` = {$ID}");
        }

        /**
         * @param int $ID
         * @return false|mixed
         */
        public function getLatinNameByID(int $ID)
        {
            global $link;
            return $link->get_var("SELECT `name` FROM `user_latin_names` WHERE `id` = {$ID}");
        }

        /**
         * @return $this
         * @throws \Exception
         */
        public function addWinGame(): User
        {
            $game_win = (int)$this->get_meta('game-win');
            $this->update_meta('game-win', ($game_win + 1));
            return $this;
        }

        /**
         * @return $this
         * @throws \Exception
         */
        public function addLostGame(): User
        {
            $game_lost = (int)$this->get_meta('game-lost');
            $this->update_meta('game-lost', ($game_lost + 1));
            return $this;
        }

        /**
         * @return int
         */
        public function getWinGame(): int
        {
            return (int)$this->get_meta('game-win');
        }

        /**
         * @return int
         */
        public function getLostGame(): int
        {
            return (int)$this->get_meta('game-lost');
        }

        /**
         * @return int
         */
        public function getCountGame(): int
        {
            return (int)$this->get_meta('game-count');
        }

        /**
         * @return int
         */
        public function getResultWinGame(): int
        {
            return intval((100 * $this->getWinGame()) / $this->getCountGame());
        }

        /**
         * @return $this
         * @throws \Exception
         */
        public function resetNames(): User
        {
            global $link;
            $link->where('user_id', $this->user_id)->delete('users_names');
            $link->where('user_id', $this->user_id)->update('users', [
                'name' => null
            ]);
            return $this;
        }

        /**
         * @return bool
         */
        public function isDeleteMessage(): bool
        {
            $result = $this->get_meta('delete-message');
            return $result == 'on' || empty($result);
        }

        /**
         * @param int $server_id
         * @return $this
         * @throws \Exception
         */
        public function checkLeagueWithPoint(int &$server_id): User
        {

            $user_league = $this->get_game();
            if (get_server($server_id)->league_id > $user_league->id) {
                if ($this->get_meta('priority') != $user_league->id) $server_id = get_server_by_league($user_league->id);
                $this->update_meta('priority', $user_league->id);
            }

            return $this;

        }

        /**
         * @param int $server_id
         * @return $this
         * @throws ExceptionMessage
         */
        public function checkTimeServerLeague(int $server_id): User
        {

            $server = new Server($server_id);
            $league = $server->get_league();

            if ($league->start_time > 0 && $league->end_time > 0) {

                if (!(date('H') >= $league->start_time && date('H') <= $league->end_time)) {
                    $message = '⏱ در این ساعت بازی ' . $league->icon . ' فعال نیست .' . "\n";
                    $message .= 'در بین ساعت ' . $league->start_time . ' تا ' . ($league->end_time + 1) . ' امتحان کنید ';
                    throw new ExceptionMessage($message);
                }

            }

            if ($league->id == 5 && !$this->is(ADMIN_ID)) {
                throw new ExceptionMessage(' 🔴 شما مجاز نیستید✋');
            }

            return $this;
        }

        /**
         * @param int $coin
         * @return bool
         */
        public function has_coin(int $coin): bool
        {
            return has_coin($this->user_id, $coin);
        }

        /**
         * @param int $coin
         * @return bool
         * @throws \Exception
         */
        public function demote_coin(int $coin): bool
        {
            if ($this->has_coin($coin)) {

                demote_coin($this->user_id, $coin);
                return true;
            }
            return false;
        }

        /**
         * @param int $user_id
         * @param int $coin
         * @return bool
         * @throws \Exception
         */
        public function move_coin(int $user_id, int $coin): bool
        {
            if ($this->has_coin($coin) && user_exists($user_id)) {

                $this->demote_coin($coin);
                $log = 'تعداد [coin] به کاربر [user] ارسال کرد.';
                add_log('coin', __replace__($log, ['[coin]' => $coin, '[user]' => $user_id]), $this->user_id);
                $message = '♨️ [[coin]] سکه از طرف [[user]] برای شما ارسال شد .';
                (new User($user_id, 0))->add_coin($coin)->SendMessageHtml(
                    __replace__($message, [
                        '[[coin]]' => $coin,
                        '[[user]]' => "<u>" . $this->user()->name . "</u>"
                    ])
                );

                $message = '🪙 ' . "<u><b>" . '[[coin]] سکه ' . "</b></u>" . ' از طرف شما برای [[user]] ارسال شد ✅';
                $this->SendMessageHtml(
                    __replace__($message, [
                        '[[coin]]' => $coin,
                        '[[user]]' => "<u>" . user($user_id)->name . "</u>"
                    ])
                );
                return true;

            }
            $this->SendMessage('خطا، شما سکه کافی ندارید.');
            return false;
        }

        /**
         * @param int $minutes
         * @return bool
         */
        public function isTimeOnlineFriend(int $minutes = 5): bool
        {
            global $link;
            return (bool)$link->get_row("SELECT * FROM `last_game_user` WHERE `user_id` = {$this->user_id} AND `last_time` >= '" . date('Y-m-d H:i', strtotime('-' . $minutes . ' minutes')) . "' AND `last_time` <= '" . date('Y-m-d H:i') . "'");
        }

        /**
         * @return int
         */
        public function getCodeStatusFriend(): int
        {

            if ($this->get_meta('status') == 'hide') return 0;

            if ($this->user_on_game()) {
                $server_status = $this->server()->status;
                if ($server_status == 'started') return 1;
                if ($server_status == 'opened') return 2;
                return 3;
            } elseif ($this->isTimeOnlineFriend()) return 3;
            else {
                return -1;
            }

        }

        /**
         * @return string
         */
        public function getStatusFriend(): string
        {

            switch ($this->getCodeStatusFriend()) {

                case 1:
                    return 'آنلاین درحال بازی 🟢';

                case 2:
                    return 'آنلاین منتظر 🟣';

                case 3:
                    return 'آنلاین خارج از بازی 🟡';

                case -1:
                    return ' آفلاین 🔴';

                default:
                case 0:
                    return 'وضعیت خاموش  ⚫️';

            }

        }

        /**
         * @return string
         */
        public function getStatusFriendEmoji(): string
        {

            switch ($this->getCodeStatusFriend()) {

                case 1:
                    return '🟢';

                case 2:
                    return '🟣';

                case 3:
                    return '🟡';

                case -1:
                    return '🔴';

                default:
                case 0:
                    return '⚫️';

            }

        }

        /**
         * @return \library\User[]
         */
        public function friends(): array
        {
            global $link;
            $list_friends = [];
            $friends = $link->get_result("SELECT * FROM `friends` WHERE `user_id` = {$this->user_id}");
            foreach ($friends as $friend) {
                $list_friends[] = new User($friend->friend_id);
            }
            return $list_friends;
        }

        /**
         * @return string
         */
        public function toStringFriend(): string
        {
            return $this->get_league()->emoji . $this->user()->name . ' ' . '➖' . ' ' . 'وضعیت' . ' ' . $this->getStatusFriendEmoji();
        }

        /**
         * @return $this
         * @throws \Exception
         */
        public function changeStatusLastGame(): User
        {
            global $link;

            if (((bool)$link->get_row("SELECT * FROM `last_game_user` WHERE `user_id` = {$this->user_id}")) == true) {

                $link->where('user_id', $this->user_id)->update('last_game_user', [
                    'last_time' => date('Y-m-d H:i:s')
                ]);

            } else {
                $link->insert('last_game_user', [
                    'user_id' => $this->user_id
                ]);
            }

            return $this;

        }

        /**
         * @param \library\User $user
         * @return bool
         * @throws \Exception
         */
        public function removeFriend(User $user): bool
        {
            global $link;
            $link->where('user_id', $this->user_id)->where('friend_id', $user->getUserId())->delete('friends', 1);
            $link->where('user_id', $user->user_id)->where('friend_id', $this->getUserId())->delete('friends', 1);
            return true;
        }

        /**
         * @param int $user_id
         * @return $this
         * @throws \ExceptionError
         */
        public function requestFriend(int $user_id): User
        {

            global $link;

            $count = intval($link->get_var("SELECT `count` FROM `request_friends` WHERE `user_id` = {$this->user_id} AND `friend_id` = {$user_id}"));

            if ($count > 2) throw new ExceptionError('شما دیگر نمیتوانید به این کاربر درخواست دوستی ارسال کنید.');
            if ($this->countFriend() == 40) throw new ExceptionWarning('شما تنها میتوانید 40 دوست در لیست داشته باشید.');

            if ($count == 0) {
                $link->insert('request_friends', [
                    'user_id' => $this->user_id,
                    'friend_id' => $user_id,
                ]);
            } else {
                $link->where('user_id', $this->user_id)->where('friend_id', $user_id)->update('request_friends', [
                    'count' => $count + 1
                ]);
            }


            return $this;

        }

        /**
         * @return int
         */
        public function countFriend(): int
        {
            global $link;
            return intval($link->get_var("SELECT COUNT(*) FROM `friends` WHERE `user_id` = {$this->user_id}"));
        }

        /**
         * @return int
         */
        public function countFriendRequest(): int
        {
            global $link;
            return intval($link->get_var("SELECT COUNT(*) FROM `friends` WHERE `user_id` = {$this->user_id} AND `create_by` = {$this->user_id}"));
        }

        /**
         * @param int $user_id
         * @return bool
         */
        public function isFriend(int $user_id): bool
        {
            global $link;
            return (bool)$link->get_row("SELECT * FROM `friends` WHERE `user_id` = {$this->user_id} AND `friend_id` = {$user_id}");
        }

        /**
         * @param \library\User $user
         * @return bool
         * @throws \Exception
         */
        public function add_friend(User $user): bool
        {
            global $link;

            if ($this->countFriend() < 40 && $user->countFriend() < 40) {

                $link->insert('friends', [
                    'user_id' => $this->user_id,
                    'friend_id' => $user->user_id,
                    'create_by' => $this->user_id
                ]);
                $link->insert('friends', [
                    'user_id' => $user->user_id,
                    'friend_id' => $this->user_id,
                    'create_by' => $this->user_id
                ]);
                $link->where('user_id', $this->user_id)->where('friend_id', $user->getUserId())->update('request_friends', [
                    'count' => 0
                ]);

                return true;
            }
            return false;
        }

        /**
         * @return bool
         */
        public function haveSubscribe(): bool
        {
            return (bool)$this->subscribe();
        }

        /**
         * @return object|null
         */
        public function subscribe(): ?object
        {
            global $link;
            return $link->get_row("SELECT * FROM `subscriptions` WHERE `user_id` = {$this->user_id} AND `ended_at` >= '" . date('Y-m-d') . "' ORDER BY `id` DESC LIMIT 1");
        }

        /**
         * @return object[]|null
         */
        public function subscribes(): ?array
        {
            global $link;
            return $link->get_result("SELECT * FROM `subscriptions` WHERE `user_id` = {$this->user_id} AND `ended_at` >= '" . date('Y-m-d') . "' ORDER BY `id` DESC ");
        }

        /**
         * @param string $type
         * @return object|null
         */
        public function subscribeType(string $type): ?object
        {
            global $link;
            return $link->get_row("SELECT * FROM `subscriptions` WHERE `user_id` = {$this->user_id} AND ( `type` = '{$type}' OR `type` = 'all' ) AND `ended_at` >= '" . date('Y-m-d') . "' ORDER BY `id` DESC LIMIT 1");
        }

        /**
         * @param string $type
         * @return bool
         */
        public function haveSubscribeType(string $type): bool
        {
            return (bool)$this->subscribeType($type);
        }

        /**
         * @param string $type
         * @param int $day
         * @param int $amount
         * @return $this
         * @throws \Exception
         */
        public function addSubscribe(string $type, int $day, int $amount): User
        {
            global $link;

            $subscription = $this->subscribeType($type);

            if (isset($subscription->ended_at)) {

                $date1 = date_create();
                $date2 = date_create($subscription->ended_at);
                $diff = date_diff($date1, $date2);
                $day += intval($diff->format("%a"));

            }

            $link->insert('subscriptions', [
                'user_id' => $this->user_id,
                'type' => $type,
                'day' => $day,
                'amount' => $amount,
                'ended_at' => date('Y-m-d 00:00:00', strtotime('+' . $day . ' day'))
            ]);


            return $this;

        }

    }

}