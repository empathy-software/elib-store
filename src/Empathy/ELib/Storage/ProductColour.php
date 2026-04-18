<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

use Empathy\MVC\Entity;
use Empathy\MVC\Model;

class ProductColour extends Entity
{
    public const TABLE = 'product_colour';

    public int $id;

    public int $product_id = 0;

    public int $property_option_id = 0;

    public ?string $image = null;

    public function validates(): void
    {
        if ($this->property_option_id < 1) {
            $this->addValError('Invalid colour option.');
        }
    }

    public function hasColours(mixed $product_id): mixed
    {
        $rows = 0;
        $sql = 'SELECT * FROM '.Model::getTable(self::class).' WHERE product_id = ?';
        $error = 'Could not check for colours.';
        $result = $this->query($sql, $error, [$product_id]);
        $rows += $result->rowCount();

        return ($rows > 0);
    }

    public function getColoursIndexed(mixed $product_id): mixed
    {
        $colours = [];
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
                $colour = strtolower((string) $colour);
                $colour .= '.gif';
                $colours[$id]['swatch'] = $colour;
            }
        }

        return $colours;
    }

    public function getFirstColourImage(mixed $product_id): mixed
    {
        $sql = 'SELECT * FROM '.Model::getTable(self::class).' WHERE product_id = ?'
            .' ORDER BY id LIMIT 0,1';
        $error = 'Could not get first colour image.';
        $result = $this->query($sql, $error, [$product_id]);
        $row = null;
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
        }

        return $row !== null ? $row['image'] : '';
    }

    public function getColourOptionIDs(mixed $product_id): mixed
    {
        $colours = [];
        $sql = 'SELECT property_option_id FROM '.Model::getTable(self::class)
            .' WHERE product_id = ?';
        $error = 'Could not get product colours for variants wizard.';
        $result = $this->query($sql, $error, [$product_id]);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $colours[] = $row['property_option_id'];
            }
        }

        return $colours;
    }

}
