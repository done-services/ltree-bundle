<?php

namespace Pvsaintpe\LTreeBundle\Repository;

interface LTreePathInterface
{
    public function getPath(): array;

    public function setPath(?array $path): LTreeEntityInterface|LTreePathInterface;

    public function getLevel(): int;
}
