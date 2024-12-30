<?php

namespace Pvsaintpe\LTreeBundle\Repository;

interface LTreeParentInterface
{
    /**
     * @param object|null|LTreeEntityInterface|LTreeParentInterface $parent
     * @return object
     */
    public function setParent($parent): object;

    /**
     * @return object|null|LTreeEntityInterface|LTreeParentInterface
     */
    public function getParent();
}
