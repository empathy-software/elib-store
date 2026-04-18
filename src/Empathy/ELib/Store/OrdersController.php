<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

use Empathy\ELib\Storage\OrderItem;
use Empathy\MVC\Model;

class OrdersController extends AdminController
{
    public function default_event(): void
    {
        $o = Model::load(OrderItem::class);
        $orders = $o->getOrders();
        $this->presenter->assign('orders', $orders);
        $this->setTemplate('elib://admin/orders.tpl');
    }
}
