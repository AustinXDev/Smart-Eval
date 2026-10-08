<?php 

namespace App\Services\RegistrationService;

/**
 * Thrown for any expected registration failure (bad credentials, no activation token, expires token). The message is safe to show to
 * the user as-is.
 */

class RegistrationException extends \RuntimeException{

}

?>