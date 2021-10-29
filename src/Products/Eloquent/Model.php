<?php

namespace DoubleThreeDigital\SimpleCommerce\Products\Eloquent;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Model extends EloquentModel
{
    protected $table = 'products';

    protected $fillable = [
        'title', 'slug', 'price', 'description', 'product_variants', 'tax_category',
    ];

    protected $casts = [
        'product_variants' => 'json',
    ];

    public function coupons(): HasMany
    {
        return $this->hasMany(SimpleCommerce::couponDriver()['model']);
    }
}
