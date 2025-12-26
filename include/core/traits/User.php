<?php


trait User
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $user_id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var integer|null
     */
    public $league;

    /**
     * @var integer
     */
    public $coin;


    /**
     * @var int|string
     */
    public $status;


    /**
     * @var int|string
     */
    public $data;


    /**
     * @var string
     */
    public $created_at;
}