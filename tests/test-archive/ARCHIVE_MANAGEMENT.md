# Archive Management

Route: `archive-management`. Access: the existing `super_admin` management policy.
No worker schedule, encryption format, or schema is changed.

## Available

- Archive-only paginated queries, filters, snapshot/submission counts, and details.
- Encrypted Drive checksum verification through ArchiveBackupService.
- Authenticated, CSRF-protected .json.enc uploads, limited to 2 MiB (below the existing 3 MiB request cap).
- Existing AES-GCM decryption; version, counts, schema columns/value shape, unique keys, and reference checks.
- Duplicate-period detection and read-only preview; temporary encrypted/plaintext files removed in finally.
- Read-only existing record-level recovery entries. Admin names are looked up through the live repository separately.

## Deliberately unavailable

- Delete: no approved retention policy or reviewed deletion/audit service.
- Restore: DatabaseRestoreService remains restricted to dbsmarteval_restore_test. No restore endpoint exists.
- Operation outcomes, restored counts, backup filenames/sizes, and recovery-attempt KPI: not stored in the current schema.
- Manual verification does not update job status/timestamps and does not prove decryptability or restorability.
- Preview is not a transactional restore dry run; database collation collisions, global primary-key conflicts, and race conditions must be rechecked by a future restore service.

## Configuration

Use the existing DB_NAME, ARCHIVE_DB_NAME, APP_URL, APP_API, APP_API_ROOT (when applicable),
BACKUP_ENCRYPTION_KEY, Google OAuth configuration, and private refresh token.
The OS temporary directory must be writable and outside DOCUMENT_ROOT.
PHP upload_max_filesize/post_max_size must allow the 2 MiB file plus multipart overhead.
Preview does not need Google OAuth; verification does.
No production database migrations are required for this release.

## Verification

Run `php tests/test-archive/test-management.php` for synthetic validation/security-policy/encryption checks.
It uses only randomized temporary files and removes them; it never bootstraps or writes a database.
Existing archive/cleanup/restore test scripts are not safe general-purpose test suites; do not run them against production.

Manually verify at desktop/tablet/mobile sizes:
- anonymous/dean access denied; super-admin entry visible;
- filters reset pagination; asynchronous errors are visible and Refresh retries;
- tabs support arrows/Home/End; details support Escape and focus return;
- verified backup checksum success/failure; no changed worker state;
- corrupted/oversized/wrong-key uploads rejected; repeat upload cannot retain an old preview;
- existing period flagged; restore/delete remain unavailable.

## Proposed future audit migration (not applied)

A separate operation-level table is preferable to changing legacy record-level logs:
operation ID, nullable period ID, authenticated admin ID, department/period metadata snapshot,
backup filename and checksum, started/finished timestamps, result, restored counts JSON,
and sanitized failure reason. Keep it independent of deletable archive records.
Review this schema and its retention/access policy before implementing a migration.
Write the attempt before the restore transaction and persist its outcome after commit/rollback.
A future recovery transaction must reject duplicate periods and global keys, validate references
against actual constraints, insert parents before children, compare database counts, and coordinate
with the background worker. Never disable foreign keys or restore evaluation data to the live database.

## Implementation verification results

- PHP syntax checks passed for changed/new PHP files.
- JavaScript module syntax checks and git diff whitespace checks passed.
- 45 synthetic backup validation, encryption, identifier, and endpoint-policy checks passed.
- 11 isolated authorization, CSRF, method, and upload-size checks passed with fake PDO.
- Read-only local database transactions verified overview, pagination, department/search filters,
  logs, details, duplicate-period detection, and validation of an existing archive payload.
- Headless Chrome at 1440, 768, and 390 pixels: no page-wide horizontal overflow;
  tables scroll within their container; tabs, filtering, details/Escape/focus return,
  and the shared mobile drawer worked with no JavaScript exceptions.
- Browser uploads: damaged encrypted file rejected (422); valid synthetic backup previewed;
  existing period flagged; restoration stayed disabled.
- Browser harness used a synthetic administrator and a read-only archive transaction.
  Synthetic backup fixtures were stored outside the repository and removed after checks.
- No production deployment, database writes, archive deletion, or restoration was performed.
- Live Google Drive verification was not invoked; its existing checksum service is reused.
  Confirm production OAuth/token access before relying on that action.
- Archive Issues counts missing jobs, failed backup jobs, and cleanup needing reconciliation.

## Changed and new files

Modified:
- .gitignore: narrow exceptions for these focused tests and notes.
- public/index.php and app/config/nav.php: super-admin page route/navigation.
- app/middleware/ApiSecurity.php: explicit endpoint policies and multipart preview registration.
- app/Core/Container.php: archive controller/service wiring.
- app/Repositories/ArchiveRepo/ArchiveReadRepository.php: management reads and schema inspection.
- app/Services/BackupServices/DatabaseExportService.php: expose the existing table allowlist;
  export format and behavior are unchanged.
- api/admin/archive/list.php and details.php: implement existing empty placeholders.

Added:
- views/admin/archive_management.view.php
- views/partials/modals/archive_modal/details.php
- public/assets/js/admin/archive_management/index.js, table.js, api/api.js, and components/*.js
- app/Controllers/Archive/ArchiveManagementController.php
- app/Services/ArchiveServices/ArchiveManagementService.php
- app/Services/BackupServices/BackupPreviewService.php
- api/admin/archive/overview.php, logs.php, verify.php, and preview.php
- tests/test-archive/test-management.php and test-management-security.php
- this implementation note

The sidebar/header components, HTTP/CSRF helper, DataTables helper, modal helper, logout,
and session timeout components are reused. The archive worker, restore guard, schema,
encryption implementation, and Google Drive integration are unchanged.


## Frontend module map

The structure follows the existing student/teacher admin modules:
- index.js: initialization and the shared logout binding.
- api/api.js: named requests using the shared HTTP/CSRF helper.
- table.js: archive/log table configuration, shared filter names, and lazy table instances.
- components/archiveRender.js: escaped display markup and value formatting.
- components/overview.js: KPI/filter option loading and stale-response protection.
- components/navigation.js: tabs, keyboard navigation, filter debounce, and Refresh.
- components/detailsModal.js: detail loading, focus handling, and dismissal.
- components/backupActions.js: verification feedback and row action menus.
- components/backupForm.js: upload validation and recovery preview.
- components/ui.js: small shared DOM and notice helpers.

Components expose explicit init functions. The page still loads only index.js.
All backend routes, request payloads, and disabled recovery/deletion safeguards are preserved.
