<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

use Empathy\MVC\Entity;
use Empathy\MVC\Model;
use OV\Misc;

class OrderItem extends Entity
{
    public const TABLE = 'e_order';

    public int $id;

    public int $user_id = 0;

    /**
     * May be {@see \Empathy\MVC\Entity} insert sentinel <code>'DEFAULT'</code>.
     */
    public int|string $status = 1;

    /**
     * May be {@see \Empathy\MVC\Entity} insert sentinel <code>'MYSQLTIME'</code>.
     */
    public string|null $stamp = null;

    public ?string $first_name = null;
    public ?string $last_name = null;
    public ?string $address1 = null;
    public ?string $address2 = null;
    public ?string $city = null;
    public ?string $state = null;
    public ?string $zip = null;
    public ?string $country = null;

    public ?string $shipping = null;
    public ?string $total = null;

    public ?string $order_id = null;

    public function getOrders(): mixed {
        $order = [];
        $sql = 'select t3.order_id as order_id, t3.stamp, t5.id as product_id,  t5.name, t2.status, t4.price, t4.quantity, t4.notes from '
            . Model::getTable(OrderItem::class) . ' t3 '
            . 'join ' . Model::getTable(OrderStatus::class) . ' t2 on t3.status = t2.id '
            . 'join ' . Model::getTable(LineItem::class) . ' t4 on t4.order_id = t3.id '
            . 'left join ' . Model::getTable(ProductVariant::class) . ' t6 on t6.id = t4.variant_id '
            . ' left join ' . Model::getTable(ProductItem::class) . ' t5 on t5.id = t6.product_id '
            . ' order by t3.id desc';

        //echo $sql;
        $error = 'Could not get orders.';
        $result = $this->query($sql, $error);
        $total = 0;
        $lastId = 0;
        $item = ['items' => []];
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $p = Model::load(ProductItem::class);

                if ($row['order_id'] !== $lastId && $lastId !== 0) {
                    $item['id'] = $lastId;
                    $item['total'] = $total;
                    $total = 0;
                    array_push($order, $item);
                    $item = ['items' => []];
                }

                $lastId = $row['order_id'];
                $item['id'] = $row['order_id'];
                $item['stamp'] = $row['stamp'];
                $item['status'] = $row['status'];
                $p->load($row['product_id']);
                array_push($item['items'], [
                    'product' => $p,
                    'id' => $row['product_id'],
                    'name' => $row['name'] ?? $row['notes'],
                    'price' => $row['price'],
                    'quantity' => $row['quantity'],
                    'notes' => $row['notes'],
                    'brand' => $p->getBrand() ?? 'General',
                    'path' => $row['product_id'] ? Misc::generatePath($p->category_id, $p->name) : '',
                ]);
                $total += $row['price'];
            }
            $item['total'] = $total;
            array_push($order, $item);
        }

        return $order;
    }

    public function loadByOrderId(mixed $order_id): mixed {
        $sql = 'select id from ' . Model::getTable(OrderItem::class) . ' where order_id = ?';
        $result = $this->query($sql, 'Could not load by order id', [$order_id]);
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            return $this->load($row['id']);
        }
        return false;
    }
}
