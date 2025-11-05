<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade steps for local_ai_forum_assistant.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_ai_forum_assistant_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // 🔹 Paso: crear la tabla local_ai_forum_assistant_coursecfg si no existe.
    if ($oldversion < 2025102801) {

        // Definir la tabla.
        $table = new xmldb_table('local_ai_forum_assistant_coursecfg');

        // Campos.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');

        // Claves.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('unique_course', XMLDB_KEY_UNIQUE, ['courseid']);

        // Crear tabla si no existe.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Guardar punto de actualización.
        upgrade_plugin_savepoint(true, 2025102801, 'local', 'ai_forum_assistant');
    }

    return true;
}
