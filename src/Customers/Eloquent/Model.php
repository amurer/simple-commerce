<?php

namespace DoubleThreeDigital\SimpleCommerce\Customers\Eloquent;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    protected $table = 'customers';

    protected $fillable = [
        'name', 'email',
    ];

    public function orders()
    {
        // return $this->hasMany(Order)
        // return $this->hasMany(config('simple-commerce.models.order'));
    }
}
