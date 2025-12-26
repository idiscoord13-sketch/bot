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
function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1)
{
    return add_filter($tag, $function_to_add, $priority, $accepted_args);
}

/**
 * Execute functions hooked on a specific action hook.
 *
 * This function invokes all functions attached to action hook `$tag`. It is
 * possible to create new action hooks by simply calling this function,
 * specifying the name of the new hook using the `$tag` parameter.
 *
 * You can pass extra arguments to the hooks, much like you can with `apply_filters()`.
 *
 * Example usage:
 *
 *     // The action callback function
 *     function example_callback( $arg1, $arg2 ) {
 *         // (maybe) do something with the args
 *     }
 *     add_action( 'example_action', 'example_callback', 10, 2 );
 *
 *     /*
 *      * Trigger the actions by calling the 'example_callback()' function that's
 *      * hooked onto `example_action` above.
 *      *
 *      * - 'example_action' is the action hook
 *      * - $arg1 and $arg2 are the additional arguments passed to the callback.
 *     $value = do_action( 'example_action', $arg1, $arg2 );
 *
 * @param string $tag The name of the action to be executed.
 * @param mixed ...$arg Optional. Additional arguments which are passed on to the
 *                       functions hooked to the action. Default empty.
 * @global array $wp_filter Stores all of the filters
 * @global array $wp_actions Increments the amount of times action was triggered.
 * @global array $wp_current_filter Stores the list of current filters with the current one last
 *
 * @since 1.2.0
 * @since 5.3.0 Formalized the existing and already documented `...$arg` parameter
 *              by adding it to the function signature.
 *
 */
function do_action($tag, ...$arg)
{
    global $wp_filter, $wp_actions, $wp_current_filter;

    if (!isset($wp_actions[$tag])) {
        $wp_actions[$tag] = 1;
    } else {
        ++$wp_actions[$tag];
    }

    // Do 'all' actions first
    if (isset($wp_filter['all'])) {
        $wp_current_filter[] = $tag;
        $all_args = func_get_args();
        _wp_call_all_hook($all_args);
    }

    if (!isset($wp_filter[$tag])) {
        if (isset($wp_filter['all'])) {
            array_pop($wp_current_filter);
        }
        return;
    }

    if (!isset($wp_filter['all'])) {
        $wp_current_filter[] = $tag;
    }

    if (empty($arg)) {
        $arg[] = '';
    } elseif (is_array($arg[0]) && 1 === count($arg[0]) && isset($arg[0][0]) && is_object($arg[0][0])) {
        // Backward compatibility for PHP4-style passing of `array( &$this )` as action `$arg`.
        $arg[0] = $arg[0][0];
    }

    $wp_filter[$tag]->do_action($arg);

    array_pop($wp_current_filter);
}

/**
 * Removes a function from a specified action hook.
 *
 * This function removes a function attached to a specified action hook. This
 * method can be used to remove default functions attached to a specific filter
 * hook and possibly replace them with a substitute.
 *
 * @param string $tag The action hook to which the function to be removed is hooked.
 * @param callable $function_to_remove The name of the function which should be removed.
 * @param int $priority Optional. The priority of the function. Default 10.
 * @return bool Whether the function is removed.
 * @since 1.2.0
 *
 */
function remove_action($tag, $function_to_remove, $priority = 10)
{
    return remove_filter($tag, $function_to_remove, $priority);
}

/**
 * Check if any action has been registered for a hook.
 *
 * @param string $tag The name of the action hook.
 * @param callable|bool $function_to_check Optional. The callback to check for. Default false.
 * @return bool|int If $function_to_check is omitted, returns boolean for whether the hook has
 *                  anything registered. When checking a specific function, the priority of that
 *                  hook is returned, or false if the function is not attached. When using the
 *                  $function_to_check argument, this function may return a non-boolean value
 *                  that evaluates to false (e.g.) 0, so use the === operator for testing the
 *                  return value.
 * @see has_filter() has_action() is an alias of has_filter().
 *
 * @since 2.5.0
 *
 */
function has_action($tag, $function_to_check = false)
{
    return has_filter($tag, $function_to_check);
}

/**
 * Retrieve the name of an action currently being processed.
 *
 * @param string|null $action Optional. Action to check. Defaults to null, which checks
 *                            if any action is currently being run.
 * @return bool Whether the action is currently in the stack.
 * @since 3.9.0
 *
 */
function doing_action($action = null)
{
    return doing_filter($action);
}