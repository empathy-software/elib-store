<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

use Empathy\MVC\Entity;

class OrderStatus extends Entity
{
    public const TABLE = 'order_status';

    public int $id;

    public ?string $status = null;
}
