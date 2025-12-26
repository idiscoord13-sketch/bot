<?php

require_once "wpdb.php";

class Tdb extends wpdb
{
    private static $_link = null;
    private static $_error = null;
    private static $last_query = null;
    public $user_ids = array();


    public $host = 'localhost';
    public $db = 'iranimaf_main';
    public $username = 'iranimaf_black';
    public $password = 'F{e.087U@QXH&;}?';
    public $mysqli_con;

    public $tables = ['users', 'user_meta', 'users_names', 'user_name'];
    public $time_tabel = 'log';
    public $duration = 10;


    /**
     * Tdb constructor.
     * @param string $host
     * @param string $username
     * @param string $password
     * @param null $db
     * @param string $charset
     * @param null $port
     * @param null $socket
     * @throws Exception
     */
    public function __construct( $host = 'localhost', $username = 'root', $password = '', $db = null, $charset = 'utf8mb4', $port = null, $socket = null )
    {
        $this->mysqli_con = mysqli_connect($this->host, $username, $password, $db);

        self::connectDB( $db, $username, $password, $host );
        parent::__construct( $host, $username, $password, $db, $charset, $port, $socket );
    }

    public function __destruct()
    {
        $this->close();
        $this->disconnect();

    }

    /**
     * @param $dbname
     * @param string $user
     * @param string $pass
     * @param string $host
     * @throws Exception
     */

    public function create_tabels_files($mysqli_con, $tables = [], $time_tabel, $duration, $userId = false) {

        $directory = __DIR__;
        $directory = $directory . '/../sql_booseter/tabel_files';
        $file_path = $directory . "/indexes.txt";
        $indexes = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($userId) {
            $indexes[] = $userId;
        }


        if (!file_exists($directory)) {
            mkdir($directory, 0777, true); // Create the directory recursively
        }
        $indexesString = implode(PHP_EOL, $indexes);
        file_put_contents($file_path, $indexesString);


        if ($userId) {
            $indexes = [$userId];
        }


        if (!$mysqli_con) {
            return ('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
        }

        foreach ($tables as $table) {
            $user_ids_string = '(' . implode(', ', $indexes) . ')';

            $query = "SELECT *
        FROM $table
        WHERE user_id in $user_ids_string;";
            $result = mysqli_query($mysqli_con, $query);

            if ($result) {
                // Create a new table if it doesn't exist
                $create_table_query = "CREATE TABLE IF NOT EXISTS small_$table LIKE $table;";
                if (mysqli_query($mysqli_con, $create_table_query)) {
                    // Delete old data from the new table
                    $delete_query = "DELETE FROM small_$table;";
                    if (mysqli_query($mysqli_con, $delete_query)) {
                        // Insert new data into the new table
                        while ($row = mysqli_fetch_assoc($result)) {
                            $columns = array_keys($row);
                            $values = array_map(function ($value) use ($mysqli_con) {
                                return "'" . mysqli_real_escape_string($mysqli_con, $value) . "'";
                            }, array_values($row));

                            $insert_query = "INSERT INTO small_$table (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");";
                            if (!mysqli_query($mysqli_con, $insert_query)) {
                                echo "<br> Error inserting data into table 'small_$table': " . mysqli_error($mysqli_con) . "<br>";
                            }
                        }
                        echo "<br> Data for table '$table' has been successfully stored in the new table: small_$table<br>";
                    } else {
                        echo "<br> Error deleting old data from table 'small_$table': " . mysqli_error($mysqli_con) . "<br>";
                    }
                } else {
                    echo "<br> Error creating table 'small_$table': " . mysqli_error($mysqli_con) . "<br>";
                }
            } else {
                echo "<br> Error retrieving data from table '$table': " . mysqli_error($mysqli_con) . "<br>";
            }
        }



        return 'files created successfuly';

    }

    public static function connectDB( $dbname, string $user = 'root', string $pass = '', string $host = 'localhost' )
    {
        $link = mysqli_connect( $host, $user, $pass, $dbname );
        if ( mysqli_connect_error() )
        {
            self::$_error = mysqli_connect_error();
            throw new Exception( "ERROR CONNECTION" );
        }
        $link->set_charset( 'utf8mb4' );
        self::$_link = $link;
        if ( $link->connect_error || mysqli_error( $link ) )
        {
            throw new ExceptionMessage( '🔴 متاسفانه اتصال به دیتابیس را از دست دادیم. لطفا چند لجظه صبر کنید.' );
        }
    }

    public static function connect_error()
    {
        if ( ! is_null( self::$_error ) )
        {
            return 'error => (' . self::$_error . ')';
        }
        return false;
    }

    public static function connect_error_list()
    {
        if ( mysqli_error( self::$_link ) )
        {
            return mysqli_error_list( self::$_link );
        }
        return false;
    }

    private static function createVal( $arr )
    {
        $tmp = array();
        foreach ( $arr as $item )
        {
// this line makes sure you don't risk a sql injection attack
// $connection is your current connection
            $tmp[] = mysqli_escape_string( self::$_link, $item );
        }
        return '\'' . implode( '\', \'', $tmp ) . '\'';
    }

    private static function createKey( $arr )
    {

        $tmp = array();
        foreach ( $arr as $item )
        {
// this line makes sure you don't risk a sql injection attack
// $connection is your current connection
            $tmp[] = mysqli_escape_string( self::$_link, $item );
        }
        return '`' . implode( '`, `', $tmp ) . '`';
    }

    public function insertT( $table, $data )
    {
//        $this->prependLog('insertT $table: '. $table, '/TDBLog');
//        $this->prependLog('insertT $data: '. $data, '/TDBLog');

        if ( empty( $table ) || is_array( $table ) || ! is_string( $table ) ) return (bool) false;
        if ( empty( $data ) || ! is_array( $data ) ) return (bool) false;

        $key = self::createKey( array_keys( $data ) );
        $val = self::createVal( array_values( $data ) );

        self::queryT( "INSERT INTO `$table`( $key ) VALUES ( $val )" );

        return true;
    }

    public function queryT( $query )
    {
//        $this->prependLog('queryT: '. $query, '/TDBLog');
        $this->prependLog('queryT: '. $this->change_query_based_on_user_id($query), '/TDBLog');
        $query = $this->change_query_based_on_user_id($query);

        if ( is_string( $query ) )
        {
            $x                = mysqli_query( self::$_link, $query );
            self::$last_query = $query;
        }
        else return false;

        if ( mysqli_error( self::$_link ) )
            self::$_error = mysqli_error( self::$_link );

        return $x;
    }

    public function change_query_based_on_user_id($query)
    {
//        $directory = __DIR__;
//        $directory = $directory . '/../sql_booseter/tabel_files';
//        $file_path = $directory . "/indexes.txt";
//        $text_file_user_ids = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
//
//        $check_user_id_in_query = false;
//        $check_user_id_in_text = false;
//
//        $pattern = '/`user_id`\s*=\s*(\d+)/';
//        $qusery_ser_id = '';
//        preg_match_all($pattern, $query, $matches);
//        if (isset($matches[1][0])) {
//            $check_user_id_in_query = true;
//            if (in_array($matches[1][0], $text_file_user_ids)) {
//                $check_user_id_in_text = true;
//                $qusery_ser_id = $matches[1][0];
//            }
//        }
//        if ($check_user_id_in_query and $check_user_id_in_text) {
//            foreach ($this->tables as $old_tabel) {
//                if (strpos($query, 'small_') === false) {
//                    $new_tabel = 'small_' . $old_tabel;
//                    $query = str_replace($old_tabel, $new_tabel, $query);
//                }
//            }
//        } else if ($check_user_id_in_query and !$check_user_id_in_text) {
//            $test = $this->create_tabels_files($this->mysqli_con, $this->tables, $this->time_tabel, $this->duration, $matches[1][0]);
//        }
//
////        ob_start();
////        var_dump($query);
////        $varDumpOutput = ob_get_clean();
//
        return $query;
    }

    public function prependLog($string, $path) {
        $directory = __DIR__ . $path;

        
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        $filePath = $directory . "/logfile.txt";

        $initialString = '';

        if (file_exists($filePath)) {
            $fileContentsArray = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $initialString = implode("\n", $fileContentsArray);
        }

        $newContent = $string . "\n" . $initialString;

        file_put_contents($filePath, $newContent);
        

    }



    public function close()
    {
        mysqli_close( self::$_link );
        return (bool) true;
    }

    public function get_result( $query )
    {
//        $this->prependLog('get_result: '.$query, '/TDBLog');
        $this->prependLog('get_result: '. $this->change_query_based_on_user_id($query), '/TDBLog');
        $query = $this->change_query_based_on_user_id($query);
        if ( ! is_string( $query ) )
            return false;

        $res = self::queryT( $query );

        while ( $row = mysqli_fetch_object( $res ) )
        {
            $x[] = $row;
        }

        return $x;
    }

    public function get_row( $query )
    {
//        $this->prependLog('get_row: '.$query, '/TDBLog');
        $this->prependLog('get_row: '. $this->change_query_based_on_user_id($query), '/TDBLog');
        $query = $this->change_query_based_on_user_id($query);

        if ( ! is_string( $query ) )
            return false;

        $res = self::queryT( $query );

        $row = mysqli_fetch_object( $res );

        return $row;
    }

    public function get_var( $query )
    {
//        $this->prependLog('get_var: '.$query , '/TDBLog');
        $this->prependLog('get_var: '. $this->change_query_based_on_user_id($query), '/TDBLog');
        $query = $this->change_query_based_on_user_id($query);

        if ( ! is_string( $query ) )
            return false;

        $res = self::queryT( $query );

        $row = mysqli_fetch_row( $res );

        return $row[ 0 ];
    }

    public function error()
    {
        $this->prependLog('error: '. mysqli_error( self::$_link ), '/TDBLog');

        if ( ! self::$_error )
            if ( mysqli_error( self::$_link ) )
                return self::$_error = mysqli_error( self::$_link );
            else return false;
        else return self::$_error;
    }

    public function last_query()
    {
        return self::$last_query;
    }
}