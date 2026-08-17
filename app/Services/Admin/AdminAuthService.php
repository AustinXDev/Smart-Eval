<?php 

namespace App\Services\Admin;

use App\Repositories\AdminRepository;
use App\Repositories\AdminTwoFactorRepository;
use App\providers\EmailProvider;
use App\Services\Admin\AdminTwoFactorEmail;
use Exception;

class AdminAuthService
{

  private const MAX_ATTEMPTS = 3;
  private const LOCK_MINUTES = 1;

  private const OTP_EXPIRATION_MINUTES = 5;

  private const MAX_OTP_ATTEMPTS = 5;
  private const OTP_ATTEMPT_WINDOW_MINUTES = 5; 

  public function __construct(
    private AdminRepository $admins,
    private AdminTwoFactorRepository $adminTwoFactor,
    private EmailProvider $mailer,
  )
  {
  }

  public function login(
    string $username,
    string $password,
    string $ip
  ): array {

    if($username === '' || $password === ''){
      throw new \Exception(
        'Username and password are required.'
      );
    }

    /**
     * Check recent failed attempts
     */
    $attemptCount = $this->admins->countRecentFailedAttempts(
      $username,
      $ip,
      self::LOCK_MINUTES
    );

    if ($attemptCount >= self::MAX_ATTEMPTS) {
        throw new \Exception(
            'Too many failed login attempts. Please try again later.'
        );
    }

    /**
     * Find admin
     */
    $admin = $this->admins->findByUsername(
      $username
    );


    if(!$admin) {

      $this->admins->recordLoginAttempt(
        $username,
        $ip,
        false
      );

      $remaining = max(
        self::MAX_ATTEMPTS - ($attemptCount + 1),
        0
      );

      throw new \Exception(
        "Incorrect username or password. {$remaining} attempt(s) remaining."
      );

    }

    /**
     * Verify password
     */
    if(!password_verify(
      $password,
      $admin->passwordHash
    )){
       $this->admins->recordLoginAttempt(
            $username,
            $ip,
            false
        );

        $remaining = max(
            self::MAX_ATTEMPTS - ($attemptCount + 1),
            0
        );

        throw new \Exception(
            "Incorrect username or password. {$remaining} attempt(s) remaining."
        );
    }

    /*
    * Credentials are correct
    */

    $this->admins->clearFailedAttempts($ip);

    /**
     * Generate OTP
     */
    $code = str_pad(
      (string) random_int(0, 999999),
      6,
      '0',
      STR_PAD_LEFT
    );

    /**
     * Hash OTP
     */
    $codeHash = hash(
      'sha256',
      $code
    );
    
    $expiresAt = (new \DateTimeImmutable(
      'now',
      new \DateTimeZone('Asia/Manila')
    ))
    ->modify(
      '+' . self::OTP_EXPIRATION_MINUTES. 'minutes'
    )
    ->format('Y-m-d H:i:s');

    /**
     * Delete previous OTP
     */
    $this->adminTwoFactor->deletePreviousCodes(
      $admin->adminId
    );

    /**
     * Save new OTP
     */
    $created = $this->adminTwoFactor->createCode(
      $admin->adminId,
      $codeHash,
      $expiresAt
    );

    if(!$created) {
      throw new \Exception(
          'Unable to generate verification code.'
      );
    }

    /**
     * Send Email
     */
    $sent = $this->mailer->send(
      $admin->email,
      'Smart-Eval Admin Verification Code',
      AdminTwoFactorEmail::build(
        $admin->username,
        $code,
        self::OTP_EXPIRATION_MINUTES
      )
    );

    if (!$sent) {
        throw new \Exception(
            'Failed to send verification code.'
        );
    }

    /*
    * Store pending authentication
    */
    $_SESSION['admin_2fa_pending'] = true;
    $_SESSION['admin_2fa_id'] =
        $admin->adminId;

    $_SESSION['admin_2fa_expires_at'] =
        time() + (
            self::OTP_EXPIRATION_MINUTES * 60
        );

    return [
        'status' => '2fa_required',
        'message' =>
            'A verification code has been sent to your registered email.'
    ];

  }

  public function verify(
    string $code,
    string $ip
  ): array {

    /**
     * Check 2fa is pending
     */
    if(
      empty($_SESSION['admin_2fa_pending']) ||
      empty($_SESSION['admin_2fa_id'])
    ) {
      throw new \Exception(
        'No verification request is pending.'
      );
    }

    /**
     * Check session expiration
     */
    if(empty($_SESSION['admin_2fa_expires_at']) ||
    time() > $_SESSION['admin_2fa_expires_at']) {

      unset(
          $_SESSION['admin_2fa_pending'],
          $_SESSION['admin_2fa_id'],
          $_SESSION['admin_2fa_expires_at']
      );

      throw new \Exception(
          'Your verification session has expired. Please log in again.'
      );

    }


    /**
     * Get admin id from pending session
     */
    $adminId = (int) $_SESSION['admin_2fa_id'];


    /**
     * Validate OTP format
     */
    $code = trim($code);

    if (!preg_match('/^\d{6}$/', $code)) {

       $this->adminTwoFactor->recordAttempt(
          $adminId,
          $ip,
          false
        );

      throw new \Exception(
          'Invalid verification code.'
      );

    }

    /**
     * Count OTP recent failures. 
     */
    $failures = $this->adminTwoFactor->countRecentFailures(
      $adminId,
      $ip,
      self::OTP_ATTEMPT_WINDOW_MINUTES
    );


    if($failures >= self::MAX_OTP_ATTEMPTS) {

      unset(
        $_SESSION['admin_2fa_pending'],
        $_SESSION['admin_2fa_id'],
        $_SESSION['admin_2fa_expires_at']
      );

      throw new \Exception(
        'Too many verification attempts. Please log in again.'
      );

    }


    /**
     * Find valid OTP
     */
    $record = $this->adminTwoFactor->findValidCode(
      $adminId
    );

    if (!$record) {

      $this->adminTwoFactor->recordAttempt(
        $adminId,
        $ip,
        false
      );

      throw new \Exception(
          'Verification code is invalid or expired.'
      );

    }

     /*
     * Hash submitted OTP.
     */
    $submittedHash = hash(
        'sha256',
        $code
    );

    $remainingAttempts = max(
        self::MAX_OTP_ATTEMPTS - ($failures + 1),
        0
    );

    /*
     * Compare hashes safely.
     */
    if (!hash_equals(
        $record['code_hash'],
        $submittedHash
    )) {

        $this->adminTwoFactor->recordAttempt(
          $adminId,
          $ip,
          false
        );

        throw new \Exception(
          "Incorrect verification code. {$remainingAttempts} attempt(s) remaining."
        );
    }

    /*
     * Mark OTP as used.
     */
    $used = $this->adminTwoFactor->markAsUsed(
        (int) $record['id']
    );

    if (!$used) {

        throw new \Exception(
            'Unable to complete verification. Please try again.'
        );

    }

    $this->adminTwoFactor->recordAttempt(
        $adminId,
        $ip,
        true
    );


    $admin = $this->admins->findById($adminId);

    if (!$admin) {
      throw new \Exception(
          'Unable to verify administrator account.'
      );
    }
    /*
     * OTP verified.
     *
     * Now convert the temporary
     * authentication session into
     * an authenticated admin session.
     */

    /*
     * Regenerate session ID
     * after authentication.
     */
    session_regenerate_id(true);

    $_SESSION['admin_authenticated'] = true;
    $_SESSION['admin_id'] = $adminId;
    $_SESSION['role'] = $admin->role;
    $_SESSION['admin_username'] = $admin->username;

    /*
     * Remove temporary 2FA session.
     */
    unset(
        $_SESSION['admin_2fa_pending'],
        $_SESSION['admin_2fa_id'],
        $_SESSION['admin_2fa_expires_at']
    );

    return [
        'status'  => 'success',
        'message' => 'Verification successful. Welcome back.',
        'redirect' => 'dashboard'
    ];
  }

}

?>