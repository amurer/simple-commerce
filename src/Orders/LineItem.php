<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use ArrayAccess;
use DoubleThreeDigital\SimpleCommerce\Contracts\Product;
use DoubleThreeDigital\SimpleCommerce\Facades\Product as ProductFacade;
use DoubleThreeDigital\SimpleCommerce\Products\ProductVariant;
use Illuminate\Support\Collection;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class LineItem implements ArrayAccess
{
    use FluentlyGetsAndSets;

    public $id;
    public $product;
    public $variant;
    public $quantity;
    public $total;
    public $metadata;

    public function __construct()
    {
        $this->total = 0;
        $this->metadata = collect();
    }

    public function id($id = null)
    {
        return $this->fluentlyGetOrSet('id')
            ->args(func_get_args());
    }

    public function product($product = null)
    {
        return $this->fluentlyGetOrSet('product')
            ->setter(function ($value) {
                if (! $value instanceof Product) {
                    $value = ProductFacade::find($value);
                }

                return $value;
            })
            ->args(func_get_args());
    }

    public function variant($variant = null)
    {
        return $this->fluentlyGetOrSet('variant')
            ->setter(function ($value) {
                if (! $this->product) {
                    throw new \Exception("Product not yet defined. Please define the product first, then the variant.");
                }

                if (! $value instanceof ProductVariant) {
                    $value = $this->product->variant($value);
                }

                return $value;
            })
            ->args(func_get_args());
    }

    public function quantity($quantity = null)
    {
        return $this->fluentlyGetOrSet('quantity')
            ->args(func_get_args());
    }

    public function total($total = null)
    {
        return $this->fluentlyGetOrSet('total')
            // ->setter(function ($value) {
            //     return Currency::toPence($value);
            // })
            ->args(func_get_args());
    }

    public function metadata($metadata = null)
    {
        return $this->fluentlyGetOrSet('metadata')
            ->setter(function ($value) {
                if (! $value instanceof Collection) {
                    $value = collect($value);
                }

                return $value;
            })
            ->args(func_get_args());
    }

    public function offsetSet($offset, $value)
    {
        //
    }

    public function offsetExists($offset)
    {
        //
    }

    public function offsetUnset($offset)
    {
        //
    }

    public function offsetGet($offset)
    {
        if (method_exists($this, $offset)) {
            return $this->{$offset}();
        }
    }

    public function fileData(): array
    {
        return [
            'id' => $this->id,
            'product' => $this->product->id(),
            'variant' => optional($this->variant)->id(),
            'quantity' => $this->quantity,
            'total' => $this->total,
            'metadata' => $this->metadata->toArray(),
        ];
    }
}
