<?php

namespace library;

/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $url
 * @property string $title
 */
class Media
{

    /**
     * @return Media[]
     */
    public static function getMedia( int $offset = 0, int $limit = 20 ) : ?array
    {
        global $link;
        return $link->get_result( "SELECT * FROM `media` ORDER BY `id` DESC LIMIT " . ( $offset == 0 ? $limit : $offset * $limit . ', ' . $limit ) );
    }

    /**
     * @return Media[]
     */
    public static function getMediaWithType( string $type, int $offset = 0, int $limit = 20 ) : ?array
    {
        global $link;
        return $link->get_result( "SELECT * FROM `media` WHERE `type` = '{$type}' ORDER BY `id` ASC LIMIT " . ( $offset <= 1 ? $limit : $limit . ', ' . $offset * $limit ) );
    }

    /**
     * @param int $id
     * @return Media
     */
    public static function find( int $id ) : object
    {
        global $link;
        return $link->get_row( "SELECT * FROM `media` WHERE `id` = {$id}" );
    }

}