<?php


add_filter('filter_report_by_id', function( $res ) {
    if ( $res->type == 'x' )
    {
        $res->type = 'ایجاد اختلال در نظم بازی';
    }
    return $res;
});


add_filter('filter_report_name', function( $name ) {
    switch ( trim($name) )
    {
        case 'استفاده از الفاظ رکیک':
            return 'C1';
        case 'تقلب در بازی':
            return 'C2';
        case 'لو دادن نقش خود یا دیگران':
            return 'C3';
        case 'ارسال شماره یا آیدی':
            return 'C4';
        case 'ایجاد اختلال در نظم بازی':
            return 'C5';
        case 'تبلیغات':
            return 'C6';
        case 'اسم نامتعارف':
            return 'C7';
        case 'C1':
            return 'استفاده از الفاظ رکیک';
        case 'C2':
            return 'تقلب در بازی';
        case 'C3':
            return 'لو دادن نقش خود یا دیگران';
        case 'C4':
            return 'ارسال شماره یا آیدی';
        case 'C5':
            return 'ایجاد اختلال در نظم بازی';
        case 'C6':
            return 'تبلیغات';
        case 'C7':
            return 'اسم نامتعارف';
    }
    return 'نا معلوم';
});

/**
 * @param int $user_id
 * @param int $user_report
 * @param int $server_id
 * @param string $type
 * @param int|null $message_id
 * @param string|null $note
 * @return int
 * @throws \Exception
 */
function add_report( int $user_id, int $user_report, int $server_id, string $type, int $message_id = null, string $note = null ) : int
{
    global $link;
    try
    {
        $link->insert('report', [
            'user_id'       => $user_id,
            'server_id'     => $server_id,
            'user_reported' => $user_report,
            'status'        => 'send_to_admin',
            'type'          => $type,
            'message_id'    => $message_id,
            'note'          => $note
        ]);
        return $link->getInsertId();
    } catch ( Exception $e )
    {
        throw new Exception('Error To Add Report In Database');
    }
}

/**
 * @param int $user_id
 * @param int $server_id
 * @param int $user_reported
 * @return false|object|null
 */
function get_report( int $user_id, int $user_reported, int $server_id )
{
    global $link;
    return $link->get_row("SELECT * FROM `report` WHERE `user_id` = {$user_id} AND `server_id` = {$server_id} AND `user_reported` = {$user_reported}");
}

/**
 * @param int $id
 * @return false|object|null
 */
function get_report_by_id( int $id )
{
    global $link;
    return apply_filters('filter_report_by_id', $link->get_row("SELECT * FROM `report` WHERE `id` = {$id}"));
}

/**
 * @param int $user_id
 * @param int $server_id
 * @param array $data
 * @return bool
 * @throws Exception
 */
function update_report( int $user_id, int $server_id, array $data )
{
    global $link;
    try
    {
        $link->where('user_reported', $user_id)->where('server_id', $server_id)->update('report', $data);
        return true;
    } catch ( Exception $e )
    {
        throw new Exception('Error To Update Report In Database');
    }
}

/**
 * @param int $server_id
 * @param int $user_reported
 * @return array|false|\helper\Report
 */
function get_report_by_server( int $server_id, int $user_reported )
{
    global $link;
    return $link->get_result("SELECT * FROM `report` WHERE `server_id` = {$server_id} AND `user_reported` = {$user_reported}");
}