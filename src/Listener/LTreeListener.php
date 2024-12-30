<?php

namespace Pvsaintpe\LTreeBundle\Listener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use ErrorException;
use LogicException;
use Pvsaintpe\LTreeBundle\Attribute\Driver\AttributeDriverInterface;
use Pvsaintpe\LTreeBundle\Attribute\Driver\PropertyNotFoundException;
use Pvsaintpe\LTreeBundle\Repository\LTreeEntityRepositoryInterface;
use ReflectionException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class LTreeListener
{
    public function __construct(
        private readonly AttributeDriverInterface  $attributeDriver,
        private readonly PropertyAccessorInterface $propertyAccessor
    )
    {
    }

    /**
     * @throws ErrorException
     * @throws PropertyNotFoundException
     * @throws ReflectionException
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$this->attributeDriver->entityIsLTree($entity)) {
            return;
        }

        $parentPath = $this->attributeDriver->getParentProperty($entity)->getName();
        if (!$args->hasChangedField($parentPath)) {
            return;
        }

        $repo = $args->getObjectManager()->getRepository(get_class($entity));
        if (!$repo instanceof LTreeEntityRepositoryInterface) {
            throw new LogicException(sprintf('%s must implement LTreeEntityRepositoryInterface', get_class($repo)));
        }
        $repo->moveNode($entity, $args->getNewValue($parentPath));
        $this->buildPath($entity, $args->getObjectManager()->getClassMetadata(get_class($entity)));
    }

    /**
     * @throws ErrorException
     * @throws PropertyNotFoundException
     */
    protected function buildPath(object $entity, ClassMetadata $classMetadata): void
    {
        $pathName = $this->attributeDriver->getPathProperty($entity)->getName();
        $parentName = $this->attributeDriver->getParentProperty($entity)->getName();

        $parent = $this->propertyAccessor->getValue($entity, $parentName);
        $identifiers = $classMetadata->getIdentifierValues($entity);
        $idValue = reset($identifiers);

        if (!$idValue) {
            throw new LogicException('Can\'t build path property without id');
        }
        $pathValue = array();
        if ($parent) {
            $pathValue = $this->propertyAccessor->getValue($parent, $pathName);
            if (!$pathValue || empty($pathValue)) {
                $this->buildPath($parent, $classMetadata);
                $pathValue = $this->propertyAccessor->getValue($parent, $pathName);
            }
            if (!$pathValue || empty($pathValue)) {
                throw new ErrorException('Unable to build parent path property');
            }
        }
        if (!is_array($pathValue)) {
            $this->buildPath($parent, $classMetadata);
            $pathValue = $this->propertyAccessor->getValue($parent, $pathName);
        }
        // TODO: sanitize only if marked to by the path attribute
        $pathValue[] = preg_replace('/[^A-Za-z0-9]/', '', $idValue);
        $this->propertyAccessor->setValue($entity, $pathName, $pathValue);
    }

    /**
     * @throws ErrorException
     * @throws PropertyNotFoundException
     * @throws ReflectionException
     */
    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $em = $eventArgs->getObjectManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if (!$this->attributeDriver->entityIsLTree($entity)) {
                continue;
            }
            $classMetadata = $em->getClassMetadata(get_class($entity));
            $this->buildPath($entity, $classMetadata);
            $uow->recomputeSingleEntityChangeSet($classMetadata, $entity);
        }
    }
}
