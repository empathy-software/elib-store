<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

define('FLAT_FEE', 3.00);
define('FREE_THRESHOLD', 29.98);
define('INTL_STANDARD', 10.00);

class ShippingCalculator
{
    private $total;
    private $cats;
    private $cat;
    private $calc_intl;
    private $fee;
    private $item_count;
    private $intl_shipping;

    public function __construct($total, $cats, $cat, $item_count, $calc_intl)
    {
        $this->total = $total;
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

    public function getFee()
    {
        if ($this->calc_intl) {
            $fee = $this->fee + $this->intl_shipping + INTL_STANDARD;
        } else {
            $fee = $this->fee;
        }

        return $fee;
    }

}
