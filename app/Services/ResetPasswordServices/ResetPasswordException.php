<?php 

namespace App\Services\ResetPasswordServices;


/**
 * Thrown for any expected request reset failure (bad credentials, missing input, expired token, duplicate password). The message is safe to show to
 * the user as-is.
 */

class ResetPasswordException extends \RuntimeException
{
}

?>