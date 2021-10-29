<?php

namespace DoubleThreeDigital\SimpleCommerce\Coupons\Eloquent;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Model extends EloquentModel
{
    protected $table = 'coupons';

    protected $fillable = [
        'code', 'type', 'value', 'maximum_uses', 'minimum_cart_value', 'redeemed',
    ];

    public function products(): BelongsTo
    {
        return $this->belongsTo(SimpleCommerce::productDriver()['model']);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(SimpleCommerce::orderDriver()['model']);
    }
}
