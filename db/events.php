<?php
defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname'   => '\mod_forum\event\post_created',
        'callback'    => '\local_ai_forum_assistant\event_observer::forum_post_created',
        'includefile' => '/local/ai_forum_assistant/classes/event_observer.php',
        'internal'    => false,
        'priority'    => 1000,
    ],
];
