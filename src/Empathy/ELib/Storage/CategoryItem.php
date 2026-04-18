<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

use Empathy\MVC\Entity;
use Empathy\MVC\Model;

class CategoryItem extends Entity
{
    public const TABLE = 'category';

    public int $id;

    public int $category_id;

    /**
     * May be {@see \Empathy\MVC\Entity} insert sentinel <code>'DEFAULT'</code>.
     */
    public int|string $hidden = 0;

    public string $name = '';

    public ?string $shipping = null;
    public ?string $intl_shipping = null;

    public function buildDescendantIDs(mixed $id, mixed &$ids): void
    {
        array_push($ids, $id);
        $sql = 'SELECT id FROM '.Model::getTable(self::class).' WHERE category_id = ?';
        $error = 'Could not check for descendants.';
        $result = $this->query($sql, $error, [$id]);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $this->buildDescendantIDs($row['id'], $ids);
            }
        }
    }

    public function getChildren(mixed $id): mixed
    {
        $c = [];
        $sql = 'SELECT id FROM '.Model::getTable(self::class).' WHERE category_id = ?'
            .' AND hidden != 1'
            .' ORDER BY name';
        $error = 'Could not get child catgories.';
        $result = $this->query($sql, $error, [$id]);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                array_push($c, $row['id']);
            }
        }

        return $c;
    }

    public function hasChildren(): mixed
    {
        $c = 0;
        $sql = 'SELECT id FROM '.Model::getTable(self::class).' WHERE category_id = ?';
        $error = 'Could not check for child categories.';
        $result = $this->query($sql, $error, [$this->id]);
        if ($result->rowCount() > 0) {
            $c = 1;
        }

        return $c;
    }

    public function validates(): void
    {
        $name = $this->name;
        $name = str_replace(' ', '', $name);
        $name = str_replace('-', '', $name);

        if ($this->name === '' || !ctype_alnum(str_replace('\'', '', $name))) {
            $this->addValError('Invalid name');
        }
    }

    public function getAncestorIDs(mixed $id, mixed $ancestors): mixed
    {
        $section_id = 0;
        $category_id = 0;
        $sql = 'SELECT category_id FROM '.Model::getTable(self::class).' WHERE id = ?';
        $error = 'Could not get parent id.';
        $result = $this->query($sql, $error, [$id]);
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            $category_id = $row['category_id'];
        }
        if ($category_id !== 0) {
            array_push($ancestors, $category_id);
            $ancestors = $this->getAncestorIDs($category_id, $ancestors);
        }

        return $ancestors;
    }

    public function buildTree(mixed $current, mixed $tree): mixed
    {
        $i = 0;
        $nodes = [];
        $sql = 'SELECT id,name AS label FROM '.Model::getTable(self::class).' WHERE category_id = ? ORDER BY name';
        $error = 'Could not get child sections.';
        $result = $this->query($sql, $error, [$current]);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $id = $row['id'];
                $nodes[$i]['id'] = $id;
                $nodes[$i]['label'] = $row['label'];
                $nodes[$i]['children'] = $tree->buildTree($id, $tree);
                $i++;
            }
        }

        return $nodes;
    }

    public function hasCategoriesOrProducts(mixed $id): mixed
    {
        $data = 0;
        $sql = 'SELECT id FROM '.Model::getTable(self::class).' WHERE category_id = ?';
        $error = 'Could not check for existing child categories.';
        $result = $this->query($sql, $error, [$id]);
        if ($result->rowCount() > 0) {
            $data = 1;
        }
        $sql = 'SELECT id FROM '.Model::getTable(ProductItem::class).' WHERE category_id = ?';
        $error = 'Could not check for existing products.';
        $result = $this->query($sql, $error, [$id]);
        if ($result->rowCount() > 0) {
            $data = 1;
        }

        return $data;
    }

    public function buildBreadCrumb(mixed $id, mixed &$ancestors): void
    {
        $category = [];
        $row = null;
        $sql = 'SELECT id,category_id,name FROM '.Model::getTable(self::class).' WHERE id = ?';
        $error = 'Could not get parent id.';
        $result = $this->query($sql, $error, [$id]);
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            $category['id'] = $row['id'];
            $category['name'] = $row['name'];
        }
        array_push($ancestors, $category);

        if ($row !== null && $row['category_id'] !== 0) {

            $this->buildBreadCrumb($row['category_id'], $ancestors);
        }
    }

    //public function loadIndexed()
    public function loadIndexed(mixed $parent_id): mixed
    {
        $sql = 'SELECT * FROM '.Model::getTable(self::class);
        //$sql .= ' WHERE parent_id = '.$parent_id;
        $error = 'Could not fetch categories.';
        $result = $this->query($sql, $error);
        $category = [];
        foreach ($result as $row) {
            $id = $row['id'];
            $category[$id] = $row['name'];
        }

        return $category;
    }

    public function getShipping(mixed $ids): mixed
    {
        $idsString = $this->buildUnionString($ids);
        $shipping = [];
        $sql = 'SELECT shipping FROM '.Model::getTable(self::class)
            .' WHERE id IN ' . $idsString[0]
            .' AND shipping IS NOT NULL';
        $error = 'Could not get shipping info from categories.';
        $result = $this->query($sql, $error, $idsString[1]);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                array_push($shipping, $row['shipping']);
            }
        }

        return $shipping;
    }

    public function getShippingIntl(mixed $ids): mixed
    {
        $idsString = $this->buildUnionString($ids);
        $shipping = 0;
        $sql = 'SELECT MAX(intl_shipping) AS intl_shipping FROM '.Model::getTable(self::class)
            .' WHERE id IN '. $idsString[0]
            .' AND intl_shipping IS NOT NULL GROUP BY id';
        $error = 'Could not get international shipping info from categories.';
        $result = $this->query($sql, $error, $idsString[1]);
        if ($result->rowCount() === 1) {
            $row = $result->fetch();
            $shipping = $row['intl_shipping'];
        }

        return $shipping;
    }

    public function loadIDByName(mixed $name): mixed
    {
        $id = 0;
        $sql = 'SELECT id FROM '.Model::getTable(self::class)
            .' WHERE name LIKE ?';
        $error = 'Could not get category id by name.';
        $result = $this->query($sql, $error, ['\'' . str_replace('-', ' ', $name) . '\'']);
        if ($result->rowCount() === 1) {
            $row = $result->fetch();
            $id = $row['id'];
        }

        return $id;
    }

}
