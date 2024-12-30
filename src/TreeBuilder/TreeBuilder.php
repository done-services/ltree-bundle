<?php

namespace Pvsaintpe\LTreeBundle\TreeBuilder;

use Countable;
use LogicException;

class TreeBuilder implements TreeBuilderInterface
{
    public function __construct(
        private readonly TreeBuilderInterface $arrayBuilder,
        private readonly TreeBuilderInterface $objectBuilder)
    {
    }

    public function buildTree(
        Countable|iterable $list,
        string             $pathName,
        ?string            $parentPath = null,
        ?string            $parentName = null,
        ?string            $childrenName = null
    ): object|array
    {
        $element = null;

        foreach ($list as $item) {
            $element = $item;
            break;
        }

        if (is_array($element)) {
            return $this->arrayBuilder->buildTree($list, $pathName, $parentPath, $parentName, $childrenName);
        }

        if (is_object($element)) {
            return $this->objectBuilder->buildTree($list, $pathName, $parentPath, $parentName, $childrenName);
        }

        throw new LogicException('Unable to find builder');
    }
}
