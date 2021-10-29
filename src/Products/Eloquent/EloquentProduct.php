<?php

namespace DoubleThreeDigital\SimpleCommerce\Products\Eloquent;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Contracts\Product as ProductContract;
use DoubleThreeDigital\SimpleCommerce\Exceptions\ProductNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\TaxCategory as TaxCategoryFacade;
use DoubleThreeDigital\SimpleCommerce\Products\ProductVariant;
use DoubleThreeDigital\SimpleCommerce\Tax\Standard\TaxCategory;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class EloquentProduct implements ProductContract
{
    public $id;
    public $title;
    public $slug;
    public $data;

     /** @var \Illuminate\Database\Eloquent\Model $model */
     protected $model;

     public function all()
    {
        return SimpleCommerce::productDriver()['model']::all();
    }

    // Note: this method will return a Query Builder instance which
    // contains models, rather than instances of this class.
    public function query()
    {
        return SimpleCommerce::productDriver()['model']::query();
    }

    public function find($id): self
    {
        $this->model = SimpleCommerce::productDriver()['model']::find($id);

        if (! $this->model) {
            throw new ProductNotFound(); // TODO: what message should we be passing in?
        }

        return $this->hydrateFromModel($this->model);
    }

    public function create(array $data = [], string $site = ''): ProductContract
    {
        $this->model = SimpleCommerce::productDriver()['model']::create($data);

        return $this->hydrateFromModel($this->model);
    }

    public function save(): ProductContract
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
        // TODO: Implement toResource() method.
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
            return $this->title;
        }

        $this->title = $title;
        return $this;
    }

    public function slug(?string $slug = null)
    {
        if (! $slug) {
            return $this->slug;
        }

        $this->slug = $slug;
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

    public function fresh(): ProductContract
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

    public function stockCount()
    {
        if ($this->purchasableType() === 'variants' || ! $this->has('stock')) {
            return null;
        }

        return (int) $this->get('stock');
    }

    public function purchasableType(): string
    {
        if (isset($this->get('product_variants')['variants'])) {
            return 'variants';
        }

        return 'product';
    }

    public function variants(): Collection
    {
        if (! isset($this->get('product_variants')['variants'])) {
            return collect();
        }

        return collect($this->get('product_variants')['options'])
            ->map(function ($variantOption) {
                $productVariant = (new ProductVariant)
                    ->key($variantOption['key'])
                    ->product($this)
                    ->name($variantOption['variant'])
                    ->price($variantOption['price'])
                    ->data(Arr::except($variantOption, ['key', 'variant', 'price', 'stock']));

                if (isset($variantOption['stock'])) {
                    $productVariant->stock($variantOption['stock']);
                }

                return $productVariant;
            });
    }

    public function variant(string $optionKey): ?ProductVariant
    {
        return $this->variants()->filter(function ($variant) use ($optionKey) {
            return $variant->key() === $optionKey;
        })->first();
    }

    public function taxCategory(): ?TaxCategory
    {
        if (! $this->get('tax_category')) {
            return TaxCategoryFacade::find('default');
        }

        return TaxCategoryFacade::find($this->get('tax_category'));
    }

    public static function bindings(): array
    {
        return [];
    }

    protected function hydrateFromModel($model): self
    {
        $this->id = $model->id;
        $this->title = $model->title;
        $this->slug = $model->slug;
        $this->data = Arr::except($model->toArray(), ['id', 'title', 'slug']);

        return $this;
    }
}
