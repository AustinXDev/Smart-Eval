<?php 

namespace App\Session;

/**
 * Wraps PHP's native session handling so nothing in the app touches
 * $_SESSION directly. Centralizing this makes it easy to enforce
 * secure cookie params, regenerate IDs on privilege changes, and
 * keep session structure consistent.
 */

class SessionManager
{

    public function __construct(array $cookieOptions = [])
    {
        if (session_status() === PHP_SESSION_NONE) {

            $defaults = [
                'httponly' => true,
                'samesite' => 'Lax',
                'secure'   => $this->isHttps(),
                'path'     => '/',
            ];

            session_set_cookie_params(
                array_merge($defaults, $cookieOptions)
            );

            session_start();
        }
    }

  private function isHttps(): bool
  {
      return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
          || ($_SERVER['SERVER_PORT'] ?? null) == 443;
  }

  /**
   * Regenerates the session ID, keeping current data.
   * MUST be called on login (and ideally logout) to prevent
   * session fixation attacks.
   */

  public function regenerate(): void
  {
      session_regenerate_id(true);
  }

  public function set(string $key, $value): void
  {
      $_SESSION[$key] = $value;
  }

  public function get(string $key, $default = null)
  {
      return $_SESSION[$key] ?? $default;
  }

  public function has(string $key): bool
  {
      return isset($_SESSION[$key]);
  }

  public function remove(string $key): void
  {
      unset($_SESSION[$key]);
  }

  /**
  * Fully destroys the session (use on logout).
  */
  public function destroy(): void
  {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
  }
}

?>