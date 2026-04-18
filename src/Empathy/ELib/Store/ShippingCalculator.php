<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

define('FLAT_FEE', 3.00);
define('FREE_THRESHOLD', 29.98);
define('INTL_STANDARD', 10.00);

class ShippingCalculator
{
    private mixed $fee;
    private mixed $intl_shipping = 0;

    public function __construct(mixed $total, private readonly mixed $cats, private readonly mixed $cat, private readonly mixed $item_count, private readonly mixed $calc_intl)
    {
        $special_shipping = $this->cat->getShipping($this->cats);

        if ($this->calc_intl) {
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
        } elseif ($this->item_count === 1 && $lowest < FLAT_FEE) {
            $this->fee = $lowest;
        } else {
            $this->fee = FLAT_FEE;
        }

    }

    public function getFee(): mixed
    {
        return $this->calc_intl ? $this->fee + $this->intl_shipping + INTL_STANDARD : $this->fee;
    }

}
