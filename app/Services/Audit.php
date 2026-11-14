<?php

namespace App\Services;

use App\Models\AuditLog;

class Audit
{
    private const MASK = ['password', 'password_confirmation', 'password_hash', 'token', 'token_hash', 'invitation_token_hash', '_token'];

    public static function log(string $action, string $entity, $entityId = null, ?array $old = null, ?array $new = null, string $result = 'Success', ?int $userId = null): void
    {
        try {
            AuditLog::create([
                'user_id' => $userId ?? auth()->id(),
                'action_type' => substr($action, 0, 40),
                'entity_name' => substr($entity, 0, 40),
                'entity_id' => $entityId !== null ? (string) $entityId : null,
                'old_value' => $old ? self::mask($old) : null,
                'new_value' => $new ? self::mask($new) : null,
                'result' => $result,
                'ip_address' => request()?->ip() ?? '0.0.0.0',
                'user_agent' => substr((string) request()?->userAgent(), 0, 255),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private static function mask(array $data): array
    {
        foreach ($data as $k => $v) {
            if (in_array($k, self::MASK, true)) {
                $data[$k] = '***';
            } elseif ($k === 'nid_number' && is_string($v) && strlen($v) > 4) {
                $data[$k] = str_repeat('*', strlen($v) - 4).substr($v, -4);
            } elseif (is_array($v)) {
                $data[$k] = self::mask($v);
            }
        }

        return $data;
    }
}
