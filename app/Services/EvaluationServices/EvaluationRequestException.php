<?php
namespace App\Services\EvaluationServices;

final class EvaluationRequestException extends \RuntimeException
{
    public function __construct(string $message, public readonly int $status = 400)
    {
        parent::__construct($message);
    }
}
