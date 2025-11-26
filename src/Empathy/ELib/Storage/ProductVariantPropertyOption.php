<?php

namespace Empathy\ELib\Storage;

use Empathy\MVC\Model;
use Empathy\MVC\Entity;


class ProductVariantPropertyOption extends Entity
{
    const TABLE = 'product_variant_property_option';

    public $id;
    public $product_variant_id;
    public $property_option_id;

    public function emptyByVariant($variant_id)
    {
        $sql = 'DELETE FROM '.Model::getTable(self::class).' WHERE product_variant_id = '.$variant_id;
        $error = 'Could not clear property options associated with product variants.';
        $this->query($sql, $error);
    }

    public function getActiveOptions($product_id)
    {
        $ids = array();

        $sql = 'SELECT DISTINCT property_option_id AS id'
            .' FROM '.Model::getTable(self::class).' t1,'
            .' '.Model::getTable(ProductVariant::class).' t2'
            .' WHERE t2.id = t1.product_variant_id'
            .' AND t2.product_id = ?'
            .' AND t2.status = '.ProductVariantStatus::AVAILABLE;

        $error = 'Could not get active option ids for product.';
        $result = $this->query($sql, $error, [$product_id]);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                array_push($ids, $row['id']);
            }
        }

        return $ids;
    }

}
