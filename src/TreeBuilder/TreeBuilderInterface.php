<?php

namespace Pvsaintpe\LTreeBundle\TreeBuilder;

use Countable;

interface TreeBuilderInterface
{
    public function buildTree(
        Countable|iterable $list,
        string             $pathName,
        ?string            $parentPath = null,
        ?string            $parentName = null,
        ?string            $childrenName = null
    ): array|object;
}
