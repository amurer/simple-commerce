<?php

namespace DoubleThreeDigital\SimpleCommerce\Customers\Eloquent;

use DoubleThreeDigital\SimpleCommerce\Contracts\Customer as CustomerContract;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use DoubleThreeDigital\SimpleCommerce\Http\Resources\GenericResource;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Collection;
use Statamic\Support\Arr;

class EloquentCustomer implements CustomerContract
{
    public $id;
    public $name;
    public $email;
    public $data;

    /** @var \Illuminate\Database\Eloquent\Model $model */
    protected $model;

    public function all()
    {
        return SimpleCommerce::customerDriver()['model']::all();
    }

    // Note: this method will return a Query Builder instance which
    // contains models, rather than instances of this class.
    public function query()
    {
        return SimpleCommerce::customerDriver()['model']::query();
    }

    public function find($id): CustomerContract
    {
        $this->model = SimpleCommerce::customerDriver()['model']::find($id);

        if (! $this->model) {
            throw new CustomerNotFound(); // TODO: what message should we be passing in?
        }

        return $this->hydrateFromModel($this->model);
    }

    public function findByEmail(string $email): CustomerContract
    {
        $this->model = SimpleCommerce::customerDriver()['model']::where('email', $email)->first();

        if (! $this->model) {
            throw(new CustomerNotFound()); // TODO: what message should we be passing in?
        }

        return $this->hydrateFromModel($this->model);
    }

    public function create(array $data = [], string $site = ''): CustomerContract
    {
        $this->model = SimpleCommerce::customerDriver()['model']::create($data);

        return $this->hydrateFromModel($this->model);
    }

    public function save(): CustomerContract
    {
        $this->model = $this->model->update(array_merge($this->data, [
            'name' => $this->name,
            'email' => $this->email,
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
            return $this->name;
        }

        $this->name = $title;
        return $this;
    }

    public function slug(?string $slug = null)
    {
        if (! $slug) {
            return $this->email;
        }

        $this->email = $slug;
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

    public function fresh(): CustomerContract
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

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function orders(): Collection
    {
        return $this->model->orders->map(function ($model) {
            return $this->hydrateFromModel($model);
        });
    }

    public function addOrder($orderId): CustomerContract
    {
        // $this->model->orders()->attach($orderId);

        return $this;
    }

    public function routeNotificationForMail($notification = null)
    {
        return $this->email();
    }

    public static function bindings(): array
    {
        return [];
    }

    protected function hydrateFromModel($model): self
    {
        $this->id = $model->id;
        $this->name = $model->name;
        $this->email = $model->email;
        $this->data = Arr::except($model->toArray(), ['id', 'name', 'email']);

        return $this;
    }
}
