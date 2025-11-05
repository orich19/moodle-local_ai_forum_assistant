<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/ai_forum_assistant:manage' => [
        'riskbitmask' => RISK_CONFIG,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW
        ],
    ],
];
