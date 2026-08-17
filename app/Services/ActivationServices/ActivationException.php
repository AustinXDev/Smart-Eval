<?php 
namespace App\Services\ActivationServices;

/**
 * Thrown for any expected login failure (bad credentials, missing input, activated_account). The message is safe to show to
 * the user as-is.
 */

class ActivationException extends \RuntimeException 
{
}

?>