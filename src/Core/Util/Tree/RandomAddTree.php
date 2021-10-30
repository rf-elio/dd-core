<?php

namespace Elio\FactFinder\Core\Util\Tree;

/**
 * Class RandomAddTree
 * @package Elio\FactFinder\Core\Util\Tree
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2020, elio GmbH (https://www.elio-systems.com)
 */
class RandomAddTree
{
    /**
     * @var Node[]
     */
    protected array $nodeList = [];

    /**
     * Adds a new tree node
     *
     * @param string      $id
     * @param string|null $parentID
     * @param mixed       $value
     */
    public function add(string $id, ?string $parentID, $value): void
    {
        if(isset($this->nodeList[$id]))
        {
            $this->nodeList[$id]->addParentID($parentID);
        }
        else
        {
            $this->nodeList[$id] = new Node($id, $parentID, $value);
        }
    }

    /**
     * Creates the tree and returns the root nodes
     *
     * @return Node[]
     */
    public function create() : array
    {
        foreach ($this->nodeList as $node)
        {
            foreach ($node->getParentIDs() as $parentID)
            {
                if(isset($this->nodeList[$parentID]))
                {
                    $node->addParent($this->nodeList[$parentID]);
                }
            }
        }

        $rootNodes = [];

        foreach ($this->nodeList as $node)
        {
            if(!$node->hasParents())
            {
                $rootNodes[] = $node;
            }
        }

        return $rootNodes;
    }
}