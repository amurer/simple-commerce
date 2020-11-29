<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

use DoubleThreeDigital\SimpleCommerce\Data\Address;
use Statamic\Entries\Entry;

interface OrderRepository
{
    public function make(): self;

    public function find(string $id): self;

    public function data(array $data = []);

    public function save(): self;

    public function update(array $data, bool $mergeData = true): self;

    public function entry(): Entry;

    public function toArray(): array;

    public function billingAddress(): ?Address;

    public function shippingAddress(): ?Address;

    public function coupon(): CouponRepository;
}
