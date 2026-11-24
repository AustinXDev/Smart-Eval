<?php

declare(strict_types=1);

namespace App\Services\Admin;

/** The Account Management PIN has not been entered (or the unlock expired). */
final class AccountPinRequiredException extends \RuntimeException
{
}
