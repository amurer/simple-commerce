<?php

namespace DoubleThreeDigital\SimpleCommerce\Customers\Eloquent;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Model extends EloquentModel
{
    protected $table = 'customers';

    protected $fillable = [
        'name', 'email',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(SimpleCommerce::orderDriver()['model']);
    }
}
