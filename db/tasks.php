<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Definición de tareas programadas (scheduled tasks) para local_ai_forum_assistant.
 *
 * Aunque la tarea principal de respuesta IA se maneja como "adhoc task",
 * este archivo es útil para definir tareas de mantenimiento o limpieza periódica
 * (si en el futuro las agregas).
 */

$tasks = [
    // Ejemplo de tarea futura (comentada):
    // [
    //     'classname' => 'local_ai_forum_assistant\task\cleanup_old_tasks',
    //     'blocking' => 0,
    //     'minute' => 'R',
    //     'hour' => '2',
    //     'day' => '*',
    //     'dayofweek' => '*',
    //     'month' => '*',
    //     'disabled' => 0,
    //     'description' => 'Limpia registros antiguos de respuestas IA almacenadas en logs o tablas temporales.'
    // ],
];
