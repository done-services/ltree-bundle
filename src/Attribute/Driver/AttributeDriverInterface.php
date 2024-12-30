<?php

namespace Pvsaintpe\LTreeBundle\Attribute\Driver;

use Doctrine\ORM\Mapping\Id;
use Pvsaintpe\LTreeBundle\Attribute\LTreeChilds;
use Pvsaintpe\LTreeBundle\Attribute\LTreeEntity;
use Pvsaintpe\LTreeBundle\Attribute\LTreeParent;
use Pvsaintpe\LTreeBundle\Attribute\LTreePath;
use ReflectionException;
use ReflectionProperty;

interface AttributeDriverInterface
{
    public const ENTITY_ATTRIBUTE = LTreeEntity::class;

    public const CHILDS_ATTRIBUTE = LTreeChilds::class;

    public const PARENT_ATTRIBUTE = LTreeParent::class;

    public const PATH_ATTRIBUTE = LTreePath::class;

    public const ID_ATTRIBUTE = Id::class;

    /**
     * Check that ltree entity attribute is in the $object
     *
     * @throws ReflectionException
     */
    public function entityIsLTree(object $object): bool;

    /**
     * Check that ltree entity attribute is in the $className
     *
     * @throws ReflectionException
     */
    public function classIsLTree(string $className): bool;

    /**
     * Return children property reflection object
     *
     * @throws PropertyNotFoundException
     */
    public function getChildrenProperty(object $object): ReflectionProperty;

    /**
     * Return parent property reflection object
     *
     * @throws PropertyNotFoundException
     */
    public function getParentProperty(object $object): ReflectionProperty;

    /**
     * Return path property reflection object
     *
     * @throws PropertyNotFoundException
     */
    public function getPathProperty(object $object): ReflectionProperty;


    /**
     * Return id property reflection object
     *
     * @throws PropertyNotFoundException
     */
    public function getIdProperty(object $object): ReflectionProperty;
}
