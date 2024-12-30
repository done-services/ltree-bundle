<?php

namespace Pvsaintpe\LTreeBundle\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class LTreePath
{
    public function __construct(public bool $sanitize = false)
    {
    }
}
