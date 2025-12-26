<?php


namespace library;


class Vote
{
    /**
     * @var string
     */
    protected string $table = 'votes';

    /**
     * @var Server
     */
    private Server $server;


    /**
     * @var int
     */
    private int $server_id;

    /**
     * Vote constructor.
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
        $this->server_id = $this->server->getId();
    }

    /**
     * @return Server
     */
    public function getServer(): Server
    {
        return $this->server;
    }

    /**
     * @param $user
     * @return int
     */
    private function getUser($user): int
    {

        if ($user instanceof User) {

            return $user->getUserId();

        } elseif (isset($user->user_id)) {

            return $user->user_id;

        } elseif (isset($user['user_id'])) {

            return $user['user_id'];

        } elseif (is_numeric($user)) {

            return $user;

        } else {

            return false;

        }

    }

    /**
     * @param $from_user
     * @param $to_user
     * @return int
     * @throws \Exception
     */
    public function voteToUser($from_user, $to_user): int
    {
        global $link;
        $from_user = $this->getUser($from_user);
        $to_user = $this->getUser($to_user);

        if (!$this->is($from_user)) {

            $link->insert($this->table, [
                'server_id' => $this->server_id,
                'user_id' => $from_user,
                'user_vote' => $to_user
            ]);

            return $link->getInsertId();

        } else {

            $link
                ->where('server_id', $this->server_id)
                ->where('user_id', $from_user)
                ->update($this->table, [
                    'user_vote' => $to_user
                ]);

            return 1;

        }

    }

    /**
     * @return int
     */
    public function countVotes(): int
    {
        global $link;
        return (int) $link->get_var("SELECT COUNT(*) FROM `{$this->table}` WHERE `server_id` = {$this->server_id}");
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function is($user_id): bool
    {
        global $link;
        $user_id = $this->getUser($user_id);
        return (bool) $link->get_row("SELECT * FROM `{$this->table}` WHERE `server_id` = {$this->server_id} AND `user_id` = {$user_id}");
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function removeVoteUser(int $from_user, int $to_user): Vote
    {
        global $link;

        $from_user = $this->getUser($from_user);
        $to_user = $this->getUser($to_user);

        $link
            ->where('server_id', $this->server_id)
            ->where('user_id', $from_user)
            ->where('user_vote', $to_user)
            ->delete($this->table);

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function removeVote(int $from_user): Vote
    {
        global $link;

        $from_user = $this->getUser($from_user);

        $link
            ->where('server_id', $this->server_id)
            ->where('user_id', $from_user)
            ->delete($this->table);

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function clear(): Vote
    {
        global $link;

        $link
            ->where('server_id', $this->server_id)
            ->delete($this->table);

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
     * @return User[][]
     */
    public function votes(): array
    {
        global $link;
        $results = $link->get_result("SELECT * FROM `{$this->table}` WHERE `server_id` = {$this->server_id}");
        $list = [];
        foreach ($results as $result) {

            $list[$result->user_vote][] = new User($result->user_id, $this->server_id);

        }

        return $list;
    }


    /**
     * @param $user
     * @return \library\User
     */
    public function getVoteUser($user): User
    {
        global $link;
        $user = $this->getUser($user);
        return new User($link->get_var("SELECT `user_vote` FROM `{$this->table}` WHERE `server_id` = {$this->server_id} AND `user_id` = {$user}") ?? 0, $this->server_id);
    }

    /**
     * @param $from_user
     * @return int
     */
    public function indexOf($from_user): int
    {
        $user = $this->getUser($from_user);
        global $link;
        $result = (int) $link->get_var("SELECT COUNT(*) FROM `{$this->table}` WHERE `user_id` = {$user} AND `server_id` = {$this->server_id} ORDER BY `id` ASC");
        return $result + 1;
    }

    /**
     * @param $from_user
     * @return \library\User
     */
    public function voteTo($from_user)
    {
        $user = $this->getUser($from_user);
        global $link;
        $result = $link->get_row("SELECT * FROM `{$this->table}` WHERE `user_id` = {$user} AND `server_id` = {$this->server_id} LIMIT 1");
        return new User($result->user_vote, $this->server_id);
    }


}