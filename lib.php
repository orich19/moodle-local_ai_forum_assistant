<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Añade el enlace “Configuración IA del curso” al panel de administración del curso.
 *
 * Este enlace aparece en el bloque de navegación del curso (Course administration)
 * solo para usuarios con capacidad de edición (moodle/course:update).
 *
 * @param navigation_node $navigation Nodo de navegación del curso.
 * @param stdClass $course Objeto del curso actual.
 * @param context_course $context Contexto del curso.
 * @return void
 */
function local_ai_forum_assistant_extend_navigation_course($navigation, $course, $context) {
    // Verificar contexto válido y permisos.
    if ($context->contextlevel !== CONTEXT_COURSE) {
        return;
    }

    if (!has_capability('moodle/course:update', $context)) {
        return;
    }

    // Evitar errores en páginas fuera del curso.
    if (empty($course) || empty($course->id)) {
        return;
    }

    // Crear URL hacia la página de configuración del curso.
    $url = new moodle_url('/local/ai_forum_assistant/course_settings.php', ['id' => $course->id]);

    // Texto traducible del enlace.
    $linktext = get_string('courseconfigtitle', 'local_ai_forum_assistant');

    // Agregar el enlace al menú del curso.
    $navigation->add(
        $linktext,
        $url,
        navigation_node::TYPE_SETTING,
        null,
        null,
        new pix_icon('i/settings', '')
    );
}
