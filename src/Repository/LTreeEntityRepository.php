<?php

namespace Pvsaintpe\LTreeBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\{AbstractQuery, Query, QueryBuilder};
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use LogicException;
use Pvsaintpe\LTreeBundle\Attribute\Driver\{AttributeDriverInterface, PropertyNotFoundException};
use Pvsaintpe\LTreeBundle\DqlFunction\{LTreeConcatFunction,
    LTreeNlevelFunction,
    LTreeOperatorFunction,
    LTreeSubpathFunction};
use Pvsaintpe\LTreeBundle\TreeBuilder\TreeBuilderInterface;
use Pvsaintpe\LTreeBundle\Types\LTreeType;
use ReflectionException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class LTreeEntityRepository extends ServiceEntityRepository implements LTreeEntityRepositoryInterface
{
    public const LTREE_ALIAS = 'ltree_entity';

    private AttributeDriverInterface $attributeDriver;
    private PropertyAccessorInterface $propertyAccessor;
    private TreeBuilderInterface $treeBuilder;

    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function getTreeBuilder(): TreeBuilderInterface
    {
        if (null === $this->treeBuilder) {
            throw new LogicException('Repository must inject property accessor service itself');
        }

        return $this->treeBuilder;
    }

    public function setTreeBuilder(TreeBuilderInterface $treeBuilder): self
    {
        $this->treeBuilder = $treeBuilder;

        return $this;
    }

    public function setAttributeDriver(AttributeDriverInterface $attributeDriver): self
    {
        $this->attributeDriver = $attributeDriver;

        return $this;
    }

    public function getPropertyAccessor(): PropertyAccessorInterface
    {
        if (!isset($this->propertyAccessor)) {
            throw new LogicException('Repository must inject property accessor service itself');
        }

        return $this->propertyAccessor;
    }

    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor): self
    {
        $this->propertyAccessor = $propertyAccessor;

        return $this;
    }

    /**
     * @return array|mixed
     *
     * @throws PropertyNotFoundException
     * @throws ReflectionException
     */
    public function getAllParent(object $entity, int $hydrate = AbstractQuery::HYDRATE_OBJECT): mixed
    {
        return $this->getAllParentQueryBuilder($entity)->getQuery()->getResult($hydrate);
    }

    /**
     * @throws PropertyNotFoundException
     * @throws ReflectionException
     */
    public function getAllParentQueryBuilder(object $entity): QueryBuilder
    {
        $this->checkClass($entity);
        $pathName = $this->getAttributeDriver()->getPathProperty($entity)->getName();
        $pathValue = $this->getPropertyAccessor()->getValue($entity, $pathName);

        $qb = $this->createQueryBuilder(static::LTREE_ALIAS);
        $qb->where(sprintf(LTreeOperatorFunction::FUNCTION_NAME.'(%s.%s, \'@>\', :self_path) = true', static::LTREE_ALIAS, $pathName));
        $qb->andWhere(sprintf('%s.%s <> :self_path', static::LTREE_ALIAS, $pathName));
        $qb->orderBy(sprintf('%s.%s', static::LTREE_ALIAS, $pathName), 'DESC');
        $qb->setParameter('self_path', $pathValue, LTreeType::TYPE_NAME);

        return $qb;
    }

    /**
     * @throws ReflectionException
     */
    protected function checkClass(object $entity): void
    {
        if (!is_a($entity, $this->getClassName())) {
            throw new InvalidArgumentException(sprintf('Entity must be instance of %s', $this->getClassName()));
        }

        if (!$this->getAttributeDriver()->classIsLTree($this->getClassName())) {
            throw new InvalidArgumentException('Entity must have ltree entity attribute');
        }
    }

    public function getAttributeDriver(): AttributeDriverInterface
    {
        if (!isset($this->attributeDriver)) {
            throw new LogicException('Repository must inject attribute driver service itself');
        }

        return $this->attributeDriver;
    }

    /**
     * @throws PropertyNotFoundException
     * @throws ReflectionException
     */
    public function getAllLTree(int $hydrate = AbstractQuery::HYDRATE_OBJECT): mixed
    {
        return $this->getInverseLTreeBuilder()->getQuery()->getResult($hydrate);
    }

    /**
     * @throws PropertyNotFoundException
     * @throws ReflectionException
     */
    public function getInverseLTreeBuilder(?object $entity = null): QueryBuilder
    {
        if (empty($entity)) {
            $entityClassName = $this->getClassName();
            $entity = new $entityClassName();
        }

        $this->checkClass($entity);

        $idName = $this->getAttributeDriver()->getIdProperty($entity)->getName();
        $idValue = $this->getPropertyAccessor()->getValue($entity, $idName);
        $pathName = $this->getAttributeDriver()->getPathProperty($entity)->getName();

        if ($idValue) {
            $pathValue = $this->getPropertyAccessor()->getValue($entity, $pathName);
            $pathValue[] = '*';
        } else {
            $pathValue = [];
        }

        $qb = $this->createQueryBuilder(static::LTREE_ALIAS);

        if ($pathValue) {
            $qb->where(sprintf(LTreeOperatorFunction::FUNCTION_NAME.'(%s.%s, \'~\', :self_path) = false', static::LTREE_ALIAS, $pathName));
            $qb->setParameter('self_path', $pathValue, LTreeType::TYPE_NAME);
        }

        $qb->orderBy(sprintf('%s.%s', static::LTREE_ALIAS, $pathName), 'ASC');

        return $qb;
    }

    /**
     * @return array|mixed|object
     *
     * @throws PropertyNotFoundException
     * @throws ReflectionException
     */
    public function getAllChildren(object $entity, bool $treeMode = false, int $hydrate = AbstractQuery::HYDRATE_OBJECT): mixed
    {
        $this->checkClass($entity);
        $result = $this->getAllChildrenQueryBuilder($entity)->getQuery()->getResult($hydrate);

        if ($treeMode && !in_array($hydrate, [Query::HYDRATE_OBJECT, Query::HYDRATE_ARRAY], true)) {
            throw new LogicException('If treeMode is true, hydration mode must be object or array');
        }

        if (!$treeMode) {
            return $result;
        }

        $pathName = $this->getAttributeDriver()->getPathProperty($entity)->getName();
        $pathValue = $this->getPropertyAccessor()->getValue($entity, $pathName);
        $parentName = $this->getAttributeDriver()->getParentProperty($entity)->getName();
        $childName = $this->getAttributeDriver()->getChildrenProperty($entity)->getName();

        return $this->treeBuilder->buildTree($result, $pathName, $pathValue, $parentName, $childName);
    }

    /**
     * @throws PropertyNotFoundException
     * @throws ReflectionException
     */
    public function getAllChildrenQueryBuilder(object $entity): QueryBuilder
    {
        $this->checkClass($entity);
        $pathName = $this->getAttributeDriver()->getPathProperty($entity)->getName();
        $pathValue = $this->getPropertyAccessor()->getValue($entity, $pathName);
        $orderFieldName = 'parent_paths_for_order';

        $qb = $this->createQueryBuilder(static::LTREE_ALIAS);
        $qb->addSelect(sprintf(LTreeSubpathFunction::FUNCTION_NAME.'(%s.%s, 0, -1) as HIDDEN %s', static::LTREE_ALIAS, $pathName, $orderFieldName));
        $qb->where(sprintf(LTreeOperatorFunction::FUNCTION_NAME.'(%s.%s, \'<@\', :self_path) = true', static::LTREE_ALIAS, $pathName));
        $qb->andWhere(sprintf('%s.%s <> :self_path', static::LTREE_ALIAS, $pathName));
        $qb->orderBy($orderFieldName);
        $qb->setParameter('self_path', $pathValue, LTreeType::TYPE_NAME);

        return $qb;
    }

    /**
     * @param array|object|null $to (if null move to root node)
     *
     * @throws ReflectionException
     * @throws PropertyNotFoundException
     */
    public function moveNode(object $entity, $to = null): mixed
    {
        $this->checkClass($entity);
        $pathName = $this->getAttributeDriver()->getPathProperty($entity)->getName();
        $oldPathValue = $this->getPropertyAccessor()->getValue($entity, $pathName);

        if (null !== $to) {
            $this->checkClass($to);
            $newPathValue = $this->getPropertyAccessor()->getValue($to, $pathName);
        } else {
            $newPathValue = [];
        }

        $prepareString = static function ($str) use ($pathName) {
            $replacement = [
                '%alias%' => static::LTREE_ALIAS,
                '%path%' => $pathName
            ];

            return str_replace(array_keys($replacement), array_values($replacement), $str);
        };

        $qb = $this->createQueryBuilder(static::LTREE_ALIAS)
            ->update()
            ->set(
                $prepareString('%alias%.%path%'),
                $prepareString(implode('', [
                    LTreeConcatFunction::FUNCTION_NAME,
                    '(:new_path, ',
                    LTreeSubpathFunction::FUNCTION_NAME,
                    '(%alias%.%path%, (',
                    LTreeNlevelFunction::FUNCTION_NAME,
                    '(:self_path) - 1)))',
                ]))
            )
            ->where($prepareString(LTreeOperatorFunction::FUNCTION_NAME.'(%alias%.%path%, \'<@\', :self_path) = true'))
            ->setParameter(':self_path', $oldPathValue, LTreeType::TYPE_NAME)
            ->setParameter(':new_path', $newPathValue, LTreeType::TYPE_NAME);

        return $qb->getQuery()->execute();
    }
}
