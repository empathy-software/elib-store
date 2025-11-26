<?php

namespace Empathy\ELib\Store;

use Empathy\MVC\Model;
use Empathy\MVC\Session;
use Empathy\ELib\Storage\ProductVariant;

class ShoppingCart
{
    public function calcTotal($items)
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['line'];
        }

        return $total;
    }

    public function loadFromCart($c)
    {
        $ids = array();
        $product_data = array();

        if (($cart = Session::get('cart')) != false) {
            foreach ($cart as $v => $qty) {
                array_push($ids, $v);
            }
            $v = Model::load(ProductVariant::class);
            $id_string = $v->buildUnionString($ids);
            $product_data = $v->getCartData($id_string);

            foreach ($product_data as $index => $value) {
                $id = $value['id'];
                $price = $value['price'];
                $qty = $cart[$id]['qty'];
                $product_data[$index]['qty'] = $qty;
                $product_data[$index]['line'] = $qty * $price;
                $product_data[$index]['stamp'] = $cart[$id]['stamp'];
            }

            // sort
            foreach ($product_data as $key => $row) {
                $stamp[$key] = $row['stamp'];
                unset($row['stamp']);
            }
            array_multisort($stamp, SORT_ASC, $product_data);
        }

        return $product_data;
    }

    public function add($variant_id, $qty)
    {
        if (($cart = Session::get('cart')) == false) {
            $cart = array();
        }

        if (isset($cart[$variant_id])) {
            $cart[$variant_id]['qty'] += (int) ($qty);
        } else {
            $cart[$variant_id]['qty'] = (int) ($qty);
            $cart[$variant_id]['stamp'] = time();
        }
        Session::set('cart', $cart);
    }

    public function remove($variant_id)
    {
        if (($cart = Session::get('cart')) != false) {
            if (isset($cart[$variant_id])) {
                unset($cart[$variant_id]);
                Session::set('cart', $cart);
            }
        }
    }

    public function update($variant_id, $qty)
    {
        if (($cart = Session::get('cart')) != false) {
            if (isset($cart[$variant_id])) {
                $cart[$variant_id]['qty'] = (int) ($qty);
                Session::set('cart', $cart);
            }
        }
    }

    public static function getTotalItems()
    {
        $total = 0;
        if (($cart = Session::get('cart')) != false) {
            foreach ($cart as $v => $qty) {
                $total += $qty['qty'];
            }
        }

        return $total;
    }

    public function emptyCart()
    {
        Session::set('cart', array());
    }

    public function isEmpty()
    {
        $empty = false;
        if(($cart = Session::get('cart')) == false
           || sizeof($cart) < 1)
        {
            $empty = true;
        }

        return $empty;
    }

    public static function dump()
    {
        $c = Session::get('cart');
        print_r($c);
    }

}
