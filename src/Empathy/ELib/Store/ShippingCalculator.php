<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

define('FLAT_FEE', 3.00);
define('FREE_THRESHOLD', 29.98);
define('INTL_STANDARD', 10.00);

class ShippingCalculator
{
    private mixed $cats;
    private mixed $cat;
    private mixed $calc_intl;
    private mixed $fee;
    private mixed $item_count;
    private mixed $intl_shipping;

    public function __construct(mixed $total, mixed $cats, mixed $cat, mixed $item_count, mixed $calc_intl)
    {
        $this->cats = $cats;
        $this->cat = $cat;
        $this->item_count = $item_count;
        $this->intl_shipping = 0;
        $this->calc_intl = $calc_intl;

        $special_shipping = $this->cat->getShipping($this->cats);

        if ($calc_intl) {
            $this->intl_shipping = $this->cat->getShippingIntl($this->cats);
        }

        $highest = FLAT_FEE;
        $lowest = FLAT_FEE;

        foreach ($special_shipping as $item) {
            if ($item < $lowest) {
                $lowest = $item;
            }
            if ($item > $highest) {
                $highest = $item;
            }
        }

        if ($highest > FLAT_FEE) {
            $this->fee = $highest;
        } elseif ($total > FREE_THRESHOLD) {
            $this->fee = 0;
        } else {
            if ($this->item_count === 1 && $lowest < FLAT_FEE) {
                $this->fee = $lowest;
            } else {
                $this->fee = FLAT_FEE;
            }
        }

    }

    public function getFee(): mixed
    {
        if ($this->calc_intl) {
            $fee = $this->fee + $this->intl_shipping + INTL_STANDARD;
        } else {
            $fee = $this->fee;
        }

        return $fee;
    }

}
