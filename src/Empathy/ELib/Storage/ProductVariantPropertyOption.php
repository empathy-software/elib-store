<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

use Empathy\MVC\Entity;
use Empathy\MVC\Model;

class ProductVariantPropertyOption extends Entity
{
    public const TABLE = 'product_variant_property_option';

    public int $id;

    public int $product_variant_id = 0;

    public int $property_option_id = 0;

    public function emptyByVariant(mixed $variant_id): void
    {
        $sql = 'DELETE FROM '.Model::getTable(self::class).' WHERE product_variant_id = '.$variant_id;
        $error = 'Could not clear property options associated with product variants.';
        $this->query($sql, $error);
    }

    public function getActiveOptions(mixed $product_id): mixed
    {
        $ids = [];

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
                $ids[] = $row['id'];
            }
        }

        return $ids;
    }

}
