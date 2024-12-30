<?php

namespace Pvsaintpe\LTreeBundle\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectRepository;
use Pvsaintpe\LTreeBundle\Attribute\Driver\AttributeDriverInterface;
use Pvsaintpe\LTreeBundle\TreeBuilder\TreeBuilderInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

interface LTreeEntityRepositoryInterface extends ObjectRepository
{
    public function setTreeBuilder(TreeBuilderInterface $treeBuilder): self;

    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor): self;

    public function setAttributeDriver(AttributeDriverInterface $attributeDriver): self;

    public function getAllParentQueryBuilder(object $entity): QueryBuilder;

    /**
     * @param object $entity object entity
     * @param int $hydrate Doctrine processing mode to be used during hydration process.
     *                               One of the Query::HYDRATE_* constants.
     * @return array|mixed with parents for $entity. The root node is last
     */
    public function getAllParent(object $entity, int $hydrate = AbstractQuery::HYDRATE_OBJECT): mixed;

    /**
     * @param int $hydrate Doctrine processing mode to be used during hydration process.
     *                               One of the Query::HYDRATE_* constants.
     * @return array|mixed with parents for $entity. The root node is last
     */
    public function getAllLTree(int $hydrate = AbstractQuery::HYDRATE_OBJECT): mixed;

    public function getAllChildrenQueryBuilder(object $entity): QueryBuilder;

    public function getInverseLTreeBuilder(object $entity = null): QueryBuilder;

    /**
     * @param object $entity object entity
     * @param bool $treeMode This flag set how result will be presented
     * @param int $hydrate Doctrine processing mode to be used during hydration process.
     *                               One of the Query::HYDRATE_* constants.
     * @return array|mixed If $treeMode is true, result will be grouped to tree.
     *                  If hydrate is object, children placed in childs property.
     *                  If hydrate is array, children placed in __childs key.
     *               If $treeMode is false, result will be in one level array
     */
    public function getAllChildren(object $entity, bool $treeMode = false, int $hydrate = AbstractQuery::HYDRATE_OBJECT): mixed;

    /**
     * @param object $entity
     * @param object|array|null $to object or path array
     */
    public function moveNode(object $entity, object|array $to = null);
}
