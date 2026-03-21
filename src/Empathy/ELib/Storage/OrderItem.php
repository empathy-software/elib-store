<?php

namespace Empathy\ELib\Storage;

use Empathy\MVC\Model;
use Empathy\MVC\Entity;

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
        $order = array();
        $sql = 'SELECT t3.id AS order_id, username, t2.status, stamp, SUM(t4.price) AS total'
            .' FROM '.Model::getTable(UserItem::class).' t1, '.Model::getTable(OrderStatus::class).' t2, '.Model::getTable('OrderItem').' t3'
            .' LEFT JOIN '.Model::getTable(LineItem::class).' t4 ON t4.order_id = t3.id'
            .' WHERE t1.id = t3.user_id AND t2.id = t3.status'
            .' GROUP BY t3.id'
            .' ORDER BY stamp DESC';

        $error = 'Could not get orders.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $item = array();
                $item['id'] = $row['order_id'];
                $item['username'] = $row['username'];
                $item['stamp'] = $row['stamp'];
                $item['status'] = $row['status'];
                if ($row['total'] == '') {
                    $row['total'] = 0;
                }
                $item['total'] = $row['total'];
                array_push($order, $item);
            }
        }

        return $order;
    }

}
