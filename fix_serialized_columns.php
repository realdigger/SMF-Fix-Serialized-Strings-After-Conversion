<?php

// Fixes corrupted serialized strings after a character set conversion.
function fix_serialized_columns()
{
    global $smcFunc;

    $request = $smcFunc['db_query']('', '
        SELECT id_action, extra
        FROM {db_prefix}log_actions',
        array()
    );

    while ($row = $smcFunc['db_fetch_assoc']($request)) {

        // remove, delete
        if (safe_unserialize($row['extra']) === false && preg_match('~^(a:3:{s:5:"topic";i:\d+;s:7:"subject";s:)(\d+):"(.+)"(;s:6:"member";s:5:"\d+";})$~',
                $row['extra'], $matches) === 1) {
            $smcFunc['db_query']('', '
				UPDATE {db_prefix}log_actions
				SET extra = {string:extra}
				WHERE id_action = {int:current_action}',
                array(
                    'current_action' => $row['id_action'],
                    'extra'          => $matches[1] . strlen($matches[3]) . ':"' . $matches[3] . '"' . $matches[4],
                )
            );
        }

        // delete_member
        if (safe_unserialize($row['extra']) === false && preg_match('~^(a:3:{s:6:"member";s:)(\d+):"(.*)"(;s:4:"name";s:)(\d+):"(.*)"(;s:12:"member_acted";s:)(\d+):"(.*)"(;})$~',
                $row['extra'], $matches) === 1) {
            $smcFunc['db_query']('', '
                UPDATE {db_prefix}log_actions
                SET extra = {string:extra}
                WHERE id_action = {int:current_action}',
                array(
                    'current_action' => $row['id_action'],
                    'extra'          => $matches[1] . strlen($matches[3]) . ':"' . $matches[3] . '"' . $matches[4] . strlen($matches[6]) . ':"' . $matches[6] . '"' . $matches[7] . strlen($matches[9]) . ':"' . $matches[9] . '"' . $matches[10],
                )
            );
        }

        // id_group, additional_groups, real_name, personal_text, location ...etc
        if (safe_unserialize($row['extra']) === false && preg_match('~^(a:3:{s:8:"previous";s:)(\d+):"(.*)"(;s:3:"new";s:)(\d+):"(.*)"(;s:10:"applicator";s:\d+:"\d+";})$~',
                $row['extra'], $matches) === 1) {
            $smcFunc['db_query']('', '
                UPDATE {db_prefix}log_actions
                SET extra = {string:extra}
                WHERE id_action = {int:current_action}',
                array(
                    'current_action' => $row['id_action'],
                    'extra'          => $matches[1] . strlen($matches[3]) . ':"' . $matches[3] . '"' . $matches[4] . strlen($matches[6]) . ':"' . $matches[6] . '"' . $matches[7],
                )
            );
        }

        // added_to_group
        if (safe_unserialize($row['extra']) === false && preg_match('~^(a:2:{s:5:"group";s:)(\d+):"(.*)"(;s:6:"member";i:\d+;})$~',
                $row['extra'], $matches) === 1) {
            echo $row['extra'] . '<br />';
            echo $matches[1] . strlen($matches[3]) . ':"' . $matches[3] . '"' . $matches[4] . '<br />';
            $smcFunc['db_query']('', '
                UPDATE {db_prefix}log_actions
                SET extra = {string:extra}
                WHERE id_action = {int:current_action}',
                array(
                    'current_action' => $row['id_action'],
                    'extra'          => $matches[1] . strlen($matches[3]) . ':"' . $matches[3] . '"' . $matches[4],
                )
            );
        }

        // add_group, edited_group
        if (safe_unserialize($row['extra']) === false && preg_match('~^(a:1:{s:5:"group";s:)(\d+):"(.*)"(;})$~',
                $row['extra'], $matches) === 1) {
            $smcFunc['db_query']('', '
                UPDATE {db_prefix}log_actions
                SET extra = {string:extra}
                WHERE id_action = {int:current_action}',
                array(
                    'current_action' => $row['id_action'],
                    'extra'          => $matches[1] . strlen($matches[3]) . ':"' . $matches[3] . '"' . $matches[4],
                )
            );
        }

    }
    $smcFunc['db_free_result']($request);

    // Refresh some cached data.
    updateSettings(array(
        'memberlist_updated' => time(),
    ));
}
