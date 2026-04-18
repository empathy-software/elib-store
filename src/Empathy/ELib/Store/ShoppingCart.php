<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

use Empathy\ELib\Storage\ProductVariant;
use Empathy\MVC\Model;
use Empathy\MVC\Session;

class ShoppingCart
{
    public function calcTotal(mixed $items): mixed {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['line'];
        }

        return $total;
    }

    public function loadFromCart(): mixed {
        $ids = [];
        $product_data = [];

        if (($cart = Session::get('cart')) !== false) {
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
                $product_data[$index]['shipping'] = $value['shipping'];
            }

            // sort
            $stamp = [];
            foreach ($product_data as $key => $row) {
                $stamp[$key] = $row['stamp'];
                unset($row['stamp']);
            }
            array_multisort($stamp, SORT_ASC, $product_data);
        }

        return $product_data;
    }

    public function add(mixed $variant_id, mixed $qty): void {
        if (($cart = Session::get('cart')) === false) {
            $cart = [];
        }

        if (isset($cart[$variant_id])) {
            $cart[$variant_id]['qty'] += (int) ($qty);
        } else {
            $cart[$variant_id]['qty'] = (int) ($qty);
            $cart[$variant_id]['stamp'] = time();
        }
        Session::set('cart', $cart);
    }

    public function remove(mixed $variant_id): void {
        if (($cart = Session::get('cart')) !== false) {
            if (isset($cart[$variant_id])) {
                unset($cart[$variant_id]);
                Session::set('cart', $cart);
            }
        }
    }

    public function update(mixed $variant_id, mixed $qty): void {
        if (($cart = Session::get('cart')) !== false) {
            if (isset($cart[$variant_id])) {
                $cart[$variant_id]['qty'] = (int) ($qty);
                Session::set('cart', $cart);
            }
        }
    }

    public static function getTotalItems(): mixed {
        $total = 0;
        if (($cart = Session::get('cart')) !== false) {
            foreach ($cart as $v => $qty) {
                $total += $qty['qty'];
            }
        }

        return $total;
    }

    public function emptyCart(): void {
        Session::set('cart', []);
    }

    public function isEmpty(): mixed {
        $empty = false;
        if (($cart = Session::get('cart')) === false
           || sizeof($cart) < 1) {
            $empty = true;
        }

        return $empty;
    }

    public static function dump(): void {
        $c = Session::get('cart');
        print_r($c);
    }

    public function getQtyByProductId(int $productId): int
    {
        $items = $this->loadFromCart();
        $total = 0;
        foreach ($items as $item) {
            if ((int)$item['product_id'] === $productId) {
                $total += (int)$item['qty'];
            }
        }
        return $total;
    }
}
