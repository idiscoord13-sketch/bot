<?php

require_once WP_DIR . '/Hook.php';

/** @var Hook[] $wp_filter */
global $wp_filter, $wp_actions, $wp_current_filter;

if ($wp_filter) {
    $wp_filter = Hook::build_preinitialized_hooks($wp_filter);
} else {
    $wp_filter = array();
}

function _wp_filter_build_unique_id($tag, $function, $priority)
{
    global $wp_filter;
    static $filter_id_count = 0;

    if (is_string($function)) {
        return $function;
    }

    if (is_object($function)) {
        // Closures are currently implemented as objects
        $function = array($function, '');
    } else {
        $function = (array)$function;
    }

    if (is_object($function[0])) {
        // Object Class Calling
        return spl_object_hash($function[0]) . $function[1];
    } elseif (is_string($function[0])) {
        // Static Calling
        return $function[0] . '::' . $function[1];
    }
}

function _wp_call_all_hook($args)
{
    global $wp_filter;

    $wp_filter['all']->do_all_hook($args);
}

