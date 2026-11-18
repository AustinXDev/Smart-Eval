<?php

declare(strict_types=1);

namespace App\Controllers\ActivityLogs;

use App\Repositories\LogRepo\ActivityLogRepository;
use RuntimeException;

/**
 * Activity Logs page data (super admin only, see ApiSecurity).
 *
 * GET admin/activity_logs/list.php
 *   actor_type  admin | student | system   (the page tab)
 *   module, department, search, from, to   (optional filters)
 *   draw, start, length                    (DataTables paging)
 */
class ActivityLogController
{
    private const ACTOR_TYPES = ['admin', 'student', 'system'];

    public function __construct(private ActivityLogRepository $logs)
    {
    }

    public function list(array $query): array
    {
        $actorType = (string) ($query['actor_type'] ?? 'admin');
        if (!in_array($actorType, self::ACTOR_TYPES, true)) {
            throw new RuntimeException('Invalid log type.');
        }

        foreach (['draw', 'start', 'length'] as $key) {
            if (isset($query[$key]) && filter_var($query[$key], FILTER_VALIDATE_INT) === false) {
                throw new RuntimeException('Invalid pagination parameters.');
            }
        }

        $filters = ['actor_type' => $actorType];

        $module = trim((string) ($query['module'] ?? ''));
        if ($module !== '') {
            if (!preg_match('/^[a-z_]{1,40}$/', $module)) {
                throw new RuntimeException('Invalid module filter.');
            }
            $filters['module'] = $module;
        }

        $department = trim((string) ($query['department'] ?? ''));
        if ($department !== '') {
            if (!in_array($department, ['college', 'shs'], true)) {
                throw new RuntimeException('Invalid department filter.');
            }
            $filters['department'] = $department;
        }

        foreach (['from', 'to'] as $key) {
            $date = trim((string) ($query[$key] ?? ''));
            if ($date === '') {
                continue;
            }
            $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
            if ($parsed === false || $parsed->format('Y-m-d') !== $date) {
                throw new RuntimeException('Invalid date filter.');
            }
            $filters[$key] = $date;
        }

        $search = $query['search'] ?? '';
        $search = is_array($search) ? ($search['value'] ?? '') : $search;
        if (!is_string($search) || mb_strlen($search) > 100) {
            throw new RuntimeException('Invalid search.');
        }
        if (trim($search) !== '') {
            $filters['search'] = trim($search);
        }

        $page = $this->logs->findPage(
            max(0, (int) ($query['start'] ?? 0)),
            max(1, min(100, (int) ($query['length'] ?? 10))),
            $filters
        );

        return [
            'draw' => max(0, (int) ($query['draw'] ?? 0)),
            'recordsTotal' => $page['recordsTotal'],
            'recordsFiltered' => $page['recordsFiltered'],
            'data' => array_map([$this, 'present'], $page['data']),
            'counts' => $this->logs->countByActorType($filters),
            'modules' => $this->logs->modulesFor($actorType),
        ];
    }

    /** Row for the browser: decoded metadata, no internal fields. */
    private function present(array $row): array
    {
        $metadata = json_decode((string) ($row['metadata'] ?? ''), true);

        return [
            'log_id' => (int) $row['log_id'],
            'created_at' => $row['created_at'],
            'actor_type' => $row['actor_type'],
            'actor_id' => $row['actor_id'],
            'actor_name' => $row['actor_name'],
            'actor_role' => $row['actor_role'],
            'module' => $row['module'],
            'action' => $row['action'],
            'description' => $row['description'],
            'target_type' => $row['target_type'],
            'target_id' => $row['target_id'],
            'department' => $row['department'],
            'metadata' => is_array($metadata) ? $metadata : null,
            'ip_address' => $row['ip_address'],
        ];
    }
}
