<?php

namespace Pvsaintpe\LTreeBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;

interface LTreeChildrenInterface
{
    /**
     * @param object|LTreeChildrenInterface|LTreeEntityInterface $children
     * @return object
     */
    public function addChildren($children): object;

    /**
     * @param object|LTreeEntityInterface|LTreeChildrenInterface $children
     * @return object
     */
    public function removeChildren($children): object;

    /**
     * @return ArrayCollection|object[]|LTreeEntityInterface[]|LTreeChildrenInterface[]
     */
    public function getChildren(): ArrayCollection|array;
}
