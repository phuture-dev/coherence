<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests\Fixtures;

class SampleEntity
{
    private int $age;
    private string $name;

    public function __construct(string $name, int $age)
    {
        $this->name = $name;
        $this->age = $age;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
