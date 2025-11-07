<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) { // Solo administradores
    $settings = new admin_settingpage(
        'local_ai_forum_assistant',
        get_string('pluginname', 'local_ai_forum_assistant')
    );

    // Encabezado
    $settings->add(new admin_setting_heading(
        'local_ai_forum_assistant/settingsheading',
        get_string('settingsheading', 'local_ai_forum_assistant'),
        get_string('settingsheading_desc', 'local_ai_forum_assistant')
    ));

    // API Key
    $settings->add(new admin_setting_configtext(
        'local_ai_forum_assistant/apikey',
        get_string('apikey', 'local_ai_forum_assistant'),
        get_string('apikey_desc', 'local_ai_forum_assistant'),
        '',
        PARAM_TEXT
    ));

    // Modelo
    $settings->add(new admin_setting_configselect(
        'local_ai_forum_assistant/aimodel',
        get_string('aimodel', 'local_ai_forum_assistant'),
        get_string('aimodel_desc', 'local_ai_forum_assistant'),
        'gpt-4o-mini',
        [
            'gpt-4o-mini' => 'GPT-4o-mini (rápido, económico)',
            'gpt-4o'      => 'GPT-4o (equilibrado)',
            'gpt-4-turbo' => 'GPT-4 Turbo (potente)',
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo (más económico)',
        ]
    ));

    // Activar plugin globalmente
    $settings->add(new admin_setting_configcheckbox(
        'local_ai_forum_assistant/enable',
        get_string('enableplugin', 'local_ai_forum_assistant'),
        get_string('enableplugin_desc', 'local_ai_forum_assistant'),
        0
    ));

    // Prompt base
    $settings->add(new admin_setting_configtextarea(
        'local_ai_forum_assistant/prompttemplate',
        get_string('prompttemplate', 'local_ai_forum_assistant'),
        get_string('prompttemplate_desc', 'local_ai_forum_assistant'),
        "Actúa como un docente que participa en foros de Moodle. Da retroalimentación constructiva y fomenta la reflexión."
    ));

    // Docente por defecto
    $settings->add(new admin_setting_configtext(
        'local_ai_forum_assistant/defaultteacherid',
        get_string('defaultteacherid', 'local_ai_forum_assistant'),
        get_string('defaultteacherid_desc', 'local_ai_forum_assistant'),
        '',
        PARAM_INT
    ));

    // Cursos habilitados
    $settings->add(new admin_setting_configtext(
        'local_ai_forum_assistant/enabledcourses',
        get_string('enabledcourses', 'local_ai_forum_assistant'),
        get_string('enabledcourses_desc', 'local_ai_forum_assistant'),
        '',
        PARAM_TEXT
    ));

    // Retraso
    $settings->add(new admin_setting_configtext(
        'local_ai_forum_assistant/responsedelay',
        get_string('responsedelay', 'local_ai_forum_assistant'),
        get_string('responsedelay_desc', 'local_ai_forum_assistant'),
        30,
        PARAM_INT
    ));

    // -------- LICENCIAS --------
    $settings->add(new admin_setting_heading(
        'local_ai_forum_assistant/licheading',
        get_string('licheading', 'local_ai_forum_assistant'),
        get_string('licheading_desc', 'local_ai_forum_assistant')
    ));

    // URL del servidor
    $settings->add(new admin_setting_configtext(
        'local_ai_forum_assistant/licserver',
        get_string('licserver', 'local_ai_forum_assistant'),
        get_string('licserver_desc', 'local_ai_forum_assistant'),
        '',
        PARAM_RAW_TRIMMED
    ));

    // License key
    $settings->add(new admin_setting_configtext(
        'local_ai_forum_assistant/licensekey',
        get_string('licensekey', 'local_ai_forum_assistant'),
        get_string('licensekey_desc', 'local_ai_forum_assistant'),
        '',
        PARAM_RAW_TRIMMED
    ));

    // Secret
    $settings->add(new admin_setting_configtext(
        'local_ai_forum_assistant/licsecret',
        get_string('licsecret', 'local_ai_forum_assistant'),
        get_string('licsecret_desc', 'local_ai_forum_assistant'),
        '',
        PARAM_RAW_TRIMMED
    ));

    $ADMIN->add('localplugins', $settings);
}
