<?php
namespace local_ai_forum_assistant\task;

defined('MOODLE_INTERNAL') || die();

use core\task\scheduled_task;
use local_ai_forum_assistant\license_client;

class validate_license_task extends scheduled_task {
    public function get_name() {
        return get_string('task_validatelicense', 'local_ai_forum_assistant');
    }

    public function execute() {
        $res = license_client::validate();
        if (empty($res['ok'])) {
            mtrace('[AI Forum Assistant] License validation failed: ' . (isset($res['error']) ? $res['error'] : 'unknown'));
        } else {
            mtrace('[AI Forum Assistant] License validation ok. valid=' . (!empty($res['valid']) ? '1' : '0'));
        }
        return true;
    }
}
