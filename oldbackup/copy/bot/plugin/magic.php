<?php


/**
 * @param int $server_id
 * @param int $user_id
 * @param int $magic_number
 * @return bool
 * @throws \ExceptionWarning
 */
function add_magic( int $server_id, int $user_id, int $magic_number ) : bool
{

    if ( $user_id == ADMIN_ID ) return true;
    $magic_count = get_server_meta( $server_id, 'magic-count', $user_id );

    if ( $magic_count < 3 || $magic_number == 0 )
    {

        if ( !is_server_meta( $server_id, 'magic-' . $magic_number, $user_id ) )
        {

            add_server_meta( $server_id, 'magic-count', $magic_count + 1, $user_id );
            add_server_meta( $server_id, 'magic-' . $magic_number, 'use', $user_id );
            return true;

        }

    }
    else
    {

        throw new ExceptionWarning( 'در هر بازی فقط از سه جادو میتوانید استفاده کنید.' );

    }

    return false;
}

/**
 * @param int $user_id
 * @return bool
 */
function shield( int $user_id ) : bool
{
    $server = is_user_in_which_server( $user_id );
    if ( isset( $server ) )
    {
        return get_server_meta( $server->id, 'shield', $user_id ) == 'on';
    }
    return false;
}

/**
 * @param int $user_id
 * @return bool
 * @throws Exception
 */
function unshield( int $user_id ) : bool
{
    $server = is_user_in_which_server( $user_id );
    if ( is_server_meta( $server->id, 'shield', $user_id ) ) update_server_meta( $server->id, 'shield', 'off', $user_id );
    return true;
}