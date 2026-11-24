<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Repositories\AdminRepo\AdminRepository;
use App\Services\Admin\AccountPinService;
use App\Services\Admin\AdminAccountService;
use RuntimeException;

/**
 * Account Management endpoints (api/admin/accounts/*). The API layer has already
 * checked that the caller is an active super admin; every action here also
 * needs the Account Management PIN to be unlocked.
 */
final class AdminAccountController
{
    public function __construct(
        private AdminRepository $admins,
        private AccountPinService $pin,
        private AdminAccountService $accounts
    ) {
    }

    public function list(): array
    {
        $me = $this->currentAdminId();
        $this->pin->requireUnlocked($me);

        return $this->accounts->list($me);
    }

    public function create(array $input, string $loginUrl): array
    {
        $this->pin->requireUnlocked($this->currentAdminId());

        return $this->accounts->create($input, $loginUrl);
    }

    public function update(array $input): array
    {
        $me = $this->currentAdminId();
        $this->pin->requireUnlocked($me);

        return $this->accounts->update((int) ($input['admin_id'] ?? 0), $input, $me);
    }

    public function delete(array $input): array
    {
        $me = $this->currentAdminId();
        $this->pin->requireUnlocked($me);

        return $this->accounts->delete((int) ($input['admin_id'] ?? 0), $me);
    }

    /** action: verify | set | lock */
    public function pin(array $input): array
    {
        $admin = $this->admins->findById($this->currentAdminId());
        if (!$admin) {
            throw new RuntimeException('Administrator account not found.');
        }

        $action = (string) ($input['action'] ?? '');

        match ($action) {
            'verify' => $this->pin->verify($admin, (string) ($input['pin'] ?? '')),
            'set'    => $this->pin->setPin(
                $admin,
                (string) ($input['account_password'] ?? ''),
                (string) ($input['pin'] ?? ''),
                (string) ($input['pin_confirm'] ?? '')
            ),
            'lock'   => $this->pin->lock(),
            default  => throw new RuntimeException('Unknown PIN action.'),
        };

        return ['unlocked' => $action !== 'lock'];
    }

    private function currentAdminId(): int
    {
        return (int) ($_SESSION['admin_id'] ?? 0);
    }

    /* ------------------------------------------------------------------
       Shared by the api/admin/accounts/*.php endpoints
    ------------------------------------------------------------------ */

    public static function make(\PDO $pdo, bool $withMailer = false): self
    {
        $admins = new AdminRepository($pdo);

        return new self(
            $admins,
            new AccountPinService($admins),
            new AdminAccountService($admins, $withMailer ? new \App\providers\EmailProvider() : null)
        );
    }

    /** JSON body of the request (ApiSecurity has already checked it is a JSON object). */
    public static function input(): array
    {
        $data = json_decode((string) file_get_contents('php://input'), true);

        return is_array($data) ? $data : [];
    }

    /**
     * Runs an action and prints the JSON response:
     * 200 success · 422 field errors · 423 PIN required · 400 rule/validation message · 500 other.
     */
    public static function respond(callable $action, string $successMessage = ''): void
    {
        try {
            $data = $action();
            echo json_encode([
                'status'  => 'success',
                'message' => $successMessage,
                'data'    => $data,
            ]);
        } catch (\App\Services\Admin\AdminAccountValidationException $e) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'errors' => $e->errors()]);
        } catch (\App\Services\Admin\AccountPinRequiredException $e) {
            http_response_code(423);
            echo json_encode(['status' => 'error', 'code' => 'pin_required', 'message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code($e instanceof RuntimeException ? 400 : 500);
            echo json_encode(['status' => 'error', 'message' => \App\middleware\ApiSecurity::publicMessage($e)]);
        }
    }
}
