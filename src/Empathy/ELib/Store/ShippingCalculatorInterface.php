<?php

namespace Empathy\ELib\Store;

interface ShippingCalculatorInterface
{
    public function getFee(): float;
}