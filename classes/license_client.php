<?php
namespace local_ai_forum_assistant;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');

class license_client {

    private static function base(): string {
        $base = get_config('local_ai_forum_assistant', 'licserver') ?? '';
        return rtrim($base, '/');
    }

    private static function secret(): string {
        return get_config('local_ai_forum_assistant', 'licsecret') ?? '';
    }

    public static function activate(): array {
        global $USER;
        $key = get_config('local_ai_forum_assistant', 'licensekey');
        if (empty($key)) return ['ok'=>false,'error'=>'no_key'];

        $curl = new \curl();
        $res = $curl->post(self::base().'/wp-json/ailm/v1/activate', [
            'license_key' => $key,
            'email'       => $USER->email ?? '',
            'secret'      => self::secret()
        ]);
        return json_decode($res ?? '[]', true) ?: ['ok'=>false,'error'=>'network'];
    }

    public static function validate(): array {
        $key = get_config('local_ai_forum_assistant', 'licensekey');
        if (empty($key)) return ['ok'=>false,'valid'=>false,'error'=>'no_key'];

        $curl = new \curl();
        $res = $curl->post(self::base().'/wp-json/ailm/v1/validate', [
            'license_key' => $key,
            'secret'      => self::secret()
        ]);
        $data = json_decode($res ?? '[]', true) ?: ['ok'=>false,'valid'=>false];

        set_config('license_last_check', time(), 'local_ai_forum_assistant');
        set_config('license_last_valid', !empty($data['valid']) ? 1 : 0, 'local_ai_forum_assistant');

        return $data;
    }

    public static function deactivate(): array {
        $key = get_config('local_ai_forum_assistant', 'licensekey');
        if (empty($key)) return ['ok'=>false,'error'=>'no_key'];

        $curl = new \curl();
        $res = $curl->post(self::base().'/wp-json/ailm/v1/deactivate', [
            'license_key' => $key,
            'secret'      => self::secret()
        ]);
        return json_decode($res ?? '[]', true) ?: ['ok'=>false];
    }

    public static function is_allowed_now(): bool {
        $last   = (int)get_config('local_ai_forum_assistant', 'license_last_check');
        $valid  = (int)get_config('local_ai_forum_assistant', 'license_last_valid');

        if (!$last || (time() - $last) > 6*3600) {
            $res = self::validate();
            return !empty($res['valid']);
        }
        return (bool)$valid;
    }
}
