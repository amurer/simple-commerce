<?php

namespace DoubleThreeDigital\SimpleCommerce\Coupons\Eloquent;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    protected $table = 'coupons';

    protected $fillable = [
        'code', 'type', 'value', 'maximum_uses', 'minimum_cart_value', 'redeemed',
    ];

    public function products()
    {
        // BelongsTo Product
    }
}
