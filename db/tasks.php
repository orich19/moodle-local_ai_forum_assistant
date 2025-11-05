<?php
defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => '\\local_ai_forum_assistant\\task\\validate_license_task',
        'blocking'  => 0,
        'minute'    => 'R',
        'hour'      => '1',
        'day'       => '*',
        'dayofweek' => '*',
        'month'     => '*'
    ],
];
