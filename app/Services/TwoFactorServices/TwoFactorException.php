<?php 
namespace App\Services\TwoFactorServices;

use Exception;

/**
 * Thrown for any expected two factor authentication failure (bad credentials, missing input, not match otp)
 */

class TwoFactorException extends Exception 
{

}


?>