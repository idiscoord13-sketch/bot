<?php


/**
 * Trait Father
 */
trait Base
{
    /**
     * @var int|null
     */
    private ?int $user_id;

    /**
     * @var int
     */
    private int $id;

    /**
     * Father constructor.
     * @param int $id
     * @param int|null $user_id
     */
    public function __construct(int $id = 0, ?int $user_id = 0)
    {
        $this->user_id = $user_id;
        if (!property_exists(static::class, 'table') || empty($this->table)) {
            $array = explode('\\', static::class);
            $this->table = strtolower(end($array));
        }
        $this->id = $id;
        $this->buildProperty();
    }

    private function buildProperty()
    {
        if ($this->id > 0) {
            global $link;
            $order = $link->get_row("SELECT * FROM `{$this->table}` WHERE `id` = {$this->id}");
            if (isset($order)) {
                foreach ($order as $key => $value) {
                    $this->{$key} = $order->{$key};
                }
            }
        }
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     * @return Base
     */
    public function setUserId(int $user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @throws Exception
     */
    public function delete(): void
    {
        global $link;
        try {
            $link->where('id', $this->getId())->orWhere('user_id', $this->getUserId())->delete($this->getTable());
        } catch (Exception $e) {
            throw new Exception("ERROR ON DELETE " . static::class . " ID " . $this->id . ' MESSAGE ' . $e->getMessage());
        }
    }

    /**
     * @param array $params
     * @throws Exception
     */
    public function update(array $params): void
    {
        global $link;
        try {
            $link->where('id', $this->id)->update($this->table, $params);
        } catch (Exception $e) {
            throw new Exception('ERROR ON UPDATE ' . static::class . ' ID ' . $this->id . ' MESSAGE ' . $e->getMessage());
        }
    }

    /**
     * @param array $params
     * @return static
     * @throws Exception
     */
    public static function create(array $params)
    {
        global $link;
        $table = (new static)->getTable();
        try {
            $link->insert($table, $params);
            return new static($link->getInsertId(), $params['user_id'] ?? 0);
        } catch (Exception $e) {
            throw new Exception('ERROR ON CREATE ' . static::class);
        }
    }
}