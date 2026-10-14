# Email architecture rollout

Step 1 (implemented): existing EmailProvider facade delegates to EmailTransportInterface. SmtpTransport holds current PHPMailer configuration and connection reuse. send() remains boolean for all existing services. deliver() exposes DeliveryResult internally: accepted, invalid_recipient, rejected (explicit SMTP 4xx/5xx), uncertain (no definitive rejection). No fallback, quota logic, or schema change is enabled. Generic 4xx/5xx does not establish quota exhaustion; provider-specific classification is required before switching.

Deploy app/providers/EmailProvider.php together with all three app/Services/Email files. Case-sensitive paths must match namespaces. Run php tests/email/run.php and php tests/security/run.php.

Step 2: choose one fallback service, confirm account quotas/reset windows and approved sender domain, then add named transport configuration and a dispatcher. Preserve existing SMTP_* configuration. Do not change providers on invalid recipient, authentication misconfiguration, or uncertain acceptance. Do not resend with a new OTP just to change provider.

Step 3: add a reviewed migration and atomic database quota reservations shared by immediate sends and workers. Provider-side usage can differ from app counters, especially with external senders or uncertain outcomes; reserve headroom and follow authoritative provider quota responses.

Step 4: enable provider-specific confirmed-quota fallback, cooldowns, operator monitoring and staging tests. Use at least one primary and one approved fallback. No credentials or SMTP error bodies should enter public API responses.

Step 5: assess production bursts and only add an urgent queue if synchronous authentication mail becomes a measured bottleneck. Existing reminder queue remains unchanged.
