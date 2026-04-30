<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests\Fixtures;

class SampleProtectedEntity
{
    protected string $email;
    public string $role = 'user';

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
