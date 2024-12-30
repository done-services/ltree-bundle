<?php

namespace Pvsaintpe\LTreeBundle\Attribute\Driver;

use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use ReflectionProperty;

class AttributeDriver implements AttributeDriverInterface
{
    /**
     * @throws ReflectionException
     */
    public function entityIsLTree(object $object): bool
    {
        return $this->hasClassAttribute($object, self::ENTITY_ATTRIBUTE);

    }

    /**
     * @param object|class-string $object
     * @throws ReflectionException
     */
    protected function hasClassAttribute(object|string $object, string $attributeName): bool
    {
        $reflectionClass = new ReflectionClass($object);

        return !empty($reflectionClass->getAttributes($attributeName));
    }

    /**
     * @throws ReflectionException
     */
    public function classIsLTree(string $className): bool
    {

        return $this->hasClassAttribute($className, self::ENTITY_ATTRIBUTE);
    }

    /**
     * @throws PropertyNotFoundException
     */
    public function getChildrenProperty(object $object): ReflectionProperty
    {
        return $this->findAttributeProperty($object, self::CHILDS_ATTRIBUTE);
    }

    /**
     * @throws PropertyNotFoundException
     */
    protected function findAttributeProperty(object $object, string $attributeName): ReflectionProperty
    {
        $reflectionObject = new ReflectionObject($object);
        foreach ($reflectionObject->getProperties() as $property) {
            if (!empty($property->getAttributes($attributeName))) {
                return $property;
            }
        }

        throw new PropertyNotFoundException($object, $attributeName);
    }

    /**
     * @throws PropertyNotFoundException
     */
    public function getParentProperty(object $object): ReflectionProperty
    {
        return $this->findAttributeProperty($object, self::PARENT_ATTRIBUTE);
    }

    /**
     * @throws PropertyNotFoundException
     */
    public function getPathProperty(object $object): ReflectionProperty
    {
        return $this->findAttributeProperty($object, self::PATH_ATTRIBUTE);
    }

    /**
     * @throws PropertyNotFoundException
     */
    public function getIdProperty(object $object): ReflectionProperty
    {
        return $this->findAttributeProperty($object, self::ID_ATTRIBUTE);
    }
}
