<?php
namespace local_ai_forum_assistant\task;

defined('MOODLE_INTERNAL') || die();

use core\task\scheduled_task;
use local_ai_forum_assistant\license_client;

class validate_license_task extends scheduled_task {

    /**
     * Nombre visible en la página de tareas programadas.
     */
    public function get_name() {
        return get_string('task_validatelicense', 'local_ai_forum_assistant');
    }

    /**
     * Ejecuta la validación de licencia contra el servidor remoto.
     */
    public function execute() {
        mtrace('[AI Forum Assistant] Iniciando validación de licencia...');

        $result = license_client::validate();

        if (empty($result['ok'])) {
            mtrace('[AI Forum Assistant] ❌ Validación fallida: ' . ($result['error'] ?? 'error desconocido'));
        } else {
            $status = !empty($result['valid']) ? 'válida ✅' : 'inválida ❌';
            mtrace('[AI Forum Assistant] Resultado de validación: ' . $status);
        }

        return true;
    }
}
