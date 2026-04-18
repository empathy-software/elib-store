<?php

namespace Empathy\ELib\Store;

use Empathy\MVC\Model;
use Empathy\MVC\DI;
use Empathy\MVC\Session;
use Empathy\ELib\Storage\ShippingAddress;
use Empathy\ELib\Storage\OrderItem;
use Empathy\ELib\Storage\LineItem;
use Empathy\ELib\Storage\ProductVariant;


class Checkout
{
    private $invoice_no;
    private $invoice_id;

    public function __construct($items, $c)
    {
        $s = Model::load(ShippingAddress::class);
        $s->id = Session::get('shipping_address_id');
        $s->load();

        $o = Model::load(OrderItem::class);
        $o->user_id = DI::getContainer()->get('CurrentUser')->getUserId();
        $o->status = 'DEFAULT';
        $o->stamp = 'MYSQLTIME';
        $o->first_name = $s->first_name;
        $o->last_name = $s->last_name;
        $o->address1 = $s->address1;
        $o->address2 = $s->address2;
        $o->city = $s->city;
        $o->state = $s->state;
        $o->zip = $s->zip;
        $o->country = $s->country;

        $this->invoice_no = $o->insert();

        $l = Model::load(LineItem::class);

        $total = 0;
        foreach ($items as $item) {
            if (is_numeric($item['qty']) && $item['qty'] > 0) {
                $l->order_id = $this->invoice_no;
                $l->variant_id = $item['id'];
                $l->price = $item['price'];
                $l->quantity = $item['qty'];
                $total += $l->quantity * $l->price;
                $l->insert();
            }
        }

        $o->load($this->invoice_no);

        $o->order_id = 'OV' . $this->invoice_no . '-' . bin2hex(random_bytes(3));
        $this->invoice_id = $o->order_id;

        $o->total = $total;
        $o->save();

        $countries = \Empathy\ELib\Country\Country::build();
        $shippingCountry = Session::get('shipping_country') ? Session::get('shipping_country'): 'GB';
        $country = $countries[$shippingCountry];

        // add shipping
        $l = Model::load(LineItem::class);
        $l->order_id = $this->invoice_no;
        $l->variant_id = 0;
        $sc = DI::getContainer()->get('ShippingCalculator');
        $l->price = $sc->getFee();
        $l->quantity = 1;
        $l->notes = 'Shipping to ' . $country;
        $l->insert();
    }

    public function getInvoiceNo()
    {
        return $this->invoice_no;
    }

    public function setInvoiceNo($id)
    {
        $this->invoice_no = $id;
    }

    public function getInvoiceId()
    {
        return $this->invoice_id;
    }
}
