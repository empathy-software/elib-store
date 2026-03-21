<?php

namespace Empathy\ELib\Storage;

use Empathy\ELib\Store\ImageUpload;
use Empathy\MVC\Model;
use Empathy\MVC\Entity;

class ProductImage extends Entity
{
    const TABLE = 'product_image';

    public int $id;
    public $image;
    public $product_id;
    public $default_image;

    public function validates()
    {
        if ($this->image == '') {
            $this->addValError('Missing filename');
        }
        if ((int) $this->product_id === 0) {
            $this->addValError('Invlalid product id');
        }
    }

    public function loadByProductItem($product)
    {
        $sql = 'SELECT * FROM '.Model::getTable(self::class)
            .' WHERE product_id = ?'
            .' order by default_image DESC';

        $error = 'Could not get images for product.';
        return $this->query($sql, $error, [$product->id])->fetchAll();
    }

    public function loadByProductItemDisplayed($product)
    {
        $noneFound = false;
        $images = $this->loadByProductItem($product);
        if (count($images) < 1) {
            $noneFound = true;
            $images = [
                [
                    'id' => 0,
                    'image' => $product->image != '' ? $product->image : 'blank.gif',
                    'default_image' => true,
                    'product_id' => $product->id,
                ]
            ];
        }
        return [ $images, $noneFound ];
    }

    public function delete(): void
    {
        $images_removed = false;
        if ($this->image != '') {
            $u = new ImageUpload('product', false, array());
            if ($u->remove(array($this->image))) {
                $images_removed = true;
            }
        }
        if ($this->image == '' || $images_removed) {
            parent::delete();
            if ($this->default_image) {
                $p = Model::load(ProductItem::class);
                $p->load($this->product_id);
                $remaining = $this->loadByProductItem($p);
                if (count($remaining) > 0) {
                    $i = Model::load(self::class);
                    $i->load($remaining[0]['id']);
                    $i->makeDefault();
                }
            }
        }
    }

    public function makeDefault()
    {
        $sql = 'UPDATE '.Model::getTable(self::class)
            .' SET default_image = 0 WHERE product_id = ?';
        $error = 'Could not update default image.';
        $this->query($sql, $error, [$this->product_id]);

        $this->default_image = 1;
        $this->save();
    }
}
