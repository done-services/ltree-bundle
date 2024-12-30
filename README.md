# ltree-bundle

Installation:
-------------

```
composer require pvsaintpe/ltree-bundle
```

Using
-----

1. Create Entity class:

```php
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Pvsaintpe\LTreeBundle\Attribute\{LTreeChilds, LTreeEntity, LTreeParent, LTreePath};
use Pvsaintpe\LTreeBundle\Repository\LTreeEntityInterface;

#[Entity(repositoryClass="App\EntityRepository\TestRepository")]
#[LTreeEntity]
class TestEntity implements LTreeEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy="AUTO")]
    #[ORM\Column(type="integer")]
    private int $id;

    #[LTreePath]
    #[ORM\Column(type="ltree")]
    private array $path = null;

    #[LTreeParent]
    #[ORM\ManyToOne(targetEntity="TestEntity", inversedBy="children")]
    private ?TestEntity $parent = null;

    #[LTreeChilds]
    #[ORM\OneToMany(targetEntity="TestEntity", mappedBy="parent", cascade={"all"}, orphanRemoval=true)]
    #[ORM\JoinColumn(onDelete="CASCADE")]
    private Collection $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }
}
```

2. Create Repository class:

```php
use Doctrine\ORM\EntityManagerInterface;
use Pvsaintpe\LTreeBundle\Repository\LTreeEntityRepository;

/**
 * @method TestEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestEntity[]    findAll()
 * @method TestEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestRepository extends LTreeEntityRepository
{
    public function __construct(EntityManagerInterface $registry)
    {
        parent::__construct($registry, $registry->getClassMetadata(TestEntity::class));
    }
}
```

3. Create Extension via migration

```php
    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE IF NOT EXISTS EXTENSION ltree');
    }
    ...
```

4. Configure Doctrine Type via config if overridden by another bundle (packages/doctrine.yaml):

```yaml
doctrine:
  dbal:
    url: '%env(resolve:DATABASE_URL)%'
    types:
      ltree: Pvsaintpe\LTreeBundle\Types\LTreeType
```

5. Configure Bundle via config (bundles.php) if not using flex:

```php
Pvsaintpe\LTreeBundle\LTreeBundle::class => ['all' => true],
```
