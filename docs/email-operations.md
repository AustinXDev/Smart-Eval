# Transactional email operations

Keep PHPMailer and use a verified SMTP service with suitable daily and burst quotas. Configure existing SMTP_HOST, SMTP_PORT, SMTP_ENCRYPTION (tls or ssl), SMTP_USERNAME, SMTP_PASSWORD, SMTP_FROM and SMTP_FROM_NAME in the private .env. Do not commit credentials. Sender-domain SPF/DKIM/DMARC must match your provider instructions.

Optional settings (defaults shown):

```dotenv
SMTP_TIMEOUT_SECONDS=15
MAIL_WORKER_MAX_JOBS=100
MAIL_WORKER_MAX_SECONDS=180
MAIL_WORKER_DELAY_MS=1000
```

No schema change is required. Deploy EmailProvider, NotificationRepository, NotificationWorkerService and cron/process-notification-queue.php together because claim-token method signatures changed. Also deploy the updated student OTP and password-recovery services.

Start with one scheduled CLI worker every five minutes, for example:

```text
*/5 * * * * /absolute/path/to/php /absolute/path/to/Smart-Eval/cron/process-notification-queue.php
```

Use the hosting provider's actual PHP binary and application path. The 180-second deadline is soft: the last SMTP operation may finish later. CLI script runtime remains 240 seconds; stale leases recover after ten minutes. Claiming one job at a time prevents unstarted jobs from sitting in processing. Defaults pace reminder sends at approximately one second between attempts; SMTP time adds to that. Multiple workers multiply throughput and send rate, so this is not a global provider quota limiter. Keep capacity reserved for immediate OTP/reset emails.

The provider reuses its SMTP connection only for the CLI worker and clears per-message data after each send. Authentication emails stay synchronous, with explicit failure handling. Queue retries remain bounded to three attempts, delayed one and five minutes. All failures currently use that bounded policy; permanent/transient classification and bounce webhooks are follow-up integrations specific to the chosen SMTP provider.

The unique queue key allows one reminder per student/period/type. Worker completion checks its claim token. SMTP acceptance is not inbox delivery. Crash after SMTP acceptance but before recording success can still produce a duplicate; this is at-least-once delivery, not exactly once.

Verify on staging: SMTP TLS/certificate validation, sender authentication, controlled mailbox delivery, queue retry/recovery, actual InnoDB concurrency, provider quotas and backlog latency. Existing indexes for worker, claim and recovery support the queries. No real mail or production database operations are performed by tests/email/run.php.
