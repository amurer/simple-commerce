<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use Illuminate\Support\Collection;

trait LineItems
{
    public function lineItems(): Collection
    {
        if (! $this->has('items')) {
            return collect();
        }

        return collect($this->get('items'))->map(function ($item) {
            $lineItem = (new LineItem)
                ->id($item['id'])
                ->product($item['product'])
                ->quantity($item['quantity'])
                ->total($item['total']);

            if (isset($item['variant'])) {
                $lineItem = $lineItem->variant($item['variant']);
            }

            if (isset($item['metadata'])) {
                $lineItem = $lineItem->metadata($item['metadata']);
            }

            // dump('grab bag', $item, $lineItem);
            dump($this->entry->path());
            dump('----');

            return $lineItem;
        });
    }

    public function lineItem($lineItemId): LineItem
    {
        return $this->lineItems()
            ->firstWhere('id', $lineItemId);
    }

    public function addLineItem(LineItem $lineItem): LineItem
    {
        if (! $lineItem->id()) {
            $lineItem->id(app('stache')->generateId());
        }

        $this->data([
            'items' => $this->lineItems()->push($lineItem)->map(function (LineItem $item) {
                return $item->fileData();
            })->toArray(),
        ]);

        $this->save();
        $this->recalculate();

        dd($this->get('items'), $this->entry->path());

        return $this->lineItem($lineItem->id());
    }

    public function updateLineItem($lineItemId, array $lineItemData): LineItem
    {
        $this->data([
            'items' => $this->lineItems()
                ->map(function ($item) use ($lineItemId, $lineItemData) {
                    if ($item['id'] !== $lineItemId) {
                        return $item;
                    }

                    return array_merge($item, $lineItemData);
                })
                ->toArray(),
        ]);

        $this->save();
        $this->recalculate();

        return $this->lineItem($lineItemId);
    }

    public function removeLineItem($lineItemId): Collection
    {
        $this->data([
            'items' => $this->lineItems()
                ->reject(function ($item) use ($lineItemId) {
                    return $item['id'] === $lineItemId;
                })
                ->toArray(),
        ]);

        $this->save();
        $this->recalculate();

        return $this->lineItems();
    }

    public function clearLineItems(): Collection
    {
        $this->data([
            'items' => [],
        ]);

        $this->save();
        $this->recalculate();

        return $this->lineItems();
    }
}
