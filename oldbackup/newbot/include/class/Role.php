<?php


namespace library;


use Base;


if (!class_exists("library\Role")) {
    /**
     * Class Role
     * @package library
     * @property int $group_id
     * @property string $name
     * @property string $icon
     * @property string $detail
     * @property int $coin
     * @property int $level
     * @property string $created_at
     * @property string $updated_at
     */
    class Role
    {
        use Base;

        /**
         * @var Server
         */
        private Server $server;

        /**
         * @var int[][]
         */
        private array $roles;

        /**
         * Role constructor.
         * @param $server
         * @param int $id
         */
        public function __construct($server, int $id = 0)
        {
            if (!($server instanceof Server)) {
                if (isset($server->server_id)) {
                    $server = new Server($server->server_id);
                } elseif (isset($server['server_id'])) {
                    $server = new Server($server['server_id']);
                } elseif (is_numeric($server)) {
                    $server = new Server($server);
                } else {
                    $server = new Server(0);
                }
            }
            $this->server = $server;
            $this->id = $id;
            $this->buildProperty();
        }

        /**
         * @return $this
         * @throws \Exception
         */
        public function clear(): Role
        {
            foreach ($this->roles as $role) {
                foreach ($role as $index => $item) {
                    if (!empty($item)) {
                        $this->server->setUserId($item)->deleteMetaUser($index);
                    }
                }
            }
            return $this;
        }

        /**
         * @param int $user_id
         * @param string $type
         * @param bool $add
         * @return false|int|User|object|string|null
         */
        public function select(int $user_id, string $type = 'select', bool $add = true)
        {
            if ($add) $this->roles[] = [$type => $user_id];
            $user = get_server_meta($this->server->getId(), $type, $user_id);
            if ($this->getUser) {
                $this->getUser = false;
                return new User((is_numeric($user) ? $user : 0), $this->server->getId());
            } elseif ($this->getString) {
                $this->getString = false;
                return (string)$user;
            } elseif ($this->getInt) {
                $this->getInt = false;
                return (int)$user;
            }
            return new User((is_numeric($user) ? $user : 0), $this->server->getId());
        }

        /**
         * @var bool
         */
        private bool $getInt = false;

        /**
         * @var bool
         */
        private bool $getString = false;

        /**
         * @var bool
         */
        private bool $getUser = false;

        /**
         * @param int $role_id
         * @return User
         */
        public function getUser(int $role_id): User
        {
            return new User(get_role_by_user($this->server->getId(), $role_id) ?? 0, $this->server->getId());
        }

        /**
         * @param int $user_id
         * @param string $type
         * @throws \Exception
         */
        public function delete(int $user_id, string $type = 'select'): void
        {
            $this->server->setUserId($user_id)->deleteMetaUser($type);
        }

        /**
         * @param object $roles
         * @return Role[]
         */
        public static function buildingRoles(object $roles): array
        {
            $list_role = [];
            foreach ($roles as $role) {
                if (isset($role->id)) {
                    $list_role[] = new Role(0, $role->id);
                }
            }
            return $list_role;
        }

        /**
         * @param int $server_id
         * @param $role
         * @return Role
         * @throws \Exception
         */
        public static function buildingRole(int $server_id, $role): Role
        {
            if (isset($role->id)) {
                return new Role($server_id, $role->id);
            } elseif (is_numeric($role)) {
                return new Role($server_id, $role);
            } else {
                throw new \Exception('ERROR ON ROLE ID');
            }
        }

        /**
         * @param int $role_id
         * @return bool
         */
        public function exists(int $role_id): bool
        {
            return role_exists($role_id, $this->id);
        }

        /**
         * @param int $select
         * @param int $user_id
         * @param string $type
         * @return $this
         * @throws \Exception
         */
        public function set(int $select, int $user_id = 0, string $type = 'select'): Role
        {
            update_server_meta($this->server->getId(), $type, $select, $user_id);
            return $this;
        }

        /**
         * @param callable|null $callable
         * @return $this
         */
        public function answerCallback(?callable $callable = null): Role
        {
            global $dataid, $user_select;
            if (is_callable($callable)) {
                AnswerCallbackQuery(
                    $dataid,
                    call_user_func($callable, $user_select)
                );
                return $this;
            }
            AnswerCallbackQuery(
                $dataid,
                '👤 شما ' . $user_select->get_name() . ' را انتخاب کردید.'
            );
            return $this;
        }

        /**
         * @return Role
         */
        public function user(): Role
        {
            $this->getUser = true;
            return $this;
        }

        /**
         * @var int
         */
        private int $select = 0;

        /**
         * @return int
         */
        public function getSelect(): int
        {
            return $this->select;
        }

        /**
         * @param int $select
         * @return Role
         */
        public function setSelect(int $select): Role
        {
            $this->select = $select;
            return $this;
        }

        /**
         * @param $role_id
         * @return bool
         */
        public function is($role_id): bool
        {
            if ($role_id instanceof Role) {
                return $role_id->getId() == $this->id;
            } elseif (isset($role_id['role_id'])) {
                return $role_id['role_id'] == $this->id;
            } elseif (isset($role_id->role_id)) {
                return $role_id->role_id == $this->id;
            } elseif (is_numeric($role_id)) {
                return $role_id == $this->id;
            }
            return false;
        }

        /**
         * @return Role
         */
        public function getInt(): Role
        {
            $this->getInt = true;
            return $this;
        }

        /**
         * @return $this
         */
        public function getString(): Role
        {
            $this->getString = true;
            return $this;
        }

    }
}