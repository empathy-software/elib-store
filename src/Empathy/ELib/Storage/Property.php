<?php

namespace Empathy\ELib\Storage;

use Empathy\MVC\Model;
use Empathy\MVC\Entity;
use Empathy\MVC\Validate;



class Property extends Entity
{
    const TABLE = 'property';

    public $id;
    public $name;

    public function loadForVariant($variant_id)
    {
        $p = array();
        $sql = 'SELECT t1.name as property_name, t2.option_val '
            .' FROM '.Model::getTable('ProductVariantPropertyOption').', '.Model::getTable(Property::class).' t1 LEFT JOIN '
            .' '.Model::getTable(PropertyOption::class).' t2 ON t1.id = t2.property_id WHERE '
            .' t2.id = property_option_id AND product_variant_id = ?';
        $error = 'Could not find variant properties.';
        $result = $this->query($sql, $error, [$variant_id]);
        foreach ($result as $row) {
            array_push($p, $row);
        }

        return ($p);
    }

    public function getAllWithOptions($props)
    {
        $propsString = $this->buildUnionString($props);

        $property = array();
        $sql = 'SELECT t1.id, t1.name, t2.id AS option_id, t2.option_val FROM '
            .Model::getTable(Property::class).' t1 '
            .'LEFT JOIN '.Model::getTable(PropertyOption::class).' t2 ON t2.property_id = t1.id';
        if (sizeof($propsString[1])) {
            $sql .= ' WHERE t1.id IN '.$propsString[0];
        }
        $sql .= ' ORDER BY t1.name, t2.option_val';

        $error = 'Could not get all properties and options.';
        $result = $this->query($sql, $error. $propsString[1]);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $id = $row['id'];
                if (!isset($property[$id]['name'])) {
                    $property[$id]['name'] = $row['name'];
                }
                if (isset($row['option_id'])) {
                    $option_id = $row['option_id'];
                    $property[$id]['option'][$option_id] = $row['option_val'];
                }
            }
        }

        return $property;
    }

    public function validates()
    {
        $this->doValType(Validate::TEXT, 'name', $this->name, false);
    }

    /*
      public function loadIndexed()
      {
      $attr = array();
      //    $attr[0] = "None";
      $sql = "SELECT * FROM ".Attribute::$table;
      $error = "Could not fetch attributes.";
      $result = $this->query($sql, $error);
      while ($row = mysql_fetch_array($result)) {
      $id = $row['id'];
      $attr[$id] = $row['name'];
      }

      return $attr;
      }
    */

    public function getAllWithOptionsForProduct($props, $opts)
    {
        $propsString = $this->buildUnionString($props);

        $property = array();
        $sql = 'SELECT t1.id, t1.name, t2.id AS option_id, t2.option_val FROM '
            .Model::getTable(Property::class).' t1 '
            .'LEFT JOIN '.Model::getTable(PropertyOption::class).' t2 ON t2.property_id = t1.id';

        $params = [];
        if (sizeof($propsString[1]) > 1) {
            $sql .= ' WHERE t1.id IN '. $propsString[0];
            $params = array_merge($params, $propsString[1]);
        }

        if (count($opts[1]) > 1) {
            $sql .= ' AND t2.id IN'.$opts[0];
            $params = array_merge($params, $opts[1]);
        }
        $sql .= ' ORDER BY t1.name, t2.option_val';

        $error = 'Could not get all properties and options.';
        $result = $this->query($sql, $error, $params);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $id = $row['id'];
                if (!isset($property[$id]['name'])) {
                    $property[$id]['name'] = $row['name'];
                }
                if (isset($row['option_id'])) {
                    $option_id = $row['option_id'];
                    $property[$id]['option'][$option_id] = $row['option_val'];
                }
            }
        }

        return $property;
    }

}
