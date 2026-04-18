<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

use Empathy\MVC\Entity;
use Empathy\MVC\Model;

class CategoryProperty extends Entity
{
    public const TABLE = 'category_property';

    public int $id;
    public $category_id;
    public $property_id;

    public function emptyByCategory($category_id)
    {
        $sql = 'DELETE FROM '.Model::getTable(self::class).' WHERE category_id = ?';
        $error = 'Could not clear properties associated with categories.';
        $this->query($sql, $error, [$category_id]);
    }

    // this function has replaced old 'loadForCategory' function
    public function getPropertiesByCategory($cat)
    {
        $catString = $this->buildUnionString($cat);
        $properties = [];
        $sql = 'SELECT property_id FROM '.Model::getTable(self::class)
            .' WHERE category_id IN ' . $catString[0];
        $error = 'Could not find active category properties.';
        $result = $this->query($sql, $error, $catString[1]);

        $i = 0;
        foreach ($result as $row) {
            $properties[$i] = $row['property_id'];
            $i++;
        }

        return $properties;
    }

    /* old
       public function loadAll()
       {
       $sql = "SELECT * FROM ".CategoryAttribute::$table;
       $error = "Could not load product attributes.";
       $result = $this->query($sql, $error);

       }

       public function updateForCategory($category_id, $attribute)
       {
       $sql = "DELETE FROM ".CategoryAttribute::$table. " WHERE category_id = $category_id";
       $error = "Could not delete default category attributes.";
       $this->query($sql, $error);

       foreach ($attribute as $attribute_id) {
       $sql = "INSERT INTO ".CategoryAttribute::$table." VALUES(NULL, $category_id, $attribute_id)";
       $error = "Could not insert product attribute.";
       $this->query($sql, $error);
       }
       }
    */

}
