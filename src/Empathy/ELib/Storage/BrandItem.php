<?php

namespace Empathy\ELib\Storage;

use Empathy\MVC\Model;
use Empathy\MVC\Entity;

class BrandItem extends Entity
{
    const TABLE = 'brand';

    public int $id;
    public $name;
    public $about;

    public function validates()
    {
        if ($this->name == '') {
            $this->addValError('Invalid brand name.');
        }
    }

    public function buildTree($current, $tree)
    {
        $i = 0;
        $nodes = array();
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
    public function getBrands()
    {
        $brand = array();
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
