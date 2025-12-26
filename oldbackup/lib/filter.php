<?php

defined('BASE_DIR') || die('NO ACCESS');

/**
 * Hooks a function on to a specific action.
 *
 * Actions are the hooks that the WordPress core launches at specific points
 * during execution, or when specific events occur. Plugins can specify that
 * one or more of its PHP functions are executed at these points, using the
 * Action API.
 *
 * @param string $tag The name of the action to which the $function_to_add is hooked.
 * @param callable $function_to_add The name of the function you wish to be called.
 * @param int $priority Optional. Used to specify the order in which the functions
 *                                  associated with a particular action are executed. Default 10.
 *                                  Lower numbers correspond with earlier execution,
 *                                  and functions with the same priority are executed
 *                                  in the order in which they were added to the action.
 * @param int $accepted_args Optional. The number of arguments the function accepts. Default 1.
 * @return true Will always return true.
 * @since 1.2.0
 *
 */
function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1)
{
    global $wp_filter;
    if (!isset($wp_filter[$tag])) {
        $wp_filter[$tag] = new Hook();
    }
    $wp_filter[$tag]->add_filter($tag, $function_to_add, $priority, $accepted_args);
    return true;
}

/**
 * Calls the callback functions that have been added to a filter hook.
 *
 * The callback functions attached to the filter hook are invoked by calling
 * this function. This function can be used to create a new filter hook by
 * simply calling this function with the name of the new hook specified using
 * the `$tag` parameter.
 *
 * The function also allows for multiple additional arguments to be passed to hooks.
 *
 * Example usage:
 *
 *     // The filter callback function
 *     function example_callback( $string, $arg1, $arg2 ) {
 *         // (maybe) modify $string
 *         return $string;
 *     }
 *     add_filter( 'example_filter', 'example_callback', 10, 3 );
 *
 *     /*
 *      * Apply the filters by calling the 'example_callback()' function that's
 *      * hooked onto `example_filter` above.
 *      *
 *      * - 'example_filter' is the filter hook
 *      * - 'filter me' is the value being filtered
 *      * - $arg1 and $arg2 are the additional arguments passed to the callback.
 *     $value = apply_filters( 'example_filter', 'filter me', $arg1, $arg2 );
 *
 * @param string $tag The name of the filter hook.
 * @param mixed $value The value to filter.
 * @param mixed ...$args Additional parameters to pass to the callback functions.
 * @return mixed The filtered value after all hooked functions are applied to it.
 * @global array $wp_filter Stores all of the filters.
 * @global array $wp_current_filter Stores the list of current filters with the current one last.
 *
 * @since 0.71
 *
 */
function apply_filters($tag, $value = '')
{
    global $wp_filter, $wp_current_filter;

    $args = func_get_args();

    // Do 'all' actions first.
    if (isset($wp_filter['all'])) {
        $wp_current_filter[] = $tag;
        _wp_call_all_hook($args);
    }

    if (!isset($wp_filter[$tag])) {
        if (isset($wp_filter['all'])) {
            array_pop($wp_current_filter);
        }
        return $value;
    }

    if (!isset($wp_filter['all'])) {
        $wp_current_filter[] = $tag;
    }

    // Don't pass the tag name to WP_Hook.
    array_shift($args);

    $filtered = $wp_filter[$tag]->apply_filters($value, $args);

    array_pop($wp_current_filter);

    return $filtered;
}

/**
 * Removes a function from a specified filter hook.
 *
 * This function removes a function attached to a specified filter hook. This
 * method can be used to remove default functions attached to a specific filter
 * hook and possibly replace them with a substitute.
 *
 * To remove a hook, the $function_to_remove and $priority arguments must match
 * when the hook was added. This goes for both filters and actions. No warning
 * will be given on removal failure.
 *
 * @param string $tag The filter hook to which the function to be removed is hooked.
 * @param callable $function_to_remove The name of the function which should be removed.
 * @param int $priority Optional. The priority of the function. Default 10.
 * @return bool    Whether the function existed before it was removed.
 * @since 1.2.0
 *
 * @global array $wp_filter Stores all of the filters
 *
 */
function remove_filter($tag, $function_to_remove, $priority = 10)
{
    global $wp_filter;

    $r = false;
    if (isset($wp_filter[$tag])) {
        $r = $wp_filter[$tag]->remove_filter($tag, $function_to_remove, $priority);
        if (!$wp_filter[$tag]->callbacks) {
            unset($wp_filter[$tag]);
        }
    }

    return $r;
}

/**
 * Check if any filter has been registered for a hook.
 *
 * @param string $tag The name of the filter hook.
 * @param callable|bool $function_to_check Optional. The callback to check for. Default false.
 * @return false|int If $function_to_check is omitted, returns boolean for whether the hook has
 *                   anything registered. When checking a specific function, the priority of that
 *                   hook is returned, or false if the function is not attached. When using the
 *                   $function_to_check argument, this function may return a non-boolean value
 *                   that evaluates to false (e.g.) 0, so use the === operator for testing the
 *                   return value.
 * @global array $wp_filter Stores all of the filters.
 *
 * @since 2.5.0
 *
 */
function has_filter($tag, $function_to_check = false)
{
    global $wp_filter;

    if (!isset($wp_filter[$tag])) {
        return false;
    }

    return $wp_filter[$tag]->has_filter($tag, $function_to_check);
}

/**
 * Retrieve the name of a filter currently being processed.
 *
 * The function current_filter() only returns the most recent filter or action
 * being executed. did_action() returns true once the action is initially
 * processed.
 *
 * This function allows detection for any filter currently being
 * executed (despite not being the most recent filter to fire, in the case of
 * hooks called from hook callbacks) to be verified.
 *
 * @param null|string $filter Optional. Filter to check. Defaults to null, which
 *                            checks if any filter is currently being run.
 * @return bool Whether the filter is currently in the stack.
 * @see did_action()
 * @global array $wp_current_filter Current filter.
 *
 * @since 3.9.0
 *
 * @see current_filter()
 */
function doing_filter($filter = null)
{
    global $wp_current_filter;

    if (null === $filter) {
        return !empty($wp_current_filter);
    }

    return in_array($filter, $wp_current_filter);
}