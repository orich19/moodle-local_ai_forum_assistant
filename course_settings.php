<?php
require_once(__DIR__ . '/../../config.php');

$courseid = required_param('id', PARAM_INT);

// 📘 Obtener información del curso y verificar acceso.
$course = get_course($courseid);
require_login($course);
$context = context_course::instance($course->id);

// Solo los usuarios con permiso de edición pueden cambiar la configuración.
require_capability('moodle/course:update', $context);

// Configurar página.
$PAGE->set_url('/local/ai_forum_assistant/course_settings.php', ['id' => $courseid]);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('courseconfigtitle', 'local_ai_forum_assistant'));
$PAGE->set_heading(format_string($course->fullname));

global $DB;

// 🔹 Obtener o crear registro de configuración del curso.
$record = $DB->get_record('local_ai_forum_assistant_coursecfg', ['courseid' => $courseid]);
$currentenabled = $record ? (bool)$record->enabled : false;

// 🔸 Procesar formulario al enviar.
if (optional_param('save', false, PARAM_BOOL) && confirm_sesskey()) {
    $enabled = optional_param('enabled', 0, PARAM_BOOL) ? 1 : 0;

    if ($record) {
        $record->enabled = $enabled;
        $DB->update_record('local_ai_forum_assistant_coursecfg', $record);
    } else {
        $new = (object)[
            'courseid' => $courseid,
            'enabled'  => $enabled
        ];
        $DB->insert_record('local_ai_forum_assistant_coursecfg', $new);
    }

    // Mostrar mensaje de confirmación y redirigir.
    redirect(
        $PAGE->url,
        get_string('changessaved', 'local_ai_forum_assistant'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// 🔧 Renderización de la página.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('courseconfigtitle', 'local_ai_forum_assistant'));

// 🔹 Formulario manual simple.
echo html_writer::start_tag('form', ['method' => 'post', 'action' => $PAGE->url]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $courseid]);

// Checkbox de habilitación.
$checkboxattrs = [
    'type'  => 'checkbox',
    'name'  => 'enabled',
    'value' => 1
];
if ($currentenabled) {
    $checkboxattrs['checked'] = 'checked';
}

$checkbox = html_writer::empty_tag('input', $checkboxattrs);
echo html_writer::tag('label', $checkbox . ' ' . get_string('courseenable', 'local_ai_forum_assistant'));
echo html_writer::empty_tag('br');
echo html_writer::empty_tag('br');

// Botón de guardado.
echo html_writer::empty_tag('input', [
    'type'  => 'submit',
    'name'  => 'save',
    'value' => get_string('savechanges')
]);

echo html_writer::end_tag('form');
echo $OUTPUT->footer();
