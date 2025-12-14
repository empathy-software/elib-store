<?php

namespace Empathy\ELib\Store;

use Empathy\MVC\Model;
use Empathy\ELib\Storage\OrderItem;


class OrdersController extends AdminController
{
    public function default_event()
    {
        $o = Model::load(OrderItem::class);
        $orders = $o->getOrders();
        $this->presenter->assign('orders', $orders);
        $this->setTemplate('elib://admin/orders.tpl');
    }
}
