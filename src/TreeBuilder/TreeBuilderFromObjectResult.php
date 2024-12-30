<?php

namespace Pvsaintpe\LTreeBundle\TreeBuilder;

use Countable;
use Pvsaintpe\LTreeBundle\TreeBuilder\Exceptions\NotImplementException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class TreeBuilderFromObjectResult implements TreeBuilderInterface
{
    public function __construct(private readonly PropertyAccessorInterface $propertyAccessor)
    {
    }

    /**
     * @throws NotImplementException
     */
    public function buildTree(
        Countable|iterable $list,
        string             $pathName,
        ?string            $parentPath = null,
        ?string            $parentName = null,
        ?string            $childrenName = null
    ): object|array
    {
        throw new NotImplementException('Build tree from object not implement yet');
    }
}
