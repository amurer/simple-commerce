<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Eloquent;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineItemModel extends EloquentModel
{
    protected $table = 'line_items';

    protected $fillable = [
        'order_id', 'product', 'variant', 'quantity', 'total', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(SimpleCommerce::orderDriver()['model']);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(SimpleCommerce::productDriver()['model'], 'product');
    }
}
