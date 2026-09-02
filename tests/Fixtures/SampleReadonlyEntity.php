<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests\Fixtures;

class SampleReadonlyEntity
{
    public int $count;
    public readonly string $name;

    public function __construct(string $name, int $count = 1)
    {
        $this->name = $name;
        $this->count = $count;
    }
}
