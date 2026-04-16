<?php

namespace Empathy\ELib\Storage;

use Empathy\MVC\Entity;

class LineItem extends Entity
{
    const TABLE = 'line_item';

    public int $id;
    public $order_id;
    public $variant_id;
    public $price;
    public $quantity;
    public $notes;

    public function getOrderItems($orderId)
    {
        $result = $this->getAllCustom('where order_id = ? and variant_id > 0', [$orderId]);
        return $result->fetchAll();
    }
}
