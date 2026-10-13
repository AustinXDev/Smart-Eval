# Security regression checks

Run from the project root:

```powershell
php tests/security/run.php
python -B tests/security/request_body.py
node tests/security/rendering.mjs
```

The PHP tests use in-memory PDO/repository doubles. The HTTP tests start and stop a disposable server bound to 127.0.0.1, serving test fixtures only. Neither loads app/init.php, connects to MySQL, sends email, or calls external services.

Before deploying, use a disposable database to verify actual InnoDB row locking, token transactions, teacher selection, login/MFA/session cookies, and report downloads. Run the dependency audit separately. Confirm Apache honors the root .htaccess and that production uses HTTPS and a restricted database account.

API policy follows the current navigation: Super Admin manages records; Dean reads dashboard/analytics/PDF routes; authenticated students use evaluation and their own department's teacher list. Unknown API entry points fail closed. New endpoints must be added to ApiSecurity::policy().

Sessions have 30-minute request inactivity and 8-hour absolute limits. Existing sessions require a fresh login. APP_ENV=development enables the hosting PHP error-display setting; production is the default. Password resets now send recovery links rather than setting a shared password.

API bootstrap supports api/ within the application root and sibling public_html/api/. For a different layout, set APP_API_ROOT in the private .env to the absolute filesystem path of the deployed API directory (not its URL). An explicit value overrides automatic candidates.
