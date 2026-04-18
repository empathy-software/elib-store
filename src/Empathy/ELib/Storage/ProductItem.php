<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

use Empathy\MVC\Entity;
use Empathy\MVC\Model;

class ProductItem extends Entity
{
    public const TABLE = 'product';

    public int $id;
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
    public $shipping_uk;
    public $shipping_eu;
    public $shipping_other;
    private $images = [];
    private $noImageFound = false;
    private $soldInStore = 0;
    private $stock;
    private $brand;

    public function validates()
    {
        if ($this->name === '') { // || !ctype_alnum(str_replace(' ', '', $this->name)))
            $this->addValError('Invalid product name');
        }
        if ($this->description === '') {
            $this->addValError('Invalid product description');
        }
    }

    public function load(mixed $id = null): bool
    {
        $i = Model::load(ProductImage::class);
        parent::load($id);
        list($this->images, $this->noImageFound) = $i->loadByProductItemDisplayed($this);

        $v = Model::load(ProductVariant::class);
        $this->stock = $v->getStockLevels($id);

        if ($this->brand_id) {
            $b = Model::load(BrandItem::class);
            $b->load($this->brand_id);
            $this->brand = $b->name;
        }

        return true;
    }

    public function getStock()
    {
        return $this->stock;
    }

    public function getImages()
    {
        return $this->images;
    }

    public function getNoImageFound()
    {
        return $this->noImageFound;
    }

    public function getDefaultImage()
    {
        return $this->images[0];
    }

    public function hasOneVariant()
    {
        $id = 0;
        $sql = 'SELECT id FROM '.Model::getTable(ProductVariant::class).' WHERE product_id = ?';
        $error = 'Could not check for single variant on product.';
        $result = $this->query($sql, $error, [$this->id]);
        if ($result->rowCount() === 1) {
            $row = $result->fetch();
            $id = $row['id'];
        }

        return $id;
    }

    public function hasVariants()
    {
        $variants = false;
        $sql = 'SELECT id FROM '.Model::getTable(ProductVariant::class).' WHERE product_id = ?';
        $error = 'Could not check for product variants.';
        $result = $this->query($sql, $error, [$this->id]);
        if ($result->rowCount() > 0) {
            $variants = true;
        }

        return $variants;
    }

    public function convertCategory()
    {
        $sql = 'SELECT name from '.Model::getTable(CategoryItem::class).' WHERE id = ?';
        $error = 'Could not get category name.';
        $result = $this->query($sql, $error, [$this->category_id]);
        $row = $result->fetch();

        return $row['name'];
    }

    public function getOnlyVariantID()
    {
        $id = 0;
        $sql = 'SELECT id FROM '.Model::getTable(ProductVariant::class)
            .' WHERE product_id = ? LIMIT 0,1';
        $error = 'Could not get id for only product variant.';
        $result = $this->query($sql, $error, [$this->id]);
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            $id = $row['id'];
        }

        return $id;
    }

    // including variants

    public function getAllImages()
    {
        $image = [];
        $sql = 'SELECT image FROM '.Model::getTable(ProductItem::class);
        $error = 'Could not get product images.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                array_push($image, $row['image']);
            }
        }
        $sql = 'SELECT image FROM '.Model::getTable(ProductVariant::class);
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
        $sql = 'SELECT MIN(price) AS price FROM '.Model::getTable(ProductVariant::class)
            .' WHERE product_id = ?'
            .' AND product_id > 0';
        $error = 'Could not get price.';
        $result = $this->query($sql, $error, [$this->id]);
        if ($result->rowCount() === 1) {
            $row = $result->fetch();
            $price = $row['price'];
        }

        return $price;
    }

    // new get price function
    public function getMinPrice($id)
    {
        $price = 0;
        $sql = 'SELECT MIN(price) AS price FROM '.Model::getTable(ProductVariant::class)
            .' WHERE product_id = ?'
            .' AND status = '.ProductVariantStatus::AVAILABLE;
        $error = 'Could not get price.';
        $result = $this->query($sql, $error, [$id]);
        if ($result->rowCount() === 1) {
            $row = $result->fetch();
            $price = $row['price'];
        }

        return $price;
    }

    public function loadIDByName($name)
    {
        $id = 0;
        $sql = 'SELECT id FROM '.Model::getTable(ProductItem::class)
            .' WHERE name LIKE ?';

        echo $sql;
        $error = 'Could not get product id by name.';
        $result = $this->query($sql, $error, ['\''.str_replace('-', ' ', $name).'\'']);
        if ($result->rowCount() === 1) {
            $row = $result->fetch();
            $id = $row['id'];
        }

        return $id;
    }

    public function setSoldInStore($soldInStore)
    {
        $this->soldInStore = $soldInStore;
    }

    public function getSoldInStore()
    {
        return $this->soldInStore;
    }

    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Mark products for a vendor as verified after the vendor account is approved.
     */
    public function verify(int $vendorId): void
    {
        $sql = 'UPDATE ' . Model::getTable(self::class)
            . ' SET vendor_verified = 1 WHERE vendor_id = ?';
        $this->query($sql, 'Could not verify vendor products.', [$vendorId]);
    }
}
