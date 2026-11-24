<?php

namespace App\Models;

class Admin
{
  /** Stored role values (admins.role enum). */
  public const ROLE_SUPER_ADMIN = 'super admin';
  public const ROLE_DEPARTMENT_HEAD = 'department head';

  public const STATUS_ACTIVE = 'active';
  public const STATUS_INACTIVE = 'inactive';

  public function __construct(
    public int $adminId,
    public string $username,
    public string $email,
    public string $role,
    public string $passwordHash,
    public string $fullName = '',
    public string $status = self::STATUS_ACTIVE,
    public ?string $createdAt = null,
    public ?string $updatedAt = null,
    public ?string $pinHash = null,
    public int $pinFailedAttempts = 0,
    public ?string $pinLockedUntil = null
  ) {
  }

  /**
   * Columns added by scripts/sql/2026_11_24_admin_account_management.sql have
   * defaults here, so the model also works before that migration is applied.
   */
  public static function fromArray(array $data): self
  {
    return new self(
      (int) $data['admin_id'],
      (string) $data['username'],
      (string) $data['email'],
      (string) ($data['role'] ?? ''),
      (string) $data['password_hash'],
      (string) ($data['full_name'] ?? ''),
      (string) ($data['status'] ?? self::STATUS_ACTIVE),
      $data['created_at'] ?? null,
      $data['updated_at'] ?? null,
      $data['access_pin_hash'] ?? null,
      (int) ($data['pin_failed_attempts'] ?? 0),
      $data['pin_locked_until'] ?? null
    );
  }

  /**
   * Role key used by the navigation (app/config/nav.php) and permission checks:
   * 'super admin' -> 'super_admin', 'department head' -> 'dean'.
   * (Before this helper, 'department head' became 'department_head', which
   * matched neither the menu nor the API checks.)
   */
  public static function roleKey(string $role): string
  {
    $key = strtolower(str_replace(' ', '_', trim($role)));

    return $key === 'department_head' ? 'dean' : $key;
  }

  public function roleKeyValue(): string
  {
    return self::roleKey($this->role);
  }

  public function isActive(): bool
  {
    return $this->status === self::STATUS_ACTIVE;
  }

  /** Safe fields for the Account Management page (no password or PIN data). */
  public function toPublicArray(): array
  {
    return [
      'admin_id'   => $this->adminId,
      'username'   => $this->username,
      'full_name'  => $this->fullName,
      'email'      => $this->email,
      'role'       => $this->role,
      'role_label' => self::roleLabel($this->role),
      'status'     => $this->status,
      'created_at' => $this->createdAt,
      'updated_at' => $this->updatedAt,
    ];
  }

  public static function roleLabel(string $role): string
  {
    return match ($role) {
      self::ROLE_SUPER_ADMIN => 'Super Admin',
      self::ROLE_DEPARTMENT_HEAD => 'Department Head',
      default => ucwords($role),
    };
  }
}
