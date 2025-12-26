<?php

defined('BASE_DIR') || die('NO ACCESS');

require LIB_DIR . '/scan.php';

foreach (scan_dir(LIB_DIR) as $item) if (file_exists(LIB_DIR . '/' . $item) && $item != 'plugin.php') {
    require LIB_DIR . '/' . $item;
}
