# Email reliability deployment

This implementation preserves immediate authentication email and the reminder queue.
Faculty report delivery is not enabled: verify teacher_notification_queue schema and its report contract first.

## Deployment order

1. Back up the database and pause reminder cron. Wait for active workers to finish.
2. Apply database/migrations/20261014_email_reliability.sql once using your database administration tool.
   This is additive DDL but MySQL DDL implicitly commits. It quarantines existing processing jobs.
3. Deploy the PHP changes together. The new worker requires delivery_state.
4. Run php tests/email/run.php. These tests use fake SMTP and PDO, not app/init.php.
5. Configure quota values for the verified allowance of the actual SMTP account.
6. Enable cron with a controlled test recipient first; inspect counts and failed/uncertain jobs.

## Environment options

MAIL_QUOTA_ENABLED=false
MAIL_DAILY_LIMIT=400
MAIL_MINUTE_LIMIT=10
MAIL_AUTH_RESERVE=100
MAIL_BULK_INTERVAL_SECONDS=2
MAIL_SMTP_TIMEOUT=10
MAIL_SMTP_TIMELIMIT=10
MAIL_WORKER_MAX_JOBS=10
MAIL_WORKER_SECONDS=120
MAIL_WORKER_SAFETY_SECONDS=60

Quota is off by default for staged deployment. Set MAIL_QUOTA_ENABLED=true after migration
and account-specific limits are verified. The sample limits are operational examples, not a
statement of Gmail allowance. All processes using the same SMTP host/account must use this
central provider and the same database. Sending from other applications is not accounted for.

Reservations use rolling 24-hour and one-minute windows. Failed and uncertain attempts count
conservatively. Authentication bypasses bulk pacing and bulk reserve, not the total provider
limit or cooldown. A provider outage cannot be solved by reserved capacity.
Bulk pacing waits up to five seconds outside a transaction, then retries the reservation once.
Longer waits defer the job and end the run. Throughput still depends on cron frequency and limits.

PHPMailer command timeouts bound network waits; the worker checks elapsed time before claiming.
A currently executing SMTP transaction can finish beyond its safety margin. Do not describe
this as a strict process-kill deadline. Keep the 10-minute queue lease comfortably above the
maximum observed operation duration.

## Outcomes

sent + delivery_state=accepted means SMTP acceptance, not confirmed inbox delivery.
failed + delivery_state=uncertain requires manual review. Never blindly reset it to pending.
Accepted messages whose DB update failed remain sending and are quarantined by stale recovery.
This intentionally favors avoiding duplicate sends over automatic recovery.
SMTP keeps its healthy connection only in CLI batch mode, clears message state between sends,
and disconnects at worker completion. HTTP requests do not reuse each other's objects.

Only outcome categories and job IDs are logged. Never enable SMTP debug in production.
Cron summaries are counts. Add duration/backlog monitoring and log rotation appropriate to hosting.
Periodically delete email_provider_reservations older than two days; preserve the active 24-hour
window. No recipient, OTP, message body, or credentials are stored in quota reservations.

## Verification still required in staging

Use disposable MySQL/MariaDB tables to test concurrent claims/reservations, stale-token updates,
lease recovery, and migration compatibility. Fake-PDO tests do not prove InnoDB locking.
Use a sandbox SMTP server to measure connection reuse, cleanup, timeouts and accepted-then-crash
recovery. Do not send bulk tests through production Gmail or to students.
Compare 100/1,000/10,000 fake jobs, DB query timings and auth latency before raising limits.

## Rollback

Stop cron. Keep the additive schema and quota records. Review/quarantine sending and uncertain
jobs before rolling back worker code. The old worker can blindly retry a previously accepted
message, so do not resume it until these jobs are excluded. MAIL_QUOTA_ENABLED=false disables
quota enforcement but does not reverse queue safety changes. Restore PHP together, not piecemeal.
