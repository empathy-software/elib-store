<?php

namespace Empathy\ELib\Storage;

use Empathy\ELib\Model,
    Empathy\MVC\Entity;



class ProductItem extends Entity
{
    const TABLE = 'product';

    public $id;
    public $category_id;
    public $brand_id;
    public $name;
    public $description;
    public $image;
    public $upc;
    public $status;
    public $vendor_id;
    public $min_price;
    public $vendor_verified;

    public function validates()
    {
        if ($this->name == '') { // || !ctype_alnum(str_replace(' ', '', $this->name))) 
            $this->addValError('Invalid product name');
        }
        if ($this->description == '') {
            $this->addValError('Invalid product description');
        }
    }

    public function hasOneVariant()
    {
        $id = 0;
        $sql = 'SELECT id FROM '.Model::getTable('ProductVariant').' WHERE product_id = '.$this->id;
        $error = 'Could not check for single variant on product.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() == 1) {
            $row = $result->fetch();
            $id = $row['id'];
        }

        return $id;
    }

    public function hasVariants()
    {
        $variants = false;
        $sql = 'SELECT id FROM '.Model::getTable('ProductVariant').' WHERE product_id = '.$this->id;
        $error = 'Could not check for product variants.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            $variants = true;
        }

        return $variants;
    }

    public function convertCategory()
    {
        $sql = "SELECT name from ".Model::getTable('CategoryItem')." WHERE id = $this->category_id";
        $error = "Could not get category name.";
        $result = $this->query($sql, $error);
        $row = $result->fetch();

        return $row['name'];
    }

    public function getOnlyVariantID()
    {
        $id = 0;
        $sql = 'SELECT id FROM '.Model::getTable('ProductVariant')
            .' WHERE product_id = '.$this->id.' LIMIT 0,1';
        $error = 'Could not get id for only product variant.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            $id = $row['id'];
        }

        return $id;
    }

    // including variants

    public function getAllImages()
    {
        $image = array();
        $sql = 'SELECT image FROM '.Model::getTable('ProductItem');
        $error = 'Could not get product images.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                array_push($image, $row['image']);
            }
        }
        $sql = 'SELECT image FROM '.Model::getTable('ProductVariant');
        $error = 'Could not get variant images.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                array_push($image, $row['image']);
            }
        }

        return $image;
    }

    public function getPrice()
    {
        $price = 0;
        $sql = 'SELECT MIN(price) AS price FROM '.Model::getTable('ProductVariant')
            .' WHERE product_id = '.$this->id
            .' AND product_id > 0';
        $error = 'Could not get price.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() == 1) {
            $row = $result->fetch();
            $price = $row['price'];
        }

        return $price;
    }

    // new get price function
    public function getMinPrice($id)
    {
        $price = 0;
        $sql = 'SELECT MIN(price) AS price FROM '.Model::getTable('ProductVariant')
            .' WHERE product_id = '.$id
            .' AND status = '.ProductVariantStatus::AVAILABLE;
        $error = 'Could not get price.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() == 1) {
            $row = $result->fetch();
            $price = $row['price'];
        }

        return $price;
    }

    public function loadIDByName($name)
    {
        $id = 0;
        $sql = 'SELECT id FROM '.Model::getTable('ProductItem')
            .' WHERE name LIKE \''.str_replace('-', ' ', $name).'\'';

        echo $sql;
        $error = 'Could not get product id by name.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() == 1) {
            $row = $result->fetch();
            $id = $row['id'];
        }

        return $id;
    }

}
