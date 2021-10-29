<?php

namespace DoubleThreeDigital\SimpleCommerce\Products\Eloquent;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    protected $table = 'products';

    protected $fillable = [
        'title', 'slug', 'price', 'description', 'product_variants', 'tax_category',
    ];

    protected $casts = [
        'product_variants' => 'json',
    ];
}
