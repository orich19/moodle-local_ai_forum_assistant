<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Versión del plugin local_ai_forum_assistant.
 *
 * Este archivo define los metadatos de versión y compatibilidad de Moodle.
 */

$plugin->component = 'local_ai_forum_assistant'; // Nombre completo del componente.
$plugin->version   = 2025102900;                 // Incrementa esta versión (año + mes + día + contador).
$plugin->requires  = 2022041900;                 // Requiere Moodle 4.0 o superior.
$plugin->maturity  = MATURITY_STABLE;            // Estado del plugin (STABLE, BETA, etc.).
$plugin->release   = 'v1.1';                     // Versión legible para humanos.
$plugin->has_config = true;                      // ✅ Habilita la página de configuración en "Extensiones locales".
