<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Orders\LineItem;

interface Calculator
{
    public function calculate(Order $order): array;

    public function calculateLineItem(array $data, LineItem $lineItem): array;

    public function calculateLineItemTax(array $data, LineItem $lineItem): array;

    public function calculateOrderShipping(array $data): array;

    public function calculateOrderCoupons(array $data): array;

    public static function bindings(): array;
}
