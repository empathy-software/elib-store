<?php

namespace Empathy\ELib\Storage;

use Empathy\MVC\Model;
use Empathy\MVC\Entity;

class ProductImage extends Entity
{
    const TABLE = 'product_image';

    public $id;
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
        $images =  $this->query($sql, $error, [$product->id])->fetchAll();
        if (count($images) < 1) {
            $images = [
                [
                    'id' => 0,
                    'image' => $product->image != '' ? $product->image : 'blank.gif',
                    'default_image' => true,
                    'product_id' => $product->id,
                ]
            ];
        }
        return $images;
    }
}
