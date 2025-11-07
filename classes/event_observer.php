<?php
namespace local_ai_forum_assistant;

defined('MOODLE_INTERNAL') || die();

use local_ai_forum_assistant\license_client;

/**
 * Observador de eventos para el Asistente de Foros IA.
 */
class event_observer {

    /**
     * Evento disparado cuando se crea una nueva publicación en un foro.
     */
    public static function forum_post_created(\mod_forum\event\post_created $event) {
        global $DB;

        if (!license_client::is_allowed_now()) {
            debugging('[AI Forum Assistant] License invalid or not active.', DEBUG_DEVELOPER);
            return true;
        }

        // 🔸 Verificar si el plugin está activado y si existe la API Key.
        $enabled = get_config('local_ai_forum_assistant', 'enable');
        $apikey  = get_config('local_ai_forum_assistant', 'apikey');

        if (empty($enabled) || empty($apikey)) {
            debugging('[AI Forum Assistant] Plugin desactivado o API Key faltante.', DEBUG_DEVELOPER);
            return true;
        }

        // 🟡 Paso 1: Verificar si el curso está en la lista global habilitada.
        $enabledcourses = get_config('local_ai_forum_assistant', 'enabledcourses');
        $enabledlist = array_map('trim', explode(',', $enabledcourses ?? ''));

        if (!in_array($event->courseid, $enabledlist)) {
            debugging('[AI Forum Assistant] Curso '.$event->courseid.' no está en la lista global habilitada.', DEBUG_DEVELOPER);
            return true;
        }

        // 🟢 Paso 2: Verificar configuración local del curso.
        $record = $DB->get_record('local_ai_forum_assistant_coursecfg', ['courseid' => $event->courseid]);
        if (!$record || !$record->enabled) {
            debugging('[AI Forum Assistant] IA deshabilitada localmente para el curso ID: '.$event->courseid, DEBUG_DEVELOPER);
            return true;
        }

        // 🧠 Obtener datos de la publicación.
        $postid = $event->objectid;
        $post = $DB->get_record('forum_posts', ['id' => $postid], '*', MUST_EXIST);
        $discussion = $DB->get_record('forum_discussions', ['id' => $post->discussion], '*', MUST_EXIST);
        $forum = $DB->get_record('forum', ['id' => $discussion->forum], '*', MUST_EXIST);

        // ⚠️ Evitar responder a publicaciones de docentes (para no generar bucles).
        if (self::is_teacher($post->userid, $forum->course)) {
            debugging('[AI Forum Assistant] Publicación de docente detectada. No se genera respuesta.', DEBUG_DEVELOPER);
            return true;
        }

        // 🕒 Crear tarea ad hoc (asíncrona) para respuesta IA con retardo.
        $delay = (int) get_config('local_ai_forum_assistant', 'responsedelay');

        $task = new \local_ai_forum_assistant\task\post_ai_reply_task();
        $task->set_component('local_ai_forum_assistant');
        $task->set_custom_data([
            'postid' => $post->id,
            'discussionid' => $discussion->id,
            'forumid' => $forum->id,
            'courseid' => $forum->course
        ]);

        if ($delay > 0) {
            $runat = time() + $delay;
            $task->set_next_run_time($runat);
        }

        \core\task\manager::queue_adhoc_task($task);
        debugging('[AI Forum Assistant] Tarea IA programada para ejecutarse en '.$delay.' segundos.', DEBUG_DEVELOPER);

        return true;
    }

    /**
     * Llamada a la API de OpenAI para generar la respuesta.
     */
    public static function call_openai($prompt, $apikey) {
        $url = 'https://api.openai.com/v1/chat/completions';

        // ✅ Modelo y lenguaje seleccionados en la configuración.
        $model = get_config('local_ai_forum_assistant', 'aimodel') ?: 'gpt-4o-mini';
        $language = get_config('local_ai_forum_assistant', 'forcelanguage') ?: 'es';

        $data = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Eres un docente que participa activamente en foros educativos. 
                    Da retroalimentación constructiva, analítica y motivadora, sin usar saludos,
                    sin mencionar nombres de personas ni incluir firmas o despedidas.
                    Responde solo con el cuerpo del mensaje, en tono profesional, cálido y claro.
                    Todas las respuestas deben estar escritas en el idioma: ' . $language . '.'
                ],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7,
            'max_tokens' => 350,
        ];

        $options = [
            'http' => [
                'header'  => "Content-Type: application/json\r\n" .
                             "Authorization: Bearer $apikey\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
                'timeout' => 30,
            ],
        ];

        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);

        if ($result === false) {
            debugging('[AI Forum Assistant] Error al conectar con la API de OpenAI.', DEBUG_DEVELOPER);
            return null;
        }

        $json = json_decode($result, true);
        return $json['choices'][0]['message']['content'] ?? null;
    }

    /**
     * Método para generar y publicar respuesta IA.
     */
    public static function generate_and_post_ai_reply($forum, $discussion, $post, $apikey) {
        global $DB;

        // 📜 Prompt base configurable.
        $prompttemplate = get_config('local_ai_forum_assistant', 'prompttemplate') ??
            "Actúa como un docente que participa en foros de Moodle. 
            Da retroalimentación constructiva y fomenta la reflexión,
            sin incluir saludos, nombres de estudiantes ni despedidas.";

        // 🧩 Obtener mensaje inicial del foro (tema docente).
        $firstpost = $DB->get_record('forum_posts', ['id' => $discussion->firstpost]);
        $topiccontext = '';
        if ($firstpost && $firstpost->id != $post->id) {
            $topiccontext = "\n\nTema principal del foro (docente):\n" . strip_tags($firstpost->message);
        }

        // 🧠 Construir prompt contextualizado.
        $prompt = $prompttemplate
            . $topiccontext
            . "\n\nMensaje del estudiante:\n" . strip_tags($post->message)
            . "\n\nResponde considerando el tema original del foro y la relación con la intervención del estudiante, evitando saludos, nombres y firmas.";

        // Generar respuesta IA.
        $response = self::call_openai($prompt, $apikey);
        if ($response) {
            self::post_reply_as_teacher($forum, $discussion, $post, $response);
        }
    }

    /**
     * Publicar la respuesta generada por IA usando la cuenta de un docente real o por defecto.
     */
    public static function post_reply_as_teacher($forum, $discussion, $post, $message) {
        global $DB, $CFG, $USER;

        require_once($CFG->dirroot . '/mod/forum/lib.php');

        // 1️⃣ Buscar docente asignado al curso.
        $teacherid = self::get_teacher_userid($forum->course);

        // 2️⃣ Si no hay docente, usar el configurado por defecto.
        if (!$teacherid) {
            $defaultid = get_config('local_ai_forum_assistant', 'defaultteacherid');
            if (!empty($defaultid)) {
                $teacherid = $defaultid;
                debugging('[AI Forum Assistant] No se encontró docente asignado. Usando docente por defecto ID: '.$teacherid, DEBUG_DEVELOPER);
            } else {
                debugging('[AI Forum Assistant] No se encontró docente ni ID por defecto configurado. No se publica respuesta.', DEBUG_DEVELOPER);
                return;
            }
        }

        // 3️⃣ Obtener el usuario docente.
        $teacheruser = $DB->get_record('user', ['id' => $teacherid], '*', MUST_EXIST);

        // 4️⃣ Guardar sesión actual y establecer el docente temporalmente.
        $originaluser = $USER;
        \core\session\manager::set_user($teacheruser);

        // 5️⃣ Crear la nueva respuesta.
        $newpost = new \stdClass();
        $newpost->discussion = $discussion->id;
        $newpost->parent = $post->id;
        $newpost->subject = 'Re: ' . format_string($post->subject);
        $newpost->message = $message;
        $newpost->messageformat = FORMAT_HTML;
        $newpost->messagetrust = 1;
        $newpost->userid = $teacherid;
        $newpost->created = time();
        $newpost->modified = time();

        forum_add_new_post($newpost, null);

        // 6️⃣ Restaurar sesión original.
        \core\session\manager::set_user($originaluser);

        debugging('[AI Forum Assistant] Respuesta IA publicada correctamente con docente ID: '.$teacherid, DEBUG_DEVELOPER);
    }

    /**
     * Verifica si un usuario es docente en el curso (evita que IA responda a docentes).
     */
    private static function is_teacher($userid, $courseid) {
        global $DB;

        $context = \context_course::instance($courseid);
        $teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);

        if (!$teacherrole) {
            return false;
        }

        $sql = "SELECT 1
                  FROM {role_assignments} ra
                  JOIN {context} ctx ON ra.contextid = ctx.id
                 WHERE ra.userid = :userid
                   AND ra.roleid = :roleid
                   AND ctx.id = :contextid";

        $params = [
            'userid'    => $userid,
            'roleid'    => $teacherrole->id,
            'contextid' => $context->id,
        ];

        return $DB->record_exists_sql($sql, $params);
    }

    /**
     * Encuentra un docente asignado al curso (rol editingteacher) y devuelve su ID.
     */
    private static function get_teacher_userid($courseid) {
        global $DB;

        $context = \context_course::instance($courseid);
        $teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);

        if (!$teacherrole) {
            return null;
        }

        $sql = "SELECT ra.userid
                  FROM {role_assignments} ra
                  JOIN {context} ctx ON ra.contextid = ctx.id
                 WHERE ctx.id = :contextid
                   AND ra.roleid = :roleid
                 LIMIT 1";

        $params = [
            'contextid' => $context->id,
            'roleid'    => $teacherrole->id,
        ];

        $record = $DB->get_record_sql($sql, $params);
        return $record ? $record->userid : null;
    }
}
