<?php 

namespace App\Services\LoginServices;

/**
 * Thrown for any expected login failure (bad credentials, locked out,
 * inactive account, missing input). The message is safe to show to
 * the user as-is.
 */

class AuthException extends \RuntimeException
{
}

?>