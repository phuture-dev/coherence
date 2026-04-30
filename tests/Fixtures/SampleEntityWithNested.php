<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests\Fixtures;

class SampleEntityWithNested
{
    public ?self $child = null;
    public string $label;

    public function __construct(string $label, ?self $child = null)
    {
        $this->label = $label;
        $this->child = $child;
    }
}
