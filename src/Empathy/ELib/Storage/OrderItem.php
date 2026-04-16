<?php

namespace Empathy\ELib\Storage;

use Empathy\MVC\Model;
use Empathy\MVC\Entity;
use Empathy\ELib\Storage\UserItem;
use Empathy\ELib\Storage\OrderStatus;
use Empathy\ELib\Storage\LineItem;
use Empathy\ELib\Storage\ProductItem;
use OV\Misc;

class OrderItem extends Entity
{
    const TABLE = 'e_order';

    public int $id;
    public $user_id;
    public $status;
    public $stamp;
    public $first_name;
    public $last_name;
    public $address1;
    public $address2;
    public $city;
    public $state;
    public $zip;
    public $country;
    public $shipping;

    public function getOrders()
    {
        $order = [];
        $sql = 'select t3.id as order_id, t3.stamp, t5.id as product_id,  t5.name, t2.status, t4.price, t4.quantity, t4.notes from '
            . Model::getTable(OrderItem::class) . ' t3 '
            . 'join ' . Model::getTable(OrderStatus::class) . ' t2 on t3.status = t2.id '
            . 'join ' . Model::getTable(LineItem::class) . ' t4 on t4.order_id = t3.id '
            . 'left join '. Model::getTable(ProductVariant::class) . ' t6 on t6.id = t4.variant_id '
            .' left join ' . Model::getTable(ProductItem::class) . ' t5 on t5.id = t6.product_id '
            .' order by t3.id desc';

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

}
