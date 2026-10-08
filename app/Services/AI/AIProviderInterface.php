<?php

namespace App\Services\AI;

interface AIProviderInterface
{
    public function generate(string $prompt): string;

}
