<?php

namespace Empathy\ELib\Storage;

use Empathy\MVC\Model;
use Empathy\MVC\Entity;


class ProductColour extends Entity
{
    const TABLE = 'product_colour';

    public int $id;
    public $product_id;
    public $property_option_id;
    public $image;

    public function validates()
    {
        if ($this->name == '') {
            $this->addValError('Invalid brand name.');
        }
    }

    public function hasColours($product_id)
    {
        $rows = 0;
        $sql = 'SELECT * FROM '.Model::getTable(self::class).' WHERE product_id = ?';
        $error = 'Could not check for colours.';
        $result = $this->query($sql, $error, [$product_id]);
        $rows += $result->rowCount();

        return ($rows > 0);
    }

    public function getColoursIndexed($product_id)
    {
        $colours = array();
        $sql = 'SELECT t1.id AS id, t2.option_val FROM '.Model::getTable(self::class).' t1,'
            .' '.Model::getTable(PropertyOption::class).' t2'
            .' WHERE t1.property_option_id = t2.id'
            .' AND t1.product_id = ?';
        $error = 'Could not get product colours.';
        $result = $this->query($sql, $error, [$product_id]);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $id = $row['id'];
                $colour = $row['option_val'];
                $colours[$id]['colour'] = $colour;
                $colour = strtolower($colour);
                $colour .= '.gif';
                $colours[$id]['swatch'] = $colour;
            }
        }

        return $colours;
    }

    public function getFirstColourImage($product_id)
    {
        $sql = 'SELECT * FROM '.Model::getTable(self::class).' WHERE product_id = ?'
            .' ORDER BY id LIMIT 0,1';
        $error = 'Could not get first colour image.';
        $result = $this->query($sql, $error, [$product_id]);
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
        }

        return $row['image'];
    }

    public function getColourOptionIDs($product_id)
    {
        $colours = array();
        $sql = 'SELECT property_option_id FROM '.Model::getTable(self::class)
            .' WHERE product_id = ?';
        $error = 'Could not get product colours for variants wizard.';
        $result = $this->query($sql, $error, [$product_id]);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                array_push($colours, $row['property_option_id']);
            }
        }

        return $colours;
    }

}
