<?php 

namespace App\Services\ForgotPasswordServices;

/**
 * Thrown for any expected login failure (bad credentials, missing input, sending reset). The message is safe to show to
 * the user as-is.
 */

class ForgotPasswordException extends \RuntimeException
{

}

?>