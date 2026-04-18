<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

use Empathy\ELib\Storage\LineItem;
use Empathy\ELib\Storage\OrderItem;
use Empathy\ELib\Storage\ShippingAddress;
use Empathy\MVC\DI;
use Empathy\MVC\Model;
use Empathy\MVC\Session;

class Checkout
{
    private mixed $invoice_no;
    private mixed $invoice_id;

    public function __construct(mixed $items)
    {
        $s = Model::load(ShippingAddress::class);
        $addressId = Session::get('shipping_address_id');
        $s->load(is_numeric($addressId) ? (int) $addressId : 0);

        $o = Model::load(OrderItem::class);
        $o->user_id = DI::getContainer()->get('CurrentUser')->getUserID();
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

        $total = 0.0;
        foreach ($items as $item) {
            if (is_numeric($item['qty']) && $item['qty'] > 0) {
                $l->order_id = $this->invoice_no;
                $l->variant_id = $item['id'];
                $price = $item['price'];
                $l->price = is_string($price)
                    ? $price
                    : number_format((float) $price, 2, '.', '');
                $l->quantity = (int) $item['qty'];
                $total += $l->quantity * (float) $l->price;
                $l->insert();
            }
        }

        $o->load($this->invoice_no);

        $o->order_id = 'OV' . $this->invoice_no . '-' . bin2hex(random_bytes(3));
        $this->invoice_id = $o->order_id;

        $o->total = number_format($total, 2, '.', '');
        $o->save();

        $countries = \Empathy\ELib\Country\Country::build();
        $shippingCountry = Session::get('shipping_country') ? Session::get('shipping_country') : 'GB';
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

    public function getInvoiceNo(): mixed {
        return $this->invoice_no;
    }

    public function setInvoiceNo(mixed $id): void {
        $this->invoice_no = $id;
    }

    public function getInvoiceId(): mixed {
        return $this->invoice_id;
    }
}
