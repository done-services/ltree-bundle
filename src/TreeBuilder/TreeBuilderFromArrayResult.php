<?php

namespace Pvsaintpe\LTreeBundle\TreeBuilder;

use Countable;
use InvalidArgumentException;
use LogicException;

class TreeBuilderFromArrayResult implements TreeBuilderInterface
{
    public const CHILD_KEY = '__childs';

    public function buildTree(
        Countable|iterable $list,
        string             $pathName,
        ?string            $parentPath = null,
        ?string            $parentName = null,
        ?string            $childrenName = null
    ): object|array
    {
        $nodeList = array();
        $pathFinder = static function (array $path, array &$nodeList, $value) use (&$pathFinder) {
            if (count($path) === 1) {
                $nodeList[array_shift($path)] = $value;
                return true;
            }
            $key = array_shift($path);
            if (!isset($nodeList[$key])) {
                return false;
            }
            $element = &$nodeList[$key];
            if (!is_array($element)) {
                throw new InvalidArgumentException('All result values must be instance of array');
            }
            if (!isset($element[self::CHILD_KEY])) {
                $element[self::CHILD_KEY] = array();
            }
            return $pathFinder($path, $element[self::CHILD_KEY], $value);
        };

        while (count($list) > 0) {
            $forUnset = array();
            foreach ($list as $key => $item) {
                $path = array_diff($item[$pathName], $parentPath);
                if ($pathFinder($path, $nodeList, $item)) {
                    $forUnset[] = $key;
                }
            }
            foreach ($forUnset as $key) {
                unset($list[$key]);
            }
            if (count($forUnset) === 0) {
                throw new LogicException('Impossible to build tree, not all elements have parent node');
            }
        }

        return $nodeList;
    }
}
