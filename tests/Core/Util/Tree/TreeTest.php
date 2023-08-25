<?php


namespace Elio\ElioSearch\Tests\Core\Util\Tree;


use Elio\ElioSearch\Core\Util\Tree\Node;
use Elio\ElioSearch\Core\Util\Tree\RandomAddTree;
use PHPUnit\Framework\TestCase;

/**
 * Class TreeTest
 *
 * @package Elio\ElioSearch\Tests\Core\Util\Tree
 */
class TreeTest extends TestCase
{
    public function testCreate(): void
    {
        $categoriesList = $this->getCategoriesList();

        $tree = new RandomAddTree();
        foreach ($categoriesList as $category) {
            $tree->add($category['id'], $category['parentId'], $category['value']);
        }
        self::assertSame(
            $this->toArray($this->getExpectedTree()),
            $this->toArray($tree->create())
        );

        // random order of categories
        shuffle($categoriesList);
        $tree = new RandomAddTree();
        foreach ($this->getCategoriesList() as $category) {
            $tree->add($category['id'], $category['parentId'], $category['value']);
        }
        self::assertSame(
            $this->toArray($this->getExpectedTree()),
            $this->toArray($tree->create())
        );
    }

    private function getCategoriesList(): array
    {
        return [
            [
                'id' => 1,
                'parentId' => null,
                'value' => 'Electronics, Sports & Toys'
            ],
            [
                'id' => 2,
                'parentId' => null,
                'value' => 'Clothing, Automotive & Tools'
            ],
            [
                'id' => 3,
                'parentId' => 1,
                'value' => 'Books & Health'
            ],
            [
                'id' => 4,
                'parentId' => 1,
                'value' => 'Games & Outdoors'
            ],
            [
                'id' => 5,
                'parentId' => 4,
                'value' => 'Games'
            ],
            [
                'id' => 6,
                'parentId' => 4,
                'value' => 'Outdoors'
            ],
            [
                'id' => 7,
                'parentId' => 2,
                'value' => 'Music'
            ],
            [
                'id' => 8,
                'parentId' => 2,
                'value' => 'Movies'
            ],
        ];
    }

    /**
     * @param Node[] $nodes
     *
     * @return array
     */
    private function toArray(array $nodes): array
    {
        $result = [];
        foreach ($nodes as $node) {
            $result[] = $node->toArray();
        }
        return $result;
    }

    private function getExpectedTree(): array
    {
        $nodeGames = new Node('5', '4', 'Games');
        $nodeOutdoors = new Node('6', '4', 'Outdoors');

        $nodeBooksHealth = new Node('3', '1', 'Books & Health');
        $nodeGamesOutdoors = new Node('4', '1', 'Games & Outdoors');

        $nodeMusic = new Node('7', '2', 'Music');
        $nodeMovies = new Node('8', '2', 'Movies');

        $nodeElectronicsSportsToys = new Node('1', null, 'Electronics, Sports & Toys');
        $nodeClothingAutomotiveTools = new Node('2', null, 'Clothing, Automotive & Tools');

        $nodeGamesOutdoors->addChild($nodeGames);
        $nodeGamesOutdoors->addChild($nodeOutdoors);

        $nodeElectronicsSportsToys->addChild($nodeBooksHealth);
        $nodeElectronicsSportsToys->addChild($nodeGamesOutdoors);

        $nodeClothingAutomotiveTools->addChild($nodeMusic);
        $nodeClothingAutomotiveTools->addChild($nodeMovies);

        return [
            $nodeElectronicsSportsToys,
            $nodeClothingAutomotiveTools
        ];
    }
}
