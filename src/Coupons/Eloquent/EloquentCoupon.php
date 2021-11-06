<?php

namespace DoubleThreeDigital\SimpleCommerce\Coupons\Eloquent;

use DoubleThreeDigital\SimpleCommerce\Contracts\Coupon as CouponContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order as ContractsOrder;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CouponNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Http\Resources\GenericResource;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class EloquentCoupon implements CouponContract
{
    public $id;
    public $code;
    public $data;

    /** @var \Illuminate\Database\Eloquent\Model $model */
    protected $model;

    public function all()
    {
        return SimpleCommerce::couponDriver()['model']::all();
    }

    // Note: this method will return a Query Builder instance which
    // contains models, rather than instances of this class.
    public function query()
    {
        return SimpleCommerce::couponDriver()['model']::query();
    }

    public function find($id): CouponContract
    {
        $this->model = SimpleCommerce::couponDriver()['model']::find($id);

        if (! $this->model) {
            throw new CouponNotFound(); // TODO: what message should we be passing in?
        }

        return $this->hydrateFromModel($this->model);
    }

    public function findByCode(string $code): CouponContract
    {
        $this->model = SimpleCommerce::couponDriver()['model']::where('code', $code)->first();

        if (! $this->model) {
            throw new CouponNotFound(); // TODO: what message should we be passing in?
        }

        return $this->hydrateFromModel($this->model);
    }

    public function create(array $data = [], string $site = ''): CouponContract
    {
        $this->model = SimpleCommerce::couponDriver()['model']::create($data);

        return $this->hydrateFromModel($this->model);
    }

    public function save(): CouponContract
    {
        $this->model = $this->model->update(array_merge($this->data, [
            'code' => $this->code,
        ]));

        return $this->hydrateFromModel($this->model);
    }

    public function delete()
    {
        $this->model->delete();
    }

    public function toResource()
    {
        return new GenericResource($this);
    }

    public function toAugmentedArray($keys = null)
    {
        // TODO: If using Runway, we should get the model's blueprint and return it's augmented data.

        return $this->toArray();
    }

    public function id()
    {
        return $this->id;
    }

    public function title(?string $title = null)
    {
        if (! $title) {
            return $this->code;
        }

        $this->code = $title;
        return $this;
    }

    public function slug(?string $slug = null)
    {
        if (! $slug) {
            return $this->code;
        }

        $this->code = $slug;
        return $this;
    }

    public function site($site = null)
    {
        if (! $site) {
            return '';
        }

        // $this->email = $slug;
        return $this;
    }

    public function fresh(): CouponContract
    {
        return $this->find($this->id);
    }

    public function data($data = null)
    {
        if (! $data) {
            return collect($this->data);
        }

        if ($data instanceof Collection) {
            $data = $data->toArray();
        }

        $this->data = $data;
        return $this;
    }

    public function has(string $key): bool
    {
        return $this->data()->has($key);
    }

    public function get(string $key, $default = null)
    {
        return $this->data()->get($key, $default);
    }

    public function set(string $key, $value)
    {
        $this->data()->set($key, $value);

        $this->model->update([$key => $value]);

        return $this;
    }

    public function toArray(): array
    {
        return $this->data()->toArray();
    }

    public function code(): string
    {
        return $this->code;
    }

    public function isValid(ContractsOrder $order): bool
    {
        $order = Order::find($order->id());

        if ($this->has('minimum_cart_value') && $order->has('items_total')) {
            if ($order->get('items_total') < $this->get('minimum_cart_value')) {
                return false;
            }
        }

        if ($this->has('redeemed') && $this->has('maximum_uses') && $this->get('maximum_uses') !== null) {
            if ($this->get('redeemed') >= $this->get('maximum_uses')) {
                return false;
            }
        }

        if ($this->isProductSpecific()) {
            $couponProductsInOrder = $order->lineItems()->filter(function ($lineItem) {
                return in_array($lineItem['product'], $this->get('products'));
            });

            if ($couponProductsInOrder->count() === 0) {
                return false;
            }
        }

        return true;
    }

    public function redeem(): self
    {
        $this->set('redeemed', $this->get('redeemed') + 1);

        return $this;
    }

    public static function bindings(): array
    {
        return [];
    }

    protected function isProductSpecific()
    {
        return $this->model->products()->count() >= 1;
    }

    protected function hydrateFromModel($model): self
    {
        $this->id = $model->id;
        $this->code = $model->code;
        $this->data = Arr::except($model->toArray(), ['id', 'code']);

        return $this;
    }
}
