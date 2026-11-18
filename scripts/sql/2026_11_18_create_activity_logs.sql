-- Activity logs: who did what, to which record, and when.
--
-- Written by App\Services\LoggingServices\ActivityLogService (via the API hook
-- in app/api_bootstrap.php and the period scheduler).
--
-- Privacy rules (evaluation anonymity):
--   * Student evaluation events record only "submitted X of Y" — never which
--     teacher was evaluated, scores, answers or comments.
--   * Passwords, tokens and verification codes are never stored.
--
-- No foreign keys on purpose: log rows must survive when the admin, student
-- or record they mention is deleted. actor_name keeps the name at that time.

CREATE TABLE IF NOT EXISTS activity_logs (
    log_id       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    -- Who
    actor_type   ENUM('admin','student','system') NOT NULL,
    actor_id     VARCHAR(20)  NULL,
    actor_name   VARCHAR(100) NULL,
    actor_role   VARCHAR(30)  NULL,

    -- What
    module       VARCHAR(40)  NOT NULL,
    action       VARCHAR(40)  NOT NULL,
    description  VARCHAR(255) NOT NULL,

    -- On which record
    target_type  VARCHAR(40)  NULL,
    target_id    VARCHAR(40)  NULL,
    department   ENUM('college','shs') NULL,

    -- Context
    metadata     LONGTEXT     NULL,
    ip_address   VARCHAR(45)  NULL,
    user_agent   VARCHAR(255) NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (log_id),
    KEY idx_logs_created (created_at),
    KEY idx_logs_actor   (actor_type, actor_id),
    KEY idx_logs_module  (module, action),
    KEY idx_logs_target  (target_type, target_id),
    KEY idx_logs_dept    (department, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
