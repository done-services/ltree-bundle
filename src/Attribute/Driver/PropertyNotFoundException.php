<?php

namespace Pvsaintpe\LTreeBundle\Attribute\Driver;

use Exception;

class PropertyNotFoundException extends Exception
{
    public const INIT_ERROR = 'Class %s does not exist for property with attribute %s';
    private string $className;
    private string $attributeClassName;

    public function __construct(object $object, string $attributeClassName)
    {
        $this->className = get_class($object);
        $this->attributeClassName = $attributeClassName;

        parent::__construct(sprintf(static::INIT_ERROR, $this->getClassName(), $this->getAttributeClassName()));
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getAttributeClassName(): string
    {
        return $this->attributeClassName;
    }
}
