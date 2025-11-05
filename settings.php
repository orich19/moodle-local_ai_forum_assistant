<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_ai_forum_assistant',
        get_string('pluginname', 'local_ai_forum_assistant')
    );

    $settings->add(new admin_setting_heading(
        'local_ai_forum_assistant/settingsheading',
        get_string('settingsheading', 'local_ai_forum_assistant'),
        get_string('settingsheading_desc', 'local_ai_forum_assistant')
    ));

    $settings->add(new admin_setting_configtext(
        'local_ai_forum_assistant/apikey',
        get_string('apikey', 'local_ai_forum_assistant'),
        get_string('apikey_desc', 'local_ai_forum_assistant'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configselect(
        'local_ai_forum_assistant/aimodel',
        get_string('aimodel', 'local_ai_forum_assistant'),
        get_string('aimodel_desc', 'local_ai_forum_assistant'),
        'gpt-4o-mini',
        [
            'gpt-4o-mini' => 'GPT-4o-mini (rápido, económico)',
            'gpt-4o' => 'GPT-4o (equilibrado)',
            'gpt-4-turbo' => 'GPT-4 Turbo (potente)',
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo (más económico)',
        ]
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_ai_forum_assistant/enable',
        get_string('enableplugin', 'local_ai_forum_assistant'),
        get_string('enableplugin_desc', 'local_ai_forum_assistant'),
        0
    ));

    $settings->add(new admin_setting_configtextarea(
        'local_ai_forum_assistant/prompttemplate',
        get_string('prompttemplate', 'local_ai_forum_assistant'),
        get_string('prompttemplate_desc', 'local_ai_forum_assistant'),
        "Actúa como un docente que participa en foros de Moodle. Da retroalimentación constructiva y fomenta la reflexión."
    ));

    $settings->add(new admin_setting_configtext(
        'local_ai_forum_assistant/defaultteacherid',
        get_string('defaultteacherid', 'local_ai_forum_assistant'),
        get_string('defaultteacherid_desc', 'local_ai_forum_assistant'),
        '',
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_ai_forum_assistant/enabledcourses',
        get_string('enabledcourses', 'local_ai_forum_assistant'),
        get_string('enabledcourses_desc', 'local_ai_forum_assistant'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_ai_forum_assistant/responsedelay',
        get_string('responsedelay', 'local_ai_forum_assistant'),
        get_string('responsedelay_desc', 'local_ai_forum_assistant'),
        30,
        PARAM_INT
    ));

    // ✅ License server settings
    $settings->add(new admin_setting_configtext(
        'local_ai_forum_assistant/licserver',
        get_string('licserver', 'local_ai_forum_assistant'),
        get_string('licserver_desc', 'local_ai_forum_assistant'),
        '',
        PARAM_RAW_TRIMMED
    ));

    $settings->add(new admin_setting_configtext(
        'local_ai_forum_assistant/licensekey',
        get_string('licensekey', 'local_ai_forum_assistant'),
        get_string('licensekey_desc', 'local_ai_forum_assistant'),
        '',
        PARAM_RAW_TRIMMED
    ));

    // (Opcional en MVP) secret global del servidor WP.
    // Se puede omitir si luego migramos a token por licencia.
    $settings->add(new admin_setting_configtext(
        'local_ai_forum_assistant/licsecret',
        get_string('licsecret', 'local_ai_forum_assistant'),
        get_string('licsecret_desc', 'local_ai_forum_assistant'),
        '',
        PARAM_RAW_TRIMMED
    ));

    // Registrar la página en el panel de administración → Extensiones locales.
    $ADMIN->add('localplugins', $settings);
}
