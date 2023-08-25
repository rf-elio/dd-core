<?php

namespace Elio\ElioSearch\Core\Suggest;


/**
 * Class SuggestGroup
 * @package Elio\ElioSearch\Core\Suggest
 * @author Ralf Frommherz <ralf@frommherz.me>
 */
class SuggestGroup
{
    /**
     * @var string
     */
    private string $type;
    /**
     * @var string
     */
    private string $label;
    /**
     * @var SuggestItem[]
     */
    private array $items = [];
    /**
     * @var bool
     */
    private bool $visible = true;
    /**
     * @var int
     */
    private int $position = 0;

    /**
     * @param string $type
     * @param string $label
     */
    public function __construct(string $type, string $label)
    {
        $this->type = $type;
        $this->label = $label;
    }

    /**
     * @return bool
     */
    public function hasItems() : bool
    {
        return !empty($this->items);
    }

    /**
     * @return SuggestItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param SuggestItem[] $items
     */
    public function setItems(array $items): void
    {
        $this->items = [];
        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    /**
     * @param SuggestItem $item
     */
    public function addItem(SuggestItem $item): void
    {
        $this->items[$item->getId()] = $item;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function hasImage() : bool
    {
        foreach ($this->items as $item) {
            if($item->hasImage()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return int
     */
    public function count() : int
    {
        return count($this->items);
    }

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @param bool $visible
     */
    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @param SuggestItem $item
     */
    public function remove(SuggestItem $item): void
    {
        unset($this->items[$item->getId()]);
    }
}
