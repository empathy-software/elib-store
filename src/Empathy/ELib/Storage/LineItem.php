<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

use Empathy\MVC\Entity;

class LineItem extends Entity
{
    public const TABLE = 'line_item';

    public int $id;

    public int $order_id = 0;

    public int $variant_id = 0;

    public float $price = 0.00;

    public int $quantity = 0;

    public ?string $notes = null;

    public function getOrderItems(mixed $orderId): mixed
    {
        return $this->getAllCustom('where order_id = ? and variant_id > 0', [$orderId]);
    }
}
