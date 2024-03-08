<?php

namespace Elio\ElioSearch\Core\Util\Tree;

/**
 * Class Node
 * @package Elio\ElioSearch\Core\Util\Tree
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class Node implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $id;
    /**
     * @var string[]
     */
    protected array $parentIDs = [];
    /**
     * @var Node[]
     */
    protected array $parentNodes = [];
    /**
     * @var Node[]
     */
    protected array $childNodes = [];
    /**
     * @var mixed
     */
    protected $value;

    /**
     * Node constructor.
     * @param mixed       $id
     * @param mixed|null $parentID
     * @param mixed|null $value
     */
    public function __construct(
        mixed $id,
        mixed $parentID = null,
        mixed $value = null
    )
    {
        $this->id = $id;
        $this->value = $value;

        if($parentID !== null)
        {
            $this->addParentID($parentID);
        }
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string[]
     */
    public function getParentIDs(): array
    {
        return $this->parentIDs;
    }

    /**
     * @param Node $parent
     */
    public function addParent(Node $parent): void
    {
        $this->parentNodes[$parent->getId()] = $parent;
        $parent->addChild($this);
    }

    /**
     * @param Node $child
     */
    public function addChild(Node $child): void
    {
        $this->childNodes[$child->getId()] = $child;
    }

    /**
     * @return bool
     */
    public function hasParents() : bool
    {
        return count($this->parentNodes) > 0;
    }

    /**
     * Adds a parent id
     *
     * @param string|null $parentID
     */
    public function addParentID(?string $parentID): void
    {
        if($parentID !== null)
        {
            $this->parentIDs[] = $parentID;
        }

        $this->parentIDs = array_unique($this->parentIDs);
    }

    /**
     * @return Node[]
     */
    public function getChildNodes(): array
    {
        return $this->childNodes;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @return Node[]
     */
    public function getParentNodes(): array
    {
        return $this->parentNodes;
    }

    /**
     * Converts the tree into an array
     *
     * @return array
     */
    public function toArray() : array
    {
        $children = [];

        foreach ($this->childNodes as $childNode)
        {
            $children[] = $childNode->toArray();
        }

        return [
            'id' => $this->id,
            'value' => $this->value,
            'children' => $children
        ];
    }

    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    public function setChildNodes(array $childNodes): void
    {
        $this->childNodes = $childNodes;
    }
}