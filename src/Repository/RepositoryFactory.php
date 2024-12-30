<?php

namespace Pvsaintpe\LTreeBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Repository\RepositoryFactory as RepositoryFactoryBase;
use Pvsaintpe\LTreeBundle\Attribute\Driver\AttributeDriverInterface;
use Pvsaintpe\LTreeBundle\TreeBuilder\TreeBuilderInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class RepositoryFactory implements RepositoryFactoryBase
{
    public function __construct(
        private readonly RepositoryFactoryBase     $inner,
        private readonly AttributeDriverInterface  $attributeDriver,
        private readonly PropertyAccessorInterface $propertyAccessor,
        private readonly TreeBuilderInterface      $treeBuilder
    )
    {
    }

    public function getRepository(EntityManagerInterface $entityManager, $entityName): EntityRepository
    {
        $repository = $this->inner->getRepository($entityManager, $entityName);

//        var_dump($entityName, get_class($repository));
        if ($repository instanceof LTreeEntityRepositoryInterface) {
            $repository->setAttributeDriver($this->attributeDriver);
            $repository->setPropertyAccessor($this->propertyAccessor);
            $repository->setTreeBuilder($this->treeBuilder);
        }

        return $repository;
    }
}
