<?php
namespace local_ai_forum_assistant;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/filelib.php');

class license_client {
    private static function base() {
        $base = get_config('local_ai_forum_assistant', 'licserver') ?? '';
        return rtrim($base, '/');
    }

    private static function secret() {
        // MVP: secret global. Futuro: token por licencia.
        return get_config('local_ai_forum_assistant', 'licsecret') ?? '';
    }

    public static function activate(): array {
        global $USER;
        $key = get_config('local_ai_forum_assistant', 'licensekey');
        if (empty($key)) {
            return ['ok' => false, 'error' => 'no_key'];
        }
        $url = self::base() . '/wp-json/ailm/v1/activate';
        $curl = new \curl();
        $res = $curl->post($url, [
            'license_key' => $key,
            'email'       => isset($USER->email) ? $USER->email : '',
            'secret'      => self::secret()
        ], ['timeout' => 15]);

        return json_decode($res ?? '[]', true) ?: ['ok' => false, 'error' => 'network'];
    }

    public static function validate(): array {
        $key = get_config('local_ai_forum_assistant', 'licensekey');
        if (empty($key)) {
            return ['ok' => false, 'valid' => false, 'error' => 'no_key'];
        }
        $url = self::base() . '/wp-json/ailm/v1/validate';
        $curl = new \curl();
        $res = $curl->post($url, [
            'license_key' => $key,
            'secret'      => self::secret()
        ], ['timeout' => 15]);

        $data = json_decode($res ?? '[]', true) ?: ['ok' => false, 'valid' => false, 'error' => 'network'];

        // Cache corto (6 horas)
        set_config('license_last_check', time(), 'local_ai_forum_assistant');
        set_config('license_last_valid', !empty($data['valid']) ? 1 : 0, 'local_ai_forum_assistant');

        return $data;
    }

    public static function deactivate(): array {
        $key = get_config('local_ai_forum_assistant', 'licensekey');
        if (empty($key)) {
            return ['ok' => false, 'error' => 'no_key'];
        }
        $url = self::base() . '/wp-json/ailm/v1/deactivate';
        $curl = new \curl();
        $res = $curl->post($url, [
            'license_key' => $key,
            'secret'      => self::secret()
        ], ['timeout' => 15]);

        return json_decode($res ?? '[]', true) ?: ['ok' => false, 'error' => 'network'];
    }

    public static function is_allowed_now(): bool {
        $last  = (int) get_config('local_ai_forum_assistant', 'license_last_check');
        $valid = (int) get_config('local_ai_forum_assistant', 'license_last_valid');

        // Si nunca se validó o han pasado >6h, revalida online
        if (!$last || (time() - $last) > 6 * 3600) {
            $res = self::validate();
            return !empty($res['valid']);
        }
        return (bool)$valid;
    }
}
