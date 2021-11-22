<?php

namespace Elio\FactFinder\Core\Suggest;


/**
 * Class SuggestGroup
 * @package Elio\FactFinder\Core\Suggest
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
        $this->items = $items;
    }

    /**
     * @param SuggestItem $item
     */
    public function addItem(SuggestItem $item): void
    {
        $this->items[] = $item;
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
}