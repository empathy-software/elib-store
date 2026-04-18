<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

use Empathy\MVC\Entity;
use Empathy\MVC\Model;

class BrandItem extends Entity
{
    public const TABLE = 'brand';

    public int $id;

    public ?string $name = null;

    public ?string $about = null;

    public function validates(): void {
        if ($this->name === '') {
            $this->addValError('Invalid brand name.');
        }
    }

    public function buildTree(mixed $current, mixed $tree): mixed {
        $i = 0;
        $nodes = [];
        $sql = 'SELECT id,name FROM '.Model::getTable(self::class).' ORDER BY name';
        $error = 'Could not get brands.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $id = $row['id'];
                $nodes[$i]['id'] = $id;
                $nodes[$i]['name'] = $row['name'];
                $i++;
            }
        }

        return $nodes;
    }

    // produce a list of artists ordered correctly
    // not used to produce the artist tree
    public function getBrands(): mixed {
        $brand = [];
        $brand[0] = 'None';
        $sql = 'SELECT * FROM '.Model::getTable(self::class).' ORDER BY name';
        $error = 'Could not get list of brands.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $id = $row['id'];

                $brand[$id] = $row['name'];
            }
        }

        return $brand;
    }

}
