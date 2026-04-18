<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

use Empathy\ELib\Store\ImageUpload;
use Empathy\MVC\Entity;
use Empathy\MVC\Model;

class ProductImage extends Entity
{
    public const TABLE = 'product_image';

    public int $id;

    public string $image = '';

    public int $product_id = 0;

    public int $default_image = 1;

    public function validates(): void
    {
        if ($this->image === '') {
            $this->addValError('Missing filename');
        }
        if ($this->product_id === 0) {
            $this->addValError('Invlalid product id');
        }
    }

    public function loadByProductItem(mixed $product): mixed
    {
        $sql = 'SELECT * FROM '.Model::getTable(self::class)
            .' WHERE product_id = ?'
            .' order by default_image DESC';

        $error = 'Could not get images for product.';
        return $this->query($sql, $error, [$product->id])->fetchAll();
    }

    public function loadByProductItemDisplayed(mixed $product): mixed
    {
        $noneFound = false;
        $images = $this->loadByProductItem($product);
        if (count($images) < 1) {
            $noneFound = true;
            $images = [
                [
                    'id' => 0,
                    'image' => $product->image !== '' ? $product->image : 'blank.gif',
                    'default_image' => true,
                    'product_id' => $product->id,
                ],
            ];
        }
        return [ $images, $noneFound ];
    }

    #[\Override]
    public function delete(): void
    {
        $images_removed = false;
        if ($this->image !== '') {
            $u = new ImageUpload('product', false, []);
            if ($u->remove([$this->image])) {
                $images_removed = true;
            }
        }
        if ($this->image === '' || $images_removed) {
            parent::delete();
            if ($this->default_image !== 0) {
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

    public function makeDefault(): void
    {
        $sql = 'UPDATE '.Model::getTable(self::class)
            .' SET default_image = 0 WHERE product_id = ?';
        $error = 'Could not update default image.';
        $this->query($sql, $error, [$this->product_id]);

        $this->default_image = 1;
        $this->save();
    }
}
