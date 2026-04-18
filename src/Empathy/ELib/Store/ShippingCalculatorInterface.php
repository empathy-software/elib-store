<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

interface ShippingCalculatorInterface
{
    public function getFee(): float;
}
