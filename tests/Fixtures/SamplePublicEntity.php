<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests\Fixtures;

class SamplePublicEntity
{
    public int $count;
    public string $name;
    protected string $secret = 'protected';
    public array $tags;
    private string $token = 'private';

    public function __construct(string $name, int $count = 1, array $tags = [])
    {
        $this->name = $name;
        $this->count = $count;
        $this->tags = $tags;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
