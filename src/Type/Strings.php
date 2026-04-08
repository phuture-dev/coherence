<?php

declare(strict_types=1);

namespace Phuture\Coherence\Type;

use Phuture\Coherence\Interface\Stringable;
use Phuture\Coherence\Support\FluentClass;

class Strings extends FluentClass implements Stringable, \Stringable
{
    public function __toString()
    {
        return $this->toString();
    }

    public function toString() : string
    {
        return (string) $this->data;
    }
}
